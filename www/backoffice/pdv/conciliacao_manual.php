<?php 
die("Programa Bloqueado.<br>Por favor, solicite o desbloqueio para Fabio ou Glaucia.");
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";

if($Conciliar && b_IsBKOUsuarioSondaIntegracao()) {
    
    //Capturando e convertendo os IDS de transações com o banco na lista
    $ids = str_replace(PHP_EOL, ",",trim($ids));
    
    //Buscando os dados necessários para executar a atualização
    $sql ="select idvenda,idcliente,total from tb_pag_compras where id_transacao_itau IN (".$ids.") and tipo_cliente='LR' and iforma='".$forma_pagamentos."' and status != 3;";
    //echo $sql."<br>";
    $rs_transacoes = SQLexecuteQuery($sql);
    //capturando o total de registros
    $registros_total = pg_num_rows($rs_transacoes);
    
    //verificando o sucesso na busca
    if(!$rs_transacoes || $registros_total == 0) {
            echo "Nenhum pagamento encontrado.<br>";
    } else {
            while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){
                
                //Setando variável de controle da transação
                $msg = "";
                // Iniciando a transação
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.<br>";
                
                //Atualizando a tabela de pagamentos
                $sql = "update tb_pag_compras set status=3, datacompra= datainicio+'10 minutes'::interval, dataconfirma=datainicio+'10 minutes'::interval
                        where idvenda = ".$rs_transacoes_row['idvenda']."
                        and tipo_cliente = 'LR'
                        and status != 3
                        and iforma='".$forma_pagamentos."';
                        ";
                //echo $sql."<br>";
                $ret_pagto = SQLexecuteQuery($sql);
                if(!$ret_pagto) $msg .= "Erro ao atualizar o Pagamento de LAN (LR) do meio de pagamento [".$forma_pagamentos."] IdVenda [".$rs_transacoes_row['idvenda']."].<br>";
                
                //Atualizando a tabela de pedidos
                $sql = "update tb_dist_venda_games set vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'].", vg_ultimo_status_obs = vg_ultimo_status_obs = concat('Ajuste Manual para Venda Realizada (5) em ',to_char((vg_data_inclusao+'10 minutes'::interval),'DD/MM/YYYY HH24:MI:SS')),vg_pagto_valor_pago=".($rs_transacoes_row['total']/100)."
                        where vg_id  = ".$rs_transacoes_row['idvenda']."
                        and vg_ultimo_status != ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'].";
                        ";
                //echo $sql."<br>";
                $ret_pedido = SQLexecuteQuery($sql);
                if(!$ret_pedido) $msg .= "Erro ao atualizar o Pedido [".$rs_transacoes_row['idvenda']."].<br>";
                
                //Atualizando o Saldo do Usuário
                $sql = "update dist_usuarios_games set ug_perfil_saldo=ug_perfil_saldo+".($rs_transacoes_row['total']/100)."
                        where ug_id=".$rs_transacoes_row['idcliente'].";
                        ";
                //echo $sql."<br>";
                $ret_usuario = SQLexecuteQuery($sql);
                if(!$ret_usuario) $msg .= "Erro ao atualizar o saldo do usuário [".$rs_transacoes_row['idcliente']."].<br>";
                
                // Finalizando a transação
                if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
			$ret_commit = SQLexecuteQuery($sql);
			if(!$ret_commit) $msg .= "Erro ao comitar transação.\n";
		} else {
			$sql = "ROLLBACK TRANSACTION ";
                	$ret_rollback = SQLexecuteQuery($sql);
			if(!$ret_rollback) $msg .= "Erro ao dar rollback na transação.\n";
		} //end else
                
                if(empty($msg)) {
                    echo "Pedido [".$rs_transacoes_row['idvenda']."] da LAN [".$rs_transacoes_row['idcliente']."] no Valor de [R$ ".  number_format(($rs_transacoes_row['total']/100), 2, ",", ".")."] : Concilado com Sucesso!<br>";
                }//end if(empty($msg))
                else {
                    echo $msg;
                }//end else

            } //end while
            
    } //end else do if(!$rs_transacoes || $registros_total == 0)
    
} //end if($Conciliar) 
?>
<script language="JavaScript">
function validaForm() {
    if (document.form1.ids.value == '' || document.form1.ids.value.trim() == '') {
        alert('Obrigatório informar os IDS das transações junto ao Banco!');
        return false;
    }
    else return true;
}
</script>
<style>
    .wraper {
        margin: 0 auto;
        width: 470px;
    }
    .titulo {
        color: #0F64CF;
        font-size: 18px;
        font-weight: 900;
    }

    select {
        opacity: 0.75;
    }

    input[type="submit"] {
        object-position: 10px;
        position: relative;
        float: right;
    }
    .textarea {
        margin-left: 20%;
    }
    textarea {
        margin-left: 10%;
    }
</style>
<br/>
<div class="wraper">
    <form name="form1" id="form1" method="post" action="" onsubmit="return validaForm();">
        <h2 class="titulo">Conciliação Manual para LAN House</h2>

        <label for="forma_pagamentos">Selecione o banco:</label>
        <select id='forma_pagamentos' name='forma_pagamentos'>
        <?php
        foreach ($GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'] as $key => $value) {
            echo "<option value='".$key."' ".(($key==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE'])?"selected":"disabled").">".$value."</option>";
        } //end foreach ($GLOBALS['FORMAS_PAGAMENTO'] as $key => $value)
        ?>
        </select>

        <br/>
        <br/>

        <div class="textarea">
            <label for="ids">Ids das transações dos Bancos:</label><br/>
            <textarea cols="10" rows="20" id="ids" name="ids"></textarea>
        </div>

        <br/>
        <br/>
        <input type="submit" name="Conciliar" id="Conciliar"/>
    </form>    
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>