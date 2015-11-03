<?php
/**
 * IDProvider is a MediaWiki Extension that provides (unique) ID's in serveral ways:
 *
 * * Through the API
 * * Through a PHP getter function
 * * Through wikitext string substitutions (@see http://mediawiki.org/wiki/Extension:Substitutor)
 *
 * Common "magic words" in MediaWiki are replaced dynamically in MediaWiki.
 * You would not want this dynamic behaviour with timestamps or unique strings / numbers / IDs,
 * since they should be fixed after they were created.
 * This provides useful if you want to create unique names and URL's for "red links",
 * or simply timestamp sites / changes.
 *
 * @see http://mediawiki.org/wiki/Extension:IDProvider
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @links https://github.com/gesinn-it/IDProvider/blob/master/README.md Documentation
 * @links https://github.com/gesinn-it/IDProvider Source code
 *
 * @author Simon Heimler, 2015
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
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
$wgSubstitutorMaxRand          = 9999999999;
$wgSubstitutorRandStringLength = 12;


//////////////////////////////////////////
// CREDITS                              //
//////////////////////////////////////////

$wgExtensionCredits['other'][] = array(
   'path'           => __FILE__,
   'name'           => 'Substitutor',
   'author'         => array('Simon Heimler'),
   'version'        => '0.1.0',
   'url'            => 'https://www.mediawiki.org/wiki/Extension:Substitutor',
   'descriptionmsg' => 'substitutor-desc',
   'license-name'   => 'MIT'
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
  $newText = substituteShortID($newText);


  // If a substitution was made, save the edited page anew
  // @see https://doc.wikimedia.org/mediawiki-core/master/php/html/classWikiPage.html
  if ($oldText != $newText ) {
    $content = $content->getContentHandler()->unserializeContent( $newText );
    $page->doEditContent($content,
      $context->getUser(),
      "Extension:Substitor - automatic string substitution"
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
 * Substitutes ___FAKEID___ with a fake ID, that should be unique
 * (No garantuees however!)
 *
 * @param  [string] $oldText original mediawiki text
 * @return [string]          substituted mediawiki text
 */
function substituteFakeID($oldText) {

  $pattern = "/___FAKEID___/";
  $newText = preg_replace_callback($pattern, "generateFakeIDCallback", $oldText);

  return $newText;
}


/**
 * Substitutes ___SHORTID___ with a shorter fake ID, that should be unique
 *
 * @param  [string] $oldText original mediawiki text
 * @return [string]          substituted mediawiki text
 */
function substituteShortID($oldText) {

  $pattern = "/___SHORTID___/";
  $newText = preg_replace_callback($pattern, "generateShortIDCallback", $oldText);

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

  $id = generateRandomString(4);
  $id .= uniqid();

  return $id;
}

/**
 * Generates a Fake ID that is very likely to be truly unique (no guarantee however!)
 * This version tries to be as short as possible
 *
 * This is achived through mixing a militimestamp (php uniqid();) with a random string
 *
 * @return [string]
 */
function generateShortIDCallback() {

  // Generates a random string of length 1-2.
  $id = base_convert(rand(0, 36^2), 10, 36);

  // This will "compress" the uniqid (some sort of microtimestamp) to a more dense string
  $id .= base_convert(uniqid(), 10, 36);

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
