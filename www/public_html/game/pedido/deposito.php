<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/PagamentoOfflineController.class.php';

$controller = new HeaderController;
$controller->setHeader();

if (isset($_POST['submit'])) {
    $_SESSION['pagamento.pagto_ja_fiz'] = true;
    Util::redirect("/game/pagamento/finaliza_venda.php");
}

//Definindo valor máximo
if($controller->usuario->b_IsLogin_pagamento_free()) {
        $total_diario_const = $RISCO_GAMERS_FREE_TOTAL_DIARIO;
        $pagamentos_diario_const = $RISCO_GAMERS_FREE_PAGAMENTOS_DIARIO;
//	Gamers VIP- Pagamento Online = no max R$1000,00 por día por usuário (ver getVendasMoneyTotalDiarioOnline()) em até 20 vezes
} elseif($controller->usuario->b_IsLogin_pagamento_vip()) {
        $total_diario_const = $RISCO_GAMERS_VIP_TOTAL_DIARIO;
        $pagamentos_diario_const = $RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO;
//	Gamers - Pagamento Online = no max R$450,00 por día por usuário (ver getVendasMoneyTotalDiarioOnline()) em até 10 vezes
} else {
        $total_diario_const = $RISCO_GAMERS_TOTAL_DIARIO;
        $pagamentos_diario_const = $RISCO_GAMERS_PAGAMENTOS_DIARIO;
}

//Recupra carrinho do session
$carrinho = $GLOBALS['_SESSION']['carrinho'];

