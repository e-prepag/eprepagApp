<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1);
require_once RAIZ_DO_PROJETO . 'banco/bradesco/config.inc.urls_bradesco.php';

define("BRADESCO_MSG_ERROR_LOG", "ERROR_LOG");

// Arquivo de Log onde serao registrados todos os erros gerados  
define("LOG_FILE_BRADESCO_WS_ERRORS",	RAIZ_DO_PROJETO . "log/log_BRADESCO_TRANSF_WS-Errors.log");

$ARRAY_ERROS = array('-401', '-399', '-398', '-201', '-202', '-203', '-205', '-206', '-207', '-208', '-299', '-501', '-999');

//Todos os erros possíveis de retorno
$ARRAY_ERROS_TOKEN_MENSAGEM = array(
                                        '-401' => 'Credencias de acesso não estão presentes no cabeçalho da requisição BASE_64(Email:ChaveDeSeguranca).',
                                        '-399' => 'Dados mínimos da requisição não informado (Verifique: merchantid, email e chave da loja).',
                                        '-399' => 'Dados mínimos da requisição não informado (Verifique: merchantid, email, chave da loja e tokenCode).',
                                        '-399' => 'Dados mínimos da requisição não informado (Verifique: merchantid, email, chave da loja, tokenCode e orderId).',
                                        '-399' => 'Dados mínimos da requisição não informado (Verifique: merchantid,email,chave da loja,tokenCode,dataInicial e dataFinal).',
                                        '-399' => 'Dados mínimos da requisição não informados ou inválidos (Verifique: offset e limit).',
                                        '-398' => 'Erro ao autenticar loja (Verifique: merchantid, email e chave da loja).',
                                        '-201' => 'Token inválido.',
                                        '-202' => 'Erro na criação do token.',
                                        '-203' => 'Erro na atualização do token.',
                                        '-205' => 'Erro na validação do token.',
                                        '-206' => 'Token não foi encontrado.',
                                        '-207' => 'Aguardar tempo limite para geração de um novo token.',
                                        '-208' => 'Token expirado. Necessário gerar um novo para continuar.',
                                        '-299' => 'Erro na geração do token.',
                                        '-501' => 'Nenhum registro encontrado.',
                                        '-999' => 'Falha na autenticação.'
                                    );


require_once("classGerais.php");
require_once("classXMLEstruturaBradescoTransf.php");
require_once("classBradescoTransferencia.php");
