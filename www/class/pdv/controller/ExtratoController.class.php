<?php
/**
 * Classe para as regras de negocio das vendas
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 16-07-2015
 */
require_once DIR_CLASS . 'util/Util.class.php';
require_once DIR_CLASS . 'pdv/controller/HeaderController.class.php';

$_PaginaOperador2Permitido = 54; 
$pagina_titulo = "Saldo";

class ExtratoController extends HeaderController{
    public $raiz_do_projeto;

    public function __construct(){
        $this->objBanner = new BannerBO;
        
        parent::__construct();
        
        if(!$this->lanHouse) 
        {
            $this->accessDenied();
        }
    }

    private function addSqlParam(&$params, $value){
        $params[] = $value;
        return '$' . count($params);
    }

    private function addSqlCodigoFilter(&$sql, &$params, $column, $codigo){
        if($codigo !== null){
            $phCodigo = $this->addSqlParam($params, (int) $codigo);
            $sql .= " and {$column} = {$phCodigo}";
        }
    }

    private function addSqlDateRangeFilter(&$sql, &$params, $column, $dataIni, $dataFim){
        if($dataIni !== null && $dataFim !== null){
            $phDataIni = $this->addSqlParam($params, $dataIni);
            $phDataFim = $this->addSqlParam($params, $dataFim);
            $sql .= " and {$column} >= {$phDataIni} and {$column} <= {$phDataFim}";
        }
    }

    private function getExtratoFiltros(){
        $tf_v_codigo = null;
        if (isset($_POST['tf_v_codigo']) && ctype_digit((string)$_POST['tf_v_codigo'])) {
            $tf_v_codigo = (int) $_POST['tf_v_codigo'];
        }

        $data_inclusao_ini = null;
        $data_inclusao_fim = null;
        if (
            !empty($_POST['tf_v_data_inclusao_ini']) &&
            !empty($_POST['tf_v_data_inclusao_fim']) &&
            verifica_data($_POST['tf_v_data_inclusao_ini']) != 0 &&
            verifica_data($_POST['tf_v_data_inclusao_fim']) != 0
        ) {
            $data_inclusao_ini = formata_data($_POST['tf_v_data_inclusao_ini'], 1) . " 00:00:00";
            $data_inclusao_fim = formata_data($_POST['tf_v_data_inclusao_fim'], 1) . " 23:59:59";
        }

        $ugo_login = "";
        if (!empty($_POST['ugo_login'])) {
            $ugo_login_tmp = strtoupper(trim($_POST['ugo_login']));
            if (preg_match('/^[A-Z0-9_.@-]{1,100}$/', $ugo_login_tmp)) {
                $ugo_login = $ugo_login_tmp;
            }
        }

        return array($tf_v_codigo, $data_inclusao_ini, $data_inclusao_fim, $ugo_login);
    }
    
