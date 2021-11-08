<?php

namespace MediaWiki\Extension\IdProvider;

use Exception;
use Wikimedia\Rdbms\DBUnexpectedError;

class IncrementIdGenerator extends IdGenerator {

	private $prefix;
	private $padding;

	/**
	 * @param $prefix
	 * @param $padding
	 */
	public function __construct( $prefix, $padding ) {
		$this->prefix = $prefix;
		$this->padding = $padding;
	}

	/**
	 * Corresponds to the idprovider-increment API usage
	 *
	 * @param array $params Associative array. See the API / Parser function for usage
	 * @return string|int
	 *
	 * @throws Exception
	 */
	public function generate() {
		$id = self::calculateIncrement( $this->prefix );

		if ( $this->padding && $this->padding > 0 ) {
			$id = str_pad( $id, $this->padding, '0', STR_PAD_LEFT );
		}

		$id = $this->prefix . $id;

		return $id;
	}

	/**
	 * Returns the current increment +1, increments the increment of the used prefix
	 *
	 * @param string $prefix
	 * @return int
	 *
	 * @throws DBUnexpectedError
	 * @throws Exception
	 *
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
				'prefix'    => $prefix,
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
}
