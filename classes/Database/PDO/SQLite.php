<?php

/**
 * SQLite Class File for PICKLES
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
 * SQLite Database Abstraction Layer
 */
class Database_PDO_SQLite extends Database_PDO_Common
{
	/**
	 * Driver
	 *
	 * @access protected
	 * @var    string
	 */
	protected $driver = 'pdo_sqlite';

	/**
	 * DSN format
	 *
	 * @access protected
	 * @var    string
	 */
	protected $dsn = 'sqlite:[[hostname]]';
}

?>
