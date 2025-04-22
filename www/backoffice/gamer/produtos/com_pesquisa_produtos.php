<?php 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."class/gamer/classIntegracao.php";

	$time_start = getmicrotime();
	
	if(!$ncamp)    $ncamp       = 'ogp_id';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 0;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}


	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100;	//$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&BtnSearch=1&tf_ogp_id=$tf_ogp_id&tf_ogp_ativo=$tf_ogp_ativo&tf_ogp_mostra_integracao=$tf_ogp_mostra_integracao";
	$varsel .= "&tf_ogp_data_inclusao_ini=$tf_ogp_data_inclusao_ini&tf_ogp_data_inclusao_fim=$tf_ogp_data_inclusao_fim";
	$varsel .= "&tf_ogp_nome=$tf_ogp_nome&tf_ogp_descricao=$tf_ogp_descricao";
	$varsel .= "&tf_opr_status=$tf_opr_status&tf_ogp_opr_codigo=$tf_ogp_opr_codigo";

	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//Produto
		//------------------------------------------------------------------
		//codigo
		if($msg == "")
			if($tf_ogp_id){
			
				if(!is_numeric($tf_ogp_id))
					$msg = "Código do produto deve ser numérico.\n";
			}
		//Data
		if($msg == "")
			if($tf_ogp_data_inclusao_ini || $tf_ogp_data_inclusao_fim){
				if(verifica_data($tf_ogp_data_inclusao_ini) == 0)	$msg = "A data de inclusão inicial do produto é inválida.\n";
				if(verifica_data($tf_ogp_data_inclusao_fim) == 0)	$msg = "A data de inclusão final do produto é inválida.\n";
			}
		//opr_codigo
		if($msg == "")
			if($ogp_opr_codigo){
			
				if(!is_numeric($ogp_opr_codigo))
					$msg = "O código da operadora deve ser numérico.\n";
			}
		//ogp_ativo
		if($msg == "")
			if($ogp_ativo){
			
				if(!is_numeric($ogp_ativo))
					$msg = "O código de ativo deve ser numérico.\n";
			}

		//Busca produtos
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){

			$filtro = array();
			if($tf_ogp_id) 							$filtro['ogp_id']				= $tf_ogp_id;
			if(!is_null($ogp_id_list) && ($ogp_id_list!=""))	{
				$filtro['ogp_id_list']	= get_lista_produtos_integracao();
			}
			if($tf_ogp_nome) 						$filtro['ogp_nomeLike']			= $tf_ogp_nome;
			if($tf_ogp_descricao) 					$filtro['ogp_descricaoLike']	= $tf_ogp_descricao;
			if(!is_null($tf_ogp_ativo) && ($tf_ogp_ativo!=""))	{
				$filtro['ogp_ativo']			= $tf_ogp_ativo;
			}
			if(!is_null($tf_ogp_mostra_integracao) && ($tf_ogp_mostra_integracao!=""))	{
				$filtro['ogp_mostra_integracao']= $tf_ogp_mostra_integracao;
			}
			if($tf_ogp_nome_imagem)					$filtro['ogp_nome_imagemLike']	= $tf_ogp_nome_imagem;
			if($tf_ogp_data_inclusao_ini && $tf_ogp_data_inclusao_fim) {
				$filtro['ogp_data_inclusaoMin'] = $tf_ogp_data_inclusao_ini;
				$filtro['ogp_data_inclusaoMax'] = $tf_ogp_data_inclusao_fim;
			}
			if(!is_null($tf_opr_status) && ($tf_opr_status!=""))	{
				$filtro['opr_status'] = $tf_opr_status;
			}
			if($tf_ogp_opr_codigo) $filtro['ogp_opr_codigo'] = $tf_ogp_opr_codigo;
			$filtro['opr'] = 1;

			$rs_produtos = null;
			//echo "<pre>".print_r($filtro,true)."</pre>";
            $classProd = new Produto();
			$ret = $classProd->obtermelhorado($filtro, null, $rs_produtos);
			if($ret != "") $msg = $ret;
			else {
				$total_table = pg_num_rows($rs_produtos); 

				if($total_table == 0) {
					$msg = "Nenhum produto encontrado.\n";
				} else {
					//Ordem
					$orderBy = $ncamp;
					if($ordem == 1){
						$orderBy .= " desc ";
						$img_seta = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
					} else {
						$orderBy .= " asc ";
						$img_seta = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
					}
			
					$orderBy .= " limit ".$max; 
					$orderBy .= " offset ".$inicial;
				
					$ret = $classProd->obtermelhorado($filtro, $orderBy, $rs_produtos);
					if($ret != "") $msg = $ret;
					else {
				
						if($max + $inicial > $total_table)
							$reg_ate = $total_table;
						else
							$reg_ate = $max + $inicial;
					}
				}
			}
				
		}
	}
	
	//Operadoras
	$sql  = "select * from operadoras ope order by opr_nome";
	$rs_operadoras = SQLexecuteQuery($sql);
	
	
