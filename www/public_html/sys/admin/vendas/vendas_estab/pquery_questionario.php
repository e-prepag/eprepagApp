<?php  

    require_once "../../../../../includes/constantes.php";
    require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

?>
<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

	set_time_limit ( 3000 ) ;
	$pos_pagina = $seg_auxilar;
?>
<?php 
	$time_start = getmicrotime();

	$dd_pin_status = 5;
//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "dd_mode: ".$dd_mode."<br>";
//echo "Submit: $Submit<br>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		$dd_mode = "S";
		$Submit = "Buscar";
	}

	if(!$dd_mode || ($dd_mode!='V')) {
		$dd_mode = "S";
	}
	$where_mode_data = "vg.vg_data_inclusao";	// default
	if($dd_mode=='S') $where_mode_data = "vg.vg_data_concilia";

	if(!$ncamp)            $ncamp           = 'trn_data';
	if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
	if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');
	if(!$inicial)          $inicial         = 0;
	if(!$range)            $range           = 1;
	if(!$ordem)            $ordem           = 1;
//	if($BtnSearch)         $inicial         = 0;
//	if($BtnSearch)         $range           = 1;
//	if($BtnSearch)         $total_table     = 0;
	if($BtnSearch && $BtnSearch!=1 ) {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

	$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
	$data_inicial_limite = '01/08/2004';
	$FrmEnviar = 1;
	
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "/sys/imagens/proxima.gif";
	$img_anterior = "/sys/imagens/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

//	$resuf = pg_exec($connid, "select uf from uf order by uf");
//	$resuf_except = pg_exec($connid, "select uf from uf order by uf");

	if($cb_opr_teste)
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') order by opr_ordem");
	else
		$resopr = pg_exec($connid, "select opr_nome, opr_codigo from operadoras where (opr_status = '".$operadora_ativada."') and (opr_codigo <> ".$opr_teste.") order by opr_ordem");

	if($dd_operadora)
	{
		$res_opr_info = pg_exec($connid, "select opr_codigo, opr_nome, opr_pin_online from operadoras where opr_codigo=".$dd_operadora."");
		$pg_opr_info = pg_fetch_array($res_opr_info);

		$dd_operadora_nome = $pg_opr_info['opr_nome'];
	
//		if($pg_opr_info['opr_pin_online'] == 0) {
			// Levanta lista de valores
			$sql = "select pin_valor, count(*) as n from pins where 1=1 ";
			if($dd_operadora) {
				$sql .= " and opr_codigo=".$dd_operadora." ";
			}
			if($dd_canal) {
				$sql .= " and pin_canal='".$dd_canal."' ";
			}
			$sql .= " group by pin_valor ";
			$sql .= " order by pin_valor;";

//			$resval = pg_exec($connid, "select pin_valor as valor from pins where opr_codigo='".$pg_opr_info['opr_codigo']."' group by pin_valor order by pin_valor");
			$resval = pg_exec($connid, $sql);

/*
		} else	{
			$resval = pg_exec($connid, "select valor_fixo as valor from pin_valor_lista t0, pin_valor_fixo t1 where t0.valor_lista_cod = t1.valor_lista_cod and opr_codigo = ".$pg_opr_info['opr_codigo']." group by valor_fixo order by valor_fixo");
			$res_opr_area = pg_exec($connid, "select oparea_codigo, area_nome from operadora_area where opr_codigo=".$pg_opr_info['opr_codigo']." order by oparea_codigo");
		}
*/
	} else {
		$tf_produto = null;
		$tf_pins = null;
	}

	if(!verifica_data($tf_data_inicial)) {
		$data_inic_invalida = true;
		$FrmEnviar = 0;
	}

	if(!verifica_data($tf_data_final)) {
		$data_fim_invalida = true;
		$FrmEnviar = 0;
	}

	if(qtde_dias($data_inicial_limite, $tf_data_inicial) < 0) {
		$data_inicial_menor = true;
		$FrmEnviar = 0;
	}

	// Processa a seleção de produtos no POST
	if ($tf_produto && is_array($tf_produto)) {
			if (count($tf_produto) == 1) {
				$tf_produto = $tf_produto[0];
			} else {
				$tf_produto = implode("|",$tf_produto);
			}	
		}
	if ($tf_produto && $tf_produto != "") {
		$tf_produto = explode("|",$tf_produto);	
	}

	$i = 0;
	$num_col = count($tf_produto);
	while ($i <= $num_col) {				
		$filtro['produto'.$i] = $tf_produto[$i];
		$palavra = urlencode($filtro['produto'.$i]);
		$varsel .= "&tf_produto[]=".$palavra;
		$i++;
	}

	// Processa a seleção de valores no POST
	if ($tf_pins && is_array($tf_pins)) {
			if (count($tf_pins) == 1) {
				$tf_pins = $tf_pins[0];
			} else {
				$tf_pins = implode("|",$tf_pins);
			}	
		}
	if ($tf_pins && $tf_pins != "") {
		$tf_pins = explode("|",$tf_pins);	
	}

	$produtos_query = "";
	$i = 0;
	$num_col_pin = count($tf_pins);
	while ($i <= $num_col_pin) {				
		$filtro['pin'.$i] = $tf_pins[$i];
		$palavra = urlencode($filtro['pin'.$i]);
		$varsel .= "&tf_pins[]=".$palavra;
		$i++;
	}

	if($FrmEnviar == 1) {

		$where_data_1a = "";
		$where_data_1b = "";
		$where_data_2 = "";
		$where_valor_1 = "";
		$where_valor_2 = "";
		$where_opr_1 = "";
		$where_opr_2 = "";
		$where_canal_1 = "";
		$where_canal_2 = "";

		if($tf_data_inicial && $tf_data_final) {
			$data_inic = formata_data(trim($tf_data_inicial), 1);
			$data_fim = formata_data(trim($tf_data_final), 1); 
//			$where_data_1 = " and ((t0.trn_data >= '".trim($data_inic)." 00:00') and (t0.trn_data <= '".trim($data_fim)." 23:59')) "; 
			$where_data_1a = " and ((vg_data_inclusao >= '".trim($data_inic)." 00:00:00') and (vg_data_inclusao <= '".trim($data_fim)." 23:59:59')) "; 
			$where_data_1b = " and (($where_mode_data >= '".trim($data_inic)." 00:00:00') and ($where_mode_data <= '".trim($data_fim)." 23:59:59')) "; 
			$where_data_2 = " and ((ve_data_inclusao >= '".trim($data_inic)." 00:00:00') and (ve_data_inclusao <= '".trim($data_fim)." 23:59:59')) "; 
		}

		
		if($dd_operadora) {
//			$where_opr_1 = " and (t0.opr_codigo = ".$dd_operadora.") ";
			$where_opr_1 = " and (vgm.vgm_opr_codigo = ".$dd_operadora.") ";
			if($dd_operadora_nome=='ONGAME') 
				$where_opr_2 = " and (ve_jogo = 'OG') ";
			elseif  ($dd_operadora_nome=='MU ONLINE') 
				$where_opr_2 = " and (ve_jogo = 'MU') ";
			elseif  ($dd_operadora_nome=='HABBO HOTEL') 
				$where_opr_2 = " and (ve_jogo = 'HB') ";
			else
				$where_opr_2 = " and (ve_jogo = 'xx') ";
		}
/*
/		if($dd_operadora=="") $dd_valor = "";
		if($dd_valor) {
//			$where_valor_1 = " and (t0.pin_valor = ".$dd_valor.") ";
			$where_valor_1 = " and (vgm.vgm_valor = ".$dd_valor.") ";
			$where_valor_2 = " and (ve_valor = ".$dd_valor.")";
		}
*/
		if($dd_canal) {
			if($dd_canal=="s") {
				$where_canal_1 = "  ";
				$where_canal_2 = " and (FALSE) ";
			}
			if($dd_canal=="p") {
				$where_canal_1 = " and (FALSE) ";
				$where_canal_2 = " ";
			}
		}
		// Adiciona lista de produtos ao query
		$values_query_vgm = "";
		$values_query_ve = "";
		if ($filtro['produto0'] != '') {
			$s = 0;
			$values_query_vgm .= " and  ( ";
			$values_query_ve .= " and  ( ";
			while ($filtro['produto'.$s] != '') {
				$com .= ", '".$filtro['produto'.$s]."' as produto".$s." ";
				$values_query_vgm .= " upper(vgm_nome_produto) = '".strtoupper(str_replace("'", "''", $filtro['produto'.$s]))."' ";
				$values_query_ve .= " upper(ve_jogo) = '".strtoupper(str_replace("'", "''", $filtro['produto'.$s]))."' ";
				$s++;
				if ($filtro['produto'.$s] != '') $values_query_vgm .= " or ";
//echo "PRODUTO: ".$filtro['produto'.$s]."<br>";
			}
			$values_query_vgm .= ") ";	
			$values_query_ve .= ") ";	
		} 
		// Adiciona lista de valores ao query
		$produtos_query_vgm = "";
		$produtos_query_ve = "";
		if ($filtro['pin0'] != '') {
			$s = 0;
			$produtos_query_vgm .= " and  ( ";
			$produtos_query_ve .= " and  ( ";
			while ($filtro['pin'.$s] != '') {
				$com .= ", '".$filtro['pin'.$s]."' as pin".$s." ";
				$produtos_query_vgm .= " vgm_valor = '".$filtro['pin'.$s]."' ";
				$produtos_query_ve .= " ve_valor = '".$filtro['pin'.$s]."' ";
				$s++;
				if ($filtro['pin'.$s] != '') {
					$produtos_query_vgm .= " or ";
					$produtos_query_ve .= " or ";
				}
			}
			$produtos_query_vgm .= ") ";
			$produtos_query_ve .= ") ";
		}


		$estat  = "select trn_data, vg_id, sum(total_face) as total_face, status, currency, item_type, payment_method, game_type, ug_sexo, ug_age, ug_country, ug_zip_code, canal 
			from (

				select vg.vg_data_inclusao as trn_data, vg_id, sum(vgm.vgm_valor*vgm.vgm_qtde) as total_face, vg.vg_ultimo_status as status, 
					'BRL' as currency, 'VC' as item_type, 
					'Cash' as payment_method, 
					ogp.ogp_quest_main_genre as game_type, 
					'-' as ug_sexo, 0 as ug_age, 
					'BR' as ug_country, (select ug_cidade || '/' || ug_estado from usuarios_games ug where vg.vg_ug_id = ug.ug_id) as ug_zip_code, 
					'LH' as canal 
				from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					inner join tb_dist_operadora_games_produto ogp on ogp.ogp_id = vgm.vgm_ogp_id
				where 1=1 ".$where_valor_1." ".$where_opr_1." and vg.vg_data_inclusao>='2008-01-01 00:00:00' and vg.vg_ultimo_status=5 
				".$where_data_1a." ".$where_canal_1." 
					and not (ogp_nome = 'TREINAMENTO')
					";
		$estat .= " $produtos_query_vgm $values_query_vgm ";
		$estat	.= "
				group by vg_id, vg.vg_data_inclusao, vg.vg_ultimo_status, vgm.vgm_valor, vg.vg_ug_id, ogp.ogp_quest_main_genre 

				union all
				
				select $where_mode_data as trn_data, vg_id, sum(vgm.vgm_valor*vgm.vgm_qtde) as total_face, vg.vg_ultimo_status as status, 
					'BRL' as currency, 'VC' as item_type, 
					(case when vg_pagto_tipo = 1 then 'Deposito' when vg_pagto_tipo = 2 then 'Boleto' else 'Online' end ) as payment_method, 		
					ogp.ogp_quest_main_genre as game_type, 
					(select ug_sexo from usuarios_games ug where vg.vg_ug_id  = ug.ug_id) as ug_sexo, coalesce(EXTRACT(year from AGE(NOW(), (select ug_data_nascimento from usuarios_games ug where vg.vg_ug_id = ug.ug_id) )), 0) as ug_age, 
					'BR' as ug_country, (select ug_cidade || '/' || ug_estado from usuarios_games ug where vg.vg_ug_id  = ug.ug_id) as ug_zip_code, 
					'Gamer' as canal 
				from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					inner join tb_operadora_games_produto ogp on ogp.ogp_id = vgm.vgm_ogp_id	
				where 1=1 ".$where_valor_1." ".$where_opr_1." and $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status=5 
							".$where_data_1b." ".$where_canal_1." 
						and not (ogp_nome = 'TREINAMENTO')
					";
		$estat .= " $produtos_query_vgm $values_query_vgm ";

// 	'OG' -> ogp_nome = 'Point Blank'	-> ogp_quest_credit_type='virtual coin', ogp_quest_main_genre = 'Action', ogp_quest_subgenre = 'FPS'
//	'HB' -> ogp_nome = 'Habbo Hotel'	-> ogp_quest_credit_type='virtual coin', ogp_quest_main_genre = 'Family', ogp_quest_subgenre = 'Children'

		$estat	.= "
				group by vg_id, $where_mode_data, vg.vg_ultimo_status, vgm.vgm_valor, vg.vg_pagto_tipo, ug_sexo, ug_age, vg.vg_ug_id, ogp.ogp_quest_main_genre

				union all

				select ve_data_inclusao as trn_data, ve_id as vg_id, sum(ve_valor) as total_face, 5 as status, 
					'BRL' as currency, 'VC' as item_type, 
					'Deposito' as payment_method, 
					case when ve_jogo='OG' then 'Action' when ve_jogo='HB' then 'Family' when ve_jogo='MU' then 'Action' else '??' end as game_type, 
					'-' as ug_sexo, 0 as ug_age, 
					'BR' as ug_country, (ve_cidade || '/' || ve_estado) as ug_zip_code, 
					'POS' as canal 
				from dist_vendas_pos ve 
				where 1=1 ".$where_data_2."".$where_valor_2." ".$where_opr_2." ".$where_canal_2." ";
		$estat .= " $produtos_query_ve $values_query_ve ";
		$estat	.= "
				group by ve_id, ve_data_inclusao, ve_opr_codigo, ve_valor, (ve_cidade || '/' || ve_estado), ve_jogo  

			) v
			";
//   "(case when ve_jogo='OG' then 'ONGAME' when ve_jogo='HB' then 'HABBO HOTEL' when ve_jogo='MU' then 'MU ONLINE' else '???' end)"

//	Para listar apenas registros da Rede Prepag
//				and ve_cod_rede=9999 
		
//Mounting as Special Sub-select by Union Platform

//$estatp1 = str_replace("changethis","estat_venda",$estat);
//$estatp2 = str_replace("changethis","estat_venda_2004",$estat);
//$estatp3 = str_replace("changethis","estat_venda_1sem05",$estat);
//$estat = "$estatp1 UNION $estatp2 UNION $estatp3 ";
//$estat = $estatp1;
//--The End


		$estat .= " group by trn_data, vg_id, total_face, currency, item_type, payment_method, game_type, ug_sexo, ug_age, ug_country, ug_zip_code, status, canal  
					order by trn_data desc, total_face desc"; 

		$res_count = pg_query($estat);
		$total_table = pg_num_rows($res_count);
	

/*
		$estat .= " order by ".$ncamp; 
*/
/*
		if($ordem == 0)
		{
			$estat .= " desc ";
			$img_seta = "../../../images/seta_down.gif";	
		}
		else
		{
			$estat .= " asc ";
			$img_seta = "../../../images/seta_up.gif";
		}

*/		$qtde_geral = 0;
		$valor_geral = 0;

		require_once $raiz_do_projeto."includes/gamer/AES.class.php";
		require_once $raiz_do_projeto."includes/gamer/chave256_tmp.php"; 

		//instanciando a classe de cryptografia
		$aes = new AES($chave256bits);
		$estat_encrypted = base64url_encode($aes->encrypt($estat));
//echo "<hr>".$estat."<br>";
//echo "<hr>".$estat_encrypted."<br>";

//echo "".str_replace("\n", "<br>\n", $estat)."<br>";
//echo $estat."<br>";
//die("Stop");
		$res_geral = pg_exec($connid, $estat);
		while($pg_geral = pg_fetch_array($res_geral))
		{
			$qtde_geral += $pg_geral['quantidade'];
			$valor_geral += $pg_geral['total_face'];
		}

		$estat .= " limit ".$max; 
		$estat .= " offset ".$inicial;

	}
	else
		$estat = "select est_codigo from estabelecimentos where est_codigo = 0";
		
//	trace_sql($estat, "Arial", 2, "#666666", 'b');
$sql_transform=$estat;
	
	$resestat = pg_exec($connid, $estat);

	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		
	$varsel  = "&cb_opr_teste=$cb_opr_teste&cb_estab_teste=$cb_estab_teste";
	$varsel .= "&tf_data_final=$tf_data_final&tf_data_inicial=$tf_data_inicial&dd_canal=$dd_canal";
	$varsel .= "&tf_codigo_estab=$tf_codigo_estab&tf_nome_estab=$tf_nome_estab&dd_uf=$dd_uf&dd_uf_except=$dd_uf_except";
	$varsel .= "&dd_operadora=$dd_operadora&dd_opr_area=$dd_opr_area&fpin=$fpin";

	if ($tf_pins && is_array($tf_pins)) {
		if (count($tf_pins) == 1) {
			$tf_pins = $tf_pins[0];
		} else {
			$tf_pins = implode("|",$tf_pins);
		}
	}
	if ($tf_pins && $tf_pins != "") {
		$tf_pins = explode("|",$tf_pins);	
	}

?>
<html>
<head>

<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<title>E-Prepag</title>
<script language='javascript' src='/js/<?php echo LANG_NAME_CALENDAR_FILE; ?>'></script>
<script language="javascript" src="/js/jquery.js"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
	function GP_popupConfirmMsg(msg) { //v1.0
	  document.MM_returnValue = confirm(msg);
	}

//	$(document).ready(function () {
//	});

	function load_caixas(){
		
		ResetCheckedValue();
		<?php 	$i = 0;
		$parametros = ",'tf_produto[]': [";
		while ($i <= $num_col ) {
		?>	
		var tf_produto<?php echo $i?> = "<?php echo $tf_produto[$i]?>" ;	
		<?php	
		$parametros .= "\"$tf_produto[$i]\"";
			$i++;
			if ( $i <= $num_col) {
				$parametros .= ",";
			}
		}

		$parametros .= "]"; ?>
		
		var opr_codigo = 0;
		if(document.getElementById('dd_operadora')) {
			opr_codigo = document.getElementById('dd_operadora').value;
		}
				// values in dd_pin_status start with 'st' to avoid geting null when status = 0
				$.ajax({
					type: "POST",
					url: "/ajax/gamer/ajaxProdutoComPesquisaVendas.php",
					data: {id:+((opr_codigo>0)?opr_codigo:-1)<?php echo $parametros?>},
					beforeSend: function(){
						$('#mostraValores').html("Aguarde...");
					},
					success: function(html){
						//alert('valor');
						$('#mostraValores').html(html);
					},
					error: function(){
						alert('erro valor');
					}
				});// fim ajax
	}	// fim function 

	function v_precos() {
	
		ResetCheckedValuePin();
		<?php 	$i = 0;
			$parametros = ",'tf_pins[]': [";
			while ($i < $num_col_pin ) {
		?>	
		var tf_pins<?php echo $i?> = "<?php echo $tf_pins[$i]?>" ;	
		<?php	
				$parametros .= "'$tf_pins[$i]'";
				$i++;
				if ( $i < $num_col_pin) {
					$parametros .= ",";
				}
			}
			$parametros .= "]"; 
		?>

		var selectedItems = new Array();

		var opr_codigo = 0;
		if(document.getElementById('dd_operadora')) {
			opr_codigo = document.getElementById('dd_operadora').value;
		}
		
		$.ajax({
					
			type: "POST",
			url: "/ajax/gamer/ajaxTipoComPesquisaVendas.php",
			data: 
				{id:+((opr_codigo>0)?opr_codigo:-1)<?php echo $parametros?>},
			beforeSend: function(){
					$('#mostraValores2').html("Aguarde...");
				},
			success: function(html){
					$('#mostraValores2').html(html);
				},
			error: function(){
					alert('erro ao carregar valores');
				}

		}); //fim ajax

	}// fim function reload precos


	function ResetCheckedValue() {
		// reset the $varsel var 'tf_pins'
		if(document.form1) {
			if(document.form1.tf_produto) {
				document.form1.tf_produto.value = '';
			}

			// reset the checkboxes with values 'tf_pins[]'
			var chkObj = document.form1.elements.length;
			var chkLength = chkObj.length;
			for(var i = 0; i < chkLength; i++) {
				var type = document.form1.elements[i].type;
				if(type=="checkbox" && document.form1.elements[i].checked) {
					chkObj[i].checked = false;
				}
			}
		}
	}

	function ResetCheckedValuePin() {
		// reset the $varsel var 'tf_pins'
		if(document.form1) {
			if(document.form1.tf_pins) {
				document.form1.tf_pins.value = '';
			}

			// reset the checkboxes with values 'tf_pins[]'
			var chkObj = document.form1.elements.length;
			var chkLength = chkObj.length;
			for(var i = 0; i < chkLength; i++) {
				var type = document.form1.elements[i].type;
				if(type=="checkbox" && document.form1.elements[i].checked) {
					chkObj[i].checked = false;
				}
			}
		}
	}

	function gerarArquivo() {
		var sql_estat = "<?php echo $estat_encrypted ?>";
		$.ajax({
				type: "POST",
				url: "pquery_questionario_arquivo.php",
				data: "sql_estat="+sql_estat,	
				beforeSend: function(){
					$("#area").html("<img src='/sys/imagens/ajax-loader.gif' />");
				},
				success: function(html){
					$("#area").html(html);
					//alert(html);
				},
				error: function(){
					alert('erro ao carregar valores (gerarArquivo)');
				}				
			});
	}
//-->
</script>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="903" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr> 
    <td height="22,5" valign="center" bgcolor="#00008C" width="903"><p><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><b><b><b><?php echo LANG_PINS_PAGE_TITLE_1; ?><br>
        </b></b></b></font></p></td>
  </tr>
  <tr> 
    <td align="center" valign="top" bgcolor="#FFFFFF"> <table width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top"> 
          <td height="100%"> <form name="form1" method="post" action="">
		  <input type="hidden" name="dd_pin_status" id="dd_pin_status" value="<?php echo $dd_pin_status ?>">
              <table width="100%" border="0" cellpadding="2" cellspacing="2">
                <tr bgcolor="#00008C"> 
                  <td colspan="11" bgcolor="#ECE9D8"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_SEARCH_1; ?></font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_START_DATE; ?>:</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input name="tf_data_inicial" type="text" class="form" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                    <a href="#"><img src="/sys/imagens/cal.gif" width="16" height="16" alt="Calendário" onClick="popUpCalendar(this, form1.tf_data_inicial, 'dd/mm/yyyy')" border="0" align="absmiddle"></a> 
                    </font></td>
                  <td width="90"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_END_DATE; ?>:</font></td>
                  <td colspan="3"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <input name="tf_data_final" type="text" class="form" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                    <a href="#"><img src="/sys/imagens//cal.gif" width="16" height="16" alt="Calendário" onClick="popUpCalendar(this, form1.tf_data_final, 'dd/mm/yyyy')" border="0" align="absmiddle"></a> 
                    </font></td>
                  <td width="62">&nbsp;</td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_CHANNEL; ?> </font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                    <select name="dd_canal" id="dd_canal" class="combo_normal" onChange="document.form1.submit()">
                      <option value="" <?php  if($dd_canal!="s" and $dd_canal!="p") echo "selected" ?>><?php echo LANG_PINS_ALL; ?></option>
                      <option value="s" <?php  if($dd_canal=="s") echo "selected" ?>>Site</option>
                      <option value="p" <?php  if($dd_canal=="p") echo "selected" ?>>POS</option>
                    </select>
					</td>
                  <td width="90"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><nobr><?php echo LANG_PINS_REPORT_TYPE; ?>: &nbsp;</nobr></font></td>
                  <td colspan="3"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
					<?php  if($_SESSION["tipo_acesso_pub"]=='PU') { ?>
						<span style="font-weight: bold"><?php echo LANG_PINS_OUT; ?></span>
						<input type="hidden" name="dd_mode" id="dd_mode" value="<?php echo $dd_mode?>">
					<?php  } else { ?>	
					<select name="dd_mode" id="dd_mode" class="combo_normal" onChange="document.form1.submit()">
					  <option value="S" <?php  if($dd_mode=="S") echo "selected" ?>><?php echo LANG_PINS_OUT; ?></option>
					  <option value="V" <?php  if($dd_mode=="V") echo "selected" ?>><?php echo LANG_PINS_SALES; ?></option>
					</select>
					<?php 
					  } 
					?>
					</font></td>
                  <td width="62"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_OPERATOR; ?>:</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
					<?php 
						if($_SESSION["tipo_acesso_pub"]=='PU') {
					?>
						<?php echo $_SESSION["opr_nome"]?>
						<input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
					<?php 
					  } else {
					?>

                    <select name="dd_operadora" id="dd_operadora" class="combo_normal">
                      <option value=""><?php echo LANG_PINS_ALL_OPERATORS; ?></option>
                      <?php  while ($pgopr = pg_fetch_array ($resopr)) { ?>
                      <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?> (<?php echo $pgopr['opr_codigo']?>)</option>
                      <?php  } ?>
                    </select>
					<?php 
					  } 
					?>
                    </font></td>
                  <td ><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_VALUE; ?>:</font></td>
                  <td colspan="3"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
					<div id='mostraValores'></div> 
                    </font></td>
                  <td width="62"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> &nbsp;</font></div></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> &nbsp;</font></td>
                  <td ><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_PRODUCT; ?>:</font></td>
                  <td colspan="3"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
					<div id='mostraValores2'></div>

                    </font></td>
                  <td width="62"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> &nbsp;</font></div></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="96"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                  <td width="196"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                  <td ><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
                  <td colspan="3"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> &nbsp;</font></td>
                  <td width="62"><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                      <input type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="botao_search">
                      </font></div></td>
                </tr>

			  </table>
            </form>
            <table border="0" cellpadding="0" cellspacing="0" width="897">
              <tr> 
                <td> 
                  <?php 
					if($data_inic_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_PINS_START_DATE."</b></font>";
					if($data_fim_invalida == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_PINS_END_DATE."</b></font>";
					if($data_inicial_menor == true) echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>".LANG_PINS_COMP_DATE_START_WITH_END."</b></font>";
				?>
                </td>
              </tr>
            </table>
            <table border='0' width="100%" cellpadding="2" cellspacing="1">
              <tr> 
                <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                  <?php  if($total_table > 0) { ?>
                  <?php echo LANG_SHOW_DATA; ?> <strong><?php  echo $inicial + 1 ?></strong> 
                  <?php echo LANG_TO; ?> <strong><?php  echo $reg_ate ?></strong> <?php echo LANG_FROM; ?> <strong><?php  echo $total_table ?></strong></font> 
                  <?php  } ?>
                </td>
                <td><div align="right"><a href="https://<?php  echo $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] ?>/sys/admin/commerce/index.php"><img src="/sys/imagens/voltar_menu.gif" width="107" height="15" border="0"></a> 
                    <?php 
					$_SESSION['sqldata']=$sql_transform;
					?>
                    </div>
                </td>
              </tr>
			  <tr valign="top"> 
			    <td height="100%" bgcolor="#CCCCCC" id="area" class='texto' align="center" colspan="3">
				  <div id="download" onClick="gerarArquivo();" onMouseOver="this.style.backgroundColor='#CCFF99'" onMouseOut="this.style.backgroundColor='#CCCCCC'"><strong>Gerar Arquivo</strong></div>
			    </td>
			  </tr>
            </table>
            <table width="100%" border='0' cellpadding="2" cellspacing="1">
                <?php 
				if($ordem == 1)
					$ordem = 0;
				else
					$ordem = 1;
				?>
              <tr bgcolor="#00008C"> 
                <td colspan="9"><div align="center"> <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></strong></div></td>
                <td colspan="2"><div align="center"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "Demographic"; ?>
                    </font></strong></div></td>
                <td colspan="2"><div align="center"> <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></strong></div></td>
				
			  </tr>
              <tr bgcolor="#00008C"> 
                <td align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo LANG_PINS_DATE; ?></font></strong></td>
                <td align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "vg_id"; ?></font></strong></td>
                <td align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "status venda"; ?></font></strong></td>
                <td><div align="right"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "Canal"; ?></font></strong></div></td>
                <td><div align="right"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "Price"; ?></font></strong></div></td>
                <td><div align="center"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "Currency"; ?>
                    </font></strong></div></td>
                <td><div align="center"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "Item type"; ?>
                    </font></strong></div></td>
                <td><div align="center"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "Payment method"; ?>
                    </font></strong></div></td>
                <td><div align="center"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "Game type"; ?>
                    </font></strong></div></td>
                <td><div align="center"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "Sex"; ?>
                    </font></strong></div></td>
                <td><div align="center"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "Age"; ?>
                    </font></strong></div></td>
                <td><div align="center"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "Country"; ?>
                    </font></strong></div></td>
                <td><div align="center"> 
                    <strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><?php echo "City/State"; ?>
                    </font></strong></div></td>
				
			  </tr>
              <?php 

					$cor1 = $query_cor1;
					$cor2 = $query_cor1;
					$cor3 = $query_cor2;
					while ($pgrow = pg_fetch_array($resestat))
					{
						$valor = true;

						$qtde_total_tela += $pgrow['quantidade'];
						$valor_total_tela += $pgrow['total_face'];
						$slink = (
								($pgrow['canal']=="Gamer") ? "https://" . $_SERVER['SERVER_NAME'] . ":8080/gamer/vendas/com_venda_detalhe.php?venda_id=".$pgrow['vg_id'] : 
								(	
									($pgrow['canal']=="LH") ? "https://" . $_SERVER['SERVER_NAME'] . ":8080/pdv/vendas/com_venda_detalhe.php?venda_id=".$pgrow['vg_id'] : 
										(
											($pgrow['canal']=="POS")?"" : ""
										)
								)
							);

				?>
              <tr bgcolor="<?php  echo $cor1 ?>" onmouseover="bgColor='#CFDAD7'" onmouseout="bgColor='<?php echo $cor1 ?>'"> 
                <td title="<?php echo substr($pgrow['trn_data'], 0, 19) ?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo formata_data($pgrow['trn_data'], 0) ?></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo "<a href='".$slink."'>".$pgrow['vg_id']."</a>" ?></font></div></td>
				<td><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['status'] ?></font></div></td>

				<td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['canal'] ?></font></div></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo number_format(($pgrow['total_face']), 2, ',', '.') ?></font></div></td>

                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['currency'] ?></font></div></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['item_type'] ?></font></div></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['payment_method'] ?></font></div></td>

				<td><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['game_type'] ?></font></div></td>

				<td><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['ug_sexo'] ?></font></div></td>
				<td><div align="center"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['ug_age'] ?></font></div></td>
				<td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><?php  echo $pgrow['ug_country'] ?></font></div></td>
				<td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><nobr><?php  echo $pgrow['ug_zip_code'] ?></nobr></font></div></td>

              </tr>
              <?php 
				 		if($cor1 == $cor2)
							$cor1 = $cor3;
						else
							$cor1 = $cor2;
					}
			 		if (!$valor) { ?>
              <tr bgcolor="<?php  echo $cor1 ?>" onmouseover="bgColor='#CFDAD7'" onmouseout="bgColor='<?php echo $cor1 ?>'"> 
                <td colspan="10"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                    <?php echo LANG_NO_DATA; ?>.<br>
                    <br>
                    </strong></font></div></td>
              </tr>
              <?php  } else { ?>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="2"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo LANG_PINS_SUBTOTAL; ?></strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($qtde_total_tela, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="2"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($valor_total_tela, 2, ',', '.') ?></strong></font></div></td>
                <td colspan="12"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
              </tr>
              <tr bgcolor="#E4E4E4"> 
                <td colspan="2"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo LANG_PINS_TOTAL; ?></strong></font></td>
                <td><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($qtde_geral, 0, ',', '.') ?></strong></font></div></td>
                <td colspan="2"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php  echo number_format($valor_geral, 2, ',', '.') ?></strong></font></div></td>
                <td colspan="12"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
              </tr>
              <?php 
					paginacao_query($inicial, $total_table, $max, '13', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
				?>
                <tr bgcolor="#E4E4E4"> 
                <td colspan="13" bgcolor="#FFFFFF"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><strong> 
                  <?php echo LANG_PINS_LAST_MSG; ?>. </strong></font></td>
              </tr>
              <?php  } ?>
	          <?php 
					$time_end = getmicrotime();
					$elapsed_time = $time_end - $time_start;
				?>
              <tr> 
                <td height="52" colspan="13" bgcolor="#FFFFFF"><p><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><?php  echo LANG_POS_SEARCH_MSG." ".number_format($elapsed_time, 2, '.', '.')." ".LANG_POS_SEARCH_MSG_UNIT ?> 
                    </font></p>
                  </td>
              </tr>
            </table>
			</td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td colspan="3">
      <?php  require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php"; ?>
      <div align="center"></div></td></tr>
</table>
</body>
</html>
<script>

<?php
	if($dd_operadora) {
?>
	$(document).ready(function () {
		load_caixas(); 
		v_precos();
	});
<?php
	}
?>

$(document).ready(function () {
	$('#dd_operadora').change( function() { 
		load_caixas(); 
		v_precos();
	});
});
</script>
