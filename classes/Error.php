<?php

/**
 * Error handling class
 *
 * Handles (for the most part) all the errors and warnings that are encountered
 * by the PICKLES core classes.  Unfortunately this was written, but never really
 * utilized correctly, so I'm thinking it may be better to remove it entirely or
 * add logic to appropriately report the errors, perhaps as a variable in the
 * model data array.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 * @todo      Internally document the functions better.
 * @todo      Convert the class to extend Singleton.
 * @todo      Quite possibly revamp the class entirely as it is still left over
 *            from the previous iteration of PICKLES (the system received a kick
 *            in the ass Q2 of 2008 to move in a more object-oriented direction,
 *            previously models were procedural files and not instantiated
 *            classes.  I want to thank Joe Stump for the direction I took.
 */
class Error {

	/**
	 * Private message arrays
	 */
	private static $errors;
	private static $warnings;
	
	/**
	 * Gets an instance of the object
	 *
	 * Determines if the Error object has been instantiated, and if not, it will
	 * go ahead and instantiate it.
	 *
	 * @return object An instance of the Error class
	 */
	public static function instance() {
		static $object;

		if (!is_object($object)) {
			$object = new Error();
		}

		return $object;
	}

	/**
	 * Adds an error message
	 *
	 * Takes the passed error message and loads it into the private array of
	 * error messages.
	 *
	 * @return boolean true
	 */
	public static function addError($message) {
		self::$errors[] = $message;
		return true;
	}

	/**
	 * Adds a warning message
	 *
	 * Takes the passed warning message and loads it into the private array of
	 * warning messages.
	 *
	 * @return boolean true
	 */
	public static function addWarning($message) {
		self::$warnings[] = $message;
		return true;
	}

	/**
	 * Gets the stored errors
	 *
	 * Returns the errors that have been stored in the private array of error
	 * messages.
	 *
	 * @return array Error messages indexed by the order they were stored
	 */
	public static function getError() {
		return self::$errors;
	}

	/**
	 * Gets the stored warnings
	 *
	 * Returns the warnings that have been stored in the private array of warning
	 * messages.
	 *
	 * @return array Warning messages indexed by the order they were stored
	 */
	public static function getWarning() {
		return self::$warnings;
	}

	/**
	 * Determines if there are any stored errors or warnings
	 *
	 * Checks the private error and warning arrays and returns the status.
	 *
	 * @return boolean Whether or not there are any errors or warnings.
	 */
	public static function isError() {
		if (is_array(self::getError()) || is_array(self::getWarning())) {
			return true;
		}

		return false;
	}

	/**
	 * Display errors and warnings
	 *
	 * If any errors or warnings are set they are echo'd out separated by XHTML
	 * compliant line breaks.  Also clears out the private arrays upon displaying
	 * their contents.
	 *
	 * @return boolean Whether or not there are any errors or warnings.
	 */
	public function display() {
		if (self::isError()) {
			if (self::getError()) {
				foreach (self::getError() as $error) {
					echo "{$error}<br />";
				}
			}

			if (self::getWarning()) {
				foreach (self::getWarning() as $error) {
					echo "{$warning}<br />";
				}
			}
			
			self::$errors = self::$warnings = null;
			return true;
		}

		return false;
	}
}

?>
