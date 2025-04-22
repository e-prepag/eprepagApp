<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once "/www/includes/bourls.php";
	set_time_limit ( 18000 ) ;


	$time_start = getmicrotime();
	
	
	if(!$ncamp)    $ncamp       = 'n';
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
		$limit = 0;
	}

//echo "tf_ativo: ".$tf_ativo."<br>";
	if(!isset($BtnSearch)){
		$tf_ativo="1";
	}
//echo "tf_ativo: ".$tf_ativo."<br>";

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 200;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&BtnSearch=1&tf_n=$tf_n";
	$varsel .= "&tf_email=$tf_email";
	$varsel .= "&tf_tipo=$tf_tipo";
	$varsel .= "&tf_ativo=$tf_ativo";
	$varsel .= "&dd_opr_codigo=$dd_opr_codigo";
	$varsel .= "&tf_news=$tf_news";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";


	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";
		if($msg == "") {
			if($tf_v_data_inclusao_ini || $tf_v_data_inclusao_fim)
			{
				if(verifica_data($tf_v_data_inclusao_ini) == 0)	$msg = "A data inicial da venda é inválida." . PHP_EOL;
				if(verifica_data($tf_v_data_inclusao_fim) == 0)	$msg = "A data final da venda é inválida." . PHP_EOL;
			}
		}
	
		//Busca emails
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){

			$filtro = array();
			if($tf_n) 		$filtro['n'] = $tf_n;
			if($tf_email) 	$filtro['email'] = $tf_email;
			if($tf_tipo) 	$filtro['tipo'] = $tf_tipo;
			if($tf_ativo =='1' || $tf_ativo=='2') $filtro['ativo'] = $tf_ativo;	
			if($tf_news =='t' || $tf_news =='n' || $tf_news=='s' || $tf_news=='h') $filtro['news'] = strtoupper($tf_news);	
			if($dd_opr_codigo) $filtro['dd_opr_codigo'] = $dd_opr_codigo;
			//if(			)	$filtro[''] = 
			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) {
				$filtro['data_inclusao_ini'] = formata_data($tf_v_data_inclusao_ini,1);
				$filtro['data_inclusao_fim'] = formata_data($tf_v_data_inclusao_fim,1);
			}
			if($tf_semoptout) 		$filtro['sem_optout'] = 1;
			
			if ($tf_produto && is_array($tf_produto)) {
					if (count($tf_produto) == 1) {
					$tf_produto = $tf_produto[0];
					} else {
					$tf_produto = implode("|",$tf_produto);
					}	
				}
			if ($tf_produto && $tf_produto != "") {
				$tf_produto = explode("|",$tf_produto);	
			}

			$i = 0;
			$num_col = count($tf_produto);
			while ($i <= $num_col) {
				
				$filtro['produto'.$i] = $tf_produto[$i];

				$palavra = urlencode($filtro['produto'.$i]);

				$varsel .= "&tf_produto[]=".$palavra;

				$i++;

			}
			/////////////////////////// PIN////////////////////////

			if ($tf_pins && is_array($tf_pins)) {
					if (count($tf_pins) == 1) {
					$tf_pins = $tf_pins[0];
					} else {
					$tf_pins = implode("|",$tf_pins);
					}	
				}
			if ($tf_pins && $tf_pins != "") {
				$tf_pins = explode("|",$tf_pins);	
			}

			$i = 0;
			$num_col_pin = count($tf_pins);
			while ($i <= $num_col_pin) {
				
				$filtro['pin'.$i] = $tf_pins[$i];

				$palavra = urlencode($filtro['pin'.$i]);

				$varsel .= "&tf_pins[]=".$palavra;
//echo $varsel;
				$i++;

			}

			///////////////////////////////////////////////////////////
			$rs_newsletter = null;
			
 include $raiz_do_projeto . "includes/gamer/inc_newsletter_obter.php";
//echo "<pre>".print_r($filtro,true)."</pre>";
//die("AKI");

			if ($total_reg == '') { 

			$ret = obter($filtro, null, null, $rs_newsletter);
				if($ret != "") $msg = $ret;
				else {
					$total_table = pg_num_rows($rs_newsletter);
				} 
			} else {
				$total_table = $total_reg;
			}

