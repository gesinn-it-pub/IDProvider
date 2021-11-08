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

use MediaWiki\Extension\IdProvider\IdProvider;
use PHPUnit\Framework\TestCase;

/**
 * @group IDProvider
 * @covers \MediaWiki\Extension\IdProvider\IdProvider
 */
class IdProviderTest extends TestCase {

	public function testPrefixIsAdded() {
		$provider = new IdProvider( self::generator( [ '1', '2' ] ), self::noopIsUniqueId() );
		$id = $provider->getId( [ 'prefix' => 'ABC-' ] );
		$this->assertSame( 'ABC-1', $id );
	}

	public function testChecksForUniqueness() {
		$provider = new IdProvider( self::generator( [ '1', '2' ] ), self::isUniqueId( '1' ) );
		$id = $provider->getId();
		$this->assertSame( '2', $id );
	}

	public function testSkipsCheckForUniqueness() {
		$provider = new IdProvider( self::generator( [ '1', '2' ] ), self::isUniqueId( '1' ) );
		$id = $provider->getId( [ 'skipUniqueTest' => true ] );
		$this->assertSame( '1', $id );
	}

	private static function isUniqueId( $existingId ): \Closure {
		return function ( $id ) use ( $existingId ) { return $id !== $existingId; };
	}

	private static function noopIsUniqueId(): \Closure {
		return function () { return true; };
	}

	private static function generator( $ids ) {
		return new class ( $ids ) {

			private $ids;

			public function __construct( $ids ) { $this->ids = $ids; }

			public function generate() {
				return array_shift( $this->ids );
			}
		};
	}
}
