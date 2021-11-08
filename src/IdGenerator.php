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

use MWException;
use Title;
use WikiPage;

/**
 * The actual IDProvider Functions
 *
 * They can be used in a programmatic way through this class
 * Use the static getId function as the main entry point
 *
 * @file
 * @ingroup Extensions
 */
class IdGenerator {

	public static function getIncrement( $params = [] ) {
		$prefix = self::paramGet( $params, 'prefix', '' );
		$padding = self::paramGet( $params, 'padding', 1 );
		$generator = new IncrementIdGenerator( $prefix, $padding );

		$skipUniqueTest = self::paramGet( $params, 'skipUniqueTest', false );
		return self::generateUsing($generator, $skipUniqueTest);
	}

	/**
	 * Corresponds to the idprovider-random API usage
	 *
	 * @param array $params Associative array. See the API / Parser function for usage
	 * @return string
	 */
	public static function getRandom( $params = [] ) {
		// Defaults and escaping
		$type = self::paramGet( $params, 'type', 'uuid' );
		$prefix = self::paramGet( $params, 'prefix', '' );
		$skipUniqueTest = self::paramGet( $params, 'skipUniqueTest', false );

		return $type === 'fakeid' ? self::calculateFakeId( $prefix, $skipUniqueTest )
			: self::calculateUUID( $prefix, $skipUniqueTest );
	}

	private static function calculateUUID( $prefix = '', $skipUniqueTest = false ) {
		$generator = new UuidGenerator( $prefix );
		return self::generateUsing($generator, $skipUniqueTest);
	}

	private static function calculateFakeId( $prefix = '', $skipUniqueTest = false ) {
		$generator = new FakeIdGenerator($prefix);
		return self::generateUsing($generator, $skipUniqueTest);
	}

	/**
	 * Checks whether a WikiPage with the following id/title already exists
	 *
	 * @param string $id
	 * @return bool
	 *
	 * @throws MWException
	 */
	protected static function isUniqueId( $id ) {
		$title = Title::newFromText( $id );
		$page = WikiPage::factory( $title );

		if ( $page->exists() ) {
			return false;
		} else {
			return true;
		}
	}

	//////////////////////////////////////////
	// HELPER FUNCTIONS                     //
	//////////////////////////////////////////

	/**
	 * Helper function, that safely checks whether an array key exists
	 * and returns the trimmed value. If it doesn't exist, returns $default or null
	 *
	 * @param array $params
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	protected static function paramGet( $params, $key, $default = null ) {
		if ( isset( $params[$key] ) ) {
			return trim( $params[$key] );
		} else {
			return $default;
		}
	}

	/**
	 * @param $generator
	 * @param $skipUniqueTest
	 * @return mixed
	 */
	public static function generateUsing( $generator, $skipUniqueTest ) {
		$id = $generator->generate();
		if ( !$skipUniqueTest ) {
			while ( !self::isUniqueId( $id ) ) {
				$id = $generator->generate();
			}
		}
		return $id;
	}
}
