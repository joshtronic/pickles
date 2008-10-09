<?php

/**
 * Debug Viewer Class File for PICKLES
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
 * Debugging Viewer
 *
 * Displays debugging information on the screen. 
 */
class Viewer_Debug extends Viewer_Common {

	/**
	 * Display the debugging information
	 *
	 * var_dump()s a few important super globals to aid in debugging.
	 *
	 * @todo May want to use contrib/dBug as it has better output.
	 * @todo Just a thought, perhaps instead of a debug viewer, the debug mode
	 *       would be better served as an option in the model and then the
	 *       regular viewer would be executed as well as displaying the debug
	 *       data.
	 * @todo May also be cool to add in logic to check for the vi swap file, and
	 *       if that's present, force the model into a debug state, as it will
	 *       indicate it's being worked on.
	 * @todo Even cooler still, perhaps a debug console should be available when
	 *       the model is marked debug or when a file is open.  It would be
	 *       activated with a hot key, would be legendary.
	 * @todo Perhaps it could be a configuration option to say that if a file is
	 *       open, to now allow the end user to see the page, give them a generic
	 *       "hey we're working on it" message.
	 */
	public function display() {
		echo '<h1>Debug</h1>' . "\n";
		echo '<h2>$_REQUEST</h2>' . "\n";
		echo '<pre>';
		var_dump($_REQUEST);
		echo '</pre>';
		echo '<h2>$_SESSION</h2>' . "\n";
		echo '<pre>';
		var_dump($_SESSION);
		echo '</pre>';
		echo '<h2>$_SERVER</h2>' . "\n";
		echo '<pre>';
		var_dump($_SERVER);
		echo '</pre>';
	}
}

?>
