<?php



// Fill the inverse
//$chars_ext_ascii_to_utf = array();
//foreach($chars_utf_to_ext_ascii as $key => $val) { 
//	$chars_ext_ascii_to_utf[$val] = $key;
////	echo "\"$val\" =&gt; \"$key\",<br>\n";
//}


// translate "แ" to "รก" in a string
function translate_extended_ascii_to_utf($s_ext_ascii) {
//echo "s_ext_ascii: '".$s_ext_ascii."'<br>";
	$s_utf = "";
	for($i=0;$i<strlen($s_ext_ascii);$i++) {

		// Our chars go from 128 to 188 ASCII codes and begin with "ร"
		if(ord($s_ext_ascii[$i])>=128) {
			$s_char = get_utf_from_extended_ascii($s_ext_ascii[$i]);
		} else {
			$s_char = $s_ext_ascii[$i];
		}
		$s_utf .= $s_char;
//echo "'".$s_ext_ascii[$i]."' - ".ord($s_ext_ascii[$i])." - '".$s_char."' (".ord(substr($s_char,1,1)).")<br>";
	}
	return $s_utf;
}

// translate "รก" to "แ"
function translate_utf_to_extended_ascii($s_utf) {
	$s_ext_ascii = "";
//if($GLOBALS['_SESSION']['bdebug']===true) {
//	$GLOBALS['_SESSION']['sdebug'] .= "\n\ns_utf: '$s_utf'<br>\n";
//}
	for($i=0;$i<strlen($s_utf);$i++) {

		$s_char = "*";
		// Our chars go from 128 to 188 ASCII codes and begin with "ร"
		if($s_utf[$i]=="ร") {
			$i++;
			$s_char = get_extended_ascii_from_utf($s_utf[$i]);
		} else {
			$s_char = $s_utf[$i];
		}
		$s_ext_ascii .= $s_char;
//if($GLOBALS['_SESSION']['bdebug']===true) {
//	$GLOBALS['_SESSION']['sdebug'] .= " * ".htmlentities($s_utf[$i])." (".ord($s_utf[$i])."): '$s_char'<br>\n";
//}
	}
	return $s_ext_ascii;
}

// remove special chars and replace with "__" for use in SQL string
// nใo estแ mais sendo usado
function remove_special_chars($s_utf) {
	$s_ext_ascii = "";
//if($GLOBALS['_SESSION']['bdebug']===true) {
//	$GLOBALS['_SESSION']['sdebug'] .= "\n\ns_utf: '$s_utf'<br>\n";
//}
	for($i=0;$i<strlen($s_utf);$i++) {

		$s_char = "*";
		// Our chars go from 128 to 188 ASCII codes and begin with "ร"
		if($s_utf[$i]=="ร") {
			$i++;
			$s_char = "__";
		} else {
			$s_char = $s_utf[$i];
		}
		$s_ext_ascii .= $s_char;
//if($GLOBALS['_SESSION']['bdebug']===true) {
//	$GLOBALS['_SESSION']['sdebug'] .= " * ".htmlentities($s_utf[$i])." (".ord($s_utf[$i])."): '$s_char'<br>\n";
//}
	}
	return $s_ext_ascii;
}

// translate "แ" to "ก" (meaning "รก")
function get_utf_from_extended_ascii($ext_ascii) {

	$chars_ext_ascii_to_utf = array(
		'แ' => 161, '้' => 169, 'ํ' => 173, '๓' => 179, '๚' => 186, 'เ' => 160, '่' => 168, '์' => 172, '๒' => 178, '๙' => 185, 'ใ' => 163, '๕' => 181, 'โ' => 162, '๊' => 170, '๎' => 174, '๔' => 180, 'ไ' => 164, '๋' => 171, '๏' => 175, '๖' => 182, 'ü' => 188, '็' => 167, 'ม' => 129, 'ษ' => 137, 'อ' => 141, 'ำ' => 147, 'ฺ' => 154, 'ภ' => 128, 'ศ' => 136, 'ฬ' => 140, 'า' => 146, 'ู' => 153, 'ร' => 131, 'ี' => 149, 'ย' => 130, 'ส' => 138, 'ฮ' => 142, 'ิ' => 148, 'Û' => 155, 'ฤ' => 132, 'ห' => 139, 'ฯ' => 143, 'ึ' => 150, 'Ü' => 156, 'ว' => 135, 
	);
	return "ร".chr($chars_ext_ascii_to_utf[$ext_ascii]);
}

// translate "ก" (meaning "รก") to "แ"
function get_extended_ascii_from_utf($utf) {
	// "ก" => "แ" means that in a PostgreSQL DB with ASCII codification an extended character "แ" will be stored as "รก"
	$chars_utf_to_ext_ascii = array(
		161 => 'แ', 169 => '้', 173 => 'ํ', 179 => '๓', 186 => '๚', 160 => 'เ', 168 => '่', 172 => '์', 178 => '๒', 185 => '๙', 163 => 'ใ', 181 => '๕', 162 => 'โ', 170 => '๊', 174 => '๎', 180 => '๔', 164 => 'ไ', 171 => '๋', 175 => '๏', 182 => '๖', 188 => 'ü', 167 => '็', 129 => 'ม', 137 => 'ษ', 141 => 'อ', 147 => 'ำ', 154 => 'ฺ', 128 => 'ภ', 136 => 'ศ', 140 => 'ฬ', 146 => 'า', 153 => 'ู', 131 => 'ร', 149 => 'ี', 130 => 'ย', 138 => 'ส', 142 => 'ฮ', 148 => 'ิ', 155 => 'Û', 132 => 'ฤ', 139 => 'ห', 143 => 'ฯ', 150 => 'ึ', 156 => 'Ü', 135 => 'ว',
	);
	return $chars_utf_to_ext_ascii[ord($utf)];
}

?>