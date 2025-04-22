<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php"; 
require_once $raiz_do_projeto . "class/gamer/classIntegracao.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "class/phpmailer/class.smtp.php";
require_once $raiz_do_projeto . "class/classEmailAutomatico.php";

//Alicota EPP Administradora
$alicota_epp_adm = array(6.38,0.38);//6.38;

// Publishers com Detalhamento (POS - LAN - SITE)
/*
$DETAILS = array(
		'GPotato',  
		'Vostu',
		'Kaizen', 
		'ONGAME',
		'HABBO HOTEL',
//		'Softnyx',
		'Skillab',
		);
*/

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

$email_texto	= !empty($_POST['email_texto'])		? $_POST['email_texto']		: "";

// linha abaixo e temporario ate a implementacao final
if (is_null($dd_operadora)) {
	$dd_operadora = 23;
}
elseif(empty($dd_operadora)) {
	$email_destino = "";
}

$texto = "";
$time_start_stats = getmicrotime();

?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?php echo LANG_COMMISSIONS_FINANCIAL_TITLE; ?><?php if(strlen($_SESSION["opr_nome"])>0) echo " (".$_SESSION["opr_nome"].") ";?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />	
    <link href="/css/pos.css" rel="stylesheet" type="text/css">
    <link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">   
    <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />      
    <link rel="stylesheet" href="/sys/css/css.css" type="text/css">
    <script type="text/javascript" src="/js/jquery/jquery.js"></script>
    <script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/global.js"></script>
    <script>
        function showValues() {
            var str = $('form').serialize();
            return str;
        }
        
        function enviaBexs() {
            $('#modal-preenchimento-datas').modal('hide');
            
            $.ajax({
                type: "POST",
                url: "financial_bexs.php",
                data: showValues(),
                beforeSend: function(){
                    $(".errorBox").html("<img src='/sys/imagens/ajax-loader.gif' />");
                    $("#bexs").attr("disabled","disabled");
                },
                success: function(resultado){
                    $("#bexs").attr("disabled","disabled");
                    if($.trim(resultado)){
                        var s = resultado;
                        if(s.indexOf("ERRO") != -1){
                            $('.errorBox').addClass('txt-vermelho');
                            $('#div_mensagem').addClass('msgErroBexs');
                            $('.errorBox').html(resultado);
                        } else{
                            if(s.indexOf("[INFO]") != -1){
                                $('.errorBox').addClass('txt-azul-claro');
                                $('#div_mensagem').addClass('alert alert-info');
                                $('.errorBox').html(resultado);
                            } else{
                                $('.errorBox').addClass('txt-verde');
                                $('#div_mensagem').addClass('msgSucessoBexs');
                                $('.errorBox').html(resultado);
                            }
                        } 
                        
                    } else{
                        $('.errorBox').html('<span class="txt-vermelho">ERRO: Problema ao acessar página de envio de remessas digitais BEXS!</span>');
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
                    $(".errorBox").html(msg_error+"<br>Por favor, relate o problema ao setor de T.I.");
                    $('.errorBox').addClass('txt-vermelho');
                    $('#div_mensagem').addClass('msgErroBexs');
                    
                    //return false;
                },
                timeout: 120000    
            });
        }
    </script>
    <style>body{color:#737373;}</style>
</head>
<body>
<?php
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem";
	} else {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (not (opr_codigo in ($dd_operadora_EPP_Cash, $dd_operadora_EPP_Cash_LH)))  order by opr_nome"; //opr_ordem
	}
        //echo $sqlopr."<br>";
	$resopr = SQLexecuteQuery($sqlopr);

	$bg_col_01 = "#FFFFFF";
	$bg_col_02 = "#EEEEEE";
	$bg_col = $bg_col_01;
 
	$msg_spot = "";

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
	}

	$dd_operadora_nome = "";
	if($dd_operadora) {
		$resopr_nome = SQLexecuteQuery("select opr_nome,opr_cont_mail from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem");
		if($pgopr_nome = pg_fetch_array ($resopr_nome)) { 
			$dd_operadora_nome	= $pgopr_nome['opr_nome'];
				$email_destino		= $pgopr_nome['opr_cont_mail'];
		} 
	}


	$bg_col_01 = "#FFFFFF";
	$bg_col_02 = "#EEEEEE";
	$bg_col = $bg_col_01;

	// Cria array de canais ====================================================================================
        if ($make_detail == 1){
		$aCanais = array("S", "L", "P", "C");
	}
	else {
		$aCanais = array("S", "T", "C");
	}
	$aCanaisTodos = array("T", "S", "C", "E", "M", "L", "P");
        
        //Canais Indiretos e Diretos
        $aCanaisDiretos = array("E", "M");
        $aCanaisIndiretos = array("L", "P");
        
        //Capturando se deve ser desmembrado cartão ou não
        if(!empty($dd_operadora)) {
            $cartoes = getSplitCard($dd_operadora);
        }//end if(!empty($dd_operadora))
        
	if ($cartoes == 1){
		$cartoes = true;
	}
	else {
		$cartoes = false;
	}

	
	// Totais por mes ========================================================================================
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
	}
        
        if(($tf_data_inicial) && ($tf_data_final)&& ($dd_operadora)) {
                $inicio = explode('/',$tf_data_inicial);
                $fim = explode('/',$tf_data_final);
                $sql_total_mes = "
                                select 
                                        fp_channel,fp_publisher, sum(fp_number) as n, sum(fp_total) as total
                                from financial_processing 
                                where  fp_publisher = ".$dd_operadora."
                                       and fp_date >= '".$inicio[2]."-".$inicio[1]."-".$inicio[0]." 00:00:00' 
                                       and fp_date <= '".$fim[2]."-".$fim[1]."-".$fim[0]." 00:00:00'
                                       and fp_date >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$dd_operadora.")
                                       and fp_freeze=0
                                group by fp_channel,fp_publisher; ";
                $teste_mes = false;
                $data_legenda = null;
     
                if($inicio[1] == $fim[1] && ($fim[0]-$inicio[0]+1) == date("t", mktime(0, 0, 0, $inicio[1], $inicio[0], $inicio[2]))) {
                    $teste_mes = true;
                    $data_legenda = $inicio[2]."-".$inicio[1]."-01 00:00:00";
                }//end if($inicio[1] == $fim[1] && ($fim[0]-$inicio[0]) == date("t", mktime(0, 0, 0, $inicio[1], $inicio[0], $inicio[2])))
                
                $sql_min_date = " 
                        select to_char(min(fp_date),'DD/MM/YYYY') as menor_data
                        from financial_processing 
                        where  fp_publisher = ".$dd_operadora."
                               and fp_freeze=0";
                //echo $sql_min_date."<br>";
                $min_date = SQLexecuteQuery($sql_min_date);
                if($min_date) {
                    $min_date_row = pg_fetch_array($min_date);
                    echo "<span class='txt-vermelho'><strong>A menor data deste Publisher que ainda não possui fechamento é ".$min_date_row['menor_data']."</strong></span><br>";
                }//end if($min_date)

        }// end if(($tf_data_inicial) && ($tf_data_final)&& ($dd_operadora)) 
        else $sql_total_mes = "select 1;";
        
        //echo $sql_total_mes."<br>";
        $vendas_total_mes = SQLexecuteQuery($sql_total_mes);

	if($vendas_total_mes) {
		$aNVendas = array();
		$aVendas = array();
		$aNVendasEComis = array();
		$aVendasEComis = array();
                $aTaxaComissao = array();
		
		// Preenche Valores mensais e totais ================================================= 
		while ($vendas_total_mes_row = pg_fetch_array($vendas_total_mes)){
                        $aNVendas[$vendas_total_mes_row['fp_channel']] += $vendas_total_mes_row['n'];
                        $aVendas[$vendas_total_mes_row['fp_channel']] += $vendas_total_mes_row['total'];
                        $aNVendasEComis[$vendas_total_mes_row['fp_channel']] += $vendas_total_mes_row['n'];
                        $aVendasEComis[$vendas_total_mes_row['fp_channel']] += $vendas_total_mes_row['total'];
		}//end while
    
                //echo "<pre>".print_r($aNVendas,true).print_r($aVendas,true).print_r($aNVendasEComis,true).print_r($aVendasEComis,true)."</pre>";
                //die();
                //Calculando comissão
                if($dd_operadora) {
                    //BUSCANDO COMISSOES VARIAVEIS
                    $existe_direta_indireta = false;
                    $sql_comissao = "select * 
                                    from operadoras o 
                                            left outer join tb_comissoes c on o.opr_codigo=c.co_opr_codigo 
                                    where to_char(co_data_inclusao,'YYYYDDMMHH24MISS') = (select to_char(max(co_data_inclusao),'YYYYDDMMHH24MISS') from tb_comissoes where co_opr_codigo=".$dd_operadora.") 
                                                    and opr_codigo = ".$dd_operadora." 
                                    order by co_opr_codigo, co_canal, co_data_inclusao desc, co_volume_tipo, co_volume_min ";
                    //echo  $sql_comissao."<br>";
                    $dados_comissao = SQLexecuteQuery($sql_comissao);
                    $teste_publihser_internacional = null;
                    $teste_bexs = null;
                    $opr_razao = null;
                    $opr_pais = null;
                    $opr_nome = null;
                    $teste_possui_detalhamento_por_canal = 0;
                    while ($dados_comissao_row = pg_fetch_array($dados_comissao)){
                        $dados_comissao_row['co_volume_tipo'] = trim($dados_comissao_row['co_volume_tipo']);
                        $teste_publihser_internacional = $dados_comissao_row['opr_internacional_alicota'];
                        $teste_bexs = $dados_comissao_row['opr_facilitadora'];
                        $merchant_id_bexs = $dados_comissao_row['merchant_id_bexs'];
                        $opr_razao = $dados_comissao_row['opr_razao'];
                        $opr_nome = $dados_comissao_row['opr_nome'];
                        $teste_possui_detalhamento_por_canal = $dados_comissao_row['opr_possui_detalhe'];
                        if($dados_comissao_row['opr_comissao_por_volume'] == 1) {
                            //echo "[".$dados_comissao_row['co_volume_tipo']."]--<br>".$dados_comissao_row['co_volume_min']."-".$dados_comissao_row['co_volume_max']."<br>";
                            if(empty($dados_comissao_row['co_volume_tipo'])){
                                //echo "Entrou no empty<br>";
                                foreach ($aVendas as $canal => $value) {
                                        if($aVendas[$canal] > $dados_comissao_row['co_volume_min'] && $aVendas[$canal] <= $dados_comissao_row['co_volume_max']) {
                                            //echo $aVendas[$canal]." -- $canal - ".$dados_comissao_row['co_comissao']." <br>";
                                            $aVendas_aux[$canal] = $aVendas[$canal]*($dados_comissao_row['co_comissao']/100);
                                            $inicio = explode('/',$tf_data_inicial);
                                            $fim = explode('/',$tf_data_final);
                                            $sql_aliquot = "update financial_processing set fp_aliquot=".$dados_comissao_row['co_comissao']." 
                                                            where fp_channel='".$canal."' 
                                                                and fp_publisher = ".$dd_operadora."
                                                                and fp_date >= '".$inicio[2]."-".$inicio[1]."-".$inicio[0]." 00:00:00' 
                                                                and fp_date <= '".$fim[2]."-".$fim[1]."-".$fim[0]." 00:00:00'
                                                                and fp_freeze=0";
                                            //echo $sql_aliquot." :V sem Dir e Ind<br>";
                                            $dados_update = SQLexecuteQuery($sql_aliquot);
                                            $aTaxaComissao[$canal] = $dados_comissao_row['co_comissao'];
                                        }//if($aVendas_aux[$canal] >= $dados_comissao_row['co_volume_min'] && $aVendas_aux[$canal] <= $dados_comissao_row['co_volume_max'])
                                    
                                }//end foreach
                                
                            }//end if(empty($dados_comissao_row['co_volume_tipo'])
                            else {
                                //echo "NAUNNNNNNNN Entrou no empty<br>";
                                $existe_direta_indireta = true;
                                foreach ($aVendas as $canal => $value) {
                                        //echo $aVendas[$canal]." -- $canal - $key <br>";
                                        if(in_array($canal, $aCanaisDiretos)) {
                                            if($aVendas[$canal] > $dados_comissao_row['co_volume_min'] && $aVendas[$canal] <= $dados_comissao_row['co_volume_max'] && $dados_comissao_row['co_volume_tipo']=='D') {
                                                //echo "*** Direto: ".$canal." - ".$dados_comissao_row['co_comissao']." - ".$dados_comissao_row['co_volume_min']." < ".$aVendas[$canal]." <= ".$dados_comissao_row['co_volume_max']." <br>";
                                                $aVendas_aux[$canal] = $aVendas[$canal]*($dados_comissao_row['co_comissao']/100);
                                                $inicio = explode('/',$tf_data_inicial);
                                                $fim = explode('/',$tf_data_final);
                                                $sql_aliquot = "update financial_processing set fp_aliquot=".$dados_comissao_row['co_comissao']." 
                                                                where fp_channel='".$canal."' 
                                                                    and fp_publisher = ".$dd_operadora."
                                                                    and fp_date >= '".$inicio[2]."-".$inicio[1]."-".$inicio[0]." 00:00:00' 
                                                                    and fp_date <= '".$fim[2]."-".$fim[1]."-".$fim[0]." 00:00:00'
                                                                    and fp_freeze=0";
                                                //echo $sql_aliquot." :V Dir<br>";
                                                $dados_update = SQLexecuteQuery($sql_aliquot);
                                                $aTaxaComissao[$canal] = $dados_comissao_row['co_comissao'];
                                            }//if($aVendas_aux[$canal] >= $dados_comissao_row['co_volume_min'] && $aVendas_aux[$canal] <= $dados_comissao_row['co_volume_max'])
                                        }//end if (in_array($canal, $aCanaisDiretos)) 
                                        elseif(in_array($canal, $aCanaisIndiretos)) {
                                            if($aVendas[$canal] > $dados_comissao_row['co_volume_min'] && $aVendas[$canal] <= $dados_comissao_row['co_volume_max'] && $dados_comissao_row['co_volume_tipo']=='I') {
                                                //echo "*** Indireto: ".$canal." - ".$dados_comissao_row['co_comissao']." - ".$dados_comissao_row['co_volume_min']." < ".$aVendas[$canal]." <= ".$dados_comissao_row['co_volume_max']." <br>";
                                                $aVendas_aux[$canal] = $aVendas[$canal]*($dados_comissao_row['co_comissao']/100);
                                                $inicio = explode('/',$tf_data_inicial);
                                                $fim = explode('/',$tf_data_final);
                                                $sql_aliquot = "update financial_processing set fp_aliquot=".$dados_comissao_row['co_comissao']." 
                                                                where fp_channel='".$canal."' 
                                                                    and fp_publisher = ".$dd_operadora."
                                                                    and fp_date >= '".$inicio[2]."-".$inicio[1]."-".$inicio[0]." 00:00:00' 
                                                                    and fp_date <= '".$fim[2]."-".$fim[1]."-".$fim[0]." 00:00:00'
                                                                    and fp_freeze=0";
                                                //echo $sql_aliquot." :V Ind<br>";
                                                $dados_update = SQLexecuteQuery($sql_aliquot);
                                                $aTaxaComissao[$canal] = $dados_comissao_row['co_comissao'];
                                        }//if($aVendas_aux[$canal] >= $dados_comissao_row['co_volume_min'] && $aVendas_aux[$canal] <= $dados_comissao_row['co_volume_max'])
                                        }//end elseif(in_array($canal, $aCanaisIndiretos))
                                }//end foreach
                                
                                
                            }//end else do if(empty($dados_comissao_row['co_volume_tipo'])
                        }//end if($dados_comissao_row['opr_comissao_por_volume'] == 1) 
                        else {
                            foreach ($aVendas as $canal => $value) {
                                    if($aVendas[$dados_comissao_row['co_canal']] != 0 && $dados_comissao_row['co_canal']==$canal) {
                                        $aVendas_aux[$dados_comissao_row['co_canal']] = $aVendas[$dados_comissao_row['co_canal']]*($dados_comissao_row['co_comissao']/100);
                                        $inicio = explode('/',$tf_data_inicial);
                                        $fim = explode('/',$tf_data_final);
                                        $sql_aliquot = "update financial_processing set fp_aliquot=".$dados_comissao_row['co_comissao']." 
                                                        where fp_channel='".$canal."' 
                                                            and fp_publisher = ".$dd_operadora."
                                                            and fp_date >= '".$inicio[2]."-".$inicio[1]."-".$inicio[0]." 00:00:00' 
                                                            and fp_date <= '".$fim[2]."-".$fim[1]."-".$fim[0]." 00:00:00'
                                                            and fp_freeze=0";
                                        //echo $sql_aliquot." :NAUN Var<br>";
                                        $dados_update = SQLexecuteQuery($sql_aliquot);
                                        $aTaxaComissao[$dados_comissao_row['co_canal']] = $dados_comissao_row['co_comissao'];
                                        //echo $aVendas_aux[$dados_comissao_row['co_canal']]."<br>";
                                    }
                                    //echo $dados_comissao_row['co_canal'].' posicaun:'.$key." --  ".($dados_comissao_row['co_comissao']/100)."<br>";
                            }//end foreach
                        }//end else do if($dados_comissao_row['opr_comissao_por_volume'] == 1) 
                    } //end while ($dados_comissao_row = pg_fetch_array($dados_comissao))
                    
                    //BUSCANDO A COMISSÃO SOMENTE DO CANAL CARTÃO
                    $sql_comissao = "select * 
                                    from operadoras o 
                                            left outer join tb_comissoes c on o.opr_codigo=c.co_opr_codigo 
                                    where opr_codigo = ".$dd_operadora." 
                                            and co_canal='C'
                                    order by co_opr_codigo, co_canal, co_data_inclusao desc, co_volume_tipo, co_volume_min ";
                    $dados_comissao = SQLexecuteQuery($sql_comissao);
                    $dados_comissao_row = pg_fetch_array($dados_comissao);
                    foreach ($aVendas as $canal => $value) {
                            if($aVendas[$dados_comissao_row['co_canal']] != 0 && $dados_comissao_row['co_canal']==$canal) {
                                $aVendas_aux[$dados_comissao_row['co_canal']] = $aVendas[$dados_comissao_row['co_canal']]*($dados_comissao_row['co_comissao']/100);
                                $inicio = explode('/',$tf_data_inicial);
                                $fim = explode('/',$tf_data_final);
                                $sql_aliquot = "update financial_processing set fp_aliquot=".$dados_comissao_row['co_comissao']." 
                                                where fp_channel='".$canal."' 
                                                    and fp_publisher = ".$dd_operadora."
                                                    and fp_date >= '".$inicio[2]."-".$inicio[1]."-".$inicio[0]." 00:00:00' 
                                                    and fp_date <= '".$fim[2]."-".$fim[1]."-".$fim[0]." 00:00:00'
                                                    and fp_freeze=0";
                                //echo $sql_aliquot." :C<br>";
                                $dados_update = SQLexecuteQuery($sql_aliquot);
                                $aTaxaComissao[$dados_comissao_row['co_canal']] = $dados_comissao_row['co_comissao'];
                                //echo $aVendas_aux[$dados_comissao_row['co_canal']]."<br>";
                            }//end if($aVendas[$dados_comissao_row['co_canal']] != 0 && $dados_comissao_row['co_canal']==$canal)
                            //echo $dados_comissao_row['co_canal'].' posicaun:'.$key." --  ".($dados_comissao_row['co_comissao']/100)."<br>";
                    }//end foreach
                }//end if($dd_operadora)
                //echo "-------------------------------<pre>".print_r($aVendas_aux,true)."</pre>";
                //die();
                //Convertendo canais
                foreach ($aVendas as $canal => $value) {
                        //echo $canal." -- <pre>".print_r($m,true)."</pre><br>";
                        if ($make_detail == 1){
                                $aux_converte = converteChannel($canal);
                        }
                        else {
                                $aux_converte = converteChannelTotal($canal);
                        }
                        if($aux_converte != "???") {
                                $aNVendas_tmp[$aux_converte] += $aNVendas[$canal];
                                $aVendas_tmp[$aux_converte] += $aVendas_aux[$canal];
                                $aNVendasEComis_tmp[$aux_converte] += $aNVendasEComis[$canal];
                                $aVendasEComis_tmp[$aux_converte] += $aVendasEComis[$canal];
                        }//end if($aux_converte != "???") 
                }//end foreach   
                unset($aNVendas);
                unset($aVendas);
                unset($aNVendasEComis);
                unset($aVendasEComis);
                //manuquisse invertendo variáveis para os nomes  serem mais coerentes com os valores recebidos
                $aNVendas = $aNVendasEComis_tmp;
                $aVendas = $aVendasEComis_tmp;
                $aNVendasEComis = $aNVendas_tmp;
                $aVendasEComis = $aVendas_tmp;
                //echo "<hr><pre>".print_r($aNVendas,true).print_r($aVendas,true).print_r($aNVendasEComis,true).print_r($aVendasEComis,true)."</pre>";
                //die();
//echo "<pre>".print_r($aTaxaComissao,true)."</pre>";
?>
<form name="form1" method="post" action="">
<input type="hidden" name="dd_email" id="dd_email" value="0">
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_COMMISSIONS_FINANCIAL_PAGE_TITLE." <font color='#3300FF'>".$dd_operadora_nome."</font> ".($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final);?> 
                        <?php if(strlen($_SESSION["opr_nome"])>0) echo "<font color='#66CC33'>".$_SESSION["opr_nome"]."</font> ";?>- <?php echo LANG_COMMISSIONS_PAGE_TITLE_1; ?> </strong>
                    </div>
                </div>
                <div class="row txt-cinza top10">
                    <div class="col-md-offset-6 col-md-6">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <div class="row txt-cinza top10">
                    <div class="col-md-2">
                        <span class="pull-right"><?php echo LANG_STATISTICS_OPERATOR; ?></span>
                    </div>
                    <div class="col-md-3">
<?php
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            echo $_SESSION["opr_nome"];
?>
                        <input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
<?php
                        } else 
                        {
?>
                        <select name="dd_operadora" id="dd_operadora" class="form-control">
                            <option value=""><?php echo LANG_STATISTICS_ALL_OPERATOR; ?></option>
                            <?php while ($pgopr = pg_fetch_array ($resopr)) { ?>
                            <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php echo $pgopr['opr_nome']." (".$pgopr['opr_codigo'].")" ?></option>
                            <?php } ?>
                        </select>
<?php
                        }//end else do if($_SESSION["tipo_acesso_pub"]=='PU')
