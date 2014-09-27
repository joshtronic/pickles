<?php

/**
 * Resource Class File for PICKLES
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
 * Resource Class
 *
 * This is a parent class that all PICKLES modules should be extending. Each
 * module can specify it's own meta data and whether or not a user must be
 * properly authenticated to view the page. Currently any pages without a
 * template are treated as pages being requested via AJAX and the return will
 * be JSON encoded. In the future this may need to be changed out for logic
 * that allows the requested module to specify what display type(s) it can use.
 */
class Resource extends Object
{
    /**
     * Secure
     *
     * Whether or not the page should be loaded via SSL.
     *
     * @var boolean defaults to false
     */
    public $secure = false;

    /**
     * Filter
     *
     * Variables to filter.
     *
     * @var array
     */
    public $filter = [];

    /**
     * Validate
     *
     * Variables to validate.
     *
     * @var array
     */
    public $validate = [];

    // @todo Document this
    public $status   = 200;
    public $message  = 'OK';
    public $echo     = false;
    public $limit    = false;
    public $offset   = false;
    public $errors   = [];
    public $uids     = [];
    public $response = false;
    public $profiler = false;

    /**
     * Constructor
     *
     * The constructor does nothing by default but can be passed a boolean
     * variable to tell it to automatically run the __default() method. This is
     * typically used when a module is called outside of the scope of the
     * controller (the registration page calls the login page in this manner.
     */
    public function __construct($uids = false)
    {
        $this->uids = $uids;

        try
        {
            // Determines if we need to serve over HTTP or HTTPS
            if ($this->secure
                && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == false))
            {
                throw new Exception('400 - SSL is required.');
            }

            $method   = $_SERVER['REQUEST_METHOD'];
            $filter   = isset($this->filter[$method]);
            $validate = isset($this->validate[$method]);

            if ($filter || $validate)
            {
                // Hack together some new globals
                if (in_array($method, ['PUT', 'DELETE']))
                {
                    $GLOBALS['_' . $method] = [];

                    // @todo Populate it
                }

                $global =& $GLOBALS['_' . $method];

                // Checks that the required parameters are present
                // @todo Add in support for uid:* variables
                if ($validate)
                {
                    $variables = [];

                    foreach ($this->validate[$method] as $variable => $rules)
                    {
                        if (!is_array($rules))
                        {
                            $variable = $rules;
                        }

                        $variables[] = $variable;
                    }

                    $missing_variables = array_diff($variables, array_keys($global));

                    if ($missing_variables !== array())
                    {
                        foreach ($missing_variables as $variable)
                        {
                            $this->errors[$variable] = 'The ' . $variable . ' parameter is required.';
                        }
                    }
                }

                foreach ($global as $variable => $value)
                {
                    // Applies any filters
                    if ($filter && isset($this->filter[$method][$variable]))
                    {
                        $function = $this->filter[$method][$variable];

                        if ($function == 'password_hash')
                        {
                            $global[$variable] = password_hash($value, PASSWORD_DEFAULT);
                        }
                        else
                        {
                            $global[$variable] = $function($value);
                        }
                    }

                    if ($validate && isset($this->validate[$method][$variable]))
                    {
                        $rules = $this->validate[$method][$variable];

                        if (is_array($rules))
                        {
                            if (isset($global[$variable]) && !String::isEmpty($global[$variable]))
                            {
                                if (is_array($rules))
                                {
                                    $rule_errors = Validate::isValid($global[$variable], $rules);

                                    if (is_array($rule_errors))
                                    {
                                        $this->errors[$variable] = $rule_errors[0];
                                    }
                                }
                            }
                        }
                    }
                }

                // if PUT or DELETE, need to update the super globals directly as
                // they do not stay in sync. Probably need to make them global in
                // this class method
                //
                // $_PUT = $GLOBALS['_PUT'];
            }

            if ($this->errors)
            {
                throw new Exception('400 - Missing or invalid parameters.');
            }

            parent::__construct(['cache', 'db']);

            // Checks if the request method has been implemented
            //if (get_class($this) != 'Resource')
            {
                if (!method_exists($this, $method))
                {
                    throw new Exception('405 - Method not allowed.');
                }
                else
                {
                    // Gets the profiler status
                    // @todo Refactor out that stripos
                    $profiler = $this->config->pickles['profiler'];
                    $profiler = $profiler === true
                                || stripos($profiler, 'timers') !== false;

                    // Starts a timer before the resource is executed
                    if ($profiler)
                    {
                        Profiler::timer('resource ' . $method);
                    }

                    $this->response = $this->$method();

                    // Stops the resource timer
                    if ($profiler)
                    {
                        Profiler::timer('resource ' . $method);
                    }
                }
            }
        }
        catch (Exception $e)
        {
            $this->status  = 400;
            $this->message = $e->getMessage();
        }
    }

    public function respond()
    {
        header('Content-type: application/json');

        $meta = [
            'status'  => $this->status,
            'message' => $this->message,
        ];

        foreach (['echo', 'limit', 'offset', 'errors'] as $variable)
        {
            if ($this->$variable)
            {
                $meta[$variable] = $this->$variable;
            }
        }

        $response = ['meta' => $meta];

        foreach (['response', 'profiler'] as $variable)
        {
            if ($this->$variable)
            {
                $response[$variable] = $this->$variable;
            }
        }

        $pretty = isset($_REQUEST['pretty']) ? JSON_PRETTY_PRINT : false;

        echo json_encode($response, $pretty);
   }
}

