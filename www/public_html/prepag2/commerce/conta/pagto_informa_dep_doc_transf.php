<?php
header("Content-Type: text/html; charset=ISO-8859-1; P3P: CP='CAO PSA OUR'",true);
require_once "../../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_CLASS . "gamer/classIntegracao.php";

validaSessao();

//Recupera usuario
if(isset($_SESSION['usuarioGames_ser']) && !is_null($_SESSION['usuarioGames_ser'])){
        $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
        $usuarioId = $usuarioGames->getId();
}
$vg_integracao_parceiro_origem_id = $_SESSION['integracao_origem_id'];

require_once DIR_INCS . "gamer/venda_e_modelos_logica_epp.php";

require_once DIR_INCS . "gamer/pagto_informa_dep_doc_transf_verificacoes.php";

if($msg) $msg = $_REQUEST['msg'];

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

//echo "data1: $pagto_data_data $pagto_data_horas:$pagto_data_minutos<br>";
//Limpa arquivos temporarios da venda
$arquivos = buscaArquivosIniciaCom($FOLDER_COMMERCE_UPLOAD_TMP, 'nome', 'asc', "money_comprovante_" . $venda_id . "_");
for($j = 0; $j < count($arquivos); $j++){
        if (is_file($FOLDER_COMMERCE_UPLOAD_TMP . $arquivos[$j])) unlink($FOLDER_COMMERCE_UPLOAD_TMP . $arquivos[$j]);
}


//Processa pagto
//----------------------------------------------------------------------------------------
$btChange = $_REQUEST['btChange'];
$btSubmit = $_REQUEST['btSubmit'];

//echo "B: btChange: $btChange<br>";
//echo "B: btSubmit: $btSubmit<br>";

if($btSubmit || $btChange){

        //Variaveis do Formulario
        $pagto_banco 		= $_REQUEST['pagto_banco'];
        $pagto_local 		= $_REQUEST['pagto_local'];
        $pagto_num_docto 	= $_REQUEST['pagto_num_docto'];
        $pagto_valor_pago 	= $_REQUEST['pagto_valor_pago'];
//		$pagto_data_Dia 	= $_REQUEST['pagto_data_Dia'];
//		$pagto_data_Mes 	= $_REQUEST['pagto_data_Mes'];
//		$pagto_data_Ano 	= $_REQUEST['pagto_data_Ano'];
        $pagto_data_data 	= $_REQUEST['pagto_data_data'];
        $pagto_data_horas 	= $_REQUEST['pagto_data_horas'];
        $pagto_data_minutos	= $_REQUEST['pagto_data_minutos'];

        $pagto_data_data_full = $pagto_data_data ." ".$pagto_data_horas .":". $pagto_data_minutos;

//echo "data2: '$pagto_data_data_full'<br>";

}
//echo "B: $pagto_data_data $pagto_data_horas:$pagto_data_minutos<br>";

if($btSubmit){

        require_once DIR_INCS . "gamer/pagto_informa_dep_doc_transf_validacoes.php";

        //Valida arquivo upload - comprovante
        if($msg == ""){
                if(	($pagto_banco == "001" && $pagto_local == "06") ||
                        ($pagto_banco == "237" && $pagto_local == "06") ||
                        ($pagto_banco == "104" && $pagto_local == "06") ){

                        $fileName = $HTTP_POST_FILES['comprovante']['name'];
                        $fileSource = $HTTP_POST_FILES['comprovante']['tmp_name'];
                        $fileDest = $FOLDER_COMMERCE_UPLOAD_TMP . "money_comprovante_" . $venda_id . "_" . $pagto_banco . "_" . $pagto_local . "_" . $fileName;

                        if (($fileSource != 'none') && ($fileSource != '' )) {

                                if(strlen($fileName) > 4) $fileExtensao = strtoupper(substr(strrchr($fileName, '.'), 1));
                                if($fileExtensao != 'JPG' && $fileExtensao != 'GIF' && $fileExtensao != 'PNG'){
                                        $msg .= "Arquivo de comprovante inválido. Deve ser do tipo JPG, GIF ou PNG.\n";

                                } else if (!move_uploaded_file($fileSource, $fileDest)) {
                                        $msg = "Não foi possivel realizar o upload do comprovante, tente novamente.\n";
                                }

//				} else {
//					$msg .= "Arquivo de comprovante não fornecido.\n";
                        }

                }
        }

        if($msg == ""){
                //Poe no session
                $_SESSION['pagto.pagto_banco'] 		= $pagto_banco;
                $_SESSION['pagto.pagto_local'] 		= $pagto_local;
                $_SESSION['pagto.pagto_num_docto'] 	= $pagto_num_docto;
                $_SESSION['pagto.pagto_valor_pago'] = $pagto_valor_pago;
//			$_SESSION['pagto.pagto_data_Dia'] 	= $pagto_data_Dia;
//			$_SESSION['pagto.pagto_data_Mes'] 	= $pagto_data_Mes;
//			$_SESSION['pagto.pagto_data_Ano'] 	= $pagto_data_Ano;
                $_SESSION['pagto.pagto_data_data'] 	= $pagto_data_data;
                $_SESSION['pagto.pagto_data_horas'] 	= $pagto_data_horas;
                $_SESSION['pagto.pagto_data_minutos'] 	= $pagto_data_minutos;

                //redireciona
                $strRedirect = "/prepag2/commerce/conta/pagto_informa_dep_doc_transfConf.php";
                                                    
                //Fechando Conexão
                pg_close($connid);

                redirect($strRedirect);
        }
}

