<?php

class store_admin_affiliates_edit extends store_admin {

	protected $display = array(DISPLAY_SMARTY, DISPLAY_JSON);

	public function __default() {

		if (isset($_REQUEST['id'])) {
			$sql = "
				SELECT
					a.id,
					a.tax_id,
					a.tax_class,
					a.commission_rate,

					e.email,

					ca.company    AS contact_company,
					ca.first_name AS contact_first_name,
					ca.last_name  AS contact_last_name,
					ca.address1   AS contact_address1,
					ca.address2   AS contact_address2,
					ca.city       AS contact_city,
					ca.state      AS contact_state,
					ca.zip_code   AS contact_zip_code,
					ca.phone      AS contact_phone,
					ca.fax        AS contact_fax,

					pa.company    AS payee_company,
					pa.first_name AS payee_first_name,
					pa.last_name  AS payee_last_name,
					pa.address1   AS payee_address1,
					pa.address2   AS payee_address2,
					pa.city       AS payee_city,
					pa.state      AS payee_state,
					pa.zip_code   AS payee_zip_code,
					pa.phone      AS payee_phone,
					pa.fax        AS payee_fax
				
				FROM affiliates      AS a
				INNER JOIN emails    AS e  ON e.id  = a.email_id
				INNER JOIN addresses AS ca ON ca.id = a.contact_address_id
				INNER JOIN addresses AS pa ON pa.id = a.payee_address_id
				
				WHERE a.id = '{$_REQUEST['id']}';
			";
			
			$this->setPublic('affiliate', $this->db->getRow($sql));
		}
	}
}

?>
