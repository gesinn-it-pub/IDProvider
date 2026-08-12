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

use Exception;
use Wikimedia\Rdbms\DBUnexpectedError;

class IncrementIdGenerator {

	/**
	 * @var callable
	 */
	private $dbExecute;

	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * @var int
	 */
	private $padding;

	/**
	 * @param callable $dbExecute
	 * @param string $prefix
	 * @param int $padding
	 * @throws Exception
	 */
	public function __construct( $dbExecute, $prefix, $padding ) {
		$this->dbExecute = $dbExecute;
		$this->prefix = $prefix;
		$this->padding = (int)$padding;
	}

	/**
	 * Corresponds to the idprovider-increment API usage
	 *
	 * @return string|int
	 *
	 * @throws Exception
	 */
	public function generate() {
		$id = $this->calculateIncrement( $this->prefix );

		if ( $this->padding && $this->padding > 0 ) {
			$id = str_pad( (string)$id, $this->padding, '0', STR_PAD_LEFT );
		}

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
	private function calculateIncrement( string $prefix ) {
		$fname = __METHOD__;
		return ( $this->dbExecute )( static function ( $dbw ) use ( $prefix, $fname ) {
			$prefixIncrement = $dbw->select( 'idprovider_increments', 'increment', [
				'prefix' => $prefix,
			], $fname );

			if ( $prefixIncrement->numRows() <= 0 ) {
				$dbw->insert( 'idprovider_increments', [ 'prefix' => $prefix, 'increment' => 1 ] );
				$increment = 1;
			} else {
				$incrementRow = $prefixIncrement->fetchRow();
				$increment = $incrementRow['increment'] + 1;
				$dbw->update( 'idprovider_increments', [ 'increment = ' . $increment ],
					[ 'prefix' => $prefix ] );
			}

			return $increment;
		} );
	}
}
