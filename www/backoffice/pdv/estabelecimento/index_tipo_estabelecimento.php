<?php
header("Content-Type: text/html; charset=UTF-8",true);
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>
<link rel="stylesheet" type="text/css" href="/css/gamer/cssClassLista.css" />
<?php
$acao			= isset($_REQUEST['acao'])			? $_REQUEST['acao']								: 'listar';

$msg	= "";

if($acao == 'inserir')
{
	
	 $sql = "INSERT INTO tb_tipo_estabelecimento (
													te_ativo,
													te_descricao
									) VALUES (
													".intval($te_ativo*1).", 
													'".str_replace("'",'"',$te_descricao_update)."' 
													);";
	//echo $sql."<br>";
	$rs_tipo_estabalecimento = SQLexecuteQuery($sql);
	if(!$rs_tipo_estabalecimento) {
			$msg .= "Erro ao salvar informa&ccedil;&otilde;es da Tipo de Estabelecimento de LANHouses. ($sql)<br>";
	}
   $acao = 'listar';
}

if($acao == 'atualizar')
{
    $sql = "UPDATE tb_tipo_estabelecimento SET
						te_descricao		= '".str_replace("'",'"',$te_descricao_update)."',
						te_ativo			= ".intval((isset($te_ativo)?$te_ativo:0)*1)."
			WHERE te_id = $te_id_update";
	//echo $sql;
	$rs_tipo_estabalecimento = SQLexecuteQuery($sql);
	if(!$rs_tipo_estabalecimento) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es do Tipo de Estabelecimento de LANHouses. ($sql)<br>";
	}
	else {
		$msg .= "Sucesso ao atualizar informa&ccedil;&otilde;es do Tipo de Estabelecimento ID:($te_id_update).<br>";
	}
	$acao = 'listar';
}

if($acao == 'editar')
{
    $sql = "SELECT *
			FROM tb_tipo_estabelecimento 
			WHERE te_id = $te_id"; 
	//echo $sql;
	$rs_tipo_estabalecimento = SQLexecuteQuery($sql);
	if(!($rs_tipo_estabalecimento_row = pg_fetch_array($rs_tipo_estabalecimento))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es da Tipo de Estabelecimento de LANHouses. ($sql)<br>";
	}
	else {
		$te_descricao	= $rs_tipo_estabalecimento_row['te_descricao'];
		$te_id			= $rs_tipo_estabalecimento_row['te_id'];
		$te_ativo		= $rs_tipo_estabalecimento_row['te_ativo'];
		if (pg_num_rows($rs_tipo_estabalecimento) > 0)
			require_once 'tipo_estabelecimento_edt.php';
		else
			$acao = 'listar';
	}
}
if($acao == 'novo')
{
   require_once 'tipo_estabelecimento_edt.php';
}

if($acao == 'listar')
{
    require_once 'tipo_estabelecimento_lst.php';
}
//echo $msg;
?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>