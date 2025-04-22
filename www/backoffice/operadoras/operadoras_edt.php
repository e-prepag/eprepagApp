<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
require_once $raiz_do_projeto."includes/gamer/constantesPinEpp.php";
require_once "/www/includes/bourls.php";

if($acao == 'novo')
{
    $acao = 'inserir';
}
else
{
    $acao = 'atualizar';
}
chdir('../admin');
?>
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
<script src="/js/jquery.mask.min.js"></script>
<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	plugins : "safari,pagebreak,layer,advhr,iespell,insertdatetime,preview,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
	
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,fontselect,fontsizeselect",
	
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,insertdate,inserttime,charmap,preview,fullscreen,|,forecolor,backcolor",
	
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",

	content_css : "/js/tiny_mce/css/content.css",

	template_external_list_url : "/js/tiny_mce/lists/template_list.js",
	external_link_list_url : "/js/tiny_mce/lists/link_list.js",
	external_image_list_url : "/js/tiny_mce/lists/image_list.js",
	media_external_list_url : "/js/tiny_mce/lists/media_list.js",

	template_replace_values : {
		username : "Some User",
		staffid : "991234"
	},
	
	translate_mode : true,
	language : "pt"
});

function validaUsuario()
{
	if (document.frmPreCadastro.opr_nome.value == "")
    {
        alert("Favor informar o Nome do Publisher.");
        document.frmPreCadastro.opr_nome.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_contato.value == "")
    {
        alert("Favor informar o Contato no Publisher.");
        document.frmPreCadastro.opr_contato.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_site.value == "")
    {
        alert("Favor informar o Site do Publisher.");
        document.frmPreCadastro.opr_site.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_cont_fone.value == "")
    {
        alert("Favor informar o Telefone do Contato no Publisher.");
        document.frmPreCadastro.opr_cont_fone.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_cont_mail.value == "")
    {
        alert("Favor informar o Email do Contato no Publisher.");
        document.frmPreCadastro.opr_cont_mail.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_pedido_estoque_prazo.value == "")
    {
        alert("Favor informar o Prazo para o Estoque deste Publisher.");
        document.frmPreCadastro.opr_pedido_estoque_prazo.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_status.value == "")
    {
        alert("Favor informar o Status deste Publisher.");
        document.frmPreCadastro.opr_status.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_contato_epp.value == "")
    {
        alert("Favor informar o Responsável na E-Prepag por este Publisher.");
        document.frmPreCadastro.opr_contato_epp.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_min_repasse.value == "")
    {
        alert("Favor informar o Valor de Repasse Mínimo para este Publisher.");
        document.frmPreCadastro.opr_min_repasse.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_facilitadora.value.trim() == "")
    {
        alert("Favor informar se utiliza Facilitadora para este Publisher. Caso não utilize preencha com 0");
        document.frmPreCadastro.opr_facilitadora.value = "";
        document.frmPreCadastro.opr_facilitadora.focus();
        return false;
    }
    if (document.frmPreCadastro.merchant_id_bexs.value.trim() == "")
    {
        alert("Favor informar o Merchant ID para este Publisher, definido pelo BEXS. Caso não tenha relação com o BEXS preencha com 0");
        document.frmPreCadastro.merchant_id_bexs.value = "";
        document.frmPreCadastro.merchant_id_bexs.focus();
        return false;
    }
    if($("#opr_prefixo_ponto_certo").hasClass("obrigatorio") && $("#opr_prefixo_ponto_certo").val().trim() == "")
    {
        alert("Obrigatório preencher o campo contendo o prefixo utilizado no relatório da rede ponto certo para identificação na coluna de descrição do arquivo de importação!");
        $("#opr_prefixo_ponto_certo").focus();
        return false;
    }
    return true;
}


function isEmail(pVal)
{
	var reTipo = /^.+@.+\..{2,3}$/;//expressão regular que valida email
	if (!reTipo.test(pVal))
	{
		alert(pVal + " NÃO é um E-Mail válido.");
		return false;
	}
	else return true;
}

function isURL(pVal)
{
	var reTipo = /^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/; // é a expressão regular apropriada para URL
	if (!reTipo.test(pVal))
	{
		alert(pVal + " NÃO é uma URL válida.");
		return false;
	}
	else return true;
}

function isTipo(pVal)
{
	var reTipo = /^\d+$/; // é a expressão regular apropriada
	if (!reTipo.test(pVal)&&(pVal!=''))
	{
		alert(pVal + " NÃO contém apenas dígitos.");
		return false;
	}
	else return true;
}

function lTrim(sStr){
	while (sStr.charAt(0) == " ") 
		sStr = sStr.substr(1, sStr.length - 1);
	return sStr;
}

function rTrim(sStr){
	while (sStr.charAt(sStr.length - 1) == " ") 
		sStr = sStr.substr(0, sStr.length - 1);
	return sStr;
}

function alltrim(sStr){
	return rTrim(lTrim(sStr));
}

function isTipo2(str) {
    str = alltrim(str);
    return /^[-+]?[0-9]+(\.[0-9]+)?$/.test(str);
}

