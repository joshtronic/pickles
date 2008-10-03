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
		$array = null;
		  
		if (is_array($object)) {
			foreach ($object as $key => $value) {
				$array[$key] = self::object2array($value);
			}
		}
		else {
			$variables = get_object_vars($object);
			  
			if (is_array($variables)) {
				foreach ($variables as $key => $value) {
					$array[$key] = ($key && !$value) ? null : self::object2array($value);
				}
			}
			else {
				return $object;
			}
		}

		return $array;
	}
}

?>
