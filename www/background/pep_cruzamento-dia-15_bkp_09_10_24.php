<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
// Cruzamento de Dados de Usuários com Compras no Último Mês que são Pessoas Politicamente Expostas
// pep_cruzamento.php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(1200);
ini_set('max_execution_time', 1200); 

//Data considerada
$data_inicio = mktime(0, 0, 0, date('n')-1,  date('d'), date('Y'));
$data_fim = mktime(0, 0, 0, date('n'),  date('d'), date('Y'));
//Teste 55 (Homologação)
//$data_inicio = mktime(0, 0, 0, 8,  date('d'), 2012);
//$data_fim = mktime(0, 0, 0, 11,  date('d'), 2012);
$dataClickIni = date('Y-m-d',$data_inicio);
$dataClickFim = date('Y-m-d',$data_fim);

// Dados do Email
$email  = "rc@e-prepag.com.br,rc1@e-prepag.com.br, lucas.alexandre@gokeitecnologia.com.br";
$cc     = "glaucia@e-prepag.com.br";
$bcc    = "wagner@e-prepag.com.br";
$subject= "Cruzamento de Dados PEP";
$msg    = "";

require_once "/www/includes/main.php";
require_once "/www/includes/gamer/main.php"; 
include_once "/www/includes/complice/functions.php";

$time_start_stats = getmicrotime();

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Cruzamento de Dados de Usuários com Compras no Último Mês que são Pessoas Politicamente Expostas (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

//Publishers Exigem CPFs como Obrigatórios
$vetorPublisher = levantamentoPublisherObrigatorioCPF($vetorPublisherLegenda);

//Buscando CPF e nomes envolvido em transações de vendas
//echo "SQL :".$sql.PHP_EOL;
$limite = 1000;
$offset = 0;

$sql = "select ug_nome,tipo,ug_cpf from ( 
			(select 
					'Gamer' as tipo,
					ug_cpf,
					UPPER(ug_nome_cpf) as ug_nome
			from tb_venda_games vg 
					inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
			where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
					and vg.vg_data_concilia >= '".$dataClickIni." 00:00:00'
					and vg.vg_data_concilia <= '".$dataClickFim." 23:59:59'
					and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
					and vgm_opr_codigo IN (".implode(",", $vetorPublisher).")
			group by tipo, ug_nome_cpf, ug_cpf)

		union all

			(select 
					'CPF na Venda do PDV' as tipo,
					vgm_cpf as ug_cpf, 
					UPPER(vgm_nome_cpf) as ug_nome
			from tb_dist_venda_games vg 
					inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
			where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
					and vg.vg_data_inclusao >= '".$dataClickIni." 00:00:00'
					and vg.vg_data_inclusao <= '".$dataClickFim." 23:59:59'
					and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
			group by tipo, vgm_nome_cpf, vgm_cpf) 

		union all

			(select 
					'PDV - Representante Legal' as tipo,
					ug_repr_legal_cpf as ug_cpf, 
					UPPER(ug_repr_legal_nome) as ug_nome
			from tb_dist_venda_games vg 
					inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id
			where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
					and vg.vg_data_inclusao >= '".$dataClickIni." 00:00:00'
					and vg.vg_data_inclusao <= '".$dataClickFim." 23:59:59'
					and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
			group by tipo, ug_repr_legal_nome, ug_repr_legal_cpf) 

		union all

			(select 
					'PDV - Sócio' as tipo,
					ugs_cpf as ug_cpf,
					UPPER(ugs_nome) as ug_nome
			from tb_dist_venda_games vg 
					inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					inner join dist_usuarios_games_socios ugc on ugc.ug_id = vg.vg_ug_id
			where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
					and vg.vg_data_inclusao >= '".$dataClickIni." 00:00:00'
					and vg.vg_data_inclusao <= '".$dataClickFim." 23:59:59'
					and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
			group by tipo, ugs_nome, ugs_cpf) 

		union all

			(select 
					'CPF no Gift Card' as tipo,
					picc_cpf as ug_cpf, 
					UPPER(picc_nome) as ug_nome
			from pins_integracao_card_historico
				left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
			where pin_status = '4' 
					and pih_codretepp = '2'
					and pih_data >= '".$dataClickIni." 00:00:00'
					and pih_data <= '".$dataClickFim." 23:59:59'
					and pih_id IN (".implode(",", $vetorPublisher).") 
			group by tipo, picc_nome, picc_cpf)

		union all

			(select 
					'CPF no Boleto Express' as tipo,
					vgcbe_cpf as ug_cpf, 
					UPPER(vgcbe_nome_cpf) as ug_nome
			from tb_venda_games_cpf_boleto_express
				inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
				inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
			where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
					and vgcbe_data_inclusao >= '".$dataClickIni." 00:00:00'
					and vgcbe_data_inclusao <= '".$dataClickFim." 23:59:59'
					and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
			group by tipo, vgcbe_nome_cpf, vgcbe_cpf)
) tabelaUnion 
			group by ug_nome, tipo, ug_cpf  
			order by ug_nome;
";
$rs = SQLexecuteQuery($sql);
$total_de_registros = pg_num_rows($rs);
$dadosTotais = pg_fetch_all($rs);

