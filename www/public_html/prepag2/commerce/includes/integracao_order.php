<div id="modal-info" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title txt-azul-claro" id="modal-title"><strong>E-Prepag Créditos para games</strong></h4>
            </div>
            <div class="modal-body">
                 
                <h2>O que é isso?</h2>
                <p>Este e-mail vem de sua conta cadastrada no jogo/site de origem, ou em alguns casos, do e-mail digitado na tela de pagamento anterior a esta. Esta identificação possibilita futuras consultas em seu histórico de compras na E-Prepag e para utilização de saldo em E-Prepag Cash. Caso seja seu primeiro acesso ao sistema E-Prepag você receberá automaticamente em seu e-mail a sua senha de acesso.</p>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid ">
    <div class="container txt-azul-claro bg-branco p-bottom40">
        <div class="row txt-cinza espacamento top20">
            <div class="col-md-8 col-lg-8 col-xs-12 col-sm-12 bg-cinza-claro">
                <?php foreach($data['cart'] as $item){ ?>
                <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                    <div class="row">
                        <div class="col-xs-3 col-sm-5">
                            Produto:
                        </div>
                        <div class="col-xs-9 col-sm-7">
                            <strong><?php echo $item['product']; ?></strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            IOF.:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                            <?php echo $item['iof']; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-sm-5">
                            Total:
                        </div>
                        <div class="col-xs-7 col-sm-7">
                           R$ <?php echo $item['price']; ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <table class="table bg-branco hidden-sm hidden-xs txt-preto">
                    <thead>
                        <tr class="bg-cinza-claro text-center">
                            <th class="txt-left">Produto</th>
                            <th>I.O.F.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['cart'] as $item){ ?>
                        <tr class="text-center trListagem">
                            <td class="text-left"><?php echo $item['product'];?></td>
                            <td><?php echo $item['iof'];?></td>
                            <td>R$ <?php echo $item['price'];?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            
        
            <div class="col-md-4 col-lg-4 col-sm-12 col-xs-12 txt-preto top10">
                <?php if(isset($data['venda_id']) && $data['venda_id']){ ?><p><strong>Pedido <?php echo $data['venda_id']; ?></strong></p><?php } ?>
                <p></p><strong>User:</strong> <?php echo $data['email']; ?> <span class="glyphicon glyphicon-info-sign txt-vermelho c-pointer t0"  data-toggle="modal" data-target="#modal-info"></span>
				<?php if($_SESSION['integracao_origem_id'] == "10427"){ ?>
				      <p style="font-size: 12px; margin-top: 8px; margin-bottom: 4px; color: green;">Para realizar o resgate, é necessário que o e-mail associado à sua conta E-prepag seja o mesmo utilizado em sua conta do jogo. Caso o e-mail da conta E-prepag seja diferente, entre em contato com nosso suporte.</p>
				<?php } ?>
                <p class="top10"><a href="https://<?php echo $_SERVER["SERVER_NAME"] ?>/game/suporte.php" target="_blank"><span class="glyphicon glyphicon-question-sign t0"></span> suporte</a></p>
            </div>
        
        </div>
        
<!--    </div>
</div>-->