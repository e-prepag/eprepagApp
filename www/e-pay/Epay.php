<?php

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

class Epay{

   const URL = ["https://precision.epayworldwide.com/up-interface", "https://backup.precision.epayworldwide.com/up-interface"];
   private $tid = "93889371";
   private $user = "UPTestBrazilHQ";
   private $pass = "demo123456";
   private $environment;
   private $connect;
  // private $user;
   
   public function __construct(){
	   
	   $this->connect = ConnectionPDO::getConnection()->getLink();
	   //$this->user = $user;
	   $valid = $this->testConnect(0);
	   if($valid != true){
		   $this->testConnect(1);
	   }

   }	
   
   public function testConnect($indexUrl){
	   
	   $curlConnect = curl_init();
	   $guid = sprintf("%08X-%04X-%04X-%04X-%012X",rand(100000000, 999999999),rand(1000, 9999),rand(1000, 9999),rand(1000, 9999),rand(1000000000000, 9999999999999));
	   $data = json_encode([
			"TYPE" => "DIAGNOSTIC",
			"AUTHORIZATION" => [
				"USERNAME" => $this->user,
				"PASSWORD" => $this->pass
			],
			"TERMINALID" => $this->tid,
			"LOCALDATETIME" => date("Y-m-d H:i:s"),
			"TXID" => $guid
	   ]);	      
	   curl_setopt_array($curlConnect, [
	  
	       CURLOPT_URL => Epay::URL[$indexUrl],
		   CURLOPT_RETURNTRANSFER => true,
		   CURLOPT_CUSTOMREQUEST => "POST",
		   CURLOPT_POSTFIELDS => $data,
		   CURLOPT_TIMEOUT => 120,
		   CURLOPT_SSL_VERIFYHOST => false,
		   CURLOPT_SSL_VERIFYPEER => false
	   
	   ]);
	   $result = curl_exec($curlConnect);
	   $info = curl_getinfo($curlConnect);
	   curl_close($curlConnect);
	 
	   $this->log_epay("connect", $result, Epay::URL[$indexUrl], "receipt", "/www/log/connect_EPAY.txt");
	 
	   if($info["http_code"] == 200){
		   $this->environment = Epay::URL[$indexUrl];
		   return true;
	   }elseif($info["http_code"] != 200 || $result == "" || $result == false || $result == null){
		   return false;
	   }   
	   
   }
   
   public function catalog(){
	   
	   $curlCatolog = curl_init();
	   $guid = sprintf("%08X-%04X-%04X-%04X-%012X",rand(100000000, 999999999),rand(1000, 9999),rand(1000, 9999),rand(1000, 9999),rand(1000000000000, 9999999999999));
	   $data = json_encode([
			"TYPE" => "CATALOG",
			"AUTHORIZATION" => [
				"USERNAME" => $this->user,
				"PASSWORD" => $this->pass
			],
			"TERMINALID" => $this->tid,
			"LOCALDATETIME" => date("Y-m-d H:i:s"),
			"TXID" => $guid
	   ]);	      
	   curl_setopt_array($curlCatolog, [
	  
	       CURLOPT_URL => $this->environment,
		   CURLOPT_RETURNTRANSFER => true,
		   CURLOPT_CUSTOMREQUEST => "POST",
		   CURLOPT_POSTFIELDS => $data,
		   CURLOPT_TIMEOUT => 120,
		   CURLOPT_SSL_VERIFYHOST => false,
		   CURLOPT_SSL_VERIFYPEER => false
	   
	   ]);
	   $result = curl_exec($curlCatolog);
	   $info = curl_getinfo($curlCatolog);
	   curl_close($curlCatolog);
	 
	   $this->log_epay("catalog", $result, $this->environment, "receipt", "/www/log/catalog_EPAY.txt");
	   if($info["http_code"] == 200){
		   return $result;
	   }elseif($info["http_code"] != 200 || $result == "" || $result == false || $result == null){
		   return false;
	   }   
	 
   }
   
