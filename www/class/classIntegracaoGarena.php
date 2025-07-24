<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

class Garena
{

	private $conta;
	private $codGarena;
	private $rolesGarena = [];
	private $valorResgate;
	private $venda;
	private $idPin;
	private $txn_id;
	private $dataUtilizacao;
	private $type;
	public $error = [];
	private $url;

	public function __construct($idPin, $conta, $type = "pdv", $idvenda = 0, $atimo = false, $produtoAtimo = false)
	{

		$this->url = ["producao"=> [ getenv("GARENA_URL_ROLES"), getenv("GARENA_URL_PARTNER") ], "homologacao"=> [ getenv("GARENA_URL_ROLES_HOMOLOG"), getenv("GARENA_URL_PARTNER_HOMOLOG") ]];

	    if($atimo == false){
			if($this->testeConexaoBanco()){
				$this->setConta($conta);
				$this->setCodigo($idPin[0]);
				if (empty($this->error)) {

					if ($this->recuperaPacoteProduto($type, $idvenda) === false) {
						array_push($this->error, ["Erro" => "Não foi possivel fazer o resgate, pacote garena não encontardo (EPP0005)."]);
					} else {
						$this->verificaContaPorJogo();
						$situacao = $this->situacaoProcesso();
						if ($situacao == null || $situacao == 'LIBERADO' || $situacao != "CONFIRMADO") {
							$this->travaProcesso();
						} else {
							array_push($this->error, ["Erro" => "Esse pedido Já foi resgatado ou já está em processo de resgate (EPP0043)."]);
						}
					}

				}
			} else {
				array_push($this->error, ["Erro" => "Resgate temporariamente indisponível, tente novamente mais tarde (EPP0001)."]);
			}
		} else {

			if ($produtoAtimo == 433 || $produtoAtimo == 355) {
				$this->codGarena = 100067;
			} else if ($produtoAtimo == 454 || $produtoAtimo == 374) {
				$this->codGarena = 100080;
			} else if ($produtoAtimo == 493) {
				$this->codGarena = 100130;
			} else if ($produtoAtimo == 569 || $produtoAtimo == 498) {
				$this->codGarena = 100151;
			}
			$this->setConta($conta);
		}
	}

	private function verificaContaPorJogo()
	{

		if ($this->codGarena == 100067) {

			$count = strlen($this->conta);
			if ($count < 8 || $count > 20) {
				array_push($this->error, ["Erro" => "Formato da conta garena digitada está invalido (EPP0002)."]);
			}

		} else if ($this->codGarena == 100130) {
			$count = strlen($this->conta);
			if ($count < 8 || $count > 20) {
				array_push($this->error, ["Erro" => "Formato da conta garena digitada está invalido (EPP0002)."]);
			}
		} else if ($this->codGarena == 100151) {
			$count = strlen($this->conta);
			if ($count < 8 || $count > 22) {
				array_push($this->error, ["Erro" => "Formato da conta garena digitada está invalido (EPP0002)."]);
			}
		} else {

			$count = strlen($this->conta);
			if ($count < 8 || $count > 20) {
				array_push($this->error, ["Erro" => "Formato da conta garena digitada está invalido (EPP0002)."]);
			}

		}

	}

	private function setConta($conta)
	{

		if ($conta == "" || $conta == null) {
			array_push($this->error, ["Erro" => "A conta digitada está invalida (EPP0003)."]);
		} else {
			$this->conta = $conta;
		}

	}

	private function setDataUtilizacao($data)
	{

		$this->dataUtilizacao = $data;

	}

	public function getDataUtilizacao()
	{

		return $this->dataUtilizacao;

	}

	private function setTxn_id($code)
	{

		$this->txn_id = $code;

	}

	public function getTxn_id()
	{

		return $this->txn_id;

	}

	private function setCodigo($pin)
	{

		if ($pin == "" || $pin == null) {
			array_push($this->error, ["Erro" => "O pin selecionado é invalido (EPP0004)."]);
		} else {

			if ($this->verificaSituacaoPin($pin)) {
				$this->idPin = $pin;
			} else {
				array_push($this->error, ["Erro" => "O pin selecionado não está disponivel para ser resgatado (EPP0006)."]);
			}

		}

	}

	private function testeConexaoBanco()
	{

		$con = $this->retornaConexao();
		if ($con == "" || $con == null || empty($con)) {
			return false;
		}
		return true;

	}

