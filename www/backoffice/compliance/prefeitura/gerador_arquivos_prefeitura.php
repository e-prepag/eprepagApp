<?php
set_time_limit(3600);

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "includes/complice/functions.php";
require_once $raiz_do_projeto . "class/util/classFilePosition.php";

?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice -
                <?php echo $currentAba->getDescricao(); ?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<div class="clearfix"></div>
<?php

$server_url = "backoffice.e-prepag.com.br";

//=========  Mês/Ano considerado no Elaboração dos Arquivos
$currentmonth = mktime(0, 0, 0, date('n') - 1, 1, date('Y'));
$mesAno = date('m/Y', $currentmonth);

//forçando mês 06 de 2019
//$mesAno = '06/2019';

//========= Variável contendo o Ano Mês inicio das operações para efeitos de 
//========= exclusão nos teste Trimenetrais e Semestrais por não estarem completos
$dataInicioOperacao = 201407;

// Split ano/mes
list($mes, $ano) = explode("/", $mesAno);

//Publishers Já em Operação constantes em arquivos para Prefeitura anteriores
$vetorPublisher = levantamentoPublisherOperantesMunicipais($ano, $mes);

//Publishers novos nunca antes contou nos arquivos para Prefeitura
$vetorPublisherNovos = levantamentoPublisherNovosOperantesMunicipais($ano, $mes);

// Instanciando a variavel para verificação de Publishers ja informados
$verificadorPublishers = implode(",", $vetorPublisher);

// Instanciando a variavel para verificação de novos Publishers
$verificadorPublishersNovos = implode(",", $vetorPublisherNovos);

//Dados necessários
$codigoSegmento = '001';
$versaoLayout = '03';
$codigoCidadeSP_IBGE = '3550308';
$funcao = 'D';          // Função Débito Fixa
$qtdeEstabelecimentos = count($vetorPublisher); //Quantidade de estabelecimentos 
$qtdeEstabelecimentosAtivo = count($vetorPublisher); //Quantidade de estabelecimentos operando
//Adicionando os publishers novos no total de estabelecimentos
if (!empty($verificadorPublishersNovos)) {
    $qtdeEstabelecimentos += count($vetorPublisherNovos);
    $qtdeEstabelecimentosAtivo += count($vetorPublisherNovos);
} //end if(!empty($verificadorPublishersNovos))
$identificacaoRegisto10 = '10';  // Identificação do tipo de registro
$identificacaoRegisto11 = '11';  // Identificação do tipo de registro
$identificacaoRegisto65 = '65';  // Identificação do tipo de registro
$identificacaoRegisto66 = '66';  // Identificação do tipo de registro
$identificacaoRegisto90 = '90';  // Identificação do tipo de registro
$identificacaoRegistoTotalGeral = '99';  // Identificação do tipo de registro Total Geral
$numeroRegistrosTipo90 = '1';    // Número de Registros Tipo 90
$cnpjEPP = '19037276000172';     // CNPJ da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$nomeEmissor = 'E-PREPAG ADMINISTRADORA DE CARTOES LTDA'; // Nome da instituição
$inscricaoEstatual = ' ';        // Número da Inscrição Estadual
$ISPB = '19037276';              // Código ISPB cadastrado
$municipioEmissor = 'São Paulo'; // Município da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$ufEmissor = 'SP';               // Estado da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$faxEmissor = '1130309101';      // Fax da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$logradouroEmissor = 'Rua Deputado Lacerda Franco';  // Logradouro da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$numeroEmissor = '300';          // Número do endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$complementoEmissor = 'Conjunto 26A';  // Complemento do endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$bairroEmissor = 'Pinheiros';    // Bairro do endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$cepEmissor = '05418000';        // CEP do endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$telefoneEmissor = '1130309101'; // Telefone do endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
$identificacaoSUREM10 = '2';     // Código de Identificação do Instrumento Legal: IN SF/Surem nº 10/2009
$idNatureza = '4';               // Código da identificação da natureza das operações informadas
$idFinalidade = '1';             // Código da finalidade do arquivo
$idNaturezaOperacao = '2';       // Código da identificação da natureza das operações realizada => 1 = Crédito => 2 = Débito
$idTipoOperacao = '4';           // Código do Tipo de operação realizada => 1- para operação eletrônica; 2- para operação manual; 3- para POS; 4- E-Commerce; 5- para demais tecnologias. A partir de 01/05/2016 o tipo de informação ?1? não mais deverá ser utilizado, o tipo de operação deverá ser classificado nos tipos ?2, 3, 4 e 5? conforme a operação.



