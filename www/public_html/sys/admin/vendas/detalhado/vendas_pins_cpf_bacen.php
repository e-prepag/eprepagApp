<?php
//session_start();
//if ($GLOBALS['_SESSION']['userlogin_bko']!="WAGNER") die("Programa em Manutencao!<br>Aguarde a liberacao!");


ob_start(NULL, 10240000);

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
ini_set('memory_limit','512M');
set_time_limit (60000) ;

require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";

//if(!b_is_AdminPlus()) die("<center><div align='left' style='margin-left:auto;margin-right:auto;width:850px;background-color:#CCCCCC;padding-left:20px;'><br><b>".$GLOBALS['_SESSION']['userlogin_bko'].",<br><br>Por favor, aguarde a finalização dos ajuste para tentarmos contornar o problema de estouro memória para tentar executar este relatório.<br><br>Conto com sua compreensão,<br>Wagner</b><br><br></div></center>");

$vetor_SIGLA_PAIS = array(
"Afghanistan" => "AF",
"Albania" => "AL",
"Algeria" => "DZ",
"American Samoa" => "AS",
"Andorra" => "AD",
"Angola" => "AO",
"Anguilla" => "AI",
"Antarctica" => "AQ",
"Antigua and Barbuda" => "AG",
"Argentina" => "AR",
"Armenia" => "AM",
"Aruba" => "AW",
"Australia" => "AU",
"Austria" => "AT",
"Azerbaijan" => "AZ",
"Bahamas (the)" => "BS",
"Bahrain" => "BH",
"Bangladesh" => "BD",
"Barbados" => "BB",
"Belarus" => "BY",
"Belgium" => "BE",
"Belize" => "BZ",
"Benin" => "BJ",
"Bermuda" => "BM",
"Bhutan" => "BT",
"Bolivia (Plurinational State of)" => "BO",
"Bonaire, Sint Eustatius and Saba" => "BQ",
"Bosnia and Herzegovina" => "BA",
"Botswana" => "BW",
"Bouvet Island" => "BV",
"Brazil" => "BR",
"British Indian Ocean Territory (the)" => "IO",
"Brunei Darussalam" => "BN",
"Bulgaria" => "BG",
"Burkina Faso" => "BF",
"Burundi" => "BI",
"Cabo Verde" => "CV",
"Cambodia" => "KH",
"Cameroon" => "CM",
"Canada" => "CA",
"Cayman Islands (the)" => "KY",
"Central African Republic (the)" => "CF",
"Chad" => "TD",
"Chile" => "CL",
"China" => "CN",
"Christmas Island" => "CX",
"Cocos (Keeling) Islands (the)" => "CC",
"Colombia" => "CO",
"Comoros (the)" => "KM",
"Congo (the Democratic Republic of the)" => "CD",
"Congo (the)" => "CG",
"Cook Islands (the)" => "CK",
"Costa Rica" => "CR",
"Croatia" => "HR",
"Cuba" => "CU",
"Curaçao" => "CW",
"Cyprus" => "CY",
"Czechia" => "CZ",
"Côte d'Ivoire" => "CI",
"Denmark" => "DK",
"Djibouti" => "DJ",
"Dominica" => "DM",
"Dominican Republic (the)" => "DO",
"Ecuador" => "EC",
"Egypt" => "EG",
"El Salvador" => "SV",
"Equatorial Guinea" => "GQ",
"Eritrea" => "ER",
"Estonia" => "EE",
"Eswatini" => "SZ",
"Ethiopia" => "ET",
"Falkland Islands (the) [Malvinas]" => "FK",
"Faroe Islands (the)" => "FO",
"Fiji" => "FJ",
"Finland" => "FI",
"France" => "FR",
"French Guiana" => "GF",
"French Polynesia" => "PF",
"French Southern Territories (the)" => "TF",
"Gabon" => "GA",
"Gambia (the)" => "GM",
"Georgia" => "GE",
"Germany" => "DE",
"Ghana" => "GH",
"Gibraltar" => "GI",
"Greece" => "GR",
"Greenland" => "GL",
"Grenada" => "GD",
"Guadeloupe" => "GP",
"Guam" => "GU",
"Guatemala" => "GT",
"Guernsey" => "GG",
"Guinea" => "GN",
"Guinea-Bissau" => "GW",
"Guyana" => "GY",
"Haiti" => "HT",
"Heard Island and McDonald Islands" => "HM",
"Holy See (the)" => "VA",
"Honduras" => "HN",
"Hong Kong" => "HK",
"Hungary" => "HU",
"Iceland" => "IS",
"India" => "IN",
"Indonesia" => "ID",
"Iran (Islamic Republic of)" => "IR",
"Iraq" => "IQ",
"Ireland" => "IE",
"Isle of Man" => "IM",
"Israel" => "IL",
"Italy" => "IT",
"Jamaica" => "JM",
"Japan" => "JP",
"Jersey" => "JE",
"Jordan" => "JO",
"Kazakhstan" => "KZ",
"Kenya" => "KE",
"Kiribati" => "KI",
"Korea (the Democratic People's Republic of)" => "KP",
"Korea (the Republic of)" => "KR",
"Kuwait" => "KW",
"Kyrgyzstan" => "KG",
"Lao People's Democratic Republic (the)" => "LA",
"Latvia" => "LV",
"Lebanon" => "LB",
"Lesotho" => "LS",
"Liberia" => "LR",
"Libya" => "LY",
"Liechtenstein" => "LI",
"Lithuania" => "LT",
"Luxembourg" => "LU",
"Macao" => "MO",
"Madagascar" => "MG",
"Malawi" => "MW",
"Malaysia" => "MY",
"Maldives" => "MV",
"Mali" => "ML",
"Malta" => "MT",
"Marshall Islands (the)" => "MH",
"Martinique" => "MQ",
"Mauritania" => "MR",
"Mauritius" => "MU",
"Mayotte" => "YT",
"Mexico" => "MX",
"Micronesia (Federated States of)" => "FM",
"Moldova (the Republic of)" => "MD",
"Monaco" => "MC",
"Mongolia" => "MN",
"Montenegro" => "ME",
"Montserrat" => "MS",
"Morocco" => "MA",
"Mozambique" => "MZ",
"Myanmar" => "MM",
"Namibia" => "NA",
"Nauru" => "NR",
"Nepal" => "NP",
"Netherlands (the)" => "NL",
"New Caledonia" => "NC",
"New Zealand" => "NZ",
"Nicaragua" => "NI",
"Niger (the)" => "NE",
"Nigeria" => "NG",
"Niue" => "NU",
"Norfolk Island" => "NF",
"Northern Mariana Islands (the)" => "MP",
"Norway" => "NO",
"Oman" => "OM",
"Pakistan" => "PK",
"Palau" => "PW",
"Palestine, State of" => "PS",
"Panama" => "PA",
"Papua New Guinea" => "PG",
"Paraguay" => "PY",
"Peru" => "PE",
"Philippines (the)" => "PH",
"Pitcairn" => "PN",
"Poland" => "PL",
"Portugal" => "PT",
"Puerto Rico" => "PR",
"Qatar" => "QA",
"Republic of North Macedonia" => "MK",
"Romania" => "RO",
"Russian Federation (the)" => "RU",
"Rwanda" => "RW",
"Réunion" => "RE",
"Saint Barthélemy" => "BL",
"Saint Helena, Ascension and Tristan da Cunha" => "SH",
"Saint Kitts and Nevis" => "KN",
"Saint Lucia" => "LC",
"Saint Martin (French part)" => "MF",
"Saint Pierre and Miquelon" => "PM",
"Saint Vincent and the Grenadines" => "VC",
"Samoa" => "WS",
"San Marino" => "SM",
"Sao Tome and Principe" => "ST",
"Saudi Arabia" => "SA",
"Senegal" => "SN",
"Serbia" => "RS",
"Seychelles" => "SC",
"Sierra Leone" => "SL",
"Singapore" => "SG",
"Sint Maarten (Dutch part)" => "SX",
"Slovakia" => "SK",
"Slovenia" => "SI",
"Solomon Islands" => "SB",
"Somalia" => "SO",
"South Africa" => "ZA",
"South Georgia and the South Sandwich Islands" => "GS",
"South Sudan" => "SS",
"Spain" => "ES",
"Sri Lanka" => "LK",
"Sudan (the)" => "SD",
"Suriname" => "SR",
"Svalbard and Jan Mayen" => "SJ",
"Sweden" => "SE",
"Switzerland" => "CH",
"Syrian Arab Republic" => "SY",
"Taiwan (Province of China)" => "TW",
"Tajikistan" => "TJ",
"Tanzania, United Republic of" => "TZ",
"Thailand" => "TH",
"Timor-Leste" => "TL",
"Togo" => "TG",
"Tokelau" => "TK",
"Tonga" => "TO",
"Trinidad and Tobago" => "TT",
"Tunisia" => "TN",
"Turkey" => "TR",
"Turkmenistan" => "TM",
"Turks and Caicos Islands (the)" => "TC",
"Tuvalu" => "TV",
"Uganda" => "UG",
"Ukraine" => "UA",
"United Arab Emirates (the)" => "AE",
"United Kingdom of Great Britain and Northern Ireland (the)" => "GB",
"United States Minor Outlying Islands (the)" => "UM",
"United States of America (the)" => "US",
"Uruguay" => "UY",
"Uzbekistan" => "UZ",
"Vanuatu" => "VU",
"Venezuela (Bolivarian Republic of)" => "VE",
"Viet Nam" => "VN",
"Virgin Islands (British)" => "VG",
"Virgin Islands (U.S.)" => "VI",
"Wallis and Futuna" => "WF",
"Western Sahara" => "EH",
"Yemen" => "YE",
"Zambia" => "ZM",
"Zimbabwe" => "ZW",
"Åland Islands" => "AX"    
);

