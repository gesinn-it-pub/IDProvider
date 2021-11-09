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
use MediaWiki\Extension\IdProvider\Api\Random;
use PHPUnit\Framework\TestCase;

/**
 * @group IDProvider
 * @covers \MediaWiki\Extension\IdProvider\Api\Random
 */
class RandomTest extends TestCase {

	public function testGetExamples() {
		$random = new Random( new ApiMain(), null );
		$messages = $random->getExamplesMessages();
		$this->assertCount( 2, $messages );
	}
}
