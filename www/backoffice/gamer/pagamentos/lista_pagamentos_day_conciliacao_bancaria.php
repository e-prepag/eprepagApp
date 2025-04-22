<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
$pagina_titulo = "Lista de pagamentos por dia";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."banco/bradesco/inc_urls_bradesco.php";
require_once $raiz_do_projeto."banco/bancodobrasil/inc_urls_bancodobrasil.php";
require_once $raiz_do_projeto."includes/constantesPagamento.php";
require_once $raiz_do_projeto."includes/inc_Pagamentos.php";
require_once $raiz_do_projeto."includes/functionsPagamento.php";

$url_atual = explode('/', $_SERVER['REQUEST_URI']);
array_pop($url_atual);
$url_atual = implode('/',$url_atual);

//Explicativo
require_once $raiz_do_projeto."class/classDescriptionReport.php";
require_once $raiz_do_projeto . 'includes/Feriados.php';
$descricao = new DescriptionReport('conciliacao_bancaria');
$strDescricao = $descricao->MontaAreaDescricao();
echo str_replace("<script language='JavaScript' src='" . DIR_WEB . "/js/jquery.js'></script>", "", $strDescricao);

function calcularValorEsperado($data_inicio, $num_dias, $tipo_pagamento){
    
    $sql = "SELECT
            sum(rfcb_venda_lans+rfcb_venda_gamer+rfcb_valor_dep_brad) as valor_esperado
        FROM relfin_conciliacao_bancaria
        WHERE rfcb_tipo_pagamento='{$tipo_pagamento}' and rfcb_data_registro >= ('{$data_inicio}'::timestamp) 
        GROUP BY rfcb_data_registro 
        ORDER BY rfcb_data_registro 
        LIMIT {$num_dias} ;";
        
    $rs = SQLexecuteQuery($sql);
    $valor_esperado = 0;
    if($rs){
        while($fetched = pg_fetch_array($rs)){
            $valor_esperado += $fetched['valor_esperado'];
        }
    }
    return $valor_esperado;
}

function retornaTaxaEsperada($iforma, $data_registro, $qtde) {

    $banco = retornaBanco($iforma);
    
    $sql = "SELECT taxa FROM taxas_transacoes_cobradas_da_epp 
            WHERE id_forma = '{$iforma}' and banco = '{$banco}' and
            data = ( 
                        select max(data) from taxas_transacoes_cobradas_da_epp 
                        where data <= ('{$data_registro}') and id_forma = '{$iforma}' and banco = '{$banco}' 
                    );";

    $rs_taxa = sqlexecuteQuery($sql);
    if(!$rs_taxa || pg_num_rows($rs_taxa) == 0) return 0;
    else {
        $rs_row = pg_fetch_array($rs_taxa);
        $taxa = $rs_row['taxa'] * $qtde;
        
        return number_format($taxa,2,".","");
    } 

}//end function retornaTaxaEsperada($iforma, $data_registro, $qtde)

function retornaBanco($iforma){ // $iforma = Alpha do tipo de pagamento
    switch($iforma) {
        case '5':
            $banco = 237; //Transferência entre contas Bradesco (BRD5)
            break;
        case '6':
            $banco = 237; //Pagamento Fácil Bradesco (BRD6)(INATIVO)******
            break;
        case '9':
            $banco = 001; //Pagamento BB - Débito sua Conta (BBR9)
            break;
        case 'A':
            $banco = 341; //Pagamento Banco Itaú (BITA)
            break;
        case 'B':
            $banco = 11; //Pagamento HiPay (HIPB)(INATIVO)******
            break;
        case 'E':
            $banco = 998; //PINs EPP (EPPE)(INATIVO)******
            break;
        case 'P':
            $banco = 12; //Pagamento PayPal (PYPP)(INATIVO)******
            break;
        case 'R':
            $banco = 400;
        case 'C':
            $banco = 0; //*******CIELO (INATIVO)******
            break;
        case 'Z':
            $banco = 999; //Pagamento Banco E-Prepag (BEPZ)(INATIVO)******
            break;
        default:
            $banco = 0;
            break;
    }
    return $banco;
}//end function retornaBanco($iforma)

function retornaTaxaEsperadaAtual($iforma, $ano, $mes) {

    $banco = retornaBanco($iforma);
    $sql_w = "";
    
    if($ano != ""){
        if($mes != ""){
            if($mes == '02' && ($ano%4 == 0 && $ano%100 != 0) || ($ano%4 != 0 && $ano%400 == 0)){
                $sql_w = " and data IN
                            (
                                select data
                                from taxas_transacoes_cobradas_da_epp
                                where data < ('".$ano."-".$mes."-29 00:00:00')
                            ) "; 
            } elseif($mes == '02'){
                $sql_w = " and data IN
                            (
                                select data
                                from taxas_transacoes_cobradas_da_epp
                                where data < ('".$ano."-".$mes."-28 00:00:00')
                            ) "; 
            } elseif($mes == '04' || $mes == '06' || $mes == '09' || $mes == '11'){
                $sql_w = " and data IN
                            (
                                select data
                                from taxas_transacoes_cobradas_da_epp
                                where data < ('".$ano."-".$mes."-30 00:00:00')
                            ) ";
            } else{
                $sql_w = " and data IN
                            (
                                select data
                                from taxas_transacoes_cobradas_da_epp
                                where data < ('".$ano."-".$mes."-31 00:00:00')
                            ) ";
            }
            
        } else{
            $sql_w = " and data IN
                        (
                            select data
                            from taxas_transacoes_cobradas_da_epp
                            where data < ('".$ano."-12-31 00:00:00')
                        ) ";
        }
    } else{
        
            $sql_w = " and data IN
                        (
                            select data
                            from taxas_transacoes_cobradas_da_epp
                            where data < CURRENT_DATE
                        ) ";
    }

    $sql = "SELECT taxa,data
            FROM taxas_transacoes_cobradas_da_epp 
            WHERE 
            banco = '".$banco."' and id_forma = '".$iforma."'  
            $sql_w ";
    
    $sql .= " group by taxa,data
            order by data,taxa";
    
    $rs_taxa = sqlexecuteQuery($sql);
    if(!$rs_taxa || pg_num_rows($rs_taxa) == 0) return false;
    else {
        $rs_row = pg_fetch_all($rs_taxa);
        return $rs_row;
    } 
}//end function retornaTaxaEsperadaAtual($iforma, $ano, $mes)

