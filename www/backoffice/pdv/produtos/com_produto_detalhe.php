<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."class/util/Util.class.php";

$msg = "";

if(!$produto_id) $msg = "Código do produto não fornecido.\n";
elseif(!is_numeric($produto_id)) $msg = "Código do produto inválido.\n";


//Processa acoes
//----------------------------------------------------------------------------------------------------------
if($msg == ""){

	if(isset($BtnAtualizar) && $BtnAtualizar){
            
                if(!$check_valor_variavel){
                    $ogp_valor_minimo = $ogp_valor_maximo = NULL;
                }else{
                    $ogp_valor_minimo = !empty($ogp_valor_minimo) ? Util::getNumero($ogp_valor_minimo, true) : NULL;
                    $ogp_valor_maximo = !empty($ogp_valor_maximo) ? Util::getNumero($ogp_valor_maximo, true) : NULL;
                }

		//Tirando as aspas duplas da edição
		$ogp_descricao = str_replace("'",'"',$ogp_descricao);

		//cria objeto produto
		$produto = new Produto($produto_id, $ogp_nome, $ogp_descricao, $ogp_descricao_api,$ogp_ativo, null, null, $ogp_opr_codigo, $ogp_mostra_integracao_gamer, $ogp_iof, $ogp_inibi_lojas_online, null, $ogp_pin_request, (isset($ogp_comunicacao_cupom)?$ogp_comunicacao_cupom:""), $ogp_valor_minimo, $ogp_valor_maximo, $ogp_idade_minima);

		//valida campos e atualiza
                $instProduto = new Produto();
		$msgAcao = $instProduto->atualizar($produto);
		if($msgAcao == ""){
                    $msgAcao = "Atualizado com sucesso.";
                    
                    require_once  $raiz_do_projeto."class/util/Busca.class.php";
                    //require_once $raiz_do_projeto."/www/web/prepag2/b2c/config.inc.b2c.php";
                    //para voltar com os produtos b2c, descomente a linha acima

                    //array com todos os produtos listados
                    $arrProduto = array();

                    $rs = null;
                    $filtro['opr'] = 1;
                    $filtro['opr_status'] = '1';
                    $filtro['ogp_codigo_negado'] = 39;
                    $filtro['ogp_mostra_integracao_gamer_com_loja'] = '1'; // Wagner
                    $filtro['ogp_ativo'] = 1;

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
                                $produto->nome                              = htmlentities(utf8_encode($rs_row['ogp_nome']));
                                $produto->busca                             = htmlentities(strip_tags(Util::cleanStr2(utf8_encode($rs_row['ogp_nome'])." | ".utf8_encode($rs_row['opr_nome_loja'])))); //corrigir traducao dew caracter q nao ta funfando
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
                }
	}
	
	if($acao){

		//excluir imagem
		if($acao == "ei"){
			$sql = "update tb_dist_operadora_games_produto set ogp_nome_imagem = NULL
					where ogp_id = " . $produto_id;
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao atualizar produto.\n";
            else $msgSuc = "Imagem do Produto excluida com sucesso.\n";
		}

		//excluir modelo
		if($acao == "e"){
                        $instProdutoModelo = new ProdutoModelo();
			$msgAcao = $instProdutoModelo->excluir($modelo_id);
			//if($msgAcao == "") $msgAcao = "Modelo excluido com sucesso.";
		}
	}
    
    if(isset($_GET['msg'])){
            $msgSuc = "Imagem do Produto atualizada com sucesso.\n";
        }
	
}


//Mostra a pagina
//----------------------------------------------------------------------------------------------------------

