<?php
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
if ($_SESSION["tipo_acesso_pub"] == 'PU') {
	//redireciona
	$strRedirect = "/sys/admin/commerce/index.php";
	ob_end_clean();
	header("Location: " . $strRedirect);
	exit;
	?>
	<html>

	<body onLoad="window.location='<?php echo $strRedirect ?>'">
		<?php
		exit;

		ob_end_flush();
}
$time_start = getmicrotime();

header("Content-Type: text/html;charset=ISO-8859-1");

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
set_time_limit(30000);
?>
	<html>

	<head>
		<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
		<title>E-Prepag Arquivo RPS NFSe</title>
		<script language='javascript' src='../stats/js/jquery-1.4.4.js'></script>
		<script language="JavaScript" type="text/JavaScript">
<!--
function gerarArquivo(varArquivo) {
	window.location.href = 'financial_nfse_download.php?varArquivo='+varArquivo;
}
//-->
</script>
	</head>

	<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
		<table width="903" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td height="22,5" valign="center" bgcolor="#00008C" width="903">
					<p>
						<font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><b>Gera&ccedil;&atilde;o de
								Arquivo RPS - Período considerado [<?php echo $nfes_periodo; ?>]<br></b></font>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					&nbsp;
				</td>
			</tr>
			<?php
			include "../vendas/vendas_estab/nfesp_lote.php";
			//echo count($valorNota)."<br>";
