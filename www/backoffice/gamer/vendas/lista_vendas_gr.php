<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once "/www/includes/bourls.php";

set_time_limit ( 300 ) ;

	$time_start_stats = getmicrotime();

	$tf_v_data_inclusao_start = date("Y-m-d H:i:s");

	if(!isset($tf_v_data_inclusao_ini)) {
		$tf_v_data_inclusao_ini = date("d/m/Y");
	}
//	if(!isset($tf_v_data_inclusao_fim)) {
		$tf_v_data_inclusao_fim = $tf_v_data_inclusao_ini;
//	}

//echo "Data: ".$data_ini." - ".$data_fim."<br>";
//echo "Data: ".$tf_v_data_inclusao_ini." - ".$tf_v_data_inclusao_fim."<br>";
	$data_ini = substr( $tf_v_data_inclusao_ini,6,4) . "/" . substr($tf_v_data_inclusao_ini,3,2) . "/" . substr($tf_v_data_inclusao_ini,0,2);
	$data_fim = substr( $tf_v_data_inclusao_fim,6,4) . "/" . substr($tf_v_data_inclusao_fim,3,2) . "/" . substr($tf_v_data_inclusao_fim,0,2);
	if (!isset($tf_v_hour) || $tf_v_hour=="-") {
		$data_ini .= " 00:00:00";
		$data_fim .= " 23:59:59";
	} else {
		$data_ini .= " ".$tf_v_hour.":00:00";
		$data_fim .= " ".$tf_v_hour.":59:59";
	}

//echo "data_ini: $data_ini<br>";
//echo "data_fim: $data_fim<br>";
	$data_ini_short = substr($data_ini,0,10);
	$data_fim_short = substr($data_fim,0,10);
	$b_datas_iguais = ($data_ini_short==$data_fim_short);
//echo "data_ini_short: $data_ini_short<br>";
//echo "data_fim_short: $data_fim_short<br>";
//echo "Equals?: ".(($data_ini_short==$data_fim_short)?"YES":"no")."<br>";

	$dia_da_semana = "";
	if($b_datas_iguais) {
		$i_dow = date('w', strtotime($data_ini));
		$dia_da_semana = (($i_dow==0)?"Dom":(($i_dow==6)?"Sab":($i_dow+1)."aF"));
	}


//echo "Data: ".$data_ini." - ".$data_fim."<br>";
	$tf_v_data_inclusao_prev = date("Y-m-d", strtotime(date("Y-m-d", strtotime($data_ini)) . " -1 day"));
	$tf_v_data_inclusao_next = date("Y-m-d", strtotime(date("Y-m-d", strtotime($data_ini)) . " +1 day"));

//echo "[".$tf_v_data_inclusao_prev.", ".$tf_v_data_inclusao_next."]<br>";
	$tf_v_data_inclusao_prev = substr( $tf_v_data_inclusao_prev,8,2) . "/" . substr($tf_v_data_inclusao_prev,5,2) . "/" . substr($tf_v_data_inclusao_prev,0,4);
	$tf_v_data_inclusao_next = substr( $tf_v_data_inclusao_next,8,2) . "/" . substr($tf_v_data_inclusao_next,5,2) . "/" . substr($tf_v_data_inclusao_next,0,4);
//echo "[".$tf_v_data_inclusao_prev.", ".$tf_v_data_inclusao_next."]<br>";


	if(!$tf_pagto_tipo)
		$tf_pagto_tipo = '5';

	// Por enquanto apenas as vendas completas
	$tf_ultimo_status = '5';

	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";

	//paginacao
	$p = $_REQUEST['p'];
	if(!$p) $p = 1;
//	$registros = 50;
	$registros_total = 0;

	//Validacoes
	$msg = "";	

	//Recupera as vendas

//					sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, 
		$sql = "select extract(hour from vg.vg_data_inclusao) as hora_dados, extract(minute from vg.vg_data_inclusao) as minute_dados, 
					(case when vgm_opr_codigo = 78 then 0 else sum(vgm.vgm_valor * vgm.vgm_qtde) end ) as valor, 
					sum(vgm.vgm_qtde) as qtde_itens
				from tb_venda_games vg 
					inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id 
				where 1=1 \n";
				if($tf_ultimo_status) {
					$sql .= "	and vg.vg_ultimo_status = '$tf_ultimo_status' \n";
				}
				$sql .= "	and (not ug.ug_id = '7909') \n";
				if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) {
					if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0) {
						$sql .= "	and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' \n";
					}
				}
