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
 * This is a parent class that all PICKLES modules should be extending. 
 * Each module can specify it's own meta data and whether or not a user
 * must be properly authenticated to view the page. Currently any pages
 * without a template are treated as pages being requested via AJAX and the
 * return will be JSON encoded. In the future this may need to be changed
 * out for logic that allows the requested module to specify what display
 * type(s) it can use.
 */
class Module extends Object
{
	/**
	 * Page title
	 *
	 * @access protected
	 * @var    string, boolean false by default
	 */
	protected $title = false;

	/**
	 * Meta description
	 *
	 * @access protected
	 * @var    string, boolean false by default
	 */
	protected $description = false;

	/**
	 * Meta keywords (comma separated)
	 *
	 * @access protected
	 * @var    string, boolean false by default
	 */
	protected $keywords = false;

	/**
	 * Access level of the page
	 *
	 * Defaults to false which is everybody, even anonymous
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $access = false;

	/**
	 * Secure
	 *
	 * Whether or not the page should be loaded via SSL.  Not currently
	 * being used.  Defaults to false, non-SSL.
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $secure = false;

	/**
	 * Session
	 *
	 * Whether or not a session should be established when this page is
	 * loaded.  Defaults to false, no session.
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $session = false;

	/**
	 * AJAX
	 *
	 * Whether or not the page must be loaded via AJAX and if so, what
	 * pages are allowed to access it and the request method.
	 *
	 * @access protected
	 * @var    boolean or array
	 */
	protected $ajax = false;

	/**
	 * Default display engine
	 *
	 * Defaults to null but could be set to Smarty, JSON, XML or RSS. Value
	 * is overwritten by the config value if not set by the module.
	 *
	 * @access protected
	 * @var    string, boolean false by default
	 */
	protected $engine = false;

	/**
	 * Default template
	 *
	 * Defaults to null but could be set to any valid template basename.
	 * The value is overwritten by the config value if not set by the
	 * module.  The display engine determines what the file extension
	 * should be.
	 *
	 * @access protected
	 * @var    string, boolean false by default
	 */
	protected $template = false;

	/**
	 * Constructor
	 *
	 * The constructor does nothing by default but can be passed a boolean
	 * variable to tell it to automatically run the __default() method.
	 * This is typically used when a module is called outside of the scope
	 * of the controller (the registration page calls the login page in
	 * this manner.
	 *
	 * @param boolean $autorun optional flag to autorun __default()
	 */
	public function __construct($autorun = false)
	{
		parent::__construct();

		if ($autorun === true)
		{
			$this->__default();
		}
	}

	/**
	 * Default "Magic" Method
	 *
	 * This function is overloaded by the module. The __default() method is
	 * where you want to place any code that needs to be executed at
	 * runtime. The reason the code isn't in the constructor is because the
	 * module must be instantiated before the code is executed so that the
	 * controller script is aware of the authentication requirements.
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
	 * Attempts to load the module variable. If it's not set, will attempt
	 * to load from the config.
	 *
	 * @param  string $name name of the variable requested
	 * @return mixed value of the variable or boolean false
	 */
	public function __get($name)
	{
		if ($this->$name == false)
		{
			if (isset($this->config->site[$name]))
			{
				$this->$name = $this->config->site[$name];
			}
		}

		return $this->$name;
	}
}

?>
