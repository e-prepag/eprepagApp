<?php 
$pagina_titulo = "Confirma edita pagamento"; 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."banco/bradesco/inc_urls_bradesco.php";
require_once $raiz_do_projeto."banco/bancodobrasil/inc_urls_bancodobrasil.php";
require_once $raiz_do_projeto."banco/itau/inc_config.php";
require_once $raiz_do_projeto."includes/constantesPagamento.php";
require_once $raiz_do_projeto."includes/inc_Pagamentos.php";
require_once $raiz_do_projeto."includes/functionsPagamento.php";
//die("Bloqueada, contate o administrador");
// Para Sonda Itaú -> bloquear função getSondaItau_InShopline($dados)

/*
echo "<pre>".print_r($_SESSION,true)."</pre>";
echo "HTTP_REFERER: '".$_SERVER['HTTP_REFERER']."'<br>";
echo "op: '$op', id: '$id'<br>";
*/

if($_SESSION['tipo_acesso']=="AT") {
	echo "OK<br>";
} else {
	die("Função Bloqueada, contate o administrador<br>");
}

//Validacoes
if(isset($_GET['id'])) {
        unset($_GET['id']);
        if(!isset($_POST['id'])) {
                unset($id);
        }
}
//Validacoes
if(isset($_GET['op'])) {
        unset($_GET['op']);
        if(!isset($_POST['op'])) {
                unset($op);
        }
}

