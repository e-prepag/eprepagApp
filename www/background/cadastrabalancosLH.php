<?php 
/*************************************************************************************
	O Balanço de LANs tem a seguintes características:

	- Para novas LAN que ainda não tiveram movimentação é gerado um registro na tabela dist_balancos com saldo zero.
	- Somente são gerados novos registros de balanço para LANs que possuem movimentação após a geração do ultimo registro de balanço.
***************************************************************************************/
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
ini_set('memory_limit', '-1');

set_time_limit(18000);

$cReturn = PHP_EOL;
$cBr = $cReturn;
$cHr = str_repeat("-",80).PHP_EOL;
$cHr2 = str_repeat("=",80).PHP_EOL;
$cHr3 = str_repeat("*",80).PHP_EOL;

$cBold = "";
$cBoldEnd = "";
$cH3 = PHP_EOL;
$cH3End = PHP_EOL;
$cSpan01 = "";
$cSpanEnd = "";
$cFontAlert1 = "";
$cFontAlert2 = "";
$cFontAlertRed = "";
$cFontAlertBlue = "";
$cFontAlertGreen = "";
$cFontAlertEnd = "";

//Varivel inibindo execição no Banco de Dados
$b_db_execute = true;

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php"; 

ob_end_flush();

$data_de_hoje_start = date("Y-m-d H:i:s");

echo $cHr2."Cadastro do Balanço de PDV (".$data_de_hoje_start.")".$cReturn.$cReturn;

$time_start = getmicrotime();

// Seleciona cada lanhouse :
//real ---------> $query = "select ug_id,ug_perfil_saldo,ug_perfil_limite,ug_risco_classif from dist_usuarios_games where ug_ativo = '1' ";
$query = "select * from dist_usuarios_games ug where ug_ativo = '1' order by ug.ug_id, ug.ug_login;";

echo "SQL: ".$query.$cReturn.$cReturn;

//// configura o intervalo de verificação de venda no caso 5 , signica que vai verificar se houve venda dentro dos ultimos 5 dias 
//$n_dias = 7;

//////////////////////////////////////////////////////////////////////

$rs = SQLexecuteQUERY($query);

$total_considerado = pg_num_rows($rs);
echo "PDVs Considerados: ".$total_considerado.$cReturn;

$msg = "";
$nrows = 0;
$str_lista_lans = "";
$n_lista_lans = 0;
$i = 0;

//echo "BALANCO_ZERO_FLOAT: ".$BALANCO_ZERO_FLOAT.$cReturn; 

