<?php
die("Acesso negado!");
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/PagamentoOfflineController.class.php';

$controller = new PagamentoOfflineController;
/*
 * Início controller
 */
$msg = "";
$validate = new Validate();

if(!isset($_POST['email']) || !isset($_POST['confirma_email']) || $_POST['email'] != $_POST['confirma_email'] || $validate->email($_POST['email'])){ 
    $msg = "E-mail '".$_POST['email']."' inválido.";
    $link = "/game/pedido/pagamento-offline.php";
}

require_once DIR_INCS . "inc_register_globals.php";

//Recupera carrinho do session
$carrinho = $_SESSION['carrinho'];

//Produto Modelo
if(!$mod) $mod = $_POST['mod'];

//Alawar - idjogo
if(!$codeProd) $codeProd = $_POST['codeProd'];

/*    Teste de quantidade de itens   */
//Captura da quantidade para teste 
if(!$qtde_nova) {
    if(isset($_POST['qtde'])) {
        $qtde_nova = $_POST['qtde'];
    }
    else {
        $qtde_nova =  1;
    }
}

//Variavel para habilitar o teste de carrinho existente
$pularTesteInicial = ((isset($GLOBALS['_SESSION']['carrinho']) && count($GLOBALS['_SESSION']['carrinho'])) > 0 ? TRUE : FALSE);

if(verificaQtdeCarrinho($qtde_nova, $mod, $pularTesteInicial)) {
    if($mod && $mod != "" && is_numeric($mod)){
        //Acao
        if(!$acao) 
            $acao = $_POST['acao'];

        //Adiciona modelo no carrinho
        //---------------------------------------------------------------
        if($acao == "a"){
            // Alawar - Verificar se o cadigo do Jogo foi enviado para o carrinho
            if( ($mod == $prod_mod_Alawar) && !$_POST['codeProd'] ) {				
//              redirect("/prepag2/commerce/jogos/");
                Util::redirect("/game/");
            }

            //verifica se o modelo esta no carrinho
            if(!$carrinho[$mod]){
                //verifica se o modelo existe e esta ativo	
                $rs = null;
                $filtro['ogpm_ativo'] = 1;
                $filtro['ogpm_id'] = $mod;
                $filtro['com_produto'] = true;	// **

                // Debug reinaldops
                if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                    if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                        $filtro['show_treinamento'] = 1;
                    }
                    
                }

                $ogpm_ativo = $rs_row['ogpm_ativo'];
                $b_show_treinamento = false;

                $ret = ProdutoModelo::obter($filtro, null, $rs);

                //Adiciona modelo no carrinho
                if($rs && pg_num_rows($rs) == 1){
                    $carrinho[$mod] = 1;
                    if(isset($codeProd) && ($codeProd>0)) {
                        $GLOBALS['_SESSION']['carrinho_alawar_prod_id'] = $codeProd;
                    }
                }

            }
        }
			
        //remove modelo no carrinho
        //---------------------------------------------------------------
        if($acao == "d"){

            //verifica se o modelo ja esta no carrinho
            if($carrinho[$mod]){

                //Remove modelo no carrinho
                //$carrinho[$mod] = null;
                unset($carrinho[$mod]);
            }
        }

        //atualiza modelo no carrinho
        //---------------------------------------------------------------
        if($acao == "u"){

            //Qtde
            if(!$qtde) $qtde = $_POST['qtde'];

            //Atualiza se for qtde valida
            if($qtde && is_numeric($qtde) && $qtde > 0 ){

                //verifica se o modelo esta no carrinho
                if($carrinho[$mod]){
                    //atualiza modelo no carrinho
                    $carrinho[$mod] = $qtde;
                    //Se o modelo nao esta no carrinho, adiciona
                } else {
                    //verifica se o modelo existe e esta ativo	
                    $rs = null;
                    $filtro['ogpm_ativo'] = 1;
                    $filtro['ogpm_id'] = $mod;
                    $filtro['com_produto'] = true;	// **

                    // Debug reinaldops
                    if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                            if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                                    $filtro['show_treinamento'] = 1;
                            }
                    }

                    if(isset($controller->usuarios)) {
                            if($controller->usuario->b_IsLogin_pagamento_pin_eprepag()) {
//							$filtro['ogpm_ativo'] = 0;
                            }
                    }
                    
                    $ret = ProdutoModelo::obter($filtro, null, $rs);

                    //Adiciona modelo no carrinho
                    if($rs && pg_num_rows($rs) == 1){
                            $carrinho[$mod] = $qtde;
                    }
                }
            }
        }
			
        //Devolve carrinho no session
        $_SESSION['carrinho'] = $carrinho;


    }//end if($mod && $mod != "" && is_numeric($mod))

}//end if(verificaQtdeCarrinho($qtde_nova, $mod))
else 
    $msg = "Número máximo de produtos no carrinho é de ".$QTDE_MAX_ITENS." unidades.";

