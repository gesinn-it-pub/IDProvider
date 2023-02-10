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
 * @covers \MediaWiki\Extension\IdProvider\IdProviderFactory
 * @covers \MediaWiki\Extension\IdProvider\Generators\IncrementIdGenerator
 */
class IncrementTest extends ApiTestCase {

	public function testIncrement() {
		$this->markTestSkipped(
			'Need to fix the "no such table: unittest_idprovider_increments" which occurs in test setting only'
		);

		$getId = function ( $prefix ) {
			return $this->doApiRequest( [
				'action' => 'idprovider-increment',
				'prefix' => $prefix,
				'padding' => 5,
				'format' => 'json',
			] )[0]['id'];
		};

		$prefix = 'TEST' . rand( 0, 9999999 ) . '@';

		$id = $getId( $prefix );
		$this->assertSame( $prefix . '00001', $id );

		$id = $getId( $prefix );
		$this->assertSame( $prefix . '00002', $id );

		$id = $getId( $prefix );
		$this->assertSame( $prefix . '00003', $id );
	}
}
