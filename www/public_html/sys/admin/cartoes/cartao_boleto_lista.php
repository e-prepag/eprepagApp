<?php 
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
$pos_pagina = $seg_auxilar;

$time_start = getmicrotime();

if($_SESSION["tipo_acesso_pub"]=='PU') {
    $dd_operadora = $_SESSION["opr_codigo_pub"];
    $Submit = "Buscar";
}

if(!$ncamp) $ncamp = 'vc_data';

if(!$tf_data_inicial)
{
    $resdatainicio = pg_exec($connid, "select vc_data from dist_vendas_cartoes_tmp order by vc_data limit 1");
    if($pgdatainicio = pg_fetch_array ($resdatainicio)) 
    {
            $tf_data_inicial = substr($pgdatainicio['vc_data'],8,2)."/".substr($pgdatainicio['vc_data'],5,2)."/".substr($pgdatainicio['vc_data'],0,4);
    } else 
    {
            $tf_data_inicial = date('d/m/Y');
    }
    $today_data = date('d/m/Y');
    $iday = intval(substr($today_data,0,2));
    $imonth = intval(substr($today_data,3,2));
    $iyear = intval(substr($today_data,6,4));

    $tf_data_inicial = date('d/m/Y', mktime(0,0,0,$imonth,$iday,$iyear));
}
if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
if(!$inicial)          $inicial         = 0;
if(!$range)            $range           = 1;
if(!$ordem)            $ordem           = 0;
if($BtnSearch)         $inicial         = 0;
if($BtnSearch)         $range           = 1;
if($BtnSearch)         $total_table     = 0;

$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
$data_inicial_limite = '01/08/2004';
$FrmEnviar = 1;

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "/sys/imagens/proxima.gif";
$img_anterior = "/sys/imagens/anterior.gif";
$max          = 100; //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;


$resusuario = pg_exec($connid, "select ug_id, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)  WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)  END) as ug_nome_fantasia, (select count(*) from dist_vendas_cartoes_tmp vc where vc.vc_ug_id = ug.ug_id and vc_ativo='1') as n, ug.ug_tipo_cadastro from dist_usuarios_games ug where ug_ativo=1 and (select count(*) from dist_vendas_cartoes_tmp vc where vc.vc_ug_id = ug.ug_id and vc_ativo='1')>0 group by ug_id, ug_nome_fantasia, ug.ug_tipo_cadastro, ug.ug_nome order by ug_nome_fantasia");  // "and ug_usuario_cartao=1 "

/*
    if($dd_operadora)
    {			
            $res_opr_info = pg_exec($connid, "select opr_codigo, opr_nome, opr_pin_online from operadoras where opr_codigo=".$dd_operadora."");
            $pg_opr_info = pg_fetch_array($res_opr_info);

            $dd_operadora_nome = $pg_opr_info['opr_nome'];

            if($pg_opr_info['opr_pin_online'] == 0)
                    $resval = pg_exec($connid, "select pin_valor as valor from pins where opr_codigo='".$pg_opr_info['opr_codigo']."' group by pin_valor order by pin_valor");
            else
            {
                    $resval = pg_exec($connid, "select valor_fixo as valor from pin_valor_lista t0, pin_valor_fixo t1 where t0.valor_lista_cod = t1.valor_lista_cod and opr_codigo = ".$pg_opr_info['opr_codigo']." group by valor_fixo order by valor_fixo");
                    $res_opr_area = pg_exec($connid, "select oparea_codigo, area_nome from operadora_area where opr_codigo=".$pg_opr_info['opr_codigo']." order by oparea_codigo");
            }
    }
*/
if(!verifica_data($tf_data_inicial))
{
        $data_inic_invalida = true;
        $FrmEnviar = 0;
}


if(!verifica_data($tf_data_final))
{
        $data_fim_invalida = true;
        $FrmEnviar = 0;
}

if(qtde_dias($data_inicial_limite, $tf_data_inicial) < 0)
{
        $data_inicial_menor = true;
        $FrmEnviar = 0;
}

