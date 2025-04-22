<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/CanalDenuncia.php";
require_once "/www/includes/bourls.php";
set_time_limit(3600);

?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>

<link rel="stylesheet" href="<?php echo $server_url_ep ?>/js/fancybox/jquery.fancybox.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $server_url_ep ?>/css/modal.css" type="text/css" />
<script src="<?php echo $server_url_ep ?>/js/fancybox/jquery.fancybox.js"></script>
<script src="<?php echo $server_url_ep ?>/js/modal.js"></script>

<link rel="stylesheet" type="text/css" href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" />
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <h4 class="txt-azul-claro bottom50">Relatório Canal de Denúncias</h4>
    <form id="buscaDenuncia" name="buscaDenuncia" method="post" action="">
        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
            <div class="col-md-2 col-sm-12 col-xs-12 text-right"><b>Data inicial:</b></div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <input type="text" value="<?php if(isset($_POST["data_inicial"]))  echo $_POST["data_inicial"]; else echo date('d/m/Y'); ?>" id="data_inicial" name="data_inicial" char="10" maxlength="10" class="form form-control data w150">
            </div>
            <div class="col-md-2 col-sm-12 col-xs-12 text-right"><b>Data final:</b></div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <input type="text" value="<?php if(isset($_POST["data_final"])) echo $_POST["data_final"];  else echo date('d/m/Y');?>" id="data_final" name="data_final" char="10" maxlength="10" class="form-control data w150">
            </div>

            
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12 text-right"><br></div>
        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
            <div class="col-md-2 col-sm-12 col-xs-12 text-right"><b>Anônima?</b></div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <select class="form-control" name="anonima" id="anonima" >
                    <option value="">Selecione</option>
                    <option value="1" <?php if(isset($GLOBALS['_POST']['anonima']) && $GLOBALS['_POST']['anonima'] == "1") echo "selected" ?>>Sim</option>
                    <option value="0" <?php if(isset($GLOBALS['_POST']['anonima']) && $GLOBALS['_POST']['anonima'] == "0") echo "selected" ?>>Não</option>
              </select>
            </div>
            <div class="col-md-2 col-sm-12 col-xs-12 text-right"><b>Motivo Denúncia:</b></div>
            <div class="col-md-4 col-sm-12 col-xs-12">
                <select class="form-control" name="motivo" id="motivo" >
                    <option value="">Selecione um Motivo</option>
<?php
                    foreach(CanalDenuncia::$ARRAY_MOTIVOS as $ind => $m){
?>
                        <option value="<?php echo $ind ?>" <?php if(isset($GLOBALS['_POST']['motivo']) && $ind == $GLOBALS['_POST']['motivo']) echo "selected" ?>><?php echo $m ?></option>;
<?php                        
                    } //end foreach(CanalDenuncia::$ARRAY_MOTIVOS as $ind => $m)
?>
              </select>
            </div>
            <div class="col-md-2 col-sm-12 col-xs-12 pull-right">
                <button type="submit" name="BtnSearch" id="BtnSearch" value="Consultar" class="btn pull-right btn-success">Consultar</button>
            </div>
            
        </div>
    </form>
</div>
</div>   <!--fecha a div de class="txt-azul-claro col-md-12 bg-branco p-bottom40"> -->
<div class="row espacamento text-center bg-branco">
    <div class="msg_box col-md-12 txt-preto top20"></div>
</div>
</div>   <!--fecha a div de class="container"> -->

<?php
    
