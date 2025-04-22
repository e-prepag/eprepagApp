<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once DIR_INCS . "gamer/constantesPinEpp.php";
require_once '../../public_html/sys/includes/language/eprepag_lang_pt.inc.php';

set_time_limit ( 3000 ) ;

$time_start = getmicrotime();


$cor1 = "#F5F5FB";
$cor2 = "#F5F5FB";
$cor3 = "#FFFFFF";

$days_for_mean = 7;
$prazo_vermelho_vezes = 1;
$prazo_amarelo_vezes = 2;

$ChkTreinamento = "1";
if(!$fcanal) $fcanal = 's';

if($_SESSION["tipo_acesso_pub"]=='PU') {
        $fopr = $_SESSION["opr_codigo_pub"];
        $Submit = "Buscar";
}

if(!$ncamp) $ncamp = 'opr_nome';
if(!$nscamp) $nscamp = 'ec_uf, pin_valor';


$sql = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_pin_online = 0 ";
if(!$ChkTreinamento && $fopr <> 78) $sql .=" and (opr_codigo <> 78) "; 
$sql .= " order by opr_nome";
$resopr = pg_exec($connid, $sql);

if($Submit){

	if($fopr){			
		$resopr_val = pg_exec($connid, "select opr_codigo from operadoras where opr_codigo='$fopr'");

                $pgopr_val = pg_fetch_array($resopr_val);
		$resval = pg_exec($connid, "select pin_valor from pins where opr_codigo='".$pgopr_val['opr_codigo']."'". (($fcanal=='s' || $fcanal=='p' || $fcanal=='r' )?" and pin_canal='".$fcanal."' ":""). " group by pin_valor order by pin_valor");

        } 
        else {
		$fvalor = '';
	}
	//Busca Operadoras exceto Brasil Telecom
	$sql = "select t1.opr_nome, t0.pin_valor, count(t0.pin_valor) as quantidade, (CASE WHEN t0.opr_codigo <> 78 THEN sum(t0.pin_valor) ELSE 0 END) as total_face, t0.opr_codigo, t0.pin_status, t1.opr_pedido_estoque_prazo as prazo_pedido ";
	$sql .= "from pins t0, operadoras t1 ";
	$sql .= "where t0.opr_codigo <> 32 and t1.opr_codigo <> 32 and t1.opr_pin_online = 0 ";
	$sql .= "and pin_status='1' ";
	if($fopr){ $sql .= "and (t0.opr_codigo='".$fopr."') and (t0.opr_codigo=t1.opr_codigo) "; }
	if($fvalor){ $sql .= "and (t0.pin_valor='".$fvalor."') "; }
	if($fcanal=='s' || $fcanal=='p' || $fcanal=='r'){ $sql .= "and (t0.pin_canal='".$fcanal."') "; }
	if(!$ChkTreinamento && $fopr <> 78) $sql .="and (t0.opr_codigo <> 78) ";
	if(!$fopr && !$fvalor){ $sql .= "and (t0.opr_codigo=t1.opr_codigo) "; }
	$sql .= "group by t1.opr_faturamento_ordem, t1.opr_nome, t0.pin_valor, t0.opr_codigo, t0.pin_status, t1.opr_pedido_estoque_prazo ";
		
	//Busca Brasil Telecom para fazer union com Operadoras
	$sqlBT  = "select 'BRASIL TELECOM ' || ec_uf, pin_valor, count(pin_valor) as qtde, sum(pin_valor) as total, 32, pin_status, 0 ";
	$sqlBT .= "from estab_comissao, pins ";
	$sqlBT .= "where (ec_codigo = pin_local) and opr_codigo = 32 and ec_opr_codigo = 32 ";
	$sqlBT .= "and pin_status='1' ";
	if($fvalor)	$sqlBT .= "and pin_valor='$fvalor' ";
	if($fuf) $sqlBT .= "and ec_codigo='$fuf' ";
	$sqlBT .= "group by ec_uf, pin_valor, pin_status ";

	//Se nao houver filtro, faz union da Brasil Telecom
//	if(!$fopr || $fopr == '32') $sql .= "union " . $sqlBT;

	$sql .= "order by opr_nome, ".$ncamp.", pin_valor, pin_status"; 
//			echo $sql;
	$resestat = pg_exec($connid, $sql);

	$sql = "select t1.opr_nome, t0.pin_valor, count(t0.pin_valor) as quantidade, (CASE WHEN t0.opr_codigo <> 78 THEN sum(t0.pin_valor) ELSE 0 END) as total_face, t0.opr_codigo, t1.opr_pedido_estoque_prazo as prazo_pedido ";
	$sql .= "from pins t0, operadoras t1 ";
	$sql .= "where t0.opr_codigo <> 32 and t1.opr_codigo <> 32 and t1.opr_pin_online = 0 ";
	// Se procurar por pins de POS apresenta apenas o status 7 - 'Vendido - POS', caso contrario apresenta apenas 3 - 'Vendido' e 6 - 'Vendido – Lan House'
	$sql .= " and ".(($fcanal=='p')?"pin_status='7'":"(pin_status='3' or pin_status='6' or pin_status='8')");
	$sql .= " and (pin_datavenda >='" . date("Y-m-d",strtotime("now -6 days")) . "' and pin_datavenda <='".date("Y-m-d",strtotime("now"))."') ";	
	if($fopr){ $sql .= "and (t0.opr_codigo='".$fopr."') and (t0.opr_codigo=t1.opr_codigo) "; }
	if($fvalor){ $sql .= "and (t0.pin_valor='".$fvalor."') "; }
	if($fcanal=='s' || $fcanal=='p' || $fcanal=='r'){ $sql .= "and (t0.pin_canal='".$fcanal."') "; }
	if(!$ChkTreinamento && $fopr <> 78) $sql .=" and (t0.opr_codigo <> 78) ";
	if(!$fopr && !$fvalor){ $sql .= " and (t0.opr_codigo=t1.opr_codigo) "; }
	$sql .= "group by t1.opr_nome, t0.pin_valor, t0.opr_codigo, t1.opr_pedido_estoque_prazo ";
	$sql .= "order by ".$ncamp.", pin_valor"; 

	$sqlMedia = $sql;
//echo "sqlMedia: " . $sqlMedia . "<br>";
	$rs_Media = pg_exec($connid, $sqlMedia);

	//Busca Brasil Telecom
/*	$sql  = "select ec_uf, count(pin_valor) as qtde, pin_valor, sum(pin_valor) as total ";
	$sql .= "from estab_comissao, pins ";
	$sql .= "where (ec_codigo = pin_local) and opr_codigo = 32 and ec_opr_codigo = 32 and pin_status='1'";
	if($fvalor)	$sql .= "and pin_valor='$fvalor' ";
	if($fuf) $sql .= "and ec_codigo='$fuf' ";
	$sql .= "group by ec_uf, pin_valor ";
	$sql .= "order by $nscamp desc";
echo $sql;

    $fp=fopen("../../debug.log","ab");
	fwrite($fp,"\r\nIn Pins_Qtde Like as:\r\n".$sql."\r\n");
	fclose($fp);
	$resoprbrt = pg_exec($connid, $sql);
*/	
	$varsel = "&fopr=$fopr&fvalor=$fvalor";

	if($fopr) {
		$sql="select ec_uf,ec_codigo from estab_comissao where ec_opr_codigo = $fopr order by ec_uf asc";
		$resec=pg_exec($connid,$sql);
	}
/*	
	$sql = "select t0.opr_codigo, pin_valor, count(pin_qtde) as total 
			from estat_venda t0,operadoras t1 
			where  
			t0.opr_codigo=t1.opr_codigo
			and opr_pin_online=0
			and opr_status='1'
			and t0.opr_codigo <> 78                                         
			and (trn_data >='".date("Y-m-d",strtotime("now -7 days"))."'  
			and trn_data <='".date("Y-m-d",strtotime("now -1 days"))."')
			group by t0.opr_codigo,pin_valor 
			order by t0.opr_codigo,pin_valor "; 
	$mediaopr=pg_exec($connid,$sql);
*/

	//Esgotados
	$sql  = "select distinct operadoras.opr_nome, pins.opr_codigo, pins.pin_valor from pins inner join operadoras ";
	$sql .= "on pins.opr_codigo = operadoras.opr_codigo ";
	$sql .= "where operadoras.opr_status='1' and operadoras.opr_pin_online = 0 ";
	if($fcanal=='s' || $fcanal=='p' || $fcanal=='r'){ $sql .= "and (pins.pin_canal='".$fcanal."') "; }
	if($Submit &&($fopr!="")){
		$sql .= "and operadoras.opr_codigo=".$fopr." ";
	}	
	$sql .= "and (not (operadoras.opr_codigo=17 and pins.pin_valor=26)) ";	// Não conta Mu Online - 26,00
	$sql .= "except ";
	$sql .= "select distinct operadoras.opr_nome, pins.opr_codigo, pins.pin_valor from pins inner join operadoras ";
	$sql .= "on pins.opr_codigo = operadoras.opr_codigo ";
	$sql .= "where operadoras.opr_status='1' and operadoras.opr_pin_online = 0 and pins.pin_status = '1' ";
	if($fcanal=='s' || $fcanal=='p' || $fcanal=='r'){ $sql .= "and (pins.pin_canal='".$fcanal."') "; }
	if($Submit &&($fopr!="")){
		$sql .= "and operadoras.opr_codigo=".$fopr." ";
	}
        $sql .= " order by opr_nome, pin_valor; ";
	
//echo "sqlEsgotados: $sql<br>";
    $rs_esgotados = pg_exec($connid, $sql);
}

