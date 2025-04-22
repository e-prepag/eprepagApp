<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

	set_time_limit ( 3000 ) ;

	$time_start = getmicrotime();
	

	$default_add  = nome_arquivo($PHP_SELF);

	// Cria instância com usuário teste
	$usuarioGames = new UsuarioGames(7909);
	$bret = $usuarioGames->b_IsLogin_pagamento_vip(1, $usuarios_pagamento_online_vip_id);
	
	$meu_ip_1 = '201.93.162.169';
	$meu_ip_2 = '189.62.151.212';

	if ($_SERVER['REMOTE_ADDR'] == $meu_ip_1 || $meu_ip_2) {
		echo 'teste';
		"<pre>".print_r($usuarios_pagamento_online_vip_id, true)."</pre>";
	}
	
/*
if(b_IsUsuarioReinaldo()) {
echo "<pre>".print_r($usuarios_pagamento_online_vip_id, true)."</pre>";
}
*/
	$total_table = count($usuarios_pagamento_online_vip_id);
	$reg_ate = $total_table;

	$s_ug_id_list = "";
	foreach($usuarios_pagamento_online_vip_id as $key => $val) {
		$s_ug_id_list .= (($s_ug_id_list)?", ":"").$val;
	}

	$sql  = "select ug_id, ug_nome, ug_email, vg_valor, vg_qtde_itens, vg_data_primeira_venda, vg_data_ultima_venda, \n";
	$sql .= "	(EXTRACT(epoch FROM (vg_data_ultima_venda - vg_data_primeira_venda ))/(24*3600))  as ndays,    \n";
	$sql .= "	(coalesce((EXTRACT(epoch FROM (vg_data_ultima_venda - vg_data_primeira_venda ))/(24*3600) ), 1) /vg_qtde_itens) as ndays_per_venda,    \n";
	$sql .= "	vg_valor_inc, vg_qtde_itens_inc, vg_data_primeira_venda_inc, vg_data_ultima_venda_inc   \n";
	$sql .= "from usuarios_games ug \n";
	$sql .= "	inner join ( \n";
	$sql .= "		select vg_ug_id, \n";
	$sql .= "			sum(vgm.vgm_valor * vgm.vgm_qtde) as vg_valor, \n";
	$sql .= "			sum(vgm.vgm_qtde) as vg_qtde_itens, \n";
	$sql .= "			min(vg_data_inclusao) as vg_data_primeira_venda, \n";
	$sql .= "			max(vg_data_inclusao) as vg_data_ultima_venda \n";
	$sql .= "		from tb_venda_games vg \n";
	$sql .= "		inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id \n";
	$sql .= "		where vg_ultimo_status=5 \n";
	$sql .= "			and vg.vg_ug_id in ($s_ug_id_list) ";
	$sql .= "		group by vg_ug_id \n";
	$sql .= "	) v	on v.vg_ug_id = ug.ug_id \n";
	$sql .= "	inner join ( \n";
	$sql .= "		select vg_ug_id, \n";
	$sql .= "		sum(vgm.vgm_valor * vgm.vgm_qtde) as vg_valor_inc, \n";
	$sql .= "		sum(vgm.vgm_qtde) as vg_qtde_itens_inc, \n";
	$sql .= "		min(vg_data_inclusao) as vg_data_primeira_venda_inc, \n";
	$sql .= "		max(vg_data_inclusao) as vg_data_ultima_venda_inc \n";
	$sql .= "		from tb_venda_games vg \n";
	$sql .= "			inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id \n";
	$sql .= "		where vg_ultimo_status=6 \n";
	$sql .= "			and vg.vg_ug_id in ($s_ug_id_list) ";
	$sql .= "		group by vg_ug_id \n";

	$sql .= "	) vi	on vi.vg_ug_id = ug.ug_id \n";
	$sql .= "where 1=1 \n";
	$sql .= "	and ug.ug_id in ($s_ug_id_list) ";
	$sql .= "order by vg_valor desc, vg_qtde_itens desc;	--ug_nome;";

/*
if(b_IsUsuarioReinaldo()) {
echo str_replace("\n", "<br>\n", $sql)."<br>";
//die($sql);
}
*/
	$rs = SQLexecuteQuery($sql);


