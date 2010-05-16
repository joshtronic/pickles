<?php

/**
 * Module Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package   PICKLES
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
 */

/**
 * Module Class
 *
 * This is a parent class that all PICKLES modules should be extending. Each
 * module can specify it's own meta data and whether or not a user must be
 * properly authenticated to view the page. Currently any pages without a
 * template are treated as pages being requested via AJAX and the return will
 * be JSON encoded. In the future this may need to be changed out for logic
 * that allows the requested module to specify what display type(s) it can use.
 */
class Module extends Object
{
	/**
	 * Database
	 *
	 * @access protected
	 * @var    object
	 */
	protected $db = null;

	/**
	 * Page title
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $title = null;

	/**
	 * Meta description
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $description = null;

	/**
	 * Meta keywords (comma separated)
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $keywords = null;

	/**
	 * Access level of the page
	 *
	 * @access protected
	 * @var    boolean, null by default
	 */
	protected $access = null;

	/**
	 * Secure
	 *
	 * Whether or not the page should be loaded via SSL.
	 *
	 * @access protected
	 * @var    boolean, null by default
	 * @todo   Implement this functionality
	 */
	protected $secure = null;

	/**
	 * Session
	 *
	 * Whether or not a session should be established.
	 *
	 * @access protected
	 * @var    boolean, null by default
	 */
	protected $session = null;

	/**
	 * AJAX
	 *
	 * Whether or not the page must be loaded via AJAX and if so, what pages
	 * are allowed to access it and the request method.
	 *
	 * @access protected
	 * @var    boolean or array, null by default
	 * @todo   Implement this functionality
	 */
	protected $ajax = null;

	/**
	 * Default display engine
	 *
	 * Defaults to null but could be set to Smarty, JSON, XML or RSS. Value is
	 * overwritten by the config value if not set by the module.
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $engine = null;

	/**
	 * Default template
	 *
	 * Defaults to null but could be set to any valid template basename. The
	 * value is overwritten by the config value if not set by the module. The
	 * display engine determines what the file extension should be.
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $template = null;

	/**
	 * Request Data
	 *
	 * Modules should not interact with $_REQUEST, $_POST or $_GET directly as
	 * the contents could be unsafe. The Controller cleanses this data and sets
	 * it into this variable for safe access by the module.
	 *
	 * @access protected
	 * @var    array, null by default
	 * @todo   Currently the super globals are not being cleared out
	 */
	protected $request = null;

	/**
	 * Constructor
	 *
	 * The constructor does nothing by default but can be passed a boolean
	 * variable to tell it to automatically run the __default() method. This is
	 * typically used when a module is called outside of the scope of the
	 * controller (the registration page calls the login page in this manner.
	 *
	 * @param boolean $autorun optional flag to autorun __default()
	 */
	public function __construct($autorun = false)
	{
		parent::__construct();

		$this->db = Database::getInstance();

		if ($autorun === true)
		{
			$this->__default();
		}
	}

	/**
	 * Default "Magic" Method
	 *
	 * This function is overloaded by the module. The __default() method is
	 * where you want to place any code that needs to be executed at runtime.
	 * The reason the code isn't in the constructor is because the module must
	 * be instantiated before the code is executed so that the controller
	 * script is aware of the authentication requirements.
	 */
	public function __default()
	{

	}

	/**
	 * Magic Setter Method
	 *
	 * Prohibits the direct modification of module variables.
	 *
	 * @param  string $name name of the variable to be set
	 * @param  mixed $value value of the variable to be set
	 * @return boolean false
	 */
	public function __set($name, $value)
	{
		trigger_error('Cannot set module variables directly', E_USER_ERROR);
		return false;
	}

	/**
	 * Magic Getter Method
	 *
	 * Attempts to load the module variable. If it's not set, will attempt to
	 * load from the config.
	 *
	 * @param  string $name name of the variable requested
	 * @return mixed value of the variable or boolean false
	 */
	public function __get($name)
	{
		if ($this->$name == null)
		{
			if (isset($this->config->module[$name]))
			{
				$this->$name = $this->config->module[$name];
			}
			else
			{
				switch ($name)
				{
					case 'engine':
						$default = DISPLAY_PHP;
						break;

					case 'template':
						$default = 'index';
						break;

					default:
						$default = false;
						break;
				}

				$this->$name = $default;
			}
		}

		return $this->$name;
	}

	/**
	 * Sets the Request
	 *
	 * @param  array $request data to be loaded into the request variable
	 * @return boolean whether or not the assignment was successful
	 */
	public function setRequest($request)
	{
		$backtrace = debug_backtrace();

		if ($backtrace[1]['class'] == 'Controller')
		{
			$this->request = $request;
			return true;
		}
		else
		{
			trigger_error('Only Controller can perform setRequest()', E_USER_ERROR);
			return false;
		}
	}
}

?>
