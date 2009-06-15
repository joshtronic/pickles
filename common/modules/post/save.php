<?php

class post_save extends post_edit {

	public function __default() {
		parent::__default();

		$existing = $this->public['post'];
		
		if ($_REQUEST['id'] != '' && $_REQUEST['id'] != $existing['post_id']) {
			// @todo Throw error
		}
		else {
			// Prepends days 1-9 with a 0 since html_select_date doesn't do so
			if ($_REQUEST['Date_Day'] < 10) {
				$_REQUEST['Date_Day'] = '0' . $_REQUEST['Date_Day'];
			}

			// Converts the hour to military time
			if ($_REQUEST['Time_Meridian'] == 'pm' && $_REQUEST['Time_Hour'] < 12) {
				$_REQUEST['Time_Hour'] += 12;
			}
			else if ($_REQUEST['Time_Meridian'] == 'am' && $_REQUEST['Time_Hour'] == 12) {
				$_REQUEST['Time_Hour'] = '00';
			}

			// Contructs the posted at timestamp
			$_REQUEST['posted_at'] = $_REQUEST['Date_Year'] . '-' . $_REQUEST['Date_Month'] . '-' . $_REQUEST['Date_Day'] . ' ' .  $_REQUEST['Time_Hour'] . ':' . $_REQUEST['Time_Minute'] . ':' . $_REQUEST['Time_Second'];

			// Assembles the data array
			$data = array(
				'title'     => $_REQUEST['title'],
				'body'      => $_REQUEST['body'],
				'posted_at' => $_REQUEST['posted_at'],
				'hidden'    => $_REQUEST['hidden']
			);

			if ($_REQUEST['id'] != '') {
				if ($_REQUEST['title'] != $existing['title'] || $_REQUEST['body'] != $existing['body'] || $_REQUEST['posted_at'] != $posted_at) {
					$this->db->update('posts', $data, array('post_id' => $_REQUEST['id']));
				}
			}
			else {
				$this->db->insert('posts', $data);
			}
		}

		header('Location: /weblog');
	}
}

?>
