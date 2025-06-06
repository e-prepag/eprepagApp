<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once "../../../includes/constantes.php";
require_once DIR_INCS ."gamer/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
require_once DIR_INCS . 'gamer/functions.php';
$controller = new HeaderController;
$controller->setHeader();
/*
 * Início controller
 */

require_once DIR_INCS . "inc_register_globals.php";

//Recupera carrinho do session
$carrinho = $_SESSION['carrinho'];

//Produto Modelo
if(!$mod) $mod = $_POST['mod'];

//Idjogo
if(!$codeProd) $codeProd = $_POST['codeProd'];

//Valor para p´rodutos de valor variável
if(!$valor) $valor = $_POST['valor'];

/*    Teste de quantidade de itens   */
//Captura da quantidade para teste 
if(!$qtde_nova) {
    if(isset($_POST['qtde'])) {
            $qtde_nova = $_POST['qtde'];
    }
    else {
            $qtde_nova =  1;
    }
}
$msg = "";

//Variavel para habilitar o teste de carrinho existente
$pularTesteInicial = ((isset($GLOBALS['_SESSION']['carrinho']) && count($GLOBALS['_SESSION']['carrinho'])) > 0 ? TRUE : FALSE);

if($mod && $mod != "" && is_numeric($mod)){
    //Acao
    if(!$acao) 
        $acao = $_POST['acao'];

    //Adiciona modelo no carrinho
    //---------------------------------------------------------------
    if($acao == "a"){
        // Alawar - Verificar se o cadigo do Jogo foi enviado para o carrinho
        if( ($mod == $prod_mod_Alawar) && !$_POST['codeProd'] ) {				
//              redirect("/prepag2/commerce/jogos/");
            Util::redirect("/game/");
        }

        //verifica se o modelo esta no carrinho
        if(!$carrinho[$mod]){
            //verifica se o modelo existe e esta ativo	
            $rs = null;
            $filtro['ogpm_ativo'] = 1;
            $filtro['ogpm_id'] = $mod;
            $filtro['com_produto'] = true;	// **

            // Debug reinaldops
            if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                    $filtro['show_treinamento'] = 1;
                }

            }

            $ogpm_ativo = $rs_row['ogpm_ativo'];
            $b_show_treinamento = false;
            $instProdutoModelo = new ProdutoModelo;
            $ret = $instProdutoModelo->obter($filtro, null, $rs);

            //Adiciona modelo no carrinho
            if($rs && pg_num_rows($rs) == 1){
                $carrinho[$mod] = 1;
                if(isset($codeProd) && ($codeProd>0)) {
                    $GLOBALS['_SESSION']['carrinho_alawar_prod_id'] = $codeProd;
                }
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

    //adiciona qtde modelo no carrinho
    //---------------------------------------------------------------
    if($acao == "u"){

        if(verificaQtdeCarrinho($qtde_nova, $mod, $pularTesteInicial, ($codeProd?$codeProd:0), ($valor?$valor:0))  ) {

            //Qtde
            if(!$qtde) $qtde = $_POST['qtde'];

            //Atualiza se for qtde valida
            if($qtde && is_numeric($qtde) && $qtde > 0 ){

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
                    $filtro['com_produto'] = true;	// **

                    // Debug reinaldops
                    if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                            $controller->usuarios = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                            if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                                    $filtro['show_treinamento'] = 1;
                            }
                    }

                    if(isset($controller->usuarios)) {
                            if($controller->usuario->b_IsLogin_pagamento_pin_eprepag()) {
//							$filtro['ogpm_ativo'] = 0;
                            }
                    }

                    $ret = (new ProdutoModelo)->obter($filtro, null, $rs);

                    //Adiciona modelo no carrinho
                    if($rs && pg_num_rows($rs) == 1){
                            $carrinho[$mod] = $qtde;
                    }
                }
            }
        }//end if(verificaQtdeCarrinho($qtde_nova, $mod))
        else $msg = "Número máximo de produtos no carrinho é de ".$QTDE_MAX_ITENS." unidades.";
    }
    
    //diminiu qtde modelo no carrinho
    //---------------------------------------------------------------
    if($acao == "m"){

        //Qtde
        if(!$qtde) $qtde = $_POST['qtde'];

        //Atualiza se for qtde valida
        if($qtde && is_numeric($qtde) && $qtde > 0 ){

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
                $filtro['com_produto'] = true;	// **

                // Debug reinaldops
                if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                        $controller->usuarios = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                        if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                                $filtro['show_treinamento'] = 1;
                        }
                }

                if(isset($controller->usuarios)) {
                        if($controller->usuario->b_IsLogin_pagamento_pin_eprepag()) {
//							$filtro['ogpm_ativo'] = 0;
                        }
                }

                $ret = (new ProdutoModelo)->obter($filtro, null, $rs);

                //Adiciona modelo no carrinho
                if($rs && pg_num_rows($rs) == 1){
                        $carrinho[$mod] = $qtde;
                }
            }
        }
    }
}//end if($mod && $mod != "" && is_numeric($mod))
elseif($mod == $NO_HAVE) {

    if( ($mod == $NO_HAVE) && !$valor && !$codeProd ) {				
        Util::redirect("/game/");
    }

    //Adiciona modelo no carrinho
    //---------------------------------------------------------------
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

        if(verificaQtdeCarrinho($qtde_nova, $mod, $pularTesteInicial, ($codeProd?$codeProd:0), ($valor?$valor:0))) {

            //Qtde
            if(!$qtde) $qtde = $_POST['qtde'];

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
        }//end if(verificaQtdeCarrinho($qtde_nova, $mod))
        else $msg = "Número máximo de produtos no carrinho é de ".$QTDE_MAX_ITENS." unidades.";
    }
        

    //diminiu qtde modelo no carrinho
    //---------------------------------------------------------------
    if($acao == "m"){
        
        //Qtde
        if(!$qtde) $qtde = $_POST['qtde'];

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
        

}//end elseif($mod == $NO_HAVE)

