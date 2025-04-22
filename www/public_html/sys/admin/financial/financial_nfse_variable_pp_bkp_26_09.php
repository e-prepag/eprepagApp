<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

if($_SESSION["tipo_acesso_pub"]=='PU') {
	//redireciona
	$strRedirect = "/sys/admin/commerce/index.php";
	ob_end_clean();
	header("Location: " . $strRedirect);
	exit;
	?><html><body onLoad="window.location='<?php echo $strRedirect?>'"><?php
	exit;
	
	ob_end_flush();
}

$email_destino	= !empty($_POST['email_destino'])	? $_POST['email_destino']	: "glaucia@e-prepag.com.br";
$email_texto	= !empty($_POST['email_texto'])		? $_POST['email_texto']		: "Digite aqui o texto complementar do E-mail.";
$period			= "month";
$dd_mode		= "S";
require_once $raiz_do_projeto . "includes/gamer/constantesPinEpp.php"; 


if(!isset($dd_operadora_multi)){
    $dd_operadora_multi = array();
}

if(in_array("ALL", $dd_operadora_multi, true)) {
	$sqlopr = "select opr_codigo from operadoras where (opr_status = '1') and opr_codigo!=".$dd_operadora_EPP_Cash." and  opr_codigo!=".$dd_operadora_EPP_Cash_LH ." order by opr_nome";
	$resopraux = SQLexecuteQuery($sqlopr);
	unset($dd_operadora_multi);
	while ($resoprauxrow = pg_fetch_array ($resopraux)) {
		$dd_operadora_multi[] = $resoprauxrow['opr_codigo'];
	}
}

//echo "<pre>".print_r($dd_operadora_multi,true)."</pre>";

require_once $raiz_do_projeto . "includes/sys/inc_financial_variable.php";

$texto = "";
$time_start_stats = getmicrotime();

