<?php

// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

//echo "<hr>".$_REQUEST."<hr><br>";
$vals_post = $_REQUEST['vals'];
if(!$vals_post) {
	echo "No data received by POST<br>";
	die("Stop");
//	echo "using default data<br>";
//	$vals_post = 'a:24:{i:0;a:2:{s:7:"nvendas";i:0;s:9:"nusuarios";i:42;}i:1;a:2:{s:7:"nvendas";s:1:"1";s:9:"nusuarios";i:9;}i:2;a:2:{s:7:"nvendas";s:1:"2";s:9:"nusuarios";i:8;}i:3;a:2:{s:7:"nvendas";s:1:"3";s:9:"nusuarios";i:4;}i:4;a:2:{s:7:"nvendas";s:1:"4";s:9:"nusuarios";i:5;}i:5;a:2:{s:7:"nvendas";s:1:"5";s:9:"nusuarios";i:1;}i:6;a:2:{s:7:"nvendas";s:1:"6";s:9:"nusuarios";i:2;}i:7;a:2:{s:7:"nvendas";s:1:"7";s:9:"nusuarios";i:2;}i:8;a:2:{s:7:"nvendas";s:1:"9";s:9:"nusuarios";i:1;}i:9;a:2:{s:7:"nvendas";s:2:"12";s:9:"nusuarios";i:1;}i:10;a:2:{s:7:"nvendas";s:2:"13";s:9:"nusuarios";i:1;}i:11;a:2:{s:7:"nvendas";s:2:"15";s:9:"nusuarios";i:2;}i:12;a:2:{s:7:"nvendas";s:2:"18";s:9:"nusuarios";i:1;}i:13;a:2:{s:7:"nvendas";s:2:"20";s:9:"nusuarios";i:1;}i:14;a:2:{s:7:"nvendas";s:2:"25";s:9:"nusuarios";i:1;}i:15;a:2:{s:7:"nvendas";s:2:"27";s:9:"nusuarios";i:1;}i:16;a:2:{s:7:"nvendas";s:2:"33";s:9:"nusuarios";i:1;}i:17;a:2:{s:7:"nvendas";s:2:"34";s:9:"nusuarios";i:1;}i:18;a:2:{s:7:"nvendas";s:2:"38";s:9:"nusuarios";i:1;}i:19;a:2:{s:7:"nvendas";s:2:"39";s:9:"nusuarios";i:1;}i:20;a:2:{s:7:"nvendas";s:2:"41";s:9:"nusuarios";i:1;}i:21;a:2:{s:7:"nvendas";s:2:"50";s:9:"nusuarios";i:1;}i:22;a:2:{s:7:"nvendas";s:3:"106";s:9:"nusuarios";i:1;}i:23;a:2:{s:7:"nvendas";s:3:"227";s:9:"nusuarios";i:1;}}';
} else {
//	echo "Reading data from POST<br>";
}

$vals = unserialize($vals_post);

$graphValues = "";
$graphLabels = "";
//echo "<hr>IN ajax :<br>";

foreach($vals as $key => $val) {
//echo "<pre>".print_r($val, true)."</pre>\n";
	$graphValues .= ((strlen($graphValues))?",":"").$val['nusuarios'];
	$graphLabels .= ((strlen($graphLabels))?",":"").$val['nvendas'];
}

//echo "<hr>graphValues: '".$graphValues. "'<br>";
//echo "<hr>graphLabels: '".$graphLabels. "'<br>";

//die("Stop");
  // get form data
//  if(count($_REQUEST)) foreach($_REQUEST as $name => $val) eval('$' . $name . ' = "' . $val . '";');

  // initialize values
    $graphType = 'hBar';
    $graphShowValues = 1;
//    $graphValues = '123,456,789,987,654,321';
//    $graphLabels = 'Horses,Dogs,Cats,Birds,Pigs,Cows';
    $graphBarWidth = 10;
    $graphBarLength = '1.0';
    $graphLabelSize = 10;
    $graphValuesSize = 10;
    $graphPercSize = 10;
    $graphPadding = 5;
    $graphBGColor = '#ABCDEF';
    $graphBorder = '1px solid blue';
    $graphBarColor = '#A0C0F0';
    $graphBarBGColor = '#E0F0FF';
    $graphBarBorder = '1px outset white';
    $graphLabelColor = '#000000';
    $graphLabelBGColor = '#C0E0FF';
    $graphLabelBorder = '1px groove white';
    $graphValuesColor = '#000000';
    $graphValuesBGColor = '#FFFFFF';
    $graphValuesBorder = '1px groove white';
	$percValuesDecimals = 2;

  if($graphValues) {
    require_once "../../../includes/graphs.inc.php";
    $graph = new BAR_GRAPH($graphType);
    $graph->values = $graphValues;
    $graph->labels = $graphLabels;
    $graph->showValues = $graphShowValues;
    $graph->barWidth = $graphBarWidth;
    $graph->barLength = $graphBarLength;
    $graph->labelSize = $graphLabelSize;
    $graph->absValuesSize = $graphValuesSize;
    $graph->percValuesSize = $graphPercSize;
    $graph->graphPadding = $graphPadding;
    $graph->graphBGColor = $graphBGColor;
    $graph->graphBorder = $graphBorder;
    $graph->barColors = $graphBarColor;
    $graph->barBGColor = $graphBarBGColor;
    $graph->barBorder = $graphBarBorder;
    $graph->labelColor = $graphLabelColor;
    $graph->labelBGColor = $graphLabelBGColor;
    $graph->labelBorder = $graphLabelBorder;
    $graph->absValuesColor = $graphValuesColor;
    $graph->absValuesBGColor = $graphValuesBGColor;
    $graph->absValuesBorder = $graphValuesBorder;
    $graph->percValuesDecimals = $percValuesDecimals;
   echo $graph->create();
  }
  else echo '<h4>No values fro graph!</h4>';

?>


