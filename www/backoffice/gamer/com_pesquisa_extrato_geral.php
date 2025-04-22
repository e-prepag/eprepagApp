<?php 
	set_time_limit ( 6000 ) ;
ob_start();
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."includes/inc_Pagamentos.php";
require_once $raiz_do_projeto."includes/gamer/func_conta_dez_dias.php";

$time_start = getmicrotime();

if(!$inicial)  $inicial     = 0;
if(!$range)    $range       = 1;
if(!$ordem)    $ordem       = 0;
if($Pesquisar) $total_table = 0;
$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 200; //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;
$registros	  = $max;

$dd_balancos	= $_POST['dd_balancos'];
$dd_boletos		= $_POST['dd_boletos'];
$dd_compras		= $_POST['dd_compras'];
if(!$tf_v_data_inclusao_ini) {
	$hoje		= date("d/m/Y");
	$tf_v_data_inclusao_ini = data_menos_n($hoje,5);
	
}	
if(!$tf_v_data_inclusao_fim) $tf_v_data_inclusao_fim = $hoje;
$tf_v_codigo	= $_POST['tf_v_codigo'];
$ug_nome	= strtoupper($_POST['ug_nome']);
$codigo_user	= $_POST['codigo_user'];

if (isset($tf_v_codigo)){
	$varse1 .= "&tf_v_codigo=$tf_v_codigo";
}

if (isset($tf_v_data_inclusao_ini) && isset($tf_v_data_inclusao_fim)) {
	$varse1 .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
}
	
if (isset($ug_nome)){
	$varse1 .= "&ug_nome=$ug_nome";
}
		
if (isset($codigo_user)){
	$varse1 .= "&codigo_user=$codigo_user";
}

