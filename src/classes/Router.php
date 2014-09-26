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
            // Catches requests that aren't lowercase
            $lowercase_request = strtolower($_REQUEST['request']);

            if ($_REQUEST['request'] != $lowercase_request)
            {
                // @todo Rework the Browser class to handle the 301 (perhaps redirect301()) to not break other code
                header('Location: ' . substr_replace($_SERVER['REQUEST_URI'], $lowercase_request, 1, strlen($lowercase_request)), true, 301);
                throw new Exception();
            }

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

            array_unshift($nouns, $version);

            $class = implode('_', $nouns);

            array_unshift($nouns, SITE_MODULE_PATH);

            $filename = implode('/', $nouns) . '.php';

            if (file_exists($filename))
            {
                if (class_exists($class))
                {
                    $resource = new $class($uids);

                    // Determines if we need to serve over HTTP or HTTPS
                    if ($resource->secure == false && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])
                    {
                        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
                        throw new Exception();
                    }
                    elseif ($resource->secure == true && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == false))
                    {
                        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
                        throw new Exception();
                    }

                    // Checks for the PHPSESSID in the query string
                    if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') === false)
                    {
                        // XHTML compliancy stuff
                        // @todo Wonder if this could be yanked now that we're in HTML5 land
                        ini_set('arg_separator.output', '&amp;');
                        ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

                        // @todo Will want to generate the header based on if we're pushing documentation or API
                        header('Content-type: text/html; charset=UTF-8');
                        // header('Content-type: application/json');
                        //header('Content-type: application/json; charset=UTF-8');
                    }
                    else
                    {
                        // Redirect so Google knows to index the page without the session ID
                        list($request_uri, $phpsessid) = explode('?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
                        header('HTTP/1.1 301 Moved Permanently');
                        header('Location: ' . $request_uri);

                        throw new Exception('Requested URI contains PHPSESSID, redirecting.');
                    }

                    // Gets the profiler status
                    $profiler = $this->config->pickles['profiler'];
                    $profiler = $profiler === true || stripos($profiler, 'timers') !== false;

                    $method = strtolower($_SERVER['REQUEST_METHOD']);

                    if (method_exists($resource, $method))
                    {
                        // Starts a timer before the resource is executed
                        if ($profiler)
                        {
                            Profiler::timer('resource ' . $method);
                        }

                        $response = new Response();

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

                        $response->respond();
                    }
                    else
                    {
                        throw new Exception('Missing method');
                    }
                }
                else
                {
                    throw new Exception('Missing class');
                }
            }
            else
            {
                throw new Exception('Missing file');
            }
        }
        catch (Exception $e)
        {
            // @todo
            exit('fuuuu');
            $output = $e->getMessage();
        }
    }
}

