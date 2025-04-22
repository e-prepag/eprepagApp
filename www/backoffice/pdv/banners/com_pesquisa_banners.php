<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once "/www/includes/bourls.php";

	$time_start = getmicrotime();

//echo "<pre>".print_r($_REQUEST, true)."<pre>";

	if(!$ncamp)    $ncamp       = 'b_id';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 0;
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
	$max          = 100;	//$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&BtnSearch=1&tf_b_id=$tf_b_id&tf_b_ativo=$tf_b_ativo";
	$varsel .= "&tf_b_data_expira_ini=$tf_b_data_expira_ini&tf_b_data_expira_fim=$tf_b_data_expira_fim";
	$varsel .= "&tf_b_data_inicio_ini=$tf_b_data_inicio_ini&tf_b_data_inicio_fim=$tf_b_data_inicio_fim";
	$varsel .= "&tf_b_nome=$tf_b_nome&tf_b_conteudo=$tf_b_conteudo";
	$varsel .= "&tf_b_tipo=$tf_b_tipo&tf_b_status=$tf_b_status&tf_b_titulo=$tf_b_titulo";

	if(isset($BtnSearch))
	{
		//Validacao
		$msg = "";

		//codigo
		if($msg == "")
			if($tf_b_id)
			{
				if(!is_numeric($tf_b_id))
					$msg = "Código do banner deve ser numérico.\n";
			}
		
		//Data
		if($msg == "")
			if($tf_b_data_expira_ini || $tf_b_data_expira_fim)
			{
				if(verifica_data($tf_b_data_expira_ini) == 0)
					$msg = "A data de encerramento inicial do banner é inválida.\n";
				
				if(verifica_data($tf_b_data_expira_fim) == 0)
					$msg = "A data de encerramento final do banner é inválida.\n";
			}
		
		
		//Busca banners
		if($msg == "")
		{

			$filtro = array();
			if($tf_b_id) 			$filtro['b_id'] = $tf_b_id;
			if($tf_b_status) 		$filtro['b_status'] = (($tf_b_status=="1")?1:0);
			if($tf_b_nome) 			$filtro['b_nomeLike'] = $tf_b_nome;
			if($tf_b_conteudo) 		$filtro['b_conteudo'] = $tf_b_conteudo;
			if($tf_b_tipo) 			$filtro['b_tipo'] = $tf_b_tipo;
			if($tf_b_ativo) 		$filtro['b_ativo'] = $tf_b_ativo;
			if($tf_b_titulo) 		$filtro['b_tituloLike'] = $tf_b_titulo;
			if($tf_b_data_expira_ini && $tf_b_data_expira_fim) 
			{
				$filtro['b_data_expiraMin'] = $tf_b_data_expira_ini;
				$filtro['b_data_expiraMax'] = $tf_b_data_expira_fim;
			}
			if($tf_b_data_inicio_ini && $tf_b_data_inicio_fim) 
			{
				$filtro['b_data_inicioMin'] = $tf_b_data_inicio_ini;
				$filtro['b_data_inicioMax'] = $tf_b_data_inicio_fim;
			}
			$rs_banners = null;
                        $instBanner = new Banner();
			$ret = $instBanner->obter($filtro, null, $rs_banners);
			if($ret != "") 
				$msg = $ret;
			else 
			{
				$total_table = pg_num_rows($rs_banners);

				if($total_table == 0)
					$msg = "Nenhum banner encontrado.\n"; 
				else 
				{
					//Ordem
					$orderBy = $ncamp;
					if($ordem == 1)
					{
						$orderBy .= " desc ";
						$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
					} 
					else 
					{
						$orderBy .= " asc ";
						$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
					}
			
					$orderBy .= " limit " . $max; 
					$orderBy .= " offset " . $inicial;
                                        $instBanner = new Banner();
					$ret = $instBanner->obter($filtro, $orderBy, $rs_banners);
					if($ret != "") 
						$msg = $ret;
					else 
					{
						if($max + $inicial > $total_table)
							$reg_ate = $total_table;
						else
							$reg_ate = $max + $inicial;
					}
				}
			}
				
		}
	}
	ob_end_flush();
