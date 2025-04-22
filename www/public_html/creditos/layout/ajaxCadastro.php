<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require_once "../../../includes/constantes.php";

$isAvailable = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ( array_key_exists('field', $_POST) ) {    
        require_once RAIZ_DO_PROJETO . 'db/connect.php';
        require_once RAIZ_DO_PROJETO . 'db/ConnectionPDO.php';
        require_once 'UserLan.php';

        $userLan = new UserLan();
        
            if ($_POST['field'] == "username") {
                $method = "hasLogin";
            } else if ($_POST['field'] == "email") {
                $method = "hasEmail";
            } else if ($_POST['field'] == "cnpj_empresa") {
                $method = "hasCNPJ";
            } else if ($_POST['field'] == "cpf_representante") {
                $method = "hasCPF";
            } 
 
            $isAvailable = !$userLan->$method($_POST[$_POST['field']]);
        }
		
		if(array_key_exists('rf', $_POST)){
			$cnpj = str_replace([".", "/", "-"], "", $_POST["cnpj_empresa"]);
		    $curl = curl_init();
			curl_setopt_array($curl, [
				CURLOPT_URL => "https://www.receitaws.com.br/v1/cnpj/".$cnpj,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CUSTOMREQUEST => "GET"
			]);
			$retorno = json_decode(curl_exec($curl), true);
			$info = curl_getinfo($curl);
			curl_close($curl);
			
			$connection = ConnectionPDO::getConnection()->getLink(); 
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

			if($info["http_code"] == 200){
				if(($retorno["situacao"] == "ATIVA") && $retorno["status"] == "OK"){  // || $retorno["situacao"] == "BAIXADA"
					if(count($validos) > 0){
						$isAvailable = true;
					}else{
					    $isAvailable = false;
					}
				}else{
					$isAvailable = false;
				}
			}else{
				$isAvailable = false;
			}	
	    }
    }
	
output_result($isAvailable);

function output_result($isAvailable){
    echo json_encode(array('valid' => $isAvailable));
    exit;
}
