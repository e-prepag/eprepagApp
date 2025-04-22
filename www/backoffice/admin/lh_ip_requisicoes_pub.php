<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
require_once ($raiz_do_projeto."includes/inc_functions.php");
require_once ($raiz_do_projeto."class/classDistIntegracaoIP.php");
$opr_codigo = isset($_POST['opr_codigo'])   ? $_POST['opr_codigo']		: null;
$pin_codigo	= isset($_POST['pin_codigo'])	? $_POST['pin_codigo']		: null;

$time_start_stats = getmicrotime();
//paginacao
if(isset($_GET['p'])){
    $p = $_GET['p'];
}else{
    $p = 1;
}
$registros = 50;
$registros_total = 0;
//rotinas de inicializacao se click em botao gerar arquivo

//Vericações e Update
$msg = "";

//Recupera as vendas
if($msg == ""){
	$sql_filters = array();
	$sql  = "SELECT *,to_char(dilp_data,'DD/MM/YYYY HH24:MI:SS') as dilp_data_aux from dist_ip_log_publisher "; 
	if(isset($tf_v_data_inclusao_ini) && strlen($tf_v_data_inclusao_ini))
				$sql_filters[] = "dilp_data >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(isset($tf_v_data_inclusao_fim) && strlen($tf_v_data_inclusao_fim))
				$sql_filters[] = "dilp_data <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	if(!empty($opr_codigo)||$opr_codigo==='0')
				$sql_filters[] = "opr_codigo = ".addslashes($opr_codigo);
	if (count($sql_filters) > 0) {
		$sql_aux = implode(" and ", $sql_filters);
		$sql  .= "WHERE ".$sql_aux;
	}
//	echo $sql;
	$rs_total = SQLexecuteQuery($sql);
	if($rs_total) $registros_total = pg_num_rows($rs_total);
	$sql .= " ORDER BY dilp_data DESC";	
	$sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
//echo $sql ."<br>\n";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log || pg_num_rows($rs_log) == 0) $msg = "Nenhum pin encontrado.\n";
}
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
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro">
	<table class="table txt-preto fontsize-pp">
	<tr valign="top" align="center">
      <td align="center">
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table class="table txt-preto fontsize-pp">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Operadora</b></td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de cadastro</b></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>
				  <select name="opr_codigo" id="opr_codigo" class="combo_normal">
					<option value=''<?php if(!$opr_codigo) echo " selected"?>>Selecione a operadora</option>
			        <?php 
					$sql = "select distinct(dilp.opr_codigo), CASE WHEN opr_nome is null THEN 'Nao Cadastrado ID: '||dilp.opr_codigo ELSE opr_nome END as opr_nome 
							from dist_ip_log_publisher dilp
								LEFT OUTER JOIN operadoras o ON (dilp.opr_codigo=o.opr_codigo) ";
					$rs_operadoras = SQLexecuteQuery($sql);
	
					while($rs_operadoras_row = pg_fetch_array($rs_operadoras)) { ?>
				    <option value=<?php echo "\"".$rs_operadoras_row['opr_codigo'].(($opr_codigo==$rs_operadoras_row['opr_codigo'])?"\" selected":"\""); ?>><?php echo $rs_operadoras_row['opr_nome']; ?></option>
					<?php } ?>
					</select>
				  </td>
    	          <td class="texto" align="center"><nobr>&nbsp;
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" size="11" maxlength="10" value="<?php if(isset($tf_v_data_inclusao_ini)) echo $tf_v_data_inclusao_ini; ?>">
					  a 
					  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" size="11" maxlength="10" value="<?php if(isset($tf_v_data_inclusao_ini)) echo $tf_v_data_inclusao_fim; ?>">
					  &nbsp;</nobr>
				  </td>
				</tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center" colspan="2"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
				  </td>
    	        </tr>
			</table>
			<table class="table txt-preto fontsize-pp">
			<tr bgcolor="F0F0F0">
 			  <td class="texto" align="center"><b>C&oacute;digo Operadora</b>&nbsp;</td>
			  <td class="texto" align="center"><b>IP Publisher</b>&nbsp;</td>
			  <td class="texto" align="center"><b>Dia e Hora</b>&nbsp;</td>
			  <td class="texto" align="center"><b>IP Requisitado</b></td>
			  <td class="texto" align="center"><b>ID da LAN</b></td>
			  <td class="texto" align="center"><b>Retorno Interno</b>&nbsp;</td>
			  <td class="texto" align="center"><b>Email</b>&nbsp;</td>
			  <td class="texto" align="center"><b>Jogo</b>&nbsp;</td>
			  <td class="texto" align="center"><b>Promo&ccedil;&atilde;o</b>&nbsp;</td>
			</tr>
			<tr bgcolor='#000000'><td colspan='9' height='1'></td></tr>
    	<?php	

			$i=0;
			$irows=0;
			if($rs_log) {
				$verificador = new DistIntegracaoIP(null,null,null,null,null);
				while($rs_log_row = pg_fetch_array($rs_log)){ 
					$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
 			?>
    	        <tr<?php echo $bgcolor?> valign="top">
				  <td class="texto" align="center">&nbsp;<?php echo $rs_log_row['opr_codigo']?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_log_row['dilp_ip_publisher']?></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_log_row['dilp_data_aux']?></nobr></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_log_row['dilp_ip_verificado']?>&nbsp;</td>
    	          <td class="texto" align="center">&nbsp;<?php echo (($rs_log_row['ug_id']>0 && $rs_log_row['ug_id']!=7909) ? "<a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=" . $rs_log_row['ug_id']."' target='_blank'>" : "").$rs_log_row['ug_id'].(($rs_log_row['ug_id']>0 && $rs_log_row['ug_id']!=7909) ? "</a>" : "");?>&nbsp;</td>
				  <td class="texto" align="center"><nobr>&nbsp;<?php echo $verificador->legenda_resposta($rs_log_row['dilp_codretepp_interno']);?>&nbsp;</nobr></td>
				  <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_log_row['dilp_email']?></nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_log_row['dilp_jogo']?></nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_log_row['dilp_promocao']?></nobr></td>
    	        </tr>
    	<?php	
				}
				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas tentativas para os valores escolhidos</font></td>
					</tr>
			<?php
				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas tentativas para os valores escolhidos</font></td>
    	        </tr>
		<?php
			}
		?>
			</table>
      </td>
    </tr>
	</table>

	<br>&nbsp;
	<table class="table txt-preto fontsize-pp">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo (isset($varsel)) ? $varsel : "";?>';" class="btn btn-sm btn-info">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='<?php echo $sNomePaginaAux;?>';" class="btn btn-sm btn-info">
      		<?php if($p < ($registros_total/$registros)){ ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo (isset($varsel)) ? $varsel : "";?>';" class="btn btn-sm btn-info">
			<?php } ?></nobr>
      	</td>
    </tr>
	</table>
	<br>&nbsp;

	<table class="table txt-preto fontsize-pp">
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>

	</form>
	</center>

</body>
</html>
<?php
//phpinfo();
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>