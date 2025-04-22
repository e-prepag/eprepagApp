<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

$time_start_stats = getmicrotime();
$sNomePaginaAux = substr($_SERVER['HTTP_REFERER'],strrpos($_SERVER['HTTP_REFERER'],'/')+1,(strlen($_SERVER['HTTP_REFERER'])-strrpos($_SERVER['HTTP_REFERER'],'/')));
$sNomePaginaAux     = isset($_POST['sNomePagina'])          ? $_POST['sNomePagina']       : $sNomePaginaAux;
    
//paginacao
$p = $_GET['p'];
if(!$p) $p = 1;
$registros = 50;
$registros_total = 0;
//rotinas de inicializacao se click em botao gerar arquivo

//Vericações e Update
$msg = "";

//Recupera as vendas
if($msg == ""){
        //inicializando por Dia marcado
        $make_group_by_day = 1;
        
	// Permite apenas um agrupamento
	if($make_group_by_user==1) {
		$make_group_by_ip=0;
		$make_group_by_day=0;
	}
	if($make_group_by_ip==1 && $make_group_by_day==1) {
		$make_group_by_day = 0;
	}
	if ($make_group_by_ip == 1){
		$sql  = "SELECT count(psah_ip_autor) as total,psah_ip_autor,psah_autor,psah_acao,to_char(MAX(psah_data),'DD/MM/YYYY HH24:MI:SS') as psah_data_aux ";
	} elseif ($make_group_by_day == 1){
		$sql  = "SELECT count(*) as total, to_char(psah_data,'YYYY/MM/DD') as psah_data_aux, '-' as psah_ip_autor, '-' as psah_autor, '--' as psah_acao   ";
	} elseif ($make_group_by_user == 1){
		$sql  = "SELECT count(psah_autor) as total,' - ' as psah_ip_autor,psah_autor,' - ' as psah_acao,' - ' as psah_data_aux ";
	} else {
		$sql  = "SELECT *,to_char(psah_data,'DD/MM/YYYY HH24:MI:SS') as psah_data_aux ";
	}
	$sql .=" from pins_store_apl_historico WHERE (psah_pin_id = '0' or pin_status = '0') "; 
	if(strlen($tf_v_data_inclusao_ini))
				$sql .= " and psah_data >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($tf_v_data_inclusao_fim))
				$sql .= " and psah_data <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	if ($make_group_by_ip == 1){
		$sql .= " GROUP BY psah_ip_autor,psah_autor,psah_acao";	
		$sql .= " ORDER BY total DESC, MAX(psah_data) DESC";	
	} elseif ($make_group_by_day == 1){
		$sql .= "GROUP BY psah_data_aux ";
		$sql .= "ORDER BY psah_data_aux DESC ";
	} elseif ($make_group_by_user == 1){
		$sql .= " GROUP BY psah_autor,psah_acao,psah_data_aux";	
		$sql .= " ORDER BY total DESC";	
	} else {
		$sql .= " ORDER BY psah_data DESC";	
	}
	$rs_total = SQLexecuteQuery($sql);
	if($rs_total) $registros_total = pg_num_rows($rs_total);
	$sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
//if(b_IsUsuarioReinaldo()) { 
//echo "(R) ".str_replace("\b", "\b<br>", $sql)."<br>";
//}
	$rs_pins = SQLexecuteQuery($sql);
	if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
}
?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
        optDate.interval = 1000;

    setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
});
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li class="active">ALERTA: Tentativa de uso de PINs INEXISTENTES:</li>
    </ol>
