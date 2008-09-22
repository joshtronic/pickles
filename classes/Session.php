<?php

/**
 * Session wrapper class
 *
 * Basic functions to ensure that interaction with the $_SESSION super global is
 * done in a consistent manner.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 * @todo      Make the SQL less specific, right now you have to use a table
 *            named users, and use the email as the username.  I will need to
 *            move this to the configuration and allow the user to specify which
 *            table to authenticate against, and what column names to use for the
 *            username and password.
 */
class Session extends Singleton {

	/**
	 * Private instance of the Session class
	 */
	private static $instance;

	/**
	 * Session ID
	 */
	public static $id = null;

	/**
	 * Private constructor
	 *
	 * Checks if session.auto_start is not enabled and if not, it will start the
	 * session and then grabs the session ID.
	 *
	 * @todo Need to look into whether or not sessions can be disabled
	 *       indefinately.  If so, this may need to be rethought.
	 */
	private function __construct() {
		if (ini_get('session.auto_start') == 0) {
			session_start();
		}
		
		$this->id = session_id();
	}

	/**
	 * Gets an instance of the Session class
	 */
	public static function getInstance() {
		if (!self::$instance instanceof Session) {
			self::$instance = new Session();
		}

		return self::$instance;
	}

	/**
	 * Destroys the session
	 *
	 * Loops through all the $_SESSION variables and session_unregister()s them.
	 * After the unregistering session_destory() is called
	 */
	public function destroy() {
		// foreach ($_SESSION as $variable => $value)
		foreach (array_keys($_SESSION) as $variable) {
			session_unregister($variable);
		}

		session_destroy();
	}

	/**
	 * Clone function
	 *
	 * Triggers a PHP error as the Session class cannot be cloned because it
	 * extends the Singleton class.
	 *
	 * @todo The Config and Session (and any other Singleton classes) need to
	 *       have this function.  I think it can be added to the Singleton class
	 *       directly, then the child classes will have it via inheritance.  Wait
	 *       just remembered that class inheritance with static classes is a bit
	 *       screwy so every class may need the function directly.
	 */
	public function __clone() {
		trigger_error('Clone is not allowed for ' . __CLASS__, E_USER_ERROR);
	}

	/**
	 * Gets a session variable
	 *
	 * @param  string $var Name of the variable to be returned
	 * @return Value of the requested variable
	 * @todo   Returns null if the variable isn't set.  Not sure if this is as
     *         drastic as returning false, we'll see.
	 */
	public function __get($var) {
		if (!isset($_SESSION[$var])) {
			$_SESSION[$var] = null;
		}

		return $_SESSION[$var];
	}

	/**
	 * Sets a session variable
	 *
	 * @param  string $var Name of the variable to set
	 * @param  mixed $val Value to be assigned to the passed variable
	 * @return boolean The returned status of assigning the variable.
	 */
	function __set($var, $val) {
		return ($_SESSION[$var] = $val);
	}

	/**
	 * Checks if a session variable is set
	 *
	 * @param  string $var Name of the variable to be checked
	 * @return boolean Whether or not the passed variable is set
	 */
	public function __isset($var) {
		return isset($_SESSION[$var]) || isset($this->$var);
	}

	/**
	 * Destructor
	 *
	 * Closes out the session.
	 */
	public function __destruct() {
		session_write_close();
	}
}

?>