?>
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_b_data_inicio_ini','tf_b_data_inicio_fim',optDate);
        setDateInterval('tf_b_data_expira_ini','tf_b_data_expira_fim',optDate);
        
    });
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table fontsize-pp txt-preto">
  <tr> 
    <td>
        <form name="form1" method="post" action="com_pesquisa_banners.php">
        <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr> 
            <td colspan="5">
				<table width="894" border="0" cellpadding="0" cellspacing="0" dwcopytype="CopyTableCell">
					<tr> 
					    <td width="894">
							<b>Money Distribuidor - Pesquisa de Banners de Promoções</b>
                        </td>
                    </tr>
		      </table>
			</td>
          </tr>
		</table>
            <table class="table top20" cellpadding="0" cellspacing="2">
                <tr bgcolor="#F5F5FB"> 
                    <td><a href="com_banner_detalhe_insere.php" class="btn btn-info btn-sm">Cadastrar Banner</a></td>
                    <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
                </tr>
            </table>
        			<table class="table" cellpadding="0" cellspacing="2">
          				<tr bgcolor="#FFFFFF"> 
            				<td colspan="6" bgcolor="#ECE9D8" class="texto">Promoção</font></td>
          				</tr>
          				<tr bgcolor="#F5F5FB"> 
            				<td width="100" class="texto">Código</font></td>
            				<td>
              					<input name="tf_b_id" type="text" class="form2" value="<?php echo $tf_b_id ?>" size="7" maxlength="7">
							</td>
            				<td class="texto">Conteúdo</font></td>
							<td>
								<select name="tf_b_conteudo" class="form-control">
									<option value="" <?php if($tf_b_conteudo == "") echo "selected" ?>>Selecione</option>
									<option value="0" <?php if ($tf_b_conteudo == "0") echo "selected";?>>Imagem</option>
									<option value="1" <?php if ($tf_b_conteudo == "1") echo "selected";?>>Texto</option>
								</select>
							</td>
          				</tr>
          				<tr bgcolor="#F5F5FB"> 
            				<td class="texto">Nome</font></td>
            				<td>
              					<input name="tf_b_nome" type="text" class="form2" value="<?php echo $tf_b_nome ?>" size="25" maxlength="100">
							</td>
            				<td class="texto">Título</font></td>
            				<td>
              					<input name="tf_b_titulo" type="text" class="form2" value="<?php echo $tf_b_titulo ?>" size="25" maxlength="100">
							</td>
		  				</tr>
          				<tr bgcolor="#F5F5FB"> 
                        	<td class="texto">Data de Início</font></td>
            				<td class="texto">
              					<input name="tf_b_data_inicio_ini" type="text" class="form" id="tf_b_data_inicio_ini" value="<?php echo $tf_b_data_inicio_ini ?>" size="9" maxlength="10">
              					a 
              					<input name="tf_b_data_inicio_fim" type="text" class="form" id="tf_b_data_inicio_fim" value="<?php echo $tf_b_data_inicio_fim ?>" size="9" maxlength="10">
							</td>
            				<td class="texto">Data de Expirar</font></td>
            				<td class="texto">
              					<input name="tf_b_data_expira_ini" type="text" class="form" id="tf_b_data_expira_ini" value="<?php echo $tf_b_data_expira_ini ?>" size="9" maxlength="10">
              					a 
              					<input name="tf_b_data_expira_fim" type="text" class="form" id="tf_b_data_expira_fim" value="<?php echo $tf_b_data_expira_fim ?>" size="9" maxlength="10">
							</td>
          				</tr>
                        <tr bgcolor="#F5F5FB"> 
                            <td class="texto">Status</font></td>
							<td>
								<select name="tf_b_status" class="form-control">
									<option value="" <?php if(!($tf_b_status == "1" || $tf_b_status == "-1")) echo "selected" ?>>Selecione</option>
									<option value="1" <?php if ($tf_b_status == "1") echo "selected";?>>Ativo</option>
									<option value="-1" <?php if ($tf_b_status == "-1") echo "selected";?>>Inativo</option>
								</select>
							</td>
                            <td class="texto">Tipo</font></td>
							<td>
								<select name="tf_b_tipo" class="form-control">
									<option value="" <?php if($tf_b_tipo == "") echo "selected" ?>>Selecione</option>
									<option value="0" <?php if ($tf_b_tipo == "0") echo "selected";?>>Gamers</option>
									<option value="1" <?php if ($tf_b_tipo == "1") echo "selected";?>>Lan Houses</option>
                                    <option value="2" <?php if ($tf_b_tipo == "2") echo "selected";?>>Todos</option>
								</select>
							</td>
          				</tr>
          			</table>
                    <table class="table">
          				<tr bgcolor="#F5F5FB"> 
            				<td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          				</tr>
          				<?php if($msg != "") { ?>
                        	<tr class="texto">
                            	<td align="center">
                                	<br><br>
                                    <font color="#FF0000">
										<?php echo $msg ?>
                                    </font>
                               	</td>
                           	</tr>
						<?php } ?>
					</table>
					</form>
					<?php if($total_table > 0) { ?>
                        <table class="table" cellpadding="0" cellspacing="2">
                			<tr bgcolor="#00008C"> 
                  				<td height="11" colspan="3" bgcolor="#FFFFFF"> 
                                    <table class="table">
				  	  					<tr> 
											<td colspan="20" class="texto"> 
                          						Exibindo resultados 
                                                <strong><?php echo $inicial + 1 ?></strong> 
                          						a 
                                                <strong><?php echo $reg_ate ?></strong> 
                                                de 
                                                <strong><?php echo $total_table ?></strong></font>
                                          	</td>
					  					</tr>
					  					<?php $ordem = ($ordem == 1)?2:1; ?>
                      					<tr  bgcolor="#ECE9D8"> 
                        					<td align="center">
                                            	<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=b_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.</a> 
                          						<?php if($ncamp == 'b_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          						</strong>
                                          	</td>
                        					<td align="center">
                                            	<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=b_data_inicio&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Início</font></a>
                          						<?php if($ncamp == 'b_data_inicio') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          						</strong>
                                          	</td>
                                            <td align="center">
                                            	<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=b_data_expira&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Fim</font></a>
                          						<?php if($ncamp == 'b_data_expira') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          						</strong>
                                          	</td>
                        					<td align="center">
                                            	<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=b_ativo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Status</font></a>
                          						<?php if($ncamp == 'b_ativo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          						</strong>
                                           	</td>
                        					<td align="center">
                                            	<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=b_nome&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Nome</font></a>
                          						<?php if($ncamp == 'b_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          						</strong>
                                           	</td>
                                            <td align="center">
                                            	<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=b_tipo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Tipo</font></a>
                          						<?php if($ncamp == 'b_tipo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          						</strong>
                                           	</td>
                                            <td align="center">
                                            	<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=b_clicks&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Acessos</font></a>
                          						<?php if($ncamp == 'b_clicks') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          						</strong>
                                           	</td>
                                            <td align="center">
                                            	<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=b_conteudo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Conteúdo</font></a>
                          						<?php if($ncamp == 'b_conteudo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          						</strong>
                                           	</td>
                                            <td align="center">
                                            	<strong>
                                                	<font class="texto">Ativo no site</font>
                          						</strong>
                                           	</td>
										</tr>
										<?php
											$cor1 = $query_cor1;
											$cor2 = $query_cor1;
											$cor3 = $query_cor2;
											while($rs_banners_row = pg_fetch_array($rs_banners))
											{
												$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
												$status = ($rs_banners_row['b_ativo'] == 1)?"Ativo":"Inativo";
												$tipo = ($rs_banners_row['b_tipo'] == 0)?"Gamer":(($rs_banners_row['b_tipo'] == 1)?"Lan House":"Todos");
												$conteudo = ($rs_banners_row['b_conteudo'] == 0)?"Texto":(($rs_banners_row['b_conteudo'] == 1)?"Imagem":"URL");
												
												$sql_ativo = pg_query("select b_id from tb_promocoes where b_data_inicio <= current_date and b_data_expira >= current_date and b_id = " . $rs_banners_row["b_id"] . " and b_ativo = 1");
												$ativo_site = (pg_num_rows($sql_ativo) != 0) ? "Promoção atualmente <font color=\"blue\"><b>ativa</b></font> no site" : "Promoção atualmente <font color=\"red\"><b>inativa</b></font> no site";
										?>
                      					<tr bgcolor="<?php echo $cor1 ?>"> 
                        					<td class="texto" align="center"><a style="text-decoration:none" href="com_banner_detalhe.php?banner_id=<?php echo $rs_banners_row['b_id'] ?>"><?php echo $rs_banners_row['b_id'] ?></a></td>
                        					<td class="texto" align="center"><?php echo formata_data($rs_banners_row['b_data_inicio'],0) ?></td>
                                            <td class="texto" align="center"><?php echo formata_data($rs_banners_row['b_data_expira'],0) ?></td>
                        					<td class="texto" align="center"><?php echo $status ?></td>
                        					<td class="texto"><a style="text-decoration:none" href="com_banner_detalhe.php?banner_id=<?php echo $rs_banners_row['b_id'] ?>"><?php echo $rs_banners_row['b_nome'] ?></a></td>
                        					<td class="texto" align="center"><?php echo $tipo ?></td>
                                            <td class="texto" align="center"><?php echo $rs_banners_row['b_clicks'] ?></td>
                                         	<td class="texto" width="70" align="center"><?php echo $conteudo ?></td>
                                            <td class="texto" width="190" align="center"><?php echo $ativo_site ?></td>      
                      					</tr>
										<?php } ?>
                      				<tr> 
                        				<td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
                      				</tr>
									<?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
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