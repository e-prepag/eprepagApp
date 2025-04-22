<?php 
        require_once '../../../includes/constantes.php';
        require_once $raiz_do_projeto."db/connect.php";
        require_once $raiz_do_projeto."db/ConnectionPDO.php";
	require_once $raiz_do_projeto."includes/inc_register_globals.php";	

	function moeda2numeric($val){

		if(	(strlen($val) >= 4)
			&& (strrpos($val, ",") == strlen($val) - 3)
			&& is_numeric(substr($val, 0, 1))
			//&& (substr($val, 0, 1) != "0")
			){

			$val = str_replace('.','',$val);
			$val = str_replace(',','.',$val);
		}
		
		return $val;
	}
?>
<?php
	if(!$des_id) $des_id = 0;
	if(!$des_opr_codigo) $des_opr_codigo = 0;
	if(!$des_vg_pagto_tipo) $des_vg_pagto_tipo = 0;
	if(!$usuario_id) $usuario_id = 0;

	$varsel = "&opr_nome=$opr_nome&perc_desconto=$perc_desconto&des_opr_codigo=$des_opr_codigo&des_vg_pagto_tipo=$des_vg_pagto_tipo&des_id=$des_id&usuario_id=$usuario_id";


	//Valor inicial
	if(!$novo_perc_desconto) $novo_perc_desconto = $perc_desconto;


	if($BtnSearch){
		
		$msg = "";
		
		if(!$novo_perc_desconto || trim($novo_perc_desconto) == "") $msg = "Desconto deve ser preenchido.\n";
		else {
			$novo_perc_desconto_aux = moeda2numeric($novo_perc_desconto);
			if(!is_numeric($novo_perc_desconto_aux)) $msg = "Desconto inválido. Utilize o formato x,xx\n";
		}

		//atualiza desconto
		if($des_id){
			$sql  = "update tb_dist_descontos
					 set des_perc_desconto = $novo_perc_desconto_aux
					 where des_id = $des_id"; 
			$ret = pg_exec($connid, $sql);
			if(!$ret) $msg = "Erro ao atualizar desconto.\n";
			
		//Insere desconto para a operadora		
		}elseif($msg == ""){
			$sql  = "insert into tb_dist_descontos (des_opr_codigo, des_vg_pagto_tipo, des_ug_id, des_perc_desconto)
				 	 values($des_opr_codigo, $des_vg_pagto_tipo, $usuario_id, $novo_perc_desconto_aux)"; 
			$ret = pg_exec($connid, $sql);
			if(!$ret) $msg = "Erro ao inserir desconto.\n";
		}

?>		
		<script>
//			window.opener.location.href='com_desconto.php?';
			window.opener.location.reload();
			window.close();
		</script>
<?php		exit;
	}

	//Busca operadoras
//	$sql = "select * from operadoras where opr_status = '1' 
//				and opr_codigo not in (select des_opr_codigo from tb_dist_descontos where des_opr_codigo <> 0 and des_vg_pagto_tipo = $des_vg_pagto_tipo and des_ug_id = 0)
//			order by opr_nome";
//	$rs_operadoras = pg_exec($connid, $sql);
		
?>

<html>
<head>
<title>E-Prepag - Créditos para games online</title>
<link href="/css/css.css" rel="stylesheet" type="text/css">
</head>

<script language="javascript">

function fcnValidaDesconto() {

	var perc_desconto = document.formDesconto.novo_perc_desconto.value;
	if(perc_desconto == ''){
		alert('O Desconto deve ser preenchido');
		return false;
	}
	
	perc_desconto = perc_desconto.replace("." , "").replace("," , ".");
	if(isNaN(perc_desconto)){
		alert('Valor do Desconto inválido. Utilize o formato x,xx');
		return false;
	}
	
	return true;
}

</script>


<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td align="center" valign="top" bgcolor="#FFFFFF" width="100%">
	
        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr> 
            <td colspan="5" height="21" bgcolor="00008C">
				<font face="Arial, Helvetica, sans-serif" size="1" color="#FFFFFF">&nbsp;<b>Desconto Default</b></font></td>
			</td>
          </tr>
		</table>

 <form name="formDesconto" method="post" action="com_desconto_selecao.php?<?php echo $varsel ?>">

        <table width="100%" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF" height="25"> 
            <td colspan="2" bgcolor="#ECE9D8"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<b>Desconto</b></font></td>
          </tr>
          <?php if($opr_nome && trim($opr_nome) != ""){ ?>
          <tr bgcolor="#F5F5FB" height="25"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<b>Operadora</b></font></td>
            <td><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $opr_nome?></font></td>
          </tr>
          <?php } ?>
          <tr bgcolor="#F5F5FB"> 
            <td width="100"><font color="#666666" size="1" face="Arial, Helvetica, sans-serif">&nbsp;<b>Desconto</b></font></td>
            <td>
				<input type="text" name="novo_perc_desconto" value="<?php echo $novo_perc_desconto?>" size="5" maxlength="5">
				<font color="#666666" size="1" face="Arial, Helvetica, sans-serif">(x,xx)</font>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td></td>
            <td><input type="submit" name="BtnSearch" value="Alterar" class="botao_search" onclick="return fcnValidaDesconto();"></td>
          </tr>

		<?php if($msg != ""){?>
		  	<tr><td colspan="2">&nbsp;</td></tr>
		  	<tr><td colspan="2" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php echo $msg?></font></td></tr>
		<?php }?>
		</table>
</form>


   </td>
  </tr>
</table>
</body>
</html>
