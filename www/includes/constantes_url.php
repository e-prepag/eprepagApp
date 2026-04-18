<?php

/**
 * Helper seguro para ENV
 */
function env(string $key, ?string $default = null): string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === '') {
        if ($default !== null) {
            return $default;
        }

        throw new RuntimeException("Vari�vel de ambiente {$key} n�o definida.");
    }

    return $value;
}

/**
 * Valida��o de protocolo
 */
$hasCertificate = filter_var(
    env('HAS_CERTIFICATE', 'false'),
    FILTER_VALIDATE_BOOLEAN
);

$tipo_http = $hasCertificate ? 'https://' : 'http://';

/**
 * URL base validada
 */
$baseUrl = env('EPREPAG_URL');
$baseUrlBO = env('BACKOFFICE_URL');


if (!filter_var($tipo_http . $baseUrl, FILTER_VALIDATE_URL)) {
    throw new RuntimeException('EPREPAG_URL inv�lida.');
}

/**
 * Constantes
 */
define('EPREPAG_URL', $baseUrl);
define('EPREPAG_URL_HTTP', $tipo_http . $baseUrl);
define('EPREPAG_URL_HTTPS', $tipo_http . $baseUrl);

define('EPREPAG_URL_HTTP_COM', $tipo_http . $baseUrl);
define('EPREPAG_URL_HTTPS_COM', $tipo_http . $baseUrl);
define('EPREPAG_URL_COM', $baseUrl);

define('BACKOFFICE_URL', $baseUrlBO);

/**
 * URLs fixas
 */
const NOVIDADES_URL = "https://e-prepagpdv.com.br/category/blog-pdv/";
const SOBRE_URL = "https://solucoes.e-prepag.com/a-e-prepag/";
const QUEMSOMOS_URL = "https://solucoes.e-prepag.com/quem-somos/";
const CARTAO_URL = "https://solucoes.e-prepag.com/cartao-e-prepag-2/";
const COMPRASEG_URL = "https://solucoes.e-prepag.com/compra-segura/";
const FORMASPAG_URL = "https://solucoes.e-prepag.com/formas-de-pagamento/";
const SOLUCOES_URL = "//solucoes.e-prepag.com";
const EPPDV_URL = "https://e-prepagpdv.com.br/";
