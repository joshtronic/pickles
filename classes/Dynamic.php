<?php

/**
 * Dynamic Content Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
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
			// Checks if we're working with an internal PICKLES file
			$is_internal = preg_match('/^\/__pickles\/(css|js)\/.+$/', $reference);

			$query_string = '';

			// Checks for ? and extracts query string
			if (strstr($reference, '?'))
			{
				list($reference, $query_string) = explode('?', $reference);
			}

			if ($is_internal)
			{
				// Sets the path to the actual internal path
				$file = str_replace('/__pickles/', PICKLES_PATH, $reference);
			}
			else
			{
				// Adds the dot so the file functions can find the file
				$file = '.' . $reference;
			}

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
					throw new Exception('Supplied reference does not exist');
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
	 * Attempts to minify the stylesheet and then  returns the reference URI
	 * for the file, minified or not.
	 *
	 * @param  string $reference URI reference of the Stylesheet
	 * @return string URI reference reference with dynamic content
	 */
	public function css($original_reference)
	{
		if (preg_match('/^\/__pickles\/css\/.+$/', $original_reference) == false)
		{
			// Injects .min into the filename
			$parts = explode('.', $original_reference);

			if (count($parts) == 1)
			{
				throw new Exception('Filename must have an extension (e.g. /path/to/file.css)');
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

				if (is_writable($path) && (!file_exists($minified_filename) || filemtime($original_filename) > filemtime($minified_filename)))
				{
					// Minifies CSS with a few basic character replacements.
					$stylesheet = file_get_contents($original_filename);
					$stylesheet = str_replace(array("\t", "\n", ', ', ' {', ': ', ';}'), array('', '', ',', '{', ':', '}'), $stylesheet);
					$stylesheet = preg_replace('/\/\*.+?\*\//', '', $stylesheet);
					file_put_contents($minified_filename, $stylesheet);

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
		}
		else
		{
			$reference = $this->reference($original_reference);
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
		if (preg_match('/^\/__pickles\/js\/.+$/', $original_reference) == false)
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

						if (is_writable($path) && (!file_exists($minified_filename) || filemtime($original_filename) > filemtime($minified_filename)) && extension_loaded('curl'))
						{
							// Sets up the options list
							$options = array(
								CURLOPT_URL             => 'http://closure-compiler.appspot.com/compile',
								CURLOPT_RETURNTRANSFER  => true,
								CURLOPT_HTTPHEADER      => array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'),
								CURLOPT_POST            => true,
								CURLOPT_POSTFIELDS      => 'js_code=' . urlencode(file_get_contents($original_filename)) . '&compilation_level=' . ($level . '_' . ($level == 'WHITESPACE' ? 'ONLY' : 'OPTIMIZATIONS')) . '&output_format=text&output_info=compiled_code'
							);

							try
							{
								// Executes the request
								$curl = curl_init();
								curl_setopt_array($curl, $options);
								file_put_contents($minified_filename, curl_exec($curl));
								curl_close($curl);

								$reference = $minified_reference;
							}
							catch (Exception $exception)
							{
								$reference = $original_reference;
							}
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
		}
		else
		{
			$reference = $this->reference($original_reference);
		}

		return $reference;
	}
}

?>
