<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";
require_once DIR_INCS."pdv/captura_inc.php";
require_once DIR_CLASS."gamer/classAlawarGames.php";
$controller = new HeaderController;
/*
 * Início controller
 */
//Produto
if(isset($_GET['token'])){
    
    $objEncryption = new Encryption();
    $token = unserialize($objEncryption->decrypt($_GET['token']));
    $prod = $token['produto'];
	
	if($token == null || $token == ""){
		
		if($_GET['token'] != ""){
			$conexao = ConnectionPDO::getConnection();
			$buscaProduto = "select * from link_produto_amigavel where palavras_chaves like :CHAVE;";
			$query = $conexao->getLink()->prepare($buscaProduto);
			$query->bindValue(":CHAVE", "%".strtolower($_GET['token'])."%");
			$query->execute();
			$resultado = $query->fetch(PDO::FETCH_ASSOC); 
			$prod = $resultado['id_produto'];
		}
		
	}
    
}else{
    
    $prod = $_POST['prod'];
}

$msg = "";

//valida produto
if(!$prod || $prod == "" || !is_numeric($prod)) 
    $msg = "Código do produto não fornecido ou inválido.";
else{
    
    $sistema = "gamer";    
    $ug_id = ($controller->usuario) ? $controller->usuario->getId() : "7909";
    $ogp_id = $prod;


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
}
 
//Obtem o produto selecionado	
if($msg == ""){
    $rs = null;
    $filtro['ogp_ativo'] = 1;
    $filtro['ogp_id'] = $prod;
// Wagner
    $filtro['ogp_mostra_integracao_com_loja'] = '1';
    $img_logo_stardoll = "";
    
    if ($_SESSION['epp_origem'] == "STARDOLL") {			// Filtro indireto por HTTP_REFERER capturado
        $filtro['ogp_opr_codigo'] = 38;
    } elseif ($_SESSION['epp_origem'] == "TMP") {			// Testes
        $filtro['ogp_opr_codigo'] = 38;
    } 
    
    
    if($prod == $GLOBALS['prod_Alawar']) {

        $codProdAlawar = $_POST['codeProd'];
        $objAlawar = new AlawarGames();
        $comboGames = $objAlawar->createComboBox($codProdAlawar);
    } 
    
    $filtro['opr'] = 1;
    $instProduto = new Produto();
    $ret = $instProduto->obterMelhorado($filtro, null, $rs);
    if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
    else $rs_row = pg_fetch_array($rs);
    
    $opr_codigo = $rs_row['ogp_opr_codigo'];

    if($opr_codigo==13) {
        $b_bloqueia_Ongame = true;
        if($rs_row['ogp_ativo'] == 1) {
            $msg = "Desculpe, este produto só pode ser comprado nos <a href=\"/busca-pdv.php\">Pontos de Venda E-Prepag</a>.";
        }
    }
    
    $b_Monitoramento_Yahoo = "";
    
    if($prod==5 || $prod==43 || $prod==48 || $prod==57 || $prod==70 ) {
        $b_Monitoramento_Yahoo = "OK";
    }
}
//echo "<pre>" .print_r($rs_row, true). "</pre>";
if($msg == ""){
    // Wagner
   			
    $produto = new Produto($rs_row['ogp_id'], $rs_row['ogp_nome'],$rs_row['ogp_descricao'],$rs_row['ogp_ativo'],$rs_row['ogp_nome_imagem'],$rs_row['ogp_data_inclusao'], $rs_row['ogp_opr_codigo'], $rs_row['ogp_mostra_integracao'], $rs_row['ogp_iof'], $rs_row['ogp_pin_request'], $rs_row['ogp_detalhes_utilizacao'], $rs_row['ogp_termos_condicoes'], $rs_row['ogp_valor_minimo'], $rs_row['ogp_valor_maximo']);
  
    if(isset($produto) && is_object($produto)){
        $produto->setNomeOperadora($rs_row['opr_nome_loja']);
        $opr_codigo = $produto->getOprCodigo();

        // [Alawar] - Se código da operadora for Alawar e $_GET['codeProd'] não estiver setado, volta para a vitrine
        if ( ($opr_codigo == $opr_codigo_Alawar) && !$_POST['codeProd']) {
            //redirect("/prepag2/commerce/jogos/");
            Util::redirect("/game/");
        }

        $pagina_titulo = $produto->getNome(); 
    }else{
        $msg = "Produto inválido";
    }
    
}

