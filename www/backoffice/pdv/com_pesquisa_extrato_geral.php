<?php 
ob_start();
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/inc_Pagamentos.php"; 
require_once $raiz_do_projeto."includes/gamer/func_conta_dez_dias.php";
require_once "/www/includes/bourls.php";

$time_start = getmicrotime();

//if(!isset($inicial) || !$inicial)  $inicial     = 0;
if(!isset($range) || !$range)    $range       = 1;
if(!isset($ordem) || !$ordem)    $ordem       = 0;
if(isset($Pesquisar) && $Pesquisar) $total_table = 0;
$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 2000; //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;
$registros	  = $max;

$dd_balancos = $_REQUEST['dd_balancos'];
$dd_boletos = $_REQUEST['dd_boletos'];
$dd_compras = $_REQUEST['dd_compras'];
$lan_tipo = $_REQUEST['lan_tipo'];
if(!isset($tf_v_data_inclusao_ini) || !$tf_v_data_inclusao_ini) {
	$hoje = date("d/m/Y");
	//$hoje = "20/03/2008";
	$tf_v_data_inclusao_ini = data_menos_n($hoje,5);
	
}	
if(!isset($tf_v_data_inclusao_fim) || !$tf_v_data_inclusao_fim) $tf_v_data_inclusao_fim = $hoje;
$tf_v_codigo = $_REQUEST['tf_v_codigo'];
$nome_fantasia = strtoupper($_REQUEST['nome_fantasia']);
$codigo_lan = $_REQUEST['codigo_lan'];

if (isset($tf_v_codigo)){
	$varse1 .= "&tf_v_codigo=$tf_v_codigo";
}

if (isset($tf_v_data_inclusao_ini) && isset($tf_v_data_inclusao_fim)) {
	$varse1 .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
}
	
if (isset($nome_fantasia)){
	$varse1 .= "&nome_fantasia=$nome_fantasia";
}
		
if (isset($codigo_lan)){
	$varse1 .= "&codigo_lan=$codigo_lan";
}

$lh_ug_perfil_saldo		= "";
$lh_ug_perfil_limite	= "";
$lh_ug_credito_pendente = "";
$lh_n					= "";
$lh_n1					= "";
$lh_valor				= "";
$lh_repasse				= "";
$lh_cor_codigo			= "";
$lh_cor_venda_bruta		= "";
$lh_cor_venda_liquida	= "";

if ($codigo_lan>0){
		$sql  = "select ug_perfil_saldo, ug_perfil_limite, ug_credito_pendente, ug_risco_classif from dist_usuarios_games where ug_id=$codigo_lan";
//echo $sql . "<br>";
		$rs_LH = SQLexecuteQuery($sql);
		if(!$rs_LH || pg_num_rows($rs_LH) == 0) $msg = "PDV $codigo_lan não encontrado.\n";
		else {				
			$rs_LH_row = pg_fetch_array($rs_LH);
			$lh_ug_perfil_saldo		= $rs_LH_row['ug_perfil_saldo'];
			$lh_ug_perfil_limite	= $rs_LH_row['ug_perfil_limite'];
			$lh_ug_credito_pendente = $rs_LH_row['ug_credito_pendente'];
			$lh_ug_risco_classif	= $rs_LH_row['ug_risco_classif'];
		}

		// Vendas em aberto
		$sql  = "select count(*) as n
				from tb_dist_venda_games vg 
				where vg_ug_id=$codigo_lan and vg_concilia = 0 and
					vg_id in (
							select vg_id
							from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
							where 1=1 
								and vg_ug_id=$codigo_lan 
								and vg_concilia = 0 
								and vg_data_inclusao>=(			
									select cor_periodo_ini from cortes c 
									where 1=1 
										and cor_ug_id = $codigo_lan 
										and cor_status=1
									order by cor_periodo_ini desc limit 1
									)
							order by vg_data_inclusao desc		
						)";
//echo $sql . "<br>";
		$rs_Pendentes = SQLexecuteQuery($sql);
		if(!$rs_Pendentes || pg_num_rows($rs_Pendentes) == 0) $msg = "Vendas de PDV $codigo_lan não encontrada.\n";
		else {				
			$rs_Pendentes_row = pg_fetch_array($rs_Pendentes);
			$lh_n			= $rs_Pendentes_row['n'];
		}

		// PINs nas vendas em aberto
		$sql  = "select count(*) as n1, sum(vgm.vgm_valor * vgm.vgm_qtde) as valor,
						 sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse 
				from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
				where vg_ug_id=$codigo_lan and vg_concilia = 0 and
					vg_id in (
							select vg_id
							from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
							where 1=1 
								and vg_ug_id=$codigo_lan 
								and vg_concilia = 0 
								and vg_data_inclusao>=(			
									select cor_periodo_ini from cortes c 
									where 1=1 
										and cor_ug_id = $codigo_lan 
										and cor_status=1
									order by cor_periodo_ini desc limit 1
									)
							order by vg_data_inclusao desc		
						)";
//echo $sql . "<br>";
		$rs_Pendentes = SQLexecuteQuery($sql);
		if(!$rs_Pendentes || pg_num_rows($rs_Pendentes) == 0) $msg = "Vendas de PDV $codigo_lan não encontrada.\n";
		else {				
			$rs_Pendentes_row = pg_fetch_array($rs_Pendentes);
			$lh_n1			= $rs_Pendentes_row['n1'];
			$lh_valor		= $rs_Pendentes_row['valor'];
			$lh_repasse		= $rs_Pendentes_row['repasse'];
		}

		$sql  = "select cor_codigo, cor_venda_bruta, cor_venda_liquida from cortes where cor_ug_id = $codigo_lan and cor_status=1 order by cor_periodo_ini desc limit 1";
//echo $sql . "<br>";
		$rs_cor_aberto = SQLexecuteQuery($sql);
		if(!$rs_cor_aberto || pg_num_rows($rs_cor_aberto) == 0) $msg = "Corte aberto para PDV $codigo_lan não encontrado.\n";
		else {				
			$rs_cor_aberto_row = pg_fetch_array($rs_cor_aberto);
			$lh_cor_codigo			= $rs_cor_aberto_row['cor_codigo'];
			$lh_cor_venda_bruta		= $rs_cor_aberto_row['cor_venda_bruta'];
			$lh_cor_venda_liquida	= $rs_cor_aberto_row['cor_venda_liquida'];
		}


}
	
if (isset($dd_balancos)) {
	$varse1 .= "&dd_balancos=$dd_balancos";
}

if (isset($dd_boletos)) {
	$varse1 .= "&dd_boletos=$dd_boletos";
}

if (isset($dd_compras)) {
	$varse1 .= "&dd_compras=$dd_compras";
}

if (isset($dd_recarga)) {
	$varse1 .= "&dd_recarga=$dd_recarga";
}

if (isset($lan_tipo)) {
	$varse1 .= "&lan_tipo=$lan_tipo";
}


// desenhando painel -- abaixo 
$pagina_titulo = "Meus Pedidos";

?>
	<script language='javascript' src='/js/popcalendar.js'></script>
	<script src="/js/jquery.js" language="javascript"></script>
	<style>
		#box {
			z-index: 2; 
			height: 500; 
			width: 680px; 
			color: #FFFFFF; 
			font-size: 14px; 
			background-color: #FFFFFF;
			border: 1px solid #444; 
			padding: 5px; 
			position: fixed;
			top: 10%;
			left: 26%;
			text-align: left;
			display: none;
			overflow: auto;
		}
