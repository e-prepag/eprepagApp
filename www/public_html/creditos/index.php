<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

header("Content-Type: text/html; charset=ISO-8859-1",true);

require_once "../../includes/constantes.php";
require_once RAIZ_DO_PROJETO."class/pdv/controller/IndexController.class.php";
$qtdFeedsIndex = MAX_FEEDS_JSON;
$controller = new IndexController;

/* inicio banner */
$feeds = $controller->getFeedBlog($qtdFeedsIndex);

if($feeds){
    $qtdFeeds = (count($feeds) > MAX_FEEDS_JSON) ? MAX_FEEDS_JSON : count($feeds);
}

$banner = $controller->getBanner();
/* fim banner*/

/* inicio produtos */
$filtro['b2c'] = false;
$filtro['id_user'] = $controller->usuarios->getId();
$filtro['_ug_possui_restricao_produtos'] = $controller->usuarios->getPossuiRestricaoProdutos();
$arrJsonFiles = unserialize(ARR_PRODUTOS_CREDITOS);


$busca = new Busca;
$busca->setFullPath(DIR_JSON);
$busca->setArrJsonFiles($arrJsonFiles);

if(isset($filtro) && !empty($filtro)){
    $busca->setFiltro($filtro);
}

$json = $busca->getAllJsonByFilter();

//var_dump($json);



$qtdProdutoPorPagina = 8; //($controller->usuarios->getId() == 17371)?29:
$inicio = 0;
$pagina = 1;
$ate = $qtdProdutoPorPagina;
$sql = 'select ug_ativo from dist_usuarios_games where ug_id = '.$filtro['id_user'];
$rs_vip = SQLexecuteQuery($sql);
$retornoVip = pg_fetch_assoc($rs_vip);
$showModal = false;
if ($retornoVip["ug_ativo"] == "2") {
    $showModal = true;
}
$produtos = array_values($json);

for($i=$inicio;$i<$ate;$i++){
    if(!empty($produtos[$i])) {
        if(in_array($controller->usuarios->getId(),$ARRAY_INIBI_VENDA_HARDCODE) && in_array((str_replace("/creditos/produto/produto_detalhe.php?prod=","",$produtos[$i]["id"])*1),$ARRAY_INIBI_PRODUTOS_VENDA_TO_ID_HARDCODE)){
            $ate++;
            continue;
        }
		
		/*if($controller->usuarios->getId() == 17371){
			
			//var_dump($produtos[$i]['object']->id);
			if($produtos[$i]['object']->id == 355 || $produtos[$i]['object']->id == 374){	
				$productResult[] = $produtos[$i];
			}
				
		}else{*/
			$productResult[] = $produtos[$i];
		//}
		    
    }
}

foreach($productResult as $produto){
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

$inicio = $i;
/* fim produtos*/

require_once "includes/header.php";


/*
	// Exibe o modal de promoção até o dia 06/02/2024 e somente para PDVs ativos
	$usuarioDados = unserialize($_SESSION["dist_usuarioGames_ser"]);
	
	$dataAtual = date('Y-m-d');
	$dataLimite = "2024-02-06";
	
	if($usuarioDados->ug_blAtivo == 1 && $dataAtual <= $dataLimite) {
		include "includes/modal-promocao-pdv.php";
	}
*/

?>

<div class="container txt-azul-claro bg-branco">
	
    <?php
    if($controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]) 
    {
?>    
    <div id="modal-add-saldo" class="modal fade" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title"><strong><?php echo ($controller->usuarios->getRiscoClassif()==1) ? "Limite" : "Saldo"; ?> Disponível</strong></h4>
                </div>
                <div class="modal-body txt-verde text22">
                    <p><strong>R$ <?php echo $controller->saldoLimite; ?></strong></p>
<?php
                    if($controller->usuarios->getRiscoClassif() == 2)
                    {
?>
                    <p><a href="/creditos/add_saldo.php" title="Clique para adicionar saldo para seu ponto de venda" alt="Clique para adicionar saldo para seu ponto de venda"><button type="button" class="btn btn-success">Adicionar Saldo</button></a></p>
<?php
                    }
?>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
<?php
    }
?> 

    <div class="col-md-10 col-lg-10 col-xs-12 col-sm-12">
        <div class="row top40 hidden-sm hidden-xs">
            <div class="col-md-12">
                <span class="glyphicon glyphicon-triangle-right graphycon-big color-blue pull-left"></span>
                <strong class="pull-left top15 color-blue font20">Minha Loja</strong>  
            </div>
        </div>
        <div class="row hidden-sm hidden-xs">
            <div class="col-md-8 ">
                <hr class="border-blue">
            </div>
        </div>
        <div class="col-md-4 top20 hidden-sm hidden-xs">
            <ul class="nav nav-pills nav-stacked">
