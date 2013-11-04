<?php

/**
 * Form Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Form Class
 *
 * This class contains methods for easily generating form elements. There is a
 * heavy focus on select boxes as they have the most overhead for a developer.
 *
 * @deprecated
 */
class Form extends Object
{
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
	public static function getInstance($class = 'Form')
	{
		return parent::getInstance($class);
	}

	// }}}
	// {{{ Input

	/**
	 * Input
	 *
	 * Generates an input with the passed data.
	 *
	 * @param  string $name name (and ID) for the element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @param  string $type optional type of input
	 * @param  boolean $checked optional whether the input is checked
	 * @return string HTML for the input
	 */
	public function input($name, $value = null, $classes = null, $additional = null, $type = 'text', $checked = false)
	{
		if ($additional)
		{
			$additional = ' ' . $additional;
		}

		if (in_array($type, array('checkbox', 'radio')) && $checked == true)
		{
			$additional .= ' checked="checked"';
		}

		if ($value)
		{
			$additional .= ' value="' . $value . '"';
		}

		if ($classes)
		{
			$additional .= ' class="' . $classes . '"';
		}

		return '<input type="' . $type . '" name="' . $name . '" id="' . $name . '"' . $additional . ' />';
	}

	// }}}
	// {{{ Hidden

	/**
	 * Hidden
	 *
	 * Shorthand method to generate a hidden input.
	 *
	 * @param  string $name name (and ID) for the element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function hidden($name, $value = null, $classes = null, $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'hidden');
	}

	/**
	 * Hidden Input
	 *
	 * Shorthand method to generate a hidden input.
	 *
	 * @deprecated Use hidden() instead
	 *
	 * @param  string $name name (and ID) for the element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function hiddenInput($name, $value = null, $classes = null, $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'hidden');
	}

	// }}}
	// {{{ Password

	/**
	 * Password
	 *
	 * Shorthand method to generate a password input.
	 *
	 * @param  string $name name (and ID) for the element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function password($name, $value = null, $classes = null, $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'password');
	}

	/**
	 * Password Input
	 *
	 * Shorthand method to generate a password input.
	 *
	 * @deprecated Use password() instead
	 *
	 * @param  string $name name (and ID) for the element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function passwordInput($name, $value = null, $classes = null, $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'password');
	}

	// }}}
	// {{{ Submit

	/**
	 * Submit
	 *
	 * Shorthand method to generate a submit input (button).
	 *
	 * @param  string $name name (and ID) for the input element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function submit($name, $value = null, $classes = null, $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'submit');
	}

	/**
	 * Submit Input
	 *
	 * Shorthand method to generate a submit input (button).
	 *
	 * @deprecated Use submit() instead
	 *
	 * @param  string $name name (and ID) for the input element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function submitInput($name, $value = null, $classes = null, $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'submit');
	}

	// }}}
	// {{{ Security

	/**
	 * Security
	 *
	 * Generates a hidden input with an SHA1 hash as the value. The name of the
	 * field is cannot be changed as this method was only intended for use with
	 * forms that are submitted via AJAX to provide better security.
	 *
	 * @param  string $value value to hash
	 * @return string HTML for the input
	 */
	public function security($value)
	{
		// Returns the hidden input
		return $this->hiddenInput('security_hash', Security::generateHash($value));
	}

	/**
	 * Security Input
	 *
	 * Generates a hidden input with an SHA1 hash as the value. The name of the
	 * field is cannot be changed as this method was only intended for use with
	 * forms that are submitted via AJAX to provide better security.
	 *
	 * @deprecated Use security() instead
	 *
	 * @param  string $value value to hash
	 * @return string HTML for the input
	 */
	public function securityInput($value)
	{
		// Returns the hidden input
		return $this->hiddenInput('security_hash', Security::generateHash($value));
	}

	// }}}
	// {{{ Checkbox

