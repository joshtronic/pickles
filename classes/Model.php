<?php

/**
 * Model class
 *
 * Every model in PICKLES at both the core and site levels need to extend this
 * class. It handles the getting of common model variables (auth, data and view)
 * as well as making sure that every model has a database object available.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 */
class Model extends Object {
	
	/**
	 * Data array used by the viewer
	 */
	protected $data = array();
	
	/**
	 * Database object
	 */
	protected $db = null;

	/**
	 * Name of the model
	 */
	protected $name = null;

	protected $authentication = null;
	protected $viewer         = null;
	protected $session        = null;

	/**
	 * Constructor
	 *
	 * Handles calling the parent constructor and sets up the model's internal
	 * database object
	 */
	public function __construct() {
		parent::__construct();

		$this->db = DB::getInstance();
	}

	/**
	 * Gets the authenticate value
	 *
	 * @todo Add in configuration level override
	 * @return boolean Whether or not the model requires user authentication to use
	 */
	public function getAuthentication() {
		// Order of precedence: Model, Config, Guess
		if ($this->authentication == null) {
			return false; 
		}
		else {
			return $this->authentication;
		}
	}

	/**
	 * Gets the session value
	 *
	 * @todo Add in configuration level override
	 * @return boolean Whether or not the session needs to be started
	 */
	public function getSession() {
		// Order of precedence: Auth On, Model, Config, Guess
		if ($this->authentication === true) {
			return true;
		}
		else {
			if ($this->session == null) {
				return false;
			}
			else {
				return $this->session;
			}
		}
	}

	/**
	 * Gets the requested Viewer
	 *
	 * @todo Add in configuration level override
	 * @return string The viewer that the model has requested to be used
	 */
	public function getViewer() {
		// Order of precedence: Model, Config, Guess
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
	 * This function is overloaded by the model.  The __default() function is
	 * where any code that needs to be executed at run time needs to be placed.
	 * It's not in the constructor as the model needs to be instantiated first
	 * so that the authorization requirements can be checked without running
	 * code it's potentially not supposed to have run.
	 */
	public function __default() { }
}

?>