<!--
.style1 {
	color: #FF0000;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
}
-->
   </style> 
    <link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
	
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.1/css/jquery.dataTables.css">
	
    <script language="javascript">
	
        $(function(){
           var optDate = new Object();
                optDate.interval = 10000;

            setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        });
   //======================================================@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@+++++++++++++==========>>>>>>>>>>>
	// funcao que invoca o popup e muda a cor da linha clicada
		function popBal(bal_id,id_lan) {
			var bal_id = bal_id;
			var id_lan = id_lan;
			var corAntiga = document.getElementById('linha'+bal_id).bgColor.replace('#','');
			$('#box').load("com_detalhe_balanco.php?bal_id="+bal_id+"&id_lan="+id_lan+"&corAntiga="+corAntiga).show();

			document.getElementById('linha'+bal_id).bgColor = "#878787";
		}
	// fim
	//======================================================@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@+++++++++++++==========>>>>>>>>>>>
	//funcao que fecha popup, aponta o browser para a linha que foi clicada anteriormente, e renova a cor da linha que foi alterada.
		function fecha(id,corAntiga) {
			//alert(id);
			//alert(corAntiga);
	//		window.location.hash = id ;
			$('#box').hide();
			var vTimer = window.setTimeout("cor("+id+",'"+corAntiga+"')",3000);
		}
	// fim
	//======================================================@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@+++++++++++++=========<<<<<<<<<<<<
	
	//======================================================@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@+++++++++++++==========>>>>>>>>>>>	
	// funcao que altera para a cor original da linha clicada
	function cor(id,corAntiga) {
			var cor = "#"+corAntiga;
			document.getElementById('linha'+id).bgColor = cor;
	}
	// fim
	//======================================================@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@+++++++++++++=========<<<<<<<<<<<<	
	</script>
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
            <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
            <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
        </ol>
    </div>
	<!--Div popup -->
	<div id="box" ></div>	 
    <table class="table txt-preto fontsize-pp">
		<tr valign="top" align="center">
		  <td>
		    <form name="form1" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <table class="table">
					<tr bgcolor="F0F0F0">
					  <td class="texto" align="center" colspan="6"><b>Pesquisa <b><?php echo " (".$total_table." registro"?></b>
				     <?php if($total_table>1) echo "s"; ?><?php echo ")"?></b></td>
					</tr>
					<tr bgcolor="F5F5FB">
					
					  <td class="texto" align="center"><div align="right"><b>N&uacute;mero da venda</b>: </div></td>
					  <td class="texto" align="center"><div align="left">
						<input name="tf_v_codigo" id='tf_v_codigo' type="text" class="form" value="<?php echo $tf_v_codigo ?>" size="24" maxlength="10">
					</div></td>
					
					  <td colspan="3" align="center" class="texto">
						  <b>Per&iacute;odo da Pesquisa</b>
						  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
						  a 
						  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
                      </td>
					  <td class="texto" align=""><input type="submit" name="btPesquisar" value="Pesquisar" class="botao_simples"></td>
					</tr>
					<tr bgcolor="F5F5FB">
					 <!-- 
					  <td align="center" class="texto"><div align="right"><strong>Nome Fantasia: </strong></div></td>
					  <td align="center" class="texto"><div align="left"><strong>
					 <input name="nome_fantasia" type="text" class="form" id="nome_fantasia" value="<?php // echo $nome_fantasia?>" size="24" maxlength="40">
					  </strong></div></td> -->
					  <td align="center" class="texto"><div align="right"><strong>Codigo da Lan : </strong></div></td>
					  <td align="center" class="texto"><div align="left"><strong>
					  <input name="codigo_lan" type="text" class="form" id="codigo_lan" value="<?php echo $codigo_lan?>" size="24" maxlength="7">
					  </strong></div></td>
					  <td colspan="1" align="right" class="texto"><strong>
					 <label>
	<?php   
					
	if (isset($dd_balancos))$ch_balancos = "checked";
	if (isset($dd_boletos)) $ch_boletos = "checked";
	if (isset($dd_compras)) $ch_compras = "checked";
	if (isset($dd_recarga)) $dd_recarga = "checked";
					
	?>
					 <?php  //var_dump($ch_balancos);?>
					 <input type="checkbox" name="dd_balancos" id="dd_balancos" checked <?php //echo $ch_balancos?>>
					 </label>
					 Balan&ccedil;os</strong></td>
					  <td colspan="1" align="center" class="texto"><strong>
					 <input type="checkbox" name="dd_boletos" id="dd_boletos" checked  <?php //echo $dd_boletos?>>
					 Pagamentos</strong></td>
					  <td colspan="2" align="left" class="texto"><strong>
					 <input type="checkbox" name="dd_compras" id="dd_compras" checked <?php //echo $dd_compras?>>
					 Compras</strong></td>
					 
				  </tr>
				   <!-- 
					<tr bgcolor="F5F5FB">
					  <td align="center" class="texto"><div align="right"><strong>Tipo de Lan:</strong></div></td>
					  <td align="left" class="texto">
					   <select name="lan_tipo" id="lan_tipo">
							 <option value=''<?php //if ($lan_tipo != 'PJ' && $lan_tipo != 'PF' ) echo " selected" ?>>Todos</option>
						 <option value='PJ'<?php //if ($lan_tipo == 'PJ' ) echo " selected" ?>>PJ</option>
						 <option value='PF'<?php //if ($lan_tipo == 'PF' ) echo " selected" ?>>PF</option>
					  </select>    	         </td>
					  <td colspan="3" align="center" class="texto">&nbsp;</td>
					  <td class="texto" align="center">&nbsp;</td>
				  </tr>
				  -->
					<tr bgcolor="F5F5FB">
					  <td height="21" align="center" class="texto">&nbsp;</td>
					  <td class="texto" align="center">&nbsp;</td>
					  <td colspan="3" align="center" class="texto">&nbsp;</td>
					  <td class="texto" align="center"><div align="right"><a href="/index.php"><img src="/images/voltar_menu.gif" width="107" height="15" border="0"></a></div></td>
				  </tr>
				</table>
			  </form>

