<?php

class store_admin_home extends store_admin {

	public function __default() {

		$sql = '
			SELECT
				(SELECT COUNT(id) FROM orders WHERE DATE_FORMAT(time_placed, "%Y%m%d") = DATE_FORMAT(CURDATE(), "%Y%m%d")) AS orders_today,
				(SELECT COUNT(id) FROM orders WHERE DATE_FORMAT(time_placed, "%Y%m") = DATE_FORMAT(CURDATE(), "%Y%m")) AS orders_mtd,
				(SELECT COUNT(id) FROM orders WHERE DATE_FORMAT(time_placed, "%Y") = DATE_FORMAT(CURDATE(), "%Y")) AS orders_ytd,

				(SELECT SUM(total_amount) - SUM(shipping_amount) FROM orders WHERE DATE_FORMAT(time_placed, "%Y%m%d") = DATE_FORMAT(CURDATE(), "%Y%m%d")) AS sales_today,
				(SELECT SUM(total_amount) - SUM(shipping_amount) FROM orders WHERE DATE_FORMAT(time_placed, "%Y%m") = DATE_FORMAT(CURDATE(), "%Y%m")) AS sales_mtd,
				(SELECT SUM(total_amount) - SUM(shipping_amount) FROM orders WHERE DATE_FORMAT(time_placed, "%Y") = DATE_FORMAT(CURDATE(), "%Y")) AS sales_ytd,
				(SELECT COUNT(id) FROM customers) AS total_customers;
		';

		$this->setPublic('statistics', $this->db->getRow($sql));
	}
}

?>
