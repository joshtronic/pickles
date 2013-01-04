<?php

class callback_github extends Module
{
	public $method = 'POST';

	public function __default()
	{
		if (isset($_SERVER['REMOTE_ADDR'], $_POST['payload'])
			&& in_array($_SERVER['REMOTE_ADDR'], array('207.97.227.253', '50.57.128.197', '108.171.174.178')))
		{
			`git pull origin master`;
		}

		Browser::redirect('/');
	}
}

?>
