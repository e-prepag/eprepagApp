<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

	$time_start = getmicrotime();
	

	$default_add  = nome_arquivo($PHP_SELF);

	// Cria instância com LH teste
	$usuarioGames = new UsuarioGames(468);
	$bret = $usuarioGames->b_IsLogin_email_ponto_venda(1, $usuarios_ponto_venda_id);
	$total_table = count($usuarios_ponto_venda_id);
	$reg_ate = $total_table;

	$s_ug_id_list = "";
	foreach($usuarios_ponto_venda_id as $key => $val) {
		$s_ug_id_list .= (($s_ug_id_list)?", ":"").$val;
	}

	$sql  = "select *  \n";
	$sql .= "from dist_usuarios_games ug \n";
	$sql .= "where ug.ug_id in ($s_ug_id_list) ";
	$sql .= "order by ug_login;";

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


<table >
  <tr> 
    <td valign="top"> 
        <form name="form1" method="post">
        <table border="0" cellpadding="0" cellspacing="2">
		</table>

            <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>

        <?php if($total_table > 0) { ?>
        <table class="fontsize-pp txt-preto">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
                      <table class="table">
				  	  <tr> 
						<td colspan="3" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr bgcolor="#ECE9D8" class="texto"> 
					  	<td align="center"><b>Código</b></td>
					  	<td align="center"><b>Login</b></td>
					  	<td align="center"><b>Email</b></td>
                      
					</tr>
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;

						if((pg_num_rows($rs) != 0) && ($rs)) {
							while ($pgrs = pg_fetch_array ($rs)) {
								//if(b_IsUsuarioReinaldo()) 
								{
//									$taxa_aproveitamento = 100.*$pgrs['vg_valor']/($pgrs['vg_valor'] + $pgrs['vg_valor_inc']);
								?>
								<?php
									//title="Taxa de aproveitamento: <_?_php echo number_format($taxa_aproveitamento, 2, '.', '.') ?_>%"
									?>
								  <tr bgcolor="<?php echo $cor1 ?>" class="texto"> 
									<td align="center"><a href="com_usuario_detalhe.php?usuario_id=<?php echo $pgrs['ug_id']; ?>" target="_blank"><?php echo $pgrs['ug_id']; ?></a></td>
									<td align="center"><nobr><?php echo (($pgrs['ug_login'])?$pgrs['ug_login']:"-") ?></nobr></td>
									<td align="center"><?php echo $pgrs['ug_email'] ?></td>
								  </tr>
							<?php 
								}
							}
							
							?>
						  <tr> 
							<td colspan="13" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
						  </tr>
							<?php 
						} else {
						?>
						  <tr bgcolor="#ECE9D8" class="texto"> 
							<td align="center" colspan="13"><b>Não foram encontrados registros</b></td>
						  </tr>
						<?php
						}

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
