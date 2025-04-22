<?php 
require_once "../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_INCS . "pdv/captura_inc.php"; 
require_once DIR_CLASS . "gamer/classAlawarGames.php"; 

if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
	$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
}
//Produto
$prod = $_REQUEST['prod'];

$msg = "";

//valida produto
if($msg == ""){
	if(!$prod || $prod == "" || !is_numeric($prod)) $msg = "Código do produto não fornecido ou inválido.";
}

//Obtem o produto selecionado	
if($msg == ""){
        if($prod == 5) {
            header("Location: https://".$_SERVER["SERVER_NAME"]."/game/produto/detalhe.php?token=IlJQTB5TdFZVAj0XFwEHNwdDTRYaf1tNFW9eBQ==");
        }
        else {
            header("Location: https://".$_SERVER["SERVER_NAME"]."/");
        }
        die();
	$rs = null;
	$filtro['ogp_ativo'] = 1;
	$filtro['ogp_id'] = $prod;
// Wagner
	$filtro['ogp_mostra_integracao_com_loja'] = '1';

	// Debug reinaldops
//		if($usuarioGames->b_IsLogin_pagamento_usa_produto_treinamento()) {
//			$filtro['show_treinamento'] = 1;
//		}

	$img_logo_stardoll = "";
	if ($_SESSION['epp_origem'] == "STARDOLL") {			// Filtro indireto por HTTP_REFERER capturado
		$filtro['ogp_opr_codigo'] = 38;
//echo "*(".$_SESSION['epp_origem'].")";
	} elseif ($_SESSION['epp_origem'] == "TMP") {			// Testes
		$filtro['ogp_opr_codigo'] = 38;
	} 

	if(isset($usuarioGames)) {
		if($usuarioGames->b_IsLogin_pagamento_pin_eprepag()) {
//				$filtro['ogp_ativo'] = 0;
		}
	}

//*		if(isset($usuarioGames)) {
//*			if($usuarioGames->b_IsLogin_Reinaldo()) { 
//echo "prod: $prod, codeProd: $codeProd, opr_codigo_Alawar: ".$GLOBALS['opr_codigo_Alawar']."<br>";
			if($prod == $GLOBALS['prod_Alawar']) {
				$codProdAlawar = $_REQUEST['codeProd'];

				$objAlawar = new AlawarGames();

				$comboGames = $objAlawar->createComboBox($codProdAlawar);
//				echo $comboGames;

				// Dummy
//					$filtro['ogp_ativo'] = 0;

			} else {
//			echo "*";
			}
//die("Stop");
//*			}
//*		}
	
	$ret = Produto::obtermelhorado($filtro, null, $rs);
	if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
}

header("Location: https://".$_SERVER["SERVER_NAME"]."/");
die();	

//redireciona
if($msg != ""){
	$strRedirect = "/game/index.php?msg=$msg";
	redirect($strRedirect);
}

$rs_row = pg_fetch_array($rs);
// Wagner
$produto = new Produto($rs_row['ogp_id'], $rs_row['ogp_nome'],$rs_row['ogp_descricao'],$rs_row['ogp_ativo'],$rs_row['ogp_nome_imagem'],$rs_row['ogp_data_inclusao'], $rs_row['ogp_opr_codigo'], $rs_row['ogp_mostra_integracao']);
$opr_codigo = $produto->getOprCodigo();

// [Alawar] - Se código da operadora for Alawar e $_GET['codeProd'] não estiver setado, volta para a vitrine
if ( ($opr_codigo == $opr_codigo_Alawar) && !$_GET['codeProd']) {
	redirect("/game/jogos/");
}

$pagina_titulo = $produto->getNome(); 
include DIR_WEB . "game/icludes/cabecalho.php"; 
?>


	<table  border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
	<tr><td colspan="5">&nbsp;</td></tr>
	<tr>
      <td align="center" class="texto" width="30%">
	<?php 
