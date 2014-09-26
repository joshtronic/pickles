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

        $response = new Response();

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
            array_unshift($nouns, SITE_MODULE_PATH);
            $filename = implode('/', $nouns) . '.php';

            if (!file_exists($filename))
            {
                // @todo Should be a 404, will need to change it up after I add
                //       namespaces and a Pickles\Exception
                throw new Exception('Cannot find the file ' . $filename);
            }

            if (!class_exists($class))
            {
                throw new Exception('Cannot find the class ' . $class);
            }

            $resource = new $class($uids);

            // Determines if we need to serve over HTTP or HTTPS
            if ($resource->secure == false && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])
            {
                throw new Exception('This resource expects HTTPS communication.');
            }
            elseif ($resource->secure == true && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == false))
            {
                throw new Exception('This resource expects HTTP communication.');
            }

            // Gets the profiler status
            $profiler = $this->config->pickles['profiler'];
            $profiler = $profiler === true || stripos($profiler, 'timers') !== false;

            $method = strtolower($_SERVER['REQUEST_METHOD']);

            if (!method_exists($resource, $method))
            {
                throw new Exception('Cannot find the method ' . $class . '::' . $method);
            }

            // Starts a timer before the resource is executed
            if ($profiler)
            {
                Profiler::timer('resource ' . $method);
            }

            if ($resource->validate)
            {
                $validation_errors = $resource->__validate();

                if ($validation_errors)
                {
                    $response->status  = 400;
                    $response->message = implode(' ', $validation_errors);
                }
            }

            if ($response->status == 200)
            {
                $resource_return = $resource->$method();

                if ($resource_return)
                {
                    $response->response = $resource_return;
                }
            }

            // Stops the resource timer
            if ($profiler)
            {
                Profiler::timer('resource ' . $method);
            }
        }
        catch (Exception $e)
        {
            $response->status  = 500;
            $response->message = $e->getMessage();
        }

        $response->respond();
    }
}

