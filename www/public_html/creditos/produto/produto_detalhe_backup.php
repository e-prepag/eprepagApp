<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
require_once "../../../includes/constantes.php";
require_once DIR_CLASS."pdv/controller/ProdutosController.class.php";
require_once DIR_CLASS."gamer/classConversionPINsEPP.php";
header("Content-Type: text/html; charset=ISO-8859-1",true);

$qtdFeedsIndex = 5;
$controller = new ProdutosController;

if(isset($_GET['token'])){
    
    $objEncryption = new Encryption();
    $token = unserialize($objEncryption->decrypt($_GET['token']));
    $_POST["prod"] = $token['produto'];
    
}

if(!isset($_POST["prod"]) || $_POST["prod"] == "")
    $controller->accessDenied();

if($GLOBALS['_SESSION']["dist_usuarioGames_ser"]){
    $sistema = "pdv";
    $ug_id = $controller->usuarios->getId();
}else{
    $sistema = "gamer";    
    $ug_id = ($controller->usuario) ? $controller->usuario->getId() : "7909";
}

$ogp_id = $_POST["prod"];

if(in_array($controller->usuarios->getId(),$ARRAY_INIBI_VENDA_HARDCODE) && in_array($ogp_id,$ARRAY_INIBI_PRODUTOS_VENDA_TO_ID_HARDCODE)){ 
        $msg = "O produto que você está tentando acessar está indisponível no momento.<br>Entre em contato com nosso suporte.<br>Obrigado.";
?>
       <form name="pagamento" id="pagamento" method="POST" action="/creditos/mensagem.php">
           <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
           <input type='hidden' name='titulo' id='titulo' value='Produto Indisponível no Momento'>
           <input type='hidden' name='link' id='link' value='/creditos/'>
       </form>
       <script language='javascript'>
           document.getElementById("pagamento").submit();
       </script>       
<?php    
    die();
}

$sqlClickProduto = "insert into clicks (sistema, ug_id, ogp_id) values (:sistema, :ug_id, :ogp_id)";
//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();
$stmt = $pdo->prepare($sqlClickProduto);
$stmt->bindParam(':sistema', $sistema, PDO::PARAM_STR);
$stmt->bindParam(':ug_id', $ug_id, PDO::PARAM_INT);
$stmt->bindParam(':ogp_id', $ogp_id, PDO::PARAM_INT);
$stmt->execute();
//insert

$produto = $controller->getProdutoValor($_POST["prod"]);
$modelos = $produto->getModelo();

///prepag2/dist_commerce/images/produtos/
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";
?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>Selecione o valor</strong>
                </div>
            </div>
            <div class="row txt-cinza espacamento right10 borda-fina">
                <div class="col-md-3">
                    
<?php 
                if($produto->getNomeImagem() && $produto->getNomeImagem() != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $produto->getNomeImagem()))
                { 
?>
                    <p class="bottom0"><img border="0" style="max-width: 100%;" src="<?php echo $GLOBALS['URL_DIR_IMAGES_PRODUTO'] . $produto->getNomeImagem()?>"></p>
<?php 
                } 
?>
                    <p class="txt-azul-claro bottom0 top20"><strong><?php  echo $produto->getNome(); ?> </strong></p>
                    <p class="txt-azul-claro bottom0 p-top10">Publisher: <span class="txt-cinza"><?php  echo $produto->getNomeOperadora(); ?></span></p>  
                    
<?php 
                if(!$produto->getMostraIntegracao())
                {
?>               
                    <p class="p-top10"><?php  echo $produto->getDescricao(); ?></p>
<?php
                }
?>
                </div>
                <div class="col-md-9">
