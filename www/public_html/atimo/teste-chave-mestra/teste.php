<?php

	require_once "/www/db/connect.php";
	require_once "/www/db/ConnectionPDO.php";

	$usuario = '17371';

	function verificarIPUtilizado($usuario){
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		
		// Leva em consideração a quatidade de utilização nos ultimos 7 dias ordernando pela maior utilização que tenha pedido vinculado
        $sql = "select count(*) as qtde, ugl_ip from dist_usuarios_games_log where ugl_ug_id = :USUARIO and ugl_data_inclusao >= (CURRENT_TIMESTAMP - INTERVAL '7 day') and ugl_uglt_id = 5 group by ugl_ip order by qtde desc limit 1;";
		$query = $conexao->prepare($sql);
		$query->bindValue(":USUARIO", $usuario);
		$query->execute();
		$rowIP = $query->fetch(PDO::FETCH_ASSOC);
			
		if($rowIP != false){
			if($_SERVER["REMOTE_ADDR"] == $rowIP["ugl_ip"]){
			
			    return 'verdadeiro';
		    }else{
			    return 'chamou a função';
			}
		     
	    }else{
			return 'deu ruim'; 
	    }
		
	}
	
	echo verificarIPUtilizado($usuario);