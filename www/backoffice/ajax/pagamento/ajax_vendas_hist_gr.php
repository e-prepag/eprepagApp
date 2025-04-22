<?php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//	set_time_limit ( 300 ) ;
//header('Content-type: image/jpeg');

require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto . "includes/jpgraph/src/jpgraph.php";
require_once $raiz_do_projeto . "includes/jpgraph/src/jpgraph_bar.php";
require_once $raiz_do_projeto . "includes/jpgraph/src/jpgraph_line.php";
require_once $raiz_do_projeto . "includes/functions.php";


// Recover data from post
$strenc = $_POST['strench'];

$arr_rev = unserialize(urldecode($strenc));

$xmax = 24;

$width0 = 3;

// Create a data set in range (50,70) and X-positions
$fname = $arr_rev['fname'];
$img_path = $arr_rev['img_path'];
$chart_title = $arr_rev['chart_title'];
$ndatapoints = $arr_rev['npoints'];
$data_hora = $arr_rev['data_hora'];
$hist = $arr_rev['hist'];
$ymax = $arr_rev['ymax'];
$data_ini = $arr_rev['data_ini'];
$data_fim = $arr_rev['data_fim'];


$expiration = 600;	// time in seconds to delete image form server


	// -----------------------------------
	// Remove old images	
	// -----------------------------------			
	$i = 0;
	list($usec, $sec) = explode(" ", microtime());
	$now = ((float)$usec + (float)$sec);			
	$current_dir = @opendir($raiz_do_projeto . "backoffice".$img_path);	
        if(is_dir($current_dir)) {
            while($filename = @readdir($raiz_do_projeto . "backoffice".$img_path)) {
                    if ($filename != "." and $filename != ".." and $filename != "index.html") {
                            $name = str_replace(".png", "", $filename);		
    //echo "$i: ".$img_path.$filename." - ";
                            if ((($name + $expiration) < $now) && (strpos($filename, ".png")!==false)) {
                                    @unlink($raiz_do_projeto . "backoffice".$img_path.$filename);
    //echo " delete it";
                            }
                            $i++;
    //echo "<br>";
                    }
            }	
            @closedir($current_dir);
        }

//die("Stop");


/*
grava_log_graph(str_repeat("=", 80)."\nGraph INICIAR: ".date("Y-m-d H:i:s")." \n");
$sout = "";
foreach($dataz as $key => $val) {
$sout .= "[".$key."] -> ";
	foreach($val as $key2 => $val2) {
$sout .= "[".$key2."] - ".$val2.", ";
	}
$sout .= "\n";
}
$sout .= "\n";
grava_log_graph($sout);
*/

// Setup a basic graph
// width, height
$graph = new Graph(500,300,'auto');
//$graph->SetShadow('gray@0.4',5);
$graph->SetScale("linlin");
//$graph->img->SetAntiAliasing();

// xleft, xright, yleft, yright
$graph->img->SetMargin(40,50,40,40);        
$graph->SetShadow();

$graph->title->Set($chart_title);

// Use a lot of grace to get large scales
$graph->yaxis->scale->SetGrace(50,10);

// Use text X-scale so we can text labels on the X-axis
//$graph->SetScale("textlin");

// Y2-axis is linear
//$graph->SetY2Scale("lin");

// Make sure X-axis as at the bottom of the graph
$graph->xaxis->SetPos('min');
$graph->xaxis->SetTextTickInterval(1);
$graph->xaxis->SetTextLabelInterval(2);

$graph->yaxis->SetPos('min');
$graph->yaxis->SetTextTickInterval(1);
$graph->yaxis->SetTextLabelInterval(2);
$graph->yaxis->SetTickSide(SIDE_LEFT);
$graph->yaxis->SetColor('black','red');
$graph->yaxis->SetTitleMargin(30);


//$graph->xgrid->Show (true);


// Manual y- and x-scale
$graph->SetScale('linlin',0,$ymax,0,$xmax);


$graph->SetY2Scale('lin',0,100);
$graph->y2axis->SetTickSide(SIDE_RIGHT);
$graph->y2axis->SetColor('black','blue');
$graph->y2axis->SetLabelFormat('%3d.0%%');

// http://localhost/teste/php/jpgraph-3.0.7/docportal/chunkhtml/ch14s07.html
// Graph::SetTickDensity($aYDensity, $aXDensity)
$graph->SetTickDensity(TICKD_DENSE, TICKD_DENSE);

// Use built in font
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Create the bar plot
$b1 = new BarPlot($data_hora);
//$b1->SetLegend("Vendas por hora");
$b1->SetWidth(1.0);
$b1->SetShadow('gray@0.5');

$b1->SetFillColor("orange@0.4");
$b1->SetValuePos('top','top');	// ???
//$b1->value->SetAngle(90);
$b1->value->SetAlign('center','middle');	// ???
$b1->value->HideZero();
$b1->value->SetFormat("%d");
//$b1->value->SetFont(FF_ARIAL,FS_NORMAL,8);
$b1->value->SetColor('red@0.4');
$b1->value->Show();

// Create accumulative graph
$lplot = new LinePlot($hist);

// We want the line plot data point in the middle of the bars
$lplot->SetBarCenter();

// Use transperancy
$lplot->SetFillColor('lightblue@0.6');
$lplot->SetColor('blue@0.6');
$graph->AddY2($lplot);


// The order the plots are added determines who's ontop
$graph->Add($b1);

$graph->Stroke($raiz_do_projeto . "backoffice".$img_path.$fname);


// ================================================
function grava_log_graph($mensagem){
	global $_SERVER;
	$ARQUIVO_LOG_HTTP_REFERER = $raiz_do_projeto . "log/log_graph.txt";	

	//Arquivo
	$file = $ARQUIVO_LOG_HTTP_REFERER;

	//Mensagem
//	$mensagem = date('Y-m-d H:i:s') . " " . (($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:"Empty") . " - " . $_SERVER["REMOTE_ADDR"] . "\n";
//echo 	$mensagem;
	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 	
}

?>