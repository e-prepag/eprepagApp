<?php
require_once "../../../includes/constantes.php";   
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_INCS . "pdv/corte_classPrincipal.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";

$_PaginaOperador1Permitido = 53; // o número magico 
$_PaginaOperador2Permitido = 54; 

validaSessao(); 

//login
$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
$usuario_id = $usuarioGames->getId();

//Validacao
//------------------------------------------------------------------------------------------------------------------
$msg = "";
$msgFatal = "";
$str_redirect = "";

//Valida estabelecimento
if($msg == "" && $msgFatal == "")
        if(!$usuario_id || !is_numeric($usuario_id) || trim($usuario_id) == "") $msgFatal = "Código do usuário inválido.\n";

//Valida codigo do boleto
if($msg == ""){
        if(!$bbc_boleto_codigo || trim($bbc_boleto_codigo) == "" || !is_numeric($bbc_boleto_codigo)) $msg = "Código do boleto inválido.\n";
}

//Busca dados do boleto
if($msg == ""){
        $sql = "select * from boleto_bancario_cortes bbc
                        where bbc.bbc_boleto_codigo = $bbc_boleto_codigo
                                and bbc.bbc_ug_id = $usuario_id";
        $rs_boleto = SQLexecuteQuery($sql);
        if(!$rs_boleto || pg_num_rows($rs_boleto) == 0) $msg = "Erro ao buscar boleto.\n";
        else {
                $rs_boleto_row = pg_fetch_array($rs_boleto);
                $bbc_bco_codigo = $rs_boleto_row['bbc_bco_codigo'];

                //Validacoes
                //-----------------------------------------------------------------------------------------------------
                //Banco
                if(!$bbc_bco_codigo || trim($bbc_bco_codigo) == "" || !is_numeric($bbc_bco_codigo)) $msg = "Código do banco inválido.\n";
        }
}

//define boleto
if($msg == ""){
        if($bbc_bco_codigo == $GLOBALS['BOLETO_COD_BANCO_BRADESCO']) $str_redirect = "corte_boleto_bradesco.php?bbc_boleto_codigo=$bbc_boleto_codigo";
        else $msg = "Boleto para o banco $bbc_bco_codigo não implementado.\n";
}

//redirect
if($msg == ""){
        ob_clean();
        header("Location: $str_redirect");
        exit;
}

echo str_replace("\n", "<br>", $msg);
?>