//echo "<pre>".print_r($_SESSION,true)."</pre>";
//echo "HTTP_REFERER: '".$_SERVER['HTTP_REFERER']."'<br>";
//echo "op: '$op', id: '$id'<br>";
//	echo "_SERVER['HTTP_REFERER']: ".$_SERVER['HTTP_REFERER']."<br>"; 
	if(($op=="pag" || $op=="des") && ($id>0)) {
//		echo "Processa: op='".$op."', id='".$id."'<br>";

		$sql = "SELECT numcompra, idvenda, iforma, id_transacao_itau, status, tipo_cliente from tb_pag_compras WHERE idpagto=".$id.";";
//echo "<br>".$sql."<br>";

		$rs_transacoes = SQLexecuteQuery($sql);
		if(!$rs_transacoes || pg_num_rows($rs_transacoes) == 0) {
			$msg_retorno = "Nenhum pagamento encontrado.\n";
//echo "Msg: ".$msg_retorno."<br>";
		} else {
			$rs_transacoes_row = pg_fetch_array($rs_transacoes);
			if($rs_transacoes_row) {
				$numOrder = $rs_transacoes_row['numcompra'];
				$idvenda = $rs_transacoes_row['idvenda'];
				$tipo_cliente = $rs_transacoes_row['tipo_cliente'];
//echo "idvenda: ".$idvenda."<br>";
//echo "numOrder: ".$numOrder."<br>";
echo "tipo_cliente: ".$tipo_cliente."<br>";
echo "substr(tipo_cliente, 0, 1): '".substr($tipo_cliente, 0, 1)."'<br>";

				$prefix_1 = getDocPrefix($rs_transacoes_row['iforma']);

				$vg_pagto_num_docto	= $prefix_1.$rs_transacoes_row['iforma']."_".$rs_transacoes_row['numcompra']."";
//echo "$vg_pagto_num_docto: '".$vg_pagto_num_docto."'<br>";	

				$dataconfirma = "CURRENT_TIMESTAMP";
				$sonda = "????";
				// $valtotal = 0;

				unset($aline5);
				unset($aline6);
				unset($aline9);
				unset($alineC);
				//unset($alineA);
				unset($alinePIX);
				if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'])  {
					
					// obtem status, OK se status='081'
					$b_sonda_5 = getTransacaoPagamentoOK("Transf", $rs_transacoes_row['numcompra'], $aline5);

					// Se existe registro da transação -> salva data
                    if((count($aline5)>0)) {
						$s_sonda = (($b_sonda_5)?"OK":"none");
						$sBanco = "Bradesco";
                        $dataconfirma = "'".date('Y-m-d H:i:s')."'";
						//$valtotal = $aline5[2]/100;
					}
					$s_sync = (($b_sonda_5)?"NO SYNC":"");

				} else if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'])  {

					// obtem status, OK se status='003'
					$b_sonda_6 = getTransacaoPagamentoOK("PagtoFacil", $rs_transacoes_row['numcompra'], $aline6);

					// Se existe registro da transação -> salva data 	
					if(strlen($aline6[1])>0) {
						$s_sonda = (($b_sonda_6)?"OK":"none");
						$sBanco = "Bradesco";
						$dataconfirma = "'".substr($aline6[3],6,4)."-".substr($aline6[3],3,2)."-".substr($aline6[3],0,2)." ".$aline6[4]."'";
						//$valtotal = $aline6[2]/100;
					}
					$s_sync = (($b_sonda_6)?"NO SYNC":"");

				} else if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'])  {

					// obtem status, OK se status='003'

					$b_sonda_9 = getTransacaoPagamentoOK("BancodoBrasil", $rs_transacoes_row['numcompra'], $aline9);

//Dummy 
// 2011-08-21
//	Pedido: 9096939	(numcompra = '20110817194418646')
//	Pedido: 9987200	(numcompra = '20110817193944429')
//if ($rs_transacoes_row['numcompra'] == "20110817194418646" || $rs_transacoes_row['numcompra'] == "20110817193944429") {
//	$b_sonda_9 = true;
//}

// 2011-08-23
//	Nro do pedido: 5472778	(numcompra = '20110819205124942')
//	Nro do pedido: 9625735	(numcompra = '20110819194838530')
//if ($rs_transacoes_row['numcompra'] == "20110819205124942" || $rs_transacoes_row['numcompra'] == "20110819194838530") {
//	$b_sonda_9 = true;
//}

// 2011-08-21
//	Pedido: 2230473	(numcompra = '20110821114457983')
//if ($rs_transacoes_row['numcompra'] == "20110821114457983") {
//	$b_sonda_9 = true;
//}

// 2011-08-21
//	Pedido: 1553913	- (numcompra = '20110821140241608')
//if ($rs_transacoes_row['numcompra'] == "20110821140241608") {
//	$b_sonda_9 = true;
//}


/*
// $a_sondas_confirmadas = array('20110823201412605', '20110820225642182', '20110820222102917');
$a_sondas_confirmadas = array('20110808220653539');
if (in_array($rs_transacoes_row['numcompra'], $a_sondas_confirmadas)) {
	$b_sonda_9 = true;
}
*/
//if($rs_transacoes_row['numcompra']=='20090916115521867') {
//echo "\n ABCD: '".$rs_transacoes_row['numcompra']."' => ";
//echo "".($b_sonda_9?"SIM":"não").", situação: '".$aline9['Situação']."'\n";
//print_r($aline9);
//echo "\n";
//}
/*
$a_sondas_confirmadas = array('20111003115058707');
if (in_array($rs_transacoes_row['numcompra'], $a_sondas_confirmadas)) {
	$b_sonda_9 = true;
}
*/
/*
$a_sondas_confirmadas = array('20111004221513835');
if (in_array($rs_transacoes_row['numcompra'], $a_sondas_confirmadas)) {
	$b_sonda_9 = true;
}
*/
/*
$a_sondas_confirmadas = array('20111028221238953');
if (in_array($rs_transacoes_row['numcompra'], $a_sondas_confirmadas)) {
	$b_sonda_9 = true;
}
*/
					// Se existe registro da transação -> salva data 	
//							if(strlen($aline9['Situação'])>0) {
//								$s_sonda = (($b_sonda_9)?"OK":"none");
//								$sBanco = "Banco do Brasil";
//								$dataconfirma = "";	//"'".substr($aline9[3],6,4)."-".substr($aline9[3],3,2)."-".substr($aline9[3],0,2)." ".$aline9[4]."'";
//								//$valtotal = $aline9[2]/100;
//							}
					if($b_sonda_9) {
						$s_sonda = (($b_sonda_9)?"OK":"none");
						$sBanco = "Banco do Brasil";
						//     [dataPagamento] => 16092009
						$dataconfirma = "'".substr($aline9['dataPagamento'],4,4)."-".substr($aline9['dataPagamento'],2,2)."-".substr($aline9['dataPagamento'],0,2)."'";
					}
					$s_sync = (($b_sonda_9)?"NO SYNC":"");

				} else if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'])  {
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//							$b_sonda_A = getTransacaoPagamentoOK("BancoItau", $rs_transacoes_row['numcompra'], $alineA);
					$pedido =  str_pad($rs_transacoes_row['id_transacao_itau'], 8, "0", STR_PAD_LEFT);
//global $formato_itau;
//echo "Pedido BITA: ".$pedido." (formato_itau: ".$formato_itau.")\n";