	/**
	 * Checkbox
	 *
	 * Generates a checkbox input with the passed data.
	 *
	 * @param  string $name name (and ID) for the select element
	 * @param  string $value optional preset value
	 * @param  boolean $checked optional whether the checkbox is checked
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function checkbox($name, $value = null, $checked = false, $classes = null, $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'checkbox', $checked);
	}

	// }}}
	// {{{ Checkboxes

	// @todo

	// }}}
	// {{{ Radio Button

	/**
	 * Radio Button
	 *
	 * Generates a radio input with the passed data.
	 *
	 * @param  string $name name (and ID) for the select element
	 * @param  string $value optional preset value
	 * @param  boolean $checked optional whether the checkbox is checked
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function radio($name, $value = null, $checked = false, $classes = null, $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'radio', $checked);
	}

	// }}}
	// {{{ Radio Buttons

	// @todo

	// }}}
	// {{{ Text Area

	/**
	 * Textarea
	 *
	 * Generates a textarea with the passed data.
	 *
	 * @param  string $name name (and ID) for the select element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @param  string $type optional type of input
	 * @return string HTML for the input
	 */
	public function textarea($name, $value = null, $classes = null, $additional = null)
	{
		if ($additional)
		{
			$additional = ' ' . $additional;
		}

		if ($classes)
		{
			$additional .= ' class="' . $classes . '"';
		}

		return '<textarea name="' . $name . '" id="' . $name . '"' . $additional . '>' . $value . '</textarea>';
	}

	// }}}
	// {{{ Select

	/**
	 * Select
	 *
	 * Generates a select box with the passed data.
	 *
	 * @param  string $name name (and ID) for the select element
	 * @param  array $options key/values for the option elements
	 * @param  string $selected optional selected option
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the select box
	 */
	public function select($name, $options, $selected = null, $classes = null, $additional = null)
	{
		if ($additional)
		{
			$additional = ' ' . $additional;
		}

		if ($classes)
		{
			$additional .= ' class="' . $classes . '"';
		}

		return '<select id="' . $name . '" name="' . $name . '" class="' . $classes . '"' . $additional . '>' . $this->options($options, $selected) . '</select>';
	}

	// }}}
	// {{{ Options

	/**
	 * Options
	 *
	 * Generates the option elements from the passed array
	 *
	 * @param  array $options key/values for the options
	 * @param  string $selected optional default option
	 * @return string HTML for the options
	 */
	public function options($options, $selected = null)
	{
		$found_selected = false;
		$options_html   = '';

		if (is_array($options))
		{
			foreach ($options as $main_key => $main_label)
			{
				if (is_array($main_label))
				{
					$options_html .= '<optgroup label="' . addslashes($main_key) . '">';

					foreach ($main_label as $sub_key => $sub_label)
					{
						$selected_attribute = false;
						if ($selected !== null && $found_selected === false)
						{
							if ($selected == $sub_key)
							{
								$selected_attribute = ' selected="selected"';
								$found_selected     = true;
							}
						}

						$options_html .= '<option label="' . addslashes($sub_label) . '" value="' . $sub_key . '"' . $selected_attribute . '>' . $sub_label . '</option>';
					}

					$options_html .= '</optgroup>';
				}
				else
				{
					$selected_attribute = false;
					if ($selected !== null && $found_selected === false)
					{
						if ($selected == $main_key)
						{
							$selected_attribute = ' selected="selected"';
							$found_selected     = true;
						}
					}

					$options_html .= '<option label="' . addslashes($main_label) . '" value="' . $main_key . '"' . $selected_attribute . '>' . $main_label . '</option>';
				}
			}
		}

		if ($selected !== null && $found_selected === false)
		{
			$options_html .= '<option value="' . $selected . '" selected="selected" class="error">' . $selected . '</option>';
		}

		return $options_html;
	}

	// }}}
	// {{{ State Select

	/**
	 * State Select
	 *
	 * Generates a select box with the United States, Puerto Rico and miliary
	 * options
	 *
	 * @param  string $name optional name (and ID) for the select element
	 * @param  string $selected optional selected option
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the select box
	 */
	public function stateSelect($name = 'state', $selected = null, $classes = null, $additional = null)
	{
		$options = array(
			null => '-- Select State --',
			'AK' => 'Alaska',
			'AL' => 'Alabama',
			'AS' => 'American Samoa',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'GU' => 'Guam',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MH' => 'Marshall Islands',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'MP' => 'Northern Mariana Islands',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PW' => 'Palau',
			'PA' => 'Pennsylvania',
			'PR' => 'Puerto Rico',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VI' => 'Virgin Islands',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
			'AE' => 'Armed Forces Africa',
			'AA' => 'Armed Forces Americas (except Canada)',
			'AE' => 'Armed Forces Canada',
			'AE' => 'Armed Forces Europe',
			'AE' => 'Armed Forces Middle East',
			'AP' => 'Armed Forces Pacific'
		);

		return $this->select($name, $options, $selected, $classes, $additional);
	}

