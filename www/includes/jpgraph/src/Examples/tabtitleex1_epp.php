<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');

$min = 10;
$max = 20;
$datay1 = array(rand($min, $max),rand($min, $max),rand($min, $max),rand($min, $max));
$datay2 = array(rand($min, $max),rand($min, $max),rand($min, $max),rand($min, $max));
$datay3 = array(rand($min, $max),rand($min, $max),rand($min, $max),rand($min, $max));

$sout = "";
for($i=0;$i<count($datay1);$i++) {
	$datayt[$i] = $datay1[$i] + $datay2[$i] + $datay3[$i];
$sout .= $i.": ".$datay1[$i].", ".$datay2[$i].", ".$datay3[$i]." => ".$datayt[$i]."\n";
}


// Setup the graph
$graph = new Graph(800,600);
$graph->SetMarginColor('white');
$graph->SetScale("textlin");
$graph->SetFrame(false);
$graph->SetMargin(30,50,30,30);

$graph->tabtitle->Set(' Year '.date("Y").' ' );
$graph->tabtitle->SetFont(FF_ARIAL,FS_BOLD,13);

$graph->yaxis->HideZeroLabel();
$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');
$graph->xgrid->Show();

$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());

// Create the first line
$p1 = new LinePlot($datay1);
$p1->SetColor("navy");
$p1->SetLegend('Ongame');
$graph->Add($p1);

// Create the second line
$p2 = new LinePlot($datay2);
$p2->SetColor("red");
$p2->SetLegend('Habbo Hotel');
$graph->Add($p2);

// Create the third line
$p3 = new LinePlot($datay3);
$p3->SetColor("orange");
$p3->SetLegend('Softnyx');
$graph->Add($p3);

// Create the total line
$p4 = new LinePlot($datayt);
$p4->SetColor("green");
$p4->SetLegend('Total');
$graph->Add($p4);

$graph->legend->SetShadow('gray@0.4',5);
$graph->legend->SetPos(0.1,0.1,'right','top');

$t1 = new Text($sout);
$t1->SetPos(0.05,0.5);
$t1->SetOrientation("h");
$t1->SetFont(FF_FONT1,FS_NORMAL);
$t1->SetBox("white","black",'gray');
$t1->SetColor("black");
$graph->AddText($t1);

// Output line
$graph->Stroke();

?>


