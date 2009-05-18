<?php

/**
 * @todo Add discounts to the order view
 * @todo Collapse the updates to the latest 3-5 and then have a "more" option
 */
class store_admin_orders_edit extends store_admin {

	protected $display = array(DISPLAY_SMARTY, DISPLAY_JSON);

	public function __default() {

		if (isset($_REQUEST['id'])) {
			$sql = '
				SELECT
					o.id AS order_id,
					c.id AS customer_id,
					DATE_FORMAT(o.time_placed, "%m/%d/%Y") AS order_time,
					o.total_amount,
					CONCAT(os.id, "|", os.name) AS status_id,
					osu.note AS shipping_note, 
					osu.update_time AS last_update,
					o.transaction_id,
					CONCAT(s.id, "|", s.name) AS shipping_method,
					o.weight,
					"This would be the shipping notes" AS memo,
					os.name AS status,

					o.cc_type,
					o.cc_last4,
					o.cc_expiration,
					o.shipping_amount,
					o.tracking_number,

					e.email,

					ba.company    AS billing_company,
					ba.first_name AS billing_first_name,
					ba.last_name  AS billing_last_name,
					ba.address1   AS billing_address1,
					ba.address2   AS billing_address2,
					ba.city       AS billing_city,
					ba.state      AS billing_state,
					ba.zip_code   AS billing_zip_code,
					ba.phone      AS billing_phone,
					ba.fax        AS billing_fax,

					sa.company    AS shipping_company,
					sa.first_name AS shipping_first_name,
					sa.last_name  AS shipping_last_name,
					sa.address1   AS shipping_address1,
					sa.address2   AS shipping_address2,
					sa.city       AS shipping_city,
					sa.state      AS shipping_state,
					sa.zip_code   AS shipping_zip_code,
					sa.phone      AS shipping_phone,
					sa.fax        AS shipping_fax

				FROM orders AS o

				LEFT JOIN customers AS c
					ON o.xref_type = "CUSTOMER" AND o.xref_id = c.id

				LEFT JOIN emails AS e
					ON o.xref_type = "EMAIL"    AND e.id = o.xref_id
					OR o.xref_type = "CUSTOMER"	AND e.id = c.email_id

				LEFT JOIN addresses AS ba
					ON ba.id = o.billing_address_id

				LEFT JOIN addresses AS sa
					ON sa.id = o.shipping_address_id

				LEFT JOIN shipping AS s
					ON s.id = o.shipping_id

				LEFT JOIN order_status_updates AS osu
					ON osu.order_id = o.id
					AND osu.id = (SELECT MAX(id) FROM order_status_updates WHERE order_id = o.id)

				LEFT JOIN order_statuses AS os
					ON os.id = osu.status_id

				WHERE o.id = "' . $_REQUEST['id'] . '"

				ORDER BY o.id DESC
				
				LIMIT 1;
			';

			$order = $this->db->getRow($sql);

			$sql = '
				SELECT op.quantity, p.*
				FROM order_products AS op
				INNER JOIN products AS p ON p.id = op.product_id
				WHERE op.order_id = "' . $_REQUEST['id'] . '"
			';

			$order['products'] = $this->db->getArray($sql);

			$sql = 'SELECT * FROM order_status_updates WHERE order_id = "' . $_REQUEST['id'] . '" ORDER BY update_time DESC;';

			$order['updates'] = $this->db->getArray($sql);

			$this->setPublic('order',            $order);
			$this->setPublic('serialized_order', serialize($order));

			foreach ($this->db->getArray('SELECT * FROM order_statuses;') as $status) {
				$statuses[$status['id']]                               = $status['name'];
				$status_options[$status['id'] . '|' . $status['name']] = $status['name'];
			}

			$this->setPublic('statuses',       $statuses);
			$this->setPublic('status_options', $status_options);

			foreach ($this->db->getArray('SELECT * FROM shipping;') as $shipping_method) {
				$shipping_methods[$status['id']]                                                  = $status['name'];
				$shipping_method_options[$shipping_method['id'] . '|' . $shipping_method['name']] = $shipping_method['name'];
			}

			$this->setPublic('shipping_methods',        $shipping_methods);
			$this->setPublic('shipping_method_options', $shipping_method_options);
		}
	}
}

?>
