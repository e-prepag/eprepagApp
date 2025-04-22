<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."banco/bexs/config.inc.bexs.php";

set_time_limit(3600);

define("PATH_BEXS", $raiz_do_projeto."arquivos_gerados/bexs_arquivos_operacoes/");

?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<link rel="stylesheet" type="text/css" href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" />
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <form id="buscaRemessa" name="buscaRemessa" method="post" action="relatorio_envio_remessas_bexs.php">
        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
            <div class="col-md-2 col-sm-12 col-xs-12 text-right">Data inicial:</div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <input type="text" value="<?php if(isset($_POST["data_inicial"]))  echo $_POST["data_inicial"]; else echo date('d/m/Y'); ?>" id="data_inicial" name="data_inicial" char="10" maxlength="10" class="form form-control data w150">
            </div>
            <div class="col-md-2 col-sm-12 col-xs-12 text-right">Data final:</div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <input type="text" value="<?php if(isset($_POST["data_final"])) echo $_POST["data_final"];  else echo date('d/m/Y');?>" id="data_final" name="data_final" char="10" maxlength="10" class="form-control data w150">
            </div>

            <div class="col-md-2 col-sm-12 col-xs-12 pull-right">
                <button type="submit" name="BtnSearch" id="BtnSearch" value="Consultar" class="btn pull-right btn-success">Consultar</button>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12 text-right"><br></div>
        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
        <div class="col-md-2 col-sm-12 col-xs-12 text-right">Publisher:</div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <select name="opr_facilitadora" id="opr_facilitadora" >
                    <option value="">Selecione um Publisher</option>
              <?php
                $sql_opr = "SELECT opr_codigo, opr_nome, opr_razao, opr_facilitadora FROM operadoras WHERE opr_internacional_alicota > 0 AND opr_facilitadora > 0";
                $rs_opr = SQLexecuteQuery($sql_opr);
                
                  while($rs_opr_row = pg_fetch_array($rs_opr)){ 
              ?>
                    <option value="<?php echo $rs_opr_row['opr_facilitadora'] ?>" <?php if(isset($_POST['opr_facilitadora']) && $rs_opr_row['opr_facilitadora'] == $_POST['opr_facilitadora']) echo "selected" ?>><?php echo $rs_opr_row['opr_nome']." (".$rs_opr_row['opr_codigo'].")" ?></option>
              <?php 
                  } //end while($rs_opr_row = pg_fetch_array($rs_opr))
              ?>
              </select>
            </div>
            <div class="col-md-2 col-sm-12 col-xs-12 text-right">Status:</div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <select name="status" id="status" style="width: 440px">
                    <option value="">Selecione um Status</option>
<?php
                    foreach($GLOBALS['ARRAY_STATUS_REMESSA'] as $ind => $s){
?>
                        <option value="<?php echo $ind ?>" <?php if(isset($_POST['status']) && $ind == $_POST['status']) echo "selected" ?>><?php echo $s ?></option>;
<?php                        
                    } //end foreach($GLOBALS['ARRAY_STATUS_REMESSA'] as $ind => $s)
?>
              </select>
            </div>
        </div>
    </form>
</div>
</div>   <!--fecha a div de class="txt-azul-claro col-md-12 bg-branco p-bottom40"> -->
<div class="row espacamento text-center bg-branco">
    <div class="msg_box col-md-12 txt-preto top20"></div>
</div>
</div>   <!--fecha a div de class="container"> -->

<div id="modal-info" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title text-left txt-cinza" id="modal-title">Cancelamento de Remessa</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success text-left" id="tipo-modal" role="alert"> 
                  <h5><span id="error-text"><?php echo "O status da remessa selecionada foi atualizado para CANCELADA!" ?></span></h5>
              </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="submita();" >Fechar</button>
            </div>
        </div>
    </div>
</div> 

<?php
    
