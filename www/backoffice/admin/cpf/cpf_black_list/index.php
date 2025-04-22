<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/functions.php";

$ativacao = array(
				'0' => "Est&aacute; na Lista",
				'1' => "Retirado da Lista",
				);

$acao	= isset($_REQUEST['acao'])	? $_REQUEST['acao']				: 'listar';
$cpf	= isset($_REQUEST['cpf'])	? preg_replace('/[^0-9]/', '', $_REQUEST['cpf'])	: '';

$msg	= "";

if (!is_csv_numeric_global($cpf,1)&&!empty($cpf)) {
	$msg	.= "CPF inv&aacute;lido! O CPF é compostos de somente n&uacute;meros!</br>";
	$acao	= 'listar';
	$cpf	= '';
}
elseif(!empty($cpf)&&($acao=='inserir')){
	$cpf = str_replace(" ", "", $cpf);
}

if($acao == 'inserir')
{
	if(empty($msg)) {
                $sql = "select * from cpf_black_list where cpf = $cpf;";
                $rs_log = SQLexecuteQuery($sql);
                if($rs_log && pg_num_rows($rs_log)>0) {
                    $msg = "<b class='txt-vermelho'>Este CPF já consta na Black List!</b><p class='top20'></p>";
                }
                else {
                    $sql = "select * from cpf_white_list where cpf = $cpf;";
                    $rs_log = SQLexecuteQuery($sql);
                    if($rs_log && pg_num_rows($rs_log)>0) {
                        $msg = "<b class='txt-vermelho'>Este CPF AINDA CONSTA na WHITE List!</b><p class='top20'></p>";
                    }
                    else {
                        $sql = "INSERT INTO cpf_black_list (
                                                            cpf, 
                                                            shn_login
                                                    ) 
                                    VALUES (
                                                            $cpf,
                                                            '".$GLOBALS['_SESSION']['userlogin_bko']."'
                                            );";
                        //echo $sql."<br>";
                        $rs_black_list = SQLexecuteQuery($sql);
                        if(!$rs_black_list) {
                                $msg .= "Erro ao salvar o CPF na Black List. ($sql)<br>";
                        }
                        $cpf = "";
                    }
                }//end else do if($rs_log)
	}//end if(empty($msg))
	$acao = 'listar';
}//end if($acao == 'inserir')

if($acao == 'excluir')
{
    $sql = "DELETE FROM cpf_black_list 
		WHERE cpf = $cpf; ";
	//echo $sql;
	$rs_black_list = SQLexecuteQuery($sql);
	if(!$rs_black_list) {
		$msg .= "Erro ao Excluir o CPF da Black List. ($sql)<br>";
	}
	$cpf = "";
	$acao = 'listar';
}

if($acao == 'novo')
{
    include 'cpf_black_list_edt.php';
}

if($acao == 'listar')
{
    include 'cpf_black_list_lst.php';
}
?>
<script type="text/javascript">
	document.getElementById("msg").innerHTML = "<?php echo $msg;?>";
</script>
</body>
</html>