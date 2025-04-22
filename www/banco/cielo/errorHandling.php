<?php
// Verifica em Resposta XML a ocorrência de erros 
// Parâmetros: XML de envio, XML de Resposta
function VerificaErro($vmPost, $vmResposta)
{
        $error_msg = null;

        try 
        {
                if(stripos($vmResposta, "SSL certificate problem") !== false)
                {
                        throw new Exception("CERTIFICADO INVÁLIDO - O certificado da transação não foi aprovado", "099");
                }

                $objResposta = simplexml_load_string($vmResposta, null, LIBXML_NOERROR);
                if($objResposta == null)
                {
                        throw new Exception("HTTP READ TIMEOUT - o Limite de Tempo da transação foi estourado", "099");
                }
        }
        catch (Exception $ex)
        {
                $error_msg = "     Código do erro: " . $ex->getCode() . PHP_EOL;
                $error_msg .= "     Mensagem: " . $ex->getMessage() . PHP_EOL;
                $error_msg .= "     XML de envio: " . PHP_EOL . $vmPost;
                gravaLog_CIELO("Ocorreu um erro em sua transação (1)".PHP_EOL." Detalhes do erro: ".$error_msg.PHP_EOL);
                return true;
        }

        if($objResposta->getName() == "erro")
        {
                $error_msg  = "     Código do erro: " . $objResposta->codigo . PHP_EOL;
                $error_msg .= "     Mensagem: " . utf8_decode($objResposta->mensagem) . PHP_EOL;
                $error_msg .= "     XML de envio: " . PHP_EOL . $vmPost;
                gravaLog_CIELO("Ocorreu um erro em sua transação (2)".PHP_EOL." Detalhes do erro: ".$error_msg.PHP_EOL." objResposta: ".print_r($objResposta, true).PHP_EOL);
        }
}


// Grava erros no arquivo de log
function Handler($eNum, $eMsg, $file, $line, $eVars)
{
        $e = "";
        $Data = date("Y-m-d H:i:s (T)");

        $errortype = array(
                        E_ERROR 			=> 'ERROR',
                        E_WARNING			=> 'WARNING',
                        E_PARSE				=> 'PARSING ERROR',
                        E_NOTICE			=> 'RUNTIME NOTICE',
                        E_CORE_ERROR		=> 'CORE ERROR',
                        E_CORE_WARNING      => 'CORE WARNING',
        E_COMPILE_ERROR     => 'COMPILE ERROR',
        E_COMPILE_WARNING   => 'COMPILE WARNING',
        E_USER_ERROR        => 'ERRO NA TRANSACAO',
        E_USER_WARNING      => 'USER WARNING',
        E_USER_NOTICE       => 'USER NOTICE',
        E_STRICT            => 'RUNTIME NOTICE',
        E_RECOVERABLE_ERROR	=> 'CATCHABLE FATAL ERROR'
                        );

        $e .= "**********************************************************".PHP_EOL;
        $e .= $Data . " - ERROR: " . $eNum . " " . $errortype[$eNum] . " - ";
        $e .= "     ARQUIVO: " . $file . "(Linha " . $line .")".PHP_EOL;
        $e .= "     MENSAGEM: " . PHP_EOL . $eMsg .PHP_EOL.PHP_EOL;

        error_log($e, 3, $GLOBALS['logFile']);

}
?>