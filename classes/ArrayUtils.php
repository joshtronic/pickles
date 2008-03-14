<?php

class ArrayUtils {

	public static function object2array($object) {
		if (is_object($object)) {
			$object = (array)$object;
		}

		foreach ($object as $key => $value) {
			if (is_object($value)) {
				$object[$key] = self::object2array($value);
			}
		}

		return $object;
	}

}

?>
