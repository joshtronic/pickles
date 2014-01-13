<?php

class ValidateTest extends PHPUnit_Framework_TestCase
{
	public function testIsValidTooLong()
	{
		$variable = 'really long string';
		$rules    = 'length:16';

		Validate::isValid($variable, $rules);
	}
}

?>