define("AGENTE_DE_COBRO", "E-PREPAG");

define("CNPJ_AGENTE_DO_COBRO", "08221305000135");
define("CNPJ_AGENTE_DO_COBRO_ADM", "19037276000172"); 

define("TIPO_DE_CLIENTE_NO_BRASIL", "F");

// Moeda considerada no relatório
define("REMESSA_MOEDA", "USD");

// Moeda considerada no relatório
define("REMESSA_MOEDA_EURO", "EUR");

define("TIPO_DE_TRASACAO", "2");

//Esse ID é concatenado no inicio de cada id da operação('id_venda' => 'id_op') para diferenciar o tipo de venda que foi feito
$ARRAY_CONCATENA_ID_VENDA = array
                                    (
                                        'gamer'          => '10',
                                        'pdv'            => '20',
                                        'cards'          => '30',
                                        'boleto_express' => '40'
                                    );

$cotacao_dolar = null;

//Declarando valor IOF 6.38 ou 0.38
$iof = array(6.38,0.38);

$time_start = getmicrotime();

$b_debug = false;
if ($_SESSION["tipo_acesso_pub"]=='AT') {
    if(!$flist_vg_id) {
	$flist_vg_id = false;
    }//end if(!$flist_vg_id)
}

if(!$dd_opr_codigo) $dd_opr_codigo = '';
if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');

if(b_is_Publisher()) {
        $dd_opr_codigo = $_SESSION["opr_codigo_pub"];
}

// Levanta lista de operadoras
$sql  = "select opr_codigo, opr_nome, opr_pais, opr_razao, opr_internacional_alicota, opr_vinculo_empresa, opr_cotacao_dolar from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
$resopr = pg_exec($connid,$sql);

