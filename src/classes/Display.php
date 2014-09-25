<?php

/**
 *Display Class File for PICKLES
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
 * Display Class
 *
 * If you can see it then it probably happened in here.
 */
class Display extends Object
{
    /**
     * Module
     *
     * This is the module we are attempting to display output for.
     */
    public $module = null;

    public function __construct($module = null)
    {
        if ($module && $module instanceof Module)
        {
            $this->module = $module;
        }
    }

    public function render()
    {
        try
        {
            // Starts up the buffer so we can capture it
            ob_start();

            if (!is_array($this->module->response))
            {
                $this->module->response = [$this->module->response];
            }

            $return_json     = false;
            $return_template = false;
            $return_xml      = false;

            foreach ($this->module->output as $return)
            {
                $variable  = 'return_' . $return;
                $$variable = true;
            }

            // Makes sure the return type is valid
            if (!$return_json && !$return_template && !$return_xml)
            {
                throw new Exception('Invalid return type.');
            }

            // Checks for the PHPSESSID in the query string
            if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') === false)
            {
                // XHTML compliancy stuff
                // @todo Wonder if this could be yanked now that we're in HTML5 land
                ini_set('arg_separator.output', '&amp;');
                ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

                header('Content-type: text/html; charset=UTF-8');
            }
            else
            {
                // Redirect so Google knows to index the page without the session ID
                list($request_uri, $phpsessid) = explode('?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $request_uri);

                throw new Exception('Requested URI contains PHPSESSID, redirecting.');
            }

            $loaded = false;

            if ($return_template)
            {
                // Determines if we're using a custom class or not
                $dynamic_class = (class_exists('CustomDynamic') ? 'CustomDynamic' : 'Dynamic');
                $form_class    = (class_exists('CustomForm')    ? 'CustomForm'    : 'Form');
                $html_class    = (class_exists('CustomHTML')    ? 'CustomHTML'    : 'HTML');

                // Exposes some objects and variables to the local scope of the template
                $this->request   = $this->js_file = $_REQUEST['request'];
                $this->css_class = strtr($this->request, '/', '-');

                $this->dynamic = new $dynamic_class();
                $this->form    = new $form_class();
                $this->html    = new $html_class();

                // Checks for the parent template and tries to load it
                if ($this->module->template)
                {
                    $profiler = $this->config->pickles['profiler'];
                    $profiler = $profiler === true || stripos($profiler, 'timers') !== false;

                    // Starts a timer for the loading of the template
                    if ($profiler)
                    {
                        Profiler::timer('loading template');
                    }

                    // Assigns old variable
                    $required_template      = $this->module->templates[0];
                    $this->module->template = end($this->module->templates);
                    $loaded                 = require_once $required_template;

                    // Stops the template loading timer
                    if ($profiler)
                    {
                        Profiler::timer('loading template');
                    }
                }
            }

            $meta = [
                'status'  => $this->module->status,
                'message' => $this->module->message,
            ];

            $response = [
                'meta'     => $meta,
                'response' => $this->module->response,
            ];

            if (!$loaded)
            {
                if ($return_json)
                {
                    $pretty = isset($_REQUEST['pretty']) ? JSON_PRETTY_PRINT : false;
                    echo json_encode($response, $pretty);
                }
                elseif ($return_xml)
                {
                    echo Convert::arrayToXML($response, isset($_REQUEST['pretty']));
                }
            }

            // Grabs the buffer so we can massage it a bit
            $buffer = ob_get_clean();

            // Kills any whitespace and HTML comments in templates
            if ($loaded)
            {
                // The BSA exception is because their system sucks and demands
                // there be comments present
                $buffer = preg_replace(['/^[\s]+/m', '/<!--(?:(?!BuySellAds).)+-->/U'], '', $buffer);
            }

            return $buffer;
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }
}

