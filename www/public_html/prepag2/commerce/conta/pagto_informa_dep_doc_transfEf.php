<?php 

header("Content-Type: text/html; charset=ISO-8859-1; P3P: CP='CAO PSA OUR'",true);
require_once "../../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
validaSessao(); 

require_once DIR_INCS . "gamer/venda_e_modelos_logica_epp.php"; 

require_once DIR_INCS . "gamer/pagto_informa_dep_doc_transf_verificacoes.php";


	//Variaveis do Formulario
	$pagto_banco 		= $_SESSION['pagto.pagto_banco'];
	$pagto_local 		= $_SESSION['pagto.pagto_local'];
	$pagto_num_docto 	= $_SESSION['pagto.pagto_num_docto'];
	$pagto_valor_pago 	= $_SESSION['pagto.pagto_valor_pago'];
//	$pagto_data_Dia 	= $_SESSION['pagto.pagto_data_Dia'];
//	$pagto_data_Mes 	= $_SESSION['pagto.pagto_data_Mes'];
//	$pagto_data_Ano 	= $_SESSION['pagto.pagto_data_Ano'];
	$pagto_data_data 	= $_SESSION['pagto.pagto_data_data'];
	$pagto_data_horas 	= $_SESSION['pagto.pagto_data_horas'];
	$pagto_data_minutos	= $_SESSION['pagto.pagto_data_minutos'];

	$pagto_data_data_full = $pagto_data_data ." ".$pagto_data_horas .":". $pagto_data_minutos;


	require_once DIR_INCS . "gamer/pagto_informa_dep_doc_transf_validacoes.php";

	if($msg == ""){
		//move arquivos temporarios da venda para definitivo
		$arquivos = buscaArquivosIniciaCom($FOLDER_COMMERCE_UPLOAD_TMP, 'nome', 'asc', "money_comprovante_" . $venda_id . "_");
		for($j = 0; $j < count($arquivos); $j++){
			if(is_file($FOLDER_COMMERCE_UPLOAD_TMP . $arquivos[$j])) {
				if(!rename($FOLDER_COMMERCE_UPLOAD_TMP . $arquivos[$j], $FOLDER_COMMERCE_UPLOAD . $arquivos[$j])){
					$msg .= "Não foi possivel salvar o comprovante, tente novamente.\n"; 
				}
			}
		}
	}
	
	if($msg != ""){
		//redireciona
		$strRedirect = "/prepag2/commerce/conta/pagto_informa_dep_doc_transf.php?msg=" . urlencode($msg);
		redirect($strRedirect);
	}

	//Atualiza dados do pagamento
	$sql  = "update tb_venda_games set ";
	$sql .= "	vg_ultimo_status = " . 		SQLaddFields($STATUS_VENDA['DADOS_PAGTO_RECEBIDO'], "") . ",";
	$sql .= "	vg_pagto_data_inclusao = ".	SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
	$sql .= "	vg_pagto_banco = " . 		SQLaddFields($pagto_banco, "s") . ",";
	$sql .= "	vg_pagto_local = " . 		SQLaddFields($pagto_local, "s") . ",";
	$sql .= "	vg_pagto_num_docto = " . 	SQLaddFields(implode("|", $pagto_num_docto), "s") . ",";
	$sql .= "	vg_pagto_valor_pago = " . 	SQLaddFields(moeda2numeric($pagto_valor_pago), "") . ",";