if($FrmEnviar == 1)
{
    $where_data = "";
    $where_valor = "";
    $where_opr = "";
    $where_estabelecimento = "";
    $where_ativo = "";

    if($tf_data_inicial && $tf_data_final) 
    {
            $data_inic = formata_data(trim($tf_data_inicial), 1);
            $data_fim = formata_data(trim($tf_data_final), 1); 
            $where_data = " and ((vc_data >= '".trim($data_inic)." 00:00:00') and (vc_data <= '".trim($data_fim)." 23:59:59')) "; 
    }

    if($dd_operadora) 
    {
        if($dd_operadora==13)
            $where_opr = " and ((vc_total_5k+vc_total_10k+vc_total_15k+vc_total_20k)>0) ";
        if($dd_operadora==17)
            $where_opr = " and (vc_total_mu_online>0) ";
    }
    if($dd_operadora=="") $dd_valor = "";
//echo "dd_valor: ".$dd_valor."<br>";

    if($dd_valor) 
    {
        if($dd_operadora==13) 
        {
            if($dd_valor==13)
                $where_valor = " and (vc_total_5k>0) ";
            elseif($dd_valor==25)
                $where_valor = " and (vc_total_10k>0) ";
            elseif($dd_valor==37)
                $where_valor = " and (vc_total_15k>0) ";
            elseif($dd_valor==49)
                $where_valor = " and (vc_total_20k>0) ";
        }
        if($dd_operadora==17)
            if($dd_valor==10)
                $where_valor = " and (vc_total_mu_online>0) ";
    }


    if($dd_ativo) 
    {
            $where_ativo = " and (vc_ativo='$dd_ativo') ";
    }
    if($dd_estabelecimento) 
    {
            $where_estabelecimento = " and (vc_ug_id=$dd_estabelecimento) ";
    }

    $estat  = "select vc.*, ug.ug_id, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia) WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)  END) as ug_nome_fantasia, ug.ug_tipo_cadastro, ug.ug_razao_social, (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_cnpj) WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_cpf) END) as ug_cpf_cnpj, ug_tel_ddi, ug_tel_ddd, ug_tel, ug_email from dist_vendas_cartoes_tmp vc left join dist_usuarios_games ug on vc.vc_ug_id = ug.ug_id ";
    if($where_data||$where_valor||$where_opr)
        $estat  .= " where 1=1 ".$where_data." ".$where_valor." ".$where_opr." ".$where_estabelecimento." ".$where_ativo." ";		

    $res_count = pg_query($estat);
    $total_table = pg_num_rows($res_count);

    if($ncamp=="vc_data") 
    {
        $estat .= " order by vc_data "; //" order by vc_data::date "; 
    } else 
    {
        $estat .= " order by ".$ncamp; 
    }

    if($ordem == 0)
    {
        $estat .= " desc ";
        if($ncamp!="vc_id") $estat .= ", vc_id desc, vc_id_seq desc "; else $estat .= ", vc_id_seq desc "; 
        $img_seta = "/sys/imagens/seta_down.gif";	
    }
    else
    {
        $estat .= " asc ";
        if($ncamp!="vc_id") $estat .= ", vc_id desc, vc_id_seq desc "; else $estat .= ", vc_id_seq desc "; 
        $img_seta = "/sys/imagens/seta_up.gif";
    }

    $valor_total_comissao_tela = 0;
    $valor_comissao_geral = 0;

//echo "Geral: ".$estat."<br>";
    $res_geral = pg_exec($connid, $estat);
    while($pg_geral = pg_fetch_array($res_geral))
    {
        $valor_ongame_comissao = 0;
        $valor_mu_comissao = 0;
        $tt_vc_comissao = $pg_geral['vc_comissao'];

        if(($dd_operadora==13) || ($dd_operadora=="")) {
                $valor_ongame_comissao = ($pg_geral['vc_total_5k']*13 + $pg_geral['vc_total_10k']*25 + $pg_geral['vc_total_15k']*37 + $pg_geral['vc_total_20k']*49) * (100-$tt_vc_comissao)/100;
        }

        if(($dd_operadora==17) || ($dd_operadora=="")) {
                $valor_mu_comissao = ($pg_geral['vc_total_mu_online']*10)*(100-$tt_vc_comissao)/100;
        }

        $valor_frete = $pg_geral['vc_frete'];
        $valor_comissao_geral += $valor_ongame_comissao + $valor_mu_comissao + $valor_frete;
//echo "$qtde_ongame, $qtde_mu (".$pg_geral['vc_total_5k'].") -> $qtde_geral<br>";
    }

    $estat .= " limit ".$max; 
    $estat .= " offset ".$inicial;
}
else
    $estat = "select est_codigo from estabelecimentos where est_codigo = 0";
		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