?>
                    </div>
                    <div class="col-md-2">
                        <span class="pull-right">Exibir Vendas</span>
                    </div>
                    <div class="col-md-3">
<?php
                        if (b_IsSysAdminFinancial())  
                        {
?>
                        <select name="onlyeppcash" id="onlyeppcash" class="form-control">
                            <option value="" <?php if($onlyeppcash == "") echo "selected" ?>>Todas</option>
                            <option value="1" <?php if($onlyeppcash == "1") echo "selected" ?>>Somente Vendas EPP CASH</option>
                            <option value="2" <?php if($onlyeppcash == "2") echo "selected" ?>>Vendas sem EPP CASH</option>
                        </select>
<?php
                        }
?>
                    </div>
                </div>
                <div class="row txt-cinza top10">
                    <div class="col-md-2">
                        <span class="pull-right"><?php echo LANG_COMMISSIONS_MAKE_GRAPHIC; ?></span>
                    </div>
                    <div class="col-md-3">
                        <span class="pull-left"><input type="checkbox" id="make_graphic" name="make_graphic" value="1" 
                        <?php
                        if ($make_graphic == 1){
                                echo " checked";
                        }								
                        ?> /></span>
                    </div>
                    <div class="col-md-2">
                        <span class="pull-right">Tipo de gráfico</span>
                    </div>
                    <div class="col-md-3">
                        <select name="type_graphic" id="type_graphic" class="form-control">
                            <option value="graphic_comiss.php" <?php if($type_graphic == "graphic_comiss.php") echo "selected" ?>><?php echo LANG_COMMISSIONS_GRAPHIC_BAR; ?></option>
                            <option value="graphic_comiss_top_grace.php" <?php if($type_graphic == "graphic_comiss_top_grace.php") echo "selected" ?>><?php echo LANG_COMMISSIONS_GRAPHIC_TOP_GRACE; ?></option>
                            <option value="graphic_comiss_line_plot.php" <?php if($type_graphic == "graphic_comiss_line_plot.php") echo "selected" ?>><?php echo LANG_COMMISSIONS_GRAPHIC_LINE_PLOT; ?></option>
                            <option value="graphic_comiss_multi_lines.php" <?php if($type_graphic == "graphic_comiss_multi_lines.php") echo "selected" ?>><?php echo LANG_COMMISSIONS_GRAPHIC_MULTI_LINES; ?></option>
                        </select>
                    </div>                    
                </div>
                <div class="row txt-cinza top10">
                    <div class="col-md-2">
                        <span class="pull-right"><?php echo LANG_COMMISSIONS_PERIOD; ?></span>
                    </div>
                    <div class="col-md-3">
                        <input name="tf_data_inicial" type="text" class="form-control pull-left w100" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10" />
                        <span class="pull-left espacamento-laterais10"> até </span>
                        <input name="tf_data_final" type="text" class="form-control pull-left w100" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10" />
                    </div>