//	if(!$pagto_data_Dia) $pagto_data_Dia = date('d');
//	if(!$pagto_data_Mes) $pagto_data_Mes = date('m');
//	if(!$pagto_data_Ano) $pagto_data_Ano = date('Y');
if(!$pagto_data_data) $pagto_data_data = "";	//date('d/m/Y');	//('Y-m-d');
if(!$pagto_data_horas) $pagto_data_horas = "";
if(!$pagto_data_minutos) $pagto_data_minutos = "";

$pagto_data_data_full = $pagto_data_data ." ".$pagto_data_horas .":". $pagto_data_minutos;

//echo "data3: $pagto_data_data $pagto_data_horas:$pagto_data_minutos<br>";


$pagina_titulo = "Informa Pagamento";
?>
<script language='javascript' src='/js/popcalendar.js'></script>
<?php
$cabecalho_file = isset($GLOBALS['_SESSION']['is_integration']) && $GLOBALS['_SESSION']['is_integration'] == true ? DIR_WEB . "/prepag2/commerce/includes/cabecalho_int.php" : "/game/includes/cabecalho.php";
include $cabecalho_file;

//<link rel="stylesheet" type="text/css" media="all" href="../../../incs/jscalendar/calendar-blue2.css" title="blue2" />    <!-- calendar stylesheet -->
//<script type="text/javascript" src="../../../incs/jscalendar/calendar.js"></script>  <!-- main calendar program -->
//<script type="text/javascript" src="../../../incs/jscalendar/lang/calendar-br.js"></script>  <!-- language for the calendar -->
//<script type="text/javascript" src="../../../incs/jscalendar/calendar-setup.js"></script>    <!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->

require_once DIR_INCS . "gamer/venda_e_modelos_view_epp.php";