// Carrega dados bancário reflesh 
function carga_dados(){
		$(document).ready(function(){
			var inter;
			var opr;
			if(document.getElementById('opr_internacional').checked)
			{
				inter = document.getElementById('opr_internacional').value;
			}
			else {
				inter = 0;
			}
			opr = document.getElementById('opr_codigo').value;
			//alert('['+inter+'] ['+opr+']');
			$.ajax({
				type: "POST",
				url: "operadoras_dados_bancarios.php",
				data: "opr_internacional="+inter+"&opr_codigo="+opr+"&opr_ajax=1",
				beforeSend: function(){
					$('#dados').html("<center><table><tr><td><img src='/images/pdv/loading1.gif' border='0' title='Aguardando pagamento...'/></td></tr><tr><td><font size='1'><b>Aguarde... Verificando.</b></font></td></tr></table></center>"); 
				},
				success: function(html){
					$('#dados').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});
}

// Carrega dados banco intermediario reflesh 
function carga_dados_inter(){
		$(document).ready(function(){
			var inter;
			var opr;
			if(document.getElementById('opr_banco_intermediario').checked)
			{
				inter = document.getElementById('opr_banco_intermediario').value;
			}
			else {
				inter = 0;
			}
			opr = document.getElementById('opr_codigo').value;
			//alert('['+inter+'] ['+opr+']');
			$.ajax({
				type: "POST",
				url: "operadoras_dados_bancarios_inter.php",
				data: "opr_banco_intermediario="+inter+"&opr_codigo="+opr+"&opr_ajax=1",
				beforeSend: function(){
					$('#dados_inter').html("<center><table><tr><td><img src='/images/pdv/loading1.gif' border='0' title='Aguardando pagamento...'/></td></tr><tr><td><font size='1'><b>Aguarde... Verificando.</b></font></td></tr></table></center>"); 
				},
				success: function(html){
					$('#dados_inter').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});
}

// Edita comissões 
function edita_comissoes(){
		$(document).ready(function(){
			var opr;
			opr = document.getElementById('opr_codigo').value;
			$.ajax({
				type: "POST",
				url: "/ajax/ajaxAlterarComissoes.php",
				data: "opr_codigo="+opr+"&user_backoffice=<?php echo $_SESSION['userlogin_bko'];?>",
				beforeSend: function(){
					$('#box-comisao').html("<center><table><tr><td><img src='/images/pdv/loading1.gif' border='0' title='Aguardando pagamento...'/></td></tr><tr><td><font size='1'><b>Aguarde... Verificando.</b></font></td></tr></table></center>"); 
				},
				success: function(html){
					$('#box-comisao').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});
}

$(function(){

    if($("#opr_internacional_alicota").val() == "0"){
        $("#opr_cep").attr("required", "required");
    }else{
        $("#opr_cep").removeAttr("required");
    }

    $("#opr_data_inicio_operacoes").datepicker();
    $("#opr_data_inicio_contabilizacao_utilizacao").datepicker();
    $("#data_int_nac").datepicker();

    $("#opr_distribui_ponto_certo").change(function(){
        if($(this).val() == 1){
            $("#div_opr_prefixo_ponto_certo")
                    .css("display","block");
            $("#opr_prefixo_ponto_certo")
                    .addClass("obrigatorio");
            
        }else{
            $("#div_opr_prefixo_ponto_certo")
                    .css("display","none");
            $("#opr_prefixo_ponto_certo").val("")
                    .removeClass("obrigatorio");
        }    
    });
    
    $("#opr_contabiliza_utilizacao").change(function(){
        if($(this).val() == 1){
            $("#div_opr_data_inicio_contabilizacao_utilizacao")
                    .css("display","block");
            $("#opr_data_inicio_contabilizacao_utilizacao")
                    .addClass("obrigatorio");
            
        }else{
            $("#div_opr_data_inicio_contabilizacao_utilizacao")
                    .css("display","none");
            $("#opr_data_inicio_contabilizacao_utilizacao").val("")
                    .removeClass("obrigatorio");
        }    
    });
    
    $("#opr_vinculo_empresa").change(function(){
        if($(this).val() == 1){
            if($("#trTrocaNacInt").hasClass("ocultar")){
                $("#trTrocaNacInt").removeClass("ocultar");
            }
        }else{
            $("#opr_troca_nacional_internacional").val("0");
            if(!$("#trTrocaNacInt").hasClass("ocultar")){
                $("#trTrocaNacInt").addClass("ocultar");
                $("#trNacInt").addClass("ocultar");
            }
        }
    });
    
    $("#opr_troca_nacional_internacional").change(function(){
        if($(this).val() == 1){
            if($("#trNacInt").hasClass("ocultar")){
                $("#trNacInt").removeClass("ocultar");
            }
        }else{
            if(!$("#trNacInt").hasClass("ocultar")){
                $("#trNacInt").addClass("ocultar");
            }
        }
    });
    
    $("#salvaNacionalInternacional").click(function(){
        if($("#data_int_nac").val() != "" && $("#nacionalInternacional").val() != ""){
            $.post( "/ajax/ajaxTrocaNacInt.php", {
                                                str: $("#nacionalInternacional").val(), 
                                                <?php if(isset($opr_codigo)) echo "opr: $opr_codigo,"; ?>
                                                otni_id: $("#otni_id").val(),
                                                data: $("#data_int_nac").val()
                                        }, function( data ) {
                if(data){
                    $("#tbNacionalInternacional").html(data);
                }else{
                    alert("ERRO");
                }
            });   
        }else{
            alert("Por favor, preencha os campos de data e origem.");
        }
    });
    
    $("#novaTroca").click(function(){
        $("#otni_id").val("");
        $("#nacionalInternacional").val("");
        $("#internacionalNacional").val("");
        $("#data_int_nac").val("");
        $("#divNacInc").removeClass("ocultar");
    });
    
    $("#nacionalInternacional").change(function(){
        if($(this).val() == "0"){
            $("#internacionalNacional").val("Internacional");
        }else if($(this).val() == 1){
            $("#internacionalNacional").val("Nacional");
        }else{
            $("#internacionalNacional").val("");
        }
        
    });
    
    $("#opr_distribui_ponto_certo").trigger("change");
    
    $("#opr_contabiliza_utilizacao").trigger("change");
    
    $("#opr_vinculo_empresa").trigger("change");
    
    $("#opr_troca_nacional_internacional").trigger("change");
    
    $("#opr_internacional_alicota").change(function(){
        if($("#opr_internacional_alicota").val() == "0"){
            $("#opr_cep").attr("required", "required");
        }else{
            $("#opr_cep").removeAttr("required");
        }
    });
    
    $("#opr_cep").mask("99999-999");
    
    var searching = false;
    $("#opr_cep").keyup(function() {
        cep = this.value;
        if (cep.length == 9 && !searching && $("#opr_internacional_alicota").val() == "0") {
            cep = cep.replace("-","");
            $.ajax({
                type: "POST",
                url: "/includes/cep.php",
                data: "cep=" + cep,
                beforeSend: function() {
                    searching = true;
                    $("#opr_cep_gif").html("<img src='/images/ajax-loader.gif'/>");
                },
                success: function(txt) {
                    searching = false;
                    $("#opr_cep_gif").html("");
                    if (txt.indexOf("ERRO") < 0){
                        txt = txt.split("&");
                        var msg = "";
                        var endereco = txt[0].trim() + ' ' + txt[1];
                        var bairro = txt[2];
                        var cidade = txt[3];
                        var estado = txt[4];
                        $("#opr_endereco").val(endereco);
                        $("#opr_bairro").val(bairro);
                        $("#opr_cidade").val(cidade);
                        $("#opr_estado").val(estado);
                    }else{
                        alert("CEP inexistente!");
                    }
                }
            });
        }
    });

    function callEditNacInc(id){
        var origem;
        if($("#"+id).find("td:eq(1)").html() == "Nacional")
            origem = 0;
        else
            origem = 1;
        
        $("#otni_id").val($("#"+id).attr("id"));
        $("#data_int_nac").val($("#"+id).find("td:eq(0)").html());
        $("#nacionalInternacional").val(origem);
        $("#internacionalNacional").val($("#"+id).find("td:eq(2)").html());
        $("#divNacInc").removeClass("ocultar");
    }
});
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="index.php">Operadoras</a></li>
        <li class="active"><?php echo ($acao == 'atualizar') ? "Edição" : "Cadastro"; ?></li>
    </ol>
</div>
<div class="col-md-12">
    <a class="btn btn-sm btn-info" href="../operadoras_integracao_pin/index.php" class="menu">Consultar Operadoras com Integração de PINs clique aqui.</a>
</div>
<div class="col-md-12">
	<br>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" id="acao" value="<?php  if(isset($acao)) echo $acao; ?>" />
    <input type="hidden" name="opr_codigo" id="opr_codigo" value="<?php  if(isset($opr_codigo)) echo $opr_codigo; ?>" />
        <fieldset>
            <legend>Publisher</legend>
            <table class="table txt-preto fontsize-pp p0">
                <tr>
                    <td>&nbsp;&nbsp;ID: </td>
                    <td>
                        <?php  if(isset($opr_codigo)) echo $opr_codigo; ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Nome: </td>
                    <td>
                        <?php
                        if(empty($opr_nome)) {
                        ?>
                        <input name="opr_nome" type="text" id="opr_nome" size="20" maxlength="20" value="<?php  if(isset($opr_nome)) echo $opr_nome; ?>"/>
                        <?php
                        }//end  if(empty($opr_nome))
                        else {
                            echo $opr_nome;
                        ?>
                            <input name="opr_nome" type="hidden" id="opr_nome" value="<?php  if(isset($opr_nome)) echo $opr_nome; ?>"/>
                        <?php
                        }//end else do  if(empty($opr_nome))
                        ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Nome na Lista de Produtos no Novo Layout de LAN: </td>
                    <td>
                        <input name="opr_nome_loja" type="text" id="opr_nome_loja" size="40" maxlength="40" value="<?php  if(isset($opr_nome_loja)) echo $opr_nome_loja; ?>" />
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Contato: </td>
                    <td>
                        <input name="opr_contato" type="text" id="opr_contato" size="40" maxlength="40" value="<?php  if(isset($opr_contato)) echo $opr_contato; ?>" />
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Site: </td>
                    <td><input name="opr_site" type="text" id="opr_site" size="35" maxlength="35" value="<?php  if(isset($opr_site)) echo $opr_site; ?>" onBlur="isURL(this.value);"/></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Fone Contato: </td>
                    <td><input name="opr_cont_fone" type="text" id="opr_cont_fone" size="40" maxlength="40" value="<?php  if(isset($opr_cont_fone)) echo $opr_cont_fone; ?>" onBlur="isTipo(this.value);"/> <span style="color:red; font-size: 9px;">Colocar Números sem espaços, hífen, etc.</span></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* E-Mail DIMP:</td>
                    <td><input name="opr_email_dimp" type="email" id="opr_email_dimp" size="40" maxlength="256" value="<?php  if(isset($opr_email_dimp)) echo $opr_email_dimp; ?>"/> <span style="color:red; font-size: 9px;">Informe apenas um e-mail.</span></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* E-Mail de Fechamento Financeiro:</td>
                    <td><input name="opr_cont_mail" type="text" id="opr_cont_mail" size="40" maxlength="256" value="<?php  if(isset($opr_cont_mail)) echo $opr_cont_mail; ?>" onBlur="isEmail(this.value);"/> <span style="color:red; font-size: 9px;">Sempre usar vírgula(,) para separar e-mails</span></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Prazo Pedido Estoque:</td>
                    <td><input name="opr_pedido_estoque_prazo" type="text" id="opr_pedido_estoque_prazo" size="4" maxlength="4" value="<?php  if(isset($opr_pedido_estoque_prazo)) echo $opr_pedido_estoque_prazo; ?>" onBlur="isTipo(this.value);"/> Dias</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Status:</td>
                    <td>
                        <select id='opr_status' name='opr_status'>
                            <option value='' <?php echo ((isset($opr_status) && $opr_status=="")?" selected":"") ?>>Selecione</option>
                            <option value='0' <?php echo ((isset($opr_status) && $opr_status=="0")?" selected":"") ?>>INATIVO</option>
                            <option value='1' <?php echo ((isset($opr_status) && $opr_status=="1")?" selected":"") ?>>ATIVO</option>
                            <option value='2' <?php echo ((isset($opr_status) && $opr_status=="2")?" selected":"") ?>>HOMOLOGAÇÃO</option>
                        </select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Responsável na E-Prepag pelo Publisher: </td>
                    <td>
                        <input name="opr_contato_epp" type="text" id="opr_contato_epp" size="40" maxlength="40" value="<?php  if(isset($opr_contato_epp)) echo $opr_contato_epp; ?>" />
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Valor do Repasse Mínimo: </td>
                    <td>
                        <input name="opr_min_repasse" type="text" id="opr_min_repasse" size="40" maxlength="40" value="<?php if(isset($opr_min_repasse)) echo number_format($opr_min_repasse, 2, ',', '.'); ?>" />
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Possui múltiplas cotações de dólar? </td>
                    <td>
                        <select id='opr_cotacao_dolar' name='opr_cotacao_dolar'>
                            <option value='0' <?php echo ((isset($opr_cotacao_dolar) && $opr_cotacao_dolar==0)?" selected":"") ?>>NÃO</option>
                            <option value='1' <?php echo ((isset($opr_cotacao_dolar) && $opr_cotacao_dolar==1)?" selected":"") ?>>SIM</option>
                        </select>
                        <span style="color:red; font-size: 9px;"><b>ATENÇÃO!</b> Campo novo, atualize-o por favor!</span>
                    </td>
                    <td>&nbsp;</td>
                </tr> 
                <tr>
                    <td>* Publisher é Internacional? </td>
                    <td>
                        <select id='opr_internacional_alicota' name='opr_internacional_alicota'>
                            <option value='0' <?php echo ((isset($opr_internacional_alicota) && $opr_internacional_alicota=="0")?" selected":"") ?>>NÃO é Internacional</option>
                            <option value='0.38' <?php echo ((isset($opr_internacional_alicota) && $opr_internacional_alicota=="0.38")?" selected":"") ?>>Internacional pela Facilitadora com Alíquota de 0,38%</option>
                            <option value='6.38' <?php echo ((isset($opr_internacional_alicota) && $opr_internacional_alicota=="6.38")?" selected":"") ?>>Internacional com Alíquota de 6,38%</option>
                            <option value='10' <?php echo ((isset($opr_internacional_alicota) && $opr_internacional_alicota=="10")?" selected":"") ?>>Internacional com Alíquota de 10%</option>
                            <option value='15' <?php echo ((isset($opr_internacional_alicota) && $opr_internacional_alicota=="15")?" selected":"") ?>>Internacional com Alíquota de 15%</option>
                        </select>
                    </td>
                    <td>&nbsp;</td>
                </tr> 
                <tr>
                    <td>* Possui detalhamento por Canal? </td>
                    <td>
                        <select id='opr_possui_detalhe' name='opr_possui_detalhe'>
                            <option value='0' <?php echo ((isset($opr_possui_detalhe) && $opr_possui_detalhe=="0" || empty($opr_possui_detalhe))?" selected":"") ?>>NÃO possui detalhamento</option>
                            <option value='1' <?php echo ((isset($opr_possui_detalhe) && $opr_possui_detalhe=="1")?" selected":"") ?>>Possui detalhamento</option>
                        </select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
		<tr>
                    <td>Prazo Pedido Coment&aacute;rio:</td>
                    <td><input name="opr_pedido_estoque_prazo_comentario" type="text" id="opr_pedido_estoque_prazo_comentario" size="80" maxlength="255" value="<?php  if(isset($opr_pedido_estoque_prazo_comentario)) echo $opr_pedido_estoque_prazo_comentario; ?>" /> </td>
                    <td>&nbsp;</td>
                </tr>
<?php
// Comentado por erro na atualização e perda de dados
//if (b_IsBKOUsuarioAdminComplice()) {
?>                
                <tr>
                    <td>* Exige CPF de Clientes na Loja?</td>
                    <td>
                        <select id='opr_need_cpf_lh' name='opr_need_cpf_lh'>
                            <option value='0' <?php echo ((isset($opr_need_cpf_lh) && $opr_need_cpf_lh=="0" || empty($opr_need_cpf_lh))?" selected":"") ?>>NÃO</option>
                            <option value='1' <?php echo ((isset($opr_need_cpf_lh) && $opr_need_cpf_lh=="1")?" selected":"") ?>>SIM</option>
                        </select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Empresa Vinculada:</td>
                    <td>
                        <select id='opr_vinculo_empresa' name='opr_vinculo_empresa'>
                            <option value='0' <?php echo ((isset($opr_vinculo_empresa) && $opr_vinculo_empresa=="0" || empty($opr_vinculo_empresa))?" selected":"") ?>>E-Prepag Pagamentos</option>
                            <option value='1' <?php echo ((isset($opr_vinculo_empresa) && $opr_vinculo_empresa=="1")?" selected":"") ?>>E-Prepag Administradora</option>
                        </select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr class="ocultar" id="trTrocaNacInt">
                    <td>* Possui troca nacional ou internacional?</td>
                    <td>
                        <select id='opr_troca_nacional_internacional' name='opr_troca_nacional_internacional'>
                            <option value='0' <?php if(!isset($opr_troca_nacional_internacional) || $opr_troca_nacional_internacional == "0")echo "selected"; ?>>Não</option>
                            <option value='1' <?php if(isset($opr_troca_nacional_internacional) && $opr_troca_nacional_internacional == "1") echo "selected"; ?>>Sim</option>
                        </select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr class="ocultar <?php if(isset($_GET['acao']) && $_GET['acao'] == "novo") echo "hidden";?>" id="trNacInt">
                    <td>&nbsp;</td>
                    <td>
                        <div class="bloco blockNtable row">
                            <div style="borda">
                                <span class="borda azul-claro p10 negrito pointer" id="novaTroca" style="">Novo</span>
                            </div>
                        </div>
                        <div class="row bloco blockNtable ocultar" id="divNacInc">
                            <input type="hidden" name="otni_id" id="otni_id" value="">
                            <input type="text" id="data_int_nac" style="width: 75px;" name="data_int_nac" placeholder="DATA">
                            <select id='nacionalInternacional' name='nacionalInternacional'>
                                <option value=''>Selecione a origem</option>
                                <option value='0'>Nacional</option>
                                <option value='1'>Internacional</option>
                            </select>
                            ->
                            <input type="text" id="internacionalNacional" placeholder="DESTINO" readonly="true" name="internacionalNacional">
                            <input type="button" value="salvar" id="salvaNacionalInternacional">
                        </div>
                        <div class="row" id="tbNacionalInternacional">
<?php 
                    if (isset($rs_TrocaNacionalInternacional) && pg_num_rows($rs_TrocaNacionalInternacional) > 0){
?>                        
                            <div class="borda bloco top10">
                                <p>Clique para editar</p>
                                <table class="row text-center bordaTabela" >
                                    <thead class="">
                                        <tr>
                                            <th>Data de Alteração</th>
                                            <th>Origem</th>
                                            <th>Destino</th>
                                        </tr>
                                    </thead>
                                    <tbody title="Clique para editar">
<?php
                        while($rs_row = pg_fetch_array($rs_TrocaNacionalInternacional)) {
                            $data = explode(" ",$rs_row['otni_data']);
                            $data = explode("-",$data[0]);
                            $data = $data[2]."/".$data[1]."/".$data[0];
?>
                                        <tr class='bannersOpt' onclick="callEditNacInc(this.id)" id="<?php echo $rs_row['otni_id'];?>">
                                        <td class='style1'><?php echo $data;?></td> 
                                        <td class='style1'><?php echo ($rs_row['otni_origem'] == 1) ? "Internacional" : "Nacional";?></td> 
                                        <td class='style1'><?php echo ($rs_row['otni_destino'] == 1) ? "Internacional" : "Nacional";?></td> 
                                    </tr>
<?php
                        }
?>
                                </tbody>
                            </table>
                            </div>
<?php
                    }
?>                        
                        </div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td> Data In&iacute;cio do V&iacute;nculo: </td>
                    <td>
                            <input name="opr_data_inicio_operacoes" type="text" class="form" id="opr_data_inicio_operacoes" size="11" maxlength="10" value="<?php  if(isset($opr_data_inicio_operacoes)) echo $opr_data_inicio_operacoes; ?>">
                    </td>
                    <td>&nbsp;</td>
                </tr>
<?php
//} //end if (b_IsBKOUsuarioAdminComplice()) 
?>                
                <tr>
                    <td><nobr>* Desmembra Cartões - Prestação de Contas?</nobr></td>
                    <td>
                        <select id='opr_desmembra_cartao' name='opr_desmembra_cartao'>
                            <option value='0' <?php echo ((isset($opr_desmembra_cartao) && $opr_desmembra_cartao=="0" || empty($opr_desmembra_cartao))?" selected":"") ?>>NÃO</option>
                            <option value='1' <?php echo ((isset($opr_desmembra_cartao) && $opr_desmembra_cartao=="1")?" selected":"") ?>>SIM</option>
                        </select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Emite Cartão Físico Conosco?</td>
                    <td>
                        <select id='opr_emite_cartao_conosco' name='opr_emite_cartao_conosco'>
                            <option value='0' <?php echo ((isset($opr_emite_cartao_conosco) && $opr_emite_cartao_conosco=="0" || empty($opr_emite_cartao_conosco))?" selected":"") ?>>NÃO Emite</option>
                            <option value='1' <?php echo ((isset($opr_emite_cartao_conosco) && $opr_emite_cartao_conosco=="1")?" selected":"") ?>>EMITE</option>
                        </select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Distribui PINs pela rede Ponto Certo?</td>
                    <td>
                        <div style="width: 20%;float: left;">
                            <select id='opr_distribui_ponto_certo' name='opr_distribui_ponto_certo'>
                                <option value='0' <?php echo ((isset($opr_distribui_ponto_certo) && $opr_distribui_ponto_certo=="0" || empty($opr_distribui_ponto_certo))?" selected":"") ?>>NÃO Distribui</option>
                                <option value='1' <?php echo ((isset($opr_distribui_ponto_certo) && $opr_distribui_ponto_certo=="1")?" selected":"") ?>>DISTRIBUI</option>
                            </select>
                        </div>
                        <div style="width: 80%;float: left;display:<?php echo ((isset($opr_distribui_ponto_certo) && $opr_distribui_ponto_certo=="1")?" block":"none") ?>;" id="div_opr_prefixo_ponto_certo">
                            <span style="float:left">Prefixo na Rede Ponto Certo: </span>
                            <input name="opr_prefixo_ponto_certo" type="text" id="opr_prefixo_ponto_certo" size="32" maxlength="32" value="<?php  if(isset($opr_prefixo_ponto_certo)) echo $opr_prefixo_ponto_certo; ?>" />
                        </div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><nobr>* Contabiliza PINs por data de Utilização no Canal LAN?</nobr></td>
                    <td>
                        <div style="width: 15%;float: left;">
                            <select id='opr_contabiliza_utilizacao' name='opr_contabiliza_utilizacao'>
                                <option value='0' <?php echo ((isset($opr_contabiliza_utilizacao) && $opr_contabiliza_utilizacao=="0" || empty($opr_contabiliza_utilizacao))?" selected":"") ?>>NÃO</option>
                                <option value='1' <?php echo ((isset($opr_contabiliza_utilizacao) && $opr_contabiliza_utilizacao=="1")?" selected":"") ?>>SIM</option>
                            </select>
                        </div>
                        <div style="width: 85%;float: left;display:<?php echo (($opr_contabiliza_utilizacao=="1")?" block":"none") ?>;" id="div_opr_data_inicio_contabilizacao_utilizacao">
                            <span style="float:left">Data de Utilização Inicial: </span>
                            <input name="opr_data_inicio_contabilizacao_utilizacao" type="text" class="form" id="opr_data_inicio_contabilizacao_utilizacao" size="11" maxlength="10" value="<?php  if(isset($opr_data_inicio_contabilizacao_utilizacao)) echo $opr_data_inicio_contabilizacao_utilizacao; ?>">
                        </div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Possui MarkUp nos Produtos?</td>
                    <td>
                        <select id='opr_markup' name='opr_markup'>
                            <option value='0' <?php echo ((isset($opr_markup) && $opr_markup=="0" || empty($opr_markup))?" selected":"") ?>>NÃO Possui Markup</option>
                            <option value='1' <?php echo ((isset($opr_markup) && $opr_markup=="1")?" selected":"") ?>>SIM POSSUI Markup</option>
                        </select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Utiliza a Facilitadora? <b>(Perfil Operacional ID)</b></td>
                    <td>
                        <input name="opr_facilitadora" type="text" class="form" id="opr_facilitadora" size="11" maxlength="11" value="<?php  if(isset($opr_facilitadora)) echo $opr_facilitadora; else echo "0" ?>">  <span style="color:red; font-size: 10px;"> *Prencheer com 0 (zero) caso não utilize facilitadora</span>;
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>*<b> Merchant ID</b> - BEXS</td>
                    <td>
                        <input name="merchant_id_bexs" type="text" class="form" id="merchant_id_bexs" size="11" maxlength="11" value="<?php  if(isset($merchant_id_bexs)) echo $merchant_id_bexs; else echo "0" ?>">  <span style="color:red; font-size: 10px;"> *Prencheer com 0 (zero) caso não tenha relação com o BEXS</span>;
                    </td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </fieldset>
        <br>
        <fieldset>
            <legend>Meios de pagamento BLOQUEADOS</legend>
            <table class="table txt-preto fontsize-pp">
<?php
            foreach($FORMAS_PAGAMENTO_DESCRICAO as $idForma => $icone){
                
                if(in_array($idForma, $FORMAS_PAGAMENTO_INATIVAS))
                    continue;
                
                $checked = (isset($arrTipoPagtoBloqueado) && in_array($idForma,$arrTipoPagtoBloqueado)) ? " checked='checked' " : '';
                
                echo "<tr>
                        <td><input type='checkbox' id='$idForma' $checked name='formasPagamento[$idForma]' value='$idForma'></td>
                        <td><label for='$idForma'>$icone</label></td>
                      </tr>";
            }
?>
            </table>
            
        </fieldset>
        <br>
        <fieldset>
            <legend>Valores de PINs</legend>
            <table class="table txt-preto fontsize-pp">
                <tr>
                    <td align="right">Valor 1:</td>
                    <td><input name="opr_valor1" type="text" id="opr_valor1" size="6" maxlength="6" value="<?php  if(isset($opr_valor1)) echo $opr_valor1; ?>" onBlur="isTipo2(this.value);"/></td>
                    <td align="right">Valor 2:</td>
                    <td><input name="opr_valor2" type="text" id="opr_valor2" size="6" maxlength="6" value="<?php  if(isset($opr_valor2)) echo $opr_valor2; ?>" onBlur="isTipo2(this.value);"/></td>
					<td align="right">Valor 3:</td>
                    <td><input name="opr_valor3" type="text" id="opr_valor3" size="6" maxlength="6" value="<?php  if(isset($opr_valor3)) echo $opr_valor3; ?>" onBlur="isTipo2(this.value);"/></td>
                    <td align="right">Valor 4:</td>
                    <td><input name="opr_valor4" type="text" id="opr_valor4" size="6" maxlength="6" value="<?php  if(isset($opr_valor4)) echo $opr_valor4; ?>" onBlur="isTipo2(this.value);"/></td>
					<td align="right">Valor 5:</td>
                    <td><input name="opr_valor5" type="text" id="opr_valor5" size="6" maxlength="6" value="<?php  if(isset($opr_valor5)) echo $opr_valor5; ?>" onBlur="isTipo2(this.value);"/></td>
                </tr>
				 <tr>
                    <td align="right">Valor 6:</td>
                    <td><input name="opr_valor6" type="text" id="opr_valor6" size="6" maxlength="6" value="<?php  if(isset($opr_valor6)) echo $opr_valor6; ?>" onBlur="isTipo2(this.value);"/></td>
                    <td align="right">Valor 7:</td>
                    <td><input name="opr_valor7" type="text" id="opr_valor7" size="6" maxlength="6" value="<?php  if(isset($opr_valor7)) echo $opr_valor7; ?>" onBlur="isTipo2(this.value);"/></td>
					<td align="right">Valor 8:</td>
                    <td><input name="opr_valor8" type="text" id="opr_valor8" size="6" maxlength="6" value="<?php  if(isset($opr_valor8)) echo $opr_valor8; ?>" onBlur="isTipo2(this.value);"/></td>
                    <td align="right">Valor 9:</td>
                    <td><input name="opr_valor9" type="text" id="opr_valor9" size="6" maxlength="6" value="<?php  if(isset($opr_valor9)) echo $opr_valor9; ?>" onBlur="isTipo2(this.value);"/></td>
					<td align="right">Valor 10:</td>
                    <td><input name="opr_valor10" type="text" id="opr_valor10" size="6" maxlength="6" value="<?php  if(isset($opr_valor10)) echo $opr_valor10; ?>" onBlur="isTipo2(this.value);"/></td>
                </tr>
				 <tr>
                    <td align="right">Valor 11:</td>
                    <td><input name="opr_valor11" type="text" id="opr_valor11" size="6" maxlength="6" value="<?php  if(isset($opr_valor11)) echo $opr_valor11; ?>" onBlur="isTipo2(this.value);"/></td>
                    <td align="right">Valor 12:</td>
                    <td><input name="opr_valor12" type="text" id="opr_valor12" size="6" maxlength="6" value="<?php  if(isset($opr_valor12)) echo $opr_valor12; ?>" onBlur="isTipo2(this.value);"/></td>
					<td align="right">Valor 13:</td>
                    <td><input name="opr_valor13" type="text" id="opr_valor13" size="6" maxlength="6" value="<?php  if(isset($opr_valor13)) echo $opr_valor13; ?>" onBlur="isTipo2(this.value);"/></td>
                    <td align="right">Valor 14:</td>
                    <td><input name="opr_valor14" type="text" id="opr_valor14" size="6" maxlength="6" value="<?php  if(isset($opr_valor14)) echo $opr_valor14; ?>" onBlur="isTipo2(this.value);"/></td>
					<td align="right">Valor 15:</td>
                    <td><input name="opr_valor15" type="text" id="opr_valor15" size="6" maxlength="6" value="<?php  if(isset($opr_valor15)) echo $opr_valor15; ?>" onBlur="isTipo2(this.value);"/></td>
                </tr>
            </table>
	</fieldset>
	<br>
    <fieldset>
            <legend>Comissões</legend>
            <div id='box-comisao' name='box-comisao'>
			<table class="table txt-preto fontsize-pp">
				<tr>
                    <td valign="top" colspan="3">Comissão por volume de vendas:</td>
                    <td valign="top" colspan="7"><input name="opr_comissao_por_volume" type="hidden" id="opr_comissao_por_volume" value="<?php  if(isset($opr_comissao_por_volume)) echo $opr_comissao_por_volume; ?>" /> <?php echo ( (!empty($opr_comissao_por_volume))?"Sim":"Não") ?>
					<?php 
                     if(!empty($opr_codigo)) {
                         /*
						$sql = "select o.opr_codigo, o.opr_nome, c.* 
								from operadoras o 
									left outer join tb_comissoes c on o.opr_codigo=c.co_opr_codigo 
								where to_char(co_data_inclusao,'YYYYDDMMHH24MISS') = (select to_char(max(co_data_inclusao),'YYYYDDMMHH24MISS') from tb_comissoes where co_opr_codigo=$opr_codigo) 
										and opr_codigo = $opr_codigo 
										-- and opr_comissao_por_volume = 1
								order by co_opr_codigo, co_canal, co_data_inclusao desc, co_volume_tipo, co_volume_min ";
                         */
                        $sql = "select o.opr_codigo, o.opr_nome, o.opr_comissao_por_volume , c.*
                                from operadoras o
                                left outer join tb_comissoes c on o.opr_codigo=c.co_opr_codigo
                                where  opr_codigo = {$opr_codigo}
                                    AND to_char(co_data_inclusao,'YYYYDDMMHH24MISS') = (select to_char(max(co_data_inclusao),'YYYYDDMMHH24MISS') from tb_comissoes where co_opr_codigo=$opr_codigo)
                                    and co_canal != 'C'
                                    AND co_tipo != 'F'
                                order by co_opr_codigo, co_canal, co_data_inclusao ASC,co_tipo, co_volume_tipo, co_volume_min;";
						$rs = SQLexecuteQuery($sql);
						if(!$rs) {
							echo "Erro ao listar Comissõess.\n";
							echo "sql: ".$sql."<br>\n<hr>\n";
						}
						$total_table = pg_num_rows($rs);

						if($opr_comissao_por_volume) {
                            ?>
                            <style>
                                .paddingTd {padding: 7px;}
                                .paddingTdData {padding: 5px;}
                            </style>
                            <?php
							$j = 1;
							if($rs && pg_num_rows($rs) > 0){
								echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;font-family:Arial, Helvetica, sans-serif; font-size:10px;'>\n
										<tr style='text-align:center;background-color:#ffffcc;'>
											<th class='paddingTd'>N</td>
											<th class='paddingTd'>ID</td>
											<th class='paddingTd'>Tipo</td>
											<th class='paddingTd'>Canal</td>
											<th class='paddingTd'>Tipo de Volume</td>
											<th class='paddingTd'>Data de Inclusão</td>
											<th class='paddingTd'>Comissão</td>
											<th class='paddingTd'>Volume Min</td>
											<th class='paddingTd'>Volume Max</td>
										</tr>\n";
								while($rs_row = pg_fetch_array($rs)){
									if($rs_row['co_tipo'] != 'F') {
                                        $data = substr($rs_row['co_data_inclusao'], 0, 19);
                                        //$data = $data;//substr($data, 0, 4) . '/' . substr($data, 2, 2). '/' . substr($data, 2, 2);
                                        list($ano, $mes, $dia_hora) = explode('-', $data);
                                        list($dia,$hora) = explode(' ', $dia_hora);
                                        $data = "{$dia}/{$mes}/{$ano} {$hora}";
										echo "<tr align='center'".(($rs_row['co_comissao']==0)?" style='background-color:yellow; color:red'":"").">
												<td class='paddingTdData'>".($j++)."</td>
												<td class='paddingTdData'>".$rs_row['co_id']."</td>
												<td class='paddingTdData'>".$rs_row['co_tipo']."</td>
												<td class='paddingTdData'>".$rs_row['co_canal']."</td>
												<td class='paddingTdData'>".$rs_row['co_volume_tipo']."</td>
												<td class='paddingTdData'><nobr>".$data."</nobr></td>
												<td class='paddingTdData'>".$rs_row['co_comissao']."</td>
												<td class='paddingTdData'>".(($rs_row['co_tipo']=="V")?number_format($rs_row['co_volume_min'], 2, ',', '.'):"-")."</td>
												<td class='paddingTdData'>".(($rs_row['co_tipo']=="V")?number_format($rs_row['co_volume_max'], 2, ',', '.'):"-")."</td>
											</tr>\n";
									}//end if
								}
								echo "</table>\n";							
							} else {
								echo "Sem registros de comissão por volume<br>";
							}
						} else {
							if($total_table>0) {
								$rs_row = pg_fetch_array($rs);
								$co_id = $rs_row['co_id'];
								if($co_id>0) {
									$s_str_s =(($total_table>1)?"s":"");
									$s_str_m =(($total_table>1)?"m":"");
									echo "&nbsp;<blockquote style='color: red'>Existe$s_str_m $total_table registro$s_str_s de comissão por volume cadastrado$s_str_s para esta operadora.</blockquote>";
								} else {
									echo "<blockquote>&nbsp;Não existem registros de comissão por volume cadastrados para esta operadora. (2)</blockquote>";
								}
							} else {
								echo "<blockquote>&nbsp;Não existem registros de comissão por volume cadastrados para esta operadora. (1)</blockquote>";
							}
						}
                                         } //end if(!empty($opr_codigo))
					?>
					</td>
                </tr>
				<tr>
                <?php
				if(!isset($opr_comissao_por_volume) || !$opr_comissao_por_volume) {
				?>
                    <td align="right">Money:</td>
                    <td><input name="comiss_m" type="text" id="comiss_m" size="10" maxlength="15" value="<?php  if(isset($comiss_m)) echo $comiss_m; ?>" readonly/></td>
                    <td align="right">Express:</td>
                    <td><input name="comiss_e" type="text" id="comiss_e" size="10" maxlength="15" value="<?php  if(isset($comiss_e)) echo $comiss_e; ?>" readonly/></td>
					<td align="right">LAN:</td>
                    <td><input name="comiss_l" type="text" id="comiss_l" size="10" maxlength="15" value="<?php  if(isset($comiss_l)) echo $comiss_l; ?>" readonly/></td>
                	<td align="right">POS:</td>
                    <td><input name="comiss_p" type="text" id="comiss_p" size="10" maxlength="15" value="<?php  if(isset($comiss_p)) echo $comiss_p; ?>" readonly/></td>
				<?php
				}//end if($opr_comissao_por_volume)
				?>
                    <td align="right">Cartões:</td>
                    <td><input name="comiss_c" type="text" id="comiss_c" size="10" maxlength="15" value="<?php  if(isset($comiss_c)) echo $comiss_c; ?>" readonly/></td>
				</tr>
				<tr>
					<td colspan="10" align="right">
						<input type="button" name="ajax_atualiza" id="ajax_atualiza" value="Editar" onclick="javascript:edita_comissoes();"/>
					</td>
				</tr>
	        </table>
			</div>
	</fieldset>
	<br>
    <fieldset>
            <legend>Dados para Emiss&atilde;o NFSe</legend>
            <table class="table txt-preto fontsize-pp">
                <tr>
                    <td>Raz&atilde;o: </td>
                    <td>
                        <input name="opr_razao" type="text" id="opr_razao" size="80" maxlength="255" value="<?php  if(isset($opr_razao)) echo $opr_razao; ?>"/>
                    </td>
                    <td>Para Publishers internacional este campo tamb&eacute;m ser&aacute; usado para account owner.</td>
                </tr>
                <tr>
                    <td>CNPJ: </td>
                    <td>
                        <input name="opr_cnpj" type="text" id="opr_cnpj" size="18" maxlength="14" value="<?php  if(isset($opr_cnpj)) echo $opr_cnpj; ?>" onBlur="isTipo(this.value);"/>&nbsp;ATEN&Ccedil;&Atilde;O: Colocar N&uacute;meros sem espa&ccedil;os, h&iacute;fen, etc.
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>IM: </td>
                    <td><input name="opr_im" type="text" id="opr_im" size="12" maxlength="10" value="<?php  if(isset($opr_im)) echo $opr_im; ?>" onBlur="isTipo(this.value);"/>&nbsp;Inscri&ccedil;&atilde;o Municipal - Colocar N&uacute;meros sem espa&ccedil;os, h&iacute;fen, etc.</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>Endere&ccedil;o: </td>
                    <td><input name="opr_endereco" type="text" id="opr_endereco" size="80" maxlength="255" value="<?php  if(isset($opr_endereco)) echo $opr_endereco; ?>" /></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>N&uacute;mero:</td>
                    <td><input name="opr_numero" type="text" id="opr_numero" size="10" maxlength="8" value="<?php  if(isset($opr_numero)) echo $opr_numero; ?>" onBlur="isTipo(this.value);"/></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>Complemento:</td>
                    <td><input name="opr_complemento" type="text" id="opr_complemento" size="10" maxlength="10" value="<?php  if(isset($opr_complemento)) echo $opr_complemento; ?>"/></td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td>Bairro:</td>
                    <td><input name="opr_bairro" type="text" id="opr_bairro" size="40" maxlength="40" value="<?php  if(isset($opr_bairro)) echo $opr_bairro; ?>" /> </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td>CEP:</td>
                    <td><input name="opr_cep" type="text" id="opr_cep" size="10" maxlength="9" value="<?php  if(isset($opr_cep)) echo $opr_cep; ?>"/> <span id="opr_cep_gif" class="p-left-3"></span></td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td>Cidade:</td>
                    <td><input name="opr_cidade" type="text" id="opr_cidade" size="40" maxlength="40" value="<?php  if(isset($opr_cidade)) echo $opr_cidade; ?>" /> </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td>Estado:</td>
                    <td><input name="opr_estado" type="text" id="opr_estado" size="2" maxlength="2" value="<?php  if(isset($opr_estado)) echo $opr_estado; ?>" /> </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td>Pais:</td>
                    <td><input name="opr_pais" type="text" id="opr_pais" size="40" maxlength="40" value="<?php  if(isset($opr_pais)) echo $opr_pais; ?>" /> </td>
                    <td>&nbsp;</td>
                </tr>
            </table>
    </fieldset>
	<br>
    <fieldset>
            <legend>Dados Banc&aacute;rios</legend>
            <table class="table txt-preto fontsize-pp">
                <tr>
                    <td>Publisher Internacional: </td>
                    <td width="80%">
                        <input name="opr_internacional" type="checkbox" id="opr_internacional" value="1" <?php if(!empty($opr_internacional)) echo "checked" ?> onclick="carga_dados();"/> Sim
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
					<td colspan="3">
						<div id='dados'>
						<?php
						include('operadoras_dados_bancarios.php');
						?>
						</div>
					</td>
				</tr>
			</table>
        </fieldset>
        <br>
		<div id='dados_inter'>
		</div>
		<fieldset>
            <legend>Sistema de Bonifica&ccedil;&atilde;o de LAN Houses</legend>
            <table class="table txt-preto fontsize-pp">
                <tr>
                    <td>Possui BSLAN:</td>
                    <td width="80%">
                        <input name="opr_bslan" type="checkbox" id="opr_bslan" value="1" <?php if(!empty($opr_bslan)) echo "checked" ?>/> Sim
					</td>
                </tr>
				<tr>
                    <td>Termo de Ades&atilde;o:</td>
                    <td>
						<textarea name="opr_bslan_rule" id="opr_bslan_rule" cols="80" rows="5"><?php if(isset($opr_bslan_rule)) echo trim($opr_bslan_rule); ?></textarea>
					</td>
                </tr>
            </table>
		</fieldset>
		<br>
        <fieldset>
            <legend>Publisher Utilizar Gera&ccedil;&atilde;o de PINs da E-PREPAG</legend>
            <table class="table txt-preto fontsize-pp">
                <tr>
                    <td>Formato:</td>
                    <td><select id='opr_pin_epp_formato' name='opr_pin_epp_formato'>
						<option value=''<?php echo ((!isset($opr_pin_epp_formato) || $opr_pin_epp_formato=="")?" selected":"") ?>>N&atilde;o Utiliza Gera&ccedil;&atilde;o de PINs E-PREPAG</option>
						<?php
							foreach($formato_array as $key => $val) {
								echo "<option value='".$key."'".(($opr_pin_epp_formato==(string)$key)?" selected":"").">".$val." - Formato (".$key.")</option>\n";
							}
						?>
					  </select></td>
                </tr>
            </table>
	</fieldset>
	<table class="table txt-preto fontsize-pp">
	<tr>
		<td colspan="3">&nbsp;* Campos Obrigat&oacute;rios</td>
	</tr>
	<?php
	if (!empty($opr_product_type)) {
	?>
	<tr>
		<td colspan="3" bgcolor="#CCCCCC" style="font-family:verdana,arial;font-weight:bold;">&nbsp;* Esta Operadora Possui Integra&ccedil;&atilde;o de Utiliza&ccedil;&atilde;o de PINs</td>
	</tr>
	<?php
	}
	?>
	<tr>
        <td colspan="3" align="center"><input type="submit" name="Submit" class="btn btn-info btn-sm" value="<?php if($acao == 'atualizar') echo "Atualizar"; else echo"Cadastrar"; ?>"/></td>
	</tr>
	</table>
</form>
</div>
<?php
if(!empty($opr_banco_intermediario)) {
?>
<script type="text/javascript">
	carga_dados_inter();
</script>
<?php
}
?>