<?php
                    if(!b_is_Publisher() && count(retornaIdsIntegracao($dd_operadora)) > 0) 
                    {
?>
                    <div class="col-md-2">
                        <span class="pull-right"><?php echo LANG_INTEGRATION; ?></span>
                    </div>
                    <div class="col-md-3">
<?php
                            echo montaSelectIdsIntegracao($dd_operadora, $dd_ids_integracao);
?>
                    </div>
<?php
                    }
?>
                    <div class="col-md-2">
<?php
                        if ($teste_possui_detalhamento_por_canal) 
                        {
?>
                        <input type="checkbox" id="make_detail" name="make_detail" value="1" 
<?php
                            if ($make_detail == 1)
                            {
                                echo " checked";
                            }
                            ?> />  Separar LAN e POS  
<?php
                        }//end if ($teste_possui_detalhamento_por_canal)
?>
                    </div>
                </div>
                <div class="row txt-cinza top10">
                    <div class="col-md-9">
                        <span class="pull-left">
<?php
                        if ($cartoes == 1)
                        {
                            echo "<b><font color='#BD3C2D'>Este Publisher está configurado para desmembrar vendas de Cartões</font></b>";
                        }								
?>
                        </span>
                    </div>
                    <!--<div class="col-md-3">-->
                    <input type="hidden" id="facilitadora_perfil_op" name="facilitadora_perfil_op" value="<?php echo (isset($teste_bexs)) ? $teste_bexs : "0" ; ?>">
                    <input type="hidden" id="nome_merchant" name="nome_merchant" value="<?php echo (isset($opr_razao)) ? $opr_razao : $opr_nome ; ?>">
                    <input type="hidden" id="merchant_id_bexs" name="merchant_id_bexs" value="<?php echo (isset($merchant_id_bexs)) ? $merchant_id_bexs : "0" ; ?>">
