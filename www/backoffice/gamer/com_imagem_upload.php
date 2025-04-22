<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

$webstring = "http://".$_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'];


require_once $raiz_do_projeto."includes/gamer/functions_vendaGames.php";
require_once $raiz_do_projeto."class/util/Imagem.class.php";
require_once $raiz_do_projeto."includes/inc_register_globals.php";
require_once $raiz_do_projeto.'sftp/connect.php';
require_once $raiz_do_projeto.'sftp/classSFTPconnection.php';
$msg = "";

if($produto_id){
        if(!is_numeric($produto_id)) $msg = "Código do produto inválido.\n";
} else if($modelo_id){
        if(!is_numeric($modelo_id)) $msg = "Código do modelo inválido.\n";
} else $msg = "Código do produto ou modelo não fornecido.\n";


//Processa acoes
//----------------------------------------------------------------------------------------------------------
if($msg == ""){

        if($BtnConcluir) {

                //Validacao
                $msg = "";

                //Valida arquivo
                if($msg == ""){
//				$fileSource = $HTTP_POST_FILES['arquivo']['tmp_name']; 
                        $fileSource = $_FILES['arquivo']['tmp_name'];
//echo "fileSource=$fileSource<br>";
                        if (($fileSource == 'none') || ($fileSource == '' )) $msg = "Nenhum arquivo fornecido.\n";
                }

                //Valida extensao
                if($msg == ""){
//				$fileExtensao = strtolower(substr($HTTP_POST_FILES['arquivo']['name'], -3)); 
                        $fileExtensao = strtolower(substr($_FILES['arquivo']['name'], -3)); 
                        if (!in_array($fileExtensao, $GLOBALS['IMAGES_PRODUTO_EXTENSOES'])) $msg = "Extensão de arquivo inválida.\n";
                }

                //Salva arquivo
                if($msg == ""){

                        if($produto_id)	$fileDest_nome = "p_" . $produto_id . "." . $fileExtensao; 
                        elseif($modelo_id) $fileDest_nome = "m_" . $modelo_id . "." . $fileExtensao; 

                        $fileDest = $GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $fileDest_nome;
                        
                        echo "$fileSource -&gt; $fileDest<br>";
                        if (!move_uploaded_file($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                        else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.\n";
                        else{
                            $nome_arquivo = $fileDest_nome;
                            $arquivo = $GLOBALS['FIS_DIR_IMAGES_PRODUTO'].$nome_arquivo;
                            if(SFTP_TRANSFER && file_exists($arquivo)){
                                $arq = trim(str_replace('/', '\\', $arquivo));
                                //enviar para os servidores via sFTP
                                $sftp = new SFTPConnection($server, $port);
                                $sftp->login($user, $pass);
                                $sftp->uploadFile($arquivo, "E-Prepag/www/web/prepag2/commerce/images/produtos/".$nome_arquivo);

                                //$msg .= "<br><br>Imagem de produto enviada ao servidor Windows 2003";

                            }
                        }
                }

                //atualiza base
                if($msg == ""){

                        //atualiza produto
                        if($produto_id){
                                $sql = "update tb_operadora_games_produto set ogp_nome_imagem = '" . $fileDest_nome . "'
                                                where ogp_id = " . $produto_id;
                                $ret = SQLexecuteQuery($sql);
                                if(!$ret) $msg = "Erro ao atualizar produto.\n";

                        //atualiza modelo
                        } elseif($modelo_id){
                                $sql = "update tb_operadora_games_produto_modelo set ogpm_nome_imagem = '" . $fileDest_nome . "'
                                                where ogpm_id = " . $modelo_id;
                                $ret = SQLexecuteQuery($sql);
                                if(!$ret) $msg = "Erro ao atualizar modelo.\n";

                        }
                }

                //fecha janela
                if($msg == ""){
                        $instImagem = new Imagem();
                        $instImagem->resize_img($fileDest, 205, NULL, TRUE);

                        //redireciona a janela pai
                        $msgSucess = "Sucesso";
                        if($produto_id)	$strRedirect = "/gamer/produtos/com_produto_detalhe.php?produto_id=" . $produto_id. "&msg=" . $msgSucess;
                        elseif($modelo_id) $strRedirect = "/gamer/produtos/com_modelo_detalhe.php?modelo_id=" . $modelo_id. "&msg=" . $msgSucess;
                        ?><script> if(window.opener) window.opener.location='<?php echo $strRedirect?>'; </script><?php

                        //fecha esta janela
                        ?><script> window.close(); </script><?php
                }

        }

}
?>
<html>
<head>
<title>E-Prepag - Upload</title>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<META HTTP-EQUIV="EXPIRES" CONTENT="0">
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<link href="http://<?php echo $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] ?>/css/css.css" rel="stylesheet" type="text/css">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<script>
function fcnOnSubmit(){

	if(form1.arquivo.value==''){
		alert('Arquivo não especificado');
		return false;
	}
	
}
</script>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td>
		<form name="form1" method="post" action="" ENCTYPE="multipart/form-data" onSubmit="return fcnOnSubmit();">
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
			<tr><td align="center" colspan="3">&nbsp;</td></tr>
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
