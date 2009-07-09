<?php

class store_admin_discounts extends store_admin {

	public function __default() {
		$sql = '
			SELECT d1.*
			FROM discounts AS d1
			WHERE d1.sequence = (SELECT MAX(sequence) FROM discounts WHERE id = d1.id)
			ORDER BY valid_through;
		';

		$this->setPublic('discounts', $this->db->getArray($sql));
	}
}

?>
