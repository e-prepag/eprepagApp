<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

$instProd = new Produto();
$instProdMod = new ProdutoModelo();

	$msg = "";

	if(!$produto_id) $msg = "Código do produto não fornecido.\n";
	elseif(!is_numeric($produto_id)) $msg = "Código do produto inválido.\n";

	//Processa acoes
	//----------------------------------------------------------------------------------------------------------
	if($msg == ""){

		if($BtnInserir){
			//cria objeto modelo
                        $instConversion = new ConversionPINsEPP();
			$ogpm_pin_valor_epp = $instConversion->get_ValorEPPCash('E', $ogpm_pin_valor);
                        $ogpm_pin_valor_markup = str_replace(",", ".", $ogpm_pin_valor_markup)*1;
			$ogpm_pin_valor_markup = empty($ogpm_pin_valor_markup)?str_replace(",", ".", $ogpm_valor):str_replace(",", ".", $ogpm_pin_valor_markup);

			$modelo = new ProdutoModelo($modelo_id, $produto_id, $ogpm_nome, $ogpm_descricao, $ogpm_valor, $ogpm_ativo, null, null, $ogpm_pin_valor, $ogpm_pin_valor_epp, $ogpm_pin_resquest_id, $ogpm_pin_valor_markup);

			//valida campos e insere
			$msgAcao = $instProdMod->inserir($modelo);
			if($msgAcao == ""){

                /*
                Bloco para atualizar listagem de produtos
                */
                $filtro['opr'] = 1;
                $filtro['opr_status'] = '1';
                $filtro['ogp_ativo'] = 1;
                $filtro['ogp_mostra_integracao_com_loja'] = '1';

                   require_once  $raiz_do_projeto."class/util/Busca.class.php";
                   $arrJsonFiles = unserialize(ARR_PRODUTOS_GAMER);
                   $busca = new Busca;
                   $busca->setFullPath(DIR_JSON);
                   $busca->setArrJsonFiles($arrJsonFiles);

                   $ret = $instProd->obterMelhorado($filtro, null, $rs);

                   if($rs && pg_num_rows($rs) > 0)
                   {
                       for($i=0; $rs_row = pg_fetch_array($rs); $i++)
                       {
                           if(!empty($rs_row['ogp_nome']))
                           {
                               $produto                                    = new stdClass();
                               $produto->tipo                              = "games";
                               $produto->id                                = $rs_row['ogp_id'];
                               $produto->nome                              = htmlentities($rs_row['ogp_nome']);
                               $produto->busca                             = htmlentities(strip_tags(Util::cleanStr2($rs_row['ogp_nome']." | ".$rs_row['opr_nome_loja']))); //corrigir traducao dew caracter q nao ta funfando
                               $produto->imagem                            = $rs_row['ogp_nome_imagem'];
                               $produto->operadora                         = $rs_row['opr_nome_loja'];
                               $produto->filtro['ogp_inibi_lojas_online']  = $rs_row['ogp_inibi_lojas_online'];

                               $arrTemp['games'][] = $produto;

                               unset($produto);
                           }
                       }
                   }
                   $busca->setProduto($arrTemp);
                   unset($arrTemp);

                   ////para voltar com os produtos b2c, descomente o bloco acima
                   $busca->geraJson();

                   /*
                       Fim do bloco para atualizar listagem de produtos
                    */
                
				//redireciona
				$strRedirect = "com_modelo_detalhe.php?modelo_id=" . $modelo->getId();
				ob_end_clean();
				?><html><body onload="window.location='<?php echo $strRedirect?>'"><?php
				exit;

			}
		}
		
	}


	//Mostra a pagina
	//----------------------------------------------------------------------------------------------------------

	//Recupera o produto
	if($msg == ""){
		if($produto_id && is_numeric($produto_id)) {
			$filtro = array();
			$filtro['ogp_id'] = $produto_id;
			$filtro['opr'] = 1;
			$rs_produto = null;
			$ret = $instProd->obtermelhorado($filtro, null, $rs_produto);
			if($ret != "") $msg = $ret;
			else if(!$rs_produto || pg_num_rows($rs_produto) == 0) $msg = "Nenhum produto encontrado.\n";
			else {
				$rs_produto_row = pg_fetch_array($rs_produto);
				$ogp_id 			= $rs_produto_row['ogp_id'];
				$ogp_nome 			= $rs_produto_row['ogp_nome'];
				$ogp_descricao 		= $rs_produto_row['ogp_descricao'];
				$ogp_ativo 			= $rs_produto_row['ogp_ativo'];
				$ogp_nome_imagem 	= $rs_produto_row['ogp_nome_imagem'];
				$ogp_data_inclusao 	= $rs_produto_row['ogp_data_inclusao'];
				$ogp_opr_codigo 	= $rs_produto_row['ogp_opr_codigo'];
				$opr_status 		= $rs_produto_row['opr_status'];
				$opr_nome 			= $rs_produto_row['opr_nome'];
                                $ogp_pin_request	= $rs_produto_row['ogp_pin_request'];
			}
		}
	}	

	//Recupera o pins
	if($msg == ""){
		if($ogp_opr_codigo && is_numeric($ogp_opr_codigo)) {

			$sql = "SELECT valor AS pin_valor
        FROM operadoras_valores
        WHERE opr_codigo = $ogp_opr_codigo
        AND (valor > 0 OR (valor = 0 AND opr_codigo = 78))
        ORDER BY valor";

			$rs_pins = SQLexecuteQuery($sql);
                        
                        $sql = "SELECT opr_markup FROM operadoras WHERE opr_codigo = $ogp_opr_codigo;";
                        $rs_markup = SQLexecuteQuery($sql);
                        $rs_markup_row = pg_fetch_array($rs_markup);
                        $opr_markup = $rs_markup_row["opr_markup"];
		}
	}
	

	$msg = $msgAcao . $msg;
	
	$cor1 = "#F5F5FB";
	$cor2 = "#F5F5FB";
	$cor3 = "#FFFFFF"; 	


