<?php
// see solution 1 at http://astronomy.swin.edu.au/~pbourke/geometry/insidepoly/ 
function pointInside($p,&$points) { 
	$c	= 0; 
	$p1	= $points[0]; 
	$n	= count($points); 
	for ($i=1; $i<=$n; $i++) {
		$p2 = $points[$i % $n]; 
		if ($p->y > min($p1->y, $p2->y) 
			&& $p->y <= max($p1->y, $p2->y) 
			&& $p->x <= max($p1->x, $p2->x) 
			&& $p1->y != $p2->y) { 
				$xinters = ($p->y - $p1->y) * ($p2->x - $p1->x) / ($p2->y - $p1->y) + $p1->x; 
				if ($p1->x == $p2->x || $p->x <= $xinters) 
				$c++; 
		}
		$p1 = $p2; 
	} 
	// if the number of edges we passed through is even, then it's not in the poly. 
	return $c%2!=0; 
} 
class Point { 
	var $x; 
	var $y; 
	public function Point($x, $y) { 
		$this->x = $x; 
		$this->y = $y; 
	} 
	public function print_coords() { 
		return("(".$this->x.",".$this->y.")"); 
	} 
} 

//require_once('inc/inc_brasil_pontos_41.php');
//require_once('inc/inc_brasil_pontos_65.php');
//require_once('inc/inc_brasil_pontos_82.php');
require_once $raiz_do_projeto."includes/pdv/inc_brasil_pontos_278.php";


//echo "Define Polygon: <br>";
//foreach($polygon as $key => $val) {
	//echo "Vertex $key - ".$val->print_coords()."<br>";
//}

//echo "<hr>";
//$p_in = new Point(-22.746912,-43.3562172); 
//$p_out = new Point(-22.7183687,-43.5550429+100); 

//echo "Point ".$p_in->print_coords()." is ";
//
//if (pointInside($p_in,$polygon)) {
//	echo "INSIDE";
//} else {
//	echo "OUTSIDE";
//}
//echo "<br>";

//echo "Point ".$p_out->print_coords()." is ";
//if (pointInside($p_out,$polygon)) {
//	echo "INSIDE";
//} else {
//	echo "OUTSIDE";
//}
//echo "<br>";
?>