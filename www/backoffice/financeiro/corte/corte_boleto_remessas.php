<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/pdv/corte_classPrincipal.php"; 

set_time_limit(3000); // 5min
$msgFatal = "";
$msgNaoFatal = "";
//Constantes
//----------------------------------------------------------------------------------------
$folder = $raiz_do_projeto . "arquivos_gerados/corte/";

//Log
$logFile = 'corte_boleto_remessas.log';
$logDelimitador = '#--------------------------------------------------------------';

if(isset($Registrar) && $Registrar) {

        //Validacao
        $msg = "";

        //arquivo
        if($msg == ""){
                $fileSource = $_FILES['arquivo']['tmp_name']; 
                $fileTemp = $folder . $_FILES['arquivo']['name']; 

                if (($fileSource == 'none') || ($fileSource == '' )) $msg = 'Nenhum arquivo fornecido.\n';
                elseif (!move_uploaded_file($fileSource, $fileTemp)) $msg = 'Não foi possivel copiar para o diretório destino.\n'; 
                elseif((!file_exists($fileTemp)) || (filesize($fileTemp)) == 0) $msg = 'Arquivo vazio ou inválido.\n';
        }

        if($msg == ""){

                //Abre arquivo e le conteudo
                $handle = fopen($fileTemp, "r");
                $arquivoRetorno = fread($handle, filesize($fileTemp));
                fclose($handle);

                //le header
                $arquivoRetornoAr = preg_split("/\n/", $arquivoRetorno);
                if(!$arquivoRetornoAr || count($arquivoRetornoAr) == 0) $msg = "Arquivo sem conteúdo.\n";
                else $header = $arquivoRetornoAr[0];
        }

        if($msg == ""){
                if(strpos($header, "237BRADESCO") > 0) $msg = processaBoleto_Banco("237", $arquivoRetornoAr, $fileTemp);
                elseif(strpos($header, "341BANCO ITAU S.A.") > 0) $msg = processaBoleto_Banco("341", $arquivoRetornoAr, $fileTemp); 
                elseif((str_replace("033","",$header) != $header && strpos($header, "SANTANDER") > 0) && (strlen($header)==241)) $msg = processaBoleto_Banco("033", $arquivoRetornoAr, $fileTemp);
                else $msg = "No foi identificado no arquivo a que banco pertence este boleto.\n";
        }

        if($msg == "") $msg = "Boletos inseridos com sucesso.";
        $msg .= gravaLogBoleto($_FILES['arquivo']['name'], $msg);

} // end if($Registrar)
?>
<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function fcnOnSubmit(){

	if(form1.arquivo.value==''){
		alert('Arquivo não especificado');
		return false;
	}
	return confirm('Deseja registrar estes Boletos ?');
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
        <li><a href="index.php">Corte Semanal</a></li>
        <li class="active">Boleto - Remessa e Retorno</li>
    </ol> 
</div>

<form name="form1" method="post" ENCTYPE="multipart/form-data" onSubmit="return fcnOnSubmit();">
    <table class="table">
	<tr>
		<td align="right">Arquivo Retorno:&nbsp;</td>
		<td>
		  <input type="file" name="arquivo" size="30">
		</td>
		<td align="right">
			<input type="submit" name="Registrar" value="Registrar" class="botao_search">
		</td>
	</tr>
	<?php  if(isset($msg) && $msg != ""){ ?>
		<tr bgcolor="#FFFFFF"><td colspan="3" align="center"><br><?php  echo str_replace("\n", "<br>", $msg) ?></td></tr>
	<?php  } ?>
</table>
</form>
<hr>
<p>Arquivos (10 últimos)</p>
<?php
	$Ag_EPP_Bradesco = "02062";
	$CC_EPP_Bradesco = "0020459";
    $CC_EPP_ADM_Bradesco = "0001689";

	$Ag_EPP_Itau = "0444";
	$CC_EPP_Itau = "77567";

	$CC_EPP_Itau_Novo = "89756";

?>
<div class="row fontsize-p txt-preto">
    <div class="row">
        <div class="col-md-4">
            <table class="table" border="0" cellpadding="0" cellspacing="0" align="center">
              <tr bgcolor="#F5F5FB"> 
                <td class="texto" align="center">Remessa Bradesco</td>
              </tr>
<?php  
            $arquivoAr = buscaArquivos($folder . "remessaBradesco/", 'data', 'desc', '');

            if(is_array($arquivoAr)) {
                array_splice($arquivoAr, 10); 
                foreach($arquivoAr as $key => $filename) {
?>
                <tr>
                    <td class="p0">
                        <a style="text-decoration:none" target="_blank" href="corte_boleto_remessas_down.php?arquivo=<?php  echo urlencode($filename) ?>&tipo=1">
                            <?php  echo $filename ?>
                        </a></nobr>
                    </td>
                </tr>
<?php 
                }
            }
?>
            </table>
        </div>
        
        <div class="col-md-4">

            <table class="table" align="center">
              <tr bgcolor="#F5F5FB"> 
                <td class="texto" align="center">Retorno Bradesco Pag (<?php echo "Ag: ".$Ag_EPP_Bradesco." CC: ".$CC_EPP_Bradesco; ?>)</td>
              </tr>
<?php  
                $arquivoAr = buscaArquivos($folder . "retornos/237/".$Ag_EPP_Bradesco."/".$CC_EPP_Bradesco."/", 'data', 'desc', '');

                if(is_array($arquivoAr)) {

                    array_splice($arquivoAr, 10); 
                    foreach($arquivoAr as $key => $filename) {
?>
                    <tr>
                        <td class="p0">
                            <a style="text-decoration:none" target="_blank" href="corte_boleto_remessas_down.php?dir=<?php  echo urlencode("retornos/237/$Ag_EPP_Bradesco/$CC_EPP_Bradesco/") ?>&arquivo=<?php  echo urlencode($filename) ?>">
                                <?php  echo $filename ?>
                            </a></nobr>
                        </td>
                    </tr>
<?php 
                    }
                }
?>
            </table>
        </div>
        
        <div class="col-md-4">

            <table class="table" align="center">
              <tr bgcolor="#F5F5FB"> 
                <td class="texto" align="center">Retorno Bradesco ADM (<?php echo "Ag: ".$Ag_EPP_Bradesco." CC: ".$CC_EPP_ADM_Bradesco; ?>)</td>
              </tr>
<?php  
                $arquivoAr = buscaArquivos($folder . "retornos/237/".$Ag_EPP_Bradesco."/".$CC_EPP_ADM_Bradesco."/", 'data', 'desc', '');

                if(is_array($arquivoAr)) {

                    array_splice($arquivoAr, 10); 
                    foreach($arquivoAr as $key => $filename) {
?>
                    <tr>
                        <td class="p0">
                            <a style="text-decoration:none" target="_blank" href="corte_boleto_remessas_down.php?dir=<?php  echo urlencode("retornos/237/$Ag_EPP_Bradesco/$CC_EPP_ADM_Bradesco/") ?>&arquivo=<?php  echo urlencode($filename) ?>">
                                <?php  echo $filename ?>
                            </a></nobr>
                        </td>
                    </tr>
<?php 
                    }
                }
?>
            </table>
        </div>
        
    </div>
    
    <div class="row">
        <div class="col-md-4">

                <table class="table">
                  <tr bgcolor="#F5F5FB"> 
                    <td class="texto" align="center">Retorno Itaú (<?php echo "Ag: ".$Ag_EPP_Itau." CC: ".$CC_EPP_Itau; ?>)</td>
                  </tr>
<?php  
                $arquivoAr = buscaArquivos($folder . "retornos/341/".$Ag_EPP_Itau."/".$CC_EPP_Itau."/", 'data', 'desc', '');

                if(is_array($arquivoAr)) {

                    array_splice($arquivoAr, 10); 
                    foreach($arquivoAr as $key => $filename) {
?>
                    <tr>
                        <td class="p0">
                            <a style="text-decoration:none" target="_blank" href="corte_boleto_remessas_down.php?dir=<?php  echo urlencode("retornos/341/$Ag_EPP_Itau/$CC_EPP_Itau/") ?>&arquivo=<?php  echo urlencode($filename) ?>">
                                <?php  echo $filename ?>
                            </a></nobr>
                        </td>
                    </tr>
<?php 
                    }
                }
?>
                </table>
        </div>
        
        <div class="col-md-4">
            <table class="table">
                <tr bgcolor="#F5F5FB"> 
                  <td class="texto" align="center">Retorno Itaú (<?php echo "Ag: ".$Ag_EPP_Itau." CC: ".$CC_EPP_Itau_Novo; ?>)</td>
                </tr>
<?php  
                $arquivoAr = buscaArquivos($folder . "retornos/341/".$Ag_EPP_Itau."/".$CC_EPP_Itau_Novo."/", 'data', 'desc', '');
                
                if(is_array($arquivoAr)) {

                    array_splice($arquivoAr, 10); 
                    foreach($arquivoAr as $key => $filename) {
?>
                    <tr>
                        <td class="p0">
                            <a style="text-decoration:none" target="_blank" href="corte_boleto_remessas_down.php?dir=<?php  echo urlencode("retornos/341/$Ag_EPP_Itau/$CC_EPP_Itau_Novo/") ?>&arquivo=<?php  echo urlencode($filename) ?>">
                                <?php  echo $filename ?>
                            </a></nobr>
                        </td>
                    </tr>
<?php 
                    }
                }
?>
            </table>
        </div>
        
        <div class="col-md-4">

            <table class="table">
                <tr bgcolor="#F5F5FB"> 
                  <td class="texto" align="center">Retorno Santander (<?php echo "Ag: ".$BOLETO_MONEY_BANESPA_CEDENTE_AGENCIA." CC: ".$BOLETO_MONEY_BANESPA_CEDENTE_CONTA; ?>)</td>
                </tr>
<?php
                $arquivoAr = buscaArquivos($folder . "retornos/".$BOLETO_MONEY_BANCO_BANESPA_COD_BANCO."/".$BOLETO_MONEY_BANESPA_CEDENTE_AGENCIA."/".$BOLETO_MONEY_BANESPA_CEDENTE_CONTA."/", 'data', 'desc', '');
                if(is_array($arquivoAr)) {

                    array_splice($arquivoAr, 10); 
                    foreach($arquivoAr as $key => $filename) {
?>
                    <tr>
                        <td class="p0">
                            <a style="text-decoration:none" target="_blank" href="corte_boleto_remessas_down.php?dir=<?php  echo urlencode("retornos/".$BOLETO_MONEY_BANCO_BANESPA_COD_BANCO."/".$BOLETO_MONEY_BANESPA_CEDENTE_AGENCIA."/".$BOLETO_MONEY_BANESPA_CEDENTE_CONTA."/") ?>&arquivo=<?php  echo urlencode($filename) ?>">
                                <?php  echo $filename ?>
                            </a></nobr>
                        </td>
                    </tr>
<?php 
                    }
                }
?>
            </table>
        </div>
    </div>
</div>    	

<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="F5F5FB">
	<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>
	<tr bgcolor="#F0F0F0" height="30">
		<td colspan="2">
			
			<?php  if(isset($lc) && $lc){ ?><b>Log completo</b><?php  } else {?><b>Último log</b><?php  } ?>
			
		</td>
		<td align="right">
			
			<?php  if(isset($lc) && $lc){ ?><a href='?'>Último log</a><?php  } else {?><a href='?lc=1'>Log completo</a><?php  } ?>
			
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<br><?php
            if(!isset($lc))
                $lc = false;
            echo str_replace($logDelimitador, "<hr>", str_replace("\n", "<br>", leLog($lc))) ?>
		</td>
	</tr>
	<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>
</table>
		
			
<?php  require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";?>
</body>
</html>
<?php 
// antiga função processaBoleto_Bradesco($arquivoRetornoAr, $fileTemp){ 
// ainda está utilizando tabela boletos_pendentes_bradesco para registrar todos os boletos
function processaBoleto_Banco($bol_banco_defined, $arquivoRetornoAr, $fileTemp){

    global $msgFatal, $msgNaoFatal;
	$msg = "";

	//Cria diretorio
	if($msg == ""){
		$filename = substr(strrchr($fileTemp, "/"), 1);

		if($bol_banco_defined=="237") {
			$detalhe_IdentCedAg = substr($arquivoRetornoAr[1], 24, 5);
			$detalhe_IdentCedCC	= substr($arquivoRetornoAr[1], 29, 7);
		} elseif($bol_banco_defined=="341") {
			$detalhe_IdentCedAg = substr($arquivoRetornoAr[1], 17, 4);
			$detalhe_IdentCedCC	= substr($arquivoRetornoAr[1], 23, 5);	// discard two leading zeros "00"
		} elseif($bol_banco_defined=="033") {
			$detalhe_IdentCedAg = substr($arquivoRetornoAr[1], 53, 4);
			$detalhe_IdentCedCC	= substr($arquivoRetornoAr[1], 59, 9);	
                } else {
			$detalhe_IdentCedAg = "";
			$detalhe_IdentCedCC	= "";
		}

//echo "arquivoRetornoAr[1]: '".$arquivoRetornoAr[1]."'<br>";
//echo "detalhe_IdentCedAg: ".$detalhe_IdentCedAg.", detalhe_IdentCedCC: ".$detalhe_IdentCedCC."<br>";

		// str_replace("\\", "/", $GLOBALS['folder'])
		$folder = $GLOBALS['folder'] . "retornos/$bol_banco_defined/$detalhe_IdentCedAg/$detalhe_IdentCedCC/";
//echo "filename: ".$filename."<br>";
//echo "folder: ".$folder."<br>";

//echo "<pre>";
//echo print_r($GLOBALS['folder']);
//echo "</pre>";
		$msg = criaDiretorio($folder);
	}
		
	//Verifica se arquivo ja existe
	if($msg == ""){
		$arquivosAr = buscaArquivos($folder);
		if(is_array($arquivosAr)) {
            if(array_key_exists(strtolower($filename), $arquivosAr)) $msg = "Arquivo já existe no diretório, provavelmente já foi importado anteriormente.\n";
        }
	}
	//Verifica se arquivo ja existe
	if($msg == ""){
//echo "fileTemp: ".$fileTemp.", folder+filename: ".$folder . $filename."<br>";
		if(!rename($fileTemp, $folder . $filename)) $msg = "Erro ao copiar arquivo '$filename' para o diretório $folder.\n";
	}

	//header
	if($msg == ""){
		if($bol_banco_defined=="237") {
			$bol_banco_defined_nome		= "Bradesco";

			$header_IdentReg			= substr($arquivoRetornoAr[0], 0, 1);
			$header_IdentArqRetorno		= substr($arquivoRetornoAr[0], 1, 1);
			$header_LiteralRetorno		= strtoupper(trim(substr($arquivoRetornoAr[0], 2, 7)));
			$header_CodServico			= substr($arquivoRetornoAr[0], 9, 2);
			$header_LiteralServico		= strtoupper(trim(substr($arquivoRetornoAr[0], 11, 15)));
			$header_CodEmpresa			= substr($arquivoRetornoAr[0], 26, 20);
			$header_NomeEmpresa			= strtoupper(trim(substr($arquivoRetornoAr[0], 46, 30)));
			$header_NumeroBradesco		= substr($arquivoRetornoAr[0], 76, 3);
			$header_NomeBanco			= strtoupper(trim(substr($arquivoRetornoAr[0], 79, 15)));

			//Validacao
			if($header_IdentReg != "0") 			$msg .= "Identificação do Registro inválida ($header_IdentReg).\n";
			if($header_IdentArqRetorno != "2") 		$msg .= "Identificação do Arquivo Retorno inválida ($header_IdentArqRetorno).\n";
			if($header_LiteralRetorno != "RETORNO") $msg .= "Literal Retorno inválido ($header_LiteralRetorno).\n";
			if($header_CodServico != "01") 			$msg .= "Código do Serviço inválido ($header_CodServico).\n";
			if($header_LiteralServico != "COBRANCA")$msg .= "Literal Serviço inválido ($header_LiteralServico).\n";
			if($header_NumeroBradesco != "237") 	$msg .= "Nº do Bradesco inválido ($header_NumeroBradesco).\n";
			if($header_NomeBanco != "BRADESCO") 	$msg .= "Nome  do Banco por Extenso inválido ($header_NomeBanco).\n";

		} elseif($bol_banco_defined=="341") { 
			$bol_banco_defined_nome		= "Banco Itaú";

			$header_IdentReg			= substr($arquivoRetornoAr[0], 0, 1);
			$header_IdentArqRetorno		= substr($arquivoRetornoAr[0], 1, 1);
			$header_LiteralRetorno		= strtoupper(trim(substr($arquivoRetornoAr[0], 2, 7)));
			$header_CodServico			= substr($arquivoRetornoAr[0], 9, 2);
			$header_LiteralServico		= strtoupper(trim(substr($arquivoRetornoAr[0], 11, 15)));
			$header_CodEmpresa			= substr($arquivoRetornoAr[0], 26, 20);
			$header_NomeEmpresa			= strtoupper(trim(substr($arquivoRetornoAr[0], 46, 30)));
			$header_NumeroItau			= substr($arquivoRetornoAr[0], 76, 3);
			$header_NomeBanco			= strtoupper(trim(substr($arquivoRetornoAr[0], 79, 15)));

			//Validacao
			if($header_IdentReg != "0") 			$msg .= "Identificação do Registro inválida ('$header_IdentReg').\n";
			if($header_IdentArqRetorno != "2") 		$msg .= "Identificação do Arquivo Retorno inválida ('$header_IdentArqRetorno').\n";
			if($header_LiteralRetorno != "RETORNO") $msg .= "Literal Retorno inválido ('$header_LiteralRetorno').\n";
			if($header_CodServico != "01") 			$msg .= "Código do Serviço inválido ('$header_CodServico').\n";
			if($header_LiteralServico != "COBRANCA")$msg .= "Literal Serviço inválido ('$header_LiteralServico').\n";
			if($header_NumeroItau != "341") 		$msg .= "Nº do Banco Itaú inválido ('$header_NumeroItau').\n";
			if($header_NomeBanco != "BANCO ITAU S.A.") 	$msg .= "Nome do Banco por Extenso inválido ('$header_NomeBanco').\n";

		} elseif($bol_banco_defined=="033") { 
			$bol_banco_defined_nome		= "Santander";

			$header_IdentReg			= substr($arquivoRetornoAr[0], 7, 1);
			$header_IdentArqRetorno		= substr($arquivoRetornoAr[0], 142, 1);
			$header_CodServico			= substr($arquivoRetornoAr[1], 9, 2);
			$header_NomeEmpresa			= strtoupper(trim(substr($arquivoRetornoAr[0], 72, 30)));
			$header_NumeroSantander			= substr($arquivoRetornoAr[0], 0, 3);
			$header_NomeBanco			= strtoupper(trim(substr($arquivoRetornoAr[0], 102, 30)));

			//Validacao
			if($header_IdentReg != "0") 			$msg .= "Identificação do Registro inválida ('$header_IdentReg').\n";
			if($header_IdentArqRetorno != "2") 		$msg .= "Identificação do Arquivo Retorno inválida ('$header_IdentArqRetorno').\n";
			if($header_CodServico != "01") 			$msg .= "Código do Serviço inválido ('$header_CodServico').\n";
			if($header_NumeroSantander != "033") 		$msg .= "Nº do Santander inválido ('$header_NumeroSantander').\n";
			if($header_NomeBanco != "BANCO SANTANDER (BRASIL) S/A") 	$msg .= "Nome do Banco por Extenso inválido ('$header_NomeBanco').\n";

		} else {
			$bol_banco_defined_nome		= "Banco ???";

			echo "Codigo do Banco não suportado (bol_banco_defined: $bol_banco_defined)<br>";
			die("Stop");
		}
	}
	
	
	//Inicia transacao
	if($msg == ""){
		$sql = "BEGIN TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg = "Erro ao iniciar transação.\n";
	}
	
	//detail
	if($msg == ""){
		
		$msgFatal = "";
		$msgNaoFatal = "";
		$detalhe_DataOcorr_aux = null;
		$detalhe_DataVencTitulo_aux = null;
		$detalhe_DataCredito_aux = null;
                
                //Criando contador diferenciado para Santander pois trabalha com 2 Linhas por boleto
                if($bol_banco_defined=="033") {
                    $Incrementador = 2;
                    $terminador = count($arquivoRetornoAr)-3;
                }//end if($bol_banco_defined=="033")
                else {
                    $Incrementador = 1;
                    $terminador = count($arquivoRetornoAr);
                }//end else do if($bol_banco_defined=="033")
                
		for($i = $Incrementador; $i < $terminador; $i+=$Incrementador) {
			if($bol_banco_defined=="237") {

				$detalhe_IdentReg			= substr($arquivoRetornoAr[$i], 0, 1);
				$detalhe_IdentCedCart		= substr($arquivoRetornoAr[$i], 20, 4);
				$detalhe_IdentCedAg			= substr($arquivoRetornoAr[$i], 24, 5);
				$detalhe_IdentCedCC			= substr($arquivoRetornoAr[$i], 29, 7);
				$detalhe_IdentCedCCDV		= substr($arquivoRetornoAr[$i], 36, 1);
				$detalhe_ContrPart			= substr($arquivoRetornoAr[$i], 37, 25);
				$detalhe_IdentTitulo		= substr($arquivoRetornoAr[$i], 70, 12);
				$detalhe_IdentOcorr			= substr($arquivoRetornoAr[$i], 108, 2);
				$detalhe_DataOcorr			= substr($arquivoRetornoAr[$i], 110, 6);
				$detalhe_NroDocumento		= substr($arquivoRetornoAr[$i], 116, 10);
				$detalhe_IdentTituloBanco	= substr($arquivoRetornoAr[$i], 126, 20);
				$detalhe_DataVencTitulo		= substr($arquivoRetornoAr[$i], 146, 6);
				$detalhe_ValorTitulo		= substr($arquivoRetornoAr[$i], 152, 13);
				$detalhe_ValorPago			= substr($arquivoRetornoAr[$i], 253, 13);
				$detalhe_DataCredito		= substr($arquivoRetornoAr[$i], 295, 6);	// "DDMMYY", Ex:  "100108"
				$detalhe_MotivosRejeicoes	= substr($arquivoRetornoAr[$i], 318, 10);
				$detalhe_RegSequencial		= substr($arquivoRetornoAr[$i], 394, 6);

			} elseif($bol_banco_defined=="341") {

				$detalhe_Itau_tipo_de_registro		= substr($arquivoRetornoAr[$i],  0,  1);
				$detalhe_Itau_codigo_de_inscricao	= substr($arquivoRetornoAr[$i],  1,  2);
				$detalhe_Itau_numero_de_inscricao	= substr($arquivoRetornoAr[$i],  3, 14);
				$detalhe_Itau_agencia				= substr($arquivoRetornoAr[$i], 17,  4);
				$detalhe_Itau_zeros					= substr($arquivoRetornoAr[$i], 21,  2);
				$detalhe_Itau_conta					= substr($arquivoRetornoAr[$i], 23,  5);
				$detalhe_Itau_dac					= substr($arquivoRetornoAr[$i], 28,  1);
				$detalhe_Itau_brancos1				= substr($arquivoRetornoAr[$i], 29,  8);
				$detalhe_Itau_uso_da_empresa		= substr($arquivoRetornoAr[$i], 37, 25);
				$detalhe_Itau_nosso_numero1			= substr($arquivoRetornoAr[$i], 62,  8);
				$detalhe_Itau_brancos2				= substr($arquivoRetornoAr[$i], 70, 12);
				$detalhe_Itau_carteira				= substr($arquivoRetornoAr[$i], 82,  3);
				$detalhe_Itau_nosso_numero2			= substr($arquivoRetornoAr[$i], 85,  8);
				$detalhe_Itau_dac_nosso_numero		= substr($arquivoRetornoAr[$i], 93,  1);
				$detalhe_Itau_brancos3				= substr($arquivoRetornoAr[$i], 94, 13);
				$detalhe_Itau_carteira				= substr($arquivoRetornoAr[$i],107,  1);
				$detalhe_Itau_cod_de_ocorrencia		= substr($arquivoRetornoAr[$i],108,  2);
				$detalhe_Itau_data_de_ocorrencia	= substr($arquivoRetornoAr[$i],110,  6);
				$detalhe_Itau_no_do_documento		= substr($arquivoRetornoAr[$i],116, 10);
				$detalhe_Itau_nosso_numero3			= substr($arquivoRetornoAr[$i],126,  8);
				$detalhe_Itau_brancos4				= substr($arquivoRetornoAr[$i],134, 12);
				$detalhe_Itau_vencimento			= substr($arquivoRetornoAr[$i],146,  6);
				$detalhe_Itau_valor_do_titulo		= substr($arquivoRetornoAr[$i],152, 13);
				$detalhe_Itau_codigo_do_banco		= substr($arquivoRetornoAr[$i],165,  3);
				$detalhe_Itau_agencia_cobradora		= substr($arquivoRetornoAr[$i],168,  4);
				$detalhe_Itau_dac_ag_cobradora		= substr($arquivoRetornoAr[$i],172,  1);
				$detalhe_Itau_especie				= substr($arquivoRetornoAr[$i],173,  2);
				$detalhe_Itau_tarifa_de_cobranca	= substr($arquivoRetornoAr[$i],175, 13);
				$detalhe_Itau_brancos5				= substr($arquivoRetornoAr[$i],188, 26);
				$detalhe_Itau_valor_do_iof			= substr($arquivoRetornoAr[$i],214, 13);
				$detalhe_Itau_valor_abatimento		= substr($arquivoRetornoAr[$i],227, 13);
				$detalhe_Itau_descontos				= substr($arquivoRetornoAr[$i],240, 13);
				$detalhe_Itau_valor_principal		= substr($arquivoRetornoAr[$i],253, 13);	// é o valor (detalhe_Itau_valor_do_titulo - detalhe_Itau_descontos - BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO)
				$detalhe_Itau_juros_de_mora_multa	= substr($arquivoRetornoAr[$i],266, 13);
				$detalhe_Itau_outros_creditos		= substr($arquivoRetornoAr[$i],279, 13);
				$detalhe_Itau_brancos6				= substr($arquivoRetornoAr[$i],292,  3);
				$detalhe_Itau_data_credito			= substr($arquivoRetornoAr[$i],295,  6);
				$detalhe_Itau_instrcancelada		= substr($arquivoRetornoAr[$i],301,  4);
				$detalhe_Itau_brancos7				= substr($arquivoRetornoAr[$i],305,  6);
				$detalhe_Itau_zeros					= substr($arquivoRetornoAr[$i],311, 13);
				$detalhe_Itau_nome_do_sacado		= substr($arquivoRetornoAr[$i],324, 30);
				$detalhe_Itau_brancos8				= substr($arquivoRetornoAr[$i],354, 23);
				$detalhe_Itau_erros					= substr($arquivoRetornoAr[$i],377,  8);
				$detalhe_Itau_brancos9				= substr($arquivoRetornoAr[$i],385,  7);
				$detalhe_Itau_cod_de_liquidacao		= substr($arquivoRetornoAr[$i],392,  2);
				$detalhe_Itau_numero_sequencial		= substr($arquivoRetornoAr[$i],394,  6);

				// Pagamentos online tem o campo detalhe_Itau_nosso_numero1 começando com "0"
				// boletos começam com "1", "2", "3", "4", etc
				if(substr($detalhe_Itau_nosso_numero1,0,1)=="0") {
//					echo "Desconsidera linha $detalhe_Itau_nosso_numero1 (".substr($detalhe_Itau_nosso_numero1,0,1).")<br>";
					continue;
				}

				// Traduz para as variáveis usadas no boleto Bradesco
				$detalhe_IdentReg			= $detalhe_Itau_tipo_de_registro;
				$detalhe_IdentCedCart		= "";
				$detalhe_IdentCedAg			= $detalhe_Itau_agencia;
				$detalhe_IdentCedCC			= $detalhe_Itau_conta;
				$detalhe_IdentCedCCDV		= $detalhe_Itau_dac;
				$detalhe_ContrPart			= "";
				$detalhe_IdentTitulo		= $detalhe_Itau_nosso_numero1;
				$detalhe_IdentOcorr			= $detalhe_Itau_cod_de_ocorrencia;
				$detalhe_DataOcorr			= $detalhe_Itau_data_de_ocorrencia;
				$detalhe_NroDocumento		= "";
				$detalhe_IdentTituloBanco	= "";
				$detalhe_DataVencTitulo		= "";
				$detalhe_ValorTitulo		= $detalhe_Itau_valor_do_titulo;
				$detalhe_ValorPago			= ((1*$detalhe_Itau_valor_principal)); // naun é necessário descontar 1,10 já vem descontado => +($GLOBALS['BOLETO_MONEY_ITAU_TAXA_CUSTO_BANCO_2']*100));	// O Itaú desconta sua taxa antes de entrar na nossa conta
                                $detalhe_DataCredito		= $detalhe_Itau_data_credito;
				$detalhe_MotivosRejeicoes	= "";
				$detalhe_RegSequencial		= $detalhe_Itau_numero_sequencial;
                                
			} elseif($bol_banco_defined=="033") {

				$detalhe_IdentReg		= substr($arquivoRetornoAr[$i], 13, 1);
				if($detalhe_IdentReg == "T") {
                                    $detalhe_IdentCedCart	= "";
                                    $detalhe_IdentCedAg		= substr($arquivoRetornoAr[$i], 17, 4);
                                    $detalhe_IdentCedCC		= substr($arquivoRetornoAr[$i], 23, 9);
                                    $detalhe_IdentCedCCDV	= substr($arquivoRetornoAr[$i], 31, 1);
                                    $detalhe_ContrPart		= "";
                                    $detalhe_IdentTitulo	= substr($arquivoRetornoAr[$i], 40, 13);
                                    
                                    $detalhe_IdentOcorr		= substr($arquivoRetornoAr[$i+1], 15, 2);
                                    $detalhe_DataOcorr		= substr($arquivoRetornoAr[$i+1], 137, 8);
                                    
                                    $detalhe_NroDocumento	= substr($arquivoRetornoAr[$i], 54, 15);
                                    $detalhe_IdentTituloBanco	= "";
                                    $detalhe_DataVencTitulo	= substr($arquivoRetornoAr[$i], 69, 8);
                                    
                                    $detalhe_ValorTitulo	= substr($arquivoRetornoAr[$i], 77, 15)*1;
                                    $detalhe_ValorPago		= substr($arquivoRetornoAr[$i+1], 77, 15)*1;
                                    
                                    $detalhe_DataCredito	= substr($arquivoRetornoAr[$i+1], 145, 8);
                                    $detalhe_MotivosRejeicoes	= "";
                                    $detalhe_RegSequencial	= substr($arquivoRetornoAr[$i+1], 8, 5)*1;
                                    $detalhe_IdentReg   = "1"; //para efeito de continue
                                }//end if($detalhe_IdentReg == "T")
                                else {
                                    unset($detalhe_IdentCedCart);
                                    unset($detalhe_IdentCedAg);
                                    unset($detalhe_IdentCedCC);
                                    unset($detalhe_IdentCedCCDV);
                                    unset($detalhe_ContrPart);
                                    unset($detalhe_IdentTitulo);
                                    unset($detalhe_IdentOcorr);
                                    unset($detalhe_DataOcorr);
                                    unset($detalhe_NroDocumento);
                                    unset($detalhe_IdentTituloBanco);
                                    unset($detalhe_DataVencTitulo);
                                    unset($detalhe_ValorTitulo);
                                    unset($detalhe_ValorPago);
                                    unset($detalhe_DataCredito);
                                    unset($detalhe_MotivosRejeicoes);
                                    unset($detalhe_RegSequencial);
                                    $detalhe_IdentReg   = "1"; //para efeito de continue
                                }//end else do if($detalhe_IdentReg == "T")

                        } else {
				echo "Codigo do Banco não suportado (bol_banco_defined: $bol_banco_defined)<br>";
				die("Stop");
			}

//echo "<!-- ".$arquivoRetornoAr[$i]." -->\n";

//echo "detalhe_IdentReg: $detalhe_IdentReg<br>\n detalhe_IdentCedCart: $detalhe_IdentCedCart<br>\n detalhe_IdentCedAg: $detalhe_IdentCedAg<br>\n detalhe_IdentCedCC: $detalhe_IdentCedCC<br>\n detalhe_IdentCedCCDV: $detalhe_IdentCedCCDV<br>\n detalhe_ContrPart: $detalhe_ContrPart <br>\n detalhe_IdentTitulo: $detalhe_IdentTitulo<br>\n detalhe_IdentOcorr: $detalhe_IdentOcorr<br>\n detalhe_DataOcorr: $detalhe_DataOcorr <br>\n detalhe_NroDocumento: $detalhe_NroDocumento<br>\n detalhe_IdentTituloBanco : $detalhe_IdentTituloBanco  <br>\n  detalhe_DataVencTitulo: $detalhe_DataVencTitulo   <br>\n  detalhe_ValorTitulo: $detalhe_ValorTitulo<br>\n detalhe_ValorPago: $detalhe_ValorPago <br>\n detalhe_DataCredito: $detalhe_DataCredito<br>\n detalhe_MotivosRejeicoes : $detalhe_MotivosRejeicoes  <br>\n  detalhe_RegSequencial: $detalhe_RegSequencial<br>\n";

//die("Stop ABC");
			//Validacao
			if($detalhe_IdentReg != "1") continue;
			$msgNaoFatal_aux = "";

			//ajusta valores
			unset($detalhe_DataOcorr_aux);
			unset($detalhe_DataVencTitulo_aux);
			unset($detalhe_DataCredito_aux); 		
                        //Se for Santander outro formato de data no arquivo 
		        if($bol_banco_defined=="033") {
                            if($detalhe_DataOcorr != "000000" && trim($detalhe_DataOcorr) != "") $detalhe_DataOcorr_aux = substr($detalhe_DataOcorr, 4, 4) . "-" . substr($detalhe_DataOcorr, 2, 2) . "-" . substr($detalhe_DataOcorr, 0, 2);
                            if($detalhe_DataVencTitulo != "000000" && trim($detalhe_DataVencTitulo) != "") $detalhe_DataVencTitulo_aux = substr($detalhe_DataVencTitulo, 4, 4) . "-" . substr($detalhe_DataVencTitulo, 2, 2) . "-" . substr($detalhe_DataVencTitulo, 0, 2);
                            if($detalhe_DataCredito != "000000" && trim($detalhe_DataCredito) != "") $detalhe_DataCredito_aux = substr($detalhe_DataCredito, 4, 4) . "-" . substr($detalhe_DataCredito, 2, 2) . "-" . substr($detalhe_DataCredito, 0, 2);
                        }//end if($bol_banco_defined=="033")
                        else {
                            // detalhe_DataOcorr_aux = "20" . "YY" . "MM" . "DD" // "DDMMYY", Ex:  "100108"
                            if($detalhe_DataOcorr != "000000" && trim($detalhe_DataOcorr) != "") $detalhe_DataOcorr_aux = "20" . substr($detalhe_DataOcorr, 4, 2) . "-" . substr($detalhe_DataOcorr, 2, 2) . "-" . substr($detalhe_DataOcorr, 0, 2);
                            if($detalhe_DataVencTitulo != "000000" && trim($detalhe_DataVencTitulo) != "") $detalhe_DataVencTitulo_aux = "20" . substr($detalhe_DataVencTitulo, 4, 2) . "-" . substr($detalhe_DataVencTitulo, 2, 2) . "-" . substr($detalhe_DataVencTitulo, 0, 2);
                            if($detalhe_DataCredito != "000000" && trim($detalhe_DataCredito) != "") $detalhe_DataCredito_aux = "20" . substr($detalhe_DataCredito, 4, 2) . "-" . substr($detalhe_DataCredito, 2, 2) . "-" . substr($detalhe_DataCredito, 0, 2);
			}//end else do if($bol_banco_defined=="033") 

			$detalhe_ValorTitulo_aux = number_format(intval($detalhe_ValorTitulo)/100, 2, ".","");
			$detalhe_ValorPago_aux = number_format(intval($detalhe_ValorPago)/100, 2, ".","");

			
                        if (!empty($detalhe_IdentOcorr) && ($detalhe_ValorPago_aux*1) != 0) {
                            //valida se registro ja foi importado
                            // está usando boletos_pendentes_bradesco para todos os bancos
                            $sql = "select bpb_codigo from boletos_pendentes_bradesco where 
                                                    bpb_IdentCedCart = '$detalhe_IdentCedCart' and bpb_IdentCedAg = '$detalhe_IdentCedAg' and
                                                    bpb_IdentCedCC = '$detalhe_IdentCedCC' and bpb_IdentTitulo = '$detalhe_IdentTitulo' and
                                                    bpb_IdentOcorr = '$detalhe_IdentOcorr' and bpb_DataOcorr = '$detalhe_DataOcorr_aux' and
                                                    bpb_bol_banco = '$bol_banco_defined';";
//echo $sql . "<br>";
                            $ret = SQLexecuteQuery($sql);
                            if(!$ret) $msgFatal = "Erro ao pesquisar boleto pendente ".$bol_banco_defined_nome.": Sequêncial ($detalhe_RegSequencial).\n";
                            elseif(pg_num_rows($ret) > 0){
                                    $ret_row = pg_fetch_array($ret);
                                    $bpb_codigo = $ret_row['bpb_codigo'];
                                    $msgNaoFatal_aux = "Registro já inserido anteriormente: Banco('$bol_banco_defined', '$bol_banco_defined_nome'), Carteira($detalhe_IdentCedCart), Agência($detalhe_IdentCedAg), Conta($detalhe_IdentCedCC), Identificação do Título ($detalhe_IdentTitulo), Identificação da Ocorrência ($detalhe_IdentOcorr), Data da Ocorrência ($detalhe_DataOcorr).\n";
                            } else {

                                    //Importa registro
                                    $sql = "insert into boletos_pendentes_bradesco (
                                                            bpb_IdentCedCart, bpb_IdentCedAg, bpb_IdentCedCC, bpb_ContrPart, bpb_IdentTitulo, 
                                                            bpb_IdentOcorr, bpb_DataOcorr, bpb_NroDocumento, bpb_IdentTituloBanco, 
                                                            bpb_DataVencTitulo, bpb_ValorTitulo, bpb_ValorPago, 
                                                            bpb_DataCredito, bpb_MotivosRejeicoes, bpb_bol_banco)
                                                    values( 
                                                            '$detalhe_IdentCedCart','$detalhe_IdentCedAg','$detalhe_IdentCedCC','$detalhe_ContrPart','$detalhe_IdentTitulo',
                                                            '$detalhe_IdentOcorr'," . SQLaddFields($detalhe_DataOcorr_aux, "s") . ",'$detalhe_NroDocumento','$detalhe_IdentTituloBanco',
                                                            " . SQLaddFields($detalhe_DataVencTitulo_aux, "s") . ",$detalhe_ValorTitulo_aux,$detalhe_ValorPago_aux, 
                                                            " . SQLaddFields($detalhe_DataCredito_aux, "s") . ",'$detalhe_MotivosRejeicoes', '$bol_banco_defined');";
//echo $sql . "<br>";
                                    $ret = SQLexecuteQuery($sql);
                                    if(!$ret) $msgFatal = "Erro ao inserir registro: Sequêncial ($detalhe_RegSequencial).\n";
                                    else{
                                            $rs_id = SQLexecuteQuery("select currval('boletos_pendentes_bradesco_bpb_codigo_seq') as last_id");
                                            if(!$rs_id || pg_num_rows($rs_id) == 0) $msgFatal = "Erro ao obter id do registro: Sequêncial ($detalhe_RegSequencial).\n";
                                            else {
                                                    $rs_id_row = pg_fetch_array($rs_id);
                                                    $bpb_codigo = $rs_id_row['last_id'];
                                            }
                                    }
                            }

                            if($msgFatal == "" && $msgNaoFatal_aux == ""){

                                    //Nosso Numero - Origem
                                    $origem		= intval(substr($detalhe_IdentTitulo, 0, 1));
    //echo "<!-- detalhe_IdentTitulo: '".$detalhe_IdentTitulo."' - [".$origem."] -->\n";
    //echo "detalhe_IdentTitulo: '".$detalhe_IdentTitulo."' - [".$origem."] <br>\n";

                                    //Boleto Corte
                                    if($origem == "1"){
    //echo "detalhe_IdentReg: $detalhe_IdentReg<br>\n detalhe_IdentCedCart: $detalhe_IdentCedCart<br>\n detalhe_IdentCedAg: $detalhe_IdentCedAg<br>\n detalhe_IdentCedCC: $detalhe_IdentCedCC<br>\n detalhe_IdentCedCCDV: $detalhe_IdentCedCCDV<br>\n detalhe_ContrPart: $detalhe_ContrPart <br>\n detalhe_IdentTitulo: $detalhe_IdentTitulo<br>\n detalhe_IdentOcorr: $detalhe_IdentOcorr<br>\n detalhe_DataOcorr: $detalhe_DataOcorr <br>\n detalhe_NroDocumento: $detalhe_NroDocumento<br>\n detalhe_IdentTituloBanco : $detalhe_IdentTituloBanco  <br>\n  detalhe_DataVencTitulo: $detalhe_DataVencTitulo   <br>\n  detalhe_ValorTitulo: $detalhe_ValorTitulo<br>\n detalhe_ValorPago: $detalhe_ValorPago <br>\n detalhe_DataCredito: $detalhe_DataCredito<br>\n detalhe_MotivosRejeicoes : $detalhe_MotivosRejeicoes  <br>\n  detalhe_RegSequencial: $detalhe_RegSequencial<br>\n";


                                            //Nosso Numero est_codigo, cor_codigo
                                            // detalhe_IdentTitulo:
                                            //		ver geraBoleto(): $cor_ug_id + $cor_codigo
                                            // exemplo: 10005079831P	
    //					$est_codigo	= intval(substr($detalhe_IdentTitulo, 1, 5));
                                            $est_codigo	= "";	//intval(substr($detalhe_IdentTitulo, 1, 5));
                                            //	"1000CCCCCCC"
    //					$cor_codigo	= intval(substr($detalhe_IdentTitulo, 4, 7));

                                            // para ajustar os boletos de corte com cor_codigo de +5 digitos em 2010-08-22/2010-08-25
                                            $detalhe_DataOcorr_a = substr($detalhe_DataOcorr,0,2)."-".substr($detalhe_DataOcorr,2,2)."-20".substr($detalhe_DataOcorr,4,2);
                                            $detalhe_DataOcorr_date_a = strtotime($detalhe_DataOcorr_a);
    /*
                                            // processamento temporário para corrigir erro no nosso número: ficou >99999
                                            if(($detalhe_DataOcorr_date_a-strtotime("2010-08-22"))>=0 && ($detalhe_DataOcorr_date_a-strtotime("2010-08-31"))<=0) {
                                                    if(substr($detalhe_IdentTitulo, 6, 1)=="0") {	
                                                            // formato antigo com erro: cor_codigo>99999
                                                            //   01234567890
                                                            //	"1uuuuuCCCCC"
                                                            // passa "01466" -> "101466"
                                                            $cor_codigo	= intval("1".substr($detalhe_IdentTitulo, 6, 5));
                                                    } else {
                                                            // Formato antigo	
                                                            //	"1uuuuuCCCCC"
                                                            // deixa "99899" -> "99899"
                                                            $cor_codigo	= intval(substr($detalhe_IdentTitulo, 6, 5));
                                                    }
                                            } else {
                                                    // Novo formato de "Nosso numero" no boleto
                                                    //	"1000CCCCCCC"
                                                    $cor_codigo	= intval(substr($detalhe_IdentTitulo, 3, 8));
                                            }
    */
                                            // Novo formato de "Nosso numero" no boleto
                                            //	"1000CCCCCCC"
                                            $cor_codigo	= intval(substr($detalhe_IdentTitulo, 3, 8));


                                            $sql = "select cor_bbc_boleto_codigo from cortes where cor_codigo = $cor_codigo";
    //echo $sql . "<br>";
                                            $ret = SQLexecuteQuery($sql);
                                            if(!$ret) $msgFatal = "Erro ao pesquisar codigo do boleto no corte: Corte ($cor_codigo).\n";
                                            elseif(pg_num_rows($ret) == 0) $msgFatal = "Nenhum boleto encontrado para o corte ($cor_codigo) data lida: $detalhe_DataOcorr.\n";
                                            else {
                                                    $ret_row = pg_fetch_array($ret);
                                                    $cor_bbc_boleto_codigo = $ret_row['cor_bbc_boleto_codigo'];
                                            }

                                            if($msgFatal == ""){

                                                    //Remessa Rejeitada 
                                                    if($detalhe_IdentOcorr == "03"){
                                                            //Atualiza status do boleto para rejeitado
                                                            $sql = "update boleto_bancario_cortes set 
                                                                                    bbc_bpb_codigo = $bpb_codigo,
                                                                                    bbc_status_banco = " . $GLOBALS['BOLETO_BANCO_STATUS']['REJEITADO'] . "
                                                                            where bbc_boleto_codigo = $cor_bbc_boleto_codigo";
    //echo $sql . "<br>";
                                                            $ret = SQLexecuteQuery($sql);
                                                            if(!$ret) $msgFatal = "Erro ao atualizar status REJEITADO do boleto: Código ($cor_bbc_boleto_codigo), Corte ($cor_codigo).\n";
                                                            else $msgNaoFatal_aux = "Boleto REJEITADO: Código ($cor_bbc_boleto_codigo), Corte ($cor_codigo).\n";

                                                    //Entrada Confirmada
                                                    }elseif($detalhe_IdentOcorr == "02"){
                                                            //Atualiza status do boleto para entrada confirmada
                                                            $sql = "update boleto_bancario_cortes set 
                                                                                    bbc_status_banco = " . $GLOBALS['BOLETO_BANCO_STATUS']['ACEITO'] . "
                                                                            where bbc_boleto_codigo = $cor_bbc_boleto_codigo";
    //echo $sql . "<br>";
                                                            $ret = SQLexecuteQuery($sql);
                                                            if(!$ret) $msgFatal = "Erro ao atualizar status ACEITO do boleto: Código ($cor_bbc_boleto_codigo), Corte ($cor_codigo).\n";
                                                            else $msgNaoFatal_aux = "Boleto com ENTRADA CONFIRMADA: Código ($cor_bbc_boleto_codigo), Corte ($cor_codigo).\n";

                                                    //Título Liquidado
                                                    }elseif($detalhe_IdentOcorr == "06" || $detalhe_IdentOcorr == "17"){
                                                            $sql = "update boleto_bancario_cortes set 
                                                                                    bbc_bpb_codigo = $bpb_codigo,
                                                                                    bbc_status_banco = " . $GLOBALS['BOLETO_BANCO_STATUS']['LIQUIDADO'] . ",
                                                                                    bbc_status = " . $GLOBALS['CORTE_BOLETO_STATUS']['CONCILIADO'] . ",
                                                                                    bbc_data_concilia = CURRENT_TIMESTAMP
                                                                            where bbc_boleto_codigo = $cor_bbc_boleto_codigo";
    //echo $sql . "<br>";
                                                            $ret = SQLexecuteQuery($sql);
                                                            if(!$ret) $msgFatal = "Erro ao atualizar status CONCILIADO do boleto: Código ($cor_bbc_boleto_codigo), Corte ($cor_codigo).\n";
                                                            else {
                                                                    $msgNaoFatal_aux = "Boleto LIQUIDADO: Código ($cor_bbc_boleto_codigo), Corte ($cor_codigo).\n";
                                                                    if($detalhe_ValorTitulo_aux != $detalhe_ValorPago_aux) $msgNaoFatal_aux .= "Valor Pago difere do valor do boleto: Código ($cor_bbc_boleto_codigo), Corte ($cor_codigo), Valor (" . number_format($detalhe_ValorTitulo_aux, 2, ",", ".") . "), Valor Pago (" . number_format($detalhe_ValorPago_aux, 2, ",", ".") . ").\n";
                                                                    else {
                                                                            //Concilia
                                                                            $ret = concilia_boleto($cor_codigo, $cor_bbc_boleto_codigo, null);
                                                                            if($ret != "") $msgFatal .= $ret . "Corte ($cor_codigo)";
                                                                            else $msgNaoFatal_aux .= "Conciliação do corte efetuado com sucesso.\n";
                                                                    }
                                                            }
                                                    }
                                            }
                                    }


                                    //Boleto Money
                                    if($origem == "2"){

                                            //Título Liquidado
                                            if($detalhe_IdentOcorr == "06" || $detalhe_IdentOcorr == "17"){
                                                    $bol_banco = $bol_banco_defined;
                                                    $bol_documento = $detalhe_IdentTitulo;
                                                    $bol_valor = $detalhe_ValorPago_aux;
                                                    $data = $detalhe_DataCredito_aux;

                                                    $sql = "insert into boletos_pendentes (bol_valor, bol_data, bol_banco, bol_documento, bol_importacao)";
                                                    $sql .= " values (" . $bol_valor . ",'" . $data . "','" . $bol_banco . "','" . $bol_documento . "','" . date('Y-m-d H:i:s') . "');";
                                                    //echo $sql . "<br>";	
                                                    $ret = SQLexecuteQuery($sql);
                                                    if(!$ret) $msgFatal = "Erro ao inserir boleto Money: Sequêncial ($detalhe_RegSequencial).\n";
                                                    else $msgNaoFatal_aux = "Boleto Money inserido: $detalhe_IdentTitulo.\n";
                                            }
                                    }				

                                    //Boleto Express Money
                                    if($origem == "3"){

                                            //Título Liquidado
                                            if($detalhe_IdentOcorr == "06" || $detalhe_IdentOcorr == "17"){
                                                    $bol_banco = $bol_banco_defined;
                                                    $bol_documento = $detalhe_IdentTitulo;
                                                    $bol_valor = $detalhe_ValorPago_aux;
                                                    $data = $detalhe_DataCredito_aux;

                                                    $sql = "insert into boletos_pendentes (bol_valor, bol_data, bol_banco, bol_documento, bol_importacao)";
                                                    $sql .= " values (" . $bol_valor . ",'" . $data . "','" . $bol_banco . "','" . $bol_documento . "','" . date('Y-m-d H:i:s') . "')";
                                                    //echo $sql . "<br>";
                                                    $ret = SQLexecuteQuery($sql);
                                                    if(!$ret) $msgFatal = "Erro ao inserir boleto Express Money: Sequêncial ($detalhe_RegSequencial).\n";
                                                    else $msgNaoFatal_aux = "Boleto Express Money inserido: $detalhe_IdentTitulo.\n";
                                            }
                                    }				

                                    //Boleto Express Money LH
                                    if($origem == "4"){

                                            //Título Liquidado
                                            if($detalhe_IdentOcorr == "06" || $detalhe_IdentOcorr == "17"){
                                                    $bol_banco = $bol_banco_defined;
                                                    $bol_documento = $detalhe_IdentTitulo;
                                                    $bol_valor = $detalhe_ValorPago_aux;
                                                    $data = $detalhe_DataCredito_aux;

                                                    $sql = "insert into boletos_pendentes (bol_valor, bol_data, bol_banco, bol_documento, bol_importacao)";
                                                    $sql .= " values (" . $bol_valor . ",'" . $data . "','" . $bol_banco . "','" . $bol_documento . "','" . date('Y-m-d H:i:s') . "')";
                                                    //echo $sql . "<br>";
                                                    $ret = SQLexecuteQuery($sql);
                                                    if(!$ret) $msgFatal = "Erro ao inserir boleto PDV Pré: Sequêncial ($detalhe_RegSequencial).\n";
                                                    else $msgNaoFatal_aux = "Boleto PDV Pré inserido: $detalhe_IdentTitulo.\n";
                                            }
                                    }				

                                    //Boleto Money Depósito em Saldo
                                    if($origem == "6"){

                                            //Título Liquidado
                                            if($detalhe_IdentOcorr == "06" || $detalhe_IdentOcorr == "17"){
                                                    $bol_banco = $bol_banco_defined;
                                                    $bol_documento = $detalhe_IdentTitulo;
                                                    $bol_valor = $detalhe_ValorPago_aux;
                                                    $data = $detalhe_DataCredito_aux;

                                                    $sql = "insert into boletos_pendentes (bol_valor, bol_data, bol_banco, bol_documento, bol_importacao)";
                                                    $sql .= " values (" . $bol_valor . ",'" . $data . "','" . $bol_banco . "','" . $bol_documento . "','" . date('Y-m-d H:i:s') . "')";
                                            //echo $sql . "<br>";
                                                    $ret = SQLexecuteQuery($sql);
                                                    if(!$ret) $msgFatal = "Erro ao inserir boleto Money Deposito em Saldo: Sequêncial ($detalhe_RegSequencial).\n";
                                                    else $msgNaoFatal_aux = "Boleto Money Deposito em Saldo inserido: $detalhe_IdentTitulo.\n";
                                            }
                                    }				


                            } //end if($msgFatal == "" && $msgNaoFatal_aux == ""){
                            $msgNaoFatal .= $msgNaoFatal_aux;
                            if($msgFatal != "") break;
                        }//end if (!empty($detalhe_IdentOcorr) && ($detalhe_ValorPago_aux*1) != 0)
                        elseif (($detalhe_ValorPago_aux*1) == 0)  {
                            echo "Boleto [".$detalhe_IdentTitulo."] com valor pago Zerado (Registrado e n?o pago)<br>" ;
                        }
		}//end for
		if($msgFatal != "") if(file_exists($folder . $filename)) unlink ($folder . $filename);
	}
	
	//Finaliza transacao
	if($msg == ""){
		$sql = "COMMIT TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg .= "Erro ao comitar transação.\n";
	} else {
		$sql = "ROLLBACK TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg .= "Erro ao dar rollback na transação.\n";
	}
	
	if($msgFatal != "")	return $msg . $msgFatal . "Arquivo não foi inserido.\n";
	else return $msg . $msgNaoFatal;
}