/*				// Banco Itaú sem Sonda desde 2011-11-15 0:00:00
					$a_itau_completos = array(
								"20111116113102839", "20111116102646153", "20111115211113556", "20111115210501904", "20111115200733997", "20111115155052041", "20111115153948671", "20111115152104026", "20111115145015517", "20111115140431104", "20111115133050958", "20111115130304228", "20111115075818203", "20111116105059591", "20111116104036996", "20111116102056244", "20111116101905775", "20111116092451463", "20111116092351853", "20111116081910948", "20111116020232662", "20111116001720568", "20111115232327873", "20111115230139468", "20111115223158554", "20111115211155007", "20111115203203037", "20111115191915726", "20111115185828814", "20111115181428008", "20111115174411096", "20111115173713728", "20111115172758612", "20111115171025364", "20111115170823577", "20111115165513211", "20111115164648308", "20111115164305010", "20111115161239194", "20111115161233553", "20111115160319407", "20111115160044023", "20111115152944400", "20111115151004990", "20111115150355722", "20111115142201039", "20111115141819117", "20111115141722339", "20111115141423123", "20111115135842402", "20111115133530972", "20111115133021632", "20111115124341756", "20111115124151922", "20111115123835657", "20111115122630467", "20111115113906713", "20111115113700403", "20111115113309839", "20111115111101174", "20111115092355389", "20111115084725729", "20111115084431608", "20111115005131576"
								);
*/
/*
				// Banco Itaú LHs Pre sem Sonda desde 2011-11-15 0:00:00
					$a_itau_completos = array(
								"20111115211113556", "20111115145015517", "20111116113102839", "20111115133050958", "20111115130304228", "20111115153948671", "20111115075818203", "20111115210501904","20111115140431104", "20111115200733997", "20111115155052041", "20111115152104026"
								);
*/
				// Banco Itaú Gamer sem Sonda desde 2011-11-17 0:00:00
					$a_itau_completos = array(
								"20111116191630141"
								);
					if(in_array("".$rs_transacoes_row['numcompra']."", $a_itau_completos)) {
						$pag_status = "00";
					} else {
						$pag_status = getSondaItau($pedido, $a_retorno_itau, $sitPag, $dtPag);
						$b_sonda_A = false;
					}

//echo "Status BITA: ".$pag_status." ('$sitPag', '$dtPag')<br>\n";
//echo "<pre>".print_r($a_retorno_itau, true)."</pre>";
// Dummy 
//$pag_status = "01"; 
					$b_sonda_A = (($pag_status=="00")?true:false);

echo "b_sonda_A: ".($b_sonda_A?"b_sonda_A_OK":"b_sonda_A_none")."<br>\n";

					if($b_sonda_A) {
						$s_sonda = (($b_sonda_A)?"OK":"none");
						$sBanco = "Banco Itaú";
						//     [dtPag] => 16092009
						$dataconfirma = "'".substr($dtPag,4,4) . "-" . substr($dtPag,2,2) . "-" . substr($dtPag,0,2) . "'";
					}

					$s_sync = (($b_sonda_A)?"NO SYNC":"");
				} else if($rs_transacoes_row['iforma']==$PAGAMENTO_HIPAY_ONLINE)  {
// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

					$pag_status = "";	//getSondaHipay($pedido, &$a_retorno_itau, $sitPag, $dtPag);
					$b_sonda_B = ($rs_transacoes_row['status']==3)?true:false;


					if($b_sonda_B) {
						$s_sonda = (($b_sonda_B)?"OK":"none");
						$sBanco = "Banco HiPay";
						$dataconfirma = "'".date("Y-m-d")."'";
					}
					$s_sync = (($b_sonda_B)?"NO SYNC":"");

				} else if($rs_transacoes_row['iforma']==$PAGAMENTO_PAYPAL_ONLINE)  {
// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

					$pag_status = "";	//getSondaPayPal($pedido, &$a_retorno_itau, $sitPag, $dtPag);
					$b_sonda_P = ($rs_transacoes_row['status']==3)?true:false;


					if($b_sonda_P) {
						$s_sonda = (($b_sonda_P)?"OK":"none");
						$sBanco = "Banco PayPal";
						$dataconfirma = "'".date("Y-m-d")."'";
					}
					$s_sync = (($b_sonda_P)?"NO SYNC":"");

				} else if($rs_transacoes_row['iforma']==$PAGAMENTO_BANCO_EPP_ONLINE)  {
// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

					$pag_status = "";	//getSondaItau($pedido, &$a_retorno_itau, $sitPag, $dtPag);
					$b_sonda_Z = ($rs_transacoes_row['status']==3)?true:false;


					if($b_sonda_Z) {
						$s_sonda = (($b_sonda_Z)?"OK":"none");
						$sBanco = "Banco E-Prepag";
						$dataconfirma = "'".date("Y-m-d")."'";
					}
					$s_sync = (($b_sonda_Z)?"NO SYNC":"");
			
				} else if(b_IsPagtoCielo($rs_transacoes_row['iforma'])) {
                                        $b_sonda_C = getTransacaoPagamentoOK("Cielo", $rs_transacoes_row['numcompra'], $alineC);
                                        if($b_sonda_C) {
						$s_sonda = (($b_sonda_C)?"OK":"none");
						$sBanco = "Banco Cielo";
						$dataconfirma = date("Y-m-D H:i:s");
					}
					$s_sync = (($b_sonda_C)?"NO SYNC":"");
				}
                                else if($rs_transacoes_row['iforma']==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX'])  {
                                    $b_sonda_PIX = getTransacaoPagamentoOK($GLOBALS['PAGAMENTO_PIX_NOME_BANCO'],  $rs_transacoes_row['numcompra'], $alinePIX);
                                    $s_sonda = (($b_sonda_PIX)?"OK":"none");
                                    if($b_sonda_PIX) {
                                            $sBanco = $GLOBALS['PAGAMENTO_PIX_NOME_BANCO'];
                                            //pegar a data do JSON
                                            $dataconfirma = "'".substr(str_replace('T', ' ', $alinePIX->pix[0]->horario),0,19)."'";
                                    }
                                    $s_sync = (($b_sonda_PIX)?"NO SYNC":"");
                                }


