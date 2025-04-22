<?php
//header("Content-Type: text/html; charset=ISO-8859-1",true);
// Habilita Usuarios a Utilizar tipo de pagamento Cielo
// processa_Liberacao_Cielo.php 
// - Processa quantidade de pedidos de concluídos com sucesso num  total de $quantidade_vendas_completas

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 

set_time_limit(900);
ini_set('max_execution_time', 900);

//Quantidade de vendas completas
$quantidade_vendas_completas = 5;

$time_start_stats = getmicrotime();
$msg = "";

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Processa Habilitar opção Cielo meio de pagamento (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

$sql = "select vg_ug_id,count(*) as total
from tb_venda_games 
where vg_ug_id != 7909 
and vg_ultimo_status = 5 
group by vg_ug_id
having count(*) >= ".$quantidade_vendas_completas."
order by total desc";
echo "SQL de seleção:".PHP_EOL.$sql.PHP_EOL.PHP_EOL;
$rs = SQLexecuteQuery($sql);
$n_pedidos = pg_num_rows($rs);
echo "  Encontrado".(($n_pedidos>1)?"s":"")." : ".$n_pedidos." usuário".(($n_pedidos>1)?"s":"")." com mais de ".$quantidade_vendas_completas." vendas completas".PHP_EOL;

if(!$rs || pg_num_rows($rs) == 0) {
    $msg = "Nenhum usuários selecionado".PHP_EOL;
} else {
	$vetor_ug_id = array(); 
	while($rs_row = pg_fetch_array($rs)) {
		$vetor_ug_id[]  = $rs_row['vg_ug_id'];
	}
	if(count($vetor_ug_id)==0) {
		$msg = "Sem usuários para habilitar".PHP_EOL;
	}

	// Processa atualização de usuários
	if($msg == ""){
            
            //Monta a lista    
            $lista_ug_id = implode (',', $vetor_ug_id);
            $sqlFiltra = "select ug_id from usuarios_games where ug_id IN (".$lista_ug_id.") and ug_ativo=1 and ug_use_cielo <> 1";
            //echo "SQL de Filtro do que interessa:".PHP_EOL.$sqlFiltra.PHP_EOL.PHP_EOL;            
            $arrFiltro = array(); 
            $rsFiltra = SQLexecuteQuery($sqlFiltra);
            
            if($rsFiltra){
                while($rowFiltra = pg_fetch_array($rsFiltra)) {
                    $arrFiltro[]  = $rowFiltra['ug_id'];
                }
            }
            
            //Update pra zero
            $lista_ug_id_novos = implode (',', $arrFiltro);
            if(!empty($lista_ug_id_novos)) {
                $sql = "update usuarios_games set ug_use_cielo = 1 where ug_id IN (".$lista_ug_id_novos.") and ug_ativo=1;";
                echo PHP_EOL."SQL do Update somente do que ainda não estava liberado:".PHP_EOL.$sql.PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                if(!$ret) 
                    $msg = "Erro ao atualizar ug_use_cielo dos usuários (".$lista_ug_id_novos.").".PHP_EOL;
                else{

                    foreach($arrFiltro as $id){
                            $objEnvioEmailAutomatico = new EnvioEmailAutomatico('G','CieloLiberado');
                            $objEnvioEmailAutomatico->setUgID($id);
                            $objEnvioEmailAutomatico->MontaEmailEspecifico();
                            echo "Enviado para o id: $id.".PHP_EOL;
                    }//end foreach
                }
            }//end if(!empty($lista_ug_id_novos))
        }//end if($msg == "")

        
}//end else do if(!$rs || pg_num_rows($rs) == 0)

echo PHP_EOL.str_repeat("_", 80) . PHP_EOL."Elapsed time (total de novas liberações Cielo: ".count($arrFiltro)."): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) . PHP_EOL;
?>