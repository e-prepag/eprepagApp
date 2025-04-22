<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
if($acao == 'novo')
{
    $acao = 'inserir';
}
else
{
    $acao = 'atualizar';
}


?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<!--<script type="text/javascript" src="js/jquery.ui.nestedSortable.js"></script>-->
<script>
    $(function(){
        $("#quest_data_inicio").datepicker();
        $("#quest_data_fim").datepicker();
    });
        
function validaUsuario()
{
	if (document.frmPreCadastro.quest_nome_update.value == "")
    {
        alert("Favor informar o Nome do Questionário.");
        document.frmPreCadastro.quest_nome_update.focus();
        return false;
    }
    if (document.frmPreCadastro.quest_data_inicio.value == "")
    {
        alert("Favor informar a Data Início da Vigência do Questionário.");
        document.frmPreCadastro.quest_data_inicio.focus();
        return false;
    }
    if (document.frmPreCadastro.quest_data_fim.value == "")
    {
        alert("Favor informar a Data Fim da Vigência do Questionário.");
        document.frmPreCadastro.quest_data_fim.focus();
        return false;
    }
    if (document.frmPreCadastro.quest_tipo.value == "")
    {
        alert("Favor informar o Tipo do Questionário.");
        document.frmPreCadastro.quest_tipo.focus();
        return false;
    }
    if ((document.frmPreCadastro.quest_ids_inclusao.value == "")&&(document.frmPreCadastro.quest_ids_exclusao.value == ""))
    {
        alert("Favor informar IDs de exclusão ou inclusão.");
        document.frmPreCadastro.quest_ids_inclusao.focus();
        return false;
    }
	if ((document.frmPreCadastro.quest_ids_inclusao.value.length > 0)&&(document.frmPreCadastro.quest_ids_exclusao.value.length > 0))
    {
        alert("Favor informar somente IDs de exclusão ou inclusão,\nnunca os dois.");
        document.frmPreCadastro.quest_ids_inclusao.focus();
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

<?php
if ($acao == 'atualizar') {
?>
//funcao que adiciona linha de Pergunta
function MM_load(){
        //alert('Teste');
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/ajaxPerguntas.php",
				data: "quest_id_update=<?php echo $quest_id;?>&acao=<?php if($acao == 'inserir') echo 'novo'; else echo 'editar';?>",
				success: function(html){
                    html = html.replace("<script type=\"text/javascript\" src=\"/js/jquery-1.5.2.min.js\"><\/script>","");
                    html = html.replace("<script type=\"text/javascript\" src=\"/js/jquery-ui-1.8.11.custom.min.js\"><\/script>","");
//                    html = html.replace("<script type=\"text/javascript\" src=\"js/jquery.ui.nestedSortable.js\"><\/script>","");
					$('#mostraPerguntas').html(html);
				},
				error: function(){
					alert('Erro Valor');
				}
			});
		});
}
//CARREGANDO O QUE JÁ ESTÁ CADASTRADO
MM_load();
<?php
}
?>
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php if($acao == 'atualizar') echo "Edi&ccedil;&atilde;o"; else echo"Cadastro"; ?></a></li>
    </ol>
