<?php

function permissao_visualizar_pin() {
    return false; // Permissão desativada por enquanto
}

function mascarar_pin_id($id) {
    if (permissao_visualizar_pin()) {
        return $id;
    }
    if (!$id) return $id;
    return '***';
}

function mascarar_pin_codigo($codigo) {
    if (permissao_visualizar_pin()) {
        return $codigo;
    }
    if (!$codigo) return $codigo;
    
    $codigoStr = (string)$codigo;
    $len = strlen($codigoStr);
    if ($len <= 2) {
        return str_repeat('*', $len);
    }
    return str_repeat('*', $len - 2) . substr($codigoStr, -2);
}

?>
