<?php

class store extends Model {

	public function __construct() {
		parent::__construct();

		// Loads the navigation
		$config = Config::getInstance();
		$this->data['subnav'] = $config->get('store', 'sections');

		// Loads the categories
		$categories = $this->db->getArray('SELECT id, name, permalink FROM categories WHERE parent_id IS NULL AND visible = "Y" ORDER BY weight;');
		if (is_array($categories)) {
			foreach ($categories as $key => $category) {
				$categories[$key]['subcategories'] = $this->db->getArray('
					SELECT id, name, permalink
					FROM categories
					WHERE parent_id = "' . $category['id'] . '"
					AND visible = "Y"
					ORDER BY weight;
				');
			}
		}

		$this->data['categories'] = $categories;
	}

	public function __default() {
		// Forces store/home as the first page you get when only /store is called
		$object = new store_home();
		$object->__default();

		$this->data = $object->data;
		$this->set('name', 'store/home');
	}

}

?>