//				if($tf_pagto_tipo) {
//					$sql .= "	and vg.vg_pagto_tipo = $tf_pagto_tipo ";
//				}
				$sql .= "	and vg.vg_pagto_tipo >= $tf_pagto_tipo \n";

				$sql .= "group by extract(hour from vg.vg_data_inclusao), extract(minute from vg.vg_data_inclusao), vgm_opr_codigo 
						order by extract(hour from vg.vg_data_inclusao), extract(minute from vg.vg_data_inclusao) ";

//if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//}
		$rs_transacoes = SQLexecuteQuery($sql);
		if(!$rs_transacoes || pg_num_rows($rs_transacoes) == 0) $msg = "Nenhum registro encontrado.\n";
//		else echo "Query succesfull<br>";

if($_SESSION['userlogin_bko']=='REINALDO') {
//echo str_replace("\n", "<br>\n", $sql)."<br>";
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
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       $("#tf_v_data_inclusao_ini").datepicker();
    });
    
<!--
	function reload() {
		document.form1.action = "lista_vendas_gr.php";
		document.form1.submit();
	}
	function goToDay(sday) {
		document.form1.action = "lista_vendas_gr.php";
		document.form1.tf_v_data_inclusao_ini.value = sday;
		document.form1.submit();
	}
-->
</script>
<?php
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
    <tr valign="top" align="center">
      <td>
			<form name="form1" method="post" action="lista_vendas_gr.php">
			<table border="0" cellspacing="1" width="90%" align="center">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center">&nbsp;</td>
    	          <td class="texto" align="center"><b>Data</b></td>
    	          <td>&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"></td>
    	          <td class="texto" align="center">
						
					  <input name="tf_v_data_inclusao_ini" type="text" class="formSmall" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
				  </td>
				  <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info"></td>
    	        </tr>
			</table>
			</form>

			
		<?php	
//echo $tf_v_data_inclusao_start." = ".strtotime($tf_v_data_inclusao_start)."<br>";

			// prepara dados para histograma
			$data_hora = array();
			for($i=0;$i<24;$i++) { $data_hora[$i] = 0;	}

			$i = 0;
			if($rs_transacoes) {

				// Create a data set in range (50,70) and X-positions
				$ndatapoints = pg_num_rows($rs_transacoes);
				//echo "ndatapoints: ".$ndatapoints."<br>";

				$zmax = -1;	
				$zsum = 0;

				$datax = array();
				$datay = array();
				$dataz = array();
				while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){ 

					// hora_dados, minute_dados, valor, qtde_itens 

					$ix = $rs_transacoes_row['minute_dados'];
					$iy = $rs_transacoes_row['hora_dados'];
					$datax[$i] = $ix;
					$datay[$i] = $iy;	//	$iy+1.*$ix/60;
					$dataz[$ix][$iy] = $rs_transacoes_row['valor'];

					// compute stats
					if($zmax<$dataz[$ix][$iy]) $zmax = $dataz[$ix][$iy];
					$zsum += $dataz[$ix][$iy];

					// counter
					$i++;
				}

//print_r2($dataz);
				foreach($dataz as $keyx => $valx) {
					foreach($valx as $keyy => $valy) {
						// Dados de histograma
						$data_hora[$keyy] += $valy;
//echo "[$keyx, $keyy] - (valor: $valy ) - {$data_hora[$keyy]}<br>";
					}
				}

				$ymax = -1;
				$ysum = 0;
				for($i=0;$i<count($data_hora);$i++) {
					if($ymax<$data_hora[$i]) $ymax = $data_hora[$i];
					$ysum += $data_hora[$i];
				}

//echo "ymax: $ymax<br>";
//echo "ysum: $ysum<br>";
				$hist = array();
				$hist_cum = 0;
				if($ysum==0) $ysum = 1;
				for($i=0;$i<count($data_hora);$i++) {
					$hist_cum += $data_hora[$i];
					$hist[$i] =100*$hist_cum/$ysum;
				}

