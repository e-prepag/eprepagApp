<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/pdv/corte_classPrincipal.php"; 
require_once "/www/includes/bourls.php";

	if(!$ncamp) $ncamp = 'ug_id';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 0;
//	if(!$BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
		$acao        = "";
	}
	
	//Validacao
	//------------------------------------------------------------------------------------------------------------------
	$msg = "";

	//Processa Acoes
	if($msg == ""){

		if($acao){
			//Pesquisa
			if($msg == ""){
				if(!$usuario_id || !is_numeric($usuario_id)) $msg = "Código do usuário inválido.\n";
			}
		
			//Corte do periodo
			if($msg == ""){
				if($acao && $acao == "cortar"){
					$ret = geraCorte($usuario_id, $data_inicial, $data_final);
					if($ret == "") $msgUsuario = "Corte: Corte efetuado com sucesso.\n";
					else $msgUsuario = "Corte: " . $ret;
				}
			}
		}
	}
	$msg = $msgUsuario . $msg;

	$last_sunday = date("Y-m-d 00:00:00", strtotime("last Sunday"));
	$last_monday = date("Y-m-d 23:59:59", strtotime("last Monday", strtotime($last_sunday)));
	if(!$data_final) $data_final = date("d/m/Y", strtotime($last_sunday));
	if(!$data_inicial) $data_inicial = date("d/m/Y", strtotime($last_monday));
//	if(!$data_inicial) $data_inicial=date("d/m/Y",strtotime("now last monday"));
//	if(!$data_final) $data_final=date("d/m/Y",strtotime("now last sunday"));

	$data_inicial_dia_semana = date('w', strtotime(substr($data_inicial, 6, 4) . "-" . substr($data_inicial, 3, 2) . "-" . substr($data_inicial, 0, 2)));


	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 200;	//$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;
if($BtnSearch){

	$sqlFiltro = "";
	if($tf_ug_id) 		$sqlFiltro .= " and vg.ug_id = ".$tf_ug_id." ";
	if($tf_nome) 		$sqlFiltro .= " and (ug.ug_nome_fantasia LIKE '%".strtoupper($tf_nome)."%' or ug.ug_nome LIKE '%".strtoupper($tf_nome)."%') ";

	$sql = "select ug.ug_id, ug.ug_tipo_cadastro, ug.ug_nome_fantasia, ug.ug_nome, ug.ug_perfil_corte_dia_semana, cor.cor_codigo,
				count(*) as venda_qtde, sum(valor_bruto) as venda_bruta, 
				sum(valor_comissao) as venda_comissao, sum(valor_liquido) as venda_liquida 
			from (
				select ug.ug_id, vg.vg_id,
					sum(vgm.vgm_valor * vgm.vgm_qtde) as valor_bruto, 
					sum(vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as valor_comissao,
					sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as valor_liquido
				from tb_dist_venda_games vg 
				inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
				inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id 
				where --vg.vg_cor_codigo is null and
					(vg.vg_data_inclusao >= '".formata_data($data_inicial,1)." 00:00:00' and vg.vg_data_inclusao <= '".formata_data($data_final,1)." 23:59:59')
					and (vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] . " or vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . ")
					$sqlFiltro
					group by ug.ug_id, vg.vg_id
			) as vendas 
			inner join dist_usuarios_games ug on ug.ug_id = vendas.ug_id
			left join cortes cor on cor.cor_ug_id = vendas.ug_id and cor.cor_periodo_ini = '".formata_data($data_inicial,1)." 00:00:00' and cor.cor_periodo_fim = '".formata_data($data_final,1)." 23:59:59'
			group by ug.ug_id, ug.ug_tipo_cadastro, ug.ug_nome_fantasia, ug.ug_nome, ug.ug_perfil_corte_dia_semana, cor.cor_codigo";
	$resest = SQLexecuteQuery($sql);
	$total_table = pg_num_rows($resest);

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
	if($total_table == 0) $msg = "Nenhum usuário com vendas no período encontrado.\n";
	else {				
		$resest = SQLexecuteQuery($sql);
				
		if($max + $inicial > $total_table) $reg_ate = $total_table;
		else $reg_ate = $max + $inicial;

		if($ordem == 1)	$ordem = 0;
		else $ordem = 1;
	}

	$varsel = "&BtnSearch=1&tf_ug_id=$tf_ug_id&tf_nome=$tf_nome&data_inicial=$data_inicial&data_final=$data_final";
}
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="JavaScript">
function GP_popupConfirmMsg(msg) { 
  document.MM_returnValue = confirm(msg);
}

