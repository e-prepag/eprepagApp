<?php require_once "/www/includes/bourls.php"; ?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12">
    <a href="estorno_chargeback.php?acao=novo" class="pull-right btn btn-sm btn-info">Novo Estorno ou ChargeBack</a>
</div>
<div class="col-md-12 top10 txt-preto fontsize-pp">
    <form id="buscaEstornoChargeback" name="buscaEstornoChargeback" method="post">
        <div class="top10 panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Filtros</h3>
            </div>
            <div class="panel-body">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ec_data_devolucao"  class="w100">Data da Devolução</label>
                        <input name="ec_data_devolucao" id="ec_data_devolucao" type="text" class="input-sm form-control  w150  dislineblock" <?php if(isset($_POST["ec_data_devolucao"])) echo "value='".$_POST["ec_data_devolucao"]."'"; ?> size="9" maxlength="10">
                        <span class="text-info p-3  dislineblock">a</span>
                        <input name="ec_data_devolucao_fim" id="ec_data_devolucao_fim" type="text" class="input-sm form-control w150 left5 dislineblock" id="ec_data_devolucao_fim" value="<?php if(isset($_POST["ec_data_devolucao_fim"])) echo $_POST["ec_data_devolucao_fim"]; else if(isset($_POST["ec_data_devolucao"])) echo $_POST["ec_data_devolucao"]; ?>" size="9" maxlength="10">
                        <span class="text-info p-3  dislineblock">(99/99/9999)</span>
                    </div>
                    <div class="form-group">
                        <label for="ec_tipo" class="w100">Categoria Devolução</label>
                        <select class="input-sm form-control w-auto" name="ec_tipo" id="ec_tipo" label="Categoria Devolução">
                            <option value="">Todas</option>
                            <?php
                            foreach ($vetorTipo as $key => $value) {
                            ?>
                            <option value="<?php echo $key; ?>" <?php if(isset($_POST["ec_tipo"]) && $_POST["ec_tipo"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                            <?php
                            }
                            ?>                    
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ec_tipo_usuario" class="w100">Tipo de Usuário</label>
                        <select class="input-sm form-control w-auto" name="ec_tipo_usuario" id="ec_tipo_usuario" label="Tipo de Usuário">
                            <option value="">Todas</option>
                                    <?php
                                    foreach ($vetorTipoUsuario as $key => $value) {
                                    ?>
                                    <option value="<?php echo $key; ?>" <?php if(isset($_POST["ec_tipo_usuario"]) && $_POST["ec_tipo_usuario"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                                    <?php
                                    }
                                    ?>                    
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ec_forma_devolucao" class="w100">Forma de Devolução:</label>
                        <select class="input-sm form-control w-auto" name="ec_forma_devolucao" id="ec_forma_devolucao" label="Forma de Devolução">
                            <option value="">Todas</option>
                                    <?php
                                    foreach ($vetorFormaDevolucao as $key => $value) {
                                    ?>
                                    <option value="<?php echo $key; ?>" <?php if(isset($_POST["ec_forma_devolucao"]) && $_POST["ec_forma_devolucao"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                                    <?php
                                    }
                                    ?>                    
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cec_id" class="w100">Categoria Motivo:</label>
<?php
                    if(!empty($CategoriaEstornoChargeback)){
?>
                        <select class="input-sm form-control w-auto" style="max-width: 430px;" name="cec_id" id="cec_id" label="Categoria Motivo">
                            <option value="">Todas</option>
<?php
                        foreach ($CategoriaEstornoChargeback as $CategoriaEstornoChargebackRow){
?>
                            <option value="<?php echo $CategoriaEstornoChargebackRow->getId(); ?>" <?php if(isset($_POST["cec_id"]) && $_POST["cec_id"] == $CategoriaEstornoChargebackRow->getId()) echo "selected"; ?>><?php echo $CategoriaEstornoChargebackRow->getDescricao();?></option>
<?php
                        }
?>                    
                        </select>
<?php
}else{
?>
                        <span>Não temos categorias de motivos cadastrados</span>
<?php
}
?>
                    </div>
                    <div class="form-group">
                        <label for="opr_codigo" class="w100">Publisher:</label>
                        <select class="input-sm form-control w-auto" name="opr_codigo" id="opr_codigo" label="Publisher">
                            <option value="">Todas</option>
                                    <?php
                                    foreach ($vetorPublisher as $key => $value) {
                                    ?>
                                    <option value="<?php echo $key; ?>" <?php if(isset($_POST["opr_codigo"]) && $_POST["opr_codigo"] === (string) $key) echo "selected"; ?>><?php echo $value;?></option>
                                    <?php
                                    }
                                    ?>                    
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ec_pin_bloqueado" class="w100">PINs Bloqueados Publisher:</label>
                        <select class="input-sm form-control w-auto" name="ec_pin_bloqueado" id="ec_pin_bloqueado" label="PINs Bloqueados pelo Publisher">
                            <option value="">Todas</option>
                                    <?php
                                    foreach ($vetorPINsBloqueados as $key => $value) {
                                    ?>
                                    <option value="<?php echo $key; ?>" <?php if(isset($_POST["ec_pin_bloqueado"]) && $_POST["ec_pin_bloqueado"] === (string) $key) echo "selected"; ?>><?php echo $value;?></option>
                                    <?php
                                    }
                                    ?>                    
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ug_id" class="w100">ID do Usuário</label>
                        <input type="text" <?php if(isset($_POST["ug_id"]))  echo "value='".$_POST["ug_id"]."'"; ?> name="ug_id" id="ug_id" class="input-sm form-control">
                    </div>
                    <div class="form-group">
                        <label for="edb_cpf_cnpj" class="w100">CPF/CNPJ do Titular</label>
                        <input type="text" <?php if(isset($_POST["edb_cpf_cnpj"]))  echo "value='".$_POST["edb_cpf_cnpj"]."'"; ?> name="edb_cpf_cnpj" id="edb_cpf_cnpj" class="input-sm form-control">
                    </div>
                    <div class="form-group">
                        <label for="edb_titular" class="w100">Titular</label>
                        <input type="text" <?php if(isset($_POST["edb_titular"]))  echo "value='".$_POST["edb_titular"]."'"; ?> name="edb_titular" id="edb_titular" class="input-sm form-control">
                    </div>
                    <div class="form-group">
                        <label for="vg_id" class="w100">ID do Pedido</label>
                        <input type="text" <?php if(isset($_POST["vg_id"]))  echo "value='".$_POST["vg_id"]."'"; ?> name="vg_id" id="vg_id" class="input-sm form-control">
                    </div>
                    <div class="form-group">
                        <label for="ec_cod_autorizacao" class="w100">Cód. Autorização</label>
                        <input type="text" <?php if(isset($_POST["ec_cod_autorizacao"]))  echo "value='".$_POST["ec_cod_autorizacao"]."'"; ?> name="ec_cod_autorizacao" id="ec_cod_autorizacao" class="input-sm form-control">
                    </div>
                    <div class="form-group">
                        <label for="ec_valor" class="w100">Valor (R$):</label>
                        <input type="text" <?php if(isset($_POST["ec_valor"]))  echo "value='".$_POST["ec_valor"]."'"; ?> name="ec_valor" id="ec_valor" class="input-sm form-control">
                    </div>
                </div>
          </div>
          <div class="col-md-12 text-center top20">
            <input type="hidden" name="busca" value="1">
            <input type="button" value="Limpar Filtros" id="limpar" name="limpar" class="btn btn-sm btn-info">
            <input type="button" value="Buscar" id="buscar" name="buscar" class="btn btn-sm btn-info">
          </div>
        </div>
    </form>
</div>
</div></div> <!-- fechando div container do topo para a tabela poder ocupar toda a largura da tela--> 
    <table class="table table-bordered bg-branco txt-preto fontsize-pp text-center">
        <thead>
            <tr>
                <th class="text-center">Data Devolução</th>
                <th class="text-center">Tipo Devolução</th>
                <th class="text-center">Cliente</th>
                <th class="text-center">Forma</th>
                <th class="text-center">ID Usuário</th>
                <th class="text-center">Titular</th>
                <th class="text-center">CPF Titular</th>
                <th class="text-center">Pedido</th>
                <th class="text-center">IP do Pedido</th>
                <th class="text-center">Motivo</th>
                <th class="text-center">Publisher</th>
                <th class="text-center">PIN Bloqueado Publisher</th>
                <th class="text-center">Valor R$</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
<?php 
            if(isset($EstornoChargeBack) && is_array($EstornoChargeBack)){
                $total_geral = 0;
                foreach($EstornoChargeBack as $EstornoChargeBackRow){
                    $total_geral += $EstornoChargeBackRow['ec_valor'];
?>
            <tr class="trListagem c-pointer estornoChargebackOpt" id="<?php echo $EstornoChargeBackRow['id']; ?>">
                <td><?php echo Util::getData($EstornoChargeBackRow['ec_data_devolucao']); ?></td>
                <td><?php echo $vetorTipo[$EstornoChargeBackRow['ec_tipo']]; ?></td>
                <td class="nobr"><?php echo $vetorTipoUsuario[$EstornoChargeBackRow['ec_tipo_usuario']]; ?></td>
                <td><?php echo (isset($EstornoChargeBackRow['ec_forma_devolucao'])?$vetorFormaDevolucao[$EstornoChargeBackRow['ec_forma_devolucao']]:""); ?></td>
                <td><?php echo $EstornoChargeBackRow['ug_id']; ?></td>
                <td><?php echo (isset($EstornoChargeBackRow['edb_titular'])?$EstornoChargeBackRow['edb_titular']:""); ?></td>
                <td class="nobr"><?php echo (isset($EstornoChargeBackRow['edb_cpf_cnpj'])?$EstornoChargeBackRow['edb_cpf_cnpj']:""); ?></td>
                <td><?php echo $EstornoChargeBackRow['vg_id']; ?></td>
                <td><?php echo $EstornoChargeBackRow['ec_ip_pedido']; ?></td>
                <td><?php echo $EstornoChargeBackRow['cec_descricao']; ?></td>
                <td class="nobr"><?php echo $vetorPublisher[$EstornoChargeBackRow['opr_codigo']]; ?></td>
                <td><?php echo $vetorPINsBloqueados[$EstornoChargeBackRow['ec_pin_bloqueado']]; ?></td>
                <td><?php echo Util::getNumero($EstornoChargeBackRow['ec_valor']); ?></td>
            </tr>
<?php
                }
?>
            <tr>
                <td colspan="12" align="right"><b>Total R$</b></td>
                <td><b><?php echo Util::getNumero($total_geral); ?></b></td>
            </tr>
<?php
            }else{
?>
            <tr>
                <td colspan="13">Nenhum resultado foi encontrado.</td>
            </tr>
<?php
            }
?>
        </tbody>
</table>
<script type="text/javascript" src="https://<?php echo $server_url_complete; ?>/js/jquery.mask.min.js"></script>
<link href="https://<?php echo $server_url_complete; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="https://<?php echo $server_url_complete; ?>/js/jquery-ui.min.js"></script>
<script src="https://<?php echo $server_url_complete; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
        $("#ec_data_devolucao").datepicker();
        $("#ec_data_devolucao_fim").datepicker();
        
        <?php
        if(isset($_POST["edb_cpf_cnpj"]) && strlen($_POST["edb_cpf_cnpj"]) == 18 ) {
        ?>
        $("#edb_cpf_cnpj").mask("99.999.999/9999-99");
        <?php 
        } //end if(isset($_POST["edb_cpf_cnpj"]) && strlen($_POST["edb_cpf_cnpj"]) == 18 ) 
        else {
        ?>
        $("#edb_cpf_cnpj").mask("999.999.999-999");
        <?php
        }
        ?>
        $("#ec_valor").mask("#.###.##0,00", {reverse: true});

        (function($){
            $.fn.setCursorToTextEnd = function() {
                var $initialVal = this.val();
                this.val($initialVal);
            };
        })(jQuery);
        
        $("#edb_cpf_cnpj").keyup(function(){
            if($("#edb_cpf_cnpj").val().length > 14) {
                $("#edb_cpf_cnpj").unmask().mask("99.999.999/9999-99");
                $("#edb_cpf_cnpj").setCursorToTextEnd();
            }
            /*
            else {
                $("#edb_cpf_cnpj").unmask().mask("999.999.999-999");
                $("#edb_cpf_cnpj").setCursorToTextEnd();
            }
            */
        });
        
        $(".estornoChargebackOpt").click(function(){
            window.location = "estorno_chargeback.php?acao=edita&id="+$(this).attr("id");
        });
                
        $("#limpar").click(function(){
            $("#ec_data_devolucao").val("");
            $("#ec_data_devolucao_fim").val("");
            $("#ec_tipo").val("");
            $("#opr_codigo").val("");
            $("#ec_tipo_usuario").val("");
            $("#cec_id").val("");
            $("#ec_pin_bloqueado").val("");
            $("#ug_id").val("");
            $("#edb_cpf_cnpj").val("");
            $("#edb_titular").val("");
            $("#vg_id").val("");
            $("#ec_cod_autorizacao").val("");
            $("#ec_valor").val("");
            $("#ec_forma_devolucao").val("");
            $("#edb_cpf_cnpj").mask("999.999.999-999");
        });
        
        $("#buscar").click(function(){
            var erro = [];
            if($("#ec_data_devolucao").val().length < 10){
                erro.push($("#ec_data_devolucao").attr("label"));
                $("label[for='ec_data_devolucao']").css("color","red");
            }   
            
            if($("#ec_data_devolucao_fim").val().length < 10) {
                erro.push($("#ec_data_devolucao_fim").attr("label"));
                $("label[for='ec_data_devolucao_fim']").css("color","red");
            }
            
            if(erro.length > 0)
            {
                var msgErro = "É necessário preencher um intervalo de Datas";
                alert(msgErro);
            }
            else 
               $("#"+$(this).get(0).form.id).submit();

       });
    });
</script>
<div><div>