require_once DIR_WEB . 'game/includes/cabecalho.php';
/*
 * Fim controller
 */
$controller = new HeaderController;
$controller->setHeader();
?>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script type="text/javascript" src="/js/valida.js"></script>
<script type="text/javascript" src="/js/ajax.js"></script>
<script>
function envFacebook(prod) {
    var link = "http://www.facebook.com/share.php?u=http://www.e-prepag.com.br/prepag2/commerce/modelos.php?prod="+prod+"&title=E-PREPAG&bodytext=&topic=";
    var prod = eval(prod);
    //alert(prod);
    //alert(link);
    window.open(link,'facebook');
}

function envTwitter(prod) {
    var link = "http://twitter.com/share?url=http://www.e-prepag.com.br/prepag2/commerce/modelos.php?prod="+prod+"&via=E-Prepag&text=E-PREPAG&";
    var prod = eval(prod);
    //alert(prod);
    //alert(link);
    window.open(link,'twitter');
}

function calculaTotalEPP(){
    totalEPP = 0;
    p = document.getElementsByTagName("input");
    for(i=0; i<p.length; i++){
        if(p[i].name == "produtos[]"){
            q = document.getElementById('q'+p[i].value);
            v = document.getElementById('e'+p[i].value);
            if(q.value != '' && v.value != '') totalEPP += q.value * v.value;
        }
    }
    return totalEPP;
}
function calcula(){
    total = calculaTotal();
    totalEPP = calculaTotalEPP();
    if(total><?php echo $GLOBALS['RISCO_GAMERS_VALOR_MAX'];?>) {
        manipulaModal(1,"O valor máximo por boleto é de R$<?php echo number_format($GLOBALS['RISCO_GAMERS_VALOR_MAX'],2,",",".");?>\n\nPor favor, preencha novamente o pedido.","Erro");
        resetaCampos();
        total = '0';
        totalEPP = '0';
    }
    else {
        total = '' + total.toFixed(2);
        total = total.replace('.',',');
        totalEPP = '' + totalEPP.toFixed(0);
        totalEPP = totalEPP.replace('.',',');
    }
    document.getElementById('divTotal').innerHTML = total;
    //document.getElementById('divTotalEPP').innerHTML = totalEPP;
}
function resetaCampos(){
    p = document.getElementsByTagName("input");
    pfirst = null;
    pval = 10000;
    for(i=0; i<p.length; i++){
        if(p[i].name == "produtos[]"){
            if(pval > p[i].value) {
                pfirst = q;
                pval = p[i].value;
            }
            q = document.getElementById('q'+p[i].value);
            q.value = '';
        }
    }
    if(pfirst != null) {
        pfirst.focus();
    }
}

function finalizaVenda(){
    var params = $("#form1").serialize();
    
    $.ajax({
        type: "POST",
        url: "/ajax/gamer/boleto_express_finaliza.php",
        data: params,
        success: function(txt){
            $("#geraBoleto").html(txt);
            waitingDialog.hide();
        }
    });
}

function fcnJanelaBoleto(token, url){
    window.open(url+'?token='+token,'','');
}

function fcnAbreBoleto() {
    document.getElementById('btnAbreBoleto').click();
}

function disableElementId(id, disabled){
    document.getElementById(id).disabled = disabled;
}
function hideElement(which){
    which_element = document.getElementById(which);
    if (!which_element)
        return;
//	if (which_element.style.display=="block")
        which_element.style.display="none";
//	else
//		which_element.style.display="block";
}
function verifica(evt)
{
    if (evt == 17)
    {
        return false;
    }
}

function consultaCpf(){
    var retorno;
    $.ajax({
            type: "POST",
            async: false,
            dataType: "JSON",
            url: "/ajax/ajaxCpf.php",
            data: {cpf: $("#cpf").val(), dataNascimento: $("#dataNascimento").val()},
            success: function(txt){
                if(txt.erros.length > 0){
                    retorno = txt.erros;
                    $("#prosseguir").removeAttr("disabled");

                }else{
                   retorno = ""; 
                }     

            }
        });
        return retorno;
} //end function consultaCpf()

