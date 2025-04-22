<?php 

// Livrodjx did it right

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php";
require_once '/www/includes/gamer/inc_sanitize.php'; 
require_once '/www/includes/gamer/chave.php';
require_once '/www/includes/gamer/AES.class.php';
$connection = ConnectionPDO::getConnection()->getLink(); 


$sql = "select ogp_id, ogp_nome, ogpm_nome, ogpm_descricao, opr_nome, ogp_opr_codigo as opr_codigo , ogpm_valor, opr_pin_epp_formato as formato, ogpm_pin_valor as pin_valor_final, COUNT(pins.pin_valor) AS quantidade from tb_operadora_games_produto 
inner join tb_operadora_games_produto_modelo on ogpm_ogp_id = ogp_id
inner join operadoras on opr_codigo = ogp_opr_codigo
inner join pins on pin_valor = ogpm_pin_valor and pins.pin_status = '1' and pins.opr_codigo = ogp_opr_codigo 
where ogp_ativo = 1 and opr_status = '1' and opr_pin_epp_formato is not null and ogpm_ativo = 1 or pins.pin_valor = 999 group by ogp_id, ogp_nome, ogpm_nome, ogpm_descricao, ogpm_valor, ogp_opr_codigo, ogpm_pin_valor, opr_pin_epp_formato,
opr_nome;";
$query = $connection->prepare($sql);
$query->execute();

$ret = $query->fetchAll(PDO::FETCH_ASSOC);


$banks = array(
'0' => array( '0' => '23456789',
  '1' => '8',
  '2' => '4'),
'1' => array( '0' => '23456789abcdefghjkmnpqrstvwxyz',
  '1' => '16',
  '2' => '4'),
'2' => array( '0' => '23456789ABCDEFGHIJKLMNPQRSTUVWXYZ',
  '1' => '16',
  '2' => '4'),
'3' => array( '0' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789',
  '1' => '16',
  '2' => '4'),
'4' => array( '0' => '0123456789',
  '1' => '16',
  '2' => '4'),
'5' => array( '0' => '0123456789',
  '1' => '14',
  '2' => '4'),
'6' => array( '0' => '0123456789',
  '1' => '20',
  '2' => '4'),
);

$nchars = 20;
$separador = 4;
$serial_length = 10;


function saveLog($pins, $lote, $operadora, $valor) {
	try {
		$file = fopen("/www/log/cron_estoque_pins.txt", "a+");
		fwrite($file, str_repeat("*", 50)."\n");
		fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
		fwrite($file, "OPERADORA: ".$operadora. "\n");
		fwrite($file, "VALOR DO PIN: ".$valor."\n");
		fwrite($file, "PIN(S): ".$pins."\n");
		fwrite($file, "LOTE: ".$lote."\n");
		fwrite($file, str_repeat("*", 50)."\n");
		fclose($file);
	}catch (Exception $e) {
		echo "Error(6) writing monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage().PHP_EOL;
	}
}

function gera_pin($sformato, $pin_valor) {
	global $nchars, $separador;
	
	$return				= "";
	$sformato			= intval($sformato);
	$pin_valor			= floatval($pin_valor);
	// set up a counter
	$i = 0;
	// add random characters to $return until $nchars is reached
	while ($i < $nchars) {
		// pick a random character from the possible ones
		$char = substr('0123456789', mt_rand(0, strlen('0123456789') - 1), 1);
		// we don't want this character if it's already in the password
		// but repeat character  if length > range 
		if (!strstr($return, $char)||strlen('0123456789') < $nchars) {
			// testa formatos
			if(($i%$separador==0) && ($i>0)) {
				$return .= "-";
			}
			$return .= $char;
			$i++;
		}
		
	}
	return $return;
}
	
if(count($ret) > 0) {
	
	foreach($ret as $key => $value) {
		
		if ($value["quantidade"] <= 100) { 
			
			$sql = "select max(pin_lote_codigo) as max_pin_lote_codigo from pins where opr_codigo = ". $value["opr_codigo"];
			$query = $connection->prepare($sql);
			$query->execute();
			
			$rs_lote = $query->fetch(PDO::FETCH_ASSOC);
			
			if(!$rs_lote || count($rs_lote) == 0) {
				$rs_lote = 1;
			}
			
			
			$sql_serial = "select CAST(pin_serial AS BIGINT) as max_serial from pins where opr_codigo = ".$value["opr_codigo"]." order by CAST(pin_serial AS BIGINT) desc limit 1;";
			
			$query = $connection->prepare($sql_serial);
			$query->execute();
			
			$rs_serial = $query->fetch(PDO::FETCH_ASSOC);
			
			if(!$rs_serial || count($rs_serial) == 0) {
				$rs_serial = 1;
			}
			
			//instanciando a classe de cryptografia
			$chave256bits = new Chave();
			//chave Publisher
			$aesPub = new AES($chave256bits->retornaChavePub());
			//chave Cash
			$aes = new AES($chave256bits->retornaChave());
			
			
			$i = 1;
			$iaux = 1;
			$pins_final = "";
			
			while ($i <= 50) {
				
				$spin_codigo = gera_pin($value["formato"], $value["pin_valor_final"]);
				$spin_codigo = str_replace("-","",$spin_codigo);
				$msg = "";
				$aux_spin_codigo = $spin_codigo;
				$params		= array('spin_codigo' => array ('0' => $spin_codigo,
					  '1' => 'S',
					  '2' => '1'
					)
				);
				$params		= sanitize_input_data_array($params,$err_cod);
				extract($params, EXTR_OVERWRITE);
				
				if($aux_spin_codigo == $spin_codigo) {
					
					$rs_serial ++;
					$spin_serial = str_pad(number_format($rs_serial["max_serial"], 0, '', ''), $serial_length, "0", STR_PAD_LEFT);
					
					$sql = "SELECT * FROM pins where pin_codigo = '". $spin_codigo ."' and opr_codigo = ".$value["opr_codigo"].";";
					$query = $connection->prepare($sql);
					$query->execute();
					$rs_pins = $query->fetchAll(PDO::FETCH_ASSOC);
					
					if(count($rs_pins) == 0){  
						
						$sql = "select * from pins_store where pin_codigo = '".base64_encode($aes->encrypt($spin_codigo))."'";
						$query = $connection->prepare($sql);
						$rs_pins_store = $query->fetchAll(PDO::FETCH_ASSOC);
						
						if(count($rs_pins_store) == 0) {
							
							$sql = "insert into pins (pin_serial, pin_codigo, opr_codigo, pin_valor, pin_lote_codigo, pin_dataentrada, pin_canal, pin_horaentrada,pin_status,pin_validade) values ('".$spin_serial."', '".$spin_codigo."', ".$value["opr_codigo"].", ".$value["ogpm_valor"].", ".$rs_lote["max_pin_lote_codigo"].", CURRENT_TIMESTAMP, 's', NOW(),'1',(NOW() + interval '6 month'));";
							$query = $connection->prepare($sql);
							$query->execute();
							
							$rowCount = $query->rowCount();
							
							echo $spin_codigo;
							if($rowCount == 0) {
								die();
							}
							
							$pins_final .= $spin_codigo . ", ";
							$i++;
						}
					}
				}
				
			}
			
			saveLog($pins_final, $rs_lote["max_pin_lote_codigo"], $value["opr_codigo"], $value["ogpm_valor"]);
		}
	}
}
?>