//		if($prod == 53) {
//	?_>
//		<img border="0" src="http://www.e-prepag.com.br/prepag2/commerce/images/produtos/stardoll_compra_638x121.jpg"><br>&nbsp;
//	<_?php
//		} else
		if($produto->getNomeImagem() && $produto->getNomeImagem() != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $produto->getNomeImagem())) { 
	?>
      	<img border="0" src="<?php echo $GLOBALS['URL_DIR_IMAGES_PRODUTO'] . $produto->getNomeImagem()?>">
    <?php 
		}
	?>
      </td>
    </tr>
    <tr>
      <td align="center" class="texto"><b><?php echo $produto->getNome()?></b></td>
    </tr>
    <tr>
      <td align="center" class="texto"><?php echo $produto->getDescricao()?></td>
	</tr>
	<?php 
/*
//*	if($usuarioGames->b_IsLogin_Reinaldo()) { 
	?>
    <tr>
      <td align="center" class="texto"><?php 

			echo $comboGames;

	  ?></td>
	</tr>
	<?php 
//*		  } 
*/
	  ?>
	<tr><td colspan="5">&nbsp;</td></tr>
	</table>

	<?php 
	
	/** Para Produtos Alawar **/
	$codProdAlawar = $_REQUEST['codeProd'];
	
	if($codProdAlawar) {
	
		$alawarGames = new AlawarGames();
		$filtro['pag_id'] = $codProdAlawar;
		$resultGame = $alawarGames->getGamesBy($filtro);
			
	?>		
		<table border="0" style="margin: 0 auto; display: block; *margin-left: 65px; font-family: Tahoma; font-size: 11px; border-collapse: collapse; border:1px solid #ccc; width: 650px;">
			<tr>
				<td style="padding: 5px;border-right:1px solid #ccc;"><img src="<?php echo $resultGame[$codProdAlawar]['pag_icon']; ?>" /></td>
				<td valign="top">
					<br />
					<strong style="margin-top: 10px; margin-left: 5px;"><?php echo  $resultGame[$codProdAlawar]['pag_name']; ?></strong>
					<?php if( $resultGame[$codProdAlawar]['pag_online_game'] == 1) echo "<strong>(Online)</strong>"; ?><br />
					<p style="margin-left: 5px;margin-top: 10px;"> <?php echo  $resultGame[$codProdAlawar]['pag_description']; ?> </p><br />
					<strong style="margin-left: 5px;">ID :</strong> <?php echo $codProdAlawar; ?>
					<input type="hidden" name="gamesAlawar" id="gamesAlawar" value="<?php echo $codProdAlawar; ?>">
				</td>
			</tr>
		</table>
	<?php 
			
	}

	?>

	<table  border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr valign="top" align="center">
      <td>
      	<?php 
		// Wagner
		if(!$produto->getMostraIntegracao()) {
			mostraProdutoModelos($prod, $opr_codigo);
		}
		?>
      </td>
    </tr>
	<?php
		if(false) {
	?>
    <tr valign="top" align="center">
      <td>Aguarde em Breve os Planos de Acesso</td>
    </tr>
	<?
		}
	?>
	</table>

	<table border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr>
      	<td align="center" class="texto">
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='/prepag2/commerce/index.php';" class="botao_simples">
      	</td>
    </tr>
    <tr height="60"><td>&nbsp;</td></tr>
	</table>

<?php 

include "includes/rodape.php"; 

	function mostraProdutoModelos($produtoId, $opr_codigo){

	$rs = null;
	$filtro['ogpm_ativo'] = 1;
	$filtro['ogpm_ogp_id'] = $produtoId;
	$b_show_treinamento = false;

	// Debug reinaldops
	if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
		$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
//			if($usuarioGames->b_IsLogin_pagamento_usa_produto_treinamento()) {
//				$filtro['show_treinamento'] = 1;
//				$b_show_treinamento = true && ($produtoId==63);
//			}
	}

//echo "$produtoId";
	if($produtoId==15) {
		echo "<span  class='texto'>Aguarde em Breve os Planos de Acesso</span>";
	}

	// Só lista produtos com este valor de ativo
	$produto_ativo = 1;

	if(isset($usuarioGames)) {
		if($usuarioGames->b_IsLogin_pagamento_pin_eprepag()) {
//				$filtro['ogpm_ativo'] = 0;
			// Lista produtos treinamento para usuarios b_IsLogin_pagamento_pin_eprepag(), para testes
//				$produto_ativo = 0;
		}
	}

