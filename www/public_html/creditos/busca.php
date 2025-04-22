<?php

require_once "../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/ProdutosController.class.php";

$controller = new ProdutosController;

if(!isset($_POST['busca']) || $_POST['busca'] == "")
{
    $arrProdutos = array();
    $_POST['busca'] = "";
}else
{
    require_once DIR_CLASS . "util/Busca.class.php";
    
//    if((($controller->lanHouse && $controller->usuarios->b_VendasB2C()) || $controller->operadorTipo == $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_1]) && 
//    $controller->usuarios->getRiscoClassif() == 2)
//        $filtro['b2c'] = true;
//    else
    $filtro['b2c'] = false;
    $arrJsonFiles = unserialize(ARR_PRODUTOS_CREDITOS);
    $filtro['id_user'] = $controller->usuarios->getId();
    $filtro['_ug_possui_restricao_produtos'] = $controller->usuarios->getPossuiRestricaoProdutos();
    $filtro['origem'] = "busca";
    
    $busca = new Busca;
    $busca->setFullPath(DIR_JSON);
    $busca->setArrJsonFiles($arrJsonFiles);
    $busca->setFiltro($filtro);
    $arrProdutos = json_decode($busca->getJson($_POST['busca']));
}

require_once "includes/header.php";
?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10 ">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <p class="txt-cinza"><strong class="txt-azul-claro">Resultado da busca: </strong> <?php echo $_POST['busca'];?></p>
                </div>
            </div>
            
<?php 
            if(is_object($arrProdutos->games) && !empty($arrProdutos->games))
            {
?>
            <div class="row txt-cinza espacamento">
                <div class="col-md-12">
                    <p class="txt-azul-claro"><strong>Games</strong></p>
                </div>
            </div>
            <div class="row">
<?php
                $cont = 0;
                foreach($arrProdutos->games as $ind => $objProdutos)
                {
?>
                    <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3 txt-azul-claro text-center box-produto c-pointer top20 prod" id="<?php echo $objProdutos->object->id;?>">
                        <div class="thumbnail">
                            <div class="box-image">
<?php 
                        if( $objProdutos->object->imagem && 
                            $objProdutos->object->imagem != "" && 
                            file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $objProdutos->object->imagem))
                        {
?>
                                <img border="0" class="img-produto" src="<?php echo $GLOBALS['URL_DIR_IMAGES_PRODUTO'] . $objProdutos->object->imagem;?>">
<?php 
                        }
?>
                            </div>
                            <div class="caption align-center thumbail-body">      
                                <h4 class="color-blue" style="float:bottom">
                                    <strong><?php echo $objProdutos->object->nome; ?></strong>
                                </h4>
                                <h5 class="txt-cinza">
                                    <?php echo $objProdutos->object->operadora; ?>
                                </h5>
                                <button type="button" class="btn btn-success btn-block">Comprar</button>
                            </div>
                        </div>
                    </div>
<?php
                    $cont++;
                    if($cont == 4){
                        echo "</div><div class='row'>";
                        $cont = 0;
                    }
                    if($cont == 2){
                        echo "<div class='clearfix visible-xs-block'></div>";
                    }
                }
?>
            </div>
<?php
            }
            
            /*if(is_object($arrProdutos->servicos) && !empty($arrProdutos->servicos) && $filtro['b2c'])
            {
?>
            <div class="row txt-cinza espacamento <?php if(!empty($arrProdutos->games)) echo "borda-top-azul";?>">
                <div class="col-md-12">
                    <p class="txt-azul-claro"><strong>Serviços</strong></p>
                </div>
<?php
                foreach($arrProdutos->servicos as $ind => $objProdutos)
                {
?>
                    <div class="col-md-3 txt-azul-claro text-center top20 box-servico">
                        <a href="/creditos/servico/servico_detalhe.php?product=<?php echo $objProdutos->object->id;?>" >
                            <p class="box-img-servico">
                                <img border="0" class="img-produto" title="<?php echo $objProdutos->object->nome; ?>" alt="<?php echo $objProdutos->object->nome; ?>" src="<?php echo str_replace("http:///", "/", $objProdutos->object->imagem); ?>">
                            </p>
                        <p class="txt-azul bottom0 nome-produto">
                            <strong><?php echo $objProdutos->object->nome; ?></strong>
                        </p>
                        <p class="txt-verde bottom0 fontsize-p">
                            Comissão: <?php echo $objProdutos->object->comissao; ?>%
                        </p>
                        <p class="txt-azul-claro bottom0 fontsize-p">
                            R$ <?php echo number_format($objProdutos->object->preco, 2, ',', '.'); ?>
                        </p>
                        <button type="button" class="btn btn-success">Comprar</button>
                        </a>
                    </div>
<?php                       
                }
?>
            </div>
<?php
            }
            
            if(is_object($arrProdutos->jogos) && !empty($arrProdutos->jogos) && $filtro['b2c'])
            {
?>
            <div class="row txt-cinza espacamento <?php if(!empty($arrProdutos->games) || !empty($arrProdutos->servicos)) echo "borda-top-azul";?>">
                <div class="col-md-12">
                    <p class="txt-azul-claro"><strong>Jogos</strong></p>
                </div>
<?php
                foreach($arrProdutos->jogos as $ind => $objProdutos)
                {
?>
                    <div class="col-md-3 txt-azul-claro text-center top20 box-jogos">
                        <a href="/creditos/servico/servico_detalhe.php?product=<?php echo $objProdutos->object->id;?>" >
                            <p class="box-img-servico">
                                <img border="0" class="img-produto" title="<?php echo $objProdutos->object->nome; ?>" alt="<?php echo $objProdutos->object->nome; ?>" src="<?php echo str_replace("http:///", "/", $objProdutos->object->imagem); ?>">
                            </p>
                        <p class="txt-azul bottom0 nome-produto">
                            <strong><?php echo $objProdutos->object->nome; ?></strong>
                        </p>
                        <p class="txt-verde bottom0 fontsize-p">
                            Comissão: <?php echo $objProdutos->object->comissao; ?>%
                        </p>
                        <p class="txt-azul-claro bottom0 fontsize-p">
                            R$ <?php echo number_format($objProdutos->object->preco, 2, ',', '.'); ?>
                        </p>
                        <button type="button" class="btn btn-success">Comprar</button>
                        </a>
                    </div>
<?php                       
                }
?>
            </div>
<?php
            }
             */
