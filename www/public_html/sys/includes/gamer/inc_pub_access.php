<?php
function block_user_publisher() {
	if(b_is_Publisher()) {
		$strRedirect = "/sys/admin/commerce/index.php";
		header("Location: " . $strRedirect);
		die("Stop ABCG_SYS");
	}
}

function b_is_Administrator() {
	if ( $GLOBALS['_SESSION']['tipo_acesso_pub']=="AT") {
		return true;
	}
	return false;
}

function b_is_Financeiro() {
	if ( $GLOBALS['_SESSION']['userlogin_bko']=="TAMY" || $GLOBALS['_SESSION']['userlogin_bko']=="GLAUCIA" || $GLOBALS['_SESSION']['userlogin_bko']=="WAGNER" || $GLOBALS['_SESSION']['userlogin_bko']=="FABNASCI") {
		return true;
	}
	return false;
}

function b_is_Publisher() {
	if ( $GLOBALS['_SESSION']['tipo_acesso_pub']=="PU") {
		return true;
	}
	return false;
}

function b_is_AdminPlus() {
	if ( $GLOBALS['_SESSION']['userlogin_bko']=="GLAUCIA" || $GLOBALS['_SESSION']['userlogin_bko']=="WAGNER" || $GLOBALS['_SESSION']['userlogin_bko']=="FABNASCI") {
		return true;
	}
	return false;
}

function b_is_PublisherMostraEstoquePINs() {
	if ( b_is_Publisher() &&  
			(
				b_is_NDoors() || b_is_Webzen() || b_is_PayByCash() || b_is_Bilagames() || b_is_Axeso5() || b_is_OGPlanet() || b_is_Onnet() || b_is_Stardoll()
			)
		) {
		return true;
	}
	return false;
}


function b_is_Vostu_Stardoll() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="Vostu" || $GLOBALS['_SESSION']['opr_nome']=="Stardoll") {
		return true;
	}
	return false;
}

function b_is_Stardoll() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="Stardoll" ) {
		return true;
	}
	return false;
}

function b_is_Bilagames() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="BilaGames") {
		return true;
	}
	return false;
}

function b_is_Webzen() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="Webzen") {
		return true;
	}
	return false;
}

function b_is_NDoors() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="NDoors") {
		return true;
	}
	return false;
}

function b_is_ONGAME() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="ONGAME") {
		return true;
	}
	return false;
}

function b_is_G4BOX() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="G4Box") {
		return true;
	}
	return false;
}

function b_is_OGPlanet() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="OGPlanet") {
		return true;
	}
	return false;
}
function b_is_Axeso5() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="Axeso5") {
		return true;
	}
	return false;
}

function b_is_Onnet() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="Onnet") {
		return true;
	}
	return false;
}

function b_is_PayByCash() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="PayByCash") {
		return true;
	}
	return false;
}

function b_is_GrupoPublishersPINs() {
	$b_is_pub_grupo = (b_is_OGPlanet() || b_is_Axeso5() || b_is_Onnet() || b_is_PayByCash());
	return $b_is_pub_grupo;
}

function b_is_SearchStatusPIN() {
	if ( $GLOBALS['_SESSION']['userlogin_bko']=="USER_RIOT_TEST_SP" || $GLOBALS['_SESSION']['userlogin_bko']=="USER_RIOT_SP" || $GLOBALS['_SESSION']['userlogin_bko']=="USER_ONGAME_SP") {
		return true;
	}
	return false;
}

?>