?>
<html>
<head>

<link href="/sys/css/incCss.css" rel="stylesheet" type="text/css">
<title>E-Prepag</title>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="index.php">Estoque</a></li>
        <li class="active">Consulta de Estoque de Pins</li>
    </ol>
</div>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_PINS_PAGE_TITLE; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza top10">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong> (<?php echo LANG_PINS_AVARAGE_OF.' '.$days_for_mean.' '.LANG_PINS_DAYS; ?>)</span>
                    </div>
                    <div class="col-md-3 bg-cinza-claro">
                        <span class="glyphicon glyphicon-stop top0 txt-vermelho"></span> &nbsp;&lt;=<?php echo $prazo_vermelho_vezes.' '.LANG_PINS_TIME_1; ?>
                        <span class="glyphicon glyphicon-stop top0 txt-amarelo"></span> &nbsp;&lt;=<?php echo $prazo_amarelo_vezes.' '.LANG_PINS_TIME_2; ?>
                    </div>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-3">
                        <?php echo LANG_PINS_OPERATOR; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_VALUE; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_CARDS_CHANNEL; ?>
                    </div>
<?php  
                if($fopr == 32) 
                {
?>
                    <div class="col-md-2">
                        UF
                    </div>
<?php   
                }  
?>
                </div>
                <form name="form1" method="post" action="<?php  echo $PHP_SELF ?>">
                <div class="row txt-cinza">
                    <div class="col-md-3">
