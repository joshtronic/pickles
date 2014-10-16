<?php

/**
 * Sorting Utility Collection
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
 * Sort Class
 *
 * I got tired of writing separate usort functions to sort by different array
 * keys in the array, this class solves that.
 */
class Sort
{
    /**
     * Ascending
     *
     * Variable to utilize ascending sort
     *
     * @var integer
     */
    const ASC = 'ASC';

    /**
     * Descending
     *
     * Variable to utilize descending sort
     *
     * @var integer
     */
    const DESC = 'DESC';

    /**
     * Sort By
     *
     * Sorts an array by the specified column, optionally in either direction.
     *
     * @param string $field field to sort by
     * @param array $array array to sort
     * @param string $direction optional direction to sort
     * @retun boolean true because sorting is done by reference
     */
    public static function by($field, &$array, $direction = Sort::ASC)
    {
        usort($array, create_function('$a, $b', '
            $a = $a["' . $field . '"];
            $b = $b["' . $field . '"];

            if ($a == $b)
            {
                return 0;
            }

            return ($a ' . ($direction == Sort::DESC ? '>' : '<') .' $b) ? -1 : 1;
        '));

        return true;
    }
}

