<?php

/**
 * Hooks for IDProvider extension
 *
 * @file
 * @ingroup Extensions
 */
class IDProviderHooks {

	public static function onUnitTestsList( &$files ) {
		$files = array_merge( $files, glob( __DIR__ . '/tests/phpunit/*Test.php' ) );
		return true;
	}

    /**
     * Hook: After a wiki page is saved, look for strings to substitute
     * If there are some, a new revision will be made that contains the substitutions
     */
    public static function onPageContentSaveComplete( $wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $status ) {

        // Get all necessary variables
        $oldText  = $content->getContentHandler()->serializeContent($content);
        $title    = $wikiPage->getTitle();
        $titleObj = Title::makeTitle(0, $title);
        $page     = WikiPage::factory($titleObj);
        $context  = new RequestContext();

        // Do the actual substitution
        $newText = substituteTimestamp($oldText);
        $newText = substituteRandomNumber($newText);
        $newText = substituteRandomString($newText);
        $newText = substituteFakeID($newText);
        $newText = substituteShortID($newText);


        // If a substitution was made, save the edited page anew
        // @see https://doc.wikimedia.org/mediawiki-core/master/php/html/classWikiPage.html
        if ($oldText != $newText ) {
            $content = $content->getContentHandler()->unserializeContent( $newText );
            $page->doEditContent($content,
                $context->getUser(),
                "Extension:IDProvider - automatic string substitution"
            );
        };

        return true;
    }


}