<?php 
                    if($_SESSION["tipo_acesso_pub"]=='PU') 
                    {
                        echo $_SESSION["opr_nome"];
                    } else 
                    {
?>
                    <select name="fopr" id="fopr" class="form-control">
                        <option value=""><?php echo LANG_PINS_ALL_OPERATORS; ?></option>
                        <?php  while ($pgopr = pg_fetch_array ($resopr)) { ?>
                        <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $fopr) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?></option>
                        <?php  } ?>
                    </select>
<?php 
                    } 
?>
                    </div>
                    <div class="col-md-3">
                        <select name="fvalor" id="fvalor" class="form-control">
                            <option value=""><?php echo LANG_PINS_ALL_VALUES; ?></option>
                            <?php if($resval){ while ($pgval = pg_fetch_array ($resval)) { ?>
                            <option value="<?php  echo $pgval['pin_valor'] ?>" <?php  if($pgval['pin_valor'] == $fvalor) echo "selected" ?>><?php  echo number_format($pgval['pin_valor'], 2, ',', '.') ?></option>
                            <?php  }} ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="fcanal" id="fcanal" class="form-control">
                            <option value="t" <?php  if(trim($fcanal) == 't' || trim($fcanal) == '' ) echo "selected"?>><?php echo LANG_PINS_ALL_CHANNELS; ?></option>
                            <option value="s" <?php  if(trim($fcanal) == 's') echo "selected"?>>Site</option>
                            <option value="p" <?php  if(trim($fcanal) == 'p') echo "selected"?>>POS</option>
                            <option value="r" <?php  if(trim($fcanal) == 'r') echo "selected"?>>Rede</option>
                        </select>
                    </div>
