<?php
require_once "../../includes/constantes.php";
require_once DIR_CLASS."gamer/controller/HeaderController.class.php";

$pagina_titulo = "E-prepag - Créditos para Games";


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

$arrProdutosOrdemAlfabetica = $json = $busca->getAllJsonByFilter();

if(isset($_POST['busca'])){
    $filtro['origem'] = "busca";
    $busca->setFiltro($filtro);
    $arrBuscaProduto = json_decode($busca->getJson($_POST['busca']));
}

ksort($arrProdutosOrdemAlfabetica);

$letra = "";

foreach($arrProdutosOrdemAlfabetica as $nomeProd => $produto){
    if($letra != $nomeProd[0]){
        $letra = $nomeProd[0];
    }
	
	if($_SERVER["REMOTE_ADDR"] != "201.93.162.169"){
		
		if($produto['object']->filtro->ogp_opr_codigo != 162){ //159
			$arrProdutosOrdenados[$letra][] = $produto;
		}
		
	}else{
		$arrProdutosOrdenados[$letra][] = $produto;
	}
   
    
}
/*
if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
	echo "<script>console.log('".$GLOBALS['_SESSION']['usuarioGames_ser']."')</script>";
}
*/

$maiorAltura = 0;
$array_imagem = array();

//echo "<script>console.log(".json_encode($arrProdutosOrdemAlfabetica).");</script>";

foreach($json as $produto){
	
	//echo "<script>console.log(".json_encode($produto['object']->nome).");</script>";
	//echo "<script>console.log(".json_encode(strtoupper($produto['object']->nome)).");</script>";
	
	if($_SERVER["REMOTE_ADDR"] != "201.93.162.169"){
		
		if($produto['object']->filtro->ogp_opr_codigo == 162){ ///159
			unset($json[strtoupper($produto['object']->nome)]);
			//unset($json[$produto['object']->nome]);
		}
		
	}
    if(!isset($produto['object']->imagem)){
        $height = 0;
    }else{
        list($width, $height) = getimagesize(DIR_WEB . DIR_G_IMG_PRODUTOS . $produto['object']->imagem);
    }
    $array_imagem[$produto['object']->imagem] = $height;
    if($height > $maiorAltura) {
        $maiorAltura = $height;
    }
}

//echo "<script>console.log(".json_encode($json).");</script>";

$posicao = "Inferior Internas";
$controller = new HeaderController;
$banners = $controller->getBanner($posicao);

$controller->setHeader();
?>
<div class="container txt-azul-claro bg-branco p-bottom40">
    <div class="col-md-12 barra-busca  top5">
        <div class="col-md-4 col-md-offset-4 text-center">
            <div class="row txt-branco top10">
                <strong>Encontre seu Game</strong>
            </div>
            <form name="formBusca" id="formBusca" method="post">
                <div class="input-group top10 bottom10 text-center">
                    <input type="text" class="form-control txt-cinza borda-cinza ui-autocomplete-input" placeholder="" id="busca" name="busca" aria-describedby="basic-addon2" autocomplete="off">
                    <span class="input-group-addon glyphicon glyphicon-search bg-branco c-pointer borda-cinza t0" id="basic-addon2"></span>
                </div>
            </form>
            <div class="clearfix"></div>
        </div>
        <div class="col-md-2 pull-right top5 fontsize-p txt-branco">
            <strong><a link="blog.e-prepag.com/indique-um-game/" href="#" class="p-right0 pull-right txt-branco redirecionamento">> Indique um game</a></strong>
        </div>
    </div>
    <form id="detalhe" name="detalhe" action="/ajax/produtoAmigavel.php" method="post"><!-- produto/detalhe.php -->
    <div class="col-md-12 barra-busca hidden-xs hidden-sm">
        <nav class="navbar nav-busca navbar-default bottom0">
<!--                  Collect the nav links, forms, and other content for toggling -->
              <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                  <ul class="nav nav-busca navbar-nav">
<?php
                $html = "";
            if(!empty($arrProdutosOrdenados)){
                foreach ($arrProdutosOrdenados as $letra => $arrProdutos){
                    
                    echo "<li class='dropdown''>
                                <a href='#' class='dropbtn'>$letra</a>
                                <div class='dropdown-content' style='z-index: 999;'>";
                    
                    foreach($arrProdutos as $produto){
                        echo "<a href='javascript:void(0);'><span class='nowrap fontsize-p txt-cinza prod' id='".$produto['object']->id."'><strong>".$produto['object']->nome."</strong></span></a><br>";
                    }
                    
                    echo "</div>
                    </li>";
                }
            }
?>
                  </ul>
              </div> <!--/.navbar-collapse--> 
        </nav>
    </div>
    <div class="row top40">
        <div class="col-md-12 top40">
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
    <div class="row">
    <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-azul-claro top10">
        <div class="row">
<?php

