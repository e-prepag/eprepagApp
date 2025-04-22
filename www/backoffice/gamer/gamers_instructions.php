<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> Lista Instruções cadastradas para PINs de Gamers </title>
<link rel="stylesheet" href="/css/css.css" type="text/css">

</head>

<?php 
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/inc_instrucoes.php"; ?>

<body>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<h2>Lista Instruções cadastradas para PINs de Gamers</h2>
<?php

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

$aoperadoras = array(
		"Habbo" => array("opr_codigo" => 16, "vgm_id" => 0, "vgm_nome" => ""),
		"Stardoll" => array("opr_codigo" => 38, "vgm_id" => 0, "vgm_nome" => ""),
		"Softnyx" => array("opr_codigo" => 37, "vgm_id" => 0, "vgm_nome" => ""),
		"Vostu" => array("opr_codigo" => 35, "vgm_id" => 0, "vgm_nome" => ""),
		"Vostu_Joga_Craque" => array("opr_codigo" => 35, "vgm_id" => 0, "vgm_nome" => "Joga Craque"),
//		"Vostu_MiniFazenda" => array("opr_codigo" => 35, "vgm_id" => 0, "vgm_nome" => "MiniFazenda"),
//		"Vostu_CafeMania" => array("opr_codigo" => 35, "vgm_id" => 0, "vgm_nome" => "CaféMania"),
		"Vostu_Rede_do_Crime" => array("opr_codigo" => 35, "vgm_id" => 0, "vgm_nome" => "Rede do Crime"),
		"Brancaleone" => array("opr_codigo" => 26, "vgm_id" => 0, "vgm_nome" => ""),
		"Alawar" => array("opr_codigo" => 55, "vgm_id" => 0, "vgm_nome" => ""),
	);

foreach($aoperadoras as $key => $opr) {
	$msgEmail = get_Instructions_for_Gamer_PIN($opr['opr_codigo'], $opr['vgm_id'], $opr['vgm_nome']);
	echo "<hr><p class='texto_blue'>'$key' ".(($opr['vgm_nome']!="")?"('".$opr['vgm_nome']."')":"")."<br> - opr_codigo: ".$opr['opr_codigo']."".(($opr['vgm_nome']!="")?", vgm_nome: '".$opr['vgm_nome']."'":"")."</p><div style='background-color:#FFFF99'>";
	echo ($msgEmail!="")?$msgEmail:"VAZIO";
	echo "</div><br>";
}

?>
</body>
</html>
