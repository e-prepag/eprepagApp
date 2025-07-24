<?php
// error_reporting(E_ALL); // Exibe todos os tipos de erros
// ini_set('display_errors', 1); // Exibe os erros diretamente na tela
// ini_set('log_errors', 1); // Habilita o registro de erros no log do PHP

require_once "../includes/constantes.php";
require_once "../includes/constantes_url.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
$controller = new HeaderController;

/*
    Inicio - banners
 */

$posicao = "Carrossel";
$categoria = "OffLine";
$objBanner = new BannerBO();
$banners = $objBanner->getBannersFromJson($posicao, $categoria);

/*
    Fim - banners
 * 
    Inicio - produtos
 */

global $oprid;
if(isset($_SESSION['epp_origem'])){
    if ($_SESSION['epp_origem'] == "STARDOLL") {			// Filtro indireto por HTTP_REFERER capturado
        $filtro['ogp_opr_codigo'] = 38;
    } elseif ($_SESSION['epp_origem'] == "TMP") {			// Testes
        $filtro['ogp_opr_codigo'] = 38;
    } 
}elseif(strlen($oprid)>0 && is_numeric($oprid)) {		// Filtros diretos (na URL com o parâmetro oprid)	 GameIs=22, Acclaim=19
    $filtro['ogp_opr_codigo'] = $oprid;
}

$arrJsonFiles = unserialize(ARR_PRODUTOS_GAMER);

$busca = new Busca;
$busca->setFullPath(DIR_JSON);
$busca->setArrJsonFiles($arrJsonFiles);

if(isset($filtro) && !empty($filtro)){
    $filtro['gamer'] = true;
    $busca->setFiltro($filtro);
}

$json = $busca->getAllJsonByFilter();

$qtdProdutoPorPagina = 8;
$inicio = 0;
$pagina = 1;
$ate = $qtdProdutoPorPagina;

$produtos = array_values($json);

for($i=$inicio;$i<$ate;$i++){
    if(isset($produtos[$i])){
		
		if($produtos[$i]['object']->filtro->ogp_opr_codigo != 162){ //159
			$productResult[] = $produtos[$i];
		}
		
       // $productResult[] = $produtos[$i];
	}
	
}

$maiorAltura = 0;
$array_imagem = array();

foreach($productResult as $produto){
    list($width, $height) = getimagesize(DIR_WEB . DIR_G_IMG_PRODUTOS . $produto['object']->imagem);
    $array_imagem[$produto['object']->imagem] = $height;
    if($height > $maiorAltura) {
        $maiorAltura = $height;
    }
}

$inicio = $i;

/*
    Fim produtos
 * 
    Início feed rss
 */

//COMENTADO POIS FOI SUBSTITUIDO POR UMA IMAGEM FIXA COM LINK PARA O BLOG
//
//$objJson = new Json;
//$objJson->setFullPath(DIR_JSON);
//$arrJsonFiles = unserialize(ARR_JSON_FEED_GAMER);
//$objJson->setArrJsonFiles($arrJsonFiles);
//$feeds = $objJson->getJsonRecursive();
//
//if($feeds){
//    $qtdFeeds = (count($feeds) > 4) ? 4 : count($feeds);
//}
/*
    Fim feed rss
 * 
*/

$pagina_titulo = "E-prepag - Créditos para Games";

$controller->setHeader();

?>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-W3GM5WR');</script>
<!-- End Google Tag Manager -->

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W3GM5WR"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '228069144336893'); // Insert your pixel ID here.
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=228069144336893&ev=PageView&noscript=1"/></noscript>
<!-- End Facebook Pixel Code -->
<div class="container txt-azul-claro bg-branco">
    <div class="row top10">
        <div class="col-md-9 h205 hidden-xs">