if(is_array($json) && !empty($json) && (!isset($arrBuscaProduto) || empty($arrBuscaProduto))){
    $cont = 0;
    foreach($json as $produto){
?>
        <div class="col-md-3 col-xs-6 col-sm-6 col-lg-3 txt-azul-claro text-center top20 c-pointer prod" id="<?php echo $produto['object']->id;?>">
            <div class="thumbnail">
                <div class="box-image" style="height: <?php echo $maiorAltura ?>px">
<?php 
        if( $produto['object']->imagem && 
        $produto['object']->imagem != "" && 
        file_exists(DIR_WEB . DIR_G_IMG_PRODUTOS . $produto['object']->imagem)){ 
                  
?>              
                    <img border="0" class="img-produto" style="margin-top: <?php echo ($maiorAltura - $array_imagem[$produto['object']->imagem])/2 ?>px" src="<?php echo DIR_G_IMG_PRODUTOS . $produto['object']->imagem?>">
                
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
}elseif(isset($arrBuscaProduto) && is_object($arrBuscaProduto->games)){
?>
        </div></div></div>
        <div class="row">
            <div class="col-md-12 espacamento">
                <p class="txt-cinza"><strong class="txt-azul-claro">Resultado da busca por: </strong> <?php echo htmlspecialchars($_POST['busca']);?></p>
            </div>
        </div>
        <div class="row">
        <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12 txt-azul-claro top10">
            <div class="row">
<?php
    $cont = 0;
    foreach($arrBuscaProduto->games as $ind => $objProdutos)
    {
?>
        <div class="col-md-3 col-xs-6 col-sm-6 col-lg-3 txt-azul-claro text-center top20 c-pointer prod" id="<?php echo $objProdutos->object->id;?>">
            <div class="thumbnail">
            <div class="box-image" style="height: <?php echo $maiorAltura ?>px">
<?php 
            if( $objProdutos->object->imagem && 
                $objProdutos->object->imagem != "" && 
                file_exists(DIR_WEB .DIR_G_IMG_PRODUTOS . $objProdutos->object->imagem))
            {
?>
                <img border="0" class="img-produto" src="<?php echo DIR_G_IMG_PRODUTOS . $objProdutos->object->imagem;?>">
<?php 
            }
?>
            </div>
            <div class="caption align-center thumbail-body">      
                <h4 class="color-blue" style="float:bottom">
                    <strong><?php echo $objProdutos->object->nome; ?></strong>
                </h4>
                <h2 class="txt-cinza bottom0 fontsize-p hOperadoraProduto">
                    <?php echo $objProdutos->object->operadora; ?>
                </h2>
                <button type="button" class="btn btn-success btn-block">Comprar</button>
            </div>
            </div>
        </div>
<?php
        $cont++;
        if($cont == 4){
            $cont = 0;
            echo "</div><div class='row'>";
        }
    }
} else{
?>
        <div class="alert alert-danger" role="alert">
            <span class="sr-only">Erro:</span>
            Não encontramos produtos!!
        </div>
<?php
}
?>
    </div></div>
    </div>
    <div class="clearfix"></div>
    <hr>
    <div class="col-md-6 txt-verde top50 p-bottom40">
        <div class="col-md-4">
            <img src="/imagens/logo_epp_cash.png">
        </div>
        <div class="col-md-8 txt-cinza text-left">
            <ul>
                <li>Recebimento imediato dos créditos</li>
                <li>Utilização em mais de 1.000 games</li>
                <li>Adicione saldo de qualquer lugar</li>
            </ul>
            <a link="blog.e-prepag.com/eprepag-cash-carteira-virtual/" href="#" class="p-left25 redirecionamento">Saiba mais.</a>
        </div>
    </div>
    <div class="col-md-4 top50 p-bottom40">
        <span class="text18 txt-verde">Utilize agora seu saldo</span>
        <p class="top10 txt-cinza"><a href="/game/conta/add-saldo.php">Adicione saldo online</a>, ou <a href="/creditos/login.php">encontre aqui um Ponto de Venda</a>.</p>
        <ul class="top10 nav nav-pills nav-stacked text18 bg-cinza txt-branco">
            <li role="presentation"><a href="/game/conta/extrato.php" alt="Meu Cadastro" title="Meu Cadastro"><strong>Acessar meu Cartão E-Prepag</strong></a></li>
        </ul>
    </div>
    <input type="hidden" id="prod" name="prod">
    </form>
</div>
</div>
<script type="text/javascript" src="/js/autocomplete.js"></script>
<script>
    $(document).ready(function(){
        $(".prod").click(function(){
            var id = $(this).attr("id");
            $("#prod").val(id);
            $(this).closest("form").submit();
        });
        
        resize_caption();
        
        $(window).on('load', function(){
            centralizar_imagem();
            resize_caption(); 
        });
        
        $(window).resize(function(){
            centralizar_imagem();
            resize_caption(); 
        });
        
        function resize_caption(){
            thumbnails = $(".thumbnail").toArray();
            var maiorAlturaCaption = 0;
            thumbnails.forEach(thumb => {
                caption = $(thumb).children(".thumbail-body");

                alturaCaption = $(caption).height();

                if(alturaCaption > maiorAlturaCaption){
                    maiorAlturaCaption = alturaCaption;
                }
            });

            if(maiorAlturaCaption > 0){
                thumb_body = $(".thumbail-body").toArray();
                thumb_body.forEach(body => {
                    atual = $(body).height();
                    margin = maiorAlturaCaption - atual;
                    $(body).css("margin-top", margin);
                });
            }else{
                setTimeout(resize_caption, 500);
            }
        }
        
        function centralizar_imagem(){
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

            if(maior_altura > 0){
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
        }
    });
</script>
<?php
require_once "includes/footer.php";