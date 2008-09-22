<?php

/**
 * Debugging viewer
 *
 * Displays debugging information on the screen. 
 *
 * @package    PICKLES
 * @subpackage Viewer
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2007-2008 Joshua Sherman
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
