<?php
$pos_pagina = false; //apenas para nao exibir erro/ resolver depois

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

/* 
    CONTROLLER
 */
require_once $raiz_do_projeto."class/business/EstornoChargeBackBO.class.php";
require_once $raiz_do_projeto."class/business/CategoriaEstornoChargebackBO.class.php";

$objEstornoChargeBack = new EstornoChargeBackBO();
$objCategoriaEstornoChargeback = new CategoriaEstornoChargebackBO();

//Buscando somente as Categorias Ativas
$CategoriaEstornoChargeback = $objCategoriaEstornoChargeback->pegaCategoria("cec_status = 1");

// Criando vetor com as opções possíveis para tipo de devolução
//  ec_tipo smallint NOT NULL, -- Campo contendo o tipo. Onde: 1 => ChargeBack e 2 => Estorno.
$vetorTipo = array(
                    '1' => 'ChargeBack',
                    '2' => 'Estorno'
                    );

// Criando vetor com as opções possíveis para tipo de usuário
// ec_tipo_usuario character varying(1) NOT NULL, -- Campo contendo o tipo de usuário do estorno. Onde: G => Gamer e L => Lan House.
$vetorTipoUsuario = array(
                    'G' => 'Gamer',
                    'L' => 'Lan House'
                    );

// Criando vetor com as opções possíveis para forma de devolução
// ec_forma_devolucao smallint, -- Campo contendo a forma de devolução no caso de Estorno e usuário ser LAN. Onde: 1 => Devolução em Saldo e 2 => Devolução através de Depósito.
$vetorFormaDevolucao = array(
                    '1' => 'Devolução em Saldo',
                    '2' => 'Devolução através de Depósito'
                    );

// Criando vetor com as opções possíveis para PINs Bloqueados pelo Publisher
// ec_pin_bloqueado smallint NOT NULL, -- Campo contendo informação se o PIN relacionado ao pedido foi Bloqueado ou não. Onde: 0 => NÃO foi Bloqueado e 1 => Foi Bloqueado.
$vetorPINsBloqueados = array(
                    '0' => 'NÃO foi Bloqueado',
                    '1' => 'Foi Bloqueado'
                    );

// Criando vetor com as opções possíveis para Tipo de Contas para Estorno
// edb_tipo_conta smallint NOT NULL, -- Campo contendo o tipo da conta do Titular. Onde:  1 => Conta Corrente e 2 => Conta Poupança.
$vetorTpoContas = array(
                    '1' => 'Conta Corrente',
                    '2' => 'Conta Poupança'
                    );

// Criando vetor com as opções possíveis para IDs de Publishers
// opr_codigo integer NOT NULL, -- Campo contendo o ID do Publisher.
$sql = "select 
                opr_codigo, 
                opr_nome
        from operadoras
        where opr_status = '1'
        order by opr_nome
        ";
$rs_operadoras_operantes = SQLexecuteQuery($sql);
while($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
    $vetorPublisher[$rs_operadoras_operantes_row['opr_codigo']] = $rs_operadoras_operantes_row['opr_nome'];
}//end while



if(!empty($_POST)){
    //Colocando valor no formato para Banco de Dados
    $_POST["ec_valor"] = Util::getNumero($_POST["ec_valor"],true);
    if(isset($_POST['busca'])){
        if(!empty($_POST["cec_id"]))                $filtros["cec_id"] = "ec.cec_id = ".$_POST["cec_id"]; 
        if(!empty($_POST["ec_data_devolucao"]))     $filtros["ec_data_devolucao"] = "ec_data_devolucao >= '".Util::getData($_POST["ec_data_devolucao"], true)." 00:00:00'";
        if(!empty($_POST["ec_data_devolucao_fim"])) $filtros["ec_data_devolucao_fim"] = "ec_data_devolucao <= '".Util::getData($_POST["ec_data_devolucao_fim"], true)." 23:59:59'";
        if(!empty($_POST["ec_tipo"]))               $filtros["ec_tipo"] = "ec_tipo = ".$_POST["ec_tipo"]; 
        if(!empty($_POST["ec_tipo_usuario"]))       $filtros["ec_tipo_usuario"] = "ec_tipo_usuario = '".$_POST["ec_tipo_usuario"]."'"; 
        if(!empty($_POST["ec_forma_devolucao"]))    $filtros["ec_forma_devolucao"] = "ec_forma_devolucao = ".$_POST["ec_forma_devolucao"]; 
        if(!empty($_POST["ec_pin_bloqueado"]) 
           || $_POST["ec_pin_bloqueado"] === '0')   $filtros["ec_pin_bloqueado"] = "ec_pin_bloqueado = ".$_POST["ec_pin_bloqueado"]; 
        if(!empty($_POST["opr_codigo"]))            $filtros["opr_codigo"] = "opr_codigo = ".$_POST["opr_codigo"]; 
        if(!empty($_POST["ug_id"]))                 $filtros["ug_id"] = "ug_id = ".$_POST["ug_id"]; 
        if(!empty($_POST["edb_cpf_cnpj"]))          $filtros["edb_cpf_cnpj"] = "edb_cpf_cnpj = '".$_POST["edb_cpf_cnpj"]."'"; 
        if(!empty($_POST["edb_titular"]))           $filtros["edb_titular"] = "UPPER(edb_titular) like '%".strtoupper ($_POST["edb_titular"])."%'"; 
        if(!empty($_POST["vg_id"]))                 $filtros["vg_id"] = "vg_id = ".$_POST["vg_id"]; 
        if(!empty($_POST["ec_cod_autorizacao"]))    $filtros["ec_cod_autorizacao"] = "ec_cod_autorizacao = '".$_POST["ec_cod_autorizacao"]."'"; 
        if(!empty($_POST["ec_valor"]))              $filtros["ec_valor"] = "ec_valor = ".$_POST["ec_valor"]; 
        $EstornoChargeBack = $objEstornoChargeBack->pegaEstornoChargeBack($filtros);
    }elseif(isset($_POST['novoEstornoChargeback'])){
        $objEstornoChargeBack->insereEstornoChargeBack($_POST);
    }elseif(isset($_POST['editaEstornoChargeback'])){
        if(isset($_POST["ec_id"])){
            $objEstornoChargeBack->editaEstornoChargeback($_POST);
        }
        else
            echo "<script>alert('Problema ao obter Estorno / ChargeBack.'); location.href = '/gamer/estorno_chargeback/estorno_chargeback.php';</script>";
    }

}
/*
    FIM CONTROLLER
 */

include_once (isset($_GET["acao"]) && ($_GET["acao"] == "novo" || $_GET["acao"] == "edita")) ? 'estorno_chargeback_novo_edita.php' : 'estorno_chargeback_lista.php';


require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>