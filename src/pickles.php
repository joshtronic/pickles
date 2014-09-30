<?php

/**
 * Core PICKLES Include File
 *
 * This is the file that you include on the page you're instantiating the
 * controller from (typically index.php).  The path to the PICKLES code
 * base is established as well as the path that Smarty will use to store
 * the compiled pages.
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 * @usage     <code>require_once 'pickles.php';</code>
 */

// Turns on error before the config is loaded to help catch parse errors
ini_set('display_errors', true);
error_reporting(-1);

// Defaults timezone to UTC if not set
if (ini_get('date.timezone') == '')
{
    ini_set('date.timezone', 'Etc/UTC');
}

// Loads the base config
$config = Pickles\Config::getInstance();

// Configures any available PHP configuration options
if (isset($config['php']) && is_array($config['php']))
{
    foreach ($config['php'] as $variable => $value)
    {
        ini_set($variable, $value);
    }
}

