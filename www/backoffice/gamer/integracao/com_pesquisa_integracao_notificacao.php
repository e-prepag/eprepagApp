<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once "/www/includes/bourls.php";
?>
<link href="/js/jQCTC/_assets/css/Style.css" rel="stylesheet" type="text/css" />
<?php 

	set_time_limit ( 3000 ) ;

	$time_start = getmicrotime();
	
	if(!$ncamp)    $ncamp       = 'ipnh_data_inclusao';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 1;

	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$n_rows = 0;
	}

	$total_pedidos = 0;
	$total_pedidos_pagina = 0;

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	if(!($tf_data_ini && $tf_data_fim)) {
		$tf_data_ini = date("d/m/Y");
		$tf_data_fim = date("d/m/Y");
	}

	$varsel = "&BtnSearch=1";
	$varsel .= "&tf_data=$tf_data";
	$varsel .= "&tf_data_ini=$tf_data_ini&tf_data_fim=$tf_data_fim&tf_store_id=$tf_store_id&tf_codretepp=$tf_codretepp";
	$varsel .= "&tf_v_codigo=$tf_v_codigo&tf_v_order=$tf_v_order&tf_v_email=$tf_v_email";

	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//------------------------------------------------------------------
		//Data
		if($msg == "")
			if($tf_data_ini || $tf_data_fim){
				if(verifica_data($tf_data_ini) == 0)	$msg = "A data de inclusão inicial do registro é inválida.\n";
				if(verifica_data($tf_data_fim) == 0)	$msg = "A data de inclusão final do registro é inválida.\n";
			}
		if($msg == "")
			if($tf_v_codigo){
				if(!is_csv_numeric_global($tf_v_codigo, 1)) {
					$msg = "Código da venda deve ser numérico ou lista de números separada por vírgulas.\n";
				}
			}
		if($msg == "")
			if($tf_v_order){
				// order_id de Bigpoint contem caracteres alfanumericos -> testa tipo 3
				if(!is_csv_numeric_global($tf_v_order, 3)) {
					$msg = "Código da ordem deve ser alfanumérico ou lista de alfanumericos separada por vírgulas.\n";
				}
			}

		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){

//echo "tf_data_ini: '".$tf_data_ini."', tf_data_fim: '".$tf_data_fim."'<br>";

			$filtro = array();
			if($tf_data_ini && $tf_data_fim) {
				$filtro['dataMin'] = $tf_data_ini;
				$filtro['dataMax'] = $tf_data_fim;
			}

			if($tf_store_id) {
				$filtro['store_id'] = $tf_store_id;
			}

			if(!is_null($tf_codretepp) && strlen($tf_codretepp)>0) {
				$filtro['codretepp'] = "".$tf_codretepp."";
//echo "filtroA['codretepp']: ".$filtro['codretepp']."<br>";
			}
			if($tf_v_codigo) {
				$filtro['vg_id'] = $tf_v_codigo;
			}
			if($tf_v_order) {
				$filtro['order_id'] = $tf_v_order;
			}
			if($tf_v_email) {
				$filtro['client_email'] = $tf_v_email;
			}

			$rs_historico_notify = null;
			$ret = obter($filtro, null, $rs_historico_notify);
			if($ret != "") $msg = $ret;
			else {
				$n_rows = pg_num_rows($rs_historico_notify);

				if($n_rows == 0) {
					$msg = "Nenhum registro de integração encontrado.\n";
				} else {

					while($rs_historico_notify_row = pg_fetch_array($rs_historico_notify)){
						$total_pedidos += $rs_historico_notify_row['ipnh_amount'];
					}
					$total_pedidos /= 100;

					//Ordem
					$orderBy = $ncamp;
					if($ordem == 1){
						$orderBy .= " desc ";
						$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
					} else {
						$orderBy .= " asc ";
						$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
					}
			
					$orderBy .= " limit ".$max; 
					$orderBy .= " offset ".$inicial;
				
					$ret = obter($filtro, $orderBy, $rs_historico_notify);
					if($ret != "") $msg = $ret;
					else {
				
						if($max + $inicial > $n_rows)
							$reg_ate = $n_rows;
						else
							$reg_ate = $max + $inicial;
					}
				}
			}
		}
	}
	
	//parceiros
	$sql  = "select distinct ip_store_id as parceiro, count(*) as n, ".getPartner_Names_SQL()." from tb_integracao_pedido group by ip_store_id order by opr_nome, ip_store_id;";
