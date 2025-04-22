<?php

include_once "/www/db/connect.php";
include_once "/www/db/ConnectionPDO.php";

class Genebra{
	
	private $url;
	private $params;
	private $method;
	private $environment;
	private $conexao;
	public $errors;
	private $keys = ['H' => ['key' => 'glaucia@e-prepag.com.br','pass' => '6xmc$H'], 'P' => ['key' => '','pass' => '']];
	
	public function __construct($environment){
		$this->conexao = ConnectionPDO::getConnection()->getLink();
		$this->environment = $environment;
	}
	
	public function configRequest($url, $method, $params = [], $model = ""){
		$this->url = $url;
		$this->method = $method;
		if($method == "POST"){
			if(gettype($params) != "string" && gettype($params) != "NULL"){
				$responseJson = $this->verifyModel($params, $model);
				if(isset($responseJson["error"])){
					$this->errors = $responseJson;
				}else{
					$this->params = json_encode($params);
				}
			}else{
				$this->params = $params;
			}
		}
	}
	
	private function verifyModel($data, $model){
		$errors = ["invalidos" => []];
		foreach($data as $key => $value){
			$settings = $this->model($key, $model);
			if($settings["required"] == true){
				if(empty($value)){
					$errors["invalidos"] = ["o campo $key está vazio"];
				}else{
					if(isset($settings["min"]) && isset($settings["max"])){
						if(strlen($value) < $settings["min"] || strlen($value) > $settings["max"]){
							$errors["invalidos"] = ["o campo $key está com tamanho invalido"];
						}
					}
					if(is_numeric($value) && $settings["type"] != "string"){
						if(strpos($value, ".")){
							$value = settype($value, "double");
						}else{
							$value = settype($value, "integer");
						}
						if(gettype($value) != $settings["type"]){
							$errors["invalidos"][] = "o campo $key está com formato invalido";
						}
					}else{
						if(gettype($value) != $settings["type"]){
							$errors["invalidos"][] = "o campo $key está com formato invalido";
						}
					}
				}
			}
		}
		
		if(!empty($errors)){
			$this->errors = $errors;
		}
	}
	
