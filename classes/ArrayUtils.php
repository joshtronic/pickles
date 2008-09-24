<?php

/**
 * Small collection of array utilities
 *
 * A bit too small actually, as there is still only one function in here.
 * Since I have built PICKLES based around my own needs, this has been the
 * only function I have felt the need to add.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 * @todo      Just so it doesn't seem so bare, I need to add a few more
 *            common array manipulation functions.
 */
class ArrayUtils {
	
	/**
	 * Converts an object into an array (recursive)
	 *
	 * This only gets used by the Config class because simplexml_load_file()
	 * returns an object by default, and I prefered having it in an array
	 * format.
	 *
	 * @param  object $object Object to be converted into an array
	 * @return array Resulting array formed from the passed object
	 */
	public static function object2array($object) {
		if (is_object($object)) {
			$object = (array)$object;
		}

		foreach ($object as $key => $value) {
			if (is_object($value)) {
				$object[$key] = self::object2array($value);
			}
		}

		return $object;
	}
}

?>
