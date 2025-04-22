<?php

// Use  filter_input()

/*
echo "<pre>";
print_r($_POST);
echo "<hr>";
print_r($_GET);
echo "</pre>";
*/
if(isset($_GET['dd_operadora'])) {
//	echo "Unset _GET['dd_operadora']<br>";
	unset($_GET['dd_operadora']);
	if(!isset($_POST['dd_operadora'])) {
//		echo "Unset dd_operadora<br>";
		unset($dd_operadora);
	}
}


?>