ob_end_flush();
?>
<script language="javascript">
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
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<table class="table txt-preto fontsize-pp">
  <tr> 
    <td> 
        <form name="form1" method="post" action="com_pesquisa_usuarios_vip.php">
        
        <table>
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Atualizar" class="btn btn-sm btn-info"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>

        <table class="top20 pull-left">
          <tr bgcolor="#ECE9D8" class="texto"> 
            <td align="left" colspan="2" class="texto">Limítes para Gamers Normais</td>
          </tr>
          <tr bgcolor="#F5F5FB">
              <td align="left" class="texto">RISCO_GAMERS_TOTAL_DIARIO</td>
              <td align="right" class="texto"><?php echo number_format($GLOBALS['RISCO_GAMERS_TOTAL_DIARIO'], 2, ',', '.')?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
              <td align="left" class="texto">RISCO_GAMERS_PAGAMENTOS_DIARIO</td>
              <td align="right" class="texto"><?php echo $GLOBALS['RISCO_GAMERS_PAGAMENTOS_DIARIO']?></td></tr>
        </table>

        <table class="top20 left10 pull-right">
          <tr bgcolor="#ECE9D8" class="texto"> 
            <td align="left" colspan="3" class="texto">Limítes para Gamers VIP</td>
          </tr>
          <tr bgcolor="#F5F5FB"><td align="left" class="texto">RISCO_GAMERS_VIP_TOTAL_DIARIO</td><td align="left" class="texto">&nbsp;</td><td align="right" class="texto"><?php echo number_format($GLOBALS['RISCO_GAMERS_VIP_TOTAL_DIARIO'], 2, ',', '.')?></td></tr>
          <tr bgcolor="#F5F5FB"><td align="left" class="texto">RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO</td><td align="left" class="texto">&nbsp;</td><td align="right" class="texto"><?php echo $GLOBALS['RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO']?></td></tr>
        </table>
    </td></tr></table>
