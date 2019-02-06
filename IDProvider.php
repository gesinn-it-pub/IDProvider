<?php
/**
 * IDProvider is a MediaWiki Extension that provides (unique) ID's in serveral ways:
 * * Through the API
 * * Through a PHP getter function
 * * Through wikitext string substitutions (@see http://mediawiki.org/wiki/Extension:IDProvider)
 *
 * @TODO: Reintegrate String Substitution functionality
 *
 * Delete a specific prefix counter: DELETE FROM `idprovider_increments` WHERE  `prefix`='REQ';
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @links http://mediawiki.org/wiki/Extension:IDProvider Documentation
 * @links https://github.com/gesinn-it/IDProvider/blob/master/README.md Documentation
 * @links https://github.com/gesinn-it/IDProvider Source code
 *
 * @author Simon Heimler, 2015
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */


if (function_exists('wfLoadExtension')) {

	wfLoadExtension('IDProvider');

	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['IDProvider'] = __DIR__ . '/i18n';
//	$wgExtensionMessagesFiles['IDProviderAlias'] = __DIR__ . '/IDProvider.alias.php';

	wfWarn(
		'Deprecated PHP entry point used for the IDProvider extension. Please use wfLoadExtension("IDProvider"); instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;


} else {

	// @deprecated legacy extension loading (MW <= 1.24)


	//////////////////////////////////////////
	// VARIABLES                            //
	//////////////////////////////////////////

	$dir         = dirname( __FILE__ );
	$dirbasename = basename( $dir );


	//////////////////////////////////////////
	// CONFIG                               //
	//////////////////////////////////////////

//	$wgIDProviderMinRandNumber      = 1000000000;
//	$wgIDProviderMaxRandNumber      = 9999999999;
//	$wgIDProviderRandStringLength 	= 12;


	//////////////////////////////////////////
	// CREDITS                              //
	//////////////////////////////////////////

	$wgExtensionCredits['other'][] = array(
		'path'           => __FILE__,
		'name'           => 'IDProvider',
		'author'         => array('Simon Heimler', 'Alexander Gesinn'),
		'version'        => '1.1.2',
		'url'            => 'https://www.mediawiki.org/wiki/Extension:IDProvider',
		'descriptionmsg' => 'idprovider-desc',
		'license-name'   => 'MIT'
	);


	//////////////////////////////////////////
	// LOAD FILES                           //
	//////////////////////////////////////////

	// Load Classes
	$wgAutoloadClasses['IDProviderHooks'] = $dir . '/IDProvider.hooks.php';
	$wgAutoloadClasses['IDProviderFunctions'] = $dir . '/IDProvider.functions.php';
	$wgAutoloadClasses['IDProviderIncrementApi'] = $dir . '/api/IDProviderIncrementApi.php';
	$wgAutoloadClasses['IDProviderRandomApi'] = $dir . '/api/IDProviderRandomApi.php';


	// Register hooks
	$wgHooks['UnitTestsList'][] = 'IDProviderHooks::onUnitTestsList';
	$wgHooks['ParserFirstCallInit'][] = 'IDProviderHooks::onParserFirstCallInit';
	$wgHooks['LoadExtensionSchemaUpdates'][] = 'IDProviderHooks::onLoadExtensionSchemaUpdates';

	// Register APIs
	$wgAPIModules['idprovider-increment'] = 'IDProviderIncrementApi';
	$wgAPIModules['idprovider-random'] = 'IDProviderRandomApi';

}