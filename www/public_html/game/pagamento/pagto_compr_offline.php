<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';

$controller = new HeaderController;
$controller->setHeader();

require_once DIR_INCS . "inc_register_globals.php";
require_once DIR_INCS . "gamer/functions.php";

require_once DIR_INCS . "gamer/venda_e_modelos_logica.php";

require_once DIR_INCS . "gamer/pagto_informa_dep_doc_transf_verificacoes.php";

$pagto_data_data_full = $pagto_data_data ." ".$pagto_data_horas .":". $pagto_data_minutos;

require_once DIR_INCS . "gamer/pagto_informa_dep_doc_transf_validacoes.php";


if($msg == ""){
        //move arquivos temporarios da venda para definitivo
        $arquivos = buscaArquivosIniciaCom($FOLDER_COMMERCE_UPLOAD_TMP, 'nome', 'asc', "money_comprovante_" . $venda_id . "_");
        for($j = 0; $j < count($arquivos); $j++){
                if(is_file($FOLDER_COMMERCE_UPLOAD_TMP . $arquivos[$j])) {
                        if(!rename($FOLDER_COMMERCE_UPLOAD_TMP . $arquivos[$j], $FOLDER_COMMERCE_UPLOAD . $arquivos[$j])){
                                $msg .= "Não foi possivel salvar o comprovante, tente novamente.\n"; 
                        }
                }
        }
}
	
if($msg != ""){
        //redireciona
?>
        <form name="pagamento" id="pagamento" method="POST" action="/game/mensagem.php">
            <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
            <input type='hidden' name='titulo' id='titulo' value='Informa Pagamento'>
            <input type='hidden' name='link' id='link' value='/game/pedido/passo-1.php'>
        </form>
        <script language='javascript'>
            document.getElementById("pagamento").submit();
        </script>        
<?php
        die();        
}

//Atualiza dados do pagamento
$sql  = "update tb_venda_games set 
        	vg_ultimo_status = " . 		SQLaddFields($STATUS_VENDA['DADOS_PAGTO_RECEBIDO'], "") . ",
        	vg_pagto_data_inclusao = ".	SQLaddFields("CURRENT_TIMESTAMP", "") . ",
        	vg_pagto_banco = " . 		SQLaddFields($pagto_banco, "s") . ",
        	vg_pagto_local = " . 		SQLaddFields($pagto_local, "s") . ",
        	vg_pagto_num_docto = " . 	SQLaddFields(implode("|", $pagto_num_docto), "s") . ",
        	vg_pagto_valor_pago = " . 	SQLaddFields(moeda2numeric($pagto_valor_pago), "") . ",
        	vg_pagto_data = " . 		SQLaddFields(monta_data_gravacao($pagto_data_data)." ".$pagto_data_horas.":".$pagto_data_minutos, "s") . " 
        where vg_id = " . $venda_id.";";
$ret = SQLexecuteQuery($sql);
if(!$ret){
        $msg = "Erro ao atualizar venda.\n";
        //redireciona
?>
        <form name="pagamento" id="pagamento" method="POST" action="/game/mensagem.php">
            <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
            <input type='hidden' name='titulo' id='titulo' value='Informa Pagamento'>
            <input type='hidden' name='link' id='link' value='/game/pedido/passo-1.php'>
        </form> 
        <script language='javascript'>
            document.getElementById("pagamento").submit();
        </script>        
<?php
        die();        
} 
else {
        //Log na base
        usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['INFORMA_DADOS_DE_PAGAMENTO'], null, $venda_id);
        
        include DIR_INCS . "gamer/venda_e_modelos_logica.php";
?>
<div class="container txt-azul-claro bg-branco">
    <div class="row bottom20">
        <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
            <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-azul-claro top10">
                <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">informações de depósito</h4></strong>
            </div>
