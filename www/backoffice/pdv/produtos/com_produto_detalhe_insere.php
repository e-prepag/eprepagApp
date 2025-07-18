<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once "/www/includes/bourls.php";

$msg = "";

//Processa acoes
//----------------------------------------------------------------------------------------------------------
if($msg == ""){

	if(isset($BtnInserir) && $BtnInserir){
            
                if(!$check_valor_variavel){
                    $ogp_valor_minimo = $ogp_valor_maximo = NULL;
                }else{
                    $ogp_valor_minimo = !empty($ogp_valor_minimo) ? Util::getNumero($ogp_valor_minimo, true) : NULL;
                    $ogp_valor_maximo = !empty($ogp_valor_maximo) ? Util::getNumero($ogp_valor_maximo, true) : NULL;
                }

		//cria objeto produto
				
                $produto = new Produto($produto_id, $ogp_nome, $ogp_descricao,null, $ogp_ativo, null, null, $ogp_opr_codigo, $ogp_mostra_integracao_gamer,$ogp_iof, 1,null, $ogp_pin_request, null, $ogp_valor_minimo, $ogp_valor_maximo, $ogp_idade_minima);

				if($_SERVER["REMOTE_ADDR"] == "201.93.162.169") {
					var_dump($produto);
				}
				

		//valida campos e insere
                $instProduto = new Produto();
		$msgAcao = $instProduto->inserir($produto);
		if($msgAcao == ""){
                    //redireciona
                    $strRedirect = "com_produto_detalhe.php?produto_id=" . $produto->getId();
                        
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
                    
			ob_end_clean();
		?><html><body onload="window.location='<?php echo $strRedirect?>'"><?php
			exit;
		}
	}
	
}


//Mostra a pagina
//----------------------------------------------------------------------------------------------------------