function GP_popupAlertMsg(msg) { 
  document.MM_returnValue = alert(msg);
}
$(function(){
    var optDate = new Object();
         optDate.interval = 10000;

     setDateInterval('data_inicial','data_final',optDate);

 });
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
        <li><a href="index.php">Corte Semanal</a></li>
        <li class="active">Consulta Corte Semanal</li>
    </ol> 
</div>
<table class="table txt-preto fontsize-p">
  <tr>
    <td valign="top">
        <form name="form1" method="post" action="">
        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Pesquisa</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">C&oacute;digo Usuário</font></td>
            <td width="220"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"> 
              <input name="tf_ug_id" type="text" class="form" id="tf_ug_id" value="<?php echo $tf_ug_id ?>" size="7" maxlength="7">
              </font></td>
            <td width="136"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Nome/Nome Fantasia<br>
              </font></td>
            <td width="421"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"> 
              <input name="tf_nome" type="text" class="form" id="tf_nome" value="<?php echo $tf_nome ?>" size="35" maxlength="35">
              </font></td>
            <td width="57">&nbsp;</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Periodo</font></td>
            <td><input name="data_inicial" type="text" id="data_inicial" value="<?=$data_inicial ?>" size="10" maxlength="10">
              <font color="#666666" size="1" face="Arial, Helvetica, sans-serif">a</font> 
              <input name="data_final" type="text" id="data_final" value="<?=$data_final ?>" size="10" maxlength="10"></td>
            <td width="136"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"></font></td>
            <td width="421"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"></font></td>
            <td width="57"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          </tr>

          <?php if($msg != ""){?>
          	<tr><td colspan="5">&nbsp;</td></tr>
          	<tr><td colspan="5" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php echo str_replace("\n", "<br>", $msg)?></font></td></tr>
          <?php }?>
		</form>
		</table>

            <table class="table">
			<tr bgcolor=""> 
				<td bgcolor=""><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">
					<?php if($total_table > 0) { ?>
						Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font>
					<?php } else { ?>
						&nbsp;
					<?php } ?>
				</td>
			</tr>
		</table>
		
            <table class="table">
        <tr class="texto" bgcolor="#ECE9D8"> 
          <td align="center"><strong><a class="link_br" href="<?php echo $default_add . "?ordem=".$ordem."&inicial=" . $inicial . $varsel . "&ncamp=ug_id" ?>"><font class="texto">C&oacute;digo</font></a></strong></td>
          <td align="center"><strong><a class="link_br" href="<?php echo $default_add . "?ordem=".$ordem."&inicial=" . $inicial . $varsel . "&ncamp=ug_tipo_cadastro" ?>"><font class="texto">Tipo de Cadastro</font></a></strong></td>