	// }}}
	// {{{ Date Select

	/**
	 * Date Select
	 *
	 * Generates 3 select boxes (month, day, year)
	 *
	 * @param  string $name optional name (and ID) for the select element
	 * @param  string $selected optional selected option
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @param  integer $start_year optional first year to display
	 * @param  integer $end_year optional last year to display
	 * @return string HTML for the select boxes
	 */
	public function dateSelect($name = 'date', $selected = null, $classes = null, $additional = null, $start_year = null, $end_year = null)
	{
		$html = '';

		// Breaks apart the selected value if present
		if ($selected == null || $selected == '0000-00-00')
		{
			$selected_month = null;
			$selected_day   = null;
			$selected_year  = null;
		}
		else
		{
			list($selected_year, $selected_month, $selected_day) = explode('-', $selected);
		}

		$month_options = array(
			null => 'Month',
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December',
		);

		$day_options   = array(null => 'Day');
		$year_options  = array(null => 'Year');

		// Generates the list of days
		for ($i = 1; $i <= 31; ++$i)
		{
			$day_options[str_pad($i, 2, '0', STR_PAD_LEFT)] = $i;
		}

		// Generates the list of years
		$current_year = date('Y');
		$start_year   = $start_year == null ? $current_year - 10 : $start_year;
		$end_year     = $end_year   == null ? $current_year + 10 : $end_year;

		for ($i = $start_year; $i >= $end_year; --$i)
		{
			$year_options[$i] = $i;
		}

		// Loops through and generates the selects
		foreach (array('month', 'day', 'year') as $part)
		{
			$options  = $part . '_options';
			$selected = 'selected_' . $part;
			$html   .= ' ' . $this->select($name . '[' . $part . ']', $$options, $$selected, $classes, $additional);
		}

		return $html;
	}

	// }}}
	// {{{ Date of Birth Select

	/**
	 * Date of Birth Select
	 *
	 * Generates 3 select boxes (month, day, year)
	 *
	 * @param  string $name optional name (and ID) for the select element
	 * @param  string $selected optional selected option
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the select boxes
	 */
	public function dobSelect($name = 'dob', $selected = null, $classes = null, $additional = null)
	{
		// Note: Start year based on oldest living person: http://en.wikipedia.org/wiki/Oldest_people as of November 2010
		// Note: Start and end year may seem backwards, but we want them in descending order when rendered
		return $this->dateSelect($name, $selected, $classes, $additional, date('Y'), 1896);
	}

	// }}}
	// {{{ Polar Select

	/**
	 * Polar Select
	 *
	 * Generates a polar (yes / no) select box.
	 *
	 * @param  string $name optional name (and ID) for the select element
	 * @param  string $selected optional selected option
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 */
	public function polarSelect($name = 'decision', $selected = 0, $classes = null, $additional = null)
	{
		$options = array(1 => 'Yes', 0 => 'No');

		return $this->select($name, $options, $selected, $classes, $additional);
	}

	// }}}
	// {{{ Phone Input

	/**
	 * Phone Input
	 *
	 * Generates 3 inputs for a phone number from the passed values.
	 *
	 * @param  string $name optional name (and ID) for the input elements
	 * @param  string $value optional existing value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 */
	public function phoneInput($name = 'phone', $value = null, $classes = null, $additional = null)
	{
		if ($value == null)
		{
			$value = array(
				'area_code'   => '',
				'prefix'      => '',
				'line_number' => ''
			);
		}
		else
		{
			$value = array(
				'area_code'   => substr($value, 0, 3),
				'prefix'      => substr($value, 3, 3),
				'line_number' => substr($value, 6)
			);
		}

		$parts = array(
			'area_code'   => 3,
			'prefix'      => 3,
			'line_number' => 4
		);

		if ($additional)
		{
			$additional = ' ' . $additional;
		}

		$additional .= ' class="digits';

		if ($classes)
		{
			$additional .= ' ' . $classes;
		}

		$additional .= '"';

		$html = '';
		foreach ($parts as $part => $size)
		{
			$html .= ($html != '' ? ' ' : '');
			$html .= '<input type="input" name="' . $name . '[' . $part . ']" id="' . $name . '[' . $part . ']" value="' . $value[$part] . '" minlength="' . $size . '" maxlength="' . $size . '"' . $additional . ' />';
		}

		return $html;
	}

	// }}}
}

?>