ob_end_flush();
?>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td>
	<?php if($msg != ""){?>
        <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr><td align="center"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"><?php echo str_replace("\n", "<br>", $msg) ?></font></td></tr>
		</table>
	<?php }?>

        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Produto</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>C&oacute;digo</b></td>
            <td><?php echo $ogp_id ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Data de Inclusão</b></td>
            <td><?php echo formata_data_ts($ogp_data_inclusao,0,true,true) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Status</b></td>
            <td><?php echo ($ogp_ativo == 1)?("Ativo"):("Inativo") ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Nome</b></td>
            <td><?php echo $ogp_nome ?></td>
          </tr>
		</table>

        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8" class="texto">Operadora</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Operadora</b></td>
            <td><?php echo $opr_nome ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Código</b></td>
            <td><?php echo $ogp_opr_codigo ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Status</b></td>
            <td><?php echo ($opr_status == '1')?("Ativo"):("Inativo") ?></td>
		  </tr>
		</table>

	<form name="form1" method="post" action="com_modelo_detalhe_insere.php">
		<input type="hidden" name="produto_id" value="<?php echo $produto_id ?>">

        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Modelo</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Status</b></td>
            <td>
				<select name="ogpm_ativo" class="form2">
					<option value="0" <?php if ($ogpm_ativo == "0") echo "selected";?>>Inativo</option>
					<option value="1" <?php if ($ogpm_ativo == "1") echo "selected";?>>Ativo</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>PIN da Operadora</b></td>
            <td>
				<select name="ogpm_pin_valor" class="form2">
					<option value="">Selecione</option>
					<?php if($rs_pins) while($rs_pins_row = pg_fetch_array($rs_pins)){ ?>
					<option value="<?php echo $rs_pins_row['pin_valor']; ?>" <?php if ($ogpm_pin_valor == $rs_pins_row['pin_valor']) echo "selected";?>><?php echo number_format($rs_pins_row['pin_valor'],2,',','.'); ?></option>
					<?php } ?>
				</select> <nobr>(valores cadastrados na tabela de operadoras para <?php echo $opr_nome .", cód: ". $ogp_opr_codigo .""; ?>)</nobr>							
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Valor</b></td>
            <td><input name="ogpm_valor" type="text" class="form2" value="<?php if($ogpm_valor) echo number_format($ogpm_valor,2,',','.') ?>" size="7" maxlength="7"></td>
          </tr>
          <?php
          if($opr_markup) {
          ?>   
          <tr bgcolor="#F5F5FB"> 
            <td><b>Valor SEM Markup</b></td>
            <td><input name="ogpm_pin_valor_markup" id="ogpm_pin_valor_markup" type="text" class="form2" value="<?php if($ogpm_pin_valor_markup) echo number_format($ogpm_pin_valor_markup,2,',','.'); ?>" size="7" maxlength="7"></td>
          </tr>
          <?php
          }//end if($opr_markup) 
          else {
          ?>
		<input type="hidden" name="ogpm_pin_valor_markup" id="ogpm_pin_valor_markup" value="<?php if($ogpm_valor) echo number_format($ogpm_valor,2,',','.'); ?>">
          <?php
          }//end else do if($opr_markup) 
          ?>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Nome</b></td>
            <td><input name="ogpm_nome" type="text" class="form2" value="<?php echo $ogpm_nome ?>" size="25" maxlength="100"></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Descrição</b></td>
            <td><textarea name="ogpm_descricao" cols="80" rows="8" class="form2"><?php echo $ogpm_descricao ?></textarea></td>
          </tr>
<?php
        if($ogp_pin_request > 0) {
?>
          <tr bgcolor="#F5F5FB"> 
            <td><b>ID do Modelo/Produto no Parceiro Webservice</b></td>
            <td><input name="ogpm_pin_resquest_id" type="text" class="form2" value="<?php echo $ogpm_pin_resquest_id ?>" size="25" maxlength="100"></td>
          </tr>
<?php
        } //end if($ogp_pin_request > 0) 
        else {
?>
		<input type="hidden" name="ogpm_pin_resquest_id" value="">
<?php 
        }
?>
		</table>

        <table width="894" border="0" cellpadding="0" cellspacing="1" class="texto">
		  <tr bgcolor="#F5F5FB">
			<td colspan="2" align="center"><input type="submit" name="BtnInserir" value="Inserir" class="btn btn-sm btn-info"></td>
		  </tr>
		</table>

	</form>

    </td>
  </tr>
</table>
<?php
    require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
