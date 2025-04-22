<?php

    require "/www/db/connect.php";
	require "/www/db/ConnectionPDO.php";
	
	$conexao = ConnectionPDO::getConnection()->getLink();

    if(isset($_GET["acao"]) && $_GET["acao"] == "listar"){
		$data = ["data" => []];
		$sql = "select codigo,login,ip,TO_CHAR(data_requisicao, 'DD-MM-YYYY HH24:MI:SS') as data_requisicao,qtde from bloqueia_login_usuario where visualizacao = 'S' order by data_requisicao desc;";
		$selectRows = $conexao->prepare($sql);
		$selectRows->execute();
		$resultRows = $selectRows->fetchAll(PDO::FETCH_ASSOC);

		if(count($resultRows) > 0){
			foreach($resultRows as $key => $value){
				 $dataKeys = array_keys($value);
				 $acao = ($value["qtde"] >= 5)? '<button class="btn btn-success btn-liberar" data-codigo="'.$value["codigo"].'">Liberar</button>': '<span>Usuário livre</span>'; 
				 $dataLine = [
					  $dataKeys[0] => $value["codigo"],
					  $dataKeys[1] => $value["login"],
					  $dataKeys[2] => $value["ip"],
					  $dataKeys[3] => $value["data_requisicao"],
					  $dataKeys[4] => $value["qtde"],
					  "acao" => $acao
				 ];
				 array_push($data["data"], $dataLine);
			}
			
		}
	    echo json_encode($data);
	}else if(isset($_GET["acao"]) && $_GET["acao"] == "apagar"){
		$sql = "update bloqueia_login_usuario set qtde = 0 where codigo = :CODIGO;";
		$deleteRow = $conexao->prepare($sql);
		$deleteRow->bindValue(":CODIGO", $_POST["codigo"]);
		$deleteRow->execute();
		if($deleteRow->rowCount() > 0){
			echo "Bloqueio liberado com sucesso";
			exit;
		}
		
		echo "Não foi possivel liberar o bloqueio";
	}
    

?>