function buscarray($cpfs){

    $sql = "SELECT * FROM pep WHERE cpf in($cpfs);";
	$rsEncontrado = SQLexecuteQuery($sql);
	$rsEncontradoRow = pg_fetch_all($rsEncontrado);
	if($rsEncontradoRow != false){
		$encontrados = [];
		foreach($rsEncontradoRow as $key => $value){
		    $encontrados[$key]["nome"] = $value["nome"];
			$encontrados[$key]["cpf"] = $value["cpf"];
			$encontrados[$key]["funcao"] = $value["descricao_funcao"];
			
			return $encontrados;
		}
	}else{
	    return "Nenhum registro encontrado";
	}

}
$dadosin = "";
for($num = 0; $num < count($dadosTotais); $num++){

   $dadosin .= str_replace("-","",str_replace(".", "", $dadosTotais[$num]["ug_cpf"])).",";

}
$search = substr($dadosin, 0, -1); 
$retorno = buscarray($search);
//$retorno = array(array('nome'=>'ABRAHAO DA COSTA PEREIRA','funcao'=>'teste','cpf'=>'03201373176'),array('nome'=>'ABADIO PEREIRA TRINDADE','funcao'=>'teste','cpf'=>'536.848.181-00'),array('nome'=>'ADRIANA VALERIO FERNANDES','funcao'=>'teste','cpf'=>'003.589.072-09'),array('nome'=>'ADONILDE NETO DA SILVA','funcao'=>'teste','cpf'=>'702.287.861-77'),array('nome'=>'AILTON RAMOS DE OLIVEIRA','funcao'=>'teste','cpf'=>'321.950.378-06'));

//var_dump($dadosTotais);
//var_dump($encontrado);
//exit;
if($retorno != "Nenhum registro encontrado"){

$cabecalho = "

        <h2 style='text-align:center;border:solid 1px black;width:90%;margin:0 auto;display:block;margin-top:40px;'>Resultado do alerta PEP</h2>
        <table style='padding:20px;background-color:#ddd;margin: 0 auto;width: 90%;' class='table table-bordered top20' >
        <thead class=''>
            <tr>
               
				<th style='padding:5px;'>Tipo EPP</th>
				<th style='padding:5px;'>CPF</th>
				<th style='padding:5px;'>Nome - Nosso Banco de Dados</th>
                <th style='padding:5px;'>Nome Encontrado - Lista PEP</th>
				<th style='padding:5px;'>Tipo PEP</th>	
                <th style='padding:5px;'>Data de geração</th>				
            </tr>
        </thead>
";

$exibicaoDadosProblemas = "<tbody title='Nao Encontrado'> ";

$encontrado = [];
$count = 0;
foreach($retorno as $key => $value){
       
        for($i = 0;$i < count($dadosTotais);$i++){

		    if($dadosTotais[$i]["ug_cpf"] == $value["cpf"]){
			
			   $encontrado[$count]["cpf"] = $dadosTotais[$i]["ug_cpf"];
			   $encontrado[$count]["nome"] = $dadosTotais[$i]["ug_nome"];
			   $encontrado[$count]["tipo"] = $dadosTotais[$i]["tipo"];
			   $exibicaoDadosProblemas .= " 
					<tr class='trListagem'>
						<td style='padding:5px;'>".$encontrado[$count]["tipo"]."</td>
						<td style='padding:5px;'>".$encontrado[$count]["cpf"]."</td>
						<td style='padding:5px;'>".$encontrado[$count]["nome"]."</td>
						<td style='padding:5px;'>".$value["nome"]."</td>
						<td style='padding:5px;'>".$value["funcao"]."</td>
						<td style='padding:5px;'>".date("d/m/Y H:i:s")."</td>
					</tr>
				";   
				$count++;
			   
			}		
			
        }		
}
 //var_dump($retorno);
 //var_dump($encontrado);
 //exit;
$exibicaoDadosProblemas .= "</tbody>";
$msg = $cabecalho.$exibicaoDadosProblemas."</table><h4 style='text-align:center;border:solid 1px black;width:90%;margin:0 auto;display:block;'>Total de registros verificados: <span style='color:red;padding:10px;text-align:center;'>$total_de_registros</span></h4>";

    if(enviaEmail($email, $cc, $bcc, $subject, $msg)) {
		echo "Email enviado com sucesso".PHP_EOL;
	}
	else {
		echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
	}

}else{

$cabecalho = "

        <h2 style='text-align:center;border:solid 1px black;width:90%;margin:0 auto;display:block;margin-top:40px;'>Resultado do alerta PEP</h2>
        <table style='padding:20px;background-color:#ddd;margin: 0 auto;width:90%;' class='table table-bordered top20' >
        <thead class=''>
            <tr>
                <th style='padding:10px;'>Total Nomes Levantados</th>
				<th style='padding:10px;'>Mensagem</th>
                <th style='padding:10px;'>Data de geração</th>
            </tr>
        </thead>
";

$exibicaoDadosProblemas = " 
					<tbody title='Nao Encontrado'>
						<tr class='trListagem'>
						    <td style='color:red;padding:10px;text-align:center;'>".$total_de_registros."</td>
							<td style='padding:10px;text-align:center;'>".$retorno."</td>
							<td style='padding:10px;text-align:center;'>".date("d/m/Y H:i:s")."</td>
					    </tr>
					</tbody>
";

$msg = $cabecalho.$exibicaoDadosProblemas."</table>";
     

	if(enviaEmail($email, $cc, $bcc, $subject, $msg)) {
		echo "Email enviado com sucesso".PHP_EOL;
	}
	else {
		echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
	}

}

pg_close($connid);

?>