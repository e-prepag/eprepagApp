<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "includes/pdv/corte_constantes.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."db/connect.php";
require_once $raiz_do_projeto."includes/inc_register_globals.php";
header("Content-Type: text/html; charset=UTF-8",true);

$varsel = "&usuario_id=$usuario_id";

if($v_campo){


	//-------------------------------------------------------------------------------------------------------------------------------
	//Limite de Referência
	//-------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'limitereferencia'){
		if($novo_limitereferencia){
?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php  echo $v_campo ?>&v_valor_new=<?php  echo $novo_limitereferencia ?><?php  echo $varsel ?>';
				window.close();
			</script>
<?php 			exit;
		}

		//Valor inicial
		if($limitereferencia) $novo_limitereferencia = $limitereferencia;
	}
	//-------------------------------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------------------------------
	//Risco Classificação
	//-------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'riscoclassif'){
		if($novo_riscoclassif){
?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php  echo $v_campo ?>&v_valor_new=<?php  echo $novo_riscoclassif ?><?php  echo $varsel ?>';
				window.close();
			</script>
<?php 			exit;
		}

		//Valor inicial
		if($riscoclassif) $novo_riscoclassif = $riscoclassif;
	}
	//-------------------------------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------------------------------
	//Ativo
	//-------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'ativo'){
		if($novo_ativo){
?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php  echo $v_campo ?>&v_valor_new=<?php  echo $novo_ativo ?><?php  echo $varsel ?>';
				window.close();
			</script>
<?php 			exit;
		}

		//Valor inicial
		if($ativo) $novo_ativo = $ativo;
	}
	//-------------------------------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------------------------------
	//Status - Busca
	//-------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'status_busca'){
		if($novo_statusbusca){
?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php  echo $v_campo ?>&v_valor_new=<?php  echo $novo_statusbusca ?><?php  echo $varsel ?>';
				window.close();
			</script>
<?php 			exit;
		}

		//Valor inicial
		if($status_busca) $novo_statusbusca = $status_busca;
	}
	//-------------------------------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------------------------------
	//Substatus
	//-------------------------------------------------------------------------------------------------------------------------------
//echo "v_campo: ".$v_campo."<br>";
//echo "substatus: ".$substatus."<br>";
//echo "novo_substatus: ".$novo_substatus."<br>";
	if($v_campo == 'substatus'){
		if($novo_substatus){
?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php  echo $v_campo ?>&v_valor_new=<?php  echo $novo_substatus ?>&novo_ug_data_aprovacao=<?php echo urlencode($novo_ug_data_aprovacao); ?><?php echo $varsel ?>';
				window.close();
			</script>
<?php 			exit;
		}

		//Valor inicial
		if($substatus) $novo_substatus = $substatus;
	}
	//-------------------------------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------------------------------
	//perfil_forma_pagto
	//-------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'ug_perfil_forma_pagto'){
		if($novo_ug_perfil_forma_pagto){
?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php  echo $v_campo ?>&v_valor_new=<?php  echo $novo_ug_perfil_forma_pagto ?><?php  echo $varsel ?>';
				window.close();
			</script>
<?php 			exit;
		}

		//Valor inicial
		if($ug_perfil_forma_pagto) $novo_ug_perfil_forma_pagto = $ug_perfil_forma_pagto;
	}
	//-------------------------------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------------------------------
	//perfil_corte_dia_semana
	//-------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'ug_perfil_corte_dia_semana'){
		if($novo_ug_perfil_corte_dia_semana){
?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php  echo $v_campo ?>&v_valor_new=<?php  echo $novo_ug_perfil_corte_dia_semana ?><?php  echo $varsel ?>';
				window.close();
			</script>
<?php 			exit;
		}

		//Valor inicial
		if($ug_perfil_corte_dia_semana) $novo_ug_perfil_corte_dia_semana = $ug_perfil_corte_dia_semana;
	}
	//-------------------------------------------------------------------------------------------------------------------------------
        
        //-------------------------------------------------------------------------------------------------------------------------------
	//Tipo de Cadastro
	//-------------------------------------------------------------------------------------------------------------------------------
	if($v_campo == 'ug_tipo_cadastro'){
		if($novo_ug_tipo_cadastro){
?>
			<script>
				window.opener.location.href='com_usuario_detalhe.php?acao=a&v_campo=<?php  echo $v_campo ?>&v_valor_new=<?php  echo utf8_encode($novo_ug_tipo_cadastro) ?><?php  echo $varsel ?>';
				window.close();
			</script>
<?php 			exit;
		}

		//Valor inicial
		if($ug_tipo_cadastro) $novo_ug_tipo_cadastro = $ug_tipo_cadastro;
	}
	//-------------------------------------------------------------------------------------------------------------------------------

}
?>

