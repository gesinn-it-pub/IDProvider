<?php

/**
 * @group IDProvider
 * @group API
 * @group Database
 * @group medium
 *
 * @covers IDProviderIncrementApi
 */
class IDProviderIncrementApiTest extends ApiTestCase {

	protected function setUp() {
		parent::setUp();
		$this->doLogin();
	}

	protected function tearDown() {
		parent::tearDown();
	}

	public function testIncrementApiRequest() {

		$data = $this->doApiRequest(array(
			'action' => 'idprovider-increment',
			'prefix' => '___TEST___',
			'padding' => 5,
			'format' => 'json',
		));

		$this->assertArrayHasKey( 'id', $data[0], 'returns an ID');

		$id = $data[0]['id'];

		$this->assertContains('___TEST___', $id, 'Returned Increment includes the prefix');
		$this->assertEquals(15, strlen($id), 'Generates Increments with namespace and padding of the right lengths');

	}

}
