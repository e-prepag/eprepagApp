<?php
//error_reporting(E_ALL); 
ini_set("display_errors", 0); 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."banco/bexs/config.inc.bexs.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";

set_time_limit(3600);

?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<link rel="stylesheet" type="text/css" href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" />
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <form id="relatorio" name="relatorio" method="post" action="relatorio_previa_arquivo_bexs.php">
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
                <button type="submit" name="consultar" id="consultar" value="Consultar" class="btn pull-right btn-success">Consultar</button>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12 text-right"><br></div>
        <div class="col-md-12 col-sm-12 col-xs-12 text-right">
        <div class="col-md-2 col-sm-12 col-xs-12 text-right">Publisher:</div>
            <div class="col-md-2 col-sm-12 col-xs-12">
                <select name="opr_codigo" id="opr_codigo" >
                    <option value="">Selecione um Publisher</option>
<?php
                $sql_opr = "SELECT opr_codigo, opr_nome, opr_razao FROM operadoras WHERE opr_internacional_alicota > 0 AND opr_facilitadora > 0";
                $rs_opr = SQLexecuteQuery($sql_opr);
                
                while($rs_opr_row = pg_fetch_array($rs_opr)){ 
?>
                    <option value="<?php echo $rs_opr_row['opr_codigo'] ?>" <?php if(isset($_POST['opr_codigo']) && $rs_opr_row['opr_codigo'] == $_POST['opr_codigo']) echo "selected" ?>><?php echo $rs_opr_row['opr_nome']." (".$rs_opr_row['opr_codigo'].")" ?></option>
<?php 
                } //end while($rs_opr_row = pg_fetch_array($rs_opr))
?>
              </select>
            </div>
            
        </div>
    </form>
</div>
</div>   <!--fecha a div de class="txt-azul-claro col-md-12 bg-branco p-bottom40"> -->
</div>   <!--fecha a div de class="container"> -->

<?php
if(isset($consultar) && $consultar) {

$msg = "";
$classe = "alert alert-danger";

if(empty($opr_codigo)){
    $msg = "<strong>ERRO</strong>: É preciso definir um publisher para consultar";
}

$array_infos = array(
                        'data_ini'      => formata_data($data_inicial, 1),
                        'data_fim'      => formata_data($data_final, 1),
                        'dd_operadora'  => $opr_codigo
                    );

$bexs = new classBexs(NULL, $array_infos, FALSE, TRUE);

$cot_dolar = $bexs->recupera_cotacao_dolar();

if(empty($msg)){
    
    if(empty($cot_dolar)){
        $msg = "<strong>ERRO</strong>: É preciso cadastrar uma cotação para o dólar na página <a href='../cadastro_cotacao_dolar.php' target='_blank'>Cadastrar Cotação Dólar</a> para consultar";
    } else{
        $test = $bexs->array_operacoes_detalhadas($cot_dolar, TRUE);

        if(is_array($test)){
            $classe = "alert alert-success";
            $msg = "Verificação dos dados concluída com sucesso! Não há problemas com os dados.";
        } elseif(is_null($test)){
            $classe = "alert alert-danger";
            $msg = "Problema ao recuperar dados no banco de dados!";
        } else{
            $classe = "alert alert-danger";
            $msg = $bexs->getMsgErro();
        }
    }
} //end if(empty($msg))

?>
<div class="msg_box col-md-12 borda bloco bg-cinza-claro top20"></div>
<div class="container">
    <div class="col-md-12 <?php echo $classe; ?>">
        <span><?php echo $msg; ?></span>
    </div>
</div>    
     
<?php
} // end if($consultar)
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
<?php

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>
