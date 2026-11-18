<?php
/**
 * Fake tests.
 *
 * @package SugarCommerce\Faker
 */

declare( strict_types=1 );

namespace SugarCommerce\Faker\Tests;

use SugarCommerce\Faker\Data;
use SugarCommerce\Faker\Fake;
use PHPUnit\Framework\TestCase;
use WP_Error;

/**
 * Covers Fake.
 *
 * A generator is random, so the assertions are about the shape of what comes
 * back and the bounds it respects, sampled enough times that a wrong bound
 * would show. Nothing here asserts a particular value.
 */
final class FakeTest extends TestCase {

	/**
	 * How many samples each bound check takes.
	 */
	private const SAMPLES = 300;

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		fk_reset();
	}

	/**
	 * Names come from the shipped tables.
	 *
	 * @return void
	 */
	public function test_names_come_from_the_data_tables(): void {
		for ( $i = 0; $i < 20; $i++ ) {
			$this->assertContains( Fake::first_name(), Data::FIRST_NAMES );
			$this->assertContains( Fake::last_name(), Data::LAST_NAMES );
		}
	}

	/**
	 * A full name is two parts from those tables.
	 *
	 * @return void
	 */
	public function test_name_joins_first_and_last(): void {
		[ $first, $last ] = explode( ' ', Fake::name(), 2 );

		$this->assertContains( $first, Data::FIRST_NAMES );
		$this->assertContains( $last, Data::LAST_NAMES );
	}

	/**
	 * Generated addresses are valid, and use a shipped domain.
	 *
	 * @return void
	 */
	public function test_email_is_valid(): void {
		for ( $i = 0; $i < 50; $i++ ) {
			$email = Fake::email();

			$this->assertNotFalse( filter_var( $email, FILTER_VALIDATE_EMAIL ), $email );
			$this->assertContains( substr( $email, strpos( $email, '@' ) + 1 ), Data::EMAIL_DOMAINS );
		}
	}

	/**
	 * Supplied names are used.
	 *
	 * @return void
	 */
	public function test_email_uses_supplied_names(): void {
		/*
		 * Two of the four formats abbreviate one of the names to its initial
		 * ("alovelace", "adal"), so neither is guaranteed whole -- but one of
		 * them always is, and nothing generated may appear.
		 */
		for ( $i = 0; $i < 50; $i++ ) {
			$local = strstr( Fake::email( 'Ada', 'Lovelace' ), '@', true );

			$this->assertTrue(
				str_contains( $local, 'ada' ) || str_contains( $local, 'lovelace' ),
				$local
			);
			$this->assertMatchesRegularExpression( '/^(ada|lovelace|a|l)[.a-z]*$/', $local );
		}
	}

	/**
	 * Empty names fall back to generated ones rather than reading offset 0 of "".
	 *
	 * Two of the four formats take $first[0] or $last[0]. With ?? an empty
	 * string is not null, so it was passed straight through and PHP warned
	 * about an uninitialized string offset.
	 *
	 * @return void
	 */
	public function test_email_with_empty_names(): void {
		for ( $i = 0; $i < 50; $i++ ) {
			$email = Fake::email( '', '' );

			$this->assertNotFalse( filter_var( $email, FILTER_VALIDATE_EMAIL ), $email );
			$this->assertStringNotContainsString( '@@', $email );
			$this->assertNotSame( '@', $email[0] );
		}
	}

	/**
	 * A phone number has the shape the format string promises.
	 *
	 * @return void
	 */
	public function test_phone_shape(): void {
		$this->assertMatchesRegularExpression( '/^\+\d{1,2} \d{3}-\d{3}-\d{4}$/', Fake::phone() );
	}

	/**
	 * A SKU is a shipped prefix and five digits.
	 *
	 * @return void
	 */
	public function test_sku_shape(): void {
		$sku = Fake::sku();

		$this->assertMatchesRegularExpression( '/^[A-Z]+-\d{5}$/', $sku );
		$this->assertContains( explode( '-', $sku )[0], Data::SKU_PREFIXES );
	}

	/**
	 * A price is an integer inside the range, in minor units.
	 *
	 * @return void
	 */
	public function test_price_respects_bounds(): void {
		for ( $i = 0; $i < self::SAMPLES; $i++ ) {
			$price = Fake::price( 100, 500 );

			$this->assertGreaterThanOrEqual( 100, $price );
			$this->assertLessThanOrEqual( 500, $price );
		}
	}

	/**
	 * Both ends of the range are reachable.
	 *
	 * @return void
	 */
	public function test_price_can_hit_both_ends(): void {
		$seen = [];

		for ( $i = 0; $i < self::SAMPLES; $i++ ) {
			$seen[ Fake::price( 1, 3 ) ] = true;
		}

		$keys = array_keys( $seen );
		sort( $keys );

		$this->assertSame( [ 1, 2, 3 ], $keys );
	}

	/**
	 * number() respects its bounds.
	 *
	 * @return void
	 */
	public function test_number_respects_bounds(): void {
		for ( $i = 0; $i < self::SAMPLES; $i++ ) {
			$n = Fake::number( 5, 7 );

			$this->assertGreaterThanOrEqual( 5, $n );
			$this->assertLessThanOrEqual( 7, $n );
		}
	}

	/**
	 * A country is one of the shipped list.
	 *
	 * @return void
	 */
	public function test_country(): void {
		$this->assertContains( Fake::country(), Data::COUNTRY_CODES );
	}

	/**
	 * A sentence is capitalised and ends in a full stop.
	 *
	 * @return void
	 */
	public function test_sentence_shape(): void {
		$sentence = Fake::sentence();

		$this->assertStringEndsWith( '.', $sentence );
		$this->assertSame( ucfirst( $sentence ), $sentence );
	}

	/**
	 * An explicit word count is honoured.
	 *
	 * @return void
	 */
	public function test_sentence_word_count(): void {
		$this->assertCount( 4, explode( ' ', Fake::sentence( 4 ) ) );
	}

	/**
	 * A non-positive word count falls back to a random length.
	 *
	 * @return void
	 */
	public function test_sentence_with_no_count(): void {
		foreach ( [ 0, -5 ] as $count ) {
			$words = explode( ' ', Fake::sentence( $count ) );

			$this->assertGreaterThanOrEqual( 6, count( $words ) );
			$this->assertLessThanOrEqual( 12, count( $words ) );
		}
	}

	/**
	 * A paragraph is made of sentences.
	 *
	 * @return void
	 */
	public function test_paragraph(): void {
		$paragraph = Fake::paragraph( 3 );

		$this->assertSame( 3, substr_count( $paragraph, '.' ) );
	}

	/**
	 * A date lands between the two bounds, and honours the format.
	 *
	 * @return void
	 */
	public function test_date_between(): void {
		for ( $i = 0; $i < 50; $i++ ) {
			$date = Fake::date_between( '-10 days', 'now', 'Y-m-d' );

			$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', $date );
			$this->assertGreaterThanOrEqual( strtotime( '-11 days' ), strtotime( $date ) );
			$this->assertLessThanOrEqual( strtotime( 'tomorrow' ), strtotime( $date ) );
		}
	}

	/**
	 * A past date is in the past, within the window.
	 *
	 * @return void
	 */
	public function test_past_date(): void {
		for ( $i = 0; $i < 50; $i++ ) {
			$stamp = strtotime( Fake::past_date( 30 ) );

			$this->assertLessThanOrEqual( time(), $stamp );
			$this->assertGreaterThanOrEqual( strtotime( '-31 days' ), $stamp );
		}
	}

	/**
	 * Dates are generated in UTC, so they do not shift with the server timezone.
	 *
	 * date() formats in whatever timezone the process happens to be in, so the
	 * same instant renders as two different days either side of the dateline.
	 * Pinning start and end to the same moment makes the timestamp
	 * deterministic, which leaves the timezone as the only variable.
	 *
	 * @return void
	 */
	public function test_dates_are_utc(): void {
		$original = date_default_timezone_get();

		try {
			date_default_timezone_set( 'Pacific/Kiritimati' );
			$ahead = Fake::date_between( 'now', 'now', 'Y-m-d H' );

			date_default_timezone_set( 'Pacific/Midway' );
			$behind = Fake::date_between( 'now', 'now', 'Y-m-d H' );
		} finally {
			date_default_timezone_set( $original );
		}

		$this->assertSame( $ahead, $behind );
	}

	/**
	 * random_element() returns something from the array it was given.
	 *
	 * @return void
	 */
	public function test_random_element(): void {
		$items = [ 'a', 'b', 'c' ];

		for ( $i = 0; $i < 60; $i++ ) {
			$this->assertContains( Fake::random_element( $items ), $items );
		}
	}

	/**
	 * weighted() only ever returns a key it was given.
	 *
	 * @return void
	 */
	public function test_weighted_returns_a_given_key(): void {
		$weights = [ 'common' => 90, 'rare' => 10 ];

		for ( $i = 0; $i < self::SAMPLES; $i++ ) {
			$this->assertContains( Fake::weighted( $weights ), array_keys( $weights ) );
		}
	}

	/**
	 * A zero weight is never chosen.
	 *
	 * @return void
	 */
	public function test_weighted_skips_zero_weights(): void {
		for ( $i = 0; $i < self::SAMPLES; $i++ ) {
			$this->assertSame( 'yes', Fake::weighted( [ 'yes' => 1, 'never' => 0 ] ) );
		}
	}

	/**
	 * The heavier option really is the more frequent one.
	 *
	 * @return void
	 */
	public function test_weighted_favours_the_heavier_option(): void {
		$counts = [ 'common' => 0, 'rare' => 0 ];

		for ( $i = 0; $i < 1000; $i++ ) {
			++$counts[ Fake::weighted( [ 'common' => 95, 'rare' => 5 ] ) ];
		}

		$this->assertGreaterThan( $counts['rare'], $counts['common'] );
	}

	/**
	 * many() runs the generator exactly count times.
	 *
	 * @return void
	 */
	public function test_many(): void {
		$runs = 0;

		$items = Fake::many(
			static function () use ( &$runs ) {
				return ++$runs;
			},
			5
		);

		$this->assertSame( [ 1, 2, 3, 4, 5 ], $items );
		$this->assertSame( 5, $runs );
	}

	/**
	 * A non-positive count produces nothing and runs nothing.
	 *
	 * @return void
	 */
	public function test_many_with_no_count(): void {
		$runs = 0;

		$items = Fake::many(
			static function () use ( &$runs ) {
				++$runs;

				return 1;
			},
			0
		);

		$this->assertSame( [], $items );
		$this->assertSame( 0, $runs );
	}

	/**
	 * boolean() honours its chance, and both ends are absolute.
	 *
	 * @return void
	 */
	public function test_boolean(): void {
		for ( $i = 0; $i < 50; $i++ ) {
			$this->assertTrue( Fake::boolean( 100 ) );
			$this->assertFalse( Fake::boolean( 0 ) );
		}

		$this->assertIsBool( Fake::boolean() );
	}

	/**
	 * user_id() asks for ids and returns one of them.
	 *
	 * @return void
	 */
	public function test_user_id(): void {
		$GLOBALS['fk']['users'] = [ 3, 7, 11 ];

		$id = Fake::user_id();

		$this->assertContains( $id, [ 3, 7, 11 ] );
		$this->assertIsInt( $id );
		$this->assertSame( 'ID', fk_last_query()['fields'] );
	}

	/**
	 * A role is passed through to the query, and omitted when empty.
	 *
	 * @return void
	 */
	public function test_user_id_role(): void {
		$GLOBALS['fk']['users'] = [ 1 ];

		Fake::user_id( 'customer' );
		$this->assertSame( 'customer', fk_last_query()['role'] );

		Fake::user_id();
		$this->assertArrayNotHasKey( 'role', fk_last_query() );
	}

	/**
	 * No users means null, not an error.
	 *
	 * @return void
	 */
	public function test_user_id_with_no_users(): void {
		$this->assertNull( Fake::user_id() );
	}

	/**
	 * post_id() queries the requested type and status.
	 *
	 * @return void
	 */
	public function test_post_id(): void {
		$GLOBALS['fk']['posts'] = [ 12 ];

		$this->assertSame( 12, Fake::post_id( 'download', 'draft' ) );

		$query = fk_last_query();

		$this->assertSame( 'download', $query['post_type'] );
		$this->assertSame( 'draft', $query['post_status'] );
		$this->assertSame( 'ids', $query['fields'] );
	}

	/**
	 * No posts means null.
	 *
	 * @return void
	 */
	public function test_post_id_with_no_posts(): void {
		$this->assertNull( Fake::post_id() );
	}

	/**
	 * attachment_id() queries attachments, and filters by mime when asked.
	 *
	 * @return void
	 */
	public function test_attachment_id(): void {
		$GLOBALS['fk']['posts'] = [ 5 ];

		$this->assertSame( 5, Fake::attachment_id( 'image/png' ) );

		$query = fk_last_query();

		$this->assertSame( 'attachment', $query['post_type'] );
		$this->assertSame( 'inherit', $query['post_status'] );
		$this->assertSame( 'image/png', $query['post_mime_type'] );

		Fake::attachment_id();
		$this->assertArrayNotHasKey( 'post_mime_type', fk_last_query() );
	}

	/**
	 * term_id() queries the taxonomy, including empty terms.
	 *
	 * @return void
	 */
	public function test_term_id(): void {
		$GLOBALS['fk']['terms'] = [ 9 ];

		$this->assertSame( 9, Fake::term_id( 'download_category' ) );

		$query = fk_last_query();

		$this->assertSame( 'download_category', $query['taxonomy'] );
		$this->assertFalse( $query['hide_empty'] );
	}

	/**
	 * An unregistered taxonomy yields null rather than a fatal.
	 *
	 * get_terms() returns a WP_Error for one, and a WP_Error is truthy, so a
	 * bare empty() check would fall through and try to pick an element from it.
	 *
	 * @return void
	 */
	public function test_term_id_with_a_wp_error(): void {
		$GLOBALS['fk']['terms'] = new WP_Error( 'invalid_taxonomy' );

		$this->assertNull( Fake::term_id( 'no_such_taxonomy' ) );
	}

	/**
	 * No terms means null.
	 *
	 * @return void
	 */
	public function test_term_id_with_no_terms(): void {
		$this->assertNull( Fake::term_id() );
	}

	/**
	 * Supplied names are reduced to a valid local part.
	 *
	 * "Mary Jane O'Brien" is a real name, and "mary jane.o'brien@" is not a
	 * real address.
	 *
	 * @return void
	 */
	public function test_email_sanitises_supplied_names(): void {
		for ( $i = 0; $i < 50; $i++ ) {
			$email = Fake::email( 'Mary Jane', "O'Brien" );

			$this->assertNotFalse( filter_var( $email, FILTER_VALIDATE_EMAIL ), $email );
			$this->assertMatchesRegularExpression( '/^[a-z.]+@/', $email );
		}
	}

	/**
	 * Float weights are a coin toss, not a TypeError.
	 *
	 * rand() takes ints, and under strict types a total of 0.5 + 0.5 was a
	 * TypeError.
	 *
	 * @return void
	 */
	public function test_weighted_accepts_float_weights(): void {
		$weights = [ 'a' => 0.5, 'b' => 0.5 ];

		for ( $i = 0; $i < self::SAMPLES; $i++ ) {
			$this->assertContains( Fake::weighted( $weights ), [ 'a', 'b' ] );
		}
	}

	/**
	 * The fallback at the top of the range never lands on a zero weight.
	 *
	 * The old fallback was the first key, whatever its weight.
	 *
	 * @return void
	 */
	public function test_weighted_never_falls_back_to_a_zero_weight(): void {
		for ( $i = 0; $i < self::SAMPLES; $i++ ) {
			$this->assertSame( 'yes', Fake::weighted( [ 'never' => 0, 'yes' => 1 ] ) );
		}
	}

	/**
	 * With nothing to pick, the answer is nothing.
	 *
	 * @return void
	 */
	public function test_weighted_with_no_positive_weight_is_null(): void {
		$this->assertNull( Fake::weighted( [] ) );
		$this->assertNull( Fake::weighted( [ 'a' => 0, 'b' => -1 ] ) );
		$this->assertNull( Fake::random_element( [] ) );
	}

	/**
	 * A reversed range is read as the range it obviously is.
	 *
	 * @return void
	 */
	public function test_a_reversed_range_is_tolerated(): void {
		for ( $i = 0; $i < 50; $i++ ) {
			$price = Fake::price( 500, 100 );

			$this->assertGreaterThanOrEqual( 100, $price );
			$this->assertLessThanOrEqual( 500, $price );
		}
	}


	/**
	 * One seed, the same data; a fresh seed after 0.
	 *
	 * @return void
	 */
	public function test_seed_repeats(): void {
		$draw = static fn (): array => array( Fake::name(), Fake::email(), Fake::price(), Fake::date_between( '2020-01-01', '2020-12-31' ), Fake::weighted( array( 'a' => 1, 'b' => 1 ) ), Fake::sentence() );

		Fake::seed( 42 );
		$first = $draw();
		Fake::seed( 42 );

		$this->assertSame( $first, $draw() );

		Fake::seed( 0 );
	}
}
