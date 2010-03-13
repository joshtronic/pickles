<?php

/**
 * Module Class File for PICKLES
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
 * Module Class
 *
 * This is a parent class that all PICKLES modules should be extending. 
 * Each module can specify it's own meta data and whether or not a user
 * must be properly authenticated to view the page. Currently any pages
 * without a template are treated as pages being requested via AJAX and the
 * return will be JSON encoded. In the future this may need to be changed
 * out for logic that allows the requested module to specify what display
 * type(s) it can use.
 */
class Module extends Object
{
	/**
	 * Page title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Meta description
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Meta keywords (comma separated)
	 *
	 * @var string
	 */
	public $keywords;

	/**
	 * Access level of the page
	 *
	 * Defaults to false which is everybody, even anonymous
	 *
	 * @var boolean
	 */
	public $access = false;

	/**
	 * Secure
	 *
	 * Whether or not the page should be loaded via SSL.  Not currently
	 * being used.  Defaults to false, non-SSL.
	 *
	 * @var boolean
	 */
	public $secure = false;

	/**
	 * AJAX
	 *
	 * Whether or not the page must be loaded via AJAX and if so, what
	 * pages are allowed to access it and the request method.
	 *
	 * @var array
	 */
	public $ajax = false;

	/**
	 * Default display engine
	 *
	 * Defaults to PHP but could be set to Smarty, JSON, XML or RSS.
	 *
	 * @var string
	 */
	public $engine = DISPLAY_PHP;

	/**
	 * Default template
	 *
	 * Defaults to 'index' but could be set to any valid template basename.
	 * The display engine determines what the file extension should be.
	 *
	 * @var string
	 */
	public $template = 'index';

	/**
	 * Constructor
	 *
	 * The constructor does nothing by default but can be passed a boolean
	 * variable to tell it to automatically run the __default() method.
	 * This is typically used when a module is called outside of the scope
	 * of the controller (the registration page calls the login page in
	 * this manner.
	 *
	 * @param boolean $autorun optional flag to autorun __default()
	 */
	public function __construct($autorun = false)
	{
		parent::__construct();

		if ($autorun === true)
		{
			$this->__default();
		}
	}

	/**
	 * Default "Magic" Method
	 *
	 * This function is overloaded by the module. The __default() method is
	 * where you want to place any code that needs to be executed at
	 * runtime. The reason the code isn't in the constructor is because the
	 * module must be instantiated before the code is executed so that the
	 * controller script is aware of the authentication requirements.
	 */
	public function __default()
	{

	}
}

?>
