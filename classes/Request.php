<?php

/**
 * Request class
 *
 * Another one of those left over classes that seems to have been forgotten.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 * @todo      Phase out entirely or make use of it.
 */
class Request {

	/**
	 * Private request array
	 */
	private static $request;

	/**
	 * Loads the $_REQUEST super global into the object's array
	 */
	public static function load() {
		if (is_array($_REQUEST)) {
			foreach ($_REQUEST as $key => $value) {
				self::$request[$key] = $value;
				unset($_REQUEST[$key]);
			}
		}

		return true;
	}

	/**
	 * Gets a variable
	 *
	 * @return Returns either the variable value or false if no variable.
	 * @todo   Returning false could be misleading, especially if you're
	 *         expecting a boolean value to begin with.  Perhaps an error should
	 *         be thrown?
	 */
	public static function get($variable) {
		if (isset(self::$request[$variable])) {
			return self::$request[$variable];	
		}

		return false;
	}
}

?>
