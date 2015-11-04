<?php

/**
 * @group IDProvider
 * @covers IDProviderFunctions
 */
class IDProviderFunctionsTest extends MediaWikiTestCase {

	protected function setUp() {
		parent::setUp();
	}

	protected function tearDown() {
		parent::tearDown();
	}

	public function testUUID() {
		$res = IDProviderFunctions::getUUID();
		$this->assertEquals(36, strlen($res), 'Generates UUIDs of the right length');
	}

	public function testFakeId() {
		$res = IDProviderFunctions::getFakeId();
		$this->assertGreaterThan(5, strlen($res), 'Generates FakeIds of a minimal length');
	}

	public function testIncrement() {

		$res = IDProviderFunctions::getIncrement('___TEST___', 8);

		$this->assertContains('___TEST___', $res, 'Returned Increment includes the prefix');
		$this->assertEquals(18, strlen($res), 'Generates Increments with namespace and padding of the right lengths');

		// Test that no duplicates are generated
		$increments = array();
		for ($i = 1; $i <= 16; $i++) {
			$increments[] = IDProviderFunctions::getIncrement('___TEST___', 8);
		}

		$this->assertTrue(count($increments) === count(array_unique($increments)), 'Increment values are unique');

	}

	public function testInvalidRequest() {

		$res = IDProviderFunctions::getIncrement('___TEST___', 8);

		$this->assertContains('___TEST___', $res, 'Returned Increment includes the prefix');
		$this->assertEquals(18, strlen($res), 'Generates Increments with namespace and padding of the right lengths');

		// Test that no duplicates are generated
		$increments = array();
		for ($i = 1; $i <= 16; $i++) {
			$increments[] = IDProviderFunctions::getIncrement('___TEST___', 8);
		}

		$this->assertTrue(count($increments) === count(array_unique($increments)), 'Increment values are unique');

	}

}
