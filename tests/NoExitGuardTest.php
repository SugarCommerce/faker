<?php
/**
 * Source guard.
 *
 * @package SugarCommerce\Faker
 */

declare( strict_types=1 );

namespace SugarCommerce\Faker\Tests;

use PHPUnit\Framework\TestCase;

/**
 * No shipped file may end the process on load.
 */
final class NoExitGuardTest extends TestCase {

	/**
	 * src/Fake.php opened with `defined( 'ABSPATH' ) || exit;`.
	 *
	 * It is a PSR-4 class file, so that line ran the moment the autoloader
	 * reached the class -- which under PHPUnit is before any test, and outside
	 * WordPress means ABSPATH is undefined. The process ended there: no output,
	 * exit status 0. A suite that never ran and a lint that read no files both
	 * report as a pass, so nothing was going to notice.
	 *
	 * A class file needs no guard. It declares a class and does nothing else,
	 * which is exactly the direct-access case the guard exists to prevent.
	 *
	 * @return void
	 */
	public function test_no_source_file_exits_on_load(): void {
		foreach ( $this->sources() as $file ) {
			$this->assertDoesNotMatchRegularExpression(
				'/^\s*(defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)\s*\|\|\s*)?(exit|die)\b/m',
				(string) file_get_contents( $file ),
				basename( $file ) . ' ends the process when it is loaded.'
			);
		}
	}

	/**
	 * Every PHP file that ships.
	 *
	 * @return array
	 */
	private function sources(): array {
		return glob( dirname( __DIR__ ) . '/src/*.php' ) ?: [];
	}

}
