<?php

/**
 * Model Class File for PICKLES
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
 * @copyright Copyright 2007, 2008 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Model Class
 *
 * Every model in PICKLES at both the core and site levels need to extend
 * this class. It handles the getting of common model variables (auth, data
 * and view) as well as making sure that every model has a database object
 * available.
 */
class Model extends Object {
	
	/**
	 * Data array used by the viewer
	 */
	protected $data = array();
	
	/**
	 * Config object
	 */
	protected $config = null;

	/**
	 * Database object
	 */
	protected $db = null;

	/**
	 * Name of the model
	 */
	protected $name = null;

	/**
	 * Mailer object
	 */
	protected $mailer = null;

	protected $authentication = null;
	protected $viewer         = null;
	protected $session        = null;

	/**
	 * Constructor
	 *
	 * Handles calling the parent constructor and sets up the model's
	 * internal config and database object
	 */
	public function __construct(Config $config, DB $db, Mailer $mailer = null) {
		parent::__construct($config);

		$this->config = $config;
		$this->db     = $db;

		$this->mailer = isset($mailer) ? $mailer : new Mailer($config);
	}

	/**
	 * Gets the authenticate value
	 *
	 * Order of precedence: Model, Config, Guess (guess is always false)
	 *
	 * @return boolean Whether or not the model requires user authentication
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
	 * Gets the session value
	 *
	 * Order of precedence: Auth On, Model, Config, Guess (guess is always false)
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
	 * Gets the requested Viewer
	 *
	 * Order of precedence: Model, Config, Guess (guess is always Smarty)
	 *
	 * @return string The viewer that the model has requested to be used
	 * @todo   Guess shouldn't be Smarty, it should be the dummy PHP template.
	 * @todo   Use the config override value to help determine.
	 */
	public function getViewer() {
		if ($this->viewer == null) {
			return isset($argv) ? 'CLI' : 'Smarty';
		}
		else {
			return $this->viewer;
		}
	}

	/**
	 * Alias for $model->data
	 *
	 * @return array Associative array of data that was set by the model
	 */
	public function getData() {
		if (isset($this->data)) {
			return $this->data;
		}

		return null;
	}

	public function __set($variable, $value) {
		$this->data[$variable] = $value;
	}

	/**
	 * Destructor
	 *
	 * Handles calling the parent's constructor, nothing else.
	 */
	public function __destruct() {
		parent::__destruct();
	}

	/**
	 * Default function
	 *
	 * This function is overloaded by the model.  The __default() function
	 * is where any code that needs to be executed at run time needs to be
	 * placed.  It's not in the constructor as the model needs to be
	 * instantiated first so that the authorization requirements can be
	 * checked without running code it's potentially not supposed to have
	 * been executed.
	 */
	public function __default() { }
}

?>
