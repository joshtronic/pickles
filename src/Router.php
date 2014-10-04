<?php

/**
 * Endpoint Router
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @link      http://picklesphp.com
 * @package   Pickles
 */

namespace Pickles;

/**
 * Router Class
 *
 * The heavy lifter of Pickles, makes the calls to get the session and
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
            array_unshift($nouns, '', $this->config['pickles']['namespace'], 'Resources', $version);
            $class = implode('\\', $nouns);

            // @todo Make namespace mandatory
            // Strips preceding slashs when there is no namespace
            if (strpos($class, '\\\\') === 0)
            {
                $class = substr($class, 2);
            }

            // Checks that the file is present and contains our class
            if (!class_exists($class))
            {
                throw new \Exception('Not Found.', 404);
            }

            // Instantiates our resource with the UIDs
            $resource = new $class($uids);
        }
        catch (\Exception $e)
        {
            // Creates a resource object if we don't have one
            if (!isset($resource))
            {
                $resource = new Resource();
            }

            $code = $e->getCode();

            // Anything below 200 is probably a PHP error
            if ($code < 200)
            {
                $code = 500;
            }

            $resource->status  = $code;
            $resource->message = $e->getMessage();
        }

        $resource->respond();
    }
}