if($BtnSearch) {
    
        $data_inic = formata_data(trim($tf_data_inicial), 1);
        $data_fim = formata_data(trim($tf_data_final), 1); 
        
        $teste_data_inic = substr($data_inic,5,2);
        $teste_data_fim = substr($data_fim,5,2);
        if($teste_data_inic <> $teste_data_fim) 
            die('<div class="container espacamento text-center bg-branco"><div class="row txt-azul-claro"><strong><h4>Para captura correta da taxa do dolar você deve pesquisar dentro do mesmo mês!</h4></strong></div><div class="row">Mêses considerados nesta pesquisa: Inicio ['.$teste_data_inic.'] e Final ['.$teste_data_fim.']!</div></div>');

        if($dd_opr_codigo) {
            // Buscando informações 
            $sql = "SELECT 
                            opr_codigo, 
                            opr_data_inicio_contabilizacao_utilizacao
                    FROM operadoras
                    WHERE 
                            opr_contabiliza_utilizacao != 0
                            AND opr_codigo = ".$dd_opr_codigo.";";

            //echo $sql.PHP_EOL; die();
            $rs_publisher = SQLexecuteQuery($sql);
            //echo pg_num_rows($rs_publisher)."<br>";
            if(!$rs_publisher) {
                echo "Erro na Query de Levantamento de Publishers contendo Fechamento por Utilização de PINs(".$sql.").<br>".PHP_EOL;
                $possui_totalizacao_utilizacao = FALSE;
            }elseif(pg_num_rows($rs_publisher) > 0) {
                $possui_totalizacao_utilizacao = TRUE;
            }//end if(pg_num_rows($rs_publisher) == 0)
            else {
                $possui_totalizacao_utilizacao = FALSE;
            }//end else
            
        }//end if($dd_opr_codigo) 
        else $possui_totalizacao_utilizacao = FALSE;
        
        //Buscando PINs na banco de dados
        $sql = "
                select 
                    tipo,
                    forma_pagamento,
                    vg_canal,
                    id,
                    ug_cpf, 
                    ug_nome,
                    data,
                    valor_total
                from ( 
                ";
        
        //SQL para PINs
        if($dd_canal != "CARD") {
            $sql .= "
                        ( select 
                                'gamer' as tipo,
                                CASE WHEN (vg.vg_pagto_tipo = ".$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC." OR vg.vg_pagto_tipo = ".$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']." OR vg.vg_pagto_tipo = ".$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'].") THEN 2 ELSE 1 END  as forma_pagamento,
                                'M' as vg_canal,
                                vg.vg_id as id,
                                ug_cpf, 
                                ug_nome_cpf as ug_nome,
                                vg_data_concilia as data,
                                sum((CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END) * vgm.vgm_qtde) as valor_total 
                        from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                        where vg.vg_ultimo_status='5' 
                                and vg_ug_id != 7909
                                and vg.vg_pagto_tipo != ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
                                and vg.vg_data_concilia >= '".trim($data_inic)." 00:00:00'
                                and vg.vg_data_concilia <= '".trim($data_fim)." 23:59:59' ";               
            if($dd_opr_codigo) {
                $sql .= " 
                                and (vgm_opr_codigo=".$dd_opr_codigo.")  ".PHP_EOL;
            }
            $sql .= "
                        group by id,tipo,ug_cpf, ug_nome_cpf, vg_data_concilia, forma_pagamento )

                    union all

                        ( select 
                                'gamer' as tipo,
                                1 as forma_pagamento,
                                'P' as vg_canal,
                                vg.vg_id as id,
                                ug_cpf, 
                                ug_nome_cpf as ug_nome,
                                vg_data_concilia as data,
                                sum((CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END) * vgm.vgm_qtde) as valor_total 
                        from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                		inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
                                inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                        where vg.vg_ultimo_status='5' 
                                and vg_ug_id != 7909
                                and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
                		and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' 
                                and vg.vg_data_concilia >= '".trim($data_inic)." 00:00:00'
                                and vg.vg_data_concilia <= '".trim($data_fim)." 23:59:59' ";               
            if($dd_opr_codigo) {
                $sql .= " 
                                and (vgm_opr_codigo=".$dd_opr_codigo.")  ".PHP_EOL;
            }
            $sql .= "
                        group by id,tipo,ug_cpf, ug_nome_cpf, vg_data_concilia, forma_pagamento )

                    union all

                        ( select 
                                'gamer' as tipo,
                                1 as forma_pagamento,
                                'C' as vg_canal,
                                vg.vg_id as id,
                                ug_cpf, 
                                ug_nome_cpf as ug_nome,
                                vg_data_concilia as data,
                                sum((CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END) * vgm.vgm_qtde) as valor_total 
                        from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                		inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
                                inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                        where vg.vg_ultimo_status='5' 
                                and vg_ug_id != 7909
                                and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
                		and tvgpo.tvgpo_canal='C' 
                                and vg.vg_data_concilia >= '".trim($data_inic)." 00:00:00'
                                and vg.vg_data_concilia <= '".trim($data_fim)." 23:59:59' ";               
            if($dd_opr_codigo) {
                $sql .= " 
                                and (vgm_opr_codigo=".$dd_opr_codigo.")  ".PHP_EOL;
            }
            $sql .= "
                        group by id,tipo,ug_cpf, ug_nome_cpf, vg_data_concilia, forma_pagamento )

                    union all

                        ( select 
                                'gamer' as tipo,
                                1 as forma_pagamento,
                                'L' as vg_canal,
                                vg.vg_id as id,
                                ug_cpf, 
                                ug_nome_cpf as ug_nome,
                                vg_data_concilia as data,
                                sum((CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END) * vgm.vgm_qtde) as valor_total 
                        from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                		inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
                                inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                        where vg.vg_ultimo_status='5' 
                                and vg_ug_id != 7909
                                and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
                		and tvgpo.tvgpo_canal='L' 
                                and vg.vg_data_concilia >= '".trim($data_inic)." 00:00:00'
                                and vg.vg_data_concilia <= '".trim($data_fim)." 23:59:59' ";               
            if($dd_opr_codigo) {
                $sql .= " 
                                and (vgm_opr_codigo=".$dd_opr_codigo.")  ".PHP_EOL;
            }
            $sql .= "
                        group by id,tipo,ug_cpf, ug_nome_cpf, vg_data_concilia, forma_pagamento )

                    union all

                        ( select 
                                'gamer' as tipo,
                                1 as forma_pagamento,
                                'M' as vg_canal,
                                vg.vg_id as id,
                                ug_cpf, 
                                ug_nome_cpf as ug_nome,
                                vg_data_concilia as data,
                                sum((CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END) * vgm.vgm_qtde) as valor_total 
                        from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                		inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
                                inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                        where vg.vg_ultimo_status='5' 
                                and vg_ug_id != 7909
                                and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 
                		and tvgpo.tvgpo_canal='G' 
                                and vg.vg_data_concilia >= '".trim($data_inic)." 00:00:00'
                                and vg.vg_data_concilia <= '".trim($data_fim)." 23:59:59' ";               
            if($dd_opr_codigo) {
                $sql .= " 
                                and (vgm_opr_codigo=".$dd_opr_codigo.")  ".PHP_EOL;
            }
            $sql .= "
                        group by id,tipo,ug_cpf, ug_nome_cpf, vg_data_concilia, forma_pagamento )

                    union all

                        (select 
                                'pdv' as tipo,
                                1 as forma_pagamento,
                                'L' as vg_canal,
                                vg.vg_id as id,
                                -- vgm_cpf as ug_cpf, 
                                -- vgm_nome_cpf as ug_nome,
                                ug_cnpj as ug_cpf,
                                ug_razao_social as ug_nome, ".PHP_EOL;               
            if($dd_opr_codigo && $possui_totalizacao_utilizacao) {
                $sql .= "                                pih_data as data, 
                                sum((CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END)) as valor_total ".PHP_EOL;
            }
            else {
                $sql .= "                                vg_data_inclusao as data, 
                                sum((CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END) * vgm.vgm_qtde) as valor_total ".PHP_EOL;
            }
            $sql .= "
                        from tb_dist_venda_games vg 
                                inner join dist_usuarios_games ug ON ug.ug_id = vg.vg_ug_id
                                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id  ".PHP_EOL;               
            if($dd_opr_codigo && $possui_totalizacao_utilizacao) {
                $sql .= "                              inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
                             inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno ".PHP_EOL;
            }
            $sql .= "
                        where vg.vg_ultimo_status='5'  ".PHP_EOL;               
            if($dd_opr_codigo && $possui_totalizacao_utilizacao) {
                $sql .= "                              and pin_status = '8'
                                and pih_codretepp='2'
                                and pih_data >= '".trim($data_inic)." 00:00:00'
                                and pih_data <= '".trim($data_fim)." 23:59:59'".PHP_EOL;
            }
            else {
                $sql .= "
                                and vg.vg_data_inclusao >= '".trim($data_inic)." 00:00:00'
                                and vg.vg_data_inclusao <= '".trim($data_fim)." 23:59:59'";               
            }
            if($dd_opr_codigo) {
                $sql .= " 
                                and (vgm_opr_codigo=".$dd_opr_codigo.")  ".PHP_EOL;
            }
            $sql .= "
                        -- group by id,tipo,vgm_cpf, vgm_nome_cpf, data, forma_pagamento )  
                        group by id,tipo,ug_cnpj, ug_razao_social, data, forma_pagamento )
                    ";
        }//end if($dd_canal != "CARD")

        //Testando se não foi selecionado combo de canal
        if (empty($dd_canal)) {
            $sql .= "
        
                    union all
                    ";
            
        }//end if (empty($dd_canal))
        
        //SQL para seleção de somente cartões(e-Gift) confeccionados pela E-Prepag
        if($dd_canal == "CARD" || empty($dd_canal)) {
            $sql .= "
                        (select 
                                'cards' as tipo,
                                1 as forma_pagamento,
                                'C' as vg_canal,
                                pih_id as id,
                                picc_cpf as ug_cpf, 
                                picc_nome as ug_nome,
                                pih_data as data,
                                sum(pih_pin_valor/100) as valor_total
                        from pins_integracao_card_historico
                            left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                        where pin_status = '4' 
                                and pih_codretepp = '2'
                                and pih_data >= '".trim($data_inic)." 00:00:00'
                                and pih_data <= '".trim($data_fim)." 23:59:59'";               
            if($dd_opr_codigo) {
                $sql .= " 
                                and (pih_id=".$dd_opr_codigo.")  ".PHP_EOL;
             }
            $sql .= "
                        group by id,tipo,picc_cpf, picc_nome, pih_data, forma_pagamento )
                        ";
        } //end if($dd_canal == "CARD" || empty($dd_canal)) 
        
        //SQL para PINs
        if($dd_canal != "CARD") {
            $sql .= "
                    union all

                        (select 
                                'boleto_express' as tipo,
                                1 as forma_pagamento,
                                'E' as vg_canal,
                                vg_id as id,
                                vgcbe_cpf as ug_cpf, 
                                vgcbe_nome_cpf as ug_nome, 
                                vg_data_concilia as data,
                                sum((CASE
                    WHEN vgm_opr_codigo = 124 and (vgm_valor = 4 or vgm_valor = 5) THEN 4.49
					WHEN vgm_opr_codigo = 124 and vgm_valor = 14 THEN 13.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 21 THEN 20.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 45 THEN 44.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 88 THEN 87.99
					WHEN vgm_opr_codigo = 124 and vgm_valor = 210 THEN 209.99
                    ELSE vgm_valor
                END) * vgm_qtde) as valor_total
                        from tb_venda_games_cpf_boleto_express
                            inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                            inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                        where vg_ultimo_status='5' 
                                and vg_ug_id = 7909
                                and vg_data_concilia >= '".trim($data_inic)." 00:00:00'
                                and vg_data_concilia <= '".trim($data_fim)." 23:59:59'";                
            if($dd_opr_codigo) {
                $sql .= " 
                                and (vgm_opr_codigo=".$dd_opr_codigo.")  ".PHP_EOL;
            }
            $sql .= "
                        group by id,vg_canal,tipo,vgcbe_cpf, vgcbe_nome_cpf, vg_data_concilia, forma_pagamento )
                        ";
        }//end if($dd_canal != "CARD")
        
        $sql .= "
                ) tabelaUnion 
                order by data;  
                ";
        //echo "<pre>".$sql."</pre>";
        //die();
	$resid = pg_exec($connid, $sql);
	$total_table = pg_num_rows($resid);

} //end if($BtnSearch)
?>
<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/table2CSV.js"></script>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function changeSelect() {
    
    if($('#dd_tipo').val() == "SIMBO") {
        $('.tele').attr("hidden","hidden");
        $('.simbo').removeAttr("hidden");
    }
    if($('#dd_tipo').val() == "TELE") {
        $('.simbo').attr("hidden","hidden");
        $('.tele').removeAttr("hidden");
    }
}

