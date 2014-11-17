<?php
/**
 * Substitutor looks for "magic" words in your markup and substitutes them to calculated strings
 * This differes from usual magic words in that they are only replaced once, and not dynamically
 * (You wouldn't want this with timestamps and random numbers if you use them as ID's)
 *
 * Supports currently:
 * ___TIMESTAMP___
 * ___RANDOMNUMBER___
 * ___RANDOMSTRING___
 *
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
* Add plastic.js library to all pages
*/
function onPageContentSaveComplete( $wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $status ) {

  $title = $wikiPage->getTitle();
  $oldText = $content->getContentHandler()->serializeContent($content);
  $titleObj = Title::makeTitle(0, $title);
  $page = WikiPage::factory($titleObj);
  $context = new RequestContext();

  // Do the actual substitution
  $newText = substituteTimestamp($oldText);
  $newText = substituteRandomNumber($newText);
  $newText = substituteRandomString($newText);


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



function substituteTimestamp($oldText) {

  $date = new DateTime();
  $timestamp = $date->getTimestamp();
  $newText = str_replace("___TIMESTAMP___", $timestamp, $oldText);

  return $newText;
}

function substituteRandomNumber($oldText) {

  $pattern = "/___RANDOMNUMBER___/";
  $newText = preg_replace_callback($pattern, "generateRandomNumber", $oldText);

  return $newText;
}

function substituteRandomString($oldText) {

  $pattern = "/___RANDOMSTRING___/";
  $newText = preg_replace_callback($pattern, "generateRandomString", $oldText);

  return $newText;
}


function generateRandomNumber() {
  global $wgSubstitutorMinRand;
  global $wgSubstitutorMaxRand;
    return rand($wgSubstitutorMinRand, $wgSubstitutorMaxRand);
}

function generateRandomString() {

    global $wgSubstitutorRandStringLength;

    $key = '';
    $keys = array_merge(range(0,9), range('a', 'z'));

    for($i=0; $i < $wgSubstitutorRandStringLength; $i++) {
        $key .= $keys[array_rand($keys)];
    }
    return $key;
}