$time_start_stats = getmicrotime();

$msg_retorno = "";

//Atualizando o registro - JUNTANDO
if(!empty($id_add)) {
    function normalizaValor($number){
        $number = str_replace('.', '', $number);
        $number = str_replace(',','.', $number);

        if ( substr_count($number, '.') > 1 ) {
            $number = preg_replace('/[.|,]/', '', $number);
            $cents = substr($number, -2);
            $number = substr($number, 0, -2). '.' . $cents;
        }

        return $number;
    }

    list($primeiro, $segundo) = explode(",", $id_add);
    if(!empty($primeiro)&&!empty($segundo)) {

        $valor_extrato_primeiro = normalizaValor($rfcb_valor_extrato[$primeiro]);
        $valor_extrato_segundo = normalizaValor($rfcb_valor_extrato[$segundo]);
        $total_rfcb_valor_extrato = ($valor_extrato_primeiro + $valor_extrato_segundo);

        $taxa_extrato_primeiro = normalizaValor($rfcb_taxa_extrato[$primeiro]);
        $taxa_extrato_segundo = normalizaValor($rfcb_taxa_extrato[$segundo]);
        $total_rfcb_taxa_extrato = ($taxa_extrato_primeiro + $taxa_extrato_segundo);

        if(empty($rfcb_comentario[$primeiro])) {
            $total_rfcb_comentario = $rfcb_comentario[$segundo];
        } else {
            $total_rfcb_comentario = $rfcb_comentario[$primeiro] ." - ".$rfcb_comentario[$segundo];
        }
        //Atualizando o primeiro registro
        $sql = "update relfin_conciliacao_bancaria set rfcb_valor_extrato=$total_rfcb_valor_extrato, rfcb_taxa_extrato=$total_rfcb_taxa_extrato, rfcb_comentario = '$total_rfcb_comentario', rfcb_numero_de_dias = (rfcb_numero_de_dias+1), rfcb_valor_a_receber = (rfcb_valor_a_receber+".$rfcb_valor_a_receber[$segundo]."), rfcb_data_extrato = to_date('".$rfcb_data_extrato[$segundo]."','DD/MM/YYYY') where rfcb_id = $primeiro";


        $rs_update_primeiro = SQLexecuteQuery($sql);
        if(!$rs_update_primeiro) {
            $msg_retorno .= "Erro ao atualizar o registro de data de extrato [".$rfcb_data_extrato[$primeiro]."].<br>";
        }
        //Atualizando o primeiro registro
        $sql = "update relfin_conciliacao_bancaria set rfcb_valor_extrato=0, rfcb_taxa_extrato=0, rfcb_comentario = '', rfcb_valor_a_receber = 0, rfcb_data_extrato = null where rfcb_id = $segundo";
        //echo "<br>Segundo registro:<br>$sql<br>";
        //echo "SEGUNDO: $sql <br>";
        $rs_update_segundo = SQLexecuteQuery($sql);
        if(!$rs_update_segundo) {
            $msg_retorno .= "Erro ao atualizar o registro de data de extrato [".$rfcb_data_extrato[$segundo]."].<br>";
        }
    }//end if(!empty($primeiro)&&!empty($segundo))
    else {
        $msg_retorno .= "Erro ao levantar os dois registros que devem ser juntados.<br>";
    }
    
    if(!isset($msg_retorno) || $msg_retorno == ""){
        @header ("Location: lista_pagamentos_day_conciliacao_bancaria.php?tf_v_forma_pagamento=".$_REQUEST['tf_v_forma_pagamento']);    
    }
    
}//end if(!empty($id_add))

//Atualizando o registro - SEPARANDO
if(!empty($id_del)) {
    //Atualizando o primeiro registro
    $sql = "update relfin_conciliacao_bancaria set rfcb_numero_de_dias = 1, rfcb_valor_a_receber = 0, rfcb_data_extrato = null where rfcb_id = $id_del;";
    //echo "SEPARA: $sql <br>";
    $rs_update_separa = SQLexecuteQuery($sql);
    if(!$rs_update_separa) {
        $msg_retorno = "Erro ao separar os registros.<br>";
    }
}//end if(!empty($id_del))

//Atualizando - TODAS
if(!empty($saveall)) {

    foreach ($rfcb_data_extrato as $key => $value){

        //Atualizando o registro
        $sql = "update relfin_conciliacao_bancaria set rfcb_valor_extrato=".(str_replace(',','.',str_replace('.','',$rfcb_valor_extrato[$key]))*1).", rfcb_taxa_extrato=".(str_replace(',','.',str_replace('.','',$rfcb_taxa_extrato[$key]))*1).", rfcb_comentario = '".$rfcb_comentario[$key]."', rfcb_data_extrato = to_date('".$rfcb_data_extrato[$key]."','DD/MM/YYYY') where rfcb_id = $key ;";
        //echo "TODOS: $sql <br>";
        $rs_update = SQLexecuteQuery($sql);
        if(!$rs_update) {
            $msg_retorno .= "Erro ao atualizar o registro [$key].<br>";
        }

    } //end foreach
}//end if(!empty($id_del))


