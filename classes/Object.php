<?php

/**
 * Object Class File for PICKLES
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
 * Object Class
 *
 * Every non-Singleton-based class needs to extend this class.
 * Any models will extend the Model class which entends the Object
 * class already.  This class handles getting an instance of the
 * Config object so that it's available.  Also provides a getter
 * and setter for variables.
 *
 * @todo Implement a profiler in every object
 */
class Object
{
	/**
	 * Instance of the Config object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $config = null;

	/**
	 * Constructor
	 *
	 * Establishes a Config instance for all children to enjoy
	 */
	public function __construct()
	{
		$this->config = Config::getInstance();
	}
}

?>
