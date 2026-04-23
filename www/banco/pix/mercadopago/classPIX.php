<?php

//Alterando o limeout do PHP para (PIX_TIMEOUT/1000) segundos
// ini_set('default_socket_timeout', ((PIX_TIMEOUT / 1000) + 5));
require_once "/www/includes/load_dotenv.php";
require_once "/www/includes/writeIfPossible.php";
class classPIX
{

    private $access_token;
    private $url;
    private $itens = array();

    public function __construct()
    {
        $token = getenv('mp_access_token');

        if ($token == "") {
            echo ("<br><br>ERRO ao obter acesso ao Banco!<br>Por favor, entre em
                 contado com o suporte da E-Prepag e informe o erro de código PIX790954 - MercadoPago.<br>Obrigado.");
        } else {
            $this->setAccessToken($token);
            $this->url = getenv('mp_url_api');
        }
    } //end function __construct()

    private function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    } //end function setAccessToken

    public function getAccessToken()
    {
        return $this->access_token;
    } //end function getAccessToken

    public function callService($params)
    {
        /*
        $params = array (
                                                            'metodo'    => PIX_REGISTER,
                                                            'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCPF)),
                                                            'nome'      => $usuarioGames->ug_nome_cpf,
                                                            'valor'     => number_format(($total_carrinho+$taxa),2,'.',''),
                                                            'descricao' => "E-Prepag",
                                                            'idpedido'  => $ARRAY_CONCATENA_ID_VENDA['gamer'].$_SESSION['pagamento.numorder']
                                                        );
        */
        $nomeCliente = $params['nome'];
        $cpfCnpj = $params['cpf_cnpj'];
        $valor = floatval($params['valor']);
        $email = $params['email'];
        $id_pedido = $params['idpedido'];
        $itens = $params['itens'];
        $this->itens = is_array($itens) ? $itens : array();

        $resposta = $this->sendJSON($nomeCliente, $cpfCnpj, $valor, $id_pedido, $email);

        $logFilePath = "/www/arquivos_gerados/logs/mercadopago_PIX.txt";
        $timestamp = date("Y-m-d H:i:s");
        $logEntry = "resultado data: " . $timestamp . ", venda_id: " . $id_pedido . ", cpfCnpj: " . $cpfCnpj . ", email: " . $email . ", nomeCliente: " . $nomeCliente .
            " ---" . json_encode($resposta) . "----" . serialize($resposta) . "\r\n";
        writeFileIfPossible($logFilePath, $logEntry);

        if ($resposta == false) {
            $htmlErro = "
						<div class='col-md-12' style='border: 1px solid black; padding: 5px; margin-top:3px; text-align: center; clear: both;'>
                            <b>ERRO na Comunicação com o Banco!<br>Por favor, entre em contato com o suporte da E-Prepag e informe o erro de código PIX985235 ou tente novamente mais tarde.<br>Obrigado.</b>
                        </div>";
            return $htmlErro;
        } else {

            $GLOBALS["_SESSION"]["QRCODE"] = $resposta['point_of_interaction']['transaction_data']['qr_code']; //text-left  

            if (empty($GLOBALS["_SESSION"]["QRCODE"])) {
                $html = "
						<div class='col-md-12' style='border: 1px solid black; padding: 5px; margin-top:3px; text-align: center; clear: both;'>
							<b>Tivemos um problema ao gerar sua chave PIX. Por favor, tente novamente mais tarde ou entre em contato com o suporte.</b>
						</div>";
            } else {
                $html = "
						<div class='col-md-7 text-center d-min-md-none hide-pix-success' style='color: black;'>
							<button id='btn-copy' title='Copiar código' data-clipboard-text='" . $GLOBALS["_SESSION"]["QRCODE"] . "' class='top20 btn btn-success'>Copiar código</button>
						</div>
						<div class='col-md-7 d-max-sm-none hide-pix-success col-pix'>
							<img id='img-pix' style='float: left;' src='/includes/qrcode/php/qrcode.php'/>
							<span style='margin-top: 6%;display: block;'><b>Pix copia e cola:</b></span>
							<span style='word-break: break-all;display: block; font-size:.8em;'>" . $GLOBALS["_SESSION"]["QRCODE"] . "</span> 
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
            }

            return $html;
        }
    } //end function callService

