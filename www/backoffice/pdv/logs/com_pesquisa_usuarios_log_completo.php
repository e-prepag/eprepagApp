<?php 
set_time_limit ( 6000 ) ;
ob_start();
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/gamer/func_conta_dez_dias.php";

$time_start = getmicrotime();

if(!isset($inicial) || !$inicial)  $inicial     = 0;
if(!isset($range) || !$range)    $range       = 1;
if(!isset($ordem) || !$ordem)    $ordem       = 0;
if(isset($Pesquisar) && $Pesquisar) $total_table = 0;
$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 30; //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;
$registros	  = $max;

if(!isset($tf_v_data_inclusao_ini) || !$tf_v_data_inclusao_ini) {
	$hoje		= date("d/m/Y");
	$tf_v_data_inclusao_ini = data_menos_n($hoje,1);
	
}	
if(!isset($tf_v_data_inclusao_fim) || !$tf_v_data_inclusao_fim) $tf_v_data_inclusao_fim = $hoje;

if (isset($tf_v_data_inclusao_ini) && isset($tf_v_data_inclusao_fim)) {
	$varse1 .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
}
	
if (isset($vg_id)){
	$varse1 .= "&vg_id=$vg_id";
}
		
if (isset($codigo_user)){
	$varse1 .= "&codigo_user=$codigo_user";
}
		
if (isset($vg_ultimo_status)){
	$varse1 .= "&vg_ultimo_status=$vg_ultimo_status";
}

if (isset($uglt_id)){
	$varse1 .= "&uglt_id=$uglt_id";
}

$ug_perfil_saldo		= "";
$ug_n					= "";
$ug_n1					= "";
$ug_valor				= "";
$ug_cor_codigo			= "";
$ug_cor_venda_bruta		= "";
$ug_cor_venda_liquida	= "";
//echo $codigo_user."Teste<br>"; 

//if (!empty($codigo_user)) {
	
	$sql = "
	select * from 
	(
	select 'Usuário' as tipo, ugl_id as id, ugl_data_inclusao as data_inclusao, ugl_uglt_id as uglt_id, ugl_ug_id as ug_id, ugl_vg_id as vg_id 
	from dist_usuarios_games_log ";
	unset($sbds_aux);
	if (!empty($vg_id))
		$sbds_aux[] = "ugl_vg_id = " . $vg_id ;
	if (!empty($codigo_user))
		$sbds_aux[] = "ugl_ug_id = ". $codigo_user ;
	if (!empty($tf_v_data_inclusao_ini))
		$sbds_aux[] = "ugl_data_inclusao >= '". formata_data_ts($tf_v_data_inclusao_ini, 2, true, false) ." 00:00:00'";
	if (!empty($tf_v_data_inclusao_fim))
		$sbds_aux[] = "ugl_data_inclusao <= '". formata_data_ts($tf_v_data_inclusao_fim, 2, true, false) ." 23:59:59'";
	if (!empty($uglt_id))
		$sbds_aux[] = "ugl_uglt_id = ". $uglt_id ;
	if (is_array($sbds_aux)) {
		$sql .= ' WHERE ' . implode(' AND ', $sbds_aux);
	}
	$sql .= "
	union all 
	select 'Operador' as tipo, ugol_id as id, ugol_data_inclusao as data_inclusao, ugol_uglt_id as uglt_id, ugol_ugo_id as ug_id, ugol_vg_id as vg_id 
	from dist_usuarios_games_operador_log ";
	unset($sbds_aux);
	if (!empty($vg_id))
		$sbds_aux[] = "ugol_vg_id = " . $vg_id ;
	if (!empty($codigo_user))
		$sbds_aux[] = "ugol_ugo_id in (select ugo_id from dist_usuarios_games_operador where ugo_ug_id = ". $codigo_user .")";
	if (!empty($tf_v_data_inclusao_ini))
		$sbds_aux[] = "ugol_data_inclusao >= '". formata_data_ts($tf_v_data_inclusao_ini, 2, true, false) ." 00:00:00'";
	if (!empty($tf_v_data_inclusao_fim))
		$sbds_aux[] = "ugol_data_inclusao <= '". formata_data_ts($tf_v_data_inclusao_fim, 2, true, false) ." 23:59:59'";
	if (!empty($uglt_id))
		$sbds_aux[] = "ugol_uglt_id = ". $uglt_id ;
	if (is_array($sbds_aux)) {
		$sql .= ' WHERE ' . implode(' AND ', $sbds_aux);
	}
	$sql .= "
	union all
	select 'Venda' as tipo, vg_id as id, vg_data_inclusao as data_inclusao, vg_ultimo_status as uglt_id, vg_ug_id as ug_id, vg_id as vg_id 
	from tb_dist_venda_games vg ";
	unset($sbds_aux);
	if (!empty($vg_id))
		$sbds_aux[] = "vg_id = " . $vg_id ;
	if (!empty($codigo_user))
		$sbds_aux[] = "vg_ug_id = ". $codigo_user ;
	if (!empty($tf_v_data_inclusao_ini))
		$sbds_aux[] = "vg_data_inclusao >= '". formata_data_ts($tf_v_data_inclusao_ini, 2, true, false) ." 00:00:00'";
	if (!empty($tf_v_data_inclusao_fim))
		$sbds_aux[] = "vg_data_inclusao <= '". formata_data_ts($tf_v_data_inclusao_fim, 2, true, false) ." 23:59:59'";
	if (!empty($vg_ultimo_status))
		$sbds_aux[] = "vg_ultimo_status = ". $vg_ultimo_status ;
	if (is_array($sbds_aux)) {
		$sql .= ' WHERE ' . implode(' AND ', $sbds_aux);
	}
	$sql .= "
	) log 
	";
	
	$res_tmp = SQLexecuteQuery($sql);
	if ($res_tmp) {
		$total_table = pg_num_rows($res_tmp);
	}

	$max_reg = (($inicial + $max)>$total_table)?$total_table:$max;

	//echo "total_table: ".$total_table."<br>";

	$sql .= " order by data_inclusao desc ";
	$sql .= " limit $max offset $inicial ";