if(isset($BtnSearch) && $BtnSearch) {
    
?>
    <div class="bg_branco" id="tabela_bexs">
<?php
    //Montando SQL para a Busca das Remesas
    $sql = "
            SELECT id_arquivo, to_char(data_operacao,'DD/MM/YYYY') as data_operacao, to_char(data_moeda,'DD/MM/YYYY') as data_moeda, 
            to_char(data_moeda_nacional,'DD/MM/YYYY') as data_moeda_nacional,
            to_char(data_liquidacao,'DD/MM/YYYY') as data_liquidacao, valor_moeda_estrangeira, valor_moeda_nacional, status, 
            to_char(data_ini,'DD/MM/YYYY') as data_ini, to_char(data_fim,'DD/MM/YYYY') as data_fim, perfil_op, merchant_id_bexs, opr_codigo, opr_nome
            FROM remessa_bexs
            INNER JOIN operadoras op ON op.opr_facilitadora = cast(perfil_op as smallint)
            WHERE data_operacao >= '".formata_data($_POST["data_inicial"],1)." 00:00:00' 
                  AND data_operacao <= '".formata_data($_POST["data_final"],1)." 00:00:00' 
                  AND cast(SUBSTRING(id_arquivo FROM 1 FOR (POSITION ('_xml' IN id_arquivo) -1)) as int) = op.opr_codigo ";

    if(verifica($_POST['opr_facilitadora']) && !verifica($_POST['status']) ){
        $sql .= "AND perfil_op = '".$_POST["opr_facilitadora"]."' ";
    } elseif(verifica($_POST['status']) && !verifica($_POST['opr_facilitadora'])){
        $sql .= "AND status = '".$_POST["status"]."' ";
    } elseif(verifica($_POST['status']) && verifica($_POST['opr_facilitadora'])){
        $sql .= "AND perfil_op = '".$_POST["opr_facilitadora"]."' AND status = '".$_POST["status"]."'";
    }
    $sql .= " ORDER by data_operacao DESC, data_atualizacao DESC;";

    $rs = SQLexecuteQuery($sql);
    if($rs) {
            if(pg_num_rows($rs)>0) {
                    $total_geral_real = 0;
                    $total_geral_dolar = 0;
                    $total_registros = 0;
?>
                            <table class='table table-bordered txt-preto text-center fontsize-p' >
                                <tr class='text-center'>
                                    <td><strong>Publisher</strong></td>
                                    <td><strong>Data Operação</strong></td>
                                    <td><strong>Período Considerado</strong></td>
                                    <td><strong>Data Moeda Estrangeira</strong></td>
                                    <td><strong>Data Moeda Nacional</strong></td>
                                    <td><strong>Data Liquidação</strong></td>
                                    <td><strong>Valor Moeda Estrangeira</strong></td>
                                    <td><strong>Valor Moeda Nacional</strong></td>
                                    <td><strong>Arquivo de Operações</strong></td>
                                    <td><strong>Status</strong></td>
                                    <td><strong></strong></td>
                                    <td><strong>Reenvio</strong></td>
                                </tr>
<?php
                    $i = 0;
                    while ($rsRow = pg_fetch_array($rs)) {

                        ($rsRow['status'] == '4') ?$total_geral_real+= $rsRow['valor_moeda_nacional'] : $total_geral_real+=0;

                        $total_registros++;
                        if($i%2 == 0){
                            $cor = $query_cor1;
                        } else{
                            $cor = $query_cor2;
                        }
                        $i++;


                        $href = "dld_bexs.php?f=".PATH_BEXS.$rsRow['id_arquivo'].".zip"."&fc=".$rsRow['id_arquivo'].".zip";

                        $verifica_existe_arq = file_exists(PATH_BEXS.$rsRow['id_arquivo'].".zip") ? "<a href='$href' target='_blank'>".$rsRow['id_arquivo'].".zip"."</a>" : $rsRow['id_arquivo'].".zip"."<br><i>(Arquivo não criado)</i>";
                        
                        $id_arq = $rsRow['id_arquivo'];
                        
                        if($rsRow['status'] == '4'){
                            $bg_cor = '#98FB98';
                            $class = "txt-verde-escuro";
                        }
                        elseif($rsRow['status'] == '10'){
                            $bg_cor = '#ff9090';
                            $class = "txt-vermelho-escuro";
                        } else{
                            $bg_cor = $cor;
                            $class = "";
                        }
                        ?>
                            <tr bgcolor='<?php echo $bg_cor; ?>' onmouseover=bgColor='#CFDAD7' onmouseout=bgColor='<?php echo $bg_cor; ?>' class='trListagem fontsize-p <?php echo $class; ?>'>
                                <td class='text-center'><?php echo $rsRow['opr_nome']; ?></td>
                                <td class='text-center'><?php echo $rsRow['data_operacao']; ?></td>
                                <td class='text-center'><?php echo $rsRow['data_ini']." a ".$rsRow['data_fim']; ?></td>    
                                <td class='text-center'><?php echo $rsRow['data_moeda']; ?></td>
                                <td class='text-center'><?php echo $rsRow['data_moeda_nacional']; ?></td>
                                <td class='text-center'><?php echo $rsRow['data_liquidacao']; ?></td>
                                <td class='text-center'><?php echo number_format($rsRow['valor_moeda_estrangeira'], 2, ",", "."); ?></td>
                                <td class='text-center'><?php echo number_format($rsRow['valor_moeda_nacional'], 2, ",", "."); ?></td>
                                <td class='text-center'><?php echo $verifica_existe_arq; ?></td>
                                <td class='text-center'><?php echo retorna_status_descricao($rsRow['status'], $GLOBALS['ARRAY_STATUS_REMESSA']); ?></td>
<?php
                        if($rsRow['status'] == $GLOBALS['ARRAY_STATUS']['SUCESSO_PROCESSAMENTO']) $cancel = "CONCLUÍDA"; elseif($rsRow['status'] == $GLOBALS['ARRAY_STATUS']['CANCELADA']) $cancel = "CANCELADA"; else $cancel = "Cancelar"
?>
                                <td class='text-center'>
<?php
                                    if($cancel == 'CONCLUÍDA' || $cancel == 'CANCELADA'){
                                        echo $cancel;
                                    } else{
?>
                                        <a href="javascript:cancelaBexs('<?php echo $id_arq; ?>');" ><?php echo $cancel; ?></a>  
<?php                                        
                                    }
?>
                                </td>
                                <td class='text-center'>
<?php
                                    if($rsRow['status'] == $GLOBALS['ARRAY_STATUS']['SUCESSO_WS'] ||
                                       $rsRow['status'] == $GLOBALS['ARRAY_STATUS']['ERRO_CAMPO_OBRIGATORIO'] ||
                                       $rsRow['status'] == $GLOBALS['ARRAY_STATUS']['ERRO_SFTP'] ||
                                       $rsRow['status'] == $GLOBALS['ARRAY_STATUS']['ERRO_PROCESSAMENTO']
                                    ){
                                        $params = "data_me=".formata_data($rsRow['data_moeda'],1)."&data_mn=".formata_data($rsRow['data_moeda_nacional'],1)."&data_lq=".formata_data($rsRow['data_liquidacao'],1)."&data_ini=".formata_data($rsRow['data_ini'],1)."&data_fim=".formata_data($rsRow['data_fim'],1)."&valor_moeda_nacional=".number_format($rsRow['valor_moeda_nacional'], 2, ".", "")."&perfil_op=".$rsRow['perfil_op']."&data_op=".formata_data($rsRow['data_operacao'],1)."&dd_operadora=".$rsRow['opr_codigo']."&merchant_id_bexs=".$rsRow['merchant_id_bexs']."&nome_merchant=".$rsRow['opr_nome'];
?>
                                    <button class="btn btn-info btn-sm btn-reenvio-arq" onclick="javascript:reenvio_arquivo_operacoes('<?php echo $params; ?>', '<?php echo $rsRow['id_arquivo']; ?>');">Reenviar Arquivo</button>
<?php
                                    }
?>                                        
                                </td>
                            </tr>
<?php
                    }//end while
?>
                                <tr class='negrito'>
                                    <td class='text-center' colspan='2'>Total Registros:</td> 
                                    <td class='text-left'><?php echo number_format($total_registros, 0, ",", "."); ?></td>
                                    <td class='text-right' colspan='3'>Valor Total Confirmado R$:</td>
                                    <td class='text-right'><?php echo number_format($total_geral_real, 2, ",", "."); ?></td>
                                </tr>
                            </table>
    </div>
<?php
            }//end if(pg_num_rows($rs)>0)
            else {
?>
                <div class="row espacamento">
                    <div class="col-md-3 col-md-offset-4 col-sm-12 col-xs-12 alert alert-danger">Nenhum registro encontrado no período.</div>
                </div>
<?php
            }//end else do if(pg_num_rows($rs)>0)
    }//end if($rs) 
    else {
?>          
                <div class="row espacamento">
                    <div class="col-md-3 col-md-offset-4 col-sm-12 col-xs-12 alert alert-danger">ERRO: Problema na seleção do intervalo das datas.</div>
                </div>
<?php        
    }//end else do if($rs)
} // end if($BtnSearch)

