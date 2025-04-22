<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
$time_start = getmicrotime();

if(!isset($ncamp) || !$ncamp)						$ncamp						= 'qlpr_descricao';
if(!isset($inicial) || !$inicial)					$inicial					= 0;
if(!isset($range) || !$range)						$range						= 1;
if(!isset($ordem) || !$ordem)						$ordem						= 0;
if(!isset($tf_v_data_inclusao_ini) || !$tf_v_data_inclusao_ini)    $tf_v_data_inclusao_ini     = date("d/m/Y");
if(!isset($tf_v_data_inclusao_fim) || !$tf_v_data_inclusao_fim)    $tf_v_data_inclusao_fim     = date("d/m/Y");

if(isset($btPesquisar) && $btPesquisar=="Pesquisar") {
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
if(!isset($ql_id_questionario)) $ql_id_questionario = null;
if(!isset($quest_paginacao)) $quest_paginacao = null;
if(!isset($total_table)) $total_table = null;
$varsel = "&btPesquisar=1";
$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim&ql_id_questionario=$ql_id_questionario&quest_paginacao=$quest_paginacao";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
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
		if (document.form1.ql_id_questionario.value == "" && document.form1.quest_tipo_usuario.value == "")
		{
			alert("Favor selecionar o Questionário ou Tipo de Usuário.");
			document.form1.ql_id_questionario.focus();
			return false;
		}
		return true;
	}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<form name="form1" method="post" action="lista_respostas_questionarios.php" onSubmit="return validaFiltros()">
    <table class="table txt-preto fontsize-pp">
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="4"><nobr>&nbsp;Data In&iacute;cio: &nbsp;
		  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="11" maxlength="10">
		  a &nbsp;Data Fim: &nbsp;
		  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="11" maxlength="10">
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="4"><nobr>&nbsp;Question&aacute;rio: &nbsp;
		<select name="ql_id_questionario" class="form" id="ql_id_questionario" onChange="document.form1.quest_tipo_usuario.value = '';">
			<option value="" <?php  if($ql_id_questionario == "") echo "selected" ?>>Selecione</option>
			<?php
				//colocar rotina para criação do combo de seleção de questionário
				$sql = "select * from tb_questionarios order by ql_texto";
				$res_quest = SQLexecuteQuery($sql);
				while ($res_quest_row = pg_fetch_array($res_quest)) { 
					?>
					<option value='<?php echo $res_quest_row['ql_id_questionario']?>'<?php if ($ql_id_questionario == $res_quest_row['ql_id_questionario'] ) { echo " selected = 'selected' ";}?>><?php echo $res_quest_row['ql_texto'];?> (<?php if($res_quest_row['ql_ativo']) echo "Ativo"; else echo "Inativo";?>)</option>
			<?php 
				}//end while
			?>
		</select>
		&nbsp;</nobr>
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="4"><nobr>&nbsp;Tipo de Usu&aacute;rio: &nbsp;
		<select name="quest_tipo_usuario" id="quest_tipo_usuario" class="form" onChange="document.form1.ql_id_questionario.value = '';">
			<option value="" <?php if(!isset($quest_tipo_usuario)) $quest_tipo_usuario = null;
            if($quest_tipo_usuario == "") echo "selected" ?>>Selecione</option>
			<?php foreach ($tipos_usuarios as $key => $value) { ?>
			<option value="<?php echo $key ?>" <?php if($key == $quest_tipo_usuario) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
			<?php } ?>
		</select>
		&nbsp;</nobr>
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="4"><nobr>&nbsp;Ordenar por: &nbsp;
		<select name="ncamp" id="ncamp" class="form">
			<option value="" <?php if($ncamp == "") echo "selected" ?>>Selecione</option>
			<option value="qlpr_descricao" <?php if($ncamp == "qlpr_descricao") echo "selected" ?>>Descri&ccedil;&atilde;o da Resposta</option>
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
	if(!empty($ql_id_questionario)) {
		$sql = "select * from tb_questionarios where ql_id_questionario=".$ql_id_questionario;
		$rs_dados_questionario = SQLexecuteQuery($sql);
		$rs_dados_questionario_row = pg_fetch_array($rs_dados_questionario);
		$ql_tipo_usuario = $rs_dados_questionario_row['ql_tipo_usuario'];
	}
	else {
		$ql_tipo_usuario = $quest_tipo_usuario;
	}
	//echo "[".$ql_tipo_usuario."]";
    $sql = "SELECT 
					qp.ql_id_questionario,
					qru.ug_id,
                                        qp.qlp_texto,";
	if($ql_tipo_usuario == 'L') {
		$sql .= "		(CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)||' ('||ug.ug_tipo_cadastro||')' END) as ug_nome,";
	}
	else {
		$sql .= "		ug.ug_nome as ug_nome,";
	}
	$sql .= "		qpr.qlpr_descricao,
					to_char(qru.qlpru_data_inclusao,'DD/MM/YYYY HH24:MI:SS') as qlpru_data_inclusao_aux
			FROM tb_questionarios_respostas_usuarios qru
				INNER JOIN tb_questionarios_perguntas_respostas qpr ON (qru.qlpr_id=qpr.qlpr_id)";
	if($ql_tipo_usuario == 'L') {
		$sql .= " INNER JOIN dist_usuarios_games ug ON (qru.ug_id=ug.ug_id)";
	}
	else {
		$sql .= " INNER JOIN usuarios_games ug ON (qru.ug_id=ug.ug_id)";
	}
	$sql .= "	INNER JOIN tb_questionarios_perguntas qp ON (qpr.qlp_id=qp.qlp_id)
				INNER JOIN tb_questionarios q ON (q.ql_id_questionario= qp.ql_id_questionario)";
	$sql_filters[] = "q.ql_tipo_usuario = '".addslashes($ql_tipo_usuario)."'";
	if(strlen($ql_id_questionario))
				$sql_filters[] = "qp.ql_id_questionario = ".intval($ql_id_questionario);
	if(strlen($tf_v_data_inclusao_ini))
				$sql_filters[] = "qru.qlpru_data_inclusao >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($tf_v_data_inclusao_fim))
				$sql_filters[] = "qru.qlpru_data_inclusao <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	if (count($sql_filters) > 0) {
		$sql_aux = implode(" and ", $sql_filters);
		$sql  .= " WHERE ".$sql_aux;
	}
	//echo $sql;
	$rs_respostas_total = SQLexecuteQuery($sql);
	$total_table = pg_num_rows($rs_respostas_total);
	if($total_table == 0) {
		echo "Nenhuma resposta encontrada.<br>".PHP_EOL;
	} else {		
		if($max + $inicial > $total_table)
			$reg_ate = $total_table;
		else
			$reg_ate = $max + $inicial;
	}

	//Ordem
	$sql .= " order by ".$ncamp;
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
	if(!isset($rs_respostas) || !($rs_respostas) || (pg_num_rows($rs_respostas)==0)) {
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
			Total de Usu&aacute;rios com respostas: <b><?php
			$sql = "select count(distinct(qru.ug_id)) as total from tb_questionarios_respostas_usuarios qru
				INNER JOIN tb_questionarios_perguntas_respostas qpr ON (qru.qlpr_id=qpr.qlpr_id)";
			if($ql_tipo_usuario == 'L') {
				$sql .= " INNER JOIN dist_usuarios_games ug ON (qru.ug_id=ug.ug_id)";
			}
			else {
				$sql .= " INNER JOIN usuarios_games ug ON (qru.ug_id=ug.ug_id)";
			}
			$sql .= "	INNER JOIN tb_questionarios_perguntas qp ON (qpr.qlp_id=qp.qlp_id)";
                        if(!empty($ql_id_questionario)) {
                            $sql .= "	where qp.ql_id_questionario=".$ql_id_questionario;
                        }
			//echo $sql;
			$rs_total_usuario = SQLexecuteQuery($sql);
			$rs_total_usuario_row = pg_fetch_array($rs_total_usuario);
			echo $rs_total_usuario_row['total'];	
			?></b>
			</td>
		</tr>
		<tr bgcolor="F0F0F0">
 			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_nome&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Contato</font></a><?php if($ncamp == 'ug_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong>&nbsp;</td>
			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">ID User</font></a><?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong>&nbsp;</td>
			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=qlp_texto&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Resposta</font></a><?php if($ncamp == 'qlp_texto') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong>&nbsp;</td>
			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=qlpr_descricao&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Resposta</font></a><?php if($ncamp == 'qlpr_descricao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong>&nbsp;</td>
			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=qlpru_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Data da Resposta</font></a><?php if($ncamp == 'qlpru_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong>&nbsp;</td>
		</tr>
		<tr bgcolor='#000000'><td colspan='5' height='1'></td></tr>
		<?php
		while($rs_respostas_row = pg_fetch_array($rs_respostas)){ 
//			$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
//			$irows++;
		?>
        <tr class="trListagem" valign="top">
		  <td class="texto" align="left">&nbsp;<?php echo $rs_respostas_row['ug_nome']?></td>
		  <td class="texto" align="center">&nbsp;<?php echo $rs_respostas_row['ug_id']?></td>
		  <td class="texto" align="left">&nbsp;<?php echo $rs_respostas_row['qlp_texto']?></td>
		  <td class="texto" align="center">&nbsp;<?php echo $rs_respostas_row['qlpr_descricao']?></td>
		  <td class="texto" align="center">&nbsp;<?php echo $rs_respostas_row['qlpru_data_inclusao_aux']?></td>
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
