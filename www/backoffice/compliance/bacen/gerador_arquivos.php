<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
set_time_limit(3600);

// Incluindo a Classe geradora do Arquivo
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "includes/complice/functions.php";
require_once $raiz_do_projeto . "class/util/classFilePosition.php";

//=========  Mês/Ano considerado no Elaboração dos Arquivos
$currentmonth = mktime(0, 0, 0, date('n')-1, 1, date('Y'));
$mesAno = date('m/Y',$currentmonth);

//=========  Data de doze meses anteriores a data Mês Ano considerado => 13 em função de mes anterior (-1) e 12 meses antes
$dozeMesesAnteriores = mktime(0, 0, 0, date('n')-13, 1, date('Y'));

//========= Variável contendo o Ano Mês inicio das operações para efeitos de 
//========= exclusão nos teste Trimenetrais e Semestrais por não estarem completos
$dataInicioOperacao = 201407;

// Split ano/mes
list($mes, $ano) = explode("/", $mesAno);

// Exibindo o Período de Apuração
echo "<br><b>Mês/Ano do período de apuração: [<span style='color: red'>".$mesAno."</span>]</b><br><br><br>\n";

//Publishers Já em Operação constantes em arquivos BACEN anteriores INTERNacionais
$vetorPublisher = levantamentoPublisherOperantes($ano,$mes);

//Publishers novos nunca antes contou nos arquivos BACEN INTERNacionais
$vetorPublisherNovos = levantamentoPublisherNovosOperantes($ano,$mes);

// Instanciando a variavel para verificação de novos Publishers
$verificadorPublishersNovos = implode(",", $vetorPublisherNovos);

//Buscando dados de cotação no Banco de Dados
$sql = "select cd_cotacao,opr_codigo,cd_freeze from cotacao_dolar where cd_data = '".date('Y-m-d',$currentmonth)." 00:00:00' and opr_codigo IN (".implode(",", $vetorPublisher).(!empty($verificadorPublishersNovos)?",".$verificadorPublishersNovos:"").");";
//echo $sql."<br>";
$rs = SQLexecuteQuery($sql);
if($rs) {
    $testeCotacaoZerada = false;
    while($rs_row = pg_fetch_array($rs)) {
        $vetorCotacaoUSS[$rs_row['opr_codigo']] = $rs_row['cd_cotacao'];
        if($rs_row['cd_cotacao'] == 0) {
            echo "Publisher de ID [".$rs_row['opr_codigo']."]<br>".PHP_EOL;
        }//end if($rs_row['cd_cotacao'] == 0)
    } //end while 
    if($testeCotacaoZerada) {
        die("Obrigatório o cadastramento de cotação diferente de Zero para os Publishers relacionados acima.");
    }//end if($testeCotacaoZerada)
}//end if($rs)

//Verificando se existe o cadastro de cotação do dolar para geração dos arquivos BACEN
if(isset($vetorCotacaoUSS) && count($vetorCotacaoUSS) == 0){
    die("Antes de gerar os arquivos deve ser cadastrados as cotações de dólar no sistema!");
}//end if(count($vetorCotacaoUSS))

//Totalizando publisher internacionais e verificando se bate os totais de publisher com o cadastro de cotações.
$totalPublisherInternacionais = 0;
foreach ($vetorPublisher as $key => $value) {
    if(!empty($value)) {
        $totalPublisherInternacionais++;
    }
}
foreach ($vetorPublisherNovos as $key => $value) {
    if(!empty($value)) {
        $totalPublisherInternacionais++;
    }
}
if($totalPublisherInternacionais != count($vetorCotacaoUSS)) {
    die("Existe divergencias entre o total de publisher com movimento no mês de referencia e o total de publisher cadastrado com cotações!");
}//end if($totalPublisherInternacionais != count($vetorCotacaoUSS)) 

//Dados Necessários
$identificacaoRegisto5816_I = '#A1';    // Identificação do registro => 1- REGISTRO DE IDENTIFICAÇÃO DA ADMINISTRADORA/PROCESSADORA
$identificacaoRegisto5816_F = '@1';     // Identificação do registro => 4- REGISTRO DE CONTROLE FINAL 
$tipoCartao = 'db';                     // Tipo do cartão para o documento 5816
$codigoMoeda = '220';                   // Código da Moeda fico 220 = Dolar Americano
$cnpjEPP = '19037276000172';            // CNPJ da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$codigoNossaBandeira = '4';             // Código de Nossa Bandeira de 1 (uma) posição para o documento 5816



//Verificando se faltam Dados
if (verificaFaltaCPFNome($vetorPublisher, date("t",mktime(0, 0, 0, ($mes*1), 1, $ano)), $rs_dados_incompletos, $vetorPublisherNovos)) {
    echo "<hr><b>Faltam Dados de CPF e Nome: (TOTAL [".pg_num_rows($rs_dados_incompletos)."] Usuários)</b><br><br>\n";
    while($rs_dados_incompletos_row = pg_fetch_array($rs_dados_incompletos)) {
            echo " ".$rs_dados_incompletos_row['tipo']." => ID: ".$rs_dados_incompletos_row['ug_id']." DATA: ".$rs_dados_incompletos_row['data_transacao']." Email: ".$rs_dados_incompletos_row['ug_email']."<br>\n";
    } //end while
    die("------- Ajustar os dados acima antes de gerar os Arquivos -------------<br><br>\n");
} //end if (verificaFaltaCPFNome($rs_dados_incompletos))


//Verificando se o CPF Informado possui uma estrutura correta
if (verificaCPFValido($vetorPublisher, date("t",mktime(0, 0, 0, ($mes*1), 1, $ano)), $rs_dados, $vetorPublisherNovos)) {
    $exibicaoDadosProblemas = "";
    $i = 0;
    while($rs_dados_row = pg_fetch_array($rs_dados)) {
        if(!verificaCPF_BACEN($rs_dados_row['ug_cpf'])) {
            $exibicaoDadosProblemas .= " ".$rs_dados_row['tipo']." => ID: ".$rs_dados_row['ug_id']." CPF Inválido: ".$rs_dados_row['ug_cpf']."<br>\n";
            $i++;
        }// end if(!verificaCPF_BACEN($rs_dados_row['ug_cpf']))
    } //end while
    if($i > 0) {
        echo "<hr><b>Dados de CPF Incorretos: (TOTAL [".$i."] CPFs)</b><br><br>\n";
        echo $exibicaoDadosProblemas;
        die("------- Ajustar os dados acima antes de gerar os Arquivos -------------<br><hr>\n");
    }
} //end if (verificaCPFValido())


// Teste de Abortagem
$testeData = $ano.$mes;
if($testeData < $dataInicioOperacao) {
    die("O mês ano deve ser obrigatóriamente superior a ".$dataInicioOperacao." (AAAAMM).<br>\n");
}// end if($testeData < 201403)





//================================== Inicio da Geração do Arquivo Layout 5816
echo "<b>Gerando Arquivo Mensal</b><br><br>\n";

$contFatura = 0;        // Quantidade de regitro de faturas
$contDetalhamento = 0;  // Quantidade de regitro de detalhamento
$listaUG_CPF = "";   // Lista contendo os IDs de usuários que gastram mais de 10.000 dolares
$vetorTotaisAcimaLimite = array();
if (verificaLimiteDetalhamento(10000,$rsTeste)) {
    echo "<b>[<span style='color: red'>ATENÇÃO: Existe CPF que ultrapassou os US$ 10.000 no mês</span>]</b><br><br><br>\n";
    while($rsTeste_row = pg_fetch_array($rsTeste)) {
        if(strlen($listaUG_CPF) == 0) {
            $listaUG_CPF = "'".$rsTeste_row['ug_cpf'];
        }
        else {
            $listaUG_CPF .= "', '".$rsTeste_row['ug_cpf'];
        }
        $vetorTotaisAcimaLimite[$rsTeste_row['ug_cpf']] = $rsTeste_row['total_geral'];
    }//end while
    $listaUG_CPF .= "'";
}//end if (verificaLimiteDetalhamento(1000,$rsTeste))

$listaCPFsCOAF = "";
if (verificaLimiteCOAF(5000,$rsCOAF)) {
    echo "<b>[<span style='color: red'>ATENÇÃO: Existe CPF que ultrapassou os R$ 5.000,00 no mês e devem ser completados os dados de cadastro e analisados em relação ao COAF.</span>]</b><br><br><br>\n";
    while($rsCOAF_row = pg_fetch_array($rsCOAF)) {
        if(strlen($listaCPFsCOAF) == 0) {
            $listaCPFsCOAF = "LISTA: <BR>".$rsCOAF_row['ug_cpf']."<BR>";
        }
        else {
            $listaCPFsCOAF .= $rsCOAF_row['ug_cpf']."<BR>";
        }
    }//end while
    echo $listaCPFsCOAF;
}//end if (verificaLimiteCOAF(1000,$rsCOAF))

$nomeArquivo = 'AMTF101_5816_'.$mes.$ano.'.txt';
$nomeArquivo5816[] = $nomeArquivo;

unset($file);
$file = new FilePosition($nomeArquivo);


//Buscando Publisher que possuem totalização por utilização
$vetorPublisherPorUtilizacao = levantamentoPublisherComFechamentoUtilizacaoInternacional();

if(count($vetorPublisherPorUtilizacao)>0) {
    $where_opr_venda_lan = " AND ( CASE ";
    $where_opr_venda_lan_negativa = " AND ( CASE ";
    $where_opr_utilizacao_lan = " AND ( CASE ";
    foreach ($vetorPublisherPorUtilizacao as $opr_codigo => $opr_data_inicio_contabilizacao_utilizacao){ 
        //echo "ID: ".$opr_codigo." => DATA: [".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."]<br>";
        $where_opr_venda_lan .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao < '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
        $where_opr_venda_lan_negativa .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
        $where_opr_utilizacao_lan .= "  WHEN pih_id = $opr_codigo THEN pih_data >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
    }//end foreach
    $where_opr_venda_lan .= " ELSE vg.vg_data_inclusao > '2008-01-01 00:00:00' END )";
    $where_opr_venda_lan_negativa .= " ELSE FALSE END )";
    $where_opr_utilizacao_lan .= "  ELSE FALSE END ) ";
} //end if(count($vetorPublisherPorUtilizacao)>0)
else {
    $where_opr_venda_lan = "";
    $where_opr_venda_lan_negativa = "";
    $where_opr_utilizacao_lan = "";
}//end else do if(count($vetorPublisherPorUtilizacao)>0)



//=========================================================================================================================
//1- REGISTRO DE IDENTIFICAÇÃO DA ADMINISTRADORA/PROCESSADORA
//=========================================================================================================================
// Cabeçalho
unset($vetorHeader);
$vetorHeader = array (
                     0 => array('name' => $identificacaoRegisto5816_I,
                                'size' => 3
                                ),
                     1 => array('name' => '5816',
                                'size' => 4
                                ),
                     2 => array('name' => $cnpjEPP, // CNPJ E-PREPAG ADMINISTRADORA DE CARTOES (Administradora)
                                'size' => 14
                                ),
                     3 => array('name' => $cnpjEPP, // CNPJ E-PREPAG ADMINISTRADORA DE CARTOES (Processadora)
                                'size' => 14
                                ),
                     4 => array('name' => $ano.$mes,
                                'size' => 6
                                ),
                     5 => array('name' => ' ',
                                'size' => 104
                                ),
                );

$file->setVetorHeader($vetorHeader);

