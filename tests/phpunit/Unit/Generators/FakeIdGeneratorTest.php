<?php
/**
 * MediaWiki IDProvider Extension
 *
 * Provides (unique) IDs using different ID algorithms.
 *
 * @link https://github.com/gesinn-it/IDProvider
 *
 * @author gesinn.it GmbH & Co. KG
 * @license MIT
 */

namespace Tests\Unit;

use MediaWiki\Extension\IdProvider\Generators\FakeIdGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @group IDProvider
 * @covers \MediaWiki\Extension\IdProvider\Generators\FakeIdGenerator
 */
class FakeIdGeneratorTest extends TestCase {

	/**
	 * assertMatchesRegularExpression() replaced the deprecated assertRegExp() in PHPUnit 9.1;
	 * assertRegExp() is what the extension's minimum supported MediaWiki version (1.39, which
	 * bundles PHPUnit 8.5) provides.
	 */
	private function assertMatchesRegex( string $pattern, string $string ): void {
		if ( method_exists( $this, 'assertMatchesRegularExpression' ) ) {
			$this->assertMatchesRegularExpression( $pattern, $string );
		} else {
			$this->assertRegExp( $pattern, $string );
		}
	}

	public function testGeneratesNonEmptyAlphanumericIds() {
		$id = ( new FakeIdGenerator )->generate();
		$this->assertMatchesRegex( '/^[0-9a-z]+$/', $id );
	}

	public function testGeneratesDifferentIdsOnSuccessiveCalls() {
		$generator = new FakeIdGenerator();

		$this->assertNotSame( $generator->generate(), $generator->generate() );
	}

	public function testGenerateDoesNotTriggerDeprecationWarning() {
		$caught = [];
		set_error_handler( static function ( $errno, $errstr ) use ( &$caught ) {
			$caught[] = $errstr;
			return true;
		}, E_DEPRECATED );

		try {
			( new FakeIdGenerator )->generate();
		} finally {
			restore_error_handler();
		}

		$this->assertSame( [], $caught, 'generate() must not trigger any E_DEPRECATED warning' );
	}
}
