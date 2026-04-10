<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);  // Exibe todos os tipos de erros
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
		$pasta = $raiz_do_projeto."arquivos_gerados/imagens/pdv/promocoes/";
		if(file_exists("$pasta".$_FILES["promolh_banner"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor, renomear antes.<br>";
			$promolh_banner = '';
		}
		else {
			move_uploaded_file($_FILES["promolh_banner"]["tmp_name"],"$pasta".$_FILES["promolh_banner"]["name"]);
			$promolh_banner = $_FILES["promolh_banner"]["name"];
		}
                if(empty($msg)) {
                        $titulo_tabela = str_replace("'", '"', $promolh_titulo_tabela);
                        $columns = "promolh_descricao, promolh_data_inicio, promolh_data_fim, promolh_titulo_tabela, opr_codigo, promolh_banner, promolh_link_download, promolh_regulamento";
                        $params = array($promolh_descricao, $promolh_data_inicio, $promolh_data_fim, $titulo_tabela, (int)$opr_codigo, $promolh_banner, $promolh_link_download, $promolh_regulamento);
                        $values = "$1, to_date($2,'DD/MM/YYYY'), to_date($3,'DD/MM/YYYY'), $4, $5, $6, $7, $8";

                        if(!empty($ogp_id)){
                                $columns .= ", ogp_id";
                                $values .= ", $" . (count($params) + 1);
                                $params[] = (int)$ogp_id;
                        }

                        $sql = "INSERT INTO promocoes_lanhouses (" . $columns . ") VALUES (" . $values . ")";
                        $rs_promocoes = SQLexecuteQueryParams($sql, $params);
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
		$pasta = $raiz_do_projeto."arquivos_gerados/imagens/pdv/promocoes/";
		if(file_exists("$pasta".$_FILES["promolh_banner"]["name"])){
			$msg .= "Imagem de Banner j&aacute; existe com este mesmo nome.<br>Favor renomear antes.<br>";
			$promolh_banner = '';
		}
		else {
			if(!move_uploaded_file($_FILES["promolh_banner"]["tmp_name"],"$pasta".$_FILES["promolh_banner"]["name"])){
				die("erro");
			}
			$promolh_banner = $_FILES["promolh_banner"]["name"];
		}
		if(!in_array($ext[1],$formatos)) {
			$msg .= "Arquivo N&atilde;o Possui um Formato V&aacute;lido para o Banner.<br>";
		}
	}
    $titulo_tabela = str_replace("'", '"', $promolh_titulo_tabela);
    $params = array(
        $promolh_descricao,
        (int)$opr_codigo,
        $promolh_data_inicio,
        $promolh_data_fim,
        $titulo_tabela,
        $promolh_link_download,
        $promolh_regulamento
    );

    $setSql = "promolh_descricao = $1, " .
              "opr_codigo = $2, " .
              "promolh_data_inicio = to_date($3,'DD/MM/YYYY'), " .
              "promolh_data_fim = to_date($4,'DD/MM/YYYY'), " .
              "promolh_titulo_tabela = $5, " .
              "promolh_link_download = $6, " .
              "promolh_regulamento = $7";

    if (!empty($promolh_banner)) {
        $setSql .= ", promolh_banner = $" . (count($params) + 1);
        $params[] = $promolh_banner;
    }

    if(!empty($ogp_id)){
        $setSql .= ", ogp_id = $" . (count($params) + 1);
        $params[] = (int)$ogp_id;
    }

    if(!empty($ug_id)){
        $setSql .= ", ug_id = $" . (count($params) + 1);
        $params[] = (int)$ug_id;
    }

    $whereIdx = count($params) + 1;
    $params[] = (int)$promolh_id;
    $sql = "UPDATE promocoes_lanhouses SET " . $setSql . " WHERE promolh_id = $" . $whereIdx;
    $rs_promocoes = SQLexecuteQueryParams($sql, $params);
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
			WHERE promolh_id = $1"; 
	$rs_promocoes = SQLexecuteQueryParams($sql, array((int)$promolh_id));
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
