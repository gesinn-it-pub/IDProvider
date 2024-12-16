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
class IncrementTest extends TestCase {

	public function testGetExamples() {
		// Handle module name based on MediaWiki version
		$moduleName = version_compare( MW_VERSION, '1.40', '>=' ) ? 'idprovider-increment' : null;

		$increment = new Increment( new ApiMain(), $moduleName );
		$messages = $increment->getExamplesMessages();
		$this->assertCount( 2, $messages );
	}
}
