<?php  
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once "/www/includes/bourls.php";

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit ( 3000 ) ;

$time_start_stats = getmicrotime();

$tf_v_data_inclusao_start = date("Y-m-d H:i:s");

if(!isset($tf_v_data_inclusao_ini)) {
        $tf_v_data_inclusao_ini = date("d/m/Y");
}
$tf_v_data_inclusao_fim = $tf_v_data_inclusao_ini;

//echo "Data: ".$data_ini." - ".$data_fim."<br>";
//echo "Data: ".$tf_v_data_inclusao_ini." - ".$tf_v_data_inclusao_fim."<br>";
$data_ini = substr( $tf_v_data_inclusao_ini,6,4) . "/" . substr($tf_v_data_inclusao_ini,3,2) . "/" . substr($tf_v_data_inclusao_ini,0,2)." 00:00:00";
$data_fim = substr( $tf_v_data_inclusao_fim,6,4) . "/" . substr($tf_v_data_inclusao_fim,3,2) . "/" . substr($tf_v_data_inclusao_fim,0,2)." 23:59:59";

//echo "Data: ".$data_ini." - ".$data_fim."<br>";
$tf_v_data_inclusao_prev = date("Y-m-d", strtotime(date("Y-m-d", strtotime($data_ini)) . " -1 day"));
$tf_v_data_inclusao_next = date("Y-m-d", strtotime(date("Y-m-d", strtotime($data_ini)) . " +1 day"));

//echo "[".$tf_v_data_inclusao_prev.", ".$tf_v_data_inclusao_next."]<br>";
$tf_v_data_inclusao_prev = substr( $tf_v_data_inclusao_prev,8,2) . "/" . substr($tf_v_data_inclusao_prev,5,2) . "/" . substr($tf_v_data_inclusao_prev,0,4);
$tf_v_data_inclusao_next = substr( $tf_v_data_inclusao_next,8,2) . "/" . substr($tf_v_data_inclusao_next,5,2) . "/" . substr($tf_v_data_inclusao_next,0,4);
//echo "[".$tf_v_data_inclusao_prev.", ".$tf_v_data_inclusao_next."]<br>";


$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";

//paginacao
$p = $_REQUEST['p'];
if(!$p) $p = 1;
//	$registros = 50;
$registros_total = 0;

//Validacoes
$msg = "";	

//Recupera as vendas
if($msg == ""){

//		$sql  = "select ptr_id, ptr_id_base, ptr_data_inclusao, ptr_tipo_transacao, ptr_msg, ptr_fld_007, ptr_fld_011, ptr_delay from dist_vendas_pos_transacao vp where 1=1 ";		//"and (ptr_delay=0 or ptr_delay is null) ";	// "and ptr_id_base=0 "

        $sql = "select scc_id, scc_nconns, scc_data_inclusao from tb_stats_current_connections ";
        $sql .= "where 1=1 ";
        if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
                if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
                        $sql .= " and scc_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' ";

        $rs_total = SQLexecuteQuery($sql);
        if($rs_total) $registros_total = pg_num_rows($rs_total);

        $sql .= "order by scc_data_inclusao desc; ";	// " order by vp.ptr_id desc ";	//

        $rs_transacoes = SQLexecuteQuery($sql);
        if(!$rs_transacoes || pg_num_rows($rs_transacoes) == 0) $msg = "Nenhum registro encontrado.\n";

//echo $sql."<br>";
}

// config da imagem
$nwidth = 800;
$nheight = 120;

$iheight = 10;		// altura de cada faixa de comandos

$padding_left = 30; 
$padding_right = 10; 
$padding_top = 20; 
$padding_botton = 10;
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script>
$(function(){
    $("#tf_v_data_inclusao_ini").datepicker();
});
<!--
	function reload() {
		document.form1.action = "lista_nconns_gr.php";
		document.form1.submit();
	}
	function goToDay(sday) {
		document.form1.action = "lista_nconns_gr.php";
		document.form1.tf_v_data_inclusao_ini.value = sday;
		document.form1.submit();
	}
-->
</script>
<?php 
$pagina_titulo = "Meus Bilhetes";
$data_start = date("Y-m-d H:i:s", strtotime( substr( $tf_v_data_inclusao_ini,6,4) . "/" . substr($tf_v_data_inclusao_ini,3,2) . "/" . substr($tf_v_data_inclusao_ini,0,2)));

//echo $data_start."<br>";
?>
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
            <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
            <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
        </ol>
    </div>
	<table  border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr><td>&nbsp;</td></tr>
    <tr valign="top" align="center">
      <td>
			<form name="form1" method="post" action="lista_nconns_gr.php">
			<table border="0" cellspacing="01" width="90%" align="center">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center">&nbsp;</td>
    	          <td class="texto" align="center"><b>Data</b></td>
    	          <td>&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"></td>
    	          <td class="texto" align="center">
						<input type="button" id="oneday_back" name="oneday_back" class="btn btn-sm btn-info" value="<" title="Dia anterior (<?php echo $tf_v_data_inclusao_prev;?>)" onClick="goToDay('<?php echo $tf_v_data_inclusao_prev;?>')"> &nbsp; 
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">		   &nbsp; 
					   <input type="button" id="oneday_back" name="oneday_back" class="btn btn-sm btn-info" value=">" title="Dia próximo (<?php echo $tf_v_data_inclusao_next;?>)" onClick="goToDay('<?php echo $tf_v_data_inclusao_next;?>')">
				  </td>
				  <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info"></td>
    	        </tr>
			</table>
			</form>

			
		<?php	
