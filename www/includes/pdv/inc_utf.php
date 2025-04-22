<?php



// Fill the inverse
//$chars_ext_ascii_to_utf = array();
//foreach($chars_utf_to_ext_ascii as $key => $val) { 
//	$chars_ext_ascii_to_utf[$val] = $key;
////	echo "\"$val\" =&gt; \"$key\",<br>\n";
//}


// translate "�" to "á" in a string
function translate_extended_ascii_to_utf($s_ext_ascii) {
//echo "s_ext_ascii: '".$s_ext_ascii."'<br>";
	$s_utf = "";
	for($i=0;$i<strlen($s_ext_ascii);$i++) {

		// Our chars go from 128 to 188 ASCII codes and begin with "�"
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

// translate "á" to "�"
function translate_utf_to_extended_ascii($s_utf) {
	$s_ext_ascii = "";
//if($GLOBALS['_SESSION']['bdebug']===true) {
//	$GLOBALS['_SESSION']['sdebug'] .= "\n\ns_utf: '$s_utf'<br>\n";
//}
	for($i=0;$i<strlen($s_utf);$i++) {

		$s_char = "*";
		// Our chars go from 128 to 188 ASCII codes and begin with "�"
		if($s_utf[$i]=="�") {
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
// n�o est� mais sendo usado
function remove_special_chars($s_utf) {
	$s_ext_ascii = "";
//if($GLOBALS['_SESSION']['bdebug']===true) {
//	$GLOBALS['_SESSION']['sdebug'] .= "\n\ns_utf: '$s_utf'<br>\n";
//}
	for($i=0;$i<strlen($s_utf);$i++) {

		$s_char = "*";
		// Our chars go from 128 to 188 ASCII codes and begin with "�"
		if($s_utf[$i]=="�") {
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

// translate "�" to "�" (meaning "á")
function get_utf_from_extended_ascii($ext_ascii) {

	$chars_ext_ascii_to_utf = array(
		'�' => 161, '�' => 169, '�' => 173, '�' => 179, '�' => 186, '�' => 160, '�' => 168, '�' => 172, '�' => 178, '�' => 185, '�' => 163, '�' => 181, '�' => 162, '�' => 170, '�' => 174, '�' => 180, '�' => 164, '�' => 171, '�' => 175, '�' => 182, '�' => 188, '�' => 167, '�' => 129, '�' => 137, '�' => 141, '�' => 147, '�' => 154, '�' => 128, '�' => 136, '�' => 140, '�' => 146, '�' => 153, '�' => 131, '�' => 149, '�' => 130, '�' => 138, '�' => 142, '�' => 148, '�' => 155, '�' => 132, '�' => 139, '�' => 143, '�' => 150, '�' => 156, '�' => 135, 
	);
	return "�".chr($chars_ext_ascii_to_utf[$ext_ascii]);
}

// translate "�" (meaning "á") to "�"
function get_extended_ascii_from_utf($utf) {
	// "�" => "�" means that in a PostgreSQL DB with ASCII codification an extended character "�" will be stored as "á"
	$chars_utf_to_ext_ascii = array(
		161 => '�', 169 => '�', 173 => '�', 179 => '�', 186 => '�', 160 => '�', 168 => '�', 172 => '�', 178 => '�', 185 => '�', 163 => '�', 181 => '�', 162 => '�', 170 => '�', 174 => '�', 180 => '�', 164 => '�', 171 => '�', 175 => '�', 182 => '�', 188 => '�', 167 => '�', 129 => '�', 137 => '�', 141 => '�', 147 => '�', 154 => '�', 128 => '�', 136 => '�', 140 => '�', 146 => '�', 153 => '�', 131 => '�', 149 => '�', 130 => '�', 138 => '�', 142 => '�', 148 => '�', 155 => '�', 132 => '�', 139 => '�', 143 => '�', 150 => '�', 156 => '�', 135 => '�',
	);
	return $chars_utf_to_ext_ascii[ord($utf)];
}

?>