<html>
<head>
<title>Rede E-PREPAG Meios de Pagamentos</title>
<link href="/css/css.css" rel="stylesheet" type="text/css">

<script language="javascript">

function fcnValidaLimiteReferencia() {

	if(document.formLimiteReferencia.novo_limitereferencia.value == ''){
		document.formLimiteReferencia.novo_limitereferencia.value = '0';
//		alert('O valor de Limite de Referência deve ser fornecido');
//		return false;
	}
	return true;
}

function fcnValidaRiscoClassif() {

	if(document.formRiscoClassif.novo_riscoclassif.value == ''){
		alert('A Classificação de Risco deve ser selecionada');
		return false;
	}
	return true;
}

function fcnValidaAtivo() {

	if(document.formAtivo.novo_ativo.value == ''){
		alert('O Status deve ser selecionado');
		return false;
	}
	return true;
}

function fcnValidaStatusBusca() {

	if(document.formStatus.novo_statusbusca.value == ''){
		alert('O Status Busca deve ser selecionado');
		return false;
	}
	return true;
}

function fcnValidaSubstatus() {

	if(document.formSubstatus.novo_substatus.value == ''){
		alert('O Substatus deve ser selecionado');
		return false;
	}
	return true;
}

function fcnValidaPerfilFormaPagto() {

	if(document.formPerfilFormaPagto.novo_ug_perfil_forma_pagto.value == ''){
		alert('A Forma de Pagamento deve ser selecionado');
		return false;
	}
	return true;
}

function fcnValidaPerfilCorteDiaSemana() {

	if(document.formPerfilCorteDiaSemana.novo_ug_perfil_corte_dia_semana.value == ''){
		alert('O Dia de Corte deve ser selecionado');
		return false;
	}
	return true;
}

</script>

<title>REDE E-PREPAG</title>
</head>

<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td align="center" valign="top" bgcolor="#FFFFFF" width="100%">
	
        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr> 
            <td colspan="5" height="21" bgcolor="00008C">
				<font face="Arial, Helvetica, sans-serif" size="1" color="#FFFFFF"><b><?php echo utf8_encode("Money Distribuidor - Usuário"); ?></b></font></td>
			</td>
          </tr>
		</table>

<?php  if($v_campo == 'limitereferencia'){ ?>
 <form name="formLimiteReferencia" method="post" action="?v_campo=<?php  echo $v_campo ?><?php  echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><?php echo utf8_encode("Classificação de Risco"); ?></font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><?php echo utf8_encode("Classificação"); ?></font></td>
            <td>
				<select name="novo_limitereferencia">
					<option value="" <?php  if((strcmp($novo_limitereferencia,"")==0)||(is_null($novo_limitereferencia))||($novo_limitereferencia==0)) echo "selected" ?>>Selecione</option>
				<?php  for($i=1; $i < count($RISCO_CLASSIFICACAO_NOMES)+1; $i++){ ?>
					<option value="<?php  echo utf8_encode($RISCO_CLASSIFICACAO_NOMES[$i]) ?>"<?php  if(strcmp($RISCO_CLASSIFICACAO_NOMES[$novo_limitereferencia], $RISCO_CLASSIFICACAO_NOMES[$i])==0) echo " selected"; ?>><?php  echo utf8_encode($RISCO_CLASSIFICACAO_NOMES[$i]) ?></option>
				<?php  } ?>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search" onclick="return fcnValidaLimiteReferencia();"></td>
          </tr>

		<?php  if($msg != ""){?>
		  	<tr><td colspan="2">&nbsp;</td></tr>
		  	<tr><td colspan="2" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php  echo $msg?></font></td></tr>
		<?php  }?>
		</table>
</form>
<?php  } ?>

