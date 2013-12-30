<?php

/**
 * Session Handling for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Session Class
 *
 * Provides session handling via database instead of the file based session
 * handling built into PHP. Using this class requires an array to be
 * defined in place of the boolean true/false (on/off). If simply array(),
 * the datasource will default to the value in
 * $config['pickles']['datasource'] and if the table will default to
 * "sessions". The format is as follows:
 *
 *     $config = array(
 *         'pickles' => array(
 *             'session' => array(
 *                 'datasource' => 'mysql',
 *                 'table'      => 'sessions',
 *             )
 *         )
 *     );
 *
 * In addition to the configuration variables, a table in your database
 * must be created. The [MySQL] table schema is as follows:
 *
 *     CREATE TABLE sessions (
 *         id varchar(32) COLLATE utf8_unicode_ci NOT NULL,
 *         session text COLLATE utf8_unicode_ci NOT NULL,
 *         expires_at datetime NOT NULL,
 *         PRIMARY KEY (id),
 *         INDEX (expires_at)
 *     ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 *
 * Note: The reason for not using a model class was to avoid a naming
 * conflict between the Session model and the Session class itself. This
 * will eventually be resolved when I abandon full 5.x support and migrate
 * to 5.3+ (assuming that ever happens).
 */
class Session extends Object
{
	/**
	 * Handler
	 *
	 * What the session is being handled by.
	 *
	 * @access private
	 * @var    string
	 */
	private $handler = false;

	/**
	 * Accessed At
	 *
	 * The UNIX timestamp of when the page was accessed.
	 *
	 * @access private
	 * @var    integer
	 */
	private $accessed_at = null;

	/**
	 * Time to Live
	 *
	 * The number of seconds the session should remain active. Corresponds
	 * to the INI variable session.gc_maxlifetime
	 *
	 * @access private
	 * @var    integer
	 */
	private $time_to_live = null;

	/**
	 * Datasource
	 *
	 * Name of the datasource, defaults to whatever the default datasource
	 * is defined to in config.php
	 *
	 * @access private
	 * @var    string
	 */
	private $datasource = null;

	/**
	 * Table
	 *
	 * Name of the database table in the aforementioned datasource that
	 * holds the session data. The expected schema is defined above.
	 *
	 * @access private
	 * @var    string
	 */
	private $table = null;

	/**
	 * Constructor
	 *
	 * All of our set up logic for the session in contained here. This
	 * object is initially instantiated from pickles.php and the session
	 * callbacks are established here. All variables are driven from
	 * php.ini and/or the site config. Once configured, the session is
	 * started automatically.
	 */
	public function __construct()
	{
		if (!IS_CLI)
		{
			parent::__construct();

			// Sets up our configuration variables
			if (isset($this->config->pickles['session']))
			{
				$session = $this->config->pickles['session'];
				$version = 1;
			}

			if (isset($this->config->pickles['sessions']))
			{
				$session = $this->config->pickles['sessions'];
				$version = 2;
			}

			$datasources = $this->config->datasources;

			$this->handler = 'files';
			$datasource    = false;
			$table         = 'sessions';

			if (isset($datasources[$session]))
			{
				$datasource    = $datasources[$session];
				$this->handler = $datasource['type'];

				if (isset($datasource['hostname'], $datasource['port']))
				{
					$host = 'tcp://' . $datasource['hostname'] . ':' . $datasource['port'];
				}
			}

			switch ($this->handler)
			{
				case 'memcache':
					ini_set('session.save_handler', 'memcache');
					ini_set('session.save_path',    $host . '?persistent=1&amp;weight=1&amp;timeout=1&amp;retry_interval=15');
					break;

				// @todo memcached

				case 'mysql':
					// Sets our access time and time to live
					$this->accessed_at  = time();
					$this->time_to_live = ini_get('session.gc_maxlifetime');

					$this->datasource = $datasource;
					$this->table      = $table;

					// Gets a database instance
					$this->db = Database::getInstance($this->datasource);

					// Initializes the session
					$this->initialize();
					break;

				case 'redis':
					// Keep in mind that the database value is ignored by phpredis
					$save_path = $host . '?weight=1'
					           . (isset($datasource['database']) ? '&database=' . $datasource['database'] : '')
					           . (isset($datasource['prefix']) ? '&prefix=' . $datasource['prefix'] : '');

					ini_set('session.save_handler', 'redis');
					ini_set('session.save_path',    $save_path);
					break;

				default:
				case 'files':
					ini_set('session.save_handler', 'files');
					break;
			}

			if (isset($_SERVER['HTTP_USER_AGENT'])
				&& !String::isEmpty($_SERVER['HTTP_USER_AGENT'])
				&& !preg_match('/(Baidu|Gigabot|Googlebot|libwww-perl|lwp-trivial|msnbot|SiteUptime|Slurp|WordPress|ZIBB|ZyBorg)/i', $_SERVER['HTTP_USER_AGENT']))
			{
				session_start();
			}
		}
	}

