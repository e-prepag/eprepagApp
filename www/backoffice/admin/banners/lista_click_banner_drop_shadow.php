<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
error_reporting(E_ALL ^ E_NOTICE);
$time_start = getmicrotime();

//if(!isset() || !$ncamp)						$ncamp						= 'ug_nome_aux';
if(!isset($inicial) || !$inicial)					$inicial					= 0;
if(!isset($range) || !$range)						$range						= 1;
if(!isset($ordem) || !$ordem)						$ordem						= 0;
if(!isset($tf_v_data_inclusao_ini) || !$tf_v_data_inclusao_ini)    $tf_v_data_inclusao_ini     = date("d/m/Y");
if(!isset($tf_v_data_inclusao_fim) || !$tf_v_data_inclusao_fim)    $tf_v_data_inclusao_fim     = date("d/m/Y");

if($btPesquisar=="Pesquisar") {
	$inicial     = 0;
	$range       = 1;
	$total_table = 0;
}

$tipos_usuarios = array(
				'L' => "Usu&aacute;rios Lan House",
				'G' => "Usu&aacute;rios Gamers",
				);

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 50; 
$ordem		  = ($ordem == 1)?2:1;
$range_qtde   = $qtde_range_tela;

$varsel = "&btPesquisar=1";
$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim&bds_id_banner=$bds_id_banner&bds_tipo=$bds_tipo&ncamp=$ncamp";
?>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $server_url_ep; ?>/js/global.js"></script>
<!--<script type="text/javascript" src="js/jquery.ui.nestedSortable.js"></script>-->
<script>
    $(function(){

        var optDate = new Object();
            optDate.interval = 6;
            optDate.minDate = "01/01/2010";

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
    });
    
