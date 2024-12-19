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
use MediaWiki\Extension\IdProvider\Generators\IncrementIdGenerator;
use MediaWiki\Extension\IdProvider\Generators\UuidGenerator;
use MediaWiki\Extension\IdProvider\IdProviderFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group IDProvider
 * @covers \MediaWiki\Extension\IdProvider\IdProviderFactory
 * @covers \MediaWiki\Extension\IdProvider\IdProvider
 */
class IdProviderFactoryTest extends TestCase {

	public function testRandomGeneratesUuidsByDefault() {
		$provider = IdProviderFactory::random();
		$this->assertEquals( UuidGenerator::class, $provider->generatorClass() );

		$provider = IdProviderFactory::random( [ 'type' => 'x' ] );
		$this->assertEquals( UuidGenerator::class, $provider->generatorClass() );

		$provider = IdProviderFactory::random( [ 'type' => '' ] );
		$this->assertEquals( UuidGenerator::class, $provider->generatorClass() );
	}

	public function testRandomCreatesUuidGenerator() {
		$provider = IdProviderFactory::random( [ 'type' => 'uuid' ] );
		$this->assertEquals( UuidGenerator::class, $provider->generatorClass() );
	}

	public function testRandomCreatesFakeIdGenerator() {
		$provider = IdProviderFactory::random( [ 'type' => 'fakeid' ] );
		$this->assertEquals( FakeIdGenerator::class, $provider->generatorClass() );
	}

	public function testIncrementCreatesIncrementIdGenerator() {
		$provider = IdProviderFactory::increment();
		$this->assertEquals( IncrementIdGenerator::class, $provider->generatorClass() );
	}

	public function testGetUniqueIdChecker() {	
		// Call the private method using Reflection
		$reflection = new \ReflectionMethod( IdProviderFactory::class, 'getUniqueIdChecker' );
		$reflection->setAccessible( true );
		$isUniqueClosure = $reflection->invoke( null );
	
		$this->assertTrue( $isUniqueClosure( 'NewPage' ) );
	}

	public function testParamGet() {
		// Call the private method using Reflection
		$reflection = new \ReflectionMethod( IdProviderFactory::class, 'paramGet' );
		$reflection->setAccessible( true );
	
		$params = [ 'key' => 'value' ];
	
		$this->assertEquals( 'value', $reflection->invoke( null, $params, 'key', 'default' ) );
		$this->assertEquals( 'default', $reflection->invoke( null, $params, 'missing_key', 'default' ) );
	}
}
