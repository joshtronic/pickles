<?php

class store_home extends store {

	public function __construct() {
		parent::__construct();
	}

	public function __default() {
		$this->data['featured'] = $this->db->getRow('SELECT id, name, teaser FROM products WHERE featured = "Y" AND id = 30 ORDER BY RAND() LIMIT 1;');
	
		foreach (array('gif', 'jpg', 'png') as $extension) {
			if (file_exists(getcwd() . '/images/products/' . $this->data['featured']['id'] . '/medium.' . $extension)) {
				$this->data['featured']['image'] = $extension;
			}
		}

		$this->data['top_sellers'] = $this->db->getArray('SELECT id, name FROM products ORDER BY RAND() LIMIT 10;');
	}
}

?>
