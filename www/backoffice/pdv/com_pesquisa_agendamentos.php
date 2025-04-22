<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

	$time_start = getmicrotime();
	
	if(!$ncamp)    $ncamp       = 'ae_id';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 1;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
		if($BtnSearch=="Buscar") {
			$inicial     = 0;
			$range       = 1;
			$total_table = 0;
		}

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = $qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&BtnSearch=1&tf_codigo=$tf_codigo&tf_status=$tf_status";
	$varsel .= "&tf_data_inclusao_ini=$tf_data_inclusao_ini&tf_data_inclusao_fim=$tf_data_inclusao_fim";
	$varsel .= "&tf_data_execucao_inicio_ini=$tf_data_execucao_inicio_ini&tf_data_execucao_inicio_fim=$tf_data_execucao_inicio_fim";
	$varsel .= "&tf_tipo=$tf_tipo&tf_v_codigo=$tf_v_codigo";

	$msg = "";

	//Processa acoes
	//----------------------------------------------------------------------------------------------------------
	if($msg == ""){

		if($acao){

			//excluir imagem
			if($acao == "as"){
				$sql = "update tb_dist_agendamento_execucao set ae_status = $status
						where ae_id = " . $ae_id;
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msgAcao = "Erro ao atualizar agendamento.\n";
			}

			if($msgAcao == ""){
				header("Location: com_pesquisa_agendamentos.php?$varsel");
			}
		}
		
	}


	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//codigo agendamento
		if($msg == "")
			if($tf_codigo){
				if(!is_numeric($tf_codigo))
					$msg = "Código do agendamento deve ser numérico.\n";
			}
		//Data inclusao
		if($msg == "")
			if($tf_data_inclusao_ini || $tf_data_inclusao_fim){
				if(verifica_data($tf_data_inclusao_ini) == 0)	$msg = "A data inicial de inclusão é inválida.\n";
				if(verifica_data($tf_data_inclusao_fim) == 0)	$msg = "A data final de inclusão é inválida.\n";
			}
		//Data execucao
		if($msg == "")
			if($tf_data_execucao_inicio_ini || $tf_data_execucao_inicio_fim){
				if(verifica_data($tf_data_execucao_inicio_ini) == 0)	$msg = "A data inicial de execução é inválida.\n";
				if(verifica_data($tf_data_execucao_inicio_fim) == 0)	$msg = "A data final de execução é inválida.\n";
			}
		//codigo venda
		if($msg == "")
			if($tf_v_codigo){
				if(!is_numeric($tf_v_codigo))
					$msg = "Código da venda deve ser numérico.\n";
			}
	
		//Busca agendamentos
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){
			$sql  = "select *
					 from tb_dist_agendamento_execucao ae 
 					 where 1=1 ";
			if($tf_codigo) 				$sql .= " and ae.ae_id = ".$tf_codigo." ";
			if($tf_status) 				$sql .= " and ae.ae_status = ".$tf_status." ";
			if($tf_data_inclusao_ini && $tf_data_inclusao_fim) 				$sql .= " and ae.ae_data_inclusao between '".formata_data($tf_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_data_inclusao_fim,1)." 23:59:59'";
			if($tf_data_execucao_inicio_ini && $tf_data_execucao_inicio_fim)$sql .= " and ae.ae_data_execucao_inicio between '".formata_data($tf_data_execucao_inicio_ini,1)." 00:00:00' and '".formata_data($tf_data_execucao_inicio_fim,1)." 23:59:59'";
			if($tf_tipo) 				$sql .= " and ae.ae_tipo = ".$tf_tipo." ";
			if($tf_v_codigo) 			$sql .= " and ae.ae_vg_id = ".$tf_v_codigo." ";

			$rs_agendamentos = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_agendamentos);

			//Ordem
			$sql .= " order by ".$ncamp;
			if($ordem == 1){
				$sql .= " desc ";
				$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
			} else {
				$sql .= " asc ";
				$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
			}
		
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;
//echo $sql;

			if($total_table == 0) {
				$msg = "Nenhum agendamento encontrado.\n";
			} else {		
				$rs_agendamentos = SQLexecuteQuery($sql);
				
				if($max + $inicial > $total_table) $reg_ate = $total_table;
				else $reg_ate = $max + $inicial;
			}
				
		}
	}
	
	$msg = $msgAcao . $msg;
	