function criaDiretorio($folder){
	
	$msg = "";
//echo "folder: ".$folder."<br>";

	$folderAr = preg_split("/\//", $folder);
	
	if(trim($folder) == "") $msg = "Caminho vázio.\n";
	if(count($folderAr) == 0) $msg = "Caminho inválido.\n";
	
	
	if($msg == ""){
		$fullPath = "";
		for($i=0; $i < count($folderAr); $i++){
			$level = $folderAr[$i];
//echo "level: ".$level."<br>";
			// Não faz o teste para as primeiras pastas 
			if(($level!="backoffice") && ($level!="offweb") && ($level!="corte") && ($level!="retornos") ) {
				if(trim($level) == "" && $i==0) $fullPath .= "/";
				elseif(trim($level) != ""){
					$fullPath .= "$level/";
//echo "&nbsp;&nbsp;fullPath: ".$fullPath."<br>";
					if(!is_dir($fullPath)){ 
//echo "&nbsp;&nbsp;&nbsp;&nbsp;Not is_dir<br>";
						if(!mkdir($fullPath)){
//echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Didn't mkdir<br>";
							$msg = "Erro ao criar diretório.\n";
							break;
						} 
					}
				} 
			} else {
				$fullPath .= "$level/";
			}
		}
	}
		
	return $msg;
}

