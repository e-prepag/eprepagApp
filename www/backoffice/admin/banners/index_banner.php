<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);  // Exibe todos os tipos de erros
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
// [SFTP DESATIVADO] require_once $raiz_do_projeto.'sftp/connect.php';
// [SFTP DESATIVADO] require_once $raiz_do_projeto.'sftp/classSFTPconnection.php';

$bds_banner = isset($_FILES["bds_banner"]["name"]) ? $_FILES["bds_banner"]["name"] : null;

$acao	= isset($_REQUEST['acao']) ? $_REQUEST['acao'] : 'listar';

$msg	= "";

$formatos = array('jpg','jpeg','gif','png');

$tipos = array(
				'1' => "Obrigat&oacute;ria",
				'2' => "Simples",
				);

$tipos_usuarios = array(
				'L' => "Usu&aacute;rios Lan House",
				'G' => "Usu&aacute;rios Gamers",
				);

if(isset($_SESSION['userlogin_bko']) && !is_null($_SESSION['userlogin_bko'])){
	$bds_usuario_bko = strtoupper($_SESSION['userlogin_bko']);
}
if($acao == 'inserir')
{
	$ext	= explode('/', isset($_FILES['bds_banner']['type']) ? $_FILES['bds_banner']['type'] : '');

	if(isset($ext[1]) && in_array($ext[1],$formatos)) {
		$pasta = "/www/arquivos_gerados/imagens/banners/";
		if(file_exists("$pasta".$_FILES["bds_banner"]["name"])){
			$msg .= "<span class='txt-vermelho'>Imagem de Banner já existe com este mesmo nome.<br>Favor, renomear antes.</span><br>";
			$bds_banner = null;
		}
		else {
			move_uploaded_file($_FILES["bds_banner"]["tmp_name"],"$pasta".$_FILES["bds_banner"]["name"]);
		}
	}
	//else $msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
	
	$sql = "INSERT INTO tb_banner_drop_shadow (
							bds_data_inicio, 
							bds_data_fim, 
							bds_tipo, 
							bds_usuario_bko_responsavel,
							bds_texto,
							bds_tipo_usuario,
							bds_imagem_banner,
							bds_lista_ids_inclusao,
							bds_lista_ids_exclusao,
							bds_link,
							bds_ativo
							) 
					VALUES (
							to_date($1,'DD/MM/YYYY'), 
							to_date($2,'DD/MM/YYYY'), 
							$3,
							$4,
							$5, 
							$6,
							$7,
							$8,
							$9,
							$10,
							$11);";
	$params_banner = array(
		$bds_data_inicio,
		$bds_data_fim,
		$bds_tipo,
		$bds_usuario_bko,
		str_replace("'",'"',$bds_nome_update),
		$bds_tipo_usuario,
		empty($bds_banner) ? null : $bds_banner,
		empty($bds_ids_inclusao) ? null : trim((string)($bds_ids_inclusao ?? '')),
		empty($bds_ids_exclusao) ? null : trim((string)($bds_ids_exclusao ?? '')),
		empty($bds_link) ? null : trim((string)($bds_link ?? '')),
		empty($bds_ativo) ? 0 : 1
	);
	//echo $sql."<br>";
	$rs_banner = SQLexecuteQueryParams($sql, $params_banner);
	if(!$rs_banner) {
		$msg .= "Erro ao salvar informa&ccedil;&otilde;es da question&aacute;rio. ($sql)<br>";
	}
	$acao = 'listar';
}//end if($acao == 'inserir')

