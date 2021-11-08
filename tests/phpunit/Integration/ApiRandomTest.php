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
use MWException;

/**
 * @group IDProvider
 * @group API
 * @group Database
 * @group medium
 *
 * @covers \MediaWiki\Extension\IdProvider\Api\Random
 */
class ApiRandomTest extends ApiTestCase {

	public function testRequest() {
		$data = $this->doApiRequest( [
			'action' => 'idprovider-random',
			'type' => 'uuid',
			'prefix' => '___TEST___',
			'format' => 'json',
		] );

		$this->assertArrayHasKey( 'id', $data[0], 'returns an ID' );

		$id = $data[0]['id'];

		$this->assertContains( '___TEST___', $id, 'Returned UUID includes the prefix' );
		$this->assertEquals( 46, strlen( $id ),
			'Generates UUIDs with namespace and padding of the right lengths' );
	}

	public function testInvalidRequest() {
		$this->expectException( MWException::class );
		$this->doApiRequest( [
			'action' => 'idprovider-random',
			'type' => 'notexisting',
			'format' => 'json',
		] );
	}
}
