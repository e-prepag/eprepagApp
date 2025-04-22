<?php
set_time_limit(3000);
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/pdv/corte_classPrincipal.php"; 

//Validacao
//------------------------------------------------------------------------------------------------------------------
$msg = "";
$msgUsuario = "";

//Processa Acoes
if($msg == ""){

        if(isset($acao) && $acao){
                //Validacao
                if($msg == ""){
                        if(!$corte_id || !is_numeric($corte_id)) $msg .= "Código do corte inválido.\n";
                        if(!$dep_id || !is_numeric($dep_id)) $msg .= "Código do depósito inválido.\n";
                }

                //Acoes
                if($msg == ""){

                        //Conciliacao manual
                        if($acao && $acao == "conciliar_manual"){
                                $ret = concilia_manualmente_temporario($corte_id, $dep_id, null);					
                                if($ret != "") $msg .= $ret;
                                else $msg = "Corte conciliado manualmente com sucesso.\n";
                        }
                }
        }
        $msgUsuario = $msg;
}

$varsel = "&BtnSearch=1&usuario_id=$usuario_id";

$msg = "";
if(!$usuario_id || !is_numeric($usuario_id)) $msg = "Código do usuário não fornecido ou inválido.\n";

if($msg == ""){

	//Busca dados do estabelecimento
	if($msg == ""){
		$sql = "select * from dist_usuarios_games ug where ug.ug_id = " . $usuario_id . " ";
		$rs_usuario = SQLexecuteQuery($sql);
		if(!$rs_usuario) $msg = "Erro ao buscar usuário.\n";
	}
	
	//Busca dados de corte
	if($msg == ""){
		$sql = "select * from cortes c where c.cor_ug_id = $usuario_id ";
                if(!empty($cor_codigo)) {
                    $sql .= " and cor_codigo = $cor_codigo ";
                }
                $sql .= "	order by cor_periodo_fim desc, cor_periodo_ini desc";
                //echo $sql . "<br>"; die();

		$rs_cortes = SQLexecuteQuery($sql);
		if(!$rs_cortes) $msg = "Erro ao buscar cortes.\n";
	}
}
$msg = $msgUsuario . $msg;

?>
<script>
	function canciliaManual(var_cor_codigo){

		var msg = '';
		var ret = confirm('Confirma a conciliação manual deste corte ?\nO boleto associado a este corte será cancelado.');
		if(!ret) return false;
		else { 
			var v_valor_new = prompt('Entre com o código do depósito:\n', '');
			if (v_valor_new == null) return false;
			else if(trimAll(v_valor_new) == '') msg = 'Código do depósito deve ser preenchido.';
			else if(isNaN(trimAll(v_valor_new))) msg = 'Código do depósito \'' + v_valor_new + '\' deve ser numérico.';
		}
		if(msg != '') alert(msg);
		else window.location.href='corte_consulta.php?acao=conciliar_manual&corte_id=' + var_cor_codigo + '&dep_id=' + v_valor_new + '<?=$varsel?>';
		
		return false;
	}
	
	function trimAll(sString) {
		while (sString.substring(0,1) == ' ')
			sString = sString.substring(1, sString.length);
		while (sString.substring(sString.length-1, sString.length) == ' ')
			sString = sString.substring(0,sString.length-1);
	
		return sString;
	}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
        <li><a href="index.php">Corte Semanal</a></li>
        <li class="active">Detalhe de corte</li>
    </ol> 
</div>
<table class="">
  <tr>
    <td align="center" valign="top" bgcolor="#FFFFFF" width="100%">
          <?php if($msg != ""){?><tr><td colspan="5" align="center"><font color="#FF0000" size="1" face="Arial, Helvetica, sans-serif"><?php echo $msg?></font></td></tr><?php }?>
		<br>
