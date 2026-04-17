<?php

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
require_once $raiz_do_projeto . "class/util/Imagem.class.php";
// [SFTP DESATIVADO] require_once $raiz_do_projeto . 'sftp/connect.php';
// [SFTP DESATIVADO] require_once $raiz_do_projeto . 'sftp/classSFTPconnection.php';
$msg = "";

if ($produto_id) {
	if (!is_numeric($produto_id)) $msg = "Código do produto inválido.\n";
} else if ($modelo_id) {
	if (!is_numeric($modelo_id)) $msg = "Código do modelo inválido.\n";
} else $msg = "Código do produto ou modelo não fornecido.\n";


//Processa acoes
//----------------------------------------------------------------------------------------------------------
if ($msg == "") {

	if ($BtnConcluir) {

		$UPLOAD_DIR = realpath($GLOBALS['FIS_DIR_IMAGES_PRODUTO']);

		// Validar que o diretório existe e é gravável
		if (!$UPLOAD_DIR || !is_dir($UPLOAD_DIR) || !is_writable($UPLOAD_DIR)) {
			die("Erro: Diretório de upload inválido ou sem permissão de escrita");
		}

		// Extensões permitidas (whitelist)
		$ALLOWED_EXTENSIONS = array('jpg', 'jpeg', 'png', 'gif', 'webp');
		$ALLOWED_MIMES = array(
			'image/jpeg',
			'image/jpg',
			'image/png',
			'image/gif',
			'image/webp'
		);

		// Tamanho máximo (1MB)
		$MAX_FILE_SIZE = 1 * 1024 * 1024;

		$msg = "";
		if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] === UPLOAD_ERR_NO_FILE) {
			$msg = "Nenhum arquivo fornecido.";
		}
		if ($msg == "" && $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
			switch ($_FILES['arquivo']['error']) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$msg = "Arquivo muito grande.";
					break;
				case UPLOAD_ERR_PARTIAL:
					$msg = "Upload incompleto.";
					break;
				default:
					$msg = "Erro no upload do arquivo.";
			}
		}
		if ($msg == "" && $_FILES['arquivo']['size'] > $MAX_FILE_SIZE) {
			$msg = "Arquivo excede o tamanho máximo permitido (1MB).";
		}

		if ($msg == "" && $_FILES['arquivo']['size'] == 0) {
			$msg = "Arquivo está vazio.";
		}
		if ($msg == "") {
			$original_name = $_FILES['arquivo']['name'];

			// Remove caracteres nulos
			$original_name = str_replace(chr(0), '', $original_name);
			$original_name = str_replace("\0", '', $original_name);

			// Remove path traversal
			$original_name = basename($original_name);

			// Valida que não contém caracteres perigosos
			if (preg_match('/[^a-zA-Z0-9._-]/', $original_name)) {
				// Remove caracteres não seguros
				$original_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $original_name);
			}

			// Obtém extensão de forma segura
			$file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

			// Valida extensão contra whitelist
			if (!in_array($file_extension, $ALLOWED_EXTENSIONS, true)) {
				$msg = "Extensão de arquivo inválida. Permitidas: " . implode(', ', $ALLOWED_EXTENSIONS);
			}
		}
		if ($msg == "") {
			$file_source = $_FILES['arquivo']['tmp_name'];

			// Verifica se o arquivo temporário existe
			if (!file_exists($file_source) || !is_uploaded_file($file_source)) {
				$msg = "Arquivo temporário inválido.";
			}

			// Valida MIME type real do arquivo
			if ($msg == "") {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$detected_mime = finfo_file($finfo, $file_source);
				finfo_close($finfo);

				if (!in_array($detected_mime, $ALLOWED_MIMES, true)) {
					$msg = "Tipo de arquivo não permitido. Detectado: " . htmlspecialchars($detected_mime);
				}
			}

			// Valida estrutura da imagem
			if ($msg == "") {
				$image_info = @getimagesize($file_source);
				if ($image_info === false) {
					$msg = "Arquivo não é uma imagem válida.";
				}
			}
		}
		if ($msg == "") {
			// Valida produto_id ou modelo_id
			if (isset($produto_id) && is_numeric($produto_id) && $produto_id > 0) {
				$safe_id = intval($produto_id);
				$prefix = "p";
			} elseif (isset($modelo_id) && is_numeric($modelo_id) && $modelo_id > 0) {
				$safe_id = intval($modelo_id);
				$prefix = "m";
			} else {
				$msg = "ID de produto ou modelo inválido.";
			}
		}
		if ($msg == "") {
			$file_dest_name = $prefix . "_" . $safe_id . "." . $file_extension;

			if (!preg_match('/^[a-z0-9_.-]+$/i', $file_dest_name)) {
				$msg = "Nome de arquivo gerado é inválido.";
			}
		}
		if ($msg == "") {
			// Constrói caminho absoluto
			$file_dest_path = $UPLOAD_DIR . DIRECTORY_SEPARATOR . $file_dest_name;

			// Valida que o caminho final está dentro do diretório permitido
			$real_dest_path = realpath(dirname($file_dest_path));

			if ($real_dest_path === false || strpos($real_dest_path, $UPLOAD_DIR) !== 0) {
				$msg = "Caminho de destino inválido (tentativa de path traversal detectada).";

				error_log("SECURITY: Path traversal attempt detected - Dest: $file_dest_path");
			}
		}

		if ($msg == "") {
			// Remove arquivo anterior se existir
			if (file_exists($file_dest_path)) {
				@unlink($file_dest_path);
			}

			// Move arquivo
			if (!move_uploaded_file($file_source, $file_dest_path)) {
				$msg = "Não foi possível mover o arquivo para o diretório destino.";
				error_log("Upload failed: Could not move file to $file_dest_path");
			} else {
				// Valida que o arquivo foi copiado corretamente
				if (!file_exists($file_dest_path)) {
					$msg = "Arquivo não foi salvo no destino.";
				} elseif (filesize($file_dest_path) == 0) {
					$msg = "Arquivo salvo está vazio.";
					@unlink($file_dest_path); // Remove arquivo inválido
				} else {
					// Define permissões seguras
					chmod($file_dest_path, 0644);

					// Sucesso
					echo "Arquivo enviado com sucesso: " . htmlspecialchars($file_dest_name) . "<br>";
					echo "Tamanho: " . number_format(filesize($file_dest_path) / 1024, 2) . " KB<br>";
				}
			}
		}
		if ($msg != "") {
			// Limpa arquivo temporário se ainda existir
			if (isset($file_source) && file_exists($file_source)) {
				@unlink($file_source);
			}

			echo "<div style='color: red; font-weight: bold;'>";
			echo "Erro: " . htmlspecialchars($msg);
			echo "</div>";

			// Log do erro
			error_log("Upload error: " . $msg);
		}

		//atualiza base
		if ($msg == "") {

			//atualiza produto
			if ($produto_id) {
				$sql = "update tb_dist_operadora_games_produto set ogp_nome_imagem = $1
							where ogp_id = $2";
				$params = array($file_dest_name, $produto_id);
				$ret = SQLexecuteQueryParams($sql, $params);
				if (!$ret) $msg = "Erro ao atualizar produto.\n";

				//atualiza modelo
			} elseif ($modelo_id) {
				$sql = "update tb_dist_operadora_games_produto_modelo set ogpm_nome_imagem = $1
							where ogpm_id = $2";
				$params = array($file_dest_name, $modelo_id);
				$ret = SQLexecuteQueryParams($sql, $params);
				if (!$ret) $msg = "Erro ao atualizar modelo.\n";
			}
		}

		//fecha janela
		if ($msg == "") {
			$instImagem = new Imagem();
			$instImagem->resize_img($fileDest, 205, NULL, TRUE);

			//redireciona a janela pai
			$msgSucess = "Sucesso";
			if ($produto_id)	$strRedirect = "com_produto_detalhe.php?produto_id=" . $produto_id . "&msg=" . $msgSucess;
			elseif ($modelo_id) $strRedirect = "com_modelo_detalhe.php?modelo_id=" . $modelo_id . "&msg=" . $msgSucess;
?><script>
				if (window.opener) window.opener.location = '<?php echo $strRedirect ?>';
			</script><?php

						//fecha esta janela
						?><script>
				window.close();
			</script><?php
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
		function fcnOnSubmit() {

			if (form1.arquivo.value == '') {
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
						<?php if ($msg != "") { ?>
							<tr bgcolor="#FFFFFF">
								<td align="center" colspan="3">&nbsp;</td>
							</tr>
							<tr bgcolor="#FFFFFF">
								<td colspan="3" align="center">
									<font face="Arial, Helvetica, sans-serif" size="2" color="#FF0000"><?php echo str_replace("\n", "<br>", $msg) ?></font>
								</td>
							</tr>
						<?php } ?>
						<tr bgcolor="#FFFFFF">
							<td colspan="3">&nbsp;</td>
						</tr>
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