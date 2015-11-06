<?php

/**
 * Hooks for IDProvider extension
 *
 * @file
 * @ingroup Extensions
 */
class IDProviderHooks {

	/**
	 * Adds this extension unit-tests
	 */
	public static function onUnitTestsList( &$files ) {
		$files = array_merge( $files, glob( __DIR__ . '/tests/phpunit/*Test.php' ) );
		return true;
	}


	/**
	 * Registers the database schema additions
	 */
	public static function onLoadExtensionSchemaUpdates( $updater ) {
		$updater->addExtensionTable('idprovider_increments', __DIR__ . '/sql/IDProviderIncrementTable.sql' );
		return true;
	}


	/**
	 * Register parser hooks
	 *
	 * See also http://www.mediawiki.org/wiki/Manual:Parser_functions
	 */
	public static function onParserFirstCallInit( &$parser ) {

		// Register parser functions
		$parser->setFunctionHook('idprovider-increment', 'IDProviderHooks::incrementFunctionHook');
		$parser->setFunctionHook('idprovider-random', 'IDProviderHooks::randomFunctionHook');

		return true;
	}


	/**
	 * Wrapper for the {{#idprovider-increment parser function
	 *
	 * @param $parser
	 * @param $main
	 *
	 * @return array
	 */
	public static function incrementFunctionHook($parser, $main) {

		$args = array_slice(func_get_args(), 2);
		$opts = self::extractOptions($args);

		if ($main) {
			$prefix = trim($main);
		} else {
			$prefix = (isset($opts['prefix']) ? trim($opts['prefix']) : null);
		}

		$padding = (isset($opts['padding']) ? $opts['padding'] : null);
		$start = (isset($opts['start']) ? $opts['start'] : null);
		$skipUniqueTest = (isset($opts['skipUniqueTest']) ? $opts['skipUniqueTest'] : null);

		$id = IDProviderFunctions::getIncrement($prefix, $padding, $start, $skipUniqueTest);

		return array($id, 'noparse' => true);
	}

	/**
	 * Wrapper for the {{#idprovider-random parser function
	 *
	 * @param $parser
	 * @param $main
	 *
	 * @return array
	 */
	public static function randomFunctionHook($parser, $main) {

		$args = array_slice(func_get_args(), 2);
		$opts = self::extractOptions($args);

		if ($main) {
			$type = trim($main);
		} else {
			$type = (isset($opts['type']) ? trim($opts['type']) : null);
		}

		$prefix = (isset($opts['prefix']) ? trim($opts['prefix']) : null);
		$skipUniqueTest = (isset($opts['skipUniqueTest']) ? $opts['skipUniqueTest'] : null);

		$id = IDProviderFunctions::getRandom($type, $prefix, $skipUniqueTest);

		return array($id, 'noparse' => true);
	}





	// OLD CODE @TODO: Reintegrate it

    /**
     * Hook: After a wiki page is saved, look for strings to substitute
     * If there are some, a new revision will be made that contains the substitutions
     */
    public static function onPageContentSaveComplete( $wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $status ) {

//        // Get all necessary variables
//        $oldText  = $content->getContentHandler()->serializeContent($content);
//        $title    = $wikiPage->getTitle();
//        $titleObj = Title::makeTitle(0, $title);
//        $page     = WikiPage::factory($titleObj);
//        $context  = new RequestContext();
//
//        // Do the actual substitution
//        $newText = substituteTimestamp($oldText);
//        $newText = substituteRandomNumber($newText);
//        $newText = substituteRandomString($newText);
//        $newText = substituteFakeID($newText);
//        $newText = substituteShortID($newText);
//
//
//        // If a substitution was made, save the edited page anew
//        // @see https://doc.wikimedia.org/mediawiki-core/master/php/html/classWikiPage.html
//        if ($oldText != $newText ) {
//            $content = $content->getContentHandler()->unserializeContent( $newText );
//            $page->doEditContent($content,
//                $context->getUser(),
//                "Extension:IDProvider - automatic string substitution"
//            );
//        };
//
//        return true;
    }


	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value
	 *
	 * @param array $options
	 * @return array $results
	 */
	private static function extractOptions(array $options) {

		$results = array();

		foreach ( $options as $option ) {
			$pair = explode( '=', $option, 2 );
			if ( count( $pair ) == 2 ) {
				$name = trim( $pair[0] );
				$value = trim( $pair[1] );
				$results[$name] = $value;
			}
		}

		return $results;
	}

}
