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

use ApiMain;
use MediaWiki\Extension\IdProvider\Api\Increment;
use PHPUnit\Framework\TestCase;

/**
 * @group IDProvider
 * @covers \MediaWiki\Extension\IdProvider\Api\Increment
 */
class ApiIncrementTest extends TestCase {

	public function testGetExamples() {
		$increment = new Increment( new ApiMain(), null );
		$messages = $increment->getExamplesMessages();
		$this->assertCount( 2, $messages );
	}
}