<?php
if(isset($btPesquisar) && $btPesquisar) {


    //echo "teste btn";
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// AQUI COMEÇA A QUERY DE RECUPERAR DADOS: //////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////
	$sql = "select num_doc as num_doc,id_cliente,nome,fantasia,cpf,cnpj,tipo_pagto , data, valor as valor, sum(valor - repasse) as comissao, repasse, tipo_movimentacao, tipo_lan,resultado,doc_id_link 
	from ( \n";
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Recupera as vendas ////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Bloco de carregar dados da venda			///
	$sql .= " (
		select 
		(vg.vg_id::text) as num_doc,
		vg.vg_data_inclusao as data,
		vg.vg_pagto_tipo as tipo_pagto,
		sum(vgm.vgm_valor * vgm.vgm_qtde) as valor,
		sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse ,
		'Venda'::text as tipo_movimentacao,
		ug_risco_classif as tipo_lan,
		vg.vg_ug_id as id_cliente,
		ug.ug_nome_fantasia as fantasia,
		ug.ug_nome as nome,
		ug.ug_cpf as cpf,
		ug.ug_cnpj as cnpj,
		NULL::smallint as resultado,
		NULL::bigint as doc_id_link
	from tb_dist_venda_games vg 
			inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
			inner join dist_usuarios_games ug on vg.vg_ug_id = ug.ug_id 
	where 1=1 \n";
	if(isset($tf_v_codigo) && $tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and vg.vg_id=" . $tf_v_codigo;
	if(isset($tf_v_data_inclusao_ini) && $tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
		if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
		$sql .= " and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' and vg.vg_ultimo_status = 5 \n";
	if(isset($lan_tipo) && $lan_tipo) {
		$sql .= " and ug.ug_tipo_cadastro = '$lan_tipo' \n";
		$group .= ",ug.ug_tipo_cadastro";
	}			
	if(isset($tf_v_codigo) && $tf_v_codigo) {
		$sql .= " and vg.vg_id = '$tf_v_codigo' \n";
		$group .= ",vg.vg_id";
	}			
	if(isset($nome_fantasia) && $nome_fantasia) {
		$sql .= " and (ug.ug_nome_fantasia like '%$nome_fantasia%' or ug.ug_nome like '%$nome_fantasia%') \n";
		$group .= ",ug.ug_nome_fantasia";
	}		  
	if(isset($codigo_lan) && $codigo_lan) {
		$sql .= " and ug.ug_id = '$codigo_lan' \n";
		$group .= ",ug.ug_id";
	}			
	//////////////////////////// GROUP ///////////////////////////////////////////////////
	$sql .= " group by num_doc, data, tipo_pagto, vg.vg_ultimo_status,vg.vg_ug_id,ug.ug_nome_fantasia,ug.ug_nome,ug.ug_cpf,ug.ug_cnpj,resultado,doc_id_link, ug_risco_classif ".$group."  \n";
	//////////////////////////////////////////////////////////////////////////////////////
	$sql .= ") \n";
	
	$sql .= " union all \n";
	$sql .= " (
	    select 
	        (ep.est_id::text) as num_doc,
	        ep.data_operacao as data,
	        NULL::smallint as tipo_pagto,
	        ep.est_valor as valor,
	        0 as repasse,
	        'Estorno'::text as tipo_movimentacao,
	        ug.ug_risco_classif as tipo_lan,
	        ep.ug_id as id_cliente,
	        ug.ug_nome_fantasia as fantasia,
	        ug.ug_nome as nome,
	        ug.ug_cpf as cpf,
	        ug.ug_cnpj as cnpj,
	        NULL::smallint as resultado,
	        NULL::bigint as doc_id_link
	    from estorno_pdv ep
	    inner join dist_usuarios_games ug on ep.ug_id = ug.ug_id 
	    where 1=1 \n";
	if (isset($tf_v_data_inclusao_ini) && $tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) {
	    if (verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0) {
	        $sql .= " and ep.data_operacao between '" . formata_data($tf_v_data_inclusao_ini, 1) . " 00:00:00' and '" . formata_data($tf_v_data_inclusao_fim, 1) . " 23:59:59' \n";
	    }
	}
	if (isset($lan_tipo) && $lan_tipo) {
	    $sql .= " and ug.ug_tipo_cadastro = '$lan_tipo' \n";
	}
	if (isset($tf_v_codigo) && $tf_v_codigo) {
	    $sql .= " and 1 = 2 \n"; // Ajuste conforme a lógica do filtro
	}
	if (isset($nome_fantasia) && $nome_fantasia) {
	    $sql .= " and (ug.ug_nome_fantasia like '%$nome_fantasia%' or ug.ug_nome like '%$nome_fantasia%') \n";
	}
	if (isset($codigo_lan) && $codigo_lan) {
	    $sql .= " and ug.ug_id = '$codigo_lan' \n";
	}
	$sql .= ") \n";

	

	//Validacoes
	$msg = "";	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Recupera as recargas de Celular ///////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Bloco de carregar dados de recarga de celular///
	if (isset($dd_recarga)) {
		//// Recuperando dados de recarga da Rede Ponto Certo
		$sql .= " union all \n";
		$sql .= " (
			select 
			(rp.rp_vg_id::text) as num_doc,
			rp.rp_data_recarga as data,
			NULL::smallint as tipo_pagto,
			rp_valor as valor,
			rp_valor as repasse ,
			'Recarga Celular'::text as tipo_movimentacao,
			ug_risco_classif as tipo_lan,
			rp.rp_ug_id as id_cliente,
			ug.ug_nome_fantasia as fantasia,
			ug.ug_nome as nome,
			ug.ug_cpf as cpf,
			ug.ug_cnpj as cnpj,
			NULL::smallint as resultado,
			NULL::bigint as doc_id_link
		from tb_recarga_pedidos rp 
				inner join dist_usuarios_games ug on rp.rp_ug_id = ug.ug_id 
		where 1=1 \n";
		if(isset($tf_v_data_inclusao_ini) && $tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
			if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
			$sql .= " and rp.rp_data_recarga between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' \n";
		if(isset($lan_tipo) && $lan_tipo) {
			$sql .= " and ug.ug_tipo_cadastro = '$lan_tipo' \n";
		}			
		if(isset($tf_v_codigo) && $tf_v_codigo) {
			$sql .= " and 1 = 2 \n";
		}			
		if(isset($nome_fantasia) && $nome_fantasia) {
			$sql .= " and (ug.ug_nome_fantasia like '%$nome_fantasia%' or ug.ug_nome like '%$nome_fantasia%') \n";
		}		  
		if(isset($codigo_lan) && $codigo_lan) {
			$sql .= " and ug.ug_id = '$codigo_lan' \n";
		}			
		$sql .= ") \n";

		//// Recuperando dados de recarga da Rede SIM
		$sql .= " union all \n";
		$sql .= " (
			select 
			(rp.rprs_vg_id::text) as num_doc,
			rp.rprs_data_recarga as data,
			NULL::smallint as tipo_pagto,
			rprs_valor as valor,
			(rprs_valor - (rprs_valor * rprs_comissao_para_repasse/100)) as repasse ,
			'Recarga Celular'::text as tipo_movimentacao,
			ug_risco_classif as tipo_lan,
			rp.rprs_ug_id as id_cliente,
			ug.ug_nome_fantasia as fantasia,
			ug.ug_nome as nome,
			ug.ug_cpf as cpf,
			ug.ug_cnpj as cnpj,
			NULL::smallint as resultado,
			NULL::bigint as doc_id_link
		from tb_recarga_pedidos_rede_sim rp 
				inner join dist_usuarios_games ug on rp.rprs_ug_id = ug.ug_id 
		where  rp.rprs_data_recarga is not null \n";
		if(isset($tf_v_data_inclusao_ini) && $tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
			if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
			$sql .= " and rp.rprs_data_recarga between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' \n";
		if(isset($lan_tipo) && $lan_tipo) {
			$sql .= " and ug.ug_tipo_cadastro = '$lan_tipo' \n";
		}			
		if(isset($tf_v_codigo) && $tf_v_codigo) {
			$sql .= " and 1 = 2 \n";
		}			
		if(isset($nome_fantasia) && $nome_fantasia) {
			$sql .= " and (ug.ug_nome_fantasia like '%$nome_fantasia%' or ug.ug_nome like '%$nome_fantasia%') \n";
		}		  
		if(isset($codigo_lan) && $codigo_lan) {
			$sql .= " and ug.ug_id = '$codigo_lan' \n";
		}			
		$sql .= ") \n";

		//// Recuperando dados de SEGUROS da Rede SIM
		$sql .= " union all \n";
		$sql .= " (
			select 
			(rp.sprs_vg_id::text) as num_doc,
			rp.sprs_data_seguro as data,
			NULL::smallint as tipo_pagto,
			sprs_valor as valor,
			sprs_valor as repasse ,
			'Seguro'::text as tipo_movimentacao,
			ug_risco_classif as tipo_lan,
			rp.sprs_ug_id as id_cliente,
			ug.ug_nome_fantasia as fantasia,
			ug.ug_nome as nome,
			ug.ug_cpf as cpf,
			ug.ug_cnpj as cnpj,
			NULL::smallint as resultado,
			NULL::bigint as doc_id_link
		from tb_seguro_pedidos_rede_sim rp 
				inner join dist_usuarios_games ug on rp.sprs_ug_id = ug.ug_id 
		where 1=1 \n";
		if(isset($tf_v_data_inclusao_ini) && $tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
			if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
			$sql .= " and rp.sprs_data_seguro between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' \n";
		if(isset($lan_tipo) && $lan_tipo) {
			$sql .= " and ug.ug_tipo_cadastro = '$lan_tipo' \n";
		}			
		if(isset($tf_v_codigo) && $tf_v_codigo) {
			$sql .= " and 1 = 2 \n";
		}			
		if(isset($nome_fantasia) && $nome_fantasia) {
			$sql .= " and (ug.ug_nome_fantasia like '%$nome_fantasia%' or ug.ug_nome like '%$nome_fantasia%') \n";
		}		  
		if(isset($codigo_lan) && $codigo_lan) {
			$sql .= " and ug.ug_id = '$codigo_lan' \n";
		}			
		$sql .= ") \n";

		//// Recuperando dados de vendas B2C
		$sql .= " union all \n";
		$sql .= " (
			select 
			(vb.vb2c_vg_id::text) as num_doc,
			vb.\"vb2c_dataVenda\" as data,
			NULL::smallint as tipo_pagto,
			\"vb2c_precoServico\" as valor,
			(\"vb2c_precoServico\" - (\"vb2c_precoServico\" * vb2c_comissao_para_repasse/100)) as repasse ,
			'B2C'::text as tipo_movimentacao,
			ug_risco_classif as tipo_lan,
			vb.vb2c_ug_id_lan as id_cliente,
			ug.ug_nome_fantasia as fantasia,
			ug.ug_nome as nome,
			ug.ug_cpf as cpf,
			ug.ug_cnpj as cnpj,
			NULL::smallint as resultado,
			NULL::bigint as doc_id_link
		from tb_vendas_b2c vb 
			inner join dist_usuarios_games ug on vb.vb2c_ug_id_lan = ug.ug_id 
		where vb2c_status='1' \n";
		if(isset($tf_v_data_inclusao_ini) && $tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
			if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
			$sql .= " and vb.\"vb2c_dataVenda\" between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' \n";
		if(isset($lan_tipo) && $lan_tipo) {
			$sql .= " and ug.ug_tipo_cadastro = '$lan_tipo' \n";
		}			
		if(isset($tf_v_codigo) && $tf_v_codigo) {
			$sql .= " and 1 = 2 \n";
		}			
		if(isset($nome_fantasia) && $nome_fantasia) {
			$sql .= " and (ug.ug_nome_fantasia like '%$nome_fantasia%' or ug.ug_nome like '%$nome_fantasia%') \n";
		}		  
		if(isset($codigo_lan) && $codigo_lan) {
			$sql .= " and ug.ug_id = '$codigo_lan' \n";
		}			
		$sql .= ") \n";

	}//end if (isset($dd_recarga))
	// FIM do Bloco de carregar dados de recarga de celular///

					
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		////// RECUPERA BALANÇOS //////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if (isset($dd_balancos) ) {	// && (isset($nome_fantasia) || isset($codigo_lan))

			$sql .= " union all \n";
			$sql .= " (
				select 
					(NULL::text) as num_doc,
					db.db_data_balanco as data ,
					NULL::smallint as tipo_pagto,
					db.db_valor_balanco as valor,
					NULL::real as repasse,
					'Balanco'::text as tipo_movimentacao,
					(db.db_tipo_lan::integer) as tipo_lan,
					db.db_ug_id as id_cliente,
					ug.ug_nome_fantasia as fantasia,
					ug.ug_nome as nome,
					ug.ug_cpf as cpf,
					ug.ug_cnpj as cnpj,
					db.db_resultado::smallint as resultado,
					db.db_id as doc_id_link
				from dist_balancos db inner join dist_usuarios_games ug on db.db_ug_id = ug.ug_id 
				where 1=1\n";

				if(isset($codigo_lan) && $codigo_lan) {
					$sql .= " and ug.ug_id = '$codigo_lan' \n";
				}		

				if(isset($tf_v_codigo) && $tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and db.db_id=" . $tf_v_codigo ." \n";

				if ($nome_fantasia != '') {
				$sql .=" and (ug.ug_nome_fantasia like '%$nome_fantasia%' or ug.ug_nome like '%$nome_fantasia%') \n";
				}

				if(isset($tf_v_data_inclusao_ini) && $tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) {
					if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0) {
						$sql .= " and db_data_balanco between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' \n";
					}
				}
				$sql .= " group by tipo_lan,data,valor,db.db_ug_id,ug.ug_nome_fantasia,ug.ug_nome,ug.ug_cpf,ug.ug_cnpj,resultado,doc_id_link \n)";	
		}// fim if balancos

		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		///// RECUPERA VALOR DE CREDITOS COMPRADO - PRE PAGO ////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if (isset($dd_boletos)) { 

			$sql .= " union all \n";
			$sql .= "
			(select 
				(bol_documento::text) as num_doc,
				bol_importacao as data ,
				vg_pagto_tipo as tipo_pagto ,
				sum (bol_valor - bbg_valor_taxa) as valor ,
				NULL::real as repasse ,
				'Boleto'::text as tipo_movimentacao,
				2 as tipo_lan,
				ug.ug_id as id_cliente,
				ug.ug_nome_fantasia as fantasia,
				ug.ug_nome as nome,
				ug.ug_cpf as cpf,
				ug.ug_cnpj as cnpj,
				NULL::smallint as resultado,
				vg_id::bigint as doc_id_link
			from boletos_pendentes, bancos_financeiros, tb_dist_venda_games, dist_boleto_bancario_games, dist_usuarios_games ug 
			where 1=1 
				and (bol_banco = bco_codigo) 
				and (bol_venda_games_id=vg_id) 
				and (bco_rpp = 1) 
				and (vg_ug_id = ug_id)\n";
		
			if(isset($codigo_lan) && $codigo_lan) {
				$sql .= " and ug.ug_id = ".$codigo_lan." \n";
				$sql .= " and bbg_ug_id = ".$codigo_lan." \n";
			}

			if(isset($tf_v_data_inclusao_ini) && $tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
				if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
					$sql .= " and bol_importacao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' \n";

			if(isset($tf_v_codigo) && $tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and bol_documento like '%" . $tf_v_codigo."%' \n";

			if(isset($lan_tipo) && $lan_tipo) {
				$sql .= " and ug_tipo_cadastro = '$lan_tipo' \n";
			}
			if(isset($nome_fantasia) && $nome_fantasia) {
				$sql .= " and (ug_nome_fantasia like '%$nome_fantasia%' or ug_nome like '%$nome_fantasia%') \n";
			}
			
			$sql .= "and substr(bol_documento,1,1) = '4' \n";
			$sql .= "and bbg_vg_id = vg_id \n";
			$sql .= "group by bol_documento,vg_data_inclusao,vg_pagto_tipo,bol_importacao,ug_id,ug_nome_fantasia,ug_nome,ug_cpf,ug_cnpj,resultado,doc_id_link \n";
			$sql .= ") \n";

		}// fim if boleto
		

		//////////////////////////////////////////////////////////////////////////////////////////////
		//////// Codigo para carregar os cortes POS PAGO /////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////
		if (isset($dd_boletos) ) {
		//**** codigo de corte
			$sql .= " union all \n";	

			$sql .= "(\nselect 
						(bbc_documento::text) as  num_doc,
						cor_data_concilia as data ,
						cor_tipo_pagto as  tipo_pagto , 
						cor_venda_liquida as  valor,
						NULL::real as repasse,
						'Corte'::text as tipo_movimentacao,
						1 as tipo_lan,
						ug.ug_id as id_cliente,
						ug.ug_nome_fantasia as fantasia,
						ug.ug_nome as nome,
						ug.ug_cpf as cpf,
						ug.ug_cnpj as cnpj,
						NULL::smallint as resultado,
						bbc_boleto_codigo::bigint as doc_id_link
					from cortes c 
						inner join boleto_bancario_cortes as bbc on cor_bbc_boleto_codigo = bbc_boleto_codigo 
						inner join dist_usuarios_games ug on c.cor_ug_id = ug.ug_id
					where 1=1\n";

			if(isset($codigo_lan) && $codigo_lan) {
				$sql .= " and c.cor_ug_id = '$codigo_lan' \n";
			}

			if(isset($tf_v_codigo) && $tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and bbc_documento like '%" . $tf_v_codigo."%' \n";

			if(isset($lan_tipo) && $lan_tipo) {
				$sql .= " and ug.ug_tipo_cadastro = '$lan_tipo' \n";
			}	
				
			if(isset($nome_fantasia) && $nome_fantasia) {
				$sql .= " and (ug.ug_nome_fantasia like '%$nome_fantasia%' or ug.ug_nome like '%$nome_fantasia%') \n";
			}	
							
			$sql .= " and c.cor_venda_liquida > 0 \n";
			
			if(isset($tf_v_data_inclusao_ini) && $tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) {
				if( verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0) {
					$sql .= " and cor_data_concilia between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and  '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' ";
				}
			}

			$sql .= " )\n";
			//
			$fcolor = "bgcolor='#AAAAAA'";

		} // fim cortes

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
		// PAGAMENTOS ONLINE 
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if (isset($dd_boletos) ) {
			//**** codigo de corte
			$sql .= "union all \n";	
			$sql .= "(\n
			select 
				numcompra::text as num_doc,
				datainicio as data,
				(case when iforma='A' then 10 when iforma='R' then 24  else iforma::int end ) as tipo_pagto,

				(sum (total/100 - taxas)::real) as valor,
				NULL as repasse ,
				'PagtoOnline' as tipo_movimentacao, 
				(case when tipo_cliente='LR' then 2 when tipo_cliente='LO' then 1 else 0 end) as tipo_lan,
				idcliente as id_cliente,
				ug.ug_nome_fantasia as fantasia,
				ug.ug_nome as nome,
				ug.ug_cpf as cpf,
				ug.ug_cnpj as cnpj,
				NULL::smallint as resultado,
				idvenda::bigint as doc_id_link
			from tb_pag_compras pc
				inner join dist_usuarios_games ug on pc.idcliente = ug.ug_id
			where 1=1  and pc.status=3 and (substr(pc.tipo_cliente,1,1)='L') \n";
			
			if(isset($codigo_lan) && $codigo_lan) {
				$sql .= " and pc.idcliente = '$codigo_lan' \n";
			}

			if(isset($tf_v_codigo) && $tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and numcompra like '%" . $tf_v_codigo."%' \n";
//echo "lan_tipo:$lan_tipo<br>";
			if(isset($lan_tipo) && $lan_tipo) {
				$sql .= " and ug.ug_tipo_cadastro='$lan_tipo' \n";
			}
				
			if(isset($nome_fantasia) && $nome_fantasia) {
				$sql .= " and (ug.ug_nome_fantasia like '%$nome_fantasia%' or ug.ug_nome like '%$nome_fantasia%') \n";
			}	
						
			if(isset($tf_v_data_inclusao_ini) && $tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) {
				if( verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0) {
					$sql .= " and pc.datacompra between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and  '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' \n";
				}
			}
			
			
			$sql .= "group by pc.numcompra::text, pc.datainicio, pc.iforma, tipo_cliente,idcliente,ug_nome_fantasia,ug_nome,ug_cpf,ug_cnpj,resultado,doc_id_link \n";
			$sql .= "order by pc.datainicio ) \n";

		} // fim pagamento online



		$sql .= ") as venda \n";
//if(b_IsUsuarioReinaldo()) { 
//echo "<br>(R) <span style='color:red'>Apenas Boletos</span><br>";
//$sql .= "where tipo_movimentacao = 'Boleto' \n";
//}

		$sql .= "group by venda.num_doc,venda.tipo_pagto,venda.data,venda.valor,tipo_movimentacao,repasse,tipo_lan,id_cliente,nome,fantasia,cpf,cnpj,resultado,doc_id_link \n";
	

//if(b_IsUsuarioWagner()) { 
//echo "(R) <br><br><pre align='left'>".$sql."</pre><br><br>";
//}

	$total_entrada_geral = 0;
	$total_saida_geral = 0;
	$total_comissao_geral = 0;
	$total_comissao_geral = 0;

	$res_tmp = SQLexecuteQuery($sql);
	
	$totalres = pg_num_rows($res_tmp);
	
	if(!isset($_GET["inicial"]) || empty($_GET["inicial"])){
		$_SESSION["inicial"] = 0;
		$resto = "Inicial";
	}else{
		if($_GET["inicial"] == "2000"){
			if($_GET["btPesquisar"][0] == "Proximo"){
				($_SESSION["inicial"] < $totalres)? $_SESSION["inicial"] += $_GET["inicial"]: null;
				if($_SESSION["inicial"] > $totalres){
				    $_SESSION["inicial"] -= 2000;
					$resto = "Final";
				}else{
				    $resto = $_SESSION["inicial"]." registros percorridos";
				}
			}else{
			   
			    $resto = ($_SESSION["inicial"]== "0")? null : $_SESSION["inicial"] -= $_GET["inicial"];
				if($resto == null){
				    $resto = "Inicial";
				}else{
				    $resto = $_SESSION["inicial"]." registros percorridos";
				}
			}
		}else{
		    $resto = "Inicial";
			$_SESSION["inicial"] = 0;
		}   
	}

    $inicial = $_SESSION["inicial"];
    //echo $inicial;
	
	$dados = [];
	if ($res_tmp) {
		$total_table = pg_num_rows($res_tmp);

		while( $info = pg_fetch_array($res_tmp) ){
		
		    //$dados[] = $info;

			if (
					($info['tipo_movimentacao'] == 'PagtoOnline') || 
					($info['tipo_movimentacao'] == 'Boleto') ||
					($info['tipo_movimentacao'] == 'Corte') 
				) {
				$total_entrada_geral += $info['valor'];
			}

			if (
					($info['tipo_movimentacao'] == "Venda") ||
					($info['tipo_movimentacao'] == 'Recarga Celular')||
					($info['tipo_movimentacao'] == 'Seguro')||
					($info['tipo_movimentacao'] == 'B2C') 
				) {
				$total_saida_geral += $info['valor'];
				$total_comissao_geral += ($info['valor'] - $info['repasse']);

			}
		}
	}

if ($total_table > 0) {

	$max_reg = (($inicial + $max)>$total_table)?$total_table:$max;


//echo "total_table: ".$total_table."<br>";

	$sql .= "order by data desc ";
	$sql .= "limit $max offset $inicial ";

	
	$res = SQLexecuteQuery($sql);
}//end if($btPesquisar)	
	?>
		
			  <div>
                <table class="table">
				
				 <tr <?php echo $fcolor?> class="texto" >
				   <td height="21" colspan="4" align="left" bgcolor="#EEEEEE" class='texto'>Listando de 
				   <?php echo ($inicial +1)?> a <?php echo ($max_reg)?> de <?php echo $total_table?></td>
				   <td align="center" bgcolor="#EEEEEE" id="res_saida4">&nbsp;</td>
				   <td align="center" bgcolor="#EEEEEE" id="res_comissao">&nbsp;</td>
				   <td align="center" bgcolor="#EEEEEE" id="res_comissao">&nbsp;</td>
				   <td align="center" bgcolor="#EEEEEE">&nbsp;</td>
				   <td align="center" bgcolor="#EEEEEE">&nbsp;</td>
				   <td align="center" bgcolor="#EEEEEE">&nbsp;</td>
				   <td align="center" bgcolor="#EEEEEE">&nbsp;</td>
				   <td align="center" bgcolor="#EEEEEE" id="saldo4">&nbsp;</td>
				 </tr>
				 <?php if(isset($codigo_lan) && $codigo_lan>0) { ?>
				 <tr class="texto">
				   <td height="21" align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="right" bgcolor="#DDDDDD"><b>Saldo</b></td>
				   <td align="center" bgcolor="#DDDDDD"><?php if(!empty($lh_ug_perfil_saldo)) echo number_format($lh_ug_perfil_saldo,2, ',','.'); else echo "0,00";?>&nbsp;</td>
				   <td align="right" bgcolor="#DDDDDD"><b>Limite</b></td>
				   <td align="center" bgcolor="#DDDDDD"><?php if(!empty($lh_ug_perfil_limite)) echo number_format($lh_ug_perfil_limite,2, ',','.'); else echo "0,00";?>&nbsp;</td>
				   <td align="right" bgcolor="#DDDDDD"><b>Crédito Pendente</b></td>
				   <td align="center" bgcolor="#DDDDDD"><?php if(!empty($lh_ug_credito_pendente)) echo number_format($lh_ug_credito_pendente,2, ',','.'); else echo "0,00";?>&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				 </tr>
				 <?php		if(isset($lh_ug_risco_classif) && $lh_ug_risco_classif == 1) { ?>
				 <tr class="texto">
				   <td height="21" align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="right" bgcolor="#DDDDDD"><b>Vendas aberto</b> </td>
				   <td align="center" bgcolor="#DDDDDD"><a href='/pdv/vendas/com_pesquisa_vendas.php?ordem=1&ncamp=vg_data_inclusao&inicial=0&BtnSearch=1&tf_u_codigo=<?php echo $codigo_lan ?>' target='_blank'><?php echo $lh_n?>&nbsp;(<?php echo $lh_n1 ?> PIN<?php echo ($lh_n1==1)?"":"s"?>)</a>&nbsp;</td>
				   <td align="right" bgcolor="#DDDDDD"><b>Valor aberto</b> </td>
				   <td align="center" bgcolor="#DDDDDD"><?php if(!empty($lh_valor)) echo number_format($lh_valor,2, ',','.'); else echo "0,00";?>&nbsp;</td>
				   <td align="right" bgcolor="#DDDDDD"><b>Repasse aberto</b> </td>
				   <td align="center" bgcolor="#DDDDDD"><?php if(!empty($lh_repasse)) echo number_format($lh_repasse,2, ',','.'); else echo "0,00";?>&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				 </tr>
				 <tr class="texto">
				   <td height="21" align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="right" bgcolor="#DDDDDD"><b>Corte aberto</b> </td>
				   <td align="center" bgcolor="#DDDDDD"><a href='/financeiro/corte/corte_consulta.php?usuario_id=<?php echo $codigo_lan?>' target='_blank'><?php echo $lh_cor_codigo?></a>&nbsp;</td>
				   <td align="right" bgcolor="#DDDDDD"><b>Corte Bruto</b> </td>
				   <td align="center" bgcolor="#DDDDDD"><?php if(!empty($lh_cor_venda_bruta)) echo number_format($lh_cor_venda_bruta,2, ',','.'); else echo "0,00";?>&nbsp;</td>
				   <td align="right" bgcolor="#DDDDDD"><b>Corte Liquido</b> </td>
				   <td align="center" bgcolor="#DDDDDD"><?php if(!empty($lh_cor_venda_liquida)) echo number_format($lh_cor_venda_liquida,2, ',','.'); else echo "0,00";?>&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				 </tr>
				 <?php		} ?>
				 <?php } ?>

				 <tr <?php echo $fcolor?> class="texto" >
				   <td height="21" align="center" ></td>
				   <td align="center">&nbsp;</td>
				   <td colspan="2" align="center" id="res_total3"><strong>Boletos</strong></td>
				   <td width="10%" align="center" id="res_saida3"><strong>Vendas</strong></td>
				   <td align="center" id="res_comissao3">&nbsp;</td>
				   <td width="12%" align="center" id="res_comissao3">&nbsp;</td>
				   <td align="center">&nbsp;</td>
				   <td align="center"><strong>Comissao</strong></td>
				   <td align="center">&nbsp;</td>
				   <td align="center">&nbsp;</td>
				   <td align="center" id="saldo3">&nbsp;</td>
				 </tr>
				 <tr class="texto">
				   <td height="21" align="center" bgcolor="#DDDDDD" ></td>
				   <td  align="center" bgcolor="#DDDDDD"><strong>Total Geral</strong></td>
				   <td colspan="2"  align="center" bgcolor="#DDDDDD"><?php echo number_format($total_entrada_geral,2, ',','.')?>&nbsp;</td>
				   <td  align="center" bgcolor="#DDDDDD"><?php echo number_format($total_saida_geral,2, ',','.')?>&nbsp;</td>
				   <td  align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td  align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD"><?php echo number_format($total_comissao_geral,2, ',','.')?>&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td  align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				 </tr>
				 <tr class="texto">
				   <td width="4%" height="21" align="center" ></td>
				   <td  align="center"><strong>Total</strong></td>
				   <td colspan="2"  align="center" id="res_entrada_pag"><?php // echo number_format($total_entrada,2, ',','.')?>&nbsp;</td>
				   <td  align="center" id="res_saida_pag">&nbsp;</td>
				   <td  align="center" >&nbsp;</td>
				   <td  align="center" >&nbsp;</td>
				   <td align="center">&nbsp;</td>
				   <td align="center" id="res_comiss_pag"><?php //echo number_format($comiss_tot,2, ',','.')?>&nbsp;</td>
				   <td align="center">&nbsp;</td>
				   <td align="center">&nbsp;</td>
				   <td  align="center" >&nbsp;</td>
				 </tr>
				
				<!-- 
				 <tr class='texto'>
				   <td height="21" align="center" bgcolor="#CCCCCC" ><strong>Tipo</strong></td>
				   <td  align="center" bgcolor="#CCCCCC"><strong>N&ordm; Documento</strong></td>
				   <td  align="center" bgcolor="#CCCCCC" id="res_total2"><strong>Codigo Cliente</strong></td>
				   <td  align="center" bgcolor="#CCCCCC" id="res_total2"><strong>Nome Cliente</strong></td>
				   <td  align="center" bgcolor="#CCCCCC" id="saldo2"><strong>Tipo</strong></td>
				   <td  align="center" bgcolor="#CCCCCC" id="saldo2"><strong>Data</strong></td>
				   <td align="center" bgcolor="#CCCCCC"><strong>Valor</strong></td>
				   <td align="center" bgcolor="#CCCCCC"><strong>Comiss&atilde;o</strong></td>
				   <td align="center" bgcolor="#CCCCCC"><strong>Saldo</strong></td>
				   <td align="center" bgcolor="#CCCCCC"><strong>Limite</strong></td>
				   <td  align="center" bgcolor="#CCCCCC" ><strong>Risco</strong></td>
				 </tr>
			
			      -->
				  
				 <?php	
				/// fechando a query 
	$i_row = 0;
	$total_entrada = 0;
	$total_saida = 0;
	$total_comissao = 0;
	$lista_ids = "";

	while( $info = pg_fetch_array($res) ){
	
	
	$dados[] = $info;
		//
		/// descobre se é PF ou PJ e o configura
		/* if ( $info['cnpj'] != '') {
										
			$img = '/images/balanco/pj.png';
			$nome_lan_view = $info['fantasia'];

		} else {

			$img = '/images/balanco/pf.png';
			$nome_lan_view = $info['nome'];
		}

		if ($info['tipo_lan'] == 1) {
			$url_image_tipo_lan = '/images/balanco/POS.png';
		} elseif(isset($info['tipo_lan']) && $info['tipo_lan'] == 2) {
			$url_image_tipo_lan = '/images/balanco/PRE.png';
		} else {
			$url_image_tipo_lan = '/images/balanco/baixa.png';
		}
		$url_image_tipo_lan_pre = '/images/balanco/PRE.png';
		$url_image_tipo_lan_pos = '/images/balanco/POS.png';

		$i_row++;

		///////////////////////////////////////////////////////////////////
		/////////////// ---- SETUP CORES DAS CELULAS -------///////////////
		//////////												///////////
		/////														///////
		///															    ///
		$bgcolor = ((++$i) % 2)?" bgcolor='#E0E0E0'":" bgcolor='#FAFAFE'";
		// dolor &eacute; a parte cinza									     //
		$dcolor = " bgcolor='#FBEEDB' ";
		// lcolor &eacute; a parte laranaja								     //
		$lcolor = ((++$i) % 2)?" bgcolor=\"#E0E0E0\"":" bgcolor='#FAFAFE'";

		// setando a data do pedido recem pego para comparar com o boleto
		$vcolor = "bgcolor='#FFFFCC'";
					
		//																 //
		//////														///////
		/////////////											///////////
		//////////////// ----------- FIM SETUP ------------////////////////
		///////////////////////////////////////////////////////////////////
	
		//////////////////////////// LOAD DIVIDA BOLETO //////////////////////////////////////////////////
		//// s&oacute; desenha a linha se a data do boleto for maior que a data dos pedidos 
		// se boleto tiver a data maior que da data da venda de pins
		if ($info['tipo_movimentacao'] == 'Boleto') {
						
			$total_entrada += $info['valor'];
			$boleto_valor = $info['valor'];
			//////// VIEW DATAS E PRE&Ccedil;OS
			$numero_view = $info['num_doc'];
			$data_view  = formata_data_ts($info['data'], 0, true, false);
			$valor_view = number_format($info['valor'],2, ',','.');
			$id_view = $info['id_cliente'];
			$doc_id_link = $info['doc_id_link'];  */

			/////////////////////////////////////
		?>  
		     <!--
				 <tr <?php// echo $bcolor?>>
				   <td class="texto" align="center" width="4%"><img src='/images/balanco/in.png' title="compra de cr&eacute;ditos PDV pr&eacute; (<?php// echo $i_row ?>)"/></td>
				   <td class="texto" align="center" width="11%"><a href="/pdv/vendas/com_venda_detalhe.php?venda_id=<?php// echo $doc_id_link?>" target="_blank">
				   <?php //echo $numero_view?>
				   </a></td>
				   <td width="6%" align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php// echo $id_view?>" target="_blank">
				   <?php //echo $id_view?>
				   </a></td>
				   <td width="15%" align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php// echo $id_view?>" target="_blank">
				   <?php //echo $nome_lan_view?>
				   </a></td>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php //echo $img?>' title=""></td>
				   <td align="center" class="texto"><nobr><?php //echo $data_view?></nobr></td>
				   <td class="texto" align="center" width="9%"><b>
					 <?php //echo $valor_view?>
				   </b></td>
				   <td class="texto" align="center" width="10%">&nbsp;</td>
				   <td class="texto" align="center" width="6%">&nbsp;</td>
				   <td class="texto" align="center" width="8%">&nbsp;</td>
				   <td class="texto" align="center" width="5%"><img src='<?php //echo $url_image_tipo_lan_pre ?>'></td>
				 </tr>
			-->
	   <?php
		//}  // if tipo_lan == boleto  

		//////////////////////////// LOAD DESENHA PAGAMENTO ONLINE ////////////////////////////////////////////////////
		//// linha de desenhar o pagamento online
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/* if ($info['tipo_movimentacao'] == 'PagtoOnline') {
						
			$total_entrada += $info['valor'];
			$boleto_valor = $info['valor'];
			//////// VIEW DATAS E PRE&Ccedil;OS
			$numero_view = $info['num_doc'];
			$data_view  = formata_data_ts($info['data'], 0, true, false);
			$valor_view = number_format($info['valor'],2, ',','.');
			$id_view = $info['id_cliente'];
			$tipo_pagto = $info['tipo_pagto'];
			$tipo_pagto_index_img = (($tipo_pagto==10)?"A":$tipo_pagto);
			$doc_id_link = $info['doc_id_link']; */

			/////////////////////////////////////
		?>
			<!--	 <tr <?php //echo $bcolor?>>
				   <td class="texto" align="center" width="4%" title="<?php //echo $i_row ?>"><?php //echo getLogoBancoSmall($tipo_pagto_index_img)?></td>
				   <td class="texto" align="center" width="11%"><a href="/pdv/vendas/com_venda_detalhe.php?venda_id=<?php //echo $doc_id_link?>" target="_blank">
				   <?php //echo $numero_view?>
				   </a></td>
				   <td width="6%" align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <?php //echo $id_view?>
				   </a></td>
				   <td width="15%" align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <?php //echo $nome_lan_view?>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php //echo $img?>' title=""></td>
				   <td align="center" class="texto"><nobr><?php //echo $data_view?></nobr></td>
				   <td class="texto" align="center" width="9%"><b>
					 <?php //echo $valor_view?>
				   </b></td>
				   <td class="texto" align="center" width="10%">&nbsp;</td>
				   <td class="texto" align="center" width="6%">&nbsp;</td>
				   <td class="texto" align="center" width="8%">&nbsp;</td>
				   <td class="texto" align="center" width="5%"><img src='<?php //echo $url_image_tipo_lan ?>'></td>
				 </tr> 
			-->
	   <?php
		//} // if tipo_lan == PagtoOnline

		?>

		<?php
		//////////////////////////// LOAD CORTE SEMANAL //////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////
		/* if ($info['tipo_movimentacao'] == 'Corte') {
			$valor_view = number_format($info['valor'],2, ',','.');	
			$data_conscilia = formata_data_ts($info['data'], 0, true, false) ;
			$id_view = $info['id_cliente'];
			$codigo_boleto = $info['num_doc'];
			$doc_id_link = $info['doc_id_link']; */

		?>
				   <!--<img src="../../images/<?php //echo $FORMAS_PAGAMENTO_ICONES[$info_bol['vg_pagto_tipo']]?>" width="30" height="21" title="<?php //echo $FORMAS_PAGAMENTO_DESCRICAO[$info_corte['cor_tipo_pagto']]?>">-->
			<!--	
					   <img src='/images/balanco/in.png' title="Pagamento de boleto de PDV pos (<?php //echo $i_row ?>)"/></td>
				   <td class="texto" align="center" width="11%"><a href="/financeiro/corte/corte_boleto_detalhe.php?bbc_id=<?php //echo $doc_id_link?>&BtnSearch=1&tf_ug_id=<?php //echo $id_view?>" target="_blank"><?php //echo $codigo_boleto?></td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <?php //echo $id_view?>
				   </a></td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <?php //echo $nome_lan_view?>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php// echo $img?>' title=""/></td>
				   <td align="center" class="texto"><nobr><?php //echo $data_conscilia?></nobr></td>
				   <td class="texto" align="center" width="9%"><b>
					 <?php //echo $valor_view?>
				   </b></td>
				   <td class="texto" align="center" width="10%">&nbsp;</td>
				   <td class="texto" align="center" width="6%">&nbsp;</td>
				   <td class="texto" align="center" width="8%">&nbsp;</td>
				   <td class="texto" align="center" width="5%"><img src='<?php //echo $url_image_tipo_lan_pos ?>'></td>
				 </tr>
			-->
			
			<?php			
														 
			//$total_entrada += $info['valor'];
		//}// fim if tipo_lan == corte

		?>
														 
								
		<?php //////////////// Pedido desenha linha /////////////////////////?>
		<?php /// compara se a data do corte &eacute; menor que a do pedido ent&atilde;o desenha o pedido
					
		/* if ($info['tipo_movimentacao'] == "Venda") {
									
			$id_view = $info['id_cliente'];
			$id_venda = formata_codigo_venda($info['num_doc']);
			$data_view = formata_data_ts($info['data'], 0, true, false);
			$repasse = number_format($info['repasse'], 2, ',','.');
			$comissao = $info['valor'] - $info['repasse'];
			$total_saida += $info['valor'];
			$total_comissao += $comissao; */
		?>
			<!--	 <tr <?php //echo $bgcolor?> >
				   <td class="texto" align="center" width="4%"><img src='/images/balanco/out.png' title="Venda de pins (<?php //echo $i_row ?>)"/></td>
				   <td class="texto" align="center" width="11%"><a href="/pdv/vendas/com_venda_detalhe.php?venda_id=<?php //echo $id_venda?>" target="_blank" class="link_azul">
					 <?php //echo $id_venda?>
				   </a></td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <?php //echo $id_view?>
				   </a></td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <nobr><?php //echo $nome_lan_view?></nobr>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php //echo $img?>' title=""></td>
				   <td align="center" class="texto"><nobr><?php //echo $data_view?></nobr></td>
				   <td class="texto" align="center" width="9%" title="Valor: <?php// echo number_format($info['valor'], 2, ',','.')?>, Repasse R$<?php //echo number_format($repasse, 2, ',','.')?>, Comissão: <?php //echo number_format($comissao, 2, ',','.')?>"><b>
					 <?php //echo number_format($info['valor'], 2, ',','.')?>
				   </b></td>
				   <td width="10%" align="center" class="texto"><strong>
					 <?php //echo number_format($comissao, 2, ',','.')?>
				   </strong></td>
				   <td width="6%" align="center" class="texto">&nbsp;</td>
				   <td width="8%" align="center" class="texto">&nbsp;</td>
				   <td class="texto" align="center" width="5%"><img src='<?php //echo $url_image_tipo_lan ?>'></td>
				 </tr>  -->
		 <?php   
		//} // fim if isset VENDAS

		 //////////////// Recarga de Celular desenha linha /////////////////////////
					
		/* if ($info['tipo_movimentacao'] == "Recarga Celular") {
									
			$id_view = $info['id_cliente'];
			$id_venda = formata_codigo_venda($info['num_doc']);
			$data_view = formata_data_ts($info['data'], 0, true, false);
			$repasse = number_format($info['repasse'], 2, ',','.');
			$comissao = $info['valor'] - $info['repasse'];
			$total_saida += $info['valor'];
			$total_comissao += $comissao;  */
		?>
			<!--	 <tr <?php //echo $bgcolor?> >
				   <td class="texto" align="center" width="4%"><img src='/images/balanco/icone_celular.gif' title="Recarga de Celular (<?php //echo $i_row ?>)"/></td>
				   <td class="texto" align="center" width="11%">
					 <?php //echo $id_venda?>
					</td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <?php //echo $id_view?>
				   </a></td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <nobr><?php //echo $nome_lan_view?></nobr>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php //echo $img?>' title=""></td>
				   <td align="center" class="texto"><nobr><?php //echo $data_view?></nobr></td>
				   <td class="texto" align="center" width="9%" title="Valor: <?php //echo number_format($info['valor'], 2, ',','.')?>, Repasse R$<?php //echo number_format($repasse, 2, ',','.')?>, Comissão: <?php //echo number_format($comissao, 2, ',','.')?>"><b>
					 <?php //echo number_format($info['valor'], 2, ',','.')?>
				   </b></td>
				   <td width="10%" align="center" class="texto"><strong>
					 <?php //echo number_format($comissao, 2, ',','.')?>
				   </strong></td>
				   <td width="6%" align="center" class="texto">&nbsp;</td>
				   <td width="8%" align="center" class="texto">&nbsp;</td>
				   <td class="texto" align="center" width="5%"><img src='<?php //echo $url_image_tipo_lan ?>'></td>
				 </tr>  -->
		 <?php   
		//} // fim if Recarga Celular

		 //////////////// Seguro desenha linha /////////////////////////
					
		/* if ($info['tipo_movimentacao'] == "Seguro") {
									
			$id_view = $info['id_cliente'];
			$id_venda = formata_codigo_venda($info['num_doc']);
			$data_view = formata_data_ts($info['data'], 0, true, false);
			$repasse = number_format($info['repasse'], 2, ',','.');
			$comissao = $info['valor'] - $info['repasse'];
			$total_saida += $info['valor'];
			$total_comissao += $comissao;  */
		?>
			<!--	 <tr <?php //echo $bgcolor?> >
				   <td class="texto" align="center" width="4%"><img src='/images/balanco/icone-seguros.png' title="Seguro (<?php //echo $i_row ?>)"/></td>
				   <td class="texto" align="center" width="11%">
					 <?php //echo $id_venda?>
					</td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <?php //echo $id_view?>
				   </a></td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <nobr><?php //echo $nome_lan_view?></nobr>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php //echo $img?>' title=""></td>
				   <td align="center" class="texto"><nobr><?php //echo $data_view?></nobr></td>
				   <td class="texto" align="center" width="9%" title="Valor: <?php //echo number_format($info['valor'], 2, ',','.')?>, Repasse R$<?php //echo number_format($repasse, 2, ',','.')?>, Comissão: <?php //echo number_format($comissao, 2, ',','.')?>"><b>
					 <?php //echo number_format($info['valor'], 2, ',','.')?>
				   </b></td>
				   <td width="10%" align="center" class="texto"><strong>
					 <?php //echo number_format($comissao, 2, ',','.')?>
				   </strong></td>
				   <td width="6%" align="center" class="texto">&nbsp;</td>
				   <td width="8%" align="center" class="texto">&nbsp;</td>
				   <td class="texto" align="center" width="5%"><img src='<?php //echo $url_image_tipo_lan ?>'></td>
				 </tr>
			-->
		 <?php   
		//} // fim if Recarga Celular

		 //////////////// B2C desenha linha /////////////////////////
					
		/* if ($info['tipo_movimentacao'] == "B2C") {
									
			$id_view = $info['id_cliente'];
			$id_venda = formata_codigo_venda($info['num_doc']);
			$data_view = formata_data_ts($info['data'], 0, true, false);
			$repasse = number_format($info['repasse'], 2, ',','.');
			$comissao = $info['valor'] - $info['repasse'];
			$total_saida += $info['valor'];
			$total_comissao += $comissao; */
		?>
			<!--	 <tr <?php //echo $bgcolor?> >
				   <td class="texto" align="center" width="4%"><img src='https://www.e-prepag.com.br/prepag2/b2c/imgs/b2c_icon.png' title="B2C (<?php //echo $i_row ?>)"/></td>
				   <td class="texto" align="center" width="11%">
					 <?php //echo $id_venda?>
					</td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <?php //echo $id_view?>
				   </a></td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
				   <nobr><?php //echo $nome_lan_view?></nobr>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php //echo $img?>' title=""></td>
				   <td align="center" class="texto"><nobr><?php //echo $data_view?></nobr></td>
				   <td class="texto" align="center" width="9%" title="Valor: <?php //echo number_format($info['valor'], 2, ',','.')?>, Repasse R$<?php //echo number_format($repasse, 2, ',','.')?>, Comissão: <?php //echo number_format($comissao, 2, ',','.')?>"><b>
					 <?php //echo number_format($info['valor'], 2, ',','.')?>
				   </b></td>
				   <td width="10%" align="center" class="texto"><strong>
					 <?php //echo number_format($comissao, 2, ',','.')?>
				   </strong></td>
				   <td width="6%" align="center" class="texto">&nbsp;</td>
				   <td width="8%" align="center" class="texto">&nbsp;</td>
				   <td class="texto" align="center" width="5%"><img src='<?php //echo $url_image_tipo_lan ?>'></td>
				 </tr>
			-->
		 <?php   
		//} // fim if Recarga Celular

		//////////////////////////// LOAD DESENHA BALANCO //////////////////////////////////////////////////
		// s&oacute; desenha a linha do balanco se a data do balanco for maior que as datas do corte, do pedido e do boleto

		/* if ($info['tipo_movimentacao'] == 'Balanco') {
						
			$id_view = $info['id_cliente'];
			$data_view = formata_data_ts($info['data'], 0, true, false);
			$valor_view = number_format($info['valor'],2, ',','.');
			$saldo_view = number_format($info['ug_saldo'],2, ',','.');
			$limite_view = number_format($info['ug_limite'],2, ',','.');
			$resultado = $info['resultado'];
			$num_doc =  $info['num_doc'];
			$doc_id_link = $info['doc_id_link'];

				if ($resultado == 3 || $resultado == 1 || $resultado == 5) {
									$vcolor = "bgcolor='#FFFFCC'";	
								} else {
									$vcolor = "bgcolor='#E7B6B6'";
								}
			?>
			  <?php 
							   if ($resultado == 5) {
									$url = "";
									$url_fim = "";
									$img_balanco_st = "/images/balanco/balanco_ponto_inicial.png";
									$title_status_balanco = "Ponto Inicial";
								} else {
									//$url = "<a href='com_detalhe_balanco.php?bal_id=".$num_doc."&id_lan=".$id_view."' target='_blank' class='link_azul'>";
									$url = "<a name='".$num_doc."' id='".$num_doc."' onClick='popBal(".$num_doc.",".$id_view.");' class='link_azul'>";
									$url_fim = "</a>";
									$img_balanco_st = "/images/balanco/bal.png";
									$title_status_balanco = "Balan&ccedil;o Peri&oacute;dico";
							    }
						*/   
						   ?>

			<!--	<tr <?php //echo $vcolor?> id="<?php //echo 'linha'.$num_doc?>" name="<?php //echo 'linha'.$num_doc?>">
				   <td class="texto" align="center" width="4%"><img src='<?php //echo $img_balanco_st ?>' title="balanco - <?php //echo $title_status_balanco." (".$i_row.")" ?>"/></td>
				   <td class="texto" align="center" width="11%"><?php //echo $doc_id_link ?></td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
					 <?php //echo $id_view?>
				   </a></td>
				   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php //echo $id_view?>" target="_blank">
					 <?php //echo $nome_lan_view?>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php //echo $img?>' title=""/></td>
					<td align="center" class="texto"><nobr><?php //echo $data_view?></nobr></td>
					 <td class="texto" align="center" width="9%"><?php //echo $valor_view?></td>
				  <td class="texto" align="center" width="10%">&nbsp;</td>
				   <td class="texto" align="center" width="6%"><?php //echo $saldo_view?></td>
				   <td class="texto" align="center" width="8%"><?php //echo $limite_view?></td>
				   <td class="texto" align="center" width="5%"><img src='<?php //echo $url_image_tipo_lan ?>'></td>
				 </tr>
			-->
		   <?php
		//} // fim if balancos

/*
		// Prepara lista de usuários que aparecem na conculta
		if(strpos($lista_ids, $info['id_cliente'])===false) {
			if($lista_ids!="") $lista_ids .= ", ";
			$lista_ids .= $info['id_cliente'];
		}
*/
	} // fim do while principal
	?>
			  </table>
	</div>
	  </tr>
      <!--<tr align="center"><td><table class="table">
	  <tr>
		<td colspan="20" bgcolor="#FFFFFF" class="texto"></font></td>
		</tr>
	  <tr>
		<td align="center" class="texto">
		</tr>  -->
	</table>
		<br>
	<?php 
		
	$varse1 .= "";
		
	//paginacao_query($inicial, $total_table, $max, 50, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varse1);

	?>
		</td>
	</tr>
	</table>
	<?php 
		
// Imprime lista de usuários que aparecem na conculta
//echo "IDs usuários: $lista_ids<br>";
	?>

	<div align="center">
	
	<?php
} else { // fim do if query de contagem foi maior que 0 (res > 0 )

// desenha o cabeçalho para nova pesquisa
?>
	<br>

   <!-- <span class="style1">Nenhum registro foi encontrado   </span><br> -->
<?php
///echo "<br>DADOS1:".$sql."<br>" ;
?>
	<br>
	</div>
	
<?php
}
?>

<table style="text-align: center;font-size: .8em;" id="table_id" class="display">

    <thead>
        <tr>
            <th style="color:black;">Tipo</th>
            <th style="color:black;">Nº Documento</th>
			<th style="color:black;">Codigo Cliente	</th>
			<th style="text-align: center;color:black;">Nome Cliente</th>
			<th style="color:black;">Tipo</th>
			<th style="color:black;">Data</th>
			<th style="color:black;">Valor</th>
			<th style="color:black;">Comissão</th>
			<th style="color:black;">Saldo</th>
			<th style="color:black;">Limite</th>
			<th style="color:black;">Transação</th>
			<th style="color:black;">Risco</th>	
        </tr>
    </thead>
    <tbody>
	<?php 
	
	if(count($dados) > 0 && !empty($dados)){
	    foreach($dados as $key => $value){?>
         <tr>
	        <td>
			    <?php
			     
				 switch($value["tipo_movimentacao"]){
				 
				    case "Balanco":
					     if( $value['resultado']== 5){
						    echo '<img style="margin-left: 10px;" src="/images/balanco/balanco_ponto_inicial.png">';
						 }else{
						    echo '<img style="margin-left: 10px;" src="/images/balanco/bal.png">';
						 }
					break;
					case "Venda":
					  $total_saida += $value['valor'];
					  $total_comissao += ($value['valor'] - $value['repasse']);
					  echo '<img class="venda" style="margin-left: 10px;" src="/images/balanco/out.png">';
					break;
					case "Corte":
					  $total_entrada += $value['valor'];
					  echo '<img style="margin-left: 10px;" src="/images/balanco/in.png">';
					break;
					case "PagtoOnline":
					   $total_entrada += $value['valor'];
					   $tipo_pagto = $value['tipo_pagto'];
			           $tipo_pagto_index_img = (($tipo_pagto==10)?"A":$tipo_pagto);
					   echo '<div style="margin-left: 10px;">'.getLogoBancoSmall($tipo_pagto_index_img).'</div>';
					break;
					
					case "Boleto":
					   $total_entrada += $value['valor'];
					   echo '<img style="margin-left: 10px;" src="/images/balanco/in.png">';
					break;
					case "Estorno":
						$total_entrada += $value['valor'];
						echo '<img style="margin-left: 10px;" src="/images/balanco/in.png">';
					 break;

				 }
				 
			    ?>
			</td>
			<td>
			   <?php
			   
			        switch($value["tipo_movimentacao"]){
				 
						case "Balanco":
						     
							 	if ($value['resultado'] == 3 || $value['resultado'] == 1 || $value['resultado'] == 5) {
									$vcolor = "background-color:#FFFFCC;color:black;";
                                    $balancolor = "y";									
								} else {
									$vcolor = "background-color:#E7B6B6;color:black;";
									$balancolor = "v";
								}
						
							$res = ($value["doc_id_link"] != "") ? $value["doc_id_link"] : $value["num_doc"];
							echo '<span class="balanco'.$balancolor.'" style="'.$vcolor.'">'.$res.'</span>';
						
						break;
						case "PagtoOnline":
							echo '<a href="/pdv/vendas/com_venda_detalhe.php?venda_id='.$value["doc_id_link"].'" target="_blank" class="link_azul">'.$value["num_doc"].'</a>';
						break;
						case "Corte":
					        echo '<a href="/financeiro/corte/corte_boleto_detalhe.php?bbc_id='.$value["doc_id_link"].'&BtnSearch=1&tf_ug_id='.$value["id_cliente"].'" target="_blank">'.$value["num_doc"].'</a>';
					    break;
						case "Boleto":
							echo '<a href="/pdv/vendas/com_venda_detalhe.php?venda_id='.$value["doc_id_link"].'" target="_blank" class="link_azul">'.$value["num_doc"].'</a>';
						break;
						default:
						    $res =($value["doc_id_link"] != "") ? $value["doc_id_link"] : $value["num_doc"];
							echo '<a href="/pdv/vendas/com_venda_detalhe.php?venda_id='.$res.'" target="_blank" class="link_azul">'.$res.'</a>';
						break;
					
				    }
			   
			   ?>
			</td>
			<td><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $value["id_cliente"];?>" target="_blank"><?php echo $value["id_cliente"];?></a></td>
			<td><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $value["id_cliente"];?>" target="_blank"><?php echo ($value["cnpj"] != "") ? $value["fantasia"] : $value["nome"];?></a></td>
			<td><?php echo ($value["cnpj"] != "") ? '<img style="margin-left: 10px;" src="/images/balanco/pj.png">' : '<img src="/images/balanco/pf.png">';?></td>
			<td style="color:black;"><?php echo formata_data_ts($value['data'], 0, true, false);?></td>
			<td style="color:black;"><?php echo number_format($value['valor'], 2, '.','');?></td>
			<td style="color:black;">
			<?php 
			    switch($value["tipo_movimentacao"]){
				 
						case "Venda":
						    echo number_format($value['valor'] - $value['repasse'], 2, '.','');	
						break;
						default:
				           	   
						break;
					
				}
			?>
			</td>
			<td style="color:black;"><?php 	
			    switch($value["tipo_movimentacao"]){
				 
						case "Balanco":
						    echo number_format($info['ug_saldo'],2, ',','.'); 
						break;
						default:
				             
						break;
					
				}
			?>
			</td>
			<td style="color:black;"><?php 
			    switch($value["tipo_movimentacao"]){
				 
						case "Balanco":
						    echo number_format($info['ug_limite'],2, ',','.'); 
						break;
						default:
				             
						break;
					
				}
			?>
			</td>
			<td style="color:black;"><?php
			
			    switch($value["tipo_movimentacao"]){
				 
						case "Balanco":
						    echo "Balanço"; 
						break;
						case "PagtoOnline":
						    echo "Pagamento Online"; 
						break;
						default:
				            echo $value['tipo_movimentacao'];
						break;
					
				}
			
			?></td>
			<td>
			    <?php
			     
				 switch($value["tipo_lan"]){
				 
				    case 1:
					  echo '<img style="margin-left: 10px;" src="/images/balanco/POS.png">';
					break;
					case 2:
					  echo '<img style="margin-left: 10px;" src="/images/balanco/PRE.png">';
					break;
					default:
					  echo '<img style="margin-left: 10px;" src="/images/balanco/baixa.png">';
					break;
				
				 }
				 
			    ?>
			</td>
		
		
	     </tr> 
	<?php 
	    }
	
	}
	
	?>
	 <tr>
			<td style="color:black;">Boletos: <?php echo number_format($total_entrada_geral,2, ',','.')?></td>
			<td style="color:black;">Vendas: <?php echo number_format($total_saida_geral ,2, ',','.')?></td>
			<td style="color:black;">Comissao: <?php echo number_format($total_comissao_geral ,2, ',','.')?></td>
			<td style="color:black;"></td>
			<td style="color:black;"></td>
			<td style="color:black;"></td>
			<td style="color:black;"></td>
			<td style="color:black;"></td>
			<td style="color:black;"></td>
			<td style="color:black;"></td>
			<td style="color:black;"></td>
			<td style="color:black;"></td>	
     </tr>
	</tbody>
</table>

<form style="margin: 20px;" action="/pdv/com_pesquisa_extrato_geral.php" method="get">
  <input type="hidden" name="inicial" value="2000">
  <input type="hidden" id="data_pagina" name="tf_v_data_inclusao_ini" value="">
  <input type="hidden" id="data_pagina_fim" name="tf_v_data_inclusao_fim" value="">
  <div style="background-color: #ccc;padding:8px;width: 190px;">
	  <label style="display:block;color:black;">Navegação entre os dados</label>
	  <input type="submit" name="btPesquisar[]" value="Anterior">
	  <input type="submit" name="btPesquisar[]" value="Proximo">
	  <strong>
	        <input style="display:none;" type="checkbox" name="dd_balancos" id="dd_balancos" checked>
			<!--Balan&ccedil;os-->
	  </strong>
	  <strong>
			<input style="display:none;" type="checkbox" name="dd_boletos" id="dd_boletos"  checked>
			<!--Pagamentos-->
	  </strong>
	  <strong>
			<input style="display:none;" type="checkbox" name="dd_compras" id="dd_compras" checked>
			<!--Compras-->
	  </strong>
	  <label style="display:block;color:black;">Situação de navegação: <span style="color: white;"><?php echo $resto; ?></span></label>
  </div>
</form>

<div class='texto'>
<?php
echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit;
?>
</div>
<hr>
<table border="1" cellspacing="0" bgcolor="#FFFFFF">
	<tr><td colspan="2" align="center" width="200" class='texto'><b>Legenda</b></td></tr>
	<tr bgcolor='#FFFFCC'><td align="center" valign="middle" width="36"><img src='/images/balanco/bal.png' title="Balanço"/></td><td width="200" class='texto'>&nbsp;Registro de Balanço - Saldo correto</td></tr>
	<tr bgcolor='#E7B6B6'><td align="center" valign="middle" width="36"><img src='/images/balanco/bal.png' title="Balanço"/></td><td width="200" class='texto'>&nbsp;Registro de Balanço - Saldo incorreto</td></tr>
	<tr><td align="center" valign="middle" width="36"><img src="/images/balanco/out.png" width="30" height="20" border="0" title="Venda de PINs"></td><td width="200" class='texto'>&nbsp;Compra de PINs</td><tr>
	<tr><td align="center" valign="middle" width="36"><img src='/images/balanco/in.png' title="Boleto de PDV Pós"/></td><td width="200" class='texto'>&nbsp;Boleto de PDV Pré/Pós</td><tr>
	<tr><td align="center" valign="middle" width="36"><img src='/images/balanco/icone_celular.gif' title="Recarga de Celular"/></td><td width="200" class='texto'>&nbsp;Recarga de Celular</td><tr>
	<?php
		$a_avoid_forms = array('1', '2', '7');
		foreach($FORMAS_PAGAMENTO_ICONES as $key => $val) {
			if(in_array($key, $a_avoid_forms)) {
				continue;
			}
	?>
	<tr><td align="center" valign="middle" width="36"><?php echo getLogoBancoSmall($key)?></td><td width="200" class='texto'>&nbsp;<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$key] ?></td><tr>
	<?php
		}
	?>
	<tr><td align="center" valign="middle" width="36"></td><td width="200" class='texto'>&nbsp;</td><tr>
</table>
<?php //echo count($dados);?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.1/js/jquery.dataTables.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.0.0/js/dataTables.buttons.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.0.0/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.0.0/js/buttons.print.min.js"></script>
	<script>
	$(document).ready(function(){
	
	    $("#data_pagina").val($("#tf_v_data_inclusao_ini").val());
		$("#data_pagina_fim").val($("#tf_v_data_inclusao_fim").val());
		
	    $("body").on("change","#tf_v_data_inclusao_ini",function(){

			   $("#data_pagina").val($("#tf_v_data_inclusao_ini").val());
			        			   
		});    
		$("body").on("change","#tf_v_data_inclusao_fim",function(){

			   $("#data_pagina_fim").val($("#tf_v_data_inclusao_fim").val());
		        			   
		});    
	
		$('#table_id').DataTable( {

			"ordering": false,
			dom: 'Bfrtip',
			buttons: [
				'csv', 'excel',
			],
			"pageLength": 100,
			"language": {
            "zeroRecords": "Não foram encontrados registros",
            "infoEmpty": "Dados inexistentes",
			 "info": "Mostrando a página _PAGE_ de _PAGES_",
            "infoFiltered": "",
            "sSearch": "Pesquisar",

				"paginate": {
					"previous": "Anterior",
					"next": "Próximo",
				}
            }
		}
		);
		
		let trvenda = document.querySelectorAll(".venda");
		$.each (trvenda, function(e){
		
		let parente = trvenda[e].parentElement.parentElement;
		parente.style.backgroundColor= "#dcdde1";
		
		});
		
		let trbalancoy = document.querySelectorAll(".balancoy");
		$.each (trbalancoy, function(e){
		
		let parente = trbalancoy[e].parentElement.parentElement;
		parente.style.backgroundColor= "#FFFFCC";
		
		});
		
		let trbalancov = document.querySelectorAll(".balancov");
		$.each (trbalancov, function(e){
		
		let parente = trbalancov[e].parentElement.parentElement;
		parente.style.backgroundColor= "#E7B6B6";
		
		});
				
		$(document).on("click",".paginate_button", function(){
		
		    let trvenda = document.querySelectorAll(".venda");
			$.each (trvenda, function(e){
			
			let parente = trvenda[e].parentElement.parentElement;
			parente.style.backgroundColor= "#dcdde1";
			
			});
		
	        let trbalancoy = document.querySelectorAll(".balancoy");
			$.each (trbalancoy, function(e){
			
			let parente = trbalancoy[e].parentElement.parentElement;
			parente.style.backgroundColor= "#FFFFCC";
			
			});
			
			let trbalancov = document.querySelectorAll(".balancov");
			$.each (trbalancov, function(e){
			
			let parente = trbalancov[e].parentElement.parentElement;
			parente.style.backgroundColor= "#E7B6B6";
			
			});
			
		});
		
		//paginate_button
		
		//console.log(buttons);

		// quando a pagina terminar de carregar a somatória dos valores irão subir

		$('#res_entrada_pag').html('<?php echo number_format($total_entrada,2, ',','.') ?>');
		$('#res_saida_pag').html('<?php echo number_format($total_saida,2, ',','.') ?>');
		$('#res_comiss_pag').html('<?php echo number_format($total_comissao,2, ',','.') ?>');

		var palavra_codigo = $('#codigo_lan').val();
		var palavra_nome_fantasia = $('#nome_fantasia').val();
		if (palavra_codigo != '' || palavra_nome_fantasia != '') {
			$('#balancos').removeAttr('disabled');
		} else {
			$('#balancos').attr('disabled','disabled');
		}
	});

	// habilitar checkbox de balanco se tiver codigo digitado
/*
	$('#codigo_lan').keyup(function(){
		
		var palavra = $('#codigo_lan').val();
		var palavra2 = $('#nome_fantasia').val();

			if (palavra != '' ||  palavra2 != '' ) {
				$('#balancos').removeAttr('disabled');
			} else {
				$('#balancos').attr('disabled','disabled');
				
			}

	}); // fim keyup codigo_lan

*/
	// habilitar checkbox de balanco  se nome fantatisa ou nome tiver digitado
	$('#nome_fantasia').keyup(function(){
		
		var palavra = $('#nome_fantasia').val();
		var palavra2 = $('#codigo_lan').val();
		if ( (palavra != '' || palavra2 != '')) {
				$('#balancos').removeAttr('disabled');
			} else {
				$('#balancos').attr('disabled','disabled');
			}

	}); // fim keyup 
	</script>
	
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