<?php
            if(($controller->usuarios->getRiscoClassif()==1 && $controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]) && checaBoletoEmAberto() != 0)
            { 
?>    
                <li role="presentation"><a href="/creditos/boletos.php?nao-emitido=1" class="botao-laranja txt-branco" title="Boleto Pendente" alt="Boleto Pendente" id="boleto-pendente"><strong>Boleto Pendente</strong></a></li>
<?php 
            }
?>
                <li role="presentation"><a href="/creditos/pedidos.php?nao_emitidos=1" title="Pins não emitidos" alt="Pins não emitidos"><strong>Pins não emitidos</strong></a></li>
<?php
            if($controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]) 
            {
?>
                <li role="presentation" class="c-pointer"><a data-toggle="modal" data-target="#modal-add-saldo"  alt="Clique para consultar seu saldo" title="Clique para consultar seu saldo"><strong>Consulta <?php echo ($controller->usuarios->getRiscoClassif()==1) ? "limite" : "saldo"; ?></strong></a></li>
<?php 
            }
            if(($controller->usuarios->getRiscoClassif()==2 && $controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2])) 
            { 
?>    
                <li role="presentation"><a href="/creditos/add_saldo.php" alt="Adicionar Saldo" title="Adicionar Saldo"><strong>Adicionar saldo</strong></a></li>
				<li role="presentation"><a href="https://e-prepagpdv.com.br/midia-kits/" alt="Divulgação" title="Divulgação"><strong>Divulgação</strong></a></li>
<?php 
            } 
?>
            </ul>
        </div>
        <div class="col-md-4 top20 hidden-sm hidden-xs">
            <ul class="nav nav-pills nav-stacked">
                <li role="presentation"><a href="/creditos/pedidos.php" title="Pedidos" alt="Pedidos"><strong>Pedidos</strong></a></li>
<?php 
            if($controller->lanHouse && $controller->usuarios->b_IsLogin_lista_extrato())
            {
?>
                <li role="presentation"><a href="/creditos/extrato.php" alt="Extrato" title="Extrato"><strong>Extrato</strong></a></li>
<?php 
            }
            if(($controller->lanHouse && $controller->usuarios->getRiscoClassif()==2) || ($controller->usuarios->getRiscoClassif()==2 && $controller->operadorTipo == $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_1]))
            {
?>
                <li role="presentation"><a href="/creditos/depositos.php" alt="Depósitos" title="Depósitos"><strong>Depósitos</strong></a></li>
<?php
            } 
            if(($controller->usuarios->getRiscoClassif()==1 && $controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]))
            { 
?>    
                <li role="presentation"><a href="/creditos/boletos.php" alt="Boletos - Histórico" title="Boletos - Histórico"><strong>Boletos - Histórico</strong></a></li>
<?php 
            }
?>          
				<li role="presentation"><a href="/creditos/favoritos_pdv.php" alt="Lista de Produtos Favoritos" title="Lista de Produtos Favoritos"><strong>Favoritos</strong></a></li>
            </ul>
        </div>
        <div class="col-md-4 top20 hidden-sm hidden-xs">
            <ul class="nav nav-pills nav-stacked">
<?php 
            if($controller->lanHouse)
            {
?>
                 <li role="presentation"><a href="/creditos/meu_cadastro.php" alt="Meu Cadastro" title="Meu Cadastro"><strong>Meu cadastro</strong></a></li>
                 <li role="presentation"><a href="/creditos/funcionarios.php" alt="Gerenciar funcionários" title="Gerenciar funcionários"><strong>Ger. funcionários</strong></a></li>
                 <li role="presentation"><a href="/creditos/pesquisa.php" alt="Pesquisa - Pins Disponiveis" title="Pesquisa - Pins Disponiveis"><strong>Pins disponíveis</strong></a></li>
<?php 
            }
?>
            </ul>
        </div>
        <div class="clearfix"></div>
