<?php

/**
 * Common Display Class File for PICKLES
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
 * Common Display Class
 *
 * This is the parent class class that each display class should be
 * extending and executing parent::render()
 */
abstract class Display_Common extends Object
{
	/**
	 * Templates
	 *
	 * @access protected
	 * @var    string
	 */
	protected $templates = null;

	/**
	 * Template Extension
	 *
	 * @access protected
	 * @var    string
	 */
	protected $extension = false;

	/**
	 * CSS Class Name
	 *
	 * @access protected
	 * @var    string
	 */
	protected $css_class = '';

	/**
	 * Javascript [Path and] Basename
	 *
	 * @access protected
	 * @var    array
	 */
	protected $js_basename = '';

	/**
	 * Module Return Data
	 *
	 * @access protected
	 * @var    array
	 */
	protected $module_return = null;

	/**
	 * Constructor
	 *
	 * Runs the parent's constructor and adds the module to the object.
	 */
	public function __construct()
	{
		parent::__construct();

		// Obliterates any passed in PHPSESSID (thanks Google)
		if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') !== false)
		{
			list($request_uri, $phpsessid) = split('\?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $request_uri);
			exit();
		}
		else
		{
			// XHTML compliancy stuff
			ini_set('arg_separator.output', '&amp;');
			ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

			header('Content-type: text/html; charset=UTF-8');

			// Loops through each passed template and variables it
			foreach (func_get_args() as $template)
			{
				$template = TEMPLATE_PATH . $template . ($this->extension != false ? '.' . $this->extension : '');

				if (file_exists($template) && is_file($template) && is_readable($template))
				{
					$this->templates[] = $template;
				}
			}
		}
	}

	/**
	 * Template Exists
	 *
	 * @return integer the number of templates defined
	 */
	public function templateExists()
	{
		return count($this->templates);
	}
	 
	/**
	 * Preparation Method
	 *
	 * @param array $css_class name of the CSS class name for this module
	 * @param array $js_basename path and basename of the module's JS file
	 * @param array $module_return data returned by the module
	 */
	public function prepare($css_class, $js_basename, $module_return)
	{
		$this->css_class     = $css_class;
		$this->js_basename   = $js_basename;
		$this->module_return = $module_return;
	}

	/**
	 * Rendering Method
	 *
	 * @abstract
	 */
	abstract public function render();
}

?>
