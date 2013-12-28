<?php

/**
 *Display Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Display Class
 *
 * If you can see it then it probably happened in here.
 */
class Display extends Object
{
	/**
	 * Return Type
	 *
	 * This class supports loading a PHP template, displaying JSON, XML and an
	 * RSS flavored XML. Inside your modules you can specify either a string or
	 * array. Possible values include "template", "json", "xml" and "rss".
	 * Default behavior is to try to load a template and fallback to displaying
	 * JSON. The "template" option always takes precedence when used with the
	 * other types.
	 *
	 * @var mixed string or array to determine how to return
	 */
	public $return = ['template', 'json'];

	/**
	 * Template
	 *
	 * Templates are found in the ./templates directory of your site. The
	 * template workflow is to load ./templates/__shared/index.phtml and you
	 * would set that template up to require $this->template, the path and
	 * filename for the module template (named based on the structure of the
	 * requested URI. Inside your module you can specify the basename of the
	 * parent template you would like to use or false to not use a parent
	 * template.
	 *
	 * @var string or boolean false the basename of the parent template
	 */
	public $template = 'index';

	/**
	 * Meta Data
	 *
	 * An array of meta data that you want exposed to the template. Currently
	 * you set the meta data from inside your module using the class variables
	 * title, description and keywords. The newer [preferred] method is to
	 * set an array in your module using the meta variable using title,
	 * description and keywords as the keys. You can also specify any other
	 * meta keys in the array that you would like to be exposed to your
	 * templates. The meta data is only used by TEMPLATE and RSS return types.
	 */
	public $meta = [];

	/**
	 * Module Data
	 *
	 * Any data the module returns or is assigned inside of the module will
	 * be available here and exposed to the template.
	 */
	public $module = null;

	public function render()
	{
		try
		{
			// Starts up the buffer so we can capture it
			ob_start();

			if (!is_array($this->return))
			{
				$this->return = [ $this->return ];
			}

			$return_json = $return_rss = $return_template = $return_xml = false;

			foreach ($this->return as $return)
			{
				$variable  = 'return_' . $return;
				$$variable = true;
			}

			// Makes sure the return type is valid
			if (!$return_json && !$return_rss && !$return_template && !$return_xml)
			{
				throw new Exception('Invalid return type.');
			}

			// Checks for the PHPSESSID in the query string
			if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') === false)
			{
				// XHTML compliancy stuff
				// @todo Wonder if this could be yanked now that we're in HTML5 land
				ini_set('arg_separator.output', '&amp;');
				ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

				header('Content-type: text/html; charset=UTF-8');
			}
			else
			{
				// Redirect so Google knows to index the page without the session ID
				list($request_uri, $phpsessid) = explode('?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: ' . $request_uri);

				throw new Exception('Requested URI contains PHPSESSID, redirecting.');
			}

			// @todo Derrive CSS and JS from _REQUEST['request'] no need to pass around

			$loaded = false;

			if ($return_template)
			{
				// Determines if we're using a custom class or not
				$dynamic_class = (class_exists('CustomDynamic') ? 'CustomDynamic' : 'Dynamic');
				$form_class    = (class_exists('CustomForm')    ? 'CustomForm'    : 'Form');
				$html_class    = (class_exists('CustomHTML')    ? 'CustomHTML'    : 'HTML');

				// Exposes some objects and variables to the local scope of the template
				$this->request   = $this->js_file = $_REQUEST['request'];
				// @todo replace _ with - as it's more appropriate for CSS naming
				$this->css_class = strtr($this->request, '/', '_');

				// @todo Remove the magic $__variable when all sites are ported
				$__config    = $this->config;
				$__css_class = $this->css_class;
				$__js_file   = $this->js_file;
				$__meta      = $this->meta;
				$__module    = $this->module;

				$__dynamic   = $this->dynamic = new $dynamic_class();
				$__form      = $this->form    = new $form_class();
				$__html      = $this->html    = new $html_class();

				// Checks for the parent template and tries to load it
				if ($this->template)
				{
					$parent_file = SITE_TEMPLATE_PATH . '__shared/' . $this->template . '.phtml';
					$child_file  = SITE_TEMPLATE_PATH . $_REQUEST['request'] . '.phtml';

					// Assigns old and new variables
					// @todo Remove $__template when all sites are ported
					$__template = $this->template = $child_file;

					if (file_exists($parent_file))
					{
						$loaded = require_once $parent_file;
					}
				}

				// Checks for the module template and tries to load it
				if (file_exists($child_file))
				{
					$loaded = require_once $child_file;
				}
			}

			if (!$loaded)
			{
				if ($return_json)
				{
					echo json_encode($this->module, isset($_REQUEST['pretty']) ? JSON_PRETTY_PRINT : false);
				}
				elseif ($return_xml)
				{
					echo Convert::arrayToXML($this->module, isset($_REQUEST['pretty']));
				}
			}

			// Grabs the buffer so we can massage it a bit
			$buffer = ob_get_clean();

			// Kills any whitespace and HTML comments in templates
			if ($loaded)
			{
				// The BSA exception is because their system sucks and demands there be comments present
				$buffer = preg_replace(['/^[\s]+/m', '/<!--(?:(?!BuySellAds).)+-->/U'], '', $buffer);
			}

			return $buffer;
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}
}

?>
