<?php
require_once '../../../includes/constantes.php';
include_once $raiz_do_projeto."includes/security.php";

if($acao == 'novo')
{
    $acao = 'inserir';
}
else
{
    $acao = 'atualizar';
}

$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') order by opr_nome"; 
$resopr = SQLexecuteQuery($sqlopr);
?>
<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/js/popcalendar.js"></script>
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
	if (document.frmPreCadastro.promo_nome.value == "")
    {
        alert("Favor informar o Nome da Promoção.");
        document.frmPreCadastro.promo_nome.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_codigo.value == "")
    {
        alert("Favor informar o Publisher.");
        document.frmPreCadastro.opr_codigo.focus();
        return false;
    }
    if (document.frmPreCadastro.promo_data_inicio.value == "")
    {
        alert("Favor informar a Data Início da Vigência da Promoção.");
        document.frmPreCadastro.promo_data_inicio.focus();
        return false;
    }
    if (document.frmPreCadastro.promo_data_fim.value == "")
    {
        alert("Favor informar a Data Fim da Vigência da Promoção.");
        document.frmPreCadastro.promo_data_fim.focus();
        return false;
    }
    if (document.frmPreCadastro.promo_descricao.value == "")
    {
        alert("Favor informar a Descrição da Promoção.");
        document.frmPreCadastro.promo_descricao.focus();
        return false;
    }
<?php
if(empty($promo_banner)) {
?>
    if (document.frmPreCadastro.promo_banner.value == "")
    {
        alert("Favor informar o Banner da Promoção.");
        document.frmPreCadastro.promo_banner.focus();
        return false;
    }
<?php
}
if(empty($promo_banner_resposta)) {
?>
    if (document.frmPreCadastro.promo_banner_resposta.value == "")
    {
        alert("Favor informar o Banner da Resposta da Promoção.");
        document.frmPreCadastro.promo_banner_resposta.focus();
        return false;
    }
<?php
}
?>
    return true;
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
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?><?php if($acao == 'atualizar') echo " : Edição"; else echo" : Cadastro"; ?></a></li>
    </ol>
