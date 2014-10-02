<?php

/**
 * File Utility Collection
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

namespace Pickles;

/**
 * File Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant file related manipulation.
 */
class File
{
    /**
     * Remove a Directory, Recursively
     *
     * Removes a directory by emptying all of the contents recursively and then
     * removing the directory, as PHP will not let you rmdir() on ain non-empty
     * directory. Use with caution, seriously.
     *
     * @static
     * @param  string $directory directory to remove
     * @return boolean status of the final rmdir();
     */
    public static function removeDirectory($directory)
    {
        // If directory is a directory, read in all the files
        if (is_dir($directory))
        {
            if (substr($directory, -1) != '/')
            {
                $directory .= '/';
            }

            $files = scandir($directory);

            // Loop through said files, check for directories, and unlink files
            foreach ($files as $file)
            {
                if (!in_array($file, ['.', '..']))
                {
                    if (is_dir($directory . $file))
                    {
                        File::removeDirectory($directory . $file);
                    }
                    else
                    {
                        unlink($directory . $file);
                    }
                }
            }

            rmdir($directory);
        }
        else
        {
            unlink($directory);
        }
    }
}