   public function sale($mode, $info, $txref = ""){
	   
	   $guid = sprintf("%08X-%04X-%04X-%04X-%012X",rand(100000000, 999999999),rand(1000, 9999),rand(1000, 9999),rand(1000, 9999),rand(1000000000000, 9999999999999));
	   $data = [
			 "TYPE" => "SALE",
			 "MODE" => $mode,
			 "AUTHORIZATION" => [
				 "USERNAME" => $this->user,
				 "PASSWORD" => $this->pass
			 ],
			 "TERMINALID" => $this->tid,
			 "RETAILERID" => $info["retailerid"],
			 "SHOPID" => $info["shopid"], 
			 "LOCALDATETIME" => date("Y-m-d H:i:s"),
			 "TXID" => $guid,
			 "PRODUCTID" => $info["code"],
			 "AMOUNT" => ($info["value"] * 100), 
			 "CURRENCY" => "986",
			 "RECEIPT" => [
				 "CHARSPERLINE" => 40,
				 "LANGUAGE" => "POR"
			 ]
	   ];
	   
	   if($mode == "DIRECT"){
		    $data["STORERECEIPT"] = "1"; //0
	   }else{
		    $data["TXREF"] = $txref;
	   }
	   
	   $this->log_epay("sale", json_encode($data), $this->environment, "send", "/www/log/sale_send_EPAY.txt");
	   $curlSale = curl_init();
	   curl_setopt_array($curlSale, [
	   
	       CURLOPT_URL => $this->environment,
		   CURLOPT_RETURNTRANSFER => true,
		   CURLOPT_CUSTOMREQUEST => "POST",
		   CURLOPT_POSTFIELDS => json_encode($data),
		   CURLOPT_TIMEOUT => 30,
	       CURLOPT_CONNECTTIMEOUT => 24,
		   CURLOPT_SSL_VERIFYHOST => false,
		   CURLOPT_SSL_VERIFYPEER => false
	   
	   ]);
	   $result = curl_exec($curlSale);
	   $con = curl_getinfo($curlSale);
	   $codErro = curl_errno($curlSale);
	   curl_close($curlSale);
	   
	    if($mode == "DIRECT"){
			   // variavel que força cancelamento automatico da venda
			   $testeAutomatic = false;
			   $this->log_epay("sale", $result, $this->environment, "receipt", "/www/log/sale_EPAY.txt");
			   if($con["http_code"] == 200 && $codErro == 0){
				    $mensage = json_decode($result, true);
				    if(($mensage["RESULT"] == "9996" || json_last_error() != JSON_ERROR_NONE) || $testeAutomatic == true){
					    $infoCancel = $this->cancelSale("AUTOMATIC", $info["code"], $info["value"], $guid, "sale");
					    if($infoCancel != false){
						    $cancel = json_decode($infoCancel, true);
							if($cancel["RESULT"] == 0){
								return "cancelada"; //return $infoCancel;
							}else{
								$fileName = '/www/log/epay/erro/error.txt';
							    $file = fopen($fileName, "a+");
							    fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
							    fwrite($file, "ERRO AO CANCELAR TRANSACAO E-PAY\n");
							    fwrite($file, "DADOS: ".$infoCancel."\n");
							    fwrite($file, str_repeat("*", 50)."\n");
							    fclose($file);		   
							    return "erro";
							}
				        }  
				    }elseif($mensage["RESULT"] != "0"){
					   return "cancelada";
				    } 
				   
				   $info["json"] = str_replace(["\r", "\n", "\t"], "", $result);
				   $info["status"] = 0;
				   $info["serial"] = $mensage["PINCREDENTIALS"]["SERIAL"];
				   $info["pin"] = $mensage["PINCREDENTIALS"]["PIN"];
				   $info["validity"] = $mensage["PINCREDENTIALS"]["VALIDTO"];
				   $info["guid_epay"] = $mensage["HOSTTXID"];
				   $info["guid_epp"] = $mensage["TXID"];
				   $this->insertTableSaleEpay($info);
				   return ""; //$mensage
			   }elseif($con["http_code"] == 0 && $codErro == 28){
				   $infoCancel = $this->cancelSale("AUTOMATIC", $info["code"], $info["value"], $guid, "sale");
				   if($infoCancel != false){
					    $cancel = json_decode($infoCancel, true);
					    if($cancel["RESULT"] == 0){
								return "cancelada"; //return $infoCancel;
						}else{
							$fileName = '/www/log/epay/erro/error.txt';
							$file = fopen($fileName, "a+");
							fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
							fwrite($file, "ERRO AO CANCELAR TRANSACAO E-PAY\n");
							fwrite($file, "DADOS: ".$infoCancel."\n");
							fwrite($file, str_repeat("*", 50)."\n");
							fclose($file);		   
							return "erro";
						}
				   }
			   }elseif($con["http_code"] != 200 && $con["http_code"] != 0){
				   return "cancelada";
			   }   
	   
		}else{
			$this->log_epay("sale", $result, $this->environment, "receipt", "/www/log/sale_EPAY.txt");
			$mensage = json_decode($result, true);
			return $mensage;
		}
	   
   } 
   
