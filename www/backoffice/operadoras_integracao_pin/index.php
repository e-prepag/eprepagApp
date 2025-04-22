<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/constantes_opr.php";
?>
<link rel="stylesheet" type="text/css" href="/css/cssClassLista.css" />
<?php
$acao		= isset($_REQUEST['acao'])		? $_REQUEST['acao']			: 'listar';
$opr_codigo	= isset($_REQUEST['opr_codigo'])? $_REQUEST['opr_codigo']	: NULL;
$msg	= "";

if($acao == 'atualizar')
{
    $ip_inicial = $_POST['ip_inicial'];
    $ip_final = $_POST['ip_final'];

    $ranged = '';

    if ( count($ip_inicial) > 0 ) {
        for ( $i = 0; $i < count($ip_inicial); $i++ ) {
            if ( !empty($ip_inicial[$i]) && !empty($ip_final[$i]) ) {
                $ranged .= ';' . $ip_inicial[$i] . '-' . $ip_final[$i] . ';';
            }
        }
    }

    $opr_ip = $_POST['opr_ip'];

    // Cleaning opr_ip
    $opr_ip = trim(implode(';', array_filter(explode(';', $opr_ip))));
    // Cleaning ranged
    $ranged = trim(implode(';', array_filter(explode(';', $ranged))));

    if(empty($ranged)) {
        $opr_ip .= ';189.38.238.205';
    }
    else {
        $opr_ip .= ';'.$ranged .  ';189.38.238.205';
    }
    $opr_ip = preg_replace('/\s+/', '', $opr_ip);
    $sql = "UPDATE operadoras SET
						opr_ip='".$opr_ip."',
						opr_product_type=".$opr_product_type.",
						opr_use_check=".$opr_use_check.",
						opr_partner_check='".$opr_partner_check."',
						opr_partner_email='".$opr_partner_email."',
						opr_partner_dominio='".$opr_partner_dominio."'
					WHERE opr_codigo = ".$opr_codigo;
	$rs_operadoras = SQLexecuteQuery($sql);
	if(!$rs_operadoras) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es da Operadora. ($sql)<br>";
	}
    $acao = 'listar';
}
if($acao == 'editar')
{
    $sql = "SELECT * FROM operadoras WHERE opr_codigo = $opr_codigo";
    $rs_operadoras = SQLexecuteQuery($sql);
    if(!($rs_operadoras_row = pg_fetch_array($rs_operadoras))) {
        $msg .= "Erro ao consultar informa&ccedil;&otilde;es da Operadora. ($sql)<br>";
    }
    else {
        $opr_ip				= str_replace(";189.38.238.205","",$rs_operadoras_row['opr_ip']);
        $opr_product_type	= $rs_operadoras_row['opr_product_type'];
        $opr_use_check		= $rs_operadoras_row['opr_use_check'];
        $opr_partner_check	= $rs_operadoras_row['opr_partner_check'];
        $opr_partner_email	= $rs_operadoras_row['opr_partner_email'];
        $opr_partner_dominio= $rs_operadoras_row['opr_partner_dominio'];
        if (pg_num_rows($rs_operadoras) > 0) {
            include 'operadoras_edt.php';
        }
        else
            $acao = 'listar';
    }
}

if($acao == 'novo')
{
    include 'operadoras_edt.php';
}

if($acao == 'listar')
{
    include 'operadoras_lst.php';
}
echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');

?>
</body>
</html>