//*		if(isset($usuarioGames)) {
//*			if($usuarioGames->b_IsLogin_Reinaldo()) { 
//echo "produtoId: $produtoId, opr_codigo: $opr_codigo, opr_codigo_Alawar: ".$GLOBALS['opr_codigo_Alawar'].", prod_Alawar: ".$GLOBALS['prod_Alawar']."<br>";
			if($produtoId == $GLOBALS['prod_Alawar']) {
				// Dummy - Não precissa, o modelo está ativ, o que está inativo é o produto
//					$filtro['ogpm_ativo'] = 0;
			}
//*			}
//*		}

	$ret = ProdutoModelo::obter($filtro, "ogpm_valor asc", $rs);
	if(!$rs || pg_num_rows($rs) == 0){
?>			
		<table border="0" cellspacing="0" width="90%" height="200">
		<tr align="center" bgcolor="#FFFFFF">
		  <td align="center" class="texto">Nenhum modelo disponível no momento</td>
		</tr>
		</table>
<?php
	} else {
		?><table border="0" cellspacing="0" width="100%"><?php

		//Limpa produtos
		$produtoModelo[0] = $produtoModelo[1] = $produtoModelo[2] = new ProdutoModelo();

		//mostra de 3 em 3
		for($i=0; $rs_row = pg_fetch_array($rs); $i++){
			$ogpm_ativo = $rs_row['ogpm_ativo'];
			$b_show_treinamento = false;

//*				if(isset($usuarioGames)) {
//*					if($usuarioGames->b_IsLogin_Reinaldo()) { 
//echo "produtoId: $produtoId, opr_codigo: $opr_codigo, opr_codigo_Alawar: ".$GLOBALS['opr_codigo_Alawar'].", prod_Alawar: ".$GLOBALS['prod_Alawar']."<br>";
					if($produtoId == $GLOBALS['prod_Alawar']) {
						// Dummy
//							$ogpm_ativo = 1; // para Alawar não precissa
//							$b_show_treinamento = true;
					}
//*					}
//*				}

			$produtoModelo[$i % 3] = new ProdutoModelo($rs_row['ogpm_id'], $rs_row['ogpm_ogp_id'], $rs_row['ogpm_nome'], $rs_row['ogpm_descricao'], $rs_row['ogpm_valor'], $ogpm_ativo, $rs_row['ogpm_nome_imagem'], $rs_row['ogpm_data_inclusao'], $rs_row['ogpm_pin_valor'], $rs_row['ogpm_valor_eppcash']);
/*
// Debug reinaldops
if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
if($usuarioGames->getEmail()=="REINALDOPS@HOTMAIL.COM")  {
	echo "produtoModelo($i) <br> 
	[0]->getId(): ".$produtoModelo[0]->getId()."(".($produtoModelo[0]->getAtivo()?"YES":"no").", ".(($produtoModelo[0]->getId()>0 && $b_show_treinamento)?"show":"don't")."), 
	[1]->getId(): ".$produtoModelo[1]->getId()."(".($produtoModelo[1]->getAtivo()?"YES":"no").", ".(($produtoModelo[1]->getId()>0 && $b_show_treinamento)?"show":"don't")."), 
	[2]->getId(): ".$produtoModelo[2]->getId()."(".($produtoModelo[2]->getAtivo()?"YES":"no").", ".(($produtoModelo[2]->getId()>0 && $b_show_treinamento)?"show":"don't").")<br>";
}
}
*/
			if($i % 3 == 2 || $i == pg_num_rows($rs) - 1){
?>
			<!-- Imagem -->
			<tr>
			  <td align="center" class="texto" width="30%">
			  <?php if($produtoModelo[0]->getAtivo() == $produto_ativo || ($produtoModelo[0]->getId()>0 && $b_show_treinamento)) { ?>
				<?php if($produtoModelo[0]->getNomeImagem() && $produtoModelo[0]->getNomeImagem() != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $produtoModelo[0]->getNomeImagem())){ ?>
				<img border="0" src="<?php echo $GLOBALS['URL_DIR_IMAGES_PRODUTO'] . $produtoModelo[0]->getNomeImagem()?>">
				<?php } ?>
			  <?php } ?>
			  </td>
				  <td width="5%">&nbsp;</td>
				  <td align="center" class="texto" width="30%">
			  <?php if($produtoModelo[1]->getAtivo() == $produto_ativo || ($produtoModelo[1]->getId()>0 && $b_show_treinamento)) { ?>
				<?php if($produtoModelo[1]->getNomeImagem() && $produto[1]->getNomeImagem() != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $produtoModelo[1]->getNomeImagem())){ ?>
					<img border="0" src="<?php echo $GLOBALS['URL_DIR_IMAGES_PRODUTO'] . $produtoModelo[1]->getNomeImagem()?>">
				<?php } ?>
			  <?php } ?>
				  </td>
				  <td width="5%">&nbsp;</td>
				  <td align="center" class="texto" width="30%">
			  <?php if($produtoModelo[2]->getAtivo() == $produto_ativo || ($produtoModelo[2]->getId()>0 && $b_show_treinamento)) { ?>
				<?php if($produtoModelo[2]->getNomeImagem() && $produto[2]->getNomeImagem() != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $produtoModelo[2]->getNomeImagem())){ ?>
					<img border="0" src="<?php echo $GLOBALS['URL_DIR_IMAGES_PRODUTO'] . $produtoModelo[2]->getNomeImagem()?>">
				<?php } ?>
			  <?php } ?>
				  </td>
			</tr>
			<!-- Nome -->
			<tr>
			  <td align="center" class="textoProduto" height="25">
			  <?php if($produtoModelo[0]->getAtivo() == $produto_ativo || ($produtoModelo[0]->getId()>0 && $b_show_treinamento)){ ?>
			  <b><?php echo $produtoModelo[0]->getNome()?></b>
			  <?php }?>&nbsp;
			  </td>
				  <td>&nbsp;</td>
				  <td align="center" class="textoProduto">
			  <?php if($produtoModelo[1]->getAtivo() == $produto_ativo || ($produtoModelo[1]->getId()>0 && $b_show_treinamento)){ ?>
				  <b><?php echo $produtoModelo[1]->getNome()?></b>
			  <?php }?>&nbsp;
				  </td>
				<td>&nbsp;</td>
				<td align="center" class="textoProduto">
			  <?php if($produtoModelo[2]->getAtivo() == $produto_ativo || ($produtoModelo[2]->getId()>0 && $b_show_treinamento)){ ?>
				<b><?php echo $produtoModelo[2]->getNome()?></b>
			  <?php }?>&nbsp;
				</td>
			</tr>
			<!-- Valor -->
			<tr>
				  <td align="center" class="textoEPPCASH" height="25">
			  <?php if($produtoModelo[0]->getAtivo() == $produto_ativo || ($produtoModelo[0]->getId()>0 && $b_show_treinamento)){ ?><?php
					//if(isset($usuarioGames)) {
					//	if($usuarioGames->b_IsLogin_valorPINEPPCash()) {
							echo get_info_EPPCash_NO_Table($produtoModelo[0]->getValorEPPCash());
					//	}
					//}
					?>| R$ <?php echo number_format($produtoModelo[0]->getValor(), 2, ',', '.')?>
			  <?php }?>&nbsp;
				  </td>
				  <td>&nbsp;</td>
				  <td align="center" class="textoEPPCASH"><nobr>
			  <?php if($produtoModelo[1]->getAtivo() == $produto_ativo || ($produtoModelo[1]->getId()>0 && $b_show_treinamento)){ ?><?php
					//if(isset($usuarioGames)) {
					//	if($usuarioGames->b_IsLogin_valorPINEPPCash()) {
							//echo get_info_EPPCash($produtoModelo[1]->getValorEPPCash());
							echo get_info_EPPCash_NO_Table($produtoModelo[1]->getValorEPPCash());
					//	}
					//}
					?>| R$ <?php echo number_format($produtoModelo[1]->getValor(), 2, ',', '.')?>
			  <?php }?>&nbsp;</nobr>
				  </td>
				  <td>&nbsp;</td>
				  <td align="center" class="textoEPPCASH">
			  <?php if($produtoModelo[2]->getAtivo() == $produto_ativo || ($produtoModelo[2]->getId()>0 && $b_show_treinamento)){ ?><?php
					//if(isset($usuarioGames)) {
					//	if($usuarioGames->b_IsLogin_valorPINEPPCash()) {
							echo get_info_EPPCash_NO_Table($produtoModelo[2]->getValorEPPCash());
					//	}
					//}
					?>| R$ <?php echo number_format($produtoModelo[2]->getValor(), 2, ',', '.')?>
			  <?php }?>&nbsp;
				  </td>
			</tr>
			<!-- Comprar -->
			<tr>
			  <td align="center" class="texto" height="25">
			  <?php if($produtoModelo[0]->getAtivo() == $produto_ativo || ($produtoModelo[0]->getId()>0 && $b_show_treinamento)){ ?>
					<?php 
					if($produtoModelo[0]->contar($opr_codigo,$produtoModelo[0]->getPinValor())>0) {
					?>
					<input type="button" name="btOK" value="Comprar" OnClick="window.location='/prepag2/commerce/carrinho.php?acao=a&mod=<?php echo $produtoModelo[0]->getId()?><?php echo (($GLOBALS['codeProd'])?"&codeProd=".$GLOBALS['codeProd']:"")?>';" class="botao_simples">
					<?php 
					} else {
					?>
					<font color="#FF0000">Fora de Estoque</font>
					<?php 
					}
					?>
			  <?php } ?>&nbsp;
					</td>
				  <td>&nbsp;</td>
				  <td align="center" class="texto">
			  <?php if($produtoModelo[1]->getAtivo() == $produto_ativo || ($produtoModelo[1]->getId()>0 && $b_show_treinamento)){ ?>
					<?php 
					if($produtoModelo[1]->contar($opr_codigo,$produtoModelo[1]->getPinValor())>0) {
					?>
				  <input type="button" name="btOK" value="Comprar" OnClick="window.location='/prepag2/commerce/carrinho.php?acao=a&mod=<?php echo $produtoModelo[1]->getId()?><?php echo (($GLOBALS['codeProd'])?"&codeProd=".$GLOBALS['codeProd']:"")?>';" class="botao_simples">
					<?php 
					} else {
					?>
					<font color="#FF0000">Fora de Estoque</font>
					<?php 
					}
					?>
			  <?php } ?>&nbsp;
				  </td>
				  <td>&nbsp;</td>
				  <td align="center" class="texto">
			  <?php if($produtoModelo[2]->getAtivo() == $produto_ativo || ($produtoModelo[2]->getId()>0 && $b_show_treinamento)){ ?>
					<?php 
					if($produtoModelo[2]->contar($opr_codigo,$produtoModelo[2]->getPinValor())>0) {
					?>
				  <input type="button" name="btOK" value="Comprar" OnClick="window.location='/prepag2/commerce/carrinho.php?acao=a&mod=<?php echo $produtoModelo[2]->getId()?><?php echo (($GLOBALS['codeProd'])?"&codeProd=".$GLOBALS['codeProd']:"")?>';" class="botao_simples">
					<?php 
					} else {
					?>
					<font color="#FF0000">Fora de Estoque</font>
					<?php 
					}
					?>
			  <?php }?>&nbsp;
				  </td>
			</tr>
			<tr><td colspan="5">&nbsp;</td></tr>
<?php 
				//Limpa produtos
				$produtoModelo[0] = $produtoModelo[1] = $produtoModelo[2] = new ProdutoModelo();
			}
		}
		?></table><?php 
	}
}

$PRODS = array(1, 3, 4, 5, 11, 14);
if(in_array($prod, $PRODS)) {
?>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1903237-3";
urchinTracker();
</script>
        
<?php
}
                }
?>