echo "s_sync: '$s_sync'<br>";
echo "s_sonda: '$s_sonda'<br>";
echo "dataconfirma: '$dataconfirma'<br>";
echo "sBanco: '$sBanco'<br>";

//die("Bloqueada");


				/////   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
				// Descancela a venda e completa o pagamento
				if($s_sync=="NO SYNC") {
					$dataconfirma = str_replace("/","-",$dataconfirma);
					if($dataconfirma="") $dataconfirma = "(CURRENT_TIMESTAMP)";	// ajuste de horario de verão - " + interval '1 hour'"

					$msg_retorno = "Pagamento Atualizado com sucesso (numcompra='".$numOrder."', idvenda=".$idvenda.", vg_pagto_num_docto='".$vg_pagto_num_docto."').\n";

	//echo "<br>msg_retorno: ".$msg_retorno."<br>";
		
					// Atualiza dados de pagamento em DB
					$sql = "UPDATE tb_pag_compras SET status=3, status_processed=0, datacompra= (CURRENT_TIMESTAMP) ";	// ajuste de horario de verão - " + interval '1 hour'"
					if($dataconfirma)
						$sql .= ", dataconfirma=".$dataconfirma." ";
					$sql .= " WHERE numcompra='".$numOrder."';";
	//echo "<br>".$sql."<br>";

	//echo "OK 0<br>";
					$ret = SQLexecuteQuery($sql);
					if(!$ret) {
						echo "Erro ao atualizar transação de pagamento (4).\n".$sql."\n";
	//echo "OK 1<br>";
	//					gravaLog_TMP("Erro ao confirmar transação de pagamento (4) MANUALMENTE (".date("Y-m-d H:i:s").")\n".$sql."\n");
	//					die("Stop");
					} else {
	//					gravaLog_TMP("Transação de pagamento confirmada(5) MANUALMENTE (".date("Y-m-d H:i:s").")\n  ".$sql."\n");
					}
	//echo "OK 2<br>";

					if(substr($tipo_cliente,0,1)=="L") {

						// Muda status de pedido correspondente
						$sql = "UPDATE tb_dist_venda_games SET vg_ultimo_status='".$STATUS_VENDA['PAGTO_CONFIRMADO']."', vg_pagto_num_docto='".$vg_pagto_num_docto."' WHERE vg_id=".$idvenda.";";	//	", vg_pagto_valor_pago=".$valtotal.""	//vg_pagto_banco='237', 
//echo "<br>".$sql."<br>";
						$msg_retorno .= "<br>\n".$sql."<br>\n";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) {
							echo "Erro ao atualizar status de pedido (5LH).\n";
		//					gravaLog_TMP("Erro ao atualizar status de pedido (5) MANUALMENTE (".date("Y-m-d H:i:s").")\n".$sql."\n");
		//					die("Stop");
						} else {
		//					gravaLog_TMP("Venda status atualizado para pagto. confirmado: MANUALMENTE ".$STATUS_VENDA['PAGTO_CONFIRMADO']." (5a) (".date("Y-m-d H:i:s").")\n   ".$sql."\n");
						}

					} elseif(substr($tipo_cliente,0,1)=="M") {
						// Muda status de pedido correspondente
						$sql = "UPDATE tb_venda_games SET vg_ultimo_status='".$STATUS_VENDA['PAGTO_CONFIRMADO']."', vg_pagto_num_docto='".$vg_pagto_num_docto."' WHERE vg_id=".$idvenda.";";	//	", vg_pagto_valor_pago=".$valtotal.""	//vg_pagto_banco='237', 
		//echo "<br>".$sql."<br>";
						$msg_retorno .= "<br>\n".$sql."<br>\n";

						$ret = SQLexecuteQuery($sql);
						if(!$ret) {
							echo "Erro ao atualizar status de pedido (5Gamer).\n";
		//					gravaLog_TMP("Erro ao atualizar status de pedido (5) MANUALMENTE (".date("Y-m-d H:i:s").")\n".$sql."\n");
		//					die("Stop");
						} else {
		//					gravaLog_TMP("Venda status atualizado para pagto. confirmado: MANUALMENTE ".$STATUS_VENDA['PAGTO_CONFIRMADO']." (5a) (".date("Y-m-d H:i:s").")\n   ".$sql."\n");
						}
					} else {
						echo "<font color='red'>Erro: tipo_cliente desconhecido ($tipo_cliente)</font><br>";
					}
				} else {
					echo "<font color='red'>NÃO PROCESSA: Sonda ao banco não confirmou pagamento completo. Nada foi modificado (op='".$op."', id='".$id."')</font>";
				}
			} else {
//echo "Msg:  sem fetch_row<br>";
			}
		}

	} else {
		echo "<font color='red'>NÃO PROCESSA: op='".$op."', id='".$id."'</font><br>";
	}