//Recupera o produto
if($msg == ""){

	$filtro = array();
	$filtro['ogp_id'] = $produto_id;
	$filtro['opr'] = 1;
	$rs_produto = null;
        $instProduto = new Produto();
	$ret = $instProduto->obtermelhorado($filtro, null, $rs_produto);
	if($ret != "") $msg = $ret;
	else if(!$rs_produto || pg_num_rows($rs_produto) == 0) $msg = "Nenhum produto encontrado.\n";
	else {
                        $rs_produto_row = pg_fetch_array($rs_produto);
                        $ogp_id 		= $rs_produto_row['ogp_id'];
                        $ogp_nome 		= $rs_produto_row['ogp_nome'];
                        $ogp_descricao 		= $rs_produto_row['ogp_descricao'];
						$ogp_descricao_api = $rs_produto_row['ogp_descricao_api'];
                        $ogp_ativo 		= $rs_produto_row['ogp_ativo'];
                        $ogp_nome_imagem 	= $rs_produto_row['ogp_nome_imagem'];
                        $ogp_data_inclusao 	= $rs_produto_row['ogp_data_inclusao'];
                        $ogp_opr_codigo 	= $rs_produto_row['ogp_opr_codigo'];
                        $opr_status 		= $rs_produto_row['opr_status'];
                        $opr_nome 		= $rs_produto_row['opr_nome'];
                        $ogp_mostra_integracao_gamer= $rs_produto_row['ogp_mostra_integracao_gamer'];
                        $ogp_iof		= $rs_produto_row['ogp_iof'];
                        $ogp_inibi_lojas_online = $rs_produto_row['ogp_inibi_lojas_online'];
                        $ogp_pin_request	= $rs_produto_row['ogp_pin_request'];
                        $ogp_comunicacao_cupom  = $rs_produto_row['ogp_comunicacao_cupom'];
                        $ogp_valor_minimo       = $rs_produto_row['ogp_valor_minimo'];
                        $ogp_valor_maximo       = $rs_produto_row['ogp_valor_maximo'];
                        $ogp_idade_minima       = $rs_produto_row['ogp_idade_minima'];
	}
}	

//Recupera o modelos
if($msg == ""){

	if($ogp_id && is_numeric($ogp_id)) {
		$filtro = array();
		$filtro['ogpm_ogp_id'] = $ogp_id;
		$rs_produto_modelos = null;
                $instProdutoModelo = new ProdutoModelo();
		$ret = $instProdutoModelo->obter($filtro, "ogpm_valor ASC", $rs_produto_modelos);
		if($ret != "") $msg = $ret;
	}
}	

$msg = $msgAcao . $msg;

$cor1 = "#F5F5FB";
$cor2 = "#F5F5FB";
$cor3 = "#FFFFFF"; 	

$hide_input_valor = true;
if($ogp_valor_minimo || $ogp_valor_maximo){
    $hide_input_valor = false;
}

ob_end_flush();
?>
<script language="javascript">

function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}

