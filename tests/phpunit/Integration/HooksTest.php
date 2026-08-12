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

use MediaWiki\Extension\IdProvider\Hooks;
use MediaWikiIntegrationTestCase;
use ParserOptions;
use Title;

/**
 * @group IDProvider
 * @group Database
 * @group medium
 *
 * @covers \MediaWiki\Extension\IdProvider\Hooks
 */
class HooksTest extends MediaWikiIntegrationTestCase {

	private function parse( string $wikitext ): string {
		$parserOutput = $this->getServiceContainer()->getParser()->parse(
			$wikitext,
			Title::makeTitle( NS_MAIN, 'IDProviderHooksTest' ),
			ParserOptions::newFromAnon()
		);

		return version_compare( MW_VERSION, '1.42', '>=' )
			? $parserOutput->getRawText()
			: $parserOutput->getText();
	}

	/**
	 * assertMatchesRegularExpression() replaced the deprecated assertRegExp() in PHPUnit 9.1;
	 * assertRegExp() is what the extension's minimum supported MediaWiki version (1.39, which
	 * bundles PHPUnit 8.5) provides.
	 */
	private function assertMatchesRegex( string $pattern, string $string ): void {
		if ( method_exists( $this, 'assertMatchesRegularExpression' ) ) {
			$this->assertMatchesRegularExpression( $pattern, $string );
		} else {
			$this->assertRegExp( $pattern, $string );
		}
	}

	public function testIncrementFunctionHookRegistersParserFunction() {
		$html = $this->parse( '{{#idprovider-increment:prefix=IDPTestHooksInc|padding=4}}' );

		$this->assertMatchesRegex( '/IDPTestHooksInc\d{4}/', $html );
	}

	public function testIncrementFunctionHookUsesShortFormPrefix() {
		$html = $this->parse( '{{#idprovider-increment:IDPTestHooksShort|padding=3}}' );

		$this->assertMatchesRegex( '/IDPTestHooksShort\d{3}/', $html );
	}

	public function testRandomFunctionHookRegistersParserFunction() {
		$html = $this->parse( '{{#idprovider-random:type=uuid|prefix=IDPTestHooksRand}}' );

		$this->assertStringContainsString( 'IDPTestHooksRand', $html );
	}

	public function testRandomFunctionHookUsesShortFormType() {
		$html = $this->parse( '{{#idprovider-random:fakeid}}' );

		$this->assertNotSame( '', trim( $html ) );
	}

	public function testOnUnitTestsListAddsExtensionTestFiles() {
		$files = [];
		$result = Hooks::onUnitTestsList( $files );

		$this->assertTrue( $result );
		$this->assertNotEmpty( $files );
	}

	public function testOnLoadExtensionSchemaUpdatesRegistersTable() {
		$updater = $this->getMockBuilder( \DatabaseUpdater::class )
			->disableOriginalConstructor()
			->getMock();
		$updater->expects( $this->once() )
			->method( 'addExtensionTable' )
			->with( 'idprovider_increments', $this->stringContains( 'CreateIncrementTable.sql' ) );

		$result = Hooks::onLoadExtensionSchemaUpdates( $updater );

		$this->assertTrue( $result );
	}

	/**
	 * mergeDuplicateIncrementPrefixes() only has real duplicate rows to merge on an
	 * installation upgrading from before the UNIQUE index migration, which the current
	 * idprovider_increments schema no longer allows. A throwaway table without that
	 * constraint reproduces the pre-migration situation instead.
	 */
	public function testMergeDuplicateIncrementPrefixesKeepsHighestIncrement() {
		$dbw = method_exists( $this, 'getDb' ) ? $this->getDb() : $this->db;
		$prefix = 'IDPTestMerge' . rand( 0, 999999 );
		$tableName = 'idprovider_increments_premigration_test';
		$qualifiedTableName = $dbw->tableName( $tableName );

		$dbw->query( "DROP TEMPORARY TABLE IF EXISTS $qualifiedTableName", __METHOD__ );
		$dbw->query(
			"CREATE TEMPORARY TABLE $qualifiedTableName ( " .
				'pid int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT, ' .
				'prefix varbinary(255) NOT NULL DEFAULT \'\', ' .
				'increment int unsigned NOT NULL default 0 )',
			__METHOD__
		);
		$dbw->insert( $tableName, [ 'prefix' => $prefix, 'increment' => 3 ], __METHOD__ );
		$dbw->insert( $tableName, [ 'prefix' => $prefix, 'increment' => 7 ], __METHOD__ );

		$updater = $this->getMockBuilder( \DatabaseUpdater::class )
			->disableOriginalConstructor()
			->getMock();
		$updater->method( 'getDB' )->willReturn( $dbw );

		Hooks::mergeDuplicateIncrementPrefixes( $updater, $tableName );

		$rows = $dbw->select( $tableName, [ 'increment' ], [ 'prefix' => $prefix ], __METHOD__ );
		$values = array_map( static fn ( $row ) => (int)$row->increment, iterator_to_array( $rows ) );

		$this->assertSame( [ 7 ], $values );

		$dbw->query( "DROP TEMPORARY TABLE $qualifiedTableName", __METHOD__ );
	}
}
