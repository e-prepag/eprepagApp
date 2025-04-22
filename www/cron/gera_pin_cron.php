<?php

// Livrodjx did it right

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink(); 


function saveLog($pins, $venda) {
	try {
		$file = fopen("/www/log/cron_pins.txt", "a+");
		fwrite($file, str_repeat("*", 50)."\n");
		fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
		fwrite($file, "PIN(S): ".$pins."\n");
		fwrite($file, "VENDA: ".$venda."\n");
		fwrite($file, str_repeat("*", 50)."\n");
		fclose($file);
	}catch (Exception $e) {
		echo "Error(6) writing monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage().PHP_EOL;
	}
}

function saveLogForRegisters($witch_if){
	try {
		$file = fopen("/www/log/cron_pins.txt", "a+");
		fwrite($file, str_repeat("*", 50)."\n");
		fwrite($file, "Qual condição entrou: ".$witch_if."\n");
		fwrite($file, str_repeat("*", 50)."\n");
		fclose($file);
	}catch (Exception $e) {
		echo "Error(6) writing monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage().PHP_EOL;
	}
}


$sql = "select vg_id, vg_ug_id, vg_data_inclusao, tvg.vgm_opr_codigo, tvg.vgm_qtde, vg_pagto_valor_pago , vg_pagto_tipo, vg_ultimo_status, vgm_id, vgm_pin_valor from tb_venda_games
inner join tb_venda_games_modelo tvg on tvg.vgm_vg_id = vg_id
left join tb_venda_games_modelo_pins tvgmp on tvgmp.vgmp_vgm_id = tvg.vgm_id
where 
	vgm_opr_codigo = 124
	and vg_data_inclusao > date('2023-01-01')
	and vg_ultimo_status = 3
	and tvg.vgm_pin_codinterno isnull 
	and tvgmp.vgmp_vgm_id isnull;";
	
$query = $connection->prepare($sql);
$query->execute();

$results = $query->fetchAll(PDO::FETCH_ASSOC);