ob_end_flush();
?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_data_inclusao_ini','tf_data_inclusao_fim',optDate);
        setDateInterval('tf_data_execucao_inicio_ini','tf_data_execucao_inicio_fim',optDate);
        
    });
    
    function GP_popupAlertMsg(msg) { //v1.0
    document.MM_returnValue = alert(msg);
  }
  function GP_popupConfirmMsg(msg) { //v1.0
    document.MM_returnValue = confirm(msg);
  }
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
		<li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
		<li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
		<li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
	</ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td>
        <form name="form1" method="post" action="com_pesquisa_agendamentos.php">
        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="6" bgcolor="#ECE9D8" class="texto">Agendamento</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150" class="texto">Código do Agendamento</font></td>
            <td>
              	<input name="tf_codigo" type="text" class="form2" value="<?php echo $tf_codigo ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Status</font></td>
			<td>
				<select name="tf_status" class="form2">
					<option value="" <?php if($tf_status == "") echo "selected" ?>>Todos</option>
					<option value="1" <?php if ($tf_status == "1") echo "selected";?>>Agendado</option>
					<option value="2" <?php if ($tf_status == "2") echo "selected";?>>Executado</option>
					<option value="3" <?php if ($tf_status == "3") echo "selected";?>>Cancelado</option>
					<option value="4" <?php if ($tf_status == "4") echo "selected";?>>Em Execução</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data Inclusão</font></td>
            <td class="texto">
              <input name="tf_data_inclusao_ini" type="text" class="form" id="tf_data_inclusao_ini" value="<?php echo $tf_data_inclusao_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_data_inclusao_fim" type="text" class="form" id="tf_data_inclusao_fim" value="<?php echo $tf_data_inclusao_fim ?>" size="9" maxlength="10">
			</td>
            <td class="texto">Data Execução</font></td>
            <td class="texto">
              <input name="tf_data_execucao_inicio_ini" type="text" class="form" id="tf_data_execucao_inicio_ini" value="<?php echo $tf_data_execucao_inicio_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_data_execucao_inicio_fim" type="text" class="form" id="tf_data_execucao_inicio_fim" value="<?php echo $tf_data_execucao_inicio_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Tipo</font></td>
            <td colspan="3">
				<select name="tf_tipo" class="form2">
					<option value="" <?php if($tf_tipo == "") echo "selected" ?>>Todos</option>
					<option value="1" <?php if ($tf_tipo == "1") echo "selected";?>>Distribuidor - Processamento e Envio de Email</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Código da Venda</font></td>
            <td>
              	<input name="tf_v_codigo" type="text" class="form2" value="<?php echo $tf_v_codigo ?>" size="7" maxlength="7">
			</td>
            <td class="texto" colspan="2">&nbsp;</td>
          </tr>
		</table>
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>
        </td>
  </tr>
</table>
		<?php if($total_table > 0) { ?>
        <table class="table txt-preto">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
                      <table width="100%" class="table fontsize-p">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr bgcolor="#ECE9D8" class="texto"> 
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ae_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.</a> 
                          <?php if($ncamp == 'ae_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ae_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data<br>Inclusão</font></a>
                          <?php if($ncamp == 'ae_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ae_data_execucao_iniciosao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Execução<br>Inicio</font></a>
                          <?php if($ncamp == 'ae_data_execucao_inicio') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ae_data_execucao_fim&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Execução<br>Fim</font></a>
                          <?php if($ncamp == 'ae_data_execucao_fim') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ae_status&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Status</font></a>
                          <?php if($ncamp == 'ae_status') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><font class="texto">Mensagem</font></strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ae_tipo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Tipo</font></a>
                          <?php if($ncamp == 'ae_tipo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ae_vg_id&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Cód. Venda</font></a>
                          <?php if($ncamp == 'ae_vg_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
					  	<td align="center"><b>Ação</b></td>
                      </tr>
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						while($rs_agendamentos_row = pg_fetch_array($rs_agendamentos)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;

							$status = $rs_agendamentos_row['ae_status'];
							if($status == 1) $status_aux = "Agendado";
							elseif($status == 2) $status_aux = "Processado";
							elseif($status == 3) $status_aux = "Cancelado";
							elseif($status == 4) $status_aux = "Em execução";
							else $status_aux = $status;

							$tipo = $rs_agendamentos_row['ae_tipo'];
							if($tipo == 1) $tipo_aux = "Distribuidor - Processamento e Envio de Email";
							else $tipo_aux = $tipo;
							
					?>
                      <tr bgcolor="<?php echo $cor1 ?>" class="texto"> 
                        <td align="center"><?php echo $rs_agendamentos_row['ae_id'] ?></td>
                        <td align="center"><?php if($rs_agendamentos_row['ae_data_inclusao']) echo formata_data_ts($rs_agendamentos_row['ae_data_inclusao'],0, true,true) ?></td>
                        <td align="center"><?php if($rs_agendamentos_row['ae_data_execucao_inicio']) echo formata_data_ts($rs_agendamentos_row['ae_data_execucao_inicio'],0, true,true) ?></td>
                        <td align="center"><?php if($rs_agendamentos_row['ae_data_execucao_fim']) echo formata_data_ts($rs_agendamentos_row['ae_data_execucao_fim'],0, true,true) ?></td>
                        <td align="center"><?php echo $status_aux ?></td>
                        <td nowrap><?php echo str_replace("\n", "<br>", $rs_agendamentos_row['ae_mensagem']) ?></td>
                        <td nowrap><?php echo $tipo_aux ?></td>
                        <td class="texto" align="center"><a style="text-decoration:none" href="/pdv/vendas/com_venda_detalhe.php?venda_id=<?php echo $rs_agendamentos_row['ae_vg_id'] ?>"><?php echo $rs_agendamentos_row['ae_vg_id'] ?></a></td>
                        <?php if($status == 1){ ?>
						  	<td align="center"><a style="text-decoration:none" href="#" onClick="if(confirm('Deseja cancelar este agendamento?')) window.location='com_pesquisa_agendamentos.php?acao=as&status=3&ae_id=<?php echo $rs_agendamentos_row['ae_id'] ?><?php echo $varsel ?>';return false;">Cancelar</a></td>
					  	<?php } elseif($status == 3){?>
						  	<td align="center"><a style="text-decoration:none" href="#" onClick="if(confirm('Deseja ativar este agendamento?')) window.location='com_pesquisa_agendamentos.php?acao=as&status=1&ae_id=<?php echo $rs_agendamentos_row['ae_id'] ?><?php echo $varsel ?>';return false;">Ativar</a></td>
					  	<?php } else {?>
						  	<td align="center">&nbsp;</td>
					  	<?php }?>
                      </tr>
					<?php 	}	?>
					<?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
                    </table>
				  </td>
                </tr>
              </table>
          <?php  }  ?>
    
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