?>
<script language="JavaScript">
<!--
	function voltar() {
		document.form1.submit();
	}
-->
</script>
<p>Processamento completo</p>
<?php
/*

id
varsel
tf_v_canal
tf_v_data_inclusao_ini
tf_v_data_inclusao_fim
tf_v_tipo_transacao
tf_v_forma_pagamento
tf_opr_codigo
tf_v_codigo
tf_d_valor_pago
*/
	$tf_v_data_inclusao_ini = $_POST['tf_v_data_inclusao_ini'];
	$tf_v_data_inclusao_fim = $_POST['tf_v_data_inclusao_fim'];
	$tf_v_tipo_transacao = $_POST['tf_v_tipo_transacao'];
	$varsel = $_POST['varsel'];
	$id = $_POST['id'];
	$tf_v_canal = $_POST['tf_v_canal'];

	$tf_v_forma_pagamento = $_POST['tf_v_forma_pagamento'];
	$tf_opr_codigo = $_POST['tf_opr_codigo'];
	$tf_v_codigo = $_POST['tf_v_codigo'];
	$tf_d_valor_pago = $_POST['tf_d_valor_pago'];


?>
  <form method=post name="form1" action="lista_pagamentos.php">
	<input type="hidden" name="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini; ?>">
	<input type="hidden" name="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim; ?>">
	<input type="hidden" name="tf_v_tipo_transacao" value="<?php echo $tf_v_tipo_transacao; ?>">

	<input type="hidden" name="id" value="<?php echo $id; ?>">
	<input type="hidden" name="varsel" value="<?php echo $varsel; ?>">

	<input type="hidden" name="tf_v_forma_pagamento" value="<?php echo $tf_v_forma_pagamento; ?>">
	<input type="hidden" name="tf_opr_codigo" value="<?php echo $tf_opr_codigo; ?>">
	<input type="hidden" name="tf_v_codigo" value="<?php echo $tf_v_codigo; ?>">
	<input type="hidden" name="tf_d_valor_pago" value="<?php echo $tf_d_valor_pago; ?>">

	<input type="hidden" name="msg_retorno" value="<?php echo $msg_retorno; ?>">

	<input type="button" name="btOK" value="Voltar" OnClick="voltar();" class="botao_simples">
  </form>