<?php
                        if ($teste_publihser_internacional > 0) 
                        {
                            
                            if($teste_bexs) {
?>                                
                                <input type="button" class="btn pull-left btn-success tabela" name="bexs" id="bexs" data-toggle="modal" data-target="#modal-preenchimento-datas" value="BEXS"  >
<?php   
                            } //end if($teste_bexs)
?>
                            <input type="button" name="remessa" id="remessa" value="Remessa" onClick="document.form1.action='financial_remittance.php';document.form1.submit();" class="btn  btn-success tabela">
<?php
                        }//end if ($teste_publihser_internacional > 0)
?>                        
                        <input type="button" name="atualizar" id="atualizar" value="Atualizar" onClick="document.form1.submit();" class="btn btn-success tabela pull-right">
                    <!--</div>-->
                </div>
                </br>
                <div id="div_mensagem">
                    <p class="errorBox" id="p_msg"></p>
                <div>     
            </div>
        </div>
    </div>
</div>
<!--</div>-->
<?php
		if ($teste_publihser_internacional > 0) {
                        //echo "XXX<pre>".print_r($aNVendasEComis,true)."</pre>";
                        if(!empty($cartoes)) {
                            $aNVendasEComisAux = $aNVendasEComis['S']+$aNVendasEComis['T'];
                            $aVendasEComisAux = $aVendasEComis['S']+$aVendasEComis['T'];
                            $aNVendasAux = $aNVendas['S']+$aNVendas['T'];
                            $aVendasAux = $aVendas['S']+$aVendas['T'];
                        }//end if(!empty($cartoes))
                        else {
                            $aNVendasEComisAux = $aNVendasEComis['S']+$aNVendasEComis['T']+$aNVendasEComis['C'];
                            $aVendasEComisAux = $aVendasEComis['S']+$aVendasEComis['T']+$aVendasEComis['C'];
                            $aNVendasAux = $aNVendas['S']+$aNVendas['T']+$aNVendas['C'];
                            $aVendasAux = $aVendas['S']+$aVendas['T']+$aVendas['C'];
                        }//end else do if(!empty($cartoes))
                        
                        if(!in_array($teste_publihser_internacional, $alicota_epp_adm)) {
                            $texto .= "
                                    <table>
                                        <tr>
                                            <td class='upper'>
                                                <b> ".$dd_operadora_nome." </b>
                                                ".($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final)."
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height='32px'>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class='report'>
                                                    <tr>
                                                        <td colspan='5'>
                                                            <span class='negrito'>Sales Summary</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            En.
                                                        </td>
                                                        <td colspan='2'>
                                                            Amount
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total Sales</td>
                                                        <td align='right'>".number_format($aNVendasAux, 0, ',', '.')."</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendasAux, 2, ',', '.')."</td>
                                                        <td>(+)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total de Comission</td>
                                                        <td align='right'>".number_format($aNVendasEComisAux, 0, ',', '.')."</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendasEComisAux, 2, ',', '.')."</td>
                                                        <td>(-)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Gross payout to be Wired</td>
                                                        <td></td>
                                                        <td>R$</td>
                                                        <td align='right'>". number_format(($aVendasAux-$aVendasEComisAux), 2, ',', '.')."</td>
                                                        <td>(=)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tax IRRF ".$teste_publihser_internacional."%</td>
                                                        <td></td>
                                                        <td>R$</td>
                                                        <td align='right'>". number_format((($aVendasAux-$aVendasEComisAux)*($teste_publihser_internacional/100)), 2, ',', '.')."</td>
                                                        <td>(-)</td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Net Wired Payout</b></td>
                                                        <td></td>
                                                        <td><b>R$</b></td>
                                                        <td align='right'><b>". number_format((($aVendasAux-$aVendasEComisAux)-(($aVendasAux-$aVendasEComisAux)*($teste_publihser_internacional/100))), 2, ',', '.')."</b></td>
                                                        <td>(=)</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                     </table>
                                     ";
                        }//end if(!in_array($teste_publihser_internacional, $alicota_epp_adm))
                        else {
                            // Calculando IOF da nova maneira
                            $total_sem_iof = $aVendasAux/(1+$teste_publihser_internacional/100);
                            $total_iof = $total_sem_iof*$teste_publihser_internacional/100;

                            $texto .= "
                                    <table>
                                        <tr>
                                            <td class='upper'>
                                                <b> ".$dd_operadora_nome." </b>
                                                ".($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final)."
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height='32px'>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class='report'>
                                                    <tr>
                                                        <td colspan='5'>
                                                            <span class='negrito'>Sales Summary</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            En.
                                                        </td>
                                                        <td colspan='2'>
                                                            Amount
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Transaction Volume</td>
                                                        <td align='right'>".number_format($aNVendasAux, 0, ',', '.')."</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendasAux, 2, ',', '.')."</td>
                                                        <td>(+)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tax IOF</td>
                                                        <td></td>
                                                        <td>R$</td>
                                                        <td align='right'>". number_format($total_iof, 2, ',', '.')."</td>
                                                        <td>(-)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total Comission</td>
                                                        <td align='right'>".number_format($aNVendasEComisAux, 0, ',', '.')."</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendasEComisAux, 2, ',', '.')."</td>
                                                        <td>(-)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Payout</td>
                                                        <td></td>
                                                        <td>R$</td>
                                                        <td align='right'><b>". number_format(($aVendasAux-$aVendasEComisAux-$total_iof), 2, ',', '.')."</b></td>
                                                        <td>(=)</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                     </table>
                                     ";
                        }//end else do if(!in_array($teste_publihser_internacional, $alicota_epp_adm))
			if(!empty($cartoes)) {				

                                //vendas e comissao
                                $nvendasEComisPrj = $aNVendas['C'];
                                $vendasEComisPrj = $aVendas['C'];
                                //fim vendas e comissao
                                if(!in_array($teste_publihser_internacional, $alicota_epp_adm)) {
                                    $texto .= "
                                    <br><br>
                                    <table>
                                        <tr>
                                            <td class='upper'>
                                                <b> ".$dd_operadora_nome." - Gift Cards</b>
                                                ".($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final)."
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height='32px'>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class='report'>
                                                    <tr>
                                                        <td colspan='5'>
                                                            <span class='negrito'>Sales Summary</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            En.
                                                        </td>
                                                        <td colspan='2'>
                                                            Amount
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total Sales</td>
                                                        <td align='right'>".number_format($aNVendas['C'], 0, ',', '.')."</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendas['C'], 2, ',', '.')."</td>
                                                        <td>(+)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total de Comission</td>
                                                        <td align='right'>".number_format($aNVendasEComis['C'], 0, ',', '.')."</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendasEComis['C'], 2, ',', '.')."</td>
                                                        <td>(-)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Gross payout to be Wired</td>
                                                        <td></td>
                                                        <td>R$</td>
                                                        <td align='right'>". number_format(($aVendas['C']-$aVendasEComis['C']), 2, ',', '.')."</td>
                                                        <td>(=)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tax IRRF ".$teste_publihser_internacional."%</td>
                                                        <td></td>
                                                        <td>R$</td>
                                                        <td align='right'>". number_format((($aVendas['C']-$aVendasEComis['C'])*($teste_publihser_internacional/100)), 2, ',', '.')."</td>
                                                        <td>(-)</td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Net Wired Payout</b></td>
                                                        <td></td>
                                                        <td><b>R$</b></td>
                                                        <td align='right'><b>". number_format((($aVendas['C']-$aVendasEComis['C'])-(($aVendas['C']-$aVendasEComis['C'])*($teste_publihser_internacional/100))), 2, ',', '.')."</b></td>
                                                        <td>(=)</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                     </table>
                                     <br><br>
                                     ";
                                } //end if(!in_array($teste_publihser_internacional, $alicota_epp_adm))
                                else {
                                    
                                    // Calculando IOF da nova maneira para Cartões
                                    $total_sem_iof_cartao = $aVendas['C']/(1+$teste_publihser_internacional/100);
                                    $total_iof_cartao = $total_sem_iof_cartao*$teste_publihser_internacional/100;

                                    $texto .= "
                                    <br><br>
                                    <table>
                                        <tr>
                                            <td class='upper'>
                                                <b> ".$dd_operadora_nome." - Gift Cards</b>
                                                ".($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final)."
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height='32px'>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class='report'>
                                                    <tr>
                                                        <td colspan='5'>
                                                            <span class='negrito'>Sales Summary</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            En.
                                                        </td>
                                                        <td colspan='2'>
                                                            Amount
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Transaction Volume</td>
                                                        <td align='right'>".number_format($aNVendas['C'], 0, ',', '.')."</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendas['C'], 2, ',', '.')."</td>
                                                        <td>(+)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tax IOF</td>
                                                        <td></td>
                                                        <td>R$</td>
                                                        <td align='right'>". number_format($total_iof_cartao, 2, ',', '.')."</td>
                                                        <td>(-)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total de Comission</td>
                                                        <td align='right'>".number_format($aNVendasEComis['C'], 0, ',', '.')."</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendasEComis['C'], 2, ',', '.')."</td>
                                                        <td>(-)</td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Payout</b></td>
                                                        <td></td>
                                                        <td><b>R$</b></td>
                                                        <td align='right'><b>". number_format(($aVendas['C']-$aVendasEComis['C']-$total_iof_cartao), 2, ',', '.')."</b></td>
                                                        <td>(=)</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                     </table>
                                     <br><br>
                                     ";
                                }//end else do if(!in_array($teste_publihser_internacional, $alicota_epp_adm))
                                if(!in_array($teste_publihser_internacional, $alicota_epp_adm)) {
                                
                                        ?>
                                        <input type="hidden" name="grosswiredcard" id="grosswiredcard" value="<?php echo number_format(($aVendas['C']-$aVendasEComis['C']), 2, ',', '.');?>">
                                        <input type="hidden" name="witholdingcard" id="witholdingcard" value="<?php echo number_format((($aVendas['C']-$aVendasEComis['C'])*($teste_publihser_internacional/100)), 2, ',', '.');?>">
                                        <input type="hidden" name="netwiredcard" id="netwiredcard" value="<?php echo number_format((($aVendas['C']-$aVendasEComis['C'])-(($aVendas['C']-$aVendasEComis['C'])*($teste_publihser_internacional/100))), 2, ',', '.');?>">
                                        <?php
                                }//end if(!in_array($teste_publihser_internacional, $alicota_epp_adm))
                                else { 
                                        ?>
                                        <input type="hidden" name="grosswiredcard" id="grosswiredcard" value="<?php echo number_format($aVendas['C'], 2, ',', '.');?>">
                			<input type="hidden" name="management_feecard" id="management_feecard" value="<?php echo number_format($aVendasEComis['C'], 2, ',', '.');?>">
                                        <input type="hidden" name="witholdingcard" id="witholdingcard" value="<?php echo number_format($total_iof_cartao, 2, ',', '.');?>">
                                        <input type="hidden" name="netwiredcard" id="netwiredcard" value="<?php echo number_format(($aVendas['C'] - $aVendasEComis['C'] - $total_iof_cartao), 2, ',', '.');?>">
                                        <?php
                                } //end else do if(!in_array($teste_publihser_internacional, $alicota_epp_adm))
                        } //end if(!empty($cartoes))
			else {
                        ?>
			<input type="hidden" name="grosswiredcard" id="grosswiredcard" value="">
			<input type="hidden" name="witholdingcard" id="witholdingcard" value="">
			<input type="hidden" name="netwiredcard" id="netwiredcard" value="">
			<?php
                        } //end else do if(!empty($cartoes))
                        if(!in_array($teste_publihser_internacional, $alicota_epp_adm)) {
			?>
			<input type="hidden" name="grosswired" id="grosswired" value="<?php echo number_format(($aVendasAux -$aVendasEComisAux), 2, ',', '.');?>">
			<input type="hidden" name="witholding" id="witholding" value="<?php echo number_format((($aVendasAux -$aVendasEComisAux)*($teste_publihser_internacional/100)), 2, ',', '.');?>">
			<input type="hidden" name="netwired" id="netwired" value="<?php echo number_format((($aVendasAux - $aVendasEComisAux)-(($aVendasAux-$aVendasEComisAux)*($teste_publihser_internacional/100))), 2, ',', '.');?>">
			<input type="hidden" name="tax" id="tax" value="<?php echo $teste_publihser_internacional;?>">
			<input type="hidden" name="rperiod" id="rperiod" value="<?php echo ($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final); ?>">
			<?php
                        } //end if(!in_array($teste_publihser_internacional, $alicota_epp_adm))
                        else {
                        ?>
			<input type="hidden" name="grosswired" id="grosswired" value="<?php echo number_format($aVendasAux, 2, ',', '.');?>">
			<input type="hidden" name="management_fee" id="management_fee" value="<?php echo number_format($aVendasEComisAux, 2, ',', '.');?>">
			<input type="hidden" name="witholding" id="witholding" value="<?php echo number_format($total_iof, 2, ',', '.');?>">
			<input type="hidden" name="netwired" id="netwired" value="<?php echo number_format(($aVendasAux - $aVendasEComisAux - $total_iof), 2, ',', '.');?>">
			<input type="hidden" name="tax" id="tax" value="<?php echo $teste_publihser_internacional;?>">
			<input type="hidden" name="rperiod" id="rperiod" value="<?php echo ($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final); ?>">
                        <?php
                        }//end else do if(!in_array($teste_publihser_internacional, $alicota_epp_adm))
		}
		else {
			if($teste_possui_detalhamento_por_canal==1) {

                                if($make_detail) {
                                    $aNTotalVendaAux = ($aNVendas['S']+$aNVendas['P']+$aNVendas['L']);
                                    $aTotalVendaAux = ($aVendas['S']+$aVendas['P']+$aVendas['L']);
                                    $aNTotalIndiretoAux = $aNVendasEComis['P']+$aNVendasEComis['L'];
                                    $aTotalIndiretoAux = $aVendasEComis['P']+$aVendasEComis['L'];
                                    $aNTotalIndiretoVendaAux = $aNVendas['P']+$aNVendas['L'];
                                    $aTotalIndiretoVendaAux = $aVendas['P']+$aVendas['L'];
                                } //end if($make_detail)
                                else {
                                    $aNTotalVendaAux = ($aNVendas['S']+$aNVendas['T']);
                                    $aTotalVendaAux = ($aVendas['S']+$aVendas['T']);
                                    $aNTotalIndiretoAux = $aNVendasEComis['T'];
                                    $aTotalIndiretoAux = $aVendasEComis['T'];
                                    $aNTotalIndiretoVendaAux = $aNVendas['T'];
                                    $aTotalIndiretoVendaAux = $aVendas['T'];
                                } //end else do if($make_detail)
                                
                                $aNVendasEComisTotal = $aNVendasEComis['S']+$aNTotalIndiretoAux;
                                $aVendasEComisTotal = $aVendasEComis['S']+$aTotalIndiretoAux;

                                if(empty($cartoes)){
                                    $aNTotalVendaAux += $aNVendas['C'];
                                    $aTotalVendaAux += $aVendas['C'];
                                    $aNVendasEComisTotal += $aNVendasEComis['C'];
                                    $aVendasEComisTotal += $aVendasEComis['C'];
                                }//end if(empty($cartoes))
                                
				$aTotalAux = ($aTotalVendaAux)-($aVendasEComisTotal);
                                
                                $texto1 = "
                                    <table class='report'>
                                        <tr>
                                            <td colspan='4'>
                                                <span class='negrito'>Vendas - Valor Bruto</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Canal
                                            </td>
                                            <td>
                                                Qtde.
                                            </td>
                                            <td colspan='2'>
                                                Valor
                                            </td>
                                        </tr>
                                                ";
                                $texto2 = "
                                    <table class='report'>
                                        <tr>
                                            <td colspan='4'>
                                                <span class='negrito'>Comissão</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                Qtde.
                                            </td>
                                            <td colspan='2'>
                                                Valor
                                            </td>
                                        </tr>
                                                ";
                                $texto3 = "
                                    <table class='report'>
                                        <tr>
                                            <td colspan='4'>
                                                <span class='negrito'>Resumo</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td colspan='2'>
                                                Valor
                                            </td>
                                            <td></td>
                                        </tr>
                                                ";
				if($existe_direta_indireta) {
                                    $texto1 .= "
                                        <tr>
                                               <td><nobr>Canal Direto</nobr></td>
                                               <td align='right'>".number_format($aNVendas['S'], 0, ',', '')."</td>
                                               <td>R$</td>
                                               <td align='right'>".number_format($aVendas['S'], 2, ',', '.')."</td>
                                        </tr>
                                        <tr>
                                                <td><nobr>Canal Indireto</nobr></td>
                                                <td align='right'>".number_format($aNTotalIndiretoVendaAux, 0, ',', '')."</td>
                                                <td>R$</td>
                                                <td align='right'>".number_format($aTotalIndiretoVendaAux, 2, ',', '.')."</td>
                                        </tr>
                                        ";
                                    $texto2 .= "
                                        <tr>
                                               <td><nobr>Comissão Canal Direto</nobr></td>
                                               <td align='right'>".number_format($aNVendasEComis['S'], 0, ',', '')."</td>
                                               <td>R$</td>
                                               <td align='right'>".number_format($aVendasEComis['S'], 2, ',', '.')."</td>
                                        </tr>
                                        <tr>
                                                <td><nobr>Comissão Canal Indireto</nobr></td>
                                                <td align='right'>".number_format($aNTotalIndiretoAux, 0, ',', '')."</td>
                                                <td>R$</td>
                                                <td align='right'>".number_format($aTotalIndiretoAux, 2, ',', '.')."</td>
                                        </tr>
                                        ";
                                    if(empty($cartoes)) {
                                        $texto1 .= "
                                            <tr>
                                                <td><nobr>Canal Cartão</nobr></td>
                                                <td align='right'>".number_format($aNVendas['C'], 0, ',', '')."</td>
                                                <td>R$</td>
                                                <td align='right'>".number_format($aVendas['C'], 2, ',', '.')."</td>
                                            </tr>
                                            ";
                                        $texto2 .= "
                                        <tr>
                                            <td><nobr>Comissão Canal Cartão</nobr></td>
                                            <td align='right'>".number_format($aNVendasEComis['C'], 0, ',', '')."</td>
                                            <td>R$</td>
                                            <td align='right'>".number_format($aVendasEComis['C'], 2, ',', '.')."</td>
                                        </tr>
                                        ";
                                    }//end if(empty($cartoes))
                                }//end if($existe_direta_indireta)
                                else {
                                    //Montando por canais  
                                    for($j=0;$j<count($aCanais);$j++) {
                                            //vendas e comissao
                                            $nvendasEComisPrj = $aNVendas[$aCanais[$j]];
                                            $vendasEComisPrj = $aVendas[$aCanais[$j]];

                                            if($aCanais[$j] != 'C') {
                                                    $texto1 .= "
                                            <tr>
                                                <td><nobr>".getChannelName($aCanais[$j])."</nobr></td>
                                                <td align='right'>".number_format($nvendasEComisPrj, 0, ',', '')."</td>
                                                <td>R$</td>
                                                <td align='right'>".number_format($vendasEComisPrj, 2, ',', '.')."</td>
                                            </tr>
                                            ";
                                                    $texto2 .= "
                                            <tr>
                                                   <td><nobr>Comissão Canal ".getChannelName($aCanais[$j])."</nobr></td>
                                                   <td align='right'>".number_format($aNVendasEComis[$aCanais[$j]], 0, ',', '')."</td>
                                                   <td>R$</td>
                                                   <td align='right'>".number_format($aVendasEComis[$aCanais[$j]], 2, ',', '.')."</td>
                                            </tr>
                                            ";
                                            }//end if($aCanais[$j] != 'C') 
                                            elseif(empty($cartoes)) {
                                                    $texto1 .= "
                                            <tr>
                                                <td><nobr>".getChannelName($aCanais[$j])."</nobr></td>
                                                <td align='right'>".number_format($nvendasEComisPrj, 0, ',', '')."</td>
                                                <td>R$</td>
                                                <td align='right'>".number_format($vendasEComisPrj, 2, ',', '.')."</td>
                                            </tr>
                                            ";
                                                   $texto2 .= "
                                            <tr>
                                                <td><nobr>Comissão Canal Cartão</nobr></td>
                                                <td align='right'>".number_format($aNVendasEComis['C'], 0, ',', '')."</td>
                                                <td>R$</td>
                                                <td align='right'>".number_format($aVendasEComis['C'], 2, ',', '.')."</td>
                                            </tr>
                                            ";
                                            }//end elseif(empty($cartoes))
                                    }//end for
                                }//end else do if($existe_direta_indireta)
				
                                $texto1 .= "
                                        <tr>
                                            <td><b><nobr>Total de Venda</nobr></b></td>
                                            <td align='right'><b>".number_format($aNTotalVendaAux, 0, ',', '')."</b></td>
                                            <td><b>R$</b></td>
                                            <td align='right'><b>".number_format($aTotalVendaAux, 2, ',', '.')."</b></td>
                                        </tr>
                                        ";
                                $texto2 .= "
                                        <tr>
                                            <td><b><nobr>Total de Comissão</nobr></b></td>
                                            <td align='right'><b>".number_format($aNVendasEComisTotal, 0, ',', '')."</b></td>
                                            <td><b>R$</b></td>
                                            <td align='right'><b>".number_format($aVendasEComisTotal, 2, ',', '.')."</b></td>
                                        </tr>
                                        ";
                                $texto3 .= "
                                        <tr>
                                            <td><nobr>Total de Venda</nobr></td>
                                            <td>R$</td>
                                            <td align='right'>".number_format($aTotalVendaAux, 2, ',', '.')."</td>
                                            <td>(+)</td>
                                        </tr>
                                        <tr>
                                            <td><nobr>Total de Comissão</nobr></td>
                                            <td>R$</td>
                                            <td align='right'>".number_format($aVendasEComisTotal, 2, ',', '.')."</td>
                                            <td>(-)</td>
                                        </tr>
                                        <tr>
                                            <td><b>Repasse</b></td>
                                            <td><b>R$</b></td>
                                            <td align='right'><b>". number_format($aTotalAux, 2, ',', '.')."</b></td>
                                            <td>(=)</td>
                                        </tr>
                               ";
                                
                                $texto1 .= "
                                </table>
                                ";
                                $texto2 .= "
                                </table>
                                ";
                                $texto3 .= "
                                </table>
                                ";
                                
                                $texto .= "
                                    <table>
                                        <tr>
                                            <td colspan='4' class='upper'>
                                                <b> ".$dd_operadora_nome." </b>
                                                ".($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final)."
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan='4' height='32px'>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class='border'>
                                                ".$texto1."
                                            </td>
                                            <td class='border'>
                                                ".$texto2."
                                            </td>
                                            <td>
                                                ".$texto3."
                                            </td>
                                        </tr>
                                     </table>
                                     ";

			}
			else {
                                $aNTotalVendaAux = $aNVendas['T']+$aNVendas['S'];
                                //echo "<pre>".print_r($aNVendas,true)."<pre>";
                                $aTotalVendaAux = $aVendas['T']+$aVendas['S'];
                                $aNVendasEComisTotal = $aNVendasEComis['S']+$aNVendasEComis['T'];
                                //echo "<pre>".print_r($aNVendasEComis,true)."<pre>";
                                $aVendasEComisTotal = $aVendasEComis['S']+$aVendasEComis['T'];

                                if(empty($cartoes)){
                                    $aNTotalVendaAux += $aNVendas['C'];
                                    $aTotalVendaAux += $aVendas['C'];
                                    $aNVendasEComisTotal += $aNVendasEComis['C'];
                                    $aVendasEComisTotal += $aVendasEComis['C'];
                                }//end if(empty($cartoes))
                                
                                $texto .= "
                                    <table>
                                        <tr>
                                            <td class='upper'>
                                                <b> ".$dd_operadora_nome." </b>
                                                ".($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final)."
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height='32px'>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class='report'>
                                                    <tr>
                                                        <td colspan='5'>
                                                            <span class='negrito'>Resumo</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            Qtde.
                                                        </td>
                                                        <td colspan='2'>
                                                            Valor
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total de Vendas</td>
                                                        <td align='right'>".number_format($aNTotalVendaAux, 0, ',', '.')."</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aTotalVendaAux, 2, ',', '.')."</td>
                                                        <td>(+)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total de Comissão</td>
                                                        <td align='right'>".number_format($aNVendasEComisTotal, 0, ',', '.')."</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendasEComisTotal, 2, ',', '.')."</td>
                                                        <td>(-)</td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Repasse</b></td>
                                                        <td></td>
                                                        <td><b>R$</b></td>
                                                        <td align='right'><b>". number_format(($aTotalVendaAux-$aVendasEComisTotal), 2, ',', '.')."</b></td>
                                                        <td>(=)</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                     </table>
                                     ";
			}
                        if(!empty($cartoes)) {				
                            $texto .= "
                                <br><br>
                                    <table>
                                        <tr>
                                            <td class='upper'>
                                                <b> ".$dd_operadora_nome." </b>
                                                ".($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final)."
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height='32px'>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class='report'>
                                                    <tr>
                                                        <td colspan='4'>
                                                            <span class='negrito'>Resumo</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td colspan='2'>
                                                            Valor
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Canal Cartão</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendas['C'], 2, ',', '.')."</td>
                                                        <td>(+)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total de Comissão</td>
                                                        <td>R$</td>
                                                        <td align='right'>".number_format($aVendasEComis['C'], 2, ',', '.')."</td>
                                                        <td>(-)</td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Repasse</b></td>
                                                        <td><b>R$</b></td>
                                                        <td align='right'><b>". number_format(($aVendas['C']-$aVendasEComis['C']), 2, ',', '.')."</b></td>
                                                        <td>(=)</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                     </table>
                                 <br><br>
                                 ";
                    } //end if(!empty($cartoes))

		}//end else do if ($teste_publihser_internacional>0)
		echo $texto;
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."  </font></td></tr>";
	}