// IOF
$iof = 6.38; //Aliquota de IOF - usar PONTO (.) como casa decimal


//Buscando Publisher que possuem totalização por utilização
$vetorPublisherPorUtilizacao = levantamentoPublisherComFechamentoUtilizacaoMunicipal();

if (count($vetorPublisherPorUtilizacao) > 0) {
    $where_opr_venda_lan = " AND ( CASE ";
    $where_opr_venda_lan_negativa = " AND ( CASE ";
    $where_opr_utilizacao_lan = " AND ( CASE ";
    foreach ($vetorPublisherPorUtilizacao as $opr_codigo => $opr_data_inicio_contabilizacao_utilizacao) {
        //echo "ID: ".$opr_codigo." => DATA: [".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."]<br>";
        $where_opr_venda_lan .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao < '" . substr($opr_data_inicio_contabilizacao_utilizacao, 0, 19) . "' ";
        $where_opr_venda_lan_negativa .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao >= '" . substr($opr_data_inicio_contabilizacao_utilizacao, 0, 19) . "' ";
        $where_opr_utilizacao_lan .= "  WHEN pih_id = $opr_codigo THEN pih_data >= '" . substr($opr_data_inicio_contabilizacao_utilizacao, 0, 19) . "' ";
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


// Exibindo o Período de Apuração
echo "<b>Mês/Ano do período de apuração: [<span style='color: red'>" . $mesAno . "</span>]</b><br><br><br>" . PHP_EOL;


// Teste de Abortagem
$testeData = $ano . $mes;
if ($testeData < $dataInicioOperacao) {
    die("O mês ano deve ser obrigatóriamente superior a " . $dataInicioOperacao . " (AAAAMM).<br>" . PHP_EOL);
}// end if($testeData < 201403)


//================================== Inicio da Geração do Arquivo Layout SUREM10
echo "<b>Gerando Arquivo Mensal</b><br><br>" . PHP_EOL;

$contFatura = 0;        // Quantidade de regitro de faturas
$contDetalhamento = 0;  // Quantidade de regitro de detalhamento

$nomeArquivo = 'SUREM10_' . $mes . $ano . '.txt';
$nomeArquivoSUREM10[] = $nomeArquivo;

unset($file);
$file = new FilePosition($nomeArquivo);

//=========================================================================================================================
//1- REGISTRO DO TIPO 10
//=========================================================================================================================
// Cabeçalho
unset($vetorHeader);
$vetorHeader = array(
    0 => array(
        'name' => $identificacaoRegisto10,
        'size' => 2
    ),
    1 => array(
        'name' => $cnpjEPP,
        'size' => 14
    ),
    2 => array(
        'name' => $inscricaoEstatual,
        'size' => 14
    ),
    3 => array(
        'name' => $nomeEmissor,
        'size' => 33
    ),
    4 => array(
        'name' => $versaoLayout,
        'size' => 2
    ),
    5 => array(
        'name' => $municipioEmissor,
        'size' => 30
    ),
    6 => array(
        'name' => $ufEmissor,
        'size' => 2
    ),
    7 => array(
        'name' => $faxEmissor,
        'size' => 10
    ),
    8 => array(
        'name' => $ano . $mes . '01',
        'size' => 8
    ),
    9 => array(
        'name' => $ano . $mes . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)),
        'size' => 8
    ),
    10 => array(
        'name' => $identificacaoSUREM10,
        'size' => 1
    ),
    11 => array(
        'name' => $idNatureza,
        'size' => 1
    ),
    12 => array(
        'name' => $idFinalidade,
        'size' => 1
    ),
);

$file->setVetorHeader($vetorHeader);

//=========================================================================================================================
//2- REGISTRO DO TIPO 11
//=========================================================================================================================
// Dados 
unset($vetorLines);
$vetorLines = array(
    0 => array(
        'name' => $identificacaoRegisto11,
        'size' => 2
    ),
    1 => array(
        'name' => $logradouroEmissor,
        'size' => 34
    ),
    2 => array(
        'name' => $numeroEmissor,
        'size' => 5
    ),
    3 => array(
        'name' => $complementoEmissor,
        'size' => 22
    ),
    4 => array(
        'name' => $bairroEmissor,
        'size' => 15
    ),
    5 => array(
        'name' => $cepEmissor,
        'size' => 8
    ),
    6 => array(
        'name' => 'EVERTON RODRIGUES DE ALMEIDA',
        'size' => 28
    ),
    7 => array(
        'name' => $telefoneEmissor,
        'size' => 12
    ),
);

