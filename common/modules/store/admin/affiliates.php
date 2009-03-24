<?php

class store_admin_affiliates extends store_admin {

	public function __default() {
		$sql = '
			SELECT
				a.id,
				a.commission_rate,
				a.order_count,
				a.unpaid_balance,

				e.email,

				ca.first_name,
				ca.last_name,
				ca.phone
			
			FROM affiliates      AS a
			INNER JOIN emails    AS e  ON e.id  = a.email_id
			INNER JOIN addresses AS ca ON ca.id = a.contact_address_id
			
			ORDER BY
				unpaid_balance DESC,
				order_count DESC
			;
		';

		$this->setPublic('affiliates', $this->db->getArray($sql));
	}
}

?>