</div>
<div id="msg" name="msg" class="lstDado"></div>
<div class="col-md-12 txt-preto fontsize-pp">
    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" id="acao" value="<?php echo $acao; ?>" />
    <input type="hidden" name="quest_id_update" id="quest_id_update" value="<?php echo $quest_id; ?>" />
        <fieldset>
            <legend>Question&aacute;rio</legend>
            <table class="table txt-preto fontsize-pp">
                <tr>
                    <td>* Nome / T&iacute;tulo: </td>
					<td>
                        <input name="quest_nome_update" type="text" id="quest_nome_update" size="80" maxlength="256" value="<?php if(isset($quest_nome)) echo $quest_nome; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* In&iacute;cio do Question&aacute;rio: </td>
                    <td>
						<input name="quest_data_inicio" type="text" class="form" id="quest_data_inicio" size="11" maxlength="10" value="<?php if(isset($quest_data_inicio)) echo $quest_data_inicio; ?>">
					</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Final do Question&aacute;rio: </td>
                    <td>
						<input name="quest_data_fim" type="text" class="form" id="quest_data_fim" size="11" maxlength="10" value="<?php if(isset($quest_data_fim)) echo $quest_data_fim; ?>">
					</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Tipo de Question&aacute;rio:</td>
                    <td>
						 <select name="quest_tipo" id="quest_tipo" class="combo_normal">
						  <option value="">Selecione um Tipo de Exibi&ccedil;&atilde;o</option>
						  <?php foreach ($tipos as $key => $value) { ?>
						  <option value="<?php echo $key ?>" <?php if(isset($quest_tipo) && $key == $quest_tipo) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
						  <?php } ?>
						</select>
					</td>
					<td>&nbsp;</td>
                </tr>
                <tr>
                    <td> IDs que devem responder:</td>
					<td>
                        <input name="quest_ids_inclusao" type="text" id="quest_ids_inclusao" size="80" value="<?php if(isset($quest_ids_inclusao)) echo $quest_ids_inclusao; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><nobr> IDs que N&Atilde;O devem responder:</nobr></td>
					<td>
                        <input name="quest_ids_exclusao" type="text" id="quest_ids_exclusao" size="80" value="<?php if(isset($quest_ids_exclusao)) echo $quest_ids_exclusao; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<?php
				if(!empty($quest_usuario_bko)) {
				?>
				<tr>
                	<td> Usu&aacute;rio Respons&aacute;vel:</td>
                    <td>
                        <?php echo $quest_usuario_bko; ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <?php
				}
				?>
                <tr>
                    <td> <?php if(!empty($quest_banner)) { echo "Alterar o Banner"; } else { echo "Upload do Banner"; } ?>:</td>
                    <td>
						<input type="file" name="quest_banner" id="quest_banner" size="50" />&nbsp; Formatos Permitidos (<?php
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
				if(!empty($quest_banner)) {
					 $pasta = $url . "/imagens/questionario/";
				?>
				<tr>
                	<td> Imagem do Banner Atual:</td>
                    <td>
						<img src="<?php echo $pasta.$quest_banner;?>" alt="Banner desta question&aacute;rio" border="0" align="absmiddle" />
					</td>
                </tr>
                <?php
				}
				?>
				<tr>
                    <td>* Tipo de Usu&aacute;rios:</td>
                    <td>
						 <select name="quest_tipo_usuario" id="quest_tipo_usuario" class="combo_normal">
						  <?php foreach ($tipos_usuarios as $key => $value) { ?>
						  <option value="<?php echo $key ?>" <?php if(isset($quest_tipo_usuario) && $key == $quest_tipo_usuario) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
						  <?php } ?>
						</select>
					</td>
					<td>&nbsp;</td>
                </tr>
                <tr>
                    <td> Ativo: </td>
                    <td>
                        <input name="quest_ativo" type="checkbox" id="quest_ativo" value="1" <?php if(isset($quest_ativo) && $quest_ativo == "1") echo "checked" ?>/> - Question&aacute;rio.
                    </td>
                    <td>&nbsp;</td>
                </tr>
			</table>
        </fieldset>
    <br>
    <?php
	if ($acao == 'atualizar') {
	?>
	<!--Div Box que altera a pergunta -->
	<div id="teste"></div>
	<div id="boxPopUpAlterar"></div>
	<fieldset>
            <legend>Perguntas</legend>
			<div id='mostraPerguntas'>
			</div>
    </fieldset>
    <?php
	}
	?>
	<table class="table txt-preto fontsize-pp">
	<tr>
		<td colspan="3">&nbsp;* Campos Obrigat&oacute;rios</td>
	</tr>
	<tr>
        <td colspan="3" align="center"><input type="submit" name="Submit" class="btn btn-sm btn-info" id="Submit" value="<?php if($acao == 'atualizar') echo "Atualizar"; else echo"Cadastrar"; ?>"/></td>
	</tr>
	</table>
</form>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>