<?php
        if(!empty($banners)){
?>
            <div id="myCarousel" class="carousel slide" data-ride="carousel">
            <!-- Indicators -->
                <ol class="carousel-indicators">
<?php

                for($i=0;$i<count($banners);$i++){
?>
                    <li data-target="#myCarousel" data-slide-to="<?php echo $i;?>" class="<?php if($i==0) echo "active" ;?>"></li>
<?php
                }
?>
                </ol>

            <!-- Wrapper for slides -->
                <div class="carousel-inner" role="listbox">
<?php
                for($i=0;$i<count($banners);$i++){
?>
                    <div class="item <?php if($i==0) echo "active" ;?>">
                        <a href='<?php echo $banners[$i]->link; ?>' class="banner" id="<?php echo $banners[$i]->id;?>" target="_blank">
                            <img width="756" height="205" src="<?php echo $objBanner->urlLink.$banners[$i]->imagem; ?>">
                            <div class="carousel-caption text-left">
<!--                                <h3><?php echo $banners[$i]->titulo; ?></h3>-->
                            </div>
                        </a>
                    </div>
<?php
                }
?>
                </div>

                <!-- Left and right controls -->
                <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                  <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                  <span class="sr-only">Próximo</span>
                </a>
                <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                  <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                  <span class="sr-only">Anterior</span>
                </a>
            </div>
<?php
        }
?>
        </div>
        <div class="col-md-3 txt-branco hidden-xs hidden-sm" id="box-busca-pdv">
            
        </div>
    </div>
    <div class="row top20">
        <div class="col-md-12">
            <span class="glyphicon glyphicon-triangle-right graphycon-big color-blue pull-left"></span>
            <strong class="pull-left top15 color-blue font20">Compra de créditos</strong>  
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 ">
            <hr class="border-blue">
        </div>
    </div>
    <div class="clearfix"></div>
    <form id="detalhe" name="detalhe" action="/ajax/produtoAmigavel.php" method="post"><!-- game/produto/detalhe.php -->
    <div class="row linha-prod">
        <div class="txt-azul-escuro ico-lateral-left">
            <span class="glyphicon glyphicon-chevron-left hidden c-pointer graphycon-big zindex100" id="rolagem-produtos-esquerda" aria-hidden="true"></span>
        </div>
        <div class="txt-azul-escuro pull-right ico-lateral-right">
            <span class="glyphicon glyphicon-chevron-right c-pointer graphycon-big zindex100" style="z-index: 9999;" id="rolagem-produtos-direita" aria-hidden="true"></span>
            <input type="hidden" id="inicio" value="<?php echo $inicio;?>">
            <input type="hidden" id="qtd" value="<?php echo $qtdProdutoPorPagina;?>">
            <input type="hidden" id="total" value="<?php echo count($json);?>">
            <input type="hidden" id="ultimoBotao" value="">
        </div>
    <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-azul-claro top10 produtos">
        <div class="row">
