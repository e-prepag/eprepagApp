<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once "/www/includes/bourls.php";
	$msg = "";

	if(!$banner_id) 
		$msg = "Código do banner não fornecido.\n";
	elseif(!is_numeric($banner_id)) 
		$msg = "Código do banner inválido.\n";

	//Processa acoes
	if($msg == "")
	{
		if($BtnAtualizar)
		{
			//cria objeto banner
			$banner = new Banner($banner_id, $b_nome, $b_texto_conteudo, $b_conteudo, $b_tipo, $b_ativo, null, null, $b_data_expira, $b_data_inicio, $b_titulo, $b_url, null);

			//valida campos e atualiza
                        $instBanner = new Banner();
			$msgAcao = $instBanner->atualizar($banner);
			if($msgAcao == "") 
				$msgAcao = "Atualizado com sucesso.";
		}
		
		if($acao)
		{
			//excluir imagem
			if($acao == "ei")
			{		
				if ($tipo == "B")
					$sql = "update tb_promocoes set b_img_banner = NULL where b_id = " . $banner_id;
				else if ($tipo == "C")
					$sql = "update tb_promocoes set b_img_conteudo = NULL where b_id = " . $banner_id;
				
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao atualizar banner.\n";
			}
		}
	}

	//Recupera o banner
	if($msg == "")
	{
		$filtro = array();
		$filtro['b_id'] = $banner_id;
		$rs_banner = null;
                $instBanner = new Banner();
		$ret = $instBanner->obter($filtro, null, $rs_banner);
		if($ret != "") 
			$msg = $ret;
		else if(!$rs_banner || pg_num_rows($rs_banner) == 0) 
			$msg = "Nenhum banner encontrado.\n";
		else 
		{
			$rs_banner_row 		= pg_fetch_array($rs_banner);
			$b_id 				= $rs_banner_row['b_id'];
			$b_nome 			= $rs_banner_row['b_nome'];
			$b_texto_conteudo 	= $rs_banner_row['b_texto_conteudo'];
			$b_conteudo 		= $rs_banner_row['b_conteudo'];
			$b_ativo 			= $rs_banner_row['b_ativo'];
			$b_tipo 			= $rs_banner_row['b_tipo'];
			$b_img_banner 		= $rs_banner_row['b_img_banner'];
			$b_img_conteudo 	= $rs_banner_row['b_img_conteudo'];
			$b_data_expira 		= $rs_banner_row['b_data_expira'];
			$b_data_inicio 		= $rs_banner_row['b_data_inicio'];
			$b_titulo 			= $rs_banner_row['b_titulo'];
			$b_clicks 			= $rs_banner_row['b_clicks'];
			$b_url 				= $rs_banner_row['b_url'];
		}
	}	
	
	//Recupera o relatório de acessos
	if($msg == "")
	{
		if($b_id && is_numeric($b_id)) 
		{
			$filtro = array();
			$filtro['b_id'] = $b_id;
			$rs_banner_relatorios = null;
                        $instBannerRelatorio = new BannerRelatorio();
			$ret = $instBannerRelatorio->obter($filtro, null, $rs_banner_relatorios);
			if($ret != "") 
				$msg = $ret;
		}
	}
	
	$msg = $msgAcao . $msg;
	
	$cor1 = "#F5F5FB";
	$cor2 = "#F5F5FB";
	$cor3 = "#FFFFFF"; 	

	ob_end_flush();