//Devolve carrinho no session
$_SESSION['carrinho'] = $carrinho;

require_once DIR_WEB . 'game/includes/cabecalho.php';
/*
 * Fim controller
 */
?>
<script src="/js/valida.js"></script>
<script>
    function fcnRemover(modeloId){
            window.location='?acao=d&mod=' + modeloId;
    }
    function fcnAtualizar(modeloId, objQtde){
            window.location='?acao=u&mod=' + modeloId + '&qtde=' + objQtde.value;
    }
</script>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12 txt-azul-claro top10">
                    <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">Confirmação dos produtos</h4></strong>
                </div>
            </div>
<?php
            if(!empty($msg)){
?>            
            <div class="row top10">
                <div class="col-md-10 col-md-offset-1 txt-vermelho alert alert-danger align-center">
                    <?php echo $msg;?>
                </div>
            </div>
            <script>
                manipulaModal(1,"<?php echo $msg; ?>","Erro");
            </script>
<?php
            }
            //Recupra carrinho do session
            $carrinho = $_SESSION['carrinho'];

            if(!$carrinho || count($carrinho) == 0){		
                $carrinhoVazio = "Carrinho vazio no momento.";
?>			
            <div class="row">
                <div class="col-md-12 espacamento text-center txt-vermelho">
                    <?php echo $carrinhoVazio;?>
                </div>
                <div class="col-md-3 col-md-offset-7 espacamento">
                    <a href="/game/" class="btn btn-primary">Voltar para jogos</a>
                </div>
            </div>
            <script>
                manipulaModal(1,"<?php echo $carrinhoVazio; ?>","Erro");
            </script>
<?php
            } else {
?>
            <form name="pagamento" id="pagamento" method="POST" action="<?php echo ($controller->logado) ? "passo-2.php" : "pagamento-offline.php";?>">
            <div class="row txt-cinza espacamento top20">
<?php
                foreach ($carrinho as $modeloId => $qtde){

                    if($modeloId !== $NO_HAVE) {
                        
                        $qtde = intval($qtde);
                        $rs = null;
                        $filtro['ogpm_ativo'] = 1;
                        $filtro['ogpm_id'] = $modeloId;
                        $filtro['com_produto'] = true;
                        // Debug reinaldops
                        if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                            $controller->usuarios = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                            if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                                $filtro['show_treinamento'] = 1;
                            }
                        }
                        $instProdutoModelo = new ProdutoModelo;
                        $ret = $instProdutoModelo->obter($filtro, null, $rs);

                        if($rs && pg_num_rows($rs) != 0){
                            $rs_row = pg_fetch_array($rs);
                            $instProduto = new Produto;
                            $iof = $instProduto->buscaIOF($modeloId) ? "Incluso" : "";
                        }
?>
                <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                    <div class="row">
                        <div class="col-xs-3 col-sm-5">
                            Produto:
                        </div>
                        <div class="col-xs-9 col-sm-7">
                            <strong><a href="/game/modelos.php?prod=<?=$rs_row['ogp_id']?>" class="link_azul"><?php echo $rs_row['ogp_nome']?></a> 
                                <?php if($rs_row['ogpm_nome']!=""){ ?> - <a href="/game/modelos.php?prod=<?php echo $rs_row['ogpm_ogp_id']?>" class="link_azul"><?php echo $rs_row['ogpm_nome']?></a><?php }?></strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            IOF.:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                            <?php echo $iof;?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5 nowrap">
                            Valor unitário:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                           <?php echo number_format($rs_row['ogpm_valor'], 2, ',', '.')?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            Qtde.:
                        </div>
                        <div class="col-xs-7 col-sm-7 nowrap">
<?php
                            if(htmlspecialchars($qtde, ENT_QUOTES) > 1)
                            {
?>                            
                                <button type="button" class="btn btn-sm btn-success t0 glyphicon fontnormal glyphicon-minus" qtde="<?php echo htmlspecialchars($qtde, ENT_QUOTES);?>" mod="<?php echo $modeloId; ?>" title="Remover"></button>
<?php
                            }
?>
                                <input class="w40 align-center" type="text" readonly="readonly" value="<?php echo htmlspecialchars($qtde, ENT_QUOTES);?>">
                                <button type="button" class="btn btn-sm btn-success t0 glyphicon fontnormal glyphicon-plus" title="Adicionar" qtde="<?php echo htmlspecialchars($qtde, ENT_QUOTES);?>" mod="<?php echo $modeloId; ?>"></button>
                                <button type="button" class="btn btn-danger btn-sm t0 glyphicon glyphicon-remove" title="Excluir"  mod="<?php echo $modeloId; ?>"></button>
                            
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            Total:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                           R$ <?php echo number_format($rs_row['ogpm_valor']*$qtde, 2, ',', '.');?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            Preço em:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                           <?php echo get_info_EPPCash_NO_Table($rs_row['ogpm_valor_eppcash']*$qtde);?>
                        </div>
                    </div>
                </div>
<?php
                    }
                    else {
                        foreach ($qtde as $codeProd => $vetor_valor) {
                            foreach ($vetor_valor as $valor => $quantidade) {
                                    $rs = null;
                                    $filtro['ogp_ativo'] = 1;
                                    $filtro['ogp_id'] = $codeProd;
                                    $filtro['ogp_mostra_integracao_com_loja'] = '1';
                                    $filtro['opr'] = 1;
                                    $ret = (new Produto)->obtermelhorado($filtro, null, $rs);
                                    if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
                                    else $rs_row = pg_fetch_array($rs);
?>
                <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                    <div class="row">
                        <div class="col-xs-3 col-sm-5">
                            Produto:
                        </div>
                        <div class="col-xs-9 col-sm-7">
                            <strong><a href="/game/modelos.php?prod=<?=$codeProd?>" class="link_azul"><?php echo $rs_row['ogp_nome']?></a> 
                            </strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            IOF.:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                            <?php echo $rs_row['ogp_iof'] ? "Incluso" : "";?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5 nowrap">
                            Valor unitário:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                           <?php echo number_format($valor, 2, ',', '.')?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            Qtde.:
                        </div>
                        <div class="col-xs-7 col-sm-7 nowrap">
<?php
                            if(htmlspecialchars($quantidade, ENT_QUOTES) > 1)
                            {
?>                            
                                <button type="button" class="btn btn-sm btn-success t0 glyphicon fontnormal glyphicon-minus" qtde="<?php echo htmlspecialchars($quantidade, ENT_QUOTES);?>" mod="<?php echo $modeloId; ?>" codeProd="<?php echo $codeProd; ?>" valor="<?php echo $valor; ?>" title="Remover"></button>
<?php
                            }
?>
                                <input class="w40 align-center" type="text" readonly="readonly" value="<?php echo htmlspecialchars($quantidade, ENT_QUOTES);?>">
                                <button type="button" class="btn btn-sm btn-success t0 glyphicon fontnormal glyphicon-plus" title="Adicionar" qtde="<?php echo htmlspecialchars($quantidade, ENT_QUOTES);?>" mod="<?php echo $modeloId; ?>" codeProd="<?php echo $codeProd; ?>" valor="<?php echo $valor; ?>"></button>
                                <button type="button" class="btn btn-danger btn-sm t0 glyphicon glyphicon-remove" title="Excluir"  mod="<?php echo $modeloId; ?>" codeProd="<?php echo $codeProd; ?>" valor="<?php echo $valor; ?>"></button>
                            
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            Total:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                           R$ <?php echo number_format($valor*$quantidade, 2, ',', '.');?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            Preço em:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                           <?php echo get_info_EPPCash_NO_Table((new ConversionPINsEPP)->get_ValorEPPCash('E',$valor)*$quantidade);?>
                        </div>
                    </div>
                </div>
<?php
                            }//end foreach 
                        }//end foreach
                    }//end else do if($modeloId !== $NO_HAVE)
                }//end foreach
