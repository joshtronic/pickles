<?php

/**
 * Smarty Viewer Class File for PICKLES
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
 * @copyright Copyright 2007, 2008 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Smarty Viewer
 *
 * Displays the associated Smarty templates for the Model.
 *
 * @link       http://smarty.net/
 */
class Viewer_Smarty extends Viewer_Common {

	/**
	 * Displays the Smarty generated pages
	 */
	public function display() {

		$smarty = new Smarty();

		// Establishes our paths
		$smarty->template_dir = SITE_PATH . '../templates/';

		$cache_dir   = SMARTY_PATH . 'cache';
		$compile_dir = SMARTY_PATH . 'compile';

		if (!file_exists($cache_dir))   { mkdir($cache_dir,   0777, true); }
		if (!file_exists($compile_dir)) { mkdir($compile_dir, 0777, true); }

		$smarty->cache_dir   = $cache_dir ;
		$smarty->compile_dir = $compile_dir;

		// Loads the trim whitespace filter
		$smarty->load_filter('output','trimwhitespace');

		// Includes the PICKLES custom Smarty functions
		$directory = PICKLES_PATH . 'functions/smarty/';

		if (is_dir($directory)) {
			if ($handle = opendir($directory)) {
				while (($file = readdir($handle)) !== false) {
					if (!preg_match('/^\./', $file)) {
						list($type, $name, $ext) = split('\.', $file);
						require_once $directory . $file;
						$smarty->register_function($name, "smarty_{$type}_{$name}");
					}
				}
				closedir($handle);
			}
		}

		// Establishes the template names
		$template        = SITE_PATH . '../templates/' . $this->model_name . '.tpl';
		$shared_template = PICKLES_PATH . 'templates/' . $this->shared_name . '.tpl';

		/**
		 * @todo There's a bug with the store home page since it's a redirect
		 */
		if (!file_exists($template)) {
			if (file_exists($shared_template)) {
				$template = $shared_template;
			}
		}

		// Pass all of our controller values to Smarty
		$smarty->assign('section',    $this->section);
		$smarty->assign('model',      $this->model_name);
		$smarty->assign('template',   $template);

		// Loads the data from the config
		$data = $this->config->getPublicData();
		
		if (isset($data) && is_array($data)) {
			$smarty->assign('config', $data);
		}

		// Loads the model's data
		if (isset($this->data) && is_array($this->data)) {
			foreach ($this->data as $variable => $value) {
				$smarty->assign($variable, $value);
			}
		}

		/**
		 * @todo There's no error checking for the index... should it be shared,
		 *       and should the error checking occur anyway since any shit could
		 *       happen?
		 */
		/*
		$template        = '../templates/index.tpl';
		$shared_template = str_replace('../', '../../pickles/', $template);

		if (!file_exists($template)) {
			if (file_exists($shared_template)) {
				$template = $shared_template;
			}
		}
		*/

		// If the index.tpl file is present, load it, else load the template directly
		/**
		 * @todo Should there be additional logic to allow the model or the
		 *       template to determine whether or not the index should be loaded?
		 */
		if ($smarty->template_exists('index.tpl')) {
			$smarty->display('index.tpl');
		}
		else {
			$smarty->display($template);
		}
	}
}

?>
