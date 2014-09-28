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

// {{{ PICKLES Constants

// @todo Finish reworking constants to be part of the Config object
if (!defined('SITE_PATH'))
{
    // Establishes our site paths, sanity check is to allow vfsStream in our tests
    define('SITE_PATH', getcwd() . '/../');
}

if (!defined('SITE_CLASS_PATH'))
{
    define('SITE_CLASS_PATH',    SITE_PATH . 'classes/');
    define('SITE_MODEL_PATH',    SITE_PATH . 'models/');
    define('SITE_RESOURCE_PATH', SITE_PATH . 'resources/');

    define('PRIVATE_PATH', SITE_PATH    . 'private/');
    define('LOG_PATH',     PRIVATE_PATH . 'logs/');
}

// }}}
// {{{ Defaults some important configuration options

// Turns on error before the config is loaded to help catch parse errors
ini_set('display_errors', true);
error_reporting(-1);

// Defaults timezone to UTC if not set
if (ini_get('date.timezone') == '')
{
    ini_set('date.timezone', 'Etc/UTC');
}

// Sets the session variables
ini_set('session.cache_expire',   86400);
ini_set('session.entropy_file',   '/dev/urandom');
ini_set('session.entropy_length', 512);
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor',     1000);
ini_set('session.hash_function',  1);

// }}}
// {{{ Loads the configuration file and sets any configuration options

// Loads the base config
$config = Pickles\Config::getInstance();

// Injects PICKLES variables into the config
$config->data['pickles']['path'] = dirname(__FILE__) . '/';

// Configures any available PHP configuration options
if (is_array($config->php) && count($config->php))
{
    foreach ($config->php as $variable => $value)
    {
        ini_set($variable, $value);
    }
}

// }}}