require_once "../includes/cabecalho.php";
/*
 * Fim controller
 */
$controller->setHeader();
?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
<?php
        if($msg != ""){
?>
        <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 espacamento">
            <span class="txt-vermelho espacamento"><strong><?php echo $msg;?></strong></span>
        </div>
<?php
        }
		/* else if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
		
			//error_reporting(E_ALL); 
			//ini_set("display_errors", 1); 
			
		   $produto_ativo = 1;
			$rs = null;
			$filtro['ogpm_ativo'] = 1;
			$produtoId = $prod;
			$filtro['ogpm_ogp_id'] = $produtoId;
			$instProdutoModelo = new ProdutoModelo();
			$ret = $instProdutoModelo->obter($filtro, "ogpm_valor asc", $rs);
			$produtoModeloGenebra = pg_fetch_assoc($rs);
			$modeloGenebra = $produtoModeloGenebra["ogpm_id"];
			
			$conexao = ConnectionPDO::getConnection();
			require_once "../../genebra/escolheLayout.php";

		} */
		else{
?>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 espacamento">
                    <strong>Selecione o valor</strong>
                </div>
            </div>
            <div class="row txt-cinza espacamento right10 borda-fina">
                <div class="col-md-3 col-lg-3 col-sm-12 col-xs-12">
                    <p class="bottom0">
<?php
                if($produto->getNomeImagem() && $produto->getNomeImagem() != "" && file_exists(DIR_WEB.DIR_G_IMG_PRODUTOS . $produto->getNomeImagem())) { 
?>
                    <img border="0" style="max-width: 100%" src="<?php echo DIR_G_IMG_PRODUTOS . $produto->getNomeImagem()?>">
<?php 
                }
?></p>
                    <p class="txt-azul-claro bottom0 top20"><strong><?php echo $produto->getNome()?></strong></p><br>
<?php
            if($produto->getPinRequest() != 0){
?>
                    <div class="panel-group" id="accordion">
<?php               
                if($produto->getDescricao()){
?>
                    <div class="panel panel-default">
                      <div class="panel-heading">
                        <h4 class="panel-title txt-azul-claro">
                          <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">Descrição</a>
                        </h4>
                      </div>
                      <div id="collapse1" class="panel-collapse collapse in">
                          <div class="panel-body"><?php echo $produto->getDescricao(); ?></div>
                      </div>
                    </div>
<?php                        
                }
                if($produto->getDetalhesUtilizacao()){
                    if($produto->getDescricao()){
                        $class = 'class="panel-collapse collapse"';
                    } else{
                        $class = 'class="panel-collapse collapse in"';
                    }
?>       
                    <div class="panel panel-default">
                      <div class="panel-heading">
                        <h4 class="panel-title txt-azul-claro">
                          <a data-toggle="collapse" data-parent="#accordion" href="#collapse2">Detalhes de resgate</a>
                        </h4>
                      </div>
                      <div id="collapse2" <?php echo $class; ?> >
                        <div class="panel-body"><?php echo $produto->getDetalhesUtilizacao(); ?></div>
                      </div>
                    </div>
<?php                        
                }
                if($produto->getTermosCondicoes()){
                    if($produto->getDescricao() && $produto->getDetalhesUtilizacao()){
                        $class = 'class="panel-collapse collapse"';
                    } else{
                        if(!$produto->getDetalhesUtilizacao() && !$produto->getDescricao()){
                            $class = 'class="panel-collapse collapse in"';
                        } else{
                            $class = 'class="panel-collapse collapse"';
                        }
                        
                    }
?>                  
                    <div class="panel panel-default">
                      <div class="panel-heading">
                        <h4 class="panel-title txt-azul-claro">
                          <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">Termos e condições</a>
                        </h4>
                      </div>
                      <div id="collapse3" <?php echo $class; ?> >
                          <div class="panel-body"><?php echo $produto->getTermosCondicoes(); ?></div>
                      </div>
                    </div>
<?php                        
                }
?>                          
                </div>
<?php
            } else{
?>
                    <p class="txt-azul-claro bottom0 p-top10">Publisher: <span class="txt-cinza"><?php echo $produto->getNomeOperadora();?></span></p>  
<?php 
                if(!$produto->getMostraIntegracao())
                {
?>               
                    <p class="p-top10"><?php  echo $produto->getDescricao(); ?></p>
<?php
                    }
            
                }
?>
                </div>
                <div class="col-md-9 col-lg-9 col-sm-12 col-xs-12">
<?php 
	
	/** Para Produtos Alawar **/
	$codProdAlawar = $_POST['codeProd'];
	
    if($codProdAlawar) {

            $alawarGames = new AlawarGames();
            $filtro['pag_id'] = $codProdAlawar;
            $resultGame = $alawarGames->getGamesBy($filtro);

    ?>		
            <table border="0" style="margin: 0 auto; display: block; *margin-left: 65px; font-family: Tahoma; font-size: 11px; border-collapse: collapse; border:1px solid #ccc; width: 650px;">
                    <tr>
                            <td style="padding: 5px;border-right:1px solid #ccc;"><img src="<?php echo $resultGame[$codProdAlawar]['pag_icon']; ?>" /></td>
                            <td valign="top">
                                    <br />
                                    <strong style="margin-top: 10px; margin-left: 5px;"><?php echo  $resultGame[$codProdAlawar]['pag_name']; ?></strong>
                                    <?php if( $resultGame[$codProdAlawar]['pag_online_game'] == 1) echo "<strong>(Online)</strong>"; ?><br />
                                    <p style="margin-left: 5px;margin-top: 10px;"> <?php echo  $resultGame[$codProdAlawar]['pag_description']; ?> </p><br />
                                    <strong style="margin-left: 5px;">ID :</strong> <?php echo $codProdAlawar; ?>
                                    <input type="hidden" name="gamesAlawar" id="gamesAlawar" value="<?php echo $codProdAlawar; ?>">
                            </td>
                    </tr>
            </table>
    <?php 

    }

    if(!$produto->getMostraIntegracao()) {
        if(is_null($produto->getValorMinimo()) && is_null($produto->getValorMaximo())) {
                $rs = null;
                $filtro['ogpm_ativo'] = 1;
                $produtoId = $prod;
                $filtro['ogpm_ogp_id'] = $produtoId;
                $b_show_treinamento = false;

                // Debug reinaldops
                if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                    $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                }

                if($produtoId==15) {
                    echo "<span  class='texto'>Aguarde em Breve os Planos de Acesso</span>";
                }

                // Só lista produtos com este valor de ativo
                $produto_ativo = 1;
                $instProdutoModelo = new ProdutoModelo();
                $ret = $instProdutoModelo->obter($filtro, "ogpm_valor asc", $rs);

                if(!$rs || pg_num_rows($rs) == 0){
        ?>			
                    <div class="row top10">
                        <p class="pull-right txt-vermelho"><strong><em>Não existem modelos cadastrados para este produto.</em></strong></p>
                    </div>
        <?php
                } else {

                    for($i=0; $rs_row = pg_fetch_array($rs); $i++){
                        $ogpm_ativo = $rs_row['ogpm_ativo'];
                        $b_show_treinamento = false;

                        $produtoModelo = new ProdutoModelo($rs_row['ogpm_id'], $rs_row['ogpm_ogp_id'], $rs_row['ogpm_nome'], $rs_row['ogpm_descricao'], $rs_row['ogpm_valor'], $ogpm_ativo, $rs_row['ogpm_nome_imagem'], $rs_row['ogpm_data_inclusao'], $rs_row['ogpm_pin_valor'], $rs_row['ogpm_valor_eppcash']);

                        $estoque = ($produtoModelo->contar($opr_codigo,$produtoModelo->getPinValor())>0 || $produto->getPinRequest() > 0) ? true : false;
        ?>
                        <div class="row top10">
                            <div class="col-md-5">
                                <p class="txt-cinza p-top10">
        <?php   
                                if($produtoModelo->getAtivo() == $produto_ativo || ($produtoModelo->getId()>0 && $b_show_treinamento)){ 
        ?>
                                    <strong><?php echo $produtoModelo->getNome()?></strong>
        <?php 
                                }
        ?>
                                </p>
                            </div>
                            <div class="col-md-7 bg-comprar p-top10 nome-produto c-pointer modelo-produto" estoque="<?php echo ($estoque) ? "1" : "0"; ?>" id="<?php echo $produtoModelo->getId();?>">
                                <?php echo (($GLOBALS['codeProd'])?"<input type='hidden' name='codeProd' value='".$GLOBALS['codeProd']."'>":"")?>
                                    <p>
                                        <strong>
                                            <span class="pull-left txt-azul-claro2">
        <?php 
                                            if($produtoModelo->getAtivo() == $produto_ativo || ($produtoModelo->getId()>0 && $b_show_treinamento)){ 
                                                echo "R$ ".number_format($produtoModelo->getValor(), 2, ',', '.')." | ".get_info_EPPCash_NO_Table($produtoModelo->getValorEPPCash());
                                            }
        ?>
                                            </span>
                                            <span class="pull-right txt-verde"><em>
        <?php 
                                            if($produtoModelo->getAtivo() == $produto_ativo || ($produtoModelo->getId()>0 && $b_show_treinamento)){ 
                                                if($produtoModelo->contar($opr_codigo,$produtoModelo->getPinValor())>0  || $produto->getPinRequest() > 0) {
        ?>
                                                    Comprar
        <?php 
                                                } else {
        ?>
                                                    <span class="txt-vermelho">Fora de Estoque</span>
        <?php 
                                                }
                                            } 
        ?>
                                            </em></span>
                                        </strong>
                                    </p>
                            </div>
                        </div>
                        <form id="seleciona" method="post" action="/game/pedido/passo-1.php">
                            <input type="hidden" name="acao" id="acao" value="a">
                            <input type="hidden" name="mod" id="mod" value="">
                        </form>
        <?php
                    }

                }
        }//end if(is_null($produto->getValorMinimo()) && is_null($produto->getValorMaximo()))
        else {
?>			
                    <div class="row top10 align-center">
                        <p class=""><strong>Informe o valor desejado de acordo com os valores máximo e mínimo informados</strong></p>
                        <div class="error-list">
                            
                        </div>
                    </div>
                    <div class="row top10">
                        <div class="col-xs-12 col-sm-offset-2 col-sm-8 col-md-offset-2 col-md-8 col-lg-offset-2 col-lg-8 align-center">
                            <form class="form-inline">
                                <div class="row p-left10 p-right10 p-bottom10 bg-comprar" >
                                    <div class="col-sm-12 col-md-6 col-lg-6 top10">
                                        <div class="form-group align-center">
                                            <div class="input-group align-center">
                                                <div class="input-group-addon">R$</div>
                                                <input type="number" class="form-control align-right" id="valor" name="valor" min="<?php echo $produto->getValorMinimo(); ?>" max="<?php echo $produto->getValorMaximo(); ?>" value="<?php echo number_format($produto->getValorMinimo(), 0); ?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
                                                <div class="input-group-addon">.00</div>
                                            </div>
                                            
                                        </div>  
                                    </div>
                                    <div class="c-pointer modelo-produto" estoque="1" id="<?php echo $NO_HAVE; ?>">
                                        <div class="col-sm-12 col-md-3 col-lg-3 top15 align-center">
                                             <span class="txt-azul-claro2 span-valor"><?php echo get_info_EPPCash_NO_Table((new ConversionPINsEPP)->get_ValorEPPCash('E',$produto->getValorMinimo())); ?></span>
                                        </div>
                                        <div class="col-sm-12 col-md-3 col-lg-3 top15">
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
                    <form id="seleciona" method="post" action="/game/pedido/passo-1.php">
                        <input type="hidden" name="acao" id="acao" value="a">
                        <input type="hidden" name="mod" id="mod" value="">
                        <input type="hidden" name="valor" id="valor_hidden" value="">
                        <input type="hidden" name="codeProd" id="codeProd" value="<?php echo $produto->getId()  ?>">
                    </form>
<?php   
            
        }//end else do if(is_null($produto->getValorMinimo()) && is_null($produto->getValorMaximo()))
    }else{
        echo $produto->getDescricao();
    }

?>
                </div>
            </div>
        </div>
<?php
        }
?>
    </div>
</div>
</div>
<script>
    $(function(){
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
<script src="/js/valida.js" type="text/javascript"></script>
<?php

//redireciona
if($msg != ""){
    echo "<script>manipulaModal(1,'".$msg."','Erro'); $('#modal-load').on('hidden.bs.modal', function () { location.href='/game/' });</script>";
//redirect($strRedirect); //comentado por diego no desenvolvimento do novo layout
}
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";