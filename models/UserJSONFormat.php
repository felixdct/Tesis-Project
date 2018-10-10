<?php
require_once("Format.php");
class UserJSONFormat implements Format {
	public function encode($obj) {
		if (is_array($obj)) {
			return json_encode($obj);
		} else {
			throw new Exception('obj is not an array');
		}
	}

	public function decode($obj) {
	}
}
?>