<?php
        require_once DIR_INCS . "gamer/venda_e_modelos_view.php";

        pg_result_seek($rs_venda_modelos, 0);
        
        $rs_venda_row = pg_fetch_array($rs_venda_modelos);
        $venda_status 		 = $rs_venda_row['vg_ultimo_status'];
        $pagto_data_inclusao = $rs_venda_row['vg_pagto_data_inclusao'];
        $pagto_banco 		 = $rs_venda_row['vg_pagto_banco'];
        $pagto_local 		 = $rs_venda_row['vg_pagto_local'];
        $pagto_num_docto 	 = $rs_venda_row['vg_pagto_num_docto'];
        $pagto_valor_pago 	 = $rs_venda_row['vg_pagto_valor_pago'];
        $pagto_data 		 = $rs_venda_row['vg_pagto_data'];
        $pagto_num_docto 	 = explode("|", $pagto_num_docto);

        if($venda_status == $STATUS_VENDA['PEDIDO_EFETUADO']){
                //redireciona
                $msg = "Problema com seu Status do Pedido. Por favor, entre em conatto com o suporte!";
?>
                <form name="pagamento" id="pagamento" method="POST" action="/game/mensagem.php">
                    <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
                    <input type='hidden' name='titulo' id='titulo' value='Informa Pagamento'>
                    <input type='hidden' name='link' id='link' value='/game/pedido/passo-1.php'>
                </form>
                <script language='javascript'>
                    document.getElementById("pagamento").submit();
                </script>        
<?php
                die();
        } 
        elseif($venda_status == $STATUS_VENDA['DADOS_PAGTO_RECEBIDO'] ||
                $venda_status == $STATUS_VENDA['PAGTO_CONFIRMADO'] 	||
                $venda_status == $STATUS_VENDA['VENDA_REALIZADA']) { 
?>
            <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-preto bg-cinza-claro p-8">
                <div class="col-md-12">
                    <div class="col-md-4 top10"><strong class='pull-right hidden-sm hidden-xs'>Banco:</strong><strong class='pull-left hidden-lg hidden-md'>Banco:</strong></div>
                    <div class="col-md-8 col-sm-12 col-xs-12 top10">
                        <span class="pull-left">
                        <?php echo $PAGTO_BANCOS[$pagto_banco]; ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                    <div class="col-md-4 top10"><strong class='pull-right hidden-sm hidden-xs'>Local:</strong><strong class='pull-left hidden-lg hidden-md'>Local:</strong></div>
                    <div class="col-md-8 col-sm-12 col-xs-12 top10">
                        <span class="pull-left">
                        <?php echo $PAGTO_LOCAIS[$pagto_banco][$pagto_local]; ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                    <div class="col-md-4 top10"><strong class='pull-right hidden-sm hidden-xs'>Data do Pagamento:</strong><strong class='pull-left hidden-lg hidden-md'>Data do Pagamento:</strong></div>
                    <div class="col-md-8 col-sm-12 col-xs-12 top10">
                        <span class="pull-left">
                        <?php echo formata_data_ts($pagto_data, 0, false, false); ?>
                        </span>
                    </div>
                </div>
<?php
                $pagto_nome_docto_Ar = explode(";", $PAGTO_NOME_DOCTO[$pagto_banco][$pagto_local]);
                for($i=0; $i<count($pagto_nome_docto_Ar); $i++) {
?>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                    <div class="col-md-4 top10"><strong class='pull-right hidden-sm hidden-xs'><?php echo $pagto_nome_docto_Ar[$i]; ?>:</strong><strong class='pull-left hidden-lg hidden-md'><?php echo $pagto_nome_docto_Ar[$i]; ?>:</strong></div>
                    <div class="col-md-8 col-sm-12 col-xs-12 top10">
                        <span class="pull-left">
                        <?php echo $pagto_num_docto[$i]; ?>
                        </span>
                    </div>
                </div>
<?php 
                } //end for
                if( 	($pagto_banco == "001" && $pagto_local == "06") ||
                ($pagto_banco == "237" && $pagto_local == "06") ||
                ($pagto_banco == "104" && $pagto_local == "06") ) {
                        $arquivos = buscaArquivosIniciaCom($FOLDER_COMMERCE_UPLOAD, 'nome', 'asc', "money_comprovante_" . $venda_id . "_");
                        if(count($arquivos) > 0) {  
?>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                    <div class="col-md-4 top10"><strong class='pull-right hidden-sm hidden-xs'>Comprovante:</strong><strong class='pull-left hidden-lg hidden-md'>Comprovante:</strong></div>
<?php 
                            for($j = 0; $j < count($arquivos); $j++){ 
?>
                    <div class="col-md-8 col-sm-12 col-xs-12 top10">
                        <span class="pull-left">
                            <a target="_blank" href="pagto_compr_down.php?venda=<?php echo $venda_id?>&arquivo=<?php echo $arquivos[$j]?>">Comprovante <?php echo ($j+1)?></a>
                        </span>
                    </div>
<?php 
                            } //end for
?>
                </div>
<?php
                        } //end if(count($arquivos) > 0) 
                } //end if( 	($pagto_banco == "001" && $pagto_local == "06")
?>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                    <div class="col-md-4 top10"><strong class='pull-right hidden-sm hidden-xs'>Valor Pago:</strong><strong class='pull-left hidden-lg hidden-md'>Valor Pago:</strong></div>
                    <div class="col-md-8 col-sm-12 col-xs-12 top10">
                        <span class="pull-left">
                        <?php echo number_format($pagto_valor_pago, 2, ',','.'); ?>
                        </span>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 espacamento txt-preto top10">
                <div class="col-md-4 col-md-offset-4 bg-cinza-claro text-center">
                    <h5><strong>Status</strong></h5>
                    <h6><?php echo $STATUS_VENDA_DESCRICAO[$venda_status]; ?></h6>
                </div>
            </div>
<?php 
        } //end elseif($venda_status == $STATUS_VENDA['DADOS_PAGTO_RECEBIDO']
?>
        </div>
    </div>
</div>
</div>
<?php    
        require_once DIR_WEB . "game/includes/footer.php";
        
}//end else do if(!$ret)

//Fechando Conexão
pg_close($connid);

?>