$ug_perfil_saldo		= "";
$ug_n					= "";
$ug_n1					= "";
$ug_valor				= "";
$ug_cor_codigo			= "";
$ug_cor_venda_bruta		= "";
$ug_cor_venda_liquida	= "";
//echo $codigo_user."Teste<br>"; 
if ($codigo_user>0){
		$sql  = "select ug_perfil_saldo from usuarios_games where ug_id=$codigo_user";
//echo $sql . "<br>";
		$rs_UG = SQLexecuteQuery($sql);
		if(!$rs_UG || pg_num_rows($rs_UG) == 0) $msg = "Usu&aacute;rio $codigo_user nï¿½o encontrado.\n";
		else {				
			$rs_UG_row = pg_fetch_array($rs_UG);
			$ug_perfil_saldo		= $rs_UG_row['ug_perfil_saldo'];
		}
		//echo $ug_perfil_saldo.":saldo<br>";
		// Vendas em aberto
		$sql  = "select count(*) as n
				from tb_venda_games vg 
				where vg_ug_id=$codigo_user and vg_concilia = 0 and
					vg_id in (
							select vg_id
							from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
							where 1=1 
								and vg_ug_id=$codigo_user 
								and vg_concilia = 0 
								and vg_data_inclusao>=(			
									select cor_periodo_ini from cortes c 
									where 1=1 
										and cor_ug_id = $codigo_user 
										and cor_status=1
									order by cor_periodo_ini desc limit 1
									)
							order by vg_data_inclusao desc		
						)";
//echo $sql . "<br>";
		$rs_Pendentes = SQLexecuteQuery($sql);
		if(!$rs_Pendentes || pg_num_rows($rs_Pendentes) == 0) $msg = "Vendas do usu&aacute; $codigo_user nï¿½o encontrada.\n";
		else {				
			$rs_Pendentes_row = pg_fetch_array($rs_Pendentes);
			$ug_n			= $rs_Pendentes_row['n'];
		}

		// PINs nas vendas em aberto
		$sql  = "select count(*) as n1, sum(vgm.vgm_valor * vgm.vgm_qtde) as valor
				from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
				where vg_ug_id=$codigo_user and vg_concilia = 0 and
					vg_id in (
							select vg_id
							from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
							where 1=1 
								and vg_ug_id=$codigo_user 
								and vg_concilia = 0 
								and vg_data_inclusao>=(			
									select cor_periodo_ini from cortes c 
									where 1=1 
										and cor_ug_id = $codigo_user 
										and cor_status=1
									order by cor_periodo_ini desc limit 1
									)
							order by vg_data_inclusao desc		
						)";
//echo $sql . "<br>";
		$rs_Pendentes = SQLexecuteQuery($sql);
		if(!$rs_Pendentes || pg_num_rows($rs_Pendentes) == 0) $msg = "Vendas do usu&aacute;rio $codigo_user n&atilde;o encontrada.\n";
		else {				
			$rs_Pendentes_row = pg_fetch_array($rs_Pendentes);
			$ug_n1			= $rs_Pendentes_row['n1'];
			$ug_valor		= $rs_Pendentes_row['valor'];
		}

		$sql  = "select cor_codigo, cor_venda_bruta, cor_venda_liquida from cortes where cor_ug_id = $codigo_user and cor_status=1 order by cor_periodo_ini desc limit 1";
//echo $sql . "<br>";
		$rs_cor_aberto = SQLexecuteQuery($sql);
		if(!$rs_cor_aberto || pg_num_rows($rs_cor_aberto) == 0) $msg = "Corte aberto para o usu&aacute;rio $codigo_user n&atilde;o encontrado.\n";
		else {				
			$rs_cor_aberto_row = pg_fetch_array($rs_cor_aberto);
			$ug_cor_codigo			= $rs_cor_aberto_row['cor_codigo'];
			$ug_cor_venda_bruta		= $rs_cor_aberto_row['cor_venda_bruta'];
			$ug_cor_venda_liquida	= $rs_cor_aberto_row['cor_venda_liquida'];
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


// desenhando painel -- abaixo 
require_once "/www/includes/bourls.php";
?>
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
    <link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
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
    <table  class="table fontsize-p txt-preto">
		<tr valign="top" align="center">

		  <td>
		    <form name="form1" method="POST" action="com_pesquisa_extrato_geral.php">
                <table class="table">
					<tr bgcolor="F0F0F0">
					  <td class="texto" align="center" colspan="6"><b>Pesquisa <b><?php echo " (".$total_table." registro"?></b>
				     <?php if($total_table>1) echo "s"; ?><?php echo ")"?></b></td>
					</tr>
					<tr bgcolor="F5F5FB">
					  <td class="texto" align="center"><div align="right"><b>N&uacute;mero da venda</b>: </div></td>
					  <td class="texto" align="center"><div align="left">
						<input name="tf_v_codigo" id='tf_v_codigo' type="text" class="form" value="<?php echo $tf_v_codigo ?>" size="24" maxlength="7">
					</div></td>
					  <td colspan="3" align="center" class="texto">
						  <b>Per&iacute;odo da Pesquisa</b>
						  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
						  a 
						  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
                      </td>
					  <td class="texto" align="center"><input type="submit" name="btPesquisar" id="btPesquisar" value="Pesquisar" class="btn btn-info btn-sm"></td>
					</tr>
					<tr bgcolor="F5F5FB">
					  <td align="center" class="texto"><div align="right"><strong>Nome: </strong></div></td>
					  <td align="center" class="texto"><div align="left"><strong>
					 <input name="ug_nome" type="text" class="form" id="ug_nome" value="<?php echo $ug_nome?>" size="24" maxlength="40">
					  </strong></div></td>
					  <td width="19%" align="left" class="texto"><strong>
					 <label>
	<?php   
					
	if (isset($dd_balancos)) $ch_balancos = "checked";
	if (isset($dd_boletos)) $ch_boletos = "checked";
	if (isset($dd_compras)) $ch_compras = "checked";
					
	?>
					
					 <input type="checkbox" name="dd_balancos" id="dd_balancos" <?php echo $ch_balancos?>>
					 </label>
					 Balan&ccedil;os</strong></td>
					  <td width="17%" align="left" class="texto"><strong>
					 <input type="checkbox" name="dd_boletos" id="dd_boletos" <?php echo $ch_boletos?>>
					 Pagamentos</strong></td>
					  <td width="17%" align="left" class="texto"><strong>
					 <input type="checkbox" name="dd_compras" id="dd_compras" <?php echo $ch_compras?>>
					 Compras</strong></td>
					  <td class="texto" align="center">&nbsp;</td>
				  </tr>
					<tr bgcolor="F5F5FB">
					  <td align="center" class="texto"><div align="right"><strong>C&oacute;digo do Usu&aacute;rio: </strong></div></td>
					  <td align="center" class="texto"><div align="left"><strong>
					 <input name="codigo_user" type="text" class="form" id="codigo_user" value="<?php echo $codigo_user?>" size="24" maxlength="7">
					</strong></div></td>
					  <td colspan="3" align="center" class="texto">&nbsp;</td>
					  <td class="texto" align="center">&nbsp;</td>
				  </tr>
				</table>
			  </form>
                      </tr>
                      
<?php
/*
 


*/
if($btPesquisar) {
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// AQUI COMEÇA A QUERY DE RECUPERAR DADOS: //////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////
	$sql = "select num_doc as num_doc, id_cliente, nome, cpf, tipo_pagto, data, valor as valor, tipo_movimentacao, resultado, doc_id_link, deposito_saldo \n";
	$sql .= " from (\n";

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////
		////// TAXAS DE MANUTENÇÃO ANUAL //////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$sql .= " (select 
			(NULL::text) as num_doc,
			pta.pta_data as data ,
			NULL::smallint as tipo_pagto,
			pta.pta_valor as valor,
			'TAXA ANUAL'::text as tipo_movimentacao,
			pta.ug_id as id_cliente,
			ug.ug_nome as nome,
			ug.ug_cpf as cpf,
			NULL::smallint as resultado,
			NULL::smallint as doc_id_link,
			pta.pta_valor as deposito_saldo
				
			from tb_pag_taxa_anual pta 
                            inner join usuarios_games ug on pta.ug_id = ug.ug_id 
                        where ug.ug_id != 7909 ";

			if($codigo_user) {
				$sql .= " and ug.ug_id = '$codigo_user' ";
			}		

			//if($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and b.b_id=" . $tf_v_codigo ." ";

			if ($ug_nome != '') {
			$sql .=" and (ug.ug_nome like '%$ug_nome%') ";
			}

			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
				if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
					$sql .= " and pta.pta_data between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' ";
			$sql .= " group by data, valor, pta.ug_id, ug.ug_nome, ug.ug_cpf, resultado, doc_id_link, deposito_saldo ) \n";	


		///////////////////////////////////////////////////////////////////////////////////////////////////////////////
		////// RECUPERA ESTORNOS //////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $sql .= " union all \n";
		$sql .= " (select 
			(NULL::text) as num_doc,
			e.tpe_data as data ,
			NULL::smallint as tipo_pagto,
			e.tpe_valor as valor,
			'Estorno'::text as tipo_movimentacao,
			e.ug_id as id_cliente,
			ug.ug_nome as nome,
			ug.ug_cpf as cpf,
			NULL::smallint as resultado,
			e.tpe_id as doc_id_link,
			e.tpe_valor as deposito_saldo
				
			from tb_pag_estorno e inner join usuarios_games ug on e.ug_id = ug.ug_id where e.tpe_tipo_user='G' ";

			if($codigo_user) {
				$sql .= " and ug.ug_id = '$codigo_user' ";
			}		

			//if($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and b.b_id=" . $tf_v_codigo ." ";

			if ($ug_nome != '') {
			$sql .=" and (ug.ug_nome like '%$ug_nome%') ";
			}

			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
				if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
					$sql .= " and e.tpe_data between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' ";
			$sql .= " group by data, valor, e.ug_id, ug.ug_nome, ug.ug_cpf, resultado, doc_id_link, deposito_saldo ) \n";	

	//Validacoes
	$msg = "";	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Recupera as vendas ////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/*if (
		(isset($dd_compras) && (!isset($dd_boletos) || !isset($dd_balancos)))
		
	|| (!isset($dd_compras) && (!isset($dd_boletos) || !isset($dd_balancos))) 
		
	|| (isset($dd_compras) && (isset($dd_boletos) || isset($dd_balancos)))) { */

	if (	(isset($dd_compras) && !isset($dd_boletos)) || 
			(!isset($dd_compras) && !isset($dd_boletos) && !isset($dd_balancos)) || 
			(isset($dd_compras) && isset($dd_boletos)) ) {

		// Bloco de carregar dados da venda			///
		if($msg == ""){
		// load vendas // 
			$sql .= " union all \n";
			$sql .= " (select (vg.vg_id::text) as num_doc,
						vg.vg_data_inclusao as data,
						vg.vg_pagto_tipo as tipo_pagto,
						sum(vgm.vgm_valor * vgm.vgm_qtde) as valor,
						'Venda'::text as tipo_movimentacao,
						vg.vg_ug_id as id_cliente,
						ug.ug_nome as nome,
						ug.ug_cpf as cpf,
						NULL::smallint as resultado,
						NULL::bigint as doc_id_link,
						0 as deposito_saldo
						from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join usuarios_games ug on vg.vg_ug_id = ug.ug_id 
						where 1=1 and vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' ";
				if($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and vg.vg_id=" . $tf_v_codigo ." ";
				if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
					if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
						$sql .= " and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' ";
				if($tf_v_codigo) {
					$sql .= " and vg.vg_id = '$tf_v_codigo' ";
					$group .= ",vg.vg_id ";
				}			
				if($ug_nome) {
					$sql .= " and (ug.ug_nome like '%$ug_nome%') ";
					$group .= ",ug.ug_nome ";
				}		  
				if($codigo_user) {
					$sql .= " and ug.ug_id = '$codigo_user' ";
					$group .= ",ug.ug_id ";
				}			
				//////////////////////////// GROUP ///////////////////////////////////////////////////
				$sql .= "  group by num_doc, data, tipo_pagto, vg.vg_ultimo_status,vg.vg_ug_id,ug.ug_nome,ug.ug_cpf,resultado,doc_id_link ".$group.", deposito_saldo  ";
				//////////////////////////////////////////////////////////////////////////////////////
				$sql .= ") ";
				
			}	// fim if msg
		} // fim if $dd_compras 
					

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		////// RECUPERA BALANÇOS //////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if (isset($dd_balancos) ) {	// && (isset($ug_nome) || isset($codigo_user))

			$sql .= " union all \n";
			$sql .= " (select 
				(NULL::text) as num_doc,
				b.b_data_balanco as data ,
				NULL::smallint as tipo_pagto,
				b.b_valor_balanco as valor,
				'Balanco'::text as tipo_movimentacao,
				b.b_ug_id as id_cliente,
				ug.ug_nome as nome,
				ug.ug_cpf as cpf,
				b.b_resultado::smallint as resultado,
				b.b_id as doc_id_link,
				0 as deposito_saldo
					
				from balancos b inner join usuarios_games ug on b.b_ug_id = ug.ug_id where 1=1 ";

				if($codigo_user) {
					$sql .= " and ug.ug_id = '$codigo_user' ";
				}		

				if($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and b.b_id=" . $tf_v_codigo ." ";

				if ($ug_nome != '') {
				$sql .=" and (ug.ug_nome like '%$ug_nome%') ";
				}

				if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
					if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
						$sql .= " and b_data_balanco between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' ";
				$sql .= " group by data, valor, b.b_ug_id, ug.ug_nome, ug.ug_cpf, resultado, doc_id_link, deposito_saldo ) \n";	
		}// fim if balancos


		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		///// RECUPERA VALOR DE CREDITOS COMPRADO - PRE PAGO ////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if (isset($dd_boletos)) { 

			//*** codigo de boleto
			$sql .= " union all \n";
			
			$sql .= "
			(select (bol_documento::text) as num_doc,
			bol_importacao as data ,
			vg_pagto_tipo as tipo_pagto ,
			sum (bbg_valor - bbg_valor_taxa) as valor ,
			'Boleto'::text as tipo_movimentacao,
			ug.ug_id as id_cliente,
			ug.ug_nome as nome,
			ug.ug_cpf as cpf,
			NULL::smallint as resultado,
			vg_id::bigint as doc_id_link,
			vg_deposito_em_saldo as deposito_saldo

			from boletos_pendentes, bancos_financeiros, tb_venda_games, boleto_bancario_games, usuarios_games ug where 1=1 

			 and (bol_banco = bco_codigo) and (bol_venda_games_id=vg_id) and (bco_rpp = 1) and (vg_ug_id = ug_id) ";
		
			if($codigo_user) {
				$sql .= " and ug.ug_id = ".$codigo_user." ";
				$sql .= " and bbg_ug_id = ".$codigo_user." ";
			}

			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
				if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
					$sql .= " and bol_importacao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' ";

			if($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and bol_documento like '%" . $tf_v_codigo."%' ";

			if($ug_nome) {
				$sql .= " and (ug_nome like '%$ug_nome%') ";
			}
			
			$sql .= "and (substr(bol_documento,1,1) = '2' or substr(bol_documento,1,1) = '3' or (substr(bol_documento, 1, 1)='6')) ";
			$sql .= "and bbg_vg_id = vg_id \n";
			$sql .= "group by bol_documento, vg_data_inclusao, vg_pagto_tipo, bol_importacao, ug_id, ug_nome, ug_cpf, resultado, doc_id_link, vg_deposito_em_saldo ";
			$sql .= ")\n ";

		}// fim if boleto
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
		// PAGAMENTOS ONLINE 
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if (isset($dd_boletos) ) {

			//**** codigo de corte
			$sql .= "union all \n";	
			$sql .= "(select numcompra::text as num_doc,
							datainicio as data,
							(".getSQLCodigoNumericoParaPagtoOnline().") as tipo_pagto,

							(sum (total/100 - taxas)::real) as valor,
							'PagtoOnline' as tipo_movimentacao,
							idcliente as id_cliente,
							ug.ug_nome as nome,
							ug.ug_cpf as cpf,
							NULL::smallint as resultado,
							idvenda::bigint as doc_id_link,
							vg_deposito_em_saldo as deposito_saldo
					from tb_pag_compras pc
						 inner join usuarios_games ug on pc.idcliente = ug.ug_id
						inner join tb_venda_games vg on vg.vg_id = pc.idvenda
					where 1=1  and pc.status=3 and (pc.tipo_cliente='M') ";
			if($codigo_user) {
				$sql .= " and pc.idcliente = '$codigo_user' ";
			}

			if($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and numcompra like '%" . $tf_v_codigo."%' ";
				
			if($ug_nome) {
				$sql .= " and (ug.ug_nome like '%$ug_nome%') ";
			}	
						
			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
				if( verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
					$sql .= " and pc.datacompra between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and  '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' ";
			
			$sql .= "\n";			
			$sql .= "group by pc.numcompra::text, pc.datainicio, pc.iforma, tipo_cliente, idcliente, ug_nome, ug_cpf, resultado, doc_id_link, vg_deposito_em_saldo \n";
			$sql .= "order by pc.datainicio ) \n";

		} // fim pagamento online



		$sql .= ") as venda \n";
//		$sql .= "where  tipo_movimentacao = 'Boleto' \n";

		$sql .= "group by venda.num_doc, venda.tipo_pagto, venda.data, venda.valor, tipo_movimentacao, id_cliente, nome, cpf, resultado, doc_id_link, deposito_saldo ";
	

//if(b_IsUsuarioWagner()) { 
//echo "<br><br>(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";
//}

	$total_entrada_geral = 0;
	$total_saida_geral = 0;
        $total_taxa_anual_geral = 0;
	
	$res_tmp = SQLexecuteQuery($sql);
	if ($res_tmp) {
		$total_table = pg_num_rows($res_tmp);

		while( $info = pg_fetch_array($res_tmp) ){

			if (
					($info['tipo_movimentacao'] == 'PagtoOnline') || 
					($info['tipo_movimentacao'] == 'Boleto')
				) {
				$total_entrada_geral += $info['valor'];
			}

			if ($info['tipo_movimentacao'] == "Venda") {
				$total_saida_geral += $info['valor'];			
			}
                        
			if ($info['tipo_movimentacao'] == "TAXA ANUAL") {
				$total_taxa_anual_geral += $info['valor'];			
			}
                        
		}
	}

if ($total_table > 0) {

	$max_reg = (($inicial + $max)>$total_table)?$total_table:$max;

//echo "total_table: ".$total_table."<br>";

	$sql .= "order by data ";
	$sql .= "limit $max offset $inicial ";
/*
if(b_IsUsuarioReinaldo()) { 
echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";
}
*/
	$res = SQLexecuteQuery($sql);
	
	?>
	</tr>
	<tr align="center"><td>
		<center>
			  <div align="center">
			   <table border="0" cellspacing="1" width="96%" align="center">
				 <tr <?php echo $fcolor?> class="texto" >
				   <td height="21" colspan="5" align="left" bgcolor="#EEEEEE" class='texto'>Listando de 
				   <?php echo ($inicial +1)?> a <?php echo ($max_reg)?> de <?php echo $total_table?></td>
				   <td align="center" bgcolor="#EEEEEE" id="res_saida4">&nbsp;</td>
				   <td align="center" bgcolor="#EEEEEE">&nbsp;</td>
				   <td align="center" bgcolor="#EEEEEE" id="saldo4">&nbsp;</td>
				 </tr>
				 <?php if($codigo_user>0) { ?>
				 <tr class="texto">
				   <td height="21" align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD">&nbsp;</td>
				   <td align="right" bgcolor="#DDDDDD"><b>Saldo</b></td>
				   <td align="center" bgcolor="#DDDDDD"><?php echo number_format($ug_perfil_saldo,2, ',','.')?>&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				 </tr>
				 <?php } ?>

				 <tr <?php echo $fcolor?> class="texto" >
				   <td height="21" align="center" ></td>
				   <td align="center">&nbsp;</td>
				   <td colspan="2" align="center" id="res_total3"><strong>Boletos</strong></td>
				   <td width="10%" align="center" id="res_saida3"><strong>Vendas</strong></td>
				   <td align="center"><strong>Taxas Anual</strong></td>
				   <td align="center">&nbsp;</td>
				   <td align="center" id="saldo3">&nbsp;</td>
				 </tr>
				 <tr class="texto">
				   <td height="21" align="center" bgcolor="#DDDDDD" ></td>
				   <td  align="center" bgcolor="#DDDDDD"><strong>Total Geral</strong></td>
				   <td colspan="2"  align="center" bgcolor="#DDDDDD"><?php echo number_format($total_entrada_geral,2, ',','.')?>&nbsp;</td>
				   <td  align="center" bgcolor="#DDDDDD"><?php echo number_format($total_saida_geral,2, ',','.')?>&nbsp;</td>
				   <td  align="center" bgcolor="#DDDDDD" ><?php echo number_format($total_taxa_anual_geral,2, ',','.')?>&nbsp;</td>
				   <td  align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				   <td  align="center" bgcolor="#DDDDDD" >&nbsp;</td>
				 </tr>
				 <tr class="texto">
				   <td width="4%" height="21" align="center" ></td>
				   <td  align="center"><strong>Total</strong></td>
				   <td colspan="2"  align="center" id="res_entrada_pag"><?php // echo number_format($total_entrada,2, ',','.')?>&nbsp;</td>
				   <td  align="center" id="res_saida_pag">&nbsp;</td>
				   <td  align="center" id="res_taxa_anual">&nbsp;</td>
				   <td  align="center" >&nbsp;</td>
				   <td align="center">&nbsp;</td>
				 </tr>
				 <tr class='texto'>
				   <td height="21" align="center" bgcolor="#CCCCCC" ><strong>Tipo</strong></td>
				   <td  align="center" bgcolor="#CCCCCC"><strong>N&ordm; Documento</strong></td>
				   <td  align="center" bgcolor="#CCCCCC" id="res_total2"><strong>Codigo Cliente</strong></td>
				   <td  align="center" bgcolor="#CCCCCC" id="res_total2"><strong>Nome Cliente</strong></td>
				   <td  align="center" bgcolor="#CCCCCC" id="saldo2">&nbsp;</td>
				   <td  align="center" bgcolor="#CCCCCC" id="saldo2"><strong>Data</strong></td>
				   <td align="center" bgcolor="#CCCCCC"><strong>Valor</strong></td>
				   <td align="center" bgcolor="#CCCCCC"><strong>Saldo</strong></td>
				 </tr>
				 <?php	
				/// fechando a query 
	$i_row = 0;
	$total_entrada = 0;
	$total_saida = 0;
        $total_taxa_anual = 0;
	
	while( $info = pg_fetch_array($res) ){
		//
		$nome_gamer_view = $info['nome'];


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
			$doc_id_link = $info['doc_id_link'];
			$vg_deposito_em_saldo = $info['deposito_saldo'];


			/////////////////////////////////////
		?>
				 <tr <?php echo $bcolor?>>
				   <td class="texto" align="center" width="4%"><img src='/images/gamer/balanco/<?php echo (($vg_deposito_em_saldo=="1")?"inblue.png":"in.png")?>' title="<?php echo (($vg_deposito_em_saldo=="1")?"Depósito em Saldo de Gamer":"Boleto de Gamer")?>"/></td>
				   <td class="texto" align="center" width="11%"><a href="/gamer/vendas/com_venda_detalhe.php?venda_id=<?php echo $doc_id_link?>" target="_blank">
				   <?php echo $numero_view?>
				   </a></td>
				   <td width="6%" align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				   <?php echo $id_view?>
				   </a></td>
				   <td width="15%" align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				   <?php echo $nome_gamer_view?>
				   </a></td>
				   </a></td>
				   <td class="texto" align="center" width="4%"></td>
				   <td align="center" class="texto"><nobr><?php echo $data_view?></nobr></td>
				   <td class="texto" align="center" width="9%"><b>
					 <?php echo $valor_view?>
				   </b></td>
				   <td class="texto" align="center" width="10%">&nbsp;</td>
				   <td class="texto" align="center" width="6%">&nbsp;</td>
				 </tr>
	   <?php
		}// if tipo_lan == boleto  

		//////////////////////////// LOAD DESENHA PAGAMENTO ONLINE ////////////////////////////////////////////////////
		//// linha de desenhar o pagamento online
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if ($info['tipo_movimentacao'] == 'PagtoOnline') {
						
			$total_entrada += $info['valor'];
			$boleto_valor = $info['valor'];
			//////// VIEW DATAS E PRE&Ccedil;OS
			$numero_view = $info['num_doc'];
			$data_view  = formata_data_ts($info['data'], 0, true, false);
			$valor_view = number_format($info['valor'],2, ',','.');
			$id_view = $info['id_cliente'];
			$tipo_pagto = $info['tipo_pagto'];
			$doc_id_link = $info['doc_id_link'];
			$vg_deposito_em_saldo = $info['deposito_saldo'];

			/////////////////////////////////////
		?>
				 <tr <?php echo $bcolor?>>
				   <td class="texto" align="center" width="4%" title="<?php echo $i_row ?>"><?php echo "<nobr>".getLogoBancoSmall($tipo_pagto).(($vg_deposito_em_saldo=="1") ? "&nbsp;<img src='/images/gamer/balanco/inblue.png' title='Dep&oacute;sito'/>":"")."</nobr>"; ?></td>
				   <td class="texto" align="center" width="11%"><a href="/gamer/vendas/com_venda_detalhe.php?venda_id=<?php echo $doc_id_link?>" target="_blank">
				   <?php echo $numero_view?>
				   </a></td>
				   <td width="6%" align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				   <?php echo $id_view?>
				   </a></td>
				   <td width="15%" align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				   <?php echo $nome_gamer_view?>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php echo $img?>' title=""></td>
				   <td align="center" class="texto"><nobr><?php echo $data_view?></nobr></td>
				   <td class="texto" align="center" width="9%"><b>
					 <?php echo $valor_view?>
				   </b></td>
				   <td class="texto" align="center" width="10%">&nbsp;</td>
				   <td class="texto" align="center" width="6%">&nbsp;</td>
				 </tr>
	   <?php
		}// if tipo_lan == PagtoOnline

		 //////////////// Pedido desenha linha /////////////////////////
		 /// compara se a data do corte &eacute; menor que a do pedido ent&atilde;o desenha o pedido
					
		if ($info['tipo_movimentacao'] == "Venda") {
									
			$id_view = $info['id_cliente'];
			$id_venda = formata_codigo_venda($info['num_doc']);
			$data_view = formata_data_ts($info['data'], 0, true, false);
			$total_saida += $info['valor'];
		?>
				 <tr <?php echo $bgcolor?> >
				   <td class="texto" align="center" width="4%"><img src='/images/gamer/balanco/out.png' title="Venda de pins (<?php echo $i_row ?>)"/></td>
				   <td class="texto" align="center" width="11%"><a href="/gamer/vendas/com_venda_detalhe.php?venda_id=<?php echo $id_venda?>" target="_blank" class="link_azul">
					 <?php echo $id_venda?>
				   </a></td>
				   <td align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				   <?php echo $id_view?>
				   </a></td>
				   <td align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				   <nobr><?php echo $nome_gamer_view?></nobr>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php echo $img?>' title=""></td>
				   <td align="center" class="texto"><nobr><?php echo $data_view?></nobr></td>
				   <td class="texto" align="center" width="9%" title="Valor: <?php echo number_format($info['valor'], 2, ',','.')?>"><b>
					 <?php echo number_format($info['valor'], 2, ',','.')?>
				   </b></td>
				   <td width="6%" align="center" class="texto">&nbsp;</td>
				 </tr>
		 <?php   
		} // fim if isset VENDAS

		//////////////////////////// LOAD DESENHA BALANCO //////////////////////////////////////////////////
		// s&oacute; desenha a linha do balanco se a data do balanco for maior que as datas do corte, do pedido e do boleto

		if ($info['tipo_movimentacao'] == 'Balanco') {
						
			$id_view = $info['id_cliente'];
			$data_view = formata_data_ts($info['data'], 0, true, false);
			$valor_view = number_format($info['valor'],2, ',','.');
			$saldo_view = number_format($info['ug_saldo'],2, ',','.');
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
//									$url = "";
//									$url_fim = "";
									$img_balanco_st = "/images/gamer/balanco/balanco_ponto_inicial.png";
									$title_status_balanco = "Ponto Inicial";
								} else {
									//$url = "<a href='com_detalhe_balanco.php?bal_id=".$num_doc."&id_lan=".$id_view."' target='_blank' class='link_azul'>";
//									$url = "<a name='".$num_doc."' id='".$num_doc."' onClick='popBal(".$num_doc.",".$id_view.");' class='link_azul'>";
//									$url_fim = "</a>";
									$img_balanco_st = "/images/gamer/balanco/bal.png";
									$title_status_balanco = "Balan&ccedil;o Peri&oacute;dico";
							    }
						   
						   ?>

				<tr <?php echo $vcolor?> id="<?php echo 'linha'.$num_doc?>" name="<?php echo 'linha'.$num_doc?>">
				   <td class="texto" align="center" width="4%"><img src='<?php echo $img_balanco_st ?>' title="balanco - <?php echo $title_status_balanco." (".$i_row.")" ?>"/></td>
				   <td class="texto" align="center" width="11%"><?php echo $doc_id_link ?></td>
				   <td align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
					 <?php echo $id_view?>
				   </a></td>
				   <td align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
					 <?php echo $nome_gamer_view?>
				   </a></td>
				   <td class="texto" align="center" width="4%"><img src='<?php echo $img?>' title=""/></td>
					<td align="center" class="texto"><nobr><?php echo $data_view?></nobr></td>
					 <td class="texto" align="center" width="9%"><?php echo $valor_view?></td>
				   <td class="texto" align="center" width="6%"><?php echo $saldo_view?></td>
				 </tr>
		   <?php
		}// fim if balancos			
													
		//////////////////////////// LOAD DESENHA ESTORNOS //////////////////////////////////////////////////
		
		if ($info['tipo_movimentacao'] == 'Estorno') {
						
			$id_view = $info['id_cliente'];
			$data_view = formata_data_ts($info['data'], 0, true, false);
			$valor_view = number_format($info['valor'],2, ',','.');
			$saldo_view = "";//number_format($info['ug_saldo'],2, ',','.');
			$resultado = $info['resultado'];
			$num_doc =  $info['num_doc'];
			$doc_id_link = $info['doc_id_link'];

			$vcolor = "bgcolor='#FFFFCC'";	
			
			$url = "";
			$url_fim = "";
			$img_balanco_st = "/images/gamer/balanco/icone_Rollback.gif";
		   ?>
			<tr <?php echo $vcolor?> id="<?php echo 'linha'.$num_doc?>" name="<?php echo 'linha'.$num_doc?>">
			   <td class="texto" align="center" width="4%"><img src='<?php echo $img_balanco_st ?>' title="Estorno - <?php echo " (".$i_row.")" ?>"/></td>
			   <td class="texto" align="center" width="11%"><?php echo $doc_id_link ?></td>
			   <td align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				 <?php echo $id_view?>
			   </a></td>
			   <td align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				 <?php echo $nome_gamer_view?>
			   </a></td>
			   <td class="texto" align="center" width="4%"><img src='<?php echo $img?>' title=""/></td>
				<td align="center" class="texto"><nobr><?php echo $data_view?></nobr></td>
				 <td class="texto" align="center" width="9%"><?php echo $valor_view?></td>
			   <td class="texto" align="center" width="6%"><?php echo $saldo_view?></td>
			 </tr>
		   <?php
		}// fim if estornos			
													
		//////////////////////////// LOAD DESENHA TAXAS ANUAIS //////////////////////////////////////////////////
		
		if ($info['tipo_movimentacao'] == 'TAXA ANUAL') {
						
			$id_view = $info['id_cliente'];
			$data_view = formata_data_ts($info['data'], 0, true, false);
			$valor_view = number_format($info['valor'],2, ',','.');
                        $total_taxa_anual += $info['valor'];
			$saldo_view = "";//number_format($info['ug_saldo'],2, ',','.');
			$resultado = $info['resultado'];
			$num_doc =  $info['num_doc'];
			$doc_id_link = $info['doc_id_link'];

			$vcolor = "bgcolor='#FFFFCC'";	
			
			$url = "";
			$url_fim = "";
			$img_balanco_st = "/images/taxa_anual.jpg";
		   ?>
			<tr <?php echo $vcolor?> id="<?php echo 'linha'.$num_doc?>" name="<?php echo 'linha'.$num_doc?>">
			   <td class="texto" align="center" width="4%"><img src='<?php echo $img_balanco_st ?>' title="Estorno - <?php echo " (".$i_row.")" ?>"/></td>
			   <td class="texto" align="center" width="11%"><?php echo $doc_id_link ?></td>
			   <td align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				 <?php echo $id_view?>
			   </a></td>
			   <td align="center" class="texto"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				 <?php echo $nome_gamer_view?>
			   </a></td>
			   <td class="texto" align="center" width="4%"><img src='<?php echo $img?>' title=""/></td>
				<td align="center" class="texto"><nobr><?php echo $data_view?></nobr></td>
				 <td class="texto" align="center" width="9%"><?php echo $valor_view?></td>
			   <td class="texto" align="center" width="6%"><?php echo $saldo_view?></td>
			 </tr>
		   <?php
		}// fim if estornos			
													
	} // fim do while principal

	?>
			  </table>
	</div>
        </td>
	  </tr>
	<tr align="center"><td><table border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
	  <tr>
		<td colspan="20" bgcolor="#FFFFFF" class="texto"></font></td>
		</tr>
	  <tr>
		<td align="center" class="texto">
		</tr>
	</table>
		<br>
	<?php 
		
	$varse1 .= "";
		
	paginacao_query($inicial, $total_table, $max, 50, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varse1);

	?>
		</td>
	</tr>
	</table>

	<div align="center">
	<script>
	$(document).ready(function(){

		// quando a pagina terminar de carregar a somatï¿½ria dos valores irï¿½o subir

		$('#res_entrada_pag').html('<?php echo number_format($total_entrada,2, ',','.') ?>');
		$('#res_saida_pag').html('<?php echo number_format($total_saida,2, ',','.') ?>');
		$('#res_taxa_anual').html('<?php echo number_format($total_taxa_anual,2, ',','.') ?>');
		
		var palavra_codigo = $('#codigo_user').val();
		var palavra_ug_nome = $('#ug_nome').val();
		if (palavra_codigo != '' || palavra_ug_nome != '') {
			$('#balancos').removeAttr('disabled');
		} else {
			$('#balancos').attr('disabled','disabled');
		}
	});

	// habilitar checkbox de balanco se tiver codigo digitado
/*
	$('#codigo_user').keyup(function(){
		
		var palavra = $('#codigo_user').val();
		var palavra2 = $('#ug_nome').val();

			if (palavra != '' ||  palavra2 != '' ) {
				$('#balancos').removeAttr('disabled');
			} else {
				$('#balancos').attr('disabled','disabled');
				
			}

	}); // fim keyup codigo_user

*/
	// habilitar checkbox de balanco  se nome fantatisa ou nome tiver digitado
	$('#ug_nome').keyup(function(){
		
		var palavra = $('#ug_nome').val();
		var palavra2 = $('#codigo_user').val();
		if ( (palavra != '' || palavra2 != '')) {
				$('#balancos').removeAttr('disabled');
			} else {
				$('#balancos').attr('disabled','disabled');
			}

	}); // fim keyup 
	</script>
	<?php
} else { // fim do if query de contagem foi maior que 0 (res > 0 )

// desenha o cabeï¿½alho para nova pesquisa
?>
	<br>

   <span class="style1">Nenhum registro foi encontrado   </span><br>
<?php
///echo "<br>DADOS1:".$sql."<br>" ;
?>
	<br>
	</div>
	
<?php
}
}//end if($btPesquisar)
?>
<div class='texto'>
<?php
echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit;
?>
</div>
<hr>
<table border="1" cellspacing="0" bgcolor="#FFFFFF">
	<tr><td colspan="2" align="center" width="200" class='texto'><b>Legenda</b></td></tr>
	<tr bgcolor='#FFFFCC'><td align="center" valign="middle" width="36"><img src='/images/gamer/balanco/bal.png' title="Balan&ccedil;o"/></td><td width="200" class='texto'>&nbsp;Registro de Balan&ccedil;o - Saldo correto</td></tr>
	<tr bgcolor='#E7B6B6'><td align="center" valign="middle" width="36"><img src='/images/gamer/balanco/bal.png' title="Balan&ccedil;o"/></td><td width="200" class='texto'>&nbsp;Registro de Balan&ccedil;o - Saldo incorreto</td></tr>
	<tr><td align="center" valign="middle" width="36"><img src="/images/gamer/balanco/out.png" width="30" height="20" border="0" title="Venda de PINs"></td><td width="200" class='texto'>&nbsp;Compra de PINs</td><tr>
	<tr><td align="center" valign="middle" width="36"><img src='/images/gamer/balanco/in.png' title="Boleto de Gamer"/></td><td width="200" class='texto'>&nbsp;Boleto de Gamer</td><tr>
	<tr><td align="center" valign="middle" width="36"><img src='/images/gamer/balanco/inblue.png' title="Dep&oacute;sito em Saldo"/></td><td width="200" class='texto'>&nbsp;Dep&oacute;sito em Saldo</td><tr>
	<tr><td align="center" valign="middle" width="36"><img src='/images/gamer/balanco/icone_Rollback.gif' title="Estorno"/></td><td width="200" class='texto'>&nbsp;Estorno de Dep&oacute;sito em Saldo</td><tr>
	<tr><td align="center" valign="middle" width="36"><img src='/images/taxa_anual.jpg' title="Taxa Anual"/></td><td width="200" class='texto'>&nbsp;Taxa de Manutenção Anual</td><tr>
	<?php
		$a_avoid_forms = array('1', '2', '7');
		foreach($FORMAS_PAGAMENTO_ICONES as $key => $val) {
			if(in_array($key, $a_avoid_forms)) {
				continue;
			}
	?>
	<tr><td align="center" valign="middle" width="36"><?php echo getLogoBancoSmall($key)?></td><td width="200" class='texto'>&nbsp;<?php echo utf8_decode($FORMAS_PAGAMENTO_DESCRICAO[$key]) ?></td><tr>
	<?php
		}
	?>
	<tr><td align="center" valign="middle" width="36"></td><td width="200" class='texto'>&nbsp;</td><tr>
</table>
<center>

<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
