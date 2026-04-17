<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/functions.php";
?>

<h2>Lista de CPF Whitelist</h2>

<?php
$ativacao = array(
				'0' => "Est� na Lista",
				'1' => "Retirado da Lista",
			);

$acao	= isset($_REQUEST['acao']) ? $_REQUEST['acao']	: 'listar';
$cpf	= isset($_REQUEST['cpf']) ? preg_replace('/[^0-9]/', '', $_REQUEST['cpf'])	: '';
$desc 	= isset($_REQUEST['desc']) ? $_REQUEST['desc'] : '';
$msg	= "";

if (!is_csv_numeric_global($cpf,1) && !empty($cpf)) {
	$msg	.= "CPF inv�lido! O CPF � composto de somente n�meros!</br>";
	$acao	= 'listar';
	$cpf	= '';
} else if(!empty($cpf)&&($acao=='inserir')){
	$cpf = str_replace(" ", "", $cpf);
}

if($acao == 'inserir') {
	if(empty($msg)) {
        $sql = "select * from cpf_white_list where cpf =$1;";
        $rs_log = SQLexecuteQueryParams($sql, array($cpf));

		if($rs_log && (($rs_log) ? pg_num_rows($rs_log) : 0)>0) {

			$msg = "<b class='txt-vermelho'>Este CPF j consta na White List!</b><p class='top20'></p>";

		} else {

			$sql = "select * from cpf_black_list where cpf = $1;";
			$rs_log = SQLexecuteQueryParams($sql, array($cpf));

            if($rs_log && (($rs_log) ? pg_num_rows($rs_log) : 0)>0) {
                $msg = "<b class='txt-vermelho'>Este CPF AINDA CONSTA na BLACK List!</b><p class='top20'></p>";
            } else {

                $sql = "INSERT INTO cpf_white_list (cpf, descricao, shn_login) VALUES ($1, $2, $3);";
                        //echo $sql."<br>";
                $params = array($cpf, $desc, $GLOBALS['_SESSION']['userlogin_bko']);
                $rs_white_list = SQLexecuteQueryParams($sql, $params);

                    if(!$rs_white_list) {
						$msg .= "Erro ao salvar o CPF na White List.<br>";
                    }
                    $cpf = "";
            }//end else do if($rs_log) Black List 
        }//end else do if($rs_log) White List
	}//end if(empty($msg))

	$acao = 'listar';

}//end if($acao == 'inserir')

if($acao == 'excluir') {
    $sql = "DELETE FROM cpf_white_list WHERE cpf =$1;";
	//echo $sql;
	$rs_white_list = SQLexecuteQueryParams($sql, array($cpf));

	if(!$rs_white_list) {
		$msg .= "Erro ao Excluir o CPF da White List. ($sql)<br>";
	}
	$cpf = "";
	$acao = 'listar';
}

if($acao == 'novo') {
    include 'cpf_white_list_edt.php';
}

if($acao == 'listar') {
    include 'cpf_white_list_lst.php';
}

?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>