if($acao == 'atualizar')
{
	if(!empty($vetor_ordem)) {	}//end if(!empty($vetor_ordem))
	
	if(!empty($_FILES["bds_banner"]["name"])) {
		$ext	= explode('/', isset($_FILES['bds_banner']['type']) ? $_FILES['bds_banner']['type'] : '');
		$pasta = "/www/arquivos_gerados/imagens/banners/";
		if(file_exists("$pasta".$_FILES["bds_banner"]["name"])){
			$msg .= "<span class='txt-vermelho'>Imagem de Banner já existe com este mesmo nome.<br>Favor, renomear antes.</span><br>";
			$bds_banner = null;
		}
		else {
			move_uploaded_file($_FILES["bds_banner"]["tmp_name"],"$pasta".$_FILES["bds_banner"]["name"]);
		}
		if(!isset($ext[1]) || !in_array($ext[1],$formatos)) {
			$msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
		}
	}
	$params_banner = array(
		str_replace("'",'"',$bds_nome_update),
		$bds_data_inicio,
		$bds_data_fim,
		$bds_tipo,
		$bds_tipo_usuario,
		$bds_usuario_bko,
		trim((string)($bds_ids_inclusao ?? '')),
		trim((string)($bds_ids_exclusao ?? ''))
	);
	$sql = "UPDATE tb_banner_drop_shadow SET
						bds_texto					= $1,
						bds_data_inicio				= to_date($2,'DD/MM/YYYY'),           
						bds_data_fim				= to_date($3,'DD/MM/YYYY'),
						bds_tipo					= $4,
						bds_tipo_usuario			= $5,
						bds_usuario_bko_responsavel	= $6,
						bds_lista_ids_inclusao		= $7,
						bds_lista_ids_exclusao		= $8,";
	$param_index = 9;
	if (!empty($bds_banner)) {
		$sql .= "		bds_imagem_banner			= $".$param_index.",";
		$params_banner[] = $bds_banner;
		$param_index++;
	}
	if (!empty($bds_link)) {
		$sql .= "		bds_link					= $".$param_index.",";
		$params_banner[] = $bds_link;
		$param_index++;
	}
	$sql .= "		bds_ativo					= $".$param_index;
	$params_banner[] = empty($bds_ativo) ? 0 : 1;
	$param_index++;
	$sql .= "	WHERE	bds_id_banner				= $".$param_index;
	$params_banner[] = $bds_id_update;
	//echo $sql."<br>:SQL<br>";
	$rs_banner = SQLexecuteQueryParams($sql, $params_banner);
	if(!$rs_banner) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es da question&aacute;rio. ($sql)<br>";
	}
	else {
		$msg .= "Sucesso ao atualizar informa&ccedil;&otilde;es da question&aacute;rio ID:($bds_id_update).<br>";
	}

	//isset($_REQUEST['bds_id']);
	$acao = 'listar';
}

if($acao == 'editar')
{
    $sql = "SELECT 
					bds_id_banner,
					to_char(bds_data_inicio,'DD/MM/YYYY') as bds_data_inicio,
					to_char(bds_data_fim,'DD/MM/YYYY') as bds_data_fim,
					bds_tipo,
					bds_lista_ids_inclusao,
					bds_lista_ids_exclusao,
					bds_ativo,
					bds_usuario_bko_responsavel,
					bds_imagem_banner,
					bds_texto,
					bds_link,
					bds_tipo_usuario
			FROM tb_banner_drop_shadow 
			WHERE bds_id_banner = $1"; 
	//echo $sql."<br>";
	$rs_banner = SQLexecuteQueryParams($sql, array($bds_id));
	if(!($rs_banner_row = pg_fetch_array($rs_banner))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es da question&aacute;rio. ($sql)<br>";
	}
	else {
		$bds_id				= $rs_banner_row['bds_id_banner'];
		$bds_nome			= $rs_banner_row['bds_texto'];
		$bds_data_inicio	= $rs_banner_row['bds_data_inicio'];
		$bds_data_fim		= $rs_banner_row['bds_data_fim'];
		$bds_tipo			= $rs_banner_row['bds_tipo'];
		$bds_ids_inclusao	= $rs_banner_row['bds_lista_ids_inclusao'];
		$bds_ids_exclusao	= $rs_banner_row['bds_lista_ids_exclusao'];
		$bds_ativo			= $rs_banner_row['bds_ativo'];
		$bds_usuario_bko	= $rs_banner_row['bds_usuario_bko_responsavel'];
		$bds_banner			= $rs_banner_row['bds_imagem_banner'];
		$bds_tipo_usuario	= $rs_banner_row['bds_tipo_usuario'];
		$bds_link			= $rs_banner_row['bds_link'];
		if ((($rs_banner) ? pg_num_rows($rs_banner) : 0) > 0)
			include 'banner_edt.php';
		else
			$acao = 'listar';
	}
}

if($acao == 'novo')
{
    include 'banner_edt.php';
}

if($acao == 'listar')
{
    include 'banner_lst.php';
}
echo $msg;
?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>