   public function cancelSale($mode, $product, $value, $txref, $type){
	   
       $guid = sprintf("%08X-%04X-%04X-%04X-%012X",rand(100000000, 999999999),rand(1000, 9999),rand(1000, 9999),rand(1000, 9999),rand(1000000000000, 9999999999999));
	   $data = [
			 "TYPE" => "CANCEL",
			 "MODE" => $mode,
			 "AUTHORIZATION" => [
			 "USERNAME" => $this->user,
			 "PASSWORD" => $this->pass
			 ],
			 "LOCALDATETIME" => date("Y-m-d H:i:s"),
			 "TERMINALID" => $this->tid,
			 "TXID" => $guid,
			 "TXREF" => $txref,
			 "CURRENCY" => "986",
			 "AMOUNT" => ($value * 100),
			 "PRODUCTID" => $product
	   ];
        
	  $this->log_epay("cancel", json_encode($data), $this->environment, "send", "/www/log/cancel_send_EPAY.txt");
      $curlCancel = curl_init();
      curl_setopt_array($curlCancel, [
	   
	       CURLOPT_URL => $this->environment,
		   CURLOPT_RETURNTRANSFER => true,
		   CURLOPT_CUSTOMREQUEST => "POST",
		   CURLOPT_POSTFIELDS => json_encode($data),
		   CURLOPT_TIMEOUT => 60,
		   CURLOPT_SSL_VERIFYHOST => false,
		   CURLOPT_SSL_VERIFYPEER => false
	   
	   ]);
	   
       $result = curl_exec($curlCancel);
	   $con = curl_getinfo($curlCancel);
	   curl_close($curlCancel);
	   
	   $this->log_epay("cancel", $result, $this->environment, "receipt", "/www/log/cancel_EPAY.txt");
	   if($con["http_code"] == 200){
		   
		   if($type != "sale"){
			   try{
				   $sql = "update tb_pedidos_epay set status = 2 where cod_epp_enviado = :REF;";
				   $updateTransaction = $this->connect->prepare($sql);
				   $updateTransaction->bindValue(":REF", $txref);
				   $updateTransaction->execute();
				   
				   $sql = "select cod_venda_origem, tipo_venda, pin_epay, pin_serial from tb_pedidos_epay where cod_epp_enviado = :REF;";
				   $findTransaction = $this->connect->prepare($sql);
				   $findTransaction->bindValue(":REF", $txref);
				   $findTransaction->execute();
				   $result = $findTransaction->fetch(PDO::FETCH_ASSOC);
				   
				   $tableName = ($result["tipo_venda"] == "PDV")?"tb_dist_venda_games":"tb_venda_games";
				   $sql = "update $tableName set vg_ultimo_status = '6' where vg_id = :VG;";
				   $updateTable = $this->connect->prepare($sql);
				   $updateTable->bindValue(":VG", $result["cod_venda_origem"]);
				   $updateTable->execute();
				   
				   $sql = "update pins set pin_status = '9' where pin_codigo = :PIN and pin_serial = :SERIAL;";
				   $updateTable = $this->connect->prepare($sql);
				   $updateTable->bindValue(":PIN", $result["pin_epay"]);
				   $updateTable->bindValue(":SERIAL", $result["pin_serial"]);
				   $updateTable->execute();
				   
				}catch(PDOException $Exception){
				   $fileName = '/www/log/epay/erro/error.txt';
				   $file = fopen($fileName, "a+");
				   fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
				   fwrite($file, "SQL: ".$sql."\n");
				   fwrite($file, "DADOS: ".$txref."\n");
				   fwrite($file, "ERRO SQL: ".$Exception->getMessage()."\n");
				   fwrite($file, str_repeat("*", 50)."\n");
				   fclose($file);		   
				   return false;
			   }
		   }
		   
		   return $result;
	   }elseif($con["http_code"] != 200 || $result == "" || $result == false || $result == null){
		   return false;
	   }

   }
    