function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function abreUpload(produto_id){

	url = "com_imagem_upload.php?produto_id=" + produto_id;
	janela = window.open(url, 'upload','top=200,left=200,width=500,height=200');

}
</script>
<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		plugins : "safari,pagebreak,layer,advhr,iespell,insertdatetime,preview,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
		theme_advanced_font_sizes : "8px,9px,10px,11px,12px,13px,14px,15px,16px,24px,36px",
		theme_advanced_buttons1_add : "|,justifyleft,justifycenter,justifyright,justifyfull,fontselect,fontsizeselect",
		theme_advanced_buttons3_add_before : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,insertdate,inserttime,charmap,preview,fullscreen,|,forecolor,backcolor",
		
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",

		content_css : "/js/tiny_mce/css/content.css",

		template_external_list_url : "/js/tiny_mce/lists/template_list.js",
		external_link_list_url : "/js/tiny_mce/lists/link_list.js",
		external_image_list_url : "/js/tiny_mce/lists/image_list.js",
		media_external_list_url : "/js/tiny_mce/lists/media_list.js",

		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		},
		
		translate_mode : true,
		language : "pt"
	});
    
    $(function(){
       $("#tokenUrl").click(function(){
           var dados = {cript : true, produto : <?php echo $produto_id;?>};
           
           $.ajax({
				type: "POST",
				url: "/ajax/geradorToken.php",
				data: dados,
				success: function(txt) {
					$("#token").html("?token="+txt).show();
				}
			});
            
           
           
       }) 
    });
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
		
	<?php if($msg != "" && !isset($msgSuc)){?>
        <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr><td align="center"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif">
              <?php echo str_replace("\n", "<br>", $msg);
              ?>
                  </font></td></tr>
		</table>
	<?php }?>
        
    <?php if($msgSuc != "" && $msg == ""){?>
        <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr><td align="center"><font color="#006400" size="2" face="Arial, Helvetica, sans-serif">
              <?php echo str_replace("\n", "<br>", $msgSuc);
              ?>
                  </font></td></tr>
		</table>
	<?php }?>

        <div class="error-list">

        </div>
        
	<form name="form1" id="form_cadastro" method="post" action="com_produto_detalhe.php">
		<input type="hidden" name="produto_id" value="<?php echo $produto_id ?>">
        <div class="col-md-5 col-md-offset-5">
            <p style="text-align: right; display: none;" id="token"></p>
        </div>
        <div class="col-md-2">
            <a href="javascript:void(0);" id="tokenUrl" class="btn pull-right btn-sm btn-info">Gerar URL Token</a>
        </div>
        <div class="col-md-12">
        <table class="table top10">
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
            <td width="150"><b>Imagem</b></td>
            <td valign="middle">
				<a style="text-decoration:none" href="#" onClick="abreUpload('<?php echo $produto_id ?>'); return false;">Nova imagem</a><br>
				<?php if($ogp_nome_imagem && $ogp_nome_imagem != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $ogp_nome_imagem)){ ?>
					<img src="http://<?php echo $_SERVER['SERVER_NAME'] . $URL_DIR_IMAGES_PRODUTO . $ogp_nome_imagem ?>" border="0">
					<br><a style="text-decoration:none" href="#" onClick="if(confirm('Deseja excluir esta imagem?')) window.location='com_produto_detalhe.php?acao=ei&produto_id=<?php echo $produto_id ?>';return false;">Excluir imagem</a>
				<?php } ?>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Status</b></td>
            <td>
				<select name="ogp_ativo" class="form2">
					<option value="0" <?php if ($ogp_ativo == "0") echo "selected";?>>INATIVO</option>
					<option value="1" <?php if ($ogp_ativo == "1") echo "selected";?>>ATIVO</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Exibir (Prod. Integração)</b></td>
            <td>
				<select name="ogp_mostra_integracao_gamer" class="form2">
					<option value="0" <?php if ($ogp_mostra_integracao_gamer == "0") echo "selected";?>>NÃO</option>
					<option value="1" <?php if ($ogp_mostra_integracao_gamer == "1") echo "selected";?>>SIM</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>IOF (Incluso)</b></td>
            <td>
				<select name="ogp_iof" class="form2">
					<option value="0" <?php if ($ogp_iof == "0") echo "selected";?>>NÃO</option>
					<option value="1" <?php if ($ogp_iof == "1") echo "selected";?>>SIM</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Inibe Lojas para Vendas ONLINE?</b></td>
            <td>
				<select name="ogp_inibi_lojas_online" class="form2">
					<option value="0" <?php if ($ogp_inibi_lojas_online == "0") echo "selected";?>>NÃO</option>
					<option value="1" <?php if ($ogp_inibi_lojas_online == "1") echo "selected";?>>SIM</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Nome</b></td>
            <td><input name="ogp_nome" type="text" class="form2" value="<?php echo $ogp_nome ?>" size="25" maxlength="100"></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Idade mínima para venda</b></td>
            <td>
                <input name="ogp_idade_minima" type="number" class="form2" value="<?php echo $ogp_idade_minima ?>" min="0" step="1">
                <small style="color:red;">(Deixe vazio, ou com valor 0, caso o produto não exija idade mínima)</small>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Descrição</b></td>
            <td><textarea name="ogp_descricao" cols="80" rows="15" class="form2"><?php echo $ogp_descricao ?></textarea></td>
          </tr>
		  
		  <tr bgcolor="#F5F5FB">
			<td><b>Descrição API</b></td>
			<td><textarea name="ogp_descricao_api" cols="80" rows="15" class="form2"><?php echo $ogp_descricao_api ?></textarea></td>
		  </tr>
<?php
            if($ogp_pin_request == "0"){
?>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Comunicação Cupom</b></td>
            <td><textarea name="ogp_comunicacao_cupom" cols="80" rows="15" class="form2"><?php echo $ogp_comunicacao_cupom ?></textarea></td>
          </tr>
<?php
            }
