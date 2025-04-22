<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "/www/genebra/Genebra.php";

$genebra = new Genebra("H");
# PEDIDO 
$data = '{
  "cpf": "04657151096",
  "data_nasc": "2000-02-16",
  "id_equipamento": 2,
  "pct_comissao": 5,
  "cep": "02839120",
  "valor_equipamento": 1000,
  "ano_fabricacao": 2015,
  "obs": ""
}';

$data = '{
  "renovacao": true,
  "dt_inicio_vigencia": "2022-12-14T20:07:10.867Z",
  "tp_cobertura": 1,
  "bonus_anterior": 0,
  "sinistros_anterior": 0,
  "dt_inicio_vigencia_anterior": "2022-12-14T20:07:10.867Z",
  "dt_final_vigencia_anterior": "2022-12-14T20:07:10.867Z",
  "ci": "string",
  "nome_cliente": "andre silva do nascimento",
  "tipo_pessoa_cliente": false,
  "cpf_cnpj_cliente": "04657151096",
  "estado_civil_cliente": 1,
  "data_nascimento_cliente": "2022-12-14T20:07:10.867Z",
  "data_prim_habilitacao_cliente": "2022-12-14T20:07:10.867Z",
  "genero_cliente": true,
  "fone": "string",
  "cep": "02839120",
  "cidade_residencia": "string",
  "uf_residencia": "string",
  "ano_modelo": 2009,
  "ano_fabricacao": 2005,
  "cod_fabricante": 0,
  "cod_fipe": 0,
  "chassi": "string",
  "placa": "string",
  "financiado": true,
  "tipo_utilizacao": 1,
  "cep_circulacao": "02839120",
  "cep_pernoite": "02839120",
  "tipo_local_pernoite": 1,
  "usa_trabalhar": true,
  "usa_estudar": true,
  "garagem_estudo": true,
  "garagem_trabalho": true,
  "km_anual": 0,
  "kit_gas": true,
  "blindagem": true,
  "passageiros": 0,
  "num_portas": 0,
  "jovem_condutor": true,
  "jovem_genero": true,
  "codigo_antifurto": 0,
  "valor_blindagem": 0,
  "valor_gas": 0,
  "tipo_isencao": 0,
  "nome_proprietario": "string",
  "tipo_pessoa_proprietario": true,
  "cpf_cnpj_proprietario": "04657151096",
  "estado_civil_proprietario": 1,
  "data_nascimento_proprietario": "2022-12-14T20:07:10.867Z",
  "is_danos_materiais": 0,
  "is_danos_corporais": 0,
  "is_app_morte": 0,
  "is_app_invalidez": 0,
  "cob_vidros": 0,
  "cob_farol": 0,
  "cob_despesas_extra": 0,
  "assist24hrs": 0,
  "carro_reserva": 0,
  "pct_comissao": 0,
  "obs": "string"
}';

$data = json_decode($data, true);
//$data = ["nome" => "", "idade" => 19];

$genebra->configRequest("https://plataforma.genebraseguros.com.br/api/v1/cotacao_auto", "POST", $data, "automovel");
var_dump($genebra->errors);
exit;
$return = $genebra->requestService();

//$genebra->configRequest("https://plataforma.genebraseguros.com.br/api/v1/cotacao_portateis", "POST", $data);
//$return = $genebra->requestService();

#CONSULTA

//$genebra->configRequest("https://plataforma.genebraseguros.com.br/api/v1/cotacao_portateis?id=24", "GET");
//$return = $genebra->requestService();

//$genebra->configRequest("https://plataforma.genebraseguros.com.br/api/v1/cotacao_auto?id=36", "GET");
//$return = $genebra->requestService();

var_dump($return);

?>