	/**
	 * Destructor
	 *
	 * Runs garbage collection and closes the session. I'm not sure if the
	 * garbage collection should stay as it could be accomplished via
	 * php.ini variables. The session_write_close() is present to combat a
	 * chicken and egg scenario in earlier versions of PHP 5.
	 */
	public function __destruct()
	{
		if ($this->handler == 'mysql')
		{
			$this->gc($this->time_to_live);
			session_write_close();
		}
	}

	/**
	 * Initializes the Session
	 *
	 * This method exists to combat the fact that calling session_destroy()
	 * also clears out the save handler. Upon destorying a session this
	 * method is called again so the save handler is all set.
	 */
	public function initialize()
	{
		// Sets up the session handler
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);

		register_shutdown_function('session_write_close');
	}

	/**
	 * Opens the Session
	 *
	 * Since the session is in the database, opens the database connection.
	 * This step isn't really necessary as the Database object is smart
	 * enough to open itself up upon execute.
	 */
	public function open()
	{
		session_regenerate_id();

		return $this->db->open();
	}

	/**
	 * Closes the Session
	 *
	 * Same as above, but in reverse.
	 */
	public function close()
	{
		return $this->db->close();
	}

	/**
	 * Reads the Session
	 *
	 * Checks the database for the session ID and returns the session data.
	 *
	 * @param  string $id session ID
	 * @return string serialized session data
	 */
	public function read($id)
	{
		$sql = 'SELECT session FROM `' . $this->table . '` WHERE id = ?;';

		$session = $this->db->fetch($sql, array($id));

		return isset($session[0]['session']) ? $session[0]['session'] : '';
	}

	/**
	 * Writes the Session
	 *
	 * When there's changes to the session, writes the data to the
	 * database.
	 *
	 * @param  string $id session ID
	 * @param  string $session serialized session data
	 * @return boolean whether the query executed correctly
	 */
	public function write($id, $session)
	{
		$sql = 'REPLACE INTO `' . $this->table . '` VALUES (?, ? ,?);';

		$parameters = array($id, $session, date('Y-m-d H:i:s', strtotime('+' . $this->time_to_live . ' seconds')));

		return $this->db->execute($sql, $parameters);
	}

	/**
	 * Destroys the Session
	 *
	 * Deletes the session from the database.
	 *
	 * @param  string $id session ID
	 * @return boolean whether the query executed correctly
	 */
	public function destroy($id)
	{
		$sql = 'DELETE FROM `' . $this->table . '` WHERE id = ?;';

		return $this->db->execute($sql, array($id));
	}

	/**
	 * Garbage Collector
	 *
	 * This is who you call when you got trash to be taken out.
	 *
	 * @param  integer $time_to_live number of seconds a session is active
	 * @return boolean whether the query executed correctly
	 */
	public function gc($time_to_live)
	{
		$sql = 'DELETE FROM `' . $this->table . '` WHERE expires_at < ?;';

		$parameters = array(date('Y-m-d H:i:s', $this->accessed_at - $time_to_live));

		return $this->db->execute($sql, $parameters);
	}
}

?>
