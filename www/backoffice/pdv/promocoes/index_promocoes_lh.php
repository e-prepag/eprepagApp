<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
$acao				= isset($_REQUEST['acao'])				? $_REQUEST['acao']								: 'listar';
$promolh_id			= isset($_REQUEST['promolh_id'])		? htmlentities($_REQUEST['promolh_id'])			: '';
$promolh_descricao	= isset($_REQUEST['promolh_descricao'])	? htmlentities($_REQUEST['promolh_descricao'])	: '';

$msg	= "";

$formatos[] = 'jpg';
$formatos[] = 'jpeg';
$formatos[] = 'gif';
$formatos[] = 'png';
		
if($acao == 'inserir')
{
	$ext	= explode('/',$_FILES['promolh_banner']['type']);

	if(in_array($ext[1],$formatos)) {
		$pasta = $raiz_do_projeto."public_html/imagens/pdv/promocoes/";
		if(file_exists("$pasta".$_FILES["promolh_banner"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor, renomear antes.<br>";
			$promolh_banner = '';
		}
		else {
			move_uploaded_file($_FILES["promolh_banner"]["tmp_name"],"$pasta".$_FILES["promolh_banner"]["name"]);
			$promolh_banner = $_FILES["promolh_banner"]["name"];
		}
                if(empty($msg)) {
                        $sql = "INSERT INTO promocoes_lanhouses (
                                                                        promolh_descricao,
                                                                        promolh_data_inicio,
                                                                        promolh_data_fim,
                                                                        promolh_titulo_tabela,
                                                                        opr_codigo,
                                                                        promolh_banner,
																		promolh_link_download,
                                                                        promolh_regulamento";
                        if(!empty($ogp_id)){
                                $sql .= ",
                                                                        ogp_id";
                        }
                        $sql .= "				) 
                                                        VALUES (
                                                                        '$promolh_descricao', 
                                                                        to_date('$promolh_data_inicio','DD/MM/YYYY'), 
                                                                        to_date('$promolh_data_fim','DD/MM/YYYY'), 
                                                                        '".str_replace("'",'"',$promolh_titulo_tabela)."', 
                                                                        $opr_codigo,
                                                                        '$promolh_banner',
                                                                        '$promolh_link_download',
                                                                        '$promolh_regulamento'";
                        if(!empty($ogp_id)){
                                $sql .= ", 
                                                                        $ogp_id";
                        }
                        $sql .= ");";
                        //echo $sql."<br>";
                        $rs_promocoes = SQLexecuteQuery($sql);
                        if(!$rs_promocoes) {
                                $msg .= "Erro ao salvar informa&ccedil;&otilde;es da Promo&ccedil;&atilde;o de LANHouses. ($sql)<br>";
                        }
                }
        }
	else $msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
	$acao = 'listar';
}

if($acao == 'atualizar')
{
	if(!empty($_FILES["promolh_banner"]["name"])) {
		$ext	= explode('/',$_FILES['promolh_banner']['type']);
		$pasta = $raiz_do_projeto."public_html/imagens/pdv/promocoes/";
		if(file_exists("$pasta".$_FILES["promolh_banner"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor renomear antes.<br>";
			$promolh_banner = '';
		}
		else {
			move_uploaded_file($_FILES["promolh_banner"]["tmp_name"],"$pasta".$_FILES["promolh_banner"]["name"]);
			$promolh_banner = $_FILES["promolh_banner"]["name"];
		}
		if(!in_array($ext[1],$formatos)) {
			$msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
		}
	}
    $sql = "UPDATE promocoes_lanhouses SET
						promolh_descricao		= '".$promolh_descricao."',
						opr_codigo				= $opr_codigo,
						promolh_data_inicio		= to_date('".$promolh_data_inicio."','DD/MM/YYYY'),           
						promolh_data_fim		= to_date('".$promolh_data_fim."','DD/MM/YYYY'),
						promolh_titulo_tabela	= '".str_replace("'",'"',$promolh_titulo_tabela)."',
						promolh_link_download	= '".$promolh_link_download."',";
	if (!empty($promolh_banner)) {
		$sql .= "		promolh_banner          = '".$promolh_banner."',";
	}
	$sql .= "           promolh_regulamento     = '".$promolh_regulamento."'";
	if(!empty($ogp_id)){
		$sql .= ",
							ogp_id		= ".$ogp_id."";
	}
	if(!empty($ug_id)){
		$sql .= ",
							ug_id		= ".$ug_id."";
	}
	$sql .= "	WHERE promolh_id = $promolh_id";
	//echo $sql;
	$rs_promocoes = SQLexecuteQuery($sql);
	if(!$rs_promocoes) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es da Promo&ccedil;&atilde;o de LANHouses. ($sql)<br>";
	}
	$promolh_descricao = "";
	$acao = 'listar';
}

if($acao == 'editar')
{
    $sql = "SELECT 
					promolh_descricao,
					to_char(promolh_data_inicio,'DD/MM/YYYY') as promolh_data_inicio,
					to_char(promolh_data_fim,'DD/MM/YYYY') as promolh_data_fim,
					promolh_titulo_tabela,
                    promolh_banner,
					promolh_link_download,
                    promolh_regulamento,
					opr_codigo,
					ogp_id,
					ug_id
			FROM promocoes_lanhouses 
			WHERE promolh_id = $promolh_id"; 
	$rs_promocoes = SQLexecuteQuery($sql);
	if(!($rs_promocoes_row = pg_fetch_array($rs_promocoes))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es da Promo&ccedil;&atilde;o de LANHouses. ($sql)<br>";
	}
	else {
		$promolh_descricao		= $rs_promocoes_row['promolh_descricao'];
		$promolh_data_inicio	= $rs_promocoes_row['promolh_data_inicio'];
		$promolh_data_fim		= $rs_promocoes_row['promolh_data_fim'];
		$promolh_titulo_tabela	= $rs_promocoes_row['promolh_titulo_tabela'];
        $promolh_banner         = $rs_promocoes_row['promolh_banner'];
        $promolh_link_download  = $rs_promocoes_row['promolh_link_download'];
        $promolh_regulamento    = $rs_promocoes_row['promolh_regulamento'];
		$opr_codigo 			= $rs_promocoes_row['opr_codigo'];
		$ogp_id					= $rs_promocoes_row['ogp_id'];
		$ug_id					= $rs_promocoes_row['ug_id'];
		if (pg_num_rows($rs_promocoes) > 0)
			include 'promocoes_lh_edt.php';
		else
			$acao = 'listar';
	}
}
echo '<link rel="stylesheet" type="text/css" href="/css/gamer/cssClassLista.css" />';
if($acao == 'novo')
{
    require_once 'promocoes_lh_edt.php';
}

if($acao == 'listar')
{
    require_once 'promocoes_lh_lst.php';
}
//echo $msg;
?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>