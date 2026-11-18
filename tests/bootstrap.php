<?php
/**
 * Test bootstrap.
 *
 * @package SugarCommerce\Faker
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/**
 * What the stubbed WordPress queries return, and what they were asked.
 *
 * @var array
 */
$GLOBALS['fk'] = [
	'users'    => [],
	'posts'    => [],
	'terms'    => [],
	'queries'  => [],
];

/**
 * Reset everything a test set up.
 *
 * @return void
 */
function fk_reset(): void {
	$GLOBALS['fk'] = [
		'users'   => [],
		'posts'   => [],
		'terms'   => [],
		'queries' => [],
	];
}

/**
 * The arguments of the most recent query.
 *
 * @return array
 */
function fk_last_query(): array {
	$queries = $GLOBALS['fk']['queries'];

	return end( $queries ) ?: [];
}

if ( ! function_exists( 'get_users' ) ) {
	/**
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	function get_users( array $args = [] ): array {
		$GLOBALS['fk']['queries'][] = [ 'fn' => 'get_users' ] + $args;

		return $GLOBALS['fk']['users'];
	}
}

if ( ! function_exists( 'get_posts' ) ) {
	/**
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	function get_posts( array $args = [] ): array {
		$GLOBALS['fk']['queries'][] = [ 'fn' => 'get_posts' ] + $args;

		return $GLOBALS['fk']['posts'];
	}
}

if ( ! function_exists( 'get_terms' ) ) {
	/**
	 * @param array $args Query arguments.
	 *
	 * @return array|WP_Error
	 */
	function get_terms( array $args = [] ) {
		$GLOBALS['fk']['queries'][] = [ 'fn' => 'get_terms' ] + $args;

		return $GLOBALS['fk']['terms'];
	}
}


if ( ! function_exists( 'is_wp_error' ) ) {
	/**
	 * @param mixed $thing Value.
	 *
	 * @return bool
	 */
	function is_wp_error( $thing ): bool {
		return $thing instanceof WP_Error;
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Minimal stand-in for core's error object. get_terms() returns one for an
	 * unregistered taxonomy, and it is truthy, so the guard against it matters.
	 */
	class WP_Error {

		/** @var string */
		public string $code;

		/**
		 * @param string $code Error code.
		 */
		public function __construct( string $code = '' ) {
			$this->code = $code;
		}
	}
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
