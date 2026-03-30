<?php
$url = "https://payment-spi.bigpoint.com";

// Extrai o domínio e a porta da URL
$urlParts = parse_url($url);
$host = $urlParts['host'];
$port = isset($urlParts['port']) ? $urlParts['port'] : ($urlParts['scheme'] === 'https' ? 443 : 80);

// Tenta abrir uma conexão de rede (Timeout de 5 segundos)
$socket = @fsockopen($host, $port, $errno, $errstr, 5);

if ($socket) {
    echo "foi possivel acessar";
    fclose($socket);
} else {
    echo "não foi possível acessar";
}

echo "\n";