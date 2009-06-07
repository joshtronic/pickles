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

	private $config;
	private $db;

	public function __construct(Config $config, DB $db) {
		parent::__construct();
		$this->config = $config;
		$this->db     = $db;
	}

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
	public function authenticate() {

		if (isset($this->config->admin, $this->config->admin->username, $this->config->admin->password)) {

			$_SESSION['user_id'] = null;
			
			if (isset($_SERVER['PHP_AUTH_USER'])) {
				if (
					$_SERVER['PHP_AUTH_USER'] == $this->config->admin->username
					&& $this->encrypt($this->config->admin->salt, $_SERVER['PHP_AUTH_PW']) == $this->config->admin->password
				) {
					$_SESSION['user_id'] = 1;
				}
			}
		}
		else {

			$table = array(
				'name'   => 'users',
				'fields' => array(
					'id'       => 'id',
					'username' => 'username',
					'password' => 'password'
				)
			);

			$table = $this->config->getTableMapping('users', $table);

			if (isset($_SERVER['PHP_AUTH_USER'])) {
				$from = '
					FROM ' . $table['name'] . '
					WHERE ' . $table['fields']['username'] . ' = "' . $_SERVER['PHP_AUTH_USER'] . '"
					AND ' . $table['fields']['password'] . ' = "' . md5($_SERVER['PHP_AUTH_PW']) . '";
				';

				$this->db->execute('SELECT COUNT(' . $table['fields']['id'] . ') ' . $from);
				if ($this->db->getField() != 0) {
					$this->db->execute('SELECT ' . $table['fields']['id'] . ' ' . $from);
					$_SESSION['user_id'] = $this->db->getField();
				}
				else {
					$_SESSION['user_id'] = null;
				}
			}
		}

		if (!isset($_SESSION['user_id'])) {
			header('WWW-Authenticate: Basic realm="' . $_SERVER['SERVER_NAME'] . ' Secured Page"');
			header('HTTP/1.0 401 Unauthorized');
			exit('Invalid login credentials, access denied.');
		}
		else {
			/**
			 * @todo add logic to allow the site owner to force a redirect when a user logs in
			 */
			//header('Location: /');
			//exit();
		}
	}

	/**
	 * Logs the user out
	 *
	 * Destroys the session, and redirects the user to the root of the site.
	 */
	public function logout() {
		session_destroy();
		header('Location: /');
	}

	public function encrypt($salt, $string) {
		return md5($salt . md5($salt . $string));
	}
}

?>
