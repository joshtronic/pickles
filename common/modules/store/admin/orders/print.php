<?php

class store_admin_orders_print extends store_admin {

	protected $display = DISPLAY_JSON;

	public function __default() {
		$saver = new store_admin_orders_save($this->config, $this->db, $this->mailer, $this->error);
		$saver->__default();

		$this->setPublic('packing_slip', nl2br($saver->packing_slip));

		$this->setPublic('status',  'Success');
		$this->setPublic('message', 'The order has been updated successfully.');
	}
}

?>
