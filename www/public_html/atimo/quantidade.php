<?php

define('PERIODO_CONSIDERADO', 30);

//=========  Dia Final considerado no processamento
$currentmonth = mktime(0, 0, 0, date('n'), date('j')-1, date('Y'));

//=========  Dia Inicial considerado no processamento
$initialmonth = mktime(0, 0, 0, date('n'), date('j')-(PERIODO_CONSIDERADO+1), date('Y'));

var_dump(date("Y-m-d",$currentmonth));

var_dump(date("Y-m-d",$initialmonth));

?>