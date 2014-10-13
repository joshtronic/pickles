<?php

/**
 * Resource Class
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
 * Resource Class
 *
 * This is a parent class that all Pickles modules should be extending. Each
 * module can specify it's own meta data and whether or not a user must be
 * properly authenticated to view the page. Currently any pages without a
 * template are treated as pages being requested via AJAX and the return will
 * be JSON encoded. In the future this may need to be changed out for logic
 * that allows the requested module to specify what display type(s) it can use.
 */
class Resource extends Object
{
    /**
     * HTTPS
     *
     * Whether or not the page should be loaded via HTTP Secure.
     *
     * @var boolean defaults to false
     */
    public $https = false;

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
    public $description = [];
    public $auth        = false;
    public $status      = 200;
    public $message     = 'OK';
    public $echo        = false;
    public $limit       = false;
    public $offset      = false;
    public $errors      = [];
    public $uids        = [];
    public $response    = false;
    public $profiler    = false;

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
        parent::__construct();

        $this->uids = $uids;
        $method     = $_SERVER['REQUEST_METHOD'];

        try
        {
            // Determines if we need to serve over HTTP or HTTPS
            if (($this->https === true
                || (isset($this->https[$method]) && $this->https[$method]))
                && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == false))
            {
                throw new \Exception('HTTPS is required.', 400);
            }

            // Check auth if flag is  explicitly true or is true for the method
            /*
            if ($this->auth === true
                || (isset($this->auth[$method]) && $this->auth[$method]))
            {
                if (!$this->config['auth'][$_SERVER['__version']])
                {
                    throw new \Exception('Authentication is not configured properly.', 401);
                }

                switch ($this->config['auth'][$_SERVER['__version']]['strategy'])
                {
                    case 'basic':
                        // @todo Check if Auth class has been implemented, if
                        //       not, fallback to the parent

                        // This class should be in the classes directory of the service
                        $auth = '\\' . $this->config['pickles']['namespace'] . '\\Classes\\Auth';

                        // Strips preceding slashs when there is no namespace
                        if (strpos($auth, '\\\\') === 0)
                        {
                            $auth = substr($auth, 2);
                        }

                        // @todo Custom method
                        if (!$auth::basic())
                        {
                            throw new \Exception('Invalid authentication credentials.', 401);
                        }
                        break;

                    default:
                        throw new \Exception('Invalid authentication strategy.', 401);
                        break;
                }
            }
            */

            // Hack together some new globals
            if (in_array($method, ['PUT', 'DELETE']))
            {
                $GLOBALS['_' . $method] = [];

                // @todo Populate it
            }

            $filter   = isset($this->filter[$method]);
            $validate = isset($this->validate[$method]);

            if ($filter || $validate)
            {
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
                            $this->errors[$variable][] = 'The ' . $variable . ' parameter is required.';
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
                                    foreach ($rules as $rule => $message)
                                    {
                                        $rule = explode(':', $rule);

                                        for ($i = 1; $i <= 2; $i++)
                                        {
                                            if (!isset($rule[$i]))
                                            {
                                                $rule[$i] = false;
                                            }
                                        }

                                        switch ($rule[0])
                                        {
                                            // {{{ Checks using filter_var()

                                            case 'filter':
                                                switch ($rule[1])
                                                {
                                                    case 'boolean':
                                                    case 'email':
                                                    case 'float':
                                                    case 'int':
                                                    case 'ip':
                                                    case 'url':
                                                        $filter = constant('FILTER_VALIDATE_' . strtoupper($rule[1]));

                                                        if (!filter_var($value, $filter))
                                                        {
                                                            $this->errors[$variable][] = $message;
                                                        }
                                                        break;

                                                    default:
                                                        $this->errors[$variable] = 'Invalid filter, expecting boolean, email, float, int, ip or url.';
                                                        break;
                                                }

                                                break;

                                            // }}}
                                            // {{{ Checks using strlen()

                                            case 'length':
                                                $length = strlen($value);

                                                switch ($rule[1])
                                                {
                                                    case '<':
                                                        $valid = $length < $rule[2];
                                                        break;

                                                    case '<=':
                                                        $valid = $length <= $rule[2];
                                                        break;

                                                    case '==':
                                                        $valid = $length == $rule[2];
                                                        break;

                                                    case '!=':
                                                        $valid = $length != $rule[2];
                                                        break;

                                                    case '>=':
                                                        $valid = $length >= $rule[2];
                                                        break;

                                                    case '>':
                                                        $valid = $length >  $rule[2];
                                                        break;

                                                    default:
                                                        $valid   = false;
                                                        $message = 'Invalid operator, expecting <, <=, ==, !=, >= or >.';
                                                        break;
                                                }

                                                if (!$valid)
                                                {
                                                    $this->errors[$variable][] = $message;
                                                }

                                                break;

                                            // }}}
                                            // {{{ Checks using preg_match()

                                            case 'regex':
                                                if (preg_match($rule[1], $value))
                                                {
                                                    $this->errors[$variable][] = $message;
                                                }
                                                break;

                                            // }}}
                                            // @todo case 'alpha':
                                            // @todo case 'alphanumeric':
                                            // @todo case 'date':
                                            // @todo case 'range':
                                        }
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
                throw new \Exception('Missing or invalid parameters.', 400);
            }

            parent::__construct(['cache', 'db']);

            // Checks if the request method has been implemented
            if (get_class($this) != 'Pickles\\Resource')
            {
                if (!method_exists($this, $method))
                {
                    throw new \Exception('Method not allowed.', 405);
                }
                else
                {
                    // Starts a timer before the resource is executed
                    if ($this->config['pickles']['profiler'])
                    {
                        $timer = get_class($this) . '->' . $method . '()';
                        Profiler::timer($timer);
                    }

                    $this->response = $this->$method();

                    // Stops the resource timer
                    if ($this->config['pickles']['profiler'])
                    {
                        Profiler::timer($timer);
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            $code = $e->getCode();

            // Anything below 200 is probably a PHP error
            if ($code < 200)
            {
                $code = 500;
            }

            $this->status  = $code;
            $this->message = $e->getMessage();
        }
    }

    public function respond()
    {
        http_response_code($this->status);
        header('Content-Type: application/json');
        header('X-Powered-By: Pickles v2 - https://picklesphp.com');

        $meta = [
            'status'  => $this->status,
            'message' => $this->message,
        ];

        // Forces errors to be an array of arrays
        if ($this->errors)
        {
            foreach ($this->errors as $key => $error)
            {
                if (!is_array($error))
                {
                    $this->errors[$key] = [$error];
                }
            }
        }

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

        if ($this->config['pickles']['profiler'])
        {
            $response['profiler'] = Profiler::report();
        }

        $pretty = isset($_REQUEST['pretty']) ? JSON_PRETTY_PRINT : false;

        echo json_encode($response, $pretty);
   }
}