<?php   if($v_campo == 'riscoclassif'){  ?>
 <form name="formRiscoClassif" method="post" action="?v_campo=<?php  echo $v_campo ?><?php  echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><?php echo utf8_encode("Classificação de Risco"); ?></font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><?php echo utf8_encode("Classificação"); ?></font></td>
            <td>
				<select name="novo_riscoclassif">
					<option value="" <?php  if((strcmp($novo_riscoclassif,"")==0)||(is_null($novo_riscoclassif))||($novo_riscoclassif==0)) echo "selected" ?>>Selecione</option>
				<?php  for($i=1; $i < count($RISCO_CLASSIFICACAO_NOMES)+1; $i++){ ?>
					<option value="<?php  echo utf8_encode($RISCO_CLASSIFICACAO_NOMES[$i]) ?>"<?php  if(strcmp($RISCO_CLASSIFICACAO_NOMES[$novo_riscoclassif], $RISCO_CLASSIFICACAO_NOMES[$i])==0) echo " selected"; ?>><?php  echo utf8_encode($RISCO_CLASSIFICACAO_NOMES[$i]) ?></option>
				<?php  } ?>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search" onclick="return fcnValidaRiscoClassif();"></td>
          </tr>

		<?php  if($msg != ""){?>
		  	<tr><td colspan="2">&nbsp;</td></tr>
		  	<tr><td colspan="2" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php  echo $msg?></font></td></tr>
		<?php  }?>
		</table>
</form>
<?php  } ?>

<?php  if($v_campo == 'ativo'){ ?>
 <form name="formAtivo" method="post" action="?v_campo=<?php  echo $v_campo ?><?php  echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Status</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Status</font></td>
            <td>
				<select name="novo_ativo">
					<option value="">Selecione o status</option>
					<option value="1" <?php  if($novo_ativo == "1") echo "selected"; ?>>Ativo</option>
					<option value="2" <?php  if($novo_ativo != "1") echo "selected"; ?>>Inativo</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search" onclick="return fcnValidaAtivo();"></td>
          </tr>

		<?php  if($msg != ""){?>
		  	<tr><td colspan="2">&nbsp;</td></tr>
		  	<tr><td colspan="2" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php  echo $msg?></font></td></tr>
		<?php  }?>
		</table>
</form>
<?php  } ?>

<?php  if($v_campo == 'status_busca'){ ?>
 <form name="formStatus" method="post" action="?v_campo=<?php  echo $v_campo ?><?php  echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Status - Busca</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Status</font></td>
            <td>
				<select name="novo_statusbusca">
					<option value="">Selecione o status</option>
					<option value="1" <?php  if($novo_statusbusca == "1") echo "selected"; ?>>Ativo</option>
					<option value="2" <?php  if($novo_statusbusca != "1") echo "selected"; ?>>Inativo</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search" onClick="return fcnValidaStatusBusca();"></td>
          </tr>

		<?php  if($msg != ""){?>
		  	<tr><td colspan="2">&nbsp;</td></tr>
		  	<tr><td colspan="2" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php  echo $msg?></font></td></tr>
		<?php  }?>
		</table>
</form>
<?php  } ?>

<?php  if($v_campo == 'substatus'){ ?>
 <form name="formSubstatus" method="post" action="?v_campo=<?php  echo $v_campo ?><?php  echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Substatus</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Substatus</font></td>
            <td>
				<select name="novo_substatus">
                	<option value="" <?php  if($substatus == "") echo "selected" ?>>Selecione</option>
                	<?php
						foreach($SUBSTATUS_LH as $indice=>$dado) {
							echo "<option value=\"".$indice."\""; if($substatus == $indice) echo "selected"; echo " >".utf8_encode($dado)." (".$indice.")</option>\n";
						}
					?>

				</select>
                                <input type="hidden" name="novo_ug_data_aprovacao" id="novo_ug_data_aprovacao" value="<?php echo urldecode($data_aprovacao); ?>">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search" onClick="return fcnValidaSubstatus();"></td>
          </tr>

		<?php  if($msg != ""){?>
		  	<tr><td colspan="2">&nbsp;</td></tr>
		  	<tr><td colspan="2" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php  echo $msg?></font></td></tr>
		<?php  }?>
		</table>
</form>
<?php  } ?>

