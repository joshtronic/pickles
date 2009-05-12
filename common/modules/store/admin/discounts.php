<?php

class store_admin_discounts extends store_admin {

	public function __default() {
		$sql = '
			SELECT *
			FROM discounts
			ORDER BY valid_through;
		';

		$this->setPublic('discounts', $this->db->getArray($sql));
	}
}

?>
