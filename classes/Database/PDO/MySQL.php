<?php

/**
 * MySQL Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * MySQL Database Abstraction Layer
 */
class Database_PDO_MySQL extends Database_PDO_Common
{
	/**
	 * Driver
	 *
	 * @access protected
	 * @var    string
	 */
	protected $driver = 'pdo_mysql';

	/**
	 * DSN format
	 *
	 * @access protected
	 * @var    string
	 */
	protected $dsn = 'mysql:host=[[hostname]];port=[[port]];unix_socket=[[socket]];dbname=[[database]]';

	/**
	 * Default port
	 *
	 * @access proceted
	 * @var    integer
	 */
	protected $port = 3306;
}

?>
