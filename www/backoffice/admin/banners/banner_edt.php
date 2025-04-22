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


?>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $server_url_ep; ?>/js/global.js"></script>
<!--<script type="text/javascript" src="js/jquery.ui.nestedSortable.js"></script>-->
<script>
    $(function(){
        $("#bds_data_inicio").datepicker();
        $("#bds_data_fim").datepicker();
    });

function validaUsuario()
{
	if (document.frmPreCadastro.bds_nome_update.value == "")
    {
        alert("Favor informar o Nome do Banner Drop Shadow.");
        document.frmPreCadastro.bds_nome_update.focus();
        return false;
    }
    if (document.frmPreCadastro.bds_data_inicio.value == "")
    {
        alert("Favor informar a Data Início da Vigência do Banner Drop Shadow.");
        document.frmPreCadastro.bds_data_inicio.focus();
        return false;
    }
    if (document.frmPreCadastro.bds_data_fim.value == "")
    {
        alert("Favor informar a Data Fim da Vigência do Banner Drop Shadow.");
        document.frmPreCadastro.bds_data_fim.focus();
        return false;
    }
    if (document.frmPreCadastro.bds_tipo.value == "")
    {
        alert("Favor informar o Tipo do Banner Drop Shadow.");
        document.frmPreCadastro.bds_tipo.focus();
        return false;
    }
    if ((document.frmPreCadastro.bds_ids_inclusao.value == "")&&(document.frmPreCadastro.bds_ids_exclusao.value == ""))
    {
        alert("Favor informar IDs de exclusão ou inclusão.");
        document.frmPreCadastro.bds_ids_inclusao.focus();
        return false;
    }
	if ((document.frmPreCadastro.bds_ids_inclusao.value.length > 0)&&(document.frmPreCadastro.bds_ids_exclusao.value.length > 0))
    {
        alert("Favor informar somente IDs de exclusão ou inclusão,\nnunca os dois.");
        document.frmPreCadastro.bds_ids_inclusao.focus();
        return false;
    }
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
        <li class="active"><a href="index_banner.php"><?php echo $sistema->menu[0]->getDescricao(); ?></a></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php if($acao == 'atualizar') echo "Edi&ccedil;&atilde;o"; else echo"Cadastro"; ?></a></li>
    </ol>
</div>
<div id="msg" name="msg" class="lstDado"></div>
<div class="col-md-12">
<font face="Verdana,Arial" size="2">
    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" id="acao" value="<?php if(isset($acao)) echo $acao; ?>" />
    <input type="hidden" name="bds_id_update" id="bds_id_update" value="<?php if(isset($bds_id)) echo $bds_id; ?>" />
        <fieldset>
            <legend>Banner Drop Shadow</legend>
            <table width="90%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
                <tr>
                    <td>* Nome / T&iacute;tulo: </td>
					<td>
                        <input name="bds_nome_update" type="text" id="bds_nome_update" size="80" maxlength="256" value="<?php if(isset($bds_nome)) echo $bds_nome; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* In&iacute;cio do Banner Drop Shadow: </td>
                    <td>
						<input name="bds_data_inicio" type="text" class="form" id="bds_data_inicio" size="11" maxlength="10" value="<?php if(isset($bds_data_inicio)) echo $bds_data_inicio; ?>">
					</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Final do Banner Drop Shadow: </td>
                    <td>
						<input name="bds_data_fim" type="text" class="form" id="bds_data_fim" size="11" maxlength="10" value="<?php if(isset($bds_data_fim)) echo $bds_data_fim; ?>">
					</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Tipo de Banner Drop Shadow:</td>
                    <td>
						 <select name="bds_tipo" id="bds_tipo" class="combo_normal">
						  <option value="">Selecione um Tipo de Exibi&ccedil;&atilde;o</option>
						  <?php foreach ($tipos as $key => $value) { ?>
						  <option value="<?php echo $key ?>" <?php if(isset($bds_tipo) && $key == $bds_tipo) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
						  <?php } ?>
						</select>
					</td>
					<td>&nbsp;</td>
                </tr>
                <tr>
                    <td> IDs que devem visualizar:</td>
					<td>
                        <input name="bds_ids_inclusao" type="text" id="bds_ids_inclusao" size="80" value="<?php if(isset($bds_ids_inclusao)) echo $bds_ids_inclusao; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><nobr> IDs que N&Atilde;O devem visualizar:</nobr></td>
					<td>
                        <input name="bds_ids_exclusao" type="text" id="bds_ids_exclusao" size="80" value="<?php if(isset($bds_ids_exclusao)) echo $bds_ids_exclusao; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<?php
				if(!empty($bds_usuario_bko)) {
				?>
				<tr>
                	<td> Usu&aacute;rio Respons&aacute;vel:</td>
                    <td>
                        <?php echo $bds_usuario_bko; ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <?php
				}
				?>
                <tr>
                    <td> <?php if(!empty($bds_banner)) { echo "Alterar o Banner"; } else { echo "Upload do Banner"; } ?>:</td>
                    <td>
						<input type="file" name="bds_banner" id="bds_banner" size="50" />&nbsp; Formatos Permitidos (<?php
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
				if(!empty($bds_banner)) {
                    $pasta = $url."/imagens/banners/";
				?>
				<tr>
                	<td> Imagem do Banner Atual:</td>
                    <td>
						<img src="<?php echo $pasta.$bds_banner;?>" alt="Banner desta Banner Drop Shadow" border="0" align="absmiddle" />
					</td>
                </tr>
                <?php
				}
				?>
				<tr>
                    <td>Link: </td>
					<td>
                        <input name="bds_link" type="text" id="bds_link" size="80" maxlength="256" value="<?php if(isset($bds_link)) echo $bds_link; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td>* Tipo de Usu&aacute;rios:</td>
                    <td>
						 <select name="bds_tipo_usuario" id="bds_tipo_usuario" class="combo_normal">
						  <?php foreach ($tipos_usuarios as $key => $value) { ?>
						  <option value="<?php echo $key ?>" <?php if(isset($bds_tipo_usuario) && $key == $bds_tipo_usuario) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
						  <?php } ?>
						</select>
					</td>
					<td>&nbsp;</td>
                </tr>
                <tr>
                    <td> Ativo: </td>
                    <td>
                        <input name="bds_ativo" type="checkbox" id="bds_ativo" value="1" <?php if(isset($bds_ativo) && $bds_ativo == "1") echo "checked" ?>/> - Banner Drop Shadow.
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
        <td colspan="3" align="center"><input type="submit" name="Submit" class="btn btn-sm btn-info" id="Submit" value="<?php if($acao == 'atualizar') echo "Atualizar"; else echo"Cadastrar"; ?>"/></td>
	</tr>
	</table>
</form>
</font>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>