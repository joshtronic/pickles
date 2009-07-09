<?php

class store_admin_customers extends store_admin {

	public function __default() {
		$sql = '
			SELECT 
				customers.*,

				emails.email,

				shipping.company    AS shipping_company,
				shipping.first_name AS shipping_first_name,
				shipping.last_name  AS shipping_last_name,
				shipping.address1   AS shipping_address1,
				shipping.address2   AS shipping_address2,
				shipping.city       AS shipping_city,
				shipping.state      AS shipping_state,
				shipping.zip_code   AS shipping_zip_code,
				shipping.country    AS shipping_country,
				shipping.phone      AS shipping_phone,
				shipping.fax        AS shipping_fax,

				COUNT(orders.id)    AS order_count

			FROM customers

			INNER JOIN emails
			ON emails.id = customers.email_id

			LEFT JOIN addresses AS shipping
			ON shipping.id = customers.shipping_address_id

			LEFT JOIN orders
			ON orders.xref_id = customers.id
			AND xref_type = "CUSTOMER"

			GROUP BY customers.id

			ORDER BY shipping.last_name, shipping.first_name

			;
		';

		$this->setPublic('customers', $this->db->getArray($sql));
	}
}

?>