<?php
    if($controller->operadorTipo !== $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2])
    {
?>
        <div class="row top40">
            <div class="col-md-12">
                <span class="glyphicon glyphicon-triangle-right graphycon-big color-blue pull-left"></span>
                <strong class="pull-left top15 color-blue font20">Acesso Rápido</strong>  
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 ">
                <hr class="border-blue">
            </div>
        </div>
        
        <div class="row linha-prod">
            <div class="txt-azul-escuro ico-lateral-left">
			    <?php //if($controller->usuarios->getId() != 17371){ ?>
                <span class="glyphicon glyphicon-chevron-left hidden c-pointer graphycon-big zindex100" id="rolagem-produtos-esquerda" aria-hidden="true"></span>
				<?php //} ?>
            </div>
            <div class="txt-azul-escuro ico-lateral-right">
			    <?php //if($controller->usuarios->getId() != 17371){ ?>
                <span class="glyphicon glyphicon-chevron-right c-pointer graphycon-big zindex100" style="z-index: 9999;" id="rolagem-produtos-direita" aria-hidden="true"></span>
				<?php //} ?>
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
        file_exists(DIR_WEB . DIR_W_IMG_PRODUTOS . $produto['object']->imagem)){ 
?>              
                  
                    <img border="0" class="img-produto" style="margin-top: <?php echo ($maiorAltura - $array_imagem[$produto['object']->imagem])/2; ?>px" src="<?php echo DIR_W_IMG_PRODUTOS . $produto['object']->imagem?>">
                
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
        if($cont == 4) {
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
    <div class="row">
        <div class="col-md-12">
            <a href="/creditos/produtos.php" alt="Listar Games" title="Games" type="button" class="btn btn-lg btn-large btn-block btn-success"><strong>Ver todos os games</strong></a>
        </div>
    
        <div class="clearfix"></div>
<?php
    }
?>
        <div class="row top40">
            <div class="col-md-12">
                <span class="glyphicon glyphicon-triangle-right graphycon-big color-blue pull-left"></span>
                <strong class="pull-left top15 color-blue font20">Blog: Promoções, novidades e eventos</strong>  
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 ">
                <hr class="border-blue">
            </div>
        </div>
        <div class="row align-left">
            <div class="col-md-12">
                <a href="<?=NOVIDADES_URL?>" target="_blank"> <img id="image_bug_fixed" src="/imagens/imghomesiteparablogpdv.png" /> </a>
            </div>
<?php
//    if(is_array($feeds))
//    {
//        for($i=0;$i<$qtdFeeds;$i++){
?>
<!--            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 top10 ">
                <a target="_blank" href="<?php // echo $feeds[$i]->link;?>">
                    <img alt="<?php // echo $feeds[$i]->title; ?>" width="100%" src="<?php // echo $feeds[$i]->src[0]; ?>">
                    <h4><strong><?php // echo $feeds[$i]->title; ?></strong></h4>
                </a>
            </div>-->
            
<?php 
//            if($i == 2) echo "</div><div class='clearfix'></div><div class='row align-center'>";
//        }
//    }else{
?>
<!--            <div class="col-md-12 top20">
                <p class="txt-cinza">As noticias não foram carregadas automaticamente.</p>
                <p class="txt-cinza"><a href="http://blog.e-prepag.com/categorias/blogpdv/" target="_blank">Clique aqui</a> para ver o blog para Pontos de Venda.</p>
            </div>-->
<?php            
//    }
?>
        </div>
    </div>
</div>
<div class="col-md-2 hidden-sm hidden-xs">
<?php 
        if($banner){
            foreach($banner as $b){
?>
            <div class="row top20">
                <a href="<?php echo $b->link; ?>" class="banner" id="<?php echo $b->id; ?>" target="_blank"><img src="<?php echo $controller->objBanner->urlLink.$b->imagem; ?>" width="186" class="p-3" title="<?php echo $b->titulo; ?>"></a>
            </div>
<?php 
            }
        }
?>
        <div class="row top20 facebook"></div>
</div>
</div>

<div id="modal-bloqueiopdv" class="modal fade" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title txt-vermelho"><strong>Atenção!</strong></h4>
            </div>
            <div class="modal-body alert alert-danger txt-vermelho">
                    <p>Seu PDV está inativo, por gentileza, entre em contato com o suporte@e-prepag.com.br.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<script src="/js/facebook.js"></script>
<script>
    var showModal = <?php echo json_encode($showModal); ?>;
    $(document).ready(function(){
        if (showModal) {
            // Exibe o modal
            $("#modal-bloqueiopdv").modal('show');
            // Redireciona após alguns segundos
            setTimeout(function() {
                window.location.href = "/creditos/logout.php";
            }, 5000); // 5000 milissegundos (5 segundos)
        }
        resize_caption();  
        
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
        
        $("#rolagem-produtos-direita").click(function(){
       
            if($("#ultimoBotao").val() == "left"){
                var ate =parseInt($("#qtd").val()*2)+parseInt($("#inicio").val());
                $("#inicio").val(parseInt($("#inicio").val())+parseInt($("#qtd").val()));
            }else{
                var ate =parseInt($("#qtd").val())+parseInt($("#inicio").val());
            }


            var data = {inicio: $("#inicio").val(), qtd: $("#qtd").val(),ate: ate, categoria: "Lan House", id: <?php echo $controller->usuarios->getId();?>} ;
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

            var data = {inicio: inicio, qtd: $("#qtd").val(),ate: ate, categoria: "Lan House", id: <?php echo $controller->usuarios->getId();?>} ;
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
        
    });
    
    function postProduct(id){
        $("#prod").val(id);
        $("#detalhe").submit();
    }
</script>
<?php
require_once "includes/footer.php";