?>
          <tr bgcolor="#F5F5FB">
            <td><b>Solicita PINs via Webservice?</b></td>
            <td>
				<select name="ogp_pin_request" class="form2" id="select_webservice">
					<option value="0" <?php if ($ogp_pin_request == "0") echo "selected";?>>NÃO</option>
					<option value="1" <?php if ($ogp_pin_request == "1") echo "selected";?>>SIM - BlackHawk</option>
				</select>
			</td>
          </tr>
<?php
        if ($ogp_pin_request == "1"){
?>
            <tr bgcolor="#F5F5FB">
                  <td>
                      <b>Produto possui valor variável</b>
                  </td>
                  <td>
                      <input type="checkbox" name="check_valor_variavel" value="true" id="valor_variavel" <?php if(!$hide_input_valor) echo "checked"; ?>>
                  </td>
            </tr>
            <tr class="input_valor <?php if($hide_input_valor) echo "hide"; ?>" bgcolor="#F5F5FB">
                  <td><b>Valor Mínimo</b></td>
                  <td>R$ <input name="ogp_valor_minimo" type="text" class="form2 money" placeholder="0,00" value="<?php echo $ogp_valor_minimo ? $ogp_valor_minimo : NULL ?>"></td>
            </tr>
            <tr class="input_valor <?php if($hide_input_valor) echo "hide"; ?>" bgcolor="#F5F5FB">
                  <td><b>Valor Máximo</b></td>
                  <td>R$ <input name="ogp_valor_maximo" type="text" class="form2 money" placeholder="0,00" value="<?php echo $ogp_valor_maximo ? $ogp_valor_maximo : NULL ?>"></td>
            </tr>
<?php
        }else{
            
?>
            <tr bgcolor="#F5F5FB" class="hide" id="mostrar_valor_variavel">
                  <td>
                      <b>Produto possui valor variável</b>
                  </td>
                  <td>
                      <input type="checkbox" name="check_valor_variavel" value="true" id="valor_variavel" <?php if(!$hide_input_valor) echo "checked"; ?>>
                  </td>
            </tr>
            <tr class="input_valor <?php if($hide_input_valor) echo "hide"; ?>" bgcolor="#F5F5FB">
                  <td><b>Valor Mínimo</b></td>
                  <td>R$ <input name="ogp_valor_minimo" type="text" class="form2 money" placeholder="0,00" value="<?php echo $ogp_valor_minimo ? $ogp_valor_minimo : NULL ?>"></td>
            </tr>
            <tr class="input_valor <?php if($hide_input_valor) echo "hide"; ?>" bgcolor="#F5F5FB">
                  <td><b>Valor Máximo</b></td>
                  <td>R$ <input name="ogp_valor_maximo" type="text" class="form2 money" placeholder="0,00" value="<?php echo $ogp_valor_maximo ? $ogp_valor_maximo : NULL ?>"></td>
            </tr>
<?php
        }
