<?php

/**
 * PHP Display Class File for PICKLES
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
 * PHP Display
 *
 * Displays the associated PHP templates for the Model.  This is very
 * similar to the Smarty viewer, but less overhead since it's straight PHP.
 * The PHP viewer also utilizes a different caching system than Smarty.
 * The general rules around the caching will be the same though.
 */
class Display_PHP extends Display_Common
{
	/**
	 * Template Extension
	 *
	 * I know there's some controversy amoungst my peers concerning the
	 * usage of the .phtml extension for these PHP template files. If you
	 * would prefer .php or .tpl extensions, feel free to void your
	 * warranty and change it here.
	 *
	 * @access protected
	 * @var    string $extension file extension for the template files
	 */
	protected $extension = 'phtml';

	/**
	 * Renders the PHP templated pages
	 */
	public function render()
	{
		// Starts up the buffer
		ob_start();

		// Puts the module return data in a local variable
		$module = $this->module_return;

		// Loads the template
		if ($this->template)
		{
			require_once $this->template;
		}

		// Grabs the buffer contents and clears it out
		$buffer = ob_get_clean();

		// Minifies the output
		// @todo Need to add logic to not minify blocks of CSS or Javascript
		//$buffer = str_replace(array('    ', "\r\n", "\n", "\t"), null, $buffer);
		$buffer = str_replace(array('    ', "\t"), null, $buffer);

		// Spits out the minified buffer
		echo $buffer;
	}
}

?>