?>
                <div class="col-md-12 bg-cinza-claro hidden-sm hidden-xs">
                    <table class="table bg-branco txt-preto">
                    <thead>
                        <tr class="bg-cinza-claro text-center">
                            <th class="txt-left">Produto</th>
                            <th>I.O.F.</th>
                            <th>Valor unitário</th>
                            <th>Qtde.</th>
                            <th>Total</th>
                            <th>Preço em</th>
                        </tr>
                    </thead>
                    <tbody>
            
<?php
                $total_geral_pin_epp_cash = 0;
                $total_geral = 0;
                foreach ($carrinho as $modeloId => $qtde){

                    if($modeloId !== $NO_HAVE) {
                
                        $qtde = intval($qtde);
                        $rs = null;
                        $filtro['ogpm_ativo'] = 1;
                        $filtro['ogpm_id'] = $modeloId;
                        $filtro['com_produto'] = true;
                        // Debug reinaldops
                        if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                            $controller->usuarios = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                            if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                                $filtro['show_treinamento'] = 1;
                            }
                        }
                        $instProdutoModelo = new ProdutoModelo;
                        $ret = $instProdutoModelo->obter($filtro, null, $rs);

                        if($rs && pg_num_rows($rs) != 0){
                            $rs_row = pg_fetch_array($rs);
                            $total_geral += $rs_row['ogpm_valor'] * $qtde;
                            $total_geral_pin_epp_cash  += $rs_row['ogpm_valor_eppcash'] * $qtde;
                            $instProduto = new Produto;
                            $iof = $instProduto->buscaIOF($modeloId) ? "Incluso" : "";
    ?>

                              <tr class="text-center trListagem">
                                <td class="text-left">
                                    <a href="/game/modelos.php?prod=<?=$rs_row['ogp_id']?>" class="link_azul"><?php echo $rs_row['ogp_nome']?></a> 
                                    <?php if($rs_row['ogpm_nome']!=""){ ?> - <a href="/game/modelos.php?prod=<?php echo $rs_row['ogpm_ogp_id']?>" class="link_azul"><?php echo $rs_row['ogpm_nome']?></a><?php }?>
                                </td>
                                <td><?php echo $iof;?></td>
                                <td><?php echo number_format($rs_row['ogpm_valor'], 2, ',', '.')?></td>
                                <td>
    <?php
                                if(htmlspecialchars($qtde, ENT_QUOTES) > 1)
                                {
    ?>                            
                                    <span class="glyphicon glyphicon-minus t5 color-green-button c-pointer leftright5 font20" qtde="<?php echo htmlspecialchars($qtde, ENT_QUOTES);?>" mod="<?php echo $modeloId; ?>" title="Remover"></span>
    <?php
                                }
    ?>
                                    <input class="w40 align-center" type="text" readonly="readonly" value="<?php echo htmlspecialchars($qtde, ENT_QUOTES);?>"> 
                                    <span class="glyphicon glyphicon-plus t5 color-green-button c-pointer leftright5 font20" title="Adicionar" qtde="<?php echo htmlspecialchars($qtde, ENT_QUOTES);?>" mod="<?php echo $modeloId; ?>"></span>
                                    <span class="glyphicon glyphicon-remove t5 txt-vermelho p-left-3 c-pointer font20" title="Excluir"  mod="<?php echo $modeloId; ?>"></span>
                                </td>
                                <td>R$ <?php	echo number_format($rs_row['ogpm_valor']*$qtde, 2, ',', '.');?></td>
                                <td><?php echo get_info_EPPCash_NO_Table($rs_row['ogpm_valor_eppcash']*$qtde);?></td>
                              </tr>

    <?php
                        }
                    }//end if($modeloId !== $NO_HAVE)
                    else {
                        foreach ($qtde as $codeProd => $vetor_valor) {
                            foreach ($vetor_valor as $valor => $quantidade) {
                                    $rs = null;
                                    $filtro['ogp_ativo'] = 1;
                                    $filtro['ogp_id'] = $codeProd;
                                    $filtro['ogp_mostra_integracao_com_loja'] = '1';
                                    $filtro['opr'] = 1;
                                    $ret = (new Produto)->obtermelhorado($filtro, null, $rs);
                                    if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
                                    else $rs_row = pg_fetch_array($rs);

                                    $total_geral += $valor * $quantidade;
                                    $total_geral_pin_epp_cash  += (new ConversionPINsEPP)->get_ValorEPPCash('E',$valor)*$quantidade;
?>
                              <tr class="text-center trListagem">
                                <td class="text-left">
                                    <a href="/game/modelos.php?prod=<?=$codeProd?>" class="link_azul"><?php echo $rs_row['ogp_nome']?></a> 
                                </td>
                                <td><?php echo $rs_row['ogp_iof'] ? "Incluso" : "";?></td>
                                <td><?php echo number_format($valor, 2, ',', '.')?></td>
                                <td>
    <?php
                                if(htmlspecialchars($quantidade, ENT_QUOTES) > 1)
                                {
    ?>                            
                                    <span class="glyphicon glyphicon-minus t5 color-green-button c-pointer leftright5 font20" qtde="<?php echo htmlspecialchars($quantidade, ENT_QUOTES);?>" mod="<?php echo $modeloId; ?>" codeProd="<?php echo $codeProd; ?>" valor="<?php echo $valor; ?>" title="Remover"></span>
    <?php
                                }
    ?>
                                    <input class="w40 align-center" type="text" readonly="readonly" value="<?php echo htmlspecialchars($quantidade, ENT_QUOTES);?>"> 
                                    <span class="glyphicon glyphicon-plus t5 color-green-button c-pointer leftright5 font20" title="Adicionar" qtde="<?php echo htmlspecialchars($quantidade, ENT_QUOTES);?>" mod="<?php echo $modeloId; ?>" codeProd="<?php echo $codeProd; ?>" valor="<?php echo $valor; ?>"></span>
                                    <span class="glyphicon glyphicon-remove t5 txt-vermelho p-left-3 c-pointer font20" title="Excluir" mod="<?php echo $modeloId; ?>" codeProd="<?php echo $codeProd; ?>" valor="<?php echo $valor; ?>"></span>
                                </td>
                                <td>R$ <?php echo number_format($valor*$quantidade, 2, ',', '.');?></td>
                                <td><?php echo get_info_EPPCash_NO_Table((new ConversionPINsEPP)->get_ValorEPPCash('E',$valor)*$quantidade);?></td>
                              </tr>
<?php
                            }//end foreach 
                        }//end foreach
                    }//end else do if($modeloId !== $NO_HAVE)
                }//end foreach