?>
            <div class="alert alert-danger hidden" id="semProdutos" role="alert">
                <span class="sr-only">Erro:</span>
                Nenhum produto foi encontrado
            </div>
        </div>
        <div class="col-md-2 p-top10">
            <div class="row pull-right p-left-3">
                <p class="txt-azul-claro"><strong>Dúvidas ou problemas para concluir a venda?</strong></p>
                <p class="txt-cinza">Por favor, avise-nos entrando em contato com o nosso <a href="/game/suporte.php" target="_blank">suporte</a>.</p>
            </div>
        </div>
    </div>
</div>
<script>
$(function(){
    
    if($("div").hasClass("box-produto") == false && $("div").hasClass("box-servico") == false && $("div").hasClass("box-jogos") == false)
        $("#semProdutos").removeClass("hidden");
    
    $(".prod").click(function(){
        var id = $(this).attr("id");
        $("#prod").val(id);
        console.log(id);
        $("#detalhe").submit();
    });
    
    $(window).on('load', function() {
        resize_caption(); 
        resize_imagem();
    })
    
    
    $(window).resize(function(){
        resize_caption(); 
        resize_imagem();
    });
    
    function resize_caption(){
        thumbnails = $(".thumbnail").toArray();
        var maiorAlturaCaption = 0;
        var mudou = false;
        var teste_altura = false;
        
        thumbnails.forEach(thumb => {

            alturaCaption = 0;
            caption = $(thumb).children(".thumbail-body");
            alturaCaption = $(caption).height();
            
            if(alturaCaption > maiorAlturaCaption){
                maiorAlturaCaption = alturaCaption;
                mudou = true;
            }
            
            if(alturaCaption == 0){
                teste_altura = true;
            }
            
        });

        if(!teste_altura){
            thumb_body = $(".thumbail-body").toArray();
            
            thumb_body.forEach(body => {
                atual = $(body).height();
                margin = (maiorAlturaCaption - atual) - 1;
                $(body).css("margin-top", margin);
            });
            
        }else{
            setTimeout(resize_caption, 500);
        }
     
    }
    
    //Faz um resize na box das imagens para que fiquem do mesmo tamanho.
    function resize_imagem(){
        
        setTimeout(function(){
        
            thumbnails = $(".thumbnail").toArray();
            altura_box = $(".thumbnail").children(".box-image").height();
            maior_altura = 0;
            
            thumbnails.forEach(thumb => {
                imagem = $(thumb).children(".box-image").children("img");
                altura_imagem = $(imagem).height();
                if(maior_altura < altura_imagem){
                    maior_altura = altura_imagem;
                }
            });
            
            if(maior_altura == 0){
                resize_imagem();
            }else{
                altura_box = maior_altura;
                $(".thumbnail").children(".box-image").height(maior_altura);
            }
            
            thumbnails.forEach(thumb => {

                imagem = $(thumb).children(".box-image").children("img");

                altura_imagem = $(imagem).height();
                
                if(altura_imagem != 0){
                    margin_top = (altura_box - altura_imagem)/2;
                    $(imagem).css("margin-top", margin_top + "px");
                }
                

            });
        
        }, 1000)
        
    }
});
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>