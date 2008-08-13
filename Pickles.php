<?php

date_default_timezone_set('America/New_York');
define('PATH', getcwd() . '/');

function __autoload($class) {
	$file = PATH . '../../pickles/classes/' . str_replace('_', '/', $class) . '.php';

	if (file_exists($file)) {
		require_once $file;
	}
}

class Pickles extends Object {

	/*
	protected $config = null;

	private $controller = null;

	public function __construct($site, $controller = 'Web') {
		parent::__construct();
		
		new Controller($controller); 
	}
	*/

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
