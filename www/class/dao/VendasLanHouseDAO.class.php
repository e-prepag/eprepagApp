<?php

/**
 * Classe para as regras de negocio dos pedidos
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 08-06-2015
 */
require_once $raiz_do_projeto."class/view/VendasLanHouseVO.class.php";
require_once $raiz_do_projeto."class/view/ProdutosLanHouseVO.class.php";
require_once $raiz_do_projeto."class/view/LanHouseVO.class.php";
if(!function_exists('getCodigoNumericoParaPagto'))
    require_once $raiz_do_projeto."includes/gamer/functions_pagto.php";

class VendasLanHouseDAO {
    
    private $sql = "";
    public $vendas = array();
    public $produtos = array();
    
    public function __construct($sql = ""){
        $this->sql = "";
        
        
    }
    
    public function getVendas($sql,$formata = true){
       
        $this->sql = $sql;
        $this->vendas = array();
        
        try{
            $vendas = SQLexecuteQuery($this->sql);
            $totalLinhas = pg_num_rows($vendas);
            if($totalLinhas > 0){
                while($lineRow = pg_fetch_array($vendas)){
                    $venda = new VendasLanHouseVO;
                    $venda->setIdVenda($lineRow["vg_id"]);
                    if (isset($lineRow["qtde_itens"])) {
                        $venda->setQtdItens($lineRow["qtde_itens"]);
                    }
                    if (isset($lineRow["qtde_produtos"])) {
                        $venda->setQtdProdutos($lineRow["qtde_produtos"]);
                    }
                    if (isset($lineRow["ug_id"])) {
                        $venda->setCodUsuario($lineRow["ug_id"]);
                    }
                    if (isset($lineRow['ug_tipo_cadastro'])) {
                        $venda->setTipoUsuario($lineRow['ug_tipo_cadastro']);
                    }
					if (isset($lineRow['id_pedido_parceiro'])) {
                        $venda->setIdVendaAPI($lineRow['id_pedido_parceiro']);
                    }
                    if(isset($lineRow['ug_nome_fantasia']) || isset($lineRow['ug_nome'])){
                        (isset($lineRow['ug_tipo_cadastro']) && strtoupper($lineRow['ug_tipo_cadastro']) == 'PF' ) ? $venda->setNome($lineRow['ug_nome']) : $venda->setNome($lineRow['ug_nome_fantasia']); 
                    }
					if(isset($lineRow['ug_vip'])){
						$venda->setCategoria($lineRow['ug_vip']);
					}
                    if (isset($lineRow['vgm_cpf'])) {
                        $venda->setCPF($lineRow['vgm_cpf']);
                    }
                    if ($lineRow['vg_ultimo_status'] == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] ||
                        $lineRow['vg_ultimo_status'] == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] ||
                        $lineRow['vg_ultimo_status'] == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] ||
                        $lineRow['vg_ultimo_status'] == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'])
                    {
                            $venda->setConciliacao($lineRow['vg_concilia']);
                    }
                    else
                    {
                        $venda->setConciliacao("");
                    }
                    
                    if (isset($lineRow['cesta'])) {
                        $venda->setCesta(json_decode(str_replace(array("{","}"),array("[","]"),utf8_encode($lineRow['cesta']))));
                    }
                        
                    if($formata)
                    {
                        $venda->setDataInclusao(formata_data_ts($lineRow['vg_data_inclusao'],0, true,true));
                        $venda->setTipoPagamento(($lineRow['ug_risco_classif']==1)?$GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][getCodigoCaracterParaPagto($lineRow["vg_pagto_tipo"])]:$GLOBALS['FORMAS_PAGAMENTO_DESCRICAO_NEW'][getCodigoCaracterParaPagto($lineRow["vg_pagto_tipo"])]);
                        if(isset($lineRow['valor']))
                            $venda->setValor(number_format($lineRow['valor'], 2, ',','.'));
                        if(isset($lineRow['repasse']))
                            $venda->setRepasse(number_format($lineRow['repasse'], 2, ',','.'));
                        $venda->setStatus(
                                substr($GLOBALS['STATUS_VENDA_DESCRICAO'][$lineRow['vg_ultimo_status']], 0, strpos($GLOBALS['STATUS_VENDA_DESCRICAO'][$lineRow['vg_ultimo_status']], '.')));
                    }
                    else
                    {
                        $venda->setDataInclusao($lineRow['vg_data_inclusao']);
                        $venda->setTipoPagamento($lineRow["vg_pagto_tipo"]);
                        $venda->setValor($lineRow['valor']);
                        $venda->setRepasse($lineRow['repasse']);
                        $venda->setStatus($lineRow['vg_ultimo_status']);
                    }

                    $this->vendas[] = $venda;
                    unset($venda);
                }

