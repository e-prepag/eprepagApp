<?php 

// Configurar tempo de execução infinito
set_time_limit(0);

// Configurar limite de memória para 512M
ini_set('memory_limit', '512M');

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "functions_lote_carga.php";

//Constantes
//----------------------------------------------------------------------------------------
$logFile = 'lote_carga.log';
$logDelimitador = '#--------------------------------------------------------------';
$folder = $raiz_do_projeto . "log/";

//Operadoras
//----------------------------
$opr_codigo_Ongame		= "13";
$opr_codigo_HabboHotel	= "16";
$opr_codigo_MUOnline	= "17";
$opr_codigo_GameGol		= "18";
$opr_codigo_Acclaim		= "19";
$opr_codigo_KOL			= "21";
$opr_codigo_GameIS		= "22";
$opr_codigo_Kaizen		= "23";
$opr_codigo_Hive		= "24";
$opr_codigo_Brancaleone	= "26";
$opr_codigo_Ticket_Surf	= "27";
$opr_codigo_PayByCash	= "28";

$opr_codigo_Escola24h	= "29";
$opr_codigo_Coolnex		= "30";
$opr_codigo_GPotato		= "31";
$opr_codigo_NDoors		= "33";
$opr_codigo_Webzen		= "34";
$opr_codigo_Vostu		= "35";
$opr_codigo_Cosmopax	= "36";
$opr_codigo_Softnyx		= "37";
$opr_codigo_StarDoll	= "38";
$opr_codigo_AeriaGames	= "39";

$opr_codigo_Onnet		= "40";
$opr_codigo_OGPlanet	= "41";
$opr_codigo_BilaGames	= "42";

$opr_codigo_Ignitedgames = "43";

$opr_codigo_Axeso5		= "44";

$opr_codigo_Jolt		= "50";
$opr_codigo_Mindset		= "52";

$opr_codigo_GlobalGames	= "54";

$opr_codigo_Alawar	= "55";
$opr_codigo_77PB	= "56";

$opr_codigo_FHLGames = "61";
$opr_codigo_YNKinteractive = "60";

$opr_codigo_GlobalGames2= "69";

$opr_codigo_BATTLEFIELD = "76";
$opr_codigo_COMMANDANDCONQUER = "75";
$opr_codigo_NEEDFORSPEED = "74";
$opr_codigo_FIFAWORLD = "77";

$opr_codigo_CheckOk = "80";

$opr_codigo_GlobalGames3= "85";

$opr_codigo_XBox= "95";

$opr_codigo_Facebook_BHN = "100";

$opr_codigo_IMVU_BHN = "101";

$opr_codigo_Encripta = "103";

$opr_codigo_Valvesoftware = "106";

$opr_codigo_G2A = "110";

$opr_codigo_Rimo = "116";

$opr_codigo_Rimo1 = "117";

$opr_codigo_Rimo2 = "119";

$opr_codigo_Webzen_Packs = "118";

$opr_codigo_Webzen_2 = "120";

$opr_codigo_NoPing = "121";

$opr_codigo_Webzen_3 = "122";

$opr_codigo_G2A_2 = "123";

$opr_codigo_HabboHotel_2 = "125";

$opr_codigo_SurfTelecom = "136";

$opr_codigo_Axeso5_new = "149";

$opr_codigo_Tinder_1 = "152";

$opr_codigo_Tinder_2 = "153";

$opr_codigo_exitlag = "161";

if(!isset($opr_codigo))
    $opr_codigo = "";

if($opr_codigo==0 && isset($_POST['opr_codigo'])) 
	$opr_codigo = $_POST['opr_codigo'];
//echo "opr_codigo: ".$opr_codigo."<br>";
//echo "<pre>";
//print_r($_POST);
//print_r($_GET);
//echo "</pre>";
//echo "<hr>";

