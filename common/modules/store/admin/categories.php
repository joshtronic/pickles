<?php

class store_admin_categories extends store_admin {

	public function __default() {
		// Pulls all the categories and product counts in hierarchial and alphabetical order
		$all_categories = $this->db->getArray('
			SELECT c.*, COUNT(xref.category_id) AS product_count
			FROM categories AS c
			LEFT JOIN category_xref AS xref
			ON xref.category_id = c.id
			GROUP BY c.id
			ORDER BY c.parent_id, c.name;
		');

		// Loops through the categories and builds an array of parents and children
		$categories = array();
		if (is_array($all_categories)) {
			foreach ($all_categories as $category) {
				if ($category['parent_id'] == null) {
					$categories[$category['id']] = $category;
				}
				else {
					$categories[$category['parent_id']]['children'][] = $category;
				}
			}
		}
		
		// Passes the categories to the template
		$this->setPublic('categories', $categories);
	}
}

?>
