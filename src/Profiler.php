<?php

/**
 * Profiler
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @link      https://github.com/joshtronic/pickles
 * @package   Pickles
 */

namespace Pickles;

/**
 * Profiler Class
 *
 * The Profiler class is statically interfaced with and allows for in depth
 * profiling of a site. By default profiling is off, but can be enabled in the
 * config.ini for a site. Out of the box the profiler will report on every
 * class object in the system that extends the code Object class.
 *
 * @usage <code>Profiler::log('some action you want to track');</code>
 * @usage <code>Profiler::log($object, 'methodName');</code>
 */
class Profiler
{
    /**
     * Logs
     *
     * Array of logged events
     *
     * @static
     * @access private
     * @var    array
     */
    private static $logs = [];

    /**
     * Timers
     *
     * Array of active timers
     *
     * @static
     * @access private
     * @var    array
     */
    private static $timers = [];

    /**
     * Log
     *
     * Logs the event to be displayed later on. Due to the nature of how much
     * of a pain it is to determine which class method called this method I
     * opted to make the method a passable argument for ease of use. Perhaps
     * I'll revisit in the future. Handles all elapsed time calculations and
     * memory usage.
     *
     * @static
     * @param  mixed $data data to log
     * @param  string $method name of the class method being logged
     */
    public static function log($data, $method = false, $type = false)
    {
        $time      = microtime(true);
        $data_type = ($data == 'timer' ? $data : gettype($data));

        // Tidys the data by type
        switch ($data_type)
        {
            case 'object':
                $details['class'] = get_class($data);

                if ($method != '')
                {
                    $details['method'] = $method . '()';
                }

                $data_type = $data_type;
                break;

            case 'timer':
                $details   = $method;
                $data_type = $data_type;
                break;

            default:
                if ($type != false)
                {
                    $data_type = $type;
                }

                $details = $data;
                break;
        }

        self::$logs[] = [
            'type'         => $data_type,
            'timestamp'    => $time,
            'elapsed_time' => $time - $_SERVER['REQUEST_TIME_FLOAT'],
            'memory_usage' => memory_get_usage(),
            'details'      => $details,
        ];
    }

    /**
     * Query
     *
     * Serves as a wrapper to get query data to the log function
     *
     * @static
     * @param  string $query the query being executed
     * @param  array $input_parameters optional prepared statement data
     * @param  array $results optional results of the query
     * @param  float $duration the speed of the query
     * @param  array $explain EXPLAIN data for the query
     */
    public static function query($query, $input_parameters = false, $results = false, $duration = false, $explain = false)
    {
        $log = [
            'query'          => $query,
            'parameters'     => $input_parameters,
            'results'        => $results,
            'execution_time' => $duration,
        ];

        if ($explain)
        {
            $log['explain'] = $explain;
        }

        self::log($log, false, 'database');
    }

    /**
     * Timer
     *
     * Logs the start and end of a timer.
     *
     * @param  string $timer name of the timer
     * @return boolean whether or not timer profiling is enabled
     */
    public static function timer($timer)
    {
        // Starts the timer
        if (!isset(self::$timers[$timer]))
        {
            self::$timers[$timer] = microtime(true);

            self::Log('timer', [
                'action' => 'start',
                'name'   => $timer
            ]);
        }
        // Ends the timer
        else
        {
            self::Log('timer', [
                'action'       => 'stop',
                'name'         => $timer,
                'elapsed_time' => (microtime(true) - self::$timers[$timer])
            ]);

            unset(self::$timers[$timer]);
        }
    }

    /**
     * Report
     *
     * Generates the Profiler report that is displayed by the Controller.
     * Contains all the HTML needed to display the data properly inline on the
     * page. Will generally be displayed after the closing HTML tag.
     */
    public static function report()
    {
        $report = [
            'request_time'       => $_SERVER['REQUEST_TIME_FLOAT'],
            'execution_time'     => self::$logs[count(self::$logs) - 1]['timestamp']
                                    - $_SERVER['REQUEST_TIME_FLOAT'],
            'peak_memory_usage'  => memory_get_peak_usage(),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit'       => ini_get('memory_limit'),
            'included_files'     => count(get_included_files()),
            'logs'               => self::$logs,
        ];

        self::$logs   = [];
        self::$timers = [];

        return $report;
    }
}