//print_r2($data_hora);
//print_r2($hist);

				$datafull = array();
					list($usec, $sec) = explode(" ", microtime());
					$now = ((float)$usec + (float)$sec);
				$datafull['fname'] = $now.".png";
				$datafull['img_path'] = "/images/tmp/";
				$datafull['chart_title'] = "Vendas por hora/minuto \nem ".(($b_datas_iguais) ? ("".$data_ini_short." ($dia_da_semana)") : ("(".$data_ini_short." - ".$data_fim_short.")"));
				$datafull['npoints'] = $ndatapoints;
				$datafull['datax'] = $datax;
				$datafull['datay'] = $datay;
				$datafull['dataz'] = $dataz;
				$datafull['zmax'] = $zmax;
				$datafull['data_ini'] = substr($data_ini,0,10);
				$datafull['data_fim'] = substr($data_fim,0,10);



				$strenc = urlencode(serialize($datafull));

//print_r2($datafull);
//echo "zmax = $zmax<br>";
//print_r2($dataz);
//print_r2($data_hora);

//echo "<hr>".$strenc."<br>\n";

//print $strenc . "<br>\n";
/*
$strenc_col = "";
$ncols = 80;
for($k=0;$k<strlen($strenc);$k++) {
	$strenc_col .= $strenc[$k];
	if($k%$ncols==0 && $k!=0)  $strenc_col .= "<br>\n";
}
echo $strenc_col;
*/

				$datafullh = array();
					list($usec, $sec) = explode(" ", microtime());
					$now = ((float)$usec + (float)$sec);
				$datafullh['fname'] = $now."1.png";	// add 1 to differenciate from the previous image, in case it runs too fast (-> avoid two images with the same name)
				$datafullh['img_path'] = "/images/tmp/";
				$datafullh['chart_title'] = "Histograma de Vendas\nem ".(($b_datas_iguais) ? ("".$data_ini_short." ($dia_da_semana)") : ("(".$data_ini_short." - ".$data_fim_short.")"));
				$datafullh['npoints'] = count($data_hora);
				$datafullh['data_hora'] = $data_hora;
				$datafullh['hist'] = $hist;
					$ymax = 1000;
				$datafullh['ymax'] = $ymax;
				$datafullh['data_ini'] = substr($data_ini,0,10);
				$datafullh['data_fim'] = substr($data_fim,0,10);

				$strench = urlencode(serialize($datafullh));

//print_r2($datafullh);
//print_r2($data_hora);
//print_r2($hist);

?>
<script language="JavaScript" type="text/JavaScript">
    if(typeof trans1 == "undefined")
        var trans1 = "";
<!--
	function get_image(){
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/pagamento/ajax_vendas_gr.php",
				data: "strenc=<?php echo $strenc ?>",
				beforeSend: function(){
//					$("#ncapturas_img").html("Waiting...");	
				},
				success: function(){
//					alert("Primeiro:" + '<?php echo $datafull['img_path'].$datafull['fname']; ?>');
//					$("#ncapturas_img").html(txt);	// " ("+txt.length+")<br>"+  // +'<br>\n'+trans1
					$("#vendas_img").attr("src", '<?php echo $datafull['img_path'].$datafull['fname']; ?>');+'<br>\n'+trans1
				},
				error: function(){
					$("#vendas_img").html("???");
				}
			});
		});
	}

	function get_image_hist(){
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/pagamento/ajax_vendas_hist_gr.php",
				data: "strench=<?php echo $strench ?>",
				beforeSend: function(){
//					$("#ncapturas_img").html("Waiting...");	
				},
				success: function(){
//					alert("Segundo: " + '<?php echo $datafullh['img_path'].$datafullh['fname']; ?>');
//					$("#ncapturas_img").html(txt);	// " ("+txt.length+")<br>"+  // +'<br>\n'+trans1
					$("#vendas_img_hist").attr("src", '<?php echo $datafullh['img_path'].$datafullh['fname']; ?>');+'<br>\n'+trans1
				},
				error: function(request, error){
                                        alert("Erro: " + error);
					$("#vendas_img_hist").html("???");
				}
			});
		});
	}

	get_image();
	get_image_hist();


//--></SCRIPT>
<p class="texto"><?php echo "Total: <b style='color:#000000'>".number_format($zsum, 2, '.', '.')."</b>, valor máximo: <b style='color:#000000'>".number_format($zmax, 2, '.', '.')."</b>, média: <b style='color:#000000'>".@number_format(($zsum/count($datax)), 2, '.', '.')."</b>, n pontos: <b style='color:#000000'>".count($datax)."</b>"; ?></p>
<img id="vendas_img" src="/images/ajax-loading.gif"><br>
<br>&nbsp;
<img id="vendas_img_hist" src="/images/ajax-loading.gif"><br>
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

<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>

</body>
</html>