function buscaArquivos($folder, $ordem = 'nome', $direcao = 'asc', $filtro = '') {

	if($filtro != ''){
		if(strpos($filtro, ';') != strlen($filtro)) $filtro .= ';';
		$filtro = explode(';', $filtro);
	}
	if(is_dir($folder)){
		if ($handle = opendir($folder)) {
			//Carrega e Filtra os arquivos
			while(false !== ($file = readdir($handle))) {
			   if ($file != '.' && $file != '..') {
				    $sdate = " (".date("d/m/Y H:i:s", filemtime($folder.$file)).")";
					if($filtro != ''){
						for($j = 0; $j < count($filtro) -1; $j++){
							if(strpos(strtolower($file), strtolower($filtro[$j])) !== false){
								if($ordem == 'nome') $arquivoAr[strtolower($file)] = $file.$sdate;
								if($ordem == 'data') $arquivoAr[date("YmdHis", filemtime($folder.$file))] = $file.$sdate;
							}
						}
					} else {
						if($ordem == 'nome') $arquivoAr[strtolower($file)] = $file.$sdate;
						if($ordem == 'data') $arquivoAr[date("YmdHis", filemtime($folder.$file))] = $file.$sdate;
					}
				}
			}
			closedir($handle);

			//Ordena os arquivos
			if (count($arquivoAr) != 0) {
				if($direcao == 'asc') ksort($arquivoAr);
				if($direcao == 'desc') krsort($arquivoAr);
			}
			
			return $arquivoAr;
		}
	}
}