	public function setRoles($dados)
	{
		$roles = json_decode($dados, true);

		for ($num = 0; $num < count($roles); $num++) {
			$this->rolesGarena[$num]["code"] = $roles[$num]["code"];
			$this->rolesGarena[$num]["nome"] = $roles[$num]["nome"];
		}
	}

	public function getRoles()
	{
		return json_encode($this->rolesGarena);
	}

	private function recuperaPacoteProduto($type, $idvenda)
	{

		$con = $this->retornaConexao();

		if ($type == "pdv") {

			$this->type = "pdv";
			$sql = "SELECT * 
					FROM tb_dist_venda_games_modelo_pins 
					INNER JOIN tb_dist_venda_games_modelo 
					ON vgm_id = vgmp_vgm_id 
					WHERE vgmp_pin_codinterno = :idPin";

		} else {

			$this->type = "usuario";
			$sql = "SELECT * 
					FROM tb_venda_games_modelo_pins 
					INNER JOIN tb_venda_games_modelo 
					ON vgm_id = vgmp_vgm_id 
					WHERE vgmp_pin_codinterno = :idPin";
		}

		// Preparar e executar com bind seguro
		$comando = $con->prepare($sql);
		$comando->bindParam(':idPin', $this->idPin, PDO::PARAM_INT);
		$comando->execute();

		$resultado = $comando->fetch(PDO::FETCH_ASSOC);
		$produtoId = $resultado["vgm_ogp_id"];
		//$produtoModeloId = $resultado["vgm_ogpm_id"];
		$this->venda = $resultado["vgm_vg_id"];

		if ((int) $this->venda !== (int) $idvenda) {

			return false;
		}

		$this->valorResgate = $resultado["vgm_pin_valor"];

		switch ($produtoId) {
			case 355:
			case 433:
				$this->codGarena = 100067;
				break;
			case 374:
			case 454:
				$this->codGarena = 100080;
				break;
			case 498:
			case 569:
				$this->codGarena = 100151;
				break;
			case 493:
				$this->codGarena = 100130;
				break;
			default:
				$this->codGarena = "ERRO";
				break;
		}

		return true;

	}

