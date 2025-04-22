<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";

if(b_IsBKOUsuarioNewletter()) {
	include_once $raiz_do_projeto . "includes/gamer/main.php";
	//require_once $_SERVER['DOCUMENT_ROOT']."/connections/connect.php";
	require_once $raiz_do_projeto . "includes/functions.php";
    require_once $raiz_do_projeto . "class/classEmailAutomatico.php";

	$time_start = getmicrotime();
	
	if(!isset($ncamp) || !$ncamp)						$ncamp						= 'ee_data_inclusao';
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
	$max          = 3000; 
	$ordem		  = ($ordem == 1)?2:1;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&btPesquisar=1";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim&ug_email=$ug_email&ug_id=$ug_id&ee_tipo_usuario=$ee_tipo_usuario&ee_identificador=$ee_identificador";

	$EnvioEmailAutomatico = new EnvioEmailAutomatico();
	$vetor_identificador = $EnvioEmailAutomatico->getVetorIdentificacao();
	//echo "<pre>".print_r($vetor_identificador,true)."</pre>";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<!--<script type="text/javascript" src="js/jquery.ui.nestedSortable.js"></script>-->
<script>
    $(function(){

        var optDate = new Object();
            optDate.interval = 6;
            optDate.minDate = "01/01/2010";

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
    });
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<form name="form1" method="post" action="lista_email_automatico.php">
<table class="txt-preto fontsize-pp table">
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="5">
          Data Início:
		  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="11" maxlength="10">
		  a Data Fim: 
		  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="11" maxlength="10">
			Identificador de E-mail: 
			<select name="ee_identificador" id="ee_identificador" class="form">
				<option value="" <?php  if($ee_identificador == "") echo "selected" ?>>Selecione</option>
				<?php foreach ($vetor_identificador as $key => $value) { ?>
				<option value="<?php echo $value ?>" <?php if($value == $ee_identificador) echo "selected" ?>><?php echo $value ?></option>
				<?php } ?>
			</select>
		  
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="5">E-Mail: 
		<input name="ug_email" type="text" class="form" id="ug_email" value="<?php echo $ug_email ?>" size="50" maxlength="50">
		ID User:  
		<input name="ug_id" type="text" class="form" id="ug_id" value="<?php echo $ug_id ?>" size="15" maxlength="15">
		Tipo de Usu&aacute;rio: 
		<select name="ee_tipo_usuario" id="ee_tipo_usuario" class="form">
			<option value="" <?php  if($ee_tipo_usuario == "") echo "selected" ?>>Selecione</option>
			<?php foreach ($tipos_usuarios as $key => $value) { ?>
			<option value="<?php echo $key ?>" <?php if($key == $ee_tipo_usuario) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
			<?php } ?>
		</select>   
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="5"><input type="submit" name="btPesquisar" id="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
	  </td>
	</tr>
<?php
//echo "<pre>".print_r($_REQUEST,true)."</pre>";
if(!empty($btPesquisar))
{
    $sql = "SELECT 
					ug_email,
					ug_id,
					to_char(ee_data_inclusao,'DD/MM/YYYY HH24:MI:SS') as ee_data_inclusao_aux,
					ee_identificador,
					CASE WHEN ee_tipo_usuario='G' THEN 'GAMER' WHEN ee_tipo_usuario='L' THEN 'LANHOUSE' ELSE 'SEM TIPO' END as ee_tipo_usuario_aux,
					ee_tipo_usuario
			FROM envio_email";
	if(strlen($ug_email))
				$sql_filters[] = "ug_email like '%".addslashes($ug_email)."%'";
	if(strlen($ug_id))
				$sql_filters[] = "ug_id = ".intval($ug_id)."";
	if(strlen($ee_tipo_usuario))
				$sql_filters[] = "ee_tipo_usuario = '".addslashes($ee_tipo_usuario)."'";
	if(strlen($ee_identificador))
				$sql_filters[] = "ee_identificador = '".addslashes($ee_identificador)."'";
	if(strlen($tf_v_data_inclusao_ini))
				$sql_filters[] = "ee_data_inclusao >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($tf_v_data_inclusao_fim))
				$sql_filters[] = "ee_data_inclusao <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	if (count($sql_filters) > 0) {
		$sql_aux = implode(" and ", $sql_filters);
		$sql  .= " WHERE ".$sql_aux;
	}

	$rs_email_automatico_total = SQLexecuteQuery($sql);
	$total_table = pg_num_rows($rs_email_automatico_total);
	if($total_table == 0) {
		echo "Nenhum e-mail encontrado.<br>\n";
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

	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;
	
if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql)."<br>"; 
}

	$rs_email_automatico = SQLexecuteQuery($sql);
	if(!isset($rs_email_automatico) || !($rs_email_automatico) || (pg_num_rows($rs_email_automatico)==0)) {
		echo "Erro ao consultar informa&ccedil;&otilde;es de emails automático.<br>";
	}
	else {
		?>
		<tr> 
			<td colspan="2" class="texto"> 
			  Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
			</td>
		</tr>
		<tr bgcolor="F0F0F0">
 			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">ID</font></a><?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong></td>
 			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_email&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">E-Mail</font></a><?php if($ncamp == 'ug_email') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong></td>
			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ee_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Data de Cadastro</font></a><?php if($ncamp == 'ee_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong></td>
 			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ee_identificador&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Identificador</font></a><?php if($ncamp == 'ee_identificador') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong></td>
 			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ee_tipo_usuario&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Tipo User</font></a><?php if($ncamp == 'ee_tipo_usuario') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong></td>
		</tr>
		<tr bgcolor='#000000'><td colspan='5' height='1'></td></tr>
		<?php
		while($rs_email_automatico_row = pg_fetch_array($rs_email_automatico)){ 
			$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
			$irows++;
		?>
		<tr<?php echo $bgcolor?> valign="top">
		  <td class="texto" align="center">
		  <?php
		  if($rs_email_automatico_row['ug_id']==0) {
			  echo $rs_email_automatico_row['ug_id'];
		  }//end if($rs_email_automatico_row['ug_id']==0)
		  else {
			  if($rs_email_automatico_row['ee_tipo_usuario'] == 'G') {
			  ?><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_email_automatico_row['ug_id'];?>"><?php echo $rs_email_automatico_row['ug_id'];?></a>
			  <?php
			  }
			  elseif($rs_email_automatico_row['ee_tipo_usuario'] == 'L') {
			  ?><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_email_automatico_row['ug_id'];?>"><?php echo $rs_email_automatico_row['ug_id'];?></a>
			  <?php
			  }
			  else {
				  echo $rs_email_automatico_row['ug_id'];
			  }
		  }//end else if($rs_email_automatico_row['ug_id']==0)
		  ?>
		  </td>
		  <td class="texto" align="center"><?php echo $rs_email_automatico_row['ug_email']?></td>
		  <td class="texto" align="center"><?php echo $rs_email_automatico_row['ee_data_inclusao_aux']?></td>
		  <td class="texto" align="center"><?php echo $rs_email_automatico_row['ee_identificador']?></td>
		  <td class="texto" align="center"><?php echo $rs_email_automatico_row['ee_tipo_usuario_aux']?></td>
		</tr>
		<?php	
		}
	}
}
paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); 
?>
</table>
</form>
</body>
</html>
<?php
}
?>
