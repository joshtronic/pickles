<?php

/**
 * Module Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
	 * Cache Object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $cache = null;

	/**
	 * Database Object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $db = null;

	/**
	 * Fluid or Fixed?
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $fluid = false;

	/**
	 * Page Title
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $title = null;

	/**
	 * Meta Description
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $description = null;

	/**
	 * Meta Keywords (comma separated)
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $keywords = null;

	/**
	 * Secure
	 *
	 * Whether or not the page should be loaded via SSL.
	 *
	 * @access protected
	 * @var    boolean, null by default
	 */
	protected $secure = null;

	/**
	 * Private
	 *
	 * Whether or not the page can be accessed directly.
	 *
	 * @access protected
	 * @var    boolean, false by default
	 */
	protected $private = false;

	/**
	 * Security Settings
	 *
	 * @access protected
	 * @var    boolean, null by default
	 */
	protected $security = null;

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
	 * Whether or not the module is being called via AJAX. This determines if
	 * errors should be returned as JSON or if it should use the Error class
	 * which can be interrogated from within a template.
	 *
	 * @access protected
	 * @var    boolean, false (not AJAX) by default
	 */
	protected $ajax = false;

	/**
	 * Method
	 *
	 * Request methods that are allowed to access the module.
	 *
	 * @access protected
	 * @var    string or array, null by default
	 */
	protected $method = null;

	/**
	 * Validate
	 *
	 * Variables to validate.
	 *
	 * @access protected
	 * @var    array, null by default
	 */
	protected $validate = null;

	/**
	 * Hash
	 *
	 * Whether or not to validate the security hash. Boolean true will indicate
	 * using the name of the module as the hash, a string value will use the
	 * value instead.
	 *
	 * @access protected
	 * @var    string or boolean, null by default
	 */
	protected $hash = null;

	/**
	 * Default Display Engine
	 *
	 * Defaults to PHP but could be set to JSON, XML or RSS. Value is
	 * overwritten by the config value if not set by the module.
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $engine = DISPLAY_PHP;

	/**
	 * Default Template
	 *
	 * Defaults to null but could be set to any valid template basename. The
	 * value is overwritten by the config value if not set by the module. The
	 * display engine determines what the file extension should be.
	 *
	 * @access protected
	 * @var    string, 'index' by default
	 */
	protected $template = 'index';

	/**
	 * Return
	 *
	 * Array that is returned to the template in the case of the module not
	 * returning anything itself. This is somewhat of a one way trip as you
	 * cannot get the variable unless you reference the return array explicitly
	 * $this->return['variable']
	 *
	 * @access protected
	 * @var    array
	 */
	protected $return = array();

	/**
	 * Constructor
	 *
	 * The constructor does nothing by default but can be passed a boolean
	 * variable to tell it to automatically run the __default() method. This is
	 * typically used when a module is called outside of the scope of the
	 * controller (the registration page calls the login page in this manner.
	 *
	 * @param boolean $autorun optional flag to autorun __default()
	 * @param boolean $valiate optional flag to disable input validation during autorun
	 */
	public function __construct($autorun = false, $validate = true)
	{
		parent::__construct();

		$this->cache = Cache::getInstance();
		$this->db    = Database::getInstance();

		if ($autorun === true)
		{
			if ($validate === true)
			{
				$errors = $this->__validate();

				if ($errors !== false)
				{
					exit('Errors encountered, this is a @todo for form validation when calling modules from inside of modules');
				}
			}

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
	 * Places the variables that are being modified in the return array that is
	 * returned if nothing is returned by the module itself. This also prohibits
	 * the direct modification of module variables which could cause issues.
	 *
	 * @param string $name name of the variable to be set
	 * @param mixed $value value of the variable to be set
	 */
	public function __set($name, $value)
	{
		$this->return[$name] = $value;
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
		if (!isset($this->$name))
		{
			if (isset($this->config->pickles[$name]))
			{
				$this->$name = $this->config->pickles[$name];
			}
			else
			{
				$this->$name = false;
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
			throw new Exception('Only Controller can perform setRequest()');
		}
	}

	/**
	 * Validate
	 *
	 * Internal validation for data passed to a Module. Grabs the super global
	 * based on the Module's request method and loops through the data using the
	 * Module's validation array (if present) sanity checking each variable
	 * against the rules.
	 *
	 * @return mixed boolean false if everything is fine or an array or errors
	 */
	public function __validate()
	{
		$errors = array();

		if ($this->validate !== false)
		{
			switch (strtoupper($this->method))
			{
				case 'GET':  $global = &$_GET;     break;
				case 'POST': $global = &$_POST;    break;
				default:     $global = &$_REQUEST; break;
			}

			foreach ($this->validate as $variable => $rules)
			{
				if (!is_array($rules))
				{
					$variable = $rules;
					$rules    = true;
				}

				if (isset($global[$variable]) && !String::isEmpty($global[$variable]))
				{
					if (is_array($rules))
					{
						$rule_errors = Validate::isValid($global[$variable], $rules);

						if (is_array($rule_errors))
						{
							$errors = array_merge($errors, $rule_errors);
						}
					}
				}
				else
				{
					$errors[] = 'The ' . $variable . ' field is required.';
				}
			}
		}

		return $errors == array() ? false : $errors;
	}
}

?>