<?php  
                    if($fopr == 32) 
                    {
?>
                    <div class="col-md-2">
                        <select name="fuf" id="fuf" class="form-control">
                            <option value=""><?php echo LANG_PINS_ALL_STATES; ?></option>
                            <?php  while($pgec=pg_fetch_array($resec)) { ?>
                            <option value="<?php  echo $pgec['ec_codigo'] ?>" <?php  if(trim($fuf) == trim($pgec['ec_codigo'])) echo "selected"?>><?php  echo $pgec['ec_uf'] ?></option>
                            <?php  } ?>
                        </select>
                    </div>
<?php   
                    }  
?>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-12">
                            <button type="submit" name="Submit" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_PINS_SEARCH_2; ?></button>
                    </div>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table class="table bg-branco txt-preto fontsize-p" id="table">
                        <thead>
                          <tr class="bg-cinza-claro">
                            <th><strong><?php echo LANG_PINS_OPERATOR; ?></strong></th>
                            <th align="center"><strong><?php echo LANG_PINS_QUANTITY_1; ?></strong></th>
                            <th><strong><?php echo LANG_PINS_AVARAGE_DAILY_1; ?><br>(<?php echo LANG_PINS_AVARAGE_DAILY_2; ?>)</strong></th>
                            <th align="center"><strong><?php echo LANG_PINS_DURATION; ?></strong></th>
                            <th align="right"><strong><?php echo LANG_PINS_FACE_VALUE; ?></strong></th>
                            <th align="right"><strong><?php echo LANG_PINS_TOTAL_VALUE_STOCK; ?></strong></th>
                            <th align="center"><strong>%</strong></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
                    $cabecalho = "'".LANG_PINS_OPERATOR."','".LANG_PINS_QUANTITY_1."','".LANG_PINS_AVARAGE_DAILY_1." (".LANG_PINS_AVARAGE_DAILY_2.")','".LANG_PINS_DURATION."','".LANG_PINS_FACE_VALUE."','".LANG_PINS_TOTAL_VALUE_STOCK."'";

                    if(!$resestat || pg_num_rows($resestat) == 0)
                    { 
?>
                        <tr> 
                            <td colspan="7"align="center">
                                <strong><?php echo LANG_NO_DATA; ?></strong>
                            </td>
                        </tr>
<?php  
                    } else 
                    {
                        $a_total_geral = array();

                        // Resumo
                        $sout = "<br> <br> <b><font color='#666666' size='2' face='Arial, Helvetica, sans-serif'>".LANG_PINS_SUMMARY_LAST." ".$days_for_mean." ".LANG_PINS_DAYS."</b><table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'><tr><td align='center'><b><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>".LANG_PINS_OPERATOR."</b></td><td align='center'><b><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>".LANG_PINS_VALUE."</b></td><td align='center'><b><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>n pins</b></td><td align='center'><b><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>Total (R$)</b></td><td align='center'><b><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>".LANG_PINS_DAYS." (R$)</b></td><td align='center'><b><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>".LANG_PINS_PAGE_TITLE_2."/".LANG_DAY_2."</b></td></tr>\n";

                        while ($pgestat = pg_fetch_array($resestat)) 
                        {
                            $opr_nome_aux = $opr_nome;
                            $opr_codigo_aux = $opr_codigo;
                            $opr_nome = $pgestat['opr_nome'];
                            $opr_codigo = $pgestat['opr_codigo'];


                            if($pgestat['opr_codigo'] <> 78) 
                            {
                                $pin_total_valor += $pgestat['total_face'];
                            }
                            
                            $pin_total_qtde += $pgestat['quantidade'];
                            $total_reg ++;
                            $valor = 1;
                            
                            // Nova operadora
                            if($total_reg > 1 && $opr_nome_aux != $opr_nome)
                            {
                                // Barra de subtotal
?>
                            <tr class="bg-cinza-claro">
                                <td align="right"><b><?php echo $opr_nome_aux ?></b></td>
                                <td align="center"><b><?php echo $quantidade_opr ?></b></td>
                                <td align="center"><span title="<?php  echo $quantidade_total_opr." íte".(($quantidade_total_opr>1)?"ns":"m").", R$".number_format($venda_media_diaria_opr, 2, ',', '.')." (R$".number_format($venda_media_diaria_opr/$days_for_mean, 2, ',', '.')."/dia)" ?>"><b><?php  echo number_format($quantidade_total_opr/$days_for_mean, 2, ',', '.') ?></b></span></td>
                                <td colspan="2"> </td>
                                <td align="right"><b><?php  echo number_format($total_geral, 2, ',', '.'); ?></b></td>
                                <td align="center"><div id="<?php  echo "opr_".$opr_codigo_aux; ?>"> <?php  //echo "opr_".$opr_codigo_aux; ?></div></td>
                            </tr>
<?php
                                // reset $total_geral
                                if($opr_codigo_aux) 
                                    $a_total_geral[$opr_codigo_aux] = $total_geral;
                                
                                $total_geral = 0;
                                // Resumo
                                $sout .= "<tr bgcolor='#CCFFCC'><td align='right' colspan='2'><b><span title='Venda média: R$". number_format($venda_media_diaria_opr/(($quantidade_total_opr>0)?$quantidade_total_opr:1), 2, ',', '.')."/pin'><font color='".get_marqued_color($opr_nome_aux)."' size='1' face='Arial, Helvetica, sans-serif'>".$opr_nome_aux."</b></td><td align='center'><b><font color='".get_marqued_color($opr_nome_aux)."' size='1' face='Arial, Helvetica, sans-serif'>".$quantidade_total_opr."</b></td><td align='center'><b><font color='".get_marqued_color($opr_nome_aux)."' size='1' face='Arial, Helvetica, sans-serif'>". number_format($venda_media_diaria_opr, 2, ',', '.')."</b></td><td align='center'><b><font color='".get_marqued_color($opr_nome_aux)."' size='1' face='Arial, Helvetica, sans-serif'>". number_format($venda_media_diaria_opr/$days_for_mean, 2, ',', '.')."</b></td><td align='center'><b><font color='".get_marqued_color($opr_nome_aux)."' size='1' face='Arial, Helvetica, sans-serif'>". number_format($quantidade_total_opr/$days_for_mean, 2, ',', '.')."</b></td></tr>\n<tr bgcolor='FFFFFF'><td colspan='7' height='5'></td></tr>\n";

                                $operadora_subtotal[] = array('opr_nome' => $opr_nome_aux, 'opr_codigo' => $opr_codigo_aux, 'venda_media_diaria_opr' => $venda_media_diaria_opr);

                                $quantidade_total_opr = 0;
                                $venda_media_diaria_opr = 0;
                                $quantidade_opr = 0;
                            } 
?>
                            <tr class="trListagem"> 
                                <td><?php  echo $opr_nome ?></td>
                                <td align="center"><?php  echo $pgestat['quantidade'] ?></td>
<?php  
                            $executa=false;
                            $nrows = 0;
                            $quantidade_total = 0;
                            $pin_valor_this = "";

                            $quantidade_opr += $pgestat['quantidade'];

                            if($rs_Media && pg_num_rows($rs_Media) > 0) 
                                pg_fetch_array($rs_Media,0);
                            
                            $media = 0;
                            $pin_valor_this = $pgestat['pin_valor']; 

                            while($pgmediaopr=pg_fetch_array($rs_Media)) 
                            {
                                if($pgmediaopr['opr_nome']==$pgestat['opr_nome'] && $pgmediaopr['pin_valor']==$pgestat['pin_valor']) 
                                {

                                    $executa=true;
                                    $quantidade_total += $pgmediaopr['quantidade'];
                                    $nrows++;
                                }
                            }

                            // tem pins vendidos no período
                            if($executa) 
                            { 
                                $media = $quantidade_total/$days_for_mean;
                                $dias = floor($pgestat['quantidade']/(($media>0)?$media:1));
                                $prazo_pedido = $pgestat['prazo_pedido'];
                                if($pgestat['opr_codigo']!=78) {
                                        $venda_media_diaria = $quantidade_total*$pin_valor_this/$days_for_mean;
                                } else {
                                        $venda_media_diaria = 0;
                                }

                                // Subtotal da operadora
                                $quantidade_total_opr += $quantidade_total;
                                if($pgestat['opr_codigo']!=78) {
                                        $venda_media_diaria_opr += $quantidade_total*$pin_valor_this;
                                }
?>
                                <td align="center"><span title="<?php  echo $quantidade_total." íte".(($quantidade_total>1)?"ns":"m").", R$".number_format($venda_media_diaria*$days_for_mean, 2, ',', '.')." (R$".number_format($venda_media_diaria, 2, ',', '.')."/dia)" ?>"><?php  echo number_format($media, 2, ',', '.') ?></span></td>
                                <td align="center" class="<?php if($dias<=$prazo_vermelho_vezes*$prazo_pedido) echo "bg-vermelho"; else if($dias<=($prazo_amarelo_vezes*$prazo_pedido)) echo "bg-amarelo"; ?>"><span title="<?php  echo $prazo_pedido; ?> dias para entrega de estoque."><?php  echo $dias; ?> dia(s)</span></td>
<?php  	
                            } else { // não tem pins vendidos no período -> deixa em branco as colunas "Média Diária (Última Semana)" e "Duração"
?>
                                <td> </td>
                                <td> </td>
<?php  	
                            } 
?>
                                <td align="right"><?php  echo number_format($pin_valor_this, 2, ',', '.');?> </td>
                                <td align="right"><?php  echo number_format($pgestat['total_face'], 2, ',', '.'); ?></td>
                                <td> </td>
                            </tr>
<?php 
                            // soma para pins com vendas e sem => total_geral contem o estoque de pins
                            $total_geral += $pgestat['total_face'];
                            // Resumo
                            $sout .= "<tr><td><font color='".get_marqued_color($opr_nome)."' size='1' face='Arial, Helvetica, sans-serif'>".$opr_nome."</td><td align='center'><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>".number_format($pgestat['pin_valor'], 2, ',', '.')."</td><td align='center'><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>".$quantidade_total."</td><td align='center'><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>". (($quantidade_total>0)?number_format($venda_media_diaria*$days_for_mean, 2, ',', '.'):"0")."</td><td align='center'><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>". (($quantidade_total>0)?number_format($venda_media_diaria, 2, ',', '.'):"0")."</td><td align='center'><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>". number_format($media, 2, ',', '.')."</td></tr>\n";

                            if ($cor1==$cor2) {$cor1=$cor3;} else {$cor1=$cor2;} 			  
                        }
                        
                        // Barra de subtotal (para a última operadora listada)
                        $total_geral += $pgestat['total_face'];
                        $opr_nome_aux = $opr_nome;
                        $opr_codigo_aux = $opr_codigo;
?>                        
                        <tr class="bg-cinza-claro">
                            <td align="right"><b><?php echo $opr_nome_aux ?></b></td>
                            <td align="center"><b><?php echo $quantidade_opr ?></b></td>
                            <td align="center"><span title="<?php  echo $quantidade_total_opr." íte".(($quantidade_total_opr>1)?"ns":"m").", R$".number_format($venda_media_diaria_opr, 2, ',', '.')." (R$".number_format($venda_media_diaria_opr/$days_for_mean, 2, ',', '.')."/dia)" ?>"><b><?php  echo number_format($quantidade_total_opr/$days_for_mean, 2, ',', '.') ?></b></span></td>
                            <td colspan="2"></td>
                            <td align="right"><b><?php  echo number_format($total_geral, 2, ',', '.'); ?></b></td>
                            <td align="center"><div id="<?php  echo "opr_".$opr_codigo_aux; ?>"><?php  //echo "opr_".$opr_codigo_aux; ?></div></td>
                        </tr>
<?php
                        $operadora_subtotal[] = array('opr_nome' => $opr_nome_aux, 'opr_codigo' => $opr_codigo_aux, 'venda_media_diaria_opr' => $venda_media_diaria_opr);
                        if($opr_codigo_aux) 
                            $a_total_geral[$opr_codigo_aux] = $total_geral;
?>
                        <tr bgcolor="F0F0F0">
                            <td colspan="7" height="10"></td>
                        </tr>
<?php
                                // Resumo
                        $sout .= "<tr bgcolor='#CCFFCC'><td colspan='2' align='right'><b><span title='Venda média: R$". number_format($venda_media_diaria_opr/(($quantidade_total_opr>0)?$quantidade_total_opr:1), 2, ',', '.')."/pin'><font color='".get_marqued_color($opr_nome_aux)."' size='1' face='Arial, Helvetica, sans-serif'>".$opr_nome_aux."</span></b></td><td align='center'><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>".$quantidade_total_opr."</td><td align='center'><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>". number_format($venda_media_diaria_opr, 2, ',', '.')."</td><td align='center'><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>". number_format($venda_media_diaria_opr/$days_for_mean, 2, ',', '.')."</td><td align='center'><font color='#666666' size='1' face='Arial, Helvetica, sans-serif'>". number_format($quantidade_total_opr/$days_for_mean, 2, ',', '.')."</td></tr>\n<tr bgcolor='FFFFFF'><td colspan='6' height='5'></td></tr>\n";
                        $sout .= "</table>";
                        
                        if (!$valor) 
                        { 
?>
                        <tr> 
                            <td colspan="7"><?php echo LANG_NO_DATA; ?>.
                        </tr>
<?php  
                        } else 
                        {
?>
                            <tr> 
                                <td bgcolor="#E4E4E4"><strong><?php echo LANG_PINS_TOTAL; ?></strong></td>
                                <td bgcolor="#E4E4E4"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></div></td>
                                <td colspan="11" bgcolor="#E4E4E4"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></div>
                                <div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></div>
                                <div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($pin_total_valor, 2, ',', '.') ?></strong></div></td>
                            </tr>
                            <tr>
                                <td colspan="13" class="text-center"><a href="#" class="btn downloadCsv btn-info ">Download CSV</a></td>
                            </tr>
<?php
                        }

                        if($_SESSION["tipo_acesso_pub"]!='PU') 
                        {
                            echo "<script type='text/javascript'>\n";
                            //$stop = true;
                            foreach	($a_total_geral as $key => $val) 
                            {
                                echo "document.getElementById('opr_".$key."').innerHTML = '".number_format(100*$val/$pin_total_valor, 2, ',', '.')."%';\n";
                            }
                            echo "</script>\n";
			}
?>
                        <tr> 
                            <td colspan="12" bgcolor="#FFFFFF">
                                <strong><?php echo LANG_PINS_TOTAL_DATA_SCREEN; ?>: <?php  echo $total_reg ?></strong>
                            </td>
                        </tr>
                        <tr> 
                            <td colspan="12" bgcolor="#FFFFFF">
                              <strong><?php echo LANG_PINS_LAST_MSG; ?>.</strong>
                            </td>
                        </tr>
<?php
                    }