?>
		<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
		<script type="text/javascript">
			tinyMCE.init({
				mode : "textareas",
				theme : "advanced",
				plugins : "safari,pagebreak,layer,advhr,iespell,insertdatetime,preview,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
				
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,fontselect,fontsizeselect",
				
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,insertdate,inserttime,charmap,preview,fullscreen,|,forecolor,backcolor",
				
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
		
				content_css : "/js/tiny_mce/css/content.css",
		
				template_external_list_url : "/js/tiny_mce/lists/template_list.js",
				external_link_list_url : "/js/tiny_mce/lists/link_list.js",
				external_image_list_url : "/js/tiny_mce/lists/image_list.js",
				media_external_list_url : "/js/tiny_mce/lists/media_list.js",
		
				template_replace_values : {
					username : "Some User",
					staffid : "991234"
				},
				
				translate_mode : true,
				language : "pt"
			});
		</script>
		<!-- Editor -->
        <link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
        <script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
        <script language="javascript">
            $(function(){
               $("#b_data_inicio").datepicker();
               $("#b_data_expira").datepicker();
            });
            
			function GP_popupAlertMsg(msg) 
			{ //v1.0
				document.MM_returnValue = alert(msg);
			}
		
			function GP_popupConfirmMsg(msg) 
			{ //v1.0
				document.MM_returnValue = confirm(msg);
			}
		
			function abreUpload(banner_id,tipo)
			{
				url = "com_imagem_banner_upload.php?banner_id=" + banner_id + "&tipo_img=" + tipo;
				janela = window.open(url, 'upload','top=200,left=200,width=500,height=200');
			}
		</script>
		<div class="col-md-12">
            <ol class="breadcrumb top10">
                <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
                <li class="active">Pesquisa de banners</li>
            </ol>
        </div>
        <table class="table txt-preto">
  			<tr> 
    			<td width="891" valign="top"> 
        			<table width="894" border="0" cellpadding="0" cellspacing="2">
                    <tr> 
                      <td colspan="5">
                          <table width="894" border="0" cellpadding="0" cellspacing="0" dwcopytype="CopyTableCell">
                              <tr> 
                                  <td width="894">
                                      <b>Money Distribuidor - Banner de Promoção</b>
                                  </td>
                              </tr>
                              <tr bgcolor=""> 
                                  <td class="pull-right">
                                        <a href="com_pesquisa_banners.php"><img src="/images/voltar.gif" border="0"></a>&nbsp;&nbsp;
                                        <a href="index.php"><img src="/images/voltar_menu.gif" width="107" height="15" border="0"></a>		
                                  </td>
                              </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                  <?php if($msg != "") { ?>
        				<table width="894" border="0" cellpadding="0" cellspacing="2">
          					<tr>
                            	<td align="center"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"><?php echo str_replace("\n", "<br>", $msg) ?></font></td>
                           	</tr>
						</table>
					<?php } ?>
					<form name="form1" method="post" action="com_banner_detalhe.php">
						<input type="hidden" name="banner_id" value="<?php echo $banner_id ?>">
					<table cellpadding="0" cellspacing="1" class="table">
          				<tr bgcolor="#FFFFFF"> 
            				<td colspan="2" bgcolor="#ECE9D8">Banner</font></td>
          				</tr>
          				<tr bgcolor="#F5F5FB"> 
            				<td width="150"><b>C&oacute;digo</b></td>
            				<td><?php echo $b_id ?> (<a href="com_banner_detalhe_preview.php?b_id=<?php echo $b_id ?>" target="_blank">Preview</a>)</td>
          				</tr>
                        <tr bgcolor="#F5F5FB"> 
            				<td width="150"><b>Acessos</b></td>
            				<td><?php echo $b_clicks ?></td>
          				</tr>
                        <tr bgcolor="#F5F5FB"> 
            				<td width="150"><b>Data de Início</b></td>
                            <td><input name="b_data_inicio" type="text" class="form" id="b_data_inicio" value="<?php echo formata_data($b_data_inicio,0) ?>" size="9" maxlength="10" readonly></td>
          				</tr>
          				<tr bgcolor="#F5F5FB"> 
            				<td width="150"><b>Data de Expira</b></td>
                            <td><input name="b_data_expira" type="text" class="form" id="b_data_expira" value="<?php echo formata_data($b_data_expira,0) ?>" size="9" maxlength="10" readonly></td>
          				</tr>
          				<tr bgcolor="#F5F5FB"> 
            				<td width="150"><b>Imagem - Banner</b></td>
            				<td valign="middle">
								<a style="text-decoration:none" href="#" onClick="abreUpload('<?php echo $banner_id ?>','B'); return false;">Nova imagem</a>&nbsp;&nbsp;&nbsp;<b>Só utilize imagens no tamanho: 192 px de Largura por 46 px de Altura.</b><br>
								<?php if($b_img_banner && $b_img_banner != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_BANNER'] . $b_img_banner)){ ?>
									<img src="<?php echo $PREPAG_DOMINIO . $URL_DIR_IMAGES_BANNER . $b_img_banner ?>" border="0">
									<br><a style="text-decoration:none" href="#" onClick="if(confirm('Deseja excluir esta imagem?')) window.location='com_banner_detalhe.php?acao=ei&banner_id=<?php echo $banner_id ?>&tipo=B';return false;">Excluir imagem</a>
								<?php } ?>
							</td>
          				</tr>
          				<tr bgcolor="#F5F5FB"> 
            				<td><b>Status</b></td>
            				<td>
								<select name="b_ativo" class="form2">
									<option value="0" <?php if ($b_ativo == "0") echo "selected";?>>Inativo</option>
									<option value="1" <?php if ($b_ativo == "1") echo "selected";?>>Ativo</option>
								</select>
							</td>
          				</tr>
                        <tr bgcolor="#F5F5FB"> 
            				<td><b>Tipo</b></td>
            				<td>
								<select name="b_tipo" class="form2">
									<option value="0" <?php if ($b_tipo == "0") echo "selected";?>>GAMERS</option>
									<option value="1" <?php if ($b_tipo == "1") echo "selected";?>>LAN HOUSE</option>
                                    <option value="2" <?php if ($b_tipo == "2") echo "selected";?>>TODOS</option>
								</select>
							</td>
          				</tr>
          				<tr bgcolor="#F5F5FB"> 
            				<td><b>Nome</b></td>
            				<td><input name="b_nome" type="text" class="form2" value="<?php echo $b_nome ?>" size="50" maxlength="200"></td>
          				</tr>
                        <tr bgcolor="#F5F5FB"> 
            				<td><b>Título</b></td>
            				<td><input name="b_titulo" type="text" class="form2" value="<?php echo $b_titulo ?>" size="50" maxlength="200"></td>
          				</tr>
                        <tr bgcolor="#F5F5FB"> 
            				<td><b>Conteúdo</b></td>
            				<td>
								<select name="b_conteudo" class="form2">
									<option value="0" <?php if ($b_conteudo == "0") echo "selected";?>>0 - TEXTO</option>
									<option value="1" <?php if ($b_conteudo == "1") echo "selected";?>>1 - IMAGEM</option>
                                    <option value="2" <?php if ($b_conteudo == "2") echo "selected";?>>2 - URL</option>
                                    <option value="3" <?php if ($b_conteudo == "3") echo "selected";?>>3 - TEXTO + IMAGEM</option>
                                    <option value="4" <?php if ($b_conteudo == "4") echo "selected";?>>4 - IMAGEM + TEXTO</option>
								</select>
							</td>
          				</tr>
                        <tr bgcolor="#F5F5FB"> 
            				<td><b>URL</b></td>
            				<td><b>http://</b>&nbsp;<input name="b_url" type="text" class="form2" value="<?php echo $b_url ?>" size="50" maxlength="200"></td>
          				</tr>
          				<tr bgcolor="#F5F5FB"> 
            				<td><b>Conteúdo em Texto</b></td>
            				<td><textarea name="b_texto_conteudo" id="b_texto_conteudo" class="widgEditor nothing" cols="80" rows="30"><?php echo $b_texto_conteudo ?></textarea></td>
          				</tr>
                        <tr bgcolor="#F5F5FB"> 
            				<td width="150"><b>Imagem - Conteúdo</b></td>
            				<td valign="middle">
								<a style="text-decoration:none" href="#" onClick="abreUpload('<?php echo $banner_id ?>','C'); return false;">Nova imagem</a>&nbsp;&nbsp;&nbsp;<b>Só utilize imagens no tamanho: 500 px de Largura por 400 px de Altura.</b><br>
								<?php if($b_img_conteudo && $b_img_conteudo != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_BANNER'] . $b_img_conteudo)){ ?>
									<img src="<?php echo $PREPAG_DOMINIO . $URL_DIR_IMAGES_BANNER . $b_img_conteudo ?>" border="0">
									<br><a style="text-decoration:none" href="#" onClick="if(confirm('Deseja excluir esta imagem?')) window.location='com_banner_detalhe.php?acao=ei&banner_id=<?php echo $banner_id ?>&tipo=C';return false;">Excluir imagem</a>
								<?php } ?>
							</td>
          				</tr>
					</table>
        			<table cellpadding="0" cellspacing="1" class="table">
		  				<tr bgcolor="#F5F5FB">
							<td align="right"><input type="submit" name="BtnAtualizar" value="Atualizar" class="botao_search"></td>
		  				</tr>
					</table>
					</form>
        			<table cellpadding="0" cellspacing="1" class="table">
          				<tr bgcolor="#FFFFFF">
            				<td colspan="2" bgcolor="#ECE9D8">Relatório de Acessos</font></td>
          				</tr>
          				<tr>
		  					<td>
                                <table cellpadding="0" cellspacing="1" class="table">
									<tr bgcolor="F0F0F0" class="texto">
                                    	<td align="center" width="100"><b>Data</b></td>
                                      	<td align="center" width="100"><b>Hora</b></td>
                                        <td align="center" width="100"><b>Tipo</b></td>
                                        <td align="center" width="100"><b>Código</b></td>
                                      	<td align="center"><b>Gamer / Lan House</b></td>
									</tr>
									<?php
										if($rs_banner_relatorios)
											while ($rs_banner_relatorios_row = pg_fetch_array($rs_banner_relatorios))
											{
												$br_codigo = ($rs_banner_relatorios_row["br_tipo_usuario"] == 0) ? "br_codigo_dist" : "br_codigo";
												$br_nome = ($rs_banner_relatorios_row["br_tipo_usuario"] == 0) ? ($rs_banner_relatorios_row["br_nome_dist"] == "" ?  "br_nome_fantasia_dist" : "br_nome_dist") : "br_nome";
												$br_tipo = ($rs_banner_relatorios_row["br_tipo_usuario"] == 0) ? "Lan House" : "Gamer";
									?>
										<tr class="texto" bgcolor="#F5F5FB">
					  						<td align="center"><?php echo $rs_banner_relatorios_row['br_data'] ?></td>
					  						<td align="center"><?php echo $rs_banner_relatorios_row['br_hora'] ?></td>
                                            <td align="center"><?php echo $br_tipo ?></td>
                                            <td align="center">
												<?php
													if ($rs_banner_relatorios_row["br_tipo_usuario"] == 0)
														echo "<a href=\"/pdv/usuarios/com_usuario_detalhe.php?usuario_id=" . $rs_banner_relatorios_row[$br_codigo] . "\" style=\"text-decoration:none\">";
													else
														echo "<a href=\"/gamer/usuarios/com_usuario_detalhe.php?usuario_id=" . $rs_banner_relatorios_row[$br_codigo] . "\" style=\"text-decoration:none\">";
													echo $rs_banner_relatorios_row[$br_codigo] . "</a>";
                                                ?>
                                            </td>
					  						<td align="center">
                                           		<?php
													if ($rs_banner_relatorios_row["br_tipo_usuario"] == 0)
														echo "<a href=\"/pdv/usuarios/com_usuario_detalhe.php?usuario_id=" . $rs_banner_relatorios_row[$br_codigo] . "\" style=\"text-decoration:none\">";
													else
														echo "<a href=\"/gamer/usuarios/com_usuario_detalhe.php?usuario_id=" . $rs_banner_relatorios_row[$br_codigo] . "\" style=\"text-decoration:none\">";
													echo $rs_banner_relatorios_row[$br_nome] . "</a>";
                                            	?>
                                        	</td>
										</tr>
									<?php } ?>
								</table>
							</td>
		  				</tr>
					</table>
    			</td>
  			</tr>
		</table>
    </td>
  </tr>
</table>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</html>