<?php

/**
 * Session Handling for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Session Class
 *
 * Provides session handling via database instead of the file based session
 * handling built into PHP. Using this class requires an array to be defined
 * in place of the boolean true/false (on/off). If simply an empty array, the
 * datasource will default to the value in $config['pickles']['datasource'] and
 * if the table will default to "sessions". The format is as follows:
 */
class Session extends Object
{
	/**
	 * Constructor
	 *
	 * All of our set up logic for the session in contained here. This class is
	 * initially instantiated from pickles.php. Non-file handlers need to be
	 * configured in the site's config. MySQL support was dropped in favor of
	 * in memory stores or simply relying on file based sessions. Why? Because
	 * using MySQL for sessions is very write intensive and having done it in
	 * the past I don't recommend it. If you run a single server, files are
	 * good enough if your volume is lower. Memcache[d] is fine if you don't
	 * mind logging all of your users off your site when you restart the
	 * service and/or you run out of memory for the process. Redis is the best
	 * choice as it can be configured to be persistent and lives in memory.
	 * This is assuming you don't just want to roll your own sessions, which is
	 * pretty damn easy as well.
	 */
	public function __construct()
	{
		if (isset($_SERVER['REQUEST_METHOD']))
		{
			parent::__construct();

			// Sets up our configuration variables
			if (isset($this->config->pickles['sessions']))
			{
				$session = $this->config->pickles['sessions'];
			}

			$datasources = $this->config->datasources;
			$handler     = 'files';
			$datasource  = false;

			if (isset($session, $datasources[$session]))
			{
				$datasource = $datasources[$session];
				$handler    = $datasource['type'];

				if ($handler != 'files')
				{
					if (isset($datasource['hostname'], $datasource['port']))
					{
						$host = ($handler != 'memcached' ? 'tcp://' : '')
						      . $datasource['hostname'] . ':' . $datasource['port'];
					}
					else
					{
						throw new Exception('You must provide both the hostname and port for the datasource.');
					}
				}
			}

			switch ($handler)
			{
				case 'memcache':
					ini_set('session.save_handler', 'memcache');
					ini_set('session.save_path',    $host . '?persistent=1&amp;weight=1&amp;timeout=1&amp;retry_interval=15');
					break;

				case 'memcached':
					ini_set('session.save_handler', 'memcached');
					ini_set('session.save_path',    $host);
					break;

				case 'redis':
					$save_path = $host . '?weight=1';

					// Database ignored by phpredis when this was coded
					if (isset($datasource['database']))
					{
						$save_path .= '&database=' . $datasource['database'];
					}

					if (isset($datasource['prefix']))
					{
						$save_path .= '&prefix=' . $datasource['prefix'];
					}

					ini_set('session.save_handler', 'redis');
					ini_set('session.save_path',    $save_path);
					break;

				case 'files':
					ini_set('session.save_handler', 'files');
					break;
			}

			// Don't start sessions for people without a user agent and bots.
			if (isset($_SERVER['HTTP_USER_AGENT'])
				&& !String::isEmpty($_SERVER['HTTP_USER_AGENT'])
				&& !preg_match('/(Baidu|Gigabot|Googlebot|libwww-perl|lwp-trivial|msnbot|SiteUptime|Slurp|WordPress|ZIBB|ZyBorg)/i', $_SERVER['HTTP_USER_AGENT']))
			{
				session_start();
			}
		}
	}
}

?>
