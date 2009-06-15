<?php

class store_admin_orders extends store_admin {

	public function __default() {
			
		$where = null;

		if (isset($_REQUEST['filter'])) {
			// Validates the filter
			$status = $this->db->getRow('SELECT id, name FROM order_statuses WHERE LOWER(name) = LOWER("' . str_replace('-', ' ', $_REQUEST['filter']) . '")');

			if ($status != null) {
				$where = 'WHERE osu.status_id = "' . $status['id'] . '"';

				$this->setPublic('filter', $status['name']);
			}
		}

		$sql = '
			SELECT
				o.id AS order_id,
				c.id AS customer_id,
				CONCAT(a.last_name, ", ", a.first_name) AS customer_name,
				DATE_FORMAT(o.time_placed, "%m/%d/%Y") AS order_time,
				o.total_amount,
				os.name AS status,
				osu.update_time AS last_update,
				o.transaction_id,
				s.name AS shipping_method,
				o.weight,
				"This would be the shipping notes" AS memo,
				os.name AS status

			FROM orders AS o

			LEFT JOIN customers AS c
				ON o.xref_type = "CUSTOMER" AND o.xref_id = c.id

			INNER JOIN emails AS e
				ON o.xref_type = "EMAIL"    AND e.id = o.xref_id
				OR o.xref_type = "CUSTOMER"	AND e.id = c.email_id

			INNER JOIN addresses AS a
				ON a.id = o.shipping_address_id

			LEFT JOIN shipping AS s
				ON s.id = o.shipping_id

			LEFT JOIN order_status_updates AS osu
				ON osu.order_id = o.id
				AND osu.id = (SELECT MAX(id) FROM order_status_updates WHERE order_id = o.id)

			LEFT JOIN order_statuses AS os
				ON os.id = osu.status_id

			' . $where . '

			ORDER BY o.id DESC;
		';

		$this->setPublic('orders', $this->db->getArray($sql));
	}
}

?>
