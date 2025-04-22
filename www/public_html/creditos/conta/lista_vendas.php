<?php 
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";


$_PaginaOperador2Permitido = 54; 
validaSessao(); 

	$varsel = "&tf_v_codigo=$tf_v_codigo";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";

	//paginacao
	$p = $_REQUEST['p'];
	if(!$p) $p = 1;
	$registros = 20;
	$registros_total = 0;

	//Recupera usuario
	if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser'])){
		$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
		$usuarioId = $usuarioGames->getId();
	}

	//Validacoes
	$msg = "";	

	//Recupera as vendas
	if($msg == ""){

		$sql  = "
				select 
					vg_id, 
					vg_data_inclusao, 
					vg_pagto_tipo, 
					vg_ultimo_status, 
					vg_usuario_obs,
					valor, 
					qtde_itens, 
					qtde_produtos, 
					repasse
				from
				( 
				 (
				 select 
					vg.vg_id, 
					vg.vg_data_inclusao, 
					vg.vg_pagto_tipo::char, 
					vg.vg_ultimo_status::char, 
					vg.vg_usuario_obs, 
					sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, 
					sum(vgm.vgm_qtde) as qtde_itens, 
					count(*) as qtde_produtos, 
					sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse
				from tb_dist_venda_games vg 
					inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
				where vg.vg_ug_id=" . $usuarioId;
		if($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and vg.vg_id=" . $tf_v_codigo;
		if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
			if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
				$sql .= " and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'";
		$sql .=	" group by vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_usuario_obs 
				)
				union all 
				(
				 select 
					vb2c_vg_id as vg_id, 
					\"vb2c_dataVenda\" as vg_data_inclusao, 
					'B2C' as vg_pagto_tipo, 
					vb2c_status as vg_ultimo_status, 
					'' as vg_usuario_obs, 
					\"vb2c_precoServico\" as valor, 
					1 as qtde_itens, 
					1 as qtde_produtos, 
					(\"vb2c_precoServico\" * vb2c_comissao_para_repasse / 100) as repasse
				from tb_vendas_b2c  
				where vb2c_ug_id_lan =" . $usuarioId;
		if($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and vb2c_vg_id=" . $tf_v_codigo;
		if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
			if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
				$sql .= " and \"vb2c_dataVenda\" between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'";
		$sql .=	"  
				)
				union all 
				(
				 select 
					rprs_vg_id as vg_id, 
					rprs_data_recarga as vg_data_inclusao, 
					'Recarga' as vg_pagto_tipo, 
					rprs_status as vg_ultimo_status, 
					'' as vg_usuario_obs, 
					rprs_valor as valor, 
					1 as qtde_itens, 
					1 as qtde_produtos, 
					(rprs_valor * rprs_comissao_para_repasse / 100) as repasse
				from tb_recarga_pedidos_rede_sim  
				where rprs_ug_id =" . $usuarioId;
		if($tf_v_codigo && is_numeric($tf_v_codigo)) $sql .= " and rprs_vg_id=" . $tf_v_codigo;
		if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
			if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
				$sql .= " and rprs_data_recarga between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'";
		$sql .=	"   
                                    and rprs_data_recarga is not null
				)
				) as vendas ";
		$rs_total = SQLexecuteQuery($sql);
		if($rs_total) $registros_total = pg_num_rows($rs_total);
		$sql .= " order by vg_data_inclusao desc " .
				" offset " . ($p - 1) * $registros . " limit " . $registros;
		//echo $sql."<br>";
		
//if($usuarioGames->getLogin()=="REINALDOLH") {
//	echo str_replace("\n", "<br>\n", $sql)."<br>";
//}
		$rs_vendas = SQLexecuteQuery($sql);
		if(!$rs_vendas || pg_num_rows($rs_vendas) == 0) $msg = "Nenhuma venda encontrada.\n";

	}
	
	//Redireciona se ha algum dado invalido
	//----------------------------------------------------
//	if($msg != ""){
//		$strRedirect = "/prepag2/dist_commerce/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Meus Pedidos") . "&link=" . urlencode("/prepag2/dist_commerce/conta/index.php");
//		redirect($strRedirect);
//	}
	
?>

<?php $pagina_titulo = "Meus Pedidos"; ?>
<script language='javascript' src='/js/popcalendar.js'></script>
<?php require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/cabecalho.php";  ?>

	<table  border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr><td>&nbsp;</td></tr>
    <tr valign="top" align="center">
      <td>
			<form name="form1" method="post" action="lista_vendas.php">
			<table border="0" cellspacing="01" width="90%" align="center">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Pesquisa <?php echo " (".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Número do Pedido</b></td>
    	          <td class="texto" align="center"><b>Período da Compra</b></td>
    	          <td>&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><input name="tf_v_codigo" type="text" class="form" value="<?php echo $tf_v_codigo ?>" size="7" maxlength="7"></td>
    	          <td class="texto" align="center">
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
					  <a href="#" onClick="return false;"><img src="/imagens/cal.gif" width="16" height="16" alt="Calendário" onclick="popUpCalendar(this, form1.tf_v_data_inclusao_ini, 'dd/mm/yyyy')" border="0" align="absmiddle"></a> 
					  a 
					  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
					  <a href="#" onClick="return false;"><img src="/imagens/cal.gif" width="16" height="16" alt="Calendário" onclick="popUpCalendar(this, form1.tf_v_data_inclusao_fim, 'dd/mm/yyyy')" border="0" align="absmiddle"></a>
				  </td>
				  <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="botao_simples"></td>
    	        </tr>
			</table>
			</form>
			
			<table border="0" cellspacing="01" width="90%" align="center">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" width="5%">&nbsp;</td>
    	          <td class="texto" align="center" width="19%" height="25"><b>Número do Pedido</b></td>
    	          <td class="texto" align="center" width="24%"><b>Data da Compra</b></td>
    	          <td class="texto" align="center" width="19%"><b>Valor</b></td>
    	          <td class="texto" align="center" width="19%"><b>Repasse</b></td>
    	          <td class="texto" align="center" width="19%"><b>Qtde itens</b></td>

    	          <td class="texto" align="center" width="25%"><b>Reemitir Comprovante de Compra</b></td>
    	          <td class="texto" align="center" width="25%"><b>Status</b></td>
    	          <td class="texto" align="center" width="25%"><b>Obs.</b></td> <?php  // <b>Observações</b> ?>

				</tr>
		<?php
			$i = 0;
			while($rs_vendas_row = pg_fetch_array($rs_vendas)){ 
				$bgcolor = ((++$i) % 2)?" bgcolor=\"F5F5FB\"":"";

if(bRelatorioVendasComOperadores($usuarioGames->getLogin()) && ($rs_vendas_row['vg_pagto_tipo'] != "B2C")) {

				$sql_operador = "select ugo_id, ugo_login from dist_usuarios_games_operador_log ugol inner join dist_usuarios_games_operador ugo on ugol.ugol_ugo_id = ugo.ugo_id where ugol.ugol_vg_id = ".$rs_vendas_row['vg_id']."";
//echo "$sql_operador<br>\n";
				$rs_operador = SQLexecuteQuery($sql_operador);

				if($rs_operador && pg_num_rows($rs_operador ) > 0) {
					$pg_operador = pg_fetch_array($rs_operador);
					$ugo_id		= $pg_operador['ugo_id'];
					$ugo_login	= $pg_operador['ugo_login'];
//					echo "ID: ".$ugo_id." - '".$ugo_login."'<br>";
				} else {
					$ugo_id		= "";
					$ugo_login	= "";
				}
}

			?>
    	        <tr<?php echo $bgcolor?>>
    	          <td class="texto" align="center">
			<?php if($rs_vendas_row['vg_pagto_tipo'] != "B2C" && $rs_vendas_row['vg_pagto_tipo'] != "Recarga") { ?>
                        <a href="/creditos/conta/venda_detalhe.php?venda_id=<?php echo $rs_vendas_row['vg_id']?>" class="link_azul">
    	          	<img src="/imagens/icoMostrar.gif" width="16" height="16" border="0" alt="Mostrar <?php echo $rs_vendas_row['vg_id']?>">
    	          	</a>
                        <?php } ?>
				  </td>
    	          <td class="texto" align="center" height="25" width="200">
					<?php if($rs_vendas_row['vg_pagto_tipo'] == "B2C") { ?>
			<a href="/creditos/conta/pagto_compr_b2c.php?venda=<?php echo $rs_vendas_row['vg_id']?>" class="link_azul"><?php echo formata_codigo_venda($rs_vendas_row['vg_id'])?></a>
					<?php 
					}
					elseif($rs_vendas_row['vg_pagto_tipo'] == "Recarga") { ?>
			<a href="/creditos/conta/pagto_compr_recarga_rs.php?venda=<?php echo $rs_vendas_row['vg_id']?>" class="link_azul"><?php echo formata_codigo_venda($rs_vendas_row['vg_id'])?></a>
					<?php 
					}
                                        else { ?>
    	          	<a href="/creditos/conta/pagto_compr_redirect.php?venda=<?php echo $rs_vendas_row['vg_id']?>" class="link_azul"><?php echo formata_codigo_venda($rs_vendas_row['vg_id'])?></a>
					<?php } ?>
    	          </td>
    	          <td class="texto" align="center"><?php echo formata_data_ts($rs_vendas_row['vg_data_inclusao'], 0, true, false)?></td>
    	          <td class="texto" align="center"><?php echo number_format($rs_vendas_row['valor'], 2, ',','.')?></td>
    	          <td class="texto" align="center"><?php echo number_format($rs_vendas_row['repasse'], 2, ',','.')?></td>
    	          <td class="texto" align="center"><?php echo number_format($rs_vendas_row['qtde_itens'], 0, '','.')?></td>

				  <td class="texto" align="center" height="25" width="200">
    	          	<?php if($rs_vendas_row['vg_pagto_tipo'] != "B2C" && $rs_vendas_row['vg_pagto_tipo'] != "Recarga") { ?>
    	          	<a href="/creditos/conta/pagto_compr_redirect.php?venda=<?php echo $rs_vendas_row['vg_id']?>" class="link_azul">Reemitir</a> 
					<?php }
					else { ?>
					--
					<?php } ?>
    	          </td>
    	          <td class="texto" height="20">&nbsp;
					<?php if($rs_vendas_row['vg_pagto_tipo'] != "B2C" && $rs_vendas_row['vg_pagto_tipo'] != "Recarga") { ?>
    	          	<img src="/imagens/<?php echo $STATUS_VENDA_ICONES[$rs_vendas_row['vg_ultimo_status']]?>" width="20" height="20" border="0" alt="<?php echo $STATUS_VENDA_DESCRICAO[$rs_vendas_row['vg_ultimo_status']]?>" title="<?php echo $STATUS_VENDA_DESCRICAO[$rs_vendas_row['vg_ultimo_status']]?>">
					<?php }
					else { 
						if($rs_vendas_row['vg_ultimo_status'] == 'N') {?>
						<img src="/imagens/<?php echo $STATUS_VENDA_ICONES[$STATUS_VENDA['VENDA_CANCELADA']]?>" width="20" height="20" border="0" alt="<?php echo $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['VENDA_CANCELADA']]?>" title="<?php echo $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['VENDA_CANCELADA']]?>">
					<?php
						}
						elseif($rs_vendas_row['vg_ultimo_status'] == '0') {?>
						<img src="/imagens/<?php echo $STATUS_VENDA_ICONES[$STATUS_VENDA['PEDIDO_EFETUADO']]?>" width="20" height="20" border="0" alt="<?php echo $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['PEDIDO_EFETUADO']]?>" title="<?php echo $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['PEDIDO_EFETUADO']]?>">
					<?php
						}
						elseif($rs_vendas_row['vg_ultimo_status'] == '1') {?>
						<img src="/imagens/<?php echo $STATUS_VENDA_ICONES[$STATUS_VENDA['VENDA_REALIZADA']]?>" width="20" height="20" border="0" alt="<?php echo $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['VENDA_REALIZADA']]?>" title="<?php echo $STATUS_VENDA_DESCRICAO[$STATUS_VENDA['VENDA_REALIZADA']]?>">
					<?php
						}
					} ?>
    	          </td>
    	          <td class="texto" height="20">
    	        <?php if(trim($rs_vendas_row['vg_usuario_obs']) != ""){ ?>
				  <?php // echo $rs_vendas_row['vg_usuario_obs']?>
					<a href="/creditos/conta/venda_detalhe.php?venda_id=<?php echo $rs_vendas_row['vg_id']?>" class="link_azul">
    	          	<img src="/imagens/icoMostrar.gif" width="16" height="16" border="0" alt="Ver Observações para o pedido <?php echo $rs_vendas_row['vg_id']?>">
    	          	</a>
    	        <?php } else { ?>
					&nbsp;
    	        <?php } ?>
     	        <?php 
if(bRelatorioVendasComOperadores($usuarioGames->getLogin()) && ($rs_vendas_row['vg_pagto_tipo'] != "B2C" && $rs_vendas_row['vg_pagto_tipo'] != "Recarga")) {
					if($ugo_login!="") {
						echo "Compra feita por '$ugo_login' (ID: $ugo_id)";
					}
}
				?>
   	          </td>

    	        </tr>
		<?	} ?>
			</table>

      </td>
    </tr>
	</table>

	<table border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr>
      	<td align="center" class="texto">
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="botao_simples">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='/creditos/conta/index.php';" class="botao_simples">
      		<?php if($p < ($registros_total/$registros)){ ?>
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="botao_simples">
			<?php } ?>         	
      	</td>
    </tr>
	</table>
	<br>&nbsp;
			<table border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
				<tr>
					<td align="left" class="texto"><b><font color="#3300CC">Legenda</font></b></td>
				</tr>
				<tr bgcolor="F0F0F0"><td align="left" class="texto"><b>Status da Venda</b></td><td align="left" class="texto"><b>Descrição</b></td><td align="center" class="texto"><b>Ícone</b></td></tr>
			    <tr><td align="center" class="texto">
		<?php

	$i = 0;
	foreach ($STATUS_VENDA as $key => $value) {
		if(($STATUS_VENDA[$key]!='3') && ($STATUS_VENDA[$key]!='4')) {
			$bgcolor = ((++$i) % 2)?" bgcolor=\"F5F5FB\"":"";
			echo "<tr".$bgcolor."><td align=\"left\" class=\"texto\">".$key."</td><td align=\"left\" class=\"texto\">".$STATUS_VENDA_DESCRICAO[$value]."</td><td align=\"center\" class=\"texto\"><img src=\"../images/".$STATUS_VENDA_ICONES[$value]."\" width=\"20\" height=\"20\" border=\"0\" alt=\"".$STATUS_VENDA_DESCRICAO[$value]."\"></td></tr>";
		}
	}


		?>
					
				</td></tr>
		    </table>

		</td>
    </tr>
	</table>

<?php require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/rodape.php";  ?>
<!-- Google Analytics -->
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>  
<script type="text/javascript">  
  _uacct = "UA-1903237-3";  
  urchinTracker();  
</script>    
                        <?php }?>