?>
<script>
    jQuery(function(e){

        $.datepicker.regional['pt-BR'] = {
        closeText: 'Fechar',
        prevText: '&#x3c;Anterior',
        nextText: 'Pr&oacute;ximo&#x3e;',
        currentText: 'Hoje',
        monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho',
        'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
        'Jul','Ago','Set','Out','Nov','Dez'],
        dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sabado'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
        dayNamesMin: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
        weekHeader: 'Sm',
        dateFormat: 'dd/mm/yy',
        firstDay: 0,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['pt-BR']);

        $(".data").datepicker({
            dateFormat: "dd/mm/yy",
            maxDate: "dateToday"
        });

   });
</script>
<script>
    
    function reenvio_arquivo_operacoes(params, id_arquivo){
        var confirma = confirm("Você confirma o reenvio do arquivo de operações para a remessa ["+id_arquivo+"]");
        if(confirma){
            $.ajax({
                type: "POST",
                url: "/ajax/bexs/reenvio_arq_operacoes_bexs.php",
                data: params,
                beforeSend: function(){
                    $('.btn-reenvio-arq').attr('disabled', 'disabled');
                    $('.msg_box').addClass('alert alert-info');
                    $(".msg_box").html("<img src='/images/ajax-loader.gif' width='30' height='30' title='Verificando e reenviando arquivo...'></br><span class='txt-azul'>"+id_arquivo+"</span>");
                },
                success: function(resultado){
                    var s = resultado;
                    
                    if(s.indexOf("ERRO") != -1){
                        $('.msg_box').removeClass('alert-info text-center');
                        $('.msg_box').addClass('txt-vermelho text-left alert-danger');
                        $('.msg_box').html(resultado);
                    } else{
                        $('.msg_box').removeClass('alert-info text-center');
                        $('.msg_box').addClass('txt-verde text-left alert-success');
                        $('.msg_box').html(resultado);
                    }
                },
                error: function(jqXHR, exception){
                    var msg_error = '';
                    if (jqXHR.status === 0) {
                        msg_error = ('Not connected.\nPlease verify your network connection.');
                    } else if (jqXHR.status == 404) {
                        msg_error = ('The requested page not found. [404]');
                    } else if (jqXHR.status == 500) {
                        msg_error = ('Internal Server Error [500].');
                    } else if (exception === 'parsererror') {
                        msg_error = ('Requested JSON parse failed.');
                    } else if (exception === 'timeout') {
                        msg_error = ('Time out error.');
                    } else if (exception === 'abort') {
                        msg_error = ('Ajax request aborted.');
                    } else {
                        msg_error = ('Uncaught Error.\n' + jqXHR.responseText);
                    }
                    
                    alert("ERRO: "+msg_error);
                    $(".msg_box").html(msg_error+"<br>Por favor, relate o problema ao setor de T.I.");
                    $('.msg_box').addClass('txt-vermelho alert-danger');
                    
                },
                timeout: 120000
                
            });
        }
    }
    
    function cancelaBexs(param) {
        
        var id_arquivo = param;
        var r = confirm("Você confirma o cancelamento dessa remessa?");
        if (r == true) {
            
            $.ajax({
                type: "POST",
                url: "/ajax/bexs/edita_bexs_cancelado.php",
                data: {id_arquivo: id_arquivo},
                success: function(resultado){
                    
                    var s = resultado;
                        if(s.indexOf("Problema") != -1){
                            $('.msg_box').addClass('txt-vermelho');
                            $('.msg_box').html(resultado);
                        } else{
                            $('#modal-info').modal('show');
                        }
                },
                error: function(){
                    alert("ERRO: Problema no servidor.;");
                }
                 
            });
        }
    }
    
    function submita(){
        $('#BtnSearch').click();
    }
</script>

<?php
function verifica($var){
    return (isset($var)) ? (($var != "")? true : false) : false;
}

function retorna_status_descricao($ind, $status){
    switch ($ind){
        
        case '1':
            return $status['1'];
            break;
        case '2':
            return $status['2'];
            break;
        case '3':
            return $status['3'];
            break;
        case '4':
            return $status['4'];
            break;
        case '5':
            return $status['5'];
            break;
        case '6':
            return $status['6'];
            break;
        case '7':
            return $status['7'];
            break;
        case '8':
            return $status['8'];
            break;
        case '9':
            return $status['9'];
            break;
        case '10':
            return $status['10'];
            break;
        default :
            return '0';
    }
}

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>