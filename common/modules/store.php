<?php

/**
 * Store
 *
 * @package    PICKLES
 * @subpackage store
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2007-2009 Joshua Sherman
 */

class store extends Module {

	protected $display = DISPLAY_SMARTY;
	protected $session = true;

	public function __construct(Config $config, DB $db, Mailer $mailer, Error $error) {
		parent::__construct($config, $db, $mailer, $error);

		// Loads up the cart in case we need it
		if (!isset($_SESSION['cart'], $_SESSION['cart']['count'], $_SESSION['cart']['products'])) {
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

		$this->setPublic('cart', $_SESSION['cart']);

		// Loads the navigation
		if (isset($config->store->sections)) {
			$this->setPublic('subnav', $config->store->sections);
		}

		// Loads the categories
		/*
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

		$this->setPublic('categories', $categories);
		*/
	}

	public function __default() {
		// Forces store/home as the first page you get when only /store is called
		$object = new store_home($this->config, $this->db, $this->mailer, $this->error);
		$object->__default();

		$this->public = $object->public;
		$this->name   = 'store/home';
	}
}

?>
