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
		return new IdProvider( $generator, self::isUniqueId() );
	}

	private static function dbExecute() {
		return function ( $action ) {
			$factory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
			$mainLB = $factory->getMainLB();
			$dbw = $mainLB->getConnection( DB_PRIMARY );
			$factory->beginPrimaryChanges( __METHOD__ );
			$result = $action( $dbw );
			$factory->commitPrimaryChanges( __METHOD__ );

			return $result;
		};
	}

	/**
	 * Checks whether a WikiPage with the following id/title already exists
	 *
	 * @return \Closure
	 */
	private static function isUniqueId() {
		return function ( $id ) {
			$title = Title::newFromText( $id );
			$page = WikiPage::factory( $title );

			return !$page->exists();
		};
	}

	private static function paramGet( array $params, string $key, $default = null ) {
		return isset( $params[$key] ) ? trim( $params[$key] ) : $default;
	}
}
