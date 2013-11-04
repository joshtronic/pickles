<?php

/**
 * MySQL Class File for PICKLES
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
 * MySQL Database Abstraction Layer
 */
class Database_PDO_MySQL extends Database_PDO_Common
{
	/**
	 * Driver
	 *
	 * @var string
	 */
	public $driver = 'pdo_mysql';

	/**
	 * DSN format
	 *
	 * @var string
	 */
	public $dsn = 'mysql:host=[[hostname]];port=[[port]];unix_socket=[[socket]];dbname=[[database]]';

	/**
	 * Default port
	 *
	 * @var integer
	 */
	public $port = 3306;
}

?>
