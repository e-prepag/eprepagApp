<?php

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";



$key = 'epp@2022@pin@23453';	
$idVenda = 'MFljgYLLmHtUZJRcqWjojqAcGXvXkGE1VDVmKys7ARskRti2VlhHs0vXIZVvXrwIbEO47M3h4iXwghP2WRdsjA==';
$c = base64_decode($idVenda);
$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
$iv = substr($c, 0, $ivlen);
$hmac = substr($c, $ivlen, $sha2len=32);
$ciphertext_raw = substr($c, $ivlen+$sha2len);
$semCalculo = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);

echo $semCalculo;
exit;

$sql = "select h.* from tb_venda_games_historico h inner join tb_venda_games vg ON vg.vg_id = h.vgh_vg_id
inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id
where date(vgh_data_inclusao) >= '2022-12-08' and date(vgh_data_inclusao) <= '2022-12-16' and vgm.vgm_opr_codigo = 124 and vgm.vgm_valor = 3 and vgm.vgm_qtde = 1 order by h.vgh_data_inclusao,h.vgh_vg_id,h.vgh_status;";
$conexao = ConnectionPDO::getConnection()->getLink();
$query = $conexao->prepare($sql);
$query->execute();
$dados = $query->fetchAll(PDO::FETCH_ASSOC);

//var_dump($dados);
$vendas = [];
foreach($dados as $key => $value){
	
	//echo substr($value["vgh_data_inclusao"], 0, 10)."<br>";
	if(!array_key_exists($value["vgh_vg_id"], $vendas)){
		$vendas[$value["vgh_vg_id"]][] = substr($value["vgh_data_inclusao"], 0, 10);
	}else{

        if(!in_array(substr($value["vgh_data_inclusao"], 0, 10), $vendas[$value["vgh_vg_id"]])){
			$vendas[$value["vgh_vg_id"]][] = substr($value["vgh_data_inclusao"], 0, 10);
		}

	}
	
}

print_r($vendas);

?>