if(b_IsUsuarioReinaldo()) { 
//echo "<br><br>(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";
}
	//if(b_IsUsuarioWagner()) { 
	//echo "<br><br>(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";
	//}

	$res = SQLexecuteQuery($sql);

//}//end if (!empty($codigo_user))
?>

	<?php $pagina_titulo = "LAN Houses Log Completo"; ?>
        <link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
        <script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
        <script src="/js/global.js"></script>
	<script language="javascript">
    $(function(){
        var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
    });
        
	function validaUsuario()
	{
		if (document.form1.codigo_user.value == "")
		{
			alert("Favor ID do Usuário.");
			document.form1.codigo_user.focus();
			return false;
		}
		return true;
	}
	// onsubmit="return validaUsuario();"
	</script>
	<style type="text/css">
<!--
.style1 {
	color: #FF0000;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
}
-->
   </style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<center>
    <table class="table txt-preto fontsize-pp">
		<tr valign="top" align="center">
		  <td>
		    <form name="form1" method="POST" action="com_pesquisa_usuarios_log_completo.php">
			<table class="table txt-preto fontsize-pp">
					<tr bgcolor="F0F0F0">
					  <td class="texto" align="center" colspan="6"><b>Pesquisa <b><?php 
						echo " (".$total_table." registro"; 
						if(isset($total_table) && $total_table>1) echo "s"; 
						echo ")"?></b></td>
					</tr>
					<tr bgcolor="F5F5FB">
					  <td class="texto" align="center" colspan="2"></td>
					  <td colspan="3" align="center" class="texto">
						  <b>Per&iacute;odo do Cancelamento</b>
						  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
						  a 
						  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
                      </td>
					  <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="botao_simples"></td>
				</tr>
				<tr bgcolor="F5F5FB">
					  <td align="center" class="texto"><div align="right"><strong>C&oacute;digo da Venda: </strong></div></td>
					  <td align="center" class="texto"><div align="left"><strong>
					 <input name="vg_id" type="text" class="form" id="vg_id" value="<?php echo $vg_id?>" size="24" maxlength="7">
					  </strong></div></td>
					  <td align="center" class="texto" colspan="3"><b>Status: </b>
						<select name="vg_ultimo_status" id="vg_ultimo_status" class="combo_normal">
						  <option value="" <?php if(empty($vg_ultimo_status)) echo "selected" ?>>Todos</option>
						  <?php foreach ($STATUS_VENDA_DESCRICAO as $key => $value) { ?>
						  <option value="<?php echo $key ?>" <?php if(isset($vg_ultimo_status) && $key == $vg_ultimo_status) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
						  <?php } ?>
						</select>
					  </td>
					  <td class="texto" align="center">&nbsp;</td>
				</tr>
				<tr bgcolor="F5F5FB">
					  <td align="center" class="texto"><div align="right"><b>C&oacute;digo do Usu&aacute;rio: </b></div></td>
					  <td align="center" class="texto"><div align="left"><strong>
							<input name="codigo_user" type="text" class="form" id="codigo_user" value="<?php echo $codigo_user?>" size="24" maxlength="7">
					  </strong></div></td>
					  <td align="center" class="texto" colspan="3"><b>Tipo: &nbsp;</b>
						<select name="uglt_id" id="uglt_id" class="combo_normal">
						  <option value="" <?php if(empty($uglt_id)) echo "selected" ?>>Todos</option>
						  <?php foreach ($USUARIO_GAMES_LOG_TIPOS_DESCRICAO as $key => $value) { ?>
						  <option value="<?php echo $key ?>" <?php if(isset($uglt_id) && $key == $uglt_id) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
						  <?php } ?>
						</select>
					  </td>
					  <td class="texto" align="center">&nbsp;</td>
				</tr>
				<tr bgcolor="F5F5FB">
					  <td height="21" align="center" class="texto">&nbsp;</td>
					  <td class="texto" align="center">&nbsp;</td>
					  <td colspan="3" align="center" class="texto">&nbsp;</td>
					  <td class="texto" align="center"><div align="right"><a href="/index.php"><img src="/images/voltar_menu.gif" width="107" height="15" border="0"></a></div></td>
				</tr>
				</table>
			  </form>
                      </tr>
                      
	</tr>
