<?php
/**
 * The actual IDProvider Functions
 *
 * They can be used programmatically through this class
 * Use the static getId function as the main entry point
 *
 * @file
 * @ingroup Extensions
 */
class IDProviderFunctions {


	/**
	 * This is the global ID providing entry point
	 * It takes an associative array as its parameter, using the same options as the API
	 *
	 * @param array $params
	 *
	 * @return string|int
	 *
	 * @throws Exception
	 */
	public static function getId($params) {

		if (!isset($params['type'])) {
			throw new Exception('No type declared');
		}

		$type = $params['type'];
		$prefix = $params['prefix'] ?: '';
		$padding = $params['padding'] ?: 0;
		$wikipage = $params['wikipage'] ?: null;

		$id = null;

		if ($type === 'uuid') {
			$id = $prefix . self::getUUID();

		} else if ($type === 'increment') {
			$id = self::getIncrement($prefix, $padding);

		} else if ($type === 'fakeid') {
			$id = $prefix .  self::getFakeId();

		} else { // No valid option
			throw new Exception('Unknown type');
		}

		// If &wikipage=true, check if a page with the same name as the $id already exists
		if ($wikipage) {

			$title = Title::newFromText($id);
			$page = WikiPage::factory($title);

			// @TODO: Alternative: Retry this recursively until it works.. ???
			if ($page->exists()) {
				throw new Exception('WikiPage with that title already exists!');
			}
		}

		if ($id) {
			return $id;
		} else {
			throw new Exception('No valid ID was calculated!');
		}

	}


