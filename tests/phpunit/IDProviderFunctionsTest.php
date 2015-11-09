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

	public function testUUIDCalculation() {
		$res = IDProviderFunctions::calculateUUID();
		$this->assertEquals(36, strlen($res), 'Generates UUIDs of the right length');

		$res2 = IDProviderFunctions::calculateUUID('PREFIX_');
		$this->assertEquals(43, strlen($res2), 'Generates prefixed UUIDs of the right length');

		$res3 = IDProviderFunctions::calculateUUID(false, true);
		$this->assertEquals(36, strlen($res3), 'Generates UUIDs with $skipUniqueTest enabled');
	}

	public function testFakeIdCalculation() {
		$res = IDProviderFunctions::calculateFakeId();
		$this->assertGreaterThan(5, strlen($res), 'Generates FakeIds of a minimal length');

		$res2 = IDProviderFunctions::calculateFakeId('PREFIX_');
		$this->assertContains('PREFIX_', $res2, 'Returned random id includes the prefix');
	}

	public function testRandomWrapper() {
		$res = IDProviderFunctions::getRandom();
		$this->assertEquals(36, strlen($res), 'Generates UUIDs of the right length');

	$res2 = IDProviderFunctions::getRandom(array(
		'type' => 'fakeid',
		'prefix' => 'PREFIX_',
	));
		$this->assertContains('PREFIX_', $res2, 'Returned fakeid includes the prefix');
	}

	public function testIncrementWrapper() {

		$res = IDProviderFunctions::getIncrement(array(
			'prefix' => '___TEST___',
			'padding' => 8,
		));

		$this->assertContains('___TEST___', $res, 'Returned Increment includes the prefix');
		$this->assertEquals(18, strlen($res), 'Generates Increments with namespace and padding of the right lengths');

		// Test that no duplicates are generated
		$increments = array();
		for ($i = 1; $i <= 16; $i++) {
			$increments[] = IDProviderFunctions::getIncrement(array(
				'prefix' => '___TEST___',
				'padding' => 8,
			));
		}

		$this->assertTrue(count($increments) === count(array_unique($increments)), 'Increment values are unique');

	}
}
