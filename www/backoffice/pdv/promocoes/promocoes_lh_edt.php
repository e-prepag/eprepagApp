<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";

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

if(!isset($opr_codigo)) $opr_codigo = null;
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
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
</script>
<script type="text/javascript">
function validaUsuario()
{
	if (document.frmPreCadastro.promolh_descricao.value == "")
    {
        alert("Favor informar o Descrição da Promoção.");
        document.frmPreCadastro.promolh_descricao.focus();
        return false;
    }
    if (document.frmPreCadastro.opr_codigo.value == "")
    {
        alert("Favor informar o Publisher.");
        document.frmPreCadastro.opr_codigo.focus();
        return false;
    }
    if (document.frmPreCadastro.promolh_data_inicio.value == "")
    {
        alert("Favor informar a Data Início da Vigência da Promoção.");
        document.frmPreCadastro.promolh_data_inicio.focus();
        return false;
    }
    if (document.frmPreCadastro.promolh_data_fim.value == "")
    {
        alert("Favor informar a Data Fim da Vigência da Promoção.");
        document.frmPreCadastro.promolh_data_fim.focus();
        return false;
    }
    if (document.frmPreCadastro.promolh_titulo_tabela.value == "")
    {
        alert("Favor informar a Descrição do Título do Ranking.");
        document.frmPreCadastro.promolh_titulo_tabela.focus();
        return false;
    }
    if (document.frmPreCadastro.promolh_regulamento.value == "")
    {
        alert("Favor informar o Regulamento da Promoção.");
        document.frmPreCadastro.promolh_regulamento.focus();
        return false;
    }
    if (document.frmPreCadastro.promolh_link_download.value == "")
    {
        alert("Favor informar o Link para Download.");
        document.frmPreCadastro.promolh_link_download.focus();
        return false;
    }
    <?php
    if(empty($promolh_banner)) {
    ?>
        if (document.frmPreCadastro.promolh_banner.value == "")
        {
            alert("Favor informar o Banner da Promoção.");
            document.frmPreCadastro.promolh_banner.focus();
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
<div class="lstDado"><a href="/index.php">BackOffice</a> > <a href="index_promocoes_lh.php">Promo&ccedil;&otilde;es LAN Houses</a> > <?php if($acao == 'atualizar') echo "Edi&ccedil;&atilde;o"; else echo"Cadastro"; ?></div>
<div id="msg" name="msg" class="lstDado">
</div>
<div>
<font face="Verdana,Arial" size="2">
	<table width="100%" border="0">
		<tr>
			<td bgcolor="#00008C"><font face="Verdana,Arial" size="3" color="#FFFFFF"><b>&nbsp;&nbsp;<?php if($acao == 'atualizar') echo "Edi&ccedil;&atilde;o da"; else echo"Cadastro de"; ?> Promo&ccedil;&atilde;o LAN Houses</b></font></td>
		</tr>
	</table>
	<br>
    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" value="<?php echo $acao; ?>" />
    <input type="hidden" name="promolh_id" value="<?php echo $promolh_id; ?>" />
        <fieldset>
            <legend>Promo&ccedil;&atilde;o LAN Houses</legend>
            <table width="90%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
                <tr>
                    <td>* Descri&ccedil;&atilde;o: </td>
                    <td>
                        <input name="promolh_descricao" type="text" id="promolh_descricao" size="40" maxlength="40" value="<?php if(isset($promolh_descricao)) echo $promolh_descricao; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Publisher: </td>
                    <td>
                        <select name="opr_codigo" id="opr_codigo" class="combo_normal">
						  <option value="">Selecione um Publisher</option>
						  <?php while ($pgopr = pg_fetch_array ($resopr)) { ?>
						  <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if($pgopr['opr_codigo'] == $opr_codigo) echo "selected" ?>><?php echo $pgopr['opr_nome']." (".$pgopr['opr_codigo'].")" ?></option>
						  <?php } ?>
						</select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* In&iacute;cio da Promo&ccedil;&atilde;o: </td>
                    <td>
						<input name="promolh_data_inicio" type="text" class="form" id="promolh_data_inicio" size="11" maxlength="10" value="<?php if(isset($promolh_data_inicio)) echo $promolh_data_inicio; ?>">
						<a href="#"><img src="/images/cal.gif" width="16" height="16" alt="Calend&aacute;rio" onClick="popUpCalendar(this, frmPreCadastro.promolh_data_inicio, 'dd/mm/yyyy')" border="0" align="absmiddle"></a>
					</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Final da Promo&ccedil;&atilde;o: </td>
                    <td>
						<input name="promolh_data_fim" type="text" class="form" id="promolh_data_fim" size="11" maxlength="10" value="<?php if(isset($promolh_data_fim)) echo $promolh_data_fim; ?>">
						<a href="#"><img src="/images/cal.gif" width="16" height="16" alt="Calend&aacute;rio" onClick="popUpCalendar(this, frmPreCadastro.promolh_data_fim, 'dd/mm/yyyy')" border="0" align="absmiddle"></a>
					</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Descri&ccedil;&atilde;o do T&iacute;tulo do Ranking:</td>
                    <td>
                        <input name="promolh_titulo_tabela" type="text" id="promolh_titulo_tabela" size="80" maxlength="80" value="<?php if(isset($promolh_titulo_tabela)) echo $promolh_titulo_tabela; ?>" />
                    </td>
                </tr>
                <tr>
                    <td>* <?php if(!empty($promolh_banner)) { echo "Alterar o Banner"; } else { echo "Upload do Banner"; } ?>:</td>
                    <td>
                        <input type="file" name="promolh_banner" id="promolh_banner" size="50" />&nbsp; Formatos Permitidos (<?php
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
                    if(!empty($promolh_banner)) {
                             $pasta = "https://".$_SERVER['SERVER_NAME']."/imagens/pdv/promocoes/";
                    ?>
                <tr>
                    <td> Imagem do Banner Atual:</td>
                    <td>
                            <img src="<?php echo $pasta.$promolh_banner;?>" alt="Banner desta Promo&ccedil;&atilde;o" border="0" align="absmiddle" />
                    </td>
                </tr>
                <?php 
                    }
                ?>
				<tr>
                    <td>* Regulamento da Promo&ccedil;&atilde;o:</td>
                    <td>
                            <textarea name="promolh_regulamento" id="promolh_regulamento" cols="80" rows="5"><?php if(isset($promolh_regulamento)) echo trim($promolh_regulamento); ?></textarea></td>
                    </td>
                </tr>
                <tr>
                    <td>* Link para Download:</td>
                    <td>
                        <input name="promolh_link_download" type="text" id="promolh_link_download" size="80" maxlength="256" value="<?php  if(isset($promolh_link_download)) echo $promolh_link_download; ?>" />
                    </td>
                </tr>
                <!--tr>
                    <td> Jogo da Promo&ccedil;&atilde;o: </td>
                    <td>
                        <input name="ogp_id" type="text" id="ogp_id" size="40" maxlength="40" value="<?php //echo $ogp_id; ?>" />
                    </td>
                    <td>&nbsp;</td>
                </tr-->
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
</font>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>