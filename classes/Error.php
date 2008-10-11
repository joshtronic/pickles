<?php

/**
 * Error Class File for PICKLES
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
 * Error handling class
 *
 * Handles (for the most part) all the errors and warnings that
 * are encountered by the PICKLES core classes.  Usage is optional
 * for site level code.  Errors are logged but it's up to the
 * developer to interact with and/or display the errors to their
 * end-users.
 */
class Error extends Object {

	/**
	 * Private message arrays
	 */
	private $errors   = null;
	private $warnings = null;

	protected $logger;

	/**
	 * Constructor
	 */
	public function __construct(Config $config, Logger $logger = null) {
		$this->logger = isset($logger) ? $logger : new Logger();
	}

	/**
	 * Adds an error message
	 *
	 * @param  string Error message
	 * @return boolean true
	 */
	public function addError($message) {
		$this->errors[] = $message;
		$this->logger->write('error', '[error] ' . $message);
		return true;
	}

	/**
	 * Adds a warning message
	 *
	 * @param  string Warning message
	 * @return boolean true
	 */
	public function addWarning($message) {
		$this->warnings[] = $message;
		$this->logger->write('error', '[warning] ' . $message);
		return true;
	}

	/**
	 * Gets the stored errors
	 *
	 * @return mixed Messages in sequential order or false
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Gets the stored warnings
	 *
	 * @return array Warning messages indexed by the order they were stored
	 */
	public function getWarnings() {
		return $this->warnings;
	}

	/**
	 * Determines if there are any stored errors or warnings
	 *
	 * @return boolean Whether or not there are any errors or warnings.
	 */
	public function isError() {
		if (isset($this->errors, $this->warnings)) {
			return true;
		}

		return false;
	}
}

?>
