<?php

/**
 * Converter
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Convert Class
 *
 * Collection of statically called methods to help aid in converting formats.
 */
class Convert
{
	/**
	 * To JSON
	 *
	 * Encodes passed variable as JSON.
	 *
	 * Requires PHP 5 >= 5.2.0 or PECL json >= 1.2.0
	 * Note: PECL json 1.2.1 is included /vendors
	 *
	 * @link http://json.org/
	 * @link http://us.php.net/json_encode
	 * @link http://pecl.php.net/package/json
	 *
	 * @static
	 * @param  mixed $variable variable to convert
	 * @return JSON encoded string
	 */
	public static function toJSON($variable)
	{
		if (JSON_AVAILABLE)
		{
			return json_encode($variable);
        }
		else
		{
            return '{ "status": "error", "message": "json_encode() not found" }';
        }
	}

	/**
	 * Array to XML
	 *
	 * Converts an array into XML tags (recursive). This method expects the
	 * passed array to be formatted very specifically to accomodate the fact
	 * that an array's format isn't quite the same as well-formed XML.
	 *
	 * Input Array =
	 *     array('children' => array(
	 *         'child' => array(
	 *             array('name' => 'Wendy Darling'),
	 *             array('name' => 'John Darling'),
	 *             array('name' => 'Michael Darling')
	 *         )
	 *     ))
	 *
	 * Output XML =
	 *     <children>
	 *         <child><name>Wendy Darling</name></child>
	 *         <child><name>John Darling</name></child>
	 *         <child><name>Michael Darling</name></child>
	 *     </children>
	 *
	 * @static
	 * @param  array $array array to convert into XML
	 * @return string generated XML
	 */
	public static function arrayToXML($array, $format = false, $level = 0)
	{
		$xml = '';

		if (is_array($array))
		{
			foreach ($array as $node => $value)
			{
				// Checks if the value is an array
				if (is_array($value))
				{
					foreach ($value as $node2 => $value2)
					{
						if (is_array($value2))
						{
							// Nest the value if the node is an integer
							$new_value = (is_int($node2) ? $value2 : array($node2 => $value2));

							$xml .= ($format ? str_repeat("\t", $level) : '');
							$xml .= '<' . $node . '>' . ($format ? "\n" : '');
							$xml .= self::arrayToXML($new_value, $format, $level + 1);
							$xml .= ($format ? str_repeat("\t", $level) : '');
							$xml .= '</' . $node . '>' . ($format ? "\n" : '');
						}
						else
						{
							if (is_int($node2))
							{
								$node2 = $node;
							}

							// Checks for special characters
							if (htmlspecialchars($value2) != $value2)
							{
								$xml .= ($format ? str_repeat("\t", $level) : '');
								$xml .= '<' . $node2 . '><![CDATA[' . $value2 . ']]></' . $node2 . '>' . ($format ? "\n" : '');
							}
							else
							{
								$xml .= ($format ? str_repeat("\t", $level) : '');
								$xml .= '<' . $node2 . '>' . $value2 . '</' . $node2 . '>' . ($format ? "\n" : '');
							}
						}
					}
				}
				else
				{
					// Checks for special characters
					if (htmlspecialchars($value) != $value)
					{
						$xml .= ($format ? str_repeat("\t", $level) : '');
						$xml .= '<' . $node . '><![CDATA[' . $value . ']]></' . $node . '>' . ($format ? "\n" : '');
					}
					else
					{
						$xml .= ($format ? str_repeat("\t", $level) : '');
						$xml .= '<' . $node . '>' . $value . '</' . $node . '>' . ($format ? "\n" : '');
					}
				}
			}
		}

		return $xml;
	}
}

?>
