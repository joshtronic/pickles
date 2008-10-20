<?php

/**
 * Common Display Class File for PICKLES
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
 * Common Display Class
 *
 * This is the class that each viewer class should be extending from.
 */
abstract class Display_Common extends Object {

	/**
	 * Constructor
	 *
	 * Runs the parent's constructor and adds the module to the object.
	 */
	public function __construct(Config $config, Error $error) {
		parent::__construct();

		$this->config = $config;
		$this->error  = $error;

		/**
		 * @todo This may need to be flipped on only for Smarty and PHP templates
		 */
		// Obliterates any passed in PHPSESSID (thanks Google)
		if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') !== false) {
			list($request_uri, $phpsessid) = split('\?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $request_uri);
			exit();
		}

		// XHTML compliancy stuff
		ini_set('arg_separator.output', '&amp;');
		ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

		header('Content-type: text/html; charset=UTF-8');

		if ($this->config->getDebug() === true) {
			?>
			<style>
				div.debug {
					border: 2px solid #000;
					padding: 5px;
					margin: 10px;
					background-color: #FFF;
					color: #000;
				}
			</style>
			<div class="debug">
				<h1>PICKLES Debug Console</h1><br />
				<?php
				foreach ($GLOBALS as $name => $array) {
					if (count($array) > 0 && $name != 'GLOBALS') {
						?>
						<h2>$<?=$name;?></h2>
						<?php
						var_dump($array);
						
						echo '<br />';
					}
				}
				?>
			</div>
			<?php

			/*
			function display_buffer() {
				$buffer = str_replace(
					array('    ', "\r\n", "\n", "\t"),
					null,
					ob_get_contents()
				);
				ob_end_clean();
				exit($buffer);
			}
			*/

		}
	}

	/**
	 * Abstract rendering function that is overloaded within the loaded viewer
	 */
	public abstract function render();

	public function prepare() { }
}

?>
