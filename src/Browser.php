<?php

/**
 * Browser Utility Collection
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
 */

namespace Pickles;

/**
 * Browser Utility Class
 *
 * Just a simple collection of static functions to accomplish some of the
 * more redundant browser-related tasks.
 */
class Browser extends Object
{
    /**
     * Remote IP
     *
     * Returns the user's IP address.
     *
     * @return mixed IP address or false if unable to determine
     */
    public static function remoteIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif (isset($_SERVER['REMOTE_ADDR']))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        else
        {
            $ip = false;
        }

        return $ip;
    }
}