if (!$carrinho || count($carrinho) == 0) {
    $msg = "<p>Carrinho vazio.<br>Por favor, selecione algum produto.<p>";
}//end  if(!$carrinho || count($carrinho) == 0)
?>
<div class="container txt-azul-claro bg-branco">
    <div class="row top20">
        <div class="col-md-12">
            <div class="row top20">
                <div class="col-md-12">
                    <span class="glyphicon glyphicon-triangle-right graphycon-big color-blue pull-left"></span>
                    <strong class="pull-left top15 color-blue font20">Depósito em conta</strong>  
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 ">
                    <hr class="border-blue">
                </div>
            </div>
            <?php
            if ($msg != "") {
                ?>
                <div class="row">
                    <div class="col-md-12 espacamento text-center txt-vermelho">
                        <?php echo $msg; ?>
                    </div>
                    <div class="col-md-3 col-md-offset-7 espacamento">
                        <a href="<?php echo (isset($link) && $link != "") ? $link : "/game/"; ?>" class="btn btn-primary">Voltar</a>
                    </div>
                </div>
                <script>
                    manipulaModal(1, "<?php echo $msg; ?>", "Erro");
                </script>
                <?php
            } //end if($msg != "")
            else {
                ?>
                <div class="row txt-cinza espacamento top20">
                    <div class="col-md-12 fontsize-pp bg-cinza-claro">
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
                                
                                // Obtem o valor total deste pedido
                                $libera_pagamento = array(
                                    'Deposito' => true,
                                );

                                $pagtoInvalido = false;
                                
                                $total_geral_pin_epp_cash = 0;
                                foreach ($carrinho as $modeloId => $qtde) {
                                    if($modeloId !== $GLOBALS['NO_HAVE']) {
                                        $tipoId['produtoModeloId'] = $modeloId;
                                        $libera_pagamento = getMeiosPagamentosBloqueados($tipoId, $libera_pagamento);

                                        if(!$libera_pagamento['Deposito']){
                                            $pagtoInvalido = true;
                                        }

                                        $qtde = intval($qtde);
                                        $rs = null;
                                        $filtro['ogpm_ativo'] = 1;
                                        $filtro['ogpm_id'] = $modeloId;
                                        $filtro['com_produto'] = true;
                                        // Debug reinaldops
                                        if (isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                                            if ($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                                                $filtro['show_treinamento'] = 1;
                                            }
                                        }
                                        $instProdutoModelo = new ProdutoModelo;
                                        $ret = $instProdutoModelo->obter($filtro, null, $rs);
                                        if ($rs && pg_num_rows($rs) != 0) {
                                            $rs_row = pg_fetch_array($rs);
                                            $total_geral += $rs_row['ogpm_valor'] * $qtde;
                                            $total_geral_pin_epp_cash += $rs_row['ogpm_valor_eppcash'] * $qtde;
                                            $instProduto = new Produto;
                                            $iof = $instProduto->buscaIOF($modeloId) ? "Incluso" : "";
                                            ?>

                                            <tr class="text-center trListagem">
                                                <td class="text-left">
                                                    <input name="produtos[]" id="produtos" type="hidden" value="<?php echo $rs_row['ogpm_id']; ?>" />
                                                    <input name="v<?php echo $rs_row['ogpm_id']; ?>" id="v<?php echo $rs_row['ogpm_id']; ?>" type="hidden" value="<?php echo $rs_row['ogpm_valor']; ?>" />
                                                    <input name="e<?php echo $rs_row['ogpm_id']; ?>" id="e<?php echo $rs_row['ogpm_id']; ?>" type="hidden" value="<?php echo $rs_row['ogpm_valor_eppcash']; ?>" />
                                                    <input name="q<?php echo $rs_row['ogpm_id']; ?>" id="q<?php echo $rs_row['ogpm_id']; ?>" type="hidden" value="<?php echo $qtde; ?>" />
                                                    <?php echo $rs_row['ogp_nome'] ?>
                                                    <?php if ($rs_row['ogpm_nome'] != "") { ?> - <?php echo $rs_row['ogpm_nome'] ?><?php } ?>
                                                </td>
                                                <td><?php echo $iof; ?></td>
                                                <td><?php echo number_format($rs_row['ogpm_valor'], 2, ',', '.') ?></td>
                                                <td><?php echo htmlspecialchars($qtde, ENT_QUOTES); ?></td>
                                                <td><?php echo number_format($rs_row['ogpm_valor'] * $qtde, 2, ',', '.'); ?></td>
                                                <td><?php echo get_info_EPPCash_NO_Table($rs_row['ogpm_valor_eppcash'] * $qtde); ?></td>
                                            </tr>

                                            <?php
                                        }
                                    }//end if($modeloId !== $GLOBALS['NO_HAVE']) 
                                    else {
                                        foreach ($qtde as $codeProd => $vetor_valor) {
                                            foreach ($vetor_valor as $valor => $quantidade) {
                                                $total_geral += $valor * $quantidade;
                                                $total_geral_pin_epp_cash += (new ConversionPINsEPP)->get_ValorEPPCash('E',$valor) * $quantidade;
                                                $rs = null;
                                                $filtro['ogp_ativo'] = 1;
                                                $filtro['ogp_id'] = $codeProd;
                                                $filtro['ogp_mostra_integracao_com_loja'] = '1';
                                                $filtro['opr'] = 1;
                                                $ret = Produto::obtermelhorado($filtro, null, $rs);
                                                if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
                                                else $rs_row = pg_fetch_array($rs);

                                            ?>

                                            <tr class="text-center trListagem">
                                                <td class="text-left"><?php echo $rs_row['ogp_nome']; ?></td>
                                                <td><?php echo $rs_row['ogp_iof'] ? "Incluso" : "";?></td>
                                                <td><?php echo number_format($valor, 2, ',', '.'); ?></td>
                                                <td><?php echo htmlspecialchars($quantidade, ENT_QUOTES); ?></td>
                                                <td><?php echo number_format($valor * $quantidade, 2, ',', '.'); ?></td>
                                                <td><?php echo get_info_EPPCash_NO_Table((new ConversionPINsEPP)->get_ValorEPPCash('E',$valor)*$quantidade); ?></td>
                                            </tr>

                                            <?php
                                            }//end foreach 
                                        }//end foreach
                                    }//end else do if($modeloId !== $GLOBALS['NO_HAVE'])
                                        
                                }

                                if ($total_geral > $total_diario_const) {
                                    $msg = "O valor m\u00E1ximo por Pedido \u00E9 de R$" . number_format($total_diario_const, 2, ",", ".");
                                    echo "<script>manipulaModal(1,'" . str_replace("\n", "\\n", $msg) . "','Erro') ; $('#modal-load').on('hidden.bs.modal', function () { location.href='/game/pedido/passo-1.php' });</script>";

                                    die;
                                }
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
                </div>
<?php
                if($pagtoInvalido){
?>
                <div class="col-md-12">
                    <div class="alert alert-danger top20" id="erro" role="alert">
                        <span class="glyphicon t0 glyphicon-exclamation-sign" aria-hidden="true"></span>
                        <span class="sr-only">Error:</span>
                        Erro: forma de pagamento inválida no momento.
                    </div>
                </div>
<?php
                }
                else{
                    
                
?>
                <div class="col-md-12 text-center bottom20">
                    <form method="post">
                        <input type="submit" name="submit" class="btn btn-success" value="Já fiz o depósito e quero informar os dados">
                    </form>
                </div>
                <div class="col-md-6 fontsize-p">
                    <h4 class="p-left25"><strong>Informações sobre o depósito</strong></h4>
                    <ul class="txt-preto">
                        <li>Após efetuar o depósito em uma das contas ao lado, é necessário acessar esta página efetuando um novo pedido de mesmo valor e clicar no botão acima para informar os dados referente ao depósito.</li>
                        <li>Após informar os dados de depósito, o prazo para liberação do pedido é de até 1 dia útil.</li>
                    </ul>
                </div>
                <div class="col-md-6 bottom20">
                    <h4><strong>Bancos Disponíveis</strong></h4>
                    <div class="col-md-6 bg-cinza-claro espacamento txt-preto">
                        <a href="http://bradesco.com.br" class="decoration-none txt-preto" target="_blank">
                            <img src="/imagens/bradesco.gif" height="26">
                            <p class="top10">Agência: <span class="pull-right">2062-1</span></p>
                            <p>Conta: <span class="pull-right">0030265-1</span></p>
                        </a>
                        <p><strong>Razão Social</strong>: E-Prepag Administradora de Cartões LTDA</p>
                        <p><strong>CNPJ</strong>: 19.037.276/0001-72</p>
                    </div>
                    <div class="col-md-6 bg-cinza-claro espacamento txt-preto">
                        <a href="http://www.bb.com.br/" class="decoration-none txt-preto" target="_blank">
                            <img src="/imagens/bancodobrasil.gif" height="26">
                            <p class="top10">Agência: <span class="pull-right">4328-1</span></p>
                            <p>Conta:  <span class="pull-right">2978-5</span></p>
                        </a>
                        <p><strong>Razão Social</strong>: E-Prepag Administradora de Cartões LTDA</p>
                        <p><strong>CNPJ</strong>: 19.037.276/0001-72</p>
                    </div>
                </div>
                <?php
                }
            }//end else do if($msg != "")
            ?>
        </div>
    </div>
</div>
</div>
<?php
require_once DIR_WEB . "game/includes/footer.php";
?>