$(function(){
    
    $("#cpf").mask("999.999.999-99");
    $("#dataNascimento").mask("99/99/9999");
        
    $("#cpf").blur(function(){
    
        if(validaCpf($('#cpf').val()) == false){
            $(this).val("");
            manipulaModal(1,"Número de CPF inválido.","Erro");
        }

    }).keyup(function(){
        if($(this).val().length == 14){
            if(validaCpf($('#cpf').val()) == false){
                $(this).val("");
                manipulaModal(1,"Número de CPF inválido.","Erro");
                $("#cpf").focus();
            }
        }
    });
    
    $("#prosseguir").click(function(){
        $("#prosseguir").html("Aguarde...");
        $("#prosseguir").attr("disabled","disabled");
        waitingDialog.show('Por favor, aguarde...',{dialogSize: 'sm'});
        
        setTimeout(function(){ 
            $("#form1").trigger("submit");
        }, 300);
    });
    
    $("#form1").submit(function(){

        var msg = "";
        
        String.prototype.stripHTML = function() {
            return this.replace(/<.*?>/g, '');
        };
        
        if($('#cpf').length > 0){
           if($("#dataNascimento").val().length < 10){
               msg +="\n Data de nascimento deve ser preenchida.";
            }else{
                var currentDate = new Date();
                var dtNasc = $("#dataNascimento").val().split("/");
                var objDtNasc = new Date(parseInt(dtNasc[2]),parseInt(dtNasc[1])-1,parseInt(dtNasc[0]));
                if(objDtNasc.getTime() > currentDate.getTime()){
                    msg +="\n Data de nascimento inválida.";
                }
            }
            
            if($("#cpf").val().length < 14)
            {
                msg +="\n CPF deve ser preenchido.";
            }else{
                if(msg == ""){
                    msg = consultaCpf();
                }
                
            }
        }
        
	if(msg != ""){
            $("#prosseguir").html("Prosseguir");
            manipulaModal(1,msg.stripHTML(),"Erro");
            $("#prosseguir").removeAttr("disabled");
            waitingDialog.hide();
            return false;
	} else {
            finalizaVenda();
            return false;
	}
    });
});
</script>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12 txt-azul-claro top10">
                    <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">boleto express</h4></strong>
                </div>
            </div>
