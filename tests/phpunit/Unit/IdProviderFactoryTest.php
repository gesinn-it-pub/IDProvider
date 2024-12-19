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
use Title;

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
	
		// check when id/title is from the page which exists and return false when page already exists
        $this->assertFalse( $isUniqueClosure( 'Main Page' ) );

		// check when id/title is from the page which not exists and return true if page do not exists
        $this->assertTrue( $isUniqueClosure( 'Non existing page' ) );
	}

	public function testGetUniqueIdCheckerWhenTitleIsMalformed() {	
		// Call the private method using Reflection
		$reflection = new \ReflectionMethod( IdProviderFactory::class, 'getUniqueIdChecker' );
		$reflection->setAccessible( true );
		$isUniqueClosure = $reflection->invoke( null );

		$title = Title::newFromText( 'nonexisting title' );

		// Asserts that an InvalidArgumentException is thrown and that message is good
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( '$text must be a string' );

		// add object instead of string, it should thrown the InvalidArgumentException
		$isUniqueClosure( $title );
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
