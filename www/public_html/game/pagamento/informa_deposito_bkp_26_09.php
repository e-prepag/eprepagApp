<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
$controller = new HeaderController;
$controller->setHeader();

require_once DIR_CLASS . "gamer/classIntegracao.php";
require_once DIR_INCS . "inc_register_globals.php";
require_once DIR_INCS . "gamer/functions.php";
//Recupera usuario
if(isset($_SESSION['usuarioGames_ser']) && !is_null($_SESSION['usuarioGames_ser'])){
        $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
        $usuarioId = $usuarioGames->getId();
}

require_once DIR_INCS . "gamer/venda_e_modelos_logica.php";

require_once DIR_INCS . "gamer/pagto_informa_dep_doc_transf_verificacoes.php";

//if($msg) $msg = $_POST['msg'];

//Variaveis do Formulario
$pagto_data_data_full = $pagto_data_data ." ".$pagto_data_horas .":". $pagto_data_minutos;

//Limpa arquivos temporarios da venda
$arquivos = buscaArquivosIniciaCom($FOLDER_COMMERCE_UPLOAD_TMP, 'nome', 'asc', "money_comprovante_" . $venda_id . "_");
for($j = 0; $j < count($arquivos); $j++){
        if (is_file($FOLDER_COMMERCE_UPLOAD_TMP . $arquivos[$j])) unlink($FOLDER_COMMERCE_UPLOAD_TMP . $arquivos[$j]);
}

// Obtem o valor total deste pedido
$libera_pagamento = array(
    'Deposito' => true,
);

$pagtoInvalido = false;
pg_result_seek($rs_venda_modelos, 0);
$arr_venda_modelos = pg_fetch_all($rs_venda_modelos);

$produto_idade_minima = "";
foreach($arr_venda_modelos as $modelo){
    if(isset($modelo["vgm_ogp_id"])){
        $sql = "SELECT ogp_idade_minima FROM tb_operadora_games_produto WHERE ogp_id = " . $modelo["vgm_ogp_id"];
        $rs_operadora = SQLexecuteQuery($sql);
        $rs_idade_minima = pg_fetch_all($rs_operadora)[0]["ogp_idade_minima"];
        if($rs_idade_minima > $GLOBALS["IDADE_MINIMA"]){
            $GLOBALS["IDADE_MINIMA"] = $rs_idade_minima;
            $produto_idade_minima = $modelo["vgm_nome_produto"];
        }
    }
}

foreach($arr_venda_modelos as $ind => $rs_venda_modelos_row){
    
    $tipoId['operadora'] = $rs_venda_modelos_row['vgm_opr_codigo'];
    $arrPagtosBloqueados[] = getMeiosPagamentosBloqueados($tipoId, $libera_pagamento);
}

foreach($arrPagtosBloqueados as $ind => $val){
    if(!$arrPagtosBloqueados[$ind]['Deposito']){
        $pagtoInvalido = true;
        break;
    }
}


//Processa pagto
//----------------------------------------------------------------------------------------

if($btSubmit && !$pagtoInvalido){

        require_once DIR_INCS . "gamer/pagto_informa_dep_doc_transf_validacoes.php";

        //Valida arquivo upload - comprovante
        if($msg == ""){
            if(	($pagto_banco == "001" && $pagto_local == "06") ||
                        ($pagto_banco == "237" && $pagto_local == "06") ||
                        ($pagto_banco == "104" && $pagto_local == "06") ){

                        $fileName = $HTTP_POST_FILES['comprovante']['name'];
                        $fileSource = $HTTP_POST_FILES['comprovante']['tmp_name'];
                        $fileDest = $FOLDER_COMMERCE_UPLOAD_TMP . "money_comprovante_" . $venda_id . "_" . $pagto_banco . "_" . $pagto_local . "_" . $fileName;

                        if (($fileSource != 'none') && ($fileSource != '' )) {

                                if(strlen($fileName) > 4) $fileExtensao = strtoupper(substr(strrchr($fileName, '.'), 1));
                                if($fileExtensao != 'JPG' && $fileExtensao != 'GIF' && $fileExtensao != 'PNG'){
                                        $msg .= "Arquivo de comprovante inválido. Deve ser do tipo JPG, GIF ou PNG.\n";

                                } else if (!move_uploaded_file($fileSource, $fileDest)) {
                                        $msg = "Não foi possivel realizar o upload do comprovante, tente novamente.\n";
                                }
                        }

                }
        }

        if($msg == ""){

                //redireciona 
                $strRedirect = "/game/pagamento/pagto_compr_offline.php";
?>
<script>
    $(function(){
        enviarDados("<?php echo $strRedirect; ?>");
    });
</script>
<?php
        }
}

