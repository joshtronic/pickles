<?php

class Controller {

	public function __construct() {
		global $smarty;

		$section = $action = $is_admin = null;

		if ((isset($_REQUEST['section']) && $_REQUEST['section'] == 'admin')) {
			Session::authenticate();
		}

		// Set up the section and action from the _REQUEST values
		// @todo this needs to be refactored.. my idea is to take what's there and throw it out, then loop through and load the variables that are
		// present, then go ahead and set flags as to what kind of page it is, and what to load (is_admin, load_logic, load_template
		if (isset($_REQUEST['section'])) {
			// Determine if we're on an admin page
			$is_admin = preg_match('/^admin/', $_REQUEST['section']);

			// Check for section.action.php
			if (isset($_REQUEST['action']) && file_exists('../logic/' . $_REQUEST['section'] . '.' . $_REQUEST['action'] . '.php')) {
				$section = $_REQUEST['section'];
				$action  = $_REQUEST['action'];
			}
			// Else check for section.php
			else if (file_exists('../logic/' . $_REQUEST['section'] . '.php')) {
				$section = $_REQUEST['section'];
			}
			// Else check for section.action.tpl
			else if (isset($_REQUEST['action']) && file_exists('../templates/' . $_REQUEST['section'] . '.' . $_REQUEST['action'] . '.tpl')) {
				$section = $_REQUEST['section'];
				$action  = $_REQUEST['action'];
			}
			// Else check for section.tpl
			else if (file_exists('../templates/' . $_REQUEST['section'] . '.tpl')) {
				$section = $_REQUEST['section'];
			}
		}

		// Check that the user is authenticated
		// @todo need to fucking fix this
		if ($is_admin && !isset($_SESSION['user_id']) && !isset($_SESSION['artist_id'])) {
			$section = 'admin';
			$action  = null;
		}

		// If we've come this far without a section, use the default
		if (!isset($section)) {
			$section = Config::get('default', 'navigation');
		}

		// Check that the logic script exists and if so, load it
		$file = '../logic/' . $section . ($action ? '.' . $action : null) . '.php';
		if (file_exists($file)) {
			require_once $file;
		}

		// Check if we're accessing an admin sub section and load the logic script
		if (isset($_REQUEST['section']) && $_REQUEST['section'] != 'admin' && $is_admin) {
			if ($_REQUEST['section'] == 'admin.logout') {
				Session::logout();
			}
			else {
				$template = $_REQUEST['section'] . '.tpl';

				$file = '../logic/' . $_REQUEST['section'] . '.php';

				if (file_exists($file)) {
					require_once $file;
				}

				$section = 'admin';
			}
		}
		// Else, just define the template
		else {
			$template = $section . ($action ? '.' . $action : null) . '.tpl';
		}

		// Load the main navigation from the config
		$navigation = Config::get('sections', 'navigation');

		// Add the admin section if we're authenticated
		if (isset($_SESSION['user_id']) || isset($_SESSION['artist_id'])) {
			if (Config::get('menu', 'admin') == 'true') {
				$navigation['admin'] = 'Admin';
			}
			
			$smarty->assign('admin', Config::get('sections', 'admin'));
		}

		// Pass all of our controller values to Smarty
		$smarty->assign('navigation', $navigation);
		$smarty->assign('section',    $section);
		$smarty->assign('action',     $action);
		$smarty->assign('template',   $template);

		if (isset($_SESSION)) {
			$smarty->assign('session', $_SESSION);
		}

		// Load it up!
		header('Content-type: text/html; charset=UTF-8');
		// @todo path is hardcoded case i am teh suckage
		$smarty->display(isset($_REQUEST['ajax']) ? '/var/www/josh/common/smarty/templates/ajax.tpl' : 'index.tpl');
	}

}

?>
