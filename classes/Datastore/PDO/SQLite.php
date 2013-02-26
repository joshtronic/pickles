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
class Datastore_PDO_SQLite extends Datastore_PDO_Common
{
	/**
	 * Driver
	 *
	 * @var string
	 */
	public $driver = 'pdo_sqlite';

	/**
	 * DSN format
	 *
	 * @var string
	 */
	public $dsn = 'sqlite:[[hostname]]';
}

?>
