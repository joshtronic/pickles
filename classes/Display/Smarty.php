<?php

/**
 * Smarty Display Class File for PICKLES
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
 * Smarty Display
 *
 * Displays the associated Smarty templates for the Module.
 *
 * @link http://smarty.net/
 */
class Display_Smarty extends Display_Common
{
	/**
	 * Instance of Smarty
	 *
	 * @access private
	 * @var    object, defaults to null
	 */
	private $smarty = null;

	/**
	 * Template Extension
	 *
	 * @access protected
	 * @var    string
	 */
	protected $extension = 'tpl';

	/**
	 * Render the Smarty generated pages
	 */
	public function render()
	{
		$this->smarty = new Smarty();

		// Establishes our paths
		$this->smarty->template_dir = TEMPLATE_PATH;

		$cache_dir   = SMARTY_PATH . 'cache';
		$compile_dir = SMARTY_PATH . 'compile';

		if (!file_exists($cache_dir))
		{
			mkdir($cache_dir, 0777, true);
		}

		if (!file_exists($compile_dir))
		{
			mkdir($compile_dir, 0777, true);
		}

		$this->smarty->cache_dir   = $cache_dir ;
		$this->smarty->compile_dir = $compile_dir;

		// Loads the trim whitespace filter
		$this->smarty->load_filter('output', 'trimwhitespace');

		/*
		// @todo No functions to load currently
		// Includes the PICKLES custom Smarty functions
		$directory = PICKLES_PATH . 'functions/smarty/';

		if (is_dir($directory)) {
			if ($handle = opendir($directory)) {
				while (($file = readdir($handle)) !== false) {
					if (!preg_match('/^\./', $file)) {
						list($type, $name, $ext) = split('\.', $file);
						require_once $directory . $file;
						$this->smarty->register_function($name, "smarty_{$type}_{$name}");
					}
				}
				closedir($handle);
			}
		}
		*/

		// Assigns the variables and loads the template
		if (is_array($this->templates))
		{
			$this->smarty->assign('config', $this->config);
			$this->smarty->assign('module', $this->module_return);
			//$this->smarty->assign('template', strtr($this->template, '-', '_'));

			// Assigns the template variable if there's more than one template
			if (isset($this->templates[1]))
			{
				$this->smarty->assign('template', $this->templates[1]);
			}

			$this->smarty->display($this->templates[0]);
		}
	}
}

?>
