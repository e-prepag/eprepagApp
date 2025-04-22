<?php

if (!class_exists('ConnectionPDO')) {
    require "/www/db/connect.php";
	require "/www/db/ConnectionPDO.php";	
}

class Pix {

	private $chave;
	private $nome;
	private $code;
	private $type;
	private $valor;
	
	public function __construct($type, $chave, $nome, $code, $valor){
		$this->chave = $chave;
		$this->nome = $nome;
		$this->code = $code;
		$this->type = $type;
		$this->valor = $valor;
	}
	
	public static function buscaTransacoes(){ 
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		$sql = "SELECT * FROM tb_pag_pix WHERE sonda = 'NPG' and date(data_inclusao) = CURRENT_DATE;";
		$query = $conexao->prepare($sql);
		$query->execute();
		$pixs = $query->fetchAll(PDO::FETCH_ASSOC);
		
		return $pixs;
		
	}
	
	public static function travaTransacoes($compra){ 
		$conexao = ConnectionPDO::getConnection()->getLink();
		$sql = "UPDATE tb_pag_pix SET sonda = 'TRA' WHERE numcompra = :COMPRA;";
		$query = $conexao->prepare($sql);
		$query->bindValue(":COMPRA", $compra);
		$query->execute();
	}
	
	public static function destravaTransacoes($compra){ 
		$conexao = ConnectionPDO::getConnection()->getLink();
		$sql = "UPDATE tb_pag_pix SET sonda = 'NPG' WHERE numcompra = :COMPRA;";
		$query = $conexao->prepare($sql);
		$query->bindValue(":COMPRA", $compra);
		$query->execute();		
	}
	