//Teste de integração
if($vg_integracao_parceiro_origem_id){
    // função de captura de cpf
     cpf_page($partner_list);
    /*
    // Colocar aqui o drawshadow para capturar o CPF
    // Teste de verificação se o publisher exige a captura do CPF
    if(partnerNeedCPF($vg_integracao_parceiro_origem_id) && empty($_POST['ug_show'])) {
        //Printando o drawshadow
        echo SolicitaCPF($usuarioId);
    }//end if(partnerNeedCPF($vg_integracao_parceiro_origem_id))
    // Confirmando click no Responder e CPF válido
    elseif($_POST['Submit'] == "RESPONDER" && verificaCPF_int($_POST['ug_cpf'])) {
        require($_SERVER['DOCUMENT_ROOT'].'/prepag2/incs/rf_cpf/funcoes.php');
        $txtCPF = $_POST['ug_cpf'];
        $captcha = $_POST['captcha'];
        $token = $_POST['viewstate'];

        $getHtmlCPF = getHtmlCPF($txtCPF, $captcha, $token);

        if ($getHtmlCPF) {
            $campos = parseHtmlCPF($getHtmlCPF);

            if($campos['erro']){
                echo '<script>alert("O código digitado não corresponde ao apresentado. Por favor tente novamente.")</script>';
                echo '<script>window.location.assign("'.$GLOBALS['_SERVER']['PHP_SELF'].'")</script>';
                return;
                }
            $update_name = '';
            if($campos['nome']){
                $update_name = ' ,ug_nome=\''.$campos['nome'].'\' ';
                }
        }
        $sql = "UPDATE usuarios_games
                        SET ug_cpf='". mask($_POST['ug_cpf'],'###.###.###-##')."'
                        $update_name
                        WHERE ug_id=".$usuarioId.";";
       //echo $sql."<br>";
        $rs_cpf = SQLexecuteQuery($sql);
        //Chama de novo a função para validar o nome caso seja um CPF que não está na receita
        if(!$campos['nome']){
            echo SolicitaCPF($usuarioId);
           }
    }//end elseif($_POST['Submit'] == "RESPONDER")
    elseif($_POST['Submit'] == "RESPONDER" && verificaNome($_POST['ug_nome'])) {
        $sql = "UPDATE usuarios_games
                        SET ug_nome='".pg_escape_string(trim(strtoupper($_POST['ug_nome'])))."'
                        WHERE ug_id=".$usuarioId.";";

        //echo $sql."<br>";
        $rs_cpf = SQLexecuteQuery($sql);

        }//end elseif($_POST['Submit'] == "RESPONDER")
        */
} else {
        //Testando a necessidade de solicitação de CPF para Gamer
        if($test_opr_need_cpf) {
            cpf_page_gamer();
        }//end if($test_opr_need_cpf)

        include "../includes/pagto_compr_usuario_dados.php";
}
?>
<link href="<?php echo $url;?>/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url;?>/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>/js/global.js"></script>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script>
$(function(){
    $("#pagto_data_data").datepicker();
    $("#pagto_valor_pago").mask('000.000,00', {reverse: true});
})
</script>
    <br <?php if($GLOBALS['_SESSION']['is_integration']==true) echo 'style="display: none"'; ?>>
    <form name="form1" action="" method="post" ENCTYPE="multipart/form-data">
    <input type="hidden" name="btChange" value="1">
    <input type='hidden' name='ug_show' id='ug_show' value='<?php echo $_POST['ug_show'];?>'>
    <div class="wrapper" style="border-top: 0px;">
        <table border="0" cellspacing="0" align="center" class="wrapper" style="<?php if($GLOBALS['_SESSION']['is_integration']==true) echo 'padding-right: 30px;"'; ?>">
            <tr valign="middle" bgcolor="#FFFFFF" style="color: red; ">
              	<td align="left" class="texto_vermelho"><?php echo str_replace("\n", "<br>", $msg)?></td>
            </tr>
            <tr <?php if($GLOBALS['_SESSION']['is_integration']==true) echo 'style="display: none"'; ?>>
                <td>&nbsp;</td>
            </tr>
            <tr <?php if($GLOBALS['_SESSION']['is_integration']==true) echo 'style="display: none"'; ?>>
            	<td>&nbsp;</td>
			</tr>
            <tr>
                <td>
                    <table class="fontsize-pp" <?php if($GLOBALS['_SESSION']['is_integration']==true){ echo "style='margin: auto;'"; } ?>>
                        <tr valign="middle" bgcolor="#FFFFFF">
                            <td align="left" class="texto">
                            &nbsp;Por favor, digite os dados do comprovante de pagamento.<br>
                            &nbsp;Digite os dados corretamente para que o seu pagamento seja identificado e o seu produto liberado:<br><br>
                            <table class="table fontsize-pp">
                                    <tr bgcolor="#F0F0F0">
                                        <td class="texto" colspan="2"><b>Banco onde foi feito o pagamento:</b></td>
                                    </tr>
                                    <tr bgcolor="#FAFAFA">
                                        <td class="texto" colspan="2">
                                            <select name="pagto_banco" class="form-control input-sm" OnChange="if(document.form1.pagto_local)document.form1.pagto_local.value='';document.form1.submit();">
                                                <option value="" <?php if($pagto_banco == "") echo "selected" ?>>Selecione o Banco</option>
                                                <?php foreach ($PAGTO_BANCOS as $bancoId => $bancoNome){ ?>
                                                <?php	if($bancoId != '104'){ ?>
                                                <option value="<?php echo $bancoId; ?>" <?php if ($pagto_banco == $bancoId) echo "selected";?>><?php echo $bancoNome; ?></option>
                                                <?php	} ?>
                                                <?php } ?>
                                            </select>

                                        </td>
                                    </tr>
