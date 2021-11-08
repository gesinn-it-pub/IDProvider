<?php

namespace MediaWiki\Extension\IdProvider;

class UuidGenerator extends IdGenerator {

	private $prefix;

	/**
	 * @param $prefix
	 */
	public function __construct( $prefix ) { $this->prefix = $prefix; }

	/**
	 * Returns a UUID, using openssl random bytes
	 *
	 * @see http://stackoverflow.com/a/15875555
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function generate() {
		$prefix = trim( $this->prefix );

		/** @noinspection PhpComposerExtensionStubsInspection */
		$data = openssl_random_pseudo_bytes( 16 );

		// set version to 0100
		$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 );
		// set bits 6-7 to 10
		$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 );

		$id = $prefix . vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );

		return $id;
	}
}
