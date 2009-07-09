<?php

class store_admin_products_edit extends store_admin {

	protected $display = array(DISPLAY_SMARTY, DISPLAY_JSON);

	public function __default() {

		if (isset($_REQUEST['id'])) {
			$sql = '
				SELECT 
					customers.*,

					emails.email,

					billing.company     AS billing_company,
					billing.first_name  AS billing_first_name,
					billing.last_name   AS billing_last_name,
					billing.address1    AS billing_address1,
					billing.address2    AS billing_address2,
					billing.city        AS billing_city,
					billing.state       AS billing_state,
					billing.zip_code    AS billing_zip_code,
					billing.country     AS billing_country,
					billing.phone       AS billing_phone,
					billing.fax         AS billing_fax,

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
					shipping.fax        AS shipping_fax

				FROM customers

				INNER JOIN emails
				ON emails.id = customers.email_id

				INNER JOIN addresses AS billing
				ON billing.id = customers.billing_address_id

				INNER JOIN addresses AS shipping
				ON shipping.id = customers.shipping_address_id

				WHERE customers.id = "' . $_REQUEST['id'] . '";
			';

			$this->setPublic('customer', $this->db->getRow($sql));
		}
	}
}

?>
