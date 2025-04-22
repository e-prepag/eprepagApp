<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/classPinsCard.php";
require_once $raiz_do_projeto."includes/gamer/constantesPinEpp.php";

$acao	= isset($_REQUEST['acao']) ? $_REQUEST['acao'] : 'listar';
$msg	= "";

if($acao == 'inserir') {
	$sql = "select * from pins_card_distribuidoras where opr_codigo = ".$opr_codigo." and pcd_id_distribuidor = ".$pcd_id_distribuidor.";";
        $rs_distribuidoras = SQLexecuteQuery($sql);
        if(pg_num_rows($rs_distribuidoras) < 1) {
                
                $sql = "INSERT INTO pins_card_distribuidoras (
                                                                opr_codigo,
                                                                pcd_id_distribuidor,
                                                                pcd_formato,
                                                                pcd_comissao) 
                                                VALUES (
                                                                ".$opr_codigo.",
                                                                ".$pcd_id_distribuidor.",
                                                                ".$pcd_formato.",
                                                                ".$pcd_comissao.");";
                //echo $sql."<br>";//die();
                $rs_distribuidoras = SQLexecuteQuery($sql);
                if(!$rs_distribuidoras) {
                        $msg .= "Erro ao salvar informações da Distribuidora. ($sql)<br>";
                }
                else {
                        for ( $x = 1; $x <= $quantidade_itens ; $x++ ){
                            $valor = $_POST["valor$x"];
                            if(!empty($valor)) {
                                $sql = "insert into pins_card_distribuidoras_valores (
                                                       opr_codigo, 
                                                       pcd_id_distribuidor, 
                                                       pcdv_valor
                                                       ) 
                                               values  (
                                                       $opr_codigo,
                                                       $pcd_id_distribuidor,
                                                       $valor);";
                                //echo $sql."<br>";
                                $rs_distribuidoras_valor = SQLexecuteQuery($sql);
                                if(!$rs_distribuidoras_valor) {
                                       $msg .= "Erro ao salvar as informacoes de Valores de Cartões para a Distribuidora. ($sql)<br>";
                                }

                            }//end if(!empty($valor))
                        }//end for
                }//end else do if(!$rs_distribuidoras)
        }//end if(pg_num_rows($rs_distribuidoras) < 1)
        else $msg .= "Já existe um cadastro para este Publisher [".$opr_codigo."] e este Distribuidor [".$pcd_id_distribuidor."].<br>";

	$acao = 'listar';
}

if($acao == 'atualizar') {
	$sql = "UPDATE pins_card_distribuidoras SET
						pcd_formato             = ".$pcd_formato.",
						pcd_comissao            = ".$pcd_comissao."
               	WHERE opr_codigo = ".$opr_codigo."
                        AND pcd_id_distribuidor	= ".$pcd_id_distribuidor." ;";

        //echo $sql."<br>";die();
	$rs_distribuidoras = SQLexecuteQuery($sql);
	if(!$rs_distribuidoras) {
		$msg .= "Erro ao atualizar informações da Distribuidora. ($sql)<br>";
	}
	else {
                $sql = "DELETE FROM pins_card_distribuidoras_valores
                        WHERE opr_codigo = ".$opr_codigo."
                            AND pcd_id_distribuidor	= ".$pcd_id_distribuidor." ;";
                //echo $sql."<br>";
                $rs_distribuidoras_valor = SQLexecuteQuery($sql);
                for ( $x = 1; $x <= $quantidade_itens ; $x++ ){
                    $valor = $_POST["valor$x"];
                    if(!empty($valor)) {
                        $sql = "insert into pins_card_distribuidoras_valores (
                                               opr_codigo, 
                                               pcd_id_distribuidor, 
                                               pcdv_valor
                                               ) 
                                       values  (
                                               $opr_codigo,
                                               $pcd_id_distribuidor,
                                               $valor);";
                        //echo $sql."<br>";
                        $rs_distribuidoras_valor = SQLexecuteQuery($sql);
                        if(!$rs_distribuidoras_valor) {
                               $msg .= "Erro ao atualizar os valores de Cartões para a Distribuidora. ($sql)<br>";
                        }

                    }//end if(!empty($valor))
                }//end for
	}//end else do if(!$rs_distribuidoras)
        $acao = 'listar';
}

if($acao == 'editar') {
        $sql = "SELECT * FROM pins_card_distribuidoras WHERE opr_codigo = ".$opr_codigo." AND pcd_id_distribuidor = ".$pcd_id_distribuidor.";"; 
	//echo $sql."<br>";die();
        $rs_distribuidoras = SQLexecuteQuery($sql);
	if(!($rs_distribuidoras_row = pg_fetch_array($rs_distribuidoras))) {
		$msg .= "Erro ao consultar informações da Distribuição. ($sql)<br>";
	}
	else {
		$opr_codigo		= $rs_distribuidoras_row['opr_codigo'];
		$pcd_id_distribuidor 	= $rs_distribuidoras_row['pcd_id_distribuidor'];
		$pcd_formato		= $rs_distribuidoras_row['pcd_formato'];
		$pcd_comissao		= $rs_distribuidoras_row['pcd_comissao'];
		if (pg_num_rows($rs_distribuidoras) > 0) {
                        $sql = "select * from pins_card_distribuidoras_valores where opr_codigo = ".$opr_codigo." and pcd_id_distribuidor = ".$pcd_id_distribuidor." ORDER BY pcdv_valor;";
                        //echo $sql."<br>";die();
                        $rs_distribuidoras = SQLexecuteQuery($sql);
                        $counter = 1;
                        while($rs_distribuidoras_row = pg_fetch_array($rs_distribuidoras)) {
                            ${'valor'.$counter} = $rs_distribuidoras_row['pcdv_valor'];
                            $counter++;
                        }//end while
			include 'operadoras_cartao_edt.php';
                }//end if (pg_num_rows($rs_distribuidoras) > 0)
		else {
			$acao = 'listar';
                }//end else
	}
}

if($acao == 'novo')
{
    include 'operadoras_cartao_edt.php';
}

if($acao == 'listar')
{
    include 'operadoras_cartao_lst.php';
}
echo $msg;
?>
</body>
</html>