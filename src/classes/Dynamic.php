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
 * @copyright Copyright 2007-2014, Josh Sherman
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
}