//=========================================================================================================================
//2- REGISTRO DE DADOS (FATURA)
//=========================================================================================================================
// Buscando informações 
$sql = "select ug_cpf, ug_nome, sum(total_em_dolar) as total_em_dolar from ( 
            (select 
                    ug_cpf, 
                    UPPER(ug_nome_cpf) as ug_nome, 
                    (CASE ".PHP_EOL;
foreach ($vetorPublisher as $key => $value) {
    $sql .= "                     WHEN vgm_opr_codigo = ".$value." THEN sum(vgm.vgm_valor * vgm.vgm_qtde)/".$vetorCotacaoUSS[$value]." ".PHP_EOL;
}//end foreach 
$sql .= "\t\t\t   END) as total_em_dolar 
            from tb_venda_games vg 
                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vg.vg_data_concilia >= '".$ano."-".$mes."-01 00:00:00'
                    and vg.vg_data_concilia <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") ";
if(!empty($listaUG_CPF)) {
    $sql .= " and ug_cpf NOT IN (".$listaUG_CPF.")
            ";
    
}//end if(!empty($listaUG_CPF))
$sql .= "
            group by ug_cpf, ug_nome_cpf, vgm_opr_codigo)
            
        union all
            
            (select 
                    vgm_cpf as ug_cpf, 
                    UPPER(vgm_nome_cpf) as ug_nome, 
                    (CASE ".PHP_EOL;
foreach ($vetorPublisher as $key => $value) {
    $sql .= "                     WHEN vgm_opr_codigo = ".$value." THEN sum(vgm.vgm_valor * vgm.vgm_qtde)/".$vetorCotacaoUSS[$value]." ".PHP_EOL;
}//end foreach 
$sql .= "\t\t\t   END) as total_em_dolar 
            from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."'   ".$where_opr_venda_lan."
                    and vg.vg_data_inclusao >= '".$ano."-".$mes."-01 00:00:00'
                    and vg.vg_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") ";
if(!empty($listaUG_CPF)) {
    $sql .= " and vgm_cpf NOT IN (".$listaUG_CPF.")
            ";
    
}//end if(!empty($listaUG_CPF))
$sql .= "
            group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo) ";
    
//Contabilizando vendas por utilização de PINs Publisher
if(count($vetorPublisherPorUtilizacao)>0) {
    $sql .= "
        
        union all

            (select 
                    vgm_cpf as ug_cpf, 
                    UPPER(vgm_nome_cpf) as ug_nome, 
                    (CASE ".PHP_EOL;
    foreach ($vetorPublisher as $key => $value) {
        $sql .= "                     WHEN vgm_opr_codigo = ".$value." THEN sum(vgm.vgm_valor)/".$vetorCotacaoUSS[$value]." ".PHP_EOL;
    }//end foreach 
    $sql .= "\t\t\t   END) as total_em_dolar 
            from tb_dist_venda_games vg 
                 inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                 inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
                 inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."'
                 and pin_status = '8'
                 and pih_codretepp='2'
                 ".$where_opr_venda_lan_negativa."
                 and pih_data >= '".$ano."-".$mes."-01 00:00:00'
                 and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                 and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                 ".$where_opr_utilizacao_lan." ";
    if(!empty($listaUG_CPF)) {
        $sql .= " and vgm_cpf NOT IN (".$listaUG_CPF.")
                ";

    }//end if(!empty($listaUG_CPF))
    $sql .= "
            group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo) 
            ";
}//end if(count($vetorPublisherPorUtilizacao)>0)
 
$sql .=" 
            
        union all
            
            (select 
                    picc_cpf as ug_cpf, 
                    UPPER(picc_nome) as ug_nome, 
                    (CASE ".PHP_EOL;
foreach ($vetorPublisher as $key => $value) {
    $sql .= "                     WHEN pih_id = ".$value." THEN sum(pih_pin_valor/100)/".$vetorCotacaoUSS[$value]." ".PHP_EOL;
}//end foreach 
$sql .= "\t\t\t   END) as total_em_dolar
            from pins_integracao_card_historico
                left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
	    where pin_status = '4' 
		    and pih_codretepp = '2'
                    and pih_data >= '".$ano."-".$mes."-01 00:00:00'
                    and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and pih_id IN (".implode(",", $vetorPublisher).") ";
if(!empty($listaUG_CPF)) {
    $sql .= " and picc_cpf NOT IN (".$listaUG_CPF.")
            ";
    
}//end if(!empty($listaUG_CPF))
$sql .= " 
            group by picc_cpf, picc_nome, pih_id)
            
        union all
            
            (select 
                    vgcbe_cpf as ug_cpf, 
                    UPPER(vgcbe_nome_cpf) as ug_nome, 
                    (CASE ".PHP_EOL;
foreach ($vetorPublisher as $key => $value) {
    $sql .= "                     WHEN vgm_opr_codigo = ".$value." THEN sum(vgm_valor * vgm_qtde)/".$vetorCotacaoUSS[$value]." ".PHP_EOL;
}//end foreach 
$sql .= "\t\t\t   END) as total_em_dolar
            from tb_venda_games_cpf_boleto_express
                inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
	    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vgcbe_data_inclusao >= '".$ano."-".$mes."-01 00:00:00'
                    and vgcbe_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") ";
if(!empty($listaUG_CPF)) {
    $sql .= " and vgcbe_cpf NOT IN (".$listaUG_CPF.")
            ";
    
}//end if(!empty($listaUG_CPF))
$sql .= " 
            group by vgcbe_cpf, vgcbe_nome_cpf, vgm_opr_codigo)
        ";
if(!empty($verificadorPublishersNovos)) {
    foreach ($vetorPublisherNovos as $key => $value) {
        //echo "Key: $key -- value: $value <br>";
        $sql .= "

        union all

             (select 
                    ug_cpf, 
                    UPPER(ug_nome_cpf) as ug_nome, 
                    (sum(vgm.vgm_valor * vgm.vgm_qtde)/".$vetorCotacaoUSS[$value].") as total_em_dolar 
            from tb_venda_games vg 
                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vg.vg_data_concilia >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                    and vg.vg_data_concilia <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                    and vgm_opr_codigo = ".$value." ";
        if(!empty($listaUG_CPF)) {
            $sql .= " and ug_cpf NOT IN (".$listaUG_CPF.")
                    ";

        }//end if(!empty($listaUG_CPF))
        $sql .= "
            group by ug_cpf, ug_nome_cpf, vgm_opr_codigo)

        union all

            (select 
                    vgm_cpf as ug_cpf, 
                    UPPER(vgm_nome_cpf) as ug_nome, 
                    (sum(vgm.vgm_valor * vgm.vgm_qtde)/".$vetorCotacaoUSS[$value].") as total_em_dolar 
            from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."'  ";
        if (array_key_exists($value, $vetorPublisherPorUtilizacao)) {
            $sql .= "
                    and vg.vg_data_inclusao < '".substr($vetorPublisherPorUtilizacao[$value],0,19)."' ";
        }
        $sql .= "
                    and vg.vg_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                    and vg.vg_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and vgm_opr_codigo = ".$value." ";
        if(!empty($listaUG_CPF)) {
            $sql .= " and vgm_cpf NOT IN (".$listaUG_CPF.")
                    ";

        }//end if(!empty($listaUG_CPF))
        $sql .= "
            group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo) ";
    
        //Contabilizando vendas por utilização de PINs Publisher
        if (array_key_exists($value, $vetorPublisherPorUtilizacao)) { 
            $sql .= "
        
        union all

            (select 
                    vgm_cpf as ug_cpf, 
                    UPPER(vgm_nome_cpf) as ug_nome, 
                    (sum(vgm.vgm_valor)/".$vetorCotacaoUSS[$value].") as total_em_dolar 
            from tb_dist_venda_games vg 
                 inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                 inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
                 inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."'
                 and pin_status = '8'
                 and pih_codretepp='2'
                 and vg.vg_data_inclusao >= '".substr($vetorPublisherPorUtilizacao[$value],0,19)."'
                 and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                 and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                 and vgm_opr_codigo = ".$value." 
                 and pih_data >= '".substr($vetorPublisherPorUtilizacao[$value],0,19)."' ";
            if(!empty($listaUG_CPF)) {
                $sql .= " and vgm_cpf NOT IN (".$listaUG_CPF.")
                        ";

            }//end if(!empty($listaUG_CPF))
            $sql .= "
            group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo) 
            ";
        }//end if (array_key_exists("primeiro", $vetorPublisherPorUtilizacao))

        $sql .=" 
            
        union all
            
            (select 
                    picc_cpf as ug_cpf, 
                    UPPER(picc_nome) as ug_nome, 
                    (sum(pih_pin_valor/100)/".$vetorCotacaoUSS[$value].") as total_em_dolar
            from pins_integracao_card_historico
                left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
	    where pin_status = '4' 
		    and pih_codretepp = '2'
                    and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                    and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and pih_id = ".$value." ";
        if(!empty($listaUG_CPF)) {
            $sql .= " and picc_cpf NOT IN (".$listaUG_CPF.")
                    ";

        }//end if(!empty($listaUG_CPF))
        $sql .= " 
            group by picc_cpf, picc_nome, pih_id)
            
        union all
            
            (select 
                    vgcbe_cpf as ug_cpf, 
                    UPPER(vgcbe_nome_cpf) as ug_nome, 
                    (sum(vgm_valor * vgm_qtde)/".$vetorCotacaoUSS[$value].") as total_em_dolar 
            from tb_venda_games_cpf_boleto_express
                inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
	    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vgcbe_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                    and vgcbe_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and vgm_opr_codigo  = ".$value." ";
        if(!empty($listaUG_CPF)) {
            $sql .= " and vgcbe_cpf NOT IN (".$listaUG_CPF.")
                    ";

        }//end if(!empty($listaUG_CPF))
        $sql .= " 
            group by vgcbe_cpf, vgcbe_nome_cpf, vgm_opr_codigo)
                 ";
    }//end foreach
} //end if(!empty($verificadorPublishersNovos))
$sql .= "
            
) tabelaUnion 
            group by ug_cpf, ug_nome 
            order by ug_cpf; --total_em_dolar desc;  
    ";
            
//echo $sql."\n"; die();

// Inicializando variavel com Total do Relatório Mensal
$total_5816_dolares = 0;
// Inicializando variavel com Total de CPFs no Relatório Mensal
$total_5816_cpfs = 0;

$rs = SQLexecuteQuery($sql);
if(!$rs) echo "Erro ao selecionar os Detalhamento para os Publishers (".implode(",", $vetorPublisher).").<br>\n";
else { 
    $contFatura = pg_num_rows($rs);
    $cpf_anterior = NULL;
    while($rs_row = pg_fetch_array($rs)) {
        
        if($cpf_anterior == str_replace("-","",str_replace(".", "", $rs_row['ug_cpf']))) {
            die("<br>O CPF anterior [".$cpf_anterior."] e o próximo CPF [".$rs_row['ug_cpf']."] está sendo utilizado com NOMES DIFERENTES!<br>Corrigir antes de gerar o arquivo para o BACEN!<pre>".print_r($rs_row,true)."</pre>");
        }//end if($cpf_anterior == $rs_row['ug_cpf']) 
            
        // Acumulando os valores
        $total_5816_dolares += number_format($rs_row['total_em_dolar'],2,".","");
        $total_5816_cpfs++;

        // Dados 
        unset($vetorLines);
        $vetorLines = array (
                            0 => array('name' => str_replace("-","",str_replace(".", "", $rs_row['ug_cpf'])),
                                       'size' => 14
                                        ),
                            1 => array('name' => (strlen(str_replace("-","",str_replace(".", "", $rs_row['ug_cpf'])))>11?"J":"F"),
                                       'size' => 1
                                        ),
                            2 => array('name' => $codigoNossaBandeira,
                                       'size' => 1
                                       ),
                            3 => array('name' => $tipoCartao,
                                       'size' => 2,
//                                       'upper'=> 'nao'
                                       ),
                            4 => array('name' => substr(trim($rs_row['ug_nome']),0,50),
                                       'size' => 50
                                       ),
                            5 => array('name' => $ano.$mes,
                                       'size' => 6
                                       ),
                            6 => array('name' => $codigoMoeda,
                                       'size' => 3
                                       ),
                            7 => array('name' => number_format(($rs_row['total_em_dolar']*100), 0, '', ''),
                                       'size' => 17
                                       ),
                            8 => array('name' => ' ',
                                       'size' => 51
                                       ),
                            );
        $file->setVetorLines($vetorLines);
        
        $cpf_anterior = str_replace("-","",str_replace(".", "", $rs_row['ug_cpf']));
        
    } // end While
}//end else do if(!$rs)

//=========================================================================================================================
//3- REGISTRO DE DADOS ( DETALHAMENTO DAS DESPESAS DA FATURA - OBRIGATÓRIO PARA FATURAS NO VALOR ACIMA DE U$ 10.000,00)
//=========================================================================================================================
if(!empty($listaUG_CPF)) { 
    echo "Possui detalhamento no 5816 por possuir CPF com mais de U$ 10.000 Dolares em Gastos<br>".PHP_EOL;
    $sql = "
    select ug_cpf, ug_nome, vg_id, vgm_opr_codigo, sum(total_em_dolar) as total_em_dolar 
    from ( 
            ( select 
                    ug_cpf, 
                    ug_nome_cpf as ug_nome,
                    'G'||vg_id as vg_id,
                    vgm_opr_codigo,
                    (CASE ".PHP_EOL;
    foreach ($vetorPublisher as $key => $value) {
        $sql .= "                     WHEN vgm_opr_codigo = ".$value." THEN sum(vgm.vgm_valor * vgm.vgm_qtde)/".$vetorCotacaoUSS[$value]." ".PHP_EOL;
    }//end foreach 
    $sql .= "\t\t\t   END) as total_em_dolar 
            from tb_venda_games vg 
                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vg.vg_data_concilia >= '".$ano."-".$mes."-01 00:00:00'
                    and vg.vg_data_concilia <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                    and ug_cpf IN (".$listaUG_CPF.")
            group by vgm_opr_codigo,vg_id,ug_cpf,ug_nome_cpf
            order by ug_cpf )
            
        union all
            
            (select 
                    vgm_cpf as ug_cpf, 
                    UPPER(vgm_nome_cpf) as ug_nome, 
                    'L'||vg_id as vg_id,
                    vgm_opr_codigo,
                    (CASE ".PHP_EOL;
        foreach ($vetorPublisher as $key => $value) {
            $sql .= "                     WHEN vgm_opr_codigo = ".$value." THEN sum(vgm.vgm_valor * vgm.vgm_qtde)/".$vetorCotacaoUSS[$value]." ".PHP_EOL;
        }//end foreach 
        $sql .= "\t\t\t   END) as total_em_dolar 
            from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."'  ".$where_opr_venda_lan."
                    and vg.vg_data_inclusao >= '".$ano."-".$mes."-01 00:00:00'
                    and vg.vg_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).")
                    and vgm_cpf IN (".$listaUG_CPF.")
            group by vgm_opr_codigo,vg_id,vgm_cpf, vgm_nome_cpf ) ";
    
    //Contabilizando vendas por utilização de PINs Publisher
    if(count($vetorPublisherPorUtilizacao)>0) {
        $sql .= "
        
        union all

            (select 
                    vgm_cpf as ug_cpf, 
                    UPPER(vgm_nome_cpf) as ug_nome, 
                    'L'||vg_id as vg_id,
                    vgm_opr_codigo,
                    (CASE ".PHP_EOL;
        foreach ($vetorPublisher as $key => $value) {
            $sql .= "                     WHEN vgm_opr_codigo = ".$value." THEN sum(vgm.vgm_valor * vgm.vgm_qtde)/".$vetorCotacaoUSS[$value]." ".PHP_EOL;
        }//end foreach 
        $sql .= "\t\t\t   END) as total_em_dolar 
            from tb_dist_venda_games vg 
                 inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                 inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
                 inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."'
                 and pin_status = '8'
                 and pih_codretepp='2'
                 ".$where_opr_venda_lan_negativa."
                 and pih_data >= '".$ano."-".$mes."-01 00:00:00'
                 and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                 and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                 and vgm_cpf IN (".$listaUG_CPF.") 
                 ".$where_opr_utilizacao_lan."
            group by vgm_opr_codigo,vg_id,vgm_cpf, vgm_nome_cpf ) 
            ";
    }//end if(count($vetorPublisherPorUtilizacao)>0)

    $sql .=" 
            
            
        union all
            
            (select 
                    picc_cpf as ug_cpf, 
                    UPPER(picc_nome) as ug_nome,
                    'C'||pih_pin_id as vg_id,
                    pih_id as vgm_opr_codigo,
                    (CASE ".PHP_EOL;
    foreach ($vetorPublisher as $key => $value) {
        $sql .= "                     WHEN pih_id = ".$value." THEN sum(pih_pin_valor/100)/".$vetorCotacaoUSS[$value]." ".PHP_EOL;
    }//end foreach 
    $sql .= "\t\t\t   END) as total_em_dolar
            from pins_integracao_card_historico
                left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
	    where pin_status = '4' 
		    and pih_codretepp = '2'
                    and pih_data >= '".$ano."-".$mes."-01 00:00:00'
                    and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and pih_id IN (".implode(",", $vetorPublisher).") 
                    and picc_cpf IN (".$listaUG_CPF.")
            group by picc_cpf, picc_nome, pih_pin_id, pih_id)
            
        union all
            
            (select 
                    vgcbe_cpf as ug_cpf, 
                    UPPER(vgcbe_nome_cpf) as ug_nome, 
                    'E'||(lpad((CAST(vg_id as character varying)),8,'0')) as vg_id,
                    vgm_opr_codigo,
                    (CASE ".PHP_EOL;
    foreach ($vetorPublisher as $key => $value) {
        $sql .= "                     WHEN vgm_opr_codigo = ".$value." THEN sum(vgm_valor * vgm_qtde)/".$vetorCotacaoUSS[$value]." ".PHP_EOL;
    }//end foreach 
    $sql .= "\t\t\t   END) as total_em_dolar
            from tb_venda_games_cpf_boleto_express
                inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
	    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vgcbe_data_inclusao >= '".$ano."-".$mes."-01 00:00:00'
                    and vgcbe_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                    and vgcbe_cpf IN (".$listaUG_CPF.")
            group by vgcbe_cpf, vgcbe_nome_cpf, vg_id, vgm_opr_codigo)
            ";
    if(!empty($verificadorPublishersNovos)) {
        foreach ($vetorPublisherNovos as $key => $value) {
            //echo "Key: $key -- value: $value <br>";
            $sql .= "

            union all

                 (select 
                        ug_cpf, 
                        UPPER(ug_nome_cpf) as ug_nome, 
                        'G'||vg_id as vg_id,
                        vgm_opr_codigo,
                        (sum(vgm.vgm_valor * vgm.vgm_qtde)/".$vetorCotacaoUSS[$value].") as total_em_dolar 
                from tb_venda_games vg 
                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_concilia >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                        and vg.vg_data_concilia <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                        and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                        and vgm_opr_codigo = ".$value." 
                        and ug_cpf IN (".$listaUG_CPF.")
                group by vgm_opr_codigo,vg_id,ug_cpf,ug_nome_cpf)

            union all

                (select 
                        vgm_cpf as ug_cpf, 
                        UPPER(vgm_nome_cpf) as ug_nome, 
                        'L'||vg_id as vg_id,
                        vgm_opr_codigo,
                        (sum(vgm.vgm_valor * vgm.vgm_qtde)/".$vetorCotacaoUSS[$value].") as total_em_dolar 
                from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."'  ";
            if (array_key_exists($value, $vetorPublisherPorUtilizacao)) {
                $sql .= "
                        and vg.vg_data_inclusao < '".substr($vetorPublisherPorUtilizacao[$value],0,19)."' ";
            }
            $sql .= "
                        and vg.vg_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                        and vg.vg_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                        and vgm_opr_codigo = ".$value." 
                        and vgm_cpf IN (".$listaUG_CPF.")
                group by vgm_opr_codigo,vg_id,vgm_cpf, vgm_nome_cpf)  ";
    
            //Contabilizando vendas por utilização de PINs Publisher
            if (array_key_exists($value, $vetorPublisherPorUtilizacao)) {
                $sql .= "
        
            union all

                (select 
                        vgm_cpf as ug_cpf, 
                        UPPER(vgm_nome_cpf) as ug_nome, 
                        'L'||vg_id as vg_id,
                        vgm_opr_codigo,
                        (sum(vgm.vgm_valor)/".$vetorCotacaoUSS[$value].") as total_em_dolar 
                from tb_dist_venda_games vg 
                     inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                     inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
                     inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."'
                     and pin_status = '8'
                     and pih_codretepp='2'
                     and vg.vg_data_inclusao >= '".substr($vetorPublisherPorUtilizacao[$value],0,19)."'
                     and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                     and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                     and vgm_opr_codigo = ".$value." 
                     and vgm_cpf IN (".$listaUG_CPF.") 
                     and pih_data >= '".substr($vetorPublisherPorUtilizacao[$value],0,19)."' 
                group by vgm_cpf, vgm_nome_cpf, vg_id, vgm_opr_codigo) 
                ";
            }//end if (array_key_exists($value, $vetorPublisherPorUtilizacao))

            $sql .=" 

            union all

                (select 
                        picc_cpf as ug_cpf, 
                        UPPER(picc_nome) as ug_nome,
                        'C'||pih_pin_id as vg_id,
                        pih_id as vgm_opr_codigo,
                        (sum(pih_pin_valor/100)/".$vetorCotacaoUSS[$value].") as total_em_dolar
                from pins_integracao_card_historico
                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                where pin_status = '4' 
                        and pih_codretepp = '2'
                        and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                        and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                        and pih_id = ".$value."  
                        and picc_cpf IN (".$listaUG_CPF.")
                group by picc_cpf, picc_nome, pih_pin_id, pih_id)

            union all

                (select 
                        vgcbe_cpf as ug_cpf, 
                        UPPER(vgcbe_nome_cpf) as ug_nome, 
                        'E'||(lpad((CAST(vg_id as character varying)),8,'0')) as vg_id,
                        vgm_opr_codigo,
                        (sum(vgm_valor * vgm_qtde)/".$vetorCotacaoUSS[$value].") as total_em_dolar
                from tb_venda_games_cpf_boleto_express
                    inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                    inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vgcbe_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                        and vgcbe_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                        and vgm_opr_codigo  = ".$value." 
                        and vgcbe_cpf IN (".$listaUG_CPF.")
                group by vgcbe_cpf, vgcbe_nome_cpf, vg_id, vgm_opr_codigo)
                         ";
        }//end foreach
    } //end if(!empty($verificadorPublishersNovos))
    $sql .= "
            
    ) tabelaUnion 
    group by ug_cpf, ug_nome, vg_id, vgm_opr_codigo
    order by ug_cpf;  
    ";
          
    //echo $sql."\n"; die();

    $rs = SQLexecuteQuery($sql);
    if(!$rs) echo "Erro ao selecionar os Detalhamento para os Publishers (".implode(",", $vetorPublisher).").<br>\n";
    else { 
        $contDetalhamento = pg_num_rows($rs);
        $cpfAnterior = "";
        while($rs_row = pg_fetch_array($rs)) {
            
            // Busca informações do estabelecimento onde foi efetuada a despesa no exterior
            $sql = "select opr_razao from operadoras where opr_codigo = ".$rs_row['vgm_opr_codigo'];
            $rsRazao = SQLexecuteQuery($sql);
            $rsRazao_row = pg_fetch_array($rsRazao);
            
            //echo "Valor detalhado = [". $vetorTotaisAcimaLimite[$rs_row['ug_cpf']] ."] - Valor da compra = [".$rs_row['total_em_dolar']."] <br>";
            
            //Primeira linha com o Total da fatura que ultrapassou US$10000
            if ($cpfAnterior != $rs_row['ug_cpf']) {
                // Dados 
                unset($vetorLines);
                $vetorLines = array (
                                    0 => array('name' => str_replace("-","",str_replace(".", "", $rs_row['ug_cpf'])),
                                               'size' => 14
                                                ),
                                    1 => array('name' => (strlen(str_replace("-","",str_replace(".", "", $rs_row['ug_cpf'])))>11?"J":"F"),
                                               'size' => 1
                                                ),
                                    2 => array('name' => $codigoNossaBandeira,
                                               'size' => 1
                                               ),
                                    3 => array('name' => $tipoCartao,
                                               'size' => 2,
//                                               'upper'=> 'nao'
                                               ),
                                    4 => array('name' => substr(trim($rs_row['ug_nome']),0,50),
                                               'size' => 50
                                               ),
                                    5 => array('name' => $ano.$mes,
                                               'size' => 6
                                               ),
                                    6 => array('name' => $codigoMoeda,
                                               'size' => 3
                                               ),
                                    7 => array('name' => number_format(($vetorTotaisAcimaLimite[$rs_row['ug_cpf']]*100), 0, '', ''),
                                               'size' => 17
                                               ),
                                    8 => array('name' => ' ',
                                               'size' => 51
                                               ),
                            );
                $file->setVetorLines($vetorLines);
                
                //Incluindo no Total Geral
                $total_5816_dolares += number_format($vetorTotaisAcimaLimite[$rs_row['ug_cpf']],2,".","");
                $contFatura++;
                
            }//end if ($cpfAnterior != $rs_row['ug_cpf'])
            
            // Dados 
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => str_replace("-","",str_replace(".", "", $rs_row['ug_cpf'])),
                                           'size' => 14
                                            ),
                                1 => array('name' => (strlen(str_replace("-","",str_replace(".", "", $rs_row['ug_cpf'])))>11?"J":"F"),
                                           'size' => 1
                                            ),
                                2 => array('name' => $codigoNossaBandeira,
                                           'size' => 1
                                           ),
                                3 => array('name' => $tipoCartao,
                                           'size' => 2,
//                                           'upper'=> 'nao'
                                           ),
                                4 => array('name' => substr(trim($rs_row['ug_nome']),0,50),
                                           'size' => 50
                                           ),
                                5 => array('name' => $ano.$mes,
                                           'size' => 6
                                           ),
                                6 => array('name' => $codigoMoeda,
                                           'size' => 3
                                           ),
                                7 => array('name' => number_format((($vetorTotaisAcimaLimite[$rs_row['ug_cpf']]<($rs_row['total_em_dolar']*1)?$vetorTotaisAcimaLimite[$rs_row['ug_cpf']]:$rs_row['total_em_dolar'])*100), 0, '', ''),
                                           'size' => 17
                                           ),
                                8 => array('name' => substr(trim($rsRazao_row['opr_razao']),0,50),
                                           'size' => 50
                                           ),
                                9 => array('name' => 'D', //Valor fixo "D" para diferenciar do registro da fatura
                                           'size' => 1
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            //Caluclando para fins de arredondamentos
            $vetorTotaisAcimaLimite[$rs_row['ug_cpf']] = $vetorTotaisAcimaLimite[$rs_row['ug_cpf']] - (round($rs_row['total_em_dolar'], 2));
            
            //capturando o ultimo CPF
            $cpfAnterior = $rs_row['ug_cpf'];
            
        } // end While
    }//end else do if(!$rs)
 
}//end if(!empty($listaUG_CPF))

//=========================================================================================================================
//4- REGISTRO DE CONTROLE FINAL
//=========================================================================================================================
unset($vetorLines);
$vetorLines = array (
                     0 => array('name' => $identificacaoRegisto5816_F,
                                'size' => 2
                                ),
                     1 => array('name' => ($contFatura+$contDetalhamento+2), //Os dois são referentes aos registros de Identificação e Controle Final
                                'size' => 8
                                ),
                     2 => array('name' => ' ',
                                'size' => 135
                                ),
                );
$file->setVetorLines($vetorLines);


$file->saveFile(true);

if($file->checkFile()){
    echo "<hr>Arquivo ".$file->getFileName()." gerado com sucesso.<br>".PHP_EOL;
}
else {
   echo "<hr>Arquivo ".$file->getFileName()." não gerado.<br>".PHP_EOL; 
}

//================================== Fim da Geração do Arquivo Layout 5816





//==================================  Início do trecho compactando arquivos Semestrais para serem enviados ao BACEN
$nomeArquivo5816Zipado = 'amtf101_5816_'.$mes.$ano.".zip"; //Exemplo: amtf101_5816_122014.zip
$file = new FilePosition($nomeArquivo5816Zipado); 
$file->createZip($nomeArquivo5816,true);
echo "Arquivo Mensal Zipado Criado com Sucesso: <a href='/bacen/".date('Ymd')."/".$nomeArquivo5816Zipado."'>".$nomeArquivo5816Zipado."</a><br>".PHP_EOL;
echo "Total em Dolares no arquivo: <b>US$ ".number_format($total_5816_dolares,2,".",",")."</b><br>".PHP_EOL;
echo "Total CPFs no arquivo: <b>".number_format($total_5816_cpfs,0,",",".")."</b><br><hr><br><br>".PHP_EOL;
//==================================  Fim do trecho compactando arquivos Semestrais para serem enviados ao BACEN









/*
 ***** Capturando os Publishers Nacionais vinculados a E-Prepag Administradora  
 */

//Publishers Já em Operação constantes em arquivos BACEN anteriores CONCATENANDO com Nacionais
$vetorPublisherNacionais = levantamentoPublisherOperantesNacionais($ano,$mes);
$vetorPublisher = array_merge($vetorPublisher, $vetorPublisherNacionais);

//Publishers novos nunca antes contou nos arquivos BACEN CONCATENANDO com Nacionais
$vetorPublisherNovosNacionais = levantamentoPublisherNovosOperantesNacionais($ano,$mes);
if(!empty($verificadorPublishersNovos)) 
    $vetorPublisherNovos = array_merge($vetorPublisherNovos,$vetorPublisherNovosNacionais);
else $vetorPublisherNovos = $vetorPublisherNovosNacionais;

// Atualizando a variavel para verificação de novos Publishers Nacionais (CONCATENADO com os Internacionais).
$verificadorPublishersNovos = implode(",", $vetorPublisherNovos);

// Array para testes Nacionais
if(!empty($verificadorPublishersNovos)) 
    $vetorPublisherNacionaisTestes = array_merge($vetorPublisherNacionais,$vetorPublisherNovosNacionais);
else $vetorPublisherNacionaisTestes = $vetorPublisherNacionais;

//echo "Vetor Publisher NACIONAIS:<pre>".print_r($vetorPublisherNacionais,true)."</pre>";
//echo "Vetor Publisher:<pre>".print_r($vetorPublisher,true)."</pre>";
//echo "Vetor Publisher NOVOS NACIONAIS:<pre>".print_r($vetorPublisherNovosNacionais,true)."</pre>";
//echo "Vetor Publisher NOVOS:<pre>".print_r($vetorPublisherNovos,true)."</pre>";
//echo "String contendo novos PUblisher após concatenados: [".$verificadorPublishersNovos."]<br>";

//Dados necessários para os arquivos TRIMESTRAIS e MENSAIS
$codigoSegmento = '001';
$ano = $ano;
$trimestre = $mes;
$oitoPrimeiros = '19037276';     // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES
$funcao = 'E';          // Função Débito Fixa -- Alterado para E (pré-pago) em 15/01/2018
$bandeira = 6;          // Bandeira Fixo 6 - Bandeira própria
$formaCaptura = 4;      // Forma de Captura Fixa 4 - Não presencial
$numeroParcelas = 1;    // Numero de Parcelas Fixo 1 - Somente pagamento à vista
$produto = 20;          // Produto fixo 20 - Outros
$modalidade = 'P';      // Modalidade fixo P - Cartão emitido com bandeira de crédito sem associação com outra marca comercial, industrial ou sem fins lucrativos e cartões com função débito.
$tarifaIntercam = 0;    // Tarifa Intercambio % inicialmente como (0) Zero.
$qtdeEstabelecimentos       = count($vetorPublisher); //Quantidade de estabelecimentos 
$qtdeEstabelecimentosAtivo  = count($vetorPublisher); //Quantidade de estabelecimentos operando
//Adicionando os publishers novos no total de estabelecimentos
if(!empty($verificadorPublishersNovos)) {
    $qtdeEstabelecimentos       += count($vetorPublisherNovos);
    $qtdeEstabelecimentosAtivo  += count($vetorPublisherNovos);
} //end if(!empty($verificadorPublishersNovos))
$receitaAluguelEquipamentos = 0; // Receita de aluguel de equipamentos e de conectividade
$custoTarifaIntercambio     = 0; // Custo da tarifa de intercâmbio
$custoTaxaAcessoBandeira    = 0; // Custo das taxas de acesso às bandeiras
$ISPB = '19037276';              // Código ISPB cadastrado
$nomeEmissor = 'E-PREPAG ADMINISTRADORA DE CARTOES LTDA'; // Nome da instituição emissora de cartões de pagamento pertencente ao conglomerado financeiro.
$receitaEmissaoeFaturamento = 0;// Receitas obtidas junto aos credenciadores, provenientes de incentivos à emissão de cartões de pagamento
$receitaFinanceira = 0;         // Receitas originadas pelo crédito rotativo bem como aquelas geradas por ganhos financeiros decorrentes de inadimplência (multas, juros, etc.)
$custoProgramaRecompensa = 0;   // Custos advindos das vantagens que o emissor oferece ao portador do cartão, tais como descontos na tarifa de anuidade, programas de recompensa, seguros, etc.
$nomeArquivosTrimestrais = array(); //Lista de Arquivos a serem considerados na compactação de arquivos Trimestrais
$nomeArquivosSemestrais = array();  //Lista de Arquivos a serem considerados na compactação de arquivos Semestrais

// IOF
$iof = 6.38; //Aliquota de IOF - usar PONTO (.) como casa decimal



// Teste de verificação se é mês para geração dos arquivos Trimestrais
if(isTrimestral($mes)) {

    //================================== Início Verificando se os Dados Financeiros estão devidamente apurados (Congelados na Geração de Notas)
    $sql = "
        select 
                fp_publisher, 
                data_sem_notas
        from ( 
        ";
    $insere_union_all = 1;
    foreach ($vetorPublisher as $key => $value) {
        if($insere_union_all > 1) {
            $sql .= "

              union all

            ";
        } //end if($insere_union_all > 1)
        $sql .= "   (
                    select 
                            fp_publisher, 
                            to_char(fp_date, 'YYYY-MM-DD') as data_sem_notas
                    from financial_processing 
                    where  fp_publisher = ".$value."
                           and fp_date >=  CASE 
                                                WHEN 
                           ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                THEN 
                           ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                ELSE 
                           '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                END
                           and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                           and fp_freeze!=1
                    group by fp_publisher, data_sem_notas
                ) ";
        $insere_union_all++;

    }//end foreach ($vetorPublisher as $key => $value)


    if(!empty($verificadorPublishersNovos)) {
        foreach ($vetorPublisherNovos as $key => $value) {
            //echo "Key: $key -- value: $value <br>";
            $sql .= "

              union all

                 (
                    select 
                            fp_publisher, 
                            to_char(fp_date, 'YYYY-MM-DD') as data_sem_notas
                    from financial_processing 
                    where  fp_publisher = ".$value."
                           and fp_date >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                           and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                           and fp_freeze!=1
                    group by fp_publisher, data_sem_notas
                 )
                     ";
        }//end foreach
    } //end if(!empty($verificadorPublishersNovos))
    $sql .= " 
            ) tabelaUnion      
        group by fp_publisher, data_sem_notas
        order by fp_publisher, data_sem_notas; ";

    //echo $sql."\n"; die();

    $rs_nao_emitidos = SQLexecuteQuery($sql);
    
    if(pg_num_rows($rs_nao_emitidos) != 0) {
        echo "<hr><b>Faltam Emitir Notas: (TOTAL [".pg_num_rows($rs_nao_emitidos)."] Dias/Publishers)</b><br><br>\n";
        while($rs_nao_emitidos_row = pg_fetch_array($rs_nao_emitidos)) {
                echo " * Publisher ID [".$rs_nao_emitidos_row['fp_publisher']."] => Data [".$rs_nao_emitidos_row['data_sem_notas']."] <br>\n";
        } //end while
        die("------- Emitir Notas para os Publisher e Períodos acima antes de gerar os Arquivos -------------<br><hr>\n");
    }//end if(pg_num_rows($rs_nao_emitidos) != 0)
    //================================== Fim Verificando se os Dados Financeiros estão devidamente apurados (Congelados na Geração de Notas)
    
    
    
    //================================== Verificando se os Dados Complementares estão devidamente cadastrados
    $sql = "SELECT  *
            FROM complice 
            WHERE c_ano_mes =  '".$ano."-".$mes."-01';"; 
    //echo $sql."<br>";
    $rs_complice = SQLexecuteQuery($sql);
    if(pg_num_rows($rs_complice) == 1) {
    
        
        
        //================================== Verificando se os Dados Complementares de TODO o TRIMESTRE estão devidamente cadastrados
        $sql = "SELECT  *
                FROM complice 
                WHERE c_ano_mes >= '".getStartDateTrimestral($mes,$ano)." 00:00:00' 
                    and c_ano_mes <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'"; 
        //echo $sql."<br>";
        $rs_complice = SQLexecuteQuery($sql);
        if(pg_num_rows($rs_complice) == 3 || $testeData == $dataInicioOperacao) {
    
            echo "<b>Gerando Arquivos Trimestrais</b><br><br>\n";
            
            
            //==================================  Inicio do trecho a geração do arquivo SEGMENTO.TXT
            $nomeArquivo = 'segmento.txt';
            $nomeArquivosTrimestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            // Dados 
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => 'games',
                                           'size' => 50
                                            ),
                                1 => array('name' => 'Estabelecimentos publicadores de games que comercializam créditos pré-pagos',
                                           'size' => 250
                                            ),
                                2 => array('name' => $codigoSegmento,
                                           'size' => 3
                                           ),
                                );
            $quantidadeLinhas = 1;

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'segmento',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);
            $file->setVetorLines($vetorLines);
            $file->saveFile(true);

            if($file->checkFile()){
                echo "<hr>Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "<hr>Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo SEGMENTO.TXT




            //==================================  Inicio do trecho a geração do arquivo RANKING.TXT
            $nomeArquivo = 'ranking.txt';
            $nomeArquivosTrimestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            //Adicionando os publishers já contabilizados em períodos anteriores ao total de linhas
            $quantidadeLinhas = count($vetorPublisher);
            
            //Adicionando os publishers novos no total de linhas
            if(!empty($verificadorPublishersNovos)) {
                $quantidadeLinhas += count($vetorPublisherNovos);
            } //end if(!empty($verificadorPublishersNovos))

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'ranking',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                            'size' => 8
                                            ),
                                 3 => array('name' => ($quantidadeLinhas>15)?'15':$quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);

            // Buscando informações congeladas
            $sql = "
                select 
                        fp_publisher, 
                        sum(quantidade) as quantidade,
                        sum(total) as total,
                        sum(aliquota) as aliquota,
                        sum(total_sem_descontos) as total_sem_descontos,
                        sum(total_comissao)  as total_comissao
                from ( 
                ";
            $insere_union_all = 1;
            foreach ($vetorPublisher as $key => $value) {
                if($insere_union_all > 1) {
                    $sql .= "

                      union all

                    ";
                } //end if($insere_union_all > 1)
                $sql .= "   (
                            select 
                                    fp_publisher, 
                                    sum(fp_number) as quantidade, 
                                    ROUND((sum(fp_total)/(1+".$iof."/100)),2) as total,
                                    ROUND((sum(fp_comission)/sum(fp_total)*100),2) as aliquota,
                                    sum(fp_total) as total_sem_descontos,
                                    sum(fp_comission) as total_comissao
                            from financial_processing 
                            where  fp_publisher = ".$value."
                                   and fp_date >=  CASE 
                                                        WHEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                        THEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                        ELSE 
                                   '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                        END
                                   and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                   and fp_freeze=1
                            group by fp_publisher
                        ) ";
                $insere_union_all++;
                
            }//end foreach ($vetorPublisher as $key => $value)

            
            if(!empty($verificadorPublishersNovos)) {
                foreach ($vetorPublisherNovos as $key => $value) {
                    //echo "Key: $key -- value: $value <br>";
                    $sql .= "

                      union all

                         (
                            select 
                                    fp_publisher, 
                                    sum(fp_number) as quantidade, 
                                    ROUND((sum(fp_total)/(1+".$iof."/100)),2) as total,
                                    ROUND((sum(fp_comission)/sum(fp_total)*100),2) as aliquota,
                                    sum(fp_total) as total_sem_descontos,
                                    sum(fp_comission) as total_comissao
                            from financial_processing 
                            where  fp_publisher = ".$value."
                                   and fp_date >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                   and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                   and fp_freeze=1
                            group by fp_publisher
                         )
                             ";
                }//end foreach
            } //end if(!empty($verificadorPublishersNovos))
            $sql .= " 
                    ) tabelaUnion      
                group by fp_publisher
                order by total desc
                limit 15; ";

            //echo $sql."\n"; die();

            $rs = SQLexecuteQuery($sql);
            if(!$rs) echo "Erro ao selecionar os Publishers (".implode(",", $vetorPublisher).") e Publishers Novos (".implode(",", $vetorPublisherNovos).").<br>\n";
            else { 

                while($rs_row = pg_fetch_array($rs)) {
                    //echo $value."<pre>".print_r($rs_row,true)."</pre>".$sql."<br>";

                    // Dados 
                    unset($vetorLines);
                    $vetorLines = array (
                                        0 => array('name' => $ano,
                                                   'size' => 4
                                                    ),
                                        1 => array('name' => trimestre($trimestre),
                                                   'size' => 1
                                                    ),
                                        2 => array('name' => $rs_row['fp_publisher'],
                                                   'size' => 8
                                                   ),
                                        3 => array('name' => $funcao,
                                                   'size' => 1
                                                   ),
                                        4 => array('name' => $bandeira,
                                                   'size' => 2
                                                   ),
                                        5 => array('name' => $formaCaptura,
                                                   'size' => 1
                                                   ),
                                        6 => array('name' => $numeroParcelas,
                                                   'size' => 2
                                                   ),
                                        7 => array('name' => $codigoSegmento,
                                                   'size' => 3
                                                   ),
                                        8 => array('name' => number_format(((in_array($rs_row['fp_publisher'],$vetorPublisherNacionaisTestes)?$rs_row['total_sem_descontos']:$rs_row['total'])*100), 0, '', ''),
                                                   'size' => 15
                                                   ),
                                        9 => array('name' => $rs_row['quantidade'],
                                                   'size' => 12
                                                   ),
                                        10 => array('name' => number_format(($rs_row['aliquota']*100), 0, '', ''),
                                                   'size' => 4
                                                   ),
                                        );
                    $file->setVetorLines($vetorLines);

                }//end while ($rs_row = pg_fetch_array($rs))
            }//end else do if(!$rs)

            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo RANKING.TXT




            //==================================  Inicio do trecho a geração do arquivo DESCONTO.TXT
            $nomeArquivo = 'desconto.txt';
            $nomeArquivosTrimestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            $quantidadeLinhas = 1; //Devido a ser fixo somente uma linha com totais de estabelecimentos

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'desconto',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);

            // Buscando informações congeladas
            $sql = "
                select 
                        sum(quantidade) as quantidade,
                        sum(total) as total,
                        ROUND((sum(total_comissao)/sum(total_sem_descontos)*100),2) as aliquota,
                        MIN(aliquota_min) as aliquota_min,
                        MAX(aliquota_max)  as aliquota_max,
                        count(distinct(numero_aliquota)) as numero_aliquota,
                        sum(total_sem_descontos) as total_sem_descontos,
                        sum(total_comissao) as total_comissao
                from ( 
 
                    ";
            $insere_union_all = 1;
            foreach ($vetorPublisher as $key => $value) {
                if($insere_union_all > 1) {
                    $sql .= "

                      union all

                    ";
                } //end if($insere_union_all > 1)

                $sql .= "
                        (
                            select 
                                    sum(fp_number) as quantidade, 
                                    ";
                if (in_array($value,$vetorPublisherNacionaisTestes))
                        $sql .= "sum(fp_total) as total,";
                else $sql .= "ROUND((sum(fp_total)/(1+".$iof."/100)),2) as total,"; 
                $sql .= "
                                    MIN(fp_aliquot) as aliquota_min,
                                    MAX(fp_aliquot) as aliquota_max,
                                    fp_aliquot as numero_aliquota,
                                    sum(fp_total) as total_sem_descontos,
                                    sum(fp_comission) as total_comissao
                            from financial_processing 
                            where  fp_publisher = ".$value."
                                   and fp_date >=  CASE 
                                                        WHEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                        THEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                        ELSE 
                                   '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                        END
                                    and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                    and fp_freeze=1
                            group by numero_aliquota
                        )
                        ";
                $insere_union_all++;
                
            }//end foreach ($vetorPublisher as $key => $value)

            
            if(!empty($verificadorPublishersNovos)) {
                foreach ($vetorPublisherNovos as $key => $value) {
                    //echo "Key: $key -- value: $value <br>";
                    $sql .= "

                      union all

                         (
                            select 
                                    sum(fp_number) as quantidade, 
                                    ";
                    if (in_array($value,$vetorPublisherNacionaisTestes))
                            $sql .= "sum(fp_total) as total,";
                    else $sql .= "ROUND((sum(fp_total)/(1+".$iof."/100)),2) as total,";
                    $sql .= "
                                    MIN(fp_aliquot) as aliquota_min,
                                    MAX(fp_aliquot) as aliquota_max,
                                    fp_aliquot as numero_aliquota,
                                    sum(fp_total) as total_sem_descontos,
                                    sum(fp_comission) as total_comissao
                            from financial_processing 
                            where  fp_publisher = ".$value."
                                   and fp_date >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                   and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                   and fp_freeze=1
                            group by numero_aliquota
                         )
                             ";
                }//end foreach
            } //end if(!empty($verificadorPublishersNovos))
            $sql .= " 
                    ) tabelaUnion  ";

            //echo $sql."\n"; die();

            $rs = SQLexecuteQuery($sql);
            if(!$rs) echo "Erro ao selecionar o Publisher (".implode(",", $vetorPublisher).").<br>\n";
            else { 

                $rs_row = pg_fetch_array($rs);

                //echo $value."<pre>".print_r($rs_row,true)."</pre>".$sql."<br>";

                // Cálculo do DESVIO PADRÃO
                $desvio_padrao = 0;
                $sql = " 
                select 
                    distinct(aliquotas) as aliquotas
                from (     
                        ";
                            $insere_union_all = 1;
                foreach ($vetorPublisher as $key => $value) {
                    if($insere_union_all > 1) {
                        $sql .= "

                          union all

                        ";
                    } //end if($insere_union_all > 1)

                    $sql .= "
                            (
                                select 
                                        distinct(fp_aliquot) as aliquotas
                                from financial_processing 
                                where  fp_publisher  = ".$value."
                                        and fp_date >=  CASE 
                                                             WHEN 
                                        ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                             THEN 
                                        ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                             ELSE 
                                        '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                             END
                                        and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                        and fp_freeze=1 
                            )
                            ";
                    $insere_union_all++;

                }//end foreach ($vetorPublisher as $key => $value)


                if(!empty($verificadorPublishersNovos)) {
                    foreach ($vetorPublisherNovos as $key => $value) {
                        //echo "Key: $key -- value: $value <br>";
                        $sql .= "

                          union all

                             (
                                select 
                                        distinct(fp_aliquot) as aliquotas
                                from financial_processing 
                                where  fp_publisher = ".$value."
                                       and fp_date >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                       and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                       and fp_freeze=1
                             )
                                 ";
                    }//end foreach
                } //end if(!empty($verificadorPublishersNovos))

                $sql .= " 
                    ) tabelaUnion     
                    ";
                
                //echo $sql."\n"; die();

                $rsDesvio = SQLexecuteQuery($sql);
                if(!$rsDesvio) echo "Erro ao selecionar o Publisher Desvio Padrão (".implode(",", $vetorPublisher).").<br>\n";
                else { 

                    while($rsDesvio_row = pg_fetch_array($rsDesvio)) {
                        $desvio_padrao += pow($rsDesvio_row['aliquotas']-$rs_row['aliquota'], 2);
                    }//end while

                    $desvio_padrao = sqrt($desvio_padrao/$rs_row['numero_aliquota']);

                }//end else do if(!$rsDesvio)

                // Dados 
                unset($vetorLines);
                $vetorLines = array (
                                    0 => array('name' => $ano,
                                               'size' => 4
                                                ),
                                    1 => array('name' => trimestre($trimestre),
                                               'size' => 1
                                                ),
                                    2 => array('name' => $funcao,
                                               'size' => 1
                                               ),
                                    3 => array('name' => $bandeira,
                                               'size' => 2
                                               ),
                                    4 => array('name' => $formaCaptura,
                                               'size' => 1
                                               ),
                                    5 => array('name' => $numeroParcelas,
                                               'size' => 2
                                               ),
                                    6 => array('name' => $codigoSegmento,
                                               'size' => 3
                                               ),
                                    7 => array('name' => number_format(($rs_row['aliquota']*100), 0, '', ''),
                                               'size' => 4
                                               ),
                                    8 => array('name' => number_format(($rs_row['aliquota_min']*100), 0, '', ''),
                                               'size' => 4
                                               ),
                                    9 => array('name' => number_format(($rs_row['aliquota_max']*100), 0, '', ''),
                                               'size' => 4
                                               ),
                                    10 => array('name' => number_format(($desvio_padrao*100), 0, '', ''),
                                               'size' => 4
                                               ),
                                    11 => array('name' => number_format(($rs_row['total']*100), 0, '', ''),
                                               'size' => 15
                                               ),
                                    12 => array('name' => $rs_row['quantidade'],
                                               'size' => 12
                                               ),
                                    );
                $file->setVetorLines($vetorLines);
            }//end else do if(!$rs)
            
            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo DESCONTO.TXT




            //==================================  Inicio do trecho a geração do arquivo INTERCAM.TXT
            $nomeArquivo = 'intercam.txt';
            $nomeArquivosTrimestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            $quantidadeLinhas = 1; //Devido a ser fixo somente uma linha com totais de estabelecimentos

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'intercam',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);

            // Buscando informações congeladas
            $sql = "
                select 
                        sum(quantidade) as quantidade, 
                        ROUND(sum(total),2) as total,
                        sum(total_sem_descontos) as total_sem_descontos,
                        sum(total_comissao) as total_comissao
                from ( 
 
                    ";
            $insere_union_all = 1;
            foreach ($vetorPublisher as $key => $value) {
                if($insere_union_all > 1) {
                    $sql .= "

                      union all

                    ";
                } //end if($insere_union_all > 1)

                $sql .= "
                        (
                            select 
                                    ";
                if (in_array($value,$vetorPublisherNacionaisTestes))
                        $sql .= "sum(fp_total) as total,";
                else $sql .= "ROUND((sum(fp_total)/(1+".$iof."/100)),2) as total,";
                $sql .= "
                                    sum(fp_number) as quantidade, 
                                    sum(fp_total) as total_sem_descontos,
                                    sum(fp_comission) as total_comissao
                            from financial_processing 
                            where  fp_publisher = ".$value."
                                   and fp_date >=  CASE 
                                                        WHEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                        THEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                        ELSE 
                                   '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                        END
                                   and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                   and fp_freeze=1 
                        )
                        ";
                $insere_union_all++;
                
            }//end foreach ($vetorPublisher as $key => $value)

            
            if(!empty($verificadorPublishersNovos)) {
                foreach ($vetorPublisherNovos as $key => $value) {
                    //echo "Key: $key -- value: $value <br>";
                    $sql .= "

                      union all

                         (
                            select 
                                  ";
                    if (in_array($value,$vetorPublisherNacionaisTestes))
                            $sql .= "sum(fp_total) as total,";
                    else $sql .= "ROUND((sum(fp_total)/(1+".$iof."/100)),2) as total,";
                    $sql .= "
                                    sum(fp_number) as quantidade, 
                                    sum(fp_total) as total_sem_descontos,
                                    sum(fp_comission) as total_comissao
                            from financial_processing 
                            where  fp_publisher = ".$value."
                                   and fp_date >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                   and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                   and fp_freeze=1
                         )
                             ";
                }//end foreach
            } //end if(!empty($verificadorPublishersNovos))
            $sql .= " 
                ) tabelaUnion     
                ";
                
            //echo $sql."\n"; die();

            $rs = SQLexecuteQuery($sql);
            if(!$rs) echo "Erro ao selecionar o Publisher (".implode(",", $vetorPublisher).").<br>\n";
            else { 

                $rs_row = pg_fetch_array($rs);

                //echo $value."<pre>".print_r($rs_row,true)."</pre>".$sql."<br>";

                // Dados 
                unset($vetorLines);
                $vetorLines = array (
                                    0 => array('name' => $ano,
                                               'size' => 4
                                                ),
                                    1 => array('name' => trimestre($trimestre),
                                               'size' => 1
                                                ),
                                    2 => array('name' => $produto,
                                               'size' => 2
                                               ),
                                    3 => array('name' => $modalidade,
                                               'size' => 1
                                               ),
                                    4 => array('name' => $funcao,
                                               'size' => 1
                                               ),
                                    5 => array('name' => $bandeira,
                                               'size' => 2
                                               ),
                                    6 => array('name' => $formaCaptura,
                                               'size' => 1
                                               ),
                                    7 => array('name' => $numeroParcelas,
                                               'size' => 2
                                               ),
                                    8 => array('name' => $codigoSegmento,
                                               'size' => 3
                                               ),
                                    9 => array('name' => number_format(($tarifaIntercam*100), 0, '', ''),
                                               'size' => 4
                                               ),
                                    10 => array('name' => number_format(($rs_row['total']*100), 0, '', ''),
                                               'size' => 15
                                               ),
                                    11 => array('name' => $rs_row['quantidade'],
                                               'size' => 12
                                               ),
                                    );
                $file->setVetorLines($vetorLines);
            }//end else do if(!$rs)

            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo INTERCAM.TXT




            //==================================  Inicio do trecho a geração do arquivo LUCRCRED.TXT
            $nomeArquivo = 'lucrcred.txt';
            $nomeArquivosTrimestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            $quantidadeLinhas = 1; //Devido a ser fixo somente uma linha com totais de estabelecimentos

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'lucrcred',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);

            // Buscando informações congeladas
            $sql = "
                select 
                   sum(c_receita_credenciador) as c_receita_credenciador,
                   sum(c_receita_outras_credenciador) as c_receita_outras_credenciador,
                   sum(c_custo_processamento_front_end_back_end) as c_custo_processamento_front_end_back_end,
                   sum(c_custo_mkt_credenciado) as c_custo_mkt_credenciado, 
                   sum(c_custo_risco_credenciador) as c_custo_risco_credenciador, 
                   sum(c_custo_outros_credenciador) as c_custo_outros_credenciador, 
                   sum(c_receita_mkt_emissor) as c_receita_mkt_emissor, 
                   sum(c_receita_outras_emissor) as c_receita_outras_emissor, 
                   sum(c_custo_risco_emissor) as c_custo_risco_emissor, 
                   sum(c_custo_processamento_emissor) as c_custo_processamento_emissor, 
                   sum(c_custo_mkt_emissor) as c_custo_mkt_emissor, 
                   sum(c_custo_inadimplencia_emissor) as c_custo_inadimplencia_emissor, 
                   sum(c_custos_outros_emissor) as c_custos_outros_emissor, 
                   sum(c_custo_impostos_emissor) as c_custo_impostos_emissor
                from complice
                where c_ano_mes >= '".getStartDateTrimestral($mes,$ano)." 00:00:00' 
                   and c_ano_mes <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
               ";
            $rsInfoComplementar = SQLexecuteQuery($sql);
            if(!$rsInfoComplementar) echo "Erro ao selecionar o Publisher Dados Complementares (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                //Capturando Dados Complementares
                $rsInfoComplementar_row = pg_fetch_array($rsInfoComplementar);
            }//end else do if(!$rsDesvio)


            // Dados 
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => number_format(($rsInfoComplementar_row['c_receita_credenciador']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                3 => array('name' => number_format(($receitaAluguelEquipamentos*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                4 => array('name' => number_format(($rsInfoComplementar_row['c_receita_outras_credenciador']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                5 => array('name' => number_format(($custoTarifaIntercambio*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                6 => array('name' => number_format(($rsInfoComplementar_row['c_custo_mkt_credenciado']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                7 => array('name' => number_format(($custoTaxaAcessoBandeira*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                8 => array('name' => number_format(($rsInfoComplementar_row['c_custo_risco_credenciador']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                9 => array('name' => number_format(($rsInfoComplementar_row['c_custo_processamento_front_end_back_end']*100), 0, '', ''), 
                                           'size' => 12
                                           ),
                                10 => array('name' => number_format(($rsInfoComplementar_row['c_custo_outros_credenciador']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                );
            $file->setVetorLines($vetorLines);


            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo LUCRCRED.TXT




            //==================================  Inicio do trecho a geração do arquivo CONCCRED.TXT
            $nomeArquivo = 'conccred.txt';
            $nomeArquivosTrimestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            $quantidadeLinhas = 1; //Devido a ser fixo somente uma linha com totais de estabelecimentos

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'conccred',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);

            // Buscando informações congeladas
            $sql = "
                select 
                        sum(quantidade) as quantidade, 
                        ROUND(sum(total),2) as total,
                        sum(total_sem_descontos) as total_sem_descontos,
                        sum(total_comissao) as total_comissao
                from ( 
 
                    ";
            $insere_union_all = 1;
            foreach ($vetorPublisher as $key => $value) {
                if($insere_union_all > 1) {
                    $sql .= "

                      union all

                    ";
                } //end if($insere_union_all > 1)

                $sql .= "
                        (
                            select 
                                 ";
                if (in_array($value,$vetorPublisherNacionaisTestes))
                        $sql .= "sum(fp_total) as total,";
                else $sql .= "ROUND((sum(fp_total)/(1+".$iof."/100)),2) as total,";
                $sql .= "
                                    sum(fp_number) as quantidade, 
                                    sum(fp_total) as total_sem_descontos,
                                    sum(fp_comission) as total_comissao
                            from financial_processing 
                            where  fp_publisher = ".$value."
                                   and fp_date >=  CASE 
                                                        WHEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                        THEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                        ELSE 
                                   '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                        END
                                   and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                   and fp_freeze=1 
                        )
                        ";
                $insere_union_all++;
                
            }//end foreach ($vetorPublisher as $key => $value)

            
            if(!empty($verificadorPublishersNovos)) {
                foreach ($vetorPublisherNovos as $key => $value) {
                    //echo "Key: $key -- value: $value <br>";
                    $sql .= "

                      union all

                         (
                            select 
                                  ";
                    if (in_array($value,$vetorPublisherNacionaisTestes))
                            $sql .= "sum(fp_total) as total,";
                    else $sql .= "ROUND((sum(fp_total)/(1+".$iof."/100)),2) as total,";
                    $sql .= "
                                    sum(fp_number) as quantidade, 
                                    sum(fp_total) as total_sem_descontos,
                                    sum(fp_comission) as total_comissao
                            from financial_processing 
                            where  fp_publisher = ".$value."
                                   and fp_date >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                   and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                   and fp_freeze=1
                         )
                             ";
                }//end foreach
            } //end if(!empty($verificadorPublishersNovos))
            $sql .= " 
                ) tabelaUnion     
                ";
                
            //echo $sql."\n"; die();

            $rs = SQLexecuteQuery($sql);
            if(!$rs) echo "Erro ao selecionar os Publishers (".implode(",", $vetorPublisher).") e Publishers Novos (".implode(",", $vetorPublisherNovos).").<br>\n";
            else { 

                $rs_row = pg_fetch_array($rs);

                //echo $value."<pre>".print_r($rs_row,true)."</pre>".$sql."<br>";

                // Dados 
                unset($vetorLines);
                $vetorLines = array (
                                    0 => array('name' => $ano,
                                               'size' => 4
                                                ),
                                    1 => array('name' => trimestre($trimestre),
                                               'size' => 1
                                                ),
                                    2 => array('name' => $bandeira,
                                               'size' => 2
                                               ),
                                    3 => array('name' => $funcao,
                                               'size' => 1
                                               ),
                                    4 => array('name' => $qtdeEstabelecimentos,
                                               'size' => 9
                                               ),
                                    5 => array('name' => $qtdeEstabelecimentosAtivo,
                                               'size' => 9
                                               ),
                                    6 => array('name' => number_format(($rs_row['total']*100), 0, '', ''),
                                               'size' => 15
                                               ),
                                    7 => array('name' => $rs_row['quantidade'],
                                               'size' => 12
                                               ),
                                    );
                $file->setVetorLines($vetorLines);
            }//end else do if(!$rs)

            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo CONCCRED.TXT




            //==================================  Inicio do trecho a geração do arquivo INFRESTA.TXT
            $nomeArquivo = 'infresta.txt';
            $nomeArquivosTrimestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            //Criando vetor Auxiliar para elaboração da query
            if(!empty($verificadorPublishersNovos)) {
                $auxVetor = array_merge($vetorPublisherNacionais,$vetorPublisherNovosNacionais);
            }//end if(!empty($verificadorPublishersNovos)) 
            else $auxVetor = $vetorPublisherNacionais;
        
            // Buscando informações congeladas
            $sql = "
                SELECT 
                        opr_estado as uf,
                        count(opr_codigo) as qtde_total_estabelecimentos, 
                        0 as qtde_estab_captura_manual, 
                        0 as qtde_estab_captura_eletronica,
                        count(opr_codigo) as qtde_estab_captura_remota
                FROM operadoras
                WHERE opr_codigo IN (".implode(",", $auxVetor).")
                GROUP BY opr_estado
                ORDER BY uf;";
            $rs = SQLexecuteQuery($sql);
            if(!$rs) echo "Erro ao selecionar os Publishers (".implode(",", $vetorPublisher).") e Publishers Novos (".implode(",", $vetorPublisherNovos).").<br>\n";
            else { 
                // Verificando a quantidade
                $quantidadeLinhas = pg_num_rows($rs); //capturando a quantidade de linhas

                // Cabeçalho
                unset($vetorHeader);
                $vetorHeader = array (
                                     0 => array('name' => 'infresta',
                                                'size' => 8
                                                ),
                                     1 => array('name' => date('Ymd'),
                                                'size' => 8
                                                ),
                                     2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                                'size' => 8
                                                ),
                                     3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                                'size' => 8
                                                ),
                                );

                $file->setVetorHeader($vetorHeader);

                while($rs_row = pg_fetch_array($rs)) {
                    //echo $value."<pre>".print_r($rs_row,true)."</pre>".$sql."<br>";

                    // Dados 
                    unset($vetorLines);
                    $vetorLines = array (
                                        0 => array('name' => $ano,
                                                   'size' => 4
                                                    ),
                                        1 => array('name' => trimestre($trimestre),
                                                   'size' => 1
                                                    ),
                                        2 => array('name' => $rs_row['uf'],
                                                   'size' => 2
                                                   ),
                                        3 => array('name' => $rs_row['qtde_total_estabelecimentos'],
                                                   'size' => 8
                                                   ),
                                        4 => array('name' => $rs_row['qtde_estab_captura_manual'],
                                                   'size' => 8
                                                   ),
                                        5 => array('name' => $rs_row['qtde_estab_captura_eletronica'],
                                                   'size' => 8
                                                   ),
                                        6 => array('name' => $rs_row['qtde_estab_captura_remota'],
                                                   'size' => 8
                                                   ),
                                        );
                    $file->setVetorLines($vetorLines);

                }//end while ($rs_row = pg_fetch_array($rs))
                $file->saveFile(true);
            
            }//end else do if(!$rs)

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo INFRESTA.TXT




            //==================================  Inicio do trecho a geração do arquivo INFRTERM.TXT
            $nomeArquivo = 'infrterm.txt';
            $nomeArquivosTrimestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);


            //Criando vetor Auxiliar para elaboração da query
            if(!empty($verificadorPublishersNovos)) {
                $auxVetor = array_merge($vetorPublisherNacionais,$vetorPublisherNovosNacionais);
            }//end if(!empty($verificadorPublishersNovos)) 
            else $auxVetor = $vetorPublisherNacionais;
            
            
            // Buscando informações congeladas
            $sql = "
                SELECT 
                        opr_estado as uf,
                        0 as qtde_terminais_pos, 
                        0 as qtde_terminais_pos_compartilhados, 
                        0 as qtde_terminais_pos_leitora_dechip,
                        count(opr_codigo) as qtde_terminais_pdv
                FROM operadoras
                WHERE opr_codigo IN (".implode(",", $auxVetor).")
                GROUP BY opr_estado
                ORDER BY uf; ";
            $rs = SQLexecuteQuery($sql);
            if(!$rs) echo "Erro ao selecionar os Publishers (".implode(",", $vetorPublisher).") e Publishers Novos (".implode(",", $vetorPublisherNovos).").<br>\n";
            else { 
                // Verificando a quantidade
                $quantidadeLinhas = pg_num_rows($rs); //capturando a quantidade de linhas

                // Cabeçalho
                unset($vetorHeader);
                $vetorHeader = array (
                                     0 => array('name' => 'infrterm',
                                                'size' => 8
                                                ),
                                     1 => array('name' => date('Ymd'),
                                                'size' => 8
                                                ),
                                     2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                                'size' => 8
                                                ),
                                     3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                                'size' => 8
                                                ),
                                );

                $file->setVetorHeader($vetorHeader);


                while($rs_row = pg_fetch_array($rs)) {

                    // Dados 
                    unset($vetorLines);
                    $vetorLines = array (
                                        0 => array('name' => $ano,
                                                   'size' => 4
                                                    ),
                                        1 => array('name' => trimestre($trimestre),
                                                   'size' => 1
                                                    ),
                                        2 => array('name' => $rs_row['uf'],
                                                   'size' => 2
                                                   ),
                                        3 => array('name' => $rs_row['qtde_terminais_pos'],
                                                   'size' => 8
                                                   ),
                                        4 => array('name' => $rs_row['qtde_terminais_pos_compartilhados'],
                                                   'size' => 8
                                                   ),
                                        5 => array('name' => $rs_row['qtde_terminais_pos_leitora_dechip'],
                                                   'size' => 8
                                                   ),
                                        6 => array('name' => $rs_row['qtde_terminais_pdv'],
                                                   'size' => 8
                                                   ),
                                        );
                    $file->setVetorLines($vetorLines);

                }//end while ($rs_row = pg_fetch_array($rs))
                $file->saveFile(true);
            
            }//end else do if(!$rs)

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo INFRTERM.TXT




            //==================================  Inicio do trecho a geração do arquivo CONTATOS.TXT
            $nomeArquivo = 'contatos.txt';
            $nomeArquivosTrimestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            $quantidadeLinhas = 4; //Devido a ser fixo somente 4 Contatos diretor responsável pela prestação das informações, de dois técnicos designados como responsáveis e um contato institucional

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'contatos',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);

            // Dados Diretor
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'D',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'Glaucia da Costa Gregio',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'Diretora',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9105',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'glaucia@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // Dados Técnico 01
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'T',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'Wagner de Miranda',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'Gerente de TI',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9101',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'wagner@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // Dados Técnico 02
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'T',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'LUIS GUSTAVO DE SOUZA CARVALHO',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'ANALISTA DESENVOLVEDOR PHP',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9101',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'luis.gustavo@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);
            
            // Dados Institucional
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'I',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'Katia Godoy de Medeiros',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'ANALISTA CONTÁBIL FINANCEIRO',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9105',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'katia.medeiros@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo CONTATOS.TXT

            
            
            
            //==================================  Inicio do trecho a geração do arquivo DATABASE.TXT
            $nomeArquivo = 'database.txt';
            $nomeArquivosTrimestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'database',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $ano.$mes, //Data-base dos arquivos enviados (AAAAMM), correspondendo ao último mês do trimestre de referência. Por exemplo, a data-base do quarto trimestre de 2018 é 201812. 
                                            'size' => 6
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);

            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo DATABASE.TXT

            
            
            
            //==================================  Início do trecho compactando arquivos Trimestrais para serem enviados ao BACEN
            $nomeArquivoTrimestralZipado = "aspb034_6334_".trimestre($trimestre)."T".$ano.".zip"; //Exemplo: aspb008_6308_3T2014.zip
            $file = new FilePosition($nomeArquivoTrimestralZipado); 
            $file->createZip($nomeArquivosTrimestrais,true);
            echo "Arquivo Trimestral Zipado Criado com Sucesso: <a href='/bacen/".date('Ymd')."/".$nomeArquivoTrimestralZipado."'>".$nomeArquivoTrimestralZipado."</a><br><hr><br><br>\n";
            //==================================  Fim do trecho compactando arquivos Trimestrais para serem enviados ao BACEN
            

            
            
       }//end do if(pg_num_rows($rs_complice) == 3 || $testeData == $dataInicioOperacao)
        else {
            die("O trimestre não possui Todos os mêses Necessários Cadastrados para os Dados Complementares de Complice no BackOffice.<br>Por favor, verificar no BackOffice.");
        }//end else do if(pg_num_rows($rs_complice) == 3 || $testeData == $dataInicioOperacao)

    }//end if(pg_num_rows($rs_complice_verify) == 1)
    else {
        die("Necessários Cadastrar os Dados Complementares de Complice no BackOffice antes de continuar.");
    }//end else do if(pg_num_rows($rs_complice_verify) == 1)
    
} //end if(isTrimestral($mes))




// Teste de verificação se é mês para geração dos arquivos Semestrais
if(isSemestral($mes)) {
    
    
    
    //================================== Início Verificando se os Dados Financeiros estão devidamente apurados (Congelados na Geração de Notas)
    $sql = "
        select 
                fp_publisher, 
                data_sem_notas
        from ( 
        ";
    $insere_union_all = 1;
    foreach ($vetorPublisher as $key => $value) {
        if($insere_union_all > 1) {
            $sql .= "

              union all

            ";
        } //end if($insere_union_all > 1)
        $sql .= "   (
                    select 
                            fp_publisher, 
                            to_char(fp_date, 'YYYY-MM-DD') as data_sem_notas
                    from financial_processing 
                    where  fp_publisher = ".$value."
                           and fp_date >=  CASE 
                                                WHEN 
                           ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > '".getStartDateTrimestral($mes-3,$ano)." 00:00:00'::timestamp 
                                                THEN 
                           ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                ELSE 
                           '".getStartDateTrimestral($mes-3,$ano)." 00:00:00'::timestamp 
                                                END
                           and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                           and fp_freeze!=1
                    group by fp_publisher, data_sem_notas
                ) ";
        $insere_union_all++;

    }//end foreach ($vetorPublisher as $key => $value)


    if(!empty($verificadorPublishersNovos)) {
        foreach ($vetorPublisherNovos as $key => $value) {
            //echo "Key: $key -- value: $value <br>";
            $sql .= "

              union all

                 (
                    select 
                            fp_publisher, 
                            to_char(fp_date, 'YYYY-MM-DD') as data_sem_notas
                    from financial_processing 
                    where  fp_publisher = ".$value."
                           and fp_date >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                           and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                           and fp_freeze!=1
                    group by fp_publisher, data_sem_notas
                 )
                     ";
        }//end foreach
    } //end if(!empty($verificadorPublishersNovos))
    $sql .= " 
            ) tabelaUnion      
        group by fp_publisher, data_sem_notas
        order by fp_publisher, data_sem_notas; ";

    //echo $sql."\n"; die();

    $rs_nao_emitidos = SQLexecuteQuery($sql);
    
    if(pg_num_rows($rs_nao_emitidos) != 0) {
        echo "<hr><b>Faltam Emitir Notas: (TOTAL [".pg_num_rows($rs_nao_emitidos)."] Dias/Publishers)</b><br><br>\n";
        while($rs_nao_emitidos_row = pg_fetch_array($rs_nao_emitidos)) {
                echo " * Publisher ID [".$rs_nao_emitidos_row['fp_publisher']."] => Data [".$rs_nao_emitidos_row['data_sem_notas']."] <br>\n";
        } //end while
        die("------- Emitir Notas para os Publisher e Períodos acima antes de gerar os Arquivos -------------<br><hr>\n");
    }//end if(pg_num_rows($rs_nao_emitidos) != 0)
    //================================== Fim Verificando se os Dados Financeiros estão devidamente apurados (Congelados na Geração de Notas)
  



    //================================== Verificando se os Dados Complementares estão devidamente cadastrados
    $sql = "SELECT  *
            FROM complice 
            WHERE c_ano_mes =  '".$ano."-".$mes."-01';"; 
    //echo $sql."<br>";
    $rs_complice = SQLexecuteQuery($sql);
    if(pg_num_rows($rs_complice) == 1) {

        
        
        
        //================================== Verificando se os Dados Complementares de TODO o TRIMESTRE estão devidamente cadastrados
        $sql = "SELECT  *
                FROM complice 
                WHERE c_ano_mes >= '".getStartDateSemestral($mes,$ano)." 00:00:00' 
                    and c_ano_mes <= '".getEndDateSemestral($mes,$ano)." 00:00:00'"; 
        //echo $sql."<br>";
        $rs_complice = SQLexecuteQuery($sql);
        if(pg_num_rows($rs_complice) == 6 || $testeData == $dataInicioOperacao) {

    
            echo "<b>Gerando Arquivos Semestrais</b><br><br>\n";
            
            
            //==================================  Inicio do trecho a geração do arquivo EMISSOR.TXT
            $nomeArquivo = 'emissor.txt';
            $nomeArquivosSemestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            //Total de linhas de emissor (2 trimestre)
            $quantidadeLinhas = 2;

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'emissor',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $ISPB, 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);
            
            // Dados 
            // 1º Trimestre
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ISPB,
                                           'size' => 8
                                            ),
                                1 => array('name' => $nomeEmissor,
                                           'size' => 50
                                            ),
                                2 => array('name' => $ano,
                                           'size' => 4
                                           ),
                                3 => array('name' => trimestre($trimestre-3),
                                           'size' => 1
                                           ),
                                );

            $file->setVetorLines($vetorLines);

            // 2º Trimestre
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ISPB,
                                           'size' => 8
                                            ),
                                1 => array('name' => $nomeEmissor,
                                           'size' => 50
                                            ),
                                2 => array('name' => $ano,
                                           'size' => 4
                                           ),
                                3 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                           ),
                                );

            $file->setVetorLines($vetorLines);

            $file->saveFile(true);

            if($file->checkFile()){
                echo "<hr>Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "<hr>Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo EMISSOR.TXT




            //==================================  Inicio do trecho a geração do arquivo PORTADOR.TXT
            $nomeArquivo = 'portador.txt';
            $nomeArquivosSemestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            //Total de linhas de emissor (2 trimestre)
            $quantidadeLinhas = 2;

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'portador',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $ISPB, 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);
            
            // Dados 
            // 1º Trimestre
            //Dados sobre Taxas de Manutenção Anual
            $sql = "
                SELECT 
			MIN(pta_valor) as minimo,
			MAX(pta_valor) as maximo,
			COUNT(DISTINCT(pta_valor)) as numero_aliquota,
                        ROUND((sum(pta_valor)/COUNT(*)),2) as media_simples
                FROM tb_pag_taxa_anual	
                WHERE pta_data >= '".getStartDateTrimestral(($mes-3),$ano)." 00:00:00'				
                        AND pta_data <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59';
                ";
            //echo $sql."\n"; die();
            $rsDadosManutecaoAnual = SQLexecuteQuery($sql);
            if(!$rsDadosManutecaoAnual) echo "Erro ao selecionar os Dados sobre Taxas de Manutenção Anual (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                //Capturando Dados sobre Taxas de Manutenção Anual
                $rsDadosManutecaoAnual_row = pg_fetch_array($rsDadosManutecaoAnual);
            }//end else do if(!$rsDadosManutecaoAnual)

            //Relação de alicotas para o cálculo de desvio padrão
            $sql = "
                SELECT 
			DISTINCT(pta_valor) as aliquota
                FROM tb_pag_taxa_anual	
                WHERE pta_data >= '".getStartDateTrimestral(($mes-3),$ano)." 00:00:00'				
                        AND pta_data <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                ORDER BY aliquota;
                ";
            //echo $sql."\n"; die();
            $rsRelacaoAlicota = SQLexecuteQuery($sql);
            if(!$rsRelacaoAlicota) echo "Erro ao selecionar a Relação de Alicotas dos Dados sobre Taxas de Manutenção Anual (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                    // Cálculo do DESVIO PADRÃO
                    $desvio_padrao = 0;

                    while($rsRelacaoAlicota_row = pg_fetch_array($rsRelacaoAlicota)) {
                        $desvio_padrao += pow($rsRelacaoAlicota_row['aliquota']-$rsDadosManutecaoAnual_row['media_simples'], 2);
                    }//end while

                    $desvio_padrao = sqrt($desvio_padrao/$rsDadosManutecaoAnual_row['numero_aliquota']);
                    
            }//end else do if(!$rsRelacaoAlicota)
            //echo "Desvio Padrão:".$desvio_padrao." Desvio padrão Formatado: ".number_format(($desvio_padrao), 2, ',', '.'); die();

            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre-3),
                                           'size' => 1
                                            ),
                                2 => array('name' => $produto,
                                           'size' => 2
                                           ),
                                3 => array('name' => $bandeira,
                                           'size' => 2
                                           ),
                                4 => array('name' => $modalidade,
                                           'size' => 1
                                           ),
                                5 => array('name' => $funcao,
                                           'size' => 1
                                           ),
                                6 => array('name' => number_format(($rsDadosManutecaoAnual_row['minimo']*100), 0, '', ''),     //Tarifa de anuidade mínima
                                           'size' => 6
                                           ),
                                7 => array('name' => number_format(($rsDadosManutecaoAnual_row['media_simples']*100), 0, '', ''),     //Tarifa de anuidade média
                                           'size' => 6
                                           ),
                                8 => array('name' => number_format(($rsDadosManutecaoAnual_row['maximo']*100), 0, '', ''),     //Tarifa de anuidade máxima
                                           'size' => 6
                                           ),
                                9 => array('name' => number_format(($desvio_padrao*100), 0, '', ''),     //Desvio padrão da tarifa de anuidade
                                           'size' => 6
                                           ),
                                10 => array('name' => 0,    //Estoque de pontos acumulados nas contas dos portadores
                                           'size' => 12
                                           ),
                                11 => array('name' => 0,    //Quantidade de pontos adquiridos no âmbito dos programas de recompensa do emissor
                                           'size' => 12
                                           ),
                                12 => array('name' => 0,    //Quantidade de pontos convertidos (transferidos)
                                           'size' => 12
                                           ),
                                13 => array('name' => 0,    //Quantidade de pontos expirados
                                           'size' => 12
                                           ),
                                14 => array('name' => 0,    //Gasto efetivo do emissor com programas de recompensa
                                           'size' => 12
                                           ),
                                );

            $file->setVetorLines($vetorLines);

            // 2º Trimestre
            //Dados sobre Taxas de Manutenção Anual
            $sql = "
                SELECT 
			MIN(pta_valor) as minimo,
			MAX(pta_valor) as maximo,
			COUNT(DISTINCT(pta_valor)) as numero_aliquota,
                        ROUND((sum(pta_valor)/COUNT(*)),2) as media_simples
                FROM tb_pag_taxa_anual	
                WHERE pta_data >= '".getStartDateTrimestral(($mes),$ano)." 00:00:00'				
                        AND pta_data <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59';
                ";
            //echo $sql."\n"; die();
            $rsDadosManutecaoAnual = SQLexecuteQuery($sql);
            if(!$rsDadosManutecaoAnual) echo "Erro ao selecionar os Dados sobre Taxas de Manutenção Anual (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                //Capturando Dados sobre Taxas de Manutenção Anual
                $rsDadosManutecaoAnual_row = pg_fetch_array($rsDadosManutecaoAnual);
            }//end else do if(!$rsDadosManutecaoAnual)

            //Relação de alicotas para o cálculo de desvio padrão
            $sql = "
                SELECT 
			DISTINCT(pta_valor) as aliquota
                FROM tb_pag_taxa_anual	
                WHERE pta_data >= '".getStartDateTrimestral(($mes),$ano)." 00:00:00'				
                        AND pta_data <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                ORDER BY aliquota;
                ";
            //echo $sql."\n"; die();
            $rsRelacaoAlicota = SQLexecuteQuery($sql);
            if(!$rsRelacaoAlicota) echo "Erro ao selecionar a Relação de Alicotas dos Dados sobre Taxas de Manutenção Anual (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                    // Cálculo do DESVIO PADRÃO
                    $desvio_padrao = 0;

                    while($rsRelacaoAlicota_row = pg_fetch_array($rsRelacaoAlicota)) {
                        $desvio_padrao += pow($rsRelacaoAlicota_row['aliquota']-$rsDadosManutecaoAnual_row['media_simples'], 2);
                    }//end while

                    $desvio_padrao = sqrt($desvio_padrao/$rsDadosManutecaoAnual_row['numero_aliquota']);
                    
            }//end else do if(!$rsRelacaoAlicota)
            //echo "Desvio Padrão:".$desvio_padrao." Desvio padrão Formatado: ".number_format(($desvio_padrao), 2, ',', '.'); die();

            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => $produto,
                                           'size' => 2
                                           ),
                                3 => array('name' => $bandeira,
                                           'size' => 2
                                           ),
                                4 => array('name' => $modalidade,
                                           'size' => 1
                                           ),
                                5 => array('name' => $funcao,
                                           'size' => 1
                                           ),
                                6 => array('name' => number_format(($rsDadosManutecaoAnual_row['minimo']*100), 0, '', ''),     //Tarifa de anuidade mínima
                                           'size' => 6
                                           ),
                                7 => array('name' => number_format(($rsDadosManutecaoAnual_row['media_simples']*100), 0, '', ''),     //Tarifa de anuidade média
                                           'size' => 6
                                           ),
                                8 => array('name' => number_format(($rsDadosManutecaoAnual_row['maximo']*100), 0, '', ''),     //Tarifa de anuidade máxima
                                           'size' => 6
                                           ),
                                9 => array('name' => number_format(($desvio_padrao*100), 0, '', ''),     //Desvio padrão da tarifa de anuidade
                                           'size' => 6
                                           ),
                                10 => array('name' => 0,    //Estoque de pontos acumulados nas contas dos portadores
                                           'size' => 12
                                           ),
                                11 => array('name' => 0,    //Quantidade de pontos adquiridos no âmbito dos programas de recompensa do emissor
                                           'size' => 12
                                           ),
                                12 => array('name' => 0,    //Quantidade de pontos convertidos (transferidos)
                                           'size' => 12
                                           ),
                                13 => array('name' => 0,    //Quantidade de pontos expirados
                                           'size' => 12
                                           ),
                                14 => array('name' => 0,    //Gasto efetivo do emissor com programas de recompensa
                                           'size' => 12
                                           ),
                                );

            $file->setVetorLines($vetorLines);

            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo PORTADOR.TXT




            //==================================  Inicio do trecho a geração do arquivo LUCREMIS.TXT
            $nomeArquivo = 'lucremis.txt';
            $nomeArquivosSemestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            //Total de linhas de emissor (2 trimestre)
            $quantidadeLinhas = 2;

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'lucremis',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $ISPB, 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);
            
            // Dados 
            // 1º Trimestre
            $sql = "
                select 
                   sum(c_custo_mkt_credenciado) as c_custo_mkt_credenciado, 
                   sum(c_custo_risco_credenciador) as c_custo_risco_credenciador, 
                   sum(c_custo_outros_credenciador) as c_custo_outros_credenciador, 
                   sum(c_receita_mkt_emissor) as c_receita_mkt_emissor, 
                   sum(c_receita_outras_emissor) as c_receita_outras_emissor, 
                   sum(c_custo_risco_emissor) as c_custo_risco_emissor, 
                   sum(c_custo_processamento_emissor) as c_custo_processamento_emissor, 
                   sum(c_custo_mkt_emissor) as c_custo_mkt_emissor, 
                   sum(c_custo_inadimplencia_emissor) as c_custo_inadimplencia_emissor, 
                   sum(c_custos_outros_emissor) as c_custos_outros_emissor, 
                   sum(c_custo_impostos_emissor) as c_custo_impostos_emissor
                from complice
                where c_ano_mes >= '".getStartDateTrimestral(($mes-3),$ano)." 00:00:00' 
                   and c_ano_mes <= '".getEndDateTrimestral(($mes-3),$ano)." 00:00:00'
               ";
                
            //echo $sql."\n"; die();

            $rsInfoComplementar = SQLexecuteQuery($sql);
            if(!$rsInfoComplementar) echo "Erro ao selecionar o Publisher Dados Complementares (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                //Capturando Dados Complementares
                $rsInfoComplementar_row = pg_fetch_array($rsInfoComplementar);
            }//end else do if(!$rsInfoComplementar)

            //Total de taxas de manutenção anual no trimestre
            $sql = "
                SELECT SUM(pta_valor) as total
                FROM tb_pag_taxa_anual	
                WHERE pta_data >= '".getStartDateTrimestral(($mes-3),$ano)." 00:00:00'				
                        AND pta_data <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                ";
            //echo $sql."\n"; die();
            $rsTotalManutecaoAnual = SQLexecuteQuery($sql);
            if(!$rsTotalManutecaoAnual) echo "Erro ao selecionar o Total de Taxas de Manutenção Anual (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                //Capturando Total de taxas de manutenção anual no trimestre
                $rsTotalManutecaoAnual_row = pg_fetch_array($rsTotalManutecaoAnual);
            }//end else do if(!$rsTotalManutecaoAnual)
            

            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre-3),
                                           'size' => 1
                                            ),
                                2 => array('name' => number_format(($custoTarifaIntercambio*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                3 => array('name' => number_format(($rsTotalManutecaoAnual_row['total']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                4 => array('name' => number_format(($receitaEmissaoeFaturamento*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                5 => array('name' => number_format(($receitaFinanceira*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                6 => array('name' => number_format(($rsInfoComplementar_row['c_receita_mkt_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                7 => array('name' => number_format(($rsInfoComplementar_row['c_receita_outras_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                8 => array('name' => number_format(($rsInfoComplementar_row['c_custo_risco_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                9 => array('name' => number_format(($rsInfoComplementar_row['c_custo_processamento_emissor']*100), 0, '', ''), 
                                           'size' => 12
                                           ),
                                10 => array('name' => number_format(($rsInfoComplementar_row['c_custo_mkt_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                11 => array('name' => number_format(($custoTaxaAcessoBandeira*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                12 => array('name' => number_format(($rsInfoComplementar_row['c_custo_inadimplencia_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                13 => array('name' => number_format(($rsInfoComplementar_row['c_custos_outros_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                14 => array('name' => number_format(($rsInfoComplementar_row['c_custo_impostos_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                15 => array('name' => number_format(($custoProgramaRecompensa*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // 2º Trimestre
            $sql = "
                select 
                   sum(c_custo_mkt_credenciado) as c_custo_mkt_credenciado, 
                   sum(c_custo_risco_credenciador) as c_custo_risco_credenciador, 
                   sum(c_custo_outros_credenciador) as c_custo_outros_credenciador, 
                   sum(c_receita_mkt_emissor) as c_receita_mkt_emissor, 
                   sum(c_receita_outras_emissor) as c_receita_outras_emissor, 
                   sum(c_custo_risco_emissor) as c_custo_risco_emissor, 
                   sum(c_custo_processamento_emissor) as c_custo_processamento_emissor, 
                   sum(c_custo_mkt_emissor) as c_custo_mkt_emissor, 
                   sum(c_custo_inadimplencia_emissor) as c_custo_inadimplencia_emissor, 
                   sum(c_custos_outros_emissor) as c_custos_outros_emissor, 
                   sum(c_custo_impostos_emissor) as c_custo_impostos_emissor
                from complice
                where c_ano_mes >= '".getStartDateTrimestral($mes,$ano)." 00:00:00' 
                   and c_ano_mes <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
               ";
                
            //echo $sql."\n"; die();

            $rsInfoComplementar = SQLexecuteQuery($sql);
            if(!$rsInfoComplementar) echo "Erro ao selecionar o Publisher Dados Complementares (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                //Capturando Dados Complementares
                $rsInfoComplementar_row = pg_fetch_array($rsInfoComplementar);
            }//end else do if(!$rsInfoComplementar)

            //Total de taxas de manutenção anual no trimestre
            $sql = "
                SELECT SUM(pta_valor) as total
                FROM tb_pag_taxa_anual	
                WHERE pta_data >= '".getStartDateTrimestral($mes,$ano)." 00:00:00'				
                        AND pta_data <= '".getEndDateTrimestral($mes,$ano)." 23:59:59'
                ";
            //echo $sql."\n"; die();
            $rsTotalManutecaoAnual = SQLexecuteQuery($sql);
            if(!$rsTotalManutecaoAnual) echo "Erro ao selecionar o Total de Taxas de Manutenção Anual (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                //Capturando Total de taxas de manutenção anual no trimestre
                $rsTotalManutecaoAnual_row = pg_fetch_array($rsTotalManutecaoAnual);
            }//end else do if(!$rsTotalManutecaoAnual)
            

            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => number_format(($custoTarifaIntercambio*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                3 => array('name' => number_format(($rsTotalManutecaoAnual_row['total']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                4 => array('name' => number_format(($receitaEmissaoeFaturamento*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                5 => array('name' => number_format(($receitaFinanceira*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                6 => array('name' => number_format(($rsInfoComplementar_row['c_receita_mkt_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                7 => array('name' => number_format(($rsInfoComplementar_row['c_receita_outras_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                8 => array('name' => number_format(($rsInfoComplementar_row['c_custo_risco_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                9 => array('name' => number_format(($rsInfoComplementar_row['c_custo_processamento_emissor']*100), 0, '', ''), 
                                           'size' => 12
                                           ),
                                10 => array('name' => number_format(($rsInfoComplementar_row['c_custo_mkt_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                11 => array('name' => number_format(($custoTaxaAcessoBandeira*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                12 => array('name' => number_format(($rsInfoComplementar_row['c_custo_inadimplencia_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                13 => array('name' => number_format(($rsInfoComplementar_row['c_custos_outros_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                14 => array('name' => number_format(($rsInfoComplementar_row['c_custo_impostos_emissor']*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                15 => array('name' => number_format(($custoProgramaRecompensa*100), 0, '', ''),
                                           'size' => 12
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo LUCREMIS.TXT



            //==================================  Inicio do trecho a geração do arquivo CONCEMIS.TXT
            $nomeArquivo = 'concemis.txt';
            $nomeArquivosSemestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            //Total de linhas de emissor (2 trimestre)
            $quantidadeLinhas = 2;

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'concemis',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $ISPB, 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);
            
            // Dados 
            // 1º Trimestre
            // Capturando a quantidade total de cartões emitidos
            $sql = "
                   select 
                       count(distinct(ug_cpf)) as cartoes_emitidos 
                   from (
                           (
                               select 
                                   ug_cpf 
                               from usuarios_games 
                               where ug_data_cpf_informado IS NOT NULL 
			           and ug_cpf != ''
			           and ug_cpf IS NOT NULL 
                                   and ug_data_cpf_informado <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                           ) 
                           
                      union all

                           (
                               select 
                                   vgcbe_cpf as ug_cpf 
                               from tb_venda_games_cpf_boleto_express 
                               where vgcbe_nome_cpf IS NOT NULL 
                                   and vgcbe_data_inclusao IS NOT NULL 
			           and vgcbe_cpf != ''
			           and vgcbe_cpf IS NOT NULL 
                                   and vgcbe_data_inclusao <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                           ) 
                   
                      ";
            foreach ($vetorPublisher as $key => $value) {
                $sql .= "

                      union all

                       (select 
                               vgm_cpf as ug_cpf 
                       from tb_dist_venda_games vg 
                               inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                       where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                               and vgm_cpf != ''
                               and vgm_cpf IS NOT NULL
			       and vg.vg_data_inclusao >= ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                               and vg.vg_data_inclusao <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                               and vgm_opr_codigo = ".$value." 
                       group by vgm_cpf)
                       
                   union all

                       (select 
                               picc_cpf as ug_cpf 
                       from pins_integracao_card_historico
			left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                       where pin_status = '4' 
                               and pih_codretepp = '2'
			       and picc_cpf IS NOT NULL
			       and picc_cpf != ''
                               and pih_data >= ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                               and pih_data <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                               and pih_id = ".$value." 
                       group by picc_cpf)
                       
                         ";
            }//end foreach ($vetorPublisher as $key => $value)

            if(!empty($verificadorPublishersNovos)) {
               foreach ($vetorPublisherNovos as $key => $value) {
                   //echo "Key: $key -- value: $value <br>";
                   $sql .= "

                   union all

                       (select 
                               vgm_cpf as ug_cpf 
                       from tb_dist_venda_games vg 
                               inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                       where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                               and vgm_cpf != ''
                               and vgm_cpf IS NOT NULL
                               and vg.vg_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                               and vg.vg_data_inclusao <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                               and vgm_opr_codigo = ".$value." 
                       group by vgm_cpf)

                   union all

                       (select 
                               picc_cpf as ug_cpf 
                       from pins_integracao_card_historico
			left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                       where pin_status = '4' 
                               and pih_codretepp = '2'
			       and picc_cpf IS NOT NULL
			       and picc_cpf != ''
                               and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                               and pih_data <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                               and pih_id = ".$value." 
                       group by picc_cpf)
                       
                       ";
               }//end foreach
            } //end if(!empty($verificadorPublishersNovos))

            $sql .= " 
                       ) tabelaUnion
                   ";

            //die($sql);

            $rsInfoEmitidos = SQLexecuteQuery($sql);
            if(!$rsInfoEmitidos) echo "Erro ao selecionar o Cartões Emitidos.<br>\n";
            else { 
                //Capturando Dados Cartões Emitidos
                $rsInfoEmitidos_row = pg_fetch_array($rsInfoEmitidos);
            }//end else do if(!$rsDesvio)
            
            // Capturando a quantidade total de cartões ativos
            $sql = "
            select   
                count(distinct(ug_cpf_tmp)) as cartoes_ativos
            from 
            (
            ";
            $insere_union_all = 1;
            foreach ($vetorPublisher as $key => $value) {
                if($insere_union_all > 1) {
                    $sql .= "

                    union all

                    ";
                } //end if($insere_union_all > 1)

                $sql .= "
                        (
                            select 
                                    ug_cpf as ug_cpf_tmp
                            from tb_venda_games vg 
                                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                    inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                            where vg.vg_ultimo_status = '".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vg.vg_data_concilia >=  CASE 
                                                                    WHEN 
                                               ('".getEndDateTrimestral(($mes-3),$ano)." 00:00:00'::timestamp - '1 year'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    THEN 
                                               ('".getEndDateTrimestral(($mes-3),$ano)." 00:00:00'::timestamp - '1 year'::interval) 
                                                                    ELSE 
                                               ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    END
                                    and vg.vg_data_concilia <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                                    and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                                    and vgm_opr_codigo  = ".$value."
                        )    

                    union all

                        (
                            select 
                                    vgm_cpf as ug_cpf_tmp
                             from tb_dist_venda_games vg 
                                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                             where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vg.vg_data_inclusao >=  CASE 
                                                                    WHEN 
                                               ('".getEndDateTrimestral(($mes-3),$ano)." 00:00:00'::timestamp - '1 year'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    THEN 
                                               ('".getEndDateTrimestral(($mes-3),$ano)." 00:00:00'::timestamp - '1 year'::interval) 
                                                                    ELSE 
                                               ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    END
                                    and vg.vg_data_inclusao <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                                    and vgm_opr_codigo  = ".$value." 
                        )
                        
                     union all

                        (
                            select 
                                    picc_cpf as ug_cpf_tmp
                             from pins_integracao_card_historico
                                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
			     where pin_status = '4' 
				    and pih_codretepp = '2' 
                                    and pih_data >=  CASE 
                                                                    WHEN 
                                               ('".getEndDateTrimestral(($mes-3),$ano)." 00:00:00'::timestamp - '1 year'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    THEN 
                                               ('".getEndDateTrimestral(($mes-3),$ano)." 00:00:00'::timestamp - '1 year'::interval) 
                                                                    ELSE 
                                               ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    END
                                    and pih_data <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                                    and pih_id  = ".$value." 
                        )                        
                        
                     union all

                        (
                            select 
                                    vgcbe_cpf as ug_cpf_tmp
                            from tb_venda_games_cpf_boleto_express
                                inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                                inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
			    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vgcbe_data_inclusao >=  CASE 
                                                                    WHEN 
                                               ('".getEndDateTrimestral(($mes-3),$ano)." 00:00:00'::timestamp - '1 year'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    THEN 
                                               ('".getEndDateTrimestral(($mes-3),$ano)." 00:00:00'::timestamp - '1 year'::interval) 
                                                                    ELSE 
                                               ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    END
                                    and vgcbe_data_inclusao <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                                    and vgm_opr_codigo  = ".$value." 
                        )                        
            ";
                $insere_union_all++;
                
            }//end foreach ($vetorPublisher as $key => $value)

            if(!empty($verificadorPublishersNovos)) {
                foreach ($vetorPublisherNovos as $key => $value) {
                    //echo "Key: $key -- value: $value <br>";
                    
                    //Levantando data para verificação se deve ser incluido a parte da querie no select principal
                    $sql_data_inicio = "select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD') as data_inicio from operadoras where opr_codigo = ".$value.";";
                    $rsDataIncioOperacao = SQLexecuteQuery($sql_data_inicio);
                    if(!$rsDataIncioOperacao) die("Erro ao selecionar a Data Iniício da Operação para o Publisher (".$value.").<br>\n");
                    else { 
                        //Capturando Dados da Início da Operação
                        $rsDataIncioOperacao_row = pg_fetch_array($rsDataIncioOperacao);
                    }//end else do if(!$rsDataIncioOperacao)

                    //die("------[".$rsDataIncioOperacao_row['data_inicio']."]<br>[".getEndDateTrimestral(($mes-3),$ano)."]\n");
                    if($rsDataIncioOperacao_row['data_inicio'] <= getEndDateTrimestral(($mes-3),$ano)) {
                        //Parte que irá unir a query principal
                        $sql .= "

                    union all

                         (
                            select 
                                    ug_cpf as ug_cpf_tmp
                            from tb_venda_games vg 
                                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                    inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                            where vg.vg_ultimo_status = '".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vg.vg_data_concilia >= ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." )  
                                    and vg.vg_data_concilia <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                                    and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                                    and vgm_opr_codigo  = ".$value."
                         )

                    union all

                        (
                            select 
                                    vgm_cpf as ug_cpf_tmp
                             from tb_dist_venda_games vg 
                                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                             where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vg.vg_data_inclusao >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." )  
                                    and vg.vg_data_inclusao <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                                    and vgm_opr_codigo  = ".$value." 
                        )
                        
                     union all

                        (
                            select 
                                    picc_cpf as ug_cpf_tmp
                             from pins_integracao_card_historico
                                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
			     where pin_status = '4' 
				    and pih_codretepp = '2' 
                                    and pih_data >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." )  
                                    and pih_data <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                                    and pih_id  = ".$value." 
                        )                        
                        
                     union all

                        (
                            select 
                                    vgcbe_cpf as ug_cpf_tmp
                            from tb_venda_games_cpf_boleto_express
                                inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                                inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
			    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vgcbe_data_inclusao >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." )  
                                    and vgcbe_data_inclusao <= '".getEndDateTrimestral(($mes-3),$ano)." 23:59:59'
                                    and vgm_opr_codigo  = ".$value." 
                        )                        
                             ";
                    }//end if($rsDataIncioOperacao_row['data_inicio'] < getEndDateTrimestral(($mes-3),$ano))
                    
                }//end foreach
            } //end if(!empty($verificadorPublishersNovos))
            $sql .= " 
            )  tabelaUnion     
            ";
            
            //echo $sql."\n"; die();

            $rsInfoAtivos = SQLexecuteQuery($sql);
            if(!$rsInfoAtivos) echo "Erro ao selecionar o Cartões Ativos.<br>\n";
            else { 
                //Capturando Dados Cartões Emitidos
                $rsInfoAtivos_row = pg_fetch_array($rsInfoAtivos);
            }//end else do if(!$rsDesvio)
            
            // TO DO => Implementar Financiamento Rotativo
            $sql = "
            select   
                sum(montante_nacional) as montante_nacional,
                sum(quantidade_nacional) as quantidade_nacional,
                ROUND((sum(total)/(1+".$iof."/100)),2) as montante_internacional,
                sum(quantidade_internacional) as quantidade_internacional,
                sum(financiamento_rotativo) as financiamento_rotativo
            from 
            (
            ";
            $insere_union_all = 1;
            foreach ($vetorPublisher as $key => $value) {
                if($insere_union_all > 1) {
                    $sql .= "

                    union all

                    ";
                } //end if($insere_union_all > 1)

                $sql .= "
                        (
                            select 
                                   CASE WHEN fp_nationality = 0 THEN sum(fp_total) ELSE 0 END as montante_nacional,
                                   CASE WHEN fp_nationality = 0 THEN sum(fp_number) ELSE 0 END as quantidade_nacional,
                                   CASE WHEN fp_nationality = 1 THEN sum(fp_total) ELSE 0 END as total,
                                   CASE WHEN fp_nationality = 1 THEN sum(fp_number) ELSE 0 END as quantidade_internacional, 
                                   0 as financiamento_rotativo
                           from financial_processing 
                           where  fp_publisher = ".$value." 
                                   and fp_date >=  CASE 
                                                        WHEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > '".getStartDateTrimestral($mes-3,$ano)." 00:00:00'::timestamp 
                                                        THEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                        ELSE 
                                   '".getStartDateTrimestral($mes-3,$ano)." 00:00:00'::timestamp 
                                                        END
                                  and fp_date <= '".getEndDateTrimestral(($mes-3),$ano)." 00:00:00'
                                  and fp_freeze=1 
                           GROUP BY fp_nationality
                        )

            ";
                $insere_union_all++;
                
            }//end foreach ($vetorPublisher as $key => $value)

            if(!empty($verificadorPublishersNovos)) {
                foreach ($vetorPublisherNovos as $key => $value) {
                    //echo "Key: $key -- value: $value <br>";
                    
                    //Levantando data para verificação se deve ser incluido a parte da querie no select principal
                    $sql_data_inicio = "select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD') as data_inicio from operadoras where opr_codigo = ".$value.";";
                    $rsDataIncioOperacao = SQLexecuteQuery($sql_data_inicio);
                    if(!$rsDataIncioOperacao) die("Erro ao selecionar a Data Iniício da Operação para o Publisher (".$value.").<br>\n");
                    else { 
                        //Capturando Dados da Início da Operação
                        $rsDataIncioOperacao_row = pg_fetch_array($rsDataIncioOperacao);
                    }//end else do if(!$rsDataIncioOperacao)

                    //die("------[".$rsDataIncioOperacao_row['data_inicio']."]<br>[".getEndDateTrimestral(($mes-3),$ano)."]\n");
                    if($rsDataIncioOperacao_row['data_inicio'] <= getEndDateTrimestral(($mes-3),$ano)) {
                        //Parte que irá unir a query principal
                        $sql .= "

                      union all

                         (
                            select 
                                   CASE WHEN fp_nationality = 0 THEN sum(fp_total) ELSE 0 END as montante_nacional,
                                   CASE WHEN fp_nationality = 0 THEN sum(fp_number) ELSE 0 END as quantidade_nacional,
                                   CASE WHEN fp_nationality = 1 THEN sum(fp_total) ELSE 0 END as total,
                                   CASE WHEN fp_nationality = 1 THEN sum(fp_number) ELSE 0 END as quantidade_internacional, 
                                   0 as financiamento_rotativo
                            from financial_processing 
                            where  fp_publisher = ".$value."
                                   and fp_date >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                   and fp_date <= '".getEndDateTrimestral($mes-3,$ano)." 00:00:00'
                                   and fp_freeze=1
                           GROUP BY fp_nationality
                         )
                             ";
                    }//end if($rsDataIncioOperacao_row['data_inicio'] < getEndDateTrimestral(($mes-3),$ano))
                    
                }//end foreach
            } //end if(!empty($verificadorPublishersNovos))
            $sql .= " 
            )  tabelaUnion     
            ";
            
            //echo $sql."\n"; die();

            $rsInfoComplementar = SQLexecuteQuery($sql);
            if(!$rsInfoComplementar) echo "Erro ao selecionar o Publisher Dados Complementares (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                //Capturando Dados Complementares
                $rsInfoComplementar_row = pg_fetch_array($rsInfoComplementar);
            }//end else do if(!$rsDesvio)


            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre-3),
                                           'size' => 1
                                            ),
                                2 => array('name' => $produto,
                                           'size' => 2
                                           ),
                                3 => array('name' => $modalidade,
                                           'size' => 1
                                           ),
                                4 => array('name' => $funcao,
                                           'size' => 1
                                           ),
                                5 => array('name' => $bandeira,
                                           'size' => 2
                                           ),
                                6 => array('name' => number_format($rsInfoEmitidos_row['cartoes_emitidos'], 0, '', ''),
                                           'size' => 9
                                           ),
                                7 => array('name' => number_format($rsInfoAtivos_row['cartoes_ativos'], 0, '', ''),
                                           'size' => 9
                                           ),
                                8 => array('name' => number_format(($rsInfoComplementar_row['montante_nacional']*100), 0, '', ''),
                                           'size' => 15
                                           ),
                                9 => array('name' => number_format(($rsInfoComplementar_row['montante_internacional']*100), 0, '', ''), 
                                           'size' => 15
                                           ),
                                10 => array('name' => number_format($rsInfoComplementar_row['quantidade_nacional'], 0, '', ''),
                                           'size' => 12
                                           ),
                                11 => array('name' => number_format($rsInfoComplementar_row['quantidade_internacional'], 0, '', ''),
                                           'size' => 12
                                           ),
                                12 => array('name' => number_format(($rsInfoComplementar_row['financiamento_rotativo']*100), 0, '', ''),
                                           'size' => 15
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // 2º Trimestre
            // Capturando a quantidade total de cartões emitidos
            $sql = "
                   select 
                       count(distinct(ug_cpf)) as cartoes_emitidos 
                   from (
                           (
                               select 
                                   ug_cpf 
                               from usuarios_games 
                               where ug_data_cpf_informado IS NOT NULL 
			           and ug_cpf != ''
			           and ug_cpf IS NOT NULL 
                                   and ug_data_cpf_informado <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                           ) 
                           
                      union all

                           (
                               select 
                                   vgcbe_cpf as ug_cpf 
                               from tb_venda_games_cpf_boleto_express 
                               where vgcbe_nome_cpf IS NOT NULL 
                                   and vgcbe_data_inclusao IS NOT NULL 
			           and vgcbe_cpf != ''
			           and vgcbe_cpf IS NOT NULL 
                                   and vgcbe_data_inclusao <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                           ) 
                   
                   ";
            foreach ($vetorPublisher as $key => $value) {
                $sql .= "

                      union all

                       (select 
                               vgm_cpf as ug_cpf 
                       from tb_dist_venda_games vg 
                               inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                       where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                               and vgm_cpf != ''
                               and vgm_cpf IS NOT NULL
			       and vg.vg_data_inclusao >= ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                               and vg.vg_data_inclusao <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                               and vgm_opr_codigo = ".$value." 
                       group by vgm_cpf)
                       
                   union all

                       (select 
                               picc_cpf as ug_cpf 
                       from pins_integracao_card_historico
			left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                       where pin_status = '4' 
                               and pih_codretepp = '2'
			       and picc_cpf IS NOT NULL
			       and picc_cpf != ''
                               and pih_data >= ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                               and pih_data <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                               and pih_id = ".$value." 
                       group by picc_cpf)
                       
                         ";
            }//end foreach ($vetorPublisher as $key => $value)

            if(!empty($verificadorPublishersNovos)) {
               foreach ($vetorPublisherNovos as $key => $value) {
                   //echo "Key: $key -- value: $value <br>";
                   $sql .= "

                   union all

                       (select 
                               vgm_cpf as ug_cpf 
                       from tb_dist_venda_games vg 
                               inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                       where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                               and vgm_cpf != ''
                               and vgm_cpf IS NOT NULL
                               and vg.vg_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                               and vg.vg_data_inclusao <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                               and vgm_opr_codigo = ".$value." 
                       group by vgm_cpf)

                   union all

                       (select 
                               picc_cpf as ug_cpf 
                       from pins_integracao_card_historico
			left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                       where pin_status = '4' 
                               and pih_codretepp = '2'
			       and picc_cpf IS NOT NULL
			       and picc_cpf != ''
                               and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                               and pih_data <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                               and pih_id = ".$value." 
                       group by picc_cpf)
                       
                            ";
               }//end foreach
            } //end if(!empty($verificadorPublishersNovos))

            $sql .= " 
                       ) tabelaUnion
                   ";

            //die($sql);

            $rsInfoEmitidos = SQLexecuteQuery($sql);
            if(!$rsInfoEmitidos) echo "Erro ao selecionar o Publisher Dados Complementares (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                //Capturando Dados Cartões Emitidos
                $rsInfoEmitidos_row = pg_fetch_array($rsInfoEmitidos);
            }//end else do if(!$rsDesvio)
            
            // Capturando a quantidade total de cartões ativos
            $sql = "
            select   
                count(distinct(ug_cpf_tmp)) as cartoes_ativos
            from 
            (
            ";
            $insere_union_all = 1;
            foreach ($vetorPublisher as $key => $value) {
                if($insere_union_all > 1) {
                    $sql .= "

                    union all

                    ";
                } //end if($insere_union_all > 1)

                $sql .= "
                        (
                            select 
                                    ug_cpf as ug_cpf_tmp
                            from tb_venda_games vg 
                                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                    inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                            where vg.vg_ultimo_status = '".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vg.vg_data_concilia >=  CASE 
                                                                    WHEN 
                                               ('".getEndDateTrimestral(($mes),$ano)." 00:00:00'::timestamp - '1 year'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    THEN 
                                               ('".getEndDateTrimestral(($mes),$ano)." 00:00:00'::timestamp - '1 year'::interval) 
                                                                    ELSE 
                                               ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    END
                                    and vg.vg_data_concilia <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                                    and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                                    and vgm_opr_codigo  = ".$value."
                        )    

                    union all

                        (
                            select 
                                    vgm_cpf as ug_cpf_tmp
                             from tb_dist_venda_games vg 
                                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                             where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vg.vg_data_inclusao >=  CASE 
                                                                    WHEN 
                                               ('".getEndDateTrimestral(($mes),$ano)." 00:00:00'::timestamp - '1 year'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    THEN 
                                               ('".getEndDateTrimestral(($mes),$ano)." 00:00:00'::timestamp - '1 year'::interval) 
                                                                    ELSE 
                                               ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    END
                                    and vg.vg_data_inclusao <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                                    and vgm_opr_codigo  = ".$value." 
                        )
                        
                     union all

                        (
                            select 
                                    picc_cpf as ug_cpf_tmp
                             from pins_integracao_card_historico
                                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
			     where pin_status = '4' 
				    and pih_codretepp = '2' 
                                    and pih_data >=  CASE 
                                                                    WHEN 
                                               ('".getEndDateTrimestral(($mes),$ano)." 00:00:00'::timestamp - '1 year'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    THEN 
                                               ('".getEndDateTrimestral(($mes),$ano)." 00:00:00'::timestamp - '1 year'::interval) 
                                                                    ELSE 
                                               ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    END
                                    and pih_data <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                                    and pih_id  = ".$value." 
                        )  
                        
                     union all

                        (
                            select 
                                    vgcbe_cpf as ug_cpf_tmp
                            from tb_venda_games_cpf_boleto_express
                                inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                                inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
			    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vgcbe_data_inclusao >=  CASE 
                                                                    WHEN 
                                               ('".getEndDateTrimestral(($mes),$ano)." 00:00:00'::timestamp - '1 year'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    THEN 
                                               ('".getEndDateTrimestral(($mes),$ano)." 00:00:00'::timestamp - '1 year'::interval) 
                                                                    ELSE 
                                               ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                    END
                                    and vgcbe_data_inclusao <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                                    and vgm_opr_codigo  = ".$value." 
                        )                        
                        
            ";
                $insere_union_all++;
                
            }//end foreach ($vetorPublisher as $key => $value)

            
            if(!empty($verificadorPublishersNovos)) {
                foreach ($vetorPublisherNovos as $key => $value) {
                    //echo "Key: $key -- value: $value <br>";
                    
                    //Levantando data para verificação se deve ser incluido a parte da querie no select principal
                    $sql_data_inicio = "select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD') as data_inicio from operadoras where opr_codigo = ".$value.";";
                    $rsDataIncioOperacao = SQLexecuteQuery($sql_data_inicio);
                    if(!$rsDataIncioOperacao) die("Erro ao selecionar a Data Iniício da Operação para o Publisher (".$value.").<br>\n");
                    else { 
                        //Capturando Dados da Início da Operação
                        $rsDataIncioOperacao_row = pg_fetch_array($rsDataIncioOperacao);
                    }//end else do if(!$rsDataIncioOperacao)

                    //die("------[".$rsDataIncioOperacao_row['data_inicio']."]<br>[".getEndDateTrimestral(($mes),$ano)."]\n");
                    if($rsDataIncioOperacao_row['data_inicio'] <= getEndDateTrimestral(($mes),$ano)) {
                        //Parte que irá unir a query principal
                        $sql .= "

                    union all

                         (
                            select 
                                    ug_cpf as ug_cpf_tmp
                            from tb_venda_games vg 
                                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                    inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                            where vg.vg_ultimo_status = '".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vg.vg_data_concilia >= ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." )  
                                    and vg.vg_data_concilia >=  ('".getEndDateTrimestral($mes,$ano)." 23:59:59'::timestamp - '1 year'::interval)
                                    and vg.vg_data_concilia <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                                    and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                                    and vgm_opr_codigo  = ".$value."
                         )

                    union all

                        (
                            select 
                                    vgm_cpf as ug_cpf_tmp
                             from tb_dist_venda_games vg 
                                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                             where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vg.vg_data_inclusao >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." )  
                                    and vg.vg_data_inclusao >=  ('".getEndDateTrimestral($mes,$ano)." 23:59:59'::timestamp - '1 year'::interval)
                                    and vg.vg_data_inclusao <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                                    and vgm_opr_codigo  = ".$value." 
                        )
                        
                     union all

                        (
                            select 
                                    picc_cpf as ug_cpf_tmp
                             from pins_integracao_card_historico
                                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
			     where pin_status = '4' 
				    and pih_codretepp = '2' 
                                    and pih_data >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." )  
                                    and pih_data >=  ('".getEndDateTrimestral($mes,$ano)." 23:59:59'::timestamp - '1 year'::interval)
                                    and pih_data <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                                    and pih_id  = ".$value." 
                        )                        
                        
                     union all

                        (
                            select 
                                    vgcbe_cpf as ug_cpf_tmp
                            from tb_venda_games_cpf_boleto_express
                                inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                                inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
			    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vgcbe_data_inclusao >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." )  
                                    and vgcbe_data_inclusao >=  ('".getEndDateTrimestral($mes,$ano)." 23:59:59'::timestamp - '1 year'::interval)
                                    and vgcbe_data_inclusao <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                                    and vgm_opr_codigo  = ".$value." 
                        )                        
                             ";
                    }//end if($rsDataIncioOperacao_row['data_inicio'] < getEndDateTrimestral(($mes-3),$ano))
                    
                }//end foreach
            } //end if(!empty($verificadorPublishersNovos))

            $sql .= " 
            )  tabelaUnion     
            ";
            //echo $sql."\n"; die();

            $rsInfoAtivos = SQLexecuteQuery($sql);
            if(!$rsInfoAtivos) echo "Erro ao selecionar o Cartões Ativos.<br>\n";
            else { 
                //Capturando Dados Cartões Emitidos
                $rsInfoAtivos_row = pg_fetch_array($rsInfoAtivos);
            }//end else do if(!$rsDesvio)
            
            // TO DO => Implementar Financiamento Rotativo
            $sql = "
            select   
                sum(montante_nacional) as montante_nacional,
                sum(quantidade_nacional) as quantidade_nacional,
                ROUND((sum(total)/(1+".$iof."/100)),2) as montante_internacional,
                sum(quantidade_internacional) as quantidade_internacional,
                sum(financiamento_rotativo) as financiamento_rotativo
            from 
            (
            ";
            $insere_union_all = 1;
            foreach ($vetorPublisher as $key => $value) {
                if($insere_union_all > 1) {
                    $sql .= "

                    union all

                    ";
                } //end if($insere_union_all > 1)

                $sql .= "
                        (
                            select 
                                   CASE WHEN fp_nationality = 0 THEN sum(fp_total) ELSE 0 END as montante_nacional,
                                   CASE WHEN fp_nationality = 0 THEN sum(fp_number) ELSE 0 END as quantidade_nacional,
                                   CASE WHEN fp_nationality = 1 THEN sum(fp_total) ELSE 0 END as total,
                                   CASE WHEN fp_nationality = 1 THEN sum(fp_number) ELSE 0 END as quantidade_internacional, 
                                   0 as financiamento_rotativo
                           from financial_processing 
                           where  fp_publisher = ".$value." 
                                   and fp_date >=  CASE 
                                                        WHEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                        THEN 
                                   ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                        ELSE 
                                   '".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp 
                                                        END
                                  and fp_date <= '".getEndDateTrimestral(($mes),$ano)." 00:00:00'
                                  and fp_freeze=1
                           GROUP BY fp_nationality
                        )

            ";
                $insere_union_all++;
                
            }//end foreach ($vetorPublisher as $key => $value)

            
            if(!empty($verificadorPublishersNovos)) {
                foreach ($vetorPublisherNovos as $key => $value) {
                    //echo "Key: $key -- value: $value <br>";
                    
                    //Levantando data para verificação se deve ser incluido a parte da querie no select principal
                    $sql_data_inicio = "select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD') as data_inicio from operadoras where opr_codigo = ".$value.";";
                    $rsDataIncioOperacao = SQLexecuteQuery($sql_data_inicio);
                    if(!$rsDataIncioOperacao) die("Erro ao selecionar a Data Iniício da Operação para o Publisher (".$value.").<br>\n");
                    else { 
                        //Capturando Dados da Início da Operação
                        $rsDataIncioOperacao_row = pg_fetch_array($rsDataIncioOperacao);
                    }//end else do if(!$rsDataIncioOperacao)

                    //die("------[".$rsDataIncioOperacao_row['data_inicio']."]<br>[".getEndDateTrimestral(($mes),$ano)."]\n");
                    if($rsDataIncioOperacao_row['data_inicio'] <= getEndDateTrimestral(($mes),$ano)) {
                        //Parte que irá unir a query principal
                        $sql .= "

                      union all

                         (
                            select 
                                   CASE WHEN fp_nationality = 0 THEN sum(fp_total) ELSE 0 END as montante_nacional,
                                   CASE WHEN fp_nationality = 0 THEN sum(fp_number) ELSE 0 END as quantidade_nacional,
                                   CASE WHEN fp_nationality = 1 THEN sum(fp_total) ELSE 0 END as total,
                                   CASE WHEN fp_nationality = 1 THEN sum(fp_number) ELSE 0 END as quantidade_internacional, 
                                   0 as financiamento_rotativo
                            from financial_processing 
                            where  fp_publisher = ".$value."
                                   and fp_date >=  ( select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                   and fp_date >=  ('".getStartDateTrimestral($mes,$ano)." 00:00:00'::timestamp) 
                                   and fp_date <= '".getEndDateTrimestral($mes,$ano)." 00:00:00'
                                   and fp_freeze=1
                           GROUP BY fp_nationality
                         )
                             ";
                    }//end if($rsDataIncioOperacao_row['data_inicio'] < getEndDateTrimestral(($mes),$ano))
                    
                }//end foreach
            } //end if(!empty($verificadorPublishersNovos))

            $sql .= " 
            )  tabelaUnion     
            ";
            
            //echo $sql."\n"; die();

            $rsInfoComplementar = SQLexecuteQuery($sql);
            if(!$rsInfoComplementar) echo "Erro ao selecionar o Publisher Dados Complementares (".implode(",", $vetorPublisher).").<br>\n";
            else { 
                //Capturando Dados Complementares
                $rsInfoComplementar_row = pg_fetch_array($rsInfoComplementar);
            }//end else do if(!$rsDesvio)


            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => $produto,
                                           'size' => 2
                                           ),
                                3 => array('name' => $modalidade,
                                           'size' => 1
                                           ),
                                4 => array('name' => $funcao,
                                           'size' => 1
                                           ),
                                5 => array('name' => $bandeira,
                                           'size' => 2
                                           ),
                                6 => array('name' => number_format($rsInfoEmitidos_row['cartoes_emitidos'], 0, '', ''),
                                           'size' => 9
                                           ),
                                7 => array('name' => number_format($rsInfoAtivos_row['cartoes_ativos'], 0, '', ''),
                                           'size' => 9
                                           ),
                                8 => array('name' => number_format(($rsInfoComplementar_row['montante_nacional']*100), 0, '', ''),
                                           'size' => 15
                                           ),
                                9 => array('name' => number_format(($rsInfoComplementar_row['montante_internacional']*100), 0, '', ''), 
                                           'size' => 15
                                           ),
                                10 => array('name' => number_format($rsInfoComplementar_row['quantidade_nacional'], 0, '', ''),
                                           'size' => 12
                                           ),
                                11 => array('name' => number_format($rsInfoComplementar_row['quantidade_internacional'], 0, '', ''),
                                           'size' => 12
                                           ),
                                12 => array('name' => number_format(($rsInfoComplementar_row['financiamento_rotativo']*100), 0, '', ''),
                                           'size' => 15
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo CONCEMIS.TXT




            //==================================  Inicio do trecho a geração do arquivo CONTATOS.TXT
            $nomeArquivo = 'contatos.txt';
            $nomeArquivosSemestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);

            $quantidadeLinhas = 8; //Devido a ser fixo somente 3 Contatos diretor responsável pela prestação das informações, de dois técnicos designados como responsáveis

            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'contatos',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $ISPB, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $quantidadeLinhas, // contador de linhas do arquivo excluindo a linha do header
                                            'size' => 8
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);

            // 1º Trimestre
            // Dados Diretor
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre-3),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'D',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'Glaucia da Costa Gregio',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'Diretora',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9105',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'glaucia@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // Dados Técnico 01
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre-3),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'T',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'Wagner de Miranda',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'Gerente de TI',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9101',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'wagner@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // Dados Técnico 02
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre-3),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'T',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'LUIS GUSTAVO DE SOUZA CARVALHO',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'ANALISTA DESENVOLVEDOR PHP',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9101',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'luis.gustavo@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // Dados Institucional
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre-3),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'I',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'Katia Godoy de Medeiros',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'ANALISTA CONTÁBIL FINANCEIRO',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9105',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'katia.medeiros@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // 2º Trimestre
            // Dados Diretor
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'D',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'Glaucia da Costa Gregio',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'Diretora',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9105',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'glaucia@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // Dados Técnico 01
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'T',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'Wagner de Miranda',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'Gerente de TI',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9101',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'wagner@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // Dados Técnico 02
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'T',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'LUIS GUSTAVO DE SOUZA CARVALHO',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'ANALISTA DESENVOLVEDOR PHP',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9101',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'luis.gustavo@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            // Dados Institucional
            unset($vetorLines);
            $vetorLines = array (
                                0 => array('name' => $ano,
                                           'size' => 4
                                            ),
                                1 => array('name' => trimestre($trimestre),
                                           'size' => 1
                                            ),
                                2 => array('name' => 'I',
                                           'size' => 1
                                           ),
                                3 => array('name' => 'Katia Godoy de Medeiros',
                                           'size' => 50
                                           ),
                                4 => array('name' => 'ANALISTA CONTÁBIL FINANCEIRO',
                                           'size' => 50
                                           ),
                                5 => array('name' => '11 3030-9105',
                                           'size' => 50
                                           ),
                                6 => array('name' => 'katia.medeiros@e-prepag.com.br',
                                           'size' => 50
                                           ),
                                );
            $file->setVetorLines($vetorLines);

            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo CONTATOS.TXT

            
            
            
            //==================================  Inicio do trecho a geração do arquivo DATABASE.TXT
            $nomeArquivo = 'database.txt';
            $nomeArquivosSemestrais[] = $nomeArquivo;
            unset($file);
            $file = new FilePosition($nomeArquivo);
            // Cabeçalho
            unset($vetorHeader);
            $vetorHeader = array (
                                 0 => array('name' => 'database',
                                            'size' => 8
                                            ),
                                 1 => array('name' => date('Ymd'),
                                            'size' => 8
                                            ),
                                 2 => array('name' => $oitoPrimeiros, // 8 primeiros digitos do CNPJ E-PREPAG ADMINISTRADORA DE CARTOES 
                                            'size' => 8
                                            ),
                                 3 => array('name' => $ano.$mes, //Data-base dos arquivos enviados (AAAAMM), correspondendo ao último mês do trimestre de referência. Por exemplo, a data-base do quarto trimestre de 2018 é 201812. 
                                            'size' => 6
                                            ),
                            );

            $file->setVetorHeader($vetorHeader);

            $file->saveFile(true);

            if($file->checkFile()){
                echo "Arquivo ".$file->getFileName()." gerado com sucesso.<br>\n";
            }
            else {
               echo "Arquivo ".$file->getFileName()." não gerado.<br>\n"; 
            }
            //==================================  Fim do trecho a geração do arquivo DATABASE.TXT



            
            
            
            //==================================  Início do trecho compactando arquivos Semestrais para serem enviados ao BACEN
            $nomeArquivoSemestralZipado = "aspb008_6308_".semestre($trimestre)."S".$ano.".zip"; //Exemplo: aspb008_6308_2S2014.zip
            $file = new FilePosition($nomeArquivoSemestralZipado); 
            $file->createZip($nomeArquivosSemestrais,true);
            echo "Arquivo Semestral Zipado Criado com Sucesso: <a href='/bacen/".date('Ymd')."/".$nomeArquivoSemestralZipado."'>".$nomeArquivoSemestralZipado."</a><br><hr><br><br>\n";
            //==================================  Fim do trecho compactando arquivos Semestrais para serem enviados ao BACEN
            
       
            
            
            
    
        }//end do if(pg_num_rows($rs_complice) == 6 || $testeData == $dataInicioOperacao)
        else {
            die("O semestre não possui Todos os mêses Necessários Cadastrados para os Dados Complementares de Complice no BackOffice.<br>Por favor, verificar no BackOffice.");
        }//end else do if(pg_num_rows($rs_complice) == 6 || $testeData == $dataInicioOperacao)

    }//end if(pg_num_rows($rs_complice) == 1)
    else {
        die("Necessários Cadastrar os Dados Complementares de Complice no BackOffice antes de continuar.");
    }//end else do if(pg_num_rows($rs_complice) == 1)
    
}//end if(isSemestral($mes))




//==================================  Início do trecho da alteração para já em arquivo do BACEN 
if(!empty($verificadorPublishersNovos)) {
    alteracaoPublisherNovosJaArquivoBACEN($vetorPublisherNovos);
}//end if(!empty($verificadorPublishersNovos))
//==================================  Fim do trecho da alteração para já em arquivo do BACEN 


require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";


/* teste do calculo do desvio padrao
$teste = array(2.5,2.8);
$desvio_padrao = 0;
foreach ($teste as $key => $value) {
    
                            $desvio_padrao += pow($value-2.62, 2);
}//end while

$desvio_padrao = sqrt($desvio_padrao/count($teste));

echo $desvio_padrao;
 */
?>  