<?php
            if(is_null($produto->getValorMinimo()) && is_null($produto->getValorMaximo())) {
                if(is_array($modelos))
                {
                    foreach($modelos as $modelo){
?>
                        <div class="row top10">
                            <div class="col-md-5">
                                <p class="txt-cinza p-top10"><strong><?php echo $modelo->getNome(); ?></strong></p>
                            </div>
                            <div class="col-md-7 bg-comprar p-top10 nome-produto">
<?php
                            if($modelo->contar($produto->getOprCodigo(),$modelo->getPinValor())>0 || $produto->getPinRequest() > 0) 
                            {
?>
                                <span class="c-pointer" id="<?php echo $modelo->getId();?>">
                                    <div class="col-md-6 txt-azul-claro2"><p class="pull-left "><strong>R$ <?php echo number_format($modelo->getValor(), 2, ',', '.')?></strong></p></div>
                                    <div class="col-md-6 txt-verde">
                                        <p class="pull-right">
                                            <strong><em>Comprar</em></strong>
                                        </p>
                                    </div>
                                </span>
<?php
                            } 
                            else 
                            {
?>
                                <p class="pull-right txt-vermelho"><strong><em>Fora de Estoque</em></strong></p>
<?php
                            }
?>
                            </div>
                        </div>
<?php 
                    }
?>
                    <form id="seleciona" method="post" action="produtos_selecionados.php">
                        <input type="hidden" name="acao" id="acao" value="a">
                        <input type="hidden" name="mod" id="mod" value="">
                        <input type="hidden" name="valor" id="valor_hidden" value="">
                        <input type="hidden" name="codeProd" id="codeProd" value="<?php echo $produto->getId()  ?>">
                    </form>
<?php
                }elseif($produto->getMostraIntegracao() == 1)
                {
?>
                    <div class="row top10">
                        <?php  echo $produto->getDescricao(); ?>
                    </div>
<?php
                }
                else
                {    
?>
                    <div class="row top10">
                        <p class="pull-right txt-vermelho"><strong><em>Não existem modelos cadastrados para este produto.</em></strong></p>
                    </div>
<?php
                }
            }else{
                
?>
                    
                <div class="row top10 align-center">
                    <p class=""><strong>Informe o valor desejado de acordo com os valores máximo e mínimo informados</strong></p>
                    <div class="error-list">

                    </div>
                </div>
                <div class="row top10">
                    <div class="col-xs-12 col-sm-offset-2 col-sm-8 col-md-offset-2 col-md-8 col-lg-offset-2 col-lg-8 align-center">
                        <div class="col-sm-12 col-md-6 col-lg-6 top10" id="input-valor">
                            <div class="form-group align-center">
                                <div class="input-group align-center">
                                    <div class="input-group-addon">R$</div>
                                    <input type="number" class="form-control align-right" id="valor" name="valor" min="<?php echo $produto->getValorMinimo(); ?>" max="<?php echo $produto->getValorMaximo(); ?>" value="<?php echo number_format($produto->getValorMinimo(), 0); ?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
                                    <div class="input-group-addon">.00</div>
                                </div>

                            </div>  
                        </div>
                        <form class="form-inline c-pointer modelo-produto" estoque="1" id="<?php echo $NO_HAVE; ?>">
                            
                            <div class="row p-left10 p-right10 bg-comprar">
                                <div>
                                    <div class="col-sm-12 col-md-3 col-lg-3 top15 align-center">
                                    </div>
                                    <div class="col-sm-12 col-md-3 col-lg-3 mt-md-15 pb-sm-15">
                                        <div class="" estoque="1" id="<?php echo $NO_HAVE ?>">
                                            <strong class="txt-verde-escuro"><em>Comprar</em></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row top10">
                                <div class="col-lg-12 align-center">
                                    <span>Valor mínimo: <?php echo $produto->getValorMinimo(); ?> | Valor máximo: <?php echo $produto->getValorMaximo(); ?></span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <form id="seleciona" method="post" action="produtos_selecionados.php">
                    <input type="hidden" name="acao" id="acao" value="a">
                    <input type="hidden" name="mod" id="mod" value="">
                    <input type="hidden" name="valor" id="valor_hidden" value="">
                    <input type="hidden" name="codeProd" id="codeProd" value="<?php echo $produto->getId()  ?>">
                </form>    
<?php
            }
?>
                </div>
            </div>
        </div>
        <div class="col-md-2 p-top10">
            <div class="row pull-right p-8">
                <p class="txt-azul-claro"><strong>Dúvidas ou problemas para concluir a venda?</strong></p>
                <p class="txt-cinza">Por favor, avise-nos entrando em contato com o nosso <a href="/game/suporte.php" target="_blank">suporte</a>.</p>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $(".bg-comprar > span").hover(
         function() {
           $(this).children(".txt-azul-claro2").css("color","#fff");
           $(this).children(".txt-verde").css("color","#fff");
         }, function() {
           $(this).children(".txt-azul-claro2").css("color","#478ee6");
           $(this).children(".txt-verde").css("color","##009b4a");
         }
       ).click(function(){
             $("#mod").val($(this).attr("id"));
             $("#seleciona").submit();
       });
       
          $(".modelo-produto").hover(
            function() {
              $(this).children(".txt-azul-claro2").css("color","#fff");
              $(this).children(".txt-verde").css("color","#fff");
            }, function() {
              $(this).children(".txt-azul-claro2").css("color","#478ee6");
              $(this).children(".txt-verde").css("color","##009b4a");
            }
          ).click(function(){

                if($(this).attr("estoque") == "1"){
                    console.log("redir");
                    $("#mod").val($(this).attr("id"));
                    if($("#valor").length && $("#valor").val() === ""){
                        var html = "<p class='txt-vermelho'>Por favor, informe um valor no campo!</p>";
                        $(".error-list").html(html);
                        return;
                    }
                    $('#valor_hidden').val($("#valor").val());
                    $("#seleciona").submit();
                }
          });
      
        $(".prod").click(function(){
            var id = $(this).attr("id");
            $("#prod").val(id);
            $("#detalhe").submit();
        });
        
        $("#valor").change(function(){
            var min = parseInt($("#valor").attr("min"));
            var max = parseInt($("#valor").attr("max"));
            var valor = parseInt($("#valor").val());
            if(valor < min){

                var html = "<p class='txt-vermelho'>O valor " + valor + " não esta dentro do mínimo e máximo específicado. Por favor, insira um valor entre " + min + " e " + max + "!</p>";
                $("#valor").val(min);
                $(".error-list").html(html);
            }else if(valor > max){
                var html = "<p class='txt-vermelho'>O valor " + valor + " não esta dentro do mínimo e máximo específicado. Por favor, insira um valor entre " + min + " e " + max + "!</p>";
                console.log(valor);
                $("#valor").val(max);
                $(".error-list").html(html);
            }else{
                var html = "R$" + valor + ",00";
                $.post("/game/ajax/epp_info.php",
                {
                  valor: valor,
                },
                function(data){
                  var html = data;
                  $(".span-valor").html(html);
                });
                $(".error-list").html("");
            }
        });
    });
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>