<?php

/**
 * Authentication
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @link      http://picklesphp.com
 * @package   Pickles
 */

namespace Pickles;

/**
 * Auth Parent Class
 */
class Auth extends Object
{
    /**
     * Basic Auth
     *
     * Implement this method when using 'basic' auth. Allows you to roll a
     * custom solution based on your needs. Want to use username and password?
     * Rather use an API key and not worry about the password? Do it. Return
     * true when authentication is successful and false when it is not.
     */
    public static function basic()
    {
        return false;
    }

    /**
     * OAuth2
     *
     * Handles authentication of the access token.
     */
    final static public function oauth2()
    {

    }
}