ob_end_flush();
require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
$(document).ready(function(){

    var optDate = new Object();
    optDate.interval = 10000;

    setDateInterval('tf_ogp_data_inclusao_ini','tf_ogp_data_inclusao_fim',optDate);

});

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
    <td width="891" valign="top"> 
        <form name="form1" method="post" action="com_pesquisa_produtos.php">
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td><a href="com_produto_detalhe_insere.php" class="btn btn-sm btn-info">Novo</a></td>
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
		</table>
        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="6" bgcolor="#ECE9D8" class="texto">Produto</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Código</font></td>
            <td>
              	<input name="tf_ogp_id" type="text" class="form2" value="<?php echo $tf_ogp_id ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Status</font></td>
			<td>
				<select name="tf_ogp_ativo" class="form2">
					<option value="" <?php if($tf_ogp_ativo == "") echo "selected" ?>>Selecione</option>
					<option value="0" <?php if ($tf_ogp_ativo == "0") echo "selected";?>>Inativo</option>
					<option value="1" <?php if ($tf_ogp_ativo == "1") echo "selected";?>>Ativo</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Nome</font></td>
            <td>
              	<input name="tf_ogp_nome" type="text" class="form2" value="<?php echo $tf_ogp_nome ?>" size="25" maxlength="100">
			</td>
            <td class="texto">Descrição</font></td>
            <td>
              	<input name="tf_ogp_descricao" type="text" class="form2" value="<?php echo $tf_ogp_descricao ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data de Inclusão</font></td>
            <td class="texto">
              <input name="tf_ogp_data_inclusao_ini" type="text" class="form" id="tf_ogp_data_inclusao_ini" value="<?php echo $tf_ogp_data_inclusao_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_ogp_data_inclusao_fim" type="text" class="form" id="tf_ogp_data_inclusao_fim" value="<?php echo $tf_ogp_data_inclusao_fim ?>" size="9" maxlength="10">
			</td>
			<td class="texto">Exibido (Prod. Integração)</font></td>
			<td>
				<select name="tf_ogp_mostra_integracao" class="form2">
					<option value="" <?php if($tf_ogp_mostra_integracao == "") echo "selected" ?>>Selecione</option>
					<option value="0" <?php if ($tf_ogp_mostra_integracao == "0") echo "selected";?>>NÃO</option>
					<option value="1" <?php if ($tf_ogp_mostra_integracao == "1") echo "selected";?>>SIM</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Operadora</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Operadora</td>
            <td>
				<select name="tf_ogp_opr_codigo" class="form2">
					<option value="" <?php if($tf_ogp_opr_codigo == "") echo "selected" ?>>Selecione</option>
					<?php if($rs_operadoras) while($rs_operadoras_row = pg_fetch_array($rs_operadoras)){ ?>
					<option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>" <?php if ($tf_ogp_opr_codigo == $rs_operadoras_row['opr_codigo']) echo "selected";?>><?php echo $rs_operadoras_row['opr_nome']." (".$rs_operadoras_row['opr_codigo'].")"; ?></option>
					<?php } ?>
				</select>
			</td>
            <td class="texto">Status</font></td>
			<td>
				<select name="tf_opr_status" class="form2">
					<option value="" <?php if($tf_opr_status == "") echo "selected" ?>>Selecione</option>
					<option value="0" <?php if ($tf_opr_status == "0") echo "selected";?>>Inativo</option>
					<option value="1" <?php if ($tf_opr_status == "1") echo "selected";?>>Ativo</option>
				</select>
			</td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
		   <td class="texto" colspan="4" align="center">
				<input type="checkbox" name="ogp_id_list" id="ogp_id_list" value="1" <? if($ogp_id_list==1) echo "checked" ?>>
				Exibir somente os produtos cadastrados no vetor da integração.
		   </td>
		  </tr>
		</table>
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>

		<?php if($total_table > 0) { ?>
        <table class="table">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
                    <table class="table">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr  bgcolor="#ECE9D8"> 
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ogp_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.</a> 
                          <?php if($ncamp == 'ogp_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ogp_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data de Inclusão</font></a>
                          <?php if($ncamp == 'ogp_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ogp_ativo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Status</font></a>
                          <?php if($ncamp == 'ogp_ativo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ogp_mostra_integracao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Exibido na<br>Integração</font></a>
                          <?php if($ncamp == 'ogp_mostra_integracao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ogp_nome&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Nome</font></a>
                          <?php if($ncamp == 'ogp_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=opr_nome&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Operadora</font></a>
                          <?php if($ncamp == 'opr_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=opr_status&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Operadora<br>Status</font></a>
                          <?php if($ncamp == 'opr_status') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                      </tr>
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						while($rs_produtos_row = pg_fetch_array($rs_produtos)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
							$status = ($rs_produtos_row['ogp_ativo'] == 1)?"Ativo":"Inativo";
							$ogp_mostra_integracao = ($rs_produtos_row['ogp_mostra_integracao'] == 1)?"Sim":"Não";
							$opr_status = ($rs_produtos_row['opr_status'] == 1)?"Ativo":"Inativo";
					?>
                      <tr bgcolor="<?php echo $cor1 ?>"> 
                        <td class="texto" width="50" align="center"><a style="text-decoration:none" href="com_produto_detalhe.php?produto_id=<?php echo $rs_produtos_row['ogp_id'] ?>"><?php echo $rs_produtos_row['ogp_id'] ?></a></td>
                        <td class="texto" width="100" align="center"><?php echo formata_data($rs_produtos_row['ogp_data_inclusao'],0) ?></td>
                        <td class="texto" width="50" align="center"><?php echo $status ?></td>
                        <td class="texto" width="50" align="center"><?php echo $ogp_mostra_integracao ?></td>
                        <td class="texto"><a style="text-decoration:none" href="com_produto_detalhe.php?produto_id=<?php echo $rs_produtos_row['ogp_id'] ?>"><?php echo $rs_produtos_row['ogp_nome'] ?></a></td>
                        <td class="texto"><?php echo $rs_produtos_row['opr_nome'] ?></td>
                        <td class="texto" width="50" align="center"><?php echo $opr_status ?></td>
                      </tr>
					<?php 	}	?>
                      <tr> 
                        <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
                      </tr>
					<?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
                    </table>
				  </td>
                </tr>
              </table>
          <?php  }  ?>
    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
