<?php

/**
 * Logging System for PICKLES
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
 * Log Class
 *
 * Standardized logging methods for ease of reporting.
 */
class Log
{
	/**
	 * Log Information
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function information($message)
	{
		return self::write('information', $message);
	}

	/**
	 * Log Warning
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function warning($message)
	{
		return self::write('warning', $message);
	}

	/**
	 * Log Error
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function error($message)
	{
		return self::write('error', $message);
	}

	/**
	 * Log Slow Query
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function slowQuery($message)
	{
		return self::write('slow_query', $message);
	}

	/**
	 * Log Credit Card Transaction
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function transaction($message)
	{
		return self::write('transaction', $message);
	}

	/**
	 * Log PHP Error
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function phpError($message, $time = false)
	{
		return self::write('php_error', $message, false, $time);
	}

	/**
	 * Log SQL Query
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function query($message)
	{
		return self::write('query', $message);
	}

	/**
	 * Write Message to Log File
	 *
	 * @static
	 * @access private
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	private static function write($log_type, $message, $format = true, $time = false)
	{
		$config = Config::getInstance();

		if ($config->pickles['logging'] === true)
		{
			$log_path = LOG_PATH . date('Y/m/d/', ($time == false ? time() : $time));

			try
			{
				if (!file_exists($log_path))
				{
					mkdir($log_path, 0755, true);
				}

				$log_file = $log_path . $log_type . '.log';

				$message .= "\n";

				if ($format == true)
				{
					$backtrace = debug_backtrace();
					rsort($backtrace);
					$frame = $backtrace[strpos($backtrace[0]['file'], 'index.php') === false ? 0 : 1];

					return file_put_contents($log_file, date('H:i:s') . ' ' . str_replace(getcwd(), '', $frame['file']) . ':' . $frame['line'] . ' ' . $message, FILE_APPEND);
				}
				else
				{
					return file_put_contents($log_file, $message, FILE_APPEND);
				}
			}
			catch (ErrorException $exception)
			{
				return false;
			}
		}

		return false;
	}
}

?>