<?php if($rs_usuario && pg_num_rows($rs_usuario) > 0){
		$rs_usuario_row = pg_fetch_array($rs_usuario);
?>


        <table class="table fontsize-pp txt-preto">
          <tr bgcolor="#ECE9D8"> 
			<td align="center"><strong>C&oacute;d.</b></td>
			<td align="center"><b>Status</b></td>
			<td align="center"><b>Login</b></td>
			<td align="center"><b>Nome Fantasia</b></td>
			<td align="center"><b>Nome</b></td>
			<td align="center"><b>CNPJ</b></td>
			<td align="center"><b>CPF</b></td>
			<td align="center"><b>RG</b></td>
			<td align="center"><b>Responsável</b></td>
			<td align="center"><b>Email</b></td>
			<td align="center"><b>Endereço</b></td>
			<td align="center"><b>Bairro</b></td>
			<td align="center"><b>Cidade</b></td>
			<td align="center"><b>Estado</b></td>
			<td align="center"><b>CEP</b></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td align="center"><?php echo $rs_usuario_row['ug_id'] ?></td>
            <td align="center"><?php if(isset($ativo)) echo $ativo ?></td>
            <td><?php echo $rs_usuario_row['ug_login'] ?></td>
            <td><?php echo $rs_usuario_row['ug_nome_fantasia'] ?></td>
            <td><?php echo $rs_usuario_row['ug_nome'] ?></td>
            <td nowrap><?php echo mascara_cnpj_cpf($rs_usuario_row['ug_cnpj'], 'cnpj') ?></td>
            <td nowrap><?php echo $rs_usuario_row['ug_cpf'] ?></td>
            <td nowrap><?php echo $rs_usuario_row['ug_rg'] ?></td>
            <td><?php echo $rs_usuario_row['ug_responsavel'] ?></td>
            <td><?php echo $rs_usuario_row['ug_email'] ?></td>
            <td><?php echo $rs_usuario_row['ug_endereco'] ?>, <?php echo $rs_usuario_row['ug_numero'] ?> <?php echo $rs_usuario_row['ug_complemento'] ?> </td>
            <td><?php echo $rs_usuario_row['ug_bairro'] ?></td>
            <td><?php echo $rs_usuario_row['ug_cidade'] ?></td>
            <td><?php echo $rs_usuario_row['ug_estado'] ?></td>
            <td nowrap><?php echo $rs_usuario_row['ug_cep'] ?></td>
          </tr>
		</table>
		<br>
<?php }?>

		<table class="table txt-preto fontsize-p">
		  <tr bgcolor="#ECE9D8"> 
			<td align="center"><b>Cód</b></td>
			<td align="center"><b>Período de Apuração</b></td>
			<td align="center"><b>Qtde Vendas</b></td>
			<td align="center"><b>Venda Bruta</b></td>
			<td align="center"><b>Comissão</b></td>
			<td align="center"><b>Venda Líquida</b></td>
			<td align="center"><b>Status</b></td>
			<td align="center"><b>Tipo Pagto</b></td>
			<td align="center"><b>Boleto</b></td>
		  </tr>
<?php
if(!isset($cor1))
    $cor1 = "";
$cor2 = "#f5f5fb";
$cor3 = "#E5E5Eb";

			if($rs_cortes){
			while($rs_cortes_row = pg_fetch_array($rs_cortes)){
				$cor_status = $rs_cortes_row['cor_status'];
				$cor_status_descricao = $GLOBALS['CORTE_STATUS_DESCRICAO'][$rs_cortes_row['cor_status']];
				$cor_tipo_pagto = $rs_cortes_row['cor_tipo_pagto'];
?>
		  <tr class="texto" bgcolor="<?php if($cor1) echo $cor1; ?>"> 
			<td align="center"><?php echo $rs_cortes_row['cor_codigo'] ?></font></td>
			<td align="center"><?php echo formata_data($rs_cortes_row['cor_periodo_ini'], 0) ?> a <?php echo formata_data($rs_cortes_row['cor_periodo_fim'], 0) ?></td>
			<td align="right"><?php echo $rs_cortes_row['cor_venda_qtde'] ?> </font></td>
			<td align="right"><?php echo number_format ($rs_cortes_row['cor_venda_bruta'], 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format ($rs_cortes_row['cor_venda_comissao'], 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format ($rs_cortes_row['cor_venda_liquida'], 2, ',', '.') ?></td>
			<td align="center">
				<?php if($cor_status == $GLOBALS['CORTE_STATUS']['ABERTO']){?>
				<!--a onclick="return confirm('Confirma a conciliação manual deste corte ?\nO boleto associado a este corte será cancelado.');" href="corte_consulta.php?acao=conciliar_manual&corte_id=<?php echo $rs_cortes_row['cor_codigo']?><?=$varsel?>"-->
				<a href="#" onclick="return canciliaManual(<?php echo $rs_cortes_row['cor_codigo']?>);">
				<font class="texto"><?php echo substr($cor_status_descricao, 0, strpos($cor_status_descricao, ".")) ?></font>
				</a>
				<?php }else{ ?><?php echo substr($cor_status_descricao, 0, strpos($cor_status_descricao, ".")) ?><?php } ?>
				
			</td>
			<td align="center">
				<?php if($cor_tipo_pagto == $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){?>
					<?php if($rs_cortes_row['cor_bbc_boleto_codigo']){?>
					<a href="corte_boleto.php?bbc_boleto_codigo=<?php echo $rs_cortes_row['cor_bbc_boleto_codigo'] ?>" class="link_br" target="_blank">
					<font class="texto"><?php echo $GLOBALS['CORTE_FORMAS_PAGAMENTO_DESCRICAO'][$cor_tipo_pagto] ?></font>
					</a>
					<?php }else{ ?><?php echo $GLOBALS['CORTE_FORMAS_PAGAMENTO_DESCRICAO'][$cor_tipo_pagto] ?><?php } ?>
				<?php }?>
			</td>
			<td align="center">
				<?php if($rs_cortes_row['cor_bbc_boleto_codigo']){?>
				<a href="corte_boleto_detalhe.php?bbc_id=<?php echo $rs_cortes_row['cor_bbc_boleto_codigo'] ?>" class="link_br">
				<font class="texto"><?php echo $rs_cortes_row['cor_bbc_boleto_codigo'] ?></font>
				</a>
				<?php } ?>
			</td>
		  </tr>
<?php
				$cor1 = ($cor1 == $cor2 ? $cor3 : $cor2);
            }}
?>
	</table>

<?php
	require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
          </td>
        </tr>
      </table>
   </td>
  </tr>
</table>
</body>
</html>
