<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

	$msg = "";

	if(!$modelo_id) $msg = "Código do modelo não fornecido.\n";
	elseif(!is_numeric($modelo_id)) $msg = "Código do modelo inválido.\n";


	//Processa acoes
	//----------------------------------------------------------------------------------------------------------
	if($msg == ""){

		if($BtnAtualizar){

			$ogpm_pin_valor_markup = ($opr_markup == "0")?str_replace(",", ".", $ogpm_valor):str_replace(",", ".", $ogpm_pin_valor_markup);
                        
			//cria objeto produto
			$modelo = new ProdutoModelo($modelo_id, $ogpm_ogp_id, $ogpm_nome, $ogpm_descricao, $ogpm_valor, $ogpm_perc_desconto, $ogpm_ativo, null, null, $ogpm_pin_valor, null, null, $ogpm_pin_resquest_id, $ogpm_pin_valor_markup);

			//valida campos e atualiza
                        $instProdutoModelo = new ProdutoModelo();
			$msgAcao = $instProdutoModelo->atualizar($modelo);
			if($msgAcao == "") $msgAcao = "Atualizado com sucesso.";
            
            /*
            Bloco para atualizar listagem de produtos
            */
            $filtro['opr'] = 1;
            $filtro['opr_status'] = '1';
            $filtro['ogp_codigo_negado'] = 39;
            $filtro['ogp_mostra_integracao_gamer_com_loja'] = '1'; // Wagner
            $filtro['ogp_ativo'] = 1;

            require_once  $raiz_do_projeto . "class/util/Busca.class.php";
            $arrJsonFiles = unserialize(ARR_PRODUTOS_CREDITOS);
            $busca = new Busca;
            $busca->setFullPath(DIR_JSON);
            $busca->setArrJsonFiles($arrJsonFiles);
            $instProduto = new Produto();
            $ret = $instProduto->obterMelhorado($filtro, null, $rs);

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
                        $produto->operadora                         = utf8_decode($rs_row['opr_nome_loja']);
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
		}

		if($acao){

			//excluir imagem
			if($acao == "ei"){
				$sql = "update tb_dist_operadora_games_produto_modelo set ogpm_nome_imagem = NULL
						where ogpm_id = " . $modelo_id;
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao atualizar modelo.\n";
			}
		}
		
		
	}


	//Mostra a pagina
	//----------------------------------------------------------------------------------------------------------

	//Recupera o modelo
	if($msg == ""){

		$filtro = array();
		$filtro['ogpm_id'] = $modelo_id;
		$rs_modelo = null;
                $instProdutoModelo = new ProdutoModelo();
		$ret = $instProdutoModelo->obter($filtro, null, $rs_modelo);
		if($ret != "") $msg = $ret;
		else if(!$rs_modelo || pg_num_rows($rs_modelo) == 0) $msg = "Nenhum modelo encontrado.\n";
		else {
			$rs_modelo_row = pg_fetch_array($rs_modelo);
			$ogpm_id 			= $rs_modelo_row['ogpm_id'];
			$ogpm_ogp_id 		= $rs_modelo_row['ogpm_ogp_id'];
			$ogpm_nome 			= $rs_modelo_row['ogpm_nome'];
			$ogpm_descricao 	= $rs_modelo_row['ogpm_descricao'];
			$ogpm_ativo 		= $rs_modelo_row['ogpm_ativo'];
			$ogpm_nome_imagem 	= $rs_modelo_row['ogpm_nome_imagem'];
			$ogpm_data_inclusao	= $rs_modelo_row['ogpm_data_inclusao'];
			$ogpm_valor 		= $rs_modelo_row['ogpm_valor'];
			$ogpm_perc_desconto	= $rs_modelo_row['ogpm_perc_desconto'];
			$ogpm_pin_valor 	= $rs_modelo_row['ogpm_pin_valor'];
                        $ogpm_pin_resquest_id	= $rs_modelo_row['ogpm_pin_resquest_id']; 
                        $ogpm_pin_valor_markup	= $rs_modelo_row['ogpm_pin_valor_markup'];
		}
	}	

	//Recupera o produto
	if($msg == ""){
		if($ogpm_ogp_id && is_numeric($ogpm_ogp_id)) {
			$filtro = array();
			$filtro['ogp_id'] = $ogpm_ogp_id;
			$filtro['opr'] = 1;
			$rs_produto = null;
                        $instProduto = new Produto();
			$ret = $instProduto->obterMelhorado($filtro, null, $rs_produto);
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
/*
			$sql  = "select distinct pin_valor from pins where opr_codigo = " . $ogp_opr_codigo . " and pin_canal='s' order by pin_valor";
			$rs_pins = SQLexecuteQuery($sql);
*/
			$sql  = "select opr_valor1, opr_valor2, opr_valor3, opr_valor4, opr_valor5, opr_valor6, opr_valor7, opr_valor8, opr_valor9, opr_valor10, opr_valor11, opr_valor12, opr_valor13, opr_valor14, opr_valor15, opr_valor16, opr_valor17, opr_valor18, opr_valor19, opr_valor20, opr_valor21, opr_markup from operadoras where opr_codigo = " . $ogp_opr_codigo . "";
//echo $sql."<br>".
			$rs_pins_opr = SQLexecuteQuery($sql);

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

	function abreUpload(modelo_id){
	
		url = "com_imagem_upload.php?modelo_id=" + modelo_id;
		janela = window.open(url, 'upload','top=200,left=200,width=500,height=200');
	
	}
  </script>
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
            <li><a href="com_produto_detalhe.php?produto_id=<?php echo $ogpm_ogp_id ?>">Voltar</a></li>
            <li class="active">Money Distribuidor - Modelo</li>
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
	<form name="form1" method="post" action="com_modelo_detalhe.php">
		<input type="hidden" name="modelo_id" value="<?php echo $modelo_id ?>">
		<input type="hidden" name="ogpm_ogp_id" value="<?php echo $ogpm_ogp_id ?>">

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
            <td colspan="2" bgcolor="#ECE9D8">Modelo</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>C&oacute;digo</b></td>
            <td><?php echo $ogpm_id ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Data de Inclusão</b></td>
            <td><?php echo formata_data_ts($ogpm_data_inclusao,0,true,true) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Imagem</b></td>
            <td>
				<a style="text-decoration:none" href="#" onClick="abreUpload('<?php echo $modelo_id ?>'); return false;">Nova imagem</a><br>
				<?php if($ogpm_nome_imagem && $ogpm_nome_imagem != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $ogpm_nome_imagem)){ ?>
					<img src="<?php echo $PREPAG_DOMINIO . $URL_DIR_IMAGES_PRODUTO . $ogpm_nome_imagem ?>" border="0">
					<br><a style="text-decoration:none" href="#" onClick="if(confirm('Deseja excluir esta imagem?')) window.location='com_modelo_detalhe.php?acao=ei&modelo_id=<?php echo $modelo_id ?>';return false;">Excluir imagem</a>
				<?php } ?>
			</td>
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
		  <!--input type="hidden" name="ogpm_pin_valor" value="0"-->
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>PIN da Operadora</b></td>
            <td>
				<select name="ogpm_pin_valor" class="form2">
					<option value="">Selecione</option>
					<?php 
						if($rs_pins_opr) {
							$rs_pins_opr_row = pg_fetch_array($rs_pins_opr); 
							for($i=1;$i<=21;$i++) {
								if($rs_pins_opr_row["opr_valor$i"]>0) {
					?>
									<option value="<?php echo $rs_pins_opr_row["opr_valor$i"]; ?>" <?php if ($ogpm_pin_valor == $rs_pins_opr_row["opr_valor$i"]) echo "selected";?>><?php echo number_format($rs_pins_opr_row["opr_valor$i"], 2, ',', '.'); ?></option>
					<?php 
								}
								if($i>21) break;
							}
                                                        $opr_markup = $rs_pins_opr_row["opr_markup"];
						} 
					?>

				</select> (aqueles que aparecem no cadastro da operadora)
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Valor</b></td>
            <td><input name="ogpm_valor" type="text" class="form2" value="<?php echo number_format($ogpm_valor,2,',','.') ?>" size="7" maxlength="7"></td>
          </tr>
          <?php
          if($opr_markup) {
          ?>   
          <tr bgcolor="#F5F5FB"> 
            <td><b>Valor SEM Markup</b></td>
            <td><input name="ogpm_pin_valor_markup" id="ogpm_pin_valor_markup" type="text" class="form2" value="<?php echo number_format($ogpm_pin_valor_markup,2,',','.'); ?>" size="7" maxlength="7"></td>
          </tr>
          <?php
          }//end if($opr_markup) 
          else {
          ?>
		<input type="hidden" name="opr_markup" id="opr_markup" value="<?php echo $opr_markup; ?>">
          <?php
          }//end else do if($opr_markup) 
          ?>
		  <input type="hidden" name="ogpm_perc_desconto" value="0,00">
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

        <table class="table">
		  <tr bgcolor="#F5F5FB">
			<td colspan="2" align="center"><input type="submit" name="BtnAtualizar" value="Atualizar" class="btn btn-sm btn-info"></td>
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
