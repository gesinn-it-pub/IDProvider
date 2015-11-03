<?php
class IDProviderFunctions {


    /**
     * Returns a UUID
     * @see http://stackoverflow.com/a/2040279
     *
     * @return string
     */
    public static function getUUID() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
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
    public function generateRandomNumberCallback() {

        global $wgSubstitutorMinRand;
        global $wgSubstitutorMaxRand;

        return rand($wgSubstitutorMinRand, $wgSubstitutorMaxRand);
    }

    /**
    * Callback function that returns a random string with length of $wgSubstitutorRandStringLength
    *
    * @return [string]
    */
    public static function generateRandomStringCallback() {

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
    public static function generateFakeIDCallback() {

        $id = self::generateRandomString(4);
        $id .= self::uniqid();

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
    public static function generateShortIDCallback() {

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
    public static function generateRandomString($length) {

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

}
