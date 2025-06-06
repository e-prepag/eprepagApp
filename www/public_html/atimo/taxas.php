<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
// Levantamento do Montante envolvido na cobração de Taxa de Manutenção Anual
// backoffice\offweb\tarefas\gamer_taxa_manutencao_anual.php

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//Período considerado no Levantamento
define('PERIODO_CONSIDERADO', '1 year');

//Valor Taxa de Manutenção Anual em Reais (R$)
define('VALOR_TAXA', 12);

//Período considerado no Levantamento
define('QTDE_DETALHAMENTO', 30); 


// Dados do Email
$email  = "wagner@e-prepag.com.br";
$cc     = "glaucia@e-prepag.com.br";
$bcc    = "";
$subject= "Levantamento do Montante Envolvido na Cobrança de Taxa de Manutenção Anual";
$msg    = "";
$raiz_do_projeto = "/www/";

require_once "../../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";

$time_start_stats = getmicrotime();

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Levantamento do Montante Envolvido na Cobrança de Taxa de Manutenção Anual no período de ".PERIODO_CONSIDERADO." (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

//Query de captura dos dados do dia considerado
$sql = "
SELECT 
        CASE WHEN SUM(scf_valor_disponivel) < ".VALOR_TAXA." THEN SUM(scf_valor_disponivel) ELSE ".VALOR_TAXA." END as total,
        count(*) as qtde,
        SUM(scf_valor_disponivel) as total_sem_considerar_taxa,
        ug.ug_id,
        ug_email,
        ug_nome
FROM saldo_composicao_fifo scf
    INNER JOIN usuarios_games ug ON ug.ug_id = scf.ug_id
WHERE scf_data_deposito < NOW() - '".PERIODO_CONSIDERADO."':: interval
	AND scf_status = 1
	AND scf_valor_disponivel != 0
	AND scf.ug_id NOT IN ( 
				SELECT ug_id
				FROM (
					SELECT ug_id
					FROM saldo_composicao_fifo scf
					WHERE scf_data_deposito >= NOW() - '".PERIODO_CONSIDERADO."':: interval
					GROUP BY ug_id
                                        
					UNION ALL
					
					--- tabela de vendas que também contempla a tabela saldo utilizado
					SELECT vg_ug_id as ug_id
					FROM tb_venda_games vg
					WHERE vg_data_concilia >= NOW() - '".PERIODO_CONSIDERADO."':: interval
						AND vg_ultimo_status = ".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."
					GROUP BY vg_ug_id	

					UNION ALL
					
					--- tabela de taxa anual cobrado para não ser considerados todos os dias
					SELECT ug_id
					FROM tb_pag_taxa_anual 
					WHERE pta_data >= NOW() - '".PERIODO_CONSIDERADO."':: interval
					GROUP BY ug_id
					
					) excluidos
				  GROUP BY ug_id
				) 
GROUP BY ug.ug_id,ug_email,ug_nome	
ORDER BY qtde DESC;   
";
echo $sql.PHP_EOL;
$rs_dados_taxa = SQLexecuteQuery($sql);
        
//Verificando Dados
$msg .= PHP_EOL."Levantamento do Montante Envolvido na Cobrança de Taxa de Manutenção Anual no período de ".PERIODO_CONSIDERADO." (".date("Y-m-d H:i:s").") (YYYY-MM-DD)<br>".PHP_EOL."Total de Usuários Envolvidos :  [<b>".pg_num_rows($rs_dados_taxa)."</b>]".PHP_EOL;
$msg .= PHP_EOL."Valor Máximo da Taxa de Manutenção Anual por Período <b>R$ ".number_format(VALOR_TAXA,2,",",".")."</b> ".PHP_EOL.PHP_EOL;
$total_geral_taxa = 0;
while($rs_dados_taxa_row = pg_fetch_array($rs_dados_taxa)) {

        echo PHP_EOL.str_repeat("-",40).PHP_EOL.PHP_EOL;
    
        echo " UG_ID [".$rs_dados_taxa_row['ug_id']."] - Qtde [".$rs_dados_taxa_row['qtde']."] -  Depósitos Parados Totalizando  R$ [".number_format($rs_dados_taxa_row['total_sem_considerar_taxa'],2,",",".")."] => Valor Taxa R$ [".number_format($rs_dados_taxa_row['total'],2,",",".")."] ".PHP_EOL;
        $total_geral_taxa += $rs_dados_taxa_row['total'];
        
        if($rs_dados_taxa_row['qtde'] >= QTDE_DETALHAMENTO) {
                if(!isset($detalhamento)) {
                        $detalhamento = true;
                        $msg .= "Usuários que possuem mais de ".QTDE_DETALHAMENTO." depósitos: ".PHP_EOL.PHP_EOL;
                }//end if(!isset($detalhamento))
                $msg .= " Usuário [".$rs_dados_taxa_row['ug_id']."] - Email [".$rs_dados_taxa_row['ug_email']."] - Possui  [<b>".number_format($rs_dados_taxa_row['qtde'],0,",",".")."</b>] Depósitos Parados Totalizando  R$ [<b>".number_format($rs_dados_taxa_row['total_sem_considerar_taxa'],2,",",".")."</b>] - Valor da Taxa Anual a ser Cobrada  R$ [<b>".number_format($rs_dados_taxa_row['total'],2,",",".")."</b>] ".PHP_EOL;
        }//end if($rs_dados_taxa_row['qtde'] >= QTDE_DETALHAMENTO)
        
        //inicio do bloco que utiliza o saldo na fifo
        $msg_controle_transaction = "";
        $sql_begin = "BEGIN TRANSACTION ";
        $ret_begin = SQLexecuteQuery($sql_begin);
        if(!$ret_begin) $msg_controle_transaction .= "Erro ao iniciar transação.";
        $sql_insert_taxa = "INSERT INTO tb_pag_taxa_anual (ug_id, pta_data, pta_valor, pta_quantidade_depositos, pta_valor_total) VALUES (".intval($rs_dados_taxa_row['ug_id']).", NOW(),".$rs_dados_taxa_row['total'].",".$rs_dados_taxa_row['qtde'].",".$rs_dados_taxa_row['total_sem_considerar_taxa'].") RETURNING Currval('tb_pag_taxa_anual_pta_id_seq');";
        echo "SQL de INSERT na tabela de Taxa de Manutenção Anual: ".PHP_EOL.$sql_insert_taxa.PHP_EOL;
        $rs_insert_taxa = SQLexecuteQuery($sql_insert_taxa);
        if($rs_insert_taxa) {
                $rs_insert_taxa_row = pg_fetch_array($rs_insert_taxa);
                $venda_id = $rs_insert_taxa_row['currval'];
                //Definido que como ID Venda está relacionado a tabela tb_venda_games, 
                //iria confundir o conceito colocando valor do ID da taxa neste campo.
                //Portanto, ficou definido que será 0(Zero) para este campo como é o caso de estorno.
                $venda_id = 0;
                $valor_decrementar = $rs_dados_taxa_row['total'];
                $sql_busca_saldo_composicao = "select * from saldo_composicao_fifo where ug_id=".intval($rs_dados_taxa_row['ug_id'])." and scf_status=1 order by scf_data_deposito";
                echo "SQL que busca o FIFO: ".PHP_EOL.$sql_busca_saldo_composicao.PHP_EOL;
                $rs_busca_saldo = SQLexecuteQuery($sql_busca_saldo_composicao);
                while($rs_busca_saldo_row = pg_fetch_array($rs_busca_saldo)){
                        if(utilizadordeSaldo($valor_decrementar, $rs_busca_saldo_row, $venda_id, $msg_controle_transaction)) {
                                break;
                        }
                }//end while
                $sql_update_perfil_saldo = "UPDATE usuarios_games SET ug_perfil_saldo = ug_perfil_saldo-".$rs_dados_taxa_row['total']." WHERE ug_id = ".$rs_dados_taxa_row['ug_id'].";";
                echo "SQL que Atualiza o Saldo do Usuário Gamer: ".PHP_EOL.$sql_update_perfil_saldo.PHP_EOL;
                $rs_update_perfil_saldo = SQLexecuteQuery($sql_update_perfil_saldo);
                if(!$rs_update_perfil_saldo) {
                        $msg .=  "<br><br><br><b>Problema ao tentar Atualizar o Campo UG_PERFIL_SALDO na tabela de Gamer ID[".$rs_dados_taxa_row['ug_id']."].</b><br><br><br><br>";
                        $msg_controle_transaction = "Problema ao tentar Atualizar o Campo UG_PERFIL_SALDO na tabela de Gamer";
                } //end if($rs_update_perfil_saldo) 
        }//end if($rs_insert_taxa)
        else {
                $msg .=  "<br><br><br><b>Problema ao tentar inserir o registro da Taxa de Cobrança Anual.</b><br><br><br><br>";
                $msg_controle_transaction = "Problema ao tentar inserir o registro da Taxa de Cobrança Anual";
                echo "Problema ao tentar inserir o registro da Taxa de Cobrança Anual.".PHP_EOL.PHP_EOL.PHP_EOL;
        } //end else
        if($msg_controle_transaction == ""){
                $sql_commit = "COMMIT TRANSACTION ";
                $ret_commit = SQLexecuteQuery($sql_commit);
                if(!$ret_commit) $msg .= "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.\n<br></b></font><br>";
        }//end commit 
        else {
                $sql_commit = "ROLLBACK TRANSACTION ";
                echo PHP_EOL.str_repeat("=",10)."> ".$sql_commit.PHP_EOL.PHP_EOL;
                $msg .= str_repeat("=",10)."> ".$sql_commit.PHP_EOL;
                $ret_commit = SQLexecuteQuery($sql_commit);
                if(!$ret_commit) $msg .= "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
        }//end rollback
        //final do bloco que utiliza o saldo na fifo
    
} //end while
$msg .=  PHP_EOL.PHP_EOL."<b>RESUMO GERAL</b>".PHP_EOL.PHP_EOL."Total do Volume Envolvido em Arrecadação Através de Taxas: <b>R$ ".number_format($total_geral_taxa,2,",",".")." </b><br><br>".PHP_EOL.PHP_EOL;

echo str_replace('<br>', PHP_EOL, $msg);

if(!empty($msg)) {
    if(enviaEmail($email, $cc, $bcc, $subject, str_replace(PHP_EOL,'<br>'.PHP_EOL, $msg))) {
        echo "Email enviado com sucesso".PHP_EOL;
    }
    else {
        echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
    }
}//end if(!empty($msg))

echo str_repeat("_", 80) .PHP_EOL."Final de execução em: ". date('Y-m-d H:i:s'). PHP_EOL. "Elapsed time (total: ".  pg_num_rows($rs_dados_taxa)." gamers): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80).PHP_EOL;

//Fechando Conexão
pg_close($connid);


function utilizadordeSaldo(&$valor_decrementar, $rs_busca_saldo_row, $venda_id, &$msg) {
	$valor_decrementar -= $rs_busca_saldo_row['scf_valor_disponivel'];
	//Update da tabela de utilização de saldo
	$sql_update_saldo_composicao = "UPDATE saldo_composicao_fifo SET ";
	//Gerando registro da utilização do saldo
	$sql_saldo_utilizado = "INSERT INTO saldo_composicao_fifo_utilizado (scf_id, vg_id, scfu_valor) VALUES (".$rs_busca_saldo_row['scf_id'].",".intval($venda_id).",";
	if($valor_decrementar<0){
		$sql_update_saldo_composicao .= "scf_valor_disponivel=".round(($valor_decrementar*(-1)), 2);
		//Gerando registro da utilização do saldo
		$sql_saldo_utilizado .= round(($rs_busca_saldo_row['scf_valor_disponivel']+$valor_decrementar), 2).")";
	}
	else {
		$sql_update_saldo_composicao .= "scf_valor_disponivel=0, scf_status=0";
		//Gerando registro da utilização do saldo
		$sql_saldo_utilizado .= $rs_busca_saldo_row['scf_valor_disponivel'].")"; 
	}
	$sql_update_saldo_composicao .= " where scf_id=".$rs_busca_saldo_row['scf_id'];
	echo "SQL que atualiza o registro na tabela saldo_composicao_fifo:".PHP_EOL.$sql_update_saldo_composicao.PHP_EOL;
	$rs_update_saldo_composicao = SQLexecuteQuery($sql_update_saldo_composicao);
	if(!$rs_update_saldo_composicao) {
		 $msg .= "Erro ao atualizar a composição do Saldo (".$rs_busca_saldo_row['scf_id'].").".PHP_EOL;
	}
	else {
		//Gerando registro da utilização do saldo
		echo "SQL que insere o registro na tabela saldo_composicao_fifo_utilizado:".PHP_EOL.$sql_saldo_utilizado.PHP_EOL;
		$rs_saldo_utilizado = SQLexecuteQuery($sql_saldo_utilizado);
		if(!$rs_saldo_utilizado) {
			 $msg .= "Erro ao gerar ratreabilidade de utilização de Saldo (".$rs_busca_saldo_row['scf_id'].").".PHP_EOL;
		}
		if($valor_decrementar<=0){
			return true;
		}//end if($valor_decrementar<=0)
		else {
			return false;
		}
	}
}//end function utilizadordeSaldo()

?>