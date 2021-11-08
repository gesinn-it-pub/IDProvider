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

namespace MediaWiki\Extension\IdProvider;

/**
 * The actual IDProvider Functions
 *
 * They can be used in a programmatic way through this class
 * Use the static getId function as the main entry point
 *
 * @file
 * @ingroup Extensions
 */
class IdProvider {

	private $generator;

	/**
	 * @var callable Function to decide if string ID already exists, i.e. is already used as a
	 * WikiPage
	 */
	private $isUniqueId;

	/**
	 * @param $generator
	 * @param null $isUniqueId
	 */
	public function __construct( $generator, $isUniqueId = null ) {
		$this->generator = $generator;
		$this->isUniqueId = $isUniqueId;
	}

	public function getId( array $params = [] ): string {
		$prefix = self::paramGet( $params, 'prefix', '' );
		$skipUniqueTest = self::paramGet( $params, 'skipUniqueTest', false );

		$prefix = trim( $prefix );
		$id = $prefix . $this->generator->generate();
		if ( !$skipUniqueTest ) {
			while ( !( $this->isUniqueId )( $id ) ) {
				$id = $prefix . $this->generator->generate();
			}
		}

		return $id;
	}

	public function generatorClass(): string {
		return get_class( $this->generator );
	}

	private static function paramGet( array $params, string $key, $default = null ) {
		return isset( $params[$key] ) ? trim( $params[$key] ) : $default;
	}
}
