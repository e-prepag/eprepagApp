<?php
require_once ('./src/jpgraph.php');
require_once ('./src/jpgraph_line.php');
require_once ("./src/jpgraph_scatter.php");

$xmin20 = ($xmin*.20)+($xmin);
$xmax20 = ($xmax*.20)+($xmax);
//$xmax20 = $xmax;

//$xmin20 = number_format(($xmin20),0,'.','');
//$xmax20 = number_format(($xmax20),0,'.','');


$oprF = explode(',',$oprcod);
for ($i = 0; $i <= count($oprF)-1; $i++) {
	$oprCod[] = ($oprF[$i]*1);
}

$oprNF = explode(',',$opernome);
for ($i = 0; $i <= count($oprF)-1; $i++) {
	$oprnome[] = ($oprNF[$i]);
}

//echo "=".$ms;
$msF = explode(',',$ms);
for ($i = 0; $i <= count($msF)-1; $i++) {
	$datax[] = ($msF[$i]);
}

$gwF = explode(',',$gw);
for ($i = 0; $i <= count($gwF)-1; $i++) {
	$datay[] = ($gwF[$i]*100);
}
//echo "<pre>".print_r($datay, true)."</pre>";

$valorcircun = 50;
$vendatotatu  = number_format(($venda_total),2,'.','');

for($i=0; $i<count($oprCod); ++$i) {
	$vendatotper  = number_format($vnd_tot[$i],2,'.','');	
	$proporcao = ceil(($vendatotper/$vendatotatu)*$valorcircun);
	if(empty($proporcao)) {
		$proporcao = 1;
	}
	$dados_para_FCallback[] = array($msF[$i],$gwF[$i],$proporcao,$cor[$oprF[$i]]);
}

//echo '<pre>'.print_r($dados_para_FCallback,true).'</pre>';
//die('stop');


// We need to create X,Y data vectors suitable for the
// library from the above raw data.
$n = count($dados_para_FCallback);
for( $i=0; $i < $n; ++$i ) {
    
//	echo $dados_para_FCallback[$i][0].'	--> '.$dados_para_FCallback[$i][1].' --> '.$dados_para_FCallback[$i][2].' --> '.$dados_para_FCallback[$i][3].'<br>';

    $datax[$i] = $dados_para_FCallback[$i][0]*100;
    $datay[$i] = $dados_para_FCallback[$i][1]*100;
 
    // Create a faster lookup array so we don't have to search
    // for the correct values in the callback function
    $format[strval(-$datax[$i])][strval($datay[$i])] = array($dados_para_FCallback[$i][2],$dados_para_FCallback[$i][3]);
}

//echo '<pre>'.print_r($datay,true).'</pre>';
//echo '<pre>'.print_r($format,true).'</pre>';
//
//die('stop');

// Callback to negate the argument
function _cb_negate($aVal) {
    //return number_format(-$aVal,2,'.','')."%";
    return number_format(-$aVal,0,'.','')."%";
}

$n = count($datax);
for($i=0; $i<$n; ++$i) {
	$datax[$i] = (-$datax[$i]); // sem o arredondamento
}
//echo '<pre>'.print_r($datax,true).'</pre>';
//die('stop');

// Callback for markers
// Must return array(width,border_color,fill_color,filename,imgscale)
// If any of the returned values are '' then the
// default value for that parameter will be used (possible empty)
function FCallback($aYVal,$aXVal) {
	global $format;
//	if($aYVal == "0.33227" && $aXVal=="-0.7279") {
//		echo "INSIDE:<hr><pre>".print_r($format, true)."</pre>";
//	}
//echo "<hr color='red'>FORMAT ($aYVal,$aXVal) ['".strval($aXVal)."', '".strval($aYVal)."'] (".$format[strval($aXVal)][strval($aYVal)][0]." - ".$format[strval($aXVal)][strval($aYVal)][1].") ";

//echo '<pre>'.print_r(array($format[strval($aXVal)][strval($aYVal)][0],'',$format[strval($aXVal)][strval($aYVal)][1],'',''), true).'</pre><hr>';
    return array($format[strval($aXVal)][strval($aYVal)][0],'',$format[strval($aXVal)][strval($aYVal)][1],'','');
		 
}

