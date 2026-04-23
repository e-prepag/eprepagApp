<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once "/www/includes/constantes.php";
require_once "/www/includes/gamer/functions.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/class/classEmailAutomatico.php";
require_once "/www/class/phpmailer/class.phpmailer.php";
require_once "/www/class/phpmailer/class.smtp.php";

function validarData($data)
{
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
}

function verificaData($data, $quantidadeMeses)
{
    if (!validarData($data)) {
        return false;
    }

    $dataInformada = new DateTime($data);
    $limite = new DateTime(); // hoje
    $limite->modify("-{$quantidadeMeses} months");

    return $dataInformada < $limite;
}

function sanitiza($string){
    return mb_convert_encoding(htmlspecialchars(mb_convert_encoding((string)$string, 'UTF-8', 'ISO-8859-1'), ENT_QUOTES, 'UTF-8'), 'ISO-8859-1', 'UTF-8');
}

function renderMerchantItem($merchant, $nivel) {
    return "<li><strong>Código do merchant:</strong> {$merchant['opr_codigo']}<br>" .
           "<strong>Nome:</strong> " . sanitiza($merchant['opr_nome']) . "<br>" .
           "<strong>CNPJ:</strong> " . (trim($merchant['opr_cnpj']) != "" ? sanitiza($merchant['opr_cnpj']) : "Não possui") . "<br>" .
           "<strong>Risco:</strong> $nivel<br>" .
           "<strong>Data da última análise:</strong> {$merchant['data_observacao']}<br>" .
           "<strong>Observação:</strong> " . (trim($merchant['observacao']) != "" ? sanitiza($merchant['observacao']) : "Não possui") . "</li><br>";
}

$pdo = ConnectionPDO::getConnection()->getLink();

$dataAtual = date('Y-m-d H:i:s');

try {
    // Conexão com o banco de dados usando PDO

    // Consulta para buscar alterações recentes de email
    $query = "SELECT *
			    FROM (
			    SELECT DISTINCT ON (o.opr_codigo)
			    	o.opr_codigo,
			    	o.opr_nome,
			    	o.opr_cnpj,
			    	o.opr_ultima_obs,
			    	COALESCE(oo.tipo_risco, 0) AS tipo_risco,
			    	COALESCE(oo.data_observacao, NULL) AS data_observacao,
			    	COALESCE(oo.observacao, '') AS observacao
			    	FROM operadoras o
			    	LEFT JOIN operadoras_obs oo ON o.opr_codigo = oo.opr_codigo
                    ORDER BY o.opr_codigo
		        ) ultimos ORDER BY ultimos.data_observacao DESC NULLS LAST";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $merchants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($merchants && count($merchants) > 0) {

        // Configurações do e-mail
        $to = 'jose.carlos@easygroupit.com, rc@e-prepag.com.br, glaucia@e-prepag.com.br';
        //$to = 'jose.carlos@easygroupit.com';
        $cc = "";
        $subject = 'Notificação de Risco de Merchants';
        $bcc = "";

        $message = "<h1>Notificação de Risco de Merchants</h1>";
        $message .= "<p>As seguintes merchants precisam de suas análises de KYP:</p>";
        $message .= "<ul>";

        $envia = false;

        $periodo_risco_baixo = 24; //meses
        $periodo_risco_medio = 18; //meses
        $periodo_risco_alto = 12; //meses

        $tipo_risco = [
            "alto" => 3,
            "medio" => 2,
            "baixo" => 1
        ];

        foreach ($merchants as &$merchant) {
            // Verifica se a data de atualização é válida

            $merchant['data_observacao'] = isset($merchant['data_observacao']) && $merchant['data_observacao'] !== null ? substr((string)$merchant['data_observacao'], 0, 10) : null;

            if (isset($merchant['data_observacao']) && $merchant['data_observacao'] !== null && validarData($merchant['data_observacao'])) {

                if ($merchant['tipo_risco'] == $tipo_risco['baixo'] && verificaData($merchant['data_observacao'], $periodo_risco_baixo)) {

                    $envia = true;

                    $message .= renderMerchantItem($merchant, "Baixo");

                } else if ($merchant['tipo_risco'] == $tipo_risco['medio'] && verificaData($merchant['data_observacao'], $periodo_risco_medio)) {

                    $envia = true;

                    $message .= renderMerchantItem($merchant, "Médio");

                } else if ($merchant['tipo_risco'] == $tipo_risco['alto'] && verificaData($merchant['data_observacao'], $periodo_risco_alto)) {

                    $envia = true;

                    $message .= renderMerchantItem($merchant, "Alto");
                }
            }
        }
        $message .= "</ul>";

        $message .= "<p>(Atualize a análise de risco desses merchants para não receber mais este e-mail)</p>";

        if (!$envia) {
            echo "\n-\n$dataAtual Todas as merchants com o risco em dias.\n-\n";
            exit;
        }
        if (function_exists('enviaEmail3')) {
            echo "\n-\n$dataAtual Enviando e-mail...\n";

            var_dump(enviaEmail3($to, $cc, $bcc, $subject, $message, ""));

            echo $message;

            echo "\nE-mail enviado com sucesso!\n-\n";
        } else {
            echo "\n-\n$dataAtual Falha ao enviar o e-mail.\n-\n";
        }
    } else {
        echo "\n-\n$dataAtual Nenhuma alteração recente encontrada.\n-\n";
    }
} catch (PDOException $e) {
    echo "\n-\n$dataAtual Erro na conexão com o banco de dados: " . $e->getMessage() ."\n-\n";
}
