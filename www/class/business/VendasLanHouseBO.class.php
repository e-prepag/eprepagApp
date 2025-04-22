<?php

/**
 * Classe para as regras de negocio das vendas
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 08-06-2015
 */
if(!isset($raiz_do_projeto))
    $raiz_do_projeto = "/www/";
        
require_once $raiz_do_projeto."class/dao/VendasLanHouseDAO.class.php";
require_once $raiz_do_projeto."class/util/CSV.class.php";
require_once $raiz_do_projeto."class/util/Log.class.php";

class VendasLanHouseBO extends VendasLanHouseDAO{
    
    public function __construct(){
        
    }
    
    public function geraCsv($sql){
        $cabecalho = "Cód;Data Inclusão;Forma de Pagamento;Valor;Repasse;Qtde Itens;Qtde Produtos;Cód Usuário;Tipo Usuário;Nome Fantasia;Categoria;CPF;Conciliação; Status;Produtos";
        $cabecalhoProdutos = ";;;;;;;;;;;;;;Operadora;Produto;Valor de Face;Qtde";
        $espacamentoProdutos = ";;;;;;;;;;;;;;";
        global $raiz_do_projeto;
        $vendas = $this->getVendas($sql);
        
        if($vendas){
            $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto . "arquivos_gerados/csv/", $cabecalhoProdutos, $espacamentoProdutos);
            $objCsv->setCabecalho();
            $objCsv->addSubCabecalho();
            
            foreach($vendas as $venda){

                $arr = array();
    //            $objCsv->addEspacamento();
    //            $objCsv->addSubCabecalho();
    //            $objCsv->quebraLinha(); 
                $idVenda = $venda->getIdVenda();
                //inicio de preenchimento dos dados de venda
                $arr[] = $venda->getIdVenda();
                $arr[] = $venda->getDataInclusao();
                $arr[] = $venda->getTipoPagamento();
                $arr[] = $venda->getValor();
                $arr[] = $venda->getRepasse();
                $arr[] = $venda->getQtdItens();
                $arr[] = $venda->getQtdProdutos();
                $arr[] = $venda->getCodUsuario();
                $arr[] = $venda->getTipoUsuario();
                $arr[] = $venda->getNome(); 
				$arr[] = $venda->getCategoria(); 
                $arr[] = $venda->getCPF(); 
                $arr[] = $venda->getConciliacao();
                $arr[] = $venda->getStatus();

                //pegando os produtos por venda
                $produtos = $this->getProdutosVenda($idVenda);
                if(is_array($produtos)){
                    foreach ($produtos as $ind => $produto){
                        $nomeProduto = $produto->getNomeProduto();
                        $modelo = $produto->getModelo();

                        $nomeProduto = ($modelo != "") ? $nomeProduto.$modelo : $nomeProduto;

                        $arr[] = $produto->getNomeOperador();
                        $arr[] = $nomeProduto;
                        $arr[] = $produto->getValor();
                        $arr[] = $produto->getQtd();
						
						if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
							
							//var_dump(implode(";",$arr));
							//exit;
						}

                        $objCsv->setLine(implode(";",$arr));
                        unset($arr);

                        if(isset($produtos[$ind+1])){
                            //$this->csv .= $this->quebraLinha.$this->espacamentoProdutos.$this->cabecalhoProdutos.$this->quebraLinha.$this->espacamentoProdutos;
                            $objCsv->addEspacamento();
                        }
                    }//end foeach
                }//end if(is_array($produtos))
                else{
                    $arr[] = "SEM PRODUTO CADASTRADO PARA ESTA VENDA.";
                    $objCsv->setLine(implode(";",$arr));
                    unset($arr);
                }//end else do if(is_array($produtos))
            } //end foreach
			
        
            return $objCsv->export();
        }//end if($vendas)
        
    }//end function geraCsv
    
    public function getPrimeiraVenda($dataInicial, $dataFinal, $ultimoStatus = 5, $depositoEmSaldo = 1){
                
        $sqlPrimeiraVenda = "select 
                        vg_ug_id, MIN(vg_data_inclusao) AS vg_data_inclusao
                from 
                        tb_dist_venda_games
                where 
                        vg_ultimo_status = {$ultimoStatus} AND vg_deposito_em_saldo = {$depositoEmSaldo}
                group by 
                        vg_ug_id
                having 
                        MIN(vg_data_inclusao) >= '{$dataInicial} 00:00:00' and MIN(vg_data_inclusao) <= '{$dataFinal} 23:59:59' 
                order by vg_data_inclusao DESC; ";
                        
        $objPrimeiraVenda = $this->getSqlPrimeiraVenda($sqlPrimeiraVenda);
        
        if(is_array($objPrimeiraVenda)){
            foreach($objPrimeiraVenda as $ind => $venda){
                $dataInicial = $venda["venda"]->getDataInclusao();
                $sqlValorTotalPrimeiraVenda =  "select 
                                                        SUM( CASE vg_pagto_tipo WHEN 2 THEN bol_valor ELSE vg_pagto_valor_pago END) as valor_total, 
                                                        count(distinct(vg_pagto_tipo)) tipo,
	                                                (CASE WHEN (ug_tipo_cadastro='PJ') THEN ug_nome_fantasia||' ('||ug_tipo_cadastro||')' WHEN (ug_tipo_cadastro='PF') THEN ug_nome||' ('||ug_tipo_cadastro||')' END) as nome, vg_ug_id, ug_vip
                                                from 
                                                        tb_dist_venda_games
                                                inner join 
                                                        dist_usuarios_games ON vg_ug_id = ug_id 
                                                        left outer join tb_pag_compras ON vg_id = idvenda 
                                                        left outer join boletos_pendentes ON bol_venda_games_id = vg_id
                                                where 
                                                        vg_ug_id = {$venda["venda"]->getCodUsuario()} 
                                                        and vg_data_inclusao >= '".formata_data(substr($dataInicial,0,10), 1)." 00:00:00' 
                                                        and vg_data_inclusao <= '".formata_data(substr($dataInicial,0,10), 1)." 23:59:59'
                                                        and vg_ultimo_status = {$ultimoStatus}
                                                        and vg_deposito_em_saldo = {$depositoEmSaldo}
                                                group by nome, vg_ug_id, ug_vip ";

                $this->getSqlPrimeiraVenda($sqlValorTotalPrimeiraVenda);
            }    
        }
        
        return $this->vendas;
            
    }
    
    public function getPedidoVendas($usuarioId,$registros = 20,$p = 1,$boolNaoImpresso = false){
        
        
        $sql  = "select 
                    vg_id, 
					id_pedido_parceiro,
                    vg_data_inclusao, 
                    vg_pagto_tipo, 
                    vg_ultimo_status, 
                    vg_usuario_obs,
                    valor, 
                    qtde_itens, 
                    qtde_produtos, 
                    repasse,
                    cesta
                from
                    (  
                        (
                            select 
                                vg.vg_id, 
								api.id_pedido_parceiro,
                                vg.vg_data_inclusao, 
                                vg.vg_pagto_tipo::char, 
                                vg.vg_ultimo_status::char, 
                                vg.vg_usuario_obs, 
                                sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, 
                                sum(vgm.vgm_qtde) as qtde_itens, 
                                count(*) as qtde_produtos, 
                                sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse,
                                (select array(select vgm_nome_produto||' - '||vgm_nome_modelo from tb_dist_venda_games_modelo vgmt where vgmt.vgm_vg_id = vg.vg_id)) as cesta
                            from 
                                tb_dist_venda_games vg 
                            inner join 
                                tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id  
							left join 
                                pedidos_api_pdv api on api.id_pedido_eprepag = vg.vg_id
                            where 
                                vg.vg_ug_id=" . $usuarioId;
        if($boolNaoImpresso) {
            $sql .= " and vgm.vgm_id IN ( 
                                        select vgmp.vgmp_vgm_id 
                                        from tb_dist_venda_games_modelo_pins vgmp
					where 
                                            vgm.vgm_id= vgmp.vgmp_vgm_id 
                                            AND (
                                                vgmp.vgmp_impressao_qtde IS NULL OR
                                                vgmp.vgmp_impressao_qtde < 1
                                                )
                                    ) 
                                ";
            
        } //end if($boolNaoImpresso)]
        
            if(isset($_POST['tf_v_codigo']) && is_numeric($_POST['tf_v_codigo'])) 
                $sql .= " and vg.vg_id=" . $_POST['tf_v_codigo'];
		
            if(isset($_POST['tf_v_data_inclusao_ini']) && isset($_POST['tf_v_data_inclusao_fim']))
                if(verifica_data($_POST['tf_v_data_inclusao_ini']) != 0 && verifica_data($_POST['tf_v_data_inclusao_fim']) != 0)
                    $sql .= " and vg.vg_data_inclusao >= '".formata_data($_POST['tf_v_data_inclusao_ini'],1)." 00:00:00' and vg.vg_data_inclusao <= '".formata_data($_POST['tf_v_data_inclusao_fim'],1)." 23:59:59'";
		
            $sql .=     " group by 
                            vg.vg_id, api.id_pedido_parceiro, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_usuario_obs
                        ) ";
            if(!$boolNaoImpresso) {   
                ##############################B2C
                $sql .= "  union all 
                        (
                            select 
                                vb2c_vg_id as vg_id, 
								0 as id_pedido_parceiro,
                                \"vb2c_dataVenda\" as vg_data_inclusao, 
                                'B2C' as vg_pagto_tipo, 
                                vb2c_status as vg_ultimo_status, 
                                '' as vg_usuario_obs, 
                                \"vb2c_precoServico\" as valor, 
                                1 as qtde_itens, 
                                1 as qtde_produtos, 
                                (\"vb2c_precoServico\" * vb2c_comissao_para_repasse / 100) as repasse,
                                (select array(select \"vb2c_coServico\" from tb_vendas_b2c b2cc where b2c.vb2c_vg_id = b2cc.vb2c_vg_id)) as cesta
                            from 
                                tb_vendas_b2c b2c
                            where 
                                vb2c_ug_id_lan =" . $usuarioId." AND  
                                vb2c_status <> 'N'";
            
                if(isset($_POST['tf_v_codigo']) && is_numeric($_POST['tf_v_codigo'])) 
                    $sql .=    " and vb2c_vg_id= " . $_POST['tf_v_codigo'];

                if(isset($_POST['tf_v_data_inclusao_ini']) && isset($_POST['tf_v_data_inclusao_fim']))
                        if(verifica_data($_POST['tf_v_data_inclusao_ini']) != 0 && verifica_data($_POST['tf_v_data_inclusao_fim']) != 0)
                            $sql .= " and \"vb2c_dataVenda\" >= '".formata_data($_POST['tf_v_data_inclusao_ini'],1)." 00:00:00' and \"vb2c_dataVenda\" <= '".formata_data($_POST['tf_v_data_inclusao_fim'],1)." 23:59:59'";
                ################################tb_recarga_pedidos_rede_sim
                $sql .="    )
                            union all 
                            (
                                select 
                                    rprs_vg_id as vg_id, 
									0 as id_pedido_parceiro,
                                    rprs_data_recarga as vg_data_inclusao, 
                                    'Recarga' as vg_pagto_tipo, 
                                    rprs_status as vg_ultimo_status, 
                                    '' as vg_usuario_obs, 
                                    rprs_valor as valor, 
                                    1 as qtde_itens, 
                                    1 as qtde_produtos, 
                                    (rprs_valor * rprs_comissao_para_repasse / 100) as repasse,
                                    (select array(select 'Recarga'||' R$'||rprs_valor||' nro:'||rprs_numerocelular from tb_recarga_pedidos_rede_sim rpt where rp.rprs_vg_id = rpt.rprs_vg_id )) as cesta
                                from 
                                    tb_recarga_pedidos_rede_sim  rp 
                                where 
                                    rprs_ug_id =" . $usuarioId;

                if(isset($_POST['tf_v_codigo']) && is_numeric($_POST['tf_v_codigo'])) 
                    $sql .= " and rprs_vg_id=" . $_POST['tf_v_codigo'];

                if(isset($_POST['tf_v_data_inclusao_ini']) && isset($_POST['tf_v_data_inclusao_fim']))
                        if(verifica_data($_POST['tf_v_data_inclusao_ini']) != 0 && verifica_data($_POST['tf_v_data_inclusao_fim']) != 0)
                                $sql .= " and rprs_data_recarga >= '".formata_data($_POST['tf_v_data_inclusao_ini'],1)." 00:00:00' and rprs_data_recarga <= '".formata_data($_POST['tf_v_data_inclusao_fim'],1)." 23:59:59'";

                ########################tb_seguro_pedidos_rede_sim
                $sql .=         " and rprs_data_recarga is not null
                            )
                            union all 
                            (
                                select 
                                    sprs_vg_id as vg_id, 
									0 as id_pedido_parceiro,
                                    sprs_data_inclusao as vg_data_inclusao, 
                                    'Seguro' as vg_pagto_tipo, 
                                    sprs_status as vg_ultimo_status, 
                                    '' as vg_usuario_obs, 
                                    sprs_valor as valor, 
                                    1 as qtde_itens, 
                                    1 as qtde_produtos, 
                                    sprs_valor as repasse,
                                    (select array(select 'Seguro'||' R$'||sprs_valor from tb_seguro_pedidos_rede_sim tsprc where tsprc.sprs_vg_id = sprs.sprs_vg_id )) as cesta
                                from 
                                    tb_seguro_pedidos_rede_sim  sprs
                                where 
                                    sprs_ug_id =" . $usuarioId;

                if(isset($_POST['tf_v_codigo']) && is_numeric($_POST['tf_v_codigo'])) 
                    $sql .= " and sprs_vg_id=" . $_POST['tf_v_codigo'];

                if(isset($_POST['tf_v_data_inclusao_ini']) && isset($_POST['tf_v_data_inclusao_fim']))
                        if(verifica_data($_POST['tf_v_data_inclusao_ini']) != 0 && verifica_data($_POST['tf_v_data_inclusao_fim']) != 0)
                                $sql .= " and sprs_data_seguro >= '".formata_data($_POST['tf_v_data_inclusao_ini'],1)." 00:00:00' and sprs_data_seguro <= '".formata_data($_POST['tf_v_data_inclusao_fim'],1)." 23:59:59'";

                ################################tb_recarga_pedidos
                $sql .=         " and sprs_data_seguro is not null
                            )
                            union all 
                            (
                                select 
                                    rp_vg_id as vg_id, 
									0 as id_pedido_parceiro,
                                    rp_data_inclusao as vg_data_inclusao, 
                                    'Recarga Celular' as vg_pagto_tipo, 
                                    rp_status as vg_ultimo_status, 
                                    '' as vg_usuario_obs, 
                                    rp_valor as valor, 
                                    1 as qtde_itens, 
                                    1 as qtde_produtos, 
                                    rp_valor as repasse,
                                    (select array(select 'Recarga Celular'||' R$'||rp_valor||' nro: '||rp_numerocelular from tb_recarga_pedidos trpc where trp.rp_vg_id = trpc.rp_vg_id )) as cesta
                                from 
                                    tb_recarga_pedidos  trp
                                where 
                                    rp_ug_id =" . $usuarioId;

                if(isset($_POST['tf_v_codigo']) && is_numeric($_POST['tf_v_codigo'])) 
                    $sql .= " and rp_vg_id=" . $_POST['tf_v_codigo'];

                if(isset($_POST['tf_v_data_inclusao_ini']) && isset($_POST['tf_v_data_inclusao_fim']))
                        if(verifica_data($_POST['tf_v_data_inclusao_ini']) != 0 && verifica_data($_POST['tf_v_data_inclusao_fim']) != 0)
                                $sql .= " and rp_data_recarga >= '".formata_data($_POST['tf_v_data_inclusao_ini'],1)." 00:00:00' and rp_data_recarga <= '".formata_data($_POST['tf_v_data_inclusao_fim'],1)." 23:59:59'";

                $sql .=         " and rp_data_recarga is not null
                            )";
            }
            
            $sql .= "
                    ) as vendas ";
            
            
            $qtd = $this->getTotalVendas($sql);

            //quando o usuario esta na pagina 2 ou + e pesquisa por um id de pedido, o P nao deixa trazer o resultado, pois pesquisa por id de venda só retorna 1 resultado, entao o order by/offset e limit não se fazem necessario
            if(!isset($_POST['tf_v_codigo']) || $_POST['tf_v_codigo'] == "")
                $sql .= " order by vg_data_inclusao desc offset " . ($p - 1) * $registros . " limit " . $registros;
            $vendas= null;
			
			$ff = fopen("/www/log/ajuste-sql.txt", "a+");
			fwrite($ff, $sql."\n");
			fclose($ff);

            $vendas = $this->getVendas($sql,false);

            $pedido['qtd'] = $qtd;
            $pedido['vendas'] = $vendas;
            return $pedido;
    }
        
    public function getTotalVendas($sql){
        return count($this->getVendas($sql,false));
    }
} //end class
