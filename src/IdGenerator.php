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

use Exception;
use Title;
use Wikimedia\Rdbms\DBUnexpectedError;
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

	/**
	 * Corresponds to the idprovider-increment API usage
	 *
	 * @param array $params Associative array. See the API / Parser function for usage
	 * @return string|int
	 *
	 * @throws Exception
	 */
	public static function getIncrement( $params = [] ) {
		// Defaults and escaping
		$prefix = self::paramGet( $params, 'prefix', '___MAIN___' );
		$padding = self::paramGet( $params, 'padding', 1 );
		$skipUniqueTest = self::paramGet( $params, 'skipUniqueTest', false );

		// TODO: Not implemented yet
		$start = self::paramGet( $params, 'start', 1 );

		$id = self::calculateIncrement( $prefix );

		if ( $padding && $padding > 0 ) {
			$id = str_pad( $id, $padding, '0', STR_PAD_LEFT );
		}

		if ( $prefix !== '___MAIN___' ) {
			$id = $prefix . $id;
		}

		if ( !$skipUniqueTest ) {
			if ( !self::isUniqueId( $id ) ) {
				return self::getIncrement( $params );
			}
		}

		return $id;
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

	/**
	 * Returns the current increment +1, increments the increment of the used prefix
	 *
	 * @todo Support the start parameter
	 *
	 * @param string $prefix
	 * @return int
	 *
	 * @throws DBUnexpectedError
	 * @throws Exception
	 */
	private static function calculateIncrement( $prefix ) {
		$increment = null;

		// Get DB with read access
		// > MW 1.27
		if ( class_exists( '\MediaWiki\MediaWikiServices' ) &&
			method_exists( '\MediaWiki\MediaWikiServices', 'getDBLoadBalancerFactory' ) ) {
			$factory = \MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
			$mainLB = $factory->getMainLB();
			$dbw = $mainLB->getConnectionRef( DB_MASTER );
			$factory->beginMasterChanges( __METHOD__ );
		} else {
			$dbw = wfGetDB( DB_MASTER );
			$dbw->begin();
		}

		// Get the row according to the $prefix
		$prefixIncrement = $dbw->select( 'idprovider_increments', 'increment', [
			'prefix' => $prefix,
		], __METHOD__ );

		if ( $prefixIncrement->numRows() <= 0 ) {

			// If the row does not exist yet, create it first
			$dbw->insert( 'idprovider_increments', [
				'prefix' => $prefix,
				'increment' => 1,
			] );
			// > MW 1.27
			if ( class_exists( '\MediaWiki\MediaWikiServices' ) &&
				method_exists( '\MediaWiki\MediaWikiServices', 'getDBLoadBalancerFactory' ) ) {
				$factory->commitMasterChanges( __METHOD__ );
			} else {
				$dbw->commit();
			}
			$increment = 1;

		} else {

			// Read the increment
			$incrementRow = $prefixIncrement->fetchRow();
			$increment = $incrementRow['increment'] + 1;

			// Update the increment (+1)
			$dbw->update( 'idprovider_increments', [
				'increment = increment + 1',
			], [
				'prefix' => $prefix,
			] );
			// > MW 1.27
			if ( class_exists( '\MediaWiki\MediaWikiServices' ) &&
				method_exists( '\MediaWiki\MediaWikiServices', 'getDBLoadBalancerFactory' ) ) {
				$factory->commitMasterChanges( __METHOD__ );
			} else {
				$dbw->commit();
			}
		}

		if ( !$increment ) {
			throw new Exception( 'Could not calculate the increment!' );
		}

		return $increment;
	}

	/**
	 * Returns a UUID, using openssl random bytes
	 *
	 * @see http://stackoverflow.com/a/15875555
	 *
	 * @param string $prefix
	 * @param bool|false $skipUniqueTest
	 *
	 * @return string
	 */
	private static function calculateUUID( $prefix = '', $skipUniqueTest = false ) {
		$prefix = trim( $prefix );

		$data = openssl_random_pseudo_bytes( 16 );

		// set version to 0100
		$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 );
		// set bits 6-7 to 10
		$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 );

		$id = $prefix . vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );

		if ( !$skipUniqueTest ) {
			if ( !self::isUniqueId( $id ) ) {
				return self::calculateUUID( $prefix, $skipUniqueTest );
			}
		}

		return $id;
	}

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
	private static function calculateFakeId( $prefix = '', $skipUniqueTest = false ) {
		$prefix = trim( $prefix );

		// Generates a random string of length 1-2.
		$id = base_convert( rand( 0, 36 ^ 2 ), 10, 36 );

		// This will "compress" the uniqid (some sort of microtimestamp) to a more dense string
		$id .= base_convert( uniqid(), 10, 36 );

		$id = $prefix . $id;

		if ( !$skipUniqueTest ) {
			if ( !self::isUniqueId( $id ) ) {
				return self::calculateFakeId( $prefix, $skipUniqueTest );
			}
		}

		return $id;
	}

	/**
	 * Checks whether a WikiPage with the following id/title already exists
	 *
	 * @param string $id
	 * @return bool
	 *
	 * @throws MWException
	 */
	private static function isUniqueId( $id ) {
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
	private static function paramGet( $params, $key, $default = null ) {
		if ( isset( $params[$key] ) ) {
			return trim( $params[$key] );
		} else {
			return $default;
		}
	}
}
