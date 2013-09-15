<?php

/**
 * Error Reporting for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Error Class
 *
 * Standardized error reporting, mostly used to display fatal errors.
 */
class Error
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
		$config = Config::getInstance();

		if ($config->pickles['logging'] === true)
		{
			if (Log::error($message) == false)
			{
				$message .= '<br><br>This error message could not be logged as the log path or log file is not writable';
			}
		}
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<title><?php echo $_SERVER['SERVER_NAME']; ?> - error</title>
				<style>
					html
					{
						background: #eee;
						font-family: "Lucida Sans", "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, sans-serif;
						width: 100%;
						height: 100%;
						font-size: 1em;
					}
					body
					{
						text-align: center;
						margin-top: 100px;
					}
					div
					{
						font-size: 150%;
						color: #600;
						text-shadow: 2px 2px 2px #eb8383;
						margin: 0;
						font-weight: bold;
						background: #ff9c9c;
						padding: 20px;
						border-radius: 20px;
						-moz-border-radius: 20px;
						-webkit-border-radius: 20px;
						width: 550px;
						margin: 0 auto;
						border: 3px solid #890f0f;
					}
					h1, a
					{
						font-size: 70%;
						color: #999;
						text-decoration: none;
					}
					a:hover
					{
						color: #000;
					}
				</style>
			</head>
			<body>
				<h1><?php echo $_SERVER['SERVER_NAME']; ?></h1>
				<div><?php echo $message; ?></div>
				<a href="https://github.com/joshtronic/pickles" target="_blank">Powered by PICKLES</a>
			</body>
		</html>
		<?php

		exit;
	}

}

?>
