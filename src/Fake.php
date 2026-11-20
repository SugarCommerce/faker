<?php
/**
 * Fake
 *
 * @package   SugarCommerce\Faker
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace SugarCommerce\Faker;

/**
 * Fake data generator
 *
 * @since 1.0.0
 */
class Fake {

	/** ========================================================================
	 * PEOPLE
	 * ======================================================================== */

	/**
	 * Generate a random first name
	 *
	 * @return string
	 */
	public static function first_name(): string {
		return self::random_element( Data::FIRST_NAMES );
	}

	/**
	 * Generate a random last name
	 *
	 * @return string
	 */
	public static function last_name(): string {
		return self::random_element( Data::LAST_NAMES );
	}

	/**
	 * Generate a random full name
	 *
	 * @return string
	 */
	public static function name(): string {
		return self::first_name() . ' ' . self::last_name();
	}

	/**
	 * Generate a random email address
	 *
	 * Optionally pass a first and last name for consistency.
	 *
	 * @param string|null $first_name First name to use.
	 * @param string|null $last_name  Last name to use.
	 *
	 * @return string
	 */
	public static function email( ?string $first_name = null, ?string $last_name = null ): string {
		// A supplied name is reduced to what a local part may hold. "Mary
		// Jane O'Brien" is a real name and "mary jane.o'brien@" is not a
		// real address; a name with nothing usable in it falls back to a
		// generated one rather than reading offset 0 of an empty string.
		$first  = self::local_part( (string) $first_name ) ?: self::local_part( self::first_name() );
		$last   = self::local_part( (string) $last_name ) ?: self::local_part( self::last_name() );
		$domain = self::random_element( Data::EMAIL_DOMAINS );

		$formats = [
			$first . '.' . $last,
			$first[0] . $last,
			$first . $last[0],
			$last . '.' . $first,
		];

		return self::random_element( $formats ) . '@' . $domain;
	}

	/**
	 * Generate a random phone number
	 *
	 * @return string
	 */
	public static function phone(): string {
		return sprintf(
			'+%d %d-%d-%d',
			rand( 1, 99 ),
			rand( 100, 999 ),
			rand( 100, 999 ),
			rand( 1000, 9999 )
		);
	}

	/** ========================================================================
	 * COMPANIES
	 * ======================================================================== */

	/**
	 * Generate a random company name
	 *
	 * @return string
	 */
	public static function company(): string {
		$name     = self::random_element( Data::LAST_NAMES );
		$template = self::random_element( Data::COMPANY_TEMPLATES );

		return sprintf( $template, $name );
	}

	/** ========================================================================
	 * COMMERCE
	 * ======================================================================== */

	/**
	 * Generate a random price in smallest currency unit (cents)
	 *
	 * @param int $min Minimum value in cents (default 100 = $1.00).
	 * @param int $max Maximum value in cents (default 500000 = $5,000.00).
	 *
	 * @return int
	 */
	public static function price( int $min = 100, int $max = 500000 ): int {
		return self::number( $min, $max );
	}

	/**
	 * Generate a random SKU
	 *
	 * @return string
	 */
	public static function sku(): string {
		$prefix = self::random_element( Data::SKU_PREFIXES );

		return $prefix . '-' . rand( 10000, 99999 );
	}

	/**
	 * Generate a random product name
	 *
	 * @return string
	 */
	public static function product_name(): string {
		$adjective = self::random_element( Data::PRODUCT_ADJECTIVES );
		$noun      = self::random_element( Data::PRODUCT_NOUNS );

		return $adjective . ' ' . $noun;
	}

	/** ========================================================================
	 * LOCATION
	 * ======================================================================== */

	/**
	 * Generate a random country code (lowercase ISO 3166-1 alpha-2)
	 *
	 * @return string
	 */
	public static function country(): string {
		return self::random_element( Data::COUNTRY_CODES );
	}

	/** ========================================================================
	 * DATES
	 * ======================================================================== */

