<?php

/**
 * Security System for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Security Class
 *
 * Collection of static methods for handling security within a website running
 * on PICKLES. Requires sessions to be enabled.
 *
 * @usage <code>Security::login(10);</code>
 * @usage <code>Security::isLevel(SECURITY_LEVEL_ADMIN);</code>
 */
class Security
{
	/**
	 * Lookup Cache
	 *
	 * Used to minimize database lookups
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $cache = array();

	/**
	 * Generate Hash
	 *
	 * Generates an SHA1 hash from the provided string. Optionally can be salted.
	 *
	 * @param  string $value value to hash
	 * @param  mixed $salts optional salt or salts
	 * @return string SHA1 has
	 */
	public static function generateHash($value, $salts = null)
	{
		// Determines which salt(s) to use
		if ($salts == null)
		{
			$config = Config::getInstance();

			if (isset($config->security['salt']) && $config->security['salt'] != null)
			{
				$salts = $config->security['salt'];
			}
			else
			{
 				$salts = array('P1ck73', 'Ju1C3');
			}
		}

		// Forces the variable to be an array
		if (!is_array($salts))
		{
			$salts = array($salts);
		}

		// Loops through the salts, applies them and calculates the hash
		$hash = $value;
		foreach ($salts as $salt)
		{
			$hash = sha1($salt . $hash);
		}

		return $hash;
	}

	/**
	 * Check Session
	 *
	 * Checks if sessions are enabled.
	 *
	 * @static
	 * @access private
	 * @return boolean whether or not sessions are enabled
	 */
	private static function checkSession()
	{
		if (session_id() == '')
		{
			throw new Exception('Sessions must be enabled to use the Security class');
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Check Level
	 *
	 * Checks if a passed level is an integer and/or properly defined in the
	 * site's configuration file.
	 *
	 * @static
	 * @access private
	 * @param  mixed access level to validate
	 * @return whether ot not the access level is valid
	 */
	private static function checkLevel(&$access_level)
	{
		if (is_int($access_level))
		{
			return true;
		}
		else
		{
			$config = Config::getInstance();

			// Attempts to validate the string passed
			if (isset($config->security[$access_level]))
			{
				if (is_numeric($config->security[$access_level]))
				{
					$access_level = (int)$config->security[$access_level];
					return true;
				}
				else
				{
					throw new Exception('Level "' . $access_level . '" is not numeric in config.ini');
				}
			}
			else
			{
				throw new Exception('Level "' . $access_level . '" is not defined in config.ini');
			}
		}

		return false;
	}

	/**
	 * Login
	 *
	 * Creates a session variable containing the user ID and generated token.
	 * The token is also assigned to a cookie to be used when validating the
	 * security level.
	 *
	 * @static
	 * @param  $user_id ID of the user that's been logged in
	 * @return boolean whether or not the login could be completed
	 */
	public static function login($user_id)
	{
		if (self::checkSession())
		{
			$token = sha1(microtime());

			$_SESSION['__pickles']['security'] = array(
				'token'   => $token,
				'user_id' => (int)$user_id,
			);

			setcookie('pickles_security_token', $token);

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Logout
	 *
	 * Clears out the security information in the session and the cookie.
	 *
	 * @static
	 * @return boolean true
	 */
	public static function logout()
	{
		if (isset($_SESSION['__pickles']['security']))
		{
			$_SESSION['__pickles']['security'] = null;
			unset($_SESSION['__pickles']['security']);

			setcookie('pickles_security_token', '', time() - 3600);
		}

		return true;
	}

	/**
	 * Get User Level
	 *
	 * Looks up the user level in the database and caches it. Cache is used
	 * for any subsequent look ups for the user. Also validates the session
	 * variable against the cookie to ensure everything is legit.
	 *
	 * return integer user level or false
	 */
	private static function getUserLevel()
	{
		if (self::checkSession() == true && isset($_SESSION['__pickles']['security']['user_id']))
		{
			// Checks the session against the cookie
			if (isset($_SESSION['__pickles']['security']['token'], $_COOKIE['pickles_security_token'])
				&& $_SESSION['__pickles']['security']['token'] != $_COOKIE['pickles_security_token'])
			{
				Security::logout();
			}
			// Hits the database to determine the user's level
			else
			{
				// Checks the session cache instead of hitting the database
				if (isset($_SESSION['__pickles']['security']['user_id'], self::$cache[(int)$_SESSION['__pickles']['security']['user_id']]))
				{
					return self::$cache[(int)$_SESSION['__pickles']['security']['user_id']];
				}
				else
				{
					// Pulls the config and defaults where necessary
					$config = Config::getInstance();

					if ($config->security === false)
					{
						$config = array();
					}
					else
					{
						$config = $config->security;
					}

					$defaults = array('login' => 'login', 'model' => 'User', 'column' => 'level');
					foreach ($defaults as $variable => $value)
					{
						if (!isset($config[$variable]))
						{
							$config[$variable] = $value;
						}
					}

					// Uses the model to pull the user's access level
					$class = $config['model'];
					$model = new $class(array('fields' => $config['column'], 'conditions' => array('id' => (int)$_SESSION['__pickles']['security']['user_id'])));

					if ($model->count() == 0)
					{
						Security::logout();
					}
					else
					{
						$constant = 'SECURITY_LEVEL_' . $model->record[$config['column']];

						if (defined($constant))
						{
							$constant = constant($constant);

							self::$cache[(int)$_SESSION['__pickles']['security']['user_id']] = $constant;

							return $constant;
						}
						else
						{
							throw new Exception('Security level constant is not defined');
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Is Level
	 *
	 * Checks the user's access level is exactly the passed level
	 *
	 * @static
	 * @param  integer $access_level access level to be checked against
	 * @return boolean whether or not the user is that level
	 */
	public static function isLevel()
	{
		$is_level = false;

		if (self::checkSession())
		{
			$arguments = func_get_args();
			if (is_array($arguments[0]))
			{
				$arguments = $arguments[0];
			}

			foreach ($arguments as $access_level)
			{
				if (self::checkLevel($access_level))
				{
					if (self::getUserLevel() == $access_level)
					{
						$is_level = true;
						break;
					}
				}
			}
		}

		return $is_level;
	}

	/**
	 * Has Level
	 *
	 * Checks the user's access level against the passed level.
	 *
	 * @static
	 * @param  integer $access_level access level to be checked against
	 * @return boolean whether or not the user has access
	 */
	public static function hasLevel()
	{
		$has_level = false;

		if (self::checkSession())
		{
			$arguments = func_get_args();
			if (is_array($arguments[0]))
			{
				$arguments = $arguments[0];
			}

			foreach ($arguments as $access_level)
			{
				if (self::checkLevel($access_level))
				{
					if (self::getUserLevel() >= $access_level)
					{
						$has_level = true;
						break;
					}
				}
			}
		}

		return $has_level;
	}

	/**
	 * Between Level
	 *
	 * Checks the user's access level against the passed range.
	 *
	 * @static
	 * @param  integer $low access level to be checked against
	 * @param  integer $high access level to be checked against
	 * @return boolean whether or not the user has access
	 */
	public static function betweenLevel($low, $high)
	{
		$between_level = false;

		if (self::checkSession())
		{
			if (self::checkLevel($low) && self::checkLevel($high))
			{
				$user_level = self::getUserLevel();

				if ($user_level >= $low && $user_level <= $high)
				{
					$between_level = true;
					break;
				}
			}
		}

		return $between_level;
	}
}

?>
