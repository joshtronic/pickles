<?php

/**
 * Logger Class File for PICKLES
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
 * Logger Class
 */
class Logger extends Object {

	public static function write($type, $message, $class = null) {
		if (!file_exists(LOG_PATH)) { mkdir(LOG_PATH, 0777, true); }

		$message = '[' . date('r') . '] '
		         . (trim($_SERVER['REMOTE_ADDR']) != '' ? '[client ' . $_SERVER['REMOTE_ADDR'] . '] ' : null)
		         . (trim($_SERVER['REQUEST_URI']) != '' ? '[uri ' . $_SERVER['REQUEST_URI'] . '] ' : null)
				 . '[script ' . $_SERVER['SCRIPT_NAME'] . '] ' . $message;

		file_put_contents(LOG_PATH . $type . '.log', $message . "\n", FILE_APPEND);
	}
}

?>