	/**
	 * Generate a random date between two relative dates
	 *
	 * @param string $start Start date (strtotime-compatible, default '-2 years').
	 * @param string $end   End date (strtotime-compatible, default 'now').
	 * @param string $format Date format (default 'Y-m-d H:i:s').
	 *
	 * @return string
	 */
	public static function date_between( string $start = '-2 years', string $end = 'now', string $format = 'Y-m-d H:i:s' ): string {
		$start_ts = strtotime( $start );
		$end_ts   = strtotime( $end );

		if ( $start_ts === false ) {
			$start_ts = strtotime( '-2 years' );
		}

		if ( $end_ts === false ) {
			$end_ts = time();
		}

		if ( $start_ts > $end_ts ) {
			$start_ts = $end_ts;
		}

		return gmdate( $format, rand( $start_ts, $end_ts ) );
	}

	/**
	 * Generate a random date in the past
	 *
	 * @param int    $max_days Maximum number of days in the past (default 730).
	 * @param string $format   Date format (default 'Y-m-d H:i:s').
	 *
	 * @return string
	 */
	public static function past_date( int $max_days = 730, string $format = 'Y-m-d H:i:s' ): string {
		$days_ago = rand( 1, $max_days );

		return gmdate( $format, strtotime( "-{$days_ago} days" ) );
	}

	/** ========================================================================
	 * TEXT
	 * ======================================================================== */

	/**
	 * Generate a random sentence
	 *
	 * @param int $word_count Approximate number of words (default 6-12 random).
	 *
	 * @return string
	 */
	public static function sentence( int $word_count = 0 ): string {
		if ( $word_count <= 0 ) {
			$word_count = rand( 6, 12 );
		}

		$words    = [];
		$all      = Data::WORDS;
		$count    = count( $all );

		for ( $i = 0; $i < $word_count; $i++ ) {
			$words[] = $all[ rand( 0, $count - 1 ) ];
		}

		$words[0] = ucfirst( $words[0] );

		return implode( ' ', $words ) . '.';
	}

	/**
	 * Generate a random paragraph
	 *
	 * @param int $sentence_count Number of sentences (default 3-6 random).
	 *
	 * @return string
	 */
	public static function paragraph( int $sentence_count = 0 ): string {
		if ( $sentence_count <= 0 ) {
			$sentence_count = rand( 3, 6 );
		}

		$sentences = [];
		for ( $i = 0; $i < $sentence_count; $i++ ) {
			$sentences[] = self::sentence();
		}

		return implode( ' ', $sentences );
	}

	/** ========================================================================
	 * WordPress
	 * ======================================================================== */

	/**
	 * Get a random existing user ID
	 *
	 * @param string $role Optional role to filter by.
	 *
	 * @return int|null User ID or null if none found.
	 */
	public static function user_id( string $role = '' ): ?int {
		$args = [
			'fields' => 'ID',
			'number' => -1,
		];

		if ( ! empty( $role ) ) {
			$args['role'] = $role;
		}

		$users = get_users( $args );

		if ( empty( $users ) ) {
			return null;
		}

		return (int) self::random_element( $users );
	}

	/**
	 * Get a random existing post ID
	 *
	 * @param string $post_type   Post type (default 'post').
	 * @param string $post_status Post status (default 'publish').
	 *
	 * @return int|null Post ID or null if none found.
	 */
	public static function post_id( string $post_type = 'post', string $post_status = 'publish' ): ?int {
		$posts = get_posts( [
			'post_type'      => $post_type,
			'post_status'    => $post_status,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		] );

		if ( empty( $posts ) ) {
			return null;
		}

		return (int) self::random_element( $posts );
	}

	/**
	 * Get a random existing attachment ID
	 *
	 * @param string $mime_type Optional MIME type filter (e.g., 'image/jpeg', 'image').
	 *
	 * @return int|null Attachment ID or null if none found.
	 */
	public static function attachment_id( string $mime_type = '' ): ?int {
		$args = [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		];

		if ( ! empty( $mime_type ) ) {
			$args['post_mime_type'] = $mime_type;
		}

		$attachments = get_posts( $args );

		if ( empty( $attachments ) ) {
			return null;
		}

		return (int) self::random_element( $attachments );
	}