<?php  if($v_campo == 'ug_perfil_forma_pagto'){ ?>
 <form name="formPerfilFormaPagto" method="post" action="?v_campo=<?php  echo $v_campo ?><?php  echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Forma de Pagamento</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Forma de Pagamento</font></td>
            <td>
                                <select name="novo_ug_perfil_forma_pagto" readonly disabled="disabled">
					<option value="">Selecione a Forma de Pagamento</option>
					<?php  foreach ($FORMAS_PAGAMENTO_DESCRICAO as $formaPagtoId => $formaPagtoDesc){ ?>
					<option value="<?php  echo $formaPagtoId; ?>" <?php  if ($novo_ug_perfil_forma_pagto == $formaPagtoId) echo "selected";?>><?php  echo utf8_encode($formaPagtoDesc); ?></option>
					<?php  } ?>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search" onclick="return fcnValidaPerfilFormaPagto();"></td>
          </tr>

		<?php  if($msg != ""){?>
		  	<tr><td colspan="2">&nbsp;</td></tr>
		  	<tr><td colspan="2" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php  echo $msg?></font></td></tr>
		<?php  }?>
		</table>
</form>
<?php  } ?>

<?php  if($v_campo == 'ug_perfil_corte_dia_semana'){ ?>
 <form name="formPerfilCorteDiaSemana" method="post" action="?v_campo=<?php  echo $v_campo ?><?php  echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Dia de Corte</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Dia de Corte</font></td>
            <td>
				<select name="novo_ug_perfil_corte_dia_semana">
					<option value="">Selecione o Dia de Corte</option>
					<?php  foreach ($GLOBALS['CORTE_DIAS_DA_SEMANA_DESCRICAO'] as $formaPagtoId => $formaPagtoDesc){ ?>
					<option value="<?php  echo $formaPagtoId; ?>" <?php  if ($novo_ug_perfil_corte_dia_semana == $formaPagtoId) echo "selected";?>><?php  echo utf8_encode($formaPagtoDesc); ?></option>
					<?php  } ?>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search" onclick="return fcnValidaPerfilCorteDiaSemana();"></td>
          </tr>

		<?php  if($msg != ""){?>
		  	<tr><td colspan="2">&nbsp;</td></tr>
		  	<tr><td colspan="2" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php  echo utf8_encode($msg)?></font></td></tr>
		<?php  }?>
		</table>
</form>
<?php  } ?>

<?php
if($v_campo == 'ug_tipo_cadastro'){ 
?>
 <form name="formUgTipoCadastro" method="post" action="?v_campo=<?php  echo $v_campo ?><?php  echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">Tipo de Cadastro</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif"><?php echo utf8_encode("Classificação"); ?></font></td>
            <td>
                <select name="novo_ug_tipo_cadastro">
                        <option value="PJ" <?php  if($novo_ug_tipo_cadastro == 'PJ') echo "selected" ?>><?php echo utf8_encode("Pessoa Jurídica (CNPJ)") ?></option>
                        <option value="PF" <?php  if($novo_ug_tipo_cadastro == 'PF') echo "selected" ?>><?php echo utf8_encode("Pessoa Física (CPF)") ?></option>
                </select>
            </td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search"></td>
          </tr>

		<?php
                if($msg != ""){
                ?>
		  	<tr><td colspan="2">&nbsp;</td></tr>
		  	<tr><td colspan="2" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php echo utf8_encode($msg); ?></font></td></tr>
		<?php
                }
                ?>
		</table>
</form>
<?php 
} //end if($v_campo == 'ug_tipo_cadastro')
?>

   </td>
  </tr>
</table>
</body>
</html>
