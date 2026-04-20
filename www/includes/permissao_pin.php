<?php

/**
 * @return bool
 */
function permissao_visualizar_pin(): bool
{
    global $connid;
    static $has_permission = null;

    // Retorna do cache em memória se já foi verificado para não fazer queries repetidas num loop de tabela
    if ($has_permission !== null) {
        return (bool)$has_permission;
    }

    // Se não tiver sessão ou id, bloqueia por padrão
    if (empty($_SESSION['iduser_bko_pub'])) {
        $has_permission = false;
        return $has_permission;
    }

    $id_usuario = (int)$_SESSION['iduser_bko_pub'];

    // Se a conexão não estiver disponível, bloqueia por segurança
    if (!$connid) {
        return false;
    }

    // Consulta no banco de dados
    $sql = "SELECT visualiza_dados FROM usuarios WHERE id = $1 LIMIT 1";
    $result = @pg_query_params($connid, $sql, array($id_usuario));

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        if ($row && isset($row['visualiza_dados']) && strtoupper((string)$row['visualiza_dados']) === 'S') {
            $has_permission = true;
            return $has_permission;
        }
    }

    $has_permission = false;
    return $has_permission;
}

/**
 * @param mixed $id
 * @return mixed
 */
function mascarar_pin_id(mixed $id): mixed
{
    if (permissao_visualizar_pin()) {
        return $id;
    }
    if (!$id) return $id;
    return '***';
}

/**
 * @param mixed $codigo
 * @return mixed
 */
function mascarar_pin_codigo(mixed $codigo): mixed
{
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
