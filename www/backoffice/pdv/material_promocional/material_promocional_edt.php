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
<script type="text/javascript">

function validaUsuario()
{
    if (document.frmPreCadastro.mat_promo_nome_update.value == "")
    {
        alert("Favor informar o Nome do Material Promocional.");
        document.frmPreCadastro.mat_promo_nome_update.focus();
        return false;
    }
    if (document.frmPreCadastro.mat_promo_ordem.value == "")
    {
        alert("Favor informar a Ordem Tipo do Material Promocional.");
        document.frmPreCadastro.mat_promo_ordem.focus();
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
<div class="lstDado"><a href="/index.php">BackOffice</a> > <a href="index.php">Materiais Promocionais</a> > <?php if($acao == 'atualizar') echo "Edi&ccedil;&atilde;o"; else echo"Cadastro"; ?></div>
<div id="msg" name="msg" class="lstDado">
</div>
<div>
<font face="Verdana,Arial" size="2">
	<table width="100%" border="0">
		<tr>
			<td bgcolor="#00008C"><font face="Verdana,Arial" size="3" color="#FFFFFF"><b>&nbsp;&nbsp;<?php if($acao == 'atualizar') echo "Edi&ccedil;&atilde;o da"; else echo"Cadastro de"; ?> Material Promocional</b></font></td>
		</tr>
	</table>
	<br>
    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" id="acao" value="<?php echo $acao; ?>" />
    <input type="hidden" name="mat_promo_id_update" id="mat_promo_id_update" value="<?php echo $mat_promo_id; ?>" />
        <fieldset>
            <legend>Material Promocional</legend>
            <table width="90%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
                <tr>
                    <td>* Nome / T&iacute;tulo: </td>
					<td>
                        <input name="mat_promo_nome_update" type="text" id="mat_promo_nome_update" size="80" maxlength="256" value="<?php echo $mat_promo_nome; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td> IDs que devem acessar:</td>
					<td>
                        <input name="mat_promo_ids_inclusao" type="text" id="mat_promo_ids_inclusao" size="80" value="<?php echo $mat_promo_ids_inclusao; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td> <?php if(!empty($mat_promo_banner)) { echo " * Alterar o Banner"; } else { echo " * Upload do Banner"; } ?>:</td>
                    <td>
						<input type="file" name="mat_promo_banner" id="mat_promo_banner" size="50" />&nbsp; Formatos Permitidos (<?php
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
                if(!empty($mat_promo_banner)) {
                         $pasta = $raiz_do_projeto."public_html/imagens/pdv/material_promocional/";
                ?>
                <tr>
                	<td> Imagem do Banner Atual:</td>
                    <td>
						<img src="<?php echo $pasta.$mat_promo_banner;?>" alt="Banner desta question&aacute;rio" border="0" align="absmiddle" />
					</td>
                </tr>
                <?php
		}
		?>
                <tr>
                    <td> * Ordenação de exibição: </td>
					<td>
                        <input name="mat_promo_ordem" type="text" id="mat_promo_ordem" size="2" maxlength="2" value="<?php echo $mat_promo_ordem; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td> Wallpapers: </td>
					<td>
                        <input name="mat_promo_wallpapers" type="text" id="mat_promo_wallpapers" size="80" maxlength="256" value="<?php echo $mat_promo_wallpapers; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td> Cartaz: </td>
					<td>
                        <input name="mat_promo_cartaz" type="text" id="mat_promo_cartaz" size="80" maxlength="256" value="<?php echo $mat_promo_cartaz; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td> Torneios: </td>
					<td>
                        <input name="mat_promo_torneios" type="text" id="mat_promo_torneios" size="80" maxlength="256" value="<?php echo $mat_promo_torneios; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td> Detalhes: </td>
					<td>
                        <input name="mat_promo_detalhes" type="text" id="mat_promo_detalhes" size="80" maxlength="256" value="<?php echo $mat_promo_detalhes; ?>"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td> Ativo: </td>
                    <td>
                        <input name="mat_promo_ativo" type="checkbox" id="mat_promo_ativo" value="1" <?php if($mat_promo_ativo == "1") echo "checked" ?>/> - Material Promocional.
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
		<td colspan="3" align="center"><input type="submit" name="Submit" id="Submit" value="<?php if($acao == 'atualizar') echo "Atualizar"; else echo"Cadastrar"; ?>"/></td>
	</tr>
	</table>
</form>
</font>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>