$file->setVetorLines($vetorLines);

//=========================================================================================================================
//3- REGISTRO DO TIPO 65
//=========================================================================================================================
// Buscando informações 
$sql = "select opr_cnpj, opr_ie, opr_codigo,opr_estado,opr_cidade,opr_cep, data, num_docto, sum(total) as total from ( ";
$insere_union_all = 1;
if (!empty($verificadorPublishers)) {
    $sql .= "
            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'G'||(lpad((CAST(vgm_vg_id as character varying)),8,'0')) as num_docto,
                    to_char(vg.vg_data_concilia,'YYYYMMDD') as data,
                    sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
            from tb_venda_games vg 
                    inner join tb_venda_games_modelo vgm on (vgm.vgm_vg_id = vg.vg_id)
                    inner join operadoras o on (o.opr_codigo = vgm.vgm_opr_codigo)
            where vg.vg_ultimo_status='" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "' 
                    and vg.vg_data_concilia >= '" . $ano . "-" . $mes . "-01 00:00:00'
                    and vg.vg_data_concilia <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                    and vg.vg_ug_id != '" . $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'] . "'
                    and vgm_opr_codigo IN (" . implode(",", $vetorPublisher) . ") 
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data)
            
        union all
            
            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'L'||(lpad((CAST(vgm_vg_id as character varying)),8,'0')) as num_docto,
                    to_char(vg.vg_data_inclusao,'YYYYMMDD') as data,
                    sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
            from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on (vgm.vgm_vg_id = vg.vg_id) 
                    inner join operadoras o on (o.opr_codigo = vgm.vgm_opr_codigo)
            where vg.vg_ultimo_status='" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "'  " . $where_opr_venda_lan . "
                    and vg.vg_data_inclusao >= '" . $ano . "-" . $mes . "-01 00:00:00'
                    and vg.vg_data_inclusao <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                    and vgm_opr_codigo IN (" . implode(",", $vetorPublisher) . ") 
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data) ";

    //Contabilizando vendas por utilização de PINs Publisher
    if (count($vetorPublisherPorUtilizacao) > 0) {
        $sql .= "
        
        union all

            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'L'||(lpad((CAST(vgm_vg_id as character varying)),8,'0')) as num_docto,
                    to_char(pih_data,'YYYYMMDD') as data,
                    sum(vgm.vgm_valor) as total 
            from tb_dist_venda_games vg 
                 inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                 inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
                 inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno
                 inner join operadoras o on (o.opr_codigo = vgm.vgm_opr_codigo)
            where vg.vg_ultimo_status='" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "'
                 and pin_status = '8'
                 and pih_codretepp='2'
                 " . $where_opr_venda_lan_negativa . "
                 and pih_data >= '" . $ano . "-" . $mes . "-01 00:00:00'
                 and pih_data <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                 and vgm_opr_codigo IN (" . implode(",", $vetorPublisher) . ") 
                 " . $where_opr_utilizacao_lan . "
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data) 
            ";
    }//end if(count($vetorPublisherPorUtilizacao)>0)

    $sql .= " 
            
        union all

            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'C'||(lpad((CAST(pgc_vg_id as character varying)),17,'0')) as num_docto,
                    to_char(pg.pgc_pin_response_date,'YYYYMMDD') as data,
                    CASE WHEN (select opr_product_type from operadoras inner join pins_gocash ON opr_codigo = pgc_opr_codigo limit 1) = 5 
                                    THEN sum(pgc_real_amount) 
                         WHEN (
                                (select opr_product_type from operadoras  inner join pins_gocash ON  opr_codigo = pgc_opr_codigo limit 1) = 7 
                                    OR 
                                (select opr_product_type from operadoras  inner join pins_gocash ON  opr_codigo = pgc_opr_codigo limit 1) = 4 )  
                                    THEN sum (pgc_face_amount) 
                         ELSE sum (pgc_face_amount) END as total 
            from pins_gocash pg
                inner join operadoras o ON opr_codigo = pgc_opr_codigo
            where pgc_opr_codigo != 0 
                    and pg.pgc_pin_response_date >= '" . $ano . "-" . $mes . "-01 00:00:00'
                    and pg.pgc_pin_response_date <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                    and pgc_opr_codigo IN (" . implode(",", $vetorPublisher) . ") 
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data)
            
        union all

            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'P'||(lpad((CAST(id_transacao as character varying)),12,'0')) as num_docto,
                    to_char(data_transacao,'YYYYMMDD') as data,
                    sum (valor) as total 
            from pos_transacoes_ponto_certo pc
                inner join operadoras o ON o.opr_codigo = pc.opr_codigo
            where pc.opr_codigo is not NULL 
                    and data_transacao >= '" . $ano . "-" . $mes . "-01 00:00:00'
                    and data_transacao <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                    and pc.opr_codigo IN (" . implode(",", $vetorPublisher) . ") 
            group by opr_cnpj,opr_ie,o.opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data)
            
        union all

            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'E'||(lpad((CAST(vg_id as character varying)),8,'0')) as num_docto,
                    to_char(vgcbe_data_inclusao,'YYYYMMDD') as data,
                    sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
            from tb_venda_games_cpf_boleto_express
                    inner join tb_venda_games vg ON (vg_id = vgcbe_vg_id) 
                    inner join tb_venda_games_modelo vgm on (vgm.vgm_vg_id = vg.vg_id)
                    inner join operadoras o on (o.opr_codigo = vgm.vgm_opr_codigo)
            where vg.vg_ultimo_status='" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "' 
                    and vgcbe_data_inclusao >= '" . $ano . "-" . $mes . "-01 00:00:00'
                    and vgcbe_data_inclusao <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                    and vg.vg_ug_id = '" . $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'] . "'
                    and (
                            vgcbe_cpf is not null
                            OR
                            vgcbe_nome_cpf is not null
                            OR
                            length(vgcbe_cpf) = 14 
                            OR
                            vgcbe_nome_cpf != ''
                        )
                    and vgm_opr_codigo IN (" . implode(",", $vetorPublisher) . ") 
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data)

        ";
    $insere_union_all++;
} //end if(!empty($verificadorPublishers))
if (!empty($verificadorPublishersNovos)) {
    foreach ($vetorPublisherNovos as $key => $value) {
        //echo "Key: $key -- value: $value <br>";
        if ($insere_union_all > 1) {
            $sql .= "
            
        union all

            ";
        }//end if($insere_union_all > 1)
        $sql .= "
             (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'G'||(lpad((CAST(vgm_vg_id as character varying)),8,'0')) as num_docto, 
                    to_char(vg.vg_data_concilia,'YYYYMMDD') as data,
                    (sum(vgm.vgm_valor * vgm.vgm_qtde)) as total 
            from tb_venda_games vg 
                    inner join tb_venda_games_modelo vgm on (vgm.vgm_vg_id = vg.vg_id) 
                    inner join operadoras o on (o.opr_codigo = vgm.vgm_opr_codigo)
            where vg.vg_ultimo_status='" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "' 
                    and vg.vg_data_concilia >= '" . $ano . "-" . $mes . "-01 00:00:00'
                    and vg.vg_data_concilia <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                    and vg.vg_ug_id != '" . $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'] . "'
                    and vgm_opr_codigo = " . $value . " 
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data)

        union all

            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'L'||(lpad((CAST(vgm_vg_id as character varying)),8,'0')) as num_docto, 
                    to_char(vg.vg_data_inclusao,'YYYYMMDD') as data,
                    (sum(vgm.vgm_valor * vgm.vgm_qtde)) as total 
            from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on (vgm.vgm_vg_id = vg.vg_id) 
                    inner join operadoras o on (o.opr_codigo = vgm.vgm_opr_codigo)
            where vg.vg_ultimo_status='" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "' ";
        if (array_key_exists($value, $vetorPublisherPorUtilizacao)) {
            $sql .= "
                    and vg.vg_data_inclusao < '" . substr($vetorPublisherPorUtilizacao[$value], 0, 19) . "' ";
        }
        $sql .= "
                    and vg.vg_data_inclusao >= '" . $ano . "-" . $mes . "-01 00:00:00'
                    and vg.vg_data_inclusao <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                    and vgm_opr_codigo = " . $value . " 
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data) ";

        //Contabilizando vendas por utilização de PINs Publisher
        if (array_key_exists($value, $vetorPublisherPorUtilizacao)) {
            $sql .= "
        
        union all

            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'L'||(lpad((CAST(vgm_vg_id as character varying)),8,'0')) as num_docto,
                    to_char(pih_data,'YYYYMMDD') as data,
                    sum(vgm.vgm_valor) as total 
            from tb_dist_venda_games vg 
                 inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                 inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
                 inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno
                 inner join operadoras o on (o.opr_codigo = vgm.vgm_opr_codigo)
            where vg.vg_ultimo_status='" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "'
                 and pin_status = '8'
                 and pih_codretepp='2'
                 and vg.vg_data_inclusao >= '" . substr($vetorPublisherPorUtilizacao[$value], 0, 19) . "'
                 and pih_data >= '" . $ano . "-" . $mes . "-01 00:00:00'
                 and pih_data <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                 and vgm_opr_codigo = " . $value . " 
                 and pih_data >= '" . substr($vetorPublisherPorUtilizacao[$value], 0, 19) . "'
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data) 
                    ";
        }//end if (array_key_exists($value, $vetorPublisherPorUtilizacao)) 

        $sql .= " 

        union all

            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'C'||(lpad((CAST(pgc_vg_id as character varying)),17,'0')) as num_docto,
                    to_char(pg.pgc_pin_response_date,'YYYYMMDD') as data,
                    CASE WHEN (select opr_product_type from operadoras inner join pins_gocash ON opr_codigo = pgc_opr_codigo limit 1) = 5 
                                    THEN sum(pgc_real_amount) 
                         WHEN (
                                (select opr_product_type from operadoras  inner join pins_gocash ON  opr_codigo = pgc_opr_codigo limit 1) = 7 
                                    OR 
                                (select opr_product_type from operadoras  inner join pins_gocash ON  opr_codigo = pgc_opr_codigo limit 1) = 4 )  
                                    THEN sum (pgc_face_amount) 
                         ELSE sum (pgc_face_amount) END as total 
            from pins_gocash pg
                inner join operadoras o ON opr_codigo = pgc_opr_codigo
            where pgc_opr_codigo != 0 
                    and pg.pgc_pin_response_date >= '" . $ano . "-" . $mes . "-01 00:00:00'
                    and pg.pgc_pin_response_date <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                    and pgc_opr_codigo = " . $value . "  
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data)
           
        union all

            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'P'||(lpad((CAST(id_transacao as character varying)),12,'0')) as num_docto,
                    to_char(data_transacao,'YYYYMMDD') as data,
                    sum (valor) as total 
            from pos_transacoes_ponto_certo pc
                inner join operadoras o ON o.opr_codigo = pc.opr_codigo
            where pc.opr_codigo is not NULL 
                    and data_transacao >= '" . $ano . "-" . $mes . "-01 00:00:00'
                    and data_transacao <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                    and pc.opr_codigo = " . $value . " 
            group by opr_cnpj,opr_ie,o.opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data)
           
        union all

            (select 
                    o.opr_cnpj, 
                    o.opr_ie, 
                    o.opr_codigo,
                    o.opr_cep,
                    o.opr_cidade,
                    o.opr_estado, 
                    'E'||(lpad((CAST(vg_id as character varying)),8,'0')) as num_docto,
                    to_char(vgcbe_data_inclusao,'YYYYMMDD') as data,
                    sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
            from tb_venda_games_cpf_boleto_express
                    inner join tb_venda_games vg ON (vg_id = vgcbe_vg_id) 
                    inner join tb_venda_games_modelo vgm on (vgm.vgm_vg_id = vg.vg_id)
                    inner join operadoras o on (o.opr_codigo = vgm.vgm_opr_codigo)
            where vg.vg_ultimo_status='" . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "' 
                    and vgcbe_data_inclusao >= '" . $ano . "-" . $mes . "-01 00:00:00'
                    and vgcbe_data_inclusao <= '" . $ano . "-" . $mes . "-" . date("t", mktime(0, 0, 0, ($mes * 1), 1, $ano)) . " 23:59:59'
                    and vg.vg_ug_id = '" . $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'] . "'
                    and (
                            vgcbe_cpf is not null
                            OR
                            vgcbe_nome_cpf is not null
                            OR
                            length(vgcbe_cpf) = 14 
                            OR
                            vgcbe_nome_cpf != ''
                        )
                    and vgm_opr_codigo = " . $value . "  
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data)

                 ";
        $insere_union_all++;
    }//end foreach
} //end if(!empty($verificadorPublishersNovos))
$sql .= "
            
) tabelaUnion 
            group by opr_cnpj,opr_ie,opr_codigo,opr_estado,opr_cidade,opr_cep,num_docto,data 
            order by opr_cnpj,opr_ie,data,num_docto,total;
    ";

