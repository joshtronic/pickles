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
 * @copyright Copyright 2007-2013, Joshua Sherman
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

			// @todo LESS and SASS compiling should happen regardless of minification
			if ($this->config->pickles['minify'] === true)
			{
				// Compiles LESS & SASS to CSS before minifying
				if ($less || $sass)
				{
					$compiled_filename = str_replace('.min', '', $minified_filename);

					if ($less)
					{
						// I couldn't get getenv() to give me the PATH value... so yeah, there's that.
						exec('echo $PATH', $path);
						putenv('PATH=' . $path[0] . PATH_SEPARATOR . '/usr/local/bin');

						$command = 'lessc ' . $original_filename . ' > ' . $compiled_filename;
					}
					elseif ($sass)
					{
						$command = 'sass ' . $original_filename . ':' . $compiled_filename;
					}

					exec($command, $output, $return);

					if ($return !== 0)
					{
						throw new Exception('There was an error executing `' . $command . '` it returned exit code ' . $return);
					}

					$original_filename = $compiled_filename;
				}

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
	public function js($original_reference, $level = 'simple')
	{
		$level = strtoupper($level);

		switch ($level)
		{
			CASE 'WHITESPACE':
			CASE 'SIMPLE':
			CASE 'ADVANCED':
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

					if (is_writable($path)
						&& (!file_exists($minified_filename) || filemtime($original_filename) > filemtime($minified_filename))
						&& extension_loaded('curl')
						&& $this->config->pickles['minify'] === true)
					{
						$config = Config::getInstance();

						exec('java -jar ' . $config->pickles['path'] . 'vendors/google/closure-compiler/compiler.jar --js=' . $original_filename . ' --compilation_level='  . ($level . '_' . ($level == 'WHITESPACE' ? 'ONLY' : 'OPTIMIZATIONS')) . ' --js_output_file=' . $minified_filename);

						$reference = $minified_reference;
					}
					elseif (file_exists($minified_filename))
					{
						$reference = $minified_reference;
					}
					else
					{
						Log::warning('Unable to minify ' . $original_reference . ' and a minified copy does not already exist');
					}

					$reference = $this->reference($reference);
				}
				else
				{
					throw new Exception('Supplied reference does not exist');
				}

				break;

			default:
				throw new Exception('The level "' . $level . '" is invalid. Valid levels include "whitespace", "simple" and "advanced"');
				break;
		}

		return $reference;
	}
}

?>
