<?php

/**
 * PHP Display Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package   PICKLES
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
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
		// Starts up the buffer
		ob_start();

		// Puts the class variables in local scope for the template
		$config           = $this->config;
		$module_css_class = $this->css_class;
		$module_js_file   = $this->js_basename;
		$module           = $this->module_return;

		// Loads the template
		if ($this->parent_template != null)
		{
			if ($this->child_template == null)
			{
				$template = $this->parent_template;
			}
			else
			{
				$template = $this->child_template;
			}

			require_once $this->parent_template;
		}
		elseif ($this->child_template != null)
		{
			$template = $this->child_template;

			require_once $template;
		}

		// Grabs the buffer contents and clears it out
		$buffer = ob_get_clean();

		/*
		@todo this wasn't working as well as I'd like:

		$regex = array(
			'PRE'      => '!<pre>.+?</pre>!is',
			'SCRIPT'   => '!<script[^>]+>.+?</script>!is',
			'TEXTAREA' => '!<textarea[^>]+>.+?</textarea>!is',
		);

		$search_replace = array();

		// Cleans up the stuff we don't want to minify
		foreach ($regex as $type => $pattern)
		{
			$hash = '@@@PICKLES::' . $type . '@@@';

			preg_match_all($pattern, $buffer, $matches);
			$search_replace[$hash] = $matches[0];
			$buffer = preg_replace($pattern, $hash, $buffer);
		}

		// Strips the whitespace 
		$buffer = trim(preg_replace('/((?<!\?>)\n)[\s]+/m', '\1', $buffer)); 

		// Kills any HTML comments
		$buffer = preg_replace('/<!--.*-->/U', '', $buffer); 

		// Injects the stuff we stripped earlier
		foreach ($search_replace as $search => $replacements)
		{
			foreach ($replacements as $replacement)
			{
				$buffer = preg_replace('/'.$search.'/', $replacement, $buffer, 1);
			}
		}
		*/
	
		// Kills any whitespace and HTML comments
		$buffer = preg_replace(array('/^[\s]+/m', '/<!--.*-->/U'), '', $buffer); 

		// Note, this doesn't exit in case you want to run code after the display of the page
		echo $buffer;
	}
}

?>