//echo $sql."".PHP_EOL; die();

//echo $sql;

$rs = SQLexecuteQuery($sql);

// if ($rs) {
//     if (pg_num_rows($rs) > 0) {
//         echo "<table border='1'>";
//         echo "<tr><th>Coluna 1</th><th>Coluna 2</th></tr>"; // Ajuste os cabeçalhos

//         while ($row = pg_fetch_assoc($rs)) {
//             echo "<tr>";
//             foreach ($row as $value) {
//                 echo "<td>" . htmlspecialchars($value) . "</td>"; // Evita problemas de segurança
//             }
//             echo "</tr>";
//         }
//         echo "</table>";
//     } else {
//         echo "Nenhum registro encontrado.";
//     }
// } else {
//     echo "Erro na consulta: " . pg_last_error($GLOBALS['connid']);
// }

if (!$rs)
    echo "Erro ao selecionar os Detalhamento para os Publishers (" . implode(",", $vetorPublisher) . ").<br>" . PHP_EOL;
else {
    $contFatura = pg_num_rows($rs);
    $arrayTipo66 = array();
    $cnpjAnterior = "";
    if (pg_num_rows($rs) > 0) {
        echo "Com registros";
        while ($rs_row = pg_fetch_assoc($rs)) {
            // Carregando vetor para os registros Tipo 66
            $arrayTipo66[str_replace("-", "", str_replace(".", "", trim($rs_row['opr_cnpj'])))][(int) str_replace("-", "", str_replace(".", "", trim($rs_row['opr_ie'])))]['TOTAL_DEBITO'] += number_format(($rs_row['total'] * 100), 0, '', '');

            if (!empty($cnpjAnterior) && $cnpjAnterior != str_replace("-", "", str_replace(".", "", trim($rs_row['opr_cnpj'])))) {

                //=========================================================================================================================
                //4- REGISTRO DO TIPO 66
                //=========================================================================================================================
                echo "CNPJ: " . $cnpjAnterior . " IE: " . key($arrayTipo66[$cnpjAnterior]) . " Total: R$ " . number_format($arrayTipo66[$cnpjAnterior][key($arrayTipo66[$cnpjAnterior])]['TOTAL_DEBITO'] / 100, 2, ",", ".") . "<br>";
                // Dados 
                unset($vetorLines);
                $vetorLines = array(
                    0 => array(
                        'name' => $identificacaoRegisto66,
                        'size' => 2
                    ),
                    1 => array(
                        'name' => $cnpjAnterior,
                        'size' => 14
                    ),
                    2 => array(
                        'name' => (key($arrayTipo66[$cnpjAnterior]) == 0 ? ' ' : key($arrayTipo66[$cnpjAnterior])),
                        'size' => 14
                    ),
                    3 => array(
                        'name' => $ano . $mes,
                        'size' => 6,
                    ),
                    4 => array(
                        'name' => 0,
                        'size' => 18
                    ),
                    5 => array(
                        'name' => $arrayTipo66[$cnpjAnterior][key($arrayTipo66[$cnpjAnterior])]['TOTAL_DEBITO'],
                        'size' => 18
                    ),
                    6 => array(
                        'name' => ' ',
                        'size' => 54
                    ),
                );
                $file->setVetorLines($vetorLines);


            } // end if(!empty($cnpjAnterior) && $cnpjAnterior != str_replace("-","",str_replace(".", "", trim($rs_row['opr_cnpj']))))

            //Capturando CNPJ anterior
            $cnpjAnterior = str_replace("-", "", str_replace(".", "", trim($rs_row['opr_cnpj'])));

            // Dados 
            unset($vetorLines);
            $vetorLines = array(
                0 => array(
                    'name' => $identificacaoRegisto65,
                    'size' => 2
                ),
                1 => array(
                    'name' => str_replace("-", "", str_replace(".", "", trim($rs_row['opr_cnpj']))),
                    'size' => 14
                ),
                2 => array(
                    'name' => str_replace("-", "", str_replace(".", "", trim($rs_row['opr_ie']))),
                    'size' => 14
                ),
                3 => array(
                    'name' => $rs_row['data'],
                    'size' => 8,
                    //                                       'upper'=> 'nao'
                ),
                4 => array(
                    'name' => $rs_row['num_docto'],
                    'size' => 18
                ),
                5 => array(
                    'name' => $idNaturezaOperacao,
                    'size' => 1
                ),
                6 => array(
                    'name' => $idTipoOperacao,
                    'size' => 1
                ),
                7 => array(
                    'name' => number_format(($rs_row['total'] * 100), 0, '', ''),
                    'size' => 13
                ),
                8 => array(
                    'name' => 0,
                    'size' => 2
                ),
                9 => array(
                    'name' => 0,
                    'size' => 10
                ),
                10 => array(
                    'name' => number_format($rs_row['opr_cep'], 0, '', ''),
                    'size' => 8
                ),
                11 => array(
                    'name' => ' ' . $rs_row['opr_codigo'],
                    'size' => 8
                ),
                12 => array(
                    'name' => ' ',
                    'size' => 4
                ),
                13 => array(
                    'name' => $rs_row['opr_estado'],
                    'size' => 2
                ),
                14 => array(
                    'name' => $codigoCidadeSP_IBGE,
                    'size' => 7
                ),
                15 => array(
                    'name' => ' ',
                    'size' => 14
                ),
            );
            $file->setVetorLines($vetorLines);
        } // end While
    } else {
        echo "Sem registros";
        $contFatura = 0;
        $arrayTipo66 = array();
        $cnpjAnterior = "";

        // Carregando vetor para os registros Tipo 66
        $arrayTipo66[str_replace("-", "", str_replace(".", "", trim("0")))][(int) str_replace("-", "", str_replace(".", "", trim("0")))]['TOTAL_DEBITO'] += number_format(0, 0, '', '');

        if (!empty($cnpjAnterior) && $cnpjAnterior != str_replace("-", "", str_replace(".", "", trim("0")))) {

            //=========================================================================================================================
            //4- REGISTRO DO TIPO 66
            //=========================================================================================================================
            echo "CNPJ: " . $cnpjAnterior . " IE: " . key($arrayTipo66[$cnpjAnterior]) . " Total: R$ " . number_format(0, 2, ",", ".") . "<br>";
            // Dados 
            unset($vetorLines);
            $vetorLines = array(
                0 => array(
                    'name' => $identificacaoRegisto66,
                    'size' => 2
                ),
                1 => array(
                    'name' => $cnpjAnterior,
                    'size' => 14
                ),
                2 => array(
                    'name' => (key($arrayTipo66[$cnpjAnterior]) == 0 ? ' ' : key($arrayTipo66[$cnpjAnterior])),
                    'size' => 14
                ),
                3 => array(
                    'name' => $ano . $mes,
                    'size' => 6,
                ),
                4 => array(
                    'name' => 0,
                    'size' => 18
                ),
                5 => array(
                    'name' => $arrayTipo66[$cnpjAnterior][key($arrayTipo66[$cnpjAnterior])]['TOTAL_DEBITO'],
                    'size' => 18
                ),
                6 => array(
                    'name' => ' ',
                    'size' => 54
                ),
            );
            $file->setVetorLines($vetorLines);


        } // end if(!empty($cnpjAnterior) && $cnpjAnterior != str_replace("-","",str_replace(".", "", trim($rs_row['opr_cnpj']))))

        //Capturando CNPJ anterior
        $cnpjAnterior = str_replace("-", "", str_replace(".", "", trim("0")));

        // Dados 
        unset($vetorLines);
        $vetorLines = array(
            0 => array(
                'name' => $identificacaoRegisto65,
                'size' => 2
            ),
            1 => array(
                'name' => str_replace("-", "", str_replace(".", "", trim("0"))),
                'size' => 14
            ),
            2 => array(
                'name' => str_replace("-", "", str_replace(".", "", trim("0"))),
                'size' => 14
            ),
            3 => array(
                'name' =>  date('Ymd'),
                'size' => 8,
                //                                       'upper'=> 'nao'
            ),
            4 => array(
                'name' => "0",
                'size' => 18
            ),
            5 => array(
                'name' => $idNaturezaOperacao,
                'size' => 1
            ),
            6 => array(
                'name' => $idTipoOperacao,
                'size' => 1
            ),
            7 => array(
                'name' => number_format((0), 0, '', ''),
                'size' => 13
            ),
            8 => array(
                'name' => 0,
                'size' => 2
            ),
            9 => array(
                'name' => 0,
                'size' => 10
            ),
            10 => array(
                'name' => number_format(0, 0, '', ''),
                'size' => 8
            ),
            11 => array(
                'name' => ' ' . 0,
                'size' => 8
            ),
            12 => array(
                'name' => ' ',
                'size' => 4
            ),
            13 => array(
                'name' => 0,
                'size' => 2
            ),
            14 => array(
                'name' => $codigoCidadeSP_IBGE,
                'size' => 7
            ),
            15 => array(
                'name' => ' ',
                'size' => 14
            ),
        );
        $file->setVetorLines($vetorLines);
    }

    //=========================================================================================================================
    //4- REGISTRO DO TIPO 66 ==> Ultimo registro após o fim do While
    //=========================================================================================================================
    echo "CNPJ: " . $cnpjAnterior . " IE: " . key($arrayTipo66[$cnpjAnterior]) . " Total: R$ " . number_format($arrayTipo66[$cnpjAnterior][key($arrayTipo66[$cnpjAnterior])]['TOTAL_DEBITO'] / 100, 2, ",", ".") . "<br>";
    // Dados 
    unset($vetorLines);
    $vetorLines = array(
        0 => array(
            'name' => $identificacaoRegisto66,
            'size' => 2
        ),
        1 => array(
            'name' => $cnpjAnterior,
            'size' => 14
        ),
        2 => array(
            'name' => (key($arrayTipo66[$cnpjAnterior]) == 0 ? ' ' : key($arrayTipo66[$cnpjAnterior])),
            'size' => 14
        ),
        3 => array(
            'name' => $ano . $mes,
            'size' => 6,
        ),
        4 => array(
            'name' => 0,
            'size' => 18
        ),
        5 => array(
            'name' => $arrayTipo66[$cnpjAnterior][key($arrayTipo66[$cnpjAnterior])]['TOTAL_DEBITO'],
            'size' => 18
        ),
        6 => array(
            'name' => ' ',
            'size' => 54
        ),
    );
    $file->setVetorLines($vetorLines);

}//end else do if(!$rs)