//	$sql .= "	vg_pagto_data = " . 		SQLaddFields($pagto_data_Ano . $pagto_data_Mes . $pagto_data_Dia, "s") . " ";
	$sql .= "	vg_pagto_data = " . 		SQLaddFields(monta_data_gravacao($pagto_data_data)." ".$pagto_data_horas.":".$pagto_data_minutos, "s") . " ";
	$sql .= "where vg_id = " . $venda_id;

	$ret = SQLexecuteQuery($sql);
	if(!$ret){
		$msg = "Erro ao atualizar venda.\n";
		$strRedirect = "/prepag2/commerce/conta/pagto_informa_dep_doc_transf.php?msg=" . urlencode($msg);
	
	} else {

		//Variaveis do Formulario
		unset($_SESSION['pagto.pagto_banco']);
		unset($_SESSION['pagto.pagto_local']);
		unset($_SESSION['pagto.pagto_num_docto']);
		unset($_SESSION['pagto.pagto_valor_pago']);
//		unset($_SESSION['pagto.pagto_data_Dia']);
//		unset($_SESSION['pagto.pagto_data_Mes']);
//		unset($_SESSION['pagto.pagto_data_Ano']);
//		unset($_SESSION['pagto.pagto_data_Ano']);
		unset($_SESSION['pagto.pagto_data_data']);
		unset($_SESSION['pagto.pagto_data_horas']);
		unset($_SESSION['pagto.pagto_data_minutos']);
		
		$strRedirect = "/prepag2/commerce/conta/pagto_compr_redirect.php?venda=" . $venda_id;
		
		//Log na base
		usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['INFORMA_DADOS_DE_PAGAMENTO'], null, $venda_id);
		
		//Envia email
		//--------------------------------------------------------------------------------
		$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
		$sql  = "select * from tb_venda_games vg " .
				"inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
				"where vg.vg_id = " . $venda_id . " and vg.vg_ug_id=" . $usuarioGames->getId();
		$rs_venda_modelos = SQLexecuteQuery($sql);

		$parametros['prepag_dominio'] = "http://www.e-prepag.com.br";

		$msgEmail  = email_cabecalho($parametros);
	    $msgEmail .= "	<br>
	    				<table border='0' cellspacing='0' width='90%'>
			            <tr>
			            	<td class='texto'> 
	    						Obrigado por realizar sua compra conosco!<br><br><br>
	    						O crédito para a liberação do seu pedido número <b>" . formata_codigo_venda($venda_id) . "</b> foi recebido e validado.<br> 
	    						O seu pedido já se encontra em fase de processamento.
			                </td>
			            </tr>
						</table>";
		
		$msgEmail .= "	<br>
						<table border='0' cellspacing='0' width='90%'>
			    	        <tr bgcolor='FFFFFF'>
			    	          <td class='texto' colspan='4'><b>INFORMAÇÕES DO PEDIDO</b><br><br></td>
			    	        </tr>
			    	        <tr bgcolor='C0C0C0'>
			    	          <td class='texto' align='center'><b>CODIGO</b></td>
			    	          <td class='texto' align='center'><b>PRODUTO</b></td>
			    	          <td class='texto' align='center'><b>QTDE</b></td>
			    	          <td class='texto' align='right'><b>PRC UNIT</b></td>
			    	          <td class='texto' align='right'><b>PRC TOTAL</b></td>
			    	        </tr>";
					while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)){
							$codigo = $rs_venda_modelos_row['vgm_id'];
							$qtde = $rs_venda_modelos_row['vgm_qtde'];
							$valor = $rs_venda_modelos_row['vgm_valor'];
							$total_geral += $valor*$qtde;
	
			$msgEmail .= "  <tr bgcolor='F0F0F0'>
			    	          <td class='texto' align='center'>" . $codigo . "</td>
			    	          <td class='texto' width='200'>
			    	          	&nbsp;&nbsp;
			    	          	" . $rs_venda_modelos_row['vgm_nome_produto']; 
			    	          	if($rs_venda_modelos_row['vgm_nome_modelo'] != ""){ $msgEmail .= " - " . $rs_venda_modelos_row['vgm_nome_modelo'];}
			$msgEmail .= "    </td>
			    	          <td class='texto' align='center'>" . $qtde . "</td>
			    	          <td class='texto' align='right'>" . number_format($valor, 2, ',', '.') . "</td>
			    	          <td class='texto' align='right'>" . number_format($valor*$qtde, 2, ',', '.') . "</td>
			    	        </tr>";
					}
		$msgEmail .= "  <tr bgcolor='F0F0F0'>
		    	          <td colspan='3'>&nbsp;</td>
		    	          <td class='texto' align='right'><b>Total</b></td>
		    	          <td class='texto' align='right'><b>" . number_format($total_geral, 2, ',', '.') . "</b></td>
		    	        </tr>
					</table>";

	    $msgEmail .= "	<br>
	    				<table border='0' cellspacing='0'>
			            <tr>
			            	<td class='texto' colspan='2'>&nbsp;&nbsp;<b>DADOS DO PAGAMENTO:</b></td>
			            </tr>
			            <tr>
			            	<td class='texto' nowrap>&nbsp;&nbsp;<b>Banco:</b></td>
			                <td class='texto' nowrap>" . $PAGTO_BANCOS[$pagto_banco] . "</td>
			            </tr>
			            <tr>
			            	<td class='texto' nowrap>&nbsp;&nbsp;<b>Local:</b></td>
			                <td class='texto' nowrap>" . $PAGTO_LOCAIS[$pagto_banco][$pagto_local] . "</td>
			            </tr>
			            <tr>
			            	<td class='texto' nowrap>&nbsp;&nbsp;<b>Data de Pagamento:</b></td>
			                <td class='texto' nowrap>" . $pagto_data_data_full . "</td>
			            </tr>";