	private function geraHash($conteudo, $ambiente)
	{
		require_once "/www/includes/functions.php";
		$informacao = "";
		for ($linha = 0; $linha < count($conteudo); $linha++) {
			$informacao .= $conteudo[$linha];
		}

		if($ambiente == "producao"){
			$chave = getenv('GARENA_HASH');
		}else{
			$chave = getenv('GARENA_HASH_DEV');
		}

		return bin2hex(hash_hmac("sha256", $informacao, $chave, true));
	}
	public function chamaGarena($metodo, $ambiente = "homologacao")
	{

		if (count($this->error) > 0) {

			// verifica se o usuario está fazendo varias vezes a mesma requisição
			if ($this->error[0]["Erro"] === "Esse pedido Já foi resgatado ou já está em processo de resgate (EPP0043).") {
				return json_encode($this->error[0]);
			} else {
				$this->destravaProcesso();
				return json_encode($this->error[0]);
			}

		}

		if ($this->verificaPinLog() === true) {
			return json_encode(["Erro" => "Esse pin já foi utilizado (EPP0007)."]);
		}

		if($this->verificaValidade() == false){
			return json_encode(["Erro" => "Esse pin não está mais válido, nossos pins possuem 6 meses de validade (EPP0031)."]);
		}

		if ($this->codGarena === "ERRO") {
			array_push($this->error, ["Erro" => "Não foi possivel finalizar o resgate, código garena não encontrado (EPP0008)."]); //Codigo do produto garena está invalido
			$this->destravaProcesso();
			return json_encode($this->error[0]);
		}

		$disparo = curl_init();

		if ($metodo == "GET") {
			// Monta disparo para recuperar as roles do usuário
			$hashSha256 = $this->geraHash([$this->codGarena, $this->conta], $ambiente);
			curl_setopt_array($disparo, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 120,
				CURLOPT_HTTPHEADER => [
					"Authorization: Signature " . $hashSha256
				],
				CURLOPT_URL => $this->url[$ambiente][0] . "?app_id=" . $this->codGarena . "&player_id=" . $this->conta . "&channel_name=eprepag_br"
			]);

			$resultado = curl_exec($disparo);

			$tempoDuracao = curl_getinfo($disparo, CURLINFO_TOTAL_TIME);

			$this->salvaLog($resultado, "valida", $this->conta, $this->idPin, $tempoDuracao);
			if ($resultado == null || $resultado == "" || $resultado == false) {
				$this->destravaProcesso();
				array_push($this->error, ["Erro" => "Não foi possivel fazer o resgate do pin (EPP0009)."]);
				return false;
			}

			$resultado = json_decode($resultado, true);
			$informacoesDisparo = curl_getinfo($disparo);
			curl_close($disparo);

			if ($informacoesDisparo["http_code"] == 200) {

				if ($resultado["error"] == 0) {

					$this->destravaProcesso();
					// Pegar os papeis existentes do usuário
					for ($num = 0; $num < count($resultado["roles"]); $num++) {
						$this->rolesGarena[$num]["code"] = $resultado["roles"][$num]["packed_role_id"];
						$this->rolesGarena[$num]["nome"] = $resultado["roles"][$num]["role"];
					}

				} else {
					$this->destravaProcesso();
					if ($resultado["error"] == 98 || $resultado["error"] == 99) {

						/*$fileName = "/www/public_html/ajax/garena/erroServidor.txt";
						$file = fopen($fileName, "a+");
						fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\r");
						fwrite($file, "Foi encontrado erro de servidor na conexão com a garena\r");
						fwrite($file, json_encode($resultado)."\r");
						fwrite($file, str_repeat("*", 50)."\r");
						fclose($file);*/
						array_push($this->error, ["Erro" => "Não foi possivel finalizar o resgate, tente novamente (EPP0009)."]);

					} else {
						array_push($this->error, ["Erro" => $this->catalogoErro($resultado["error"])]);
					}

				}

			} elseif ($informacoesDisparo["http_code"] == 400) {
				$this->destravaProcesso();
				array_push($this->error, ["Erro" => "Não foi possivel finalizar o resgate, tente novamente (EPP0009)."]); //O channel_name garena está invalido
			} elseif ($informacoesDisparo["http_code"] == 403) {
				$this->destravaProcesso();
				array_push($this->error, ["Erro" => "Não foi possivel finalizar o resgate, tente novamente (EPP0009)."]); //O IP não está na whitelist
			}

		} elseif ($metodo == "POST") {

			/// verifica se foi retornado o ROLE ID do usuário garena
			if (count($this->rolesGarena) == 0) {
				$this->destravaProcesso();
				return json_encode(["Erro" => "Nenhum Usuário foi encontrado com essa conta (EPP0010)."]);
			}

			if ($this->verificaPinGuid() == false) {
				return json_encode(["Erro" => "Não é possivel realizar o resgate para esse pin (EPP0011)."]);
			}

			$test = ($ambiente == "homologacao") ? 1 : 0;
			$currecy = "BRL";
			$ip = $_SERVER["REMOTE_ADDR"];


			// Livrodjx has been here	
			switch ($this->valorResgate) {
				case 4:
					$this->valorResgate = 4.49;
					break;
				case 5:
					$this->valorResgate = 4.49;
					break;
				case 14:
					$this->valorResgate = 13.99;
					break;
				case 21:
					$this->valorResgate = 20.99;
					break;
				case 45:
					$this->valorResgate = 44.99;
					break;
				case 88:
					$this->valorResgate = 87.99;
					break;
				case 210:
					$this->valorResgate = 209.99;
					break;
			}
			//echo intval($this->valorResgate * 100);

			/// guardar guid
			$guid = "TXN-" . sprintf("%05X-%05X-%05X-%05X", mt_rand(10000, 90000), mt_rand(20000, 999999), mt_rand(50000, 999999), mt_rand(15000, 999999)); //
			/// Ler os roles em loop : $this->rolesGarena
			$hashSha256 = $this->geraHash([$this->codGarena, $this->conta, $this->rolesGarena[0]["code"], $guid, intval($this->valorResgate * 100), $currecy, $ip], $ambiente);

			curl_setopt_array($disparo, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_TIMEOUT => 150,
				CURLOPT_POSTFIELDS => json_encode(["test_mode" => $test, "app_id" => $this->codGarena, "player_id" => $this->conta, "packed_role_id" => $this->rolesGarena[0]["code"], "txn_id" => $guid, "amount" => intval($this->valorResgate * 100), "currency_code" => $currecy, "ip_address" => $ip]),
				CURLOPT_HTTPHEADER => [
					"Authorization: Signature " . $hashSha256
				],
				CURLOPT_URL => $this->url[$ambiente][1]
			]);
			$resultado = curl_exec($disparo);

			$resultado = curl_exec($disparo);

			$tempoDuracao = curl_getinfo($disparo, CURLINFO_TOTAL_TIME);

