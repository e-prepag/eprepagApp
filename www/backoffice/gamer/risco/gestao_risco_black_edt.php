<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
if(b_IsBKOUsuarioAdminGestaodeRisco()){

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
<script type="text/javascript">
function validaUsuario()
{
	if (document.frmPreCadastro.ugbl_status.value == "")
    {
        alert("Favor informar o Status.");
        document.frmPreCadastro.ugbl_status.focus();
        return false;
    }
    if (document.frmPreCadastro.ug_id.value == "")
    {
        alert("Favor informar o ID do Usuário.");
        document.frmPreCadastro.ug_id.focus();
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
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12">
<font face="Verdana,Arial" size="2">
    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" value="<?php echo $acao; ?>" />
        <fieldset>
            <legend>Gestão de Risco Black List</legend>
            <table width="90%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
				<?php
				if($acao == 'atualizar') {
				?>
                <input type="hidden" name="ug_id" id="ug_id" value="<?php echo $ug_id; ?>" />
				<tr>
                    <td>* Status: </td>
                    <td>
						<select name="ugbl_status" id="ugbl_status" class="combo_normal">
							<?php foreach ($ativacao as $key => $value) { ?>
							<option value="<?php echo $key ?>" <?php if($key == $ugbl_status) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
							<?php } ?>
						</select>
					</td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td>ID do Usu&aacute;rio: </td>
                    <td>
                        <?php echo $ug_id; ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td>Data da Atualiza&ccedil;&atilde;o:</td>
                    <td>
                        <?php echo $ugbl_data_ultima_alteracao; ?>
                    </td>
                </tr>
                <tr>
                    <td>Quem alterou:</td>
                    <td>
						<?php echo $shn_login; ?>
                    </td>
                </tr>
				<?php
				}//end if($acao == 'atualizar')
				else {
				?>
			    <tr>
                    <td>* ID do Usu&aacute;rio: </td>
                    <td>
                        <input name="ug_id" type="text" id="ug_id" size="20" maxlength="256" value="<?php echo $ug_id; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<?php
				}//end else  if($acao == 'atualizar')
				?>
			</table>
        </fieldset>
    <br>
    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" style="font-size:10px">
	<tr>
		<td colspan="3">&nbsp;* Campos Obrigat&oacute;rios</td>
	</tr>
	<tr>
        <td colspan="3" align="center"><input type="submit" class="btn btn-info btn-sm" name="Submit" value="<?php if($acao == 'atualizar') echo "Atualizar"; else echo"Cadastrar"; ?>"/></td>
	</tr>
	</table>
</form>
</font>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
}//end if(b_IsBKOUsuarioAdminGestaodeRisco())
?>