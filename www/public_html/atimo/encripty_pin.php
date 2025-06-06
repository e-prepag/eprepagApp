<?php

$raiz_do_projeto = "/www/";
require_once $raiz_do_projeto."includes/gamer/chave.php";
require_once $raiz_do_projeto."includes/gamer/AES.class.php";
require_once $raiz_do_projeto."class/classEncryption.php";
require_once $raiz_do_projeto."db/connect.php";
require_once $raiz_do_projeto."db/ConnectionPDO.php";

/*
$objEncryption = new Encryption();
$senha = $objEncryption->decrypt(trim('LAMGKFRwDyE='));
var_dump($senha);
exit;
*/
//Instanciando Objetos para Descriptografia
//$chave256bits = new Chave();
//$aes = new AES($chave256bits->retornaChavePub());
//var_dump($aes->decrypt(base64_decode("JyEqRCFnAAknTCIr")));
//exit;

$chave256bits = new Chave();
$aes = new AES($chave256bits->retornaChave());
$conexao = ConnectionPDO::getConnection()->getLink();

echo base64_encode($aes->encrypt("5099675705767845"));
exit;

$sql = "select pin_codigo from pins where pin_codigo in(
'5436625455559158',
'0051067151051125',
'9586570582754243',
'6604421398005317',
'6204461031897825',
'3138787094426844',
'9250050968569632',
'3595955505635505',
'7794247225118710',
'3154187476663333',
'3078327935711041',
'1845704126995214',
'7906215354164974',
'1115676897130513',
'9736049120046107',
'5250201526397929',
'1569004559957161',
'1106790319488353',
'9982972369426931',
'0212314079208668',
'7447534665094863',
'5982775467413147',
'5065372752048542',
'4144097309246794',
'7413737628523208',
'2940985050378826',
'6948877995196228',
'9480993726416502',
'5886412875440472',
'2403962751681568',
'5917564879742369',
'8596116310776401',
'8760770214079138',
'8796928135910385',
'1104343238634328',
'5862150471720938',
'6428418848305631',
'1637729117791348'
)";
$query = $conexao->prepare($sql);
$query->execute();
$pins = $query->fetchAll(PDO::FETCH_ASSOC);

foreach($pins as $key => $value){
	$hashPin = base64_encode($aes->encrypt($value['pin_codigo']));
	echo "'".$hashPin."',<br>";
}

exit;
$sql = "select pin_codigo from pins
inner join tb_dist_venda_games_modelo_pins on vgmp_pin_codinterno = pin_codinterno
inner join tb_dist_venda_games_modelo on vgmp_vgm_id = vgm_id
inner join tb_dist_venda_games on vgm_vg_id = vg_id
where pin_status = '6' and vg_ug_id = 10992 and extract(year from vg_data_inclusao) =  '2022' order by vg_data_inclusao;";
$query = $conexao->prepare($sql);
$query->execute();
$pins = $query->fetchAll(PDO::FETCH_ASSOC);

foreach($pins as $key => $value){
	$pin = $value["pin_codigo"];
	$hashPin = base64_encode($aes->encrypt($pin));
	$pinsHashs[] = $hashPin;
}

$sqlHash = "select * from pins_store where pin_codigo in('".implode("','", $pinsHashs)."') and pin_status = 3;";
$queryHash = $conexao->prepare($sqlHash);
$queryHash->execute();
$resultadoHash = $queryHash->fetchAll(PDO::FETCH_ASSOC);

foreach($resultadoHash as $index => $value){
	$pinH = '2ynTz/WT3wu9Q/KtrYcuyQ==';
	$pinR[] = $aes->decrypt(base64_decode(trim($pinH)));
}
echo "felipe: ";
print_r($pinR); 

?>