    /**
     * Returns a UUID, using openssl random bytes
	 *
     * @see http://stackoverflow.com/a/15875555
     *
     * @return string
     */
    public static function getUUID() {

		$data = openssl_random_pseudo_bytes(16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

    }

	public static function getIncrement($prefix, $padding = 0) {

		if (!$prefix) {
			$prefix = '___MAIN___';
		}

		self::ensureIncrementTable();

		$increment = self::calculateIncrement($prefix);

		$id = $increment;

		if ($padding && $padding > 0) {
			$id = str_pad($increment, $padding, '0', STR_PAD_LEFT);
		}

		if ($prefix !== '___MAIN___') {
			$id = $prefix . $id;
		}

		return $id;
	}

	/**
	 * This ensures the Increment Table exists
	 *
	 * @throws DBUnexpectedError
	 */
	private static function ensureIncrementTable() {

		$dbw = wfGetDB(DB_MASTER); // Get DB with read access

		// Check if increment table exists, if not - create it
		try {
			$dbw->select('idprovider_increments', '*');
		} catch (Exception $e) {
			$fileName = dirname( __FILE__ ) . '/sql/IDProviderIncrementTable.sql';
			$createTable = file_get_contents($fileName);
			$dbw->query($createTable);
			$dbw->commit();
		}
	}

	/**
	 * Returns the current increment +1, increments the increment of the used prefix
	 *
	 * @param $prefix
	 * @return int|null
	 *
	 * @throws DBUnexpectedError
	 */
	private static function calculateIncrement($prefix) {

		$dbw = wfGetDB(DB_MASTER); // Get DB with read access

		$increment = null;

		$dbw->begin();

		// Get the row according to the $prefix
		$prefixIncrement = $dbw->select(
			'idprovider_increments',
			'increment',
			array(
				'prefix' => $prefix,
			),
			__METHOD__
		);

		if ($prefixIncrement->numRows() <= 0) {

			// If the row does not exist yet, create it first
			$dbw->insert('idprovider_increments',
				array(
					'prefix' => $prefix,
					'increment' => 1
				)
			);
			$dbw->commit();
			$increment = 1;

		} else {
			// Read the increment
			$increment = $prefixIncrement->fetchRow()['increment'] + 1;

			// Update the increment (+1)
			$dbw->update(
				'idprovider_increments',
				array(
					'increment = increment + 1'
				),
				array(
					'prefix' => $prefix,
				)
			);
		}


		$dbw->commit();

		return $increment;

	}

	/**
	 * Generates a Fake ID that is very likely to be truly unique (no guarantee however!)
	 *
	 * This is achived through mixing a milli-timestamp (php uniqid();) with a random string
	 *
	 * @return string
	 */
	public static function getFakeId() {

		// Generates a random string of length 1-2.
		$id = base_convert(rand(0, 36^2), 10, 36);

		// This will "compress" the uniqid (some sort of microtimestamp) to a more dense string
		$id .= base_convert(uniqid(), 10, 36);

		return $id;
	}

	/**
	 * returns a random number between $wgSubstitutorMinRand and $wgSubstitutorMaxRand
	 *
	 * @param $min
	 * @param $max
	 *
	 * @return int
	 */
	public static function getRandomNumber($min, $max) {

		global $wgIDProviderMinRandNumber;
		global $wgIDProviderMaxRandNumber;

		if (!$min) {
			$min = $wgIDProviderMinRandNumber;
		}

		if (!$max) {
			$max = $wgIDProviderMaxRandNumber;;
		}

		return rand($min, $max);
	}

	/**
	 * Callback function that returns a random string with length of $wgSubstitutorRandStringLength
	 *
	 * @param integer $length
	 *
	 * @return string
	 */
	public static function getRandomString($length) {

		global $wgIDProviderRandStringLength;

		if (!$length) {
			$length = $wgIDProviderRandStringLength;
		}

		$key = '';
		$keys = array_merge(range(0,9), range('a', 'z'));

		for($i=0; $i < $length; $i++) {
			$key .= $keys[array_rand($keys)];
		}

		return $key;
	}



	// Old Substitutor Functions
	// TODO: Reimplement them


//    /**
//    * Substitutes ___TIMESTAMP___ for the current UNIX timestamp
//    *
//    * @param  string $oldText
//    * @return [string]          substituted mediawiki text
//    */
//
//	/**
//	 * @param string $oldText 	original mediawiki text
//	 * @return string
//	 */
//    public static function substituteTimestamp($oldText) {
//
//		$date = new DateTime();
//		$timestamp = $date->getTimestamp();
//		$newText = str_replace("___TIMESTAMP___", $timestamp, $oldText);
//
//		return $newText;
//    }
//
//    /**
//    * Substitutes ___RANDOMNUMBER___ with a random number
//    * Minimum and maximum are defined through $wgSubstitutorMinRand and $wgSubstitutorMaxRand
//    *
//    * @param  [string] $oldText original mediawiki text
//    * @return [string]          substituted mediawiki text
//    */
//    function substituteRandomNumber($oldText) {
//
//    $pattern = "/___RANDOMNUMBER___/";
//    $newText = preg_replace_callback($pattern, "generateRandomNumberCallback", $oldText);
//
//    return $newText;
//    }
//
//    /**
//    * Substitutes ___RANDOMSTRING___ with a random string of length $wgSubstitutorRandStringLength
//    *
//    * @param  [string] $oldText original mediawiki text
//    * @return [string]          substituted mediawiki text
//    */
//    function substituteRandomString($oldText) {
//
//		$pattern = "/___RANDOMSTRING___/";
//		$newText = preg_replace_callback($pattern, "generateRandomStringCallback", $oldText);
//
//		return $newText;
//    }
//
//
//    /**
//    * Substitutes ___FAKEID___ with a fake ID, that should be unique
//    * (No garantuees however!)
//    *
//    * @param  [string] $oldText original mediawiki text
//    * @return [string]          substituted mediawiki text
//    */
//    function substituteFakeID($oldText) {
//
//		$pattern = "/___FAKEID___/";
//		$newText = preg_replace_callback($pattern, "generateFakeIDCallback", $oldText);
//
//		return $newText;
//    }
//
//
//    /**
//    * Substitutes ___SHORTID___ with a shorter fake ID, that should be unique
//    *
//    * @param  [string] $oldText original mediawiki text
//    * @return [string]          substituted mediawiki text
//    */
//    function substituteShortID($oldText) {
//
//		$pattern = "/___SHORTID___/";
//		$newText = preg_replace_callback($pattern, "generateShortIDCallback", $oldText);
//
//		return $newText;
//    }





    //////////////////////////////////////////
    // HELPER / CALLBACK FUNCTIONS          //
    //////////////////////////////////////////

	/**
	 * Debug function that converts an object/array to a <pre> wrapped pretty printed JSON string
	 *
	 * @param $obj
	 * @return string
	 */
	public static function toJSON($obj) {
		header('Content-Type: application/json');
		echo json_encode($obj, JSON_PRETTY_PRINT);
		die();
	}

}