    public function callSonda($params, &$reposta_consulta)
    {
        // URL e token da API
        $url = $this->url . '/v1/payments/search?external_reference=' . $params['idpedido'];

        $accessToken = $this->getAccessToken();

        // Inicializa o cURL
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer $accessToken",
            ],
        ]);

        // Executa a requisição
        $response = curl_exec($curl);

        // Verifica se houve erro na requisição
        if (curl_errno($curl)) {
            echo 'Erro ao realizar a requisição: ' . curl_error($curl);
            curl_close($curl);
        }

        curl_close($curl);

        // Decodifica a resposta JSON para um array PHP
        $data = json_decode($response, true);
        $erro = "";
        $status = false;
        // Verifica se há resultados na resposta
        if (isset($data['results'][0]['status'])) {
            $status = $data['results'][0]['status'];
        } else if (isset($data['error'])) {
            $erro = $data['error'];
        } else {
            $erro = $response;
        }
        $timestamp = date("Y-m-d H:i:s");
        writeFileIfPossible("/www/arquivos_gerados/logs/mercadopago_verifica_PIX.txt", "resultado data:" . $timestamp . ": " . $erro . $status . "\r\n");

        if (!$status) {
            echo ("<br><br>ERRO na Comunicação com o Banco!<br>Por favor, entre em contado com o suporte da E-Prepag e informe o erro de código PIX985235.<br>Obrigado.");
        } else {
            if ($status == PIX_SONDA_PAGO_OK) {
                $cpf = $data['results'][0]['payer']['identification']['number'] ? $data['results'][0]['payer']['identification']['number'] : 'N/A';
                $name = $data['results'][0]['payer']['first_name'] ? $data['results'][0]['payer']['first_name'] : 'N/A';
                $reposta_consulta = $data['results'][0]['date_created'] ? $data['results'][0]['date_created'] : date('Y-m-d\TH:i:s.vO');
                $timestamp = date("Y-m-d H:i:s");
                writeFileIfPossible("/www/arquivos_gerados/logs/mercadopago_verifica_resposta_PIX.txt", "resultado data:" . $timestamp . "data: " . $reposta_consulta . ", nome: $name, cpf: $cpf\r\n");

                $numCompra = substr($params['idpedido'], 2, 17);
                $sql = "SELECT * FROM tb_pag_pix WHERE numcompra = $1;"; // AND cpf_cnpj_pagador = '".(isset($resposta->pix[0]->pagador->cpf)?$resposta->pix[0]->pagador->cpf:$resposta->pix[0]->pagador->cnpj)."'
                $rs_teste_existencia = SQLexecuteQueryParams($sql, array($numCompra));
                if (pg_num_rows($rs_teste_existencia) == 0) {
                    $sql = "INSERT INTO tb_pag_pix( 
                                                numcompra, 
                                                cpf_cnpj_pagador, 
                                                nome_pagador, 
                                                json_resposta)
                                    VALUES (
                                            $1, 
                                            $2,
                                            $3,
                                            $4);";
                    $rs = SQLexecuteQueryParams($sql, array($numCompra, $cpf, $name, json_encode($data)));
                    if ($rs)
                        $this->logEvents("Sucesso no INSERT: " . PHP_EOL . $sql . PHP_EOL);
                    else
                        $this->logEvents("ERRO no INSERT: " . PHP_EOL . $sql . PHP_EOL);
                } //end if(pg_num_rows($rs_teste_existencia) == 0)
                else
                    $this->logEvents("Já existe registro de dados do pagador para o pagamento " . substr($params['idpedido'], 2, 17) . PHP_EOL);
            } //end if($resposta->status == PIX_SONDA_PAGO_OK)
            return $status;
        } //end else if($resposta->codigo == PIX_ERRO)

    } //end function callSonda

    public function callSondaById($id, &$numPedido)
    {
        // URL e token da API
        $url = $this->url . '/v1/payments/' . $id;

        $accessToken = $this->getAccessToken();

        // Inicializa o cURL
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer $accessToken",
            ],
        ]);

        // Executa a requisição
        $response = curl_exec($curl);

        // Verifica se houve erro na requisição
        if (curl_errno($curl)) {
            echo 'Erro ao realizar a requisição: ' . curl_error($curl);
            curl_close($curl);
            exit;
        }

        curl_close($curl);

        // Decodifica a resposta JSON para um array PHP
        $data = json_decode($response, true);
        $erro = "";
        $status = false;
        // Verifica se há resultados na resposta
        if (isset($data['status'])) {
            $status = $data['status'];
        } else if (isset($data['error'])) {
            $erro = $data['error'];
        } else {
            $erro = "Status não identificado";
        }
        $timestamp = date("Y-m-d H:i:s");
        writeFileIfPossible("/www/arquivos_gerados/logs/mercadopago_verifica_PIX.txt", "resultado data:" . $timestamp . ": " . $erro . $status . "\r\n");

        if (!$status) {
            echo ("<br><br>ERRO na Comunicação com o Banco!<br>Por favor, entre em contado com o suporte da E-Prepag e informe o erro de código PIX985235.<br>Obrigado.");
        } else {
            if ($status == PIX_SONDA_PAGO_OK) {
                $numPedido = $data['external_reference'];
                $cpf = $data['payer']['identification']['number'] ? $data['payer']['identification']['number'] : 'N/A';
                $name = $data['payer']['first_name'] ? $data['payer']['first_name'] : 'N/A';
                $reposta_consulta = $data['date_created'] ? $data['date_created'] : date('Y-m-d\TH:i:s.vO');
                $timestamp = date("Y-m-d H:i:s");
                writeFileIfPossible("/www/arquivos_gerados/logs/mercadopago_verifica_resposta_PIX.txt", "resultado data:" . $timestamp . "data: " . $reposta_consulta . ", nome: $name, cpf: $cpf\r\n");

                $numCompra = substr($numPedido, 2, 17);
                $sql = "SELECT * FROM tb_pag_pix WHERE numcompra = $1;"; // AND cpf_cnpj_pagador = '".(isset($resposta->pix[0]->pagador->cpf)?$resposta->pix[0]->pagador->cpf:$resposta->pix[0]->pagador->cnpj)."'
                $rs_teste_existencia = SQLexecuteQueryParams($sql, array($numCompra));
                if (pg_num_rows($rs_teste_existencia) == 0) {
                    $sql = "INSERT INTO tb_pag_pix( 
                                                numcompra, 
                                                cpf_cnpj_pagador, 
                                                nome_pagador, 
                                                json_resposta)
                                    VALUES (
                                            $1, 
                                            $2,
                                            $3,
                                            $4);";
                    $rs = SQLexecuteQueryParams($sql, array($numCompra, $cpf, $name, json_encode($data)));
                    if ($rs)
                        $this->logEvents("Sucesso no INSERT: " . PHP_EOL . $sql . PHP_EOL);
                    else
                        $this->logEvents("ERRO no INSERT: " . PHP_EOL . $sql . PHP_EOL);
                } //end if(pg_num_rows($rs_teste_existencia) == 0)
                else
                    $this->logEvents("Já existe registro de dados do pagador para o pagamento " . substr($numPedido, 2, 17) . PHP_EOL);
            } //end if($resposta->status == PIX_SONDA_PAGO_OK)
            return $status;
        } //end else if($resposta->codigo == PIX_ERRO)

    }

    private function logEvents($msg)
    {

        $fileLog = PIX_ERROR_LOG_FILE !== null ? PIX_ERROR_LOG_FILE : "/www/arquivos_gerados/logs/log_PIX_WS-Errors.log";

        $log = "=================================================================================================" . PHP_EOL;
        $log .= "DATA -> " . date("d/m/Y - H:i:s") . PHP_EOL;
        $log .= "---------------------------------" . PHP_EOL;
        $log .= htmlspecialchars_decode($msg);
        writeFileIfPossible($fileLog, $log);
    } //end function logEvents

    private function generateRandomString($length = 32)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function identifyDocumentType($document)
    {
        $document = preg_replace('/\D/', '', $document); // Remove caracteres não numéricos

        if (strlen($document) === 11) {
            return 'CPF';
        } elseif (strlen($document) === 14) {
            return 'CNPJ';
        } else {
            return 'Número inválido';
        }
    }

    private function separarNomeSobrenome($nomeCompleto)
    {
        $nomeCompleto = trim($nomeCompleto);
        $nomeCompleto = preg_replace('/\s+/', ' ', $nomeCompleto); // Normaliza espaços múltiplos
        $partes = explode(' ', $nomeCompleto);

        if (count($partes) === 0 || $nomeCompleto === '') {
            return ['nome' => '', 'sobrenome' => ''];
        }

        if (count($partes) === 1) {
            return ['nome' => $partes[0], 'sobrenome' => ''];
        }

        $nome = array_shift($partes);
        $sobrenome = implode(' ', $partes);

        return ['nome' => $nome, 'sobrenome' => $sobrenome];
    }

    private function utf8ize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->utf8ize($value);
            }
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->utf8ize($value);
            }
        } elseif (is_string($data)) {
            // Converte apenas se não for UTF-8
            if (!mb_check_encoding($data, 'UTF-8')) {
                return mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
            }
        }
        return $data;
    }

    private function sendJSON($nome, $cpf, $valor, $vendaId, $email = "")
    {
        $itens = is_array($this->itens) ? $this->itens : array();

        $type = $this->identifyDocumentType($cpf);

        $accessToken = $this->getAccessToken();

        $dateOfExpiration = (new DateTime('+1 hour'))->format('Y-m-d\TH:i:s.000P');

        if (!$email) {
            $email = "teste@email.com";
        }

        $nomeSeparado = $this->separarNomeSobrenome($nome);

        // Verifica se o cliente existe
        $url = $this->url . "/v1/payments";

        $post_data = [
            "transaction_amount" => $valor,
            "date_of_expiration" => $dateOfExpiration,
            "payment_method_id" => "pix",
            "external_reference" => $vendaId,
            "notification_url" => "https://api.eprepag.com.br/webhook/mercadoPago.php",
            "description" => "PIX pagto id: $vendaId",

            "payer" => [
                "first_name" => $nome,
                //"last_name" => $payerLastName,
                "email" => $email,
                "identification" => [
                    "type" => $type,
                    "number" => $cpf
                ]
            ],

            "additional_info" => [
                "items" => $itens,
                "payer" => [
                    "first_name" => $nomeSeparado['nome'],
                    "last_name" => $nomeSeparado['sobrenome'],
                ]
            ]
        ];

        $post_utf8 = $this->utf8ize($post_data);

        $idempotencyKey = $this->generateRandomString();

        // Inicializa o cURL
        $ch = curl_init();

        // Configurações do cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json",
            "X-Idempotency-Key: $idempotencyKey"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_utf8));

        // Executa o cURL
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $errorFileLog = "/www/arquivos_gerados/logs/mercadopago_log_PIX_WS-Hearders.log";
        $log = "=================================================================================================" . PHP_EOL;
        $log .= "DATA -> " . date("d/m/Y - H:i:s") . " -> Send JSON to Get QRCode" . PHP_EOL;
        $log .= "RESPONSE -> " . $response . PHP_EOL;
        $log .= "---------------------------------------------------" . PHP_EOL;
        writeFileIfPossible($errorFileLog, $log);

        $customerId = null;

        if (empty($data['id'])) {
            return false;
        }

        return $data;
    } //end function sendjson

} //end class classPIX