	/**
	 * Get a random existing term ID
	 *
	 * @param string $taxonomy Taxonomy name (default 'category').
	 *
	 * @return int|null Term ID or null if none found.
	 */
	public static function term_id( string $taxonomy = 'category' ): ?int {
		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		] );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return null;
		}

		return (int) self::random_element( $terms );
	}

	/** ========================================================================
	 * UTILITIES
	 * ======================================================================== */

	/**
	 * Make what follows the same on every run.
	 *
	 * Everything here draws from PHP's Mersenne Twister, so one seed gives
	 * the same names, prices and dates again -- the same screenshots, the
	 * same failing test. 0 goes back to a fresh random seed.
	 *
	 * @since 1.0.0
	 *
	 * @param int $seed The seed.
	 *
	 * @return void
	 */
	public static function seed( int $seed ): void {
		if ( 0 === $seed ) {
			mt_srand();

			return;
		}

		mt_srand( $seed );
	}

	/**
	 * Pick a random element from an array
	 *
	 * @param array $items Source array.
	 *
	 * @return mixed Random element, or null if there is nothing to pick from.
	 */
	public static function random_element( array $items ): mixed {
		if ( [] === $items ) {
			return null;
		}

		return $items[ array_rand( $items ) ];
	}

	/**
	 * Pick a random element using weighted probabilities
	 *
	 * Weights may be any non-negative number; they do not have to add up to
	 * anything in particular. A zero weight is never picked. With no
	 * positive weight at all there is nothing to pick, and the answer is null.
	 *
	 * @param array $weighted Associative array of value => weight (e.g., ['active' => 60, 'pending' => 20]).
	 *
	 * @return mixed Random weighted element.
	 */
	public static function weighted( array $weighted ): mixed {
		$positive = array_filter( $weighted, static fn( $weight ): bool => is_numeric( $weight ) && $weight > 0 );

		if ( [] === $positive ) {
			return null;
		}

		// A point in [0, total), as a float. rand() wants ints, and under
		// strict types a total of 0.5 + 0.5 was a TypeError rather than a
		// coin toss.
		$total  = array_sum( $positive );
		$random = mt_rand( 0, mt_getrandmax() - 1 ) / mt_getrandmax() * $total;

		$current = 0;
		foreach ( $positive as $value => $weight ) {
			$current += $weight;
			if ( $random < $current ) {
				return $value;
			}
		}

		// Rounding at the very top of the range. Whatever is last still has
		// a positive weight, which the old fallback to the first key did not
		// guarantee.
		return array_key_last( $positive );
	}

	/**
	 * Generate multiple items using a callable
	 *
	 * @param callable $generator Function that returns a single item.
	 * @param int      $count     Number of items to generate.
	 *
	 * @return array
	 */
	public static function many( callable $generator, int $count ): array {
		$items = [];
		for ( $i = 0; $i < $count; $i++ ) {
			$items[] = $generator();
		}

		return $items;
	}

	/**
	 * Generate a random integer within a range
	 *
	 * @param int $min Minimum value.
	 * @param int $max Maximum value.
	 *
	 * @return int
	 */
	public static function number( int $min = 0, int $max = 100 ): int {
		// Reversed bounds are a ValueError in mt_rand(), and rand() reads
		// them as a different range. Neither is what a caller meant.
		return rand( min( $min, $max ), max( $min, $max ) );
	}

	/**
	 * Generate a random boolean
	 *
	 * @param int $chance_of_true Percentage chance of returning true (default 50).
	 *
	 * @return bool
	 */
	public static function boolean( int $chance_of_true = 50 ): bool {
		return rand( 1, 100 ) <= $chance_of_true;
	}
	/**
	 * Reduce a name to what an email local part may hold.
	 *
	 * @param string $name The name.
	 *
	 * @return string Lower-case letters and digits only; may be empty.
	 */
	private static function local_part( string $name ): string {
		return (string) preg_replace( '/[^a-z0-9]/', '', strtolower( $name ) );
	}
}
