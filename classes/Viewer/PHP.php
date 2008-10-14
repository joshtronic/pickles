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
 * Displays the associated PHP templates for the Model.  This is
 * very similar to the Smarty viewer, but less overhead since it's
 * straight PHP.  The PHP viewer also utilizes a different caching
 * system than Smarty.  The general rules around the caching will
 * be the same though.
 */
class Viewer_PHP extends Viewer_Common {

	private $template        = null;
	private $shared_template = null;

	/**
	 * Displays the PHP templated pages
	 */
	public function display() {

		// Establishes the template names
		$this->template        = SITE_PATH . '../templates/' . $this->model_name . '.php';
		$this->shared_template = PICKLES_PATH . 'templates/' . $this->shared_name . '.php';		

		/**
		 * @todo There's a bug with the store home page since it's a redirect, maybe
		 */
		if (!file_exists($this->template)) {
			if (file_exists($this->shared_template)) {
				$this->template = $this->shared_template;
			}
		}

		// Brings these variables to scope
		/**
		 * @todo Section or model needs to go, having both seems dumb.
		 */
		$section  = $this->model->section;
		$model    = $this->model->name;
		$template = $template;

		// Loads the data from the config
		$config = $this->config->getPublicData();

		// Loads the data from the model
		$data = $this->model->getData();

		// If there's data set, this brings it into scope
		if (isset($this->data) && is_array($this->data)) {
			extract($this->data);
		}

		// If the index.php file is present, load it, else load the template directly
		/**
		 * @todo Should there be additional logic to allow the model or the
		 *       template to determine whether or not the index should be loaded?
		 */
		if (file_exists(SITE_PATH . '../templates/index.php')) {
			require_once SITE_PATH . '../templates/index.php';
		}
		else if (file_exists{$this->template)) {
			require_once $this->template;
		}

		/**
		 * @todo Resurrect my buffer clean up code
		 */
	}
}

?>
