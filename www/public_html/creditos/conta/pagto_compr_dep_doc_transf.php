<?php
    require_once "../../../includes/constantes.php";
    require_once DIR_INCS . "main.php";
    require_once DIR_INCS . "pdv/main.php";
    require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";
    $_PaginaOperador2Permitido = 54; 
    validaSessao(); 
    require_once DIR_INCS . "pdv/venda_e_modelos_logica.php";

    pg_result_seek($rs_venda, 0);
    $rs_venda_row = pg_fetch_array($rs_venda);
    $pagto_tipo	 = $rs_venda_row['vg_pagto_tipo'];
    $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];

    if($pagto_tipo != $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']){
            $strRedirect = "/creditos/conta/lista_vendas.php";
            redirect($strRedirect);
    }

    $pagina_titulo = "Comprovante " . $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$pagto_tipo];
    require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/cabecalho.php"; 
?>

	<table  border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr><td>&nbsp;</td></tr>
    <tr valign="top" align="center">
      <td>
      
		<?php require_once DIR_INCS . "pdv/venda_e_modelos_view.php"; ?>
		<?php //include "../includes/pagto_compr_usuario_dados.php"; ?>

		<br>
		<table border="0" cellspacing="0" width="90%" align="center">
	        <tr bgcolor="E0E0E0">
	          <td class="texto" align="center" height="25"><b>Status</b></td>
	        </tr>
	        <tr bgcolor="F0F0F0">
	          <td class="texto" align="center" height="25"><?=$STATUS_VENDA_DESCRICAO[$vg_ultimo_status]?></td>
	        </tr>
		</table>

		<br>
		<?php require_once DIR_INCS . "pdv/inc_quadro_lotes_disponiveis.php"; ?>

      </td>
    </tr>
	</table>

	<table border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr><td>&nbsp;</td></tr>
    <tr>
      	<td align="center" class="texto">
         	<input type="button" name="btOK" value="Continuar" OnClick="window.location='/creditos/conta/lista_vendas.php';" class="botao_simples">
      	</td>
    </tr>
	</table>

<?php require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/rodape.php"; ?>

        