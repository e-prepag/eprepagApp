<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
if(b_IsBKOUsuarioAdminGestaodeRisco()){

$ativacao = array(
				'0' => "Est&aacute; na Lista",
				'1' => "Retirado da Lista",
				);


function UgIdBloqueado($ug_id) {
	$retorno = false;
	$sql = "select * from usuarios_games_black_list where ug_id = ".$ug_id;
	$rs_log = SQLexecuteQuery($sql);
	if($rs_log) {
		if (pg_num_rows($rs_log)>0) {
			$retorno = true;
		}
	}
	return $retorno;
}

$acao				= isset($_REQUEST['acao'])				? $_REQUEST['acao']								: 'listar';
$ug_id				= isset($_REQUEST['ug_id'])				? htmlentities($_REQUEST['ug_id'])				: '';
$ugbl_status		= isset($_REQUEST['ugbl_status'])		? htmlentities($_REQUEST['ugbl_status'])		: '';

$msg	= "";

if (!is_csv_numeric_global($ug_id,1)&&!empty($ug_id)) {
    $msg	.= "IDs inv&aacute;lidos! Os IDs s&atilde;o compostos de somente n&uacute;meros! Verifique tamb&eacute;m, se ap&oacute;s o ultimo ID possui uma v&iacute;rgula sobrando.</br>";
	$acao	= 'listar';
	$ug_id	= '';
}
elseif(!empty($ug_id)&&($acao=='inserir')){
	$ug_id = str_replace(" ", "", $ug_id);
	$ug_id = explode(",", $ug_id);
}

if($acao == 'inserir')
{
	if(empty($msg)) {
		foreach($ug_id as $val){
			if(UgIdBloqueado($val)) {
				$sql = "UPDATE usuarios_games_black_list SET
										ugbl_status					= 0,
										shn_login					= '".$GLOBALS['_SESSION']['userlogin_bko']."',
										ugbl_data_ultima_alteracao	= NOW()
						WHERE ug_id = $val";
			}//end if(UgIdBloqueado($val))
			else {
				$sql = "INSERT INTO usuarios_games_black_list (
																ug_id, 
																shn_login,
																ugbl_data_ultima_alteracao
															) 
													VALUES (
																$val,
																'".$GLOBALS['_SESSION']['userlogin_bko']."',
																NOW() 
															);";
			}//end else if(UgIdBloqueado($val))
			//echo $sql."<br>";
			$rs_black_list = SQLexecuteQuery($sql);
			if(!$rs_black_list) {
					$msg .= "Erro ao salvar informa&ccedil;&otilde;es da Gest&atilde;o de Risco Black List. ($sql)<br>";
			}
		}//end foreach
		$ug_id = "";
	}//end if(empty($msg))
	$acao = 'listar';
}//end if($acao == 'inserir')

if($acao == 'atualizar')
{
    $sql = "UPDATE usuarios_games_black_list SET
						ugbl_status					= ".$ugbl_status.",
						shn_login					= '".$GLOBALS['_SESSION']['userlogin_bko']."',
						ugbl_data_ultima_alteracao	= NOW()
		WHERE ug_id = $ug_id";
	//echo $sql;
	$rs_black_list = SQLexecuteQuery($sql);
	if(!$rs_black_list) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es da Gest&atilde;o de Risco Black List. ($sql)<br>";
	}
	$ugbl_status = "";
	$ug_id = "";
	$acao = 'listar';
}

if($acao == 'editar')
{
    $sql = "SELECT 
					ug_id, 
					shn_login,
					to_char(ugbl_data_ultima_alteracao,'DD/MM/YYYY') as ugbl_data_ultima_alteracao,
					ugbl_status
			FROM usuarios_games_black_list 
			WHERE ug_id = $ug_id";
	//echo $sql;
	$rs_black_list = SQLexecuteQuery($sql);
	if(!($rs_black_list_row = pg_fetch_array($rs_black_list))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es da Gest&atilde;o de Risco Black List. ($sql)<br>";
	}
	else {
		$ugbl_status				= $rs_black_list_row['ugbl_status'];
		$shn_login					= $rs_black_list_row['shn_login'];
		$ugbl_data_ultima_alteracao	= $rs_black_list_row['ugbl_data_ultima_alteracao'];
		$ug_id						= $rs_black_list_row['ug_id'];
		if (pg_num_rows($rs_black_list) > 0)
			include 'gestao_risco_black_edt.php';
		else
			$acao = 'listar';
	}
}

if($acao == 'novo')
{
    include 'gestao_risco_black_edt.php';
}

if($acao == 'listar')
{
    include 'gestao_risco_black_lst.php';
}
//echo $msg;
}//end if(b_IsBKOUsuarioAdminGestaodeRisco())
?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>