<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

// VAriável que determina o ano inicio das operações
$ANO_INICIO_OPERACAO = 2008;
?>
<link rel="stylesheet" type="text/css" href="/js/anytime.css" />
<div id='msg' name='msg'>
</div>
<?php
$acao	= isset($_REQUEST['acao'])  ? $_REQUEST['acao'] : 'listar';

if(!isset($mes))
    $mes = null;

$mes    = (strlen($mes) == 1)       ? '0'.$mes          : $mes;
$msg	= "";

if(isset($_SESSION['userlogin_bko']) && !is_null($_SESSION['userlogin_bko'])){
	$bds_usuario_bko = strtoupper($_SESSION['userlogin_bko']);
}
if($acao == 'inserir')
{
	
        $sql = "select * from complice where c_ano_mes =  '".$ano."-".$mes."-01';";
        //echo $sql."<br>";
        $rs_complice_verify = SQLexecuteQuery($sql);
        if(pg_num_rows($rs_complice_verify) == 0){
            $sql = "INSERT INTO complice (
                                                            c_ano_mes, 
                                                            c_custo_mkt_credenciado, 
                                                            c_custo_risco_credenciador,
                                                            c_custo_outros_credenciador,
                                                            c_receita_mkt_emissor,
                                                            c_receita_outras_emissor,
                                                            c_custo_risco_emissor,
                                                            c_custo_processamento_emissor,
                                                            c_custo_mkt_emissor,
                                                            c_custo_inadimplencia_emissor,
                                                            c_custos_outros_emissor,
                                                            c_custo_impostos_emissor,
                                                            c_receita_credenciador,
                                                            c_receita_outras_credenciador,
                                                            c_custo_processamento_front_end_back_end
                                                            ) 
                                            VALUES (
                                                            '".$ano."-".$mes."-01', 
                                                            ".limpaPreparaNumero($c_custo_mkt_credenciado).", 
                                                            ".limpaPreparaNumero($c_custo_risco_credenciador).", 
                                                            ".limpaPreparaNumero($c_custo_outros_credenciador).", 
                                                            ".limpaPreparaNumero($c_receita_mkt_emissor).", 
                                                            ".limpaPreparaNumero($c_receita_outras_emissor).", 
                                                            ".limpaPreparaNumero($c_custo_risco_emissor).", 
                                                            ".limpaPreparaNumero($c_custo_processamento_emissor).", 
                                                            ".limpaPreparaNumero($c_custo_mkt_emissor).", 
                                                            ".limpaPreparaNumero($c_custo_inadimplencia_emissor).", 
                                                            ".limpaPreparaNumero($c_custos_outros_emissor).", 
                                                            ".limpaPreparaNumero($c_custo_impostos_emissor).", 
                                                            ".limpaPreparaNumero($c_receita_credenciador)." , 
                                                            ".limpaPreparaNumero($c_receita_outras_credenciador)." , 
                                                            ".limpaPreparaNumero($c_custo_processamento_front_end_back_end)." 
                                                    );";
            //echo $sql."<br>";
            $rs_complice = SQLexecuteQuery($sql);
            if(!$rs_complice) {
                    $msg .= "Erro ao inserir informa&ccedil;&otilde;es do complice.<br>";
            }
            $acao = 'listar';
        }
        else {
            $msg .= "Período de novo registro já existente. Por favor, verifique o período.<br>";
            $acao = 'novo';
        }
}//end if($acao == 'inserir')

