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
 * This is the class that each viewer class should be extending from.
 *
 * @abstract
 */
abstract class Display_Common extends Object
{
	/**
	 * Template
	 *
	 * @access protected
	 * @var    string
	 */
	protected $template = null;

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
	public function __construct($template, $module_return)
	{
		parent::__construct();

		// @todo This may need to be flipped on only for Smarty and PHP templates
		// Obliterates any passed in PHPSESSID (thanks Google)
		if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') !== false)
		{
			list($request_uri, $phpsessid) = split('\?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $request_uri);
			exit();
		}

		// XHTML compliancy stuff
		ini_set('arg_separator.output', '&amp;');
		ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

		// @todo Uncomment or remove
		//header('Content-type: text/html; charset=UTF-8');

		$this->template      = $template;
		$this->module_return = $module_return;
	}

	/**
	 * Abstract rendering function that is overloaded within the loaded viewer
	 *
	 * @abstract
	 */
	public abstract function render();

	/**
	 * Preparation for display
	 */
	public function prepare()
	{
		
	}
}

?>
