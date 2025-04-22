<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>
<link rel="stylesheet" type="text/css" href="/css/gamer/cssClassLista.css" />
<?php
$acao			= isset($_REQUEST['acao'])			? $_REQUEST['acao']								: 'listar';

$msg	= "";

if($acao == 'inserir')
{
	
	 $sql = "INSERT INTO dist_videos (
										dv_ativo,
										dv_descricao,
										dv_data_cadastro, 
										dv_data_inicio, 
										dv_data_fim, 
										dv_url
									) VALUES (
										".intval($dv_ativo*1).", 
										'".str_replace("'",'"',$dv_descricao_update)."',
										NOW(),
										to_date('$dv_data_inicio','DD/MM/YYYY'), 
										to_date('$dv_data_fim','DD/MM/YYYY'),
										'$dv_url'
									);";
	//echo $sql."<br>";
	$rs_tipo_estabalecimento = SQLexecuteQuery($sql);
	if(!$rs_tipo_estabalecimento) {
			$msg .= "Erro ao salvar informa&ccedil;&otilde;es do V&iacute;deo de LANHouses. ($sql)<br>";
	}
   $acao = 'listar';
}

if($acao == 'atualizar')
{
    $sql = "UPDATE dist_videos SET
						dv_descricao		= '".str_replace("'",'"',$dv_descricao_update)."',
						dv_ativo			= ".intval($dv_ativo*1).",
						dv_data_inicio		= to_date('".$dv_data_inicio."','DD/MM/YYYY'),           
						dv_data_fim			= to_date('".$dv_data_fim."','DD/MM/YYYY'),           
						dv_url				= '$dv_url'
			WHERE dv_id = $dv_id_update";
	//echo $sql;
	$rs_tipo_estabalecimento = SQLexecuteQuery($sql);
	if(!$rs_tipo_estabalecimento) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es do V&iacute;deo de LANHouses. ($sql)<br>";
	}
	else {
		$msg .= "Sucesso ao atualizar informa&ccedil;&otilde;es do V&iacute;deo ID:($dv_id_update).<br>";
	}
	$acao = 'listar';
}

if($acao == 'editar')
{
    $sql = "SELECT dv_id,
					dv_ativo,
					dv_descricao,
					to_char(dv_data_inicio,'DD/MM/YYYY') as dv_data_inicio,
					to_char(dv_data_fim,'DD/MM/YYYY') as dv_data_fim,
					dv_url
			FROM dist_videos 
			WHERE dv_id = $dv_id"; 
	//echo $sql;
	$rs_tipo_estabalecimento = SQLexecuteQuery($sql);
	if(!($rs_tipo_estabalecimento_row = pg_fetch_array($rs_tipo_estabalecimento))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es do V&iacute;deo de LANHouses. ($sql)<br>";
	}
	else {
		$dv_descricao		= $rs_tipo_estabalecimento_row['dv_descricao'];
		$dv_id				= $rs_tipo_estabalecimento_row['dv_id'];
		$dv_ativo			= $rs_tipo_estabalecimento_row['dv_ativo'];
		$dv_data_inicio		= $rs_tipo_estabalecimento_row['dv_data_inicio'];
		$dv_data_fim		= $rs_tipo_estabalecimento_row['dv_data_fim'];
		$dv_url				= $rs_tipo_estabalecimento_row['dv_url'];
													
		if (pg_num_rows($rs_tipo_estabalecimento) > 0)
			include 'videos_edt.php';
		else
			$acao = 'listar';
	}
}
if($acao == 'novo')
{
   include 'videos_edt.php';
}

if($acao == 'listar')
{
    include 'videos_lst.php';
}
//echo $msg;
?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>