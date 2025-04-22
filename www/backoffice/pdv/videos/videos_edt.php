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
<script type="text/javascript">
function validaUsuario()
{
	if (document.frmPreCadastro.dv_descricao_update.value == "")
    {
        alert("Favor informar o Descrição do Vídeo.");
        document.frmPreCadastro.dv_descricao_update.focus();
        return false;
    }
    if (document.frmPreCadastro.dv_url.value == "")
    {
        alert("Favor informar a URL do Vídeo.");
        document.frmPreCadastro.dv_url.focus();
        return false;
    }
    if (document.frmPreCadastro.dv_data_inicio.value == "")
    {
        alert("Favor informar a Data Início da Vigência do Vídeo.");
        document.frmPreCadastro.dv_data_inicio.focus();
        return false;
    }
    if (document.frmPreCadastro.dv_data_fim.value == "")
    {
        alert("Favor informar a Data Fim da Vigência do Vídeo.");
        document.frmPreCadastro.dv_data_fim.focus();
        return false;
    }
	return true;
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
        <li><a href="index_videos.php">Listagem</a></li>
        <li class="active"><?php echo ($acao == 'atualizar') ? "Edição da" :  "Cadastro de"; ?> Vídeos LAN Houses</li>
    </ol>
</div>
<div class="lstDado"></div>
<div id="msg" name="msg" class="lstDado"></div>
<div class="col-md-12">
<font face="Verdana,Arial" size="2">
    <form method="post" enctype="multipart/form-data" action="" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" value="<?php if(isset($acao)) echo $acao; ?>" />
    <input type="hidden" name="dv_id_update" value="<?php if(isset($acao)) echo (isset($dv_id)) ? $dv_id : ""; ?>" />
        <fieldset>
            <legend>V&iacute;deos LAN Houses</legend>
            <table width="90%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
                <tr>
                    <td>* Descri&ccedil;&atilde;o: </td>
                    <td>
                        <input name="dv_descricao_update" type="text" id="dv_descricao_update" size="80" maxlength="256" value="<?php if(isset($dv_descricao)) echo $dv_descricao; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
				<tr>
                    <td>* URL: </td>
					<td>
                        <input name="dv_url" type="text" id="dv_url" size="80" maxlength="256" value="<?php if(isset($dv_url)) echo $dv_url; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* In&iacute;cio da Vig&ecirc;ncia: </td>
                    <td>
						<input name="dv_data_inicio" type="text" class="form" id="dv_data_inicio" size="11" maxlength="10" value="<?php if(isset($dv_data_inicio)) echo $dv_data_inicio; ?>">
						<a href="#"><img src="images/cal.gif" width="16" height="16" alt="Calend&aacute;rio" onClick="popUpCalendar(this, frmPreCadastro.dv_data_inicio, 'dd/mm/yyyy')" border="0" align="absmiddle"></a>
					</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Final da Vig&ecirc;ncia: </td>
                    <td>
						<input name="dv_data_fim" type="text" class="form" id="dv_data_fim" size="11" maxlength="10" value="<?php if(isset($dv_data_fim)) echo $dv_data_fim; ?>">
						<a href="#"><img src="images/cal.gif" width="16" height="16" alt="Calend&aacute;rio" onClick="popUpCalendar(this, frmPreCadastro.dv_data_fim, 'dd/mm/yyyy')" border="0" align="absmiddle"></a>
					</td>
                    <td>&nbsp;</td>
                </tr>

                <tr>
                    <td> Ativo: </td>
                    <td>
                        <input name="dv_ativo" type="checkbox" id="dv_ativo" value="1" <?php if(isset($dv_ativo) && $dv_ativo=='1') echo "checked"; ?>/> Sim
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
        <td colspan="3" align="center"><input type="submit" class="btn btn-sm btn-info" name="Submit" value="<?php if($acao == 'atualizar') echo "Atualizar"; else echo"Cadastrar"; ?>"/></td>
	</tr>
	</table>
</form>
</font>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; 
?>