function validade()
{
	if (document.form1.tf_data_inicial.value.trim() == "") { 
            window.alert("Por favor selecione a Datal Inicial.");
            document.form1.tf_data_inicial.focus();
            return false;
	}
	else if (document.form1.tf_data_final.value.trim() == "") { 
            window.alert("Por favor selecione a Datal Final.");
            document.form1.tf_data_final.focus();
            return false;
	}
        else if(DiferencaDatas(document.form1.tf_data_inicial.value,document.form1.tf_data_final.value) > 30) {
            window.alert("Por favor selecione uma diferença entre Datas de no máximo 30/31 dias.");
            document.form1.tf_data_inicial.focus();
            return false;
        }
        else return true;
}

function DiferencaDatas(data1, data2) {
    //Diferença entre datas com resultado em dias
    
    //Splitando
    var vetorData1 = data1.split("/");
    var vetorData2 = data2.split("/");
    // new Date(year, month, day, hours, minutes, seconds, milliseconds)
    var a = new Date(vetorData1[2],vetorData1[1],vetorData1[0], 0, 0, 0, 0); // data1
    var b = new Date(vetorData2[2],vetorData2[1],vetorData2[0], 0, 0, 0, 0); // data1
    var d = (b-a); // Diferença em millisegundos

    var days = Math.round((b-a)/1000/60/60/24);
    
    return days;
    
} //end function DiferencaDatas
//-->
</script>
<!-- INICIO CODIGO NOVO -->
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong>Relatório BACEN por Publisher (ACAM220) <?echo $vetor_SIGLA_PAIS['Brazil'];?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="../../commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-3">
                        <?php echo LANG_PINS_START_DATE; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_END_DATE; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_OPERATOR; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_CHANNEL; ?>
                    </div>
                </div>
                <form name="form1" method="post" action="" onSubmit="return validade()">
                <div class="row txt-cinza">
                    <div class="col-md-3">
                        <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                    </div>
                    <div class="col-md-3">
                        <input alt="Calendário" name="tf_data_final" type="text" class="form-control data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                    </div>
                    <div class="col-md-3">
<?php 
                        if(b_is_Publisher())
                        {
                            echo $_SESSION["opr_nome"];
?>
                            <input type="hidden" name="dd_opr_codigo" id="dd_opr_codigo" value="<?php echo $dd_opr_codigo?>">
<?php 
                        } else 
                        {
?>
                        <select name="dd_opr_codigo" id="dd_opr_codigo" class="form-control">
<?php
                            while ($pgopr = pg_fetch_array($resopr))
                            {
                                $vetorPublishersAux[$pgopr['opr_codigo']]['Nome'] = $pgopr['opr_nome'];
                                $vetorPublishersAux[$pgopr['opr_codigo']]['Pais'] = $vetor_SIGLA_PAIS[trim($pgopr['opr_pais'])];
                                $vetorPublishersAux[$pgopr['opr_codigo']]['Razao'] = $pgopr['opr_razao'];
                                $vetorPublishersAux[$pgopr['opr_codigo']]['IOF'] = $pgopr['opr_internacional_alicota'];
                                $vetorPublishersAux[$pgopr['opr_codigo']]['Empresa'] = ($pgopr['opr_vinculo_empresa'] == $IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO)?CNPJ_AGENTE_DO_COBRO_ADM:CNPJ_AGENTE_DO_COBRO;
                                $vetorPublishersAux[$pgopr['opr_codigo']]['MultiCotacaoDolar'] = $pgopr['opr_cotacao_dolar'];
?>
                                <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_opr_codigo) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?></option>
<?php  
                            }
