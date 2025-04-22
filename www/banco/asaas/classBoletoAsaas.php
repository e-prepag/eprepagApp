<?php

class classBoleto
{

    private $access_token;
    private $url;

    public function __construct()
    {

        if (!function_exists('getEnvVariable')) {
            require_once "/www/includes/getEnvVar.php";
        }

        //$token = getEnvVariable('mp_access_token');

        //$token = '$aact_MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmM0MzYzOGZhLWE0ZTktNDQ1Yy04OGJlLTQyMmNjNjU3YzRmZjo6JGFhY2hfMDgxN2FjMWYtMjQ3OC00OTcyLTk5ZDYtZTJlYmQwNWUwZDdh'; //chave jose
        //$token = '$aact_MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjQzZTJmODZjLTE5ZTctNDFkNy1hYmJjLTk5N2ExYWJjZDRhMTo6JGFhY2hfNmJlNmNlYjMtYmIxYy00NzFjLThlNjgtNjE4MTAxNmY1ZWRl'; //chave sandbox glaucia
        $token = '$aact_MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjE4MzAxZjU3LWUyMTktNDBmYi1iY2YxLWNmY2QyOGEwN2E0ZTo6JGFhY2hfNWQ5YTU3Y2EtZjJhMC00ZjZhLTgyMDAtYTMzYTBhMjk1Yjc2';

        if ($token == "") {
            echo ("<br><br>ERRO ao obter acesso ao Banco! Asaas.<br>Obrigado.");
        } else {
            $this->setAccessToken($token);
            //$this->url = "https://sandbox.asaas.com/api/v3/";
            $this->url = "https://api.asaas.com/v3/";
        }

    }//end function __construct()

    private function removerAcentos($string) {
        $acentos = array(
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N',
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n'
        );
        
        return strtr($string, $acentos);
    }

    private function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }//end function setAccessToken

    public function getAccessToken()
    {
        return $this->access_token;
    }//end function getAccessToken

    public function callService($params)
    {

        $nomeCliente = $this->removerAcentos($params['nome']);
        $cpfCnpj = $params['cpf_cnpj'];
        $valor = floatval($params['valor']);
        $email = $params['email'];
        $id_pedido = $params['idpedido'];

        $resposta = $this->sendJSON($nomeCliente, $cpfCnpj, $valor, $id_pedido, $email);

        $logFilePath = "/www/log/Asaas_boleto.txt";
        $ff = fopen($logFilePath, "a+");

        if ($ff) {
            $timestamp = date("Y-m-d H:i:s");
            $logEntry = "resultado data: " . $timestamp . ", venda_id: " . $id_pedido . ", cpfCnpj: " . $cpfCnpj . ", email: " . $email . ", nomeCliente: " . $nomeCliente .
                " ---" . json_encode($resposta) . "----" . serialize($resposta) . "\r\n";
            fwrite($ff, $logEntry);
            fclose($ff);

        }
        return $resposta;

    } //end function callService

    private function sendJSON($nome, $cpf, $valor, $vendaId, $email = "")
    {

        // Verifica se o cliente existe
        $url = $this->url . "customers?cpfCnpj=$cpf";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "access_token: " . $this->getAccessToken(),
            "User-Agent: Eprepag/1.0"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $customerId = null;

        if (!empty($data['data']) && isset($data['data'][0]['id'])) {
            // Cliente encontrado
            $customerId = $data['data'][0]['id'];

        } else {
            // Cliente não encontrado, criar novo cliente
            $url = $url = $this->url . "customers";
            $payload = json_encode([
                "name" => $nome,
                "cpfCnpj" => $cpf,
                "email" => $email
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "accept: application/json",
                "access_token: " . $this->getAccessToken(),
                "content-type: application/json",
                "User-Agent: Eprepag/1.0"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);

            if (isset($data['id'])) {
                $customerId = $data['id'];
            } else {
                echo "<p>Erro ao criar cliente: " . htmlspecialchars($data['errors'][0]['description']) . "</p>";
            }
        }

        if ($customerId === null) {
            return false;
        }

        // URL da API Asaas
        $url = $this->url . "payments";

        // Monta os dados em um array
        $descricaoLimitada = mb_strimwidth("Compra de $nome", 0, 34, "...");
        $postData = [
            "billingType" => "BOLETO",
            "customer" => $customerId,
            "value" => $valor,
            "dueDate" => date('Y-m-d', strtotime('+1 day')),
            "externalReference" => $vendaId,
            "description" => $descricaoLimitada
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
        if (!isset($data['bankSlipUrl'])) {
            // Extrai os dados de interesse
            file_put_contents('/www/log/Asaas_boleto_erro.txt',$response);
            return false;
        }

        return $data['bankSlipUrl'];

    }//end function sendjson

} 


?>