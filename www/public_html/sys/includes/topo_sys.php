<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <header>
        <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="/js/jquery.js"></script>
        <link rel="stylesheet" href="/sys/css/newcss.css" type="text/css">
<?php
//error_reporting(E_ALL & ~E_NOTICE);

session_start();

if($_SERVER['HTTPS']!="on") {
    Header("Location: https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
    die();
} //end if($_SERVER['HTTPS']!="on") 

require_once $raiz_do_projeto . "includes/inc_register_globals.php";		

if(empty($_SESSION["iduser_bko_pub"]))
{

        header("Location: /sys/admin/index.php");

        exit;
}
if(($_SESSION["tipo_acesso_pub"]!='AD') && ($_SESSION["tipo_acesso_pub"]!='DT') && ($_SESSION["tipo_acesso_pub"]!='SV') && ($_SESSION["tipo_acesso_pub"]!='AT') && ($_SESSION["tipo_acesso_pub"]!='PU') ){
        header("Location: /sys/admin/index.php");
        exit;
}
?>
<script language="JavaScript">
<!--
	function changeLang(langName) {
		document.form_lang.nome.value = langName;
		document.form_lang.submit();
	}
-->
</script>
<?php 
$webstring = "http".(($_SERVER['HTTPS']=='on') ? 's' : '')."://".$_SERVER['SERVER_NAME'];// . ":" . $_SERVER['SERVER_PORT'];
$raiz_do_sys = $raiz_do_projeto . "public_html/sys/";

if($_SERVER['SCRIPT_NAME'] <> "/sys/admin/pins/situacao_pin.php") {       
    require_once $raiz_do_sys . "includes/functions.php";
} //end if($_SERVER['SCRIPT_NAME'] <> "/sys/admin/pins/situacao_pin.php")
else {
    require_once $raiz_do_sys . "admin/stats/functions.php";
}//end else do if($_SERVER['SCRIPT_NAME'] <> "/sys/admin/pins/situacao_pin.php")
require_once $raiz_do_projeto.'includes/configIP.php';
require_once $raiz_do_sys . "includes/configuracao.php";
require_once $raiz_do_projeto."db/connect.php";
require_once $raiz_do_projeto."db/ConnectionPDO.php";
require_once $raiz_do_sys . "includes/header.php";
require_once $raiz_do_sys . "includes/security.php";
require_once $raiz_do_sys . "includes/languages.php";
require_once $raiz_do_projeto."class/classDescriptionReport.php";
require_once $raiz_do_projeto."class/business/SistemaBO.class.php";

$time_start_stats = getmicrotime();

$abaAtual = "0";
$paginaInicial = "/sys/admin/commerce/index.php";
$sistema = new SistemaBO(SISTEMA,$abaAtual, $paginaInicial);
$sistema->setIdUsuario($_SESSION['iduser_bko_pub']);
$sistema->setArrIdsGruposUsuario();

if(!$sistema->validaAcessoItem()){
    die('<div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" style="top:0px;"></span>
            <span class="sr-only">Error:</span>
            Acesso negado!
          </div');
}

header('Content-Type: text/html; charset='. LANG_CHARSET);

$resusr = pg_exec($connid, "select shn_nome, tipo_acesso, bko_datalogin, bko_horalogin from usuarios where id='".$_SESSION["iduser_bko_pub"]."'");
$pgusr = pg_fetch_array($resusr);

$data_login = formata_data($pgusr['bko_datalogin'], 0);
$hora_login = $pgusr['bko_horalogin'];

$data_ultimo_acesso = formata_data($_SESSION['datalog_bko'], 0);
$hora_ultimo_acesso = $_SESSION['horalog_bko'];

$dia_ingles = date("l");

if(date('H') >= 6)$cpa="Bom dia";
if(date('H') >= 13)$cpa="Boa tarde";
if(date('H') >= 18)$cpa="Boa noite";
        //echo $cpa;				
switch($dia_ingles) 
{
        case "Monday":
                $dia_semana = "Segunda-Feira"; break;
        case "Tuesday":
                $dia_semana = "Terça-Feira"; break;
        case "Wednesday":
                $dia_semana = "Quarta-Feira"; break;
        case "Thursday":
                $dia_semana = "Quinta-Feira"; break;
        case "Friday":
                $dia_semana = "Sexta-Feira"; break;
        case "Saturday":
                $dia_semana = "Sábado"; break;
        case "Sunday":
                $dia_semana = "Domingo"; break;
}

switch($pgusr['tipo_acesso']) 
{
        case "AD":
                $tipo_acesso_var = $pgusr['tipo_acesso'];
                $tipo_acesso = "ADMINISTRADOR"; break;
        case "DT":
                $tipo_acesso_var = $pgusr['tipo_acesso'];
                $tipo_acesso = "DIRETORIA"; break;
        case "SV":
                $tipo_acesso_var = $pgusr['tipo_acesso'];
                $tipo_acesso = "SUPERVISÃO"; break;
        case "AT":
                $tipo_acesso_var = $pgusr['tipo_acesso'];
                $tipo_acesso = "ATENDENTE"; break;
}
?>
 <link href="/sys/css/css.css" rel="stylesheet" type="text/css">
 </header>
<body>
 <style>
     .top20{margin-top:20px;}
     .altura-topo{height: 100px;}
 </style>
<div class="container ptb-30">
    
    <div class="row top20 text-center">
        
        <div class="col-md-3">
            <a href="<?php echo $webstring?>/sys/admin/frameset.php">
                <img src="/sys/imagens/epp_logo.gif" alt="<?php echo LANG_EPP_ADMIN_SITE_NAME?>" name="LogoRPP" border="0" id="LogoRPP">
            </a>
        </div>
        
        <div class="col-md-6 text-primary top10">
            <?php echo LANG_HOME_GREETING?> <strong><?php echo $_SESSION["nome_bko"]?> </strong>
            <?php if(substr($_SESSION['tipo_acesso_pub'],0,2)=='PU')  { ?>
                (Opr:<font color="#0000FF"><?php echo $_SESSION["opr_nome"]?></font> ID:<?php echo $_SESSION["opr_codigo_pub"]?>)
            <?php } else {	?>
            (<?php echo $_SESSION['tipo_acesso_pub']?>)
            <?php } ?>
        </div>
        
        <div class="col-md-3">
            <div class="row">
                <div class="col-md-12">
                    <form method="post" name="form_lang">
                        <input type="hidden" name="nome" value="<?php echo $_SESSION['langNome']; ?>" />
                    </form>
                    <img src="<?php echo $webstring?>/sys/imagens/flg/flag_brasil.gif" width="29" height="18" border="0" title="Português - Brasil" onClick="changeLang('pt');">&nbsp;
                    <img src="<?php echo $webstring?>/sys/imagens/flg/flag_uk.gif" width="29" height="18" border="0" title="English" onClick="changeLang('en');">&nbsp;
                    <img src="<?php echo $webstring?>/sys/imagens/flg/flag_corea.gif" width="29" height="18" border="0" title="Korean" onClick="changeLang('ko');"></nobr>
                </div>
            </div>
            <div class="row top10">
                <div class="col-md-12>"
                    <font color="#999999" size="1" face="Arial, Helvetica, sans-serif"><strong> 
                    <a href="<?php echo $webstring?>/sys/admin/commerce/index.php"><?php echo LANG_HOME_PAGE_TITLE?></a> 
                |   <font color="#666666"> <a href="<?php echo $webstring?>/sys/admin/logout.php"><?php echo LANG_HOME_QUIT?></a></strong></font>
                </div>
            </div>
        </div>
        
    </div>
        
    
</div>
 <center>
<?php 

// mudança devido novo relatorio de chargeback
if($_SERVER["PHP_SELF"] != '/sys/admin/chargeback/chargeback.php'){
    session_write_close();
}

function b_IsSysAdminFinancial(){
	$usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'FABNASCI', 'WAGNER','KATIA');
	$aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
	if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
		return true;
	}
	return false;
}

