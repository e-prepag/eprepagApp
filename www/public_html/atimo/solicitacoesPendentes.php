<?php

    require_once "/www/class/phpmailer/class.phpmailer.php";
	require_once "/www/includes/configIP.php";
	require_once "/www/class/phpmailer/class.smtp.php";
    require_once "/www/includes/constantes.php";
    require_once "/www/includes/gamer/functions.php";
    require "/www/db/connect.php";
	require "/www/db/ConnectionPDO.php";;

    $conexao = ConnectionPDO::getConnection()->getLink();

    $queryUserGroup = "select shn_login, shn_mail from grupos_acesso_usuarios gru inner join usuarios u on gru.id = u.id where grupos_id = 45;";
    $selectRow = $conexao->prepare($queryUserGroup);
	$selectRow->execute();
	$resultRows = $selectRow->fetchAll(PDO::FETCH_ASSOC);
	
	$queryPendingResquest = "select * from estorno_pdv where ug_aprovacao is null and date(data_operacao) >= :DT;";
	$requestRow = $conexao->prepare($queryPendingResquest);
	$requestRow->bindValue(":DT", date("Y-m-d", strtotime("-7 days")));
	$requestRow->execute();
	$resultPending = $requestRow->fetchAll(PDO::FETCH_ASSOC);
	
	if(count($resultPending) > 0){
		
		foreach($resultRows as $key => $value){
			$html = "<div><table style='border: solid 1px #aaa;padding: 10px;width: 100%;'><tr style='background-color: #268FBD;color: white;font-weight: bold;'><td>Data</td><td>Tipo</td><td>Pdv</td><td>Operador BO</td><td>Valor</td><td>Status</td></tr>";
			foreach($resultPending as $keyPending => $valuePending){
				
				switch($valuePending["est_tipo"]){
					case 0:
						$tipo = "Adicionar saldo";
					break;
					case 1:
						$tipo = "Zerar saldo";
					break;
					case 2:
						$tipo = "Subtrair saldo";
					break;
				}
				
				$data = DateTime::createFromFormat('Y-m-d H:i:s.u', $valuePending["data_operacao"]);
				$html .= "<tr><td>".$data->format("d-m-Y H:i:s")."</td><td>".$tipo."</td><td>".$valuePending["ug_login"]."</td><td>".$valuePending["shn_login"]."</td><td>".$valuePending["est_valor"]."</td><td>Pendente</td></tr>";
			}
			$html .= "</table></div>";
			
			$layout = file_get_contents("/www/backoffice/admin/estorno/template-grupo.html");
			$layoutFinal = str_replace(["{conteudo}", "{data-atual}"], [$html, date("d-m-Y H:i:s")], $layout);
			
			$to = strtolower($value["shn_mail"]); 
			$cc = ""; 
			$bcc = ""; 
			$subject = utf8_decode("E-prepag - Solicitação de saldo");
			enviaEmail3($to, $cc, $bcc, $subject, $layoutFinal, "");		
			
			
		}
	}
	
?>