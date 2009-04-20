<?php

class store_admin_affiliates_pay_save extends store_admin {

	protected $display = DISPLAY_JSON;

	public function __default() {

		// Checks that the amount is not greater than the unpaid balance
		if ($_REQUEST['amount'] > $this->db->getField('SELECT unpaid_balance FROM affiliates WHERE id = "' . $_REQUEST['id'] . '";')) {
			$this->error->addError('The amount of the check is greater than the unpaid balance');
		}
		// Checks for zero balance and negative checks
		else if ($_REQUEST['amount'] <= 0) {
			$this->error->addError('The amount of the check is not valid');
		}
		// Checks that the check number is an integer
		else if (!is_numeric($_REQUEST['number'])) {
			$this->error->addError('The number of the check should be an integer (example: 1234)');
		}
		// Checks that the date is valid
		else if (!is_numeric($_REQUEST['date']['mm']) || !is_numeric($_REQUEST['date']['dd']) || !is_numeric($_REQUEST['date']['ccyy']))  {
			$this->error->addError('The date does not appear to be valid');
		}
		// Adds the check to the database and updates the unpaid amount
		else {
			$check = array(
				'affiliate_id' => $_REQUEST['id'],
				'amount'       => $_REQUEST['amount'],
				'number'       => $_REQUEST['number'],
				'date'         => $_REQUEST['date']['ccyy'] . '-' . $_REQUEST['date']['mm'] . '-' . $_REQUEST['date']['dd'],
				'notes'        => $_REQUEST['notes']
			);

			$this->db->insert('checks', $check);

			$this->db->execute('
				UPDATE affiliates
				SET unpaid_balance = unpaid_balance - ' . $_REQUEST['amount'] . '
				WHERE id = "' . $_REQUEST['id'] . '";
			');
		}

		if ($this->error->getErrors()) {
			$this->setPublic('status',  'Error');
			$this->setPublic('message', 'There was an error storing the check information: ' . implode('. ', $this->error->getErrors()) . '.');
			return false;
		}
		else {
			$this->setPublic('status',  'Success');
			$this->setPublic('message', 'The check information was stored successfully.');
			$this->setPublic('amount',  $_REQUEST['amount']);
		}
	}
}

?>