//echo "total_table: $total_table<br>";

				if($total_table == 0) {
					$msg = "Nenhum cadastro encontrado." . PHP_EOL;
				} else {
					//Ordem
					//$orderBy = $ncamp;
					//if($ordem == 1){
					//	$orderBy .= " desc ";
				//		$img_seta = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
			//		} else {
			//			$orderBy .= " asc ";
			//			$img_seta = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
			//		}
					
				


				$varsel .= "&total_reg=".$total_table;
			
				$orderBy = " order by tipo, email "; 
				$limitTo = " limit ".$max." offset ".$inicial;


					$ret = obter($filtro, $orderBy, $limitTo, $rs_newsletter);

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
	
	
ob_end_flush();
?>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

$(document).ready(function () {
		load_caixas();
		v_precos();
		
		
});

function load_caixas(){


				//var id = $(this).val();
			//alert(id);
			// reset values
		
			ResetCheckedValue();

			

			<?php 	$i = 0;
	
	$parametros = ",'tf_produto[]': [";

	while ($i <= $num_col ) {
			?>	

	var tf_produto<?php echo $i?> = "<?php echo $tf_produto[$i]?>" ;	
				
			<?php	
		
	$parametros .= "\"$tf_produto[$i]\"";

	

			$i++;

			if ( $i <= $num_col) {

		$parametros .= ",";
	}

			}

	$parametros .= "]"; ?>

	

			// values in dd_pin_status start with 'st' to avoid geting null when status = 0
			$.ajax({
				type: "POST",
				url: "/ajax/gamer/ajaxProdutoComPesquisaVendas.php",
				data: {id:+((document.getElementById('dd_opr_codigo').value>0)?document.getElementById('dd_opr_codigo').value:-1)<?php echo $parametros?>},
				beforeSend: function(){
/*
					if(document.getElementById('dd_opr_codigo').value>0) {
						$('#tf_v_data_inclusao_ini').val('');
						$('#tf_v_data_inclusao_fim').val('');
					}
*/
					$('#mostraValores').html("Aguarde...");
				},
				success: function(html){
					//alert('valor');
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});// fim ajax
		}	// fim function 



	function v_precos() {
	

	
	ResetCheckedValuePin();

				<?php 	$i = 0;
	
	$parametros = ",'tf_pins[]': [";

	while ($i < $num_col_pin ) {
			?>	

	var tf_pins<?php echo $i?> = "<?php echo $tf_pins[$i]?>" ;	
				
			<?php	
		
	$parametros .= "'$tf_pins[$i]'";

	

			$i++;

			if ( $i < $num_col_pin) {

		$parametros .= ",";
	}

			}

	$parametros .= "]"; ?>
		

	var selectedItems = new Array();

		

		
		$.ajax({
				
			type: "POST",
			url: "/ajax/gamer/ajaxTipoComPesquisaVendas.php",
		    data: 
				
				{id:+((document.getElementById('dd_opr_codigo').value>0)?document.getElementById('dd_opr_codigo').value:-1)<?php echo $parametros?>},
					beforeSend: function(){
					$('#mostraValores2').html("Aguarde...");
				},
				success: function(html){
					
					$('#mostraValores2').html(html);
				},
				error: function(){
					alert('erro ao carregar valores');
				}

				}); //fim ajax

					
	
		}// fim function reload precos




function ResetCheckedValue() {
	// reset the $varsel var 'tf_pins'
	if(document.form1.tf_produto) {
		document.form1.tf_produto.value = '';
	}

	// reset the checkboxes with values 'tf_pins[]'
	var chkObj = document.form1.elements.length;
	var chkLength = chkObj.length;
	for(var i = 0; i < chkLength; i++) {
		var type = document.form1.elements[i].type;
		if(type=="checkbox" && document.form1.elements[i].checked) {
			chkObj[i].checked = false;
		}
	}
}

function ResetCheckedValuePin() {
	// reset the $varsel var 'tf_pins'
	if(document.form1.tf_pins) {
		document.form1.tf_pins.value = '';
	}

	// reset the checkboxes with values 'tf_pins[]'
	var chkObj = document.form1.elements.length;
	var chkLength = chkObj.length;
	for(var i = 0; i < chkLength; i++) {
		var type = document.form1.elements[i].type;
		if(type=="checkbox" && document.form1.elements[i].checked) {
			chkObj[i].checked = false;
		}
	}
}



</script>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        
    });
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table">
  <tr> 
    <td>
        <form name="form1" id="form1" method="get" action="com_lista_emails_newsletters.php">
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          </tr>
		</table>
        <table class="table fontsize-pp txt-preto">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="6" bgcolor="#ECE9D8" class="texto">Cadastro</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Tipo</td>
            <td>
				<select name="tf_tipo" class="form2">
					<option value="" <?php if($tf_tipo == "") echo "selected" ?>>Todos os tipos</option>
					<option value="Gamers" <?php if ($tf_tipo == "Gamers") echo "selected";?>>Gamers</option>
					<option value="LanHouses" <?php if ($tf_tipo == "LanHouses") echo "selected";?>>LanHouses</option>
					<option value="ExpressMoney" <?php if ($tf_tipo == "ExpressMoney") echo "selected";?>>ExpressMoney</option>
				</select>			</td>
            <td width="100" class="texto">Ativo</td>
			<td>
				<select name="tf_ativo" class="form2">
					<option value="" <?php if($tf_ativo == "" || ($tf_ativo != "1" && $tf_ativo != "2")) echo "selected" ?>>Todos os tipos</option>
					<option value="1" <?php if ($tf_ativo == "1") echo "selected";?>>Ativos</option>
					<option value="2" <?php if ($tf_ativo == "2") echo "selected";?>>Inativos</option>
				</select>			</td>
		  </tr>

          <tr bgcolor="#F5F5FB">
            <td height="40" class="texto">Newsletter&nbsp;</td>
            <td class="texto">
				<select name="tf_news" class="form2">
					<option value="" <?php if($tf_news == "") echo "selected" ?>>Selecione</option>
					<option value="s" <?php if ($tf_news == "s") echo "selected";?>>Sim - HTML+Texto</option>
					<option value="h" <?php if ($tf_news == "h") echo "selected";?>>Sim - HTML</option>
					<option value="t" <?php if ($tf_news == "t") echo "selected";?>>Sim - Texto</option>
					<option value="n" <?php if ($tf_news == "n") echo "selected";?>>Não</option>
				</select>
			</td>
			<td class="texto">Data Inclusão</font></td>
			<td class="texto">
				<input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
				a 
				<input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
			</td>
          </tr>

		  
		  <tr bgcolor="#F5F5FB">
            <td height="40" class="texto">Operadora</td>
            <td> <select name="dd_opr_codigo" id="dd_opr_codigo" class="combo_normal">
                <option value="">Selecione a Operadora</option>
				<?php
				$sql  = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
				$resopr = SQLexecuteQuery($sql);
				$a_opr = array();
				while ($pgopr = pg_fetch_array($resopr)) { 
					$operadora_nome = $pgopr['opr_nome'];
					$operadora_codigo = $pgopr['opr_codigo'];
					if ($dd_opr_codigo == $operadora_codigo ) {
						$select = "selected = 'selected' ";
					} else {
						$select = '';
					}
				?>
				<option value='<?php echo $operadora_codigo?>' <?php echo $select?>><?php echo $operadora_nome?>(<?php echo $operadora_codigo?>)</option>
				
				<?php }
				//ksort($a_opr); 
				 //foreach($a_opr as $key => $val) { ?>
               
                <?php //} ?>
            </select>&nbsp;</td>
            <td class="texto">Produtos</td>
            <td class="texto"><div id='mostraValores'>
			<?php 



			$i = 0;
		//	$f = count($tf_produto);
			
			while ($i < $num_col ) {
			?>	

			<input type="checkbox" id="tf_produto[]" name="tf_produto[]" value="<?php echo $tf_produto[$i]?>" checked><?php echo $tf_produto[$i]?>	
				
			<?php	
			
			$i++;

			}
							
			
			?>
	<?php 
			if($resvalue) {
                foreach($a_valores as $key => $val) { ?>
					<nobr><input type="checkbox" id="tf_produto[]" name="tf_produto[]" value="<?php echo $key; ?>" 
					<?php
						if ($tf_produto && is_array($tf_produto))
							if (in_array($key, $tf_produto)) 
								echo " checked";
						else
							if ($key == $tf_produto)
								echo " checked";
					?>><span title="<?php echo "n: ".$val; ?>"><?php echo $key . ",00"; ?></span></nobr> 
                <?php } 
			  }
			?>
			</div> </td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td height="40" class="texto">&nbsp;</td>
            <td>&nbsp;</td>
            <td class="texto">Valor:</td>
            <td class="texto"><div id='mostraValores2'>
             
              <?php 
			if($resvalue) {
                foreach($a_valores as $key => $val) { ?>
              <nobr>
                <input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="<?php echo $key; ?>" 
					<?php
						if ($tf_valor && is_array($tf_pins))
							if (in_array($key, $tf_pins)) 
								echo " checked";
						else
							if ($key == $tf_pins)
								echo " checked";
					?>>
                <span title="<?php echo "n: ".$val; ?>"><?php echo $key . ",00"; ?></span></nobr>
              <?php } 
			  }
			?>
            </div></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td height="40" class="texto">Retira OptOut&nbsp;</td>
            <td class="texto"><input type="checkbox" name="tf_semoptout" id="tf_semoptout"<?php if($tf_semoptout) echo " checked" ?>></td>
			<td class="texto">&nbsp;</font></td>
			<td class="texto">&nbsp;</td>
          </tr>

		</table>

        <table class="table">
          <tr bgcolor="#F5F5FB"> 
           <td colspan="<?php echo $num_col+4?>" id="area" class='texto' align="center">
               <div id="download" class="btn btn-info btn-sm"><strong onMouseOver="this.style.backgroundColor='#CCFF99'" onMouseOut="this.style.backgroundColor='#CCCCCC'">Gerar Arquivo</strong></div>
           </td>
            <td width="81" align="right"><input type="hidden" name="limit" id="limit" value='<?php echo $limit?>'>
              <input name="BtnSearch" type="submit" class="btn btn-info btn-sm" id="BtnSearch"  value="Buscar"></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
           <td colspan="6" bgcolor="#CCCCCC"align="center">
			<font color="blue">Ajuda:</font> Quando for selecionada uma operadora os registros retornados são aqueles que apresentam alguma venda completa naquela operadora/produto/valor, caso contrário retorna todos os registros de usuários independentemente da existência de vendas completas. <br>
			Quando uma operadora for selecionada os campos "<b>Data Inclusão</b>" permitem selecionar o intervalo de datas das vendas para identificar os usuários listados.<br>
			A opção "<b>Retira OptOut</b>" permite retirar da lista aqueles que foram cadastrados como Opt-Out. (demora um pouco mais)
		   </td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td colspan="2" align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>

		<?php if($total_table > 0) { ?>
        <table class="table fontsize-pp txt-preto">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
                      <table class=" table-bordered table">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          <?php //Exibindo resultados <strong><_? echo $inicial + 1 ?_></strong> a <strong><_? echo $reg_ate ?_></strong> de <strong><_? echo $total_table ?_></strong> ?>
                          Exibindo <strong><?php echo $total_table ?></strong> resultados
                   </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr  bgcolor="#ECE9D8"> 
                        <td align="center" width="50"><strong><font class="texto">Tipo</strong></td>
                        <td align="center" width="50"><strong><font class="texto"><nobr>Aceita News?</nobr></strong></td>
                        <td align="center" width="50"><strong><font class="texto">Email</font></strong></td>
                        <td align="center" width="50"><strong><font class="texto">Ncadastros</font></strong></td>
                        <td align="center" width="50"><strong><font class="texto">Ativos</font></strong></td>
						<td align="center" width="50" colspan = '<?php echo $num_col?>'><strong><font class="texto">Produtos</font></strong></td>
						                      </tr>
					
                     
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						
						while($rs_newsletter_row = pg_fetch_array($rs_newsletter)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;

							$tipo = $rs_newsletter_row['tipo'] ;
							$news = $rs_newsletter_row['news'] ;
							$email = $rs_newsletter_row['email'] ;
							$n = $rs_newsletter_row['n'] ;
							$ativo = $rs_newsletter_row['ativo'] ;
							$size = count($rs_newsletter_row)/2 - 4 ; 
							$roll = 0;
							$produto_nome =  $rs_newsletter_row['produto'] ;


						
									while ($roll < $size) {
									$produtos[$roll] = $rs_newsletter_row['produto'.$roll] ;

									$roll++;
									}

							if ($limit < 1) {
							$mensagem .= "\t";
							}
							
					?>
                      <tr class="texto" bgcolor="<?php echo $cor1 ?>" align="center">
                        <td><?php echo $tipo ?></td>
                        <td><nobr><?php echo ((strtoupper($news)=="N" || $news==" " || $news=="")?"NÃO":((strtoupper($news)=="T")?"SIM - Texto": ((strtoupper($news)=="H")?"SIM - HTML":"??? ($news)") ) )." ($news)" ?></nobr></td>
                        <td><nobr><?php echo $email ?></nobr></td>
                        <td><?php echo $n ?></td>
						<td><?php echo $ativo ?></td>
						<?php
						$size2 = 0;
						$total = count($produtos);?>
						<td><?php echo $produto_nome;?></td>
			        </tr>
					<?php 	
				
						}
						
					
					?>
                      
					
                      <tr> 
                        <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></td>
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

<script>
$('#dd_opr_codigo').change( function() { 
		
	load_caixas(); 
	v_precos();

		
});


$('#download').click(
						  

					  function () {


var tf_n = '<?php echo $tf_n?>';
var tf_email = '<?php echo $tf_email?>';
var tf_tipo = '<?php echo $tf_tipo?>';
var tf_ativo = '<?php echo $tf_ativo?>';
var dd_opr_codigo = '<?php echo $dd_opr_codigo?>';
var tf_news = '<?php echo $tf_news?>';
var tf_v_data_inclusao_ini = '<?php echo $tf_v_data_inclusao_ini;?>';
var tf_v_data_inclusao_fim = '<?php echo $tf_v_data_inclusao_fim;?>';
			

<?php 	$i = 0;
	
	$parametros = ",'tf_produto[]': [";

	while ($i < $num_col ) {
			?>	
		var tf_produto<?php echo $i?> = "<?php echo $tf_produto[$i]?>" ;	
			<?php	
		$parametros .= "'$tf_produto[$i]'";
		$i++;
		if ( $i < $num_col) {
			$parametros .= ",";
		}
	}
	$parametros .= "]";


	$i = 0;
	
	$parametros .= ",'tf_pins[]': [";
	while ($i < $num_col_pin ) {
?>	
		var tf_pins<?php echo $i?> = "<?php echo $tf_pins[$i]?>" ;	
<?php	
		
		$parametros .= "'$tf_pins[$i]'";
		$i++;
		if ( $i < $num_col_pin) {
			$parametros .= ",";
		}
	}
	$parametros .= "]";
?>

		
	$.ajax({
			type: "GET",
			url: "/gamer/emails/com_lista_emails_newslettersB.php",
			data: {tf_v_data_inclusao_ini:tf_v_data_inclusao_ini,tf_v_data_inclusao_fim:tf_v_data_inclusao_fim,tf_n:tf_n,tf_email:tf_email,tf_tipo:tf_tipo,tf_ativo:tf_ativo,dd_opr_codigo:dd_opr_codigo<?php echo $parametros?>,BtnSearch:'Buscar'},		
			beforeSend: function(){
				$("#area").html("<img src='/images/ajax-loader.gif' />");
			},
			success: function(html){
				$("#area").html(html);
				//alert(html);
			},
			error: function(){
				alert('erro ao carregar valores');
			}				
		}); 

});

/*$('#buscar').click(function() {
  $('#form1').submit();
}); */

/*$("input[@name='tf_produto[]']:unchecked").live('change', function () { 
			
		reload_precos();

		alert('eittaa');
		
		}); */
</script>
<?php 
    require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