?>
                        </tbody>
                    </table>
                    <table width="50%" border='0' cellpadding="2" cellspacing="1" align="center">
                        <tr bgcolor="#FF0000"> 
                          <td colspan="3" align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_NO_PINS; ?></font></strong></td>
                        </tr>
<?php  
                    if (!$rs_esgotados || pg_num_rows($rs_esgotados) == 0)
                    {
?>
                        <tr bgcolor="#f5f5fb"> 
                            <td colspan="3" bgcolor="<?php  echo $cor1 ?>" align="center">
                                <font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br><?php echo LANG_NO_DATA; ?><br><br></strong></font>
                            </td>
                        </tr>
<?php  
                    } else 
                    {
?>
                        <tr bgcolor="#66668C"> 
                          <td><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_OPERATOR; ?></font></strong></td>
                          <td align="right"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_VALUE; ?></font></strong></td>
                        </tr>
<?php
                        $cor1 = "#F5F5FB"; $cor2 = "#F5F5FB"; $cor3 = "#FFFFFF";
                        while ($pgest = pg_fetch_array($rs_esgotados))
                        {
                            $sql = "select count(*) as total_lan
                                    from tb_dist_operadora_games_produto dogp 
                                    inner join tb_dist_operadora_games_produto_modelo dogpm on dogp.ogp_id =dogpm.ogpm_ogp_id
                                    where dogp.ogp_opr_codigo = ".$pgest['opr_codigo']."
                                            and dogpm.ogpm_valor = ".$pgest['pin_valor']." 
                                            and dogpm.ogpm_ativo = 1
                                            and dogp.ogp_pin_request=0;";
                            $rs_count_lan = pg_exec($connid, $sql);
                            $rs_count_lan_row = pg_fetch_array($rs_count_lan);
                            $sql = "select count(*) as total_gamer
                                    from tb_operadora_games_produto ogp
                                    inner join tb_operadora_games_produto_modelo ogpm on ogp.ogp_id =ogpm.ogpm_ogp_id
                                    where ogp.ogp_opr_codigo = ".$pgest['opr_codigo']."
                                            and ogpm.ogpm_valor = ".$pgest['pin_valor']."
                                            and ogpm.ogpm_ativo = 1;";
                            $rs_count_gamer = pg_exec($connid, $sql);
                            $rs_count_gamer_row = pg_fetch_array($rs_count_gamer);
                                                
                                                //echo "Publisher ".$pgest['opr_nome']."GAMER:".$rs_count_gamer_row['total_gamer']."  LAN:".$rs_count_lan_row['total_lan']."<br>";
                                                if($rs_count_gamer_row['total_gamer'] != 0 || $rs_count_lan_row['total_lan'] != 0) {
                                                    
                                                    if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;}
                        ?>
					  <tr bgcolor="#f5f5fb"> 
						<td bgcolor="<?php  echo $cor1 ?>"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
						  <?php  echo $pgest['opr_nome'] ?></font></td>
						<td bgcolor="<?php  echo $cor1 ?>"><div align="right"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
							<?php  echo number_format($pgest['pin_valor'], 2, ',', '.') ?> </font></div></td>
					  </tr>
              <?php
                                                } //end if($rs_count_gamer_row['total_gamer'] == 0 && $rs_count_lan_row['total_lan'] == 0)
                                                
                                    } //end while ($pgest = pg_fetch_array($rs_esgotados))
                                    
                            } //end else  do if (!$rs_esgotados || pg_num_rows($rs_esgotados) == 0)
               ?>
					 
            </table>



            </form>
	