//Operadoras
if($msg == ""){
	$sql  = "select * from operadoras ope order by opr_nome ";
	$rs_operadoras = SQLexecuteQuery($sql);
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
        <table class="table">
          <tr><td align="center"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"><?php echo str_replace("\n", "<br>", $msg) ?></font></td></tr>
		</table>
	<?php }?>

	<form id="form_cadastro" name="form1" method="post" action="com_produto_detalhe_insere.php">

        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Produto</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Status</b></td>
            <td>
				<select name="ogp_ativo" class="form2">
					<option value="0" <?php if ($ogp_ativo == "0") echo "selected";?>>Inativo</option>
					<option value="1" <?php if ($ogp_ativo == "1") echo "selected";?>>Ativo</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>IOF (Incluso)</b></td>
            <td>
				<select name="ogp_iof" class="form2">
					<option value="0" <?php if ($ogp_iof == "0") echo "selected";?>>N�O</option>
					<option value="1" <?php if ($ogp_iof == "1") echo "selected";?>>SIM</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Inibe Lojas para Vendas ONLINE?</b></td>
            <td>
				<select name="ogp_inibi_lojas_online" class="form2">
					<option value="0" <?php if ($ogp_inibi_lojas_online == "0") echo "selected";?>>N�O</option>
					<option value="1" <?php if ($ogp_inibi_lojas_online == "1") echo "selected";?>>SIM</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Exibir (Prod. Integra��o)</b></td>
            <td>
				<select name="ogp_mostra_integracao_gamer" class="form2">
					<option value="0" <?php if ($ogp_mostra_integracao_gamer == "0") echo "selected";?>>N�O</option>
					<option value="1" <?php if ($ogp_mostra_integracao_gamer == "1") echo "selected";?>>SIM</option>
				</select>
			</td>
          </tr>
		  <tr bgcolor="#F5F5FB"> 
            <td><b>Nome</b></td>
            <td><input name="ogp_nome" type="text" class="form2" value="<?php echo $ogp_nome ?>" size="25" maxlength="100"></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Descri��o</b></td>
            <td><textarea name="ogp_descricao" cols="80" rows="8" class="form2"><?php echo $ogp_descricao ?></textarea></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Idade m�nima para venda</b></td>
            <td>
                <input name="ogp_idade_minima" type="number" class="form2" value="<?php echo $ogp_idade_minima ?>" min="0" step="1">
                <small style="color:red;">(Deixe vazio, ou com valor 0, caso o produto n�o exija idade m�nima)</small>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Solicita PINs via Webservice?</b></td>
            <td>
				<select name="ogp_pin_request" class="form2">
					<option value="0" <?php if ($ogp_pin_request == "0") echo "selected";?>>N�O</option>
					<option value="1" <?php if ($ogp_pin_request == "1") echo "selected";?>>SIM - BlackHawk</option>
				</select>
			</td>
          </tr>
            <tr bgcolor="#F5F5FB">
                  <td>
                      <b>Produto possui valor vari�vel</b>
                  </td>
                  <td>
                      <input type="checkbox" name="check_valor_variavel" value="true" id="valor_variavel" <?php if(!$hide_input_valor) echo "checked"; ?>>
                  </td>
            </tr>
            <tr class="input_valor <?php if($hide_input_valor) echo "hide"; ?>" bgcolor="#F5F5FB">
                  <td><b>Valor M�nimo</b></td>
                  <td>R$ <input name="ogp_valor_minimo" type="text" class="form2 money" placeholder="0,00" value="<?php echo $ogp_valor_minimo ? $ogp_valor_minimo : NULL ?>"></td>
            </tr>
            <tr class="input_valor <?php if($hide_input_valor) echo "hide"; ?>" bgcolor="#F5F5FB">
                  <td><b>Valor M�ximo</b></td>
                  <td>R$ <input name="ogp_valor_maximo" type="text" class="form2 money" placeholder="0,00" value="<?php echo $ogp_valor_maximo ? $ogp_valor_maximo : NULL ?>"></td>
            </tr>
		</table>

        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8" class="texto">Operadora</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Operadora</b></td>
            <td>
				<select name="ogp_opr_codigo" class="form2">
					<?php if($rs_operadoras) while($rs_operadoras_row = pg_fetch_array($rs_operadoras)){ ?>
					<option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>" <?php if ($ogp_opr_codigo == $rs_operadoras_row['opr_codigo']) echo "selected";?>><?php echo $rs_operadoras_row['opr_nome']; ?></option>
					<?php } ?>
				</select>
			</td>
		  </tr>
		</table>

        <table class="table">
		  <tr bgcolor="#F5F5FB">
			<td colspan="2" align="right"><input type="submit" name="BtnInserir" value="Inserir" class="btn btn-sm btn-info"></td>
		  </tr>
		</table>

	</form>

    </td>
  </tr>
</table>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/jquery.mask.min.js"></script>
<script>

    $(document).ready(function(){
        
        //$('.money').mask("0.000,00", {reverse: true});
        
        $('.money').mask("#.##0,00", {reverse: true});
       
        $("#valor_variavel").change(function(){
            if($("#valor_variavel").is(":checked")){
                $(".input_valor").removeClass("hide");
            }else{
                $(".input_valor").addClass("hide");
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
                        html += '<strong>O valor m�nimo n�o pode ser maior que o valor m�ximo.</strong></div> ';
                } 
                
                if($('input[name="ogp_valor_minimo"]').val() === "" || $('input[name="ogp_valor_maximo"]').val() === ""){
                    e.preventDefault();
                    html += '<div class="alert alert-danger alert-dismissible" role="alert">'; 
                        html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                        html += '<span aria-hidden="true">&times;</span></button>'; 
                        html += '<strong>Informe os valores m�nimo e m�ximo, ou desmarque a op��o de valor vari�vel.</strong></div> ';
                }
                
                if(parseFloat($('input[name="ogp_valor_minimo"]').val()) === 0.0 || parseFloat($('input[name="ogp_valor_maximo"]').val()) === 0.0){
                    e.preventDefault();
                    html += '<div class="alert alert-danger alert-dismissible" role="alert">'; 
                        html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                        html += '<span aria-hidden="true">&times;</span></button>'; 
                        html += '<strong>Informe valores acima de 0 para os valores m�nimo e m�ximo, ou desmarque a op��o de valor vari�vel.</strong></div> ';
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
