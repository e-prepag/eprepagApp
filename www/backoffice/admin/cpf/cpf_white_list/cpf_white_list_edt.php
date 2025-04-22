<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
require_once "/www/includes/bourls.php";
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
<script type="text/javascript" src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery.mask.min.js"></script>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript">
$("#cpf").mask("999.999.999-99");

function validaUsuario() {
    if (document.frmPreCadastro.cpf.value == "")
    {
        alert("Favor informar o CPF à ser Liberado.");
        document.frmPreCadastro.cpf.focus();
        return false;
    }
    else if(validaRespostaCPF(document.frmPreCadastro.cpf.value))  {
            return true;
    }
    else {
            alert("CPF inválido.");
            document.frmPreCadastro.cpf.focus();
            return false;
    }
}

function validaRespostaCPF(cpf) {
    cpf = cpf.replace(/[^\d]+/g,'');
    if(cpf == '') return false;

    // Elimina CPFs invalidos conhecidos
    if (cpf.length != 11 ||
            cpf == '00000000000' ||
            cpf == '11111111111' ||
            cpf == '22222222222' ||
            cpf == '33333333333' ||
            cpf == '44444444444' ||
            cpf == '55555555555' ||
            cpf == '66666666666' ||
            cpf == '77777777777' ||
            cpf == '88888888888' ||
            cpf == '99999999999')
            return false;

    // Valida 1o digito
    add = 0;
    for (i=0; i < 9; i ++)
            add += parseInt(cpf.charAt(i)) * (10 - i);
    rev = 11 - (add % 11);
    if (rev == 10 || rev == 11)
            rev = 0;
    if (rev != parseInt(cpf.charAt(9)))
            return false;

    // Valida 2o digito
    add = 0;
    for (i = 0; i < 10; i ++)
            add += parseInt(cpf.charAt(i)) * (11 - i);
    rev = 11 - (add % 11);
    if (rev == 10 || rev == 11)
            rev = 0;
    if (rev != parseInt(cpf.charAt(10)))
            return false;

    return true;
}//end validaRespostaCPF()
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
            <legend>Lista de Permitidos</legend>
            <table width="90%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
                <tr>
                    <td align="right">* CPF à ser Liberado: &nbsp;</td>
                    <td>
                        <input name="cpf" type="text" id="cpf" size="20" maxlength="14" value="<?php echo $cpf; ?>" placeholder="CPF"/>
                    </td>
					<td align="right">* Justificativa: &nbsp;</td>
					<td>
						 <textarea name="desc" type="text" id="desc" value="<?php echo $desc; ?>" placeholder="Justificativa" rows = "5" ></textarea>
				     </td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </fieldset>
    <br>
    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" style="font-size:10px">
	<tr>
		<td colspan="3" align="center">* Campo Obrigatório</td>
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
?>