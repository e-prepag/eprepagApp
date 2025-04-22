<?php 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

?>
<?php

	$msg = "";

	if(!$banner_id && $tipo_img)
		if(!is_numeric($banner_id)) $msg = "Código do banner inválido.\n"; 
	else 
		$msg = "Código do banner não fornecido.\n";

	//Processa acoes
	if($msg == "")
	{
		if($BtnConcluir) 
		{
			//Validacao
			$msg = "";
		
			//Valida arquivo
			if($msg == "")
			{
				$fileSource = $_FILES['arquivo']['tmp_name']; 
				if (($fileSource == 'none') || ($fileSource == '' )) $msg = "Nenhum arquivo fornecido.\n";
			}
			
			//Valida extensao
			if($msg == "")
			{
				$fileExtensao = strtolower(substr($_FILES['arquivo']['name'], -3)); 
				if (!in_array($fileExtensao, $GLOBALS['IMAGES_BANNER_EXTENSOES'])) $msg = "Extensão de arquivo inválida.\n";
			}
						
			//Salva arquivo
			if($msg == "")
			{
				if($banner_id && $tipo_img)
					if ($tipo_img == "B")
						$fileDest_nome = "bb" . $banner_id . "." . $fileExtensao;
					else if ($tipo_img == "C")
					 	$fileDest_nome = "bc" . $banner_id . "." . $fileExtensao;

				$fileDest = $GLOBALS['FIS_DIR_IMAGES_BANNER'] . $fileDest_nome; 

				if (!move_uploaded_file($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
				else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.\n";
                                else{
                                    $nome_arquivo = $fileDest_nome;
                                    $arquivo = $fileDest;
                                    if(SFTP_TRANSFER && file_exists($arquivo)){
                                        $arq = trim(str_replace('/', '\\', $arquivo));
                                        //enviar para os servidores via sFTP
                                        $sftp = new SFTPConnection($server, $port);
                                        $sftp->login($user, $pass);
                                        $sftp->uploadFile($arquivo, "E-Prepag/www/web/prepag2/dist_commerce/images/banners/".$nome_arquivo);

                                        //$msg .= "<br><br>Imagem de produto enviada ao servidor Windows 2003";

                                    }
                                }
			}

			//atualiza base
			if($msg == "")
			{
				//atualiza banner
				if($banner_id && $tipo_img)
				{
					$sql = "update tb_promocoes set ";
					if ($tipo_img == "B")
						$sql .= "b_img_banner = '" . $fileDest_nome . "' ";
					else
						$sql .= "b_img_conteudo = '" . $fileDest_nome . "' ";
					$sql .= "where b_id = " . $banner_id;
					
					$ret = SQLexecuteQuery($sql);
					
					if(!$ret) $msg = "Erro ao atualizar banner.\n";				
				}
			}

			//fecha janela
			if($msg == "")
			{
				//redireciona a janela pai
				if($banner_id) 
					$strRedirect = "com_banner_detalhe.php?banner_id=" . $banner_id;
				?>
					<script> 
						if(window.opener) window.opener.location='<?php echo $strRedirect?>'; 
                   	</script>
					<script>
						window.close();
                    </script>
				<?php
			}	
		}
	}
?>
<html>
	<head>
		<title>REDE E-PREPAG - Upload</title>
		<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
		<META HTTP-EQUIV="EXPIRES" CONTENT="0">
		<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
		<link href="/css/css.css" rel="stylesheet" type="text/css">
	</head>
	<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
		<script>
			function fcnOnSubmit()
			{
				if(form1.arquivo.value=='')
				{
					alert('Arquivo não especificado');
					return false;
				}
			}
		</script>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
  			<tr>
    			<td>
					<form name="form1" method="post" action="<?php echo $php_self ?>" ENCTYPE="multipart/form-data" onSubmit="return fcnOnSubmit();">
					<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="F5F5FB">
						<?php if($msg != ""){ ?>
							<tr bgcolor="#FFFFFF"><td align="center" colspan="3">&nbsp;</td></tr>
							<tr bgcolor="#FFFFFF"><td colspan="3" align="center"><font face="Arial, Helvetica, sans-serif" size="2" color="#FF0000"><?php echo str_replace("\n", "<br>", $msg) ?></font></td></tr>
						<?php } ?>
						<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>
						<tr>
							<td align="center" colspan="3">
								<font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Arquivo:&nbsp;</font>
				  				<input type="file" name="arquivo" size="30">
							</td>
						</tr>
						<tr>
                        	<td align="center" colspan="3">&nbsp;</td>
                       	</tr>
						<tr>
							<td align="center" colspan="3">
								<input type="submit" name="BtnConcluir" value="Concluir" class="botao_search">
							</td>
						</tr>
      				</table>
					</form>
               	</td>
  			</tr>
		</table>
	</body>
</html>