//			                <td class='texto' nowrap>" . $pagto_data_Dia . "/" . $pagto_data_Mes . "/" . $pagto_data_Ano . "</td>

						$pagto_nome_docto_Ar = split(";", $PAGTO_NOME_DOCTO[$pagto_banco][$pagto_local]);
						for($i=0; $i<count($pagto_nome_docto_Ar); $i++){
	    $msgEmail .= "  <tr>
			            	<td class='texto' nowrap>&nbsp;&nbsp;<b>" . $pagto_nome_docto_Ar[$i] . ":</b></td>
			                <td class='texto' nowrap>" . $pagto_num_docto[$i] . "</td>
			            </tr>";
						}
	    $msgEmail .= "  <tr>
			            	<td class='texto' nowrap>&nbsp;&nbsp;<b>Valor Pago:</b></td>
			                <td class='texto' nowrap>" . $pagto_valor_pago . "</td>
			            </tr>
						</table>";
	
	    $msgEmail .= "	<br>
	    				<table border='0' cellspacing='0' width='90%'>
			            <tr>
			            	<td class='texto' colspan='2'><b>DADOS DO COMPRADOR</b></td>
			            </tr>
			            <tr>
			            	<td class='texto'> 
			            		" . $usuarioGames->getNome() . "<br>
			                	CPF: " . $usuarioGames->getCPF() . "<br>
			                	" . $usuarioGames->getEndereco() . "<br>
			                	" . $usuarioGames->getBairro() . ", " . $usuarioGames->getCidade() . " - " . $usuarioGames->getEstado() . "<br>
			                	" . $usuarioGames->getCEP() . "<br>
			                </td>
			            </tr>
						</table>";
	
	    $msgEmail .= "	<br>
	    				<table border='0' cellspacing='0' width='90%'>
			            <tr>
			            	<td class='texto' colspan='2'><b>DADOS DE ENTREGA</b></td>
			            </tr>
			            <tr>
			            	<td class='texto'> 
			            		O PRODUTO SERÁ ENTREGUE ELETRONICAMENTE NO E-MAIL <b>" . $usuarioGames->getEmail() . "</b><br>
								APÓS A CONFIRMAÇÃO DE PAGAMENTO.
			                </td>
			            </tr>
						</table>";
						
	    $msgEmail .= "	<br>
	    				<table border='0' cellspacing='0' width='90%'>
			            <tr>
			            	<td class='texto' colspan='2'><b>PRAZO DE ENTREGA</b></td>
			            </tr>
			            <tr>
			            	<td class='texto'>
								O prazo para o processamento do pedido e o envio do crédito virtual é de até 48 horas. 
								Vai depender da confirmação do pagamento pelo Banco e da qualidade e exatidão dos dados informados. 
								Se não for possível realizar a conciliação, confirmação do pagamento, 
								o pedido será cancelado e um novo pedido deverá ser feito.<br><br>
								A confirmação pelo Banco leva em média 2 dias úteis, a contar do pagamento, conforme o horário do depósito, 
								fins de semana, feriados na origem ou no destino e eventuais problemas de processamento.<br> 
			                </td>
			            </tr>
						</table>";
						
		$msgEmail .= email_rodape($parametros);
		enviaEmail($usuarioGames->getEmail(), null, null, "E-Prepag - Dados de Pagamento", $msgEmail);

	}

                                            
        //Fechando Conexão
        pg_close($connid);

	//redireciona
	redirect($strRedirect);

?>
