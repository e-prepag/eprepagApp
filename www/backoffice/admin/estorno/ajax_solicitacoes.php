<?php

	require_once "/www/class/phpmailer/class.phpmailer.php";
	require_once "/www/includes/configIP.php";
	require_once "/www/class/phpmailer/class.smtp.php";
    require_once "/www/includes/constantes.php";
    require_once "/www/includes/gamer/functions.php";
    require "/www/db/connect.php";
	require "/www/db/ConnectionPDO.php";
	
	$conexao = ConnectionPDO::getConnection()->getLink();

    if(isset($_GET["acao"]) && $_GET["acao"] == "listar"){
		$data = ["data" => []];
		$sql = "select TO_CHAR(data_operacao, 'DD-MM-YYYY HH24:MI:SS') as data_operacao, est_valor, est_tipo, shn_login, ug_login, ug_descricao,est_id from estorno_pdv where ug_aprovacao is null and date(data_operacao) between :DT_INICIAL and :DT_FINAL and est_tipo = :TIPO and date(data_operacao) > '2023-07-07';";
		$selectRows = $conexao->prepare($sql);
		$selectRows->bindValue(":DT_INICIAL", $_GET["dt_inicial"]);
		$selectRows->bindValue(":DT_FINAL", $_GET["dt_final"]);
		$selectRows->bindValue(":TIPO", $_GET["tipo"]);
		$selectRows->execute();
		$resultRows = $selectRows->fetchAll(PDO::FETCH_ASSOC);

		if(count($resultRows) > 0){
			foreach($resultRows as $key => $value){
				 $dataKeys = array_keys($value);
				 $acao = '<button class="btn btn-success btn-aprovar right10 bottom10" data-codigo="'.$value["est_id"].'" data-login="'.$value["shn_login"].'">Aprovar</button><button class="btn btn-danger btn-negar bottom10" data-codigo="'.$value["est_id"].'" data-login="'.$value["shn_login"].'">Negar</button>'; 
				 
				 switch($value["est_tipo"]){
					 case 0:
					    $tipo = "Adicionar"; 
					 break;
					 case 1:
					    $tipo = "Zerar saldo";
					 break;
					 case 2:
					    $tipo = "Subtrair";
					 break;
				 }
				 
				 $dataLine = [
					  $dataKeys[0] => $value["data_operacao"],
					  $dataKeys[1] => $value["est_valor"],
					  $dataKeys[2] => $tipo,
					  $dataKeys[3] => $value["shn_login"],
					  $dataKeys[4] => $value["ug_login"],
					  $dataKeys[5] => utf8_encode($value["ug_descricao"]),
					  "acoes" => $acao
				 ];
				 array_push($data["data"], $dataLine);
			}
			
		}
		//var_dump($data);
	    echo json_encode($data);
	}else if(isset($_POST["acao"]) && ($_POST["acao"] == "aprovar" || $_POST["acao"] == "negar")){
		
        $acao = ($_POST["acao"] == "aprovar")? "S": "N";

		$sql = "update estorno_pdv set ug_aprovacao = :ACAO, ug_user_aprova = :USER where est_id = :CODIGO;";
		$updateRow = $conexao->prepare($sql);
		$updateRow->bindValue(":CODIGO", $_POST["codigo"]);
		$updateRow->bindValue(":ACAO", $acao);
		$updateRow->bindValue(":USER", $_POST["nome"]);
		$updateRow->execute();
		if($updateRow->rowCount() > 0){
			
			$queryRow = "select ug_id, ug_saldo_atual, est_tipo, est_valor, ug_login, ug_user_aprova, shn_login, to_char(data_operacao, 'DD-MM-YYYY HH24:MI:SS') data_operacao from estorno_pdv where est_id = :CODIGO;";
			$selectRow = $conexao->prepare($queryRow);
			$selectRow->bindValue(":CODIGO", $_POST["codigo"]);
			$selectRow->execute();
			$data = $selectRow->fetch(PDO::FETCH_ASSOC);
			
			if($_POST["acao"] == "aprovar"){
				if($data["est_tipo"] == 0 || $data["est_tipo"] == 2){
					$queryRow = "UPDATE dist_usuarios_games SET ug_perfil_saldo = ug_perfil_saldo + :VALOR WHERE ug_id = :USUARIO;";
					$updateUserRow = $conexao->prepare($queryRow);
					$updateUserRow->bindValue(":VALOR", $data["est_valor"]);
					$updateUserRow->bindValue(":USUARIO", $data["ug_id"]);
					$updateUserRow->execute();
					
					if($updateUserRow->rowCount() > 0){
						echo "Ação realizada com sucesso";
					}else{
						echo "Erro ao realizar a ação";
					}
				}else{
					$queryRow = "UPDATE dist_usuarios_games SET ug_perfil_saldo = 0 WHERE ug_id = :USUARIO;";
					$updateUserRow = $conexao->prepare($queryRow);
					$updateUserRow->bindValue(":USUARIO", $data["ug_id"]);
					$updateUserRow->execute();
					
					if($updateUserRow->rowCount() > 0){
						echo "Ação realizada com sucesso";
					}else{
						echo "Erro ao realizar a ação";
					}
				}
		    }else{
				// quando a requisição é negada pelo gestor
				echo "Ação realizada com sucesso";
			}
			
			$queryRow = "select shn_mail from usuarios where shn_login = :LOGIN;";
			$selectRow = $conexao->prepare($queryRow);
			$selectRow->bindValue(":LOGIN", $_POST["login"]);
			$selectRow->execute();
		    $resultRow = $selectRow->fetch(PDO::FETCH_ASSOC);
						
			$tipoAcao = ($data["est_tipo"] == 0)? "adicionar saldo": (($data["est_tipo"] == 2)? "subtrair saldo": "zerar saldo");
			
			$to = strtolower($resultRow["shn_mail"]); 
			$cc = ""; 
			$bcc = ""; 
			$subject = utf8_decode("E-prepag - Solicitação de ".$tipoAcao);
			$legendaAcao = ($acao == "S")? "Aprovada":" Negada";
			$html = file_get_contents("./template.html");
			$html = str_replace(["{data-atual}", "{tipo}", "{nome}",  "{data}", "{operador}", "{resposta}"], [date("d-m-Y H:i:s"), $tipoAcao, $data["ug_login"], $data["data_operacao"], $data["ug_user_aprova"], $legendaAcao], $html);
			$msg = $html; 
			enviaEmail3($to, $cc, $bcc, $subject, $msg, "");
				
			exit;
	
		}
		
		echo "Não foi possivel efetuar sua escolha";
	
	}

?>