function b_IsSysAdminRIOT(){
	$usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'FABNASCI', 'WAGNER', 'USER_RIOT', 'JOAO');
	$aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
	if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
		return true;
	}
	return false;
}

function b_IsSysAdminBCG(){
	$usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'FABNASCI', 'WAGNER', 'JOAO');
	$aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
	if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos) && ($GLOBALS['_SESSION']['tipo_acesso_pub']!='PU')) {
		return true;
	}
	return false;
}

function b_IsSysAdminStatistics_Abas(){
	$usuarios_BKO_AdminPINsArquivos = array('GLAUCIA', 'FABNASCI', 'WAGNER');
	$aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
	if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos) && ($GLOBALS['_SESSION']['tipo_acesso_pub']!='PU')) {
		return true;
	}
	return false;
}

function b_IsSysAdminJogos(){
	$usuarios_BKO_AdminJogos = array('GLAUCIA', 'FABNASCI', 'WAGNER', 'JOAO','DSLC');
	$aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
	if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminJogos)) {
		return true;
	}
	return false;
}

function b_IsUsuarioWagner(){
        $stmp = $GLOBALS['_SESSION']['userlogin_bko'];
        if(strtoupper($stmp)=="WAGNER") {
                return true;
        }
        return false;

}

function b_IsJustGraphFinantial(){
	$usuarios_BKO_AdminPINsArquivos = array('TAMY', 'JOAO');
	$aux_stmp = $GLOBALS['_SESSION']['userlogin_bko'];
	if(in_array(strtoupper($aux_stmp), $usuarios_BKO_AdminPINsArquivos)) {
		return true;
	}
	return false;
}

function b_IsSysAdminONGAME() {
	if ( $GLOBALS['_SESSION']['opr_nome']=="ONGAME") {
		return true;
	}
	return false;
}
?>
