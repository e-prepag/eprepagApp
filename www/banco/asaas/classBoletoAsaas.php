<?php
require_once "/www/includes/load_dotenv.php";
require_once "/www/includes/writeIfPossible.php";
class classBoleto
{

    private $access_token;
    private $url;

    public function __construct()
    {
        $token = getenv('ASAAS_ACCESS_TOKEN');

        if ($token == "") {
            error_log("[Asaas_boleto] token_nao_configurado");
        } else {
            $this->setAccessToken($token);
            $this->url = getenv('ASAAS_API_URL');
        }

    }//end function __construct()

    private function removerAcentos($string) {
        $acentos = array(
            'С' => 'A', 'Р' => 'A', 'Т' => 'A', 'У' => 'A', 'Ф' => 'A', 'Х' => 'A',
            'Щ' => 'E', 'Ш' => 'E', 'Ъ' => 'E', 'Ы' => 'E',
            'Э' => 'I', 'Ь' => 'I', 'Ю' => 'I', 'Я' => 'I',
            'г' => 'O', 'в' => 'O', 'д' => 'O', 'е' => 'O', 'ж' => 'O',
            'к' => 'U', 'й' => 'U', 'л' => 'U', 'м' => 'U',
            'Ч' => 'C', 'б' => 'N',
            'с' => 'a', 'р' => 'a', 'т' => 'a', 'у' => 'a', 'ф' => 'a', 'х' => 'a',
            'щ' => 'e', 'ш' => 'e', 'ъ' => 'e', 'ы' => 'e',
            'э' => 'i', 'ь' => 'i', 'ю' => 'i', 'я' => 'i',
            'ѓ' => 'o', 'ђ' => 'o', 'є' => 'o', 'ѕ' => 'o', 'і' => 'o',
            'њ' => 'u', 'љ' => 'u', 'ћ' => 'u', 'ќ' => 'u',
            'ч' => 'c', 'ё' => 'n'
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

        $logFilePath = "/www/arquivos_gerados/logs/Asaas_boleto.txt";
        $timestamp = date("Y-m-d H:i:s");
        $logEntry = "resultado data: " . $timestamp . ", venda_id: " . $id_pedido . ", cpfCnpj: " . $cpfCnpj . ", email: " . $email . ", nomeCliente: " . $nomeCliente .
            " ---" . json_encode($resposta) . "----" . serialize($resposta) . "\r\n";
        if (!writeFileIfPossible($logFilePath, $logEntry)) {
            error_log("[Asaas_boleto] falha_ao_gravar_log venda_id=" . $id_pedido);
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
            // Cliente nуo encontrado, criar novo cliente
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
                error_log("[Asaas_boleto] erro_criar_cliente response=" . json_encode($data));
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

        // Configuraчѕes do cURL
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

        // Executa a requisiчуo
        $response = curl_exec($ch);

        // Verifica se ocorreu algum erro
        if (curl_errno($ch)) {
            $curlError = curl_error($ch);
            curl_close($ch);
            error_log("[Asaas_boleto] erro_curl_pagamento venda_id=" . $vendaId . " erro=" . $curlError);
            return false;
        }

        // Fecha a conexуo cURL
        curl_close($ch);

        // Converte a resposta JSON para um array associativo
        $data = json_decode($response, true);

        // Verifica se a resposta contщm os dados esperados
        if (!isset($data['bankSlipUrl'])) {
            // Extrai os dados de interesse
            if (!writeFileIfPossible("/www/arquivos_gerados/logs/Asaas_boleto_erro.txt", $response . PHP_EOL)) {
                error_log("[Asaas_boleto] resposta_sem_bankSlipUrl venda_id=" . $vendaId . " response=" . $response);
            }
            return false;
        }

        return $data['bankSlipUrl'];

    }//end function sendjson

} 


?>