//função SONDA para checagem da situação do PIX
function getSondaPIX($numero, &$a_resp)
{

    $ARRAY_CONCATENA_ID_VENDA = array(
        'gamer' => '10',
        'pdv' => '20',
        'cards' => '30',
        'boleto_express' => '40'
    );

    $sql = "SELECT * from tb_pag_compras where numcompra = '" . $numero . "'";

    //echo $sql;
    $rs_sonda = SQLexecuteQuery($sql);
    if (!$rs_sonda) {
        echo "<font color='#FF0000'><b>Erro na Sonda da Compra (" . $numero . ")." . PHP_EOL . "</b></font><br>";
        return false;
    } //end if(!$rs_sonda) 
    else {
        $rs_sonda_row = pg_fetch_array($rs_sonda);

        $tipo = $rs_sonda_row['tipo_cliente'];
        $valor = $rs_sonda_row['total'];

        $numeroPedido = null;

        if ($tipo == "LR") {
            $numeroPedido = $ARRAY_CONCATENA_ID_VENDA['pdv'] . $numero;
        } elseif ($tipo == "M") {
            $numeroPedido = $ARRAY_CONCATENA_ID_VENDA['gamer'] . $numero;
        } else {
            echo "<font color='#FF0000'><b>Não consta Tipo de Pedido na Tabela de Pagamento (" . $numero . ")." . PHP_EOL . "</b></font><br>";
            return false;
        }

        $consulta = new classPIX();
        $params = array(
            'metodo' => "POST",
            'idpedido' => $numeroPedido
        );
        $auxChecagem = $consulta->callSonda($params, $a_resp);
        //var_dump($auxChecagem);

        if ($auxChecagem == PIX_SONDA_PAGO_OK) {
            return true;
        } else {
            return false;
        }
    } //end else do if(!$rs_sonda) 
} //end function getSondaPIX

function getSondaPIXbyId($id)
{
    $numPedido = null;
    $consulta = new classPIX();
    $auxChecagem = $consulta->callSondaById($id, $numPedido);

    if ($auxChecagem != PIX_SONDA_PAGO_OK || $numPedido == null) {
        return false;
    }

    return $numPedido;
} //end function getSondaPIX