?>
                        </select>
<?php 
                        } 
?>
                    </div>
                    <div class="col-md-3">
                        <select name="dd_canal" id="dd_canal" class="form-control">
                                <option value="" <?php  if(empty($dd_canal)) echo "selected" ?>>TODOS</option>
                                <option value="CARD" <?php  if($dd_canal == "CARD") echo "selected" ?>>Cartões E-Gift (Somente os Cartões confeccionado pela E-Prepag)</option>
                                <option value="PINS" <?php  if($dd_canal == "PINS") echo "selected" ?>>PIN (Somente venda de PINs)</option>
                        </select>
                    </div>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-2 text-right simbo"  <?php  if("TELE" == $dd_tipo || empty($dd_tipo)) echo "hidden" ?>>
                        Registro da Operação Cambial Simbólica:
                    </div>
                    <div class="col-md-2 simbo" <?php  if("TELE" == $dd_tipo || empty($dd_tipo)) echo "hidden" ?>>
                        <input  alt="Registro da Operação Cambial" name="reg_operacao" type="text" class="form-control" id="reg_operacao" value="<?php  echo $reg_operacao; ?>" size="9" maxlength="10">
                    </div>
                    <!-- div class="col-md-2 text-right">
                        Invoice Merchant:
                    </div>
                    <div class="col-md-2">
                        <input  alt="Invoice Merchant" name="invoice" type="text" class="form-control" id="invoice" value="<?php  echo $invoice; ?>" size="9" maxlength="10">
                    </div>
                    <div class="col-md-2 text-right">
                        Registro da Operação Cambial:
                    </div>
                    <div class="col-md-2">
                        <input  alt="Registro da Operação Cambial" name="cambial_tele" type="text" class="form-control" id="cambial_tele" value="<?php  echo $cambial_tele; ?>" size="9" maxlength="10">
                    </div -->
                    <div class="col-md-2 text-right">
                        Cotação em EURO:
                    </div>
                    <div class="col-md-2">
                        <input  alt="Cotação em EURO" name="cotacao_euro" type="text" class="form-control" id="cotacao_euro" value="<?php  echo $cotacao_euro; ?>" size="9" maxlength="10">
                    </div>
                    <div class="col-md-2 text-right tele" <?php  if("SIMBO" == $dd_tipo) echo "hidden" ?>>
                        Registro da Operação Cambial Teletransmissão:
                    </div>
                    <div class="col-md-2 tele" <?php  if("SIMBO" == $dd_tipo) echo "hidden" ?>>
                        <input  alt="Registro da Operação Cambial Teletransmissão" name="cambial_tele" type="text" class="form-control" id="cambial_tele" value="<?php  echo $cambial_tele; ?>" size="9" maxlength="10">
                    </div>
                    <div class="col-md-2 text-right">
                        Selecione o tipo de arquivo:
                    </div>
                    <div class="col-md-2">
                        <select name="dd_tipo" id="dd_tipo" class="form-control" onchange="changeSelect();">
                                <option value="TELE" <?php  if("TELE" == $dd_tipo || empty($dd_tipo)) echo "selected" ?>>TELETRANSMISSÃO</option>
                                <option value="SIMBO" <?php  if("SIMBO" == $dd_tipo) echo "selected" ?>>SIMBÓLICA</option>
                        </select>
                    </div>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-4 text-right">
                        Parceiro de Câmbio:
                    </div>
                    <div class="col-md-4">
                        <select name="cnpj_instituicao" id="cnpj_instituicao" class="form-control">
                                <option value="07679404000100" <?php  if($cnpj_instituicao == "07679404000100" || empty($cnpj_instituicao)) echo "selected" ?>>BANCO TOPÁZIO</option>
                                <option value="11703662000144" <?php  if($cnpj_instituicao == "11703662000144") echo "selected" ?>>TRAVELEX</option>
                        </select>              
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH;?></button>
                    </div>
                </div>
                </form>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
