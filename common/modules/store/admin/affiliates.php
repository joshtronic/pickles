<?php

class store_admin_affiliates extends store_admin {

	public function __default() {
		$sql = '
			SELECT *
			FROM affiliates
			ORDER BY
				unpaid_balance DESC,
				order_count DESC
			;
		';
		$this->setPublic('affiliates', $this->db->getArray($sql));
	}
}

?>