$sql_transform=$estat;

//echo "Subtotal: $estat<br>";

$resestat = pg_exec($connid, $estat);

if($max + $inicial > $total_table)
        $reg_ate = $total_table;
else
        $reg_ate = $max + $inicial;

$varsel  = "&dd_operadora=$dd_operadora&tf_data_inicial=$tf_data_inicial&tf_data_final=$tf_data_final&dd_valor=$dd_valor";
$varsel .= "&dd_estabelecimento=$dd_estabelecimento";
		
?>
<html>
<head>
<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<title>E-Prepag</title>
<script language='javascript' src='/sys/js/popcalendar.js'></script>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

//-->
</script>

<script language=javascript>
// http://bytes.com/forum/thread598274.html
function selectNode (node)
{
	var selection, range, doc, win;

	if ((doc = node.ownerDocument) && (win = doc.defaultView) && typeof win.getSelection != 'undefined' && typeof doc.createRange != 'undefined' && (selection = window.getSelection()) && typeof selection.removeAllRanges != 'undefined')	{
		range = doc.createRange();
		range.selectNode(node);
		selection.removeAllRanges();
		selection.addRange(range);
	}
	else if (document.body && typeof document.body.createTextRange != 'undefined' && (range = document.body.createTextRange())) {
		range.moveToElementText(node);
		range.select();
	}
}

function clearSelection ()
{
	if (document.selection)
		document.selection.empty();
	else if (window.getSelection)
		window.getSelection().removeAllRanges();
}
/*
function CopyToClipboard(){
   document.Form1.txtArea.focus();
   document.Form1.txtArea.select(); 
   CopiedTxt = document.selection.createRange();
   CopiedTxt.execCommand("Copy");
}

function PasteFromClipboard(){ 
   document.Form1.txtArea.focus();
   PastedText = document.Form1.txtArea.createTextRange();
   PastedText.execCommand("Paste");
} 
*/

</script>

</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_CARDS_PAGE_TITLE_4; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_CARDS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <form name="formlista" method="post">
                <input type="hidden" name="op" id="op" value="">
                <input type="hidden" name="id" id="id" value="">
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_SALES_START_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_SALES_END_DATE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input alt="Calendário" name="tf_data_final" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_CARDS_OPERATOR; ?></span>
                        </div>
                        <div class="col-md-3">
<?php
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            echo $_SESSION["opr_nome"];
?>
                            <input type="hidden" name="dd_operadora" id="dd_operadora" value="<?=$dd_operadora?>">
<?php
                        } else 
                        {
?>
                            <select name="dd_operadora" id="dd_operadora" class="form-control" onChange="document.formlista.dd_valor.value='';document.formlista.submit()">
                                <option value=""<?php if(($dd_operadora!=13) && ($dd_operadora!=17)) echo "selected" ?>><?php echo LANG_CARDS_ALL_OPERATORS; ?></option>
                                <option value="13"<?php if($dd_operadora==13) echo "selected" ?>>ONGAME (13)</option>
                                <option value="17"<?php if($dd_operadora==17) echo "selected" ?>>MU ONLINE (17)</option>
                            </select>
<?php
                        } 
?>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_POS_VALUE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_valor" id="dd_valor" class="form-control" onChange="document.formlista.submit()">
<?php 
                        if($dd_operadora==17) 
                        {
?>
                                <option value=""<?php if($dd_valor!=10) echo "selected" ?>><?php echo LANG_CARDS_ALL_VALUES; ?></option>
                                <option value="10"<?php if($dd_valor==10) echo "selected" ?>>R$ 10,00</option>
<?php
                        }
                            
                        if($dd_operadora==13) 
                        { 
?>
                                <option value=""<?php if(($dd_valor!=13) && ($dd_valor!=25) && ($dd_valor!=37) && ($dd_valor!=49)) echo "selected" ?>><?php echo LANG_CARDS_ALL_VALUES; ?></option>
                                <option value="13"<?php if($dd_valor==13) echo "selected" ?>>R$ 13,00</option>
                                <option value="25"<?php if($dd_valor==25) echo "selected" ?>>R$ 25,00</option>
                                <option value="37"<?php if($dd_valor==37) echo "selected" ?>>R$ 37,00</option>
                                <option value="49"<?php if($dd_valor==49) echo "selected" ?>>R$ 49,00</option>
<?php
                        } 
                            
                        if(($dd_operadora!=13) && ($dd_operadora!=17)) 
                        {
?>
                                <option value=""><?php echo LANG_CARDS_ALL_VALUES; ?></option>
<?php 
                        } 
