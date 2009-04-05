<?php

class Form {

	public function fromTable($table) {

		$this->getArray('DESCRIBE menopausesolutions.affiliates');

		$form = "<form>\n\t<dl>\n";

		while ($row = mysql_fetch_assoc($results)) {

			$label      = ucwords(strtr($row['Field'], '_', ' '));
			$attributes = "name='{$row['Field']}' id='{$row['Field']}'";

			$form .= "\t\t<dt>{$label}:</dt>\n\t\t<dd>\n\t\t\t";

			// ENUM()
			if (preg_match('/^enum/', $row['Type'])) {
				$options = str_replace(array("enum('", "')"), '', $row['Type']);
				$options = explode("','", $options);

				if (is_array($options)) {
					if (count($options) <= 2) {
						foreach ($options as $option) {
							$form .= "<input type='radio' {$attributes} value='{$option}' /> {$option} ";
						}
					}
					else {
						$form .= "<select {$attributes}>";

						if ($row['Null'] == 'YES' && $options[0] != '') {
							array_unshift($options, '');
						}
			
						foreach ($options as $option) {
							$form .= "<option value='{$option}'>{$option}</option>";
						}
					}
				}

				$form .= '</select>';
			}
			// TEXT and BLOG (all sizes)
			else if (preg_match('/(tiny|medium|long)?(text|blob)$/', $row['Type'])) {
				$form .= "<textarea {$attributes}></textarea>";
			}
			// DATE (not DATETIME)
			else if (preg_match('/^date$/', $row['Type'])) {

			}
			/*
			else if (preg_match('/^datetime$/', $row['Type'])) {

			}
			*/
			else if (preg_match('/(tiny|medium|long)?int([0-9]+)$/', $row['Type'])) {
				var_dump(split('int', $row['Type']));
			}
			// Generic default (input box)
			else {
				var_dump($row);
				if (preg_match('/^(var)?char\([0-9]+\)$/', $row['Type'])) {
					$type_array = split('(\(|\))', $row['Type']);
					$attributes .= " maxlength='{$type_array[1]}' size='{$type_array[1]}'";
				}

				$form .= "<input type='text' {$attributes} />";
			}

			$form .= "\n\t\t</dd>\n";
		}

		$form .= "\t</dl>\n</form>\n";

		exit($form);
	}
}

?>