			$this->salvaLog($resultado, "credito", $this->conta, $this->idPin, $tempoDuracao);
			if ($resultado == null || $resultado == "" || $resultado == false) {
				//$this->destravaProcesso();
				array_push($this->error, ["Erro" => "Não foi possivel fazer o resgate do pin (EPP0012)."]);
				return false;
			}
			$resultado = json_decode($resultado, true);

			$informacoesDisparo = curl_getinfo($disparo);
			curl_close($disparo);

			if ($informacoesDisparo["http_code"] == 200) {

				if ($resultado["error"] == 0) {
					$this->processoConfirmado();
					$this->atualizarImpressao();
					if ($this->cadastroGuid($guid, $this->idPin)) {
						if ($this->cadastroTransacaoGarena($resultado["display_txn_id"], $this->idPin)) {
							if ($this->cadastroCadastroGarena()) {
								// CODIGO ATUALIZADO PARA EVITAR ERRO DE INSERT
								$this->setTxn_id($resultado["display_txn_id"]);
							} else {
								//array_push($this->error, ["Erro"=>"Erro no cadastro da conta Garena (EPP0014)."]);
								$file = fopen("/www/log/problema_GARENA.txt", "a+");
								fwrite($file, "DATA: " . date("d-m-Y H:i:s") . "\r");
								fwrite($file, "ERRO FINAL CONTA USUADA\r");
								fwrite($file, "CONTA USADA: " . $this->conta . "\r");
								fwrite($file, str_repeat("*", 50) . "\r");
								fclose($file);
							}
						} else {
							//$this->destravaProcesso();
							//array_push($this->error, ["Erro"=>"Erro no cadastro do código gerado pela Garena (EPP0015)."]);
							$file = fopen("/www/log/problema_GARENA.txt", "a+");
							fwrite($file, "DATA: " . date("d-m-Y H:i:s") . "\r");
							fwrite($file, "ERRO FINAL GUID GARENA\r");
							fwrite($file, "CONTA USADA: " . $this->conta . "\r");
							fwrite($file, str_repeat("*", 50) . "\r");
							fclose($file);
						}
					} else {
						//$this->destravaProcesso();
						//array_push($this->error, ["Erro"=>"Erro no cadastro do guid gerado pela EPP (EPP0016)."]);
						$file = fopen("/www/log/problema_GARENA.txt", "a+");
						fwrite($file, "DATA: " . date("d-m-Y H:i:s") . "\r");
						fwrite($file, "ERRO FINAL GUID EPP\r");
						fwrite($file, "CONTA USADA: " . $this->conta . "\r");
						fwrite($file, str_repeat("*", 50) . "\r");
						fclose($file);
					}
				} else {
					if ($resultado["error"] != 2) {
						$this->destravaProcesso();
					}
					if ($resultado["error"] == 98 || $resultado["error"] == 99) {
						array_push($this->error, ["Erro" => "Não foi possivel finalizar o resgate, tente novamente (EPP0013)."]);
					} else {
						array_push($this->error, ["Erro" => $this->catalogoErro($resultado["error"])]);
					}

				}

			} elseif ($informacoesDisparo["http_code"] == 404) {
				$this->destravaProcesso();
				array_push($this->error, ["Erro" => "Não foi possivel finalizar o resgate, tente novamente (EPP0012)."]); //O channel_name garena está invalido
			} elseif ($informacoesDisparo["http_code"] == 403) {
				$this->destravaProcesso();
				array_push($this->error, ["Erro" => "Não foi possivel finalizar o resgate, tente novamente (EPP0012)."]); //O IP não está na whitelist
			} elseif ($informacoesDisparo["http_code"] == 400) {
				$this->destravaProcesso();
				array_push($this->error, ["Erro" => "Não foi possivel finalizar o resgate, tente novamente (EPP0012)."]); //Alguma tipagem dos parametros então invalidas
			}

		}

		$validacao = $this->verificaProcesso();
		return $validacao;

	}

	private function verificaProcesso()
	{

		if (count($this->error) > 0) {
			return json_encode($this->error[0]);
		} else {
			return true;
		}

	}

	private function travaProcesso()
	{

		$con = $this->retornaConexao();

		$sql = "update pins set pin_status_trava = 'PROCESSANDO' where pin_codinterno = :PIN;";
		$comando = $con->prepare($sql);
		$comando->bindValue(":PIN", $this->idPin);
		$comando->execute();
		if ($comando->rowCount() > 0) {
			return true;
		}
		return false;

	}

	private function destravaProcesso()
	{

		$con = $this->retornaConexao();

		$sql = "update pins set pin_status_trava = 'LIBERADO' where pin_codinterno = :PIN and pin_status_trava <> 'CONFIRMADO';";
		$comando = $con->prepare($sql);
		$comando->bindValue(":PIN", $this->idPin);
		$comando->execute();
		if ($comando->rowCount() > 0) {
			return true;
		}
		return false;

	}

	private function processoConfirmado()
	{

		$con = $this->retornaConexao();

		$sqlinsert = "insert into pins_integracao_historico(pih_data,pih_ip_id,pih_pin_id,pih_id,pih_codretepp,pin_status)values(CURRENT_TIMESTAMP,:IP,:PIN,124,2,8);";
		$insert = $con->prepare($sqlinsert);
		$insert->bindValue(":PIN", $this->idPin);
		$insert->bindValue(":IP", $_SERVER["SERVER_ADDR"]);
		$insert->execute();

		usleep(140);
		$sqlDataUtilizacao = "select pih_data from pins_integracao_historico where pih_pin_id = :PIN;";
		$utilizacao = $con->prepare($sqlDataUtilizacao);
		$utilizacao->bindValue(":PIN", $this->idPin);
		$utilizacao->execute();
		$resultado = $utilizacao->fetch(PDO::FETCH_ASSOC);
		$this->setDataUtilizacao($resultado["pih_data"]);

		$sql = "update pins set pin_status = 8,pin_status_trava = 'CONFIRMADO' where pin_codinterno = :PIN;";
		$comando = $con->prepare($sql);
		$comando->bindValue(":PIN", $this->idPin);
		$comando->execute();
		if ($comando->rowCount() > 0) {
			return true;
		}
		return false;

	}

	private function situacaoProcesso()
	{

		$con = $this->retornaConexao();

		$sql = "select pin_status_trava from pins where pin_codinterno = :PIN;";
		$comando = $con->prepare($sql);
		$comando->bindValue(":PIN", $this->idPin);
		$comando->execute();
		$resultado = $comando->fetch(PDO::FETCH_ASSOC);
		if ($resultado != false && count($resultado) > 0) {
			return $resultado["pin_status_trava"];
		}
		return false;

	}

	private function verificaSituacaoPin($idpin)
	{

		$con = $this->retornaConexao();

		$sql = "select pin_status from pins where pin_codinterno = :PIN;";
		$comando = $con->prepare($sql);
		$comando->bindValue(":PIN", $idpin);
		$comando->execute();
		$resultado = $comando->fetch(PDO::FETCH_ASSOC);
		if ($resultado != false && count($resultado) > 0) {

			if ($resultado["pin_status"] == 8 || $resultado["pin_status"] == 9) {
				return false;
			} else {
				return true;
			}

		}
		return false;

	}

	private function atualizarImpressao()
	{

		$con = $this->retornaConexao();

		if ($this->type == "pdv") {
			$sql = "update tb_dist_venda_games_modelo_pins set vgmp_impressao_qtde = 1, vgmp_impressao_ult_data = CURRENT_TIMESTAMP where vgmp_pin_codinterno = :PIN;";
		} else {
			$sql = "update tb_venda_games_modelo_pins set vgmp_impressao_qtde = 1, vgmp_impressao_ult_data = CURRENT_TIMESTAMP where vgmp_pin_codinterno = :PIN;";
		}
		$comando = $con->prepare($sql);
		$comando->bindValue(":PIN", $this->idPin);
		$comando->execute();
		if ($comando->rowCount() > 0) {
			return true;
		}
		return false;

	}

	private function cadastroGuid($guid, $idPin)
	{

		$con = $this->retornaConexao();

		$sql = "update pins set pin_guid_epp = :GUID where pin_codinterno = :PIN;";
		$comando = $con->prepare($sql);
		$comando->bindValue(":GUID", $guid);
		$comando->bindValue(":PIN", $idPin);
		$comando->execute();
		if ($comando->rowCount() > 0) {
			return true;
		}
		return false;

	}

	private function cadastroCadastroGarena()
	{

		$con = $this->retornaConexao();

		$sql = "update pins set pin_game_id = :CONTA where pin_codinterno = :PIN;";
		$comando = $con->prepare($sql);
		$comando->bindValue(":CONTA", $this->conta);
		$comando->bindValue(":PIN", $this->idPin);
		$comando->execute();
		if ($comando->rowCount() > 0) {
			return true;
		}
		return false;

	}

	private function verificaPinGuid()
	{

		$con = $this->retornaConexao();
		$sql = "select * from pins where pin_guid_parceiro is null and pin_codinterno = :PIN;";
		$comando = $con->prepare($sql);
		$comando->bindValue(":PIN", $this->idPin);
		$comando->execute();
		$retorno = $comando->fetch(PDO::FETCH_ASSOC);
		if ($retorno == false || $retorno == "") {
			return false;
		}
		return true;
	}

	private function verificaPinLog()
	{

		$con = $this->retornaConexao();
		$sql = "select * from pins_integracao_historico where pih_pin_id = :PIN;";
		$comando = $con->prepare($sql);
		$comando->bindValue(":PIN", $this->idPin);
		$comando->execute();
		$retorno = $comando->fetch(PDO::FETCH_ASSOC);

		//$parametros = fopen('/www/log/ggg_GARENA.txt', 'a+');
		if ($retorno == false || $retorno == "") {
			/*fwrite($parametros, "DATA: ".date("d-m-Y H:i:s")."\r");
			fwrite($parametros, "sucesso\r");
			fwrite($parametros, json_encode($retorno)."\r");
			fwrite($parametros, "pin: ".$this->idPin."\r");
			fwrite($parametros, str_repeat("*", 50)."\r");
			fclose($parametros);*/
			return false;
		}
		/*
		fwrite($parametros, "DATA: ".date("d-m-Y H:i:s")."\r");
		fwrite($parametros, "erro log\r");
		fwrite($parametros, str_repeat("*", 50)."\r");
		fclose($parametros);*/
		return true;
	}

	private function verificaValidade()
	{
		$con = $this->retornaConexao();

		$sql = "SELECT 1 
				FROM pins 
				WHERE pin_codinterno = :PIN 
				  AND pin_validade >= CURRENT_DATE;";

		$comando = $con->prepare($sql);
		$comando->bindValue(":PIN", $this->idPin);
		$comando->execute();

		// Se encontrou, está válido
		if ($comando->fetchColumn()) {
			return true;
		}

		return false;
	}


	private function cadastroTransacaoGarena($guid, $idPin)
	{

		$con = $this->retornaConexao();

		$sql = "update pins set pin_guid_parceiro = :GUID where pin_codinterno = :PIN;";
		$comando = $con->prepare($sql);
		$comando->bindValue(":GUID", $guid);
		$comando->bindValue(":PIN", $idPin);
		$comando->execute();
		if ($comando->rowCount() > 0) {
			return true;
		}
		return false;

	}

	private function catalogoErro($codigo)
	{

		$ERROS = [
			1 => "Falha ao fazer o regaste dos créditos (EPP0014).", //Falha
			2 => "Os créditos ainda não foram créditados na sua conta (EPP0015).", // Pendente
			3 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0016).", //Sem cabeçalho de autorização
			4 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0017).", //Assinatura inválida
			5 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0018).", //Assinatura incompatível
			7 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0019).", //Moeda errada
			8 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0020).", //Txn id muito longo
			9 => "A conta não foi encontrada, verifique a conta (EPP0021).", //Papel não encontrado
			10 => "Este usuário está banido (EPP0022).",
			11 => "Modo de teste não disponível (EPP0023).",
			12 => "Conta garena não encontrada, verifique a conta digitada (EPP0024).",
			13 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0025).", //Endereço IP inválido
			14 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0026).", // Modo de teste não definido
			15 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0027).", //Quantidade errada
			16 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0028).", //ID de aplicativo errado
			17 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0029).", //Cartão inválido
			18 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0030).", //Cartão usado
			19 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0031).", // Cartão expirado
			20 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0032).", // ID de transação já existe
			21 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0033).", //Erro na quantidade de jogo
			22 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0034).", // Cartão está em uso (apenas para resgate de cartão, este pino foi anexado com outro transação, e essa transação está prestes a tentar resgatar o pin (o pin não foi tocado ainda), o que poderia levar a get140_card_info bem-sucedidos se outro usuário quiser verificar o cartão informações)
			23 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0035).", // id de referência invalido (apenas para API 2.5)
			24 => "A conta digitada está bloqueada para este tipo de resgate (EPP0036).", //Região de topup bloqueada para jogador
			25 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0037).", // Muitos pedidos
			26 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0038).", //Limite por hora excedido
			27 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0039).", //Limite diário excedido
			28 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0040).", //Limite de hora em hora excedido
			29 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0041).", //Limite diário do canal excedido
			98 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0013).", //Servidor temporariamente não disponível
			99 => "Não foi possivel realizar o resgate, por favor tente novamente mais tarde (EPP0013)." //Erro do servidor			
		];

		//$this->salvaLog($ERROS[$codigo], "ERRO", $this->conta);

		// pega os erros que o usuario pode receber na tela 
		/*switch($codigo){

			case 9:
				 $retorno = (isset($ERROS[$codigo]))?$ERROS[$codigo]:"Não foi possivel realizar seu resgate, teste novamente mais tarde";
			break;
			case 10:
				 $retorno = (isset($ERROS[$codigo]))?$ERROS[$codigo]:"Não foi possivel realizar seu resgate, teste novamente mais tarde";
			break;
			case 98:
				 $retorno = (isset($ERROS[$codigo]))?$ERROS[$codigo]:"Não foi possivel realizar seu resgate, teste novamente mais tarde";
			break;
			case 12:
				 $retorno = (isset($ERROS[$codigo]))?$ERROS[$codigo]:"Não foi possivel realizar seu resgate, teste novamente mais tarde";
			break;
			case 24:
				 $retorno = (isset($ERROS[$codigo]))?$ERROS[$codigo]:"Não foi possivel realizar seu resgate, teste novamente mais tarde";
			break;
			default:
				 $retorno = "Não foi possivel realizar seu resgate, teste novamente mais tarde";
			break;			
		}*/

		$retorno = (isset($ERROS[$codigo])) ? $ERROS[$codigo] : "Não foi possivel realizar seu resgate, teste novamente mais tarde (EPP0042).";
		return $retorno;

	}

	private function salvaLog($conteudo, $type, $conta, $pin, $tempoDuracao = 0)
	{

		$fileName = "/www/log/retorno_GARENA.txt";
		$file = fopen($fileName, "a+");
		fwrite($file, "DATA: " . date("d-m-Y H:i:s") . "\r");
		fwrite($file, "TIPO: " . $type . "\r");
		fwrite($file, "CONTA: " . $conta . "\r");
		fwrite($file, "PIN UTILIZADO: " . $pin . "\r");
		fwrite($file, "RETORNO: " . $conteudo . "\r");
		fwrite($file, "TEMPO DURACAO: " . $tempoDuracao . "\r");
		fwrite($file, str_repeat("*", 50) . "\r");
		fclose($file);

	}

	public static function BuscaIdPin($pin, $id = "")
	{

		$conexao = ConnectionPDO::getConnection();

		if ($id != "") {

			$sqlPin = $conexao->getLink()->prepare("select pin_codigo from pins where pin_codinterno = :PIN;");
			$sqlPin->bindValue(":PIN", $pin);
			$sqlPin->execute();
			$retornoPin = $sqlPin->fetch(PDO::FETCH_ASSOC);

			return $retornoPin["pin_codigo"];

		}

		$sqlPin = $conexao->getLink()->prepare("select pin_status from pins where pin_codigo = :PIN;");
		$sqlPin->bindValue(":PIN", $pin);
		$sqlPin->execute();
		$retornoPin = $sqlPin->fetch(PDO::FETCH_ASSOC);

		if ($retornoPin["pin_status"] == "3") {
			$sql = $conexao->getLink()->prepare("select pin_codinterno,vgm_vg_id,vgm_nome_produto,vgm_nome_modelo,pin_status,pin_valor from pins inner join tb_venda_games_modelo_pins on vgmp_pin_codinterno = pin_codinterno inner join tb_venda_games_modelo on vgmp_vgm_id = vgm_id where pin_codigo = :PIN;");
		} else if ($retornoPin["pin_status"] == "6") {
			$sql = $conexao->getLink()->prepare("select pin_codinterno,vgm_vg_id,vgm_nome_produto,vgm_nome_modelo,pin_status,pin_valor from pins inner join tb_dist_venda_games_modelo_pins on vgmp_pin_codinterno = pin_codinterno inner join tb_dist_venda_games_modelo on vgmp_vgm_id = vgm_id where pin_codigo = :PIN;");
		} else {
			// caso status seja 8 ou diferente
			return false;
		}

		$sql->bindValue(":PIN", $pin);
		$sql->execute();
		return $sql->fetch(PDO::FETCH_ASSOC);

	}

	public static function verificaQtdePin($pin, $ip, $data)
	{

		$conexao = ConnectionPDO::getConnection();
		$limite_pin = 6;

		// verifica se o pin j� foi utilizado mais de tres vezes
		$sql = "select pin, qtde from trava_qtde_pin where ip = :IP and pin = :PIN and date(data_inclusao) = :DT;";
		$query = $conexao->getLink()->prepare($sql);
		$query->bindValue(":IP", $ip);
		$query->bindValue(":PIN", $pin);
		$query->bindValue(":DT", $data);
		$query->execute();
		$resultado = $query->fetch(PDO::FETCH_ASSOC);

		if ($resultado == false || $resultado == "") {

			// insert
			try {
				$sqlInsert = "insert into trava_qtde_pin(ip,data_inclusao,qtde,pin)values(:IP,CURRENT_TIMESTAMP,:QTD,:PIN);";
				$insert = $conexao->getLink()->prepare($sqlInsert);
				$insert->bindValue(":IP", $ip);
				$insert->bindValue(":QTD", 1);
				$insert->bindValue(":PIN", $pin);
				$insert->execute();

				if ($insert->rowCount() > 0) {
					return true;
				}
			} catch (PDOException $e) {
				return false;
			}

			return false;
		} else {

			usleep(5000);
			if ($resultado["qtde"] > $limite_pin) {
				return false;
			} else {

				//update
				$sqlUpdate = "update trava_qtde_pin set qtde = qtde + 1 where pin = :PIN and qtde <= 6;";
				$update = $conexao->getLink()->prepare($sqlUpdate);
				$update->bindValue(":PIN", $pin);
				$update->execute();

				if ($update->rowCount() > 0) {

					return true;

				}

				return false;
			}

		}

	}

	public static function verificaLotePin($ip, $data)
	{

		$conexao = ConnectionPDO::getConnection();
		$limite_pin = 2;

		// verifica se tem mais de um pin bloqueado para o IP
		$sql = "select count(*) as total from trava_qtde_pin where date(data_inclusao) = :DT and ip = :IP and qtde >= 6 group by ip, date(data_inclusao);";
		$query = $conexao->getLink()->prepare($sql);
		$query->bindValue(":IP", $ip);
		$query->bindValue(":DT", $data);
		$query->execute();
		$resultado = $query->fetch(PDO::FETCH_ASSOC);

		if ($resultado != false || $resultado != "") {
			if ($resultado["total"] >= $limite_pin) {
				return false;
			} else {
				return true;
			}
		}

		return true;

	}

	public static function salvaRetorno($pin, $ip, $dados)
	{

		$conexao = ConnectionPDO::getConnection();

		$sql = "update trava_qtde_pin set retorno = :DADOS where pin = :PIN and ip = :IP;";
		$query = $conexao->getLink()->prepare($sql);
		$query->bindValue(":IP", $ip);
		$query->bindValue(":DADOS", $dados);
		$query->bindValue(":PIN", $pin);
		$query->execute();

	}

	public static function verificaTokenRe($token)
	{

	    $dados = ["secret" => getenv("RECAPTCHA_SECRET_KEY_V3"), "response" => $token, "remoteip" => $_SERVER["REMOTE_ADDR"]];
		$curlToken = curl_init();
		curl_setopt_array($curlToken, [
		  CURLOPT_URL => getenv("RECAPTCHA_URL"),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			//CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POSTFIELDS => http_build_query($dados),
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/x-www-form-urlencoded'
			)
		]);
		//$retornoB = curl_exec($curlToken);
		$recebido = str_replace("\n", "", curl_exec($curlToken));
		$retorno = json_decode($recebido, true);
		curl_close($curlToken);

		$ff = fopen("/www/log/testeRecap2.txt", "a+");
		fwrite($ff, "data: " . date("d-m-Y H:i:s") . "\r\n");
		fwrite($ff, "ip: " . $_SERVER["REMOTE_ADDR"] . "\r\n");
		fwrite($ff, "token: " . $token . "\r\n");
		fwrite($ff, "dados: " . json_encode($dados) . "\r\n");
		fwrite($ff, "dados-recebidos: " . json_encode($retorno) . "\r\n");
		//fwrite($ff, "dados-R: ".http_build_query($dados)."\r\n");
		fwrite($ff, str_repeat("*", 45) . "\r");
		fclose($ff);

		if ($retorno["success"] == true) {
			if ($retorno["score"] >= 0.7) {
				return ["retorno" => true, "code" => 0]; //true
			} else {
				return ["retorno" => false, "code" => 1]; //false
			}
		}
		return ["retorno" => false, "code" => 2]; //false
	}

	private function retornaConexao()
	{
		$conexao = ConnectionPDO::getConnection();
		return $conexao->getLink();

	}

	public function __toString()
	{
		return json_encode(["ValorResgate" => $this->valorResgate, "venda" => $this->venda, "codigoGarena" => $this->codGarena, "conta" => $this->conta, "pinVinculado" => $this->idPin, "roles" => $this->rolesGarena]);
	}


}
?>