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


// Create the graph. These two calls are always required
$graph = new Graph(750,350,'auto');    
$graph->SetScale("textlin");
$graph->SetY2Scale("lin");
$graph->SetShadow();

$graph->ygrid->Show(true,true);
$graph->xgrid->Show(true,false);

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

// Create the linear plot
$lineplot=new LinePlot($mm);
// Setup the colors
$lineplot->SetColor("blue");
$lineplot->SetWeight(2);
// Setup legends
$lineplot->SetLegend('20'.date("y"));
// Add the plot to the graph
$graph->Add($lineplot);

// Some data
$mm = array();
foreach ($_SESSION['graphic'][str_pad((date("y")*1-1), 2 , "0", STR_PAD_LEFT)] as $mes => $valor){ 
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

// Create the linear plot
$lineplot2=new LinePlot($mm);
// Setup the colors
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);
// Setup legends
$lineplot2->SetLegend('20'.str_pad((date("y")*1-1), 2 , "0", STR_PAD_LEFT));
// Add the plot to the graph
$graph->AddY2($lineplot2);

// Add the plot to the graph
$graph->y2axis->SetColor("orange");

// Setup graph title
$graph->title->Set($labeloperadora.' - Vendas - Valor Bruto R$');
$graph->yaxis->title->Set("Vendas");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$graph->yaxis->SetColor("blue");

// Adjust the position of the legend box
$graph->legend->Pos(0.02,0.5,"right","center");

if ($isWeek > 12) {
	if ($isWeek == 24) {
		$currentweek  = mktime(0, 0, 0, 1, 1, date('Y')+1);
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
		$currentweek= mktime(0, 0, 0, 12, 28, date('Y'));
		$firstweek  = mktime(0, 0, 0, 12, 28, date('Y')-1);
		while($currentweek >=$firstweek) {
			$array_date[$isWeek-1] = date("d/m",$currentweek);
			$currentweek = mktime(0, 0, 0, date("m", $currentweek), date("d",$currentweek)-7, date("Y",$currentweek));
			$isWeek--;
		}
	}
	$graph->xaxis->SetTickLabels($array_date);
	$graph->xaxis->SetLabelAngle(90);
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
	$graph->xaxis->title->Set("Meses");
}

$graph->img->SetMargin(50,110,20,50);

$graph->xaxis->SetTextTickInterval(2);

// Save the graph
$graph->Stroke('images/'.trim($labeloperadora).$imagem);

?>
