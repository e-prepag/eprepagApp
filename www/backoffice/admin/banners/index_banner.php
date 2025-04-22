<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto.'sftp/connect.php';
require_once $raiz_do_projeto.'sftp/classSFTPconnection.php';

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
	$ext	= explode('/',$_FILES['bds_banner']['type']);

	if(in_array($ext[1],$formatos)) {
		$pasta = DIR_WEB."imagens/banners/";
		if(file_exists("$pasta".$_FILES["bds_banner"]["name"])){
			$msg .= "<span class='txt-vermelho'>Imagem de Banner já existe com este mesmo nome.<br>Favor, renomear antes.</span><br>";
			$bds_banner = null;
		}
		else {
			move_uploaded_file($_FILES["bds_banner"]["tmp_name"],"$pasta".$_FILES["bds_banner"]["name"]);
			$bds_banner = $_FILES["bds_banner"]["name"];
                        $nome_arquivo = $bds_banner;
                        $arquivo = $pasta . $_FILES["bds_banner"]["name"];
                        if(SFTP_TRANSFER && file_exists($arquivo)){
                            $arq = trim(str_replace('/', '\\', $arquivo));
                            //enviar para os servidores via sFTP
                            $sftp = new SFTPConnection($server, $port);
                            $sftp->login($user, $pass);
                            $sftp->uploadFile($arquivo, "E-Prepag/www/web/prepag2/commerce/images/banners/".$nome_arquivo);

                            //$msg .= "<br><br>Imagem de produto enviada ao servidor Windows 2003";

                        }
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
							to_date('$bds_data_inicio','DD/MM/YYYY'), 
							to_date('$bds_data_fim','DD/MM/YYYY'), 
							$bds_tipo,
							'$bds_usuario_bko',
							'".str_replace("'",'"',$bds_nome_update)."', 
							'$bds_tipo_usuario',
							";
	if (empty($bds_banner)) {
		$sql .= "NULL,";
	}
	else {
		$sql .= "'".$bds_banner."',";
	}
	if (empty($bds_ids_inclusao)) {
		$sql .= "NULL,";
	}
	else {
		$sql .= "'".trim($bds_ids_inclusao)."',";
	}
	if (empty($bds_ids_exclusao)) {
		$sql .= "NULL,";
	}
	else {
		$sql .= "'".trim($bds_ids_exclusao)."',";
	}
	if (empty($bds_link)) {
		$sql .= "NULL,";
	}
	else {
		$sql .= "'".trim($bds_link)."',";
	}
	if (empty($bds_ativo)) {
		$sql .= "0);";
	}else {
		$sql .= "1);";
	}
	//echo $sql."<br>";
	$rs_banner = SQLexecuteQuery($sql);
	if(!$rs_banner) {
		$msg .= "Erro ao salvar informa&ccedil;&otilde;es da question&aacute;rio. ($sql)<br>";
	}
	$acao = 'listar';
}//end if($acao == 'inserir')

if($acao == 'atualizar')
{
	if(!empty($vetor_ordem)) {	}//end if(!empty($vetor_ordem))
	
	if(!empty($_FILES["bds_banner"]["name"])) {
		$ext	= explode('/',$_FILES['bds_banner']['type']);
		$pasta = DIR_WEB."imagens/banners/";
		if(file_exists("$pasta".$_FILES["bds_banner"]["name"])){
			$msg .= "<span class='txt-vermelho'>Imagem de Banner já existe com este mesmo nome.<br>Favor, renomear antes.</span><br>";
			$bds_banner = null;
		}
		else {
			move_uploaded_file($_FILES["bds_banner"]["tmp_name"],"$pasta".$_FILES["bds_banner"]["name"]);
			$bds_banner = $_FILES["bds_banner"]["name"];
                        $nome_arquivo = $bds_banner;
                        $arquivo = $pasta . $_FILES["bds_banner"]["name"];
                        if(SFTP_TRANSFER && file_exists($arquivo)){
                            $arq = trim(str_replace('/', '\\', $arquivo));
                            //enviar para os servidores via sFTP
                            $sftp = new SFTPConnection($server, $port);
                            $sftp->login($user, $pass);
                            $sftp->uploadFile($arquivo, "E-Prepag/www/web/prepag2/commerce/images/banners/".$nome_arquivo);

                            //$msg .= "<br><br>Imagem de produto enviada ao servidor Windows 2003";

                        }
		}
		if(!in_array($ext[1],$formatos)) {
			$msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
		}
	}
	$sql = "UPDATE tb_banner_drop_shadow SET
						bds_texto					= '".str_replace("'",'"',$bds_nome_update)."',
						bds_data_inicio				= to_date('".$bds_data_inicio."','DD/MM/YYYY'),           
						bds_data_fim				= to_date('".$bds_data_fim."','DD/MM/YYYY'),
						bds_tipo					= $bds_tipo,
						bds_tipo_usuario			= '$bds_tipo_usuario',
						bds_usuario_bko_responsavel	= '$bds_usuario_bko',
						bds_lista_ids_inclusao		= '".trim($bds_ids_inclusao)."',
						bds_lista_ids_exclusao		= '".trim($bds_ids_exclusao)."',";
	if (!empty($bds_banner)) {
		$sql .= "		bds_imagem_banner			= '".$bds_banner."',";
	}
	if (!empty($bds_link)) {
		$sql .= "		bds_link					= '".$bds_link."',";
	}
	if (empty($bds_ativo)) {
		$sql .= "		bds_ativo					= '0'";
	}else {
		$sql .= "		bds_ativo					= '1'";
	}
	$sql .= "	WHERE	bds_id_banner				= $bds_id_update";
	//echo $sql."<br>:SQL<br>";
	$rs_banner = SQLexecuteQuery($sql);
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
			WHERE bds_id_banner = $bds_id"; 
	//echo $sql."<br>";
	$rs_banner = SQLexecuteQuery($sql);
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
		if (pg_num_rows($rs_banner) > 0)
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
//echo $msg;
?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>