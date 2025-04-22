<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

	set_time_limit ( 30000 ) ;

	$time_start = getmicrotime();
	

	$default_add  = nome_arquivo($PHP_SELF);

	// Cria instância com LH teste
	$usuarioGames = new UsuarioGames(468);
	$bret = $usuarioGames->b_IsLogin_lista_extrato(1, $usuarios_com_extrato_id);
	sort($usuarios_com_extrato_id);
//echo "<pre>".print_r($usuarios_com_extrato_id, true)."</pre>";
	$reg_ate = $total_table;

	$total_table = count($usuarios_com_extrato_id);
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
		<?php if($total_table > 0) { ?>
        <table class="table txt-preto fontsize-pp">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
				  	<table width="100%" border='0' cellpadding="2" cellspacing="1">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr bgcolor="#ECE9D8" class="texto"> 
					  	<td align="center"><b>Código</b></td>
					  	<td align="center"><b>Login</b></td>
					  	<td align="center"><b>Nome fantasia</b></td>
					  	<td align="center"><b>Email</b></td>
					  	<td align="center"><b>Tipo cadastro</b></td>
                      </tr>
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;

						$s_lista_ids = "";
						foreach($usuarios_com_extrato_id as $key => $val) {
							$s_lista_ids .= (($s_lista_ids)?", ":"")."'".$val."'";
						}
//echo $s_lista_ids."<br>";
						$a_lista = array();
						$sql  = "select ug_id, ug_login, ug_nome, ug_nome_fantasia, ug_email, ug_tipo_cadastro from dist_usuarios_games ";
						$sql .= "where 1=1 ";
						$sql .= " and ug_login in ($s_lista_ids) ";
						$sql .= "order by ug_login;";
//echo "$sql<br>";
						$rs_usuarios = SQLexecuteQuery($sql);
						if(!$rs_usuarios || pg_num_rows($rs_usuarios) == 0) $msg = "Nenhum usuário encontrado (1ag).\n";

						if($msg == ""){
							//Verifica cada item de cada produto
							while($rs_usuarios_row = pg_fetch_array($rs_usuarios)) {
								$id = $rs_usuarios_row['ug_id'];
								$a_lista[$id] = array();
								$a_lista[$id]['ug_id'] = $rs_usuarios_row['ug_id'];
								$a_lista[$id]['ug_login'] = $rs_usuarios_row['ug_login'];
								$a_lista[$id]['ug_nome'] = $rs_usuarios_row['ug_nome'];
								$a_lista[$id]['ug_nome_fantasia'] = $rs_usuarios_row['ug_nome_fantasia'];
								$a_lista[$id]['ug_email'] = $rs_usuarios_row['ug_email'];
								$a_lista[$id]['ug_tipo_cadastro'] = $rs_usuarios_row['ug_tipo_cadastro'];
							}
						}

						foreach($a_lista as $key => $val) {

							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
					?>
                      <tr bgcolor="<?php echo $cor1 ?>" class="texto"> 
                        <td align="center"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $val['ug_id'] ?>" target="_blank"><?php echo $val['ug_id'] ?></a></td>
                        <td align="center"><?php echo $val['ug_login'] ?></td>
                        <td align="center"><?php echo $val['ug_nome_fantasia'] ?></td>
                        <td align="center"><?php echo $val['ug_email'] ?></td>
                        <td align="center"><?php echo $val['ug_tipo_cadastro'] ?></td>
					  	<td align="center">&nbsp;</td>
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
  
  <tr><td align='center' class='texto'>Tempo médio de processamento: <?php echo number_format((getmicrotime() - $time_start), 2, '.', '.') ?> s.</td>
  </tr>

</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
