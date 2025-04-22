<?php
session_start();
$imagem			= isset($_POST['imagem'])		? utf8_decode($_POST['imagem'])	: null;
$labeloperadora	= isset($_POST['labeloperadora'])? $_POST['labeloperadora']		: null;
$language		= isset($_POST['language'])		? $_POST['language']			: null;

unlink('images/'.trim($labeloperadora).$imagem);

// jpgraph na produção
include ("../stats/graph/src/jpgraph.php");
include ("../stats/graph/src/jpgraph_line.php");

//include ("../jpgraph/src/jpgraph.php");
//include ("../jpgraph/src/jpgraph_line.php");

$graph = new Graph(750,350,'auto');    
$graph->SetScale("textlin");
$graph->img->SetAntiAliasing();
$graph->SetShadow();

$graph->title->Set($labeloperadora.' - Vendas - Valor Bruto R$');
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Add 10% grace to top and bottom of plot
$graph->yscale->SetGrace(10,10);

//verificador de semanal
$isWeek = 12;

// Some data
$mm = array();
foreach ($_SESSION['graphic'][date("y")] as $mes => $valor){ 
	ksort($valor);
	if (!is_array($valor)) {
		$mm[$mes*1-1] = $valor*1;
	}
	else {
		foreach ($valor as $dia => $valor2){
			$mm[] = $valor2*1;
		}
	}
	
	//altera teste de semanal
	if (count($mm) > $isWeek) {
		$isWeek=count($mm);
	}
}

if ($isWeek > 12) {
	if ($isWeek == 24) {
		if (date('j')>15) {
			$currentweek  = mktime(0, 0, 0, date('n'), 15, date('Y'));
		}
		else {
			$currentweek  = mktime(0, 0, 0, date('n'), 1, date('Y'));
		}
		$firstweek  = mktime(0, 0, 0, 1, 1, date('Y'));
		while($currentweek >=$firstweek) {
			$array_date[$isWeek-1] = date("d/m",$currentweek);
			if (date("d",$currentweek) == 15){
				$currentweek = mktime(0, 0, 0, date("m", $currentweek), 1, date("Y",$currentweek)); 
			}
			else {
				$currentweek = mktime(0, 0, 0, date("m", $currentweek)-1, 15, date("Y",$currentweek));
			}
			$isWeek--;
		}
	}
	else {
		$currentweek= mktime(0, 0, 0, date('n'), date('j'), date('Y')) - ((date('N')-1)*3600*24);
		$firstweek  = mktime(0, 0, 0, 1, 1, date('Y'));
		while($currentweek >=$firstweek) {
			$array_date[$isWeek-1] = date("d/m",$currentweek);
			$currentweek = mktime(0, 0, 0, date("m", $currentweek), date("d",$currentweek)-7, date("Y",$currentweek));
			$isWeek--;
		}
	}
	$graph->xaxis->SetTickLabels($array_date);
	$graph->xaxis->SetLabelAngle(90);
	$graph->img->SetMargin(55,15,30,50);
}
else {
	if ($language!="EN") {
		// Set new locale for Portuguese BR
		$loc_br = setlocale(LC_ALL, 'pt_BR');

		$dateLocale = new DateLocale();
		// Use Brasil locale
		$dateLocale->Set($loc_br);
		// Setup graph title
		$graph->title->Set($labeloperadora.' - Vendas - Valor Bruto R$');
	}
	else {
		$dateLocale = new DateLocale();
		// Setup graph title
		$graph->title->Set($labeloperadora.' - Sales - Gross R$');
	}
	// Get localised version of the month names
	//$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
	$graph->xaxis->SetTickLabels($dateLocale->GetShortMonth());
	$graph->img->SetMargin(55,15,10,30);
}

// Create the three var series we will combine
$p1 = new LinePlot($mm);
$p1->mark->SetType(MARK_FILLEDCIRCLE);
$p1->mark->SetFillColor("red");
$p1->mark->SetWidth(4);
$p1->SetColor("darkred");
$p1->SetCenter();
$graph->Add($p1);

$graph->Stroke('images/'.trim($labeloperadora).$imagem);
?>
