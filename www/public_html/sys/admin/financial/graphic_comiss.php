<?php
session_start();
$imagem			= isset($_POST['imagem'])		? utf8_decode($_POST['imagem'])	: null;
$labeloperadora	= isset($_POST['labeloperadora'])? $_POST['labeloperadora']		: null;
$language		= isset($_POST['language'])		? $_POST['language']			: null;

unlink('images/'.trim($labeloperadora).$imagem);

// jpgraph na produção
include ("../stats/graph/src/jpgraph.php");
include ("../stats/graph/src/jpgraph_bar.php");

//include ("../jpgraph/src/jpgraph.php");
//include ("../jpgraph/src/jpgraph_bar.php");

// Create the basic graph
$graph = new Graph(750,350,'auto');    
$graph->SetScale("textlin");

// Adjust the color for theshadow of the legend
$graph->legend->SetShadow('darkgray@0.5');
$graph->legend->SetFillColor('white@0.3');

// Adjust the position of the legend box
$graph->legend->Pos(0.3,0.91); 
$graph->legend->SetColumns(count($_SESSION['graphic'])); 
//$graph->legend->SetReverse(); 

//contador subvetor bplot
$i =0;
$colors = array (
			0 => 'red',
			1 => 'orange',
			2 => 'yellow',
			3 => 'green',
			);

//verificador de semanal e quinzenal
$isWeek = 12;

//ordenando a session graphic
ksort($_SESSION['graphic']);
foreach ($_SESSION['graphic'] as $year => $m){ 
	// Some data
	$mm = array();
	ksort($m);
	foreach ($m as $mes => $valor){
		if (!is_array($valor)) {
			$mm[$mes*1-1] = $valor*1;
		}
		else {
			ksort($valor);
			foreach ($valor as $dia => $valor2){
				$mm[] = $valor2*1;
			}
		}
		
	}
	//altera teste de semanal
	if (count($mm) > $isWeek) {
		$isWeek=count($mm);
	}
	//ordenando
	ksort($mm);
//print_r($mm);
//die();
	// Create the three var series we will combine
	$bplot[$i] = new BarPlot($mm);
	// Setup each bar with a shadow of 50% transparency
	$bplot[$i] ->SetShadow('black@0.5');
	// Setup the colors with 40% transparency (alpha channel)
	$bplot[$i] ->SetFillColor($colors[$i].'@0.5');
	// Setup legends
	$bplot[$i] ->SetLegend('20'.$year);
	//$bplot[$i] ->value ->SetMargin(150); 
	$i++;
}

//echo $isWeek;
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
		$currentweek  = mktime(0, 0, 0, 1, 4, date('Y')+1);
		$firstweek  = mktime(0, 0, 0, 1, 1, date('Y'));
		//$currentweek  = mktime(0, 0, 0, 12, 28, date('Y'));
		//$firstweek  = mktime(0, 0, 0, 12, 28, date('Y')-1);
		while($currentweek >=$firstweek) {
			$array_date[$isWeek-1] = date("d/m",$currentweek);
			$currentweek = mktime(0, 0, 0, date("m", $currentweek), date("d",$currentweek)-7, date("Y",$currentweek));
			$isWeek--;
		}
	}
	$graph->xaxis->SetTickLabels($array_date);
	$graph->xaxis->SetLabelAngle(90);
	$graph->img->SetMargin(60,15,10,80);
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

	$graph->img->SetMargin(60,15,10,60);
}

// Some extra margin (from the top)
$graph->title->SetMargin(3);
//$graph->title->SetFont(FF_ARIAL,FS_NORMAL,12);

//echo "-".$isWeek;


// Set axis titles and fonts
//$graph->xaxis->title->Set('Year 2002');
//$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
//$graph->xaxis->title->SetColor('white');

$graph->yaxis->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->SetColor('black');
//$graph->yaxis->SetTickLabels();
//$graph->yaxis->SetTicklabelmargin(50); 

//$graph->ygrid->Show(false);
$graph->ygrid->SetColor('gray@0.5');

$gbarplot = new GroupBarPlot($bplot);
$gbarplot->SetWidth(0.8);
$graph->Add($gbarplot);

//$graph->Stroke();
$graph->Stroke('images/'.trim($labeloperadora).$imagem);
?>