if(!$pagto_data_data) $pagto_data_data = "";
if(!$pagto_data_horas) $pagto_data_horas = "";
if(!$pagto_data_minutos) $pagto_data_minutos = "";

$pagto_data_data_full = $pagto_data_data ." ".$pagto_data_horas .":". $pagto_data_minutos;
?>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script src="/js/global.js"></script>
<script src="/js/valida.js"></script>
<script>
    function enviarDados(url) {
        $("#form1").attr("action",url);
        $("#form1").submit();
    } //end function enviarDados
    
    function verificarCampos(){
        var retorno = true;
        $(".form-control").each(function(i){
            if($(this).val() == ""){
                retorno = false;
            }
        })
        
        if(retorno == false){
                manipulaModal(1,"Por favor, preencher todos os campos obrigatorios","Erro");
        }
        return retorno;
    }
    
    $(function(){
        $("#pagto_data_data").datepicker({
            maxDate: 'dateToday',
            minDate: '-1m'
        });
        
        $(".money").mask("##.###,##", {reverse: true});
        
        $("#enviaForm").click(function(){
            if(verificarCampos()){
                $("#salva-infos-deposito").modal();
            }
        });
    });
</script>
<div class="container txt-azul-claro bg-branco">
    <div class="row bottom20">
        <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
            <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-azul-claro top10">
                <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">informações de depósito</h4></strong>
            </div>
<?php
require_once DIR_INCS . "gamer/venda_e_modelos_view.php";

//Testando a necessidade de solicitação de CPF para Gamer
if($test_opr_need_cpf) {
    $GLOBALS["jquery"] = true;
    cpf_page_gamer();
}//end if($test_opr_need_cpf)

require_once DIR_INCS . "gamer/pagto_compr_usuario_dados.php";

