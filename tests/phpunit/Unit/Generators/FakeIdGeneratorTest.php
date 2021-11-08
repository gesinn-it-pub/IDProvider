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

	public function testGeneratesFakeIdsOfAMinimalLength() {
		$id = ( new FakeIdGenerator )->generate();
		$this->assertGreaterThan( 5, strlen( $id ) );
	}
}
