<?php

/**
 * Classe UserLanAuxiliar
 *
 * Serve para fazer algumas verificações como
 * verificar se o email já esta cadastrado
 * verificar se o login já esta em uso
 * etc
 */
require_once DIR_CLASS . "pdv/classGamesUsuario.php";

class UserLan {

    private $db;

    private $mainSqlSearch = 'SELECT ug_id as qtde FROM dist_usuarios_games ';

    public function __construct()
    {
        $_ = ConnectionPDO::getConnection();
        $this->db = $_->getLink();
    }

    private function countResultFromOneField($field, $value, $upper = true) {
//        echo $this->mainSqlSearch . " WHERE {$field} = '".$value."' \n";
        $stmt = $this->db->prepare($this->mainSqlSearch . " WHERE {$field} = ?");
        if ( $upper ) {
            $value = strtoupper($value);
        }
        $stmt->execute(array(trim($value)));
        return count($stmt->fetchAll()) > 0;
    }

    public function hasLogin($login)
    {
        return $this->countResultFromOneField('ug_login', $login);
    }

    public function hasEmail($email)
    {
        $instUsuarioGames = new UsuarioGames;
        return $instUsuarioGames->existeEmail($email);
    }

    public function hasCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        return $this->countResultFromOneField('ug_cnpj', $cnpj);
    }

    public function hasCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $ret = $this->countResultFromOneField('ug_cpf', $cpf);
        if ( !$ret ) {
            $ret = $this->countResultFromOneField('ug_repr_legal_cpf', $cpf);
            if ( !$ret ) {
                $ret = $this->countResultFromOneField('ug_repr_venda_cpf', $cpf);
            }
        }
        return $ret;
    }
}