<?php

/**
 * PHP Viewer Class File for PICKLES
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
 * PHP Viewer
 *
 * Displays the associated PHP templates for the Model.  This is for all you
 * folks that would prefer not to user the Smarty templating engine.  Your
 * PHP templates are just PHP code, plain and simple.
 */
class Viewer_PHP extends Viewer_Common {

	/**
	 * Displays the Smarty generated pages
	 */
	public function display() {

		$smarty->template_dir = '../templates/';

		/**
		 * @todo Resurrect my buffer clean up code
		 */
		$smarty->load_filter('output','trimwhitespace');

		// Pass all of our controller values to Smarty
		$smarty->assign('section',    $this->model->section);
		$smarty->assign('model',      $this->model->name);
		$smarty->assign('template',   $template);

		// Loads the data from the config
		$data = $this->config->getViewerData();

		if (isset($data) && is_array($data)) {
			$smarty->assign('config', $data);
		}

		// Loads the data from the model
		$data = $this->model->getData();

		if (isset($data) && is_array($data)) {
			foreach ($data as $variable => $value) {
				$smarty->assign($variable, $value);
			}
		}

		// Load it up!
		header('Content-type: text/html; charset=UTF-8');

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
