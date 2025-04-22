<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1);

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 

$time_start = getmicrotime();

echo PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;
 
$msg = "";
//Inicia transacao
$sql = "BEGIN TRANSACTION ";
$ret = SQLexecuteQuery($sql);
if(!$ret) $msg = "Erro ao iniciar transacao.".PHP_EOL;

//transacao
if(empty($msg)){
			
	//Selecionado os valores para depósito
	$sql = "select ugo_ug_email, ug_id, sum(ugo_valor_credito) as ugo_total from usuarios_games_ofertas ugo inner join usuarios_games ug on (ugo.ugo_ug_email=ug.ug_email) where ugo_status=1 group by ugo_ug_email, ug_id"; 
	echo "SQL de Levantamento dos Créditos:".PHP_EOL.$sql.PHP_EOL;
	$rs_depositos = SQLexecuteQuery($sql);
	while($rs_depositos_row = pg_fetch_array($rs_depositos)){
		echo "ID usuario: ".$rs_depositos_row['ug_id'].PHP_EOL;
		echo "Email usuario: ".$rs_depositos_row['ugo_ug_email'].PHP_EOL;
		echo "Valor deposito: ".$rs_depositos_row['ugo_total'].PHP_EOL;

		//convertendo o valor do depósito 
		$valor_deposito = ($rs_depositos_row['ugo_total']/100);
		$valor_deposito_epp = (new ConversionPINsEPP)->get_ValorEPPCash('E', $valor_deposito);

		//SQL gerar o registro de venda sem modelo
		$novo_venda_id = obterIdVendaValido();
		$sql_credito_venda = "insert into tb_venda_games (" .
			"vg_id, vg_ug_id, vg_data_inclusao, vg_pagto_tipo, " .
			"vg_ultimo_status, vg_ultimo_status_obs, vg_http_referer_origem, vg_http_referer,".
			"vg_concilia, vg_data_concilia, vg_pagto_data, vg_pagto_data_inclusao,". 
			"vg_deposito_em_saldo,vg_deposito_em_saldo_valor, vg_valor_eppcash) values (";
		$sql_credito_venda .= SQLaddFields($novo_venda_id, "") . ",";
		$sql_credito_venda .= SQLaddFields($rs_depositos_row['ug_id'], "") . ",";
		$sql_credito_venda .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
		$sql_credito_venda .= SQLaddFields($GLOBALS['PAGAMENTO_OFERTAS_NUMERIC'], "") . ",";
		$sql_credito_venda .= SQLaddFields($GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'], "") . ",";
		$sql_credito_venda .= SQLaddFields("Depósito em Saldo de Ofertas", "s") . ", ";
		$sql_credito_venda .=  "'', ";
		$sql_credito_venda .= "'', ";
		$sql_credito_venda .=  "1, ";
		$sql_credito_venda .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
		$sql_credito_venda .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
		$sql_credito_venda .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
		$sql_credito_venda .= SQLaddFields("1", "") .",";
		$sql_credito_venda .= SQLaddFields($valor_deposito, "").",";
		$sql_credito_venda .= SQLaddFields($valor_deposito_epp, "")." ";
		$sql_credito_venda .= ")";

		echo "SQL INSERT VENDA:".PHP_EOL.$sql_credito_venda.PHP_EOL;
		$rs_venda = SQLexecuteQuery($sql_credito_venda);
		if(!$rs_venda) {
			 $msg .= "Erro ao inserir o registro de venda sem modelo.".PHP_EOL;
		}
		else {
			//SQL gerar o registro de pagamento
			$orderId = get_newOrderID();
			$sql_credito_pagamento = "INSERT INTO tb_pag_compras (numcompra, idvenda, cliente_nome, idcliente, tipo_cliente, frete, manuseio, taxas, subtotal, cesta, tipoPagto, prazo, numParcelas, valorParcela, total, dataInicio, status, iforma, banco, tipo_deposito, status_processed, datacompra, dataconfirma, idvenda_origem) values ('".$orderId."',".$novo_venda_id.", (select ug_nome from usuarios_games where ug_email='".$rs_depositos_row['ugo_ug_email']."'), ".$rs_depositos_row['ug_id'].", 'M', 0, 0, 0, 0, 'Depósito em Saldo de Ofertas D (".$GLOBALS['PAGAMENTO_OFERTAS_NUMERIC'].")', 0, 0, 0, 0, ".number_format( $rs_depositos_row['ugo_total'], 0, ',', '').", CURRENT_TIMESTAMP, 3, '".$GLOBALS['FORMAS_PAGAMENTO']['OFERTAS']."', '".$GLOBALS['PAGAMENTO_OFERTAS_COD_BANCO']."', ".$GLOBALS['TIPO_DEPOSITO']['DEPOSITO_OFERTAS'].", 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 0)";
			echo "SQL INSERT PAGAMENTO:".PHP_EOL.$sql_credito_pagamento.PHP_EOL;
			$rs_pagamento = SQLexecuteQuery($sql_credito_pagamento);
			if(!$rs_pagamento) {
				 $msg .= "Erro ao inserir o registro de pagamento.".PHP_EOL;
			}
			else {
				//SQL INSERT FIFO
				$sql_credito = "insert into saldo_composicao_fifo (ug_id,scf_data_deposito,scf_valor,scf_valor_disponivel,scf_status,scf_canal,scf_comissao,scf_id_pagamento,vg_id) values (".$rs_depositos_row['ug_id'].", NOW(), ".$valor_deposito.", ".$valor_deposito.",1,'G',0, '".$GLOBALS['FORMAS_PAGAMENTO']['OFERTAS']."',".$novo_venda_id.")";
				echo "SQL INSERT FIFO:".PHP_EOL.$sql_credito.PHP_EOL;
				$rs_fifo = SQLexecuteQuery($sql_credito);
				if(!$rs_fifo) {
					 $msg .= "Erro ao inserir o registro na tabela FIFO.".PHP_EOL;
				}
				else {
					//SQL UPDATE USUARIOS_GAMES
					$sql_update_saldo = "update usuarios_games SET ug_perfil_saldo=ug_perfil_saldo+".$valor_deposito." where ug_id=".$rs_depositos_row['ug_id'];
					echo "SQL UPDATE USUARIOS_GAMES:".PHP_EOL.$sql_update_saldo.PHP_EOL;
					$rs_update_saldo = SQLexecuteQuery($sql_update_saldo);
					if(!$rs_update_saldo) {
						 $msg .= "Erro ao inserir o registro na tabela FIFO.".PHP_EOL;
					}
					else {
						//SQL LEVANTA DADOS LISTA OFERTAS CREDITADAS
						$sql_levanta_dados_lista = "select ugo.ugo_id, ugo_transaction_id, ugoc_descricao, ugo_data_adesao_oferta as data, ugo_valor_credito from usuarios_games_ofertas ugo inner join usuarios_games_ofertas_canal ugoc on (ugo.ugo_ugoc_id=ugoc.ugoc_id) where ugo_status=1 and ugo_ug_email='".$rs_depositos_row['ugo_ug_email']."'"; //
						$rs_levanta_dados_lista = SQLexecuteQuery($sql_levanta_dados_lista);
						$aux_lista = "<table cellspacing='0' cellpadding='5' width='100%' style='font: normal 13px arial, sans-serif;margin-top:5px;'><tr bgcolor='#CCCCCC'><td width='3'>&nbsp;</td><td align='left'><b>ID</b></td><td align='center'><b>Descrição</b></td><td align='center'><b>Data/Horário</b></td><td align='center'><b>Valor</b></td></tr>";
						while($rs_levanta_dados_lista_row = pg_fetch_array($rs_levanta_dados_lista)){
							$aux_lista .= "<tr bgcolor='#E6E6E6'><td width='3'>&nbsp;</td><td align='left'>".$rs_levanta_dados_lista_row['ugo_id']."</td><td align='center'>".$rs_levanta_dados_lista_row['ugoc_descricao']."</td><td align='center'>". $rs_levanta_dados_lista_row['data']."</td><td align='center'><font face='arial' color='#008F16' size='2'><b>". $rs_levanta_dados_lista_row['ugo_valor_credito']. " EPP Cash</b></font></tr>";
						}//end while 
						$aux_lista .= "</table>";
						
						//SQL UPDATE USUARIOS_GAMES_OFERTAS
						$sql_update = "update usuarios_games_ofertas SET ugo_status=3, ugo_vg_id=".$novo_venda_id." where ugo_status=1 and ugo_ug_email='".$rs_depositos_row['ugo_ug_email']."'";
						echo "SQL UPDATE USUARIOS_GAMES_OFERTAS:".PHP_EOL.$sql_update.PHP_EOL;
						$rs_update_ofertas = SQLexecuteQuery($sql_update);
						if(!$rs_update_ofertas) {
							 $msg .= "Erro ao inserir o registro na tabela FIFO.".PHP_EOL;
						}
						else {
							$objEnvioEmailAutomatico = new EnvioEmailAutomatico('G','DepositoOfertas');
							$objEnvioEmailAutomatico->setUgID($rs_depositos_row['ug_id']);
							$objEnvioEmailAutomatico->setListaCreditoOferta($aux_lista);
							echo $objEnvioEmailAutomatico->MontaEmailEspecifico();
						}//end else 
					}//end else (!$rs_update_saldo)
				}//end else (!$rs_fifo)
			}//end else (!$rs_pagamento)
		}//end else (!$rs_venda) 
		echo str_repeat("-",80).PHP_EOL;
	}//end while
	if (pg_numrows($rs_depositos)==0) echo "Nenhum registro encontrado!".PHP_EOL;

}//end if(empty($msg))

//Finaliza transacao
if(empty($msg)){
	$sql = "COMMIT TRANSACTION ";
	$ret = SQLexecuteQuery($sql);
	if(!$ret) $msg .= "Erro ao comitar transa&ccedil;&atilde;o.".PHP_EOL;
} else {
	$sql = "ROLLBACK TRANSACTION ";
	$ret = SQLexecuteQuery($sql);
	if(!$ret) $msg .= "Erro ao dar rollback na transa&ccedil;&atilde;o.".PHP_EOL;
}
echo $msg.PHP_EOL;
echo "Elapsed time : " . number_format(getmicrotime() - $time_start, 2, '.', '.') . " segundos.".PHP_EOL.str_repeat("=",80).PHP_EOL;
?>