?>
<br>
	<?php
	
	if ($make_graphic == 1) {
		@session_start();
		unset($_SESSION['graphic']);
                

                // Cria array de periodos ====================================================================================
                $thismonth  = mktime(0, 0, 0, date("m"), 1, date("Y"));
                $firstmonth  = mktime(0, 0, 0, 1, 1, (date("Y")-5));
                $currentmonth = $thismonth;
                while($currentmonth >=$firstmonth) {
                        $_SESSION['graphic'][date("y",$currentmonth)][date("m", $currentmonth)] = 0;
                        $currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 1, date("Y",$currentmonth));
                }

                //montando o select com os dados
                $sql_graphic = "
                                select 
                                        date_trunc('month', fp_date) as mes, 
                                        sum(fp_total) as total 
                                from financial_processing 
                                where ";
                if(!empty($dd_operadora)) {
                    $sql_graphic .= " fp_publisher = ".$dd_operadora." and 
                                    ";
                }//end if($dd_operadora)
                $sql_graphic .= " fp_date >= '".(date("Y")-5)."-01-01 00:00:00'
                                group by mes; ";
                
                // Montando a SESSION com Valores mensais totais ================================================= 
                $volume_total_mes = SQLexecuteQuery($sql_graphic);
		while ($volume_total_mes_row = pg_fetch_array($volume_total_mes)){
                            $data_MK = explode('-',$volume_total_mes_row['mes']);
                            //echo "<pre>".print_r($data_MK)."</pre>";
		            $_SESSION['graphic'][substr($data_MK[0],2,2)][$data_MK[1]] = number_format($volume_total_mes_row['total'], 0, ',', '');
		}
                $imagem = str_replace("/","_",($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial."-".$tf_data_final))."_imagem.png";
                //echo "<pre>".print_r($_SESSION['graphic'])."</pre>";
	?>