//=========================================================================================================================
//5- REGISTRO DO TIPO 90
//=========================================================================================================================
if (!isset($arrayTipo66))
    $arrayTipo66 = array();
//Verificando a quantidade de registro do tipo 66
$contFatura66 = count($arrayTipo66);
//echo "<pre>".print_r($arrayTipo66,true)."</pre>";

unset($vetorLines);
$vetorLines = array(
    0 => array(
        'name' => $identificacaoRegisto90,
        'size' => 2
    ),
    1 => array(
        'name' => $cnpjEPP,
        'size' => 14
    ),
    2 => array(
        'name' => $inscricaoEstatual,
        'size' => 14
    ),
    3 => array(
        'name' => $identificacaoRegisto65,
        'size' => 2
    ),
    4 => array(
        'name' => $contFatura,
        'size' => 12
    ),
    5 => array(
        'name' => $identificacaoRegisto66,
        'size' => 2
    ),
    6 => array(
        'name' => $contFatura66,
        'size' => 12
    ),
    7 => array(
        'name' => $identificacaoRegistoTotalGeral,
        'size' => 2
    ),
    8 => array(
        'name' => ($contFatura + $contFatura66 + 3),
        'size' => 12
    ),
    9 => array(
        'name' => ' ',
        'size' => 53
    ),
    10 => array(
        'name' => $numeroRegistrosTipo90,
        'size' => 1
    ),
);
$file->setVetorLines($vetorLines);

