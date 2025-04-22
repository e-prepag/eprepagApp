<?php
//Alterando o limeout do PHP para (CPF_TIMEOUT/1000) segundos
ini_set('max_execution_time', ((CPF_TIMEOUT / 1000) + 50));
ini_set('default_socket_timeout', ((CPF_TIMEOUT / 1000) + 5));

if ($_SERVER["REMOTE_ADDR"] == "201.93.162.169") {
        //error_reporting(E_ALL); 
        //ini_set("display_errors", 1); 
}
class classCPF
{

        private $soapClient;
        private $serviceCode;
        private $service_online;
        private $quantidade_limite;
        private $quantidade_contas;
        public $arraySoapClient;
        private $timeBenchmark;
        private $error_system;

        public function __construct($testaConexao = true)
        {

                /*
                    Início Benchmark
                 * gravando tempo inicial para finaliza-lo no destruct
                 */
                list($usec, $sec) = explode(" ", microtime());
                $this->timeBenchmark = ((float) $usec + (float) $sec);
                $this->set_error_system(NULL);
                /*
                    Fim do código do benchmark
                 */

                $this->set_service_status(false);
                $this->set_quantidade_limite(CPF_QUANTIDADE_LIMITE);
                $this->set_quantidade_contas(CPF_QUANTIDADE_CONTAS);
                try {
                        //Para Consulta CACHE
                        if (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE || CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_HUB) {
                                $this->set_service_status(true);
                        } //end if (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) 

                        //Para Consulta através de Parceiros
                        else {
                                if ($testaConexao) {
                                        $this->arraySoapClient = array(
                                                'location' => CPF_SERVICE_URL,
                                                'uri' => CPF_SERVICE_URL,
                                                'cache_wsdl' => WSDL_CACHE_NONE,
                                                'soap_version' => SOAP_1_1,//SOAP_1_2,
                                                //'encoding'	=> 'UTF-8',
                                                'encoding' => 'ISO-8859-1',
                                                'trace' => 1,
                                                'exceptions' => 1,
                                        );
                                        if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA) {
                                                $this->arraySoapClient['login'] = CPF_CLIENT_ID;
                                                $this->arraySoapClient['password'] = CPF_CLIENT_PASSWORD;
                                                $this->arraySoapClient['connection_timeout'] = (CPF_TIMEOUT / 1000);
                                        }

                                        $soapClient = @new SoapClient(CPF_WSDL_URL, $this->arraySoapClient);

                                        $this->set_service_status(true);

                                        $this->logEvents("Service enable!\n", CPF_MSG_ERROR_LOG);
                                }//end if($testaConexao)
                        }//end else do if (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) 

                } catch (SoapFault $e) {
                        $this->logEvents("Caught exception 1 (" . $e->faultcode . "): " . $e->getMessage() . PHP_EOL, CPF_MSG_ERROR_LOG);
                        $this->set_error_system($e->getMessage());
                }
        }//end function __construct()

        private function set_service_status($status)
        {
                $this->service_online = $status;
        }//end function set_service_status

        public function get_service_status()
        {
                return $this->service_online;
        }//end function get_service_status

        private function set_error_system($error)
        {
                $this->error_system = $error;
        }//end function set_error_system

        public function get_error_system()
        {
                return $this->error_system;
        }//end function get_error_system

        public function set_quantidade_limite($quantidade_limite)
        {
                $this->quantidade_limite = $quantidade_limite;
        }//end function set_quantidade_limite

        public function get_quantidade_limite()
        {
                return $this->quantidade_limite;
        }//end function get_quantidade_limite

        public function set_quantidade_contas($quantidade_contas)
        {
                $this->quantidade_contas = $quantidade_contas;
        }//end function set_quantidade_contas

        public function get_quantidade_contas()
        {
                return $this->quantidade_contas;
        }//end function get_quantidade_contas

        public function callService($typeOfService = '', $requestParams = array())
        {

                // Armazena na classe os dados do serviço informado 
                $cpfRequestRecord = $this->getRequestObject($typeOfService, $requestParams);

                try {
                        $this->soapClient = @new SoapClient(CPF_WSDL_URL, $this->arraySoapClient);

                } catch (SoapFault $e) {
                        $this->logEvents("Caught exception 2A (" . utf8_decode($e->faultcode) . "): " . utf8_decode($e->getMessage()) . PHP_EOL, CPF_MSG_ERROR_LOG, 0);
                        $this->set_error_system($e->getMessage());
                }


                if ($this->soapClient) {

                        try {
                                if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY) {

                                        //Convertendo Objeto em Array =====> Necessário somente para CREDIFY
                                        $cpfRequestRecord = $this->object_to_array($cpfRequestRecord);

                                        //Convertendo Array em XML e depois XML em String =====> Necessário somente para CREDIFY
                                        $cpfRequestRecord = (string) strtoupper(str_replace('<?xml version="1.0"?>', '', print_r($this->array_to_xml($cpfRequestRecord[0], new SimpleXMLElement('<xml/>'))->asXML(), true)));

                                        //Resolvendo problema de estrutura de variável para o Parceiro Credify
                                        $cpfRequestRecord = array($cpfRequestRecord);

                                        //Credify Teste sem as linhas acima
                                        //$resultWS = $this->soapClient->__soapCall($typeOfService, array("<xml><ACESSO><LOGON>6384</LOGON><SENHA>58122240</SENHA></ACESSO><CONSULTA><IDCONSULTA>216</IDCONSULTA><TIPOPESSOA>F</TIPOPESSOA><CPFCNPJ>01234567890</CPFCNPJ></CONSULTA></xml>")); 

                                } //end if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY)

                                //Salvando no LOG variável antes de enviada
                                $this->logEvents("Antes do metodo __sopCall:" . print_r($cpfRequestRecord, true), CPF_MSG_ERROR_LOG, 0);

                                //Chamando o serviço
                                $resultWS = $this->soapClient->__soapCall($typeOfService, $cpfRequestRecord);

                                $file = fopen("/www/log/retorno_cpf.txt", "a+");
                                fwrite($file, "DATA " . date("d-m-Y H:i:s") . "\n");
                                fwrite($file, "retorno omnidata soap = PASSO 1 " . json_encode($resultWS) . "\n");
                                fwrite($file, str_repeat("*", 50) . "\n");
                                fclose($file);

                                $this->logEvents("<hr>SUCESSO" . PHP_EOL . "<pre>" . htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages())) . "</pre>" . PHP_EOL . "<hr>", CPF_MSG_ERROR_LOG, 0);

                                if ($resultWS instanceof SoapFault) {
                                        $this->logEvents($this->getErrorMessages($resultWS), CPF_MSG_ERROR_LOG, 0);
                                } else {
                                        //Contabilizando a consulta
                                        if (!$this->counter()) {
                                                $this->logEvents("<hr>ERRO" . PHP_EOL . "<br>Problema ao inserir novo registro na tabela(cpf_partners) de apuração de requisição junto ao parceiro." . PHP_EOL . "<hr>", CPF_MSG_ERROR_LOG, 0);
                                        }

                                        //Capturando a resposta da consulta em vetor
                                        $cpfResponseRecord = $this->getResponseObject($typeOfService, $resultWS);

                                        return $cpfResponseRecord;
                                }

                        } catch (SoapFault $e) {
                                $this->logEvents("<hr>ERRO" . PHP_EOL . "<pre>" . htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages())) . "</pre>" . PHP_EOL . "<hr>", CPF_MSG_ERROR_LOG, 0);
                                $this->logEvents("Caught exception 2B (" . utf8_decode($e->faultcode) . "): " . utf8_decode($e->getMessage()) . PHP_EOL . "MAX_EXECUTION_TIME : " . ini_get('max_execution_time') . PHP_EOL . "DEFAULT_SOCKET_TIMEOUT : " . ini_get('default_socket_timeout') . PHP_EOL, CPF_MSG_ERROR_LOG, 0);
                                $this->set_error_system($e->getMessage());
                        }

                } else {
                        $this->logEvents("Erro Interno 2C: soapClient não definido" . PHP_EOL, CPF_MSG_ERROR_LOG, 0);
                }
        } //end function callService


        //Método para captura do XML do SOAP
        public function getTransactionMessages()
        {

                if ($this->soapClient) {
                        $requestMsg = htmlspecialchars_decode($this->soapClient->__getLastRequest());
                        $requestHeaderMsg = htmlspecialchars_decode($this->soapClient->__getLastRequestHeaders());
                        $responseMsg = htmlspecialchars_decode($this->soapClient->__getLastResponse());
                        $responseHeaderMsg = htmlspecialchars_decode($this->soapClient->__getLastResponseHeaders());

                        $msg = "";
                        $msg .= "--------------------------" . PHP_EOL;
                        $msg .= "Request :" . PHP_EOL . PHP_EOL . $requestMsg . PHP_EOL;
                        $msg .= "--------------------------" . PHP_EOL;
                        $msg .= "RequestHeaders:" . PHP_EOL . PHP_EOL . $requestHeaderMsg;
                        $msg .= "--------------------------" . PHP_EOL;
                        $msg .= "Response:" . PHP_EOL . PHP_EOL . $responseMsg . PHP_EOL . PHP_EOL;
                        $msg .= "--------------------------" . PHP_EOL;
                        $msg .= "ResponseHeaders:" . PHP_EOL . PHP_EOL . $responseHeaderMsg . PHP_EOL . PHP_EOL;
                } else {
                        $msg = "Erro Interno A: soapClient não definido";
                }
                return $msg;
        }//end function getTransactionMessages


        //Método para exibição da messagem de erro
        public function getErrorMessages($resultWS, $isSoapFault = true)
        {

                if ($isSoapFault) {
                        $msg .= "Message : " . $resultWS->getMessage() . PHP_EOL;
                        $msg .= "--------------------------" . PHP_EOL;
                        $msg .= "TraceString: " . $resultWS->getTraceAsString() . PHP_EOL;
                        $msg .= "--------------------------" . PHP_EOL;
                        $msg .= "Code: " . $resultWS->getCode() . PHP_EOL;
                        $msg .= "--------------------------" . PHP_EOL;
                        $msg .= "File: " . $resultWS->getFile() . PHP_EOL;
                        $msg .= "--------------------------" . PHP_EOL;
                        $msg .= "Line: " . $resultWS->getLine() . PHP_EOL;
                        $msg .= "--------------------------" . PHP_EOL;
                        $msg .= "FaultCode: " . $resultWS->faultcode . PHP_EOL;
                        $msg .= "--------------------------" . PHP_EOL;
                        $msg .= "Detail: " . $resultWS->detail . PHP_EOL . PHP_EOL . PHP_EOL;
                        $msg .= $this->getTransactionMessages();
                } else {
                        $msg .= $this->getTransactionMessages();
                }

                return $msg;
        } //end function getErrorMessages

        // General methods request
        private function getRequestObject($typeOfService = '', $requestParams = array())
        {

                if ($typeOfService == CPF_XML_REQUISICAO) {
                        if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY) {
                                $serialCheck = new verificaCPF();
                                $serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
                                return $serialCheckRequestObj;
                        } //end if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY)
                        elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA) {

                                $serialCheck = new verificaCPF_OMNIDATA();
                                $serialCheckResponseObj = $serialCheck->getRequestData($requestParams);
                                $file = fopen("/www/log/retorno_cpf.txt", "a+");
                                fwrite($file, "DATA " . date("d-m-Y H:i:s") . "\n");
                                fwrite($file, "params felipe: " . json_encode($requestParams) . "\n");
                                fwrite($file, "resposta omnidata = verificaCPF_OMNIDATA " . json_encode($serialCheckResponseObj) . "\n");
                                fwrite($file, str_repeat("*", 50) . "\n");
                                fclose($file);
                                return $serialCheckResponseObj;
                        } //end elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA)
                }//end if ($typeOfService == CPF_XML_REQUISICAO) 

        }//end 	function getRequestObject

        // General method Response
        private function getResponseObject($typeOfService = '', $soapResponseData)
        {

                if ($typeOfService == CPF_XML_REQUISICAO) {
                        if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY) {
                                $serialCheck = new verificaCPF();
                                $serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
                                return $serialCheckResponseObj;
                        } //end if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY)
                        elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA) {
                                $serialCheck = new verificaCPF_OMNIDATA();
                                $serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
                                $file = fopen("/www/log/retorno_cpf.txt", "a+");
                                fwrite($file, "DATA " . date("d-m-Y H:i:s") . "\n");
                                fwrite($file, "params felipe: " . json_encode($soapResponseData) . "\n");
                                fwrite($file, "resposta omnidata = soapResponseData " . json_encode($serialCheckResponseObj) . "\n");
                                fwrite($file, str_repeat("*", 50) . "\n");
                                fclose($file);
                                return $serialCheckResponseObj;
                        } //end elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA)
                } //end if ($typeOfService == CPF_XML_REQUISICAO)

        }//end function getResponseObject

        public function Req_EfetuaConsulta($requestParams, &$lista_resposta)
        {
                $sret = false;
                $lista_resposta = null;

                //Verifica a validade da idade (Menores que a idade mínima nem consulta o CPF)
                if ($this->verificaIdade($requestParams["data_nascimento"]) < $GLOBALS["IDADE_MINIMA"]) {
                        return 112;
                }

                // Verificando se o CPF está na BlackList
                elseif ($this->naoEstaBlackList($requestParams)) {

                        // Verificando se ultrapassou o limite máximo de utilização do CPF e se ultrapassou o limite máximo de contas com o mesmmo CPF ou consta na White List
                        if (($this->consultaQuantidadeUtilizada($requestParams) < $this->get_quantidade_limite() && $this->consultaQuantidadeContas($requestParams) <= $this->get_quantidade_contas()) || $this->estaWhiteList($requestParams)) {

                                if ($this->get_service_status()) {

                                        //inicio do bloco para a consulta de CPF
                                        $params = array(
                                                'cpfcnpj' => $requestParams['cpfcnpj'],
                                                'data_nascimento' => $requestParams['data_nascimento'],
                                        );

                                        //Para Consulta CACHE
                                        if (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) {

                                                //Implementando a chamada do método de consulta de cache
                                                $lista_resposta = $this->consultaCACHE($params);
                                                $this->logEvents("Resposta da consulta do CPF [" . $requestParams['cpfcnpj'] . "]:" . print_r($lista_resposta, true), CPF_MSG_ERROR_LOG, 0);

                                                return $lista_resposta['retorno'];

                                        } //end if (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) 
                                        // requisição para o hub do desenvolvedor
                                        else if (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_HUB) {

                                                $infoRecebida = $this->consultaHub($params);
                                                $file = fopen("/www/log/felipeConsultaHub.txt", "a+");
                                                fwrite($file, "DATA " . date("d-m-Y H:i:s") . "\n");
                                                fwrite($file, "resposta omnidata " . json_encode($infoRecebida) . "\n");
                                                fwrite($file, str_repeat("*", 50) . "\n");
                                                fclose($file);

                                                if ($infoRecebida == "" || $infoRecebida == false) {
                                                        return 1;
                                                }

                                                if ($infoRecebida["return"] == "OK" && $infoRecebida["status"] == true) {
                                                        $lista_resposta = $infoRecebida;
                                                        return 0;
                                                } else if ($infoRecebida["return"] == "NOK" && $infoRecebida["status"] == false) {
                                                        return 2;
                                                }

                                        }
                                        //Para Consulta através de Parceiros
                                        else {

                                                if (CPF_PARTNER_ENVIRONMET != CPF_PARTNER_OMNIDATA) {
                                                        $file = fopen("/www/log/retorno_cpf.txt", "a+");
                                                        fwrite($file, "DATA " . date("d-m-Y H:i:s") . "\n");
                                                        fwrite($file, "params felipe: " . json_encode($requestParams) . "\n");
                                                        fwrite($file, "resposta CPF_PARTNER_OMNIDATA: " . json_encode($lista_resposta) . "\n");
                                                        fwrite($file, str_repeat("*", 50) . "\n");
                                                        fclose($file);
                                                        //echo "CPF consultado<pre>".print_r($responseCPF,true)."</pre>\n";
                                                        //final do bloco para a consulta de CPF

                                                        $this->logEvents("Resposta da consulta do CPF [" . $requestParams['cpfcnpj'] . "]:" . print_r($responseCPF, true), CPF_MSG_ERROR_LOG, 0);

                                                        //Salvando informações para variável por referência
                                                        $lista_resposta = $responseCPF;
                                                } else {

                                                        require "/www/consulta_cpf/Onminidata.php";

                                                        $inicio = microtime(true);

                                                        $onminidata = new Onminidata();
                                                        $onminidata->query($requestParams['cpfcnpj'], $requestParams['data_nascimento']);
                                                        $result = $onminidata->collects_data();
                                                        $id_search = $onminidata->take_property($result, "id_search");
                                                        ///sleep(5);
                                                        $tempoRetorno = 0;
                                                        $lista_resposta = $onminidata->result_status_search($id_search);
                                                        $file = fopen("/www/log/logONMINIDATA.txt", "a+");
                                                        fwrite($file, "DATA " . date("d-m-Y H:i:s") . "\n");
                                                        fwrite($file, "resposta id_search: " . json_encode($id_search) . "\n");
                                                        fwrite($file, "tentativa numero: " . $tempoRetorno . "\n");
                                                        fwrite($file, "Duração da requisição: " . number_format((microtime(true) - $inicio), 4) . "\n");
                                                        fwrite($file, str_repeat("*", 50) . "\n");
                                                        fclose($file);

                                                        while ($lista_resposta["pesquisas"]["camposResposta"]["status"] != "DadoDisponivel") {

                                                                if ($tempoRetorno >= 7) {
                                                                        break;
                                                                }

                                                                $lista_resposta = $onminidata->result_status_search($id_search);
                                                                $tempoRetorno++;
                                                                sleep(10 * $tempoRetorno);
                                                                $file = fopen("/www/log/logONMINIDATA.txt", "a+");
                                                                fwrite($file, "DATA " . date("d-m-Y H:i:s") . "\n");
                                                                fwrite($file, "tentativa numero: " . $tempoRetorno . "\n");
                                                                fwrite($file, "resposta id_search: " . json_encode($id_search) . "\n");
                                                                fwrite($file, "Duração da requisição: " . number_format((microtime(true) - $inicio), 4) . "\n");
                                                                fwrite($file, str_repeat("*", 50) . "\n");
                                                                fclose($file);
                                                        }
                                                }

                                                //retornando o código da consulta
                                                if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY) {
                                                        return $responseCPF['resposta']['codigo'];
                                                }//end if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY)
                                                elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA) {

                                                        $file = fopen("/www/log/logONMINIDATA.txt", "a+");
                                                        fwrite($file, "DATA " . date("d-m-Y H:i:s") . "\n");
                                                        fwrite($file, "Fim das tentativas" . "\n");
                                                        fwrite($file, "resposta id_search: " . json_encode($id_search) . "\n");
                                                        fwrite($file, "Duração total das requisições: " . number_format((microtime(true) - $inicio), 4) . "\n");
                                                        fwrite($file, "resposta CPF_PARTNER_OMNIDATA: " . json_encode($lista_resposta) . "\n");
                                                        fwrite($file, str_repeat("*", 50) . "\n");
                                                        fclose($file);





                                                        if (isset($lista_resposta["pesquisas"]["camposResposta"]["status"]) && $lista_resposta["pesquisas"]["camposResposta"]["status"] == "DadoDisponivel") {
                                                                $retorno = 3;
                                                        } else {
                                                                $retorno = 1;
                                                        }

                                                        return $retorno; //$responseCPF['pesquisas']['status']
                                                }//end elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA)
                                                else {
                                                        return null;
                                                }

                                        }//end else do if (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) 

                                }//end if($this->get_service_status())

                        }//end if(($this->consultaQuantidadeUtilizada($requestParams) < $this->get_quantidade_limite() && $this->consultaQuantidadeContas($requestParams) < $this->get_quantidade_contas()) || $this->estaWhiteList($requestParams))

                        // Atingiu o limite máximo de utilização do CPF
                        else {
                                return 171;

                        }//end else do if($this->consultaQuantidadeUtilizada($requestParams) < $this->get_quantidade_limite() && $this->consultaQuantidadeContas($requestParams) < $this->get_quantidade_contas() || $this->estaWhiteList($requestParams))


                } //end if($this->naoEstaBlackList($requestParams))

                // CPF consta na BlackList
                else {

                        return 299; //código penal para falsidade ideologica

                }//end else do if($this->naoEstaBlackList($requestParams))

                //Sistema não respondendo
                return $sret;

        }//end function Req_EfetuaConsulta($requestParams,&$lista_resposta)

        private function logEvents($msg, $tipoLog = 'ERROR_LOG')
        {

                if ($tipoLog == CPF_MSG_ERROR_LOG)
                        $fileLog = LOG_FILE_CPF_WS_ERRORS;
                else if ($tipoLog == CPF_MSG_TRANSACTION_LOG)
                        $fileLog = LOG_FILE_CPF_WS_TRANSACTIONS;

                $log = "=================================================================================================\n";
                $log .= "DATA -> " . date("d/m/Y - H:i:s") . "\n";
                $log .= "SERVICE STATUS  -> " . $this->get_service_status() . "\n";
                $log .= "---------------------------------\n";
                $log .= htmlspecialchars_decode($msg);

                $fp = fopen($fileLog, 'a+');
                fwrite($fp, $log);
                fclose($fp);
        }//end function logEvents

        public function array_to_xml(array $arr, SimpleXMLElement $xml)
        {
                foreach ($arr as $k => $v) {
                        if (is_array($v))
                                $this->array_to_xml($v, $xml->addChild($k));
                        else
                                $xml->addChild($k, $v);
                }
                return $xml;
        }//end function array_to_xml

        public function object_to_array($obj)
        {
                if (is_object($obj))
                        $obj = (array) $obj;
                if (is_array($obj)) {
                        $new = array();
                        foreach ($obj as $key => $val) {
                                $new[$key] = $this->object_to_array($val);
                        }
                } else
                        $new = $obj;
                return $new;
        } //end function object_to_array

        public function counter()
        {
                $sql = "update cpf_partners set cp_count = cp_count+1 where cp_id = " . CPF_PARTNER_ENVIRONMET . " and cp_date = '" . date('Y-m') . "-01 00:00:00'::timestamp;";
                $ret2 = SQLexecuteQuery($sql);

                $cmdtuples = pg_affected_rows($ret2);
                //echo $cmdtuples . " tuples are affected.<br>\n".$sql;

                //Verificando se atualizou com sucesso
                if ($cmdtuples === 1) {
                        return true;
                } else {
                        //Inserindo novo registro caso não tenha atualizado com sucesso
                        $sql = "insert into cpf_partners values (" . CPF_PARTNER_ENVIRONMET . ",'" . CPF_PARTNER_NAME . "',1,'" . date('Y-m') . "-01 00:00:00'::timestamp);";
                        $ret = SQLexecuteQuery($sql);

                        $cmdtuples_insert = pg_affected_rows($ret);
                        //echo $cmdtuples_insert . " tuples are affected.<br>\n".$sql;

                        // Verificando se inseriu com sucesso
                        if ($cmdtuples_insert === 1) {
                                return true;
                        } else {
                                return false;
                        }
                }//end else do if($cmdtuples===1)
        } //end function counter()

        // consulta no parceiro Hub do desenvovedor
        public function consultaHub($info)
        {

                $curl = curl_init();
                curl_setopt_array($curl, [
                        CURLOPT_URL => "https://ws.hubdodesenvolvedor.com.br/v2/cpf/?cpf={$info["cpfcnpj"]}&data={$info["data_nascimento"]}&token=104048520UeLqsXgHvd187856448&ignore_db", //"https://ws.hubdodesenvolvedor.com.br/v2/cpf/?cpf={$info["cpfcnpj"]}&data={$info["data_nascimento"]}&token=104048520UeLqsXgHvd187856448"
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_RETURNTRANSFER => true
                ]);
                $retorno = json_decode(curl_exec($curl), true);
                $curlInfo = curl_getinfo($curl);
                curl_close($curl);

                $file_log = fopen("/www/log/retorono_hub_desenvolvedor.txt", "a+");
                fwrite($file_log, "data: " . date("d-m-Y H:i:s") . "\n");
                fwrite($file_log, "info: " . json_encode($info) . "\n");
                fwrite($file_log, "resultado: " . json_encode($retorno) . "\n");
                fwrite($file_log, str_repeat("*", 50) . "\n");
                fclose($file_log);

                if ($curlInfo["http_code"] == 200) {
                        return $retorno;
                }

                return false;
        }

        public function consultaCACHE($requestParams)
        {

                //Contabilizando a consulta
                if (!$this->counter()) {
                        $this->logEvents("<hr>ERRO" . PHP_EOL . "<br>Problema ao inserir novo registro na tabela(cpf_partners) de apuração de requisição junto ao parceiro." . PHP_EOL . "<hr>", CPF_MSG_ERROR_LOG, 0);
                }

                // Buscando informações no Cache
                $sql = "select cpf, to_char(data_nascimento,'DD/MM/YYYY') as data_nascimento, nome from cpf_cache where cpf = " . ($requestParams['cpfcnpj'] * 1) . " and checado = 1;";
                $busca = SQLexecuteQuery($sql);

                $cmdtuples = pg_num_rows($busca);

                //Verificando se retornou consulta com sucesso
                if ($cmdtuples === 1) {

                        $busca_row = pg_fetch_array($busca);
                        $cpfResponseRecord['pesquisas']['camposResposta']['nome'] = $busca_row['nome'];
                        $cpfResponseRecord['pesquisas']['camposResposta']['data_nascimento'] = $busca_row['data_nascimento'];
                        $cpfResponseRecord['retorno'] = 1;

                } else {

                        //Retornando que não encontrou CPF no CACHE
                        $cpfResponseRecord['retorno'] = 2;

                }//end else do if($cmdtuples===1)

                return $cpfResponseRecord;

        } //end function consultaCACHE()

        public function consultaQuantidadeUtilizada($requestParams)
        {

                // Buscando informações no Cache
                $sql = "select qtde_utilizado from cpf_cache where cpf = " . ($requestParams['cpfcnpj'] * 1) . ";";
                $busca = SQLexecuteQuery($sql);

                $cmdtuples = pg_num_rows($busca);

                //Verificando se retornou consulta com sucesso
                if ($cmdtuples === 1) {

                        $busca_row = pg_fetch_array($busca);
                        return $busca_row['qtde_utilizado'];

                } else {

                        //Retornando que não encontrou CPF no CACHE
                        return 0;

                }//end else do if($cmdtuples===1)

        } //end function consultaQuantidadeUtilizada()

        public function consultaQuantidadeContas($requestParams)
        {

                // Buscando informações no Cache
                $sql = "select qtde_contas from cpf_cache where cpf = " . ($requestParams['cpfcnpj'] * 1) . ";";
                $busca = SQLexecuteQuery($sql);

                $cmdtuples = pg_num_rows($busca);

                //Verificando se retornou consulta com sucesso
                if ($cmdtuples === 1) {

                        $cpfFinal = substr($requestParams['cpfcnpj'], 0, 3) . "." . substr($requestParams['cpfcnpj'], 3, 3) . "." . substr($requestParams['cpfcnpj'], 6, 3) . "-" . substr($requestParams['cpfcnpj'], 9, 2);
                        $sql = "select * from usuarios_games where ug_cpf like '%" . $cpfFinal . "%' and ug_ativo = 1;";
                        $buscaUsuario = SQLexecuteQuery($sql);

                        $linhas = pg_num_rows($buscaUsuario);

                        if ($linhas <= 2) {
                                return $linhas;
                        } else {
                                $busca_row = pg_fetch_array($busca);
                                return $busca_row['qtde_contas'];
                        }

                } else {

                        //Retornando que não encontrou CPF no CACHE
                        return 0;

                }//end else do if($cmdtuples===1)

        } //end function consultaQuantidadeUtilizada()

        public function adicionaQtdeContas($cpf, $name, $data_nascimento)
        {
                $sql = "update cpf_cache set qtde_contas = qtde_contas+1 where cpf = " . $cpf . ";";
                $ret2 = SQLexecuteQuery($sql);

                $cmdtuples = pg_affected_rows($ret2);
                //echo $cmdtuples . " tuples are affected.<br>\n".$sql;

                //Verificando se atualizou com sucesso
                if ($cmdtuples === 1) {
                        return true;
                } else {
                        //Inserindo novo registro caso não tenha atualizado com sucesso
                        $sql = "insert into cpf_cache values (" . $cpf . ",to_date('" . $data_nascimento . "','DD/MM/YYYY'),'" . $name . "',1,0,1);";
                        $ret = SQLexecuteQuery($sql);

                        $cmdtuples_insert = pg_affected_rows($ret);
                        //echo $cmdtuples_insert . " tuples are affected.<br>\n".$sql;

                        // Verificando se inseriu com sucesso
                        if ($cmdtuples_insert === 1) {
                                return true;
                        } else {
                                return false;
                        }
                }//end else do if($cmdtuples===1)
        } //end function adicionaQtdeContas


        private function naoEstaBlackList($requestParams)
        {

                // Buscando informações na Black List
                $sql = "select cpf from cpf_black_list where cpf = " . ($requestParams['cpfcnpj'] * 1) . ";";
                $busca = SQLexecuteQuery($sql);

                //Verificando se retornou consulta com sucesso
                if ($busca && pg_num_rows($busca) === 1) {
                        //Retornando que Encontrou CPF na BlackList
                        return FALSE;

                } else {
                        //Retornando que NÃO encontrou CPF na BlackList
                        return TRUE;

                }//end else do if(pg_num_rows($busca)===1)

        } //end function naoEstaBlackList()

        private function estaWhiteList($requestParams)
        {

                // Buscando informações na White List
                $sql = "select cpf from cpf_white_list where cpf = " . ($requestParams['cpfcnpj'] * 1) . ";";
                $busca = SQLexecuteQuery($sql);

                //Verificando se retornou consulta com sucesso
                if ($busca && pg_num_rows($busca) === 1) {
                        //Retornando que Encontrou CPF na WhiteList
                        return TRUE;

                } else {
                        //Retornando que NÃO encontrou CPF na WhiteList
                        return FALSE;

                }//end else do if(pg_num_rows($busca)===1)

        } //end function estaWhiteList()

        public function verificaIdade($data_nascimento)
        {
                $data_nascimento = explode("/", $data_nascimento);
                $agora = explode("/", date("d/m/Y"));

                if ($data_nascimento[0] > $agora[0]) {
                        $agora[1] -= 1;
                }
                if ($data_nascimento[1] > $agora[1]) {
                        $agora[2] -= 1;
                }
                return $agora[2] - $data_nascimento[2];
        }

        public function __destruct()
        {
                global $raiz_do_projeto;
                require_once $raiz_do_projeto . "/class/util/Benchmark.class.php";

                $sistema = $_SERVER['REQUEST_URI'];
                $funcao = "CPF";
                $tempo = number_format(getmicrotime() - $this->timeBenchmark, 2, '.', '.');
                $erro_sistema = $this->get_error_system();

                if ($tempo != "0") {
                        $benchmark = new Benchmark($funcao, $sistema, $tempo, $erro_sistema);
                        $benchmark->save();
                }

        }

} //end class classCPF
?>