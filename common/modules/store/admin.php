<?php

class store_admin extends Module {

	protected $authentication = true;

	public function __construct(Config $config, DB $db, Mailer $mailer, Error $error) {
		parent::__construct($config, $db, $mailer, $error);

		$options = array(
			'home',
			'orders',
			'customers',
			'products', 
			'categories',
			'discounts',
			'affiliates',
			// 'vendors', 
			'gift certificates',
			'reports',
			'settings'
		);
		$this->setPublic('options', $options);
		
		$this->template = 'store/admin';
	}

	public function __default() {
		// Forces store/admin/home as the first page you get when only /store is called
		$object = new store_admin_home($this->config, $this->db, $this->mailer, $this->error);
		$object->__default();

		$this->public = $object->public;
		$this->name   = 'store/admin/home';
	}
}

?>