if(count($results) > 0) {
	foreach($results as $key => $value) {
		
		$valor_pin = $value["vgm_pin_valor"];
		$vgm_id = $value["vgm_id"];
		$vg_id = $value["vg_id"];
		$vg_pagto_valor_pago = $value["vg_pagto_valor_pago"];
		$qtde_pins = $value["vgm_qtde"];

		$sql = "select * from pins where opr_codigo = 124 and pin_status = '1' and pin_valor = $valor_pin limit $qtde_pins;";
		$query = $connection->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_ASSOC);	
		
		$data_atual = date('Y-m-d');
		$hora_atual = date('H:i:s');
		
		$update_finish = "update tb_venda_games set vg_ultimo_status_obs = '', vg_usuario_obs = '', vg_pagto_data_inclusao = :DATA_INCLUSAO , vg_ultimo_status = 5, vg_concilia = 1, vg_data_concilia = :DATA_CONCILIA , vg_user_id_concilia = '0401121156014', vg_pagto_valor_pago = :VG_VALOR_PAGTO where vg_id = :VG_ID;";
		$update_query = $connection->prepare($update_finish);
		$update_query->bindValue(':DATA_INCLUSAO', $data_atual . " " . $hora_atual);
		$update_query->bindValue(':DATA_CONCILIA', $data_atual . " " . $hora_atual);
		$update_query->bindValue(':VG_VALOR_PAGTO', $vg_pagto_valor_pago);
		$update_query->bindValue(':VG_ID', $vg_id);
		$update_query->execute();
		
		$finish_sql = "update tb_pag_compras set datacompra = :DATA_COMPRA, status = 3,status_processed = 1,dataconfirma = :DATA_CONFIRMA where idvenda = :VG_ID;";	
		$finish_query = $connection->prepare($finish_sql);
		$finish_query->bindValue(':DATA_COMPRA', $data_atual . " " . $hora_atual);
		$finish_query->bindValue(':DATA_CONFIRMA', $data_atual . " " . $hora_atual);
		$finish_query->bindValue(':VG_ID', $vg_id);
		$finish_query->execute();
		
		if(count($result) > 1) {
			
			$todos_vgmp_pins = "";
			
			foreach($result as $key => $pin) {
				
				if(strpos($todos_vgmp_pins, ',') !== false) {
					$quantidade = count(explode($todos_vgmp_pins, ","));

					if($quantidade > $qtde_pins) {
						break;
					}
				}
				
				$update_sql = "update pins set pin_status = '3', pin_datavenda = :DATA_VENDA, pin_horavenda = :HORA_VENDA, pin_datapedido = :DATA_PEDIDO, 
				pin_horapedido = :HORA_PEDIDO where pin_codinterno = :PIN_CODINTERNO;";

				$data_atual = date('Y-m-d');
				$hora_atual = date('H:i:s');

				$query_update = $connection->prepare($update_sql);
				$query_update->bindValue(':DATA_VENDA', $data_atual);
				$query_update->bindValue(':HORA_VENDA', $hora_atual);
				$query_update->bindValue(':DATA_PEDIDO', $data_atual);
				$query_update->bindValue(':HORA_PEDIDO', $hora_atual);
				$query_update->bindValue(':PIN_CODINTERNO', $pin["pin_codinterno"]);
				$query_update->execute();


				$insert_sql = "INSERT INTO tb_venda_games_modelo_pins(vgmp_vgm_id, vgmp_pin_codinterno) VALUES (:VGMP_VGM_ID, :VGMP_PIN_CODINTERNO);";
				$query_insert = $connection->prepare($insert_sql);
				$query_insert->bindValue(':VGMP_VGM_ID', $vgm_id);
				$query_insert->bindValue(':VGMP_PIN_CODINTERNO', $pin["pin_codinterno"]);
				$query_insert->execute();
				
				$todos_vgmp_pins .= $pin["pin_codinterno"] . ",";
				
				
			}
			$update_sql = "UPDATE tb_venda_games_modelo SET vgm_pin_codinterno = :CODIGO_PIN where vgm_vg_id = :VG_ID;";
			$query_update = $connection->prepare($update_sql);
			$query_update->bindValue(':CODIGO_PIN', substr($todos_vgmp_pins,0, -1));
			$query_update->bindValue(':VG_ID', $vg_id);
			$query_update->execute();
			
			saveLogForRegisters("Entrou no IF quantidade > 1");
			saveLog($todos_vgmp_pins, $vg_id);
		}
		else {
			$vgmp_pin_codinterno = $result[0]["pin_codinterno"];
			
			$update_sql = "update pins set pin_status = '3', pin_datavenda = :DATA_VENDA, pin_horavenda = :HORA_VENDA, pin_datapedido = :DATA_PEDIDO, 
			pin_horapedido = :HORA_PEDIDO where pin_codinterno = :PIN_CODINTERNO;";

			$data_atual = date('Y-m-d');
			$hora_atual = date('H:i:s');

			$query_update = $connection->prepare($update_sql);
			$query_update->bindValue(':DATA_VENDA', $data_atual);
			$query_update->bindValue(':HORA_VENDA', $hora_atual);
			$query_update->bindValue(':DATA_PEDIDO', $data_atual);
			$query_update->bindValue(':HORA_PEDIDO', $hora_atual);
			$query_update->bindValue(':PIN_CODINTERNO', $vgmp_pin_codinterno);
			$query_update->execute();


			$insert_sql = "INSERT INTO tb_venda_games_modelo_pins(vgmp_vgm_id, vgmp_pin_codinterno) VALUES (:VGMP_VGM_ID, :VGMP_PIN_CODINTERNO);";
			$query_insert = $connection->prepare($insert_sql);
			$query_insert->bindValue(':VGMP_VGM_ID', $vgm_id);
			$query_insert->bindValue(':VGMP_PIN_CODINTERNO', $vgmp_pin_codinterno);
			$query_insert->execute();
			
			
			$update_sql = "UPDATE tb_venda_games_modelo SET vgm_pin_codinterno = :CODIGO_PIN  where vgm_vg_id = :VG_ID AND vgm_id = :VGM_ID;";
			$query_update = $connection->prepare($update_sql);
			$query_update->bindValue(':CODIGO_PIN', $result[0]["pin_codinterno"]);
			$query_update->bindValue(':VG_ID', $vg_id);
			$query_update->bindValue(':VGM_ID', $vgm_id);
			$query_update->execute();
			saveLogForRegisters("Entrou no Else");
			saveLog($vgmp_pin_codinterno, $vg_id);
		}
		
		
	}
}

?>