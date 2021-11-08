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

namespace Tests\Integration;

use ApiTestCase;

/**
 * @group IDProvider
 * @group API
 * @group Database
 * @group medium
 *
 * @covers \MediaWiki\Extension\IdProvider\Api\Increment
 */
class ApiIncrementTest extends ApiTestCase {

	public function testRequest() {
		$data = $this->doApiRequest( [
			'action' => 'idprovider-increment',
			'prefix' => '___TEST___',
			'padding' => 5,
			'format' => 'json',
		] );

		$this->assertArrayHasKey( 'id', $data[0], 'returns an ID' );

		$id = $data[0]['id'];

		$this->assertContains( '___TEST___', $id, 'Returned Increment includes the prefix' );
		$this->assertEquals( 15, strlen( $id ),
			'Generates Increments with namespace and padding of the right lengths' );
	}
}
