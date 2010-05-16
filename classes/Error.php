<?php

/**
 * Error Reporting for PICKLES
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
 * Error Class
 *
 * Standardized error reporting, mostly used to display fatal errors.
 */
class Error extends Object
{
	/**
	 * Fatal Error
	 *
	 * Displays a friendly error to the user via HTML, logs it then exits.
	 *
	 * @static
	 * @param  string $message the message to be displayed to the user
	 */
	public static function fatal($message)
	{
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<title>Error - <?=$_SERVER['SERVER_NAME'];?></title>
				<style>
					html{background:#eee;font-family:Verdana;width:100%}
					body{background:#ff9c9c;padding:20px;-moz-border-radius:20px;-webkit-border-radius:20px;width:550px;margin:0 auto;margin-top:100px;text-align:center;border:3px solid #890f0f}
					h1{font-size:1.5em;color:#600;text-shadow:#a86767 2px 2px 2px;margin:0}
				</style>
			</head>
			<body>
				<h1><?=$message;?></h1>
			</body>
		</html>
		<?php

		Log::error($message);

		exit;
	}

}

?>
