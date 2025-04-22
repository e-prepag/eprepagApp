<?php

//Alterando o limeout do PHP para (PIX_TIMEOUT/1000) segundos
//ini_set('default_socket_timeout', ((PIX_TIMEOUT / 1000) + 5));

class classPIX
{

    private $access_token;
    private $url;
    private $chave_pix;

    public function __construct()
    {

        if (!function_exists('getEnvVariable')) {
            require_once "/www/includes/getEnvVar.php";
        }

        //$token = getEnvVariable('mp_access_token');

        //$token = '$aact_MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmM0MzYzOGZhLWE0ZTktNDQ1Yy04OGJlLTQyMmNjNjU3YzRmZjo6JGFhY2hfMDgxN2FjMWYtMjQ3OC00OTcyLTk5ZDYtZTJlYmQwNWUwZDdh';
        $token = '$aact_MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjE4MzAxZjU3LWUyMTktNDBmYi1iY2YxLWNmY2QyOGEwN2E0ZTo6JGFhY2hfNWQ5YTU3Y2EtZjJhMC00ZjZhLTgyMDAtYTMzYTBhMjk1Yjc2';

        //$this->chave_pix = "d91a07ca-3b55-4de7-9fd6-e210f366e831";
        $this->chave_pix = "efb5b2e8-bc83-43ce-a7b4-3530d766bbd0";

        if ($token == "") {
            echo ("<br><br>ERRO ao obter acesso ao Banco!<br>Por favor, entre em
                 contado com o suporte da E-Prepag e informe o erro de código PIX790954 - Asaas.<br>Obrigado.");
        } else {
            $this->setAccessToken($token);
            $this->url = "https://api.asaas.com/v3/";
            //$this->url = "https://api-sandbox.asaas.com/v3/";
        }

    }//end function __construct()

    private function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }//end function setAccessToken

    public function getAccessToken()
    {
        return $this->access_token;
    }//end function getAccessToken

    public function dadosPagador($idUsuario, $idpedido, $resposta_json)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url . "customers/$idUsuario",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'access_token: ' . $this->getAccessToken(),
            ),
        ));

        $response = curl_exec($curl);

        $data = json_decode($response, true);

        $cpf = $data['cpfCnpj'] ? preg_replace('/\D/', '', $data['cpfCnpj']) : 'N ret CpfCnpj';
        $name = $data['name'] ? $data['name'] : 'Nao retornou nome';

        $sql = "SELECT * FROM tb_pag_pix WHERE numcompra = '" . substr($idpedido, 2, 17) . "'; ";
        $rs_teste_existencia = SQLexecuteQuery($sql);
        if (pg_num_rows($rs_teste_existencia) == 0) {
            $sql = "INSERT INTO tb_pag_pix( 
                                                numcompra, 
                                                cpf_cnpj_pagador, 
                                                nome_pagador, 
                                                json_resposta)
                                    VALUES (
                                            '" . substr($idpedido, 2, 17) . "', 
                                            '" . $cpf . "',
                                            '" . $name . "',
                                            '" . $resposta_json . "');";
            SQLexecuteQuery($sql);
        }
    }

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

        $resposta = $this->sendJSON($nomeCliente, $cpfCnpj, $valor, $id_pedido, $email);

        $logFilePath = "/www/log/Asaas_PIX.txt";
        $ff = fopen($logFilePath, "a+");

        if ($ff) {
            $timestamp = date("Y-m-d H:i:s");
            $logEntry = "resultado data: " . $timestamp . ", venda_id: " . $id_pedido . ", cpfCnpj: " . $cpfCnpj . ", email: " . $email . ", nomeCliente: " . $nomeCliente .
                " ---" . json_encode($resposta) . "----" . serialize($resposta) . "\r\n";
            fwrite($ff, $logEntry);
            fclose($ff);

        }

        if ($resposta == false) {
            $htmlErro = "
						<div class='col-md-12' style='border: 1px solid black; padding: 5px; margin-top:3px; text-align: center; clear: both;'>
                            <b>ERRO na Comunicação com o Banco!<br>Por favor, entre em contato com o suporte da E-Prepag e informe o erro de código PIX985235 ou tente novamente mais tarde.<br>Obrigado.</b>
                        </div>";
            return $htmlErro;

        } else {

            $GLOBALS["_SESSION"]["QRCODE"] = $resposta['payload']; //text-left  

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

    private function sendJSON($nome, $cpf, $valor, $vendaId, $email = "")
    {

        // URL da API Asaas
        $url = $this->url . "pix/qrCodes/static";

        // Monta os dados em um array
        $descricaoLimitada = mb_strimwidth("Compra de $nome", 0, 34, "...");
        $postData = [
            "description" => $descricaoLimitada,
            "addressKey" => $this->chave_pix,
            "value" => $valor,
            "expirationSeconds" => 3600,
            "allowsMultiplePayments" => false,
            "externalReference" => $vendaId,
        ];

        // Inicializa o cURL
        $ch = curl_init();

        // Configurações do cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData)); // Envia os dados em JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'content-type: application/json',
            'access_token: ' . $this->getAccessToken(),
            "User-Agent: Eprepag/1.0"
        ]);

        // Executa a requisição
        $response = curl_exec($ch);

        // Verifica se ocorreu algum erro
        if (curl_errno($ch)) {
            echo 'Erro no cURL: ' . curl_error($ch);
            exit;
        }

        // Fecha a conexão cURL
        curl_close($ch);

        // Converte a resposta JSON para um array associativo
        $data = json_decode($response, true);

        // Verifica se a resposta contém os dados esperados
        if (!isset($data['id'])) {
            // Extrai os dados de interesse
            return false;
        }

        return $data;

    }//end function sendjson

} //end class classPIX


?>