//echo "<pre>".print_r($valorNota,true)."</pre>";
//echo "<pre>".print_r($_SERVER,true)."</pre>";
			$data = date("Ymd");

			//Alicota de IRRF para fins de cálculos
			$alicotaIRRF = 1.5;

			//Limite para discriminação do IRRF da Nota Fiscal
			$limiteInformeIRRF = 10;

			//RPS EPP Pagto.
			$sNFe = "";
			$sNFe .= gera_cabecalho('EPP', $data, $data);
			$total_geral = 0;
			$cont_nota = 0;

			//RPS EPP ADM
			$sNFeADM = "";
			$sNFeADM .= gera_cabecalho_administradora($data);
			$total_geral_adm = 0;
			$cont_nota_adm = 0;

			foreach ($valorNota as $line => $valor) {
				$valorAux = $valor * 1;
				if (!empty($valorAux)) {

					// Verificando se o Publisher é Nacional ou Internacional
					$sql = "select opr_internacional_alicota from operadoras where opr_codigo = " . $line . "; ";
					//echo $sql."<br>";
					$resAlicota = SQLexecuteQuery($sql);
					$insideIRRF = 0;
					if ($resAlicotaRow = pg_fetch_array($resAlicota)) {
						$insideIRRF = $resAlicotaRow['opr_internacional_alicota'];
					}//end if ($resAlicotaRow = pg_fetch_array($resAlicota))
					//echo $line." => ".$insideIRRF." Valor => ".$valor."<BR>";
			
					//Setando variaveis para captura no mês referência
					setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
					date_default_timezone_set('America/Fortaleza');
					$mesFechamento = mktime(0, 0, 0, date("n") - 1, 1, date("Y"));

					// Para EPP PAGAMENTOS
					if (empty($vinculoEmpresa[$line])) {

						$sql = "select * from nfse_epp where opr_codigo=$line and nfes_periodo='$nfes_periodo'";
						//echo $sql."<br>";
						$rs_periodo = SQLexecuteQuery($sql);
						if (pg_num_rows($rs_periodo) > 0) {
							echo "<tr><td><font color='#000000' class='texto' align='center'>Per&iacute;odo j&aacute; processado para o Publisher ($line)</font></td></tr>";
						} else {
							//$varTipoRPS = "02";
							$varTipoRPS = "RPS";
							$varSerieRPS = "EPP";

							$sql = "select MAX(nfes_seq) as nfes_seq from nfse_epp;";
							//echo $sql."<br>";
							$resRPS = SQLexecuteQuery($sql);
							if ($resRPSrow = pg_fetch_array($resRPS)) {
								if (empty($resRPSrow['nfes_seq'])) {
									$varNumeroRPSaux = 1;
								} else {
									$varNumeroRPSaux = $resRPSrow['nfes_seq'] + 1;
								}
							} else {
								$varNumeroRPSaux = 1;
							}
							if ($cont_nota == 0) {
								$varLoteAux = $varNumeroRPSaux;
							}
							$varNumeroRPS = str_pad($varNumeroRPSaux, 12, "0", STR_PAD_LEFT);
							$varNumLote = ""; //str_pad($loteid, 4, "0", STR_PAD_LEFT);
							$varDataEmissaoRPS = $data;
							$valor_reg_nfe = $valor;
							//$varSituacaoRPS = "N"; //Normal
							$varSituacaoRPS = "T"; //Operação Normal
							$varValorRPS = str_pad($valor_reg_nfe, 15, "0", STR_PAD_LEFT);
							$varDeducaoRPS = str_pad($deducoes, 15, "0", STR_PAD_LEFT);
							$varCodigoServicoRPS = "05895"; //Código do Serviço Prestado antes 02798 , depois foi 06173
							$varAliquotaRPS = "0200";	// 2% antes - agora é 5% passou para 2% novamente em 24/01/2018
							$varISSRetido = "2";

							$sql = "select * from operadoras where opr_codigo=" . $line;
							$resOPR = SQLexecuteQuery($sql);
							$resOPRrow = pg_fetch_array($resOPR);

							if (empty($resOPRrow['opr_cnpj'])) {
								$varIndicadorCPF = "3";
							} elseif (strlen($resOPRrow['opr_cnpj']) > 11) {
								$varIndicadorCPF = "2";
							} else {
								$varIndicadorCPF = "1";
							}

							$varCPF = $resOPRrow['opr_cnpj'];
							$varIM = str_pad($resOPRrow['opr_im'], 8, "0", STR_PAD_LEFT);
							$varIE = str_pad("", 12, "0", STR_PAD_LEFT);

							$varNome = str_pad(substr($resOPRrow['opr_razao'], 0, 75), 75, " ", STR_PAD_RIGHT);

							$varTipoEndereco = str_pad(substr($resOPRrow['opr_endereco'], 0, 3), 3, " ", STR_PAD_RIGHT);
							$varEndereco = str_pad(substr($resOPRrow['opr_endereco'], 3, 50), 50, " ", STR_PAD_RIGHT);
							$varNumero = str_pad($resOPRrow['opr_numero'], 10, " ", STR_PAD_RIGHT);
							$varComplemento = str_pad($resOPRrow['opr_complemento'], 30, " ", STR_PAD_RIGHT);
							$varBairro = str_pad($resOPRrow['opr_bairro'], 30, " ", STR_PAD_RIGHT);

							$varCidade = str_pad($resOPRrow['opr_cidade'], 50, " ", STR_PAD_RIGHT);
							$varUF = str_pad($resOPRrow['opr_estado'], 2, " ", STR_PAD_RIGHT);
							$varCEP = str_pad(str_replace(" ", "", str_replace("-", "", str_replace(".", "", $resOPRrow['opr_cep']))), 8, " ", STR_PAD_RIGHT);

							$varEmail = str_pad($resOPRrow['opr_email'], 75, " ", STR_PAD_RIGHT);
							//$varEmail		= str_pad("wagner@teste.com", 75, " ", STR_PAD_RIGHT);
							$varDiscriminacao = "Intermediação na distribuição de créditos para uso de games, conforme contrato assinado entre as partes.|Ref. " . ucfirst(strftime("%B", $mesFechamento)) . "/" . date("Y", $mesFechamento) . ".";
							if ($insideIRRF == 0) {
								$totalIRRFaux = $valor / 100 * $alicotaIRRF / 100;
								if ($totalIRRFaux >= $limiteInformeIRRF) {
									$varDiscriminacao .= "|" . "|" . "Valor Total NF: R$ " . number_format(($valor / 100), 2, ",", ".") . "|" . "IRRF (" . $alicotaIRRF . "%)...: R$ " . number_format($totalIRRFaux, 2, ",", ".") . "|" . "Valor Líquido.: R$ " . number_format((($valor / 100) - $totalIRRFaux), 2, ",", ".");
								}//end if($totalIRRFaux>=10) 
							}//end if($insideIRRF > 0)
			
							$varTributacaoServico = "T";
							$varCodigoSubitemLista = "";
							$varRegimeTributacao = "0";
							$varDataPagamentoServico = "";
							$varTipoNFTS = "1";

							$sNFe .= gera_lote($varTipoRPS, $varSerieRPS, $varNumeroRPS, $varNumLote, $varDataEmissaoRPS, $varSituacaoRPS, $varValorRPS, $varDeducaoRPS, $varCodigoServicoRPS, $varAliquotaRPS, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao, "", $varTributacaoServico, $varCodigoSubitemLista, $varRegimeTributacao, $varDataPagamentoServico, $varTipoNFTS) .
								""; //"NFS"
							$sql = "INSERT INTO nfse_epp (
										nfes_seq, 
										nfes_data, 
										opr_codigo, 
										nfes_valor,
										nfes_periodo
										) 
								VALUES (
										$varNumeroRPSaux,
										NOW(),
										$line, 
										$valor,
										'$nfes_periodo');";
							//echo $sql."<br>";
							$rs_operadoras = SQLexecuteQuery($sql);
							if (!$rs_operadoras) {
								$msg .= "Erro ao salvar informa&ccedil;&otilde;es da RPS. ($sql)<br>";
							}
							$total_geral += $valor;
							$cont_nota++;
						}//end não encontrou registro do publisher para este periodo
					}//end if(empty($vinculoEmpresa[$line]))
			
					// Para EPP ADMINISTRADORA
					else {
						$sql = "select * from nfse_epp_adm where opr_codigo=$line and nfes_periodo='$nfes_periodo'";
						//echo $sql."<br>";
						$rs_periodo = SQLexecuteQuery($sql);
						if (pg_num_rows($rs_periodo) > 0) {
							echo "<tr><td><font color='#000000' class='texto' align='center'>Per&iacute;odo j&aacute; processado para o Publisher ($line)</font></td></tr>";
						} else {
							$varTipoRPS = "RPS";
							$varSerieRPS = "EPP";

							$sql = "
                                select MAX(nfes_seq) as nfes_seq
                                FROM (
                                        select MAX(nfes_seq) as nfes_seq from nfse_epp_adm

                                        UNION ALL

                                        select MAX(nfes_seq) as nfes_seq from tb_pag_taxa_anual
                                ) as t;";
							//echo $sql."<br>";
							$resRPS = SQLexecuteQuery($sql);
							if ($resRPSrow = pg_fetch_array($resRPS)) {
								if (empty($resRPSrow['nfes_seq'])) {
									$varNumeroRPSaux = 1;
								} else {
									$varNumeroRPSaux = $resRPSrow['nfes_seq'] + 1;
								}
							} else {
								$varNumeroRPSaux = 1;
							}
							if ($cont_nota_adm == 0) {
								$varLoteAuxADM = $varNumeroRPSaux;
							}
							$varNumeroRPS = str_pad($varNumeroRPSaux, 12, "0", STR_PAD_LEFT);
							$varNumLote = ""; //str_pad($loteid, 4, "0", STR_PAD_LEFT);
							$varDataEmissaoRPS = $data;
							$valor_reg_nfe = $valor;
							//$varSituacaoRPS = "N"; //Normal
							$varSituacaoRPS = "T"; //Operação Normal
							$varValorRPS = str_pad($valor_reg_nfe, 15, "0", STR_PAD_LEFT);
							$varDeducaoRPS = str_pad($deducoes, 15, "0", STR_PAD_LEFT);
							$varCodigoServicoRPS = "05820";
							$varAliquotaRPS = "0200";	// 2% antes - agora é 5%
							$varISSRetido = "2";

							$sql = "select * from operadoras where opr_codigo=" . $line;
							$resOPR = SQLexecuteQuery($sql);
							$resOPRrow = pg_fetch_array($resOPR);

							if (empty($resOPRrow['opr_cnpj'])) {
								$varIndicadorCPF = "3";
							} elseif (strlen($resOPRrow['opr_cnpj']) > 11) {
								$varIndicadorCPF = "2";
							} else {
								$varIndicadorCPF = "1";
							}

							$varCPF = $resOPRrow['opr_cnpj'];
							$varIM = str_pad($resOPRrow['opr_im'], 8, "0", STR_PAD_LEFT);
							$varIE = str_pad("", 12, "0", STR_PAD_LEFT);

							$varNome = str_pad(substr($resOPRrow['opr_razao'], 0, 75), 75, " ", STR_PAD_RIGHT);

							$varTipoEndereco = str_pad(substr($resOPRrow['opr_endereco'], 0, 3), 3, " ", STR_PAD_RIGHT);
							$varEndereco = str_pad(substr($resOPRrow['opr_endereco'], 3, 50), 50, " ", STR_PAD_RIGHT);
							$varNumero = str_pad($resOPRrow['opr_numero'], 10, " ", STR_PAD_RIGHT);
							$varComplemento = str_pad($resOPRrow['opr_complemento'], 30, " ", STR_PAD_RIGHT);
							$varBairro = str_pad($resOPRrow['opr_bairro'], 30, " ", STR_PAD_RIGHT);

							$varCidade = str_pad($resOPRrow['opr_cidade'], 50, " ", STR_PAD_RIGHT);
							$varUF = str_pad($resOPRrow['opr_estado'], 2, " ", STR_PAD_RIGHT);
							$varCEP = str_pad(str_replace(" ", "", str_replace("-", "", str_replace(".", "", $resOPRrow['opr_cep']))), 8, " ", STR_PAD_RIGHT);

							$varEmail = str_pad($resOPRrow['opr_email'], 75, " ", STR_PAD_RIGHT);
							$varDiscriminacao = "Ref. " . ucfirst(strftime("%B", $mesFechamento)) . "/" . date("Y", $mesFechamento) . ".";
							if ($insideIRRF == 0) {
								$totalIRRFaux = $valor / 100 * $alicotaIRRF / 100;
								if ($totalIRRFaux >= $limiteInformeIRRF) {
									$varDiscriminacao .= "|" . "|" . "Valor Total NF: R$ " . number_format(($valor / 100), 2, ",", ".") . "|" . "IRRF (" . $alicotaIRRF . "%)...: R$ " . number_format($totalIRRFaux, 2, ",", ".") . "|" . "Valor Líquido.: R$ " . number_format((($valor / 100) - $totalIRRFaux), 2, ",", ".");
									$varDiscriminacao .= "|" . "|" . "Serviço de administração de cartão de crédito sujeito ao auto recolhimento, não cabe retenção pelo tomador conforme IN 153/87, o responsável pelo recolhimento é a empresa recebedora dos valores (prestador de serviços).";
								}//end if($totalIRRFaux>=10) 
							}//end if($insideIRRF > 0)
			
							$varTributacaoServico = "T";
							$varCodigoSubitemLista = "";
							$varRegimeTributacao = "0";
							$varDataPagamentoServico = "";
							$varTipoNFTS = "1";

							$sNFeADM .= gera_lote($varTipoRPS, $varSerieRPS, $varNumeroRPS, $varNumLote, $varDataEmissaoRPS, $varSituacaoRPS, $varValorRPS, $varDeducaoRPS, $varCodigoServicoRPS, $varAliquotaRPS, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao, "", $varTributacaoServico, $varCodigoSubitemLista, $varRegimeTributacao, $varDataPagamentoServico, $varTipoNFTS) .
								""; //"NFS"
							$sql = "INSERT INTO nfse_epp_adm (
                                                                                nfes_seq, 
                                                                                nfes_data, 
                                                                                opr_codigo, 
                                                                                nfes_valor,
                                                                                nfes_periodo
                                                                                ) 
                                                                VALUES (
                                                                                $varNumeroRPSaux,
                                                                                NOW(),
                                                                                $line, 
                                                                                $valor,
                                                                                '$nfes_periodo');";
							//echo $sql."<br>";
							$rs_operadoras = SQLexecuteQuery($sql);
							if (!$rs_operadoras) {
								$msg .= "Erro ao salvar informa&ccedil;&otilde;es da RPS. ($sql)<br>";
							}
							$total_geral_adm += $valor;
							$cont_nota_adm++;
						}//end não encontrou registro do publisher para este periodo
					}//end else do if(empty($vinculoEmpresa[$line]))            
				}//end se o valor apurado é diferente de ZERO
			}//end foreach
			$sNFe .= gera_rodape($cont_nota, ($total_geral / 100));
			$sNFeADM .= gera_rodape($cont_nota_adm, ($total_geral_adm / 100));

			$varArquivo = "lotes/" . "nfse_lote_" . date("Ymd") . "_" . str_pad($varLoteAux, 4, "0", STR_PAD_LEFT) . ".txt";
			$handle = fopen($varArquivo, "w+");
			if (fwrite($handle, $sNFe) === FALSE) {
				$msg = "Não foi possível gravar em '$varArquivo' (2).";
				echo "<tr><td><font color='#000000' class='texto' align='center'>" . $msg . "</font></td></tr>";
				//die("Stop");
			} else {
				echo "<tr><td bgcolor='#CCCCCC' id='area' class='texto' align='center'><div id='download' onClick='gerarArquivo(\"" . $varArquivo . "\");' onMouseOver='this.style.backgroundColor=\"#CCFF99\"' onMouseOut='this.style.backgroundColor=\"#CCCCCC\"'><strong>EPP - Pagamentos => Arquivo de lote Nº " . str_pad($varLoteAux, 4, "0", STR_PAD_LEFT) . " gravado com sucesso.</strong></td></tr>";
			}
			fclose($handle);
			if ($cont_nota > 0) {
				// pass
			} else {
				echo "<tr><td><font color='#000000' class='texto' align='center'>ATENÇÃO: EPP - Pagamentos => ESSE ARQUIVO FOI GERADO NOVAMENTE, VERIFIQUE SE A NOTA JÁ FOI ENVIADA. </font></td></tr>";
			}
			echo "<tr><td>&nbsp;</td></tr>";

			$varArquivo = "lotes/" . "nfse_lote_" . date("Ymd") . "_" . str_pad($varLoteAuxADM, 4, "0", STR_PAD_LEFT) . "_ADMINISTRADORA.txt";
			$handle = fopen($varArquivo, "w+");
			if (fwrite($handle, $sNFeADM) === FALSE) {
				$msg = "Não foi possível gravar em '$varArquivo' (2).";
				echo "<tr><td><font color='#000000' class='texto' align='center'>" . $msg . "</font></td></tr>";
				//die("Stop");
			} else {
				echo "<tr><td bgcolor='#CCCCCC' id='area' class='texto' align='center'><div id='download' onClick='gerarArquivo(\"" . $varArquivo . "\");' onMouseOver='this.style.backgroundColor=\"#CCFF99\"' onMouseOut='this.style.backgroundColor=\"#CCCCCC\"'><strong>EPP - Administradora => Arquivo de lote Nº " . str_pad($varLoteAuxADM, 4, "0", STR_PAD_LEFT) . " gravado com sucesso.</strong></td></tr>";
			}
			fclose($handle);
			if ($cont_nota_adm > 0) {
				// pass
			} else {
				echo "<tr><td><font color='#000000' class='texto' align='center'>ATENÇÃO: EPP - Administradora => ESSE ARQUIVO FOI GERADO NOVAMENTE, VERIFIQUE SE A NOTA JÁ FOI ENVIADA. </font></td></tr>";
			}
			?>
			<tr>
				<td>
					&nbsp;
				</td>
			</tr>
			<tr>
				<td>
					<?php
					$time_end_ = getmicrotime();
					echo "<font color='#FF0000' size='1' face='Arial, Helvetica, sans-serif'>(Tempo de Execução : " . number_format((getmicrotime() - $time_start), 2, '.', '.') . "s)</font><br>";
					require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
					?>
					<div align="center"></div>
				</td>
			</tr>
		</table>
	</body>

	</html>