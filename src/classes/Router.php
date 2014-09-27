<?php

/**
 * Single Entry Router
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
 * Router Class
 *
 * The heavy lifter of PICKLES, makes the calls to get the session and
 * configuration loaded.  Loads modules, serves up user authentication when the
 * module asks for it, and loads the viewer that the module requested. Default
 * values are present to make things easier on the user.
 *
 * @usage <code>new Router();</code>
 */
class Router extends Object
{
    /**
     * Constructor
     *
     * To save a few keystrokes, the Controller is executed as part of the
     * constructor instead of via a method. You either want the Controller or
     * you don't.
     */
    public function __construct()
    {
        parent::__construct();

        try
        {
            // Grabs the requested page
            $request    = $_REQUEST['request'];
            $components = explode('/', $request);
            $version    = array_shift($components);
            $nouns      = [];
            $uids       = [];

            // Loops through the components to determine nouns and IDs
            foreach ($components as $index => $component)
            {
                if ($index % 2)
                {
                    $uids[end($nouns)] = $component;
                }
                else
                {
                    $nouns[] = $component;
                }
            }

            // Creates our class name
            array_unshift($nouns, $version);
            $class = implode('_', $nouns);

            // Creates our filename
            array_unshift($nouns, SITE_RESOURCE_PATH);
            $filename = implode('/', $nouns) . '.php';

            // Checks that the file is present and contains our class
            if (!file_exists($filename) || !class_exists($class))
            {
                throw new Exception('404 - Not Found.');
            }

            // Instantiates our resource with the UIDs
            $resource = new $class($uids);
        }
        catch (Exception $e)
        {
            // Creates a resource object if we don't have one
            if (!isset($resource))
            {
                $resource = new Resource();
            }

            $resource->status  = 400;
            $resource->message = $e->getMessage();
        }

        $resource->respond();
    }
}

