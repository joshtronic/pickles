<?php

class store extends Model {

	protected $session = true;

	public function __construct() {
		parent::__construct();

		// Loads up the cart in case we need it
		if (!isset($_SESSION['cart'])) {
			$_SESSION['cart'] = array();
			$_SESSION['cart'] = array('count' => 0, 'products' => null);
		}
		else {
			$count = 0;
		
			if (is_array($_SESSION['cart']['products'])) {
				foreach ($_SESSION['cart']['products'] as $product_id => $product_info) {
					$count += $product_info['quantity'];
				}
			}

			$_SESSION['cart']['count'] = $count;
		}

		$this->data['cart'] = $_SESSION['cart'];

		// Loads the navigation
		$config = Config::getInstance();
		$this->data['subnav'] = $config->store->sections;

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
