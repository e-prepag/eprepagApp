<?php
if (!function_exists("pdv_iso_to_utf8")) {
	function pdv_iso_to_utf8($value) {
		if ($value === null) {
			return "";
		}
		if (!is_string($value) || $value === "") {
			return $value;
		}
		$converted = function_exists("iconv") ? @iconv("ISO-8859-1", "UTF-8//IGNORE", $value) : false;
		return ($converted !== false) ? $converted : $value;
	}
}

if (!function_exists("pdv_utf8_to_iso")) {
	function pdv_utf8_to_iso($value) {
		if ($value === null) {
			return "";
		}
		if (!is_string($value) || $value === "") {
			return $value;
		}
		$converted = function_exists("iconv") ? @iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value) : false;
		return ($converted !== false) ? $converted : $value;
	}
}
