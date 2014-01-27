<?php

/**
 * Common Display Class File for PICKLES
 *
 * PHP version 5.3+
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
	 * Template Extension
	 *
	 * @access protected
	 * @var    string $extension file extension for the template files
	 */
	protected $extension = null;

	/**
	 * Parent Template
	 *
	 * @access protected
	 * @var    string
	 */
	protected $parent_template = null;

	/**
	 * Child (sub) Template
	 *
	 * @access protected
	 * @var    string
	 */
	protected $child_template = null;

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
	 * Meta Data
	 *
	 * @access protected
	 * @var    array
	 */
	protected $meta_data = null;

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
	 * Gets those headers working
	 */
	public function __construct()
	{
		parent::__construct();

		// Obliterates any passed in PHPSESSID (thanks Google)
		if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') !== false)
		{
			list($request_uri, $phpsessid) = explode('?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $request_uri);
			exit;
		}
		else
		{
			// XHTML compliancy stuff
			ini_set('arg_separator.output', '&amp;');
			ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

			header('Content-type: text/html; charset=UTF-8');
		}
	}

	/**
	 * Set Template
	 *
	 * Sets the template file based on passed template type. Adds path and
	 * extension if applicable.
	 *
	 * @param string $template template file's basename
	 * @param string $type template file's type (either parent or child)
	 */
	private function setTemplate($template, $type)
	{
		if ($template != null)
		{
			$template_name = $type . '_template';
			$template_path = SITE_TEMPLATE_PATH . ($type == 'parent' ? '__shared/' : '');
			$template_file = $template_path . $template . ($this->extension != false ? '.' . $this->extension : '');

			if (file_exists($template_file))
			{
				$this->$template_name = $template_file;
			}
		}
	}

	/**
	 * Set Template Variables
	 *
	 * Sets the variables used by the templates
	 *
	 * @param string $parent_template parent template
	 * @param string $child_template child (sub) template
	 * @param string $css_class name of the CSS class for the module
	 * @param string $js_basename basename for the javascript file for the module
	 * @param boolean $fluid whether or not use a fluid layout
	 */
	public function setTemplateVariables($parent_template, $child_template, $css_class, $js_basename, $fluid)
	{
		$this->setTemplate($parent_template, 'parent');
		$this->setTemplate($child_template,  'child');

		$this->css_class   = $css_class;
		$this->js_basename = $js_basename;
		$this->fluid       = $fluid;
	}

	/**
	 * Set Meta Data
	 *
	 * Sets the meta data from the module so the display class can use it
	 *
	 * @param array $meta_data key/value array of data
	 */
	public function setMetaData($meta_data)
	{
		$this->meta_data = $meta_data;
	}

	/**
	 * Set Module Return
	 *
	 * Sets the return data from the module so the display class can display it
	 *
	 * @param array $module_return key / value pairs for the data
	 */
	public function setModuleReturn($module_return)
	{
		$this->module_return = $module_return;
	}

	/**
	 * Template Exists
	 *
	 * Checks the templates for validity, not required by every display type so
	 * the return defaults to true.
	 *
	 * @return boolean whether or not the template exists
	 */
	public function templateExists()
	{
		return true;
	}

	/**
	 * Rendering Method
	 *
	 * @abstract
	 */
	abstract public function render();
}

?>