                return $this->vendas;
            }else{
                throw new Exception("FALHA NA OBTENCAO DO REGISTRO DE VENDAS");
            }
        } catch (Exception $ex) {
            $geraLog = new Log("VENDASLANHOUSE",array("ERROR: ".$ex->getMessage(),
                                          "FILE: ".$ex->getFile(),
                                          "LINE ".$ex->getLine()));
            return false;
        }
	
    }
    
    public function getProdutosVenda($idVenda, $sql = null){
        $sql = "select opr_nome,
                        vgm_nome_produto,
                        vgm_nome_modelo,
                        vgm_pin_valor,
                        vgm_qtde,
                        vgm_valor,
                        opr_min_repasse,
                        vgm_perc_desconto,
                        ogp_iof
                from tb_dist_venda_games_modelo vgm 
                        inner join operadoras opr on opr.opr_codigo = vgm.vgm_opr_codigo 
                        inner join tb_dist_operadora_games_produto ogp on ogp.ogp_id =  vgm.vgm_ogp_id 
                where vgm_vg_id = " . $idVenda. "; ";
        $this->produtos = array();
        
        try{
            $resultProdutos = SQLexecuteQuery($sql);
            if($resultProdutos && pg_num_rows($resultProdutos) > 0){
                    while($lineProd = pg_fetch_array($resultProdutos)){
                        $produto = new ProdutosLanHouseVO;
                        
                        $produto->setNomeOperador($lineProd['opr_nome']);
                        $produto->setNomeProduto($lineProd['vgm_nome_produto']);
                        $produto->setModelo($lineProd['vgm_nome_modelo']);
                        $produto->setValor(number_format($lineProd['vgm_pin_valor'], 2, ',','.'));
                        $produto->setQtd($lineProd['vgm_qtde']);
                        $produto->setValorUnitario($lineProd['vgm_valor']);
                        $produto->setRepasse($lineProd['opr_min_repasse']);
                        $produto->setDesconto($lineProd['vgm_perc_desconto']);
                        $produto->setIOF($lineProd['ogp_iof']);
                        

                        $this->produtos[] = $produto;
                    }
                    
                    return $this->produtos;
            }else{
                throw new Exception("FALHA NA OBTENCAO DE PRODUTOS.");
            }
            
        } catch (Exception $ex) {
            $geraLog = new Log("VENDASLANHOUSE",array("ERROR: ".$ex->getMessage(),
                                                      "FILE: ".$ex->getFile(),
                                                      "LINE ".$ex->getLine()));
            return false;
        }
        
    }
    
    public function getSqlPrimeiraVenda($sql){
        //echo "<br><hr><br>".$sql."<br><hr><br>"; #descomente para ver as querys 
        $this->sql = $sql;    
        try{
            $primeirasVendas = SQLexecuteQuery($this->sql);
            $totalLinhas = pg_num_rows($primeirasVendas);
            if($totalLinhas > 0){
                while($lineRow = pg_fetch_array($primeirasVendas)){
                    
                    if( isset($this->vendas[$lineRow['vg_ug_id']]['venda']) && $this->vendas[$lineRow['vg_ug_id']]['venda'] instanceof VendasLanHouseVO && 
                        isset($this->vendas[$lineRow['vg_ug_id']]['lan_house']) && $this->vendas[$lineRow['vg_ug_id']]['lan_house'] instanceof LanHouseVO){
                        $venda      = $this->vendas[$lineRow['vg_ug_id']]['venda'];
                        $lanHouse   = $this->vendas[$lineRow['vg_ug_id']]['lan_house'];
                    }else{
                        $venda = new VendasLanHouseVO;
                        $lanHouse = new LanHouseVO;
                    }
                    
                    if(isset($lineRow["vg_ug_id"]))             $venda->setCodUsuario($lineRow["vg_ug_id"]);
                    if(isset($lineRow["valor_total"]))          $venda->setValor($lineRow["valor_total"]);
                    if(isset($lineRow["vg_data_inclusao"]))     $venda->setDataInclusao(formata_data_ts($lineRow['vg_data_inclusao'],0, true,true));
                    if(isset($lineRow["ug_id"]))                $lanHouse->setId($lineRow['vg_ug_id']);
                    if(isset($lineRow["nome"]))                 $lanHouse->setNome($lineRow['nome']);
                    if(isset($lineRow["ug_vip"]))               $lanHouse->setVip($lineRow['ug_vip']);
                    if(isset($lineRow["tipo"]))                 $venda->setTipoPagamento($lineRow['tipo']);
                    
                    
                    $this->vendas[$lineRow['vg_ug_id']]['venda'] = $venda;
                    $this->vendas[$lineRow['vg_ug_id']]['lan_house'] = $lanHouse;
                    unset($venda);
                    unset($lanHouse);
                }
                
                return $this->vendas;
            }else{
                throw new Exception("NENHUM REGISTRO ENCONTRADO NA BUSCA COM A QUERY ".$this->sql."\n");
            }
        } catch (Exception $ex) {
            $geraLog = new Log("VENDASLANHOUSE",array("ERROR: ".$ex->getMessage(),
                                          "FILE: ".$ex->getFile(),
                                          "LINE ".$ex->getLine()));
            return false;
        }
    }
}
