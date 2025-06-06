<?php
require_once "/www/includes/load_dotenv.php";
class classBoleto
{

    private $access_token;
    private $url;

    public function __construct()
    {
        $token = getenv('ASAAS_ACCESS_TOKEN');

        if ($token == "") {
            echo ("<br><br>ERRO ao obter acesso ao Banco! Asaas.<br>Obrigado.");
        } else {
            $this->setAccessToken($token);
            $this->url = getenv('ASAAS_API_URL');
        }

    }//end function __construct()

    private function removerAcentos($string) {
        $acentos = array(
            '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A',
            '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'E',
            '�' => 'I', '�' => 'I', '�' => 'I', '�' => 'I',
            '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O',
            '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'U',
            '�' => 'C', '�' => 'N',
            '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a',
            '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'e',
            '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'i',
            '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o',
            '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'u',
            '�' => 'c', '�' => 'n'
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
            // Cliente n�o encontrado, criar novo cliente
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

        // Configura��es do cURL
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

        // Executa a requisi��o
        $response = curl_exec($ch);

        // Verifica se ocorreu algum erro
        if (curl_errno($ch)) {
            echo 'Erro no cURL: ' . curl_error($ch);
            exit;
        }

        // Fecha a conex�o cURL
        curl_close($ch);

        // Converte a resposta JSON para um array associativo
        $data = json_decode($response, true);

        // Verifica se a resposta cont�m os dados esperados
        if (!isset($data['bankSlipUrl'])) {
            // Extrai os dados de interesse
            file_put_contents('/www/log/Asaas_boleto_erro.txt',$response);
            return false;
        }

        return $data['bankSlipUrl'];

    }//end function sendjson

} 


?>