<table class="txt-preto fontsize-pp">
    <tr><td>
		<?php if($total_table > 0) { ?>
        <table class="table">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
                      <table class="table">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr bgcolor="#ECE9D8" class="texto"> 
					  	<td align="center" colspan="3">&nbsp;</td>
					  	<td align="center" colspan="5"><b>Vendas Completas</b></td>
						<td align="center"><strong><font class="texto">&nbsp;</font></strong></td>
					  	<td align="center" colspan="4"><b>Vendas Incompletas</b></td>
					  </tr>
                      <tr bgcolor="#ECE9D8" class="texto"> 
					  	<td align="center"><b>Código</b></td>
					  	<td align="center"><b>Nome</b></td>
					  	<td align="center"><b>Email</b></td>
                      
						<td align="center"><strong><font class="texto"><nobr>Vendas R$</nobr></font></strong></td>
                        <td align="center"><strong><font class="texto"><nobr>n Vendas</nobr></font></strong></td>
                        <td align="center"><strong><font class="texto"><nobr>Ticket médio</nobr></strong></td>
                        <td align="center"><strong><font class="texto"><nobr>Data última venda</nobr></font></strong></td>
						<td align="center"><strong><font class="texto"><nobr>Status</nobr>

						<td align="center"><strong><font class="texto">&nbsp;</font></strong></td>

						<td align="center"><strong><font class="texto"><nobr>Pedidos R$</nobr></font></strong></td>
                        <td align="center"><strong><font class="texto"><nobr>n Pedidos</nobr></font></strong></td>
                        <td align="center"><strong><font class="texto"><nobr>Ticket médio</nobr></strong></td>
                        <td align="center"><strong><font class="texto"><nobr>Data último pedido</nobr></font></strong></td>

					  </tr>
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;

						if((pg_num_rows($rs) != 0) && ($rs)) {
							while ($pgrs = pg_fetch_array ($rs)) {
								//if(b_IsUsuarioReinaldo()) 
								{
									$taxa_aproveitamento = 100.*$pgrs['vg_valor']/($pgrs['vg_valor'] + $pgrs['vg_valor_inc']);
								?>
								  <tr bgcolor="<?php echo $cor1 ?>" class="texto" title="Taxa de aproveitamento: <?php echo number_format($taxa_aproveitamento, 2, '.', '.') ?>%"> 
									<td align="center"><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $pgrs['ug_id']; ?>" target="_blank"><?php echo $pgrs['ug_id']; ?></a></td>
									<td align="center"><nobr><?php echo (($pgrs['ug_nome'])?$pgrs['ug_nome']:"-") ?></nobr></td>
									<td align="center"><?php echo $pgrs['ug_email'] ?></td>
									<?php 
										$vg_qtde_itens = (($pgrs['vg_qtde_itens']>0)?$pgrs['vg_qtde_itens']:1);
									?>
									<td align="right"><?php echo number_format($pgrs['vg_valor'], 2, '.', '.') ?></td>
									<td align="right"><?php echo $vg_qtde_itens ?></td>
									<td align="right"><?php echo number_format($pgrs['vg_valor']/$vg_qtde_itens, 2, '.', '.') ?></td>
									<td align="right" title="Primeira venda: '<?php echo substr($pgrs['vg_data_primeira_venda'], 0, 19) ?>'
Dias entre 1a e última vendas: <?php echo number_format($pgrs['ndays'], 2, '.', '.') ?> 
Média de dias por venda: <?php echo number_format($pgrs['ndays_per_venda'], 2, '.', '.') ?>">

									<nobr><?php echo substr($pgrs['vg_data_ultima_venda'], 0, 19) ?></nobr></td>
									<?php
										$status	= qtde_dias(substr($pgrs['vg_data_ultima_venda'], 8, 2)."-".substr($pgrs['vg_data_ultima_venda'], 5, 2)."-".substr($pgrs['vg_data_ultima_venda'], 0, 4),date('d-m-Y'));
										if ($status <= $ATRASO_GAMER_DIAS_LIM_1) {
											$status_label	=	"<font color='#66CC00'>Frequente</font>";
										}
										elseif($status > $ATRASO_GAMER_DIAS_LIM_1 && $status <= $ATRASO_GAMER_DIAS_LIM_2){
											$status_label	=	"<font color='#FFCC00'>Atrasado</font>";
										}
										elseif($status > $ATRASO_GAMER_DIAS_LIM_2){
											$status_label	=	"<font color='red'>Abandonou</font>";
										}
									?>
									<td align="right" title="<?php echo $status." dias sem comprar" ?>"><?php echo $status_label ?></td>

									<td align="center"><strong><font class="texto">&nbsp;</font></strong></td>

									<!-- incompletos -->
									<?php 
										$vg_qtde_itens_inc = (($pgrs['vg_qtde_itens_inc']>0)?$pgrs['vg_qtde_itens_inc']:1);
									?>
									<td align="right"><?php echo number_format($pgrs['vg_valor_inc'], 2, '.', '.') ?></td>
									<td align="right"><?php echo $vg_qtde_itens_inc ?></td>
									<td align="right"><?php echo number_format($pgrs['vg_valor_inc']/$vg_qtde_itens_inc, 2, '.', '.') ?></td>
									<td align="right" title="Primeira venda: '<?php echo substr($pgrs['vg_data_primeira_venda_inc'], 0, 19) ?>'"><nobr><?php echo substr($pgrs['vg_data_ultima_venda_inc'], 0, 19) ?></nobr></td>

								  </tr>
							<?php 
								}
							}
							
							?>
						  <tr> 
							<td colspan="13" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
						  </tr>
						  <tr> 
							<td colspan="13" bgcolor="#FFFFFF" class="texto">Lista de IDs: <?php echo $s_ug_id_list ?></font></td>
						  </tr>
							<?php 
						} else {
						?>
						  <tr bgcolor="#ECE9D8" class="texto"> 
							<td align="center" colspan="13"><b>Não foram encontrados registros</b></td>
						  </tr>
						<?php
						}
/*
						foreach($usuarios_pagamento_online_vip_id as $key => $val) {
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
//echo "Levanta $val<br>";
							$obj_usuarioGames = UsuarioGames::getUsuarioGamesById($val);
//echo "<pre>".print_r($obj_usuarioGames, true)."</pre>";
							
					?>
                      <tr bgcolor="<?php echo $cor1 ?>" class="texto"> 
                        <td align="center"><a href="com_usuario_detalhe.php?usuario_id=<?php echo $obj_usuarioGames->getId() ?>" target="_blank"><?php echo $obj_usuarioGames->getId() ?></a></td>
                        <td align="center"><?php echo (($obj_usuarioGames->getNome())?$obj_usuarioGames->getNome():"-") ?></td>
                        <td align="center"><?php echo $obj_usuarioGames->getEmail() ?></td>
					  	<td align="center">&nbsp;</td>
                      </tr>
					<?php 	}	
*/					
					?>
                    </table>
				  </td>
                </tr>
              </table>
          <?php  }  ?>
    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
