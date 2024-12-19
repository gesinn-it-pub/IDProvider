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

use MediaWiki\Extension\IdProvider\Generators\FakeIdGenerator;
use MediaWiki\Extension\IdProvider\Generators\IncrementIdGenerator;
use MediaWiki\Extension\IdProvider\Generators\UuidGenerator;
use MediaWiki\MediaWikiServices;
use MWException;
use Title;
use WikiPage;

class IdProviderFactory {

	public static function increment( array $params = [] ) {
		$prefix = self::paramGet( $params, 'prefix', '' );
		$padding = self::paramGet( $params, 'padding', 1 );

		$generator = new IncrementIdGenerator( self::dbExecute(), $prefix, $padding );

		return self::provider( $generator );
	}

	public static function random( array $params = [] ) {
		$type = self::paramGet( $params, 'type', 'uuid' );
		$generator = $type === 'fakeid' ? ( new FakeIdGenerator ) : ( new UuidGenerator );

		return self::provider( $generator );
	}

	private static function provider( $generator ) {
		return new IdProvider( $generator, self::getUniqueIdChecker() );
	}

	private static function dbExecute() {
		return function ( $action ) {
			// Use a separate DB connection here to be able to avoid concurrency issues and
			// not disturb possible surrounding transactions. (This seems to be natural as
			// the creation of IDs is completely independent of actual MediaWiki data.)
			$lb = MediaWikiServices::getInstance()->getDBLoadBalancerFactory()->newMainLB();
			$dbw = $lb->getConnection( DB_PRIMARY );
			$dbw->clearFlag( DBO_TRX );

			$result = $action( $dbw );

			$lb->disable();
			return $result;
		};
	}

	/**
	 * Returns a closure that checks if a string ID or title is unique by verifying whether
	 * the corresponding WikiPage already exists. The closure can be invoked later with a specific
	 * title or ID to check for its uniqueness.
	 *
	 * @return \Closure A closure that takes a string $text as input and returns a boolean indicating
	 *                  whether the WikiPage associated with the given title/ID exists or not.
	 */
	private static function getUniqueIdChecker() {
		return function ( $text ) {
			$title = Title::newFromText( $text );
			
			// If no Title object is found, the page does not exist
			if ( $title === null ) {
				return true;
			}

			// MW 1.36+
			if ( method_exists( MediaWikiServices::class, 'getWikiPageFactory' ) ) {
				$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
			} else {
				$page = WikiPage::factory( $title );
			}

			return !$page->exists();
		};
	}

	private static function paramGet( array $params, string $key, $default = null ) {
		return isset( $params[$key] ) ? trim( $params[$key] ) : $default;
	}
}
