<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1);
require_once RAIZ_DO_PROJETO . 'banco/bradesco/config.inc.urls_bradesco.php';

define("BRADESCO_MSG_ERROR_LOG", "ERROR_LOG");

// Arquivo de Log onde serao registrados todos os erros gerados  
define("LOG_FILE_BRADESCO_WS_ERRORS",	RAIZ_DO_PROJETO . "log/log_BRADESCO_TRANSF_WS-Errors.log");

$ARRAY_ERROS = array('-401', '-399', '-398', '-201', '-202', '-203', '-205', '-206', '-207', '-208', '-299', '-501', '-999');

//Todos os erros poss�veis de retorno
$ARRAY_ERROS_TOKEN_MENSAGEM = array(
                                        '-401' => 'Credencias de acesso n�o est�o presentes no cabe�alho da requisi��o BASE_64(Email:ChaveDeSeguranca).',
                                        '-399' => 'Dados m�nimos da requisi��o n�o informado (Verifique: merchantid, email e chave da loja).',
                                        '-399' => 'Dados m�nimos da requisi��o n�o informado (Verifique: merchantid, email, chave da loja e tokenCode).',
                                        '-399' => 'Dados m�nimos da requisi��o n�o informado (Verifique: merchantid, email, chave da loja, tokenCode e orderId).',
                                        '-399' => 'Dados m�nimos da requisi��o n�o informado (Verifique: merchantid,email,chave da loja,tokenCode,dataInicial e dataFinal).',
                                        '-399' => 'Dados m�nimos da requisi��o n�o informados ou inv�lidos (Verifique: offset e limit).',
                                        '-398' => 'Erro ao autenticar loja (Verifique: merchantid, email e chave da loja).',
                                        '-201' => 'Token inv�lido.',
                                        '-202' => 'Erro na cria��o do token.',
                                        '-203' => 'Erro na atualiza��o do token.',
                                        '-205' => 'Erro na valida��o do token.',
                                        '-206' => 'Token n�o foi encontrado.',
                                        '-207' => 'Aguardar tempo limite para gera��o de um novo token.',
                                        '-208' => 'Token expirado. Necess�rio gerar um novo para continuar.',
                                        '-299' => 'Erro na gera��o do token.',
                                        '-501' => 'Nenhum registro encontrado.',
                                        '-999' => 'Falha na autentica��o.'
                                    );


require_once("classGerais.php");
require_once("classXMLEstruturaBradescoTransf.php");
require_once("classBradescoTransferencia.php");