//echo $tf_v_data_inclusao_start." = ".strtotime($tf_v_data_inclusao_start)."<br>";
			$i = 0;
			if($rs_transacoes) {

// Create a data set in range (50,70) and X-positions
$ndatapoints = pg_num_rows($rs_transacoes);
$samplerate = 240; 
$start = time();
$end = $start+$ndatapoints*$samplerate;
$data = array();
$xdata = array();
				$xmax = -1;	
				$ymax = -1;	
				$ymin = 999999999999999999;	
				$ylast = 0;	
				$ysum = 0;
				while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){ 

					$data[$i] = (int)$rs_transacoes_row['scc_nconns'];
//					$xdata[$i] = 1*(strtotime($rs_transacoes_row['scc_data_inclusao'])-strtotime($data_ini));
					$xdata[$i] = strtotime($rs_transacoes_row['scc_data_inclusao']);	

					// compute stats
					if($ymax<$data[$i]) $ymax = $data[$i];
					if($ymin>$data[$i]) $ymin = $data[$i];
					if($xmax<$xdata[$i]) $xmax = $xdata[$i];
					$ysum += $data[$i];


//echo $i.": ".substr($rs_transacoes_row['scc_data_inclusao'],11,8)." - ".$rs_transacoes_row['scc_nconns']."<br>";
//echo $i.": ".$xdata[$i]." (".is_numeric($xdata[$i]).") - ".$data[$i]." (".is_numeric($data[$i]).")<br>";
					$i++;
				}
//echo "data(".sizeof($data)."): [".$data[0].", ".$data[sizeof($data)-1]."]<br>";
				$ylast = $data[0];	

//echo "tf_v_data_inclusao_ini: ".$tf_v_data_inclusao_ini."<br>";

$datafull = array();
	list($usec, $sec) = explode(" ", microtime());
	$now = ((float)$usec + (float)$sec);
$datafull['fname'] = $now.".png";
$datafull['img_path'] = "/images/tmp/";
$datafull['chart_title'] = "max_db_connections"." (".$datafull['monthname'].")";
$datafull['monthname'] = date("Y-M-d", mktime(0,0,0 , substr($tf_v_data_inclusao_ini,3,2) , substr($tf_v_data_inclusao_ini,0,2) , substr($tf_v_data_inclusao_ini,6,4)));
$datafull['npoints'] = $ndatapoints;
$datafull['samplerate'] = $samplerate;
$datafull['data'] = $data;
$datafull['xdata'] = $xdata;
$datafull['ymax'] = $ymax;
$datafull['data_ini'] = substr($data_ini,0,10);
$datafull['data_fim'] = substr($data_fim,0,10);

/*
if(substr($data_ini,0,10)==substr($data_fim,0,10)) {
	$subtitle = get_day_of_week_short(substr($data_ini,0,10)).' - '.substr($data_ini,0,10);
} else {
	$subtitle = "datas ".substr($data_ini,0,10)." - ".substr($data_fim,0,10);
}
echo "Data: ".substr($data_ini,0,10)." - ".substr($data_fim,0,10)."<br>";
//echo "Data: ".$tf_v_data_inclusao_ini." - ".$tf_v_data_inclusao_fim."<br>";
$dia_ingles = date("w", strtotime(substr($data_ini,0,10)));
echo "$tf_v_data_inclusao_ini -> $dia_ingles<br>";
echo "subtitle: ".$subtitle."<br>";
*/
//echo "img_path+fname: http://www.e-prepag.com.br:8080".$datafull['img_path'].$datafull['fname']."<br>";

$strenc = urlencode(serialize($datafull));
//print $strenc . "<br>\n";
?>
<link rel="stylesheet" href="/css/css.css" type="text/css">
<script language="JavaScript" type="text/JavaScript">
<!--
	function get_image(){
		$(document).ready(function(){
            
            if(typeof trans1 === "undefined"){
                var trans1 = "";
            }
            
			$.ajax({
				type: "POST",
				url: "/ajax/ajax_nconns_gr.php",
				data: "strenc=<?php echo $strenc ?>",
				beforeSend: function(){
//					$("#nconns_img").html("Waiting...");	
				},
				success: function(){
//					alert('<?php echo $datafull['img_path'].$datafull['fname']; ?>');
//					$("#nconns_img").html(txt);	// " ("+txt.length+")<br>"+  // +'<br>\n'+trans1
					$("#nconns_img").attr("src", '<?php echo $datafull['img_path'].$datafull['fname']; ?>');+'<br>\n'+trans1
				},
				error: function(){
					$("#nconns_img").html("???");
				}
			});
		});
	}

	get_image();

//--></SCRIPT>
<p class="texto">Totais: <?php echo "último valor: <b style='color:#000000'>$ylast</b>, valor máximo: <b style='color:#000000'>$ymax</b>, valor mínimo: <b style='color:#000000'>$ymin</b>, média: <b style='color:#000000'>".@number_format(($ysum/count($data)), 2, '.', '.')."</b>, n pontos: <b style='color:#000000'>".count($data)."</b>"; ?></p>

<img id="nconns_img" src="/images/ajax-loading.gif"><br>
<?php

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="4">&nbsp;<font color='#FF0000'>Não foram encontrados registros para os valores escolhidos</font></td>
    	        </tr>
		<?php
			}
		?>


      </td>
    </tr>
	</table>
	<br>&nbsp;

	<table border='0' cellpadding='0' cellspacing='1' width='80%' bordercolor='#cccccc' style='border-collapse:collapse;'>	
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo $search_msg_stats . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>
	</div>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";

//Fechando Conexão
//pg_close($connid);

?>
</body>
</html>
