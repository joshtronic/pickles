<?php

/**
 * PHP Display Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * PHP Display
 *
 * Displays the associated PHP templates for the Model.
 */
class Display_PHP extends Display_Common
{
	/**
	 * Template Extension
	 *
	 * I know there's some controversy amoungst my peers concerning the
	 * usage of the .phtml extension for these PHP template files. If you
	 * would prefer .php or .tpl extensions, feel free to void your
	 * warranty and change it here.
	 *
	 * @access protected
	 * @var    string $extension file extension for the template files
	 */
	protected $extension = 'phtml';

	/**
	 * Template Exists
	 *
	 * @return integer the number of templates defined
	 */
	public function templateExists()
	{
		if ($this->parent_template != null)
		{
			return file_exists($this->parent_template) && file_exists($this->child_template);
		}
		else
		{
			return file_exists($this->child_template);
		}
	}

	/**
	 * Renders the PHP templated pages
	 */
	public function render()
	{
		if ($this->templateExists())
		{
			// Starts up the buffer
			ob_start();

			// Creates (possibly overwritten) objects
			$dynamic_class = (class_exists('CustomDynamic') ? 'CustomDynamic' : 'Dynamic');
			$form_class    = (class_exists('CustomForm')    ? 'CustomForm'    : 'Form');
			$html_class    = (class_exists('CustomHTML')    ? 'CustomHTML'    : 'HTML');

			// {{{ Old scope, magic variables

			// Puts the class variables in local scope of the template
			$__dynamic   = new $dynamic_class();
			$__form      = new $form_class();
			$__html      = new $html_class();
			$__config    = $this->config;
			$__meta      = $this->meta_data;
			$__module    = $this->module_return;
			$__css_class = $this->css_class;
			$__js_file   = $this->js_basename;
			$__fluid     = $this->fluid;

			// }}}
			// {{{ New scope, class variables

			$this->dynamic   = &$__dynamic;
			$this->form      = &$__form;
			$this->html      = &$__html;
			$this->meta      = &$__meta;
			$this->module    = &$__module;
			$this->js_file   = &$this->js_basename;

			// }}}

			// Loads the template
			if ($this->parent_template != null)
			{
				if ($this->child_template == null)
				{
					$__template = $this->parent_template;
				}
				else
				{
					$__template = $this->child_template;
				}

				require_once $this->parent_template;
			}
			elseif ($this->child_template != null)
			{
				$__template = $this->child_template;

				require_once $__template;
			}

			// Grabs the buffer contents and clears it out
			$buffer = ob_get_clean();

			// Kills any whitespace and HTML comments
			$buffer = preg_replace(array('/^[\s]+/m', '/<!--(?:(?!BuySellAds).)+-->/U'), '', $buffer);

			// Note, this doesn't exit in case you want to run code after the display of the page
			echo $buffer;
		}
		else
		{
			echo Convert::toJSON($this->module_return);
		}
	}
}

?>
