<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
    
    header("access-control-allow-origin: " . EPREPAG_URL_HTTPS . "");
    require_once "/www/db/connect.php";
    require_once "/www/db/ConnectionPDO.php";

    $prod = $_POST["prod"];

    //$prod = 433;

    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();
    $sql = "select * from link_produto_amigavel where id_produto = :PRODUTO;";  
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":PRODUTO", $prod);
    $stmt->execute();
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    if($produto != false){
		$key = json_decode($produto["palavras_chaves"], true)["p1"];
	    header("location: " . EPREPAG_URL_HTTPS . "/catalogo/".$key);
	}else{
		
		require_once '../../includes/constantes.php';
		$raiz_do_projeto = '/www/';
		require_once $raiz_do_projeto."class/util/Util.class.php";
		require_once $raiz_do_projeto."class/business/BannerBO.class.php";
		require_once $raiz_do_projeto."class/classEncryption.php";
		
		$str = serialize(["produto" => $prod]);
		$objEncryption = new Encryption();
		$key = $objEncryption->encrypt($str);
		header("location: " . EPREPAG_URL_HTTPS . "/game/produto/detalhe.php?token=".$key);
	}
   
?>