?>
                        <tr class="bg-cinza-claro text-center">
                            <td colspan="3">&nbsp;</td>
                            <td><strong>Total:</strong></td>
                            <td><?php echo number_format($total_geral, 2, ',', '.') ?></td>
                            <td><?php echo get_info_EPPCash_NO_Table($total_geral_pin_epp_cash); ?></td>
                        </tr>
                    </tbody>
                    </table>
                </div>
				
				<?php
				if(!isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
				?>
					<div class="row espacamento text-center">
						<a style="margin: 10px 0 0 0;" class="btn btn-success" href="<?= EPREPAG_URL_HTTPS ?>/game/conta/login.php">Para prosseguir faça login na sua conta E-prepag</a>
					</div>
				<?php
				}
				?>
				
            </div>
	
            <div class="row espacamento">
                <div class="col-md-6 col-xs-12 col-sm-12 alert alert-info">Nas compras de R$<?php echo $RISCO_GAMERS_VALOR_MIN_PARA_TAXA;?>,00 ou mais você não paga taxas bancárias</div>
                <div class="col-md-3 col-md-offset-1 col-xs-12 col-sm-12">
                    <a href="/game/" class="btn btn-primary">Continuar Comprando</a>
                </div>
                <div class="top10 col-sm-12 col-xs-12 hidden-md hidden-lg"></div>
                <div class="col-md-2 col-xs-12 col-sm-12">
                    <button type="submit" class="btn btn-success btn-confirm">Confirmar</button>
                </div>
            </div>
			
            <input type="hidden" name="acao" id="acao">
            <input type="hidden" name="mod" id="mod" value="">
            <input type="hidden" name="codeProd" id="codeProd" value="">
            <input type="hidden" name="valor" id="valor" value="">
            <input type="hidden" name="qtde" id="qtde" value="">
            <input type="hidden" name="totalCarrinho" value="<?php echo $total_geral;?>">
        </form>