<?php
            //Recupra carrinho do session
            $carrinho = $_SESSION['carrinho'];

            if(!$carrinho || count($carrinho) == 0){		
                $msg = "Carrinho vazio no momento.";
            }
            
            if($msg != ""){
?>			
            <div class="row">
                <div class="col-md-12 espacamento text-center txt-vermelho">
                    <?php echo $carrinhoVazio;?>
                </div>
                <div class="col-md-3 col-md-offset-7 espacamento">
                    <a href="<?php echo (isset($link) && $link != "") ? $link : "/game/";?>" class="btn btn-primary">Voltar</a>
                </div>
            </div>
            <script>
                manipulaModal(1,"<?php echo htmlspecialchars($msg); ?>","Erro");
            </script>
<?php
            } else {
?>
            <form id="form1" name="form1" method="post" target="_blank">
            <div class="row txt-cinza espacamento top20">
                <div class="col-md-12 bg-cinza-claro">
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
                $total_geral_pin_epp_cash = 0;
                foreach ($carrinho as $modeloId => $qtde){

                    $qtde = intval($qtde);
                    $rs = null;
                    $filtro['ogpm_ativo'] = 1;
                    $filtro['ogpm_id'] = $modeloId;
                    $filtro['com_produto'] = true;
                    // Debug reinaldops
                    if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                        if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                            $filtro['show_treinamento'] = 1;
                        }
                    }
                    $instProdutoModelo = new ProdutoModelo;
                    $ret = $instProdutoModelo->obter($filtro, null, $rs);
                    
                    if($rs && pg_num_rows($rs) != 0){
                        $rs_row = pg_fetch_array($rs);
                        $total_geral += $rs_row['ogpm_valor'] * $qtde;
                        $total_geral_pin_epp_cash  += $rs_row['ogpm_valor_eppcash'] * $qtde;
                        $instProduto = new Produto;
                        $iof = $instProduto->buscaIOF($modeloId) ? "Incluso" : "";
?>
                    
                          <tr class="text-center trListagem">
                            <td class="text-left">
                                <input name="produtos[]" id="produtos" type="hidden" value="<?php echo $rs_row['ogpm_id'];?>" />
                                <input name="v<?php echo $rs_row['ogpm_id'];?>" id="v<?php echo $rs_row['ogpm_id'];?>" type="hidden" value="<?php echo $rs_row['ogpm_valor'];?>" />
                                <input name="e<?php echo $rs_row['ogpm_id'];?>" id="e<?php echo $rs_row['ogpm_id'];?>" type="hidden" value="<?php echo $rs_row['ogpm_valor_eppcash'];?>" />
                                <input name="q<?php echo $rs_row['ogpm_id'];?>" id="q<?php echo $rs_row['ogpm_id'];?>" type="hidden" value="<?php echo $qtde;?>" />
                                <?php echo $rs_row['ogp_nome']?>
                                <?php if($rs_row['ogpm_nome']!=""){ ?> - <?php echo $rs_row['ogpm_nome']?><?php }?>
                            </td>
                            <td><?php echo $iof;?></td>
                            <td><?php echo number_format($rs_row['ogpm_valor'], 2, ',', '.')?></td>
                            <td><?php echo htmlspecialchars($qtde, ENT_QUOTES);?></td>
                            <td><?php	echo number_format($rs_row['ogpm_valor']*$qtde, 2, ',', '.');?></td>
                            <td><?php echo get_info_EPPCash_NO_Table($rs_row['ogpm_valor_eppcash']*$qtde);?></td>
                          </tr>
                          
<?php
                    }
                }
                
                if($total_geral>$GLOBALS['RISCO_GAMERS_VALOR_MAX']) {
                    $msg = "O valor m\u00E1ximo por boleto \u00E9 de R$".number_format($GLOBALS['RISCO_GAMERS_VALOR_MAX'],2,",",".");
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
            if($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) {
                if(b_Is_Boleto_Express_Bradesco()){
                        $taxa_adicional = $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL'];
                }
                elseif(b_Is_Boleto_Express_Santander()) {
                        $taxa_adicional = $GLOBALS['BOLETO_MONEY_BANESPA_TAXA_ADICIONAL'];
                }
                elseif(b_Is_Boleto_Express_Itau("", "")) {
                        $taxa_adicional = $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'];
                } else {
                        $taxa_adicional = 0;
                }
?>
            <div class=" txt-cinza-claro2 text-left p-left10">
<?php                
                    echo "Para compras abaixo de R$ ".number_format($GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'],2,",",".")." será cobrada uma taxa de boleto de R$ ".number_format($taxa_adicional,2,",",".").".";
?>
            </div>
<?php                
            }
            
?>
            <div id="geraBoleto" class="row espacamento">
                <div class="col-md-7 txt-preto">
                    <div class="row top20">
                        <div class="col-md-12 txt-azul-claro text-right">
                            <strong>Por favor, informe o seu CPF e data de nascimento:</strong>
                        </div>
                    </div>
                    <div class="row top20 form-group">
                        <div class="col-md-6 text-right">
                            <label for="cpf">CPF:</label>
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" id="cpf" name="cpf" type="text" value="">
                        </div>
                    </div>
                    <div class="row top10  form-group">
                        <div class="col-md-6 text-right">
                            <label for="dataNascimento">Data de nascimento:</label>
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" id="dataNascimento" name="dataNascimento" type="text" value="">
                        </div>
                    </div>
                    <div class="row top10 form-group">
                        <div class="col-md-12 dislineblock">
                            <a href="javascript:void(0);" id="prosseguir" class="pull-right btn btn-success">Prosseguir</a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tab_content">&nbsp;</div>
            <div class="row txt-cinza-claro2 text-center espacamento">
                O pagamento de compras só pode ser feito por maiores de 18 anos e menores de idade quando autorizados pelos pais ou responsável.
            </div>
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_POST["email"]);?>">
            <input type="hidden" name="emailConf" value="<?php echo htmlspecialchars($_POST["email"]);?>">
            </form>
<?php
            }
?>
        </div>
    </div>
</div>
</div>
<?php
require_once DIR_WEB . "game/includes/footer.php";

function verificaQtdeCarrinho($qtde_nova = 0, $modelo =0, $pularTesteInicial = true){
    if($pularTesteInicial) {
        $carrinho = $_SESSION['carrinho'];
        $total_aux = $qtde_nova;
        if(is_array($carrinho) && count($carrinho) > 0) {
            foreach($carrinho as $modeloId => $qtde){
                    if($modelo != $modeloId) {
                            $total_aux += $qtde;
                    }
            } //end foreach
            //echo "[$total_aux]<br>";
            if($total_aux > $GLOBALS['QTDE_MAX_ITENS']) {
                return false;
            }
            else {
                return true;
            }
        }//end if(is_array($carrinho) && count($carrinho) > 0) 
        else {
            return false;
        }
    }//end if($pularTesteInicial)
    else {
        return true;
    }//end else do if($pularTesteInicial)
} //end function verificaQtdeCarrinho
