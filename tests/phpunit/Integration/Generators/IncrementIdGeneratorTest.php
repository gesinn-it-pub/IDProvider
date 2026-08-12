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

namespace Tests\Integration;

use MediaWiki\Extension\IdProvider\Generators\IncrementIdGenerator;
use MediaWikiIntegrationTestCase;

/**
 * @group IDProvider
 * @group Database
 * @group medium
 *
 * @covers \MediaWiki\Extension\IdProvider\Generators\IncrementIdGenerator
 */
class IncrementIdGeneratorTest extends MediaWikiIntegrationTestCase {

	/**
	 * @var string[]
	 */
	protected $tablesUsed = [ 'idprovider_increments' ];

	/**
	 * getDb() was not yet available on MediaWikiIntegrationTestCase in the extension's
	 * minimum supported MediaWiki version (1.39); $this->db is the portable equivalent.
	 *
	 * @return \Wikimedia\Rdbms\IDatabase
	 */
	private function getTestDb() {
		return method_exists( $this, 'getDb' ) ? $this->getDb() : $this->db;
	}

	private function newGenerator( string $prefix, int $padding = 0 ): IncrementIdGenerator {
		$dbExecute = function ( $action ) {
			return $action( $this->getTestDb() );
		};

		return new IncrementIdGenerator( $dbExecute, $prefix, $padding );
	}

	public function testGenerateInsertsFirstIncrementForNewPrefix() {
		$prefix = 'IDPTestNew' . rand( 0, 999999 );
		$generator = $this->newGenerator( $prefix );

		$id = $generator->generate();

		$this->assertSame( 1, $id );
	}

	public function testGenerateIncrementsExistingPrefixOnEachCall() {
		$prefix = 'IDPTestExisting' . rand( 0, 999999 );
		$generator = $this->newGenerator( $prefix );

		$this->assertSame( 1, $generator->generate() );
		$this->assertSame( 2, $generator->generate() );
		$this->assertSame( 3, $generator->generate() );
	}

	public function testGenerateTracksSeparatePrefixesIndependently() {
		$prefixA = 'IDPTestA' . rand( 0, 999999 );
		$prefixB = 'IDPTestB' . rand( 0, 999999 );

		$generatorA = $this->newGenerator( $prefixA );
		$generatorB = $this->newGenerator( $prefixB );

		$this->assertSame( 1, $generatorA->generate() );
		$this->assertSame( 1, $generatorB->generate() );
		$this->assertSame( 2, $generatorA->generate() );
	}

	/**
	 * PHPUnit is single-threaded and MediaWiki's default test setup uses
	 * connection-scoped temporary tables, so a genuinely concurrent second DB
	 * connection cannot see the same test table. This instead proves the
	 * property that matters: many rapid, interleaved calls for the same prefix
	 * never repeat or skip a value, which is what the "FOR UPDATE" row lock in
	 * calculateIncrement() guarantees under real concurrent access.
	 */
	public function testGenerateNeverRepeatsOrSkipsAValueAcrossManyCalls() {
		$prefix = 'IDPTestSeq' . rand( 0, 999999 );
		$generator = $this->newGenerator( $prefix );

		$ids = [];
		for ( $i = 0; $i < 25; $i++ ) {
			$ids[] = $generator->generate();
		}

		$this->assertSame( range( 1, 25 ), $ids );
	}

	/**
	 * Defense-in-depth check for the UNIQUE index added alongside the locking fix:
	 * even if application-level locking were bypassed, the database itself refuses
	 * a second row for a prefix that already exists.
	 */
	public function testDatabaseRejectsDuplicatePrefixRow() {
		$prefix = 'IDPTestUnique' . rand( 0, 999999 );
		$dbw = $this->getTestDb();
		$dbw->insert( 'idprovider_increments', [ 'prefix' => $prefix, 'increment' => 1 ], __METHOD__ );

		$this->expectException( \Wikimedia\Rdbms\DBQueryError::class );
		$dbw->insert( 'idprovider_increments', [ 'prefix' => $prefix, 'increment' => 1 ], __METHOD__ );
	}
}
