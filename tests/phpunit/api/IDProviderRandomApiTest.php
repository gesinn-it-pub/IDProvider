<?php

/**
 * @group IDProvider
 * @group API
 * @group Database
 * @group medium
 *
 * @covers IDProviderRandomApi
 */

use MWException;

class IDProviderRandomApiTest extends ApiTestCase {

	protected function setUp() {
		parent::setUp();
		$this->doLogin();
	}

	protected function tearDown() {
		parent::tearDown();
	}

	public function testIncrementApiRequest() {
		$data = $this->doApiRequest( [
			'action' => 'idprovider-random',
			'type'   => 'uuid',
			'prefix' => '___TEST___',
			'format' => 'json',
		] );

		$this->assertArrayHasKey( 'id', $data[0], 'returns an ID' );

		$id = $data[0]['id'];

		$this->assertContains( '___TEST___', $id, 'Returned UUID includes the prefix' );
		$this->assertEquals( 46, strlen( $id ), 'Generates UUIDs with namespace and padding of the right lengths' );

	}

	/**
	 * Invalid Request
	 */
	public function testInvalidIncrementApiRequest() {
		$this->expectException( MWException::class );
		$this->doApiRequest( [
			'action' => 'idprovider-random',
			'type'   => 'notexisting',
			'format' => 'json',
		] );
	}
}