?>
                            </select>
                        </div>
                        <div class="row txt-cinza top10">
                            <div class="col-md-12 txt-azul-claro top10 text-left">
                                <p><?php echo LANG_CARDS_MSG_HELP; ?></p>
                            </div>
                        </div>
<?php                        
                        if (($data_inic_invalida == true)||($data_fim_invalida == true)||($data_inicial_menor == true))
                        {
?>
                        <div class="row txt-cinza top10 ">
                            <span class="txt-vermelho bg-cinza-claro espacamento">
<?php
                            if($data_inic_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b><?php echo LANG_CARDS_START_DATE ?></b></font>";
                            if($data_fim_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_CARDS_END_DATE."</b></font>";
                            if($data_inicial_menor == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_CARDS_START_END_DATE."</b></font>";
?>
                            </span>
                        </div>
<?php   
                        } 
?>
                    </div>
                    <div class="row txt-cinza top10 text-right">
                        <div class="col-md-12">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_CARDS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_CARDS_SEARCH_2;?></button>
                        </div>
                    </div>
                </form>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                            <thead>
                                <tr class="bg-cinza-claro">
                                    <th align="center"><strong>ID</strong></th>
                                    <th align="center"><strong><?php echo LANG_CARDS_NUMBER_HEADING; ?></strong></th>
                                    <th align="center"><strong><?php echo LANG_CARDS_DATE; ?></strong></th>
                                    <th align="center"><strong><?php echo LANG_CARDS_VENCIMENTO; ?></strong></th>
                                    <th align="center"><strong><?php echo LANG_CARDS_ESTABLISHMENT ?></strong></th>
                                    <th align="center"><strong><?php echo LANG_POS_VALUE; ?> (R$)<br><font size="1">(- <?php echo LANG_CARDS_COMMISSION_FREIGHT; ?>)</strong></th>
                                </tr>
                            </thead>
<?php
                            $_SESSION['sqldata']=$sql_transform;
                            
                            if($total_table > 0) 
                            {                            
?>                                
                            <tr>
                                <th colspan="6">
<?php 
                                    echo LANG_SHOW_DATA.' '; 
?>
                                    <strong><?php echo $inicial + 1 ?></strong> 
                                    <?php echo ' '.LANG_TO.' '; ?><strong><?php echo $reg_ate ?></strong><?php echo ' '.LANG_FROM.' ' ?><strong><?php echo $total_table ?></strong>
                                </th>
                            </tr>
                            <tbody>
<?php
                            $cor1 = $query_cor1;
                            $cor2 = $query_cor1;
                            $cor3 = $query_cor2;

                                while ($pgrow = pg_fetch_array($resestat))
                                {
                                    $valor = true;
                                    $valor_ongame_comissao = 0;
                                    $valor_mu_comissao = 0;
                                    $tt_vc_comissao = $pgrow['vc_comissao'];

                                    if(($dd_operadora==13) || ($dd_operadora=="")) 
                                    {
                                        $valor_ongame_comissao = ($pgrow['vc_total_5k']*13 + $pgrow['vc_total_10k']*25 + $pgrow['vc_total_15k']*37 + $pgrow['vc_total_20k']*49) * (100-$tt_vc_comissao)/100;
                                    }
                                    if(($dd_operadora==17) || ($dd_operadora=="")) 
                                    {
                                        $valor_mu_comissao = ($pgrow['vc_total_mu_online']*10)*(100-$tt_vc_comissao)/100;
                                    }

                                    $valor_frete = $pgrow['vc_frete'];
                                    $valor_total_comissao_tela += ($valor_ongame_comissao + $valor_mu_comissao + $valor_frete);
?>
                                <tr class="trListagem"> 
                                    <td align="center"><?php echo $pgrow['vc_id'] ?></td>
                                    <td align="center">
                                        <span onMouseOver="selectNode(this);" OnMouseOut="clearSelection(this);">
<?php 
                                    if(($dd_operadora==13) || ($dd_operadora=="")) 
                                    {
                                        if($pgrow['vc_id_seq']!="0") 
                                            echo $pgrow['vc_id_seq']; 
                                        else 
                                        {
                                            if(strlen($pgrow['vc_id_seq_str'])>0) 
                                                echo "<b>".$pgrow['vc_id_seq_str']."</b>"; 
                                            else 
                                                echo "(vazio)";
                                        }
                                    } 
                                    else 
                                        echo "-"; 
?>
                                        </span>
                                    </td>
                                    <td align="center"><?php echo formata_data($pgrow['vc_data'], 0) ?></td>  
                                    <td align="center">
                                        <span onMouseOver="selectNode(this);" OnMouseOut="clearSelection(this);" title="Data de vencimento: <?php echo formata_data($pgrow['vc_venc'],0); ?>"><?php echo substr($pgrow['vc_venc'],8,2).substr($pgrow['vc_venc'],5,2).substr($pgrow['vc_venc'],0,4) ?></span>
                                    </td>
                                    <td>
                                        <?php echo (strlen($pgrow['ug_nome_fantasia'])>0)?("<span onmouseover=\"selectNode(this);\" OnMouseOut=\"clearSelection(this);\">".$pgrow['ug_nome_fantasia']."</span> (".$pgrow['ug_tipo_cadastro'].") (ID: ".$pgrow['ug_id']).")":"--"; ?>
                                        <table border='1'>
                                            <tr>
                                                <td>
                                                    <table border='0' class="txt-preto fontsize-p">
                                                        <tr>
                                                            <td><?php echo LANG_CARDS_FULL_NAME; ?></td>
                                                            <td><span onMouseOver="selectNode(this);" OnMouseOut="clearSelection(this);"><?=$pgrow['ug_razao_social']?></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?=(($pgrow['ug_tipo_cadastro']=="PJ")?"CNPJ":"CPF")?></td>
                                                            <td><span onMouseOver="selectNode(this);" OnMouseOut="clearSelection(this);"><?=$pgrow['ug_cpf_cnpj']?></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php echo LANG_CARDS_PHONE; ?></td>
                                                            <td><?=$pgrow['ug_tel_ddi']?>-<?=$pgrow['ug_tel_ddd']?>-<?=$pgrow['ug_tel']?></td>
                                                        </tr>
                                                        <tr>
                                                            <td>E-mail</td>
                                                            <td><?=$pgrow['ug_mail']?></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align="center">
                                        <span onMouseOver="selectNode(this);" OnMouseOut="clearSelection(this);">
                                            <?php echo str_replace('.', '', number_format(($valor_ongame_comissao + $valor_mu_comissao + $valor_frete), 2, ',', '.')) ?></span>
                                    </td>
                                </tr>
<?php  
                                }
                                
                                $time_end = getmicrotime();
                                $time = $time_end - $time_start;
?>
                                <tr class="bg-cinza-claro"> 
                                    <td colspan="5"><strong><?php echo LANG_CARDS_SUBTOTAL; ?></strong></td>
                                    <td align="center"><strong><?php echo number_format($valor_total_comissao_tela, 2, ',', '.'); ?></strong></td>
                                </tr>
                                <tr> 
                                    <td colspan="5"><strong><?php echo LANG_ALL; ?></strong></td>
                                    <td align="center"><strong><?php echo number_format($valor_comissao_geral, 2, ',', '.'); ?></strong></td>
                                </tr>
<?php  
                                paginacao_query($inicial, $total_table, $max, '11', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
?>
                                <tr> 
                                    <td colspan="10" bgcolor="#FFFFFF"><?php echo $search_msg . number_format($time, 2, '.', '.') . $search_unit; ?></td>
                                </tr>
<?php
                            }else
                            {
?>
                                <tr>
                                    <td colspan="<?php echo $colspan?>"><strong><?php echo LANG_NO_DATA; ?>.</strong></td>
                                </tr>
<?php
                            }
?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="/js/table2CSV.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:""});
    
    var optDate = new Object();
        optDate.interval = 1;

        setDateInterval('tf_data_inicial','tf_data_final',optDate);
});
</script>

<!-- FIM CODIGO NOVO -->
<?php
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>
</body>
</html>