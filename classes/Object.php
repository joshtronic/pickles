<?php

/**
 * Object class
 *
 * Every non-Singleton-based class needs to extend this class.  Any models will
 * extend the Model class which entends the Object class already.  This class
 * handles getting an instance of the Config object so that it's available.  Also
 * provides a getter and setter for variables.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 */
class Object {

	/**
	 * Protected instance of the Config class
	 */
	protected $config = null;

	/**
	 * Constructor
	 *
	 * Handles getting an instance of the Config class.
	 */
	public function __construct() {
		$this->config = Config::getInstance();
	}

	/**
	 * Destructor
	 */
	public function __destruct() { }

	/*
	// @todo maybe later
	public function __get($variable) {
		if (!isset($this->data[$variable])) {
			$this->data[$variable] = null;
		}

		return $this->data[$variable];
	}
	*/

	/**
	 * Gets a variable
	 *
	 * @return Returns either the variable value or false if no variable.
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
		$this->$variable = $value;
	}
}

?>
