<?php

date_default_timezone_set('America/New_York');
// @todo no hard coded paths
//define('PICKLES_PATH', '/var/www/josh/common/');
define('PATH', getcwd() . '/');

function __autoload($class) {
	// @todo fix the path when we move to prod
	//$file = PICKLES_PATH . 'pickles_classes/' . str_replace('_', '/', $class) . '.php';
	$file = PATH . '../../common/pickles_classes/' . str_replace('_', '/', $class) . '.php';

	if (file_exists($file)) {
		require_once $file;
	}
}

class Pickles extends Object {

	protected $config = null;

	private $controller = null;

	public function __construct($site, $controller = 'Web') {
		parent::__construct();

		// Load the config for the site passed in
		$this->config = Config::getInstance();
		$this->config->load($site);

		// Generate a generic "site down" message
		if ($this->config->get('disabled')) {
			exit("<h2><em>{$_SERVER['SERVER_NAME']} is currently down for maintenance</em></h2>");
		}

		new Controller($controller); 
	}

}

/*
if (Config::getSession() && !isset($_SESSION)) {
	session_start();
}


// Use the FCKeditor instead of textareas
// @todo add a wrapper for these two
if (Config::getFCKEditor()) {
	require_once JLIB_PATH . 'common/static/fckeditor/fckeditor.php';
}

// Load up MagpieRSS is so desired
if (Config::getMagpieRSS()) {
	require_once JLIB_PATH . '/var/www/josh/common/contrib/magpierss/rss_fetch.inc';
}

//Request::load();
*/

?>