<?php 
$cont = 0;
if(is_array($productResult) && !empty($productResult)){
    foreach($productResult as $produto){
?>
        <div class="col-md-3 col-xs-6 col-sm-6 col-lg-3 txt-azul-claro text-center top20 c-pointer" onclick="postProduct(<?php echo $produto['object']->id;?>)">
            <div class="thumbnail">
                <div class="box-image" style="height: <?php echo $maiorAltura ?>px">
<?php 
        if( $produto['object']->imagem && 
        $produto['object']->imagem != "" && 
        file_exists(DIR_WEB . DIR_G_IMG_PRODUTOS . $produto['object']->imagem)){ 
?>              
                  
                    <img border="0" class="img-produto" style="margin-top: <?php echo (($maiorAltura - $array_imagem[$produto['object']->imagem])/2) - 1 ; ?>px" src="<?php echo DIR_G_IMG_PRODUTOS . $produto['object']->imagem?>">
                
<?php 
        } 
?>              
                </div>
                <div class="caption align-center thumbail-body">      
                    <h4 class="color-blue" style="float:bottom">
                        <strong><?php echo $produto['object']->nome; ?></strong>
                    </h4>
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
}else{
?>
        <div class="alert alert-danger" role="alert">
            <span class="sr-only">Erro:</span>
            Não encontramos produtos!!
        </div>
<?php
}
?>
    </div></div>
    <input type="hidden" id="prod" name="prod">
    </div>
    </form>
    <div class="row">
        <div class="col-md-12">
            <a href="/game/" alt="Listar Games" title="Games" type="button" class="btn btn-lg btn-large btn-block btn-success"><strong>Ver todos os games</strong></a>
        </div>
    </div>
    <div class="row top20">
        <div class="col-md-12">
            <span class="glyphicon glyphicon-triangle-right graphycon-big color-green pull-left"></span>
            <strong class="pull-left top15 color-green font20">Cartão E-prepag Cash</strong>  
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 ">
            <hr class="border-green">
        </div>
    </div>
    <div class="row" title="Saiba mais sobre E-prepag Cash">
        <a href=<?=CARTAO_URL?>"target="_blank">
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 top20">
                <img class="width100" src="imagens/cartao-eprep.jpg" />
            </div>

            <div class="col-xs-12 col-sm-6 col-md-8 col-lg-8">
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 align-center top20">
                    <div class="row">
                        <div class="col-xs-offset-1 col-sm-offset-0 col-xs-10 col-md-12">
                            <img class="width100" src="imagens/tempo-de-compra.jpg" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h4><b>Receba os créditos no jogo rapidamente</b></h4>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 align-center top20">
                    <div class="row">
                        <div class="col-xs-offset-1 col-sm-offset-0 col-xs-10 col-md-12">
                            <img class="width100" src="imagens/mais-de-mil-games.jpg" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h4><b>Utilize em mais de 1.000 games</b></h4>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 align-center top20">
                    <div class="row">
                        <div class="col-xs-offset-1 col-sm-offset-0 col-xs-10 col-md-12">
                            <img class="width100" src="imagens/saldo-na-carteira.jpg" />
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h4><b>Adicione saldo de onde estiver</b></h4>
                        </div>
                    </div>
                </div>
            </div>
        </a>
<!--        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 align-center top20">
            <div class="row">
                <div class="col-md-12">
                    <img class="width80" src="images/mais-de-mil-games.jpg" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h4><b>Receba os créditos no jogo rapidamente</b></h4>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 align-center top20">
            <div class="row">
                <div class="col-md-12">
                    <img class="width80" src="images/saldo-na-carteira.jpg" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h4><b>Receba os créditos no jogo rapidamente</b></h4>
                </div>
            </div>
        </div>-->
<!--            <div class="col-md-5 align-center top20">
                <img src="images/logo_epp_cash.png" alt="" srcset="">
            </div>
            <div class="col-md-7 align-left color-grey font16 top20">
                <ul>
                    <li>Recebimento imediato dos créditos</li>
                    <li>Utilização em mais de 1000 games</li>
                    <li>Adicione saldo de qualquer lugar</li>
                </ul>
                <a class="btn btn-success btn-block redirecionamento" link="blog.e-prepag.com/eprepag-cash-carteira-virtual/" role="button">Saiba Mais</a>
            </div>-->
    </div>
    <div class="row top20">
        <div class="col-md-12">
            <span class="glyphicon glyphicon-triangle-right graphycon-big color-yellow pull-left"></span>
            <strong class="pull-left top15 color-yellow font20">Promoções, lançamentos e novidades !</strong>  
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 ">
            <hr class="border-yellow">
        </div>
    </div>
    <div class="clearfix"></div>
	
	<div class="col-sm-12 col-xs-12 col-md-6 align-center top20">
		<a href="<?php echo NOVIDADES_URL; ?>" target="_blank"> <img src="/imagens/imghomesiteparablog.png" id="image_bug_fixed" /> </a>
	</div>
	
    <div class="row">
		
		<div class="col-md-6 col-sm-12 col-xs-12 col-lg-6 bottom10 txt-cinza">
			<div class="row">
            <!--<div class="col-md-12">
                
            </div>-->
<?php
//COMENTADO POIS FOI SUBSTITUIDO POR UMA IMAGEM FIXA COM LINK PARA O BLOG
//
//    if($feeds){
//        for($i=0;$i<$qtdFeeds;$i++){
?>
<!--        <div class="col-md-6 align-center top20">
            <a target="_blank" href="<?php // echo $feeds[$i]->link;?>">
                <div class="row">
                    <div class="col-md-12">
                        <img alt="<?php // echo $feeds[$i]->title; ?>" width="100%" src="<?php // echo $feeds[$i]->src[0]; ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 align-center">
                        <h5><strong><?php // echo $feeds[$i]->title; ?></strong></h5>
                    </div>
                </div>
            </a>
        </div>-->
        
<?php 
//            if($i == 1){
//                echo "</div><div class='row'>";
//            }
//        }
//    }
//?>
        </div>        
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12 col-lg-6 bottom10">
        <div class="col-md-6 col-sm-6 facebook-gamer top20"></div>
        <div class="col-sm-12 col-xs-12 col-md-6 align-center top20">
            <a href="/cadastro-de-ponto-de-venda.php" target="_blank"><img src="/imagens/banner-faca-parte188-X-200.jpg"></a>
        </div>
    </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bg-info align-center borda-direita-basica p-bottom10">
        <h3>
            Quer ser um ponto de venda ?  
            <a href="/cadastro-de-ponto-de-venda.php" class="txt-branco" target="_blank"><b><span class="link-destaque">Cadastre-se</span></b></a>
            ou 
            <a href="https://e-prepagpdv.com.br/" class="txt-branco" target="_blank"><b><span class="link-destaque">saiba mais</span></b></a>.            
        </h3>
    </div>
</div>
</div>

<script type="text/javascript" src="/js/buscalans.js"></script>    
<script>
$(document).ready(function(){
    
    montaBoxPdv();
    
    $(window).on('load', function() {
        resize_caption(); 
        resize_imagem();
    })
    
    
    $(window).resize(function(){
        resize_caption(); 
        resize_imagem();
    });
    
    $("#rolagem-produtos-direita").click(function(){
       
        if($("#ultimoBotao").val() == "left"){
            var ate =parseInt($("#qtd").val()*2)+parseInt($("#inicio").val());
            $("#inicio").val(parseInt($("#inicio").val())+parseInt($("#qtd").val()));
        }else{
            var ate =parseInt($("#qtd").val())+parseInt($("#inicio").val());
        }
        
        
        var data = {inicio: $("#inicio").val(), qtd: $("#qtd").val(),ate: ate, categoria: "Gamer"} ;
        $.ajax({
            type: "POST",
            data: data,
            url: "/ajax/produtos.php",
            success: function(produtos){
                if(ate >= $("#total").val())
                    $(".glyphicon-chevron-right").addClass("hidden");
                
                if(ate > $("#qtd").val())
                    $(".glyphicon-chevron-left").removeClass("hidden");

                $("#inicio").val(ate);
                $(".produtos").html(produtos).queue(resize_caption()).queue(resize_imagem());
                $("#ultimoBotao").val("right");
            },
            error: function(){
                alert('erro info_pedido');
            }
        });
    });
    
    $("#rolagem-produtos-esquerda").click(function(){
        var inicio = "";
        var ate = "";
        
        if($("#ultimoBotao").val() == "right"){
            inicio = parseInt($("#inicio").val())-(parseInt($("#qtd").val())*2);
            ate = parseInt($("#inicio").val())- parseInt($("#qtd").val());
        }else{
            inicio = parseInt($("#inicio").val())-parseInt($("#qtd").val());
            ate = $("#inicio").val();
        }
        
        var data = {inicio: inicio, qtd: $("#qtd").val(),ate: ate, categoria: "Gamer"} ;
        console.log(data);
        $.ajax({
            type: "POST",
            data: data,
            url: "/ajax/produtos.php",
            success: function(produtos){
                if(inicio < $("#qtd").val())
                    $(".glyphicon-chevron-left").addClass("hidden");
                
                $(".glyphicon-chevron-right").removeClass("hidden");
                    
                $("#inicio").val(inicio);
                $(".produtos").html(produtos).queue(resize_caption()).queue(resize_imagem());
                $("#ultimoBotao").val("left");
            },
            error: function(){
                alert('erro info_pedido');
            }
        });
    });
    
    $(".prod").click(function(){
        
    });
    
    //O caption é o nome do produto. Essa função verifica qual é maior (ocupa mais linhas) e faz um margin-top nos outros menores
    //para que todos os produtos tenham um thumbnail do mesmo tamanho
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

    function postProduct(id){
        $("#prod").val(id);
        $("#detalhe").submit();
    }
</script>
<script src="/js/facebook.js"></script>
<?php
require_once "game/includes/footer.php";