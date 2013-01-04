<?php

class callback_github_hook extends Module
{
	public $method = 'POST';

	public function __default()
	{
		if (isset($_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR'], $_POST['payload'])
			&& $_SERVER['HTTP_USER_AGENT'] == 'GitHub Services Web Hook'
			&& in_array($_SERVER['REMOTE_ADDR'], array('207.97.227.253', '50.57.128.197', '108.171.174.178')))
		{
			`git pull origin master`;
			`git submodule init`;
			`git submodule update`;
		}

		Browser::redirect('/');
	}
}

?>