    public function init($registros = 20, $p = 0){
        //paginacao
        $intervaloMaximoMeses = 6;
         
        if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser']))
        {
            $usuarioId = $this->usuarios->getId();
 
            $data_limit = $this->usuarios->getDataInclusao();

            $risco = $this->usuarios->getRiscoClassif();

            if ($risco == '2') 
            {
                $saldo_conta = number_format($this->usuarios->getPerfilSaldo(),2,',','.');
            } else 
            {
                $saldo_conta = number_format($this->usuarios->getPerfilLimite(),2,',','.');
            }
            // Fixa a ordem como descendente 
            $op_extrato = "desc" ;
            
            
            $ordem = $op_extrato;
            $data_limit = formata_data($data_limit,1);
            $data_limit = strtotime($data_limit);
	}	

	        ////////////////////////////////////////////////////////////////////////////////////////
	$msg = "";
        list($tf_v_codigo, $data_inclusao_ini, $data_inclusao_fim, $ugo_login) = $this->getExtratoFiltros();

        ////// QUERY GERAL  //////////////////////////////////////////////////////////////////////////////////////
        $sql_params = array();
        $phUsuarioId = $this->addSqlParam($sql_params, (int) $usuarioId);
        $phStatusVenda = $this->addSqlParam($sql_params, (string) $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']);

	$sql = "select num_doc, pedido_parceiro, tipo_pagto, data, valor, sum(valor - repasse) as comissao, repasse, tipo, operador,resultado from (
                (select (vg.vg_id::text) as num_doc,
				 vpdv.id_pedido_parceiro as pedido_parceiro,
                 vg.vg_data_inclusao as data,
                 vg.vg_pagto_tipo as tipo_pagto ,
                 sum(vgm.vgm_valor * vgm.vgm_qtde) as valor ,
                 sum(vgm.vgm_qtde) as qtde_itens,
                 count(*) as qtde_produtos,
                 sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse ,
                 'Venda'::text as tipo,";
        if(!empty($ugo_login)){
            $sql .= " ugo_login as operador,";
        } else {
            $sql .= " '' as operador,";
        }
        $sql .= "
                 NULL::smallint as resultado

                from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
						 left join pedidos_api_pdv vpdv on vg.vg_id = vpdv.id_pedido_eprepag ";
        if(!empty($ugo_login)){
            $sql .= "
                        inner join dist_usuarios_games_operador_log ugol on ugol.ugol_vg_id = vg.vg_id
                        inner join dist_usuarios_games_operador ugo on ugol.ugol_ugo_id = ugo.ugo_id ";
        }
        $sql .= " 
                where vg.vg_ug_id= {$phUsuarioId} and vg.vg_ultimo_status={$phStatusVenda} ";
        $this->addSqlCodigoFilter($sql, $sql_params, "vg.vg_id", $tf_v_codigo);
        $this->addSqlDateRangeFilter($sql, $sql_params, "vg.vg_data_inclusao", $data_inclusao_ini, $data_inclusao_fim);
        if(!empty($ugo_login)){
            $phUgoLogin = $this->addSqlParam($sql_params, $ugo_login);
            $sql .= " and ugo.ugo_login = {$phUgoLogin} ";
        }
        $sql .= " group by num_doc , pedido_parceiro, data , tipo_pagto , vg.vg_ultimo_status, vg.vg_usuario_obs , operador
                 ) ";

        if(empty($ugo_login)){
            $phFormaPagamentoPix = $this->addSqlParam($sql_params, (string) $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']);
            $phPagamentoPixNumeric = $this->addSqlParam($sql_params, (int) $GLOBALS['PAGAMENTO_PIX_NUMERIC']);
            $phStatusAtivo = $this->addSqlParam($sql_params, '1');

            $sql .= " 
                union all
                (select (vg_id::text) as num_doc,
				0 as pedido_parceiro,
                bol_importacao as data ,
                vg_pagto_tipo as tipo_pagto ,
                sum (bbg_valor - bbg_valor_taxa) as valor ,
                NULL as qtde_itens,
                NULL as qtde_produtos ,
                NULL as repasse ,
                'Boleto' as tipo,
                '' as operador,
                NULL::smallint as resultado
                from boletos_pendentes, bancos_financeiros, tb_dist_venda_games, dist_boleto_bancario_games
                where (bol_banco = bco_codigo) and (bol_venda_games_id=vg_id) and (bco_rpp = 1) and vg_ug_id = {$phUsuarioId}
                and vg_ultimo_status={$phStatusVenda} and bol_documento LIKE '4%' and bbg_ug_id = {$phUsuarioId}
                and bbg_vg_id = vg_id and bol_valor > 0 ";
            $this->addSqlDateRangeFilter($sql, $sql_params, "bol_importacao", $data_inclusao_ini, $data_inclusao_fim);
            $sql .= "
                    group by vg_id,vg_data_inclusao,vg_pagto_tipo,bol_importacao 
                    order by bol_importacao  )

                    union all 

                    (select 
                    (bbc_documento::text) as  num_doc,
					0 as pedido_parceiro,
                    bbc_data_inclusao as data ,
                    cor_tipo_pagto as  tipo_pagto , 
                    cor_venda_liquida as  valor,
                    NULL as qtde_itens,
                    NULL as qtde_produtos,
                    NULL as repasse,
                    'Corte' as tipo,
                    '' as operador,
                    NULL::smallint as resultado
                    from cortes c inner join boleto_bancario_cortes as bbc on cor_bbc_boleto_codigo = bbc_boleto_codigo
                    where c.cor_ug_id = {$phUsuarioId} and c.cor_venda_liquida > 0 and c.cor_status=3 ";
            $this->addSqlDateRangeFilter($sql, $sql_params, "bbc_data_inclusao", $data_inclusao_ini, $data_inclusao_fim);
            $sql .= "
                    )

                    union all 

                    (select idvenda::text as num_doc,
					0 as pedido_parceiro,
                    datainicio as data,  
                    (case when iforma='A' then 10 when iforma={$phFormaPagamentoPix} then {$phPagamentoPixNumeric} else iforma::int end ) as tipo_pagto,  
                    sum (total/100 - taxas) as valor, 
                    NULL as qtde_itens, 
                    NULL as qtde_produtos , 
                    NULL as repasse , 
                    'BoletoPagtoOnline' as tipo, 
                    '' as operador,
                    NULL::smallint as resultado

                    from tb_pag_compras
                    where substr(tipo_cliente,1,1)='L' and idcliente={$phUsuarioId} and status=3 ";
            $this->addSqlDateRangeFilter($sql, $sql_params, "datainicio", $data_inclusao_ini, $data_inclusao_fim);

            $sql .= "group by idvenda::text, datainicio, tipo_pagto, tipo_cliente 
                )

                     union all 

                    ( 
                    select 
                    (rp_vg_id::text) as num_doc,
					0 as pedido_parceiro,
                    rp_data_recarga as data , 
                    NULL as tipo_pagto, 
                    rp_valor as valor, 
                    1::smallint as qtde_itens, 
                    1::smallint as qtde_produtos, 
                    rp_valor as repasse, 
                    'Recarga Celular' as tipo,
                    '' as operador, 
                    NULL::smallint as resultado 
                    from tb_recarga_pedidos where rp_ug_id = {$phUsuarioId} and rp_status={$phStatusAtivo} ";
            $this->addSqlDateRangeFilter($sql, $sql_params, "rp_data_recarga", $data_inclusao_ini, $data_inclusao_fim);
            $sql .= " )

                    union all 

                    ( 
                    select 
                    (rprs_vg_id::text) as num_doc, 
					0 as pedido_parceiro,
                    rprs_data_recarga as data , 
                    NULL as tipo_pagto, 
                    rprs_valor as valor, 
                    1::smallint as qtde_itens, 
                    1::smallint as qtde_produtos, 
                    (rprs_valor - (rprs_valor * rprs_comissao_para_repasse/100)) as repasse, 
                    'Recarga Celular' as tipo,
                    '' as operador, 
                    NULL::smallint as resultado 
                    from tb_recarga_pedidos_rede_sim where rprs_ug_id = {$phUsuarioId} and rprs_status={$phStatusAtivo} ";
            $this->addSqlDateRangeFilter($sql, $sql_params, "rprs_data_recarga", $data_inclusao_ini, $data_inclusao_fim);

            $sql .= " and rprs_data_recarga is not null
            
            )

            union all 

            ( 
            select 
            (sprs_vg_id::text) as num_doc, 
			0 as pedido_parceiro,
            sprs_data_seguro as data , 
            NULL as tipo_pagto, 
            sprs_valor as valor, 
            1::smallint as qtde_itens, 
            1::smallint as qtde_produtos, 
            sprs_valor as repasse, 
            'Seguro' as tipo,
            '' as operador, 
            NULL::smallint as resultado 
            from tb_seguro_pedidos_rede_sim where sprs_ug_id = {$phUsuarioId} and sprs_status={$phStatusAtivo} ";
            $this->addSqlDateRangeFilter($sql, $sql_params, "sprs_data_seguro", $data_inclusao_ini, $data_inclusao_fim);

            $sql .= " 
                    )

                    union all 

                    ( 
                    select 
                    (vb2c_vg_id::text) as num_doc, 
					0 as pedido_parceiro,
                    \"vb2c_dataVenda\" as data , 
                    NULL as tipo_pagto, 
                    \"vb2c_precoServico\" as valor, 
                    1::smallint as qtde_itens, 
                    1::smallint as qtde_produtos, 
                    (\"vb2c_precoServico\" - (\"vb2c_precoServico\" * vb2c_comissao_para_repasse/100)) as repasse, 
                    'B2C' as tipo,
                    '' as operador, 
                    NULL::smallint as resultado 
                    from tb_vendas_b2c where vb2c_ug_id_lan = {$phUsuarioId} and vb2c_status={$phStatusAtivo} ";
            $this->addSqlDateRangeFilter($sql, $sql_params, "\"vb2c_dataVenda\"", $data_inclusao_ini, $data_inclusao_fim);

            $sql .= "   ) ";
        } //end if(empty($_POST['ugo_login']))
        $sql .= "
                    ) as venda

                    group by venda.num_doc,pedido_parceiro, venda.tipo_pagto,venda.data,venda.valor,tipo,repasse,operador,resultado 
                    order by data desc";

        $offset = ((int)$p - 1) * (int)$registros;
        if($offset < 0) $offset = 0;
        $limit = (int)$registros;
        if($limit <= 0) $limit = 20;

        $phOffset = $this->addSqlParam($sql_params, $offset);
        $phLimit = $this->addSqlParam($sql_params, $limit);
        $sql .= " offset {$phOffset} limit {$phLimit}";

        $rs_extrato = SQLexecuteQueryParams($sql, $sql_params);
		
		/*$ff= fopen("/www/arquivos_gerados/logs/teste_extrato.php","a+");
		fwrite($ff, $sql."\r");
		fwrite($ff, str_repeat("*", 50)."\r");
		fclose($ff);*/

        $num = pg_num_rows($rs_extrato);
        
        $arrExtratos = array();
                
        while( $extrato_info = pg_fetch_array($rs_extrato) )	
        {
            //atribui os valores
            $extrato = array();

            if(empty($ugo_login) && $extrato_info['tipo'] == "Venda")
            {
            
	                $sql_operador = 
                "select 
                    ugo_id, ugo_login as operador
                from 
                    dist_usuarios_games_operador_log ugol 
                inner join 
                    dist_usuarios_games_operador ugo on ugol.ugol_ugo_id = ugo.ugo_id 
                where 
                    ugol.ugol_vg_id = $1";
//echo "$sql_operador<br>\n";
                $rs_operador = SQLexecuteQueryParams($sql_operador, array((int) $extrato_info['num_doc']));

                //$extrato['operador'] = $ugo_login;
                //$extrato['idOperador'] = $ugo_id;
                if($rs_operador && pg_num_rows($rs_operador ) > 0) {
                    $pg_operador = pg_fetch_array($rs_operador);
                    $extrato['operador'] = $pg_operador['operador'];
                } 
                else $extrato['operador'] = '';
            }//end if(!empty($_POST['ugo_login']))
            else {
                    $extrato['operador'] = $extrato_info['operador'];
            }
            
            
            $tipo = $extrato_info['tipo'];
            $tipo_pagto =  $extrato_info['tipo_pagto'];
            $data =  $extrato_info['data'];
            $valor =  $extrato_info['valor'];
			$pedidoAPI =  $extrato_info['pedido_parceiro'];
            $num_doc =  $extrato_info['num_doc'];
            $qtde_itens =  $extrato_info['qtde_itens'];
            $qtde_produtos =  $extrato_info['qtde_produtos'];
            $repasse =  $extrato_info['repasse'];
            $comissao = $extrato_info['comissao'];
            $status =  $extrato_info['status'];
            $resultado = $extrato_info['resultado'];
            $rows = "";

             //////////////////////////// BOLETO //////////////////////////////////////////////////
             //// s&oacute; desenha a linha se a data do boleto for maior que a data dos pedidos 
             // se boleto tiver a data maior que da data da compra de pins
            $data = formata_data_ts($data, 0, true, false);
            
            if (  $tipo == 'Boleto' ) 
            {
                // se tiver registro para carregar e desenhar a tabela 
                //////// VIEW DATAS E PRE&Ccedil;OS
                
                //////////////////////////////////////
                $extrato['tipo'] = "Boleto";
                $extrato['num_doc'] = $num_doc;
				$extrato['pedido_parceiro'] = (!empty($pedidoAPI) && $pedidoAPI != 0)?$pedidoAPI: "Não possui";
                $extrato['tipo_pagto'] = $tipo_pagto;
                $extrato['data_view'] = $data;
                $extrato['status'] = $balanco_title;
                $extrato['saldo_limite'] = "";
                $extrato['valor_view'] = $valor;
                $extrato['valor_venda'] = "";
                $extrato['comissao'] = "";
                $total_entrada += $valor;
            }// fim do if data do boleto
            //// s&oacute; desenha a linha se a data do boleto for maior que a data dos pedidos 
            // se boleto tiver a data maior que da data da compra de pins
            if($tipo == 'BoletoPagtoOnline')
            {
                //////////////////////////////////////
                $tipo_pagto_index_img = (($tipo_pagto==10)?"A":$tipo_pagto);
                
                $extrato['tipo'] = "BoletoPagtoOnline";
                $extrato['num_doc'] = $num_doc;
				$extrato['pedido_parceiro'] = (!empty($pedidoAPI) && $pedidoAPI != 0)?$pedidoAPI: "Não possui";
                $extrato['tipo_pagto'] = $tipo_pagto_index_img;
                $extrato['data_view'] = $data;
                $extrato['status'] = "";
                $extrato['saldo_limite'] = "";
                $extrato['valor_view'] = $valor;
                $extrato['valor_venda'] = "";
                $extrato['comissao'] = "";
                $total_entrada += $valor;
            }// fim do if data do boleto
            //////////////////////////// CORTE SEMANAL //////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////////////////////////
            /// s&oacute; desenha a linha se a data do corte for maior que a data dos pedidos 
            //		if ($risco == 1) {	
            /// se boleto tiver a data maior que da data da compra de pins
            if ( $tipo == 'Corte' ) 
            {
                $extrato['tipo'] = "Corte";
                $extrato['num_doc'] = "";
				$extrato['pedido_parceiro'] = (!empty($pedidoAPI) && $pedidoAPI != 0)?$pedidoAPI: "Não possui";
                $extrato['tipo_pagto'] = $tipo_pagto;
                $extrato['data_view'] = $data;
                $extrato['status'] = $balanco_title;
                $extrato['saldo_limite'] = "";
                $extrato['valor_view'] = $valor;
                $extrato['valor_venda'] = "";
                $extrato['comissao'] = "";

                $total_entrada += $valor;
            }// fim do if data do corte 
            /////////////////////////////// VENDA /////////////////////////
            /// compara se a data do corte &eacute; menor que a do pedido ent&atilde;o desenha o pedido
            if ( $tipo == 'Venda' ) 
            {		
                $extrato['tipo'] = "Venda";
                $extrato['num_doc'] = $num_doc;
				$extrato['pedido_parceiro'] = (!empty($pedidoAPI) && $pedidoAPI != 0)?$pedidoAPI: "Não possui";
                $extrato['tipo_pagto'] = "";
                $extrato['data_view'] = $data;
                $extrato['status'] = "";
                $extrato['saldo_limite'] = "";
                $extrato['valor_view'] = $rows;
                $extrato['valor_venda'] = $valor;
                $extrato['comissao'] = $comissao;

                $total_entrada += $valor;
                $total_comissao += $comissao;
            }	
            /////////////////////////////// RECARGA CELULAR ///////////////////////// 
            if ( $tipo == 'Recarga Celular' ) 
            {
                $extrato['tipo'] = "Recarga Celular";
				$extrato['pedido_parceiro'] = (!empty($pedidoAPI) && $pedidoAPI != 0)?$pedidoAPI: "Não possui";
                $extrato['num_doc'] = $num_doc;
                $extrato['tipo_pagto'] = "";
                $extrato['data_view'] = $data;
                $extrato['status'] = "";
                $extrato['saldo_limite'] = "";
                $extrato['valor_view'] = $rows;
                $extrato['valor_venda'] = $valor;
                $extrato['comissao'] = $comissao;

                $total_saida += $valor;
                $total_comissao += $comissao;
            }
                /////////////////////////////// SEGURO ///////////////////////// 
            if ( $tipo == 'Seguro' ) 
            {
                $extrato['tipo'] = "Seguro";
				$extrato['pedido_parceiro'] = (!empty($pedidoAPI) && $pedidoAPI != 0)?$pedidoAPI: "Não possui";
                $extrato['num_doc'] = $num_doc;
                $extrato['tipo_pagto'] = "";
                $extrato['data_view'] = $data;
                $extrato['status'] = "";
                $extrato['saldo_limite'] = "";
                $extrato['valor_view'] = $rows;
                $extrato['valor_venda'] = $valor;
                $extrato['comissao'] = $comissao;

                $total_saida += $valor;
                $total_comissao += $comissao;
            }		
            /////////////////////////////// B2C ///////////////////////// 
            if ( $tipo == 'B2C' ) 
            { 
                $extrato['tipo'] = "B2C";
				$extrato['pedido_parceiro'] = (!empty($pedidoAPI) && $pedidoAPI != 0)?$pedidoAPI: "Não possui";
                $extrato['num_doc'] = $num_doc;
                $extrato['tipo_pagto'] = "";
                $extrato['data_view'] = $data;
                $extrato['status'] = "";
                $extrato['saldo_limite'] = "";
                $extrato['valor_view'] = $rows;
                $extrato['valor_venda'] = $valor;
                $extrato['comissao'] = $comissao;

                $total_saida += $valor;
                $total_comissao += $comissao;
            }		
            //////////////////////////// /// BALANCO //////////////////////////////////////////////////
            // desenha a linha do balanco se a data do balanco for maior que as datas do corte, do pedido e do boleto

            if ($tipo == 'Balanco' ) 
            {
                if ($status == 1) 
                {
                        $balanco_img = '/imagens/pdv/balanco_POS.png';
                        $balanco_title = 'Lan Pós';
                } else 
                {
                        $balanco_img = '/imagens/pdv/balanco_PRE.png';
                        $balanco_title = 'Lan Pré';
                }

                /////////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
                $extrato['tipo'] = "B2C";
				$extrato['pedido_parceiro'] = (!empty($pedidoAPI) && $pedidoAPI != 0)?$pedidoAPI: "Não possui";
                $extrato['num_doc'] = $num_doc;
                $extrato['tipo_pagto'] = "";
                $extrato['data_view'] = $data;
                $extrato['status'] = $balanco_img;
                $extrato['saldo_limite'] = $valor;
                $extrato['valor_view'] = ""; //credito
                $extrato['valor_venda'] = ""; //debito
                $extrato['comissao'] = "";
                // O P ? o numero de registros atual
            }// fim do if balanco				
            
            if($this->usuarios->getRiscoClassif()==2)
            {
                if($extrato['valor_venda'] != "")
                    $extrato['transacao'] = "Venda";
                elseif ($extrato['valor_view'] != "")
                    $extrato['transacao'] = "Depósito";
            }else{
                if($extrato['valor_venda'] != "")
                    $extrato['transacao'] = "Venda";
                elseif ($extrato['valor_view'] != "")
                    $extrato['transacao'] = "Boleto";
            }
            
            $arrExtratos['pedidos'][] = $extrato;
            $p++;
        }// fim do while
        
        return $arrExtratos;
//    }
    }
    
    public function getTotalEntradaSaidaComissao(){
        $usuarioId = (int) $this->usuarios->getId();
        list($tf_v_codigo, $data_inclusao_ini, $data_inclusao_fim, $ugo_login) = $this->getExtratoFiltros();

        /// 1- VENDAS /////////////////////////////////////////////////////////////////////
        $sql_params = array();
        $phUsuarioId = $this->addSqlParam($sql_params, $usuarioId);
        $phStatusVenda = $this->addSqlParam($sql_params, (string) $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']);
        $phStatusAtivo = $this->addSqlParam($sql_params, '1');

        $sql  = "select 
			sum(qtde) as qtde_total,
                        sum(venda) as venda, 
                        sum(valor) as total_valor, 
                        sum(valor - repasse ) as comissao  
                from 
                      ( select 
                                count(distinct(vg_id)) as qtde,
                                sum(vgm.vgm_valor * vgm.vgm_qtde) as valor ,
                                sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse,
                                sum(vgm.vgm_valor * vgm.vgm_qtde ) as venda 
                        from 
                                tb_dist_venda_games vg 
                        inner join 
                                tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id ";
        if(!empty($ugo_login)){
                $sql .= "
                        inner join dist_usuarios_games_operador_log ugol on ugol.ugol_vg_id = vg.vg_id
                        inner join dist_usuarios_games_operador ugo on ugol.ugol_ugo_id = ugo.ugo_id
                        ";
        }
        $sql .= " 
                        where 
                                vg_ug_id = {$phUsuarioId} and 
                                vg.vg_ultimo_status = {$phStatusVenda} ";
        $this->addSqlCodigoFilter($sql, $sql_params, "vg.vg_id", $tf_v_codigo);
        $this->addSqlDateRangeFilter($sql, $sql_params, "vg.vg_data_inclusao", $data_inclusao_ini, $data_inclusao_fim);

        if(!empty($ugo_login)){
            $phUgoLogin = $this->addSqlParam($sql_params, $ugo_login);
            $sql .= " and ugo.ugo_login = {$phUgoLogin} ";
        }
        else{
            $sql .= "   UNION ALL 
                            select 
                                count(vb2c_vg_id) as qtde,
                                sum(\"vb2c_precoServico\") as valor ,
                                sum(\"vb2c_precoServico\" - (\"vb2c_precoServico\" * vb2c_comissao_para_repasse/100)) as repasse,
                                sum(\"vb2c_precoServico\") as venda 
                            from 
                                tb_vendas_b2c 
                            where 
                                vb2c_ug_id_lan = {$phUsuarioId} and 
                                vb2c_status = {$phStatusAtivo} ";
            $this->addSqlCodigoFilter($sql, $sql_params, "vb2c_vg_id", $tf_v_codigo);
            $this->addSqlDateRangeFilter($sql, $sql_params, "\"vb2c_dataVenda\"", $data_inclusao_ini, $data_inclusao_fim);
            
            $sql .= "   union all 
                        select 
                            count(sprs_vg_id) as qtde,
                            sum(sprs_valor) as valor ,
                            sum(sprs_valor) as repasse, 
                            sum(sprs_valor) as venda 
                        from tb_seguro_pedidos_rede_sim where sprs_ug_id = {$phUsuarioId} and sprs_status = {$phStatusAtivo} ";
            $this->addSqlCodigoFilter($sql, $sql_params, "sprs_vg_id", $tf_v_codigo);
            $this->addSqlDateRangeFilter($sql, $sql_params, "sprs_data_seguro", $data_inclusao_ini, $data_inclusao_fim);
            
            $sql .= "   union all 
                        select 
                            count(rprs_vg_id) as qtde,
                            sum(rprs_valor) as valor ,
                            sum(rprs_valor - (rprs_valor * rprs_comissao_para_repasse/100)) as repasse, 
                            sum(rprs_valor) as venda 
                        from tb_recarga_pedidos_rede_sim where rprs_ug_id = {$phUsuarioId} and rprs_status = {$phStatusAtivo} ";
            $this->addSqlCodigoFilter($sql, $sql_params, "rprs_vg_id", $tf_v_codigo);
            $this->addSqlDateRangeFilter($sql, $sql_params, "rprs_data_recarga", $data_inclusao_ini, $data_inclusao_fim);
            
            $sql .= "   union all 
                    select 
                        count(rp_vg_id) as qtde,
                        sum(rp_valor) as valor ,
                        sum(rp_valor) as repasse,
                        sum(rp_valor) as venda 
                    from tb_recarga_pedidos where rp_ug_id = {$phUsuarioId} and rp_status = {$phStatusAtivo} ";
            $this->addSqlCodigoFilter($sql, $sql_params, "rp_vg_id", $tf_v_codigo);
            $this->addSqlDateRangeFilter($sql, $sql_params, "rp_data_recarga", $data_inclusao_ini, $data_inclusao_fim);
        }
        
        $sql .= ") as total ";
        $rs_vendas = SQLexecuteQueryParams($sql, $sql_params);
        $rs_vendas_row = pg_fetch_array($rs_vendas);
        
        $total_final_saida = $rs_vendas_row['total_valor'];
        $total_final_comissao = $rs_vendas_row['comissao'];
        $registros_total = $rs_vendas_row['qtde_total'];
        //////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////////
        
        if(empty($ugo_login)){
                /// 2- BOLETOS PRE////////////////////////////////////////////////////////////////
                $sql_params_boleto = array();
                $phUsuarioIdBoleto = $this->addSqlParam($sql_params_boleto, $usuarioId);
                $phStatusVendaBoleto = $this->addSqlParam($sql_params_boleto, (string) $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']);
                $sql = "select 
                            sum(qtde) as qtde_total,
                            sum(bbg_valor)as valor ,
                            sum(bbg_valor_taxa) as taxa 
                        from 
                            (
                                select 
                                    count(distinct(vg_id)) as qtde,
                                    bbg_valor,
                                    bbg_valor_taxa 
                                from 
                                    boletos_pendentes, 
                                    bancos_financeiros, 
                                    tb_dist_venda_games, 
                                    dist_boleto_bancario_games 
                                where 
                                    (bol_banco = bco_codigo) and 
                                    (bol_venda_games_id=vg_id) and 
                                    (bco_rpp = 1) and 
                                    vg_ug_id = {$phUsuarioIdBoleto} and 
                                    vg_ultimo_status = {$phStatusVendaBoleto} and
                                    bol_documento LIKE '4%' and 
                                    bbg_ug_id = {$phUsuarioIdBoleto} and 
                                    bbg_vg_id = vg_id";
                $this->addSqlDateRangeFilter($sql, $sql_params_boleto, "bol_importacao", $data_inclusao_ini, $data_inclusao_fim);
                $sql .= "
                                group by bbg_valor, bbg_valor_taxa
                        ) as total";
                $rs_boleto = SQLexecuteQueryParams($sql, $sql_params_boleto);
                $rs_vendas_row = pg_fetch_array($rs_boleto);

                $total_final_entrada =  $rs_vendas_row['valor'] - $rs_vendas_row['taxa'];
                $registros_total += $rs_vendas_row['qtde_total'];
                //////////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////////

                ////// 3- BOLETOS POS //////////////////////////////////////////////////////////////////
                $sql_params_corte = array();
                $phUsuarioIdCorte = $this->addSqlParam($sql_params_corte, $usuarioId);
                $sql = "select 
                            sum (cor_venda_liquida) as venda 
                        from 
                            (
                                select 
                                    cor_venda_liquida 
                                from cortes c 
                                    inner join boleto_bancario_cortes as bbc on cor_bbc_boleto_codigo = bbc_boleto_codigo  
                                where 
                                    c.cor_ug_id = {$phUsuarioIdCorte} ";
                $this->addSqlDateRangeFilter($sql, $sql_params_corte, "bbc_data_inclusao", $data_inclusao_ini, $data_inclusao_fim);
                $sql .= ") as corte_total";
                $rs_corte = SQLexecuteQueryParams($sql, $sql_params_corte);
                $rs_vendas_row = pg_fetch_array($rs_corte);

                $total_final_entrada += $rs_vendas_row['venda'];

                /// 3- PAGAMENTO ONLINE LAN PRE////////////////////////////////////////////////////////////////
                $sql_params_pag = array();
                $phUsuarioIdPag = $this->addSqlParam($sql_params_pag, $usuarioId);
                $sql = " select 
                            count(idvenda) as qtde_total,
                            sum (total/100 - taxas) as valor
                         from tb_pag_compras
                         where substr(tipo_cliente,1,1)='L' and idcliente = {$phUsuarioIdPag} and status=3 ";
                $this->addSqlDateRangeFilter($sql, $sql_params_pag, "datainicio", $data_inclusao_ini, $data_inclusao_fim);
                $rs_boleto = SQLexecuteQueryParams($sql, $sql_params_pag);
                $rs_vendas_row = pg_fetch_array($rs_boleto);
                $total_final_entrada +=  $rs_vendas_row['valor'];
                $registros_total += $rs_vendas_row['qtde_total'];
                //////////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////////
        }//end if(empty($_POST['ugo_login']))
        else {
            $total_final_entrada = 0;
        }

        $arrExtratos = array();
        $arrExtratos['total_final_entrada'] = $total_final_entrada;
        $arrExtratos['total_final_saida'] = $total_final_saida;
        $arrExtratos['total_final_comissao'] = $total_final_comissao;
        $arrExtratos['qtd_total_registros'] = $registros_total;
        return $arrExtratos;
    }
    
    public function getOperadores(){
        $sql = "select * from dist_usuarios_games_operador ugo where ugo.ugo_ug_id = $1";
        $sqlParams = array((int) $this->usuarios->getId());
        $res_count = SQLexecuteQueryParams($sql, $sqlParams);
        $total_table = pg_num_rows($res_count);
        $rs_operadores = SQLexecuteQueryParams($sql, $sqlParams);
        $arrOperadores = array();
        
        while($rs_operadores_row = pg_fetch_array($rs_operadores)){
            $arrOperadores[] = $rs_operadores_row['ugo_login'];
        }
        return $arrOperadores;
    }
}
