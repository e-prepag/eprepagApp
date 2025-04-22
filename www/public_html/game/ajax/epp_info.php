<?php
date_default_timezone_set('America/Fortaleza');
/* 
 * Ajax que retorna a quantidade de epp_cash relacionada a um valor
 */

require_once "../../../includes/constantes.php";

require_once DIR_CLASS."util/Util.class.php";
require_once DIR_INCS."gamer/functions.php";
require_once DIR_CLASS."gamer/classConversionPINsEPP.php";


if(Util::isAjaxRequest()){
    echo get_info_EPPCash_NO_Table((new ConversionPINsEPP)->get_ValorEPPCash('E',$_POST["valor"]));
}


?>