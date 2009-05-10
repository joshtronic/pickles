<?php

function smarty_function_age($params, &$smarty) {
	
	if (empty($params['dob'])) {
		$smarty->trigger_error('assign: missing \'dob\' parameter');
	}
	else {

		// Breaks the date apart
		list($dob_year, $dob_month, $dob_day) = split('-', $params['dob'], 3);
		
		// Determines the age regardless of the day
		$age = date('Y') - $dob_year;

		// If today's month day is less than the dob decrement
		if (date('md') < $dob_month . $dob_day) {
			$age--;
		}

		return $age;
	}
}

?>