//echo "sql: $sql<br>";
	$rs_parceiros = SQLexecuteQuery($sql);

ob_end_flush();
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_data_ini','tf_data_fim',optDate);
        
    });

function open_notify_window(store_id, order_id) { 
	window.open('/gamer/integracao/com_integracao_notificacao_manual.php?store_id='+store_id+'&order_id='+order_id,'mywindow', 'width=1000,height=500');
  
}

function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

<!--
		
	//Função para buscar o endereço.
	function do_sonda(ipnh_id, store_id, order_id){
		var id_field = "id_sonda_"+ipnh_id+"_"+store_id+"_"+order_id;
		//função para verificar se o objeto DOM do javascript está pronto.
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/gamer/integracao_sonda_ajax.php",
				data: "store_id="+store_id+"&order_id="+order_id,
				beforeSend: function(){
//					$("#"+id_field).html("Aguarde... Consultando ("+tipo+")");
					$("#"+id_field).html("<img src='/images/AjaxLoadingIcon.gif' width='30' height='30' title='"+store_id+", "+order_id+"'>");
				},
				success: function(txt){
					var txt0="????";
					if(txt.length>0) {
						$("#"+id_field).html(txt);	// " ("+txt.length+")<br>"+  // +'<br>\n'+trans1
					} else {
						$("#"+id_field).html("ERROR, store_id="+store_id+"&order_id="+order_id);
					}
				},
				error: function(){
					$("#"+id_field).html("???");
				}
			});
		});
	}

//--></script>

