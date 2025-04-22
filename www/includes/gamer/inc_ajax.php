<?php


function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function reqType(){
	if ($_SERVER['HTTP_X_REQUESTED_WITH']) {
		if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			return "Ajax";
		}
	}
	return "not Ajax";
}

/*
echo "isAjax(): ".((isAjax())?"Is Ajax":"Not AJAX")."<br>";
echo "isAjax(): ".reqType()."<br>";
*/
function block_direct_calling() {
	if(!isAjax()) {
		echo "Chamada não permitida<br>"; 
		die("Stop");
	} 
}

?>