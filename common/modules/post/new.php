<?php

class post_new extends Module {

	public function __construct(Config $config, DB $db, Mailer $mailer, Error $error) {
		parent::__construct($config, $db, $mailer, $error);
		$this->template = 'post/edit';
	}
}

?>