?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> <?php echo LANG_COMMISSIONS_FINANCIAL_TITLE; ?><?php if(strlen($_SESSION["opr_nome"])>0) echo " (".$_SESSION["opr_nome"].") ";?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="/js/jquery.js"></script>
        <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
        <link href="/sys/css/css.css" rel="stylesheet" type="text/css">
        <style>body{color:#737373;}</style>
</head>
<body>

<?php
$descricao = new DescriptionReport('nfse');
echo $descricao->MontaAreaDescricao();

//A variável empresa controla qual empresa estará selecionada no select de ínculo
//Seu valor inicial é ALL, indicando que a opção Todos os Vínculos estará selecionada por padrão
$empresa = 'ALL';

//Caso exista um post do vínculo, seu valor é colocado em empresa
if(isset($_POST["empresa"])){
    $empresa = $_POST["empresa"];
}

//Caso mude o vínculos selecionado, as operadoras selecionadas serão esquecidas
if(isset($_POST["mudou"])){
    $dd_operadora_multi = array();
}

//Caso não esteja selecionado todos os vínculos, uma condição é adicionada ao select das operadoras
if($empresa !== 'ALL'){
    $condicao = "and (opr_vinculo_empresa = " . $_POST["empresa"] . ")";
}else{
    $condicao = "";
}

if($_SESSION["tipo_acesso_pub"]=='PU') {
        $sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') ".$condicao." and (opr_codigo IN '".implode(',',$dd_operadora_multi)."') and opr_codigo!=".$dd_operadora_EPP_Cash." and  opr_codigo!=".$dd_operadora_EPP_Cash_LH ." order by opr_ordem";
        //echo $sqlopr;
} else {
        $sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') ".$condicao." and opr_codigo!=".$dd_operadora_EPP_Cash." and  opr_codigo!=".$dd_operadora_EPP_Cash_LH ." order by opr_nome"; //opr_ordem
}

$resopr = SQLexecuteQuery($sqlopr);



$bg_col_01 = "#FFFFFF";
$bg_col_02 = "#EEEEEE";
$bg_col = $bg_col_01;

$dd_operadora_nome = array();

if(count($dd_operadora_multi)>0) {
        $resopr_nome = SQLexecuteQuery("select opr_nome, opr_codigo,opr_vinculo_empresa from operadoras where (opr_status = '1') ".$condicao." and (opr_codigo IN ('".implode("','",$dd_operadora_multi)."')) and opr_codigo!=".$dd_operadora_EPP_Cash." and  opr_codigo!=".$dd_operadora_EPP_Cash_LH ." order by opr_ordem");
        while($pgopr_nome = pg_fetch_array ($resopr_nome)) { 
                $dd_operadora_nome[$pgopr_nome['opr_codigo']] = $pgopr_nome['opr_nome'];
                $dd_operadora_vinculo[$pgopr_nome['opr_codigo']] = $pgopr_nome['opr_vinculo_empresa'];
        } 
}

?>
<center>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><font color="#66CC00"><?php echo LANG_COMMISSIONS_FINANCIAL_PAGE_TITLE; ?> <?php echo "<font color='#3300FF'></font>"?> <?php echo LANG_STATISTICS_FOR_MONTH;
                        ?> </font><?php if(strlen($_SESSION["opr_nome"])>0) echo "<font color='#66CC33'>".$_SESSION["opr_nome"]."</font> ";?>- <?php echo LANG_COMMISSIONS_PAGE_TITLE_1; ?> (<?php echo get_current_date()?>)</strong>
                    </div>
                </div>
                <div class="row txt-cinza top10">
                    <div class="col-md-offset-6 col-md-6">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <form name="form1" id="form1" method="post" action="">
                <div class="row txt-cinza espacamento">
                    <div class="col-md-2">
                        <span class="pull-right"><?php echo "Vínculo"; ?></span>
                    </div>
                    <div class="col-md-3">
                        <select name="empresa" id="empresa" class="form-control">
                            <option class="vinculo" value="ALL" <?php if($empresa == 'ALL') echo selected; ?>>Todos os vínculos</option>
                            <option class="vinculo" value="<?php echo $IDENTIFICACAO_EMPRESA_PAGAMENTOS ?>" <?php if($empresa == strval($IDENTIFICACAO_EMPRESA_PAGAMENTOS)) echo selected; ?>>Epp Pagamentos</option>
                            <option class="vinculo" value="<?php echo $IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO ?>" <?php if($empresa == strval($IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO)) echo selected; ?>>Epp Administradora</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <span class="pull-right"><?php echo LANG_STATISTICS_OPERATOR; ?></span>
                    </div>
                    <div class="col-md-3">
<?php
                    if($_SESSION["tipo_acesso_pub"]=='PU') 
                    {
                        echo $_SESSION["opr_nome"]?>
                    <!--input type="hidden" name="dd_operadora_multi[]" id="dd_operadora_multi[]" value="<?php echo $dd_operadora_multi[0]?>"-->
<?php
                    } else 
                    {
?>

                        <select name="dd_operadora_multi[]" multiple size="5" id="dd_operadora_multi[]" class="form-control">
                            <option value="ALL"><?php echo LANG_STATISTICS_ALL_OPERATOR; ?></option>
                            <?php while ($pgopr = pg_fetch_array ($resopr)) { ?>
                            <option value="<?php echo $pgopr['opr_codigo'] ?>"<?php if(in_array($pgopr['opr_codigo'], $dd_operadora_multi, true)) echo " selected"; ?>><?php echo $pgopr['opr_nome']." (".$pgopr['opr_codigo'].")" ?></option>
                            <?php } ?>
                        </select>
<?php
                    } 
?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-offset-8 col-md-4">
                        <input type="button" name="executar" id="executar" value="Exibir Valores" onClick="javascript:document.form1.submit();" class="btn btn-success pull-left">
<?php
                    if($_SESSION['userlogin_bko']!='LUIZ'&&$_SESSION["tipo_acesso_pub"]=='AT') 
                    {
?>
                        <input type="button" name="atualizar" id="atualizar" value="Emitir NFSe" onClick="javascript:document.form1.action='financial_nfse_build.php';document.form1.submit();" class="btn btn-success tabela pull-right">
<?php
                    }
?>
                    </div>
                </div>
<br>
<?php
//sort($dd_operadora_multi);
//echo "<pre>".print_r($dd_operadora_multi,true)."</pre>";
?>
<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' class="table fontsize-13">
<?php
foreach($dd_operadora_multi as $line => $dd_operadora ) {
	$msg_spot = "";

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
	}

	if(!$dd_mode || ($dd_mode!='V')) {
		$dd_mode = "S";
	}
	$smode = $dd_mode;


	$bg_col_01 = "#FFFFFF";
	$bg_col_02 = "#EEEEEE";
	$bg_col = $bg_col_01;

	// Cria array de canais ====================================================================================
	//$aCanais = array("C", "E", "M", "L", "P");
	$aCanais = array("S", "L", "P","C");
	$aCanaisTodos = array("S", "C", "E", "M", "L", "P");
        
        //Canais Indiretos e Diretos
        $aCanaisDiretos = array("E", "M", "S");
        $aCanaisIndiretos = array("L", "P");

        $inicio = date('Y-m-01',mktime (0,0,0,date("m")-1,1,date("Y")));
        $fim = date('Y-m-d',mktime (0,0,0,date("m")-1,date("t",mktime (0,0,0,date("m")-1,1,date("Y"))),date("Y")));
        $sql_total_mes = "
                        select 
                                fp_channel,fp_publisher, sum(fp_number) as n, sum(fp_total) as total, sum(fp_comission) as comissao
                        from financial_processing 
                        where  fp_publisher = ".$dd_operadora."
                               and fp_date >= '".$inicio." 00:00:00' 
                               and fp_date <= '".$fim." 00:00:00'
                               and fp_date >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$dd_operadora.")
                               and fp_freeze=1
                        group by fp_channel,fp_publisher; ";
		
        //echo $sql_total_mes."<br>";
        $sql_min_date = " 
                        select to_char(min(fp_date),'DD/MM/YYYY') as menor_data
                        from financial_processing 
                        where  fp_publisher = ".$dd_operadora."
                               and fp_freeze=0";
        //echo $sql_min_date."<br>";
        $min_date = SQLexecuteQuery($sql_min_date);
	if($min_date) {
            $min_date_row = pg_fetch_array($min_date);
            echo "<font color='#BD3C2D'>A menor data do Publisher <font color='#282B79'>".$dd_operadora_nome[$dd_operadora]."</font> que ainda não possui fechamento é  ".$min_date_row['menor_data'].".</font><br>";
        }//end if($min_date)
        
//echo str_replace("\n","<br>",$sql_total_mes)."<br>";

	$vendas_total_mes = SQLexecuteQuery($sql_total_mes);
	if($vendas_total_mes) {
		
		$aNVendas = array();
		$aVendas = array();
		$aNVendasEComis = 0;
		$aVendasEComis = 0;
                $aNVendasTotal = 0;
                $aVendasTotal = 0;
		
		// Preenche Valores mensais e totais ================================================= 
		while ($vendas_total_mes_row = pg_fetch_array($vendas_total_mes)){
                        $aux_canal = converteChannel($vendas_total_mes_row['fp_channel']);
                        $aNVendas[$aux_canal] += $vendas_total_mes_row['n'];
                        $aVendas[$aux_canal] += $vendas_total_mes_row['total'];
                        $aNVendasTotal += $vendas_total_mes_row['n'];
                        $aVendasTotal += $vendas_total_mes_row['total'];
                        $aNVendasEComis += $vendas_total_mes_row['n'];
                        $aVendasEComis += $vendas_total_mes_row['comissao'];
		}//end while

		$texto = "";
		if($line==0) {
			$texto .= "<tr bgcolor='#CCFFCC'><td align='center' rowspan='3'>&nbsp;</td>\n<td rowspan='3' align='center'>&nbsp;</td>\n";
			$texto .= "<td colspan='".(count($aCanais)*2+2)."' align='center'><b><font color='#337ab7'>Vendas - Valor Bruto ( R$)</font></b></td>";
			$texto .= "<td rowspan='2' colspan='2' align='center'><font color='#337ab7'>&nbsp;Remunera&ccedil;&atilde;o Total&nbsp;<br> &nbsp;(POS+LAN+EPP)&nbsp;</font></td>";
			$texto .= "<td rowspan='2' align='center'><b><font color='#337ab7'>&nbsp;".LANG_COMMISSIONS_TOTAL_TRANSFER."&nbsp;</font></b></td></tr><tr>";
			for($j=0;$j<count($aCanais);$j++) {
				$texto .= "<th colspan='2' align='center' width='120' bgcolor='#CCFFCC'><b><font color='#337ab7'>".getChannelName($aCanais[$j])."</font></b></th>";
			}
			$texto .= "<th colspan='2' align='center' width='120' bgcolor='#CCFFCC'><b><font color='#337ab7' >".LANG_COMMISSIONS_TOTAL."</font></b></th>";
			$texto .= "</tr><tr>";
			for($j=0;$j<count($aCanais);$j++) {
				$texto .= "<td align='center' bgcolor='#CCFFCC'>n</td><td align='center' bgcolor='#CCFFCC'>".LANG_COMMISSIONS_TOTAL_SALES." (R$)</td>";
			}
			$texto .= "<td align='center' bgcolor='#CCFFCC'>n</td><td align='center' bgcolor='#CCFFCC'>".LANG_COMMISSIONS_TOTAL_SALES." (R$)</td>";
			$texto .= "<td align='center' bgcolor='#CCFFCC'>n</td><td align='center' bgcolor='#CCFFCC'>".LANG_COMMISSIONS_SALES." (R$)</td>";
			$texto .= "<td align='center' bgcolor='#CCFFCC'>".LANG_COMMISSIONS_TOTAL." (R$)</td>";
			$texto .= "</tr>";
		}
		$texto .= "<tr class='trListagem'><td align='center' bgcolor='#CCFFCC'><font color='#337ab7'> <b>&nbsp;".$dd_operadora_nome[$dd_operadora]."&nbsp;</b></font></td><td align='center' bgcolor='#CCFFCC'><font color='#337ab7'> ".date('F/Y',strtotime($inicio))."</font></td>\n";
								
		for($j=0;$j<count($aCanais);$j++) {
			
			//vendas e comissao
			$nvendasEComisPrj = $aNVendas[$aCanais[$j]];
			$vendasEComisPrj = $aVendas[$aCanais[$j]];
			//fim vendas e comissao

			$texto .= "<td align='center'$stitle_n>".number_format($nvendasEComisPrj, 0, ',', '')."</td>";
			$texto .= "<td align='center'$stitle_v>".number_format($vendasEComisPrj, 2, ',', '.')."</td>";
		}
		
		$stitle_n = "";
		$stitle_v = "";

		$texto .= "<td align='center'$stitle_n><font style='font-weight:bold;'>".number_format($aNVendasTotal, 0, ',', '')."</font></td>";
		$texto .= "<td align='center'$stitle_v><font style='font-weight:bold;'>".number_format($aVendasTotal, 2, ',', '.')."</font></td>";
		$texto .= "<td align='center'$stitle_n>".number_format($aNVendasEComis, 0, ',', '')."</td>";
		$texto .= "<td align='center'$stitle_v>".number_format($aVendasEComis, 2, ',', '.')."</td>";
		$texto .= "<td align='center'$stitle_v><font style='font-weight:bold;'>&nbsp;&nbsp;". number_format(($aVendasTotal-$aVendasEComis), 2, ',', '.')."&nbsp;&nbsp;</font></td>";
		$texto .= "</tr>";
		$bg_col = $bg_col_01;
		echo $texto;
		echo "<input type='hidden' name='valorNota[".$dd_operadora."]' id='valorNota[".$dd_operadora."]' value='".number_format($aVendasEComis, 2, '', '')."'>\n";
		echo "<input type='hidden' name='vinculoEmpresa[".$dd_operadora."]' id='vinculoEmpresa[".$dd_operadora."]' value='".$dd_operadora_vinculo[$dd_operadora]."'>\n";
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".$dd_operadora_nome[$dd_operadora]." - ".LANG_NOT_FOUND_2." &nbsp;</font></td></tr>";
	}
	flush();
}//end foreach
?>
</table>
<br>
<br>
<table border='0' cellpadding='0' cellspacing='1' width='80%' bordercolor='#cccccc' style='border-collapse:collapse;'>	
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT; ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
</table>
<input type="hidden" name="nfes_periodo" id="nfes_periodo" value="<?php echo mes_do_ano2(mktime (0,0,0,date("m")-1,1,date("Y")));?>">
</form>
            </div>
        </div>
    </div>