   private function log_epay($type, $data, $optional = "", $middle, $fileName){
	   
	       $file = fopen($fileName, "a+");
		   switch($type){
		   
			   case "connect":
				   fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\r");
				   fwrite($file, "TIPO: CONEXÃO\r");
				   fwrite($file, "MEIO: ".strtoupper($middle)."\r");
				   fwrite($file, "CONTEUDO RECEBIDO: ".$data."\r");
				   fwrite($file, "URL USADA: ".$optional."\r");
				   fwrite($file, str_repeat("*", 50)."\r");
				   fclose($file);
			   break;
			   
			   case "catalog":
				   fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\r");
				   fwrite($file, "TIPO: CATALOGO\r");
				   fwrite($file, "MEIO: ".strtoupper($middle)."\r");
				   fwrite($file, "URL USADA: ".$optional."\r");
				   fwrite($file, str_repeat("*", 50)."\r");
				   fclose($file);
			   break;
			   
			   case "sale":
				   fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\r");
				   fwrite($file, "TIPO: VENDA\r");
				   fwrite($file, "MEIO: ".strtoupper($middle)."\r");
				   fwrite($file, "CONTEUDO RECEBIDO: ".$data."\r");
				   fwrite($file, "URL USADA: ".$optional."\r");
				   fwrite($file, str_repeat("*", 50)."\r");
				   fclose($file);
			   break;
			   
			   case "cancel":
				   fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\r");
				   fwrite($file, "TIPO: CANCELAMENTO\r");
				   fwrite($file, "MEIO: ".strtoupper($middle)."\r");
				   fwrite($file, "CONTEUDO RECEBIDO: ".$data."\r");
				   fwrite($file, "URL USADA: ".$optional."\r");
				   fwrite($file, str_repeat("*", 50)."\r");
				   fclose($file);
			   break;
			   
	   }
	   
   }
   
