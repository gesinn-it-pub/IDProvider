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
 * Returns a UUID, using openssl random bytes
 *
 * @see http://stackoverflow.com/a/15875555
 *
 */
class UuidGenerator {

	public function generate(): string {
		/** @noinspection PhpComposerExtensionStubsInspection */
		$data = openssl_random_pseudo_bytes( 16 );

		// set version to 0100
		$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 );
		// set bits 6-7 to 10
		$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 );

		return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
	}
}