	public function send($type){
		$code = ($type == "epp")? substr($this->code, 2): $this->code;
		$dados = [
			"MerchantOrderId" => $code,
			"Customer" => [
				"Name" => $this->nome,
				"Identity" => $this->chave,
				"IdentityType" => $this->type
			],
			"Payment" => [
				"Type" => "Pix",
				"Amount" => $this->valor
			]
		];
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://api.cieloecommerce.cielo.com.br/1/sales',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true, 
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS => json_encode($dados),
			  CURLOPT_HTTPHEADER => array(
				'MerchantKey: FJJcATX0QYKZpA6SmufTupQw1vBhabhyqiVb9zmK',
				'MerchantId: 272d8328-42fc-4563-9afb-9cd160865249',
				'Content-Type: application/json',
			  ),
			)
		);

		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);
		
		$file = fopen("/www/log/aaaa.txt", "a+");
		fwrite($file, $response."\n");
		fwrite($file, str_repeat("*", 60)."\n");
        fclose($file);
		
		return ["info" => $info,  "response" => $response, "dados" => $dados];
		
	}
	
	private function verificaPedido() {
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		$sql = "SELECT json_resposta FROM tb_pag_pix WHERE numcompra = :COMPRA;";
		$query = $conexao->prepare($sql);
		$query->bindValue(":COMPRA", substr($this->code, 2));
		$query->execute();
		$result = $query->fetch(PDO::FETCH_ASSOC);
		
		if($result == false){
			return true;
		}
		
		return $result;
		
	}
	
	public static function verify($pedido){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://apiquery.cieloecommerce.cielo.com.br/1/sales/'.$pedido,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'GET',
			  CURLOPT_HTTPHEADER => array(
				'MerchantKey: FJJcATX0QYKZpA6SmufTupQw1vBhabhyqiVb9zmK',
				'MerchantId: 272d8328-42fc-4563-9afb-9cd160865249'
			  )
		));

		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);
		
		return ["info" => $info,  "response" => $response];
	}
	
	public function getSondaPIX($compra, &$dados){
		
	}
	
	public function callService(){
		
		$validateSale = $this->verificaPedido();
		if($validateSale === true){
			$request = $this->send("epp");
			$info = $request["info"];
			$response = $request["response"];
			$dados = $request["dados"];
			$this->insertResponse(substr($this->code, 2), $this->chave, $this->nome, $response, json_encode($dados), substr($this->code, 0, 2));
			
		}else{
			$response = $validateSale["json_resposta"];
			$info["http_code"] = 201;
		}
		
		 if(!isset($info["http_code"]) && $info["http_code"] != 201) {
            echo ("<br><br>ERRO na Comunicação com o Banco!<br>Por favor, entre em contado com o suporte da E-Prepag e informe o erro de código PIX985235.<br>Obrigado.");
         }
         else {
			 
			$resposta = json_decode($response);
						
			$GLOBALS["_SESSION"]["QRCODE"] = $resposta->Payment->QrCodeString;  
			$html ="
					<div class='col-md-7 text-center d-min-md-none hide-pix-success' style='color: black;'>
						<button id='btn-copy' title='Copiar código' data-clipboard-text='".$GLOBALS["_SESSION"]["QRCODE"]."' class='top20 btn btn-success'>Copiar código</button>
					</div>
					<div class='col-md-7 d-max-sm-none hide-pix-success col-pix'>
						<img id='img-pix' style='float: left;' src='/includes/qrcode/php/qrcode.php'/>
						<span style='margin-top: 6%;display: block;'><b>Pix copia e cola:</b></span>
						<span style='word-break: break-all;display: block; font-size:.8em;'>".$GLOBALS["_SESSION"]["QRCODE"]."</span> 
					</div>
					<div class='col-md-12' style='border: 1px solid black; padding: 5px; margin-top:3px; text-align: center; clear: both;'>
						<b>Atenção o QRcode tem validade de 1 hora.</b>
					</div>
					<script src='/js/clipboard.min.js'></script>
					<script>
						$(document).ready(function(){
							var clipboard = new ClipboardJS('#btn-copy');
							clipboard.on('success', function(e){
								$('#btn-copy').attr('title', 'Código copiado');
								$('#btn-copy').tooltip('show');
							});
						});
					</script>";
			return $html;
        }
		/*if($info["http_code"] == 201){
			return $response;
		}else{			
			return json_encode(["error" => true, "code" => "PG001", "message" => "Não foi criado PIX"]);
		}*/
		
	}
	
	public function status($pedido){
		
        $request = self::verify($pedido);
		$info = $request["info"];
		$response = $request["response"];
		
		if($info["http_code"] == 200){
			$info = json_decode($response, true);
			
			if($info["Payment"]["Status"] == 2){
				$this->insertStatus($this->code, $response);
				return $response;
			}else{
				return json_encode(["error" => true, "code" => "PG002", "message" => "Nao foi pago ainda"]);
			}
		}else{			
			return json_encode(["error" => true, "code" => "PG003", "message" => "Nao foi possivel fazer a consulta"]);
		}
		
	}
	
	private function insertResponse($pedido, $chave, $nome, $response, $request, $type){
		$conexao = ConnectionPDO::getConnection()->getLink();
		$sql = "insert into tb_pag_pix(numcompra,cpf_cnpj_pagador,nome_pagador,json_resposta,json_request, type)values(:NUM, :CHAVE, :NOME, :RESPOSTA, :PEDIDO, :TYPE);";
		$query = $conexao->prepare($sql);
		$query->bindValue(":NUM", $pedido);
		$query->bindValue(":CHAVE", $chave);
		$query->bindValue(":NOME", $nome);
		$query->bindValue(":RESPOSTA", $response);
		$query->bindValue(":PEDIDO", $request);
		$query->bindValue(":TYPE", $type);
		$query->execute();
		
		if($query->rowCount() > 0){
			return true;
		}
		
		return false;
	}
	
	private function updateStatus($compra){
		$conexao = ConnectionPDO::getConnection()->getLink();
		$sql = "update tb_pag_pix set sonda = 'PG' where numcompra = :COMPRA;";
		$query = $conexao->prepare($sql);
		$query->bindValue(":COMPRA", substr($compra, 2));
		$query->execute();
		
		if($query->rowCount() > 0){
			
			$dados = [
				"http_status_code" => 200,
				"http_status_message" => "OK",
				"date" => "2023-06-09 12:15:40",
				"response" => [
					"message" => [
						"status" => "TRANSACAO_RECEBIDA",
						"id" => $compra
					]
				]
			];
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://www.e-prepag.com.br/webhook/confirmaPix.php',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS => json_encode($dados),
			  CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			  )
			));

			$response = curl_exec($curl);
			$webhook = curl_getinfo($curl);
			curl_close($curl);
			
			if($webhook["http_code"] == 200){			
			    return true;
			}else{			
			    return false;
			}

		}
		
		return false;
	}
	
	private function insertStatus($pedido, $response){
		$conexao = ConnectionPDO::getConnection()->getLink();
		
		$select = "select count(*) as qtde from tb_pag_pix_status where numcompra = :NUM;";
		$query = $conexao->prepare($select);
		$query->bindValue(":NUM", substr($pedido, 2));
	    $query->execute();
		$quantidade = $query->fetch(PDO::FETCH_ASSOC);
		
		if($quantidade["qtde"] == false || (isset($quantidade["qtde"]) && $quantidade["qtde"] == 0)){
			
			$sql = "insert into tb_pag_pix_status(numcompra, response)values(:NUM, :RESPOSTA);";
			$query = $conexao->prepare($sql);
			$query->bindValue(":NUM", substr($pedido, 2));
			$query->bindValue(":RESPOSTA", $response);
			$query->execute();
			
			if($query->rowCount() > 0){
				$update = $this->updateStatus($pedido);
				if($update){
					return true;
				}
				return false;
			}
		   
		   return false;
		}
	    
		return false;
	}
	
}

?>