</div>
<div id="msg" name="msg" class="lstDado">
</div>
<div class="col-md-12">
    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" value="<?php echo $acao; ?>" />
    <input type="hidden" name="promo_id_update" value="<?php if(isset($promo_id)) echo $promo_id; ?>" />
        <fieldset>
            <legend>Promoção</legend>
            <table class="table txt-preto fontsize-pp">
                <tr>
                    <td>* Nome: </td>
                    <td>
                        <input name="promo_nome_update" type="text" id="promo_nome_update" size="40" maxlength="40" value="<?php if(isset($promo_nome)) echo $promo_nome; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Publisher: </td>
                    <td>
                        <select name="opr_codigo" id="opr_codigo" class="combo_normal">
						  <option value="">Selecione um Publisher</option>
						  <?php while ($pgopr = pg_fetch_array ($resopr)) { ?>
						  <option value="<?php if(isset($pgopr['opr_codigo'])) echo $pgopr['opr_codigo'] ?>" <?php if(isset($pgopr['opr_codigo']) && isset($opr_codigo) && $pgopr['opr_codigo'] == $opr_codigo) echo "selected" ?>><?php echo $pgopr['opr_nome']." (".$pgopr['opr_codigo'].")" ?></option>
						  <?php } ?>
						</select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Início da Promoção: </td>
                    <td>
						<input name="promo_data_inicio" type="text" class="form" id="promo_data_inicio" size="11" maxlength="10" value="<?php if(isset($promo_data_inicio)) echo $promo_data_inicio; ?>">
						<a href="#"><img src="/images/cal.gif" width="16" height="16" alt="Calend&aacute;rio" onClick="popUpCalendar(this, frmPreCadastro.promo_data_inicio, 'dd/mm/yyyy')" border="0" align="absmiddle"></a>
					</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Final da Promoção: </td>
                    <td>
						<input name="promo_data_fim" type="text" class="form" id="promo_data_fim" size="11" maxlength="10" value="<?php if(isset($promo_data_fim)) echo $promo_data_fim; ?>">
						<a href="#"><img src="/images/cal.gif" width="16" height="16" alt="Calend&aacute;rio" onClick="popUpCalendar(this, frmPreCadastro.promo_data_fim, 'dd/mm/yyyy')" border="0" align="absmiddle"></a>
					</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Descrição da Promoção:</td>
                    <td>
						<textarea name="promo_descricao" id="promo_descricao" cols="80" rows="5"><?php if(isset($promo_descricao)) echo trim($promo_descricao); ?></textarea></td>
					</td>
                </tr>
                <tr>
                    <td>* <?php if(!empty($promo_banner)) { echo "Alterar o Banner"; } else { echo "Upload do Banner"; } ?>:</td>
                    <td>
						<input type="file" name="promo_banner" id="promo_banner" size="50" />&nbsp; Formatos Permitidos (<?php
						for($i=0;$i<count($formatos);$i++){
							if ($i==0)
								echo $formatos[$i];
							else echo ", ".$formatos[$i];
						}
						?>)
					</td>
                    <td>&nbsp;</td>
                </tr>
				<?php
				if(!empty($promo_banner)) {
					 $pasta = "https://".$_SERVER['SERVER_NAME']."/images/promocoes/";
				?>
				<tr>
                	<td> Imagem do Banner Atual:</td>
                    <td>
						<img src="<?php echo $pasta.$promo_banner;?>" alt="Banner desta Promoção" border="0" align="absmiddle" />
					</td>
                </tr>
                <?php
				}
				?>
				<tr>
                    <td> Label do Banner: </td>
                    <td>
                        <input name="promo_label_banner" type="text" id="promo_label_banner" size="80" maxlength="256" value='<?php if(isset($promo_label_banner)) echo $promo_label_banner; ?>' />
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td> Valor para Exibição: </td>
                    <td>
                        <input name="promo_valor" type="text" id="promo_valor" size="10" maxlength="10" value="<?php if(isset($promo_valor)) echo $promo_valor; ?>" onBlur="isTipo(this.value);"/> - Valor Inteiro usado como par&acirc;metro para exibição da promoção. N&atilde;o utilizar Separadores.
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td> Pergunta da Promoção: </td>
                    <td>
                        <input name="promo_pergunta" type="text" id="promo_pergunta" size="80" maxlength="256" value="<?php if(isset($promo_pergunta)) echo $promo_pergunta; ?>" />
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td> Resposta da Promoção: </td>
                    <td>
                        <input name="promo_resposta" type="text" id="promo_resposta" size="40" maxlength="40" value="<?php if(isset($promo_resposta)) echo $promo_resposta; ?>" />
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td>* <?php if(!empty($promo_banner_resposta)) { echo "Alterar a Imagem da P&aacute;gina"; } else { echo "Upload da Imagem da P&aacute;gina"; } ?>:</td>
                    <td>
						<input type="file" name="promo_banner_resposta" id="promo_banner_resposta" size="50" />&nbsp; Formatos Permitidos (<?php
						for($i=0;$i<count($formatos);$i++){
							if ($i==0)
								echo $formatos[$i];
							else echo ", ".$formatos[$i];
						}
						?>)
					</td>
                    <td>&nbsp;</td>
                </tr>
				<?php
				if(!empty($promo_banner_resposta)) {
					 $pasta = "https://".$_SERVER['SERVER_NAME']."/images/promocoes/";
				?>
				<tr>
                	<td> Imagem da P&aacute;gina Atual:</td>
                    <td>
						<img src="<?php echo $pasta.$promo_banner_resposta;?>" alt="Banner Resposta desta Promoção" border="0" align="absmiddle" />
					</td>
                </tr>
                <?php
				}
				?>
				<tr>
                    <td> Link de Redirecionamento: </td>
                    <td>
                        <input name="promo_link_redir" type="text" id="promo_link_redir" size="80" maxlength="256" value="<?php if(isset($promo_link_redir)) echo $promo_link_redir; ?>" />
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td> Ativo: </td>
                    <td>
                        <input name="promo_ativo" type="checkbox" id="promo_ativo" value="1" <?php if(isset($promo_ativo) && $promo_ativo == "1") echo "checked" ?>/> - Promoção ativa.
                    </td>
                    <td>&nbsp;</td>
                </tr>
			</table>
        </fieldset>
    <br>
    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" style="font-size:10px">
	<tr>
		<td colspan="3">&nbsp;* Campos Obrigat&oacute;rios</td>
	</tr>
	<tr>
		<td colspan="3" align="center"><input type="submit" name="Submit" value="<?php if($acao == 'atualizar') echo "Atualizar"; else echo"Cadastrar"; ?>"/></td>
	</tr>
	</table>
</form>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>