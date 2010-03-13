<?php

/**
 * Logger Class File for PICKLES
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
