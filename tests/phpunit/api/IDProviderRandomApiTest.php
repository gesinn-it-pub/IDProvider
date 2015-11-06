<?php

/**
 * @group IDProvider
 * @group API
 * @group Database
 * @group medium
 *
 * @covers IDProviderRandomApi
 */
class IDProviderRandomApiTest extends ApiTestCase {

	protected function setUp() {
		parent::setUp();
		$this->doLogin();
	}

	protected function tearDown() {
		parent::tearDown();
	}

	public function testIncrementApiRequest() {

		$data = $this->doApiRequest(array(
			'action' => 'idprovider-random',
			'type' => 'uuid',
			'prefix' => '___TEST___',
			'format' => 'json',
		));

		$this->assertArrayHasKey( 'id', $data[0], 'returns an ID');

		$id = $data[0]['id'];

		$this->assertContains('___TEST___', $id, 'Returned UUID includes the prefix');
		$this->assertEquals(46, strlen($id), 'Generates UUIDs with namespace and padding of the right lengths');

	}

	/**
	 * Invalid Request
	 *
	 * @expectedException     		UsageException
	 * @expectedExceptionMessage 	Unrecognized value for parameter 'type': notexisting
	 */
	public function testInvalidIncrementApiRequest() {

		$this->doApiRequest(array(
			'action' => 'idprovider-random',
			'type' => 'notexisting',
			'format' => 'json',
		));
	}
}
