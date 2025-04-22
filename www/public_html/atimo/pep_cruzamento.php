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
$email  = "felipe.farias@easygroupit.com"; //,rc@e-prepag.com.br,rc1@e-prepag.com.br
$cc     = ""; //glaucia@e-prepag.com.br
//$bcc    = "wagner@e-prepag.com.br";
$subject= "Cruzamento de Dados PEP";
$msg    = "";

require_once "/www/includes/main.php";
require_once "/www/includes/gamer/main.php"; 
require_once "/www/class/util/Util.class.php";
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
                        and vg.vg_data_concilia >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vg.vg_data_concilia <= '".Util::getData($dataClickFim, true)." 23:59:59'
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
                        and vg.vg_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vg.vg_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
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
                        and vg.vg_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vg.vg_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
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
                        and vg.vg_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vg.vg_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
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
                        and pih_data >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and pih_data <= '".Util::getData($dataClickFim, true)." 23:59:59'
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
                        and vgcbe_data_inclusao >= '".Util::getData($dataClickIni, true)." 00:00:00'
                        and vgcbe_data_inclusao <= '".Util::getData($dataClickFim, true)." 23:59:59'
                        and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                group by tipo, vgcbe_nome_cpf, vgcbe_cpf)
    ) tabelaUnion 
                group by ug_nome, tipo, ug_cpf  
                order by ug_nome;
";
$rs = SQLexecuteQuery($sql);

$total_de_registros = pg_num_rows($rs);
$dadosTotais = pg_fetch_all($rs);
//var_dump($dadosTotais);
//exit;

?>
<table>
<?php 
            if(isset($rs) && $rs) {
                $total_de_registros = pg_num_rows($rs);
                if($total_de_registros > 0) {

		$cabecalho = "
		<h2 style='text-align:center;border:solid 1px black;width:90%;margin:0 auto;display:block;margin-top:40px;'>Resultado do alerta PEP</h2>
		<table style='padding:20px;background-color:#ddd;margin: 0 auto;width: 90%;' class='table table-bordered top20'><thead class=''>
          
            <tr>
                <th style='padding:5px;'>Tipo EPP</th>
                <th style='padding:5px;'>CPF</th>
                <th style='padding:5px;'>Nome - Nosso Banco de Dados</th>
                <th style='padding:5px;'>Nome Encontrado - Lista PEP</th>
                <th style='padding:5px;'>Tipo PEP</th>
            </tr>
        </thead>
		<tbody title='Nome Encontrado'>
		";
        
                    while ($rsRow = pg_fetch_array($rs)) {
                        
                       // Eliminando espaços em branco inicio e fim da String
                       $rsRow['ug_nome'] = trim($rsRow['ug_nome']);

                       // Eleiminado mascara e convertendo para inteiro
                       $cpf_aux = str_replace("-","",str_replace(".", "", $rsRow['ug_cpf']))*1;
                       
                       // Verificando conteudo válido oara pesquisa
                       if($cpf_aux > 0) {
                        
                            //Buscando insidencia na lista da PEP
                            $sql = "SELECT * FROM pep WHERE cpf = ". str_replace("-","",str_replace(".", "", $rsRow['ug_cpf']))*1 ." and enviado_email = 0;";
                            //echo $sql."<br>";
                            $rsEncontrado = SQLexecuteQuery($sql);
                            // Verificando se foi encontrado algum nome
                            if(isset($rsEncontrado) && pg_num_rows($rsEncontrado) > 0) {
                                // Exibindo todas incidencias de nomes
                                while ($rsEncontradoRow = pg_fetch_array($rsEncontrado)) {

									$exibicaoDadosProblemas .= "
										<tr class='trListagem'>
											<td style='padding:5px;text-align: center;'>".$rsRow['tipo']."</td>
											<td style='padding:5px;text-align: center;'>".$rsRow['ug_cpf']."</td>
											<td style='padding:5px;text-align: center;'>".$rsRow['ug_nome']."</td>
											<td style='padding:5px;text-align: center;'>".$rsEncontradoRow['nome']."</td>
											<td style='padding:5px;text-align: center;'>".$rsEncontradoRow['descricao_funcao']."</td>
										</tr>";
									
									$sqlNew = "UPDATE pep SET enviado_email = 1 WHERE cpf = ".str_replace("-","",str_replace(".", "", $rsRow['ug_cpf']))*1 .";";
									SQLexecuteQuery($sqlNew);
                                }//end while ($rsEncontradoRow = pg_fetch_array($rsEncontrado))
                            }//end if(pg_num_rows($rsEncontrado) > 0) 
                        }//end if($cpf_aux > 0)
                    }//end while 
                }//end if(pg_num_rows($rs) > 0)
                else {
            $exibicaoDadosProblemas = "<tr>
				<td>Nenhum registro encontrado.</td>
				<td>Nenhum registro encontrado.</td>
                <td>Nenhum registro encontrado.</td>
				<td>Nenhum registro encontrado.</td>
				<td>Nenhum registro encontrado.</td>
            </tr>";

                }//end else do if(pg_num_rows($rs) > 0) 
            }elseif(isset($rs)){

            $exibicaoDadosProblemas = "<tr>
                <td colspan='3'>Erro Ao buscar informações no banco de dados.</td>
            </tr>";

            }?>

<?php
$data_execucao = date("d/m/Y H:i:s");
if($exibicaoDadosProblemas == NULL) $exibicaoDadosProblemas = "<tr>
				<td>Nenhum registro encontrado.</td>
				<td>Nenhum registro encontrado.</td>
                <td>Nenhum registro encontrado.</td>
				<td>Nenhum registro encontrado.</td>
				<td>Nenhum registro encontrado.</td>
            </tr>";
$msg = $cabecalho.$exibicaoDadosProblemas."</table><h4 style='text-align:center;border:solid 1px black;width:90%;margin:0 auto;display:block;'>Total de registros verificados: <span style='color:red;padding:10px;text-align:center;'>$total_de_registros</span> Data de Geração: $data_execucao</h4>";
     

	if(enviaEmail($email, $cc, $bcc, $subject, $msg)) {
		echo "Email enviado com sucesso".PHP_EOL;
	}
	else {
		echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
	}

pg_close($connid);

?>