   private function insertTableSaleEpay($info){
	   
	   try{
		   $dataDisparo = new DateTime();
		   $intervalo = new DateInterval('P2D');
		   $dataDisparo->add($intervalo);
		   $sql = "insert into tb_pedidos_epay(status,cod_venda_origem,cod_epp_enviado,cod_epay_recebido,info_recebido,pin_epay,data_inclusao,cod_produto,tipo_venda,data_disparo,pin_serial,valor,nome_produto,cod_loja)values(:STATUS,:VENDA,:CODEPP,:CODEPAY,:INFO,:PIN,CURRENT_TIMESTAMP,:CODPROD,:TPVENDA,:DATADISPA,:SERIAL,:VALOR,:NOMEPROD,:CODLOJA);";
		   $insertTransaction = $this->connect->prepare($sql);
		   $insertTransaction->bindValue(":STATUS", $info["status"]);
		   $insertTransaction->bindValue(":VENDA", $info["sale"]);
		   $insertTransaction->bindValue(":CODEPP", $info["guid_epp"]);
		   $insertTransaction->bindValue(":CODEPAY", $info["guid_epay"]);
		   $insertTransaction->bindValue(":INFO", $info["json"]);
		   $insertTransaction->bindValue(":PIN", $info["pin"]);
		   $insertTransaction->bindValue(":CODPROD", $info["code"]);
		   $insertTransaction->bindValue(":TPVENDA", $info["type_sale"]);
		   $insertTransaction->bindValue(":DATADISPA", $dataDisparo->format("Y-m-d")); 
		   $insertTransaction->bindValue(":SERIAL", $info["serial"]);
		   $insertTransaction->bindValue(":VALOR", $info["value"]);
		   $insertTransaction->bindValue(":NOMEPROD", $info["name_prod"]);
		   $insertTransaction->bindValue(":CODLOJA", $info["retailerid"]);
		   $insertTransaction->execute();
		   
		   if($insertTransaction->rowCount() > 0){
			   
			   $sqlLote = "select max(pin_lote_codigo) as max_pin_lote_codigo from pins where opr_codigo = :OPERADORA;";
			   $findLote = $this->connect->prepare($sqlLote);
			   $findLote->bindValue(":OPERADORA", $info["operator"]);
			   $findLote->execute();
			   $lotedb = $findLote->fetch(PDO::FETCH_ASSOC);
			   if($lotedb == false || $lotedb == null){
				   $lote = 1;
			   }else{
				   $lote = ($lotedb["max_pin_lote_codigo"] + 1);
			   }
			   
			   $status = ($info["type_sale"] == "PDV")?"6":"3";
			   $sql = "insert into pins (pin_serial,pin_codigo,opr_codigo,pin_valor,pin_lote_codigo,pin_dataentrada,pin_canal,pin_horaentrada,pin_status,pin_datavenda,pin_datapedido,pin_horavenda,pin_horapedido,pin_est_codigo,pin_validade) 
			   values (:SERIAL,:PIN,:OPERADORA,:VALOR,:LOTE,CURRENT_TIMESTAMP,:CANAL,CURRENT_TIMESTAMP,:STATUS,:DATE,:DATE,:HORA,:HORA,:ESTABE,:VALIDADE);";
			   $insertPin = $this->connect->prepare($sql);
			   $insertPin->bindValue(":SERIAL", $info["serial"]);
			   $insertPin->bindValue(":PIN", $info["pin"]);
			   $insertPin->bindValue(":DATE", date("Y-m-d"));
			   $insertPin->bindValue(":HORA", date("H:i:s"));
			   $insertPin->bindValue(":OPERADORA", $info["operator"]);
			   $insertPin->bindValue(":VALOR", $info["value"]);
			   $insertPin->bindValue(":LOTE", $lote);
			   $insertPin->bindValue(":CANAL", "s");
			   $insertPin->bindValue(":STATUS", $status);
			   $insertPin->bindValue(":ESTABE", 1);
			   $insertPin->bindValue(":VALIDADE", substr($info["validity"], 0, 10));
			   $insertPin->execute();
			   $idPin = $this->connect->lastInsertId();
			   
			   if($info["type_sale"] == "PDV"){
				   $sql = "insert into pins_dist select * from pins where pin_codinterno = :CODPIN;";
				   $insertPinDist = $this->connect->prepare($sql);
				   $insertPinDist->bindValue(":CODPIN", $idPin);
				   $insertPinDist->execute();
			   }else{
				   // pega o codigo do pin anterior e concatena com o novo, para o modelo de venda de usuario final
				   $newId = $idPin.","; 
				   $sql = "update tb_venda_games_modelo set vgm_pin_codinterno = concat(COALESCE(vgm_pin_codinterno, ''), :NEW) where vgm_id = :VGM;";
				   $updateModelSale = $this->connect->prepare($sql);
				   $updateModelSale->bindValue(":VGM", $info["model"]);
				   $updateModelSale->bindValue(":NEW", $newId, PDO::PARAM_STR);//'".$newId."'
				   $updateModelSale->execute();
				   
			   }
			   
			   $tableName = ($info["type_sale"] == "PDV")?"tb_dist_venda_games_modelo_pins":"tb_venda_games_modelo_pins";
			   $sql = "insert into $tableName(vgmp_vgm_id,vgmp_pin_codinterno)values(:VGM,:CODPIN);";
			   $insertPinModelo = $this->connect->prepare($sql);
			   $insertPinModelo->bindValue(":VGM", $info["model"]);
			   $insertPinModelo->bindValue(":CODPIN", $idPin);
			   $insertPinModelo->execute();
			   
			   return true;   
		   }else{
			   return false;   
		   }
	   }catch(PDOException $Exception){
		   $fileName = '/www/log/epay/erro/error.txt';
		   $file = fopen($fileName, "a+");
		   fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
		   fwrite($file, "SQL: ".$sql."\n");
		   fwrite($file, "DADOS: ".json_encode($info)."\n");
		   fwrite($file, "ERRO SQL: ".$Exception->getMessage()."\n");
		   fwrite($file, str_repeat("*", 50)."\n");
		   fclose($file);		   
		   return false;
	   }
    }
	
