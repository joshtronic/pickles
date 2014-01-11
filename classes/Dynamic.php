<?php

/**
 * Dynamic Content Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Dynamic Class
 *
 * Handles generating links to static content that are a timestamp injected as
 * to avoid hard caching. Also minifies content where applicable.
 *
 * Note: you will want to add a mod_rewrite line to your .htaccess to support
 * the routing to the filenames with the timestamp injected:
 *
 * RewriteRule ^(.+)\.([\d]+)\.(css|js|gif|png|jpg|jpeg)$ /$1.$3 [NC,QSA]
 */
class Dynamic extends Object
{
	/**
	 * Generate Reference
	 *
	 * Appends a dynamic piece of information to the passed reference in the
	 * form of a UNIX timestamp added to the query string.
	 *
	 * @param  string $reference URI reference of the file
	 * @param  string $failover URI reference to use if the reference can't be found
	 * @return string URI reference reference with dynamic content
	 */
	public function reference($reference, $failover = false)
	{
		// Checks if the URI reference is absolute, and not relative
		if (substr($reference, 0, 1) == '/')
		{
			$query_string = '';

			// Checks for ? and extracts query string
			if (strstr($reference, '?'))
			{
				list($reference, $query_string) = explode('?', $reference);
			}

			// Adds the dot so the file functions can find the file
			$file = '.' . $reference;

			if (file_exists($file))
			{
				// Replaces the extension with time().extension
				$parts = explode('.', $reference);

				if (count($parts) == 1)
				{
					throw new Exception('Filename must have an extension (e.g. /path/to/file.png)');
				}
				else
				{
					end($parts);
					$parts[key($parts)] = filemtime($file) . '.' . current($parts);
					$reference = implode('.', $parts);
				}

				// Adds the query string back
				if ($query_string != '')
				{
					$reference .= '?' . $query_string;
				}
			}
			else
			{
				if ($failover != false)
				{
					$reference = $failover;
				}
				else
				{
					throw new Exception('Supplied reference does not exist (' . $reference . ')');
				}
			}
		}
		else
		{
			throw new Exception('Reference value must be absolute (e.g. /path/to/file.png)');
		}

		return $reference;
	}

	/**
	 * Generate Stylesheet Reference
	 *
	 * Attempts to minify the stylesheet and then returns the reference URI for
	 * the file, minified or not. Supports LESS and SASS, pass it a .less file
	 * or a .scss file instead and it will be compiled before minification.
	 *
	 * @param  string $reference URI reference of the Stylesheet
	 * @return string URI reference reference with dynamic content
	 * @url    http://lesscss.org
	 * @url    http://sass-lang.com
	 */
	public function css($original_reference)
	{
		$less = false;
		$sass = false;

		// Injects .min into the filename
		$parts = explode('.', $original_reference);

		if (count($parts) == 1)
		{
			throw new Exception('Filename must have an extension (e.g. /path/to/file.css)');
		}
		else
		{
			end($parts);

			switch (current($parts))
			{
				case 'less':
					$less               = true;
					$parts[key($parts)] = 'css';
					break;

				case 'scss':
					$sass               = true;
					$parts[key($parts)] = 'css';
					break;
			}

			$parts[key($parts)] = 'min.' . current($parts);
			$minified_reference = implode('.', $parts);
		}

		$original_filename = '.' . $original_reference;
		$minified_filename = '.' . $minified_reference;

		$path = dirname($original_filename);

		if (file_exists($original_filename))
		{
			$reference = $original_reference;

			// Compiles LESS & SASS to CSS before minifying
			if ($less || $sass)
			{
				$compiled_filename = str_replace('.min', '', $minified_filename);

				require_once $this->config->pickles['path'] . 'vendors/composer/autoload.php';

				if ($less)
				{
					$less = new lessc();
					$less->compileFile($original_filename, $compiled_filename);
				}
				elseif ($sass)
				{
					$scss = new scssc();

					file_put_contents(
						$compiled_filename,
						$scss->compile(file_get_contents($original_filename))
					);
				}

				$original_filename = $compiled_filename;
			}

			if ($this->config->pickles['minify'] === true)
			{
				// Minifies CSS with a few basic character replacements.
				$stylesheet = file_get_contents($original_filename);
				$stylesheet = str_replace(array("\t", "\n", ', ', ' {', ': ', ';}', '{  ', ';  '), array('', '', ',', '{', ':', '}', '{', ';'), $stylesheet);
				$stylesheet = preg_replace('/\/\*.+?\*\//', '', $stylesheet);
				file_put_contents($minified_filename, $stylesheet);

				$reference = $minified_reference;
			}
			elseif (file_exists($minified_filename))
			{
				$reference = $minified_reference;
			}

			$reference = $this->reference($reference);
		}
		else
		{
			throw new Exception('Supplied reference does not exist');
		}

		return $reference;
	}

	/**
	 * Generate Javascript Reference
	 *
	 * Attempts to minify the source with Google's Closure compiler, and then
	 * returns the reference URI for the file, minified or not.
	 *
	 * @link   http://code.google.com/closure/compiler/
	 * @param  string $reference URI reference of the Javascript file
	 * @return string URI reference reference with dynamic content
	 */
	public function js($original_reference)
	{
		// Injects .min into the filename
		$parts = explode('.', $original_reference);

		if (count($parts) == 1)
		{
			throw new Exception('Filename must have an extension (e.g. /path/to/file.js)');
		}
		else
		{
			end($parts);
			$parts[key($parts)] = 'min.' . current($parts);
			$minified_reference = implode('.', $parts);
		}

		$original_filename = '.' . $original_reference;
		$minified_filename = '.' . $minified_reference;

		$path = dirname($original_filename);

		if (file_exists($original_filename))
		{
			$reference = $original_reference;

			if ($this->config->pickles['minify'] === true)
			{
				require_once $this->config->pickles['path'] . '.composer/autoload.php';

				$compiler = new Devize\ClosureCompiler\ClosureCompiler;
				$compiler->setSourceBaseDir(dirname($original_filename));
				$compiler->setTargetBaseDir(dirname($minified_filename));
				$compiler->addSourceFile(basename($original_filename));
				$compiler->setTargetFile(basename($minified_filename));
				$compiler->compile();

				$reference = $minified_reference;
			}
			elseif (file_exists($minified_filename))
			{
				$reference = $minified_reference;
			}

			$reference = $this->reference($reference);
		}
		else
		{
			throw new Exception('Supplied reference does not exist');
		}

		return $reference;
	}
}

?>