if(isset($BtnSearch) && $BtnSearch) {
    
?>
    <div class="bg_branco" id="tabela_denuncia">
<?php
    //Montando SQL para a Busca das Remesas
    $sql = "
            SELECT *
            FROM canal_de_denuncia
            WHERE data_denuncia >= '".formata_data($_POST["data_inicial"],1)." 00:00:00' 
                  AND data_denuncia <= '".formata_data($_POST["data_final"],1)." 23:59:59' ";

    if(isset($GLOBALS['_POST']['motivo']) && $GLOBALS['_POST']['motivo']){
        $sql .= " AND motivo_denuncia = '".$GLOBALS['_POST']['motivo']."' ";
    }
    
    if(isset($GLOBALS['_POST']['anonima']) && $GLOBALS['_POST']['anonima'] != ""){
        $sql .= " AND denuncia_anonima = ".$GLOBALS['_POST']['anonima']." ";
    }
    
    $sql .= " ORDER by data_denuncia DESC;";

    $rs = SQLexecuteQuery($sql);
    if($rs) {
            $num_registros = pg_num_rows($rs);
            if($num_registros > 0) {
?>
                            <table class='table table-bordered txt-preto text-center fontsize-p' >
                                <tr class='text-center'>
                                    <td><strong>Data</strong></td>
                                    <td><strong>Protocolo</strong></td>
                                    <td><strong>Nome</strong></td>
                                    <td><strong>CPF</strong></td>
                                    <td><strong>Email</strong></td>
                                    <td><strong>Celular</strong></td>
                                    <td><strong>Motivo da Denúncia</strong></td>
                                    <td><strong>Mensagem</strong></td>
                                    <td><strong>Tem ID cliente?</strong></td>
                                </tr>
<?php
                    $i = 0;
                    while ($rsRow = pg_fetch_array($rs)) {

                        if($i%2 == 0){
                            $cor = $query_cor1;
                        } else{
                            $cor = $query_cor2;
                        }
                        $i++;
                        $instCanalDenuncia = new CanalDenuncia(null);
                        ?>
                            <tr bgcolor='<?php echo $cor; ?>' onmouseover=bgColor='#CFDAD7' onmouseout=bgColor='<?php echo $cor; ?>' class='trListagem fontsize-p'>
                                <td class='text-center'><?php echo date('d/m/Y H:i',strtotime($rsRow['data_denuncia'])); ?></td>
                                <td class='text-center'><?php echo $rsRow['protocolo']; ?></td>
                                <td class='text-center'><?php echo (empty($rsRow['nome'])?"-":$rsRow['nome']); ?></td>    
                                <td class='text-center'><?php echo (empty($rsRow['cpf'])?"-":$rsRow['cpf']); ?></td>
                                <td class='text-center'><?php echo (empty($rsRow['email'])?"-":$rsRow['email']); ?></td>
                                <td class='text-center'><?php echo (empty($rsRow['celular'])?"-":$rsRow['celular']); ?></td>
                                <td class='text-center'><?php echo $instCanalDenuncia->retorna_motivo_denuncia($rsRow['motivo_denuncia']); ?></td>
                                <td class='text-center' title="Clique no ícone para ver a mensagem"><h5><a href="#" class="btn-question glyphicon glyphicon-eye-open c-pointer t0" data-msg="<h3>Denúncia feita por<br> <?php echo (empty($rsRow['nome'])?"Anônimo":$rsRow['nome']); ?> <br>Protocolo [<?php echo $rsRow['protocolo']; ?>]</h3><?php echo $rsRow['mensagem_denuncia']; ?>" style="position: relative;"></a></h5></td>
                                <td class='text-center'><?php echo ($rsRow['ug_id'] != 0)?"<a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=".$rsRow['ug_id']."' title='Clique para ver detalhes do usuário' target='_blank'>".$rsRow['ug_id']."</a>":"Não"; ?></td>
                                
                            </tr>
<?php
                    }//end while
?>
                                <tr class='negrito'>
                                    <td class='text-right' colspan='8'><b>Total Registros:</b></td> 
                                    <td class='text-left'><b><?php echo $num_registros; ?></b></td>
                                </tr>
                            </table>
    </div>
<?php
            }//end if($num_registros > 0)
            else { 
?>
                <div class="container espacamento">
                    <div class="col-md-12 col-sm-12 col-xs-12 alert alert-danger">Nenhum registro encontrado no período.</div>
                </div>
<?php
            }//end else do if($num_registros > 0)
    }//end if($rs) 
    else {
?>          
                <div class="container row espacamento">
                    <div class="col-md-12 col-sm-12 col-xs-12 alert alert-danger">ERRO: Problema na seleção do intervalo das datas.</div>
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
</body>
</html>
