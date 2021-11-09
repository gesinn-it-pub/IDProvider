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

use MediaWiki\Extension\IdProvider\Generators\IncrementIdGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @group IDProvider
 * @covers \MediaWiki\Extension\IdProvider\Generators\UuidGenerator
 */
class IncrementIdGeneratorTest extends TestCase {

	/**
	 * @throws \Exception
	 */
	public function testNoPaddingIsApplied() {
		$generator = new IncrementIdGenerator( function () { return 1; }, rand( 0, 999999 ), 0 );
		$id = $generator->generate();
		$this->assertSame( $id, 1 );
	}

	/**
	 * @throws \Exception
	 */
	public function testPaddingIsApplied() {
		foreach ( [ 3, 4, 8 ] as $padding ) {
			$generator =
				new IncrementIdGenerator( function () { return 1; }, rand( 0, 999999 ), $padding );
			$id = $generator->generate();
			$this->assertEquals( $padding, strlen( $id ) );
		}
	}

//	public function testPaddingIsCheckedForBeingAPositiveInteger() {
//		foreach ( [ 'a' ] as $padding ) {
//			$exception = null;
//			try {
//				$generator = new IncrementIdGenerator( function () { return 1; }, '', $padding );
//				$generator->generate();
//			}
//			catch ( \Exception $e ) {
//				$exception = $e;
//			}
//			$this->assertNotNull( $exception );
//		}
//	}
}