</div>
   <?php require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
</center>
    
    <script>
    
        jQuery(document).ready(function(){
                       
          
            //O input hidden "mudou" serve para verificar se o usuário selecionou um vínculou diferente
            //Se tiver selecionado, reseta as seleções de operadores
            $("#empresa").change(function(){
                 $("#form1").append("<input type='hidden' name='mudou' value='1'/>");
                 $("#form1").submit(); 
            });
           
        });
        
    </script>
</body>
</html>
<?php
// ===============================
function getChannelName($ch) {
        $sName = "???";
        switch($ch) {
                case 'C':
                        $sName = "CARD";
                        break;
                case 'S':
                        $sName = "SITE";
                        break;
                case 'L':
                        $sName = "LAN";
                        break;
                case 'P':
                        $sName = "POS";
                        break;
                case 'T':
                        $sName = "LAN + POS";
                        break;
        }
        return $sName;
}	

function converteChannel($ch) {
        $sName = "???";
        switch($ch) {
                case 'C':
                        $sName = 'C';
                        break;
                case 'E':
                        $sName = 'S';
                        break;
                case 'M':
                        $sName = 'S';
                        break;
                case 'L':
                        $sName = 'L';
                        break;
                case 'P':
                        $sName = 'P';
                        break;
        }
        return $sName;
}

function converteChannelTotal($ch) {
        $sName = "???";
        switch($ch) {
                case 'C':
                        $sName = 'C';
                        break;
                case 'E':
                        $sName = 'S';
                        break;
                case 'M':
                        $sName = 'S';
                        break;
                case 'L':
                        $sName = 'T';
                        break;
                case 'P':
                        $sName = 'T';
                        break;
        }
        return $sName;
}
?>