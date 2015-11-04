<?php

/**
 * @group IDProvider
 * @group API
 * @group Database
 * @group medium
 *
 * @covers IDProviderApi
 */
class IDProviderApiTest extends ApiTestCase {

	protected function setUp() {
		parent::setUp();
		$this->doLogin();
	}

	public function testParseNonexistentPage() {

		$data = $this->doApiRequest(array(
			'action' => 'query',
			'list' => 'idprovider',
			'type' => 'increment',
			'prefix' => '___TEST___',
			'padding' => 5,
			'format' => 'json',
		));

		$this->assertArrayHasKey( 'idprovider', $data[0], 'returns api namespace');
		$this->assertArrayHasKey( 'id', $data[0]['idprovider'], 'returns an ID');

		$id = $data[0]['idprovider']['id'];

		$this->assertContains('___TEST___', $id, 'Returned Increment includes the prefix');
		$this->assertEquals(15, strlen($id), 'Generates Increments with namespace and padding of the right lengths');

	}

}
