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
use Title;

class IdProviderFactory {

	/**
	 * @param array $params
	 * @return IdProvider
	 */
	public static function increment( array $params = [] ) {
		$prefix = self::paramGet( $params, 'prefix', '' );
		$padding = self::paramGet( $params, 'padding', 1 );

		$generator = new IncrementIdGenerator( self::dbExecute(), $prefix, $padding );

		return self::provider( $generator );
	}

	/**
	 * @param array $params
	 * @return IdProvider
	 */
	public static function random( array $params = [] ) {
		$type = self::paramGet( $params, 'type', 'uuid' );
		$generator = $type === 'fakeid' ? ( new FakeIdGenerator ) : ( new UuidGenerator );

		return self::provider( $generator );
	}

	/**
	 * @param FakeIdGenerator|IncrementIdGenerator|UuidGenerator $generator
	 * @return IdProvider
	 */
	private static function provider( $generator ) {
		return new IdProvider( $generator, self::isUniqueId() );
	}

	/**
	 * @return callable
	 */
	private static function dbExecute() {
		return static function ( $action ) {
			// Use the shared load balancer connection (respects the current DB domain,
			// including any test table prefix, unlike a freshly constructed LoadBalancer).
			// calculateIncrement() commits its change with a single atomic statement, so
			// it does not need its own isolated connection to avoid disturbing a
			// surrounding transaction.
			$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY );

			return $action( $dbw );
		};
	}

	/**
	 * Checks whether a WikiPage with the following id/title already exists
	 *
	 * @return \Closure
	 */
	private static function isUniqueId() {
		return static function ( $id ) {
			$title = Title::newFromText( $id );
			$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );

			return !$page->exists();
		};
	}

	/**
	 * @param array $params
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	private static function paramGet( array $params, string $key, $default = null ) {
		return isset( $params[$key] ) ? trim( $params[$key] ) : $default;
	}
}
