<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);

if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
	$url = $_SERVER["REQUEST_URI"];
	$dadosFiltrados = array_unique(explode("/", $url));
	$urlQuebrada = array_splice($dadosFiltrados, 1);

	if(isset($urlQuebrada[1]) && !empty($urlQuebrada[1]) && $urlQuebrada[1] != null){
		$nomeProduto = $urlQuebrada[1];
		//$chaves = ["freefire"];
		//if(in_array($nomeProduto, $chaves)){
			//$nomeProduto =  "free";
			$conexao = ConnectionPDO::getConnection();
			$buscaProduto = "select * from link_produto_amigavel where palavras_chaves like :CHAVE;";
			$query = $conexao->getLink()->prepare($buscaProduto);
			$query->bindValue(":CHAVE", "%".$nomeProduto."%");
			$query->execute();
			$resultado = $query->fetch(PDO::FETCH_ASSOC);
			
			if($resultado != false){
				header("location: " . EPREPAG_URL_HTTPS . "/game/produto/detalhe.php?token={$resultado["token_produto"]}");
				exit;
			}else{
				header("location: " . EPREPAG_URL_HTTPS . "/game/produto/detalhe.php");
				exit;
			}
		
		//}
		
	}
	
} 

// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNFH9dWl4P netflix
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNEntVWl4P league of legends
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafFtNFH5HQxg= mu online
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNEXtdWl4P webzen wcoin
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNFHldWl4P playstation store
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNEnxdWl4P crossfire
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNFH9WWl4P xbox cash card
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNFH9RWl4P xbox live gold
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNFHhUWl4P game pass ultimate
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNFHRWWl4P gcoin
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNFHRRWl4P mu legend
// IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNFHtRWl4P riders of icarus
// IlJQTB5TdFZVAj0XFwEHNwdDTRYaf1tNFW9eBQ== habbo
// insert into link_produto_amigavel(token_produto,id_produto,palavras_chaves)values();

?>