// echo "<h3>Vetor Header:</h3>";
// echo "<pre>";
// print_r($filePosition->getVetorHeader());
// echo "</pre>";

// echo "<h3>Vetor Lines:</h3>";
// echo "<pre>";
// print_r($filePosition->getVetorLines());
// echo "</pre>";
//echo $sql;

$file->saveFile(true, true);

if ($file->checkFile()) {
    echo "<hr>Arquivo " . $file->getFileName() . " gerado com sucesso.<br>" . PHP_EOL;
} else {
    echo "<hr>Arquivo " . $file->getFileName() . " não gerado.<br>" . PHP_EOL;
}

//================================== Fim da Geração do Arquivo Layout SUREM10





//==================================  Início do trecho compactando arquivos Semestrais para serem enviados para Prefeitura
$nomeArquivoSUREM10Zipado = "SUREM10_" . $mes . $ano . ".zip"; //Exemplo: SUREM10_122014.zip
@$file = new FilePosition($nomeArquivoSUREM10Zipado);
@$file->createZip($nomeArquivoSUREM10, true);
echo "Arquivo Mensal Zipado Criado com Sucesso: <a href='/bacen/" . date('Ymd') . "/" . strtolower($nomeArquivoSUREM10Zipado) . "' download>" . $nomeArquivoSUREM10Zipado . "</a><br><hr><br><br>" . PHP_EOL;
//==================================  Fim do trecho compactando arquivos Semestrais para serem enviados para Prefeitura

//==================================  Início do trecho da alteração para já em arquivo para Prefeitura 
if (!empty($verificadorPublishersNovos)) {
    alteracaoPublisherNovosJaArquivoMunicipais($vetorPublisherNovos);
}//end if(!empty($verificadorPublishersNovos))
//==================================  Fim do trecho da alteração para já em arquivo para Prefeitura 


//==================================  Início do trecho da Geração do PDF do Fommulário
?>
<script language="javascript">
    window.open("/compliance/prefeitura/gerador_pdf_municipal.php?nomeArq=<?php echo $nomeArquivoSUREM10Zipado; ?>");
</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>