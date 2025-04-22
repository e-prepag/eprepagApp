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
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<script type="text/javascript">
function validaUsuario()
{
	if (document.frmPreCadastro.te_descricao_update.value == "")
    {
        alert("Favor informar o Descrição da Tipo de Estabelecimento.");
        document.frmPreCadastro.te_descricao_update.focus();
        return false;
    }
    return true;
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
        <li><a href="index_tipo_estabelecimento.php">Listagem</a></li>
        <li class="active"><?php if($acao == 'atualizar') echo "Edi&ccedil;&atilde;o"; else echo"Cadastro"; ?></li>
    </ol>
</div>
<div class="lstDado"></div>
<div id="msg" name="msg" class="lstDado">
</div>
<div class="col-md-12 txt-preto">
    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" value="<?php echo $acao; ?>" />
    <input type="hidden" name="te_id_update" value="<?php echo (isset($te_id)) ? $te_id : ""; ?>" />
        <fieldset>
            <legend>Tipo de Estabelecimento LAN Houses</legend>
            <div class="form-group">
                <label for="te_descricao_update">* Descri&ccedil;&atilde;o:</label>
                <input name="te_descricao_update" type="text" id="te_descricao_update" size="80" maxlength="256" value="<?php if(isset($te_descricao)) echo $te_descricao; ?>"/>
            </div>
            <div class="checkbox">
                <label><input name="te_ativo" type="checkbox" id="te_ativo" value="1" <?php if(isset($te_ativo) && $te_ativo=='1') echo "checked"; ?>/>Ativo?</label>
            </div>
        </fieldset>
    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" style="font-size:10px">
	<tr>
		<td colspan="3">&nbsp;* Campos Obrigat&oacute;rios</td>
	</tr>
	<tr>
        <td colspan="3" align="center"><input type="submit" name="Submit" class="btn btn-info" value="<?php if($acao == 'atualizar') echo "Atualizar"; else echo"Cadastrar"; ?>"/></td>
	</tr>
	</table>
</form>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>