</div>  
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_store_menu.php";
?>
<form name="form1" method="post" action="pins_store_alerta.php">
	<input type="hidden" name="sNomePagina" id="sNomePagina" value="<?php echo $sNomePaginaAux; ?>">
	<table class="table txt-preto fontsize-pp bg-branco">
    <tr valign="top" align="center">
      <td align="center">
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table class="table txt-preto fontsize-pp bg-branco">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center">&nbsp;</td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de cadastro</b></td>
    	          <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;</td>
    	          <td class="texto" align="center"><nobr>&nbsp;
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
					  a 
					  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
				  </td>
				  <td class="texto" align="center">&nbsp;<input type="checkbox" id="make_group_by_ip" name="make_group_by_ip" value="1" 
						<?php
						if ($make_group_by_ip == 1){
							echo " checked";
						}								
						?> >&nbsp;&nbsp;Totalizar por IP</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;</td>
    	          <td class="texto" align="center"><nobr>&nbsp;</td>
				  <td class="texto" align="center">&nbsp;<input type="checkbox" id="make_group_by_day" name="make_group_by_day" value="1" 
						<?php
						if ($make_group_by_day == 1){
							echo " checked";
						}								
						?> >&nbsp;&nbsp;Totalizar por Dia</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;</td>
    	          <td class="texto" align="center"><nobr>&nbsp;</td>
				  <td class="texto" align="center">&nbsp;<input type="checkbox" id="make_group_by_user" name="make_group_by_user" value="1" 
						<?php
						if ($make_group_by_user == 1){
							echo " checked";
						}								
						?> >&nbsp;&nbsp;Totalizar por Usu&aacute;rio</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center" colspan="3"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
				  </td>
    	        </tr>
			</table>
			<table class="table txt-preto fontsize-pp bg-branco">
			<?php	
			if ($make_group_by_ip == 1){
			?>
				<tr bgcolor='#000000'><td colspan='5' height='1'></td></tr>
			<?php
			}
			else {
			?>
				<tr bgcolor='#000000'><td colspan='4' height='1'></td></tr>
			<?php
			}
			?>
			<tr bgcolor="F0F0F0">
			<?php
			if (($make_group_by_ip == 1) || ($make_group_by_day == 1) || ($make_group_by_user == 1)){
			?>
				<td class="texto" align="center"><b>N� Tentativas</b>&nbsp;</td>
			<?php
			}	
			?>
			  <td class="texto" align="center" width="15%"><b>Atrav&eacute;s do IP</b>&nbsp;</td>
			  <td class="texto" align="center" width="15%"><b>Usu&aacute;rio</b>&nbsp;</td>
			  <td class="texto" align="center" width="30%"><b><?php if ($make_group_by_day == 1) {?>Dia<?php } else { ?>Dia e Hora<?php } ?></b></td>
			<?php
			if (($make_group_by_day != 1) && ($make_group_by_user != 1)){
			?>
			  <td class="texto" align="center" width="40%"><b>Mensagem</b></td>
			<?php
			}	
			?>
			</tr>
		<?php	
			if (($make_group_by_ip == 1) || ($make_group_by_day == 1)){
			?>
			  <tr bgcolor='#000000'><td colspan='5' height='1'></td></tr>
			<?php
			}
			else {
			?>
			  <tr bgcolor='#000000'><td colspan='4' height='1'></td></tr>
			<?php
			}
			$i=0;
			$irows=0;
			if($rs_pins) {
				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
			?>
    	        <tr<?php echo $bgcolor?> valign="top">
				<?php
			if (($make_group_by_ip == 1) || ($make_group_by_day == 1) || ($make_group_by_user == 1)){
				?>
				  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['total']?></td>
    	        <?php
				}	
				?>
				  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['psah_ip_autor']?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['psah_autor']?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['psah_data_aux']?>&nbsp;</td>
    	        <?php
				if (($make_group_by_day != 1) && ($make_group_by_user != 1)){
				?>
				  <td class="texto" align="center">&nbsp;<?php echo $PINS_STORE_MSG_LOG[$rs_pins_row['psah_acao']]?>&nbsp;</td>
		        <?php
				}	
				?>
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
	<table class="table txt-preto fontsize-pp bg-branco">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='<?php echo $sNomePaginaAux;?>';" class="btn btn-sm btn-info">
      		<?php if($p < ($registros_total/$registros)){ ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
			<?php } ?></nobr>
      	</td>
    </tr>
	</table>
	<br>&nbsp;

	<table class="table txt-preto fontsize-pp bg-branco">
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>

	</form>
</div>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>