<?php
	if ($_SESSION["tipo_acesso_pub"]!='PU')  {
		echo $sout;
}
?>	  
			
          </td>
        </tr>
        <tr> 
          <td bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666">Ellapsed time: <?php  echo number_format(getmicrotime() - $time_start, 2, '.', '.') ?>s 
            </font></td>
        </tr>

      </table>
   </td>
  </tr>
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
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:""});
});
</script>

 <?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
 ?>
</body>
</html>
<?php

function get_marqued_color($opr_nome) {
//	$scolor = ( ($opr_nome=="ONGAME")?"#0000FF": ( ($opr_nome=="HABBO HOTEL")?"": ( ($opr_nome=="GPotato")?"#FF0000":"#666666") ) );

	switch($opr_nome) {
		case "ONGAME":
			$scolor = "#0000FF";
			break;
		case "HABBO HOTEL":
			$scolor = "#009900";
			break;
		case "GPotato":
			$scolor = "#CC0066";
			break;
		case "PayByCash":
			$scolor = "#FF9900";
			break;
		case "Webzen":
			$scolor = "#CC00FF";
			break;
		case "Vostu":
			$scolor = "#FF0000";
			break;
		case "NDoors":
			$scolor = "#66CC66";
			break;
		default:
			$scolor = "#666666";
			break;
	}
	return $scolor;

}
?>
