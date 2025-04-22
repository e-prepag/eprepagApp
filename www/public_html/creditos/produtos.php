<?php
require_once "../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/ProdutosController.class.php";
require_once DIR_CLASS . "util/Busca.class.php";

$controller = new ProdutosController;

$filtro['b2c'] = false;
$filtro['id_user'] = $controller->usuarios->getId();
$filtro['_ug_possui_restricao_produtos'] = $controller->usuarios->getPossuiRestricaoProdutos();
$arrJsonFiles = unserialize(ARR_PRODUTOS_CREDITOS);

$busca = new Busca;
$busca->setFullPath(DIR_JSON);
$busca->setArrJsonFiles($arrJsonFiles);
$busca->setFiltro($filtro);
$arrProdutosOrdemAlfabetica = $arrProdutos = $busca->getAllJsonByFilter();

//echo "<script>console.log('".json_encode($arrProdutosOrdemAlfabetica)."');</script>";
ksort($arrProdutosOrdemAlfabetica);
$letra = "";

foreach($arrProdutosOrdemAlfabetica as $nomeProd => $produto){
    if($letra != $nomeProd[0]){
        $letra = $nomeProd[0];
    }

    if(in_array($controller->usuarios->getId(),$ARRAY_INIBI_VENDA_HARDCODE) && in_array((str_replace("/creditos/produto/produto_detalhe.php?prod=","",$produto["id"])*1),$ARRAY_INIBI_PRODUTOS_VENDA_TO_ID_HARDCODE)){   
        continue;
    }
     
    $arrProdutosOrdenados[$letra][] = $produto;
}

foreach($arrProdutos as $produto){
	
	if($controller->usuarios->getId() != 17371){
		
		//if($produto['object']->id == 443){
			//unset($arrProdutos[strtoupper($produto['object']->nome)]);
		//}
		
	}else{ 
		
		if(!isset($produto['object']->imagem)){
        $height = 0;
		}else{
			list($width, $height) = getimagesize(DIR_WEB . DIR_W_IMG_PRODUTOS . $produto['object']->imagem);
		}
		$array_imagem[$produto['object']->imagem] = $height;
		if($height > $maiorAltura) {
			$maiorAltura = $height;
		}
		
    }
	
}

$banner = $controller->getBanner();

require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";
?>
<div class="container txt-azul-claro bg-branco p-bottom40">
    <?php 
	     //if($controller->usuarios->getId() != 17371){ 
	?>
    <div class="col-md-12 top10 barra-busca hidden-xs hidden-sm">
        <div class="row txt-branco text-center top10">
            <strong>Encontre seu Game</strong>
        </div>
        <nav class="navbar top5 nav-busca navbar-default bottom0">
<!--                  Collect the nav links, forms, and other content for toggling -->
              <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                  <ul class="nav nav-busca navbar-nav">
<?php
                $html = "";
            if(!empty($arrProdutosOrdenados)){
                foreach ($arrProdutosOrdenados as $letra => $arrProdutosAlfa){
                    
                    echo "<li class='dropdown''>
                                <a href='#' class='dropbtn'>$letra</a>
                                <div class='dropdown-content' style='z-index: 999;'>";
                    
                    foreach($arrProdutosAlfa as $produto){
						 
						//if($controller->usuarios->getId() != 17371 && $produto['object']->id != 443){
							echo "<a href='javascript:void(0);'><span class='nowrap fontsize-p txt-cinza prod' id='".$produto['object']->id."'><strong>".$produto['object']->nome."</strong></span></a><br>";
						//}else{
							//if($controller->usuarios->getId() == 17371){
							//    echo "<a href='javascript:void(0);'><span class='nowrap fontsize-p txt-cinza prod' id='".$produto['object']->id."'><strong>".$produto['object']->nome."</strong></span></a><br>";
							//}
						//}
                        
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
	<?php 
	   //} 
	?>
    <div class="clearfix"></div>
    <div class="row">
    <div class="col-md-12 txt-azul-claro top10">
         <div class="row">
<?php
if(is_array($arrProdutos) && !empty($arrProdutos)){
        $cont = 0;
        foreach($arrProdutos as $ind => $produto){
                if(in_array($controller->usuarios->getId(),$ARRAY_INIBI_VENDA_HARDCODE) && in_array((str_replace("/creditos/produto/produto_detalhe.php?prod=","",$produto["id"])*1),$ARRAY_INIBI_PRODUTOS_VENDA_TO_ID_HARDCODE)){ 
                    continue;
                }
?>
        <div class="col-md-3 col-xs-6 col-sm-6 col-lg-3 txt-azul-claro text-center top20 c-pointer prod" id="<?php echo $produto['object']->id;?>">
            <div class="thumbnail">
                <div class="box-image" style="height: <?php echo $maiorAltura ?>px">
<?php 
            if( $produto['object']->imagem && 
            $produto['object']->imagem != "" && 
            file_exists(DIR_WEB . DIR_W_IMG_PRODUTOS . $produto['object']->imagem)){ 
?>                    
                    <img border="0" class="img-produto" style="margin-top: <?php echo ($maiorAltura - $array_imagem[$produto['object']->imagem])/2 ?>px" src="<?php echo DIR_W_IMG_PRODUTOS . $produto['object']->imagem?>">
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
         </div></div></div>
</div>
<script type="text/javascript" src="/js/autocomplete.js"></script>
<script>
    $(function(){
        $(".prod").click(function(){
            var id = $(this).attr("id");
            $("#prod").val(id);
            $("#detalhe").submit();
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
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
pg_close($connid);
?>