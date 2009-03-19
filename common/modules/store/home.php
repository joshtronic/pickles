<?php

class store_home extends store {
	
	protected $display = DISPLAY_SMARTY;

	public function __construct(Config $config, DB $db, Mailer $mailer, Error $error) {
		parent::__construct($config, $db, $mailer, $error);
	}

	public function __default() {
		$featured = $this->db->getRow('SELECT id, name, teaser FROM products WHERE featured = "Y" AND id = 30 ORDER BY RAND() LIMIT 1;');
	
		foreach (array('gif', 'jpg', 'png') as $extension) {
			if (file_exists(getcwd() . '/images/products/' . $featured['id'] . '/medium.' . $extension)) {
				$featured['image'] = $extension;
			}
		}

		$this->setPublic('featured',    $featured);
		$this->setPublic('top_sellers', $this->db->getArray('SELECT id, name FROM products ORDER BY RAND() LIMIT 10;'));
	}
}

?>
