<?php

/**
 * Single Entry Controller
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
 * Controller Class
 *
 * The heavy lifter of PICKLES, makes the calls to get the session and
 * configuration loaded.  Loads modules, serves up user authentication when the
 * module asks for it, and loads the viewer that the module requested. Default
 * values are present to make things easier on the user.
 *
 * @usage <code>new Controller();</code>
 */
class Controller extends Object
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
            $request = $_REQUEST['request'];

            // Loads the module's information
            $module_class    = strtr($request, '/', '_');
            $module_filename = SITE_MODULE_PATH . $request . '.php';
            $module_exists   = file_exists($module_filename);

            // Attempts to instantiate the requested module
            if ($module_exists)
            {
                if (class_exists($module_class))
                {
                    $module = new $module_class;
                }
            }

            // No module instantiated, load up a generic Module
            if (!isset($module))
            {
                $module = new Module();
            }

            // Determines if we need to serve over HTTP or HTTPS
            if ($module->secure == false && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])
            {
                header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
                throw new Exception();
            }
            elseif ($module->secure == true && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == false))
            {
                header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
                throw new Exception();
            }

            // Gets the profiler status
            $profiler = $this->config->pickles['profiler'];
            $profiler = $profiler === true || stripos($profiler, 'timers') !== false;

            $default_method = '__default';
            $role_method    = null;

            // Attempts to execute the default method
            // @todo Seems a bit redundant, refactor
            if ($default_method == $role_method || method_exists($module, $default_method))
            {
                // Starts a timer before the module is executed
                if ($profiler)
                {
                    Profiler::timer('module ' . $default_method);
                }

                $valid_request = false;
                $error_message = 'An unexpected error has occurred.';

                // Determines if the request method is valid for this request
                if ($module->method)
                {
                    if (!is_array($module->method))
                    {
                        $module->method = [$module->method];
                    }

                    foreach ($module->method as $method)
                    {
                        if ($_SERVER['REQUEST_METHOD'] == $method)
                        {
                            $valid_request = true;
                            break;
                        }
                    }

                    if (!$valid_request)
                    {
                        // @todo Should probably utilize that AJAX flag to determine the type of return
                        $error_message = 'There was a problem with your request method.';
                    }
                }
                else
                {
                    $valid_request = true;
                }

                $valid_form_input = true;

                if ($valid_request && $module->validate)
                {
                    $validation_errors = $module->__validate();

                    if ($validation_errors)
                    {
                        $error_message    = implode(' ', $validation_errors);
                        $valid_form_input = false;
                    }
                }

                /**
                 * Note to Self: When building in caching will need to let the
                 * module know to use the cache, either passing in a variable
                 * or setting it on the object
                 */
                if ($valid_request && $valid_form_input)
                {
                    $module_return = $module->$default_method();

                    if (!is_array($module_return))
                    {
                        $module_return = $module->response;
                    }
                    else
                    {
                        $module_return = array_merge($module_return, $module->response);
                    }
                }

                // Stops the module timer
                if ($profiler)
                {
                    Profiler::timer('module ' . $default_method);
                }

                $display = new Display($module);
            }

            // Starts a timer for the display rendering
            if ($profiler)
            {
                Profiler::timer('display render');
            }

            // Renders the content
            $output = $display->render();

            // Stops the display timer
            if ($profiler)
            {
                Profiler::timer('display render');
            }
        }
        catch (Exception $e)
        {
            $output = $e->getMessage();
        }

        echo $output;

        // Display the Profiler's report if the stars are aligned
        if ($this->config->pickles['profiler'])
        {
            Profiler::report();
        }
    }
}

