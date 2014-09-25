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

            $response = [
                'meta' => [
                    'status'  => $this->module->status,
                    'message' => $this->module->message,
                ],
            ];

            if ($this->module->response)
            {
                $response['response'] = $this->module->response;
            }

            header('Content-type: application/json');
            $pretty = isset($_REQUEST['pretty']) ? JSON_PRETTY_PRINT : false;
            echo json_encode($response, $pretty);

            return ob_get_clean();
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }
}

