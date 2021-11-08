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

namespace MediaWiki\Extension\IdProvider\Generators;

/**
 * Generates a Fake ID that is very likely to be truly unique (no guarantee however!)
 *
 * This is achieved through mixing a milli-timestamp (php uniqid();) with a random string
 *
 */
class FakeIdGenerator {

	public function generate(): string {
		// Generates a random string of length 1-2.
		$id = base_convert( rand( 0, 36 ^ 2 ), 10, 36 );

		// This will "compress" the uniqid (some sort of microtimestamp) to a more dense string
		$id .= base_convert( uniqid(), 10, 36 );

		return $id;
	}
}
