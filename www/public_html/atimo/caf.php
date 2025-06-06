<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once "/www/class/classCAF.php";

$data_pj = [
	'templateId' => '6451848b7f4cb300084f5cb7',
	'_callbackUrl' => '',
	'attributes' => [
		'cnpj' => '44599469000164'
	]
];

$data_pf = [
	'templateId' => '645184d97f4cb300084f5cba', 
	'_callbackUrl' => '',
	'attributes' => [
		'cpf' => '06743611131'
	] 
]; 

$caf = new ClassCAF();
 
try {
	//$response = $caf->generateTransaction($data_pf);
	//$response = $caf->getAll();
	//$response = $caf->getOne("6483873eae52f70008f46b68");
	$response = $caf->generateOnboarding("PF", "andresilvay6@gmail.com", "06743611131"); 
	echo $response;
}catch(Exception $e) {
	echo "Error: " . $e->getMessage();
} 
?>  