if(!empty($msg)) {
?>
            <script>
                manipulaModal(1,"<?php echo str_replace("\n", "<br>", $msg); ?>","Erro");
            </script>
<?php
}//end if(!empty($msg))

    if($pagtoInvalido){

?>
        <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
            <div class="alert alert-danger top20" id="erro" role="alert">
                <span class="glyphicon t0 glyphicon-exclamation-sign" aria-hidden="true"></span>
                <span class="sr-only">Error:</span>
                Erro: forma de pagamento inválida no momento.
            </div>
        </div>
<?php
    
    }
    else{
?> 
            <form name="form1" id="form1" action="" method="post" ENCTYPE="multipart/form-data">
            <!-- Modal -->
            <div class="modal txt-preto fade" id="salva-infos-deposito" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Por favor, confira os dados atentamente</h4>
                        </div>
                        <div class="modal-body">
                            <div class="clearfix"></div>
                            Para sua segurança, uma vez informados os dados não será possível fazer alteração. Se as informações forem fornecidas incorretamente o seu pedido ficará pendente.
                            <div class="clearfix"></div>
                        </div>
                        <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Voltar</button>
                              <input type="submit" name="btSubmit" id="btSubmit" value="Confirmar" class="btn btn-success">
                        </div>
                    </div>
                </div>
            </div>
                <input type="hidden" name="btChange" value="1">
                <input type='hidden' name='ug_show' id='ug_show' value='<?php echo $_POST['ug_show'];?>'>
                <div class="" style="border-top: 0px;">
                        <?php echo str_replace("\n", "<br>", $msg); ?>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                    <p class="txt-preto top20">
                        Digite os dados corretamente para que o seu pagamento seja identificado e o seu produto liberado
                    </p>
                </div>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 top20">
                    <strong>
                        Informações de pagamento
                    </strong>
                </div>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-preto teste">
                    <div class="col-md-4 top20">Banco onde foi feito o pagamento:</div>
                    <div class="col-md-4 top20">
                        <select name="pagto_banco" class="form-control" OnChange="if(document.form1.pagto_local)document.form1.pagto_local.value='';document.form1.submit();">
                            <option value="" <?php if($pagto_banco == "") echo "selected" ?>>Selecione o Banco</option>
<?php 
                            foreach ($PAGTO_BANCOS as $bancoId => $bancoNome){ 
                                if($bancoId != '104' && $bancoId != '341' && $bancoId != '033'){ 
?>
                                    <option value="<?php echo $bancoId; ?>" <?php if ($pagto_banco == $bancoId) echo "selected";?>><?php echo $bancoNome; ?></option>
<?php	
                                } //end if
                            } //end foreach
?>
                        </select>
                    </div>
                </div>
<?php 
                        if($pagto_banco && !is_null($pagto_banco) && $pagto_banco != "" && is_numeric($pagto_banco)) { 
?>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-preto teste">
                    <div class="col-md-4 top10">Local onde foi feito o pagamento:</div>
                    <div class="col-md-4 top10">
                        <select name="pagto_local" class="form-control" OnChange="document.form1.submit();">
                                <option value="" <?php if($pagto_local == "") echo "selected" ?>>Selecione o Local</option>
<?php 
                            foreach ($PAGTO_LOCAIS[$pagto_banco] as $localId => $localNome){ 
?>
                                <option value="<?php echo $localId; ?>" <?php if ($pagto_local == $localId) echo "selected";?>><?php echo $localNome; ?></option>
<?php 
                            } 
?>
                        </select>
                    </div>
                </div>
<?php 
                            if($pagto_local && !is_null($pagto_local) && $pagto_local != "" && is_numeric($pagto_local)) { 
?>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-preto teste">
                    <div class="col-md-4 top10">Data do pagamento:</div>
                    <div class="col-md-4 top10">
                        <input name="pagto_data_data" type="text" class="form-control" id="pagto_data_data" value="<?php echo $pagto_data_data ?>" size="10" maxlength="10" readonly="readonly">
                    </div>
                </div>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-preto teste">
                    <div class="col-md-4 top10">Hora do pagamento:</div>
                    <div class="col-md-5 top10">
                        <select name="pagto_data_horas" class="form-control pull-left w70p">
                                <option value="" <?php if($pagto_data_horas == "") echo "selected" ?>>HH</option>
<?php
                                for($i=0; $i <= 23; $i++){ 
?>
                                <option value="<?php echo substr("0" . $i, -2); ?>" <?php if ($pagto_data_horas == substr("0" . $i, -2)) echo "selected";?>><?php echo substr("0" . $i, -2); ?></option>
<?php 
                                } 
?>
                        </select><span class="p-3 pull-left"><strong> : </strong></span>
                        <select name="pagto_data_minutos" class="form-control pull-left w70p">
                                <option value="" <?php if($pagto_data_minutos == "") echo "selected" ?>>MM</option>
<?php 
                                for($i=0; $i <= 59; $i++){ 
?>
                                <option value="<?php echo substr("0" . $i, -2); ?>" <?php if ($pagto_data_minutos == substr("0" . $i, -2)) echo "selected";?>><?php echo substr("0" . $i, -2); ?></option>
<?php 
                                } 
?>
                        </select>
                    </div>
                </div>
<?php
                                $pagto_nome_docto_Ar = explode(";", $PAGTO_NOME_DOCTO[$pagto_banco][$pagto_local]);
                                for($i=0; $i<count($pagto_nome_docto_Ar); $i++){
?>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-preto teste">
                    <div class="col-md-4 top10"><?php echo $pagto_nome_docto_Ar[$i]; ?>:</div>
                    <div class="col-md-3 top10">
                        <input name="pagto_num_docto[]" value="<?php echo htmlspecialchars($pagto_num_docto[$i], ENT_QUOTES)?>" type="text" size="20" maxlength="20" class="form-control">
                    </div>
                </div>
<?php 
                                } 
                                /*if( 	($pagto_banco == "001" && $pagto_local == "06") ||
                                            ($pagto_banco == "237" && $pagto_local == "06") ||
                                            ($pagto_banco == "104" && $pagto_local == "06") ){
?>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-preto">
                    <div class="col-md-4 top10">Comprovante:</div>
                    <div class="col-md-3 top10">
                            <label class="btn btn-info btn-file">
                                Selecione o Arquivo <input type="file" style="display: none;" name="comprovante">
                            </label>
                    </div>
                </div>
<?php 
                                } */
?>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-preto">
                    <div class="col-md-4 top10">Valor Pago:</div>
                    <div class="col-md-3 top10">
                        <input name="pagto_valor_pago" value="<?php echo htmlspecialchars($pagto_valor_pago, ENT_QUOTES); ?>" type="text" size="20" maxlength="20" class="form-control money" placeholder="0,00">
                    </div>
                </div>
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-preto">
                    <div class="col-md-4 top10"></div>
                    <div class="col-md-3 top10">
<?php
                    if(empty($strRedirect)) {
?>
                        <a href="javascript:void(0);" id="enviaForm" class="btn btn-success">Enviar</a>
<?php
                    }//end if
?>
                    </div>
                </div>
<?php 
                            }//end if($pagto_local && !is_null($pagto_local) && $pagto_local != "" && is_numeric($pagto_local))
                            
                        }//end if($pagto_banco && !is_null($pagto_banco) && $pagto_banco != "" && is_numeric($pagto_banco))
?>
                </div>
            </form>
<?php
    }
?>
        </div>
    </div>
</div>
</div>
<?php
require_once DIR_WEB . "game/includes/footer.php";
?>
