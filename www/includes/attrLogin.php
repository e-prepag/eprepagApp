<?php
	//configucações de segurança para o login de lan houses
	$cfgLoginLan = new stdClass();
	$cfgLoginLan->tempoMaxBloqueio = 300;
	$cfgLoginLan->maxTentativas = 5;

	//configucações de segurança para o login de gamers
	$cfgLoginGamer = new stdClass();
	$cfgLoginGamer->tempoMaxBloqueio = 300;
	$cfgLoginGamer->maxTentativas = 5;
?>