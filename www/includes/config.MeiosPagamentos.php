<?php
// Arquivo de configuração de Meios de Pagamentos

// ============================================
// VETORES DE CONFIGURAÇÃO
// ============================================

$vetorHabilita = array(
    "0" => "Desativado",
    "1" => "Ativado"
);

$vetoropcao = array(
    "blupay" => "Casa do Crédito",
    "cielo" => "Cielo",
    "mercadopago" => "Mercado Pago",
    "asaas" => "Asaas",
);

$vetortroca = array(
    "a" => "Ativa",
    "i" => "Inativa"
);

$vetoropcao_boleto = array(
    "bradesco" => "Bradesco",
    "asaas" => "Asaas",
);

// ============================================
// FUNÇÃO PARA LER CONFIGURAÇÕES DO BANCO
// ============================================

function getConfiguracaoPagamento($chave, $valorPadrao = null) {
    static $cache = array(); // Cache para evitar múltiplas consultas
    
    // Verifica se já está em cache
    if (isset($cache[$chave])) {
        return $cache[$chave];
    }
    
    try {
        $conexao = ConnectionPDO::getConnection()->getLink();
        
        $sql = "SELECT valor FROM configuracao_pagamentos WHERE chave = :chave LIMIT 1";
        $query = $conexao->prepare($sql);
        $query->bindValue(":chave", $chave, PDO::PARAM_STR);
        $query->execute();
        
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado && isset($resultado['valor'])) {
            $valor = $resultado['valor'];
            
            // Converte para o tipo apropriado
            if (is_numeric($valor)) {
                $valor = ($valor == (int)$valor) ? (int)$valor : (float)$valor;
            }
            
            $cache[$chave] = $valor;
            return $valor;
        }
        
        $cache[$chave] = $valorPadrao;
        return $valorPadrao;
        
    } catch(Exception $e) {
        // Em caso de erro, registra no log e retorna valor padrão
        error_log("Erro ao buscar configuração '$chave': " . $e->getMessage());
        $cache[$chave] = $valorPadrao;
        return $valorPadrao;
    }
}

/**
 * Carrega todas as configurações de uma vez (mais eficiente)
 * @return array - Array associativo com todas as configurações
 */
function carregarTodasConfiguracoes() {
    static $todasConfigs = null;
    
    if ($todasConfigs !== null) {
        return $todasConfigs;
    }
    
    $todasConfigs = array();
    
    try {
        $conexao = ConnectionPDO::getConnection()->getLink();
        
        $sql = "SELECT chave, valor FROM configuracao_pagamentos";
        $query = $conexao->prepare($sql);
        $query->execute();
        
        $resultados = $query->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $valor = $row['valor'];
            
            // Converte para o tipo apropriado
            if (is_numeric($valor)) {
                $valor = ($valor == (int)$valor) ? (int)$valor : (float)$valor;
            }
            
            $todasConfigs[$row['chave']] = $valor;
        }
        
    } catch(Exception $e) {
        error_log("Erro ao carregar todas as configurações: " . $e->getMessage());
    }
    
    return $todasConfigs;
}

// ============================================
// CARREGA TODAS AS CONFIGURAÇÕES DE UMA VEZ
// ============================================

$configsPagamento = carregarTodasConfiguracoes();

// ============================================
// DEFINE AS CONSTANTES COM VALORES DO BANCO
// ============================================

// Constantes que definem se o Pagamento está 1 => Ativado ou 0 => Desativado

if (!defined('PAGAMENTO_BRADESCO')) {
    define("PAGAMENTO_BRADESCO", isset($configsPagamento['PAGAMENTO_BRADESCO']) ? $configsPagamento['PAGAMENTO_BRADESCO'] : 0);
}

if (!defined('PAGAMENTO_BANCO_BRASIL')) {
    define("PAGAMENTO_BANCO_BRASIL", isset($configsPagamento['PAGAMENTO_BANCO_BRASIL']) ? $configsPagamento['PAGAMENTO_BANCO_BRASIL'] : 0);
}

if (!defined('PAGAMENTO_ITAU')) {
    define("PAGAMENTO_ITAU", isset($configsPagamento['PAGAMENTO_ITAU']) ? $configsPagamento['PAGAMENTO_ITAU'] : 0);
}

if (!defined('PAGAMENTO_BOLETO')) {
    define("PAGAMENTO_BOLETO", isset($configsPagamento['PAGAMENTO_BOLETO']) ? $configsPagamento['PAGAMENTO_BOLETO'] : 0);
}

if (!defined('BANCO_BOLETO')) {
    define("BANCO_BOLETO", isset($configsPagamento['BANCO_BOLETO']) ? $configsPagamento['BANCO_BOLETO'] : "bradesco");
}

if (!defined('PAGAMENTO_EPREPAG_CASH')) {
    define("PAGAMENTO_EPREPAG_CASH", isset($configsPagamento['PAGAMENTO_EPREPAG_CASH']) ? $configsPagamento['PAGAMENTO_EPREPAG_CASH'] : 0);
}

if (!defined('PAGAMENTO_CIELO')) {
    define("PAGAMENTO_CIELO", isset($configsPagamento['PAGAMENTO_CIELO']) ? $configsPagamento['PAGAMENTO_CIELO'] : 0);
}

if (!defined('PAGAMENTO_PIX')) {
    define("PAGAMENTO_PIX", isset($configsPagamento['PAGAMENTO_PIX']) ? $configsPagamento['PAGAMENTO_PIX'] : 0);
}

if (!defined('PAGAMENTO_PIX_PROVEDOR')) {
    define("PAGAMENTO_PIX_PROVEDOR", isset($configsPagamento['PAGAMENTO_PIX_PROVEDOR']) ? $configsPagamento['PAGAMENTO_PIX_PROVEDOR'] : "asaas");
}

if (!defined('PAGAMENTO_PIX_PROVEDOR2')) {
    define("PAGAMENTO_PIX_PROVEDOR2", isset($configsPagamento['PAGAMENTO_PIX_PROVEDOR2']) ? $configsPagamento['PAGAMENTO_PIX_PROVEDOR2'] : "mercadopago");
}

if (!defined('PAGAMENTO_PIX_CHAVEAMENTO')) {
    define("PAGAMENTO_PIX_CHAVEAMENTO", isset($configsPagamento['PAGAMENTO_PIX_CHAVEAMENTO']) ? $configsPagamento['PAGAMENTO_PIX_CHAVEAMENTO'] : "i");
}

if (!defined('VALOR_TROCA')) {
    define("VALOR_TROCA", isset($configsPagamento['VALOR_TROCA']) ? $configsPagamento['VALOR_TROCA'] : 0);
}

// ============================================
// LIMPA VARIÁVEIS TEMPORÁRIAS
// ============================================

unset($configsPagamento);

?>