<?php  
                    // Recuperando cotação de dolar
                    if(isset($dd_opr_codigo)) {
                        if($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1) {
                            $cotacao_dolar = (empty($cotacao_euro))?recupera_cotacao_dolar_diario($dd_opr_codigo,substr($data_inic,5,2),substr($data_inic,0,4)): str_replace(",", ".", $cotacao_euro);
                        }//end if($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'])
                        else {
                            $cotacao_dolar = (empty($cotacao_euro))?recupera_cotacao_dolar($dd_opr_codigo,substr($data_inic,5,2),substr($data_inic,0,4)): str_replace(",", ".", $cotacao_euro);
                        }//end else do if($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'])
                    }//end if(isset($dd_opr_codigo)) 

                    if(is_array($cotacao_dolar)) {
                        foreach ($cotacao_dolar as $datavalidacao => $valorvalidacao) {
                            if(empty($valorvalidacao)) {
                                unset($cotacao_dolar);
                                break;
                            }
                        }//end foreach
                    }//end if(is_array($cotacao_dolar))
                    
                    if($total_table > 0 && (!empty($cotacao_dolar) || is_array($cotacao_dolar))) {
                        //$cabecalho = "'CNPJ da Instituição','Registro da Operação Cambial','Agente de Cobro','cnpj agente de cobro','Data Transação','ID Pedido','Tipo de Cliente no Brasil','CPF','NOME','nome do comprador ou vendedor','Country','Currency','Tipo de transação','forma de pagamento','Total USD','Total R$','Invoice Merchant','Beneficiario','Cotação'";
                        $cabecalho = "'CNPJ da Instituição','Registro da Operação Cambial','CNPJ empresa facilitadora Ou CNPJ/CPF de intermediário ou representante','Data Transação','Tipo de Cliente no Brasil','CPF/CNPJ do cliente no Brasil','Nome do cliente no Brasil','Nome do comprador ou vendedor no exterior','País do comprador ou vendedor no exterior','Moeda','Tipo de transação','forma de pagamento','Valor na moeda estrangeira','Valor na moeda nacional'";
?>
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                        <thead>
                          <tr class="bg-cinza-claro nobr">
                            <th class="text-center">CNPJ da Instituição</th>
                            <th class="text-center">Registro da Operação Cambial</th>
<?php /* ?>
                            <th class="text-center">Registro da Operação Cambial Teletransmissão</th>
                            <th class="text-center">Registro da Operação Cambial Simbólica</th>
                            <th class="text-center">Agente de Cobro</th>
                            <th class="text-center">cnpj agente de cobro</th>
 */
?>
                            <th class="text-center">CNPJ empresa facilitadora Ou CNPJ/CPF de intermediário ou representante</th>
                            <th class="text-center">Data Transação</th>
<?php
/*
?>                            
                            <th class="text-center">ID Pedido</th>
<?php
*/
?>                            
                            <th class="text-center">Tipo de Cliente no Brasil</th>
                            <th class="text-center">CPF/CNPJ do cliente no Brasil</th>
                            <th class="text-center">Nome do cliente no Brasil</th>
                            <th class="text-center">Nome do comprador ou vendedor no exterior</th>
                            <th class="text-center">País do comprador ou vendedor no exterior</th>
                            <th class="text-center">Moeda</th>
                            <th class="text-center">Tipo de transação</th>
                            <th class="text-center">Forma de pagamento</th>
<?php
/*
?>                            
                            <th class="text-center">Valor Simbólica em R$</th>
                            <th class="text-center">Valor Simbólica em USD</th>
                            <th class="text-center">Valor da Teletransmissão em R$</th>
                            <th class="text-center">Valor da Teletransmissão em USD</th>
<?php
*/
?>
                            <th class="text-center">Valor na moeda estrangeira</th>
                            <th class="text-center">Valor na moeda nacional</th>
<?php
/*
?>                            
                            <th class="text-center">Invoice Merchant</th>
                            <th class="text-center">Beneficiario</th>
                            <th class="text-center">Cotação</th>
                          </tr>
                          <tr>
                            <th colspan="8">
                                <?php echo ' '.LANG_SHOW_DATA.' '; ?> <strong><?php  echo $inicial + 1 ?></strong><?php echo ' '.LANG_TO.' '; ?><strong><?php  echo $reg_ate ?></strong><?php echo ' '.LANG_FROM.' '; ?><strong><?php  echo $total_table ?> <span id="txt_totais" class="txt-azul-claro"></strong>
                            </th>
                          </tr>
<?php
*/
?>
                          <tr>
                            <th colspan="8">
                                <span id="txt_totais" class="txt-azul-claro"></strong>
                            </th>
                          </tr>
                        </thead>
                        <tbody>
<?php
                    // INICIO TRECHO TELETRANSMISSÃO
if("TELE" == $dd_tipo) {
                    $total_geral_liquido = 0;
                    $total_geral_liquido_dolar = 0;
                    $total_geral_simbolica = 0;
                    $total_geral_simbolica_dolar = 0;
                    $total_geral_remessa = 0;
                    $total_geral_remessa_dolar = 0;
                    
					$countE = 0;
                    while ($pgrow = pg_fetch_array($resid)) {
						
                            $valor = 1;
							
							// temporario \/
							if($dd_opr_codigo == 124 && $countE == 0){
								$pgrow['valor_total'] += 48.96;
								$countE++;
							}

                            $valor_geral += $pgrow['valor_total'];
                            
                            //Calculando a Comissão
                            $valor_iof = $pgrow['valor_total']/100*$vetorPublishersAux[$dd_opr_codigo]['IOF'];
                            $valor_sem_iof = $pgrow['valor_total'] - $valor_iof;
                            $alicota = recupera_comissao($dd_opr_codigo,substr($pgrow['data'],0,10),$pgrow['vg_canal']);
                            $valor_comissao = $pgrow['valor_total']/100*$alicota;
                            $valor_sem_iof_sem_comissao = $pgrow['valor_total'] - $valor_iof - $valor_comissao;
							
						
                            //var_dump($valor_iof,$valor_sem_iof,$valor_comissao, $valor_sem_iof_sem_comissao);
                            //Fim Calculando a Comissão
                            
                            //Calculando total em reias
                            $total_geral_liquido += $valor_sem_iof;
                            
                            //Calculando o valor em dolar
                            $valor_liquido_dolar = ($valor_sem_iof/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar));
                            $total_geral_liquido_dolar += $valor_liquido_dolar;
                            
                            //Calculando total simbolica
                            $total_geral_simbolica += $valor_comissao;
                            $total_geral_simbolica_dolar += ($valor_comissao/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar));
                            
                            //Calculando taotal da remessa
                            $total_geral_remessa += $valor_sem_iof_sem_comissao;
                            $total_geral_remessa_dolar += ($valor_sem_iof_sem_comissao/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar));
                            
                            //Limpando CPF/CNPJ de pontuação
                            $cpfcnpj = str_replace("-", "", str_replace(".", "", $pgrow['ug_cpf']));
?>
                            <tr class="trListagem">
                                <td align="center"><?php echo $cnpj_instituicao; ?></td>
                                <td align="center"><?php echo $cambial_tele; ?></td>
<?php
/*
?>                                
                                <td align="center"><?php echo $reg_operacao; ?></td>
                                <td align="center"><?php echo AGENTE_DE_COBRO; ?></td>
<?php
 */
?>
                                <td align="center"><?php echo $vetorPublishersAux[$dd_opr_codigo]['Empresa']; ?></td>
                                <td align="center"><?php echo substr($pgrow['data'],0,10); ?></td>
<?php
/*
?>                                
                                <td><?php  echo $ARRAY_CONCATENA_ID_VENDA[$pgrow['tipo']].str_pad($pgrow['id'],8,'0',STR_PAD_LEFT); ?></td>
<?php
*/
?>                                
                                <td align="center"><?php echo (strlen($cpfcnpj)==11)?TIPO_DE_CLIENTE_NO_BRASIL:'J'; ?></td>
                                <td><?php  echo $cpfcnpj; //str_replace("-", "", str_replace(".", "", $pgrow['ug_cpf'])); ?></td>
                                <td><?php  echo $pgrow['ug_nome']; ?></td>
                                <td><?php  echo $vetorPublishersAux[$dd_opr_codigo]['Razao']; //Nome ?></td>
                                <td><?php  echo $vetorPublishersAux[$dd_opr_codigo]['Pais']; ?></td>
                                <td align="center"><?php echo (empty($cotacao_euro))?REMESSA_MOEDA:REMESSA_MOEDA_EURO; ?></td>
                                <td align="center"><?php echo TIPO_DE_TRASACAO; ?></td>
                                <td align="center"><?php echo $pgrow['forma_pagamento']; ?></td>
<?php
/*
?>                                
                                <td align="center"><?php echo str_replace(".", ",", $valor_comissao); ?></td>
                                <td align="center"><?php echo str_replace(".", ",", ($valor_comissao/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar))); ?></td>
<?php
 */
?>
                                <td align="center"><?php echo str_replace(".", ",", ($valor_sem_iof_sem_comissao/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar)));; ?></td>
                                <td align="center"><?php echo str_replace(".", ",", $valor_sem_iof_sem_comissao); ?></td>
