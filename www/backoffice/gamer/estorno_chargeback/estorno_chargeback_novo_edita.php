<?php
require_once "/www/includes/bourls.php";
if($_GET["acao"] == "novo") $method = "novoEstornoChargeback";
elseif($_GET["acao"] == "edita") {
    if(isset($_GET["id"]) && $_GET["id"] != ""){
        $filtro["ec_id"] =  "ec.ec_id = ".$_GET["id"];
        $EstornoChargeBack = $objEstornoChargeBack->pegaEstornoChargeBack($filtro);
        $EstornoChargeBack[0]["ec_id"] = $_GET["id"];
    }else echo "<script>alert('Estorno/ChargeBack para edição, não especificada'); location.href = '/gamer/estorno_chargeback/estorno_chargeback.php';</script>";

    $method = "editaEstornoChargeback";
}
else header("Location: /gamer/estorno_chargeback/estorno_chargeback.php");
?>

<style>
    .ocultar{
        display: none;
    }
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo ((isset($filtro) && count($filtro)>0)?"Editar":"Novo"); ?> Estorno e ChargeBack</a></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <p><strong><?php echo ((isset($filtro) && count($filtro)>0)?"Edição":"Cadastro"); ?> de Estorno e Chargeback</strong></p>
</div>
<div class="txt-preto fontsize-pp">
    <form id="formEstornoChargeback" enctype="multipart/form-data" name="formEstornoChargeback" method="post" action="estorno_chargeback.php">
        <div class="col-md-12 bottom20">
            <span class="col-md-4 top20">
                <label for="ec_tipo" class="w180 left">* Categoria Devolução:</label>
                <select class="form-control input-sm  w-auto" name="ec_tipo" id="ec_tipo" char="1" label="Categoria Devolução">
                    <option value="">Selecione</option>
                            <?php
                            foreach ($vetorTipo as $key => $value) {
                            ?>
                            <option value="<?php echo $key; ?>" <?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_tipo"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                            <?php
                            }
                            ?>                    
                </select>
            </span>
        </div>
        <div class="col-md-4">
            <h4>Dados do Usuário</h4>
            <span class="col-md-12">
                <label for="ec_tipo_usuario" class="w180 left">* Tipo de Cliente:</label>
                <select class="form-control input-sm  w-auto" name="ec_tipo_usuario" id="ec_tipo_usuario" char="1" label="Tipo de Cliente">
                    <option value="">Selecione</option>
                            <?php
                            foreach ($vetorTipoUsuario as $key => $value) {
                            ?>
                            <option value="<?php echo $key; ?>" <?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_tipo_usuario"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                            <?php
                            }
                            ?>                    
                </select>
            </span>
            <span class="col-md-12">
                <label for="ug_id" class="w180 left">* ID do Usuário:</label>
                <input type="text" class="form-control input-sm w170" name="ug_id" char="2" id="ug_id" label="ID do Usuário" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ug_id"]; ?>" >
            </span>
            <span class="col-md-12">
                <label for="ec_nome" class="w180 left">* Nome Solicitante:</label>
                <input type="text" class="form-control input-sm w170" name="ec_nome" char="4" id="ec_nome" label="Nome Solicitante" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_nome"]; ?>" >
            </span>
            <span class="col-md-12">
                <label for="ec_email" class="w180 left">Email:</label> 
                <input type="text" class="form-control input-sm w170" name="ec_email" id="ec_email" label="Email" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_email"]; ?>" >
            </span>
            <span class="col-md-12">
                <label for="ec_data_nascimento" class="w180 left">Data de Nascimento:</label> 
                <input type="text" value="<?php if(isset($EstornoChargeBack)) echo Util::getData($EstornoChargeBack[0]["ec_data_nascimento"]); ?>" id="ec_data_nascimento" name="ec_data_nascimento" class="form-control input-sm w80">
            </span>
            <span class="col-md-12">
                <label for="ec_cpf" class="w180 left">CPF:</label> 
                <input type="text" class="form-control input-sm w170" name="ec_cpf" id="ec_cpf" label="CPF " value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_cpf"]; ?>" >
            </span>
            <span class="col-md-12">
                <label for="ec_telefone" class="w180 left">Telefone:</label> 
                <input type="text" class="form-control input-sm w170" name="ec_telefone" id="ec_telefone" label="Telefone" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_telefone"]; ?>" >
            </span>
        </div>
        <div class="col-md-4">
            <h4>Dados do Pedido</h4>
            <span class="col-md-12">
                <label for="vg_id" class="w180 left">* Pedido:</label>
                <input type="text" class="form-control input-sm w170" name="vg_id" char="4" id="vg_id" label="Pedido" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["vg_id"]; ?>" >
            </span>
            <span class="col-md-12">
                <label for="ec_data_devolucao" class="w180 left">* Data Devolução:</label> 
                <input type="text" value="<?php if(isset($EstornoChargeBack)) echo Util::getData($EstornoChargeBack[0]["ec_data_devolucao"]); ?>" id="ec_data_devolucao" name="ec_data_devolucao" char="10" class="form-control input-sm w80">
            </span>
            <span class="col-md-12">
                <label for="cec_id" class="w180 left">* Categoria Motivo:</label>
            <?php
            if(!empty($CategoriaEstornoChargeback)){
            ?>
                <select class="form-control input-sm" name="cec_id" id="cec_id" char="1" label="Categoria Motivo">
                    <option value="">Selecione</option>
                            <?php
                            foreach ($CategoriaEstornoChargeback as $CategoriaEstornoChargebackRow){
                            ?>
                            <option value="<?php echo $CategoriaEstornoChargebackRow->getId(); ?>" <?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["cec_id"] == $CategoriaEstornoChargebackRow->getId()) echo "selected"; ?>><?php echo $CategoriaEstornoChargebackRow->getDescricao();?></option>
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
            </span>
            <span class="col-md-12">
                <label for="ec_pin_bloqueado" class="w180 left">* PINs Bloqueados:</label>
                <select class="form-control input-sm  w-auto" name="ec_pin_bloqueado" id="ec_pin_bloqueado" char="1" label="PINs Bloqueados pelo Publisher">
                    <option value="">Todas</option>
                            <?php
                            foreach ($vetorPINsBloqueados as $key => $value) {
                            ?>
                            <option value="<?php echo $key; ?>" <?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_pin_bloqueado"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                            <?php
                            }
                            ?>                    
                </select>
            </span>
            <span class="col-md-12">
                <label for="opr_codigo" class="w180 left">* Publisher:</label>
                <select class="form-control input-sm w-auto" name="opr_codigo" id="opr_codigo" char="1" label="Publisher">
                    <option value="">Todas</option>
                            <?php
                            foreach ($vetorPublisher as $key => $value) {
                            ?>
                            <option value="<?php echo $key; ?>" <?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["opr_codigo"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                            <?php
                            }
                            ?>                    
                </select>
            </span>
            <span class="col-md-12">
                <label for="ec_valor" class="w180 left">* Valor R$:</label>
                <input type="text" class="form-control input-sm w170" name="ec_valor" char="4" id="ec_valor" label="Valor R$" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_valor"]; ?>" >
            </span>
        </div>
        <div class="col-md-4">
            <h4>Dados Adicionais</h4>
            <span class="col-md-12">
                <label for="ec_data_pedido" class="w180 left">Data do Pedido:</label> 
                <input type="text" value="<?php if(isset($EstornoChargeBack)) echo Util::getData($EstornoChargeBack[0]["ec_data_pedido"]); ?>" id="ec_data_pedido" name="ec_data_pedido" class="form-control input-sm w80">
            </span>
            <span class="col-md-12">
                <label for="ec_pin" class="w180 left">PIN:</label> 
                <input type="text" class="form-control input-sm w170" name="ec_pin" id="ec_pin" label="PIN" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_pin"]; ?>" >
            </span>
            <span class="col-md-12">
                <label for="ec_tid" class="w180 left">TID Cielo:</label> 
                <input type="text" class="form-control input-sm w170" name="ec_tid" id="ec_tid" label="TID Cielo" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_tid"]; ?>" >
            </span>
            <span class="col-md-12">
                <label for="ec_cod_boleto" class="w180 left">Código Boleto:</label> 
                <input type="text" class="form-control input-sm w170" name="ec_cod_boleto" id="ec_cod_boleto" label="Código Boleto" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_cod_boleto"]; ?>" >
            </span>
            <span class="col-md-12">
                <label for="ec_cod_deposito" class="w180 left">Código Depósito:</label> 
                <input type="text" class="form-control input-sm w170" name="ec_cod_deposito" id="ec_cod_deposito" label="Código Depósito" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_cod_deposito"]; ?>" >
            </span>
            <span class="col-md-12">
                <label for="ec_ip_pedido" class="w180 left">IP do Pedido:</label> 
                <input type="text" class="form-control input-sm w170" name="ec_ip_pedido" id="ec_ip_pedido" label="IP do Pedido" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_ip_pedido"]; ?>" >
            </span>
            <span class="col-md-12">
                <label for="ec_cod_autorizacao" class="w180 left">Cód. Autorização:</label> 
                <input type="text" class="form-control input-sm w170" name="ec_cod_autorizacao" id="ec_cod_autorizacao" label="Cód. Autorização" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_cod_autorizacao"]; ?>" >
            </span>
        </div>
        <div class="field<?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_tipo"] == 1 || !isset($EstornoChargeBack)) echo " ocultar"; ?>" name="dados_bancarios" id="dados_bancarios">
            <div class="col-md-12 top10">
                <div class="col-md-4">
                    <label for="ec_forma_devolucao" class=" left">* Forma de Devolução:</label>
                    <select class="form-control input-sm" name="ec_forma_devolucao" id="ec_forma_devolucao" char="<?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_tipo"] == 2) echo "1"; else echo "0";?>" label="Forma de Devolução">
                        <option value="">Selecione</option>
                                <?php
                                foreach ($vetorFormaDevolucao as $key => $value) {
                                ?>
                                <option value="<?php echo $key; ?>" <?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_forma_devolucao"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                                <?php
                                }
                                ?>                    
                    </select>
                </div>
            </div>
            <div class="<?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_forma_devolucao"] == 1 || !isset($EstornoChargeBack)) echo " ocultar"; ?>" name="dados_bancarios_conta" id="dados_bancarios_conta">
                <div class="col-md-12 top20">
                    <h4>Dados Bancários:</h4>
                </div>
                <div class="col-md-4">
                    <span class="col-md-12">
                        <label for="edb_titular" class="w150 left">* Titular:</label>
                        <input type="text" class="form-control input-sm w170" name="edb_titular" char="<?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_forma_devolucao"] == 2) echo "10"; else echo "0";?>" id="edb_titular" label="Titular " value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["edb_titular"]; ?>" >
                    </span>
                    <span class="col-md-12 top10">
                        <label class="w150 left">* Tipo Documento:</label>
                        <input type="radio" class="cpfcnpj" name="cpfcnpj" id="cpfcnpj1" value="cpf" <?php if(!isset($cpfcnpj) || $cpfcnpj == 'cpf' || (isset($EstornoChargeBack) && strlen($EstornoChargeBack[0]["edb_cpf_cnpj"]) == 14)) echo "checked";?>>CPF
                        <input type="radio" class="cpfcnpj" name="cpfcnpj" id="cpfcnpj2" value="cnpj" <?php if((isset($cpfcnpj) && $cpfcnpj == 'cnpj') || (isset($EstornoChargeBack) && strlen($EstornoChargeBack[0]["edb_cpf_cnpj"]) == 18)) echo "checked";?>>CNPJ
                    </span>
                    <span class="col-md-12 top10">
                        <label for="edb_cpf_cnpj" class="w150 left">* <span id="labelDoc" name="labelDoc"><?php if(!isset($cpfcnpj) || $cpfcnpj == 'cpf' || (isset($EstornoChargeBack) && strlen($EstornoChargeBack[0]["edb_cpf_cnpj"]) == 14)) echo "CPF"; else echo "CNPJ";?></span> Titular:</label>
                        <input type="text" class="form-control input-sm w170" name="edb_cpf_cnpj" char="<?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_forma_devolucao"] == 2) { if(isset($EstornoChargeBack) && strlen($EstornoChargeBack[0]["edb_cpf_cnpj"]) == 18)  echo "18"; else echo "14"; } else echo "0";?>" id="edb_cpf_cnpj" label="CPF do Titular " value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["edb_cpf_cnpj"]; ?>" >
                    </span>
                </div>
                <div class="col-md-4">
                    <span class="col-md-12">
                        <label for="edb_banco" class="w150 left">* Banco:</label>
                        <input type="text" class="form-control input-sm w170" name="edb_banco" char="<?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_forma_devolucao"] == 2) echo "4"; else echo "0";?>" id="edb_banco" label="Banco " value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["edb_banco"]; ?>" >
                    </span>
                    <span class="col-md-12">
                        <label for="edb_agencia" class="w150 left">* Agência:</label>
                        <input type="text" class="form-control input-sm w170" name="edb_agencia" char="<?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_forma_devolucao"] == 2) echo "4"; else echo "0";?>" id="edb_agencia" label="Agência " value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["edb_agencia"]; ?>" >
                    </span>
                    <span class="col-md-12">
                        <label for="edb_conta" class="w150 left">* Conta:</label>
                        <input type="text" class="form-control input-sm w170" name="edb_conta" char="<?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_forma_devolucao"] == 2) echo "4"; else echo "0";?>" id="edb_conta" label="Conta " value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["edb_conta"]; ?>" >
                    </span>
                    <span class="col-md-12">
                        <label for="edb_tipo_conta" class="w150 left">* Tipo de Conta:</label>
                        <select class="form-control input-sm w170" name="edb_tipo_conta" id="edb_tipo_conta" char="<?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["ec_forma_devolucao"] == 2) echo "1"; else echo "0";?>" label="Tipo de Conta">
                            <option value="">Selecione</option>
                                    <?php
                                    foreach ($vetorTpoContas as $key => $value) {
                                    ?>
                                    <option value="<?php echo $key; ?>" <?php if(isset($EstornoChargeBack) && $EstornoChargeBack[0]["edb_tipo_conta"] == $key) echo "selected"; ?>><?php echo $value;?></option>
                                    <?php
                                    }
                                    ?>                    
                        </select>
                    </span>
                </div>
            </div>
        </div>     
        <div class="col-md-12 top20 left botaoSalvar">
            <div class="col-md-12">
                <input type="hidden" name="ec_id" value="<?php if(isset($EstornoChargeBack)) echo $EstornoChargeBack[0]["ec_id"]; ?>">
                <input type="hidden" name="<?php echo $method; ?>" value="1">
                <input type="button" value="Salvar" id="salvar" class="btn btn-sm btn-info">
                * Campos de preenchimento obrigatórios
            </div>
        </div>
    </form>
