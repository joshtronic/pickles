<?php

/**
 * Module Class File for PICKLES
 *
 * PICKLES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * PICKLES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with PICKLES.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author    Joshua John Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007, 2008, 2009 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Module Class
 *
 * Every module (page) in PICKLES at both the core and site levels should
 * extend this class. It handles the getting of common module variables
 * (auth, data and view) as well as making sure that every module has a
 * database object available.
 */
class Module extends Object {

	/**
	 * Data array used by the display 
	 */
	// @todo REMOVE THIS
	//protected $data = array();

	/**
	 * Array of public variables to be available by the display
	 */
	protected $public = array();

	/**
	 * Passed objects
	 */
	protected $config = null;
	protected $db     = null;
	protected $mailer = null;
	protected $error  = null;

	/**
	 * Template file for the module
	 */
	protected $template = null;
	protected $name     = null;
	
	/**
	 * Module defaults
	 */
	protected $authentication = false;
	protected $caching        = false;
	protected $display        = false;
	protected $session        = false;

	private $smarty;
	private $cache_id;

	/**
	 * Constructor
	 *
	 * Handles calling the parent constructor and sets up the module's
	 * internal config and database object
	 *
	 * @param object $config Instance of the Config class
	 * @param object $db Instance of the DB class
	 * @param object $mailer Instance of the Mailer class
	 */
	public function __construct(Config $config, DB $db, Mailer $mailer, Error $error) {
		parent::__construct();

		$this->config = $config;
		$this->db     = $db;
		$this->mailer = $mailer;
		$this->error  = $error;
	}

	/**
	 * Gets the authentication value
	 *
	 * Order of precedence:
	 * Module, Config, Guess (guess is always false)
	 *
	 * @return boolean Whether or not user authentication is required
	 */
	public function getAuthentication() {
		if ($this->authentication != null) {
			return $this->authentication;
		}
		else if (is_bool($this->config->getAuthentication())) {
			return $this->config->getAuthentication();
		}

		return false;
	}
	
	/**
	 * Gets the caching value
	 *
	 * Order of precedence:
	 * POSTed, Module, Config, Guess (guess is always false)
	 *
	 * @return boolean Whether or not user authentication is required
	 */
	public function getCaching() {
		/*
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return false;
		}
		*/
		if ($this->caching != null) {
			return $this->caching;
		}
		else if ($this->config->getCaching()) {
			return $this->config->getCaching();
		}

		return false;
	}

	/**
	 * Gets the session value
	 *
	 * Order of precedence:
	 * Auth On, Module, Config, Guess (guess is always false)
	 *
	 * @return boolean Whether or not the session needs to be started
	 */
	public function getSession() {
		if ($this->authentication === true) {
			return true;
		}
		else if ($this->session != null) {
			return $this->session;
		}
		else if (is_bool($this->config->getSession())) {
			return $this->config->getSession();
		}

		return false;
	}

	/**
	 * Gets the requested Display
	 *
	 * Order of precedence:
	 * Module, Config, Guess (guess is always Smarty)
	 *
	 * @return string The display that the module has requested to be used
	 */
	public function getDisplay() {

		// Checks if the module has a display tyoe
		if (isset($this->display)) {
			// Checks if multiple display types are supported
			if (is_array($this->display)) {
				$display = $this->display[0];
			}
			else {
				$display = $this->display;
			}

			if (in_array($display, array(DISPLAY_JSON, DISPLAY_PHP, DISPLAY_RSS, DISPLAY_SMARTY))) {
				return $display;
			}
		}

		// Checks for a display type in the config
		if (isset($this->config->modules->display)) {
			return (string)$this->config->modules->display;
		}
		else {
			$this->error->addWarning('Invalid display specified, DISPLAY_PHP used by default (' . $this->display . ')');
			return DISPLAY_PHP;
		}
	}

	/**
	 * Alias for $module->data
	 *
	 * @return array Associative array of data that was set by the module
	 */
	public function getData() {
		if (isset($this->data)) {
			return $this->data;
		}

		return null;
	}

	/**
	 * Sets the variable in the data array
	 *
	 * Overrides the built-in functionality to set an object's property with
	 * logic to place that data inside the data array for easier interaction
	 * later on.
	 *
	 * @param string $variable Name of the variable to be set
	 * @param mixed $value Data to be set
	 * @todo  REMOVE ME!
	 */
	/*
	public function __set($variable, $value) {
		if ($variable != 'cache_id') {
			$this->data[$variable] = $value;
		}
	}
	*/

	public function setPublic($variable, $value) {
		$this->public[$variable] = $value;
		return true;
	}

	public function getPublic($variable) {
		if (isset($this->public[$variable])) {
			return $this->public[$variable];
		}
		else {
			return null;
		}
	}

	public function setSmartyObject(Smarty $smarty) {
		$this->smarty = $smarty;
	}

	public function isCached($id = null) {
		if ($id == null) {
			$id = get_class($this);
		}

		switch ($this->getDisplay()) {
			case DISPLAY_PHP:
				break;

			case DISPLAY_SMARTY:
				if (is_object($this->smarty)) {
					if ($this->smarty->template_exists('index.tpl')) {
						return $this->smarty->is_cached('index.tpl', $id);
					}
					else {
						return $this->smarty->is_cached($template, $id);
					}
				}
				break;
		}

		return false;
	}

	public function setCacheID($id) {
		$this->cache_id = $id;
	}

	public function getCacheID() {
		return $this->cache_id;
	}

	/**
	 * Default function
	 *
	 * This function is overloaded by the module.  The __default() function
	 * is where any code that needs to be executed at run time needs to be
	 * placed.  It's not in the constructor as the module needs to be
	 * instantiated first so that the authorization requirements can be
	 * checked without running code it's potentially not supposed to have
	 * been executed.
	 */
	public function __default() {
	
	}
}

?>
