<?php

/**
 * Common API Class File for PICKLES
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
 * Common API Interface
 *
 * Parent class that our API interface classes should be extending. Contains
 * execution of parental functions but may contain more down the road.
 */
abstract class API_Common extends Object
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		parent::__destruct();
	}
}

?>