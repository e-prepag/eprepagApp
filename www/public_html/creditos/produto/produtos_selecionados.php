<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/CarrinhoController.class.php";
require_once DIR_INCS . "gamer/constantes.php";
require_once "/www/class/pdv/classChaveMestra.php";


/*

	PARA ELIMINAR TRAVA / LIMITE
	
	Substituir o ID da e-prepagTESTES pelo ID do cliente
	
	14549 -> Rei dos Coins
	17371 -> e-prepagTESTES

*/


//Recupera carrinho do session
//$carrinho = $_SESSION['dist_carrinho'];
$controller = new CarrinhoController;
if(!isset($_SESSION['dist_usuarioGamesOperador_ser'])){
	$pdvInfo = unserialize($_SESSION["dist_usuarioGames_ser"]);
	$usuario = $pdvInfo->ug_id;
}else{
	$pdvInfo = unserialize($_SESSION['dist_usuarioGamesOperador_ser']);
	$usuario = $pdvInfo->ugo_ug_id; //    ugo_id
}
/* ORIGINAL
if((isset($_POST['acao']) && $_POST['acao'] != "" && isset($_POST["mod"]) && $_POST["mod"] != "") || $_GET["sms_ok"])
    $controller->actions($_POST);
*/

$_SERVER["REQUEST_METHOD"] == "POST" ? $controller->actions($_POST) : $controller->actions($_GET);

if(empty($_SESSION['dist_carrinho'])){
    header("Location: /creditos");
    die();
}
$modelos = $controller->getCarrinho($_SESSION['dist_carrinho']);
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";

$classChave = new ChaveMestra();
$ipSeguro = $classChave->verificarIPUtilizado($usuario);
$_SESSION["seg_ip"] = $ipSeguro; 
$urlDirect = ($ipSeguro === true) ? "/creditos/pagamento/": "/creditos/chave.php"; //$ipSeguro === true 

?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10 col-lg-10 col-xs-12 col-sm-12">
            <div class="row">
                <div class="col-md-12 espacamento col-lg-12 col-xs-12 col-sm-12">
                    <strong>Produtos selecionados</strong>
                </div>
            </div>
            <form name="pagamento" id="pagamento" action="<?php echo $urlDirect;?>">
                <div class="row txt-cinza top10 bottom20 hidden-lg hidden-md">
