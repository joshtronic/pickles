<?php

class store_admin_affiliates_save extends store_admin {

	protected $display = DISPLAY_JSON;

	public function __default() {

		// Adds the contact information into the database
		if (isset($_REQUEST['contact_address1']) && trim($_REQUEST['contact_address1']) != '') {
			$contact_address = array(
				'company'    => $_REQUEST['contact_company'],
				'first_name' => $_REQUEST['contact_first_name'],
				'last_name'  => $_REQUEST['contact_last_name'],
				'address1'   => $_REQUEST['contact_address1'],
				'address2'   => $_REQUEST['contact_address2'],
				'city'       => $_REQUEST['contact_city'],
				'state'      => $_REQUEST['contact_state'],
				'zip_code'   => $_REQUEST['contact_zip_code'],
				'country'    => 'US',
				'phone'      => $_REQUEST['contact_phone'],
				'fax'        => $_REQUEST['contact_fax']
			);

			$contact_address['hash'] = md5(implode('', $contact_address));

			if ($this->db->getField("SELECT COUNT(*) FROM addresses WHERE hash = '{$contact_address['hash']}';") == 0) {
				$contact_address_id = $this->db->insert('addresses', $contact_address);
			}
			else {
				$contact_address    = $this->db->getRow("SELECT * FROM addresses WHERE hash = '{$contact_address['hash']}';");
				$contact_address_id = $contact_address['id'];
			}
		}

		// Adds the payee information into the database
		$payee_address_id = null;

		if (isset($_REQUEST['payee_same_as_contact']) && $_REQUEST['payee_same_as_contact'] == 'on') {
			$payee_address_id = $contact_address_id;
			$payee_address    = $contact_address;
		}
		else if (isset($_REQUEST['payee_address1']) && trim($_REQUEST['payee_address1']) != '') {
			$payee_address = array(
				'company'    => $_REQUEST['payee_company'],
				'first_name' => $_REQUEST['payee_first_name'],
				'last_name'  => $_REQUEST['payee_last_name'],
				'address1'   => $_REQUEST['payee_address1'],
				'address2'   => $_REQUEST['payee_address2'],
				'city'       => $_REQUEST['payee_city'],
				'state'      => $_REQUEST['payee_state'],
				'zip_code'   => $_REQUEST['payee_zip_code'],
				'country'    => 'US',
				'phone'      => $_REQUEST['payee_phone'],
				'fax'        => $_REQUEST['payee_fax']
			);

			$payee_address['hash'] = md5(implode('', $payee_address));

			if ($this->db->getField("SELECT COUNT(*) FROM addresses WHERE hash = '{$payee_address['hash']}';") == 0) {
				$payee_address_id = $this->db->insert('addresses', $payee_address);
			}
			else {
				$payee_address    = $this->db->getRow("SELECT * FROM addresses WHERE hash = '{$payee_address['hash']}';");
				$payee_address_id = $payee_address['id'];
			}
		}

		// Adds the affiliate's email into the email database
		if (isset($_REQUEST['email']) && trim($_REQUEST['email']) != '') {
			$email = $_REQUEST['email'];

			if ($this->db->getField("SELECT COUNT(*) FROM emails WHERE email = '{$email}';") == 0) {
				$email_id = $this->db->insert('emails', array('email' => $email));
			}
			else {
				$email_id = $this->db->getField("SELECT id FROM emails WHERE email = '{$email}';");
			}
		}

		// Updates the existing affiliate
		if (isset($_REQUEST['id'])) {

			$affiliate = array(
				'email_id'           => $email_id,
				'contact_address_id' => $contact_address_id,
				'payee_address_id'   => $payee_address_id,
				'tax_id'             => $_REQUEST['tax_id'],
				'tax_class'          => $_REQUEST['tax_class'],
				'commission_rate'    => $_REQUEST['commission_rate']
			);

			$this->db->update('affiliates', $affiliate, array('id' => $_REQUEST['id']));
		
			if ($this->error->getErrors()) {
				$this->setPublic('status',  'Error');
				$this->setPublic('message', 'There was an error updating the affiliate account (' . implode('. ', $this->error->getErrors()) . '.)');
				return false;
			}
			else {
				$this->setPublic('status',  'Success');
				$this->setPublic('message', 'The affiliate information has been updated successfully.');
			}
		}
		// Adds a brand new affiliate
		else {
			$affiliate = array(
				'email_id'           => $email_id,
				'contact_address_id' => $contact_address_id,
				'payee_address_id'   => $payee_address_id,
				'tax_id'             => $_REQUEST['tax_id'],
				'tax_class'          => $_REQUEST['tax_class'],
				'date_started'       => date('Y-m-d'),
				'commission_rate'    => $_REQUEST['commission_rate']
			);

			$affiliate_id = $this->db->insert('affiliates', $affiliate);
		
			if ($this->error->getErrors()) {
				$this->setPublic('status',  'Error');
				$this->setPublic('message', 'There was an error adding the affiliate account (' . implode('. ', $this->error->getErrors()) . '.)');
				return false;
			}
			else {
				$affiliate_message = "
{$this->config->store->title} Affiliate Program
-------------------------------------------------------------------

Dear {$contact_address['first_name']} {$contact_address['last_name']},

You have been registered

Your custom URL:
---------------------
{$this->config->store->url}/referral/" . md5($affiliate_id) . "

Your commission rate:
---------------------
{$_REQUEST['commission_rate']}%

------------------

Thank you for your interest in the {$this->config->store->title} Affiliate Program.

{$this->config->store->title}
Phone: {$this->config->store->phone}
Fax:   {$this->config->store->fax}
URL:   {$this->config->store->url}
";

				mail($_REQUEST['email'], 'Welcome to the ' . $this->config->store->title . ' Affiliate Program', $affiliate_message, 'From: ' . $this->config->store->return_email);

				$this->setPublic('status',  'Success');
				$this->setPublic('message', 'The new affiliate has been added successfully.');
			}
		}
	}
}

?>
