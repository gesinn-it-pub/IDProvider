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

use MediaWiki\Extension\IdProvider\Generators\UuidGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @group IDProvider
 * @covers \MediaWiki\Extension\IdProvider\Generators\UuidGenerator
 */
class UuidGeneratorTest extends TestCase {

	public function testGeneratesUuidsOfTheRightLength() {
		$id = ( new UuidGenerator )->generate();
		$this->assertEquals( 36, strlen( $id ) );
	}
}
