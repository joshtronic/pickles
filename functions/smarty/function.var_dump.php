<?php

function smarty_function_var_dump($params, &$smarty) {
	//$hidden = array('SCRIPT_NAME', 'navigation', 'section', 'model', 'action', 'event', 'admin', 'session', 'template');
	$hidden = array();
	$all_variables = $smarty->_tpl_vars;
	$variables = array();

	if (is_array($all_variables)) {
		foreach ($all_variables as $name => $value) {
			if (!in_array($name, $hidden)) {
				$variables['$' . $name] = $value;
			}
		}
	}

	// @todo
	require_once '/home/josh/3rd-party-stuff/dBug-1.2/dBug.php';

	echo "
		<style>
			table, tr, td {
				margin: 2px;
				padding: 2px;
				border: 1px solid black;
			}
		</style>
	";

	new dBug($variables);
}

?>
