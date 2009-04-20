<?php

class store_admin_affiliates_delete extends store_admin {

	public function __default() {
		$this->db->execute('DELETE FROM affiliates WHERE id = "' . $_REQUEST['id'] . '";');

		header('Location: /store/admin/affiliates');
	}
}

?>
