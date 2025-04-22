<?php
error_reporting(E_ALL); 
ini_set("display_errors", 1); 
include_once "/www/e-pay/Epay.php";

$epay = new Epay();
//var_dump($epay->catalog());  

//$prod = ["code" => "4251404514556", "shopid" => "4303", "model" => 5702723, "operator" => "159", "retailerid" => "3662186", "type_sale" => "USUARIO", "value" => 199.99, "sale" => 47568354, "name_prod" => "Ifood cash-epay"];//

var_dump($epay->writeFileSftp());
//echo "E-pay";


//var_dump($epay->sale("DIRECT", $prod));
//var_dump($epay->sale("REPRINT", $prod, "1F60A168-0B20-1E38-0425-042E4E778B66"));
//($epay->cancelSale("MANUAL", $prod["code"], $prod["value"], "30E45F5E-09CE-252B-0522-079ADE86FA25", "manual")); 

?>