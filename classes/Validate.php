<?php

/**
 * Validator
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Validate Class
 *
 * Validation layer that's used by the Modules to validate passed data. Handles
 * single sanity checks against a variable so the validation itself can be used
 * in other places in the system
 */
class Validate
{
	/**
	 * Is Valid?
	 *
	 * Checks if a variable is valid based on the passed rules.
	 *
	 * @param  mixed $value the value to be validated
	 * @param  array $rules an array of rules (and messages) to validate with
	 * @return mixed boolean true if valid, array of errors if invalid
	 */
	public static function isValid($value, $rules)
	{
		$errors = array();

		if (is_array($rules))
		{
			foreach ($rules as $rule => $message)
			{
				$rule = explode(':', $rule);

				switch (strtolower($rule[0]))
				{
					// @todo case 'alpha':
					// @todo case 'alphanumeric':
					// @todo case 'date':

					// {{{ Checks using filter_var()

					case 'filter':
						if (count($rule) < 2)
						{
							throw new Exception('Invalid validation rule, expected: "validate:boolean|email|float|int|ip|regex|url".');
						}
						else
						{
							switch (strtolower($rule[1]))
							{
								case 'boolean':
								case 'email':
								case 'float':
								case 'int':
								case 'ip':
								case 'regex':
								case 'url':
									$filter = constant('FILTER_VALIDATE_' . strtoupper($rule[1]));
									break;

								default:
									throw new Exception('Invalid filter, expecting boolean, email, float, int, ip, regex or url.');
									break;
							}

							if (!filter_var($value, $filter))
							{
								$errors[] = $message;
							}
						}

						break;

					// }}}
					// {{{ Checks using strlen()

					case 'length':
						if (count($rule) < 3)
						{
							throw new Exception('Invalid validation rule, expected: "length:<|<=|==|!=|>=|>:integer".');
						}
						else
						{
							if (!filter_var($rule[2], FILTER_VALIDATE_INT))
							{
								throw new Exception('Invalid length value, expecting an integer.');
							}
							else
							{
								$length = strlen($value);

								switch ($rule[1])
								{
									case '<':  $valid = $length <  $rule[2]; break;
									case '<=': $valid = $length <= $rule[2]; break;
									case '==': $valid = $length == $rule[2]; break;
									case '!=': $valid = $length != $rule[2]; break;
									case '>=': $valid = $length >= $rule[2]; break;
									case '>':  $valid = $length >  $rule[2]; break;

									default:
										throw new Exception('Invalid operator, expecting <, <=, ==, !=, >= or >.');
										break;
								}

								if ($valid === true)
								{
									$errors[] = $message;
								}
							}
						}

						break;

					// }}}

					// @todo case 'range':

					// {{{ Checks using preg_match()

					case 'regex':
						if (count($rule) < 3)
						{
							throw new Exception('Invalid validation rule, expected: "regex:is|not:string".');
						}
						else
						{
							if ((strtolower($rule[1]) == 'not' && !preg_match($rule[2], $value)) || preg_match($rule[2], $value))
							{
								$errors[] = $message;
							}
						}
						break;

					// }}}
				}

			}
		}

		return count($errors) ? $errors : true;
	}
}

?>