<script language="javascript">
//Carga Grafico
$(document).ready(function () {
    $.ajax({
            type: "POST",
            url: "<?php echo $type_graphic;?>",
            data: "imagem=<?php echo $imagem;?>&labeloperadora=<?php echo $dd_operadora_nome; if ($teste_publihser_internacional > 0) { echo '&language=EN';}?>",
            success: function(html){
                    //alert(html);
                    $('#showGraphic').html('<img src="images/<?php echo trim($dd_operadora_nome).$imagem;?>" width="750" height="350" alt="Grafico" vspace="2"/><br><br>');
            },
            error: function(){
                    alert('erro valor');
            }
    });
    
    
});
</script>
<div id='showGraphic'>
</div>
<?php
	} //end if ($make_graphic == 1)
	//echo $dd_email." : DD_EMAIL<br>"; 
	if ($dd_email == "1") {
		$sendStatus = false;
		$complementSubjext = " - " . $dd_operadora_nome . " " . ($teste_mes?date('F/Y',strtotime($data_legenda)):$tf_data_inicial." - ".$tf_data_final);

		$html_imagem = "";
		if (strlen($imagem)) {
			$html_imagem .= "<img src='".trim($dd_operadora_nome).$imagem."'/><br>";
		}
		
		//Descomentar e tirar o envio direto para o Wagner
		$bcc	= "wagner@e-prepag.com.br,glaucia@e-prepag.com.br,financeiro@e-prepag.com.br";
		$attach	= "images/".trim($dd_operadora_nome).$imagem;
		//echo $attach."<br>";
                //$email_destino = "wagner@e-prepag.com.br";
                
        $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER,'FechamentoFinanceiro');		
		$objEnvioEmailAutomatico->setUgEmail($email_destino);
        $objEnvioEmailAutomatico->setBccAdicional($bcc);
        $objEnvioEmailAutomatico->setSubjectAdicional($complementSubjext);
		$objEnvioEmailAutomatico->setPartnerEmail(str_replace("\n","<br>",$email_texto));
		$objEnvioEmailAutomatico->setPartnerDetails($texto);
		$objEnvioEmailAutomatico->setPartnerGraph($html_imagem);
		echo $objEnvioEmailAutomatico->MontaEmailEspecifico($attach, false, '', $sendStatus);

                if($sendStatus) {
                    //echo "Email enviado com sucesso para $email_destino!<br>";
                    $inicio = explode('/',$tf_data_inicial);
                    $fim = explode('/',$tf_data_final);

                    if(validacaoPublisherEppPagamentosFacilitadora($dd_operadora)) {
                            $sql_calculo = "update financial_processing 
                                            set fp_comission = (fp_total * fp_aliquot/100) , 
                                                fp_start_date = '".$inicio[2]."-".$inicio[1]."-".$inicio[0]." 00:00:00',
                                                fp_end_date = '".$fim[2]."-".$fim[1]."-".$fim[0]." 00:00:00'
                                            where fp_publisher = ".$dd_operadora."
                                                and fp_date >= '".$inicio[2]."-".$inicio[1]."-".$inicio[0]." 00:00:00' 
                                                and fp_date <= '".$fim[2]."-".$fim[1]."-".$fim[0]." 00:00:00'
                                                and fp_freeze=0";
                            //echo $sql_calculo." <br>";
                            $dados_calculo = SQLexecuteQuery($sql_calculo);
                            if(!$dados_calculo) {
                                echo "Problema em salvar o congelamento do cálculo.<br>";
                            }//end if(!$dados_calculo)
                    }//end if(validacaoPublisherEppPagamentosFacilitadora($dd_operadora))
                    else {
                            $sql_calculo = "update financial_processing 
                                            set fp_comission = (fp_total * fp_aliquot/100) , 
                                                fp_start_date = '".$inicio[2]."-".$inicio[1]."-".$inicio[0]." 00:00:00',
                                                fp_end_date = '".$fim[2]."-".$fim[1]."-".$fim[0]." 00:00:00',
                                                fp_freeze = 1
                                            where fp_publisher = ".$dd_operadora."
                                                and fp_date >= '".$inicio[2]."-".$inicio[1]."-".$inicio[0]." 00:00:00' 
                                                and fp_date <= '".$fim[2]."-".$fim[1]."-".$fim[0]." 00:00:00'
                                                and fp_freeze=0";
                            //echo $sql_calculo." <br>";
                            $dados_calculo = SQLexecuteQuery($sql_calculo);
                            if(!$dados_calculo) {
                                echo "Problema em salvar o congelamento do cálculo.<br>";
                            }//end if(!$dados_calculo)
                    }//end else do if(validacaoPublisherEppPagamentosFacilitadora($dd_operadora))
                }//end if($sendStatus)
                else {
                    echo "<h3>Deu erro no envio do email, verifique a mensagem acima!<br>Por favor, tente executar o envio de email novamente.</h3>";
                    ///// ATENÇÂO fazer no NEW diretamente em produção
                }
	}//end if ($dd_email == "1")