<?php
/*
?>                                
                                <td align="center"><?php  echo str_replace(".", ",",$valor_liquido_dolar); //number_format($pgrow['valor_total']/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar), 2, ',', '.'); ?></td>
                                <td align="right"><?php  echo number_format($valor_sem_iof, 2, ',', '.'); ?></td>
                                <td align="center"><?php echo $invoice; //echo "ROUND: ".round($aux_comiss,2)." - ".$total_iof." - Tipo: ".$pgrow['tipo']." - Canal ".($pgrow['vg_canal'] == 'P'?" POS":($pgrow['vg_canal'] == 'C'?"CARTAO":($pgrow['vg_canal'] == 'M'?"GAMER":($pgrow['vg_canal'] == 'L'?"PDV":($pgrow['vg_canal'] == 'E'?"BOLETO EXPRESS":""))))); ?></td>
                                <td><?php  echo $vetorPublishersAux[$dd_opr_codigo]['Razao']; ?></td>
                                <td align="center"><?php echo str_replace(".", ",", (($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar)); ?></td>
<?php
 */
?>
                            </tr>
<?php
                    }
                    
                    if(!$valor) {
?>
                        <tr bgcolor="#f5f5fb"> 
                            <td colspan="19"><?php echo LANG_NO_DATA;?>.</td>
                        </tr>
<?php  
                    } else { 
                        $time_end = getmicrotime();
                        $time = $time_end - $time_start;
?>
                        <tr> 
                            <td colspan="11">&nbsp;</td>
                            <td>TOTAL</td>
<?php
/*
?>
                            <td class="text-right"><strong>R$ <?php  echo number_format(round($total_geral_simbolica,2), 2, ',', '.'); ?></strong></td>
                            <td class="text-right"><strong>US$ <?php  echo number_format(round($total_geral_simbolica_dolar,2), 2, ',', '.'); ?></strong></td>
                            <td class="text-right"><strong>US$ <?php  echo number_format(round($total_geral_remessa_dolar,2), 2, ',', '.'); ?></strong></td>
                            <td class="text-right"><strong>R$ <?php  echo number_format(round($total_geral_remessa,2), 2, ',', '.'); ?></strong></td>
<?php
 */
?>
                            <td class="text-right"><strong>US$ <?php echo number_format(round($total_geral_liquido_dolar,2), 2, ',', '.'); ?></strong></td>
                            <td class="text-right"><nobr><strong>R$ <?php  echo number_format(round($total_geral_liquido,2), 2, ',', '.'); ?></strong></nobr></td>
                            <td colspan="3">&nbsp;</td>
                        </tr>
<?php  
                    } 
                    // FIM TRECHO TELETRANSMISSÃO
}
if("SIMBO" == $dd_tipo ) {
                    //Resetando a resposta do banco para geração da simbolica separadamente
                    //pg_result_seek($resid, 0);
                    
                    // INICIO TRECHO SIMBOLICA
                    $total_geral_liquido = 0;
                    $total_geral_liquido_dolar = 0;
                    $total_geral_simbolica = 0;
                    $total_geral_simbolica_dolar = 0;
                    $total_geral_remessa = 0;
                    $total_geral_remessa_dolar = 0;
                    
                    while ($pgrow = pg_fetch_array($resid)) {
                            $valor = 1;

                            $valor_geral += $pgrow['valor_total'];
                            
                            //Calculando a Comissão
                            $valor_iof = $pgrow['valor_total']/100*$vetorPublishersAux[$dd_opr_codigo]['IOF'];
                            $valor_sem_iof = $pgrow['valor_total'] - $valor_iof;
                            $alicota = recupera_comissao($dd_opr_codigo,substr($pgrow['data'],0,10),$pgrow['vg_canal']);
                            $valor_comissao = $pgrow['valor_total']/100*$alicota;
                            $valor_sem_iof_sem_comissao = $pgrow['valor_total'] - $valor_iof - $valor_comissao;
							//echo '<pre>';
                            //var_dump($valor_iof,$valor_sem_iof,$valor_comissao, $valor_sem_iof_sem_comissao);
							//echo '</pre>';
                            //Fim Calculando a Comissão
                            
                            //Calculando total em reias
                            $total_geral_liquido += $valor_sem_iof;
                            
                            //Calculando o valor em dolar
                            $valor_liquido_dolar = ($valor_sem_iof/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar));
                            $total_geral_liquido_dolar += $valor_liquido_dolar;
                            
                            //Calculando total simbolica
                            $total_geral_simbolica += $valor_comissao;
                            $total_geral_simbolica_dolar += ($valor_comissao/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar));
                            
                            //Calculando taotal da remessa
                            $total_geral_remessa += $valor_sem_iof_sem_comissao;
                            $total_geral_remessa_dolar += ($valor_sem_iof_sem_comissao/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar));
                            
                            //Limpando CPF/CNPJ de pontuação
                            $cpfcnpj = str_replace("-", "", str_replace(".", "", $pgrow['ug_cpf']));
?>
                            <tr class="trListagem">
                                <td align="center"><?php echo $cnpj_instituicao; ?></td>
<?php
/*
?>                                
                                <td align="center"><?php echo $cambial_tele; ?></td>
<?php
 */
?>
                                <td align="center"><?php echo $reg_operacao; ?></td>
<?php
/*
?>
                                <td align="center"><?php echo AGENTE_DE_COBRO; ?></td>
<?php
*/
?>
                                <td align="center"><?php echo $vetorPublishersAux[$dd_opr_codigo]['Empresa']; ?></td>
                                <td align="center"><?php echo substr($pgrow['data'],0,10); ?></td>
<?php
 
/* 
         
?>                                
                                <td><?php  echo $ARRAY_CONCATENA_ID_VENDA[$pgrow['tipo']].str_pad($pgrow['id'],8,'0',STR_PAD_LEFT); ?></td>
<?php
*/

?>                                
                                <td align="center"><?php echo (strlen($cpfcnpj)==11)?TIPO_DE_CLIENTE_NO_BRASIL:'J'; ?></td>
                                <td><?php  echo $cpfcnpj; //str_replace("-", "", str_replace(".", "", $pgrow['ug_cpf'])); ?></td>
                                <td><?php  echo $pgrow['ug_nome']; ?></td>
                                <td><?php  echo $vetorPublishersAux[$dd_opr_codigo]['Razao']; ?></td>
                                <td><?php  echo $vetorPublishersAux[$dd_opr_codigo]['Pais']; ?></td>
                                <td align="center"><?php echo (empty($cotacao_euro))?REMESSA_MOEDA:REMESSA_MOEDA_EURO; ?></td>
                                <td align="center"><?php echo TIPO_DE_TRASACAO; ?></td>
                                <td align="center"><?php echo $pgrow['forma_pagamento']; ?></td>
                                <td align="center"><?php echo str_replace(".", ",", ($valor_comissao/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar))); ?></td>
                                <td align="center"><?php echo str_replace(".", ",", $valor_comissao); ?></td>