	private function model($field, $model){
		switch($model){
			case "automovel":
			    $filds = [
				    "renovacao" => ["required" => false, "type" => "boolean"],
					"dt_inicio_vigencia" => ["required" => false, "type" => "string"],
					"tp_cobertura" => ["required" => false, "type" => "integer", "min" => 1, "max" => 2],
					"bonus_anterior" => ["required" => false, "type" => "integer", "min" => 0, "max" => 10],
					"sinistros_anterior" => ["required" => false, "type" => "integer"],
					"dt_inicio_vigencia_anterior" => ["required" => false, "type" => "string"],
					"dt_final_vigencia_anterior" => ["required" => false, "type" => "string"],
					"ci" => ["required" => true, "type" => "string"],
					"nome_cliente" => ["required" => true, "type" => "string"],
					"tipo_pessoa_cliente" => ["required" => false, "type" => "boolean"],
					"cpf_cnpj_cliente" => ["required" => true, "type" => "string", "min" => 11, "max" => 14],
					"estado_civil_cliente" => ["required" => false, "type" => "integer", "min" => 1, "max" => 5],
					"data_nascimento_cliente" => ["required" => false, "type" => "string"],
					"data_prim_habilitacao_cliente" => ["required" => false, "type" => "string"],
					"genero_cliente" => ["required" => false, "type" => "boolean"],
					"fone" => ["required" => false, "type" => "string"],
					"cep" => ["required" => true, "type" => "string"],
					"cidade_residencia" => ["required" => true, "type" => "string"],
					"uf_residencia" => ["required" => true, "type" => "string"],
					"ano_modelo" => ["required" => false, "type" => "integer", "min" => 1900, "max" => 2100],
					"ano_fabricacao" => ["required" => false, "type" => "integer", "min" => 1900, "max" => 2100],
					"cod_fabricante" => ["required" => false, "type" => "integer"],
					"cod_fipe" => ["required" => false, "type" => "integer"],
					"chassi" => ["required" => true, "type" => "string"],
					"placa" => ["required" => true, "type" => "string"],
					"financiado" => ["required" => false, "type" => "boolean"],
					"tipo_utilizacao" => ["required" => false, "type" => "integer", "min" => 1, "max" => 3],
					"cep_circulacao" => ["required" => true, "type" => "string"],
					"cep_pernoite" => ["required" => true, "type" => "string"],
					"tipo_local_pernoite" => ["required" => false, "type" => "integer", "min" => 1, "max" => 3],
					"usa_trabalhar" => ["required" => false, "type" => "boolean"],
					"usa_estudar" => ["required" => false, "type" => "boolean"],
					"garagem_estudo" => ["required" => false, "type" => "boolean"],
					"garagem_trabalho" => ["required" => false, "type" => "boolean"],
					"km_anual" => ["required" => false, "type" => "integer"],
					"kit_gas" => ["required" => false, "type" => "boolean"],
					"blindagem" => ["required" => false, "type" => "boolean"],
					"passageiros" => ["required" => false, "type" => "integer"],
					"num_portas" => ["required" => false, "type" => "integer"],
					"jovem_condutor" => ["required" => false, "type" => "boolean"],
					"jovem_genero" => ["required" => false, "type" => "boolean"],
					"codigo_antifurto" => ["required" => false, "type" => "integer", "min" => 0, "max" => 3],
					"valor_blindagem" => ["required" => false, "type" => "double"],
					"valor_gas" => ["required" => false, "type" => "double"],
					"tipo_isencao" => ["required" => false, "type" => "integer", "min" => 0, "max" => 2],
					"nome_proprietario" => ["required" => false, "type" => "string"],
					"tipo_pessoa_proprietario" => ["required" => false, "type" => "boolean"],
					"cpf_cnpj_proprietario" => ["required" => true, "type" => "string", "min" => 11, "max" => 14],
					"estado_civil_proprietario" => ["required" => false, "type" => "integer", "min" => 1, "max" => 5],
					"data_nascimento_proprietario" => ["required" => false, "type" => "string"],
					"is_danos_materiais" => ["required" => false, "type" => "double"],
					"is_danos_corporais" => ["required" => false, "type" => "double"],
					"is_app_morte" => ["required" => false, "type" => "double"],
					"is_app_invalidez" => ["required" => false, "type" => "double"],
					"cob_vidros" => ["required" => false, "type" => "integer", "min" => 1, "max" => 3],
					"cob_farol" => ["required" => false, "type" => "integer", "min" => 1, "max" => 2],
					"cob_despesas_extra" => ["required" => false, "type" => "double"],
					"assist24hrs" => ["required" => false, "type" => "double", "min" => 1, "max" => 4],
					"carro_reserva" => ["required" => false, "type" => "integer", "min" => 1, "max" => 10],
					"pct_comissao" => ["required" => false, "type" => "double"],
					"obs" => ["required" => false, "type" => "string"]
				];
				return isset($filds[$field])? $filds[$field]: false;
			break;
			case "bike":
			    $filds = [
				    "cpf" => ["required" => true, "type" => "string", "min" => 11, "max" => 11],
					"data_nasc" => ["required" => false, "type" => "string"],
					"valor_bike" => ["required" => false, "type" => "double"],
					"pct_comissao" => ["required" => false, "type" => "double"],
					"cep" => ["required" => true, "type" => "string"],
					"obs" => ["required" => false, "type" => "string"]
				];
				return isset($filds[$field])? $filds[$field]: false;
			break;
			case "bolsa":
			    $filds = [
				    "cpf" => ["required" => true, "type" => "string", "min" => 11, "max" => 11],
					"data_nasc" => ["required" => false, "type" => "string"],
					"renda_estimada" => ["required" => false, "type" => "double"],
					"genero" => ["required" => false, "type" => "boolean"],
					"valor_cobertura" => ["required" => false, "type" => "double", "min" => 500, "max" => 1500],
					"pct_comissao" => ["required" => false, "type" => "double", "min" => 5, "max" => 20],
					"cobertura_saque" => ["required" => false, "type" => "boolean"],
					"obs" => ["required" => false, "type" => "string"]
				];
				return isset($filds[$field])? $filds[$field]: false;
			break;
			case "cyber":
			    $filds = [
				    "cpf" => ["required" => true, "type" => "string", "min" => 11, "max" => 11],
					"data_nasc" => ["required" => false, "type" => "string"],
					"renda_estimada" => ["required" => false, "type" => "double"],
					"genero" => ["required" => false, "type" => "boolean"],
					"valor_cobertura" => ["required" => false, "type" => "double"],
					"pct_comissao" => ["required" => false, "type" => "double", "min" => 5, "max" => 20],
					"obs" => ["required" => false, "type" => "string"]
				];
				return isset($filds[$field])? $filds[$field]: false;
			break;
			case "garantia":
			    $filds = [
				    "cnpj_tomador" => ["required" => true, "type" => "string", "min" => 11, "max" => 14],
					"data_nasc" => ["required" => false, "type" => "string"],
					"renda_estimada" => ["required" => false, "type" => "double"],
					"genero" => ["required" => false, "type" => "boolean"],
					"valor_cobertura" => ["required" => false, "type" => "double"],
					"pct_comissao" => ["required" => false, "type" => "double", "min" => 5, "max" => 20],
					"obs" => ["required" => false, "type" => "string"]
				];
				return isset($filds[$field])? $filds[$field]: false;
			break;
			default:
			    return false;
			break;
		}
	}

	public function requestService(){
		
		if(!empty($this->errors)){
			return false;
		}
		$service = curl_init();
		$fileService = fopen("/www/genebra/log/service.txt", "w+");
		$hash_access = base64_encode($this->keys[$this->environment]["key"].":".$this->keys[$this->environment]["pass"]);
		curl_setopt_array($service, [
		    CURLOPT_URL => $this->url,
			CURLOPT_HTTPHEADER => [ 
			    "Content-type: application/json",
				"Authorization: Basic ".$hash_access,
				"Accept: application/json"
			],
		    CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_VERBOSE => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_STDERR => $fileService,
			CURLOPT_CUSTOMREQUEST => $this->method
		]);
		
		if($this->method == "POST"){
			curl_setopt($service, CURLOPT_POSTFIELDS, $this->params);
		}
		
		$data = curl_exec($service);
		$infoService = curl_getinfo($service);
		curl_close($service);
		
		if($infoService["http_code"] == 200){
			$this->writeLog($data, "/www/genebra/log/request.txt");
			return $data;
		}else if($infoService["http_code"] == 0){
            $this->writeLog($data, "/www/genebra/log/erroService.txt");			
			return $data;
		}else{
			$this->writeLog($data, "/www/genebra/log/erroService.txt");	 
			return $data; 
		}
	}
	
	private function writeLog($data, $file){
		$file = fopen($file, "a+");
		fwrite($file, "Data: ".date("d-m-Y H:i:s")."\n");
		fwrite($file, "Counteudo: ".$data."\n");
		fwrite($file, str_repeat("*", 50)."\n");
		fclose($file);
	}
	
}