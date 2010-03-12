<?php

/**
 * Configuration Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package   pickles
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
 * @todo      Add support for .php and .ini config files (auto scan directory?)
 */

/**
 * Config Class
 *
 * Handles loading a configuration file and parsing the data for
 * any public nodes that need to be made available to the viewer.
 *
 * @usage <code>$config = new Config($filename); // $filename is optional, default = ../config.xml</code>
 */
class Config extends Object
{
	/**
	 * Instance of the Config object
	 *
	 * @static 
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Constructor
	 *
	 * Calls the parent constructor and loads the pass file
	 *
	 * @param string $file Filename of the config file (optional)
	 */
	public function __construct($file = '../config.xml')
	{
		parent::__construct();

		$this->load($file);
	}

	/**
	 * Loads a configuration file
	 *
	 * Handles the potential loading of the configuration file and
	 * sanitizing the boolean strings into actual boolean values.
	 *
	 * @param  string $file Filename of the XML file to be loaded
	 * @return boolean Success of the load process
 	 * @todo   Add the ability to load in multiple configuration files.
	 */
	public function load($file)
	{
		// Makes sure the file is legit on the surface
		if (file_exists($file) && is_file($file) && is_readable($file))
		{
			// Pulls the contents of the file to allow for validation
			$contents = trim(file_get_contents($file));

			// Checks that the file contents isn't empty and that the config node is present
			if ($contents != '' && substr($contents, 0, 8) == '<config>' && substr($contents, -9) == '</config>')
			{
				/**
				 * @todo LIBXML_NOCDATA is 5.1+ and I want PICKLES to
				 *       be 5.0+ compatible.  Potential fix is to read
				 *       the file in as a string, and if it has CDATA,
				 *       throw an internal warning.
				 */
				$data = simplexml_load_string($contents, 'SimpleXMLElement', LIBXML_NOCDATA);

				// Loops through the top level nodes to find public nodes
				$variables = get_object_vars($data);

				if (is_array($variables))
				{
					foreach ($variables as $key => $value)
					{
						if (is_object($value) && isset($value->attributes()->public) && $value->attributes()->public == true)
						{
							$this->_public[$key] = $value;
						}

						$this->$key = $value;
					}
				}

				return true;
			}
		}
	}

	/**
	 * Get instance of the object
	 *
	 * Instantiates a new object if one isn't already available, then
	 * returns the instance.
	 *
	 * @static
	 * @return object self::$instance instance of the Config
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance) || empty(self::$instance))
		{
			self::$instance = new Config();
		}

		return self::$instance;
	}
}

?>
