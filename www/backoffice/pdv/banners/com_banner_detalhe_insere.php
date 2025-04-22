<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once "/www/includes/bourls.php";

	//Processa acoes
	if($msg == "")
	{
		if($BtnInserir)
		{
			//cria objeto banner
			$banner = new Banner($banner_id, $b_nome, $b_texto_conteudo, $b_conteudo, $b_tipo, $b_ativo, null, null, $b_data_expira, $b_data_inicio, $b_titulo, $b_url, null);

			//valida campos e insere
			$msgAcao = $banner->inserir($banner);
			if($msgAcao == "")
			{
				//redireciona
				$strRedirect = "com_banner_detalhe.php?banner_id=" . $banner->getId();
				ob_end_clean();
				?>
                	<html><body onLoad="window.location='<?php echo $strRedirect; ?>'">
				<?php
				exit;
			}
		}
	}

	//Mostra a pagina
	$msg = $msgAcao . $msg;
	
	$cor1 = "#F5F5FB";
	$cor2 = "#F5F5FB";
	$cor3 = "#FFFFFF"; 	

	ob_end_flush();
?>
		<!-- Editor -->
		<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
		<script type="text/javascript">
			tinyMCE.init({
				mode : "textareas",
				theme : "advanced",
				plugins : "safari,pagebreak,layer,advhr,iespell,insertdatetime,preview,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
				
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,fontselect,fontsizeselect",
				
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,insertdate,inserttime,charmap,preview,fullscreen,|,forecolor,backcolor",
				
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
		
				content_css : "../../editor/tiny_mce/css/content.css",
		
				template_external_list_url : "../../editor/tiny_mce/lists/template_list.js",
				external_link_list_url : "../../editor/tiny_mce/lists/link_list.js",
				external_image_list_url : "../../editor/tiny_mce/lists/image_list.js",
				media_external_list_url : "../../editor/tiny_mce/lists/media_list.js",
		
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
        <script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
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
		</script>
                <div class="col-md-12">
                    <ol class="breadcrumb top10">
                        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
                        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
                        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
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
                                            <b>Money Distribuidor - Inserir novo banner de promoção</b>
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
					<form name="form1" method="post" action="com_banner_detalhe_insere.php">
					<table border="0" cellpadding="0" cellspacing="1" class="table">
          				<tr bgcolor="#FFFFFF"> 
            				<td colspan="2" bgcolor="#ECE9D8">Banner</font></td>
          				</tr>
                        <tr bgcolor="#F5F5FB">
            				<td width="150"><b>Data de Início</b></td>
                            <td><input name="b_data_inicio" type="text" class="form" id="b_data_inicio" size="9" maxlength="10" readonly value="<?php echo date("d/m/Y"); ?>"></td>
          				</tr>
          				<tr bgcolor="#F5F5FB">
            				<td width="150"><b>Data de Expira</b></td>
                            <td><input name="b_data_expira" type="text" class="form" id="b_data_expira" size="9" maxlength="10" readonly value="<?php echo date("d/m/Y"); ?>"></td>
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
                                    <option value="1" <?php if ($b_tipo == "2") echo "selected";?>>TODOS</option>
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
									<option value="0" <?php if ($b_conteudo == "0") echo "selected";?>>TEXTO</option>
									<option value="1" <?php if ($b_conteudo == "1") echo "selected";?>>IMAGEM</option>
                                    <option value="1" <?php if ($b_conteudo == "2") echo "selected";?>>URL</option>
                                    <option value="3" <?php if ($b_conteudo == "3") echo "selected";?>>TEXTO + IMAGEM</option>
                                    <option value="4" <?php if ($b_conteudo == "4") echo "selected";?>>IMAGEM + TEXTO</option>
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
					</table>
					<table width="894" border="0" cellpadding="0" cellspacing="1" class="texto">
		  				<tr bgcolor="#F5F5FB">
							<td colspan="2" align="center"><input type="submit" name="BtnInserir" value="Inserir" class="botao_search"></td>
		  				</tr>
					</table>
					</form>
				</td>
  			</tr>
		</table>
		<?php
			require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
		?>
	</html>