<style type="text/css">
<!--
.linkout {background-color: #FFFFFF;} 
.linkout2 {background-color: #F5F5FB;} 
.linkover {background-color:#CCFFCC; font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #666666; text-decoration: none;}
.linkover3 {background-color:#0099CC;}
-->
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<table class="table txt-preto fontsize-pp">
  <tr> 
    <td valign="top" align="center"> 
		<form name="form1" method="post" action="com_pesquisa_integracao_notificacao.php">	<?php //  onSubmit="myformchecker(this); return false;" ?>
            <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Pedidos de integração</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data de Inclusão</font></td>
            <td class="texto" colspan="3">
              <input name="tf_data_ini" type="text" class="form" id="tf_data_ini" value="<?php echo $tf_data_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_data_fim" type="text" class="form" id="tf_data_fim" value="<?php echo $tf_data_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
           <tr bgcolor="#F5F5FB"> 
            <td class="texto">Número do Pedido:</font></td>
            <td class="texto">
				<input name="tf_v_codigo" type="text" class="form2" value="<?php echo $tf_v_codigo ?>" size="20" maxlength="512">			
			</td>
            <td class="texto">Número da ordem:</font></td>
            <td class="texto">
				<input name="tf_v_order" type="text" class="form2" value="<?php echo $tf_v_order?>" size="20" maxlength="512">
			</td>
          </tr>
           <tr bgcolor="#F5F5FB"> 
            <td class="texto">Email cliente:</font></td>
            <td class="texto">
				<input name="tf_v_email" type="text" class="form2" value="<?php echo $tf_v_email?>" size="40" maxlength="256">			
			</td>
            <td class="texto" colspan="2">&nbsp;</td>
          </tr>
         <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Parceiros</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Parceiros</td>
            <td>
				<select name="tf_store_id" class="form2">
					<option value="" <?php if($tf_ip_store_id == "") echo "selected" ?>>Todos os parceiros</option>
					<?php if($rs_parceiros) while($rs_parceiros_row = pg_fetch_array($rs_parceiros)){ ?>					
					<option value="<?php echo $rs_parceiros_row['parceiro']; ?>" <?php if ($tf_store_id == $rs_parceiros_row['parceiro']) echo "selected";?>><?php echo getPartner_name_By_ID($rs_parceiros_row["parceiro"])." (ID: ".$rs_parceiros_row["parceiro"].") ".$rs_parceiros_row["n"]." registro".(($rs_parceiros_row["n"]>1)?"s":"")." "; ; ?></option>
					<?php } ?>
				</select>
			</td>
            <td width="100" class="texto">&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Status</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto"><nobr>Status da notificação</nobr></td>
            <td>
			<?php
				$b_found = false;
				foreach($a_codretepp as $key => $val) {
//echo $key." - <pre>".print_r($val, true)."</pre><br>";
					if($val['codretepp']===$tf_codretepp) {
						$b_found = true;
						break;
					}
				}
				echo "<select name='tf_codretepp' class='form2'>\n";
				echo "<option value=''".((!$b_found)?" selected":"").">Todas as opções</option>\n";
				echo "<option value='-1'".(($tf_codretepp=="-1")?" selected":"").">Todos os positivos (0+1)</option>\n";
				echo "<option value='-2'".(($tf_codretepp=="-2")?" selected":"").">Todos os Erros (2+3+4+5+6+7+8)</option>\n";
				foreach($a_codretepp as $key => $val) {
					echo "<option value='".$val['codretepp']."'".(($val['codretepp']===$tf_codretepp)?" selected":"").">".$val['codretepp']." - ".$val['description']."</option>\n";
				}
				echo "</select>\n";
				?></td>
            <td width="100" class="texto">&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td colspan="4" align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td> 
          </tr>
			<?php if($msg != ""){?>
          <tr class="texto"><td align="center" colspan="4"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr>
			<?php }?>
		</table>
		</form>
        </td></tr></table>
		<?php if($n_rows > 0) { ?>
        <table class="table txt-preto fontsize-pp bg-branco">
            <tr> 
              <td colspan="20" class="texto"> 
                Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $n_rows ?></strong></font> 
              </td>
            </tr>
            <?php $ordem = ($ordem == 1)?2:1; ?>
            <tr  bgcolor="#ECE9D8"> 
              <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ip_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">ipnh_id</font></a> 
                <?php if($ncamp == 'ip_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </strong></td>
              <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ipnh_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data de Inclusão</font></a>
                <?php if($ncamp == 'ipnh_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                </strong></td>
              <td align="center"><strong><font class="texto">Parceiro</font></strong></td>

              <td align="center"><strong><font class="texto">order_id</font></strong></td>
              <td align="center"><strong><font class="texto">amount</font></strong></td>

              <td align="center"><strong><font class="texto">client_email</font></strong></td>
              <td align="center"><strong><font class="texto">pagto_tipo</font></strong></td>
              <td align="center"><strong><font class="texto">CMD</font></strong></td>
              <td align="center"><strong><font class="texto">codretepp</font></strong></td>

              <td align="center"><strong><font class="texto">Histórico de registros com order_id</font></strong></td>

            </tr>
          <?php
              $cor_hover = "#CCFFCC";
              $cor1 = $query_cor1;
              $cor2 = $query_cor1;
              $cor3 = $query_cor2;
              $i=0;
              $s_lista_order_ids = "";
              $n_lista_order_ids = 0;

              while($rs_historico_notify_row = pg_fetch_array($rs_historico_notify)){
                  $cor1 = ($cor1 == $cor2)?$cor3:$cor2;
//							$sstyle = (($i) % 2)?"linkout2":"linkout";
                  $total_pedidos_pagina += $rs_historico_notify_row['ipnh_amount'];


          ?>
            <tr bgcolor="<?php echo $cor1 ?>" valign="top" onmouseover="bgColor='#CFDAD7'" onmouseout="bgColor='<?php echo $cor1 ?>'"> 
              <td class="texto" align="center" onMouseOver="this.className='linkover'" onMouseOut="this.className='texto'" style="border-top-width: 1px; border-top-color: darkgray; border-top-style: solid;"><?php echo $rs_historico_notify_row['ipnh_id'] ?><br>
              <?php
                  if($rs_historico_notify_row['ipnh_order_id'] && $rs_historico_notify_row['ipnh_store_id']) {
              ?>
              <input type="button" value="Notify" class="btn btn-info btn-sm" title="<?php echo "Notify manual para order_id: ".$rs_historico_notify_row['ipnh_order_id']." do parceiro '".$rs_historico_notify_row['ipnh_store_id']."'"; ?>" onClick="open_notify_window(<?php echo "'".$rs_historico_notify_row['ipnh_store_id']."','".$rs_historico_notify_row['ipnh_order_id'];?>');">
              <?php
                  }
              ?>
              <?php 
                  $sonda_url = getPartner_sonda_url_By_ID($rs_historico_notify_row['ipnh_store_id']);
                  if($sonda_url!="") {
                      // Para Stardoll -> restringe a REINALDO, TAMY, GLAUCIA
                      $b_mostra_sonda = true;
                      if($rs_historico_notify_row['ipnh_store_id']=="10406") {
                          if(!b_IsBKOUsuarioSondaIntegracao()) {
                              $b_mostra_sonda = false;
                          }
                      }
                      //echo "<br>sonda_url: '$sonda_url'";
              ?>
              <?php if($b_mostra_sonda) { ?>
              <?php
/*
              $order_id = (
                              ($rs_historico_notify_row['ipnh_store_id']=='10408') 
                                  ? $rs_historico_notify_row['ipnh_vg_id'] 
                                  : $rs_historico_notify_row['ipnh_order_id']);
*/
              $order_id = $rs_historico_notify_row['ipnh_order_id'];
              $s_lista_order_ids .= ", ".$order_id;
              $n_lista_order_ids++;

              // Testing with Elex values
//						if($rs_historico_notify_row['ipnh_store_id']=="10411") { $order_id = $rs_historico_notify_row['ipnh_vg_id']; }

//echo $rs_historico_notify_row['ipnh_store_id']."(order_id: '$order_id') ";
                  ?>
              <br><nobr><div id='id_sonda_<?php echo $rs_historico_notify_row['ipnh_id'] ."_". $rs_historico_notify_row['ipnh_store_id']."_".$order_id; ?>' onclick="do_sonda(<?php echo "'".$rs_historico_notify_row['ipnh_id']."', '".$rs_historico_notify_row['ipnh_store_id']."', '".$order_id."'" ?>);" style='color:red' title='Clique aqui para consultar o parceiro <?php echo getPartner_name_By_ID($rs_historico_notify_row['ipnh_store_id']) ?> sobre este pedido'>(Sonda)</div><br><?php 
                  //echo "ipnh_store_id: ".$rs_historico_notify_row['ipnh_store_id']. ", ipnh_order_id: " . $rs_historico_notify_row['ipnh_order_id'] . ", ipnh_vg_id: " . $rs_historico_notify_row['ipnh_vg_id'] . "<br>";
                  ?></nobr>
              <?php } ?>
              <?php 
                  }
              ?>
              </td>
              <td class="texto" align="center" style="border-top-width: 1px; border-top-color: darkgray; border-top-style: solid;"><nobr><?php echo substr($rs_historico_notify_row['ipnh_data_inclusao'],0,19) ?></nobr></td>

              <td class="texto" align="center" style="border-top-width: 1px; border-top-color: darkgray; border-top-style: solid;"><?php echo getPartner_name_By_ID($rs_historico_notify_row['ipnh_store_id']) . "<br><nobr>(ID: ".$rs_historico_notify_row['ipnh_store_id'].")</nobr>" ?></td>

              <td class="texto" align="center" style="border-top-width: 1px; border-top-color: darkgray; border-top-style: solid; color:blue"><?php echo $rs_historico_notify_row['ipnh_order_id']?></td>
              <td class="texto" align="center" style="border-top-width: 1px; border-top-color: darkgray; border-top-style: solid;"><font style='color:blue'><?php echo number_format($rs_historico_notify_row['ipnh_amount']/100, 2, '.', ',') . "<br>(" .$rs_historico_notify_row['ipnh_currency_code'] . ")" ?></font></td>

              <td class="texto" align="center" style="border-top-width: 1px; border-top-color: darkgray; border-top-style: solid;"><?php echo $rs_historico_notify_row['ipnh_client_email']?></td>
              <td class="texto" align="center" style="border-top-width: 1px; border-top-color: darkgray; border-top-style: solid;"><?php echo $rs_historico_notify_row['vg_pagto_tipo']?></td>
              <td class="texto" align="center" style="border-top-width: 1px; border-top-color: darkgray; border-top-style: solid;"><?php echo $rs_historico_notify_row['ipnh_cmd'] ?></td>
              <td class="texto" align="center" style="border-top-width: 1px; border-top-color: darkgray; border-top-style: solid;" title="<?php echo "codretepp: '".$rs_historico_notify_row['ipnh_codretepp']."' -> '".$a_codretepp[$rs_historico_notify_row['ipnh_codretepp']]['description']."'" ?>"><?php echo $rs_historico_notify_row['ipnh_codretepp'] ?></td>

              <td class="texto" width="100" align="center" style="border-top-width: 1px; border-top-color: darkgray; border-top-style: solid;"><?php 

                      $sql  = "select iph.* ";
                      $sql .= "from tb_integracao_pedido_historico iph ";
                      $sql .= " where 1=1 ";
                      $sql .= " and iph.iph_ip_store_id = '" . SQLaddFields($rs_historico_notify_row['ipnh_store_id'], "") . "' ";
                      $sql .= " and iph.iph_ip_order_id = '" . SQLaddFields($rs_historico_notify_row['ipnh_order_id'], "") . "' ";
                      $sql .= " order by iph_data_inclusao desc";

//if(b_IsUsuarioReinaldo()) { 
//echo $sql."<br>\n";
//die("Stop");
//}
                      $rs = SQLexecuteQuery($sql);

                      if(!$rs || pg_num_rows($rs) == 0) {
                          echo "<p class='texto'>Sem registros</p>\n";
                      } else {
                          echo "<table width='100%' cellpadding='0' cellspacing='1' border='1' bordercolor='#cccccc' style='border-collapse:collapse;' align='center'>\n <tr class='texto' align='center'><td><b>iph_ip_id</b></td> <td><b>store_id</b></td> <td><b>order_id</b></td> <td><b>vg_id</b></td> <td><b>data_inclusao</b></td> <td><b>confirmed</b></td></tr>\n";
                          while($rs_row = pg_fetch_array($rs)){

                              $s_link = (($rs_row['iph_ip_vg_id']>0) ? "<a href='/gamer/vendas/com_venda_detalhe.php?venda_id=" . $rs_row['iph_ip_vg_id'] . "&fila_ncamp=vg_data_inclusao&fila_ordem=1&BtnSearch=1&tf_v_codigo=" . $rs_row['iph_ip_vg_id'] . "' target='_blank'>":"") . $rs_row['iph_ip_vg_id'] . (($rs_row['iph_ip_vg_id']>0)?"</a>":"");
                              // $rs_row['iph_ip_vg_id']

                              echo "<tr class='texto' align='center' title='iph_id: " . $rs_row['iph_id'] . "'><td>" . $rs_row['iph_ip_id'] . "</td>\n";
                              echo "<td style='color:". (($rs_historico_notify_row['ipnh_store_id']!=$rs_row['iph_ip_store_id'])?"red":"darkgreen") ."'>" . $rs_row['iph_ip_store_id'] . "</td>\n";
                              echo "<td style='color:". (($rs_historico_notify_row['ipnh_order_id']!=$rs_row['iph_ip_order_id'])?"red":"blue") ."'>" . $rs_row['iph_ip_order_id'] . "</td>\n";
                              echo "<td>" . $s_link . "</td>\n";
                              echo "<td><nobr>" . substr($rs_row['iph_data_inclusao'],0,19) . "</nobr></td>\n";
                              echo "<td>" . $rs_row['iph_ip_status_confirmed'] . "</td>\n";
                              echo "</tr>\n";
                          }
                          echo "</table>\n";
                      }
              ?></td>

            </tr> 

          <?php 	
              }	
              $total_pedidos_pagina /= 100;
              if($n_rows>$max) {
          ?>
            <tr bgcolor="#ECE9D8"> 
              <td colspan="3" class="texto">&nbsp;</font></td>
              <td class="texto"><b>Subtotal:</b></font></td>
              <td class="texto"><font style='color:blue'>R$<?php echo number_format($total_pedidos_pagina, 2, '.', '.') ?></font></td>
              <td colspan="5" class="texto">&nbsp;</font></td>
            </tr>
          <?php 	
              }
          ?>
            <tr bgcolor="#ECE9D8"> 
              <td colspan="3" class="texto">&nbsp;</font></td>
              <td class="texto"><b>Total:</b></font></td>
              <td class="texto"><font style='color:blue'>R$<?php echo number_format($total_pedidos, 2, '.', '.') ?></font></td>
              <td colspan="5" class="texto">&nbsp;</font></td>
            </tr>
            <tr bgcolor="#ECE9D8"> 
              <td colspan="3" class="texto">&nbsp;</font></td>
              <td class="texto"><b>&nbsp;</b></font></td>
              <td class="texto"><font style='color:blue'>R$<?php echo number_format($total_pedidos/$n_rows, 2, '.', '.') ?>/pedido</font></td>
              <td colspan="5" class="texto">&nbsp;</font></td>
            </tr>
            <tr> 
              <td colspan="10" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
            </tr>
          <?php paginacao_query($inicial, $n_rows, $max, 100, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
            <tr> 
              <td colspan="10" bgcolor="#FFFFFF" class="texto">Lista de order_ids: <?php echo "($n_lista_order_ids) ".$s_lista_order_ids ?></font></td>
            </tr>
        </table>
          <?php  }  ?>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
<?php
	function obter($filtro, $orderBy, &$rs){

//echo "filtro['codretepp']: ".$filtro['codretepp']."<br>";
		$ret = "";
		$filtro = array_map("strtoupper", $filtro);

		$sql  = "select ipnh.*, vg.vg_pagto_tipo  ";
		$sql .= "from tb_integracao_pedido_notificacao_historico ipnh ";
		$sql .= "left outer join tb_venda_games vg on ipnh.ipnh_vg_id = vg.vg_id ";
		if(!is_null($filtro) && $filtro != ""){
		
//echo "tf_data_ini: '".formata_data($filtro['dataMin'])."', tf_data_fim: '".formata_data($filtro['dataMax'])."'<br>";

			$sql .= " where 1=1 ";

			if($filtro['dataMin'] && $filtro['dataMax']) {
				$sql .= " and (" . (is_null($filtro['dataMin']) || is_null($filtro['dataMax'])?1:0);
				$sql .= "=1 or ipnh.ipnh_data_inclusao between '".formata_data_ts_integracao($filtro['dataMin'])." 00:00:00' and '".formata_data_ts_integracao($filtro['dataMax'])." 23:59:59')";
			}

			$sql .= " and (" . (is_null($filtro['store_id'])?1:0);
			$sql .= "=1 or ipnh.ipnh_store_id = '" . SQLaddFields($filtro['store_id'], "") . "')";

			if($filtro['codretepp']=="-1") {
				$sql .= " and (" . (is_null($filtro['codretepp'])?1:0);
				$sql .= "=1 or (ipnh.ipnh_codretepp = '0' or ipnh.ipnh_codretepp = '1'))";
			} elseif($filtro['codretepp']=="-2") {
				$sql .= " and (" . (is_null($filtro['codretepp'])?1:0);
				$sql .= "=1 or (ipnh.ipnh_codretepp = '2' or ipnh.ipnh_codretepp = '3' or ipnh.ipnh_codretepp = '4' or ipnh.ipnh_codretepp = '5' or ipnh.ipnh_codretepp = '6' or ipnh.ipnh_codretepp = '7'))";
			} elseif($filtro['codretepp']) {
				$sql .= " and (" . (is_null($filtro['codretepp'])?1:0);
				$sql .= "=1 or ipnh.ipnh_codretepp = '" . SQLaddFields($filtro['codretepp'], "") . "')";
			}
		}
		if($filtro['vg_id']) {
			$sql .= " and (" . (is_null($filtro['vg_id'])?1:0);
			$sql .= "=1 or (ipnh.ipnh_vg_id in (" . $filtro['vg_id'] . ")) ) ";
		}
		if($filtro['order_id']) {
			$sql .= " and (" . (is_null($filtro['order_id'])?1:0);
			$sql .= "=1 or (upper(ipnh.ipnh_order_id) in ('" . str_replace(",", "','", str_replace(" ", "", $filtro['order_id'])) . "')) ) ";
		}
		if($filtro['client_email']) {
			$sql .= " and (" . (is_null($filtro['client_email'])?1:0);
			$sql .= "=1 or (upper(ipnh.ipnh_client_email) like '%" . strtoupper($filtro['client_email']) . "%') ) ";
		}

		if(!is_null($orderBy)) $sql .= " order by " . $orderBy;
		
//if(b_IsUsuarioReinaldo()) { 
//echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>\n";
//die("Stop");
//}
		$rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter históricos de notificação para integração(s).\n";

		return $ret;

	}

?>