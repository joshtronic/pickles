<?php

/**
 * PostgreSQL Class File for PICKLES
 *
 * PHP version 5.3+
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * PostgreSQL Database Abstraction Layer
 */
class Database_PDO_PostgreSQL extends Database_PDO_Common
{
	/**
	 * Driver
	 *
	 * @var string
	 */
	public $driver = 'pdo_pgsql';

	/**
	 * DSN format
	 *
	 * @var string
	 */
	public $dsn = 'pgsql:host=[[hostname]];port=[[port]];dbname=[[database]];user=[[username]];password=[[password]]';

	/**
	 * Default port
	 *
	 * @var integer
	 */
	public $port = 5432;
}

?>