?>
		</table>
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
			<td colspan="2" align="right">
                <input type="hidden" name="ogp_opr_codigo" value="<?php echo $ogp_opr_codigo; ?>"> 
                <input type="submit" name="BtnAtualizar" value="Atualizar" class="btn btn-info btn-sm">
            </td>
		  </tr>
		</table>
        </div>
	</form>

        <table class="table">
          <tr bgcolor="#FFFFFF">
            <td colspan="2" bgcolor="#ECE9D8">Modelos</font></td>
          </tr>
          <tr>
		  	<td>
                <table class="table">
					<tr bgcolor="F0F0F0" class="texto">
					  <td align="center"><b>Cód</b></td>
					  <td align="center"><b>Nome</b></td>
					  <td align="center"><b>Status</b></td>
					  <td align="center"><b>Preço Unitário</b></td>
					  <td align="center"><b>Valor do PIN</b></td>
					  <td align="center"><b>Excluir</b></td>
					</tr>
		<?php
				if($rs_produto_modelos)
				while ($rs_produto_modelos_row = pg_fetch_array($rs_produto_modelos)){
		?>
					<tr class="texto" bgcolor="#F5F5FB">
					  <td align="center"><a style="text-decoration:none" href="com_modelo_detalhe.php?modelo_id=<?php echo $rs_produto_modelos_row['ogpm_id'] ?>"><?php echo $rs_produto_modelos_row['ogpm_id'] ?></a></td>
					  <td align="center"><a style="text-decoration:none" href="com_modelo_detalhe.php?modelo_id=<?php echo $rs_produto_modelos_row['ogpm_id'] ?>"><?php echo $rs_produto_modelos_row['ogpm_nome'] ?></a></td>
					  <td align="center"><?php echo ($rs_produto_modelos_row['ogpm_ativo'] == 1)?("Ativo"):("Inativo") ?></td>
					  <td align="center"><?php echo number_format($rs_produto_modelos_row['ogpm_valor'], 2, ',', '.') ?></td>
					  <td align="center"><?php echo number_format($rs_produto_modelos_row['ogpm_pin_valor'], 2, ',', '.') ?></td>
                      <td align="center" width="70"><a href="#" class="btn btn-sm btn-danger" onClick="if(confirm('Deseja excluir este modelo?')) window.location='com_produto_detalhe.php?acao=e&produto_id=<?php echo $produto_id ?>&modelo_id=<?php echo $rs_produto_modelos_row['ogpm_id'] ?>';return false;">Excluir</a></td>
					</tr>
			<?php	} ?>
				</table>
			</td>
		  </tr>
          <tr>
              <td colspan="2" align="right"><a href="com_modelo_detalhe_insere.php?produto_id=<?php echo $produto_id ?>" class="btn btn-sm btn-info">Inserir novo modelo</a></td>
          </tr>
		</table>

    </td>
  </tr>
</table>

<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script>

$(document).ready(function(){
    
    $('.money').mask("#.##0,00", {reverse: true});
    
    $("#valor_variavel").change(function(){
        if($("#valor_variavel").is(":checked")){
            $(".input_valor").removeClass("hide");
        }else{
            $(".input_valor").addClass("hide");

        }
    }); 
    
    $("#select_webservice").change(function(){
        if($(this).val() == 1){
            $("#mostrar_valor_variavel").removeClass("hide");
        }else{
            $("#mostrar_valor_variavel").addClass("hide");
        }
    }); 
    
    $("#form_cadastro").submit(function(e){
        var html = "";

        if($("#valor_variavel").is(":checked")){

            if(verifica_valores()){
                e.preventDefault();
                html += '<div class="alert alert-danger alert-dismissible" role="alert">'; 
                    html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                    html += '<span aria-hidden="true">&times;</span></button>'; 
                    html += '<strong>O valor mínimo não pode ser maior que o valor máximo.</strong></div> ';
            } 

            if($('input[name="ogp_valor_minimo"]').val() === "" || $('input[name="ogp_valor_maximo"]').val() === ""){
                e.preventDefault();
                html += '<div class="alert alert-danger alert-dismissible" role="alert">'; 
                    html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                    html += '<span aria-hidden="true">&times;</span></button>'; 
                    html += '<strong>Informe os valores mínimo e máximo, ou desmarque a opção de valor variável.</strong></div> ';
            }

            if(parseFloat($('input[name="ogp_valor_minimo"]').val()) === 0.0 || parseFloat($('input[name="ogp_valor_maximo"]').val()) === 0.0){
                e.preventDefault();
                html += '<div class="alert alert-danger alert-dismissible" role="alert">'; 
                    html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                    html += '<span aria-hidden="true">&times;</span></button>'; 
                    html += '<strong>Informe valores acima de 0 para os valores mínimo e máximo, ou desmarque a opção de valor variável.</strong></div> ';
            }
        }

        $(".error-list").html(html);
        html = "";
    });

    function verifica_valores(){
        var minimo = parseFloat($('input[name="ogp_valor_minimo"]').val().replace(".",""));
        var maximo = parseFloat($('input[name="ogp_valor_maximo"]').val().replace(".",""));
        if(minimo < maximo) return false;
        else return true;
    }
});

</script>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