<?php 
                                    if($pagto_banco && !is_null($pagto_banco) && $pagto_banco != "" && is_numeric($pagto_banco)){
?>
                                    <tr bgcolor="#F0F0F0">
                                        <td class="texto" colspan="2"><b>Local onde foi feito o pagamento:</b></td>
                                    </tr>
                                    <tr bgcolor="#FAFAFA">
                                        <td class="texto" colspan="2">

                                            <select name="pagto_local" class="form-control input-sm" OnChange="document.form1.submit();">
                                                <option value="" <?php if($pagto_local == "") echo "selected" ?>>Selecione o Local</option>
                                                <?php foreach ($PAGTO_LOCAIS[$pagto_banco] as $localId => $localNome){ ?>
                                                <option value="<?php echo $localId; ?>" <?php if ($pagto_local == $localId) echo "selected";?>><?php echo $localNome; ?></option>
                                                <?php } ?>
                                            </select>

                                        </td>
                                    </tr>
<?php 
                                        if($pagto_local && !is_null($pagto_local) && $pagto_local != "" && is_numeric($pagto_local)){
?>
                                    <tr bgcolor="#F0F0F0">
                                        <td class="texto">
                                            <b>Data do pagamento no comprovante:</b>
                                        </td>
                                        <td class="texto">
                                            <input name="pagto_data_data" type="text" class="form" id="pagto_data_data" value="<?php echo $pagto_data_data ?>" size="10" maxlength="10">
                                        </td>
                                    </tr>
                                    <tr bgcolor="#F0F0F0">
                                        <td class="texto">
                                            <b>Hora do pagamento no comprovante:</b>
                                        </td>
                                        <td class="texto">
                                            <select name="pagto_data_horas" style="width: 55px;" class="form-control input-sm input-sm fontsize-pp pull-left">
                                                <option value="" <?php if($pagto_data_horas == "") echo "selected" ?>>HH</option>
                                                <?php for($i=0; $i <= 23; $i++){ ?>
                                                <option value="<?php echo substr("0" . $i, -2); ?>" <?php if ($pagto_data_horas == substr("0" . $i, -2)) echo "selected";?>><?php echo substr("0" . $i, -2); ?></option>
                                                <?php } ?>
                                            </select> <span class=" pull-left">:</span>
                                            <select name="pagto_data_minutos" style="width: 55px;" class="form-control fontsize-pp input-sm pull-left">
                                                <option value="" <?php if($pagto_data_minutos == "") echo "selected" ?>>MM</option>
                                                <?php for($i=0; $i <= 59; $i++){ ?>
                                                <option value="<?php echo substr("0" . $i, -2); ?>" <?php if ($pagto_data_minutos == substr("0" . $i, -2)) echo "selected";?>><?php echo substr("0" . $i, -2); ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
<?php
                                    $pagto_nome_docto_Ar = preg_split("/;/", $PAGTO_NOME_DOCTO[$pagto_banco][$pagto_local]);
                                    for($i=0; $i<count($pagto_nome_docto_Ar); $i++){
?>
                                    <tr bgcolor="#F0F0F0">
                                        <td class="texto"><b><?php echo $pagto_nome_docto_Ar[$i]; ?>:</b></td>
                                        <td class="texto"><input name="pagto_num_docto[]" value="<?php echo htmlspecialchars($pagto_num_docto[$i], ENT_QUOTES)?>" type="text" size="20" maxlength="20" class="form-control input-sm"></td>
                                    </tr>
<?php
                                    } 

                                    if(($pagto_banco == "001" && $pagto_local == "06") ||
                                        ($pagto_banco == "237" && $pagto_local == "06") ||
                                        ($pagto_banco == "104" && $pagto_local == "06") ){
?>
                                    <tr bgcolor="#F0F0F0">
                                        <td class="texto"><b>Comprovante:</b></td>
                                        <td class="texto"><input type="file" name="comprovante" size="30"></td>
                                    </tr>
<?php 
                                    } 
?>
                                    <tr bgcolor="#F0F0F0">
                                        <td class="texto"><b>Valor Pago:</b></td>
                                        <td class="texto"><input name="pagto_valor_pago" id="pagto_valor_pago" value="<?php echo htmlspecialchars($pagto_valor_pago, ENT_QUOTES)?>" type="text" size="20" maxlength="20" class="form-control input-sm"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" align="center"><input type="submit" name="btSubmit" value="Continuar" class="btn btn-sm btn-success <?php if($GLOBALS['_SESSION']['is_integration']==true){ echo "int-btn1 grad1"; } ?>"></td>
                                    </tr>
<?php 
                                            } 
                                        } 
?>
                                </table>
                            </td>
                        </tr>
		        	</table>
              	</td>
            </tr>
        </table>
    </div>
    </form>
<?php
if(isset($GLOBALS['_SESSION']['is_integration']) && $GLOBALS['_SESSION']['is_integration'] == true) {
    echo "<script language='javascript' src='/js/popcalendar.js'></script>";
}//end if(isset($GLOBALS['_SESSION']['is_integration']) && $GLOBALS['_SESSION']['is_integration'] == true)
require_once RAIZ_DO_PROJETO . "public_html/prepag2/commerce/includes/rodape.php"; 
                                    
//Fechando Conexão
//pg_close($connid);

?>