while ($rs_row = pg_fetch_array($rs)) {
        $i++;

	echo $cReturn.$cHr3.$cReturn."Procesa novo PDV = ".$rs_row['ug_id'].$cReturn.$cHr3.$cReturn;

	$time_start_lh = getmicrotime();
        
        // Init geral values
        $saldo = 0;
        $total = 0;
        $data_de_hoje = date("Y-m-d H:i:s");

        $saldo_ultimo_balanco = 0;
        $b_cadastra_novo_balanco = false;
        $data_ultimo_balanco = "";
        $s_resumo = "";
        $b_insert = false;

        // Init lan values
        $id_lan = $rs_row['ug_id'];
        $lan_saldo = (is_null($rs_row['ug_perfil_saldo'])?0:$rs_row['ug_perfil_saldo']);
        $lan_limite = (is_null($rs_row['ug_perfil_limite'])?0:$rs_row['ug_perfil_limite']);
        $tipo_lan = $rs_row['ug_risco_classif'];
        $data_leitura = data_menos_n($data_de_hoje,0); // Exibição da data para leitura humana


        echo (!$b_db_execute)?$s_resumo .= $cHr2."Entering n: $i (".number_format( $i*100/$total_considerado, 2, '.', '.')."%) '".$data_de_hoje."'  ID: $id_lan (tipo_lan: ".(($tipo_lan==1)?"PÓS":(($tipo_lan==2)?"PRÉ":"???")).", Ativa: ".(($rs_row['ug_ativo']==1)?"SIM":"não").", limite: $lan_limite, saldo: $lan_saldo)".$cReturn:"";	
        ///////////////////////////////////////////////////////////////////////////////////////////////

        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                if($b_db_execute) {
                        $ret = SQLexecuteQuery($sql);
                        if(!$ret) $msg = "Erro ao iniciar transação.".$cReturn;
                }
        }

        if($msg == ""){

                $ultimo_balanco_query = "
                    select coalesce (db_data_balanco,'2001-01-01'::timestamp without time zone) as data_ultimo_balanco, db_saldo, db_limite, db_valor_balanco 
                    from dist_balancos 
                    where db_ug_id = $id_lan 
                    order by db_data_balanco desc limit 1;";

                echo (!$b_db_execute)?"ultimo_balanco_query: ".$ultimo_balanco_query.$cReturn:"";
                
                $rs_ultimo_balanco_query = SQLexecuteQuery($ultimo_balanco_query);

                if(!$rs_ultimo_balanco_query || pg_num_rows($rs_ultimo_balanco_query) == 0){
                        $stmp = "******* Não encontrou ponto inicial (ug_id:$id_lan)".$cReturn;	
                        $s_resumo .= $stmp;
                        echo $stmp."".$cReturn;

                } else {
                        $dados_ultimo_balanco_query = pg_fetch_array($rs_ultimo_balanco_query);
                        $data_ultimo_balanco = $dados_ultimo_balanco_query['data_ultimo_balanco'];
                        $limite_ultimo_balanco =  $dados_ultimo_balanco_query['db_limite'];
                        $saldo_ultimo_balanco =  $dados_ultimo_balanco_query['db_saldo'];
                        $pos_valor_ultimo_balanco = $dados_ultimo_balanco_query['db_valor_balanco'];
                        $db_resultado = 0;//// <------- significa que não é um ponto inicial

                        $stmp = "******* Encontrado ponto inical (ug_id:$id_lan -> '$data_ultimo_balanco')".$cReturn;
                        $s_resumo .= $stmp;
                        echo $stmp."".$cReturn;
                }
        }

        echo "Data_ultimo_balanco: '$data_ultimo_balanco'".PHP_EOL;			

        if ($data_ultimo_balanco == "") {
                echo (!$b_db_execute)?"Criar ponto inicial de balanço (ou seja, dados do PDV neste instante)".PHP_EOL:"";

                $limite_ultimo_balanco = 0;
                $saldo_ultimo_balanco = 0;
                $pos_valor_ultimo_balanco = 0;
                $db_resultado = 5;			//// <------- significa que é um ponto inicial

                $b_cadastra_novo_balanco = true;
                $data_ultimo_balanco = date("Y-m-d H:i:s");
                $ug_risco_classif = $rs_row['ug_risco_classif'];
                $limite_ultimo_balanco = $rs_row['ug_perfil_limite'];
                $saldo_ultimo_balanco = $rs_row['ug_perfil_saldo'];
                if($ug_risco_classif==1) {
                        $saldo = $rs_row['ug_perfil_limite'];
                } elseif($ug_risco_classif==2) {
                        $saldo = $rs_row['ug_perfil_saldo'];
                } else {
                        $saldo = 0;
                }

                $stmp = "-------- ============ Cadastra novo balanço $id_lan (saldo: $saldo)".$cReturn;
                $s_resumo .= $stmp;
                echo (!$b_db_execute)?$stmp."".$cReturn:"";
                /*	  
                O CAMPO db_resultado APRESENTA OS SEGUINTES VALORES:

                1 - O SALDO PRÉ ESTA OK
                2 - FALHA NA CONTAGEM DO SALDO DE UMA LAN PRÉ
                3 - O SALDO DA LAN PÓS ESTA CORRETO
                4 - FALHA NO SALDO DE UMA LAN PÓS
                5 - É O PONTO INICIAL **

                */

        } else {
                echo (!$b_db_execute)?$cFontAlertBlue."Existe data_ultimo_balanco: '$data_ultimo_balanco'".$cFontAlertEnd.$cReturn:""; 
        }

        $s_tipo_balanco = $cFontAlertGreen."=== data_ultimo_balanco (1): '$data_ultimo_balanco' ".(($b_cadastra_novo_balanco) ? "Cadastra Balanço Ponto inicial)" : "Cadastra Balanço comun")."".$cFontAlertEnd.$cReturn;
        echo (!$b_db_execute)?$s_tipo_balanco."".$cReturn:"";

        $qtde_saldo_limite = 0;
        if($tipo_lan=="1") {
                $total = $lan_limite;
        } elseif($tipo_lan=="2") {
                $total = $lan_saldo;
        } else {
                $total = 0;
        }

        $ug_perfil_saldo  = (is_null($rs_row['ug_perfil_saldo'])?0:$rs_row['ug_perfil_saldo']); // usar aqui para o produção

        $limite = (is_null($rs_row['ug_perfil_limite'])?0:$rs_row['ug_perfil_limite']); // usar aqui para o produção

        $stmp = "Resumo - total: ".number_format($total, 2, ',', '.').", ug_perfil_saldo: ".number_format($ug_perfil_saldo, 2, ',', '.').", limite: ".number_format($limite, 2, ',', '.')."".$cReturn;
        $s_resumo .= $stmp;
        echo (!$b_db_execute)?$stmp."".$cReturn:"";

        echo (!$b_db_execute)?"   total: '$total'".PHP_EOL:"";
        echo (!$b_db_execute)?"   ug_perfil_saldo: '$ug_perfil_saldo'".PHP_EOL:"";			
        echo (!$b_db_execute)?"   limite: '$limite'".PHP_EOL:"";			


        if ($data_ultimo_balanco != '' ) {

                echo (!$b_db_execute)?"   b_cadastra_novo_balanco: '".(($b_cadastra_novo_balanco)?"TRUE":"false")."'".$cReturn:"";
                $qtde_vendas = 0;
                $qtde_boletos = 0;
                $qtde_cortes = 0;
                $qtde_pag_online = 0;
                $val_vendas = 0;
                $val_boletos = 0;
                $val_cortes = 0;
                $val_pag_online = 0;
                //b2c
                $val_vendas_b2c = 0;
                $val_comissao_b2c = 0;
                $qtde_vendas_b2c = 0;
                //redesim
                $val_vendas_redesim = 0;
                $val_comissao_redesim = 0;
                $qtde_vendas_redesim = 0;


                // Se não é ponto inicial => tenta cadastrar balanço caso tenha vendas/boletos/pagamentos
                if(!$b_cadastra_novo_balanco) {

                        ////// <--------- CONFERINDO VALORES ----------------> ////////////////////////////////////
                        //// 1 -- RESGASTAR OS VALORES -----------------------  ///////////////////////////////////

                        $data_final_intervalo = $data_de_hoje;

                        echo (!$b_db_execute)?"   data_final_intervalo: '$data_final_intervalo'".PHP_EOL:"";

                        /// A QUERY ABAIXO IRÁ PUXAR UMA LISTA COM VENDAS , BOLETOS , PAGAMENTOS , CORTES PARA PODER FAZER A CONTAGEM  
                        $sql = "select num_doc, tipo_pagto, data_transacao, valor, sum(valor - repasse) as comissao, repasse, tipo, status from ( 
                        (select (vg.vg_id::text) as num_doc,
                        vg.vg_data_inclusao as data_transacao,
                        vg.vg_pagto_tipo as tipo_pagto,
                        (sum(vgm.vgm_valor * vgm.vgm_qtde)::real) as valor,
                        sum(vgm.vgm_qtde) as qtde_itens,
                        count(*) as qtde_produtos, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse ,
                        'Venda'::text as tipo,
                         NULL::text as status
                        from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join dist_usuarios_games ug on vg.vg_ug_id = ug.ug_id where ug_id = $id_lan and vg.vg_ultimo_status=5 and vg.vg_data_inclusao between '$data_ultimo_balanco' and '$data_final_intervalo' group by num_doc, data_transacao, tipo_pagto, vg.vg_ultimo_status,vg.vg_ug_id,ug.ug_nome_fantasia,ug.ug_nome,ug.ug_cpf,ug.ug_cnpj ) 

                        union all 

                        (select (bol_documento::text) as num_doc,
                        bol_importacao as data_transacao,
                        vg_pagto_tipo as tipo_pagto ,
                        (sum (bol_valor - bbg_valor_taxa)::real) as valor ,
                        NULL::int as qtde_itens,
                        NULL::int as qtde_produtos ,
                        NULL::real as repasse ,
                        'Boleto'::text as tipo,
                        'Pre'::text as status

                        from boletos_pendentes, bancos_financeiros, tb_dist_venda_games, dist_boleto_bancario_games, dist_usuarios_games ug where ug_id =  $id_lan and (bol_banco = bco_codigo) and (bol_venda_games_id=vg_id) and (bco_rpp = 1) and (vg_ug_id = ug_id)  and bbg_vg_id = vg_id and bol_importacao between '$data_ultimo_balanco' and '$data_final_intervalo' and substr(bol_documento,1,1) = '4' group by bol_documento,vg_data_inclusao,vg_pagto_tipo,bol_importacao,ug_id,ug_nome_fantasia,ug_nome,ug_cpf,ug_cnpj ) 

                        union all 

                        (select (vb2c_vg_id::text) as num_doc,
                        \"vb2c_dataVenda\" as data_transacao,
                        NULL as tipo_pagto ,
                        (sum (\"vb2c_precoServico\")::real) as valor ,
                        count(1::int) as qtde_itens,
                        count(1::int) as qtde_produtos,
                        (sum(\"vb2c_precoServico\" - (\"vb2c_precoServico\" * vb2c_comissao_para_repasse/100))) as repasse ,
                        'B2C'::text as tipo,
                        NULL::text as status

                        from tb_vendas_b2c
                        where vb2c_ug_id_lan =  $id_lan 
                                and vb2c_status='1'
                                and \"vb2c_dataVenda\" between '$data_ultimo_balanco' and '$data_final_intervalo' 
                                group by vb2c_vg_id,\"vb2c_dataVenda\"
                        ) 

                        union all 

                        (select (rprs_id::text) as num_doc,
                        rprs_data_recarga as data_transacao,
                        NULL as tipo_pagto ,
                        (sum (rprs_valor)::real) as valor ,
                        count(1::int) as qtde_itens,
                        count(1::int) as qtde_produtos,
                        (sum(rprs_valor - (rprs_valor * rprs_comissao_para_repasse/100))) as repasse ,
                        'RecargaRS'::text as tipo,
                        NULL::text as status

                        from tb_recarga_pedidos_rede_sim
                        where rprs_ug_id =  $id_lan 
                                and rprs_status='1'
                                and rprs_data_recarga between '$data_ultimo_balanco' and '$data_final_intervalo' 
                                and rprs_data_recarga is not null
                                group by rprs_id,rprs_data_recarga
                        ) 

                        union all 

                        (select (sprs_id::text) as num_doc,
                        sprs_data_seguro as data_transacao,
                        NULL as tipo_pagto ,
                        (sum (sprs_valor)::real) as valor ,
                        count(1::int) as qtde_itens,
                        count(1::int) as qtde_produtos,
                        (sum(sprs_valor - (0))) as repasse ,
                        'SeguroRS'::text as tipo,
                        NULL::text as status

                        from tb_seguro_pedidos_rede_sim
                        where sprs_ug_id =  $id_lan 
                                and sprs_status='1'
                                and sprs_data_seguro between '$data_ultimo_balanco' and '$data_final_intervalo' 
                                group by sprs_id,sprs_data_seguro
                        ) 

                        union all

                        (select numcompra::text as num_doc,
                        datainicio as data_transacao,
                        (case when iforma='A' then 10 when iforma='R' then 24 else iforma::int end ) as tipo_pagto, 
                        (sum (total/100 - taxas)::real) as valor, NULL as qtde_itens, NULL as qtde_produtos , NULL as repasse , 'BoletoPagtoOnline' as tipo, (case when tipo_cliente='LR' then 'Pre' when tipo_cliente='LO' then 'Pos' else '???' end) as status from tb_pag_compras where substr(tipo_cliente,1,1)='L' and idcliente=$id_lan and status=3 and datacompra between '$data_ultimo_balanco' and '$data_final_intervalo' group by numcompra::text, datainicio, tipo_pagto, tipo_cliente order by data_transacao ) 

                        union all

                        (select (bbc_documento::text) as num_doc,
                        bbc_data_inclusao as data_transacao,
                        cor_tipo_pagto as tipo_pagto,
                        (cor_venda_liquida::real) as valor,
                        NULL::int as qtde_itens,
                        NULL::int as qtde_produtos,
                        NULL::real as repasse,
                        'Corte'::text as tipo,
                        'Pos'::text as status

                        from cortes c inner join boleto_bancario_cortes as bbc on cor_bbc_boleto_codigo = bbc_boleto_codigo inner join dist_usuarios_games ug on c.cor_ug_id = ug.ug_id where ug.ug_id = $id_lan and c.cor_venda_liquida > 0 and bbc_data_inclusao between '$data_ultimo_balanco' and '$data_final_intervalo')

                        ) as venda 
                        group by venda.num_doc,venda.tipo_pagto,venda.data_transacao,venda.valor,tipo,repasse,status 
                        order by data_transacao desc  ";

                        echo (!$b_db_execute)?$cReturn."SQL Totais: ".$cReturn.$sql.$cReturn:"";

                        ///////// TRATEI VALORES PARA EVITAR DIVERGENCIAS ABAIXO DE 0,001/////////////////////////////
                        //////////<------------ ATENÇÃO AQUI DECIDIR A RESPEITO DE TODAS AS VARIAVEIS ---->///////////
                        if ($saldo_ultimo_balanco < $BALANCO_ZERO_FLOAT) {
                                $saldo_ultimo_balanco = 0;
                        }
                        echo (!$b_db_execute)?"     saldo_ultimo_balanco: $saldo_ultimo_balanco".PHP_EOL:"";
                        ///////////<---------- SE TODAS AS OUTRAS TAMBEM SERÃO TRATADAS ------------------>///////////
                        //////////////////////////////////////////////////////////////////////////////////////////////

                        $res_conta_lista = SQLexecuteQuery($sql);
                        //ver com o Reynaldo aqui se coloca comissao somente em vendas
                        $val_comissao = 0;
                        while ($info_conta_lista = pg_fetch_array($res_conta_lista)) {

                                switch ($info_conta_lista['tipo']) {

                                        case 'Venda':
                                                $val_vendas += $info_conta_lista['valor'];
                                                $val_comissao += $info_conta_lista['comissao'];
                                                $qtde_vendas++;
                                                break;

                                        case 'Boleto':
                                                $val_boletos += $info_conta_lista['valor'];
                                                $qtde_boletos++;
                                                break;

                                        case 'Corte':
                                                $val_cortes += $info_conta_lista['valor'];
                                                $qtde_cortes++;
                                                break;

                                        case 'BoletoPagtoOnline':
                                                $val_pag_online += $info_conta_lista['valor'];
                                                $qtde_pag_online++;
                                                break;

                                        case 'B2C':
                                                $val_vendas_b2c += $info_conta_lista['valor'];
                                                $val_comissao_b2c += $info_conta_lista['comissao'];
                                                $qtde_vendas_b2c++;
                                                $val_vendas += $info_conta_lista['valor'];
                                                $val_comissao += $info_conta_lista['comissao'];
                                                $qtde_vendas++;
                                                break;

                                        case 'RecargaRS':
                                                $val_vendas_redesim += $info_conta_lista['valor'];
                                                $val_comissao_redesim += $info_conta_lista['comissao'];
                                                $qtde_vendas_redesim++;
                                                $val_vendas += $info_conta_lista['valor'];
                                                $val_comissao += $info_conta_lista['comissao'];
                                                $qtde_vendas++;
                                                break;

                                        case 'SeguroRS':
                                                $val_vendas_redesim += $info_conta_lista['valor'];
                                                $val_comissao_redesim += $info_conta_lista['comissao'];
                                                $qtde_vendas_redesim++;
                                                $val_vendas += $info_conta_lista['valor'];
                                                $val_comissao += $info_conta_lista['comissao'];
                                                $qtde_vendas++;
                                                break;
                                }
                        }// fim while info_conta_lista pg_fetch_array

                        $valor_final = 0; /// setei para 0 para não haver poluição 
                        //// 2 -  PARTE DE CONFERENCIAS  : AQUI IREMOS FAZER AS CONTAS DE SUBTRAçÃO E SOMA, " TIPO_LAN = 1 - POS , 2 - PRE "

                        $s_resumo1 = ""; 
                        $s_resumo1 .= "   saldo ultimo balanco: ".number_format($saldo_ultimo_balanco,2).$cReturn;
                        $s_resumo1 .= "   saldo ultimo pos balanco :". number_format($pos_valor_ultimo_balanco,2).$cReturn;
                        $s_resumo1 .= "   val vendas :".number_format($val_vendas,2).$cReturn;
                        $s_resumo1 .= "   val pag online :".number_format($val_pag_online,2).$cReturn;
                        $s_resumo1 .= "   val cortes :".number_format($val_cortes,2).$cReturn;
                        $s_resumo1 .= "   val boletos :".number_format($val_boletos,2).$cReturn;
                        $s_resumo1 .= "   val comissao :".number_format($val_comissao,2).$cReturn;
                        $s_resumo1 .= "   val b2c :".number_format($val_vendas_b2c,2).$cReturn;
                        $s_resumo1 .= "   val rede sim :".number_format($val_vendas_redesim,2).$cReturn;

                        $s_resumo1 .= "  ".$cSpan02."($nrows) nVendas: $qtde_vendas venda(s), nBoletos: $qtde_boletos boleto(s), $qtde_cortes corte(s), PagOnline: $qtde_pag_online pagamento(s) online,  Total: $total".$cSpanEnd.$cReturn;

                        $s_resumo .= $s_resumo1;

                        if ( $tipo_lan == 1 ) {	///// SE A LANHOUSE FOR POS

                                $saldo_pos = ($limite - $val_vendas);
                                $saldo = $saldo_pos;				

                                //$limite_ultimo_balanco
                                /// O SALDO POS É O VALOR QUE RESULTA DA SOBRA ENTRE O QUE JA FOI GASTO DO LIMITE
                                $valor_final =  ($pos_valor_ultimo_balanco - $val_vendas) + ($val_pag_online + $val_cortes + $val_boletos + $val_comissao); 

                                //// APARTIR da 13ª casa depois da virgula esta havendo diferenças entre os numeros DECIDIR MAIS TARDE UMA SOLUÇÃO 
                                $s_resumo .= "VALOR FINAL CALCULADO DE LIMITE: ".number_format($valor_final,2). " -----> "." LIMITE ATUAL DO PERFIL : ".number_format($saldo_pos,2).$cReturn.$cReturn;	
                                if ($valor_final != $saldo_pos) {

                                        $s_resumo .= $cFontAlertRed."HOUVE FALHA NO LIMITE !!!!!! ".$cFontAlertEnd.$cReturn;
                                        $s_resumo .= "Cortes : $val_cortes".$cReturn."vendas: $val_vendas".$cReturn."--------------".$cReturn."$valor_final";

                                        $db_resultado = 4; // 4 resultado falha na contagem do limite
                                } else {
                                        $s_resumo .= $cFontAlertBlue." O LIMITE ESTA CORRETO !!!!!!! ".$cFontAlertEnd.$cReturn;
                                        $db_resultado = 3; // 3 resultado ok o limite esta correto
                                }			
                        } else {	///// SE A LANHOUSE FOR PRE

                                $saldo = $ug_perfil_saldo;

                                $valor_final = ($saldo_ultimo_balanco - $val_vendas) + ($val_pag_online + $val_cortes + $val_boletos + $val_comissao); 

                                if ($valor_final != $ug_perfil_saldo) {
                                        $s_resumo .= $cFontAlertGreen."VALOR FINAL CALCULADO DE SALDO: ".$valor_final. " -----> "." SALDO ATUAL DO PERFIL : ".$ug_perfil_saldo."".$cFontAlertEnd.$cReturn;
                                        $s_resumo .= "Boletos : $val_boletos".$cReturn."vendas: $val_vendas".$cReturn." -------------- ".$cReturn."$valor_final";
                                        $s_resumo .= $cFontAlertRed."HOUVE FALHA NO SALDO !!!!!! ".$cFontAlertEnd.$cReturn;
                                        $db_resultado = 2; // 2 resultado falha na contagem do saldo
                                } else {

                                        $s_resumo .= $cFontAlertBlue." O SALDO ESTA CORRETO !!!!!!! ".$cFontAlertEnd.$cReturn;
                                        $db_resultado = 1; // 1 saldo correto
                                }
                        } /// fim if tipo lan

                } // fim 	if(!$b_cadastra_novo_balanco) 

                echo (!$b_db_execute)?$s_resumo."".$cReturn:"";
                echo (!$b_db_execute)?"SOMA: [".($qtde_vendas+$qtde_boletos+$qtde_cortes+$qtde_pag_online+$qtde_saldo_limite)."]".PHP_EOL:"";
                echo (!$b_db_execute)?"qtde_vendas: [".$qtde_vendas."]".PHP_EOL:"";
                echo (!$b_db_execute)?"qtde_boletos: [".$qtde_boletos."]".PHP_EOL:"";
                echo (!$b_db_execute)?"qtde_cortes: [".$qtde_cortes."]".PHP_EOL:"";
                echo (!$b_db_execute)?"qtde_pag_online: [".$qtde_pag_online."]".PHP_EOL:"";
                echo (!$b_db_execute)?"qtde_saldo_limite: [".$qtde_saldo_limite."]".PHP_EOL:"";
                echo (!$b_db_execute)?"val_comissao: [".$val_comissao."]".PHP_EOL:"";

                if((($qtde_vendas+$qtde_boletos+$qtde_cortes+$qtde_pag_online+$qtde_saldo_limite)>0)||$b_cadastra_novo_balanco) {
                        echo (!$b_db_execute)?$cReturn."  +++++  será inserido o balanco (qtde_total ".($qtde_vendas+$qtde_boletos+$qtde_cortes+$qtde_pag_online+$qtde_saldo_limite).").".$cReturn:"";

                        $saldo = (!$saldo)?0:$saldo;
                        $total = (!$total)?0:$total;

                        $query_insert = "insert into dist_balancos (db_ug_id, db_saldo, db_limite, db_tipo_lan, db_data_balanco, db_valor_balanco, db_qtde_cortes, db_qtde_boletos, db_qtde_vendas, db_qtde_pagonline, db_resultado, db_val_boletos, db_val_vendas, db_val_cortes, db_val_pag_online ) values ( ";
                        $query_insert .= "'".$id_lan."', ";
                        $query_insert .= "'".$saldo."', ";
                        $query_insert .= "'".$limite."', ";
                        $query_insert .= "'".$tipo_lan."', ";
                        $query_insert .= "CURRENT_TIMESTAMP, ";
                        $query_insert .= "'".$total."', ";
                        $query_insert .= " ".$qtde_cortes.", ";
                        $query_insert .= " ".$qtde_boletos.", ";
                        $query_insert .= " ".$qtde_vendas.", ";
                        $query_insert .= " ".$qtde_pag_online.", ";
                        $query_insert .= " ".$db_resultado.", ";
                        $query_insert .= " ".$val_boletos.", ";
                        $query_insert .= " ".$val_vendas.", ";
                        $query_insert .= " ".$val_cortes.", ";
                        $query_insert .= " ".$val_pag_online.")";

                        echo (!$b_db_execute)?$cReturn."SQL Insert: ".$cFontAlertGreen.$query_insert."".$cFontAlertEnd.$cReturn:"";

                        if($b_db_execute) {
                                SQLexecuteQuery($query_insert);
                                echo "  BALANÇO CADASTRADO OK\n  $query_insert".$cReturn;
                        } else {
                                echo "  SEM CADASTRO DE BALANÇOS".$cReturn;
                        }

                        $b_insert = true;

                        $s_resumo .= $cSpan01."Data: ".date("Y-m-d H:i:s"). ",  ID PDV: ".$id_lan. ", Saldo: ".$saldo. ",  Limite: ".$limite. "- Saldo Total: ".$total."".$cSpanEnd.$cReturn;
                        $str_lista_lans .= $rs_row['ug_id']." - ".$rs_row['ug_login']."".$cReturn;
                        $n_lista_lans++;

                        $nrows++;
                } else {
                        $s_resumo .= "  ---------  NADA será inserido no balanco - [$qtde_vendas + $qtde_boletos + $qtde_cortes + $qtde_pag_online] zerado.".$cSpanEnd.$cReturn;
                }

        } else {
                $s_resumo .= $cHr2."Nenhum balanço encontrado ou criado para o PDV: ".$id_lan.$cFontAlertRed." Não será cadastrado Balanco para ela.".$cFontAlertEnd.$cReturn;
        }

        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                if($b_db_execute) {
                        $ret = SQLexecuteQuery($sql);
                }
                if(!$ret) $msg = "Erro ao comitar transação.".PHP_EOL;
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                if($b_db_execute) {
                        $ret = SQLexecuteQuery($sql);
                }
                if(!$ret) $msg = "Erro ao dar rollback na transação.".PHP_EOL;
        }

        echo (!$b_db_execute)?$s_resumo."".$cReturn:"";
        
        // Clear errors
        $msg = "";
        
	echo "Elapsed time: ".number_format(getmicrotime() - $time_start_lh, 2, ',', '.')."s".$cReturn;
	flush();
        ob_flush();
        
}// end while ($rs_row = pg_fetch_array($rs)) 

echo str_repeat("-",80).PHP_EOL;
echo "n_lista_lans: ".$n_lista_lans." lans com novos balanços".$cReturn;
echo "str_lista_lans: ".$cReturn.$str_lista_lans."".$cReturn;

echo "Termina cadastro iniciado em '$data_de_hoje_start' e terminado ".date("Y-m-d H:i:s")."".$cReturn;
echo "Elapsed time : " . number_format(getmicrotime() - $time_start, 2, '.', '.') . " segundos , nrows=$nrows, i=$i (".number_format( $nrows/(getmicrotime() - $time_start), 2, '.', '.')." cadastros/s, (".number_format( $i/(getmicrotime() - $time_start), 2, '.', '.')." processadas/s)".$cReturn;
echo "Projeção para ".$total_considerado." registros: ".number_format( $total_considerado*(getmicrotime() - $time_start)/$i, 2, '.', '.')."s".$cReturn;
echo $cHr3;

//Fechando Conexão
pg_close($connid);

?>

