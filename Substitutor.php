<?php
/**
 * Substitutor is a MediaWiki Extension that does one-time string substitution.
 *
 * Common "magic words" in MediaWiki are replaced dynamically in MediaWiki.
 * You would not want this dynamic behaviour with timestamps or unique strings / numbers / IDs,
 * since they should be fixed after they were created.
 * This provides useful if you want to create unique names and URL's for "red links",
 * or simply timestamp sites / changes.
 *
 * For more info see http://mediawiki.org/wiki/Extension:Substitutor
 *
 * @file
 * @ingroup Extensions
 * @author Simon Heimler, 2014
 * @license GNU General Public Licence 2.0 or later
 */


//////////////////////////////////////////
// VARIABLES                            //
//////////////////////////////////////////

$dir         = dirname( __FILE__ );
$dirbasename = basename( $dir );


//////////////////////////////////////////
// CONFIG                               //
//////////////////////////////////////////

$wgSubstitutorMinRand          = 1000000000;
$wgSubstitutorMaxRand          = 9909999999;
$wgSubstitutorRandStringLength = 12;


//////////////////////////////////////////
// CREDITS                              //
//////////////////////////////////////////

$wgExtensionCredits['other'][] = array(
   'path'           => __FILE__,
   'name'           => 'Substitutor',
   'author'         => array('Simon Heimler'),
   'version'        => '0.0.1',
   'url'            => 'https://www.mediawiki.org/wiki/Extension:Substitutor',
   'descriptionmsg' => 'Substitutor-desc',
);



//////////////////////////////////////////
// LOAD FILES                           //
//////////////////////////////////////////

// Register hooks
$wgHooks['PageContentSaveComplete'][] = 'onPageContentSaveComplete';



//////////////////////////////////////////
// HOOK CALLBACKS                       //
//////////////////////////////////////////

/**
* Hook: After a wiki page is saved, look for strings to substitute
* If there are some, a new revision will be made that contains the substitutions
*/
function onPageContentSaveComplete( $wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $status ) {

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


  // If a substitution was made, save this as a new, minor edit
  if ($oldText != $newText ) {
    $content = $content->getContentHandler()->unserializeContent( $newText );

    $page->doQuickEditContent($content,
      $context->getUser(),
      "Automatic string substitution",
      true // minor modification
    );
  };

  return true;
}


/**
 * Substitutes ___TIMESTAMP___ for the current UNIX timestamp
 *
 * @param  [string] $oldText original mediawiki text
 * @return [string]          substituted mediawiki text
 */
function substituteTimestamp($oldText) {

  $date = new DateTime();
  $timestamp = $date->getTimestamp();
  $newText = str_replace("___TIMESTAMP___", $timestamp, $oldText);

  return $newText;
}

/**
 * Substitutes ___RANDOMNUMBER___ with a random number
 * Minimum and maximum are defined through $wgSubstitutorMinRand and $wgSubstitutorMaxRand
 *
 * @param  [string] $oldText original mediawiki text
 * @return [string]          substituted mediawiki text
 */
function substituteRandomNumber($oldText) {

  $pattern = "/___RANDOMNUMBER___/";
  $newText = preg_replace_callback($pattern, "generateRandomNumberCallback", $oldText);

  return $newText;
}

/**
 * Substitutes ___RANDOMSTRING___ with a random string of length $wgSubstitutorRandStringLength
 *
 * @param  [string] $oldText original mediawiki text
 * @return [string]          substituted mediawiki text
 */
function substituteRandomString($oldText) {

  $pattern = "/___RANDOMSTRING___/";
  $newText = preg_replace_callback($pattern, "generateRandomStringCallback", $oldText);

  return $newText;
}


/**
 * Substitutes ___FAKEID___ with a fake ID, that should be unique (no)
 *
 * @param  [string] $oldText original mediawiki text
 * @return [string]          substituted mediawiki text
 */
function substituteFakeID($oldText) {

  $pattern = "/___FAKEID___/";
  $newText = preg_replace_callback($pattern, "generateFakeIDCallback", $oldText);

  return $newText;
}



//////////////////////////////////////////
// HELPER / CALLBACK FUNCTIONS          //
//////////////////////////////////////////

/**
 * Callback function that returns a random number between $wgSubstitutorMinRand and $wgSubstitutorMaxRand
 *
 * @return [integer]
 */
function generateRandomNumberCallback() {

  global $wgSubstitutorMinRand;
  global $wgSubstitutorMaxRand;

  return rand($wgSubstitutorMinRand, $wgSubstitutorMaxRand);
}

/**
 * Callback function that returns a random string with length of $wgSubstitutorRandStringLength
 *
 * @return [string]
 */
function generateRandomStringCallback() {

  global $wgSubstitutorRandStringLength;

  return generateRandomString($wgSubstitutorRandStringLength);
}



/**
 * Generates a Fake ID that is very likely to be truly unique (no guarantee however!)
 *
 * This is achived through mixing a militimestamp (php uniqid();) with a random string
 *
 * @return [string]
 */
function generateFakeIDCallback() {

  $id = uniqid();
  $id .= generateRandomString(4);

  return $id;

}

/**
 * Rturns a random string with length of $length
 *
 * @param  [number] $length string length
 * @return [string]
 */
function generateRandomString($length) {

  global $wgSubstitutorRandStringLength;

  if (!$length) {
    $length = $wgSubstitutorRandStringLength;
  }

  $key = '';
  $keys = array_merge(range(0,9), range('a', 'z'));

  for($i=0; $i < $length; $i++) {
      $key .= $keys[array_rand($keys)];
  }
  return $key;
}