<?php
/*
?>                               
                                <td align="center"><?php echo str_replace(".", ",", $valor_sem_iof_sem_comissao); ?></td>
                                <td align="center"><?php echo str_replace(".", ",", ($valor_sem_iof_sem_comissao/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar)));; ?></td>
                                <td align="center"><?php  echo str_replace(".", ",",$valor_liquido_dolar); //number_format($pgrow['valor_total']/(($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar), 2, ',', '.'); ?></td>
                                <td align="right"><?php  echo number_format($valor_sem_iof, 2, ',', '.'); ?></td>
                                <td align="center"><?php echo $invoice; //echo "ROUND: ".round($aux_comiss,2)." - ".$total_iof." - Tipo: ".$pgrow['tipo']." - Canal ".($pgrow['vg_canal'] == 'P'?" POS":($pgrow['vg_canal'] == 'C'?"CARTAO":($pgrow['vg_canal'] == 'M'?"GAMER":($pgrow['vg_canal'] == 'L'?"PDV":($pgrow['vg_canal'] == 'E'?"BOLETO EXPRESS":""))))); ?></td>
                                <td><?php  echo $vetorPublishersAux[$dd_opr_codigo]['Razao']; ?></td>
                                <td align="center"><?php echo str_replace(".", ",", (($vetorPublishersAux[$dd_opr_codigo]['MultiCotacaoDolar'] == 1)?$cotacao_dolar[substr($pgrow['data'],0,10)]:$cotacao_dolar)); ?></td>
<?php
 */
?>
                            </tr>
<?php
                    }
                    
                    if(!$valor) {
?>
                        <tr bgcolor="#f5f5fb"> 
                            <td colspan="19"><?php echo LANG_NO_DATA; ?>.</td>
                        </tr>
<?php  
                    } else { 
                        $time_end = getmicrotime();
                        $time = $time_end - $time_start;
?>
                        <tr> 
                            <td colspan="11">&nbsp;</td>
                            <td>TOTAL</td>
<?php
/*
?>
                            <td class="text-right"><strong>US$ <?php  echo number_format(round($total_geral_simbolica_dolar,2), 2, ',', '.'); ?></strong></td>
                            <td class="text-right"><strong>R$ <?php  echo number_format(round($total_geral_simbolica,2), 2, ',', '.'); ?></strong></td>
                            <td class="text-right"><strong>R$ <?php  echo number_format(round($total_geral_remessa,2), 2, ',', '.'); ?></strong></td>
                            <td class="text-right"><strong>US$ <?php  echo number_format(round($total_geral_remessa_dolar,2), 2, ',', '.'); ?></strong></td>
<?php
 */
?>
                            <td class="text-right"><strong>US$ <?php  echo number_format(round($total_geral_liquido_dolar,2), 2, ',', '.'); ?></strong></td>
                            <td class="text-right"><nobr><strong>R$ <?php  echo number_format(round($total_geral_liquido,2), 2, ',', '.'); ?></strong></nobr></td>
                        </tr>
<?php  
                    } 
}                    
                    // FIM TRECHO SIMBOLICA
?>
                        <tr class="bg-cinza-claro">
                            <td colspan="20" class="fontsize-pp"><?php echo LANG_PINS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                        </tr>
                        <tr class="bg-cinza-claro"> 
                            <td colspan="20" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); //echo " - ".$total_geral_liquido. " - ".number_format(round(($total_geral_liquido_dolar),2), 2, ',', '.'); ?></td>
                        </tr>
                        </tbody>
                    </table>
                    <a href="#" class="btn downloadCsv btn-info ">Download CSV</a>

                <script language="JavaScript">
                  document.getElementById('txt_totais').innerHTML = 'Simbólica + Teletransmissão ( US$ <?php  echo number_format(round($total_geral_liquido_dolar,2), 2, ',', '.'); ?> - R$ <?php  echo number_format(round($total_geral_liquido,2), 2, ',', '.'); ?> )';
                </script>
<?php
                }
                elseif($BtnSearch) {
                    if(empty($cotacao_dolar) && !is_array($cotacao_dolar)) {
                        echo "Cotação do dolar não cadastrada para o Publisher [".$vetorPublishersAux[$dd_opr_codigo]['Nome']."] neste período selecionado ou parte do período.<br>Por favor, verifique o cadastro de cotação de dolar.";
                    }
                    else {
                         echo LANG_NO_DATA.".";
                    }
                }
?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:""});
    
    var optDate = new Object();
            optDate.interval = 1;

        setDateInterval('tf_data_inicial','tf_data_final',optDate);
});
</script>
<!-- FIM CODIGO NOVO -->
<?php  
pg_close($connid); 
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";

function recupera_cotacao_dolar($dd_opr_codigo,$mes, $ano){

    $currentmonthVerify = mktime(0, 0, 0, $mes, 1, $ano);
    $sql = "select cd_cotacao from cotacao_dolar where opr_codigo = ".$dd_opr_codigo." and cd_data = '".date('Y-m-d',$currentmonthVerify)." 00:00:00';";
    //echo $sql."<br><br>";
    $rs_dolar = SQLexecuteQuery($sql);

    if($rs_dolar && pg_num_rows($rs_dolar) > 0) {
        $valor_dolar_aux = pg_fetch_array($rs_dolar);
        return floatval($valor_dolar_aux['cd_cotacao']);
    } else{
        return FALSE;
    }
}

function recupera_cotacao_dolar_diario($dd_opr_codigo,$mes, $ano){

    $currentmonthVerify = mktime(0, 0, 0, $mes, 1, $ano);
    $sql = "select cd_cotacao,to_char(cd_data,'YYYY-MM-DD') as data from cotacao_dolar where opr_codigo = ".$dd_opr_codigo." and to_char(cd_data,'YYYY-MM') = '".date('Y-m',$currentmonthVerify)."';";
    $rs_dolar = SQLexecuteQuery($sql);
    $vetor_aux = NULL;
    if($rs_dolar && pg_num_rows($rs_dolar) > 0) {
        while ($valor_dolar_aux = pg_fetch_array($rs_dolar)) {
            $vetor_aux[$valor_dolar_aux['data']] = floatval($valor_dolar_aux['cd_cotacao']);
        }
        return $vetor_aux;
    } else{
        return FALSE;
    }
}

function recupera_comissao($dd_opr_codigo,$data,$canal){

    $sql = "select fp_aliquot from financial_processing where fp_publisher = ".$dd_opr_codigo." and fp_date = '".$data." 00:00:00' and fp_channel = '".$canal."';";
    //echo $sql."<br><br>";
    $rs_cotacao = SQLexecuteQuery($sql);

    if($rs_cotacao && pg_num_rows($rs_cotacao) > 0) {
        $rs_cotacao_row = pg_fetch_array($rs_cotacao);
        return $rs_cotacao_row['fp_aliquot'];
    } else{
        return FALSE;
    }
} 
?>
