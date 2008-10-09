<?php

/**
 * Security Class File for PICKLES
 *
 * PICKLES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 * 
 * PICKLES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with PICKLES.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author    Joshua John Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007, 2008 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Security Class
 *
 * Handles authenticating a user via an Apache login box.
 *
 * @todo Make the SQL less specific, right now you have to use a table
 *       named users, and use the email as the username.  I will need
 *       to move this to the configuration and allow the user to
 *       specify which table to authenticate against, and what column
 *       names to use for the username and password.
 */
class Security extends Object {

	/**
	 * Authenticates the user
	 *
	 * Checks for the authentication variables to be passed in the $_SERVER
	 * super global and attempts to authenticate the user against MySQL.  If
	 * the user cannot successfully they will be presented with a 401
	 * Unauthorized page.
	 *
	 * @todo I'm sure someone will find the access denied message offensive,
	 *       so this will need to be made more generic.  May also want to
	 *       add in the ability for someone to add a custom message and/or
	 *       landing page in the configuration as well.
	 */
	static function authenticate() {
		$db = DB::getInstance();

		if (isset($_SERVER['PHP_AUTH_USER'])) {
			$from = '
				FROM users
				WHERE email = "' . $_SERVER['PHP_AUTH_USER'] . '"
				AND password = "' . md5($_SERVER['PHP_AUTH_PW']) . '"
				AND admin = 1;
			';

			$db->execute('SELECT COUNT(id) ' . $from);
			if ($db->getField() != 0) {
				$db->execute('SELECT id ' . $from);
				$_SESSION['user_id'] = $db->getField();
			}
			else {
				$_SESSION['user_id'] = null;
			}
		}

		if (!isset($_SESSION['user_id'])) {
			header('WWW-Authenticate: Basic realm="Site Admin"');
			header('HTTP/1.0 401 Unauthorized');
			exit('No shirt, no shoes, no salvation. Access denied.');
		}
		else {
			// Commented out to allow navigation to the page intended
			//header('Location: /');
			//exit();
		}
	}

	/**
	 * Logs the user out
	 *
	 * Destroys the session, clears out the authentication variables in the
	 * $_SERVER super global and redirects the user to the root of the site.
	 */
	static function logout() {
		$session = Session::getInstance();
		$session->destroy();

		unset($_SERVER['PHP_AUTH_USER']);
		unset($_SERVER['PHP_AUTH_PW']);

		header('Location: /');
	}
}

?>
