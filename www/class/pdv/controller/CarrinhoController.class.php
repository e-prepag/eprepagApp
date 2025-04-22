<?php
/**
 * Classe para as regras de negocio das vendas
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 16-07-2015
 */

require_once DIR_CLASS . 'pdv/controller/HeaderController.class.php';
require_once DIR_CLASS . 'pdv/controller/ProdutosController.class.php';
require_once DIR_INCS . "funcoes_cpf.php";

$_PaginaOperador2Permitido = 54; 

$pagina_titulo = "Carrinho";

class CarrinhoController extends ProdutosController{
    
    
    
    public $erro = "";
    
    public function __construct(){
        parent::__construct();
    }
    
    public function actions($get){
        global $NO_HAVE;
        
        $carrinho = (isset($_SESSION['dist_carrinho'])) ? $_SESSION['dist_carrinho'] : null;
        //Acao
        $acao = $get['acao'];
        //Modelo
        $mod = $get['mod'];
        //Valor para p´rodutos de valor variável
        $valor = $get['valor'];
        //Idjogo
        $codeProd = $get['codeProd'];
        //Adiciona modelo no carrinho
        //---------------------------------------------------------------
        if($mod && $mod != "" && is_numeric($mod)){
            if($acao == "a"){

                //verifica se o modelo esta no carrinho
                if(!$carrinho[$mod]){

                    //verifica se o modelo existe e esta ativo	
                    $rs = null;
                    $filtro['ogpm_ativo'] = 1;
                    $filtro['ogpm_id'] = $mod;
                    $modelo = $this->getModelo($filtro, null, $rs);

                    //Adiciona modelo no carrinho
                    if(!empty($modelo)){
                        $carrinho[$mod] = 1;
                    }
                }
            }

            //remove modelo no carrinho
            //---------------------------------------------------------------
            if($acao == "d"){

                //verifica se o modelo ja esta no carrinho
                if($carrinho[$mod]){

                    //Remove modelo no carrinho
                    //$carrinho[$mod] = null;
                    unset($carrinho[$mod]);
                }
            }

            //atualiza modelo no carrinho
            //---------------------------------------------------------------
            if($acao == "u"){

                //Qtde
                if(isset($get['qtde'])) $qtde = $get['qtde'];

                    //Atualiza se for qtde valida
                    if($qtde && is_numeric($qtde) && $qtde > 0 ){

                        //somente para evitar fraude
                        if($qtde > 999)
                            $qtde = 999;

                        //verifica se o modelo esta no carrinho
                        if($carrinho[$mod]){

                                //atualiza modelo no carrinho
                                $carrinho[$mod] = $qtde;

                        //Se o modelo nao esta no carrinho, adiciona
                        } else {
                                //verifica se o modelo existe e esta ativo	
                                $rs = null;
                                $filtro['ogpm_ativo'] = 1;
                                $filtro['ogpm_id'] = $mod;
                                $modelo = $this->getModelo($filtro, null, $rs);

                                //Adiciona modelo no carrinho
                                if(!empty($modelo)){
                                        $carrinho[$mod] = $qtde;
                                }

                        }
                    }
            }
        }elseif($mod == $NO_HAVE){

            if( ($mod == $NO_HAVE) && !$valor) {				
                Util::redirect("/creditos/");
            }
            
            if($acao == "a"){

                //verifica se o modelo esta no carrinho
                if(!$carrinho[$mod][$codeProd][$valor]){
                    $carrinho[$mod][$codeProd][$valor] = 1;
                }
            }
            
            //remove modelo no carrinho
            //---------------------------------------------------------------
            if($acao == "d"){

                //verifica se o modelo ja esta no carrinho
                if($carrinho[$mod][$codeProd][$valor]){

                    //Remove modelo no carrinho
                    unset($carrinho[$mod][$codeProd][$valor]);
                    if(count($carrinho[$mod][$codeProd]) == 0) unset($carrinho[$mod][$codeProd]);
                    if(count($carrinho[$mod]) == 0) unset($carrinho[$mod]);
                }
            }
            
            //adiciona qtde modelo no carrinho
            //---------------------------------------------------------------
            if($acao == "u"){
                //Qtde
                if(isset($get['qtde'])) $qtde = $get['qtde'];

                //Atualiza se for qtde valida
                if($qtde && is_numeric($qtde) && $qtde > 0 ){

                    //verifica se o modelo esta no carrinho
                    if($carrinho[$mod][$codeProd][$valor]){
                        //atualiza modelo no carrinho
                        $carrinho[$mod][$codeProd][$valor] = $qtde;
                    } else {
                        //Se o modelo nao esta no carrinho, adiciona
                        $carrinho[$mod][$codeProd][$valor] = 1;
                    }
                }
            }
            
            //diminiu qtde modelo no carrinho
            //---------------------------------------------------------------
            if($acao == "m"){

                //Qtde
                if(isset($get['qtde'])) $qtde = $get['qtde'];

                //Atualiza se for qtde valida
                if($qtde && is_numeric($qtde) && $qtde > 0 ){

                    //verifica se o modelo esta no carrinho
                    if($carrinho[$mod][$codeProd][$valor]){
                        //atualiza modelo no carrinho
                        $carrinho[$mod][$codeProd][$valor] = $qtde;
                    } else {
                        //Se o modelo nao esta no carrinho, adiciona
                        $carrinho[$mod][$codeProd][$valor] = 1;
                    }
                }
            }
        }
        //Devolve carrinho no session
        $_SESSION['dist_carrinho'] = $carrinho;
        return true;
    }
    
