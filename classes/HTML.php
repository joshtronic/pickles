<?php

/**
 * HTML Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * HTML Class
 *
 * This class contains methods for easily generating HTML elements.
 */
class HTML extends Object
{
	private $self_closing = ['br', 'hr', 'img', 'input', 'link', 'meta'];

	public function __call($method, $arguments)
	{
		$attributes = null;
		$contents   = null;

		if (isset($arguments[0]))
		{
			$attributes = $arguments[0];
		}

		if (isset($arguments[1]))
		{
			$contents = $arguments[1];
		}

		// ->inputType('name', $attributes);
		if (preg_match('/^input/', $method) && !isset($attributes['label']))
		{
			$type = strtolower(str_replace('input', '', $method));

			switch ($type)
			{
				case 'datetimelocal': $type = 'datetime-local'; break;
				case '':              $type = 'text';           break;
			}

			$method = 'input';

			if (is_array($attributes))
			{
				$attributes['type'] = $type;
			}
			else
			{
				$attributes = ['type' => $type];
			}
		}

		if (is_array($attributes) && isset($attributes['label']))
		{
			if (isset($attributes['name']))
			{
				$label = $this->label(['for' => $attributes['name']], $attributes['label']);
			}
			else
			{
				$label = $this->label($attributes['label']);
			}

			unset($attributes['label']);

			return $label . $this->$method($attributes, $contents);
		}
		else
		{
			return $this->element($method, $attributes, $contents);
		}
	}

	// {{{ Get Instance

	/**
	 * Get Instance
	 *
	 * Gets an instance of the Form class
	 *
	 * @static
	 * @param  string $class name of the class to get an instance of
	 * @return object instance of the class
	 */
	public static function getInstance($class = 'HTML')
	{
		return parent::getInstance($class);
	}

	// }}}

	public function element($element)
	{
		$attributes = null;
		$contents   = null;

		foreach (func_get_args() as $key => $value)
		{
			if ($key && $key < 3)
			{
				if (is_array($value))
				{
					$attributes = $value;
				}
				elseif ($value)
				{
					$contents = $value;
				}
			}
		}

		$element = strtolower($element);
		$html    = '<' . $element;

		if ($attributes)
		{
			if (is_array($attributes))
			{
				foreach ($attributes as $attribute => $value)
				{
					$html .= ' ' . $attribute . '="' . str_replace('"', '\"', $value) . '"';
				}
			}
		}

		$html .= '>';

		if (!in_array($element, $this->self_closing))
		{
			$html .= $contents . '</' . $element . '>';
		}

		return $html;
	}
}

?>