	public function verifyRequest($sale_id){
		
		$sql = "select count(*) as total from tb_pedidos_epay where cod_venda_origem = :VG;";
		$findTransaction = $this->connect->prepare($sql);
		$findTransaction->bindValue(":VG", $sale_id);
		$findTransaction->execute();
		$result = $findTransaction->fetch(PDO::FETCH_ASSOC);
		
		if($result["total"] != 0 && $result["total"] != false){
			return true;
		}
		return false;
	}
   
   public function writeFileSftp(){
	   
	    //$host = "67.129.107.130"; 
	   // $sftpServer    = 'usftp.epayworldwide.com';
	    //$sftpUsername  = 'ftp.e-prepag';
	    //$sftpPassword  = '1Qq9,*>Fse&';
	    //$sftpPort      = 22;
		
		$sftpServer    = '10.204.134.60';
	    $sftpUsername  = 'root';
	    $sftpPassword  = 'g00gl3br4s1l1974@';
	    $sftpPort      = 22587;
		
		
		$msg = "";
	 	
		$arquivo_local = "/www/log/epay/recebidos/eprepag_daily_transactions_".date("Ymd").".csv";
		$arquivo_remoto = "/eprepag_daily_transactions_".date("Ymd").".csv";
		
	    $connection = ssh2_connect($sftpServer, $sftpPort);
        if($connection === FALSE) {
            $msg .= '* Falha na conexão / '.PHP_EOL;
        }else { 
		    $msg .= "* Conexão ok / ".PHP_EOL;
		    if(ssh2_auth_password($connection, $sftpUsername, $sftpPassword) === FALSE) {
				$msg .= '* Falha na autenticação / '.PHP_EOL;
			}else{
				$msg .= '* Autenticação com sucesso / '.PHP_EOL;
				$sftp = ssh2_sftp($connection);
				if($sftp){
				    $msg .= '* Sucesso no sftp / '.PHP_EOL;
						if(ssh2_scp_recv($connection, $arquivo_remoto, $arquivo_local)){
							$msg .= '* Sucesso ao baixar arquivo do sftp / '.PHP_EOL;
						}else{
							$msg .= '* Falha ao baixar arquivo do sftp / '.PHP_EOL;
						}
					//var_dump(ssh2_scp_send($connection, '/www/e-pay'.$arq_csv, $arq_csv));
				}else{
					$msg .= '* Falha no sftp / '.PHP_EOL;
				}
				
			}
	
        }	   
		
		$fileName = '/www/e-pay/retornoConcilia.txt';
		$file = fopen($fileName, "a+");
		fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
		fwrite($file, "PASSOS DA CAPTURA: \n".$msg."\n");
		fwrite($file, str_repeat("*", 50)."\n");
		fclose($file);		   

	   /*
       $fileName = "/www/log/epay/eprepag_daily_transactions_".date("Ymd").".csv";
       
	   $file = fopen($fileName, "w+");
	   $header = "Transaction Date;TransactionID;Amount;Serial number;Name;EAN;Currency;Store ID;Store Name;Retailer name (Division name);Operator;Branding\n";
	   fwrite($file, $header);
	   
	   $findTransactionDay = $this->connect->prepare("select * from tb_pedidos_epay where data_disparo = :DATA and status = 0;");
	   $findTransactionDay->bindValue(":DATA", "2022-10-05"); //date("Y-m-d")
	   $findTransactionDay->execute(); 
	   $transaction = $findTransactionDay->fetchAll(PDO::FETCH_ASSOC);
	   if(count($transaction) == 0){
		   return false;
	   }
	   $ids = [];
	   for($num = 0; $num < count($transaction); $num++){
		   $ids[] = $transaction[$num]["cod_pedido"];
		   $valor = number_format($transaction[$num]["valor"], 2, ',', '.');
		   fwrite($file, substr($transaction[$num]["data_inclusao"], 0, 19).";".$transaction[$num]["cod_venda_origem"].";".$valor.";".$transaction[$num]["pin_serial"].";".$transaction[$num]["nome_produto"].";".$transaction[$num]["cod_produto"].";BRL;".$transaction[$num]["cod_loja"].";Brasil;E_prepag BR;Brasil;Brasilien(E_prepag BR)\n");
	   }
	   fclose($file);
	   
	   try{
		   
		   $sqlUpdate = "update tb_pedidos_epay set status = 1 where cod_pedido in(".implode(",", $ids).") and status = 0;";//:DADOS
		   $updateStatusTransaction = $this->connect->prepare($sqlUpdate);
		   //$pedidosId = implode(",", $ids);
		   //$updateStatusTransaction->bindValue(":DADOS", $pedidosId);
		   $updateStatusTransaction->execute(); 
		   
		   if($updateStatusTransaction->rowCount() > 0){
			   
			   //var_dump("Ok update");
			   //exit;
			   $dataFile      = $fileName;
			   $sftpServer    = 'usftp.epayworldwide.com';
			   $sftpUsername  = 'ftp.e-prepag';
			   $sftpPassword  = '1Qq9,*>Fse&';
			   $sftpPort      = 22;
			   $sftpRemoteDir = '/';

			   $ch = curl_init('sftp://' . $sftpServer . ':' . $sftpPort . $sftpRemoteDir . '/' . basename($dataFile));
			   $fh = fopen($dataFile, 'r');
			   if ($fh) {
					curl_setopt($ch, CURLOPT_USERPWD, $sftpUsername . ':' . $sftpPassword);
					curl_setopt($ch, CURLOPT_UPLOAD, true);
					curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
					curl_setopt($ch, CURLOPT_INFILE, $fh);
					curl_setopt($ch, CURLOPT_INFILESIZE, filesize($dataFile));
					curl_setopt($ch, CURLOPT_VERBOSE, true);
					$verbose = fopen('/www/e-pay/info_curl_envio_conciliacao.txt', 'w+');
					curl_setopt($ch, CURLOPT_STDERR, $verbose);
					$response = curl_exec($ch);
					curl_close($ch);
					if ($response && curl_errno($ch) == 0) {
						return true;   
					} else {
						return false;   
					}
			   }
			   
		   }else{
			   return false;   
		   }
		   
	    }catch(PDOException $Exception){
		   $fileName = '/www/log/epay/erro/error.txt';
		   $file = fopen($fileName, "a+");
		   fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
		   fwrite($file, "SQL: ".$sqlUpdate."\n");
		   fwrite($file, "DADOS: ".json_encode($ids)."\n");
		   fwrite($file, "ERRO SQL: ".$Exception->getMessage()."\n");
		   fwrite($file, str_repeat("*", 50)."\n");
		   fclose($file);		   
		   return false;
	    }*/
	   	   
   }
   
}

?>