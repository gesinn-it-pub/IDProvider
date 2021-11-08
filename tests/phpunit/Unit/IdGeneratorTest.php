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

use MediaWiki\Extension\IdProvider\IdGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @group IDProvider
 * @covers \MediaWiki\Extension\IdProvider\IdGenerator
 */
class IdGeneratorTest extends TestCase {

	public function testDefaultCalculation() {
		$res = IdGenerator::getRandom();
		$this->assertEquals( 36, strlen( $res ), 'Generates UUIDs by default' );
	}

	public function testUuidCalculation() {
		$res = IdGenerator::getRandom( [ 'type' => 'uuid' ] );
		$this->assertEquals( 36, strlen( $res ), 'Generates UUIDs of the right length' );

		$res2 = IdGenerator::getRandom( [ 'type' => 'uuid', 'prefix' => 'PREFIX_' ] );
		$this->assertEquals( 43, strlen( $res2 ), 'Generates prefixed UUIDs of the right length' );

		$res3 = IdGenerator::getRandom( [
			'type' => 'uuid',
			'prefix' => false,
			'skipUniqueTest' => true,
		] );
		$this->assertEquals( 36, strlen( $res3 ), 'Generates UUIDs with $skipUniqueTest enabled' );
	}

	public function testFakeIdCalculation() {
		$res = IdGenerator::getRandom( [ 'type' => 'fake' ] );
		$this->assertGreaterThan( 5, strlen( $res ), 'Generates FakeIds of a minimal length' );

		$res2 = IdGenerator::getRandom( [ 'type' => 'fake', 'prefix' => 'PREFIX_' ] );
		$this->assertContains( 'PREFIX_', $res2, 'Returned random id includes the prefix' );
	}

	public function testRandomWrapper() {
		$res = IdGenerator::getRandom();
		$this->assertEquals( 36, strlen( $res ), 'Generates UUIDs of the right length' );

		$res2 = IdGenerator::getRandom( [
			'type' => 'fakeid',
			'prefix' => 'PREFIX_',
		] );
		$this->assertContains( 'PREFIX_', $res2, 'Returned fakeid includes the prefix' );
	}

	public function testIncrementWrapper() {
		$res = IdGenerator::getIncrement( [
			'prefix' => '___TEST___',
			'padding' => 8,
		] );

		$this->assertContains( '___TEST___', $res, 'Returned Increment includes the prefix' );
		$this->assertEquals( 18, strlen( $res ),
			'Generates Increments with namespace and padding of the right lengths' );

		// Test that no duplicates are generated
		$increments = [];
		for ( $i = 1; $i <= 16; $i += 1 ) {
			$increments[] = IdGenerator::getIncrement( [
				'prefix' => '___TEST___',
				'padding' => 8,
			] );
		}

		$this->assertTrue( count( $increments ) === count( array_unique( $increments ) ),
			'Increment values are unique' );
	}
}