$graph = new Graph(555,430);
$graph->SetScale("linlin");
$graph->img->SetMargin(2,2,2,2);        

// Client side image map targets
$totalItens = count($gwF);

//echo '<pre>'.print_r($datay,true).'</pre>';
//die('stop');

for ($i = 0; $i <= count($gwF)-1; $i++) {
////
///
///////////////////////////////////////////////// Verificar pq não esta indo crescimento negativo no grafico qdo clica na bola
///
////
	$targ[] = "javascript:showdata($oprCod[$i],".strval($datay[$i]).",".strval($datax[$i]/100*-1).",'$oprnome[$i]',$totalItens);";
	//echo strval($datay[$i])."<br>";
	$alts[] = $oprnome[$i];
}

//echo '<pre>'.print_r($targ,true).'</pre>';
//die('stop');


// Create a new scatter plot 
$sp1 = new ScatterPlot($datay,$datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);

// Set the scatter plot image map targets
$sp1->SetCSIMTargets($targ,$alts);

// Specify the callback
$sp1->mark->SetCallbackYX("FCallback");

// Y-scale between 0 and 100
$graph->SetScale("linlin", -100,100);

//$graph->xaxis->SetPos(($ymax*1)/2);
$graph->xaxis->SetPos(0);
//$graph->yaxis->SetPos((-$xmax*1)/2);
if($chkMarketShare=='1'){
	$graph->yaxis->SetPos(-50);
}
else {
	$graph->yaxis->SetPos(-10);
}

// Use a lot of grace to get large scales since the ballon have
// size and we don't want them to collide with the X-axis
$graph->yaxis->scale->SetGrace(20,10);
$graph->xaxis->scale->SetGrace(20,10);

$graph->xaxis->SetLabelFormatCallback("_cb_negate");
$graph->xaxis->SetFont(FF_FONT1);
$graph->yaxis->SetFont(FF_FONT1);
$graph->xaxis->SetTitle('<- Market Share'); 
$graph->yaxis->SetTitle('Growth ->'); 
$graph->xaxis->SetWeight(2); 
$graph->yaxis->SetWeight(2); 
$graph->xaxis->SetColor('black'); 
$graph->yaxis->SetColor('black'); 

$graph->Add($sp1);

if (file_exists('img/bcgMap.png'))
{
   unlink('img/bcgMap.png');
}

if (file_exists('map/exibeBCG.php'))
{
   unlink('map/exibeBCG.php');
}

$graph->Stroke('img/bcgMap.png');

//$imagemap = $graph->GetHTMLImageMap('img/bcgMap.png');

$dados  = $graph->GetHTMLImageMap('img/bcgMap.png');
$dados .= '<img src="img/bcgMap.png?_jpg_csimd=1" ismap="ismap" usemap="#img/bcgMap.png" height="430" alt="bcggrp">';

for ($i = 0; $i <= count($gwF)-1; $i++) {
	$dados = str_replace('alt="'.$oprnome[$i].'"','alt="'.$oprnome[$i].'" id="plot_'.str_replace(' ','_',$oprnome[$i]).'" name="plot_'.str_replace(' ','_',$oprnome[$i]).'"',$dados);
	$dados = str_replace('title="'.$oprnome[$i].'"','',$dados);
//	$dados = str_replace('href="javascript:','href="javascript:void(0);" onClick="',$dados);
}
//$dados = str_replace('-','',$dados);


$myFile = "map/exibeBCG.php";
$fh = fopen($myFile, 'w');
fwrite($fh, $dados);
fclose($fh);

include('map/exibeBCG.php');
?>