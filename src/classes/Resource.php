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
abstract class Resource extends Object
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

    // @todo
    public $status  = 200;
    public $message = 'OK';
    public $echo    = false;
    public $limit   = false;
    public $offset  = false;
    public $errors  = [];

    // @todo if $status != 200 && $message == 'OK' ...

    /**
     * Constructor
     *
     * The constructor does nothing by default but can be passed a boolean
     * variable to tell it to automatically run the __default() method. This is
     * typically used when a module is called outside of the scope of the
     * controller (the registration page calls the login page in this manner.
     */
    public function __construct()
    {
        parent::__construct(['cache', 'db']);
    }

    /**
     * Validate
     *
     * Internal validation for data passed to a Module. Grabs the super global
     * based on the Module's request method and loops through the data using the
     * Module's validation array (if present) sanity checking each variable
     * against the rules.
     *
     * @return mixed boolean false if everything is fine or an array or errors
     */
    public function __validate()
    {
        $errors = [];

        if ($this->validate)
        {
            if (is_array($this->method))
            {
                $this->method = $this->method[0];
            }

            switch (strtoupper($this->method))
            {
                case 'GET':
                    $global = &$_GET;
                    break;

                case 'POST':
                    $global = &$_POST;
                    break;

                default:
                    $global = &$_REQUEST;
                    break;
            }

            foreach ($this->validate as $variable => $rules)
            {
                if (!is_array($rules) && $rules !== true)
                {
                    $variable = $rules;
                    $rules    = true;
                }

                if (isset($global[$variable]) && !String::isEmpty($global[$variable]))
                {
                    if (is_array($rules))
                    {
                        $rule_errors = Validate::isValid($global[$variable], $rules);

                        if (is_array($rule_errors))
                        {
                            $errors = array_merge($errors, $rule_errors);
                        }
                    }
                }
                else
                {
                    $errors[] = 'The ' . $variable . ' field is required.';
                }
            }
        }

        return $errors == [] ? false : $errors;
    }
}