<?php                                
                if(is_array($modelos) && !empty($modelos))
                {
                    $total = 0;
                    $totalLiquido = 0;
                    $totalComissao = 0;
                    
                    foreach($modelos as $modelo)
                    {
                        if(is_array($modelo) && $modelo['modelo'] instanceof ProdutoModelo)
                        {
							// verificação de valor 
							if($modelo['modelo']->getValor() < 0){
								header("location: " . EPREPAG_URL_HTTPS . "/creditos/produtos.php");
								exit;
							}
							
                            // Capturando produto
                            $filtro['ogp_id'] = $modelo['modelo']->getProdutoId();
                            $instProduto = new Produto;
                            $produto = $instProduto->obter($filtro,"ogp_id", $resposta);
                            $resposta_row = pg_fetch_array($resposta);
                            // Fim Captura Produto
                            $total += $modelo['geral'];
                            $valorLiquido = $modelo['geral']-$modelo['comissao'];
                            $totalComissao += $modelo['comissao'];
                            $totalLiquido += $valorLiquido;
?>
                        <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                            <div class="row">
                                <div class="col-xs-3 col-sm-5">
                                    Produto:
                                </div>
                                <div class="col-xs-9 col-sm-7">
                                    <strong><?php echo ($modelo['modelo']->getNome()!="") ? $modelo['modelo']->getNomeProduto(). " - ".$modelo['modelo']->getNome() : $modelo['modelo']->getNomeProduto(); ?></strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    IOF.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                    <?php echo (($resposta_row["ogp_iof"] == 1)?"Incluso":"");?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5 nowrap">
                                    Valor unitário:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($modelo['modelo']->getValor(), 2, ',', '.')?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Qtde.:
                                </div>
                                <div class="col-xs-7 col-sm-7 nowrap">
<?php
                                if($modelo['qtd'] > 1)
                                {
?>                                  <button class="btn btn-sm btn-success t0 glyphicon fontnormal glyphicon-minus minus<?php echo $modelo['modelo']->getId(); ?>" qtde="<?php echo $modelo['qtd'];?>" mod="<?php echo $modelo['modelo']->getId(); ?>" title="Remover"></button>
<?php
                                }
?>
                                <input class="w30 iptQtd" mod="<?php echo $modelo['modelo']->getId(); ?>" qtde="<?php echo $modelo['qtd'];?>" type="text" <?php if($controller->usuarioId == 14549){echo 'maxlength="4"';}else{echo 'maxlength="3"';} ?> value="<?php echo $modelo['qtd'];?>">
                                <button class="btn btn-primary t0 btn-sm glyphicon glyphicon-ok ok<?php echo $modelo['modelo']->getId(); ?> txt-verde bg-cinza-claro c-pointer hidden" aria-describedby="sizing-addon3" title="Confirmar" qtde="<?php echo $modelo['qtd'];?>" mod="<?php echo $modelo['modelo']->getId(); ?>"></button>
                                <button class="btn btn-sm btn-success t0 glyphicon fontnormal glyphicon-plus plus<?php echo $modelo['modelo']->getId(); ?>" title="Adicionar" qtde="<?php echo $modelo['qtd'];?>" mod="<?php echo $modelo['modelo']->getId(); ?>"></button>
                                <button class="btn btn-danger btn-sm t0 glyphicon glyphicon-remove" title="Excluir"  mod="<?php echo $modelo['modelo']->getId(); ?>"></button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Preço Total:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($modelo['geral'], 2, ',', '.');?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Comissão:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($modelo['comissao'], 2, ',', '.');?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Valor Líquido:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($valorLiquido, 2, ',', '.');?>
                                </div>
                            </div>
                        </div>
<?php
                        }elseif(is_array($modelo)){
                            foreach($modelo as $valor){
                                foreach($valor as $prod){
                                    $total += $prod['geral'];
                                    $valorLiquido = $prod['geral']-$prod['comissao'];
                                    $totalComissao += $prod['comissao'];
                                    $totalLiquido += $valorLiquido;
									
									if($prod["valor"] < 0){
										header("location: " . EPREPAG_URL_HTTPS . "/creditos/produtos.php");
										exit;
									}
									
?>
                                    <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                                        <div class="row">
                                            <div class="col-xs-3 col-sm-5">
                                                Produto:
                                            </div>
                                            <div class="col-xs-9 col-sm-7">
                                                <strong><?php echo $prod['produto']["ogp_nome"]. " - ".$prod['valor']; ?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                IOF.:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                                <?php echo (($prod["produto"]["ogp_iof"] == 1)?"Incluso":"");?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5 nowrap">
                                                Valor unitário:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                               R$ <?php echo number_format($prod["valor"], 2, ',', '.')?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                Qtde.:
                                            </div>
                                            <div class="col-xs-7 col-sm-7 nowrap">
        <?php
                                            if($prod['qtd'] > 1)
                                            {
        ?>                                      <button class="btn btn-sm btn-success t0 glyphicon fontnormal glyphicon-minus minus<?php echo $prod["codeProd"]; ?>" qtde="<?php echo $prod['qtd'];?>" mod="<?php echo $prod["modelo"]; ?>" codeProd="<?php echo $prod["codeProd"]; ?>" valor="<?php echo $prod["valor"];?>" title="Remover"></button>
        <?php
                                            }
        ?>                                      
                                                <input class="w30 iptQtd" value="<?php echo $prod['qtd'];?>" <?php if($controller->usuarioId == 14549){echo 'maxlength="4"';}else{echo 'maxlength="3"';} ?> qtde="<?php echo $prod['qtd'];?>" mod="<?php echo $prod["modelo"]; ?>" codeProd="<?php echo $prod["codeProd"]; ?>" valor="<?php echo $prod["valor"];?>">
                                                <button class="btn btn-primary t0 btn-sm glyphicon glyphicon-ok ok<?php echo $prod['codeProd']; ?> txt-verde bg-cinza-claro c-pointer hidden" aria-describedby="sizing-addon3" title="Confirmar" qtde="<?php echo $prod['qtd'];?>" mod="<?php echo $prod["modelo"] ?>"></button>
                                                <button class="btn btn-sm btn-success t0 glyphicon fontnormal glyphicon-plus plus<?php echo $prod['codeProd']; ?>" title="Adicionar" qtde="<?php echo $prod['qtd'];?>" mod="<?php echo $prod["modelo"]; ?>" codeProd="<?php echo $prod["codeProd"]; ?>" valor="<?php echo $prod["valor"];?>"></button>
                                                <button class="btn btn-danger btn-sm t0 glyphicon glyphicon-remove" title="Excluir"  mod="<?php echo $prod["modelo"]; ?>" codeProd="<?php echo $prod["codeProd"]; ?>" valor="<?php echo $prod["valor"];?>"></button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                Preço Total:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                               R$ <?php echo number_format($prod['geral'], 2, ',', '.');?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                Comissão:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                               R$ <?php echo number_format($prod['comissao'], 2, ',', '.');?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                Valor Líquido:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                               R$ <?php echo number_format($valorLiquido, 2, ',', '.');?>
                                            </div>
                                        </div>
                                    </div>
<?php
                                }
                            }
                        }
                    }
?>
                    <div class="col-xs-12 col-sm-12 hidden-lg hidden-md bg-cinza-claro espacamento borda-fina">
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                <strong>Total:</strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                Preço 
                            </div>
                            <div class="col-xs-7 col-sm-7">
                               R$ <?php echo number_format($total, 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                Comissão 
                            </div>
                            <div class="col-xs-7 col-sm-7">
                               R$ <?php echo number_format($totalComissao, 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                Valor Líquido
                            </div>
                            <div class="col-xs-7 col-sm-7">
                               R$ <?php echo number_format($totalLiquido, 2, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
<?php
                }
?>
                </div>
                <div class="row txt-cinza espacamento hidden-sm hidden-xs">
                    <div class="col-md-12 bg-cinza-claro col-lg-12 col-xs-12 col-sm-12">
                        <table class="table bg-branco hidden-sm hidden-xs txt-preto fontsize-p">
                        <thead>
                          <tr class="bg-cinza-claro text-center">
                            <th class="txt-left">Produto</th>
                            <th>I.O.F.</th>
                            <th>Valor unitário</th>
                            <th>Qtde.</th>
                            <th>Total</th>
                            <th>Comissão</th>
                            <th>Valor líquido</th>
                          </tr>
                        </thead>
                        <tbody>
    <?php 
                if(is_array($modelos) && !empty($modelos))
                {
                    $total = 0;
                    $totalLiquido = 0;
                    $totalComissao = 0;
                    
                    foreach($modelos as $modelo)
                    {
                        if(is_array($modelo) && $modelo['modelo'] instanceof ProdutoModelo)
                        {
                            // Capturando produto
                            $filtro['ogp_id'] = $modelo['modelo']->getProdutoId();
                            $instProduto = new Produto;
                            $produto = $instProduto->obter($filtro,"ogp_id", $resposta);
                            $resposta_row = pg_fetch_array($resposta);
                            // Fim Captura Produto
                            $total += $modelo['geral'];
                            $valorLiquido = $modelo['geral']-$modelo['comissao'];
                            $totalComissao += $modelo['comissao'];
                            $totalLiquido += $valorLiquido;
							
							if($modelo['modelo']->getValor() < 0){
								header("location: " . EPREPAG_URL_HTTPS . "/creditos/produtos.php");
								exit;
							}
							
    ?>                        
                          <tr class="text-center">
                            <td class="text-left"><?php echo ($modelo['modelo']->getNome()!="") ? $modelo['modelo']->getNomeProduto(). " - ".$modelo['modelo']->getNome() : $modelo['modelo']->getNomeProduto(); ?></td>
                            <td><?php echo (($resposta_row["ogp_iof"] == 1)?"Incluso":"");?></td>
                            <td>R$ <?php echo number_format($modelo['modelo']->getValor(), 2, ',', '.')?></td>
                            <td class="w160">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-btn">
<?php
                                if($modelo['qtd'] > 1)
                                {
?>
                                        <span class="btn btn-primary t0 btn-sm glyphicon glyphicon-minus minus<?php echo $modelo['modelo']->getId(); ?> t5 bg-cinza-claro txt-vermelho c-pointer" qtde="<?php echo $modelo['qtd'];?>" mod="<?php echo $modelo['modelo']->getId(); ?>" title="Remover"></span>
    <?php
                                    }
?>
                                    </span>
                                    <input class="w30 form-control input-sm iptQtd" <?php if($controller->usuarioId == 14549){echo 'maxlength="4"';}else{echo 'maxlength="3"';} ?> mod="<?php echo $modelo['modelo']->getId(); ?>" qtde="<?php echo $modelo['qtd'];?>" type="text" value="<?php echo $modelo['qtd'];?>"> 
                                    <span class="input-group-btn" id="sizing-addon3">
                                        <span class="btn btn-primary t0 btn-sm glyphicon glyphicon-ok ok<?php echo $modelo['modelo']->getId(); ?> t5 txt-verde bg-cinza-claro c-pointer hidden" aria-describedby="sizing-addon3" title="Confirmar" qtde="<?php echo $modelo['qtd'];?>" mod="<?php echo $modelo['modelo']->getId(); ?>"></span>
                                        <span class="btn btn-primary t0 btn-sm glyphicon glyphicon-plus plus<?php echo $modelo['modelo']->getId(); ?> t5 txt-verde bg-cinza-claro c-pointer" aria-describedby="sizing-addon3" title="Adicionar" qtde="<?php echo $modelo['qtd'];?>" mod="<?php echo $modelo['modelo']->getId(); ?>"></span>
                                        <span class="btn btn-primary t0 btn-sm glyphicon glyphicon-remove <?php echo $modelo['modelo']->getId(); ?> t5 txt-vermelho bg-cinza-claro p-left-3 c-pointer" aria-describedby="sizing-addon3" title="Excluir"  mod="<?php echo $modelo['modelo']->getId(); ?>"></span>
                                    </span>
                                </div>
                                
                                
    
                            </td>
                            <td>R$ <?php echo number_format($modelo['geral'], 2, ',', '.');?></td>
                            <td>R$ <?php echo number_format($modelo['comissao'], 2, ',', '.');?></td>
                            <td>R$ <?php echo number_format($valorLiquido, 2, ',', '.');?></td>
                          </tr>
<?php
                        }elseif(is_array($modelo)){
                            foreach($modelo as $valor){
                                foreach($valor as $prod){
                                    $total += $prod['geral'];
                                    $valorLiquido = $prod['geral']-$prod['comissao'];
                                    $totalComissao += $prod['comissao'];
                                    $totalLiquido += $valorLiquido;
									
									if($prod["valor"] < 0){
										header("location: " . EPREPAG_URL_HTTPS . "/creditos/produtos.php");
										exit;
									}
									
?>
                                    <tr class="text-center">
                                        <td class="text-left"><?php echo $prod['produto']["ogp_nome"]. " - ".$prod['valor']; ?></td>
                                        <td><?php echo (($prod["produto"]["ogp_iof"] == 1)?"Incluso":"");?></td>
                                        <td>R$ <?php echo number_format($prod["valor"], 2, ',', '.')?></td>
                                        <td class="w160">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-btn">
<?php
                                                if($prod['qtd'] > 1)
                                                {
?>
                                                    <span class="btn btn-primary t0 btn-sm glyphicon glyphicon-minus minus<?php echo $prod["codeProd"]; ?> t5 bg-cinza-claro txt-vermelho c-pointer" qtde="<?php echo $prod['qtd'];?>" mod="<?php echo $prod["modelo"]; ?>" codeProd="<?php echo $prod["codeProd"]; ?>" valor="<?php echo $prod["valor"];?>" title="Remover"></span>
<?php
                                                }
?>
                                                </span>
                                                <input type="text" class="w30 form-control input-sm iptQtd" value="<?php echo $prod['qtd'];?>" <?php if($controller->usuarioId == 14549){echo 'maxlength="4"';}else{echo 'maxlength="3"';} ?> qtde="<?php echo $prod['qtd'];?>" mod="<?php echo $prod["modelo"]; ?>" codeProd="<?php echo $prod["codeProd"]; ?>" valor="<?php echo $prod["valor"];?>"> 
                                                <span class="input-group-btn" id="sizing-addon3">
                                                    <span class="btn btn-primary t0 btn-sm glyphicon glyphicon-ok ok<?php echo $prod["codeProd"]; ?> t5 txt-verde bg-cinza-claro c-pointer hidden" aria-describedby="sizing-addon3" title="Confirmar" qtde="<?php echo $prod['qtd'];?>" mod="<?php echo $prod["modelo"]; ?>" codeProd="<?php echo $prod["codeProd"]; ?>" valor="<?php echo $prod["valor"];?>"></span>
                                                    <span class="btn btn-primary t0 btn-sm glyphicon glyphicon-plus plus<?php echo $prod["codeProd"]; ?> t5 txt-verde bg-cinza-claro c-pointer" aria-describedby="sizing-addon3" title="Adicionar" qtde="<?php echo $prod['qtd'];?>" mod="<?php echo $prod["modelo"]; ?>" codeProd="<?php echo $prod["codeProd"]; ?>" valor="<?php echo $prod["valor"];?>"></span>
                                                    <span class="btn btn-primary t0 btn-sm glyphicon glyphicon-remove t5 txt-vermelho bg-cinza-claro p-left-3 c-pointer" aria-describedby="sizing-addon3" title="Excluir" mod="<?php echo $prod["modelo"]; ?>" codeProd="<?php echo $prod["codeProd"]; ?>" valor="<?php echo $prod["valor"];?>"></span>
                                                </span>
                                            </div>



                                        </td>
                                        <td>R$ <?php echo number_format($prod['geral'], 2, ',', '.');?></td>
                                        <td>R$ <?php echo number_format($prod['comissao'], 2, ',', '.');?></td>
                                        <td>R$ <?php echo number_format($valorLiquido, 2, ',', '.');?></td>
                                    </tr>
<?php
                                }
                            }
                        }
                    }
                }
?>
                          <tr class="bg-cinza-claro text-center">
                            <td colspan="3">&nbsp;</td>
                            <td><strong>Total:</strong></td>
                            <td>R$ <?php echo number_format($total, 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($totalComissao, 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($totalLiquido, 2, ',', '.'); ?></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                </div>
                <div class="row bottom20">
                    <div class="col-md-3 col-md-offset-7 col-xs-12 col-sm-12">
                        <a href="/creditos/produtos.php" class="btn btn-primary">Continuar Comprando</a>
                    </div>
                    <div class="top10 col-sm-12 col-xs-12 hidden-md hidden-lg"></div>
                    <div class="col-md-2 col-xs-12 col-sm-12">
                    <button type="submit" class="btn btn-success">Confirmar</button>
                </div>
                </div>
                <input type="hidden" name="acao" id="acao">
                <input type="hidden" name="mod" id="mod" value="">
                <input type="hidden" name="codeProd" id="codeProd" value="">
                <input type="hidden" name="valor" id="valor" value="">
                <input type="hidden" name="qtde" id="qtde" value="">
            </form>
        </div>
        <div class="col-md-2 p-top10">
            <div class="row pull-right p-8">
                <p class="txt-azul-claro"><strong>Dúvidas ou problemas para concluir a venda?</strong></p>
                <p class="txt-cinza">Por favor, avise-nos entrando em contato com o nosso <a href="/game/suporte.php" target="_blank">suporte</a>.</p>
            </div>
        </div>
    </div>
</div>
<div id="modal-error" class="modal fade" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title txt-vermelho"><strong>Quantidade não permitida.</strong></h4>
            </div>
            <div class="modal-body alert alert-danger txt-vermelho">
                <p>Quantidade de produtos permitida é de 1 até <?php if($controller->usuarioId == 14549){echo 1000;}else{echo $LIMITE_QUANTIDADE_PINS;} ?> unidades.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<script>
    var limite = <?php if($controller->usuarioId == 14549){echo 1000;}else{echo $LIMITE_QUANTIDADE_PINS;} ?>;
    
    function setAndSendParans(qtde, acao, mod, cod = null, val = null){
        
        if(qtde > limite){
            qtde = limite;
            $('#modal-error').modal();
        }
        
        $("#qtde").val(qtde);
        $("#acao").val(acao);
        $("#mod").val(mod);
        $("#codeProd").val(cod);
        $("#valor").val(val);
        
        if(acao != "d"){
            if($('body').attr('class') == 'modal-open' ) {
                $('#modal-error').on('hide.bs.modal', function (event) {
                    $("#pagamento").attr("action","").attr("method","post").submit();
                }); 
            } else{
                $("#pagamento").attr("action","").attr("method","post").submit();
            }
        } else{
            $("#pagamento").attr("action","").attr("method","post").submit();
        }
              
    }
    
    $(function(){
        
        $(".iptQtd").focus(function(){
            
            if(typeof($(this).attr("codeProd")) !== "undefined"){
                var mod = $(this).attr("codeProd");
            }else{
                var mod = $(this).attr("mod");
            }

            $(".minus"+mod).addClass("hidden");
            $(".plus"+mod).addClass("hidden");
            $(".ok"+mod).removeClass("hidden");

        }).blur(function(){
            
            if(parseInt($(this).val()) > limite){
                $(this).val(limite);
                $('#modal-error').modal();
            }
            
            if($("#qtde").val() != $(this).val() && $(this).val() > 0){
                
                var mod = $(this).attr("mod");
                
                if(mod != 'NO HAVE'){
                    setAndSendParans($(this).val(), "u", mod);
                }else{
                    var cod = $(this).attr("codeProd");
                    var val = $(this).attr("valor");
                    setAndSendParans($(this).val(), "u", mod, cod, val);
                }
                
            }else if($(this).val() <= 0){
                
                $(this).val($(this).attr("qtde"));
            
            }
            
            return false;
            
        }).keydown(function(e){
            
            var x = e.which || e.keyCode;
            if(x == 13 && $("#qtde").val() != parseInt($(this).val()) && parseInt($(this).val()) > 0)
            {
                var mod = $(this).attr("mod");
                
                if(mod != 'NO HAVE'){
                    setAndSendParans($(this).val(), "u", mod);
                }else{
                    var cod = $(this).attr("codeProd");
                    var val = $(this).attr("valor");
                    setAndSendParans($(this).val(), "u", mod, cod, val);
                }

            <?php // validando se foi digitado apenas caracteres válidos (números, enter, delete, backspace, setas) ?>
            }else if(!(x == 8 || x == 46 || x == 16 || (x >= 48 && x <= 57) || (x >= 96 && x <= 105) || (x >= 37 && x <= 40))){
                
                return false;
            }
            
            
        }).keyup(function(e){
            var x = e.which || e.keyCode;
            
            if(x == 13 && $("#qtde").val() != $(this).val() && $(this).val() > 0)
            {
                
                var mod = $(this).attr("mod");
                
                if(mod != 'NO HAVE'){
                    setAndSendParans($(this).val(), "u", mod);
                }else{
                    var cod = $(this).attr("codeProd");
                    var val = $(this).attr("valor");
                    setAndSendParans($(this).val(), "u", mod, cod, val);
                }
            
            <?php // validando se foi digitado apenas caracteres válidos (números, enter, delete, backspace, setas) ?>
            }else if(!(x == 8 || x == 46 || x == 16 || (x >= 48 && x <= 57) || (x >= 96 && x <= 105) || (x >= 37 && x <= 40))){
                
                return false;
            }
            
        });
        
        $(".glyphicon-plus").click(function(){

            var qtd = parseInt($(this).attr("qtde").trim())+1;
            if(qtd > limite){
                $('#modal-error').modal();
                return false;
            }
            var mod = $(this).attr("mod");
            if(mod != 'NO HAVE'){
                setAndSendParans(qtd, "u", mod);
            }else{
                var cod = $(this).attr("codeProd");
                var val = $(this).attr("valor");
                setAndSendParans(qtd, "u", mod, cod, val);
            }
            return false;
        });
        
        $(".glyphicon-minus").click(function(){
            var qtd = parseInt($(this).attr("qtde").trim())-1;
            var mod = $(this).attr("mod");
            if(mod != 'NO HAVE'){
                setAndSendParans(qtd, "u", mod);
            }else{
                var cod = $(this).attr("codeProd");
                var val = $(this).attr("valor");
                setAndSendParans(qtd, "u", mod, cod, val);
            }
            return false;
        });
        
        $(".glyphicon-remove").click(function(){
            
            var mod = $(this).attr("mod");
            if(mod != 'NO HAVE'){
                setAndSendParans("", "d", mod);
            }else{
                var cod = $(this).attr("codeProd");
                var val = $(this).attr("valor");
                setAndSendParans("", "d", mod, cod, val);
            }
            return false;
        });
        
    });
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>