?>
<textarea rows="10" cols="120" name="email_texto" id="email_texto">
<?php
echo $email_texto;
?>
</textarea>
<br><br>
E-mail: <input type="text" name="email_destino" id="email_destino" value="<?php echo $email_destino;?>" readonly="readonly" maxlength="255" size="40"/><br>
<input type="button" name="btenviar" id="btenviar" value="Enviar Email" onClick="javascript:document.form1.dd_email.value='1';document.form1.submit();" class="botao_simples">
<br>
<br>
<table border='0' cellpadding='0' cellspacing='1' width='80%' bordercolor='#cccccc' style='border-collapse:collapse;'>	
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%"> </td><td bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT; ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%"> </td>
  </tr>
</table>
</form>
<div id="modal-preenchimento-datas" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Datas necessárias para o envio das informações da remessa ao <i>BEXS</i></h4>
            </div>
            <div class="modal-bodyespacamento">
                <form id="form_datas" method="post" enctype="multipart/form-data">
                    <div id="preenchimento_datas" class="row espacamento">
                        <div class="col-md-7 txt-cinza datas_preencher">
                            <input class="form-control datas_bexs" id="data_operacao" char="10" name="data_operacao" type="hidden" value="<?php echo date('Y-m-d'); ?>" >                       
                            <div class="row top10  form-group">
                                <div class="col-md-6 text-right">
                                    <label for="data_mn">Data da Moeda Nacional:</label>
                                </div>
                                <div class="col-md-6">
                                    <input class="form-control datas_bexs" id="data_mn" char="10" name="data_mn" type="date" value="" required>
                                </div>
                            </div>
                            
                            <div class="row top10  form-group">
                                <div class="col-md-6 text-right">
                                    <label for="data_me">Data da Moeda Estrangeira:</label>
                                </div>
                                <div class="col-md-6">
                                    <input class="form-control datas_bexs" id="data_me" char="10" name="data_me" type="date" value="" required>
                                </div>
                            </div>
                            
                            <div class="row top10  form-group">
                                <div class="col-md-6 text-right">
                                    <label for="data_liquidacao">Data da Liquidação:</label>
                                </div>
                                <div class="col-md-6">
                                    <input class="form-control datas_bexs" id="data_liquidacao" char="10" name="data_liquidacao" type="date" value="" required>
                                </div>
                            </div>
                            
                            <div class="row top10 form-group">
                                <div class="col-md-12 dislineblock">
                                    <a href="javascript:enviaBexs();" id="prosseguir" class="pull-right btn btn-success">Prosseguir</a>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-5 txt-azul-claro">
                            
                            <div class="row espacamento-menor top10">
                                <div class="col-md-2 text-right">
                                    <a href="#" id="dt_mn" title="Data em que será feita a transferência em reais ao BEXS" class="btn-question glyphicon glyphicon-question-sign t0 pull-left"></a>
                                </div>
                            </div>
                               
                            <div class="row espacamento-menor top10">
                                <div class="col-md-2 text-right">
                                    <a href="#" id="dt_me" title="Data em que será creditada a moeda estrangeira (dólar) nos dados bancários do Publisher" class="btn-question glyphicon glyphicon-question-sign t0 pull-left"></a>
                                </div>
                            </div>
                            
                            <div class="row espacamento-menor top10">
                                <div class="col-md-2 text-right">
                                    <a href="#" id="dt_lq" title="Data em que o contrato é liquidado ('Concluído')" class="btn-question glyphicon glyphicon-question-sign t0 pull-left"></a>
                                </div>
                            </div>
                            
                        </div>
                    </div>    

                </form>
                <div id="boxMsgInfo" class="hide txt-azul-claro"></div>
                <div id="boxMsgError" class="txt-vermelho"></div>
            </div>
            <div class="modal-footer">
                
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>    
    </div> 
