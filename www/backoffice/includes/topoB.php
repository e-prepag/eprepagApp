<?php 
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

if(empty(session_id())){
    //session não está inicada
    session_start();
}

$pagina_titulo = "E-prepag - Créditos para Games";

header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once $raiz_do_projeto."includes/inc_register_globals.php";	

$url = $_SERVER['HTTPS']=="on" ? "https://" : "http://";
$url .= $_SERVER['SERVER_NAME'];

$webstring = "https://".$_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'];
require_once $raiz_do_projeto."includes/access_functions.php";
require_once $raiz_do_projeto.'includes/configIP.php';
require_once $raiz_do_projeto.'includes/configuracaoBO.php';
require_once $raiz_do_projeto."db/connect.php";
require_once $raiz_do_projeto."db/ConnectionPDO.php";
require_once $raiz_do_projeto."includes/header.php";
require_once $raiz_do_projeto."includes/security.php";
require_once $raiz_do_projeto."includes/functions.php";
require_once $raiz_do_projeto."includes/constantes.php";
require_once DIR_CLASS."business/SistemaBO.class.php";
require_once "/www/includes/bourls.php";

$time_start_stats = getmicrotime();

$abaAtual = (isset($_POST['navAba'])) ? $_POST['navAba'] : "0";
$paginaInicial = "/index.php";

$sistema = new SistemaBO(SISTEMA,$abaAtual, $paginaInicial);
$sistema->setIdUsuario($_SESSION['iduser_bko']);
$sistema->setArrIdsGruposUsuario();

if(!$sistema->validaAcessoItem()){
    die("Acesso negado");
}

$sistema->getMenuByItem();

if($sistema->menu){
    $idAba = $sistema->menu[0]->getIdAba();
}

$allAbas = $sistema->getAllAbas();

$resusr = pg_exec($connid, "select shn_nome, tipo_acesso, bko_datalogin, bko_horalogin from usuarios where id='".$_SESSION["iduser_bko"]."'");
$pgusr = pg_fetch_array($resusr);

$data_login = formata_data($pgusr['bko_datalogin'], 0);
$hora_login = $pgusr['bko_horalogin'];

$data_ultimo_acesso = formata_data($_SESSION['datalog_bko'], 0);
$hora_ultimo_acesso = $_SESSION['horalog_bko'];

$dia_ingles = date("l");

if(date('H') >= 6)$cpa="Bom dia";
if(date('H') >= 13)$cpa="Boa tarde";
if(date('H') >= 18)$cpa="Boa noite";

switch($dia_ingles) {
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

$tipo_acesso = getUsuarioTipoNome($pgusr['tipo_acesso']);

?>
<!DOCTYPE html>
<html>
    <head>
        <title>E-Prepag - Créditos para games online<?php echo ((isset($pagina_titulo))?" - ".$pagina_titulo:""); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- includes css -->
        <link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $server_url_ep; ?>/css/creditos.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $server_url_ep; ?>/css/game.css" rel="stylesheet" type="text/css" />
        <!-- includes js -->
        <script type="text/javascript" src="<?php echo $server_url_ep; ?>/js/jquery.js"></script>
        <script type="text/javascript" src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/bootstrap.min.js"></script>
    </head>
    <body>
        <style>
            .nav>li>a:focus, .nav>li>a:hover {
                text-decoration: none;
                background-color: #268fbd !important;
                color: #fff;
            }
            
            .lista{
                margin-bottom: 2px;
                padding: 10px;
            }
        </style>
        <div class="container-fluid bg-cinza-claro topo-h">
            <div class="container">
                <div class="row top20">
                    <div class="col-md-3">
                        <a href="/" class="">
                            <img src="/images/epp_logo.png" alt="E-Prepag" title="E-Prepag" name="LogoRPP" border="0" id="LogoRPP">
                        </a>
                    </div>
                    <div class="col-md-5 col-md-offset-4 text-right">
                        <div class="col-md-12">
                            <a class="btn btn-danger" href="/logout.php">Sair</a>
                        </div>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-5">
                        <div class="col-md-5 p-left8">
                            <span class="txt-azul-claro text-center"><i>Área administrativa</i></span>
                        </div>
                    </div>
                </div>
            </div>    
        </div>
        <div class="container-fluid borda-top-verde bg-info h-navbar">
            <div class="container">
<?php
            if(!isset($run_silently)) 
            {        
?>

                    <nav class="navbar navbar-default">
                        <div class="container">
                            <div class="navbar-header">
                                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                                    <span class="sr-only">Toggle navigation</span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>
                            </div>
                            <div id="navbar" class="navbar-collapse collapse">
                                <form id="navbarAbas" method="post" action="/">
                                    <ul class="nav navbar-nav">
<?php
                                if(isset($allAbas)){
                                    foreach($allAbas as $aba){

                                        $active = "";

                                        if(isset($idAba) && $idAba == $aba->getId()){
                                            $active = "active";
                                            $currentAba = $aba;
                                        }
                                        else if($abaAtual == $aba->getOrdem() && $paginaInicial == $_SERVER['SCRIPT_NAME'])
                                            $active = "active";
?>
                                        <li class="<?php echo $active;?>"><a href="#" class="muda-aba" ordem="<?php echo $aba->getOrdem(); ?>"><?php echo $aba->getDescricao();?></a></li>    
<?php
                                    }
                                }
?>
                                    </ul>
                                    <input type="hidden" id="navAba" name="navAba">
                                </form>
                            </div><!--/.nav-collapse -->
                        </div>
                    </nav>
					
					
<script>
$(function(){
    $(".muda-aba").click(function(){
        $("#navAba").val($(this).attr("ordem"));
        $("#navbarAbas").submit();
    });
});
</script>
<?php
            }
?>        
                <?php
$url = $_SERVER['REQUEST_URI'];

if (strpos($url, '/dashboard/pdv/index.php') !== false || strpos($url, '/dashboard/usuario/index.php') !== false) {
  echo '<div class="txt-vermelho col-md-12 bg-branco p-bottom40 teste">';
} else {
  echo '<div class="txt-azul-claro col-md-12 bg-branco p-bottom40">';
}
?>
