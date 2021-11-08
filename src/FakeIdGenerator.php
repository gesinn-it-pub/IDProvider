<?php

namespace MediaWiki\Extension\IdProvider;

class FakeIdGenerator extends IdGenerator {

	private $prefix;

	/**
	 * @param $prefix
	 */
	public function __construct( $prefix ) { $this->prefix = $prefix; }

	/**
	 * Generates a Fake ID that is very likely to be truly unique (no guarantee however!)
	 *
	 * This is achieved through mixing a milli-timestamp (php uniqid();) with a random string
	 *
	 * @param string $prefix
	 * @param bool|false $skipUniqueTest
	 *
	 * @return string
	 */
	public function generate() {
		$prefix = trim( $this->prefix );

		// Generates a random string of length 1-2.
		$id = base_convert( rand( 0, 36 ^ 2 ), 10, 36 );

		// This will "compress" the uniqid (some sort of microtimestamp) to a more dense string
		$id .= base_convert( uniqid(), 10, 36 );

		$id = $prefix . $id;

		return $id;
	}
}
