<?php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//	set_time_limit ( 300 ) ;
//header('Content-type: image/jpeg');
        require_once "../../includes/constantes.php";
        require_once $raiz_do_projeto . "includes/jpgraph/src/jpgraph.php";
        require_once $raiz_do_projeto . "includes/jpgraph/src/jpgraph_line.php";
        require_once $raiz_do_projeto . "includes/jpgraph/src/jpgraph_date.php";
        require_once $raiz_do_projeto . "includes/functions.php";

//echo "<pre>"; 
//print_r($_GET);
//print_r($_POST);
//echo "</pre>"; 

// Recover data from post
$strenc = $_POST['strenc'];

$arr_rev = unserialize(urldecode($strenc));
/*
echo "<hr>";
//print $strenc . "<br>\n";
echo "<hr>";
//print urldecode($strenc) . "<br>\n";
echo "<hr>";
echo ((is_array($arr_rev)?"is Array":"Not Array"))."<br>";

echo "<pre>";
print_r($arr_rev);
echo "</pre>";
echo "<hr>";
die("Stop");
*/
// Create a data set in range (50,70) and X-positions
$fname = $arr_rev['fname'];
$img_path = $arr_rev['img_path'];
$chart_title = $arr_rev['chart_title'];
$monthname = $arr_rev['monthname'];
$ndatapoints = $arr_rev['npoints'];
$samplerate = $arr_rev['samplerate']; 
$data = $arr_rev['data'];
$xdata = $arr_rev['xdata'];
$ymax = $arr_rev['ymax'];
$data_ini = $arr_rev['data_ini'];
$data_fim = $arr_rev['data_fim'];


//echo "C:/Sites/E-Prepag/backoffice/web".$img_path.$fname."<br>";
//die("Stop");
$expiration = 600;	// time in seconds to delete image form server

//	$img_path = "C:/Sites/E-Prepag/backoffice/web/images/tmp/";
//echo date("Y-m-d H:i:s")."<br>";
//echo "$img_path<br>";

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


				// Create the new graph
				$graph = new Graph(540,300);

				// Slightly larger than normal margins at the bottom to have room for the x-axis labels
				$graph->SetMargin(40,10,30,50);
				//$graph->title->SetFont(FF_ARIAL,FS_NORMAL,12);

				// Fix the Y-scale to go between [0,100] and use date for the x-axis
				$graph->SetScale('datlin',0,1.1*$ymax);
				$graph->title->Set($chart_title);

				if($data_ini==$data_fim) {
					$subtitle = get_day_of_week_short($data_ini).' - '.$data_ini;
				} else {
					$subtitle = "datas ".$data_ini." - ".$data_fim;
				}
				$graph->subtitle->SetColor('darkred');
				$graph->subtitle->Set($subtitle);

				// Set the angle for the labels to 90 degrees
				$graph->xaxis->SetLabelAngle(90);
//				$graph->xaxis->title->Set("hora");
				$graph->xgrid->Show();

				$line = new LinePlot($data,$xdata);
//				$line->SetLegend($monthname);
				$line->SetFillColor('lightblue@0.5');
				$graph->Add($line);
				$graph->Stroke($raiz_do_projeto . "backoffice".$img_path.$fname);
//				$graph->Stroke();


//Fechando Conexão
pg_close($connid);

?>