</div>
    
<div id="modal-info" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title text-left txt-azul-claro" id="modal-title">Remessa já enviada ao BEXS</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info text-left" id="tipo-modal" role="alert"> 
                  <h5><span id="error-text"><?php echo $msg_modal_bexs ?></span></h5>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>   
<script src="/js/valida.js"></script>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<!--<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">-->
<!--<style>
.ui-tooltip {
    width: 290px;
    color: #31708f;
    background-color: #d9edf7;
    font: bold 14px "Helvetica Neue", Sans-Serif;
    border-color: #bce8f1;
}    
</style>-->
<script>
    $(function(){
        setDateInterval('tf_data_inicial','tf_data_final',1);
        
        $("#prosseguir").click(function(){
            $('#boxMsgInfo').addClass('hide');
            
            if(valida()){
                $('#boxMsgError').html("");
                $('#boxMsgError').removeClass('msgErroBexs');
                return true;
            } else{
                $('#boxMsgError').addClass('msgErroBexs');
                $('#boxMsgError').html("Preencha corretamente os campos destacados em vermelho!");
                return false;
            }
        });
        
//        var tooltips = $( "[title]" ).tooltip({
//            position: {
//              my: "left top",
//              at: "right+5 top-5",
//              collision: "none"
//            }
//        });
        
        $(".datas_preencher").click(function(){
            $('#boxMsgInfo').addClass('hide');
        });
        
        $("#dt_mn").click(function(){
            $('#boxMsgInfo').removeClass('hide');
            $('#boxMsgInfo').addClass('alert-info');
            $('#boxMsgInfo').html("<strong>Data da Moeda Nacional</strong>: Data em que será feita a transferência em reais ao BEXS");
        });
        
        $("#dt_me").click(function(){
            $('#boxMsgInfo').removeClass('hide');
            $('#boxMsgInfo').addClass('alert-info');
            $('#boxMsgInfo').html("<strong>Data da Moeda Estrangeira</strong>: Data em que será creditada a moeda estrangeira (dólar) nos dados bancários do Publisher");
            
        });
        
        $("#dt_lq").click(function(){
            $('#boxMsgInfo').removeClass('hide');
            $('#boxMsgInfo').addClass('alert-info');
            $('#boxMsgInfo').html("<strong>Data da Liquidação</strong>: Data em que o contrato é liquidado ('Concluído')");
        });
 
    });

</script>
   <?php require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
</body>
</html>

<?php
// ===============================
function getChannelName($ch) {
        $sName = "???";
        switch($ch) {
                case 'C':
                        $sName = "CARDS";
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

function getSplitCard($opr_codigo) {
        //	-- Function: Obtem parametro de separar vendas de cartão no fechamento financeiro
        $sql = "select opr_desmembra_cartao from operadoras where opr_codigo= ".$opr_codigo.";";
        $res = SQLexecuteQuery($sql);
        if($res) {
                if($res_row = pg_fetch_array ($res)) { 
                        return $res_row['opr_desmembra_cartao'];
                } else {
                        return 0;
                }
        } else {
                return 0;
        }
        return 0;
} //end function getSplitCard($opr_codigo)


function validacaoPublisherEppPagamentosFacilitadora($opr_codigo) {

        // Buscando informações 
        $sql = "select 
                        opr_codigo
                from operadoras
                where 
                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_PAGAMENTOS']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_internacional_alicota = 0.38
                        and opr_codigo = ".$opr_codigo."
                        and opr_status != '0'
                ";

        //echo $sql.PHP_EOL; die();
        $rs_operadoras_operantes = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_operadoras_operantes)."<br>";
        if(!$rs_operadoras_operantes) {
            echo "Erro na Query de validação de Publishers Epp Pagamentos Facilitadora(".$sql.").<br>".PHP_EOL;
            return FALSE;
        }
        if(pg_num_rows($rs_operadoras_operantes) == 0) {
            return FALSE;
        }//end if(pg_num_rows($rs_operadoras_operantes) == 0)
        else {
            $rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes);
            return $rs_operadoras_operantes_row['opr_codigo'];
        }//end else
    
}//end function validacaoPublisherEppPagamentosFacilitadora()

?>