<?php

/**
 * Smarty Display Class File for PICKLES
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
 * @copyright Copyright 2007, 2008, 2009 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Smarty Display
 *
 * Displays the associated Smarty templates for the Model.
 *
 * @link http://smarty.net/
 */
class Display_Smarty extends Display_Common {

	private $smarty = null;

	public function __construct(Config $config, Error $error) {
		parent::__construct($config, $error);

		$this->smarty = new Smarty();

		// Establishes our paths
		$this->smarty->template_dir = SITE_PATH . '../templates/';

		$cache_dir   = SMARTY_PATH . 'cache';
		$compile_dir = SMARTY_PATH . 'compile';

		if (!file_exists($cache_dir))   { mkdir($cache_dir,   0777, true); }
		if (!file_exists($compile_dir)) { mkdir($compile_dir, 0777, true); }

		$this->smarty->cache_dir   = $cache_dir ;
		$this->smarty->compile_dir = $compile_dir;
	}

	public function prepare() {
		
		// Enables caching
		if ($this->caching === true) {
			$this->smarty->caching       = 1;
			$this->smarty->compile_check = true;

			if (is_numeric($this->caching)) {
				$this->smarty->cache_lifetime = $this->caching;
			}
		}

		// Loads the trim whitespace filter
		$this->smarty->load_filter('output', 'trimwhitespace');

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
	}

	/**
	 * Render the Smarty generated pages
	 */
	public function render() {

		// Establishes the template names
		$template = SITE_PATH . '../templates/' . $this->module_filename . '.tpl';

		if (!file_exists($template)) {
			$shared_template = PICKLES_PATH . 'common/templates/' . ($this->shared_filname == false ? $this->module_filename : $this->shared_filename) . '.tpl';

			if (file_exists($shared_template)) {
				$template = $shared_template;
			}
		}

		$this->template = $template;

		$cache_id = isset($this->cache_id) ? $this->cache_id : $this->module_filename;
		
		$template = $this->smarty->template_exists('index.tpl') ? 'index.tpl' : $this->template;

		if (!$this->smarty->is_cached($template, $cache_id)) {

			// Build the combined module name array and assign it
			$module_name = split('/', $this->module_name);
			array_unshift($module_name, $this->module_name);
			$this->smarty->assign('module_name', $module_name);

			// Only assign the template if it's not the index, this avoids an infinite loop.
			if ($this->template != 'index.tpl') {
				$this->smarty->assign('template', strtr($this->template, '-', '_'));
			}

			// Loads the data from the config
			$data = $this->config->getPublicData();

			if (isset($data) && is_array($data)) {
				$this->smarty->assign('config', $data);
			}

			// Loads the module's data
			// @todo remove me!
			// if (isset($this->data) && is_array($this->data)) {
			// 	foreach ($this->data as $variable => $value) {
			// 		$this->smarty->assign($variable, $value);
			// 	}
			// }

			// Loads the module's public data
			// @todo For refactoring, need to change the name from data
			if (isset($this->data) && is_array($this->data)) {
				$this->smarty->assign('module', $this->data);
			}
		}

		$this->smarty->display($template, $cache_id);
	}

	public function getSmartyObject() {
		return $this->smarty;
	}
}

?>
