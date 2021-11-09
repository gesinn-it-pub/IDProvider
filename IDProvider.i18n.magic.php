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

/**
 * Internationalisation file for magic words
 *
 * @file
 * @ingroup Extensions
 */

$magicWords = [];

/** English (English) */
$magicWords['en'] = [
	'idprovider-increment' => [ 0, 'idprovider-increment' ],
	'idprovider-random' => [ 0, 'idprovider-random' ],
];

/** Persian (فارسی) */
$magicWords['fa'] = [
	'idprovider-increment' => [ 0, 'شناسه ساز افزایشی' ],
	'idprovider-random' => [ 0, 'شناسه ساز تصادقی' ],
];