if(!$btPesquisar) {
    if(!$tf_v_data_year && !$tf_v_data_month) {
        if(!$tf_v_data_year) {
            $tf_v_data_year = date("Y");
        }
        if(!$tf_v_data_month) {
            $tf_v_data_month = date("m");
        }

        $tf_v_data_year = date("Y", strtotime($tf_v_data_year."/".$tf_v_data_month."/01"));
        $tf_v_data_month = date("m", strtotime($tf_v_data_year."/".$tf_v_data_month."/01"));
    }
}

//setando a forma de pagamento default
if(empty($tf_v_forma_pagamento)) {
    $tf_v_forma_pagamento = '5';
}

if($tf_v_data_month) {
    //Recupera o último dia dado o mês
    $get_ultimo_dia_mes = date('Y')."-".$tf_v_data_month."-01";
    $tf_v_data_month_last_day = date("t", strtotime($get_ultimo_dia_mes));
}

//Validacoes
$msg = "";

//Recupera as vendas
if($msg == ""){

    $sql  = "
        select rfcb_data_registro,to_char(rfcb_data_registro,'DD Mon YYYY')as rfcb_data_registro2, sum(rfcb_venda_gamer) as svg,sum(rfcb_venda_lans) as svl,sum(rfcb_qtde_gamer) as sqg,sum(rfcb_qtde_lans) as sql,
                max(rfcb_id) as rfcb_id_max,
                to_char(max(rfcb_data_extrato),'DD/MM/YYYY') as rfcb_data_extrato_max,
                sum(rfcb_valor_extrato) as sve,
                sum(rfcb_taxa_extrato) as ste,
                max(rfcb_comentario) as rfcb_comentario_max,
                max(rfcb_numero_de_dias) as rfcb_numero_de_dias_max,
                sum(rfcb_valor_a_receber) as svr,
                sum(rfcb_valor_dep_brad) as sdb,
                extract(dow from (TO_DATE(TO_CHAR(EXTRACT(YEAR FROM rfcb_data_registro ),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM rfcb_data_registro ),'99')||'-'||TO_CHAR(EXTRACT(DAY FROM rfcb_data_registro ),'99'),'YYYY-MM-DD'))) as dow
        from relfin_conciliacao_bancaria pgt
        ";

    // Filtros Where
    $sql_where = "";

    // Group
    $sql_group = " group by rfcb_data_registro ";

    if($tf_v_forma_pagamento) {
        if($tf_v_forma_pagamento=="C") {
            $sql_where = " where ".getSQLWhereParaPagtoOnlineConciliacao(true)." ";
        } else {
            $sql_where = " where rfcb_tipo_pagamento='".$tf_v_forma_pagamento."' ";
        }
    }

    if($tf_v_data_year) {
        if($tf_v_data_month) {//('".$datainicio." 00:00:00'::timestamp - '1 month'::interval)
            $sql_where .= " and TO_DATE(TO_CHAR(EXTRACT(YEAR FROM rfcb_data_registro ),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM rfcb_data_registro ),'9999')||'-'||TO_CHAR(EXTRACT(DAY FROM rfcb_data_registro ),'99'),'YYYY-MM-DD') between ('".$tf_v_data_year."-".$tf_v_data_month."-01 00:00:00'::timestamp";
            if($tf_ultimo_dia) {
                $sql_where .= " - '$tf_ultimo_dia days'::interval";
            }
            $sql_where .= ") and ('".$tf_v_data_year."-".$tf_v_data_month."-".$tf_v_data_month_last_day." 23:59:59'::timestamp";
            if($tf_primeiro_dia) {
                $sql_where .= " + '$tf_primeiro_dia days'::interval";
            }
            $sql_where .= ") ";
        } else {
            $sql_where .= " and TO_DATE(TO_CHAR(EXTRACT(YEAR FROM rfcb_data_registro ),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM rfcb_data_registro ),'9999')||'-'||TO_CHAR(EXTRACT(DAY FROM rfcb_data_registro ),'99'),'YYYY-MM-DD') between ('".$tf_v_data_year."-01-01 00:00:00') and ('".$tf_v_data_year."-12-31 23:59:59') ";
        }
    } else{
        
       if($tf_v_data_month != ""){
           $sql_where .= " and rfcb_data_registro <
                                (
                                    select max(rfcb_data_registro) from relfin_conciliacao_bancaria
                                    where rfcb_tipo_pagamento='".$tf_v_forma_pagamento."' 
                                )
                            and EXTRACT(MONTH FROM rfcb_data_registro ) = '".$tf_v_data_month."' ";
       } 
    }

    $sql .= $sql_where.$sql_group." order by rfcb_data_registro";

    //if(b_IsUsuarioWagner()) echo nl2br($sql_where)."<br><br><br>".$sql;

    $rs_total = SQLexecuteQuery($sql);
    if($rs_total) $registros_total = pg_num_rows($rs_total);

    $rs_transacoes = SQLexecuteQuery($sql);
    if(!$rs_transacoes || pg_num_rows($rs_transacoes) == 0) {
        $msg_retorno = "Nenhum pagamento encontrado.\n";
    }
}
require_once "/www/includes/bourls.php";
?>
<script language='javascript' src='/js/table2CSV.js'></script>
<script src="/js/jquery.base64.min.js"></script>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script>
$(function(){
   $(".datepicker").datepicker(); 
});
function myAlert(titulo, msg) {

    var my_alert = $('#my_alert');

    my_alert.attr('title', titulo);
    my_alert.html(msg);

    my_alert.dialog({
        buttons: [
            {
                text: "Ok",
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
        ]
    });
}

Number.prototype.formatMoney = function(c, d, t){
var n = this, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

$(function() {
    $('.btn-download').click(function(){
        window.location = "/includes/download/download_tabela.php?filename=tabela.csv&content=" + $.base64.encode($('#tabela_pagamento').table2CSV2().replace(/\./g, "").replace(/,/g, "."));
    });
    
    $(".extrato").blur(function(){
        var id = $(this).attr("extrato");
        var currentVal = parseFloat($(this).val().trim().replace(".","").replace(",","."));
        var valorEsperado   = parseFloat($("td[valorEsperado='"+id+"']").html().trim().replace(".","").replace(",","."));
        var subtracao = (currentVal - valorEsperado).formatMoney(2, ',', '.');
        $("td[aReceber='"+id+"']").html(subtracao);
    });
});

jQuery.fn.table2CSV2 = function() {
    rows = [];
    data = "";

    $(this).find('tr').each(function(){
        row = [];
        tr = $(this);

        tr.find('td').each(function(){
            text = $(this).text().trim();
            //Não achamos texto aqui, talvez pq esteja dentro de um input
            if(text==""){
                text = $(this).find('input').val();
            }

            if(typeof(text)!="string")
                text = "";

            text = text.replace(/\t/g, "");
            text = text.replace(/\n/g, "");
            row.push(text);
        });
        rows.push(row);
    });

    for(key in rows) {
        data += rows[key].join(";") + "\n";
    }

    var win = window.open('', 'csv', 'height=400,width=600');
    win.document.write('test');

    return data;
}

<!--
function agrupaDatas(ids) {
    if(confirm("Antes de agrupar as datas, tem que ser importado o extrato.")){
        var url = '/ajax/ajaxConciliacaoBancaria.php';
        var tipo_pagamento = '<?php echo $tf_v_forma_pagamento ;?>';
        var jqHXR = $.get(url, {ids: ids, tipo_pagamento:tipo_pagamento}, function(data){
            if ( data.status == 'ok' ) {
                 reload(ids);
            } else {
                myAlert('Erro',data.message);
            }
        }, 'json')
            .fail(function(data){
                myAlert('Erro',data.message);
            });
    }
}
function reload(id) {
    document.form1.action = "lista_pagamentos_day_conciliacao_bancaria.php";
    document.form1.id_add.value = id;
    document.form1.submit();
}
function Dreload(id) {
    document.form1.action = "lista_pagamentos_day_conciliacao_bancaria.php";
    document.form1.id_del.value = id;
    document.form1.submit();
}
function SaveAll() {
    document.form1.action = "lista_pagamentos_day_conciliacao_bancaria.php";
    document.form1.saveall.value = 1;
    document.form1.submit();
}
//-->
</script>
<?php $pagina_titulo = "Meus Pagamentos"; ?>
<style type="text/css">
    .odd {background-color: #e9e9ef;}
    tr.relfin:hover {background-color: rgba(140, 140, 146, 0.60);}
</style>
<div id="my_alert" title="" style="display: none;"></div>
<form name="form1" id="form1" method="post" action="lista_pagamentos_day_conciliacao_bancaria.php">
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12">
    <input type="hidden" id="id_add" name="id_add" value="">
    <input type="hidden" id="id_del" name="id_del" value="">
    <input type="hidden" id="saveall" name="saveall" value="">
    <table class="table txt-preto fontsize-pp">
<?php 
    if(isset($msg_retorno)) { 
?>
        <tr bgcolor="F0F0F0">
            <td class="texto" align="center">Mensagem: <b><font color="#FF0000"><?php echo $msg_retorno; ?></font></b></td>
        </tr>
<?php 
    } 
?>
        <tr bgcolor="F0F0F0">
            <td class="texto" align="center"><b>Mês da Compra</b></td>
        </tr>
        <tr bgcolor="F5F5FB">
            <td class="texto" align="center">&nbsp;
                <select id='tf_v_data_year' name='tf_v_data_year'>
                    <option value=''<?php echo (($tf_v_data_year=="")?" selected":""); ?>>Todos os anos</option>
<?php
                $year_start = 2012;
                $year_now = date("Y");
                for($i=$year_now;$i>=$year_start;$i--) {
?>
                    <option value='<?php echo $i; ?>'<?php echo (($tf_v_data_year==$i)?" selected":""); ?>><?php echo $i; ?></option>
<?php
                }
?>
                </select> -
                <select id='tf_v_data_month' name='tf_v_data_month'>
                    <option value=''<?php echo (($tf_v_data_month=="")?" selected":""); ?>>Todos os meses</option>
<?php
                $month_start = 1;
                $month_now = (($tf_v_data_year==date("y"))?date("m"):12);

                for($i=$month_start;$i<=$month_now;$i++) {
                    $simonth = (($i<10)?"0":"").$i;
?>
                    <option value='<?php echo $simonth; ?>'<?php echo (($tf_v_data_month==$simonth)?" selected":""); ?>><?php echo Mes_Do_Ano($i); ?></option>
                    <?php
                }
?>
                </select>
                <br>
                <input name="tf_ultimo_dia" type="text" class="form" id="tf_ultimo_dia" value="<?php echo $tf_ultimo_dia?>" size="2" maxlength="1"> Número de dias do Mês Anterior.
                &nbsp;&nbsp;
                <input name="tf_primeiro_dia" type="text" class="form" id="tf_primeiro_dia" value="<?php echo $tf_primeiro_dia?>" size="2" maxlength="1"> Número de dias do Mês Seguinte.
            </td>
        </tr>
        <tr bgcolor="F5F5FB">
            <td class="texto" align="center">
                <select id='tf_v_forma_pagamento' name='tf_v_forma_pagamento' class="form2">
                    <option value='5'<?php echo (($tf_v_forma_pagamento=="5")?" selected":"") ?>>Transferência entre contas Bradesco (BRD5)</option>
                    <option value='6'<?php echo (($tf_v_forma_pagamento=="6")?" selected":"") ?>>Pagamento Fácil Bradesco (BRD6)</option>
                    <option value='9'<?php echo (($tf_v_forma_pagamento=="9")?" selected":"") ?>>Pagamento BB - Débito sua Conta (BBR9)</option>
                    <option value='A'<?php echo (($tf_v_forma_pagamento=="A")?" selected":"") ?>>Pagamento Banco Itaú (BITA)</option>
                    <option value='B'<?php echo (($tf_v_forma_pagamento=="B")?" selected":"") ?>>Pagamento HiPay (HIPB)</option>
                    <option value='E'<?php echo (($tf_v_forma_pagamento=="E")?" selected":"") ?>>PINs EPP (EPPE)</option>
                    <option value='P'<?php echo (($tf_v_forma_pagamento=="P")?" selected":"") ?>>Pagamento PayPal (PYPP)</option>
                    <option value='R'<?php echo (($tf_v_forma_pagamento=="R")?" selected":"") ?>>Pagamento PIX (<?php echo $GLOBALS['PAGAMENTO_PIX_NOME_BANCO'] ?>)</option>
                    <option value='C'<?php echo (($tf_v_forma_pagamento=="C")?" selected":"") ?>>Pagamentos Cielo (F-M)</option>
                    <option value='Z'<?php echo (($tf_v_forma_pagamento=="Z")?" selected":"") ?>>Pagamento Banco E-Prepag (BEPZ)</option>
                </select>
            </td>
        </tr>
        <tr bgcolor="F5F5FB">
            <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info"></td>
        </tr>
    </table>
</div></div></div></div>
    <table class="table txt-preto fontsize-pp" id="tabela_pagamento">
        <tr bgcolor="F0F0F0">
            <td class="texto" align="center"><b>Data Venda</b>&nbsp;</td>
            <td class="texto" align="center"><b>Qtde. Venda GAMER</b></td>
            <td class="texto" align="center"><b>Backoffice Venda R$ GAMER</b></td>
            <td class="texto" align="center"><b>Qtde. Venda LAN</b></td>
            <td class="texto" align="center"><b>Backoffice Venda R$ LAN</b>&nbsp;</td>
    <?php
        if($tf_v_forma_pagamento=="5") {
    ?>
            <td class="texto" align="center"><b>Arquivo Depósitos Concilados</b></td>
    <?php
        } //end if($tf_v_forma_pagamento=="5")
    ?>
            <td class="texto" align="center"><b>Qtde. Venda GAMER+LAN</b></td>
            <td class="texto" align="center"><b>Valor Esperado R$ GAMER+LAN</b></td>
            <td class="texto" align="center"><b>Data Extrato</b></td>
            <td class="texto" align="center"><b>Valor Extrato venda Internet</b></td>
            <td class="texto" align="center"><b>Saldo a Receber</b></td>
            <td class="texto" align="center"><b>Saldo a Receber Acumulado</b></td>
            <td class="texto" align="center"><b>Taxa Esperada</b></td>
            <td class="texto" align="center"><b>Taxa Extrato</b></td>
            <td class="texto" align="center"><b>Diferença</b></td>
            <td class="texto" align="center"><b>Diferença Acumulada</b></td>
            <td class="texto" align="center"><b>Comentários</b></td>
            <td class="texto" align="center">&nbsp;</td>
        </tr>
<?php
        $i=0;
        $irows=0;
        $total_venda_gamer = 0;
        $total_bo_venda_gamer = 0;
        $total_venda_lan = 0;
        $total_bo_venda_lan = 0;
        $total_arq_dep_conc = 0;
        $total_venda_gamer_lan = 0;
        $total_esperado_gamer_lan = 0;
        $total_saldo_receber = 0;
        $total_diferenca = 0;
        $saldo_receber_acumulado = 0;
        $diferenca_acumulada = 0;

        if($rs_transacoes) {

            $irows=0;
            $total_pagamentos_page = 0;
            $total_pagtos_page = 0;
            $rfcb_id_max_anterior = 0;
            $controlador_row_span = 1;
            $controlador_totais_aux = 0;
            $contador = 0;

            $rs_transacoes_all = pg_fetch_all($rs_transacoes);

            if($rs_transacoes_all){
                //Recupera a taxa vigente cobrada na data de cada registro
                $retorna_taxa = (retornaTaxaEsperadaAtual($tf_v_forma_pagamento, $tf_v_data_year, $tf_v_data_month));
                $retorna_taxa = (!$retorna_taxa)?'0':$retorna_taxa;
                $banco = retornaBanco($tf_v_forma_pagamento);
                
                //Laço para definir a taxa vigente de cada data de registro("Data Venda")
                foreach($rs_transacoes_all as $indice => $rs_define_taxa){
                    if($retorna_taxa != '0'){
                        for($i=0; $i < count($retorna_taxa);$i++){
                            //A taxa vigente para cada tipo de pagamento será considerada levando em conta a última data de atualização da taxa
                                if(substr($retorna_taxa[$i]['data'], 0, 19) <= $rs_define_taxa['rfcb_data_registro']){
                                    $tx_esp[$rs_define_taxa['rfcb_data_registro']] = $retorna_taxa[$i]['taxa'];
                                    $data_taxa_vigente[$rs_define_taxa['rfcb_data_registro']] = $retorna_taxa[$i]['data'];

                                }
                        }
                    } else{
                        //Caso não tenha nenhuma data de atualização de taxa, a taxa vigente é setada como 0,00
                        $tx_esp[$rs_define_taxa['rfcb_data_registro']] = 0;
                    }
                }
  
                foreach($rs_transacoes_all as $ind => $rs_transacoes_row){
                    
                    $contador++;
                    $irows++;
                    $total_pagamentos_page += ($rs_transacoes_row['svg']+$rs_transacoes_row['svg']+$rs_transacoes_row['sdb']);
                    $total_pagtos_page += $rs_transacoes_row['sqg']+$rs_transacoes_row['sql'];
                    
                    if($rs_transacoes_row['rfcb_numero_de_dias_max'] > 1){
                        $taxa_esperada = 0;
                        $esperado = 0;
                        $indLoop = $ind+$rs_transacoes_row['rfcb_numero_de_dias_max'];

                        for($i=$ind;$i<$indLoop;$i++){
                            $agrup_taxas_diferentes = false;

                            //Verifica se entre as vendas agrupadas existe alguma venda com taxa diferente dentro do grupo
                            if(($i+1) < $indLoop){
                                if($tx_esp[$rs_transacoes_all[$i]['rfcb_data_registro']] != $tx_esp[$rs_transacoes_all[$i+1]['rfcb_data_registro']]){
                                    $agrup_taxas_diferentes = true;
                                    $taxa_dif = $tx_esp[$rs_transacoes_all[$i]['rfcb_data_registro']];
                                    $qtd_dif = ($rs_transacoes_all[$i]['sqg']+$rs_transacoes_all[$i]['sql']);
                                }
                            }

                            if($agrup_taxas_diferentes ){
                                $qtd_taxaesp = $taxa_dif * $qtd_dif;
                                //A taxa esperada é definida como a multiplicação da taxa vigente pela quantidade total de Vendas (GAMER+PDV)
                                $taxa_esperada += $qtd_taxaesp;
                                $esperado += retornaTaxaEsperada($tf_v_forma_pagamento, $rs_transacoes_all[$i]['rfcb_data_registro'], ($rs_transacoes_all[$i]['sqg']+$rs_transacoes_all[$i]['sql']))-$rs_transacoes_all[$i]['ste'];
                            }else{
                                $qtd_taxaesp = $tx_esp[$rs_transacoes_all[$i]['rfcb_data_registro']] * ($rs_transacoes_all[$i]['sqg']+$rs_transacoes_all[$i]['sql']);
                                //A taxa esperada é definida como a multiplicação da taxa vigente pela quantidade total de Vendas (GAMER+PDV)
                                $taxa_esperada += $qtd_taxaesp;
                                $esperado += retornaTaxaEsperada($tf_v_forma_pagamento, $rs_transacoes_all[$i]['rfcb_data_registro'], ($rs_transacoes_all[$i]['sqg']+$rs_transacoes_all[$i]['sql']))-$rs_transacoes_all[$i]['ste'];
                            }

                        }

                    }else{
                        //A taxa esperada é definida como a multiplicação da taxa vigente pela quantidade total de Vendas (GAMER+PDV)
                        $taxa_esperada =  $tx_esp[$rs_transacoes_row['rfcb_data_registro']] * ($rs_transacoes_row['sqg']+$rs_transacoes_row['sql']);
                        $esperado = retornaTaxaEsperada($tf_v_forma_pagamento, $rs_transacoes_row['rfcb_data_registro'], ($rs_transacoes_row['sqg']+$rs_transacoes_row['sql']))-$rs_transacoes_row['ste'];

                    }
?>
                    <tr class="trListagem relfin <?php echo ($contador&1)?'odd':'even' ;?>">
                        <td align="left"><nobr><?php echo $rs_transacoes_row['rfcb_data_registro2']." - ".get_dia_da_semana($rs_transacoes_row['dow']);?></nobr></td>
                        <td align="right"><?php echo number_format($rs_transacoes_row['sqg'], 0, ',', '.'); $total_venda_gamer += $rs_transacoes_row['sqg']; ?></td>
                        <td align="right"><?php echo number_format($rs_transacoes_row['svg'], 2, ',', '.'); $total_bo_venda_gamer += $rs_transacoes_row['svg'] ?></td>
                        <td align="right"><?php echo number_format($rs_transacoes_row['sql'], 0, ',', '.'); $total_venda_lan += $rs_transacoes_row['sql']; ?></td>
                        <td align="right"><?php echo number_format($rs_transacoes_row['svl'], 2, ',', '.'); $total_bo_venda_lan += $rs_transacoes_row['svl']; ?></td>
<?php
                            if($tf_v_forma_pagamento=="5") {
?>
                        <td align="right"><?php echo number_format($rs_transacoes_row['sdb'], 2, ',', '.'); $total_arq_dep_conc += $rs_transacoes_row['sdb']; ?></td>
<?php
                            } //end if($tf_v_forma_pagamento=="5")
?>
                        <td align="right"><?php echo number_format(($rs_transacoes_row['sqg']+$rs_transacoes_row['sql']), 0, ',', '.'); $total_venda_gamer_lan += ($rs_transacoes_row['sqg']+$rs_transacoes_row['sql']) ?></td>
                        <td align="right" valorEsperado="<?php echo $rs_transacoes_row['rfcb_id_max'];?>">
<?php
                            echo number_format(($rs_transacoes_row['svl']+$rs_transacoes_row['svg']+$rs_transacoes_row['sdb']), 2, ',', '.');
                            $total_esperado_gamer_lan += $rs_transacoes_row['svl']+$rs_transacoes_row['svg']+$rs_transacoes_row['sdb'];
?>
                        </td>
<?php
                            $controlador_totais_aux += ($rs_transacoes_row['svl']+$rs_transacoes_row['svg']+$rs_transacoes_row['sdb']);

                            if($controlador_row_span == 1) {
?>

                        <td class="texto" align="center" rowspan="<?php echo $rs_transacoes_row['rfcb_numero_de_dias_max'];?>" valign="middle"><nobr>
                            <input type="hidden" id="rfcb_valor_a_receber[<?php echo $rs_transacoes_row['rfcb_id_max'];?>]" name="rfcb_valor_a_receber[<?php echo $rs_transacoes_row['rfcb_id_max'];?>]" value="<?php echo ($rs_transacoes_row['svl']+$rs_transacoes_row['svg']+$rs_transacoes_row['sdb']);?>">
                            <input type="text" name="rfcb_data_extrato[<?php echo $rs_transacoes_row['rfcb_id_max'];?>]" id="rfcb_data_extrato[<?php echo $rs_transacoes_row['rfcb_id_max'];?>]" value="<?php
                                if(!empty($rs_transacoes_row['rfcb_data_extrato_max'])) {
                                    echo $rs_transacoes_row['rfcb_data_extrato_max'];
                                }
                                elseif($rs_transacoes_row['rfcb_numero_de_dias_max'] == 1) {
                                    //TRABALHO com DATA Wagner de Miranda
                                    list($day, $month, $year) = explode(' ', $rs_transacoes_row['rfcb_data_registro2']);
                                    $nmonth = date('m',strtotime($month));
                                    $data = "$day/$nmonth/$year";
                                    $feriados = new Feriados();
                                    //Se o pagamento for ITAU (A), a data considerada no extrato é D+0. Para BRADESCO (5) continua D+1 e Banco do BRASIL(9) é D+2.;
                                    if($tf_v_forma_pagamento == 'A'){
                                        $data_aux  = mktime(0, 0, 0, $nmonth, $day, $year);
                                    } 
                                    elseif($tf_v_forma_pagamento == '9'){
                                        $data_aux  = $feriados->addDiaUtil($data, 2);
                                    } 
                                    else{
                                        $data_aux  = $feriados->addDiaUtil($data, 1);
                                    }
                                    echo date('d/m/Y',$data_aux);
                                }
                            ?>" class="form datepicker" size="9" maxlength="10" readonly=readonly><nobr>
                        </td>
                        <td class="texto" align="right" rowspan="<?php echo $rs_transacoes_row['rfcb_numero_de_dias_max'];?>" valign="middle">
                            <input type="text" name="rfcb_valor_extrato[<?php echo $rs_transacoes_row['rfcb_id_max'];?>]" id="rfcb_valor_extrato[<?php echo $rs_transacoes_row['rfcb_id_max'];?>]" value="<?php echo number_format($rs_transacoes_row['sve'], 2, ',', '.'); ?>" extrato="<?php echo $rs_transacoes_row['rfcb_id_max'];?>" class="form extrato" size="8" maxlength="12"><?php //echo $controlador_totais_aux." - ".$rs_transacoes_row['sve']." - ".$rs_transacoes_row['svr'];?>
                        </td>
                        <td class="texto destaque1" align="right"  aReceber="<?php echo $rs_transacoes_row['rfcb_id_max'];?>" rowspan="<?php echo $rs_transacoes_row['rfcb_numero_de_dias_max'];?>" valign="middle">
<?php
                        //<!--Saldo a Receber-->
                        // Se tiver dias juntados entao calcular o saldo a receber na
                        // soma dos dias do campo valor esperado gamer lan
                        $total_gamer_lan = $controlador_totais_aux;
                        if($rs_transacoes_row['rfcb_numero_de_dias_max'] > 1) {
                            // Dia 1
                            $data1 = $rs_transacoes_row['rfcb_data_registro'];
                            $numDias = $rs_transacoes_row['rfcb_numero_de_dias_max'];
                            $total_gamer_lan = calcularValorEsperado($data1,$numDias, $tf_v_forma_pagamento);
                        }
                        $valor_a_receber = $rs_transacoes_row['sve']-$total_gamer_lan;
                        echo number_format($valor_a_receber, 2, ',', '.');
                        $total_saldo_receber += $valor_a_receber;
                        $saldo_receber_acumulado += $valor_a_receber;
?>
                        </td>
                        <td style="text-align: center;" rowspan="<?php echo $rs_transacoes_row['rfcb_numero_de_dias_max'];?>">
                            <?php echo number_format($saldo_receber_acumulado, 2, ',', '.');  ?>
                        </td>
                        <td class="texto" align="right" rowspan="<?php echo $rs_transacoes_row['rfcb_numero_de_dias_max'];?>" valign="middle">
                            <?php echo number_format($taxa_esperada, 2, ',', '.');?>
                        </td>
                        <td class="texto" align="right" rowspan="<?php echo $rs_transacoes_row['rfcb_numero_de_dias_max'];?>" valign="middle">
                            <input type="text" name="rfcb_taxa_extrato[<?php echo $rs_transacoes_row['rfcb_id_max'];?>]" id="rfcb_taxa_extrato[<?php echo $rs_transacoes_row['rfcb_id_max'];?>]" value="<?php echo number_format($rs_transacoes_row['ste'], 2, ',', '.');?>" class="form" size="8" maxlength="8">
                        </td>
                        <td class="texto destaque1" align="right" rowspan="<?php echo $rs_transacoes_row['rfcb_numero_de_dias_max'];?>" valign="middle">
                            <?php echo number_format($esperado, 2, ',', '.');?>
                        </td>
                        <td style="text-align: center;" rowspan="<?php echo $rs_transacoes_row['rfcb_numero_de_dias_max'];?>">
<?php 
                                $total_diferenca += $esperado;
                                $diferenca_acumulada += $esperado;
                                echo number_format($diferenca_acumulada, 2, ',', '.'); 
?>
                        </td>
                        <td class="texto" align="right" rowspan="<?php echo $rs_transacoes_row['rfcb_numero_de_dias_max'];?>" valign="middle">
                            <input type="text" name="rfcb_comentario[<?php echo $rs_transacoes_row['rfcb_id_max'];?>]" id="rfcb_comentario[<?php echo $rs_transacoes_row['rfcb_id_max'];?>]" value="<?php echo $rs_transacoes_row['rfcb_comentario_max'];?>" title="<?php echo $rs_transacoes_row['rfcb_comentario_max'];?>" class="form" size="8">
                        </td>
                        <td align="center" rowspan="<?php echo $rs_transacoes_row['rfcb_numero_de_dias_max'];?>" valign="middle">
<?php
                                if($irows>1) {
                                    echo "<img src='/images/add.gif' width='16' height='16' border='0' alt='Juntar com linha acima.' title='Juntar com linha acima.' style='cursor:pointer;cursor:hand;' onClick='agrupaDatas(\"{$rfcb_id_max_anterior},{$rs_transacoes_row['rfcb_id_max']}\");'>";
                                }

                                $rfcb_id_max_anterior = $rs_transacoes_row['rfcb_id_max'];
                                $controlador_row_span = $rs_transacoes_row['rfcb_numero_de_dias_max'];

                                if($controlador_row_span > 1) {
                                    if($irows>1) echo"<br>";
?>
                                    <img src="/images/excluir.gif" width="16" height="16" border="0" alt="Separa as linhas." title="Separa as linhas." style="cursor:pointer;cursor:hand;" onClick="Dreload('<?php echo $rs_transacoes_row['rfcb_id_max'];?>');">
<?php
                                }//end if($controlador_row_span > 1)
                                else $controlador_totais_aux = 0;

                            }//end if($controlador_row_span == 1)
                            else {
                                if($controlador_row_span == 2) {
                                    $controlador_totais_aux = 0;
                                }

                                $controlador_row_span--;
                            }
?>
                        </td>
                    </tr>
<?php
                }
            }//end if($rs_transacoes_all)

            if($irows==0) {
?>
                <tr>
                    <td class="texto" align="center" colspan="18"><font color="#FF0000">Não foram encontrados registros para os valores escolhidos (2)</font></td>
                </tr>
<?php
            } else {
?>
                <tr class="lista_pagamento_total bg-branco text-right trListagem">
                    <td><!--Data Venda --></td>
                    <td><!--Qtde. Venda GAMER--><strong><?php echo $total_venda_gamer; ?></strong></td>
                    <td><!--Backoffice Venda R$ GAMER--><strong><?php echo number_format($total_bo_venda_gamer, 2, ',', '.'); ?></strong></td>
                    <td><!--Qtde. Venda LAN--><strong><?php echo number_format($total_venda_lan, 0, ',', '.'); ?></td>
                    <td><!--Backoffice Venda R$ LAN --><strong><?php echo number_format($total_bo_venda_lan, 2, ',', '.'); ?></strong></td>
<?php
                        if ($tf_v_forma_pagamento == '5') {
                            echo '<td><strong><!--Arquivo Depósitos Concilados-->' . number_format($total_arq_dep_conc, 2, ',', '.') . '</strong></td>';
                        }
?>
                    <td><!--Qtde. Venda GAMER+LAN--><strong><?php echo number_format($total_venda_gamer_lan, 0, ',', '.');?></strong></td>
                    <td><!--Valor Esperado R$ GAMER+LAN--><strong><?php echo number_format($total_esperado_gamer_lan, 2, ',', '.'); ?></strong></td>
                    <td><!--Data Extrato--></td>
                    <td><!--Valor Extrato venda Internet--></td>
                    <td><!--Saldo a Receber--></td>
                    <td><!--Saldo a Receber Acumulado--><strong><?php echo number_format(($total_saldo_receber), 2, ',', '.'); ?></strong></td>
                    <td><!--Taxa Esperada--></td>
                    <td><!--Taxa Extrato--></td>
                    <td><!--Diferença--></td>
                    <td><!--Diferença Acumulada--><strong><?php echo number_format($total_diferenca, 2, ',', '.'); ?></strong></td>
                </tr>
<?php
            }
        }//end if($rs_transacoes) 
        else {
?>
            <tr>
                <td class="texto" align="center" colspan="7"><font color='#FF0000'>Não foram encontrados registros para os valores escolhidos</font></td>
            </tr>
<?php
        }
?>
    </table>
<div><div><div>
<br>
<table class="table txt-preto fontsize-pp">
    <tr bgcolor="F5F5FB" align="center">
        <td class="texto">
            <input type="button" name="btSalvar" id="btSalvar" value="SALVAR" class="btn btn-sm btn-info" onClick="SaveAll();">
            <input type="button" name="btDownload" value="DOWNLOAD" class="btn btn-sm btn-info btn-download" onClick="javascript:;">
        </td>
    </tr>
    <tr align="center">
        <td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td>
    </tr>
</table>

</div>
</form>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>
<?php
function get_dia_da_semana($dow) {
    switch($dow) {
        case 0:
            $sout = "Dom";
            break;
        case 1:
            $sout = "2aF";
            break;
        case 2:
            $sout = "3aF";
            break;
        case 3:
            $sout = "4aF";
            break;
        case 4:
            $sout = "5aF";
            break;
        case 5:
            $sout = "6aF";
            break;
        case 6:
            $sout = "Sab";
            break;
        default:
            $sout = "???";
            break;
    }
    return $sout;
}
?>