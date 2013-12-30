<?php

/**
 * Module Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Joshua Sherman
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
	 * Page Title
	 *
	 * @access protected
	 * @var    string, null by default
	 * @todo   Move to public scope, then abandon for $this->meta
	 */
	protected $title = null;

	/**
	 * Meta Description
	 *
	 * @access protected
	 * @var    string, null by default
	 * @todo   Move to public scope, then abandon for $this->meta
	 */
	protected $description = null;

	/**
	 * Meta Keywords (comma separated)
	 *
	 * @access protected
	 * @var    string, null by default
	 * @todo   Move to public scope, then abandon for $this->meta
	 */
	protected $keywords = null;

	/**
	 * Meta Data
	 *
	 * @var array
	 */
	public $meta = [
		'title'       => '',
		'description' => '',
		'keywords'    => '',
	];

	/**
	 * Secure
	 *
	 * Whether or not the page should be loaded via SSL.
	 *
	 * @var boolean defaults to false
	 */
	public $secure = false;

	/**
	 * Security Settings
	 *
	 * @access protected
	 * @var    boolean, null by default
	 * @todo   Move to public scope
	 */
	protected $security = null;

	/**
	 * AJAX
	 *
	 * Whether or not the module is being called via AJAX. This determines if
	 * errors should be returned as JSON or if it should use the Error class
	 * which can be interrogated from within a template.
	 *
	 * @access protected
	 * @var    boolean, false (not AJAX) by default
	 * @todo   Move to public scope
	 * @todo   Doesn't seem to be in use, but I have it defined on Clipinary
	 *         don't want to remove until I drop it else it would end up in the
	 *         module return array.
	 */
	protected $ajax = false;

	/**
	 * Method
	 *
	 * Request methods that are allowed to access the module.
	 *
	 * @access protected
	 * @var    string or array, null by default
	 * @todo   Move to public scope
	 */
	protected $method = null;

	/**
	 * Validate
	 *
	 * Variables to validate.
	 *
	 * @access protected
	 * @var    array, null by default
	 * @todo   Move to public scope
	 */
	protected $validate = null;

	/**
	 * Template
	 *
	 * This is the parent template that will be loaded if you are using the
	 * 'template' return type in the Display class. Parent templates are found
	 * in ./templates/__shared and use the phtml extension.
	 *
	 * @access protected
	 * @var    string, 'index' by default
	 */
	public $template = 'index';

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
	 * @todo   Move to public scope and rename __return so it's kinda obscured
	 * @todo   Will need to update leaderbin and sndcrd to use new variable
	 */
	protected $return = array();

	/**
	 * Output
	 *
	 * What should the class render as output? This can be a string or an array
	 * containing either 'json', 'rss', 'template' or 'xml'. Default is to use
	 * templates and if the template is not present, fall back to JSON.
	 *
	 * @var mixed string or array
	 */
	public $output = ['template', 'json'];

	/**
	 * Constructor
	 *
	 * The constructor does nothing by default but can be passed a boolean
	 * variable to tell it to automatically run the __default() method. This is
	 * typically used when a module is called outside of the scope of the
	 * controller (the registration page calls the login page in this manner.
	 *
	 * @param boolean $autorun optional flag to autorun __default()
	 * @param boolean $valiate optional flag to disable autorun validation
	 */
	public function __construct($autorun = false, $validate = true)
	{
		parent::__construct(['cache', 'db']);

		if ($autorun === true)
		{
			if ($validate === true)
			{
				$errors = $this->__validate();

				if ($errors !== false)
				{
					// @todo Fatal error perhaps?
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
	 * @todo  Ditch the $name check once everything is public
	 */
	public function __set($name, $value)
	{
		if ($name == 'method')
		{
			$this->method = $value;
		}
		else
		{
			$this->return[$name] = $value;
		}
	}

	/**
	 * Magic Getter Method
	 *
	 * Attempts to load the module variable. If it's not set, will attempt to
	 * load from the config.
	 *
	 * @param  string $name name of the variable requested
	 * @return mixed value of the variable or boolean false
	 * @todo   Unsure how necessary this will be moving forward, ideally would like to delete entirely
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
			if (is_array($this->method))
			{
				$this->method = $this->method[0];
			}

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
