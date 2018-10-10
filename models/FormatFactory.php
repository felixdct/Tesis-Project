<?php
require_once("UserJSONFormat.php");

class FormatFactory {
	public static function getEncoder($format, $objName) {
		$class = $objName.$format.'Format';

		if (class_exists($class)) {
			return new $class();
		}

		throw new Exception('Unsupported format');
	}
}
?>
