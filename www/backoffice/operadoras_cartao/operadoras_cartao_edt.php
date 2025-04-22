<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
require_once $raiz_do_projeto."includes/gamer/constantesPinEpp.php";
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
<script type="text/javascript">
function validaUsuario()
{
    if (document.frmPreCadastro.opr_codigo.value == "")
    {
        alert("Favor informar o Publisher.");
        document.frmPreCadastro.opr_codigo.focus();
        return false;
    }
    else if (document.frmPreCadastro.pcd_id_distribuidor.value == "")
    {
        alert("Favor informar a Distribuidora.");
        document.frmPreCadastro.pcd_id_distribuidor.focus();
        return false;
    }
    else if (document.frmPreCadastro.pcd_comissao.value == "")
    {
        alert("Favor informar a comissão da Distribuidora.");
        document.frmPreCadastro.pcd_comissao.focus();
        return false;
    }
    else if (document.frmPreCadastro.pcd_formato.value == "")
    {
        alert("Favor informar o Formato do PIN Cartão.");
        document.frmPreCadastro.pcd_formato.focus();
        return false;
    }
    else if (document.frmPreCadastro.valor1.value == "") 
    {
        alert("Favor informar o Valor 1 do PIN Cartão.");
        document.frmPreCadastro.valor1.focus();
        return false;
    }
    else if (!isTipo(document.frmPreCadastro.valor1.value))
    {
        document.frmPreCadastro.valor1.focus();
        document.frmPreCadastro.valor1.select();
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

</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="index.php">Distribuidora de Cartões</a></li>
        <li class="active"><?php if($acao == 'atualizar') echo "Edi&ccedil;&atilde;o"; else echo"Cadastro"; ?></li>
    </ol>
</div>
<div class="col-md-12">
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" id="acao" value="<?php echo $acao; ?>" />
        <fieldset>
            <legend>Distribuidora de Cartões</legend>
            <table class="table txt-preto">
                <thead>
                <tr>
                    <td><?php echo (empty($opr_codigo)?"*":"&nbsp;");?>&nbsp;Publisher: </td>
                    <td width='70%'>
                        <?php
                        if(empty($opr_codigo)) {
                        ?>
                        <select id='opr_codigo' name='opr_codigo' style='width: 272px'>
                            <option value='' <?php echo (empty($opr_codigo)?" selected":"") ?>>Selecione</option>
                            <?php
                            $sql = "select opr_codigo, opr_nome from operadoras where opr_emite_cartao_conosco=1 order by opr_nome;";
                            $rs_operadoras = SQLexecuteQuery($sql);
                            while ($rs_operadoras_row = pg_fetch_array($rs_operadoras)) {
                            ?>
                            <option value='<?php echo $rs_operadoras_row['opr_codigo']; ?>' <?php echo ((isset($opr_codigo)) && ($opr_codigo==$rs_operadoras_row['opr_codigo'])?" selected":"") ?>><?php echo $rs_operadoras_row['opr_nome']; ?></option>
                            <?php
                            } //end while
                            ?>
                        </select>
                        <?php
                        }//end  if(empty($opr_codigo))
                        else {
                            $sql = "select opr_codigo, opr_nome from operadoras where opr_emite_cartao_conosco=1 and opr_codigo = ".$opr_codigo." ;";
                            $rs_operadoras = SQLexecuteQuery($sql);
                            if(pg_num_rows($rs_operadoras) == 1) {
                                $rs_operadoras_row = pg_fetch_array($rs_operadoras);
                                echo $rs_operadoras_row['opr_nome'];
                            }//end if(pg_num_rows($rs_operadoras) == 1)
                            else {
                                echo "ATENÇÃO: Nenhum Publisher possui estes ID, ou mais de um Publisher póssui o mesmo ID.";
                            }//end else do if(pg_num_rows($rs_operadoras) == 1)
                        ?>
                            <input name="opr_codigo" type="hidden" id="opr_codigo" value="<?php echo $opr_codigo; ?>"/>
                        <?php
                        }//end else do if(empty($opr_codigo))
                        ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><?php echo (empty($pcd_id_distribuidor)?"*":"&nbsp;");?> Distribuidor: </td>
                    <td>
                        <?php
                        if(empty($pcd_id_distribuidor)) {
                        ?>
                        <select id='pcd_id_distribuidor' name='pcd_id_distribuidor' style='width: 272px'>
                            <option value='' <?php echo (empty($pcd_id_distribuidor)?" selected":"") ?>>Selecione</option>
                            <?php
                            foreach($GLOBALS['DISTRIBUIDORAS_CARTOES'] as $key => $val) {
                            ?>
                            <option value='<?php echo $key; ?>' <?php echo ((isset($pcd_id_distribuidor)) && ($pcd_id_distribuidor==$key)?" selected":"") ?>><?php echo $val; ?></option>
                            <?php
                            } //end foreach
                            ?>
                        </select>
                        <?php
                        }//end  if(empty($pcd_id_distribuidor))
                        else {
                            echo $GLOBALS['DISTRIBUIDORAS_CARTOES'][$pcd_id_distribuidor];
                        ?>
                            <input name="pcd_id_distribuidor" type="hidden" id="pcd_id_distribuidor" value="<?php if(isset($pcd_id_distribuidor)) echo $pcd_id_distribuidor; ?>"/>
                        <?php
                        }//end else do  if(empty($pcd_id_distribuidor))
                        ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Comissão: </td>
                    <td>
                        <input name="pcd_comissao" type="text" id="pcd_comissao" size="40" maxlength="40" value="<?php if(isset($pcd_comissao)) echo $pcd_comissao; ?>"  onBlur="isTipo(this.value);"/>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Formato: </td>
                    <td>
                        <select id='pcd_formato' name='pcd_formato' style='width: 272px'>
                            <option value='' <?php echo (empty($pcd_formato)?" selected":"") ?>>Selecione</option>
                            <?php 
                            $formatos = new Pins_Card();
                            foreach($formatos->banks as $key => $val) {
                            ?>
                            <option value='<?php echo $key; ?>' <?php echo (isset($pcd_formato) && ($pcd_formato==$key)?" selected":"") ?>>Caracteres Aceitos [<?php echo $val[0]; ?>] - Qtde Posições [<?php echo $val[1]; ?>]</option>
                            <?php
                            } //end foreach
                            //echo "<pre>".print_r($formatos->banks,true)."</pre>";
                            ?>
                        </select>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                </thead>
                <tbody id='lista' name='lista'>
                    <tr>
                        <td>* Valor 1: </td>
                        <td>
                            <input name="valor1" type="text" id="valor1" size="40" maxlength="40" value="<?php echo (isset($valor1)) ? $valor1 : "";?>"  onBlur="isTipo(this.value);"/>&nbsp;<a href="#" id="mais" name="mais"><img src="/images/add.gif" border="0" alt="Adicionar Valor" title="Adicionar Valor"/></a>&nbsp;<a href="#" id="menos" name="menos"><img src="/images/excluir.gif" border="0" alt="Excluir Valor" title="Excluir Valor"/></a>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php 
                    if(isset($counter) && $counter>2) {
                        $quantidade_itens = $counter -1;
                        for($i = 2; $i <= $quantidade_itens; $i++)
                        {
                            echo '<tr id="linha'.$i.'" name="linha'.$i.'"> 
                                    <td>* Valor '.$i.': </td>
                                    <td>
                                        <input name="valor'.$i.'" type="text" id="valor'.$i.'" size="40" maxlength="40" value="'. ${'valor'.$i}.'" onBlur="isTipo(this.value);"/> 
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                                ';
                        }//end for
                    }//end if($counter>2)
                    else {
                        $quantidade_itens = 1;
                    }//end else do if($counter>2)
                    ?>
                </tbody>
            </table>
        </fieldset>
        <!--Irá armazenar a quantidade de linhas-->
        <input type="hidden"  value="<?php echo $quantidade_itens;?>" name="quantidade_itens" id="quantidade_itens" /> 
        <br>
	<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" style="font-size:10px">
	<tr>
		<td colspan="3">&nbsp;* Campos Obrigatórios</td>
	</tr>
	<tr>
        <td colspan="3" align="center"><input type="submit" name="Submit" class="btn btn-sm btn-info" value="<?php if($acao == 'atualizar') echo "Atualizar"; else echo"Cadastrar"; ?>"/></td>
	</tr>
	</table>
</form>
</div>
<script language='javascript'>
$( "#mais" ).click(function(){
    //recuperando o próximo numero da linha
    var next = eval($('#quantidade_itens').val()) + 1;
    //inserindo formulário
    $('#lista').append('<tr id="linha' + next + '" name="linha' + next + '">' +
                        '<td>* Valor ' + next + ': </td>' +
                        '<td>' +
                        '    <input name="valor' + next + '" type="text" id="valor' + next + '" size="40" maxlength="40" value="" onBlur="isTipo(this.value);"/>' + 
                        '</td>' +
                        '<td>&nbsp;</td>' +
                    '</tr>');
     
    //armazenando a quantidade de linhas ou registros no elemento hidden
    $('#quantidade_itens').val(next);
     
    return false;
});

$( "#menos" ).click(function() {
    //recuperando o próximo numero da linha
    var next = eval($('#quantidade_itens').val());
    //inserindo formulário
    $('#linha'+next).remove();
     
    //armazenando a quantidade de linhas ou registros no elemento hidden
    if(next > 1) {
        $('#quantidade_itens').val(next-1);
    }
     
    return false;
});
</script>
