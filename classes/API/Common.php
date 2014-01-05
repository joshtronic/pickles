<?php

/**
 * Common API Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