<?php
//if (!empty($codigo_user)) {
?>
		<tr align="center"><td>
			<center>
				  <div align="center">
				   <table class="table txt-preto fontsize-pp">
					 <tr <?php echo $fcolor?> class="texto" >
					   <td height="21" colspan="6" align="left" bgcolor="#EEEEEE" class='texto'>Listando de 
					   <?php echo ($inicial +1)?> a <?php echo ($max_reg)?> de <?php echo $total_table?></td>
					 </tr>
					 <tr class='texto'>
					   <td align="center" bgcolor="#CCCCCC"><strong>Data</strong></td>
					   <td align="center" bgcolor="#CCCCCC"><strong>Tipo</strong></td>
					   <td align="center" bgcolor="#CCCCCC" id="res_total2"><strong>Codigo Cliente</strong></td>
					   <td align="center" bgcolor="#CCCCCC"><strong>ID</strong></td>
					   <td align="center" bgcolor="#CCCCCC"><strong>Situação</strong></td>
					   <td align="center" bgcolor="#CCCCCC"><strong>Venda</strong></td>
					 </tr>
					 <?php	
		$i_row = 0;
		$total_entrada = 0;
		$total_saida = 0;
		
		while( $info = pg_fetch_array($res) ){
			
			$i_row++;

			///////////////////////////////////////////////////////////////////
			/////////////// ---- SETUP CORES DAS CELULAS -------///////////////
			//////////												///////////
			/////														///////
			///															    ///
			$bgcolor = (($i_row) % 2)?" bgcolor='#E0E0E0'":" bgcolor='#FAFAFE'";
			//																 //
			//////														///////
			/////////////											///////////
			//////////////// ----------- FIM SETUP ------------////////////////
			///////////////////////////////////////////////////////////////////
							
			$vg_id_view = $info['vg_id'];
			$data_view  = formata_data_to_19($info['data_inclusao']);	//formata_data_ts($info['data_inclusao'], 0, true, false);
			$id_view = $info['ug_id'];
			$tipo_view = $info['tipo'];
			$view = $info['id'];
			$uglt_id_view = $info['uglt_id'];
			?>
					 <tr <?php echo $bgcolor?>>
					   <td class="texto" align="center"><nobr><?php echo $data_view?></nobr></td>
					   <td class="texto" align="center"><nobr><?php echo $tipo_view?></nobr></td>
					   <td align="center" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
					   <?php echo $id_view?>
					   </a></td>
					   <td align="center" class="texto"><?php echo $view?></td>
					   <td align="center" class="texto"><?php if(isset($tipo_view) && $tipo_view == "Venda") echo $STATUS_VENDA_DESCRICAO[$uglt_id_view]; else echo $USUARIO_GAMES_LOG_TIPOS_DESCRICAO[$uglt_id_view];?></td>
					   <td align="center" class="texto"><?php if(!empty($vg_id_view)) { ?><a href="/pdv/vendas/com_venda_detalhe.php?venda_id=<?php echo $vg_id_view?>" target="_blank"><?php echo $vg_id_view?></a><?php } ?></td>
					 </tr>
		   <?php

		} // fim do while principal

		?>
				  </table>
		</div>
		</center></td>
		  </tr>
		<tr align="center"><td>
		<?php 
			
		paginacao_query($inicial, $total_table, $max, 50, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varse1);

		?>
			</td>
		</tr>
		</table>

		<div align="center">
		<br>
	<?php
	if(isset($total_table) && $total_table==0) {
	?>
	   <span class="style1">Nenhum registro foi encontrado   </span><br>
	<?php
	}//end if(isset($total_table) && $total_table==0)
?>
</div>
	
<div class='texto'>
<?php
echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit;
?>
</div>
<center>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>

<?php
function formata_data_to_19($d_string) {
	return (substr($d_string, 0,19));
}

?>