function gravaLog($file, $mensagem){

	$msg = "";
	
	if (!file_exists($file)){
		if(!fopen($file, 'w')){
			$msg = "Não foi possível criar arquivo de log.";
			return $msg;
		}
	}

	if (file_exists($file) && (!is_writable($file))) {
		$msg = "Não foi possível gravar log #1.";
		return $msg;

	} else {
		if (!$handle = fopen($file, 'r+')) {
			$msg = "Não foi possível gravar log #2.";
			return $msg;
		} 
		
		//Le conteudo atual do log
		if((file_exists($file)) && (filesize($file)) > 0) {
			$mensagem .= fread($handle, filesize($file));
		}
		
		//grava o log no arquivo
		rewind($handle);
		if (fwrite($handle, $mensagem) === FALSE) {
			$msg = "Não foi possível gravar log #3.";
			return $msg;
		}
	
		fclose($handle);
		return "";
	}
}

function gravaLogBoleto($nomeArqUploaded, $mensagem){

	global $logDelimitador, $folder, $logFile;
	
	$mensagem = date('Y-m-d H:i:s') . " - " . $nomeArqUploaded . ": " . $mensagem . "\n";
	$mensagem .= $logDelimitador . "\n";
	
	return gravaLog($folder . $logFile, $mensagem);
	
}

function leLog($leLogCompleto){

	global $logDelimitador, $folder, $logFile;
	$buffer = '';
	$file = $folder . $logFile;
	
	if (file_exists($file)) {
		if ($handle = fopen($file, 'r')) {
		   	while (!feof($handle)) {
				$buffer_aux = fgets($handle);
				if($leLogCompleto){
					$buffer .= $buffer_aux;
				} else {
					if(trim($buffer_aux) == trim($logDelimitador)){
						break;
					} else {
						$buffer .= $buffer_aux;
					}
				}
			}
		}
		fclose($handle);
	}

	return $buffer;
}
?>