<?php
            }
?>
            
        </div>
    </div>
</div>
</div>
<script>
   
    $(function(){
        $(".glyphicon-plus").click(function(){
            var qtd = parseInt($(this).attr("qtde").trim())+1;
            $("#qtde").val(qtd);
            $("#acao").val("u");
            $("#mod").val($(this).attr("mod"));
            $("#codeProd").val($(this).attr("codeProd"));
            $("#valor").val($(this).attr("valor"));
            $("#pagamento").attr("action","").attr("method","post").submit();
        });
        
        $(".glyphicon-minus").click(function(){
            var qtd = parseInt($(this).attr("qtde").trim())-1;
            $("#qtde").val(qtd);
            $("#acao").val("m");
            $("#mod").val($(this).attr("mod"));
            $("#codeProd").val($(this).attr("codeProd"));
            $("#valor").val($(this).attr("valor"));
            $("#pagamento").attr("action","").attr("method","post").submit();
        });
        
        $(".glyphicon-remove").click(function(){
            if(confirm("Deseja realmente excluir o produto do carrinho?")) {
                $("#acao").val("d");
                $("#qtde").val("");
                $("#mod").val($(this).attr("mod"));
                $("#codeProd").val($(this).attr("codeProd"));
                $("#valor").val($(this).attr("valor"));
                $("#pagamento").attr("action","").attr("method","post").submit();
            }
        });
        
    });
</script>
<?php
require_once DIR_WEB . "game/includes/footer.php";

function verificaQtdeCarrinho($qtde_nova = 0, $modelo =0, $pularTesteInicial = true, $codeProd = 0, $valor = 0){
    if($pularTesteInicial) {
        $carrinho = $_SESSION['carrinho'];
        $total_aux = 0;
        if(is_array($carrinho) && count($carrinho) > 0) {
            foreach($carrinho as $modeloId => $qtde){
                    if( $modeloId == $GLOBALS['NO_HAVE']) {
                        foreach ($qtde as $codeProd => $vetor_valor) {
                            foreach ($vetor_valor as $valor => $quantidade) {
                                $total_aux += $quantidade;
                            }
                        }
                    }elseif(is_numeric($qtde)){
                            $total_aux += $qtde;
                    }
            } //end foreach
            $total_aux++;
            if($total_aux > $GLOBALS['QTDE_MAX_ITENS']) {
                    return false;
            }
            else {
                    return true;
            }
        }//end if(is_array($carrinho) && count($carrinho) > 0) 
        else {
                return false;
        }
    }//end if($pularTesteInicial)
    else {
        return true;
    }//end else do if($pularTesteInicial)
} //end function verificaQtdeCarrinho
