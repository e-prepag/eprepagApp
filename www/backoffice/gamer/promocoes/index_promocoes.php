<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto . "class/gamer/classPromocoes.php";

$teste ="wagner@e-prepag.com.br,1,78,2";
//echo $teste."<br>";
$teste = explode(',',$teste);
$token = new Promocoes();
$teste = $token -> BuscarPromocao($teste[0],$teste[1],$teste[2],$teste[3]);
//echo "<pre>".print_r($teste, true)."</pre>";
$acao	= isset($_REQUEST['acao']) ? $_REQUEST['acao'] : 'listar';

$msg	= "";

$formatos[] = 'jpg';
$formatos[] = 'jpeg';
$formatos[] = 'gif';
$formatos[] = 'png';
		
if($acao == 'inserir')
{
	$ext	= explode('/',$_FILES['promo_banner']['type']);

	if(in_array($ext[1],$formatos)) {
		$pasta = DIR_WEB."imagens/promocoes/";
		if(file_exists("$pasta".$_FILES["promo_banner"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor, renomear antes.<br>";
			$promo_banner = null;
		}
		else {
			move_uploaded_file($_FILES["promo_banner"]["tmp_name"],"$pasta".$_FILES["promo_banner"]["name"]);
			$promo_banner = $_FILES["promo_banner"]["name"];
		}
		$ext	= explode('/',$_FILES['promo_banner_resposta']['type']);
		if(file_exists("$pasta".$_FILES["promo_banner_resposta"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor renomear antes.<br>";
			$promo_banner_resposta = "";
		}
		else {
			move_uploaded_file($_FILES["promo_banner_resposta"]["tmp_name"],"$pasta".$_FILES["promo_banner_resposta"]["name"]);
			$promo_banner_resposta = $_FILES["promo_banner_resposta"]["name"];
		}
		if(!in_array($ext[1],$formatos)) {
			$msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
			$promo_banner_resposta = "";
		}
	
		$sql = "INSERT INTO promocoes (
								promo_nome, 
								opr_codigo, 
								promo_data_inicio, 
								promo_data_fim, 
								promo_descricao, 
								promo_banner,
								promo_label_banner,
								promo_valor,
								promo_pergunta,
								promo_resposta,
								promo_banner_resposta,
								promo_link_redir,
								promo_ativo
								) 
						VALUES (
								'$promo_nome_update', 
								$opr_codigo, 
								to_date('$promo_data_inicio','DD/MM/YYYY'), 
								to_date('$promo_data_fim','DD/MM/YYYY'), 
								'".str_replace("'",'"',$promo_descricao)."', 
								'$promo_banner',
								'".str_replace("'",'"',$promo_label_banner)."',";
		if (empty($promo_valor)) {
			$sql .= "NULL,";
		}
		else {
			$sql .= $promo_valor.",";
		}
		if (empty($promo_pergunta)) {
			$sql .= "NULL,";
		}
		else {
			$sql .= "'".$promo_pergunta."',";
		}
		if (empty($promo_resposta)) {
			$sql .= "NULL,";
		}
		else {
			$sql .= "'".$promo_resposta."',";
		}
		$sql .= "'".$promo_banner_resposta."',";
		if (empty($promo_link_redir)) {
			$sql .= "NULL,";
		}
		else {
			$sql .= "'".$promo_link_redir."',";
		}
		if (empty($promo_ativo)) {
			$sql .= "'0');";
		}else {
			$sql .= "'1');";
		}
		//echo $sql."<br>";
		$rs_promocoes = SQLexecuteQuery($sql);
		if(!$rs_promocoes) {
			$msg .= "Erro ao salvar informa&ccedil;&otilde;es da Promo&ccedil;&atilde;o. ($sql)<br>";
		}
	}
	else $msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
	$acao = 'listar';
}

if($acao == 'atualizar')
{
	if(!empty($_FILES["promo_banner"]["name"])) {
		$ext	= explode('/',$_FILES['promo_banner']['type']);
		$pasta = DIR_WEB."imagens/promocoes/";
		if(file_exists("$pasta".$_FILES["promo_banner"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor renomear antes.<br>";
			$promo_banner = null;
		}
		else {
			move_uploaded_file($_FILES["promo_banner"]["tmp_name"],"$pasta".$_FILES["promo_banner"]["name"]);
			$promo_banner = $_FILES["promo_banner"]["name"];
		}
		if(!in_array($ext[1],$formatos)) {
			$msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
		}
	}
	if(!empty($_FILES["promo_banner_resposta"]["name"])) {
		$ext	= explode('/',$_FILES['promo_banner_resposta']['type']);
		$pasta = DIR_WEB."imagens/promocoes/";
		if(file_exists("$pasta".$_FILES["promo_banner_resposta"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor renomear antes.<br>";
			$promo_banner_resposta = null;
		}
		else {
			move_uploaded_file($_FILES["promo_banner_resposta"]["tmp_name"],"$pasta".$_FILES["promo_banner_resposta"]["name"]);
			$promo_banner_resposta = $_FILES["promo_banner_resposta"]["name"];
		}
		if(!in_array($ext[1],$formatos)) {
			$msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
		}
	}
	$sql = "UPDATE promocoes SET
						promo_nome			= '".$promo_nome_update."',
						opr_codigo			= ".$opr_codigo.",
						promo_data_inicio	= to_date('".$promo_data_inicio."','DD/MM/YYYY'),           
						promo_data_fim		= to_date('".$promo_data_fim."','DD/MM/YYYY'),
						promo_descricao		= '".str_replace("'",'"',$promo_descricao)."',";
	if (!empty($promo_banner)) {
		$sql .= "		promo_banner			= '".$promo_banner."',";
	}
	if (!empty($promo_banner_resposta)) {
		$sql .= "		promo_banner_resposta	= '".$promo_banner_resposta."',";
	}
	if (empty($promo_valor)) {
		$sql .= "promo_valor = NULL,";
	}
	else {
		$sql .= "promo_valor =".$promo_valor.",";
	}
	if (empty($promo_pergunta)) {
		$sql .= "promo_pergunta = NULL,";
	}
	else {
		$sql .= "promo_pergunta = '".$promo_pergunta."',";
	}
	if (empty($promo_resposta)) {
		$sql .= "promo_resposta = NULL, ";
	}
	else {
		$sql .= "promo_resposta = '".$promo_resposta."', ";
	}
	if (empty($promo_link_redir)) {
		$sql .= "promo_link_redir = NULL,";
	}
	else {
		$sql .= "promo_link_redir ='".$promo_link_redir."',";
	}
	if (empty($promo_ativo)) {
		$sql .= "promo_ativo = '0',";
	}else {
		$sql .= "promo_ativo = '1',";
	}
	if (empty($promo_label_banner)) {
		$sql .= "promo_label_banner = '' ";
	}
	else {
		$sql .= "promo_label_banner = '".str_replace("'",'"',$promo_label_banner)."' ";
	}
	$sql .= "	WHERE promo_id = $promo_id_update";
	//echo $sql."<br>:SQL<br>";
	$rs_promocoes = SQLexecuteQuery($sql);
	if(!$rs_promocoes) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es da Promo&ccedil;&atilde;o. ($sql)<br>";
	}
	else {
		$msg .= "Sucesso ao atualizar informa&ccedil;&otilde;es da Promo&ccedil;&atilde;o ID:($promo_id_update).<br>";
	}
	//isset($_REQUEST['promo_id']);
	$acao = 'listar';
}

if($acao == 'editar')
{
    $sql = "SELECT 
					promo_nome,
					opr_codigo,
					to_char(promo_data_inicio,'DD/MM/YYYY') as promo_data_inicio,
					to_char(promo_data_fim,'DD/MM/YYYY') as promo_data_fim,
					promo_descricao,
					promo_banner,
					promo_valor,
					promo_pergunta,
					promo_resposta,
					promo_banner_resposta,
					promo_ativo,
					promo_link_redir,
					promo_label_banner
			FROM promocoes 
			WHERE promo_id = $promo_id"; 
	//die($sql);
	$rs_promocoes = SQLexecuteQuery($sql);
	if(!($rs_promocoes_row = pg_fetch_array($rs_promocoes))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es da Promo&ccedil;&atilde;o. ($sql)<br>";
	}
	else {
		$promo_nome				= $rs_promocoes_row['promo_nome'];
		$opr_codigo 			= $rs_promocoes_row['opr_codigo'];
		$promo_data_inicio		= $rs_promocoes_row['promo_data_inicio'];
		$promo_data_fim			= $rs_promocoes_row['promo_data_fim'];
		$promo_descricao		= $rs_promocoes_row['promo_descricao'];
		$promo_banner			= $rs_promocoes_row['promo_banner'];
		$promo_valor			= $rs_promocoes_row['promo_valor'];
		$promo_pergunta			= $rs_promocoes_row['promo_pergunta'];
		$promo_resposta			= $rs_promocoes_row['promo_resposta'];
		$promo_banner_resposta	= $rs_promocoes_row['promo_banner_resposta'];
		$promo_ativo			= $rs_promocoes_row['promo_ativo'];
		$promo_label_banner		= $rs_promocoes_row['promo_label_banner'];
		$promo_link_redir		= $rs_promocoes_row['promo_link_redir'];
		if (pg_num_rows($rs_promocoes) > 0)
			include 'promocoes_edt.php';
		else
			$acao = 'listar';
	}
}

if($acao == 'novo')
{
    include 'promocoes_edt.php';
}

if($acao == 'listar')
{
    include 'promocoes_lst.php';
}
//echo $msg;
?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>