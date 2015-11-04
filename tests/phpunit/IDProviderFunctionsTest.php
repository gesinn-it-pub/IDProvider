<?php

/**
 * @group IDProvider
 * @covers IDProviderFunctions
 */
class IDProviderFunctionsTest extends MediaWikiTestCase {

	public function testUUID() {
		$this->assertEquals(36, strlen(IDProviderFunctions::getUUID()), 'Generates UUIDs of the right length');
	}

	public function testFakeId() {
		$this->assertGreaterThan(5, strlen(IDProviderFunctions::getFakeId()), 'Generates FakeIds of a minimal length');
	}

	public function testIncrement() {

		$this->assertContains('___TEST___', IDProviderFunctions::getIncrement('___TEST___', 8), 'Returned Increment includes the prefix');
		$this->assertEquals(18, strlen(IDProviderFunctions::getIncrement('___TEST___', 8)), 'Generates Increments with namespace and padding of the right lengths');

		// Test that no duplicates are generated
		$increments = array();
		for ($i = 1; $i <= 16; $i++) {
			$increments[] = IDProviderFunctions::getIncrement('___TEST___', 8);
		}

		$this->assertTrue(count($increments) === count(array_unique($increments)), 'Increment values are unique');

	}

}
