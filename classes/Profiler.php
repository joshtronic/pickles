<?php

/**
 * Profiler 
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
 * Profiler Class
 *
 * The Profiler class is statically interfaced with and allows for in depth
 * profiling of a site. By default profiling is off, but can be enabled in the
 * config.ini for a site. Out of the box the profiler will report on every
 * class object in the system that extends the code Object class.
 *
 * Note: I really wanted to use PHP Quick Profiler by Ryan Campbell of
 * Particletree but it kept barking out errors when I tried to use it. Here's
 * a link anyway: http://particletree.com/features/php-quick-profiler/
 *
 * @usage <code>Profiler::log('started stuff');</code>
 */
class Profiler
{
	/**
	 * Profile
	 *
	 * Array of logged events
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $profile = array();

	/**
	 * Constructor
	 *
	 * Private constructor since this class is interfaced wtih statically.
	 *
	 * @access private
	 */
	private function __construct()
	{

	}

	/**
	 * Log
	 *
	 * Logs the event to be displayed later on. Due to the nature of how much
	 * of a pain it is to determine which class method called this method I
	 * opted to make the method a passable argument for ease of use. Perhaps
	 * I'll revisit in the future. Handles all elapsed time calculations and
	 * memory usage.
	 *
	 * @static
	 * @param  mixed $data data to log
	 * @param  string $method name of the class method being logged
	 */
	public static function log($data, $method = false)
	{
		$time = microtime(true);
		$type = gettype($data);

		// Tidys the data by type
		switch ($type)
		{
			case 'array':
				$log = '<pre>' . print_r($data, true) . '</pre>';
				break;
	
			case 'object':
				$log = 'object: <span style="color:#69c">' . get_class($data) . '</span>'
					. ($method != '' ? '<span style="color:#666">-></span><span style="color:#4eed9e">' . $method . '</span><span style="color:#666">()</span>' : '');
				break;

			case 'string':
			default:
				$log = $data;
				break;
		}

		self::$profile[] = array(
			'log'     => $log,
			'type'    => gettype($data),
			'time'    => $time,
			'elapsed' => $time - PICKLES_START_TIME,
			'memory'  => memory_get_usage(),
		);
	}

	/**
	 * Report
	 *
	 * Generates the Profiler report that is displayed by the Controller.
	 * Contains all the HTML needed to display the data properly inline on the
	 * page. Will generally be displayed after the closing HTML tag.
	 */
	public static function report()
	{
		$start_time = PICKLES_START_TIME;
		$end_time   = self::$profile[count(self::$profile) - 1]['time'];
		$duration   = ($end_time - $start_time) * 100;

		?>
		<style>
			#pickles-profiler
			{
				background: #212121;
				width: 600px;
				margin: 0 auto;
				margin-top: 20px;
				margin-bottom: 20px;
				border-radius: 20px;
				-moz-border-radius: 20px;
				-webkit-border-radius: 20px;
				box-shadow: 0 0 8px #999;
				-moz-box-shadow: 0 0 8px #999;
				-webkit-box-shadow: 0 0 8px #999;
				padding: 10px 20px 20px;
				font-family: monospace;
				font-size: 1em;
			}
			#pickles-profiler table
			{
				width: 100%;
			}
			#pickles-profiler table tr th, #pickles-profiler table tr td
			{
				padding: 10px;
			}
			#pickles-profiler .odd
			{
				background-color: #323232;
			}
		</style>
		<div id="pickles-profiler">
			<table>
				<tr>
					<th style="text-align:left">PICKLES Profiler</th>
					<th style="text-align:right">Memory</th>
					<th style="text-align:right">Time</th>
				</tr>
				<?php
				$units = array('bytes', 'kB', 'MB', 'GB');

				foreach (self::$profile as $key => $entry)
				{
					?>
					<tr class="<?php echo $key % 2 == 1 ? 'even' : 'odd'; ?>">
						<td><?php echo $entry['log']; ?></td>
						<td style="text-align:right"><?php echo round($entry['memory'] / pow(1024, ($i = floor(log($entry['memory'], 1024)))), 2) . ' ' . $units[$i]; ?></td>
						<td style="text-align:right"><?php echo round($entry['elapsed'] * 100, 2); ?> ms</td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
		<br /><br />
		<?php
	}
}

?>
