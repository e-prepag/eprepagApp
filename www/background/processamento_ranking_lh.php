<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(18000) ;

$msg = "";

$data_de_hoje_start = date("Y-m-d H:i:s");

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php"; 

$webstring = "http://".$_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'];

$time_start = getmicrotime(); 

// busca as promoções vigentes
$query = "select promolh_id,
				to_char(promolh_data_inicio,'YYYY-MM-DD') as promolh_data_inicio,
				to_char(promolh_data_fim,'YYYY-MM-DD') as promolh_data_fim,
				opr_codigo,
				ogp_id 
			from promocoes_lanhouses 
			where promolh_data_inicio <= NOW() 
				  and (promolh_data_fim + interval '1 day') >= NOW()";
echo "Busca as promoções vigentes: ".$query.PHP_EOL;

$rs_query = SQLexecuteQUERY($query);

// Obter apenas a lista de promoções vigentes
$a_promolh_id = array();
$i = 0;
while ($promocoes_info = pg_fetch_array($rs_query)) {
	$a_promolh_id[$i]['ID']					= $promocoes_info['promolh_id'];
	$a_promolh_id[$i]['opr_codigo']			= $promocoes_info['opr_codigo'];
	$a_promolh_id[$i]['ogp_id']				= $promocoes_info['ogp_id'];
	$a_promolh_id[$i]['promolh_data_inicio']= $promocoes_info['promolh_data_inicio'];
	$a_promolh_id[$i]['promolh_data_fim']	= $promocoes_info['promolh_data_fim'];
	$i++;
}

//echo "count(a_promolh_id): ".count($a_promolh_id)." Promoções cadastradas vigentes".PHP_EOL;
//echo "<pre>".print_r($a_promolh_id,true)."</pre>";

for($i=0;$i<count($a_promolh_id);$i++) {

	$query = "select 
				ug.ug_id,
				sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas
			from tb_dist_venda_games vg 
				inner join tb_dist_venda_games_modelo vgm 
						on vgm.vgm_vg_id = vg.vg_id 
				inner join dist_usuarios_games ug 
						on ug.ug_id = vg.vg_ug_id 
			where vg.vg_ultimo_status='5' 
					and vg.vg_data_inclusao>='".$a_promolh_id[$i]['promolh_data_inicio']." 00:00:00' 
					and vg.vg_data_inclusao<='".$a_promolh_id[$i]['promolh_data_fim']." 23:59:59' 
					and vgm_opr_codigo='".$a_promolh_id[$i]['opr_codigo']."'";
	if(!empty($a_promolh_id[$i]['ogp_id'])) {
		$query .= "
					and vgm_ogp_id=".$a_promolh_id[$i]['ogp_id'];
	}
	$query .= "
			group by ug.ug_id
			order by vendas desc
			limit 20 
			offset 0";
        echo "Total de vendas(SQL): ".$query.PHP_EOL;


	//Inicia transacao
	if($msg == ""){
		$sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao iniciar transação.".PHP_EOL;
	}

	$rs_query = SQLexecuteQuery($query);
	$j = 1;
	while ($promocoes_info = pg_fetch_array($rs_query)) {
		//echo $j." : dentro while".PHP_EOL;
		if($msg == ""){

			$query_ranking = "INSERT INTO promocoes_lanhouses_rank (
									promolh_id,
									promolh_r_data_processamento, 
									promolh_r_rank, 
									ug_id,
									promolh_r_valor) 
							VALUES (
									".$a_promolh_id[$i]['ID'].", 
									NOW(), 
									".$j.", 
									".$promocoes_info['ug_id'].",
									".$promocoes_info['vendas'].");";
                        echo "Inserindo registro: ".$query_ranking.PHP_EOL;
			$rs_query_ranking = SQLexecuteQuery($query_ranking);
			
			if(!$rs_query_ranking){
				$stmp = "******* Erro ao inserir posição no ranking da LAN : ".$promocoes_info['ug_id'].PHP_EOL;	
				$msg .= $stmp;
				echo $stmp."".PHP_EOL;
			}
			else {
				if ($j == 1) {
					$query_ranking = "UPDATE promocoes_lanhouses SET ug_id = ".$promocoes_info['ug_id']." where promolh_id = ".$a_promolh_id[$i]['ID'].";";
                                        echo "Atualizando registro: ".$query_ranking.PHP_EOL;
					$rs_query_update = SQLexecuteQuery($query_ranking);
					if(!$rs_query_update){
						$stmp = "******* Erro ao atualizar primeiro colocado na tabela promocoes_lanhouses de ID : ".$a_promolh_id[$i]['ID'].PHP_EOL;	
						$msg .= $stmp;
						echo $stmp."".PHP_EOL;
					}
				}
			}
		}
		$j++;
	}

	//Finaliza transacao
	if($msg == ""){
			$sql = "COMMIT TRANSACTION ";
                        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.\n";
	} else {
			$sql = "ROLLBACK TRANSACTION ";
                        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
	}

        echo str_repeat("-",80).PHP_EOL;
}// fim do ciclo for por User Gamer

echo $msg.PHP_EOL;
echo "Termina cadastro inciado em '$data_de_hoje_start' e terminado '".date("Y-m-d H:i:s")."'".PHP_EOL;
echo "Duração:" . number_format(getmicrotime() - $time_start, 2, '.', '.') .PHP_EOL;
echo str_repeat("=",80).PHP_EOL;

?>