function validaFiltros()
{
    if (document.form1.bds_id_banner.value == "" && document.form1.bds_tipo.value == "")
    {
        alert("Favor selecionar o Banner Drop Shadow ou Tipo de Usuário.");
        document.form1.bds_id_banner.focus();
        return false;
    }
    return true;
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" onSubmit="return validaFiltros()">
<table class="table txt-preto fontsize-pp">
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="4">&nbsp;Data In&iacute;cio: &nbsp;
		  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="11" maxlength="10">
		  a &nbsp;Data Fim: &nbsp;
		  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="11" maxlength="10">
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="4"><nobr>&nbsp;Banner Drop Shadow: &nbsp;
		<select name="bds_id_banner" class="form" id="bds_id_banner" onChange="document.form1.bds_tipo.value = '';">
			<option value="" <?php  if($bds_id_banner == "") echo "selected" ?>>Selecione</option>
			<?php
				//colocar rotina para criação do combo de seleção de Banner Drop Shadow
				$sql = "select * from tb_banner_drop_shadow order by bds_texto";
				$res_quest = SQLexecuteQuery($sql);
				while ($res_quest_row = pg_fetch_array($res_quest)) { 
					?>
					<option value='<?php echo $res_quest_row['bds_id_banner']?>'<?php if ($bds_id_banner == $res_quest_row['bds_id_banner'] ) { echo " selected = 'selected' ";}?>><?php echo $res_quest_row['bds_texto'];?> (<?php if($res_quest_row['bds_ativo']) echo "Ativo"; else echo "Inativo";?>)</option>
			<?php 
				}//end while
			?>
		</select>
		&nbsp;</nobr>
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="4"><nobr>&nbsp;Tipo de Usu&aacute;rio: &nbsp;
		<select name="bds_tipo" id="bds_tipo" class="form" onChange="document.form1.bds_id_banner.value = '';">
			<option value="" <?php  if(empty($bds_tipo)) echo "selected" ?>>Selecione</option>
			<?php foreach ($tipos_usuarios as $key => $value) { ?>
			<option value="<?php echo $key ?>" <?php if($key == $bds_tipo) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
			<?php } ?>
		</select>
		&nbsp;</nobr>
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="4"><nobr>&nbsp;Ordenar por: &nbsp;
		<select name="ncamp" id="ncamp" class="form">
			<option value="" <?php if($ncamp == "") echo "selected" ?>>Selecione</option>
			<option value="ug_nome_aux" <?php if($ncamp == "ug_nome_aux") echo "selected" ?>>Nome do Usu&aacute;rio</option>
			<option value="ug_id" <?php if($ncamp == "ug_id") echo "selected" ?>>ID do Usu&aacute;rio</option>
		</select>
		&nbsp;&nbsp;N&atilde;o Exibir P&aacute;gina&ccedil;&atilde;o: <input name="quest_paginacao" type="checkbox" id="quest_paginacao" value="1" <?php if($quest_paginacao==1) echo "checked";?>/></nobr>
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
		<td class="texto" align="center" colspan="4">
			&nbsp;
		</td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="4"><input type="submit" name="btPesquisar" id="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
	  </td>
	</tr>
<?php
//echo "<pre>".print_r($_REQUEST,true)."</pre>";
if(!empty($btPesquisar))
{
	if(!empty($bds_id_banner)) {
		$sql = "select * from tb_banner_drop_shadow where bds_id_banner=".$bds_id_banner;
		$rs_dados_drop_shadow = SQLexecuteQuery($sql);
		$rs_dados_drop_shadow_row = pg_fetch_array($rs_dados_drop_shadow);
		$bds_tipo = strtoupper($rs_dados_drop_shadow_row['bds_tipo_usuario']);
	}
	
	//echo "[".$bds_tipo."]";
    $sql = "SELECT 
					bdsc.bds_id_banner,
					bdsc.ug_id,
					";
	if($bds_tipo == 'L') {
		$sql .= "		(CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)||' ('||ug.ug_tipo_cadastro||')' END) as ug_nome_aux,";
	}
	else {
		$sql .= "		ug.ug_nome as ug_nome_aux,";
	}
	$sql .= "		to_char(bdsc.bdsc_data_inclusao,'DD/MM/YYYY HH24:MI:SS') as bdsc_data_inclusao_aux,
					bds.bds_texto
			FROM tb_banner_drop_shadow_clicks bdsc
				 INNER JOIN tb_banner_drop_shadow bds ON (bdsc.bds_id_banner=bds.bds_id_banner)";
	if($bds_tipo == 'L') {
		$sql .= " INNER JOIN dist_usuarios_games ug ON (bdsc.ug_id=ug.ug_id)";
	}
	else {
		$sql .= " INNER JOIN usuarios_games ug ON (bdsc.ug_id=ug.ug_id)";
	}
	$sql_filters[] = "bdsc.bdsc_click=1"; 
	$sql_filters[] = "bdsc.bdsc_tipo_usuario = '".addslashes($bds_tipo)."'";
	if(strlen($bds_id_banner))
				$sql_filters[] = "bdsc.bds_id_banner = ".intval($bds_id_banner);
	if(strlen($tf_v_data_inclusao_ini))
				$sql_filters[] = "bdsc.bdsc_data_inclusao >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($tf_v_data_inclusao_fim))
				$sql_filters[] = "bdsc.bdsc_data_inclusao <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	if (count($sql_filters) > 0) {
		$sql_aux = implode(" and ", $sql_filters);
		$sql  .= " WHERE ".$sql_aux;
	}
	//echo $sql;
	$rs_respostas_total = SQLexecuteQuery($sql);
	$total_table = pg_num_rows($rs_respostas_total);
	
	//Ordem
	if(!isset($ncamp) || !$ncamp) $sql .= " order by ug_nome_aux";
	else $sql .= " order by ".$ncamp;
	if($ordem == 1){
		$sql .= " desc ";
		$img_seta = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
	} else {
		$sql .= " asc ";
		$img_seta = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
	}

	if($quest_paginacao!=1) {
		$sql .= " limit ".$max; 
		$sql .= " offset ".$inicial;
	}
	//echo($sql);

	$rs_respostas = SQLexecuteQuery($sql);
	$total_table_pag = pg_num_rows($rs_respostas_total);
	if($total_table_pag == 0) {
		echo "Nenhuma resposta encontrada.<br>\n";
	} else {		
		if($max + $inicial > $total_table_pag)
			$reg_ate = $total_table_pag;
		else
			$reg_ate = $max + $inicial;
	}
	
	if(!($rs_respostas) || (pg_num_rows($rs_respostas)==0)) {
		//echo "Erro ao consultar informa&ccedil;&otilde;es de respostas.<br>";
	}
	else {
		?>
		<tr> 
			<td colspan="2" class="texto"> 
			<?php
			if($quest_paginacao!=1) {
			?>
			  Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
			<?php
			}
			else echo "&nbsp;";
			?>
			<br>
			Total de Usu&aacute;rios que clicaram: <b><?php
			echo $total_table;	
			?></b>
			</td>
		</tr>
		<tr bgcolor="F0F0F0">
 			<td class="texto" align="center">
				<strong>
						<a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_nome_aux&inicial=".$inicial.$varsel ?>" class="link_branco" >
							<font class="texto">Contato</font>
						</a><?php if($ncamp == 'ug_nome_aux') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> 
				</strong>&nbsp;
			</td>
			<td class="texto" align="center">
				<strong>
					<a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>" class="link_branco" >
						<font class="texto">ID User</font>
					</a><?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> 
				</strong>&nbsp;
			</td>
			<td class="texto" align="center">
				<strong>
					<a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bds_texto&inicial=".$inicial.$varsel ?>" class="link_branco" >
						<font class="texto">Nome Banner</font>
					</a><?php if($ncamp == 'bds_texto') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> 
				</strong>&nbsp;
			</td>
			<td class="texto" align="center">
				<strong>
					<a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bdsc_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco" >
						<font class="texto">Data do Click</font>
					</a><?php if($ncamp == 'bdsc_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> 
				</strong>&nbsp;
			</td>
		</tr>
		<tr bgcolor='#000000'><td colspan='4' height='1'></td></tr>
		<?php
		while($rs_respostas_row = pg_fetch_array($rs_respostas)){ 
			$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
			$irows++;
		?>
		<tr<?php echo $bgcolor?> valign="top">
		  <td class="texto" align="left">&nbsp;<?php echo $rs_respostas_row['ug_nome_aux']?></td>
		  <td class="texto" align="center">&nbsp;<?php echo $rs_respostas_row['ug_id']?></td>
		  <td class="texto" align="center">&nbsp;<?php echo $rs_respostas_row['bds_texto']?></td>
		  <td class="texto" align="center">&nbsp;<?php echo $rs_respostas_row['bdsc_data_inclusao_aux']?></td>
		</tr>
		<?php	
		}
	}
}

if($quest_paginacao!=1) {
	paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); 
}
?>
</table>
</form>
</body>
</html>
