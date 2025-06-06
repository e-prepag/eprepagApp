<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
$connection = ConnectionPDO::getConnection()->getLink(); 

    $cnpj = "19037276000172";
    $curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => "https://www.receitaws.com.br/v1/cnpj/".$cnpj,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CUSTOMREQUEST => "GET"
	]);
	$retorno = json_decode(curl_exec($curl), true);
	$info = curl_getinfo($curl);
	curl_close($curl);
	
	$sql = "select identificacao_cnae, atividade_cnae from cnae where aprovado_cnae = '1';";
	$query = $connection->prepare($sql);
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC); 
	
	$identificacoesLiberadas = array_column($result, "identificacao_cnae"); 
	$atividades = array_merge($retorno["atividade_principal"], $retorno["atividades_secundarias"]);
    $identificacoesParceiro = str_replace([".", "-"], "", array_column($atividades, "code"));
	$validos = array_filter($identificacoesParceiro, function($item){
		global $identificacoesLiberadas;
		if(in_array($item, $identificacoesLiberadas)){
			return $item;
		}
	});

    var_dump(($validos > 0));


/*
$file = fopen("./cnae.csv", "r");
while(!feof($file)){
	$linha = trim(fgets($file));
	$conteudoLinha = explode("   ",$linha);
	if($conteudoLinha[0] != ""){
		
		$sql = "insert into cnae(identificacao_cnae,atividade_cnae,aprovado_cnae)values(:IDEN,:ATIVI,0)";
	    //print_r($conteudoLinha);
		echo "AC ".$conteudoLinha[0]." - ".utf8_encode($conteudoLinha[1])."<br>";
		$query = $connection->prepare($sql);
		$query->bindValue(":IDEN", str_replace(["-", "/"], "", $conteudoLinha[0])); 
		$query->bindValue(":ATIVI", $conteudoLinha[1]); 
		$query->execute();
		 	
	}else{
		echo "NC ".$conteudoLinha[0]." - ".utf8_encode($conteudoLinha[1])."<br>";
	}
}
*/

/*
$sql = "select * from tb_tipo_estabelecimento where te_ativo = 1 order by te_ativo DESC,te_descricao;";
$query = $connection->prepare($sql);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC); 

foreach($result as $key => $value){
	
	echo utf8_decode($value['te_descricao'])." - ".($value['te_ativo'] == 1?"Ativo":"Inativo")."<br>";
	
}

*/
?>