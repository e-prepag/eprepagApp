<?php
	session_start();

//	$username = "Gestor";
//	$password = "r_e_c_a_r_g_a!";
//	if($_SERVER['SERVER_NAME'] == "200.196.233.24" || $_SERVER['SERVER_NAME'] == "www.e-prepag.com.br" || $_SERVER['SERVER_NAME'] == "bo.e-prepag.com.br")
//	{
//		if ($PHP_AUTH_USER != $username || $PHP_AUTH_PW != $password)
//		{ 
//			header("WWW-Authenticate: basic realm=Backoffice");
//			header("HTTP/1.0 401 Unauthorized");
//			echo "<META HTTP-EQUIV='Refresh' Content=0;URL='http://www.e-prepag.com.br/prepag/mensagens/access_denied.php'>";
//			exit;
//		}
//	}	
	if(empty($_SESSION["iduser_bko_pub"]))
	{
//		echo "<script>";
//		echo "setTimeout('top.location = \'".$url_session_expires."\'', 0);";
//		echo "</script>";

		header("Location: ".$url_session_expires);	//"/sys/admin/index.php"

		exit;
	}

	//$connid = pg_connect("host=$host port=$port dbname=$banco user=$usuario password=$senha");
	
	$sql = "select bko_autoriza, bko_local_acesso from usuarios where id='".$_SESSION['iduser_bko_pub']."'";
	$result = pg_exec($connid, $sql);   
	$pgrow = pg_fetch_array($result);  

	/*if($pgrow['bko_autoriza'] != 'S')
	{
		session_destroy();
		echo "<script>";
		echo "setTimeout('top.location = \'".$url_user_blocked."\'', 0);";
		echo "</script>";
		exit;
	}
	else
	{
		$num = substr($pgrow['bko_local_acesso'], $pos_pagina, 1);

		if($num != 1)
		{
			header("Location: ".$url_user_denied."");
			exit;
		}
	}*/
?>