</div>
<script type="text/javascript" src="https://<?php echo $server_url_complete; ?>/js/jquery.mask.min.js"></script>
<link href="https://<?php echo $server_url_complete; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="https://<?php echo $server_url_complete; ?>/js/jquery-ui.min.js"></script>
<script src="https://<?php echo $server_url_complete; ?>/js/global.js"></script>
<script>
    $(function(){
        $("#ec_data_nascimento").datepicker();
        $("#ec_data_devolucao").datepicker();
        $("#ec_data_pedido").datepicker({
            "maxDate": "dateToday"
        });
        <?php 
        if(isset($EstornoChargeBack) && strlen($EstornoChargeBack[0]["edb_cpf_cnpj"]) == 14) {
        ?>
        $("#edb_cpf_cnpj").mask("999.999.999-99");
        <?php
        } //end if(!isset($cpfcnpj) || $cpfcnpj == 'cpf' || (isset($EstornoChargeBack) && strlen($EstornoChargeBack[0]["edb_cpf_cnpj"]) == 14))
        elseif(isset($EstornoChargeBack) && strlen($EstornoChargeBack[0]["edb_cpf_cnpj"]) == 18) {
        ?>
        $("#edb_cpf_cnpj").mask("99.999.999/9999-99");
        <?php
        }//end elseif((isset($cpfcnpj) && $cpfcnpj == 'cnpj') || (isset($EstornoChargeBack) && strlen($EstornoChargeBack[0]["edb_cpf_cnpj"]) == 18)) 
        else {
        ?>
        $("#edb_cpf_cnpj").mask("999.999.999-99");
        <?php
        }
        ?>
        $("#ec_cpf").mask("999.999.999-99");
        $("#ec_telefone").mask("(99) 99999-9999");
        $("#ec_valor").mask("#.###.##0,00", {reverse: true});

        $(".cpfcnpj").change(function () {
            
                $("#edb_cpf_cnpj").unmask();
                if ($('input[name=cpfcnpj]:checked', '#formEstornoChargeback').val() == "cpf") {
                    $('#labelDoc').html("CPF");
                    $("#edb_cpf_cnpj").mask("999.999.999-99");
                }
                else if ($('input[name=cpfcnpj]:checked', '#formEstornoChargeback').val() == "cnpj") {
                    $('#labelDoc').html("CNPJ");
                    $("#edb_cpf_cnpj").mask("99.999.999/9999-99");
                }
                else  {
                    $('#labelDoc').html("CPF");
                    $("#edb_cpf_cnpj").mask("999.999.999-99");
                }
        });
        
       $("#ec_tipo").change(function(){
            if($(this).val() == 2){
                $('#ec_forma_devolucao').attr("char", "1");
                $("#dados_bancarios").fadeIn("slow");  //pode usar o fast tbm. ou ao invés do fadeIn, usar o .show();
            }
            if($(this).val() == 1){
                $('#ec_forma_devolucao').attr("char", "0");
                $('#edb_titular').attr("char", "0");
                $('#edb_cpf_cnpj').attr("char", "0");
                $('#edb_banco').attr("char", "0");
                $('#edb_agencia').attr("char", "0");
                $('#edb_conta').attr("char", "0");
                $('#edb_tipo_conta').attr("char", "0");
                $('#ec_forma_devolucao').val("");
                $("#dados_bancarios").fadeOut("fast");  //ao invés do fadeOut, usar o .hiden();
            }
            if($(this).val() == ""){
                $('#ec_forma_devolucao').attr("char", "0");
                $('#edb_titular').attr("char", "0");
                $('#edb_cpf_cnpj').attr("char", "0");
                $('#edb_banco').attr("char", "0");
                $('#edb_agencia').attr("char", "0");
                $('#edb_conta').attr("char", "0");
                $('#edb_tipo_conta').attr("char", "0");
                $('#ec_forma_devolucao').val("");
                $("#dados_bancarios").fadeOut("fast");  //ao invés do fadeOut, usar o .hiden();
            }
        });
        
       $("#ec_forma_devolucao").change(function(){
            if($(this).val() == 2){
                $('#edb_titular').attr("char", "10");
                $('#edb_cpf_cnpj').attr("char", "14");
                $('#edb_banco').attr("char", "4");
                $('#edb_agencia').attr("char", "4");
                $('#edb_conta').attr("char", "4");
                $('#edb_tipo_conta').attr("char", "1");
                $("#dados_bancarios_conta").fadeIn("slow");  //pode usar o fast tbm. ou ao invés do fadeIn, usar o .show();
            }
            if($(this).val() == 1){
                $('#edb_titular').attr("char", "0");
                $('#edb_cpf_cnpj').attr("char", "0");
                $('#edb_banco').attr("char", "0");
                $('#edb_agencia').attr("char", "0");
                $('#edb_conta').attr("char", "0");
                $('#edb_tipo_conta').attr("char", "0");
                $("#dados_bancarios_conta").fadeOut("fast");  //ao invés do fadeOut, usar o .hiden();
            }
            if($(this).val() == ""){
                $('#edb_titular').attr("char", "0");
                $('#edb_cpf_cnpj').attr("char", "0");
                $('#edb_banco').attr("char", "0");
                $('#edb_agencia').attr("char", "0");
                $('#edb_conta').attr("char", "0");
                $('#edb_tipo_conta').attr("char", "0");
                $("#dados_bancarios_conta").fadeOut("fast");  //ao invés do fadeOut, usar o .hiden();
            }
        });
        
       $("#salvar").click(function(){
            var erro = [];
            
            $(".form-control").each(function(){
                 if($(this).val().length < $(this).attr("char")){
                     erro.push($(this).attr("label"));
                     $("label[for='"+$(this).attr("id")+"']").css("color","red");
                 }else{
                     $("label[for='"+$(this).attr("id")+"']").css("color","#337ab7");
                 }
            });
            
            if(erro.length > 0)
            {
                var msgErro = erro.join()+" não estão preenchidos, ou estão preenchidos de forma incorreta.";
                alert(msgErro);
            }
            else
            {
                //console.log($(this).get(0).form.id);
               $("#formEstornoChargeback").submit();
               
            }
       });
    });
</script>