if($acao == 'atualizar')
{
	$sql = "UPDATE complice SET
                                    c_custo_mkt_credenciado        = ".limpaPreparaNumero($c_custo_mkt_credenciado).", 
                                    c_custo_risco_credenciador     = ".limpaPreparaNumero($c_custo_risco_credenciador).", 
                                    c_custo_outros_credenciador    = ".limpaPreparaNumero($c_custo_outros_credenciador).", 
                                    c_receita_mkt_emissor          = ".limpaPreparaNumero($c_receita_mkt_emissor).", 
                                    c_receita_outras_emissor       = ".limpaPreparaNumero($c_receita_outras_emissor).", 
                                    c_custo_risco_emissor          = ".limpaPreparaNumero($c_custo_risco_emissor).", 
                                    c_custo_processamento_emissor  = ".limpaPreparaNumero($c_custo_processamento_emissor).", 
                                    c_custo_mkt_emissor            = ".limpaPreparaNumero($c_custo_mkt_emissor).", 
                                    c_custo_inadimplencia_emissor  = ".limpaPreparaNumero($c_custo_inadimplencia_emissor).", 
                                    c_custos_outros_emissor        = ".limpaPreparaNumero($c_custos_outros_emissor).", 
                                    c_custo_impostos_emissor       = ".limpaPreparaNumero($c_custo_impostos_emissor).", 
                                    c_receita_credenciador         = ".limpaPreparaNumero($c_receita_credenciador).", 
                                    c_receita_outras_credenciador            = ".limpaPreparaNumero($c_receita_outras_credenciador).", 
                                    c_custo_processamento_front_end_back_end = ".limpaPreparaNumero($c_custo_processamento_front_end_back_end)."
			WHERE	c_ano_mes =  '".$ano."-".$mes."-01';";
	//echo $sql."<br>:SQL<br>";
	$rs_complice = SQLexecuteQuery($sql);
	if(!$rs_complice) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es do complice.<br>";
	}
	else {
		$msg .= "Sucesso ao atualizar informa&ccedil;&otilde;es do complice ID:('".$ano."-".$mes."-01').<br>";
	}

	$acao = 'listar';
}

if($acao == 'editar')
{
    $sql = "SELECT  *
            FROM complice 
            WHERE c_ano_mes =  '".$ano."-".$mes."-01';"; 
	//echo $sql."<br>";
	$rs_complice = SQLexecuteQuery($sql);
	if(!($rs_complice_row = pg_fetch_array($rs_complice))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es do complice. ($sql)<br>";
	}
	else {
		$c_custo_mkt_credenciado        = $rs_complice_row['c_custo_mkt_credenciado'];
                $c_custo_risco_credenciador     = $rs_complice_row['c_custo_risco_credenciador'];
                $c_custo_outros_credenciador    = $rs_complice_row['c_custo_outros_credenciador'];
                $c_receita_mkt_emissor          = $rs_complice_row['c_receita_mkt_emissor'];
                $c_receita_outras_emissor       = $rs_complice_row['c_receita_outras_emissor'];
                $c_custo_risco_emissor          = $rs_complice_row['c_custo_risco_emissor'];
                $c_custo_processamento_emissor  = $rs_complice_row['c_custo_processamento_emissor'];
                $c_custo_mkt_emissor            = $rs_complice_row['c_custo_mkt_emissor'];
                $c_custo_inadimplencia_emissor  = $rs_complice_row['c_custo_inadimplencia_emissor'];
                $c_custos_outros_emissor        = $rs_complice_row['c_custos_outros_emissor'];
                $c_custo_impostos_emissor       = $rs_complice_row['c_custo_impostos_emissor'];
		$c_receita_credenciador         = $rs_complice_row['c_receita_credenciador'];
		$c_receita_outras_credenciador            = $rs_complice_row['c_receita_outras_credenciador'];
		$c_custo_processamento_front_end_back_end = $rs_complice_row['c_custo_processamento_front_end_back_end'];
		if (pg_num_rows($rs_complice) > 0)
			include 'complice_edt.php';
		else
			$acao = 'listar';
	}
}

if($acao == 'novo')
{
    include 'complice_edt.php';
}

if($acao == 'listar')
{
    include 'complice_lst.php';
}
//echo $msg;
?>
<div id="msg"><?php echo $msg;?></div>
</body>
</html>
<?php
function mesNome($codigoMes) {
    $nomeMes = "";
    $codigoMes = $codigoMes*1;
    switch ($codigoMes){
            case 1:  $nomeMes = "Janeiro"; break;
            case 2:  $nomeMes = "Fevereiro"; break;
            case 3:  $nomeMes = "Março"; break;
            case 4:  $nomeMes = "Abril"; break;
            case 5:  $nomeMes = "Maio"; break;
            case 6:  $nomeMes = "Junho"; break;
            case 7:  $nomeMes = "Julho"; break;
            case 8:  $nomeMes = "Agosto"; break;
            case 9:  $nomeMes = "Setembro"; break;
            case 10: $nomeMes = "Outubro"; break;
            case 11: $nomeMes = "Novembro"; break;
            case 12: $nomeMes = "Dezembro"; break;
    }
    return $nomeMes;
}//end function mesNome($codigoMes) 

function limpaPreparaNumero($numero) {
    return str_replace(",",".",str_replace(".","",trim($numero)));
}//end function mesNome($codigoMes) 
?>