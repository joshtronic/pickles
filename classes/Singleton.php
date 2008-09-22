<?php

/**
 * Singleton class
 *
 * This is the file that you include on the page you're instantiating the
 * controller from (typically index.php).  The path to the PICKLES code base is
 * established as well as the path that Smarty will use to store the compiled
 * pages.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 */
class Singleton {

	/**
	 * Private constructor
	 */
	private function __construct() { }

	/**
	 * Gets a variable
	 *
	 * @param  string $variable Name of the variable to be returned
	 * @param  string $array_element Name of the array element that's part of the
	 *         requested variable (optional)
	 * @return Returns either the variable value or false if no variable.
	 * @todo   Need better checking if the passed variable is an array when the
	 *         array element value is present
	 * @todo   Returning false could be misleading, especially if you're
	 *         expecting a boolean value to begin with.  Perhaps an error should
	 *         be thrown?
	 */
	public function get($variable, $array_element = null) {
		if (isset($this->$variable)) {
			if (isset($array_element)) {
				$array = $this->$variable;

				if (isset($array[$array_element])) {
					return $array[$array_element];
				}
			}
			else {
				return $this->$variable;
			}
		}

		return false;
	}

	/**
	 * Sets a variable
	 * 
	 * @param string $variable Name of the variable to be set
	 * @param mixed $value Value to be assigned to the passed variable
     */
	public function set($variable, $value) {
		$this->$$variable = $value;
	}

	/**
	 * Freezes (caches) an object
	 *
	 * Takes the object and serializes it and then stores it and the name and the
	 * timestamp that it was serialized in the session.
	 *
	 * @todo Needs to return the status for error tracking.
	 */
	public function freeze() {
		$session = Session::getInstance();
		$this->timestamp = time();
		$class = get_class($this);
		$session->$class = serialize($this);
	}

	/**
	 * Thaws out a frozen object
	 *
	 * Forces an __autoload on the passed class name because if a serialized
	 * object is unserialized and then you attempt to use it, it will error out
	 * due to the class not being loaded.  __autoload() doesn't occur
	 * automatically in this scenario, that's why it must be forced.
	 *
	 * @param  string $class The name of the class to be thawed out
	 * @return object The unserialized object for the passed class name
	 */
	public static function thaw($class) {
		__autoload($class);

		$session = Session::getInstance();
		return unserialize($session->$class);
	}
}

?>