if(isset($opr_codigo) && $opr_codigo && is_numeric($opr_codigo)) {
	$sql = "SELECT pin_valor FROM ( \n";
	$sql .= "select opr_valor1 as pin_valor from operadoras where opr_codigo = $opr_codigo and (opr_valor1>0 or (opr_valor1=0 and opr_codigo=78)) \n";
	$sql .= "union all \n";
	for($i=2;$i<=15;$i++) {
		$sql .= "select opr_valor".$i." as pin_valor from operadoras where opr_codigo = ".$opr_codigo." and opr_valor".$i.">0 \n";
		if($i<15) {
			$sql .= "union all \n";
		}
	}
	$sql .= ") v order by pin_valor ";
//echo str_replace("\n", "<br>\n", $sql)."<br>";

//echo "sql: $sql<br>";
	$rs_pins = SQLexecuteQuery($sql);
}

	if(isset($Registrar) && $Registrar) {

		//Validacao
		//---------------------------------------------------------------------------
		$msg = "";

		//arquivo
		if($msg == ""){
                        
                        // Nova forma de fazer Upload e importar os arquivos de PINs                     
                        // Testando se foi carregado o arquivo
                        if (is_uploaded_file($_FILES['arquivo']['tmp_name']) && $_FILES['arquivo']['error']==0) {
                             //Definindo o nome para o arquivo temporário no servidor de produção
                             $path = $folder . $_FILES['arquivo']['name'];
								
                             $fileSource = $path;
                             //Testando se o arquivo já existe na pasta temporária
                             if (!file_exists($path)) {
                                 //Movendo o arquivo para a pasta temporária   
                                 if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $path)) {
                                      $msg = "Não teve sucesso no upload para a pasta temporária no Servidor.<br>";
                                    }
                             //Mensagem que o arquivo já existe no diretório temporário no Servidor       
                             } else {
                               $msg = "Arquivo já existe na pasta temporária. Provavelmente, já existe uma tentativa anterior de processamento.";
                             }
                        //Mensagem de erro por conta de problema no Upload do arquivo
                        } else {
                            $msg = "O arquivo não foi uploaded com successo.<br>";
                            $msg .= "(Error Code:" . $_FILES['arquivo']['error'] . ")<br>";
                        }

			if($msg == ""){
                            if (($fileSource == 'none') || ($fileSource == '' )) { 
                                    $msg = "Nenhum arquivo fornecido.\n";
                            } else if((!file_exists($fileSource)) || (filesize($fileSource)) == 0) {
                                    $msg = "Arquivo vazio ou inválido.\n";
                            } 
                        }//end if($msg == "")
		} //end if($msg == "")
	
		//Operadora
		if($msg == ""){
			if(trim($opr_codigo) == "")
				$msg = "A Operadora deve ser selecionada.\n";
			else if(!is_numeric($opr_codigo))
				$msg = "Operadora inválida.\n";
		}

		//Processa
		if($msg == ""){
			if($opr_codigo == $opr_codigo_Ongame){
				$msg = processaLote_Ongame($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_HabboHotel){
				$msg = processaLote_HabboHotel($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_MUOnline){
				$msg = processaLote_MUOnline($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_KOL){
				$msg = processaLote_KOL($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Acclaim){
				$msg = processaLote_Acclaim($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_GameGol){
				$msg = processaLote_GameGol($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_GameIS){
				$msg = processaLote_GameIS($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Kaizen){
				$msg = processaLote_Kaizen($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Hive){
				$msg = processaLote_Hive($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Brancaleone){
				$msg = processaLote_Brancaleone($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Ticket_Surf){
				$msg = processaLote_Ticket_Surf($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_PayByCash){
				$msg = processaLote_PayByCash($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Escola24h){
				$msg = processaLote_Escola24h($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Coolnex){
				$msg = processaLote_Coolnex($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_GPotato){
				$msg = processaLote_GPotato($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_NDoors){
				$msg = processaLote_NDoors($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Webzen  
                                || $opr_codigo == $opr_codigo_Webzen_Packs
                                || $opr_codigo == $opr_codigo_Webzen_2
                                || $opr_codigo == $opr_codigo_Webzen_3){
				$msg = processaLote_Webzen($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Vostu){
				$msg = processaLote_Vostu($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Cosmopax){
				$msg = processaLote_Cosmopax($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Softnyx){
				$msg = processaLote_Softnyx($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_StarDoll){
				$msg = processaLote_StarDoll($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_Onnet){
				$msg = processaLote_Onnet($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_OGPlanet){
				$msg = processaLote_OGPlanet($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_BilaGames){
				$msg = processaLote_BilaGames($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_AeriaGames){
				$msg = processaLote_AeriaGames($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_Ignitedgames){
				$msg = processaLote_Ignitedgames($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Axeso5
                                || $opr_codigo == $opr_codigo_Axeso5_new){
				$msg = processaLote_Axeso5($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_Jolt){
				$msg = processaLote_Jolt($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_Mindset){
				$msg = processaLote_Mindset($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_GlobalGames){
				$msg = processaLote_GlobalGames($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_Alawar){
				$msg = processaLote_Alawar($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_77PB){
				$msg = processaLote_77PB($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_FHLGames){
				$msg = processaLote_FHLGames($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_YNKinteractive){
				$msg = processaLote_YNKinteractive($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_GlobalGames2){
				$msg = processaLote_GlobalGames2($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_BATTLEFIELD 
                                || $opr_codigo == $opr_codigo_COMMANDANDCONQUER 
                                || $opr_codigo == $opr_codigo_NEEDFORSPEED 
                                || $opr_codigo == $opr_codigo_FIFAWORLD){
				$msg = processaLote_EletronicArts($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_CheckOk){
				$msg = processaLote_CheckOk($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_GlobalGames3){
				$msg = processaLote_GlobalGames($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_XBox){
				$msg = processaLote_XBox($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Facebook_BHN
                                || $opr_codigo == $opr_codigo_IMVU_BHN){
				$msg = processaLote_BHN($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_Encripta){
				$msg = processaLote_Encripta($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Valvesoftware){
				$msg = processaLote_Valvesoftware($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_G2A
                                || $opr_codigo == $opr_codigo_G2A_2){
				$msg = processaLote_G2A($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_Rimo 
                                || $opr_codigo == $opr_codigo_Rimo1
                                || $opr_codigo == $opr_codigo_Rimo2){
				$msg = processaLote_Rimo($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_NoPing){
				$msg = processaLote_NoPing($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_HabboHotel_2){
				$msg = processaLote_HabboHotel_2($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $loteValor, $fcanal);
			} else if($opr_codigo == $opr_codigo_SurfTelecom){
				$msg = processaLote_SurfTelecom($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			} else if($opr_codigo == $opr_codigo_Tinder_1
                                || $opr_codigo == $opr_codigo_Tinder_2){
				$msg = processaLote_Tinder($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			}else if($opr_codigo == $opr_codigo_exitlag){
				$msg = processaLote_ExitLag($fileSource, $_FILES['arquivo']['name'], $opr_codigo, $fcanal);
			}else {
				$msg = "Importação de lote para esta operadora ainda não existe.\n";
			}
			
			if($msg == "") $msg = "<font color='#009900'><b>Lote inserido com sucesso.</b></font>\n";
			$msg .= gravaLogFormat($_FILES['arquivo']['name'], $msg);
		}
                
                //Deletando o Arquivo temporário
                unlink($fileSource);

	}

	//Operadoras
	$sql  = "select * from operadoras ope order by opr_nome";		// "opr_nome"
	$rs_operadoras = SQLexecuteQuery($sql);

	$opr_codigos_com_valores = array(16, 26, 35, 52, 55, 56);
	$i = 1;
    if(!isset($s_js))
        $s_js = "";
	foreach($opr_codigos_com_valores as $key => $val) {
		$s_OR_post_condition = ((($i++)<count($opr_codigos_com_valores))?" || ":"");
		$s_js .= "document.form1.opr_codigo[document.form1.opr_codigo.selectedIndex].value == $val ".$s_OR_post_condition;
	}
//	echo "<hr>$s_js<hr>";
?>
<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

$(function(){
    fcnChangeOperadora();
})
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li class="active">Carga de Lote de Pins</li>
    </ol>
</div>
<script>
function fcnOnSubmit(){

	if(form1.opr_codigo.value == ''){
		alert('Operadora não selecionada');
		return false;
	} else	if(form1.arquivo.value == ''){
		alert('Arquivo não especificado');
		return false;
	}
	
	return confirm('Deseja registrar este Lote ?');
	
}

function fcnChangeOperadora(){
	// Diferente para algumas operadoras
	if(
<?php
		echo $s_js;
?>
		)
		document.getElementById('divLoteValor').style.display = 'block';
	else document.getElementById('divLoteValor').style.display = 'none';
}
function fcnSubmit(){
	fcnChangeOperadora();
	document.form1.submit();	
}

</script>
<table class="table txt-preto fontsize-pp">
  <tr>
    <td>
		<form name="form1" method="post" action="" ENCTYPE="multipart/form-data" onSubmit="return fcnOnSubmit();">
        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8" class="texto">Operadora e Canal</font></td>
          </tr>
        <tr>
            <td align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Operadora:&nbsp;</td>
            <td>
                <?php
                    $opr_nome = "Operadora Desconhecida (".$opr_codigo.")";
                    //$stmp = "";
                ?>
              <select name="opr_codigo" class="combo_normal" Onchange="fcnSubmit();">
                <option value="" <?php if($opr_codigo == "") echo "selected" ?>>Selecione</option>
                <?php if($rs_operadoras) while($rs_operadoras_row = pg_fetch_array($rs_operadoras)) { ?>
                <option value="<?php echo $rs_operadoras_row['opr_codigo'] ?>" <?php if($rs_operadoras_row['opr_codigo'] == $opr_codigo) echo "selected" ?>><?php echo $rs_operadoras_row['opr_nome']." (".$rs_operadoras_row['opr_codigo'].")" ?></option>
                <?php 
                        if($opr_codigo==$rs_operadoras_row['opr_codigo'])
                            $opr_nome = $rs_operadoras_row['opr_nome'];
                        //$stmp .= $rs_operadoras_row['opr_codigo']." - ".$rs_operadoras_row['opr_nome']."<br>";
                    }
                ?>
              </select><br>
              <?php //echo $stmp; ?>
            </td>

            <td width="21" height="28" bgcolor="#F5F5FB" align="right" align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Canal:<br></font></td>
            <td height="28" bgcolor="#F5F5FB"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                <select name="fcanal" id="fcanal" class="combo_normal">
                  <option value="s" <?php if(isset($fcanal) && trim($fcanal) == 's') echo "selected"?>>Site</option>
                  <option value="p" <?php if(isset($fcanal) && trim($fcanal) == 'p') echo "selected"?>>POS</option>
                </select>
                </font></td>


            <td align="right">
                <input type="submit" name="Registrar" value="Registrar" class="botao_search">
            </td>
        </tr>
        <tr name="divLoteValor" id="divLoteValor" style="display:none">
            <td colspan="5" align="left"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Valor do Pin:&nbsp;

              <?php //if(b_IsUsuarioReinaldo()) {
                    // <input type="text" name="loteValor" value="<_?php echo $loteValor?_>" size="10" maxlength="10"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif">(um número inteiro)</font>
                      ?>
              <select id="loteValor" name="loteValor">
                <option value="0">Escolha um valor</option>
                    <?php if(isset($rs_pins)) while($rs_pins_row = pg_fetch_array($rs_pins)){ ?>
                    <option value="<?php echo ((int)$rs_pins_row['pin_valor']); ?>" <?php if ($loteValor == ((int)$rs_pins_row['pin_valor'])) echo "selected";?>><?php echo ((int)$rs_pins_row['pin_valor']); ?></option>
                    <?php } ?>
              </select> <nobr>(valores cadastrados na tabela de operadoras para <?php echo $opr_nome .", cód: ". $opr_codigo .""; ?>)</nobr>
              <?php 
                //} 
                ?>
            </td>
        </tr>
        <tr bgcolor="#FFFFFF"><td colspan="5">&nbsp;</td></tr>
        <tr>
            <td align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Arquivo:&nbsp;</td>
            <td colspan="3">
              <input type="hidden" name ="MAX_FILE_SIZE" value="500000">
              <input type="file" name="arquivo" id="arquivo" size="60">
            </td>
            <td align="right">&nbsp;</td>
        </tr>
    </table>
    </form>

    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="F5F5FB">
        <tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>
        <?php if(isset($msg) && $msg != ""){ ?>
            <tr bgcolor="#FFFFFF"><td colspan="3" align="center"><font face="Arial, Helvetica, sans-serif" size="2" color="#FF0000"><?php echo str_replace("\n", "<br>\n", $msg) ?></font></td></tr>
            <tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>
        <?php } ?>
        <tr bgcolor="#FFFFFF"><td colspan="3"><hr></td></tr>
    </table>

<?php
    if(isset($opr_codigo) && $opr_codigo) {
?>
        <table class="table">
          <tr bgcolor="#ECE9D8"> 
            <td class="texto" align="center"><?php echo $opr_nome ?></td>
          </tr>
		  <tr>
			<td width="100%" valign="top">
			
				<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
				  <tr bgcolor="#F5F5FB"> 
					<td class="texto" align="center">Arquivos (10 últimos)</td>
				  </tr>
				<?php $arquivoAr = buscaArquivos($folder . $opr_codigo . "/", 'data', 'desc', '');?>
				<?php if(is_array($arquivoAr)) {
					array_splice($arquivoAr, 10); 
					foreach($arquivoAr as $key => $filename) {
				?>
					<tr><td>
						<a target="_blank" href="lotes_pendentes_carga_down.php?arquivo=<?php echo urlencode($filename) ?>&opr_codigo=<?php echo $opr_codigo ?>">
							<font color="#00008C" size="2" face="Arial, Helvetica, sans-serif"><?php echo $filename ?></font>
						</a>
					</td></tr>
				<?php
					}
				}
				?>
				 </table>

			</td>
		  </tr>
		</table>
		<?php
			} else {
		?>
        <table class="table">
          <tr bgcolor="#ECE9D8"> 
            <td class="texto" align="center">Escolha uma operadora</td>
		  </tr>
		</table>
		<?php
			} 
		?>
		
        <table class="table">
			<tr bgcolor="#FFFFFF"><td colspan="3"><hr></td></tr>
			<tr bgcolor="#F0F0F0" height="30">
				<td colspan="2">
					<font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
						<?php if(!isset($lc)) $lc = ""; if($lc){ ?><b>Log completo</b>
						<?php } else {?><b>Último log</b>
						<?php } ?>
					</font>
				</td>
				<td align="right">
					<font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
						<?php if($lc){ ?><a href='?opr_codigo=<?php echo $opr_codigo?>'>Último log</a>
						<?php } else {?><a href='?lc=1&opr_codigo=<?php echo $opr_codigo?>'>Log completo</a>
						<?php } ?>
					</font>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><br><?php echo str_replace($logDelimitador, "<hr>", str_replace("\n", "<br>", leLog($lc))) ?></font>
				</td>
			</tr>
			<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>
      	</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
   </td>
  </tr>
</table>
</body>
</html>