    public function getCarrinho($carrinho){
//        try {
            global $NO_HAVE;
            if(!empty($carrinho)){
                $arrModelos = array();
                $usuarioId = $this->usuarios->getId();
                $pagto = $_SESSION['dist_pagamento.pagto'];
                
                if(!$pagto){
                    if($this->confirmarCompra()){
                    $pagto = $_SESSION['dist_pagamento.pagto'];
                    }
                    else
                    {
                        echo "<script>alert('".$this->erro."'); 
                            location.href = '/creditos/produtos.php';</script>";
                    }
                }
                
                
                $test_opr_need_cpf_lh = false;
                
				$produtosCount = 0;
                foreach ($carrinho as $modeloId => $qtde){
                    if($modeloId !== $NO_HAVE) {
                        $qtde = intval($qtde);
                        $rs = null;
                        if(!empty($modeloId)) {    
                            $filtro['ogpm_ativo'] = 1;
                            $filtro['ogpm_id'] = $modeloId;
                            $filtro['com_produto'] = true;

                            $modelo = $this->getModelo($filtro, null,$rs);
                            if(!empty($modelo))
                            {
                                //Recuperando produto para obter a idade mínima
                                $rs_produto = null;
                                $filtro['ogp_ativo'] = 1;
                                $filtro['ogp_id'] = $modelo[0]->getProdutoId();
                                $ret_produto = (new Produto)->obtermelhorado($filtro, null, $rs_produto);
                                if(!$rs_produto || pg_num_rows($rs_produto) == 0) $msg = "Nenhum produto disponível no momento.";
                                else $rs_row_produto = pg_fetch_array($rs_produto);
                                
                                if($GLOBALS["IDADE_MINIMA"] < $rs_row_produto["ogp_idade_minima"]){
                                    $GLOBALS["IDADE_MINIMA"] = $rs_row_produto["ogp_idade_minima"];
                                    $GLOBALS["produto_idade_minima"] = $rs_row_produto["ogp_nome"];
                                }

                                $opr_codigo = $modelo[0]->getCodOperador();
    //                            if(!$test_opr_need_cpf_lh) {
    //                                $test_opr_need_cpf_lh = checkingNeedCPF_LH($opr_codigo);
    //                            }//end if(!$test_opr_need_cpf_lh)
                                $total_geral_aux = 0;
                                $perc_desconto = obtemDesconto($opr_codigo, $pagto, $usuarioId, $total_geral_aux);
    //                            $perc_desconto = 0;
                                if(!$test_opr_need_cpf_lh) {
                                    $test_opr_need_cpf_lh = checkingNeedCPF_LH($opr_codigo);
                                }//end if(!$test_opr_need_cpf_lh)
                                $valor = $modelo[0]->getValor(); //['ogpm_valor'];
                                $geral = $valor*$qtde;
                                $desconto = $geral*$perc_desconto/100;
                                $repasse = $geral - $desconto;

                                $qtde_total += $qtde;
                                $total_geral += $geral;
                                $total_desconto += $desconto;
                                $total_repasse += $repasse;    

                                $arrModelos[$modelo[0]->getId()]['modelo']          = $modelo[0];
                                $arrModelos[$modelo[0]->getId()]['valor']           = $valor;
                                $arrModelos[$modelo[0]->getId()]['geral']           = $geral;
                                $arrModelos[$modelo[0]->getId()]['comissao']        = $desconto;
                                $arrModelos[$modelo[0]->getId()]['repasse']         = $repasse;
                                $arrModelos[$modelo[0]->getId()]['qtd']             = $qtde;
                                $arrModelos[$modelo[0]->getId()]["variavel"]        = 0;

                            }

                        }
                    }else{
                        foreach ($qtde as $codeProd => $vetor_valor) {
                            foreach ($vetor_valor as $valor => $quantidade) {
                                    $rs = null;
                                    $filtro['ogp_ativo'] = 1;
                                    $filtro['ogp_id'] = $codeProd;
                                    $ret = (new Produto)->obtermelhorado($filtro, null, $rs);
                                    if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
                                    else $rs_row = pg_fetch_array($rs);
                                    
                                    if($GLOBALS["IDADE_MINIMA"] < $rs_row["ogp_idade_minima"]){
                                        $GLOBALS["IDADE_MINIMA"] = $rs_row["ogp_idade_minima"];
                                        $GLOBALS["produto_idade_minima"] = $rs_row_produto["ogp_nome"];
                                    }
                                    
                                    $opr_codigo = $rs_row["ogp_opr_codigo"];

                                    $total_geral_aux = 0;
                                    $perc_desconto = obtemDesconto($opr_codigo, $pagto, $usuarioId, $total_geral_aux);

                                    if(!$test_opr_need_cpf_lh) {
                                        $test_opr_need_cpf_lh = checkingNeedCPF_LH($opr_codigo);
                                    }//end if(!$test_opr_need_cpf_lh)
                                    $valor = $valor; //['ogpm_valor'];
                                    
                                    
                                    $geral = $valor*$qtde[$codeProd][$valor];
                                    $desconto = $geral*$perc_desconto/100;
                                    $repasse = $geral - $desconto;

                                    $qtde_total += $qtde[$codeProd][$valor];
                                    $total_geral += $geral;
                                    $total_desconto += $desconto;
                                    $total_repasse += $repasse;
                                    
                                    $arrModelos[$NO_HAVE][$opr_codigo][$valor]['modelo']          = $NO_HAVE;
                                    $arrModelos[$NO_HAVE][$opr_codigo][$valor]['codeProd']        = $codeProd;
                                    $arrModelos[$NO_HAVE][$opr_codigo][$valor]['produto']         = $rs_row;
                                    $arrModelos[$NO_HAVE][$opr_codigo][$valor]['valor']           = $valor;
                                    $arrModelos[$NO_HAVE][$opr_codigo][$valor]['geral']           = $geral;
                                    $arrModelos[$NO_HAVE][$opr_codigo][$valor]['comissao']        = $desconto;
                                    $arrModelos[$NO_HAVE][$opr_codigo][$valor]['repasse']         = $repasse;
                                    $arrModelos[$NO_HAVE][$opr_codigo][$valor]['qtd']             = $qtde[$codeProd][$valor];
                                    $arrModelos[$NO_HAVE][$opr_codigo][$valor]["variavel"]        = 0;
                            }
                        }
                    }
					$produtosCount++;
                }
                
                if(!empty($arrModelos)){
                    $arrModelos['qtde_total']     = $qtde_total;
					$arrModelos['qtde_total_produtos'] = $produtosCount;
                    $arrModelos['total_geral']    = $total_geral;
                    $arrModelos['total_desconto'] = $total_desconto;
                    $arrModelos['total_repasse']  = $total_repasse;
                    $arrModelos['require_cpf']    = $test_opr_need_cpf_lh;
                }
                                
                return $arrModelos;
            }else {
                throw new Exception("PRODUTO NAO ENCONTRADO.");
            }
//        } catch (Exception $ex) {
//            print     "<script>alert('Seu carrinho está vazio.'); "
//                    . "location.href = '/creditos/produtos.php';"
//                    . "</script>";
//            die;
//        }

        return $objProduto;
    }
    
    public function confirmarCompra(){
        
        global $FORMAS_PAGAMENTO;
        global $ENTRE_CONTATO_CENTRAL;
        //Bloco Prepag Money Distribuidor
	$pagto = $this->usuarios->getPerfilFormaPagto();
        $_SESSION['dist_pagamento.pagto'] = $pagto;
        $this->erro = "";
        
	if(!$pagto || trim($pagto) == "" || !is_numeric($pagto))
            $this->erro = "Forma de Pagamento não definida.<br>".$ENTRE_CONTATO_CENTRAL;
	else if(!in_array($pagto, $FORMAS_PAGAMENTO))
            $this->erro = "Forma de Pagamento definida é inválida.<br>".$ENTRE_CONTATO_CENTRAL;;
        
        return ($this->erro == "") ? true : false;
    }
}
