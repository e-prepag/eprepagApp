<?php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//	set_time_limit ( 300 ) ;
//header('Content-type: image/jpeg');

require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto . "includes/jpgraph/src/jpgraph.php";
require_once $raiz_do_projeto . "includes/jpgraph/src/jpgraph_scatter.php";
require_once $raiz_do_projeto . "includes/jpgraph/src/jpgraph_plotband.php";
require_once $raiz_do_projeto . "includes/functions.php";

//$time_start_stats = getmicrotime();

//echo "<pre>"; 
//print_r($_GET);
//print_r($_POST);
//echo "</pre>"; 

// Recover data from post
$strenc = $_POST['strenc'];

$arr_rev = unserialize(urldecode($strenc));

$xmax = 60;
$ymax = 24;

$width0 = 3;

// Create a data set in range (50,70) and X-positions
$fname = $arr_rev['fname'];
$img_path = $arr_rev['img_path'];
$chart_title = $arr_rev['chart_title'];
$ndatapoints = $arr_rev['npoints'];
$datax = $arr_rev['datax'];
$datay = $arr_rev['datay'];
$dataz = $arr_rev['dataz'];
$zmax = $arr_rev['zmax'];
$data_ini = $arr_rev['data_ini'];
$data_fim = $arr_rev['data_fim'];

$n_xpoints = $n_ypoints = $n_zpoints = $ndatapoints;

/*
$datax = array();
$datay = array();
$dataz = array();
for($i=0;$i<$n_zpoints;$i++) {
	$i_x = rand(0,$xmax);
	$i_y = rand(0,$ymax);
	$datax[$i] = $i_x;
	$datay[$i] = $i_y;
	$dataz[$i_x][$i_y] = rand(1,$zmax); 
}
*/
/*
// Dummy debug
echo "dataX<br>";
foreach($datax as $key => $val) {
	echo "(".$key." - ".$val."), ";
}
echo "<br>";
echo "dataY<br>";
foreach($datay as $key => $val) {
	echo "(".$key." - ".$val."), ";
}
echo "<br>";

echo "dataZ<br>";
foreach($dataz as $key => $val) {
echo "[".$key."] => ";
	foreach($val as $key2 => $val2) {
echo "[".$key2."] - ".$val2.", ";
	}
echo "<br>";
}
die("Stop");
*/


$expiration = 600;	// time in seconds to delete image form server


	// -----------------------------------
	// Remove old images	
	// -----------------------------------			
	$i = 0;
	list($usec, $sec) = explode(" ", microtime());
	$now = ((float)$usec + (float)$sec);			
	$current_dir = @opendir($raiz_do_projeto . "backoffice".$img_path);
        if(is_dir($raiz_do_projeto . "backoffice".$img_path)) {
            while($filename = @readdir($current_dir)) {
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

//	list($width,$color,$fcolor,$filename,$imgscale) = call_user_func($f,$this->yvalue,$this->xvalue);
function FCallback2D($yval, $xval) {
	global $dataz, $zmax, $width0;
	$zval = strval($dataz[strval($xval)][strval($yval)]);

//	$zval = rand(1,30);
    if( $zval < ($zmax/3) ) $c = "blue";
	    elseif( $zval < (2*$zmax/3) ) $c = "green";
		else $c="red";
//grava_log_graph("$xval, $yval -> zval: $zval, c: '$c'\n");

    return array($width0,$c,$c,"", 1);	
}

// Setup a basic graph
// width, height
$graph = new Graph(500,300,'auto');
$graph->SetScale("linlin");
$graph->img->SetAntiAliasing();

// xleft, xright, yleft, yright
$graph->img->SetMargin(40,50,40,40);        
$graph->SetShadow();

$graph->title->Set($chart_title);

// Use a lot of grace to get large scales
$graph->yaxis->scale->SetGrace(50,10);

// Make sure X-axis as at the bottom of the graph
$graph->xaxis->SetPos('min');
$graph->xaxis->SetTextTickInterval(1);
$graph->xaxis->SetTextLabelInterval(2);

$graph->yaxis->SetPos('min');
$graph->yaxis->SetTextTickInterval(1);
$graph->yaxis->SetTextLabelInterval(2);

$graph->xgrid->SetLineStyle('solid');
$graph->xgrid->SetColor('gray');
$graph->xgrid->SetWeight(1);
$graph->xgrid->Show(true,true);
//	$graph->ygrid->Show(true, true); //set by default

// Manual y- and x-scale
$graph->SetScale('linlin',0,$ymax,0,$xmax);

// http://localhost/teste/php/jpgraph-3.0.7/docportal/chunkhtml/ch14s07.html
// Graph::SetTickDensity($aYDensity, $aXDensity)
$graph->SetTickDensity(TICKD_DENSE, TICKD_DENSE);


$graph->xaxis->SetTitle("minuto", "center");
$graph->yaxis->SetTitle("hora", "middle");

// Create the scatter plot
$sp1 = new ScatterPlot($datay,$datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);	// MARK_FILLEDCIRCLE

// http://localhost/teste/php/jpgraph-3.0.7/docportal/chunkhtml/ch15.html#fig.example3.4
// Uncomment the following two lines to display the values
//$sp1->value->Show();
//$sp1->value->SetFont(FF_FONT1,FS_BOLD);

// Specify the callback
//$sp1->mark->SetCallback("FCallback");
$sp1->mark->SetCallbackYX("FCallback2D");

// Setup the legend for plot
//$sp1->SetLegend('Year 2002');

// Add the scatter plot to the graph
$graph->Add($sp1);

// http://localhost/teste/php/jpgraph-3.0.7/docportal/chunkhtml/ch14s08.html
// Add a horizontal band
$band = new PlotBand(HORIZONTAL,BAND_DIAGCROSS,8,18,'gray');
$band->ShowFrame(false); // No border around the plot band
$graph->Add($band);
				
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