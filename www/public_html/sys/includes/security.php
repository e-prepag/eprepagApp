<?php
	$sql = "select bko_autoriza, bko_local_acesso from usuarios where id='".$_SESSION['iduser_bko_pub']."'";
//echo "sql: $sql<br>";
//echo "pos_pagina: $pos_pagina<br>";
//die();

	$result = pg_exec($connid, $sql);   
	$pgrow = pg_fetch_array($result);  
//echo "pgrow['bko_autoriza']: ".$pgrow['bko_autoriza']."<br>";
//echo "pgrow['bko_local_acesso']: ".$pgrow['bko_local_acesso']."<br>";
//die();
	if($pgrow['bko_autoriza'] != 'S')
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

//echo "num: ".$num."<br>";
//die();
		if($num != 1)
		{
			header("Location: ".$url_user_denied."");
			exit;
		}
	}
//echo "Passou<br>";

//die();

?>	