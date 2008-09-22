<?php

/**
 * Smarty viewer
 *
 * Displays the associated Smarty templates for the Model.
 *
 * @package    PICKLES
 * @subpackage Viewer
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2007-2008 Joshua Sherman
 * @link       http://smart.net/
 */
class Viewer_Smarty extends Viewer_Common {

	/**
	 * Displays the Smarty generated pages
	 */
	public function display() {
		// Obliterates any passed in PHPSESSID (thanks Google)
		if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') !== false) {
			list($request_uri, $phpsessid) = split('\?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $request_uri);
			exit();
		}

		// XHTML compliancy stuff
		ini_set('arg_separator.output', '&amp;');
		ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

		/**
		 * @todo Create a wrapper so that we can auto load this
		 */
		require_once 'contrib/smarty/libs/Smarty.class.php';

		$smarty = new Smarty();

		/**
		 * @todo Perhaps the templates directory would be better suited as a
		 *       config variable?
		 */
		$smarty->template_dir = '../templates/';

		$cache_dir   = TEMP_PATH . 'cache';
		$compile_dir = TEMP_PATH . 'compile';

		if (!file_exists($cache_dir))   { mkdir($cache_dir,   0777, true); }
		if (!file_exists($compile_dir)) { mkdir($compile_dir, 0777, true); }

		$smarty->cache_dir   = $cache_dir ;
		$smarty->compile_dir = $compile_dir;

		$smarty->load_filter('output','trimwhitespace');

		// Include custom Smarty functions
		$directory = PICKLES_PATH . 'smarty/functions/';

		if (is_dir($directory)) {
			if ($handle = opendir($directory)) {
				while (($file = readdir($handle)) !== false) {
					if (!preg_match('/^\./', $file)) {
						list($type, $name, $ext) = split('\.', $file);
						require_once $directory . $file;
						$smarty->register_function($name, "smarty_{$type}_{$name}");
					}
				}
				closedir($handle);
			}
		}

		$navigation = $this->config->get('navigation', 'sections');

		// Add the admin section if we're authenticated
		/**
		 * @todo Add logic to check if the user is already logged in.  Currently
		 *       it is always assumed that they are not.
		 */
		if (false) {
			if ($this->config->get('admin', 'menu') == true) {
				$navigation['admin'] = 'Admin';
			}
		}

		/**
		 * @todo Maybe the template path should be part of the configuration?
		 */
		$template        = '../templates/' . $this->model->get('name') . '.tpl';
		$shared_template = str_replace('../', '../../pickles/', $template);

		if (!file_exists($template)) {
			if (file_exists($shared_template)) {
				$template = $shared_template;
			}
		}

		// Pass all of our controller values to Smarty
		$smarty->assign('navigation', $navigation);
		$smarty->assign('section',    $this->model->get('section'));
		$smarty->assign('model',      $this->model->get('name'));
		/**
		 * @todo Rename action to event
		 * @todo I'm not entirely sure that these values are necessary at all due
		 *       to new naming conventions.
		 */
		$smarty->assign('action',     $this->model->get('action'));
		$smarty->assign('event',      $this->model->get('action'));

		// Thanks to new naming conventions
		$smarty->assign('admin',      $this->config->get('admin', 'sections'));
		$smarty->assign('template',   $template);

		// Only load the session if it's available
		/**
		 * @todo Not entirely sure that the view needs full access to the session
		 *       (seems insecure at best)
		 */
		/*
		if (isset($_SESSION)) {
			$smarty->assign('session', $_SESSION);
		}
		*/

		$data = $this->model->getData();

		if (isset($data) && is_array($data)) {
			foreach ($data as $variable => $value) {
				$smarty->assign($variable, $value);
			}
		}

		/**
		 * @todo There's no error checking for the index... should it be shared,
		 *       and should the error checking occur anyway since any shit could
		 *       happen?
		 */
		/*
		$template        = '../templates/index.tpl';
		$shared_template = str_replace('../', '../../pickles/', $template);

		if (!file_exists($template)) {
			if (file_exists($shared_template)) {
				$template = $shared_template;
			}
		}
		*/

		// Load it up!
		header('Content-type: text/html; charset=UTF-8');

		// If the index.tpl file is present, load it, else load the template directly
		/**
		 * @todo Should there be additional logic to allow the model or the
		 *       template to determine whether or not the index should be loaded?
		 */
		if ($smarty->template_exists('index.tpl')) {
			$smarty->display('index.tpl');
		}
		else {
			$smarty->display($template);
		}
	}
}

?>