<?php if($pgest['ug_tipo_cadastro'] == 'PF'){ ?>
          <td align="center"><strong><a class="link_br" href="<?php echo $default_add . "?ordem=".$ordem."&inicial=" . $inicial . $varsel . "&ncamp=nome" ?>"><font class="texto">Nome</font></a></strong></td>
<?php }else{ ?>
          <td align="center"><strong><a class="link_br" href="<?php echo $default_add . "?ordem=".$ordem."&inicial=" . $inicial . $varsel . "&ncamp=nome_fantasia" ?>"><font class="texto">Nome Fantasia</font></a></strong></td>
<?php } ?>
          <td align="center"><strong><a class="link_br" href="<?php echo $default_add . "?ordem=".$ordem."&inicial=" . $inicial . $varsel . "&ncamp=venda_qtde" ?>"><font class="texto">Qtde<br>Vendas</font></a></strong></td></strong></td>
          <td align="center"><strong><a class="link_br" href="<?php echo $default_add . "?ordem=".$ordem."&inicial=" . $inicial . $varsel . "&ncamp=venda_bruta" ?>"><font class="texto">Total<br>Vendas</font></a></strong></td>
          <td align="center"><strong><a class="link_br" href="<?php echo $default_add . "?ordem=".$ordem."&inicial=" . $inicial . $varsel . "&ncamp=venda_comissao" ?>"><font class="texto">Total<br>Comissão</font></a> 
          <td align="center"><strong><a class="link_br" href="<?php echo $default_add . "?ordem=".$ordem."&inicial=" . $inicial . $varsel . "&ncamp=venda_liquida" ?>"><font class="texto">Total<br>a Pagar</font></a> 
          <td align="center"><strong><font class="texto">Dia de Corte</font></a> 
          <td align="center"><img src="/images/inserir2.gif" alt="Gera corte" width="14" height="14" border="0"></td>
        </tr>
        <?php
			$cor1 = "#F5F5FB";
			$cor2 = "#F5F5FB";
			$cor3 = "#FFFFFF"; 	
			while (isset($resest) && $pgest = pg_fetch_array($resest)){
				$valor = 1;
				if($pgest['ug_tipo_cadastro'] == 'PF') $nome = $pgest['ug_nome'];
				else $nome = $pgest['ug_nome_fantasia'];

				//Por estabelecimento
				$total_vendas 		= $pgest['venda_bruta'];
				$total_pagar 		= $pgest['venda_liquida'];
				$total_vendas_qtde 	= $pgest['venda_qtde'];
				$total_comissao 	= $pgest['venda_comissao'];

				//Total
				$soma_total_vendas 		+= $total_vendas;
				$soma_total_pagar 		+= $total_pagar;
				$soma_total_vendas_qtde += $total_vendas_qtde;
				$soma_total_comissao 	+= $total_comissao;
                        
		 ?>
        <tr class="texto" bgcolor="<?php echo $cor1 ?>"> 
          <td align="right"><?php echo $pgest['ug_id'] ?></td>
          <td align="center"><?php echo $pgest['ug_tipo_cadastro'] ?></td>
          <td><a style="text-decoration:none" href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $pgest['ug_id'] ?>" title="Ir para Usuário"><?php echo $nome ?></a></td>
          <td align="right"><?php echo number_format($total_vendas_qtde, 0, '', '.') ?></td>
          <td align="right"><?php echo number_format($total_vendas, 2, ',', '.') ?></td>
          <td align="right"><?php echo number_format($total_comissao, 2, ',', '.') ?></td>
          <td align="right"><?php echo number_format($total_pagar, 2, ',', '.') ?></td>
          <td align="center"><?php if($pgest['ug_perfil_corte_dia_semana'] != $data_inicial_dia_semana){ ?><font color="red"><?}?><?php echo $GLOBALS['CORTE_DIAS_DA_SEMANA_DESCRICAO'][$pgest['ug_perfil_corte_dia_semana']] ?></td>
		<?php if(trim($pgest['cor_codigo']) == ""){ ?>
          <td align="center"><a onclick="return confirm('Confirma o corte para o período <?=$data_inicial?> a <?=$data_final?> do estabelecimento \'<?=$pgest['nome_fantasia'] ?>\'');" href="corte_corta_semana.php?acao=cortar&usuario_id=<?php echo $pgest['ug_id']?><?=$varsel?>"><img src="/images/inserir2.gif" alt="Gera corte" width="14" height="14" border="0"></a></td>
        <?php } else { ?>
          <td align="center"><a class="link_br" href="corte_consulta.php?usuario_id=<?php echo $pgest['ug_id']?>&BtnSearch=1"><font class="texto">Já existe corte</font></a></td>
          <?php }} ?>
        </tr>
        <?php
       if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} }
	   if (!$valor)
	   {  ?>
        <tr bgcolor="#f5f5fb"> 
          <td colspan="9" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
              N&atilde;o h&aacute; registros<br>
              <br>
              </strong></font></div></td>
        </tr>
        <?php } else { ?>
        <tr class="texto" bgcolor="#E4E4E4"> 
          <td colspan="3"><strong>TOTAL</strong></font></td>
          <td align="right"><?php echo number_format($soma_total_vendas_qtde, 0, '', '.') ?></td>
          <td align="right"><?php echo number_format($soma_total_vendas, 2, ',', '.') ?></td>
          <td align="right"><?php echo number_format($soma_total_comissao, 2, ',', '.') ?></td>
          <td align="right"><?php echo number_format($soma_total_pagar, 2, ',', '.') ?></td>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr bgcolor="#E4E4E4"> 
          <td colspan="9" bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><strong> 
            OBS: Valores expressos em R$. Diferença no TOTAL devido a arredondamento da comissão.</strong></font></td>
        </tr>
        	<?php paginacao_query($inicial, $total_table, $max, '9', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);	?>
                        <?php  }  ?>
      </table>
      <?php pg_close ($connid); ?>
	  <p>&nbsp;</p>
	  <p>&nbsp;</p>
	  <p>&nbsp;</p>
	  <?php
	  
	  @require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
	  
	  ?>
    </td>
  </tr>
</table>
</body>
</html>