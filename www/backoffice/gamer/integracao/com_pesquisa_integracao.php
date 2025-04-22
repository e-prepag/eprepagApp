<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."class/gamer/classIntegracao.php";

set_time_limit ( 3000 ) ;

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

$time_start = getmicrotime();

if(!$ncamp)    $ncamp       = 'ip_data_inclusao';
if(!$inicial)  $inicial     = 0;
if(!$range)    $range       = 1;
if(!$ordem)    $ordem       = 1;

//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $n_rows = 0;
if($BtnSearch=="Buscar") {
        $inicial     = 0;
        $range       = 1;
        $n_rows = 0;
}
$total_pedidos = 0;
$total_pedidos_pagina = 0;


$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 100; //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;

$varsel = "&BtnSearch=1";
$varsel .= "&tf_data=$tf_data";
$varsel .= "&tf_data_ini=$tf_data_ini&tf_data_fim=$tf_data_fim&tf_data_conf_ini=$tf_data_conf_ini&tf_data_conf_fim=$tf_data_conf_fim&tf_store_id=$tf_store_id";
$varsel .= "&tf_cliente_email=$tf_cliente_email&tf_amount=$tf_amount";
$varsel .= "&tf_d_forma_pagto=$tf_d_forma_pagto&tf_v_codigo=$tf_v_codigo&tf_v_order=$tf_v_order&tf_v_email=$tf_v_email";
$varsel .= "&tf_v_codigo_include=$tf_v_codigo_include&tf_v_order_include=$tf_v_order_include";
$varsel .= "&tf_data_concilia_ini=$tf_data_concilia_ini&tf_data_concilia_fim=$tf_data_concilia_fim";
$varsel .= "&tf_confirmed=$tf_confirmed&tf_v_status=$tf_v_status";
//echo "tf_cliente_email: '".$tf_cliente_email."'<br>";

$sql_where = "";
//echo "tf_d_forma_pagto: '".$tf_d_forma_pagto."'<br>";
//echo "tf_v_codigo: '".$tf_v_codigo."'<br>";
//echo "tf_confirmed: '".$tf_confirmed."'<br>";
if(!($tf_data_ini && $tf_data_fim)) {
        $tf_data_ini = date("d/m/Y");
        $tf_data_fim = date("d/m/Y");
        $sql_where .= "and ip.ip_data_inclusao between '".formata_data_ts_integracao($tf_data_ini)." 00:00:00' and '".formata_data_ts_integracao($tf_data_fim)." 23:59:59' ";
}
//echo "tf_data_ini: '".$tf_data_ini."'<br>";
//echo "tf_data_fim: '".$tf_data_fim."'<br>";

if(!isset($tf_v_codigo_include)) $tf_v_codigo_include = "1";
if(!isset($tf_v_order_include)) $tf_v_order_include = "1";

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
                                $msg = "Código da venda deve ser numérico ou lista de números separada por vírgulas (1).\n";
                        }
                }
        if($msg == "")
                if($tf_v_order){
                        // order_id de Bigpoint contem caracteres alfanumericos -> testa tipo 3
                        if(!is_csv_numeric_global($tf_v_order, 3)) {
                                $msg = "Código da ordem deve ser alfanumérico ou lista de alfanumericos separada por vírgulas (2).\n";
                        }
                }

        //------------------------------------------------------------------------------------------------------------------
        if($msg == ""){

                $sql_where = "";

//echo "$tf_data_ini - $tf_data_fim<br>";
                $filtro = array();
                if($tf_data_ini && $tf_data_fim) {
                        $filtro['dataMin'] = $tf_data_ini;
                        $filtro['dataMax'] = $tf_data_fim;
                        $sql_where .= "and ip.ip_data_inclusao between '".formata_data_ts_integracao($filtro['dataMin'])." 00:00:00' and '".formata_data_ts_integracao($filtro['dataMax'])." 23:59:59' ";
                }
                if($tf_data_conf_ini && $tf_data_conf_fim) {
                        $filtro['dataConfMin'] = $tf_data_conf_ini;
                        $filtro['dataConfMax'] = $tf_data_conf_fim;
                        $sql_where .= "and ip.ip_data_confirmed between '".formata_data_ts_integracao($filtro['dataConfMin'])." 00:00:00' and '".formata_data_ts_integracao($filtro['dataConfMax'])." 23:59:59' ";
                }
                if($tf_data_concilia_ini && $tf_data_concilia_fim) {
                        $filtro['dataConciliaMin'] = $tf_data_concilia_ini;
                        $filtro['dataConciliaMax'] = $tf_data_concilia_fim;
//				$sql_where .= "and vg.vg_data_concilia between '".formata_data_ts_integracao($filtro['dataConciliaMin'])." 00:00:00' and '".formata_data_ts_integracao($filtro['dataConciliaMax'])." 23:59:59' ";
                }

                if($tf_store_id) {
                        $filtro['store_id'] = $tf_store_id;
                        $sql_where .= "and ip.ip_store_id = '".$filtro['store_id']."' ";
                }

                if($tf_cliente_email) {
                        $filtro['cliente_email'] = $tf_cliente_email;
                        $sql_where .= "and ip.ip_client_email = '".$filtro['cliente_email']."' ";
                }

                if($tf_amount) {
                        $filtro['amount'] = $tf_amount;
                        $sql_where .= "and ip.ip_amount = ".(100*$filtro['amount'])." ";
                }
                if($tf_v_status) {
                        $filtro['vg_status'] = $tf_v_status;
                }
                if($tf_d_forma_pagto) {
                        $filtro['vg_forma_pagto'] = $tf_d_forma_pagto;
//echo "filtro['vg_forma_pagto']: '".$filtro['vg_forma_pagto']."'<br>";
                }
                if($tf_v_codigo) {
                        $filtro['vg_id'] = $tf_v_codigo;
                }
                if($tf_v_order) {
                        $filtro['order_id'] = $tf_v_order;
                }
                if($tf_v_email) {
                        $filtro['client_email_txt'] = $tf_v_email;
                }
                if($tf_confirmed) {
                        $filtro['confirmed'] = $tf_confirmed;
                }

                if($tf_v_codigo_include=="1" || $tf_v_codigo_include=="-1") {
                        $filtro['tf_v_codigo_include'] = $tf_v_codigo_include;
                }
                if($tf_v_order_include=="1" || $tf_v_order_include=="-1") {
                        $filtro['tf_v_order_include'] = $tf_v_order_include;
                }

                $rs_pedidos = null;
                $ret = obter($filtro, null, $rs_pedidos);
                if($ret != "") $msg = $ret;
                else {
                        $n_rows = pg_num_rows($rs_pedidos);
//echo "n_rows: $n_rows<br>";

$b_lista = false;
                        if($n_rows == 0) {
                                $msg = "Nenhum registro de integração encontrado.\n";
                        } else {
                                if($b_lista) $s_ids = "";
                                while($rs_pedidos_row = pg_fetch_array($rs_pedidos)){
                                        $total_pedidos += $rs_pedidos_row['ip_amount'];
                                        if($b_lista) $s_ids .= $rs_pedidos_row['ip_vg_id'].", ";
                                }
                                $total_pedidos /= 100;

if($b_lista) {
if(b_IsUsuarioWagner()) { 
echo "$s_ids<br>";
}
}

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

                                $ret = obter($filtro, $orderBy, $rs_pedidos);
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
require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_data_ini','tf_data_fim',optDate);
        setDateInterval('tf_data_conf_ini','tf_data_conf_fim',optDate);
        setDateInterval('tf_data_concilia_ini','tf_data_concilia_fim',optDate);
        
    });

function open_notify_window(ip_id, store_id, order_id) { 
	window.open('/gamer/integracao/com_integracao_notificacao_manual.php?ip_id='+ip_id+'&store_id='+store_id+'&order_id='+order_id,'mywindow', 'width=1000,height=500');
}

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
		<form name="form1" method="post" action="com_pesquisa_integracao.php">
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          </tr>
			<?php if($msg != ""){?>
				<tr class="texto"><td align="center"><font color="#FF0000"><?php echo $msg?></font></td></tr>
			<?php }?>
		</table>
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="6" bgcolor="#ECE9D8" class="texto">Pedidos de integração</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data de Inclusão:</font></td>
            <td class="texto">
              <input name="tf_data_ini" type="text" class="form" id="tf_data_ini" value="<?php echo $tf_data_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_data_fim" type="text" class="form" id="tf_data_fim" value="<?php echo $tf_data_fim ?>" size="9" maxlength="10">
			</td>
            <td class="texto">Data de Confirmação:</font></td>
            <td class="texto">
              <input name="tf_data_conf_ini" type="text" class="form" id="tf_data_conf_ini" value="<?php echo $tf_data_conf_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_data_conf_fim" type="text" class="form" id="tf_data_conf_fim" value="<?php echo $tf_data_conf_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data de Conciliação:</font></td>
            <td colspan="3" class="texto">
              <input name="tf_data_concilia_ini" type="text" class="form" id="tf_data_concilia_ini" value="<?php echo $tf_data_concilia_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_data_concilia_fim" type="text" class="form" id="tf_data_concilia_fim" value="<?php echo $tf_data_concilia_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Número do Pedido:</font></td>
            <td class="texto">
				<input name="tf_v_codigo" type="text" class="form2" value="<?php echo str_replace("'", "", $tf_v_codigo) ?>" size="20"> 
				<select name="tf_v_codigo_include">
					<option value="1"<?php if ($tf_v_codigo_include=="1") echo " selected"?>>Incluir lista</option>
					<option value="-1"<?php if ($tf_v_codigo_include=="-1") echo " selected"?>>EXCLUIR lista</option>
				</select>
			</td>
            <td class="texto">Número da ordem:</font></td>
            <td class="texto">
				<input name="tf_v_order" type="text" class="form2" value="<?php echo $tf_v_order?>" size="20"> 
				<select name="tf_v_order_include">
					<option value="1"<?php if ($tf_v_order_include=="1") echo " selected"?>>Incluir lista</option>
					<option value="-1"<?php if ($tf_v_order_include=="-1") echo " selected"?>>EXCLUIR lista</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Email cliente:</font></td>
            <td class="texto">
				<input name="tf_v_email" type="text" class="form2" value="<?php echo $tf_v_email?>" size="40" maxlength="256">			
			</td>
            <td class="texto">Confirmado:</font></td>
            <td class="texto">
				<select name="tf_confirmed" class="form2">
					<option value="" <?php if($tf_confirmed == "") echo "selected" ?>>Selecione</option>
					<option value="2" <?php if ($tf_confirmed == "2") echo "selected";?>>1 - Sim (Completo)</option>
					<option value="1" <?php if ($tf_confirmed == "1") echo "selected";?>>0 - Não (com venda)</option>
					<option value="-1" <?php if ($tf_confirmed == "-1") echo "selected";?>>0 - Não (sem venda)</option>
				</select>
			</td>
          </tr>

			<tr bgcolor="#F5F5FB"> 
				<td class="texto"><nobr>Forma de Pagamento:</nobr></font></td>
				<td colspan="3">
					<select name="tf_d_forma_pagto" class="form2">
						<option value="" <?php if($tf_d_forma_pagto == "") echo "selected" ?>>Selecione</option>
						<option value="X" <?php if($tf_d_forma_pagto == "X") echo "selected" ?>>Todas as formas de pagamento online</option>
						<option value="Y" <?php if($tf_d_forma_pagto == "Y") echo "selected" ?>>Depósito e Boleto (sem online)</option>
						<?php foreach ($FORMAS_PAGAMENTO_DESCRICAO as $formaId => $formaNome){ ?>
							<option value="<?php echo $formaId; ?>" <?php if ($tf_d_forma_pagto == $formaId) echo "selected";?>><?php echo $formaId . " - " . $formaNome; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>


          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Parceiros</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Parceiros:</td>
            <td>
				<select name="tf_store_id" class="form2">
					<option value="" <?php if($tf_ip_store_id == "") echo "selected" ?>>Selecione</option>
					<?php if($rs_parceiros) while($rs_parceiros_row = pg_fetch_array($rs_parceiros)){ ?>					
					<option value="<?php echo $rs_parceiros_row['parceiro']; ?>" <?php if ($tf_store_id == $rs_parceiros_row['parceiro']) echo "selected";?>><?php echo getPartner_name_By_ID($rs_parceiros_row["parceiro"])." (ID: ".$rs_parceiros_row["parceiro"].") ".$rs_parceiros_row["n"]." registro".(($rs_parceiros_row["n"]>1)?"s":"")." "; ; ?></option>
					<?php } ?>
				</select>
			</td>
            <td width="100" class="texto">Cliente Email:</td>
			<td>
				<input type="text" name="tf_cliente_email" value="<?php echo $tf_cliente_email ?>" size="30">
			</td>
		  </tr>

          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Venda</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Valores:</td>
            <td>
              <input name="tf_amount" type="text" class="form" id="tf_amount" value="<?php echo $tf_amount ?>" size="9" maxlength="10">
			</td>
            <td width="100" class="texto">Status Venda:</td>
			<td>
				<select name="tf_v_status" class="form2">
					<option value="" <?php if($tf_v_status == "") echo "selected" ?>>Selecione</option>
					<?php foreach ($GLOBALS['STATUS_VENDA_DESCRICAO'] as $statusId => $statusNome){ ?>
						<option value="<?php echo $statusId; ?>" <?php if ($tf_v_status == $statusId) echo "selected";?>><?php echo $statusId . " - " . substr($statusNome, 0, strpos($statusNome, '.')); ?></option>
					<?php } ?>
				</select>
			</td>
		  </tr>
		
		</table>

        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td> 
          </tr>
		</table>
		</form>
</div></div>
		<?php if($n_rows > 0) { ?>
        <table class="table txt-preto fontsize-pp">
                <tr> 
                  <td colspan="14" class="texto"> 
                    Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                    a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $n_rows ?></strong></font> 
                    <span id="txt_totais"></span>
                  </td>
                </tr>
                <?php $ordem = ($ordem == 1)?2:1; ?>
                <tr  bgcolor="#ECE9D8"> 
                  <td align="center"><strong><font class="texto">C&oacute;d.</font>
                    </strong></td>
                  <td align="center"><strong><font class="texto">Data de Inclusão</font>
                    </strong></td>
                  <td align="center"><strong><font class="texto">Parceiro</font></strong></td>

                  <td align="center"><strong><font class="texto">client_id</font></strong></td>
                  <td align="center"><strong><font class="texto">Usuário</font></strong></td>

                  <td align="center"><strong><font class="texto">product_id</font></strong></td>
                  <td align="center"><strong><font class="texto">Valor</font></strong></td>

                  <td align="center"><strong><font class="texto">vg_id</font></strong></td>
                  <td align="center"><strong><font class="texto">order_id</font></strong></td>
                  <td align="center"><strong><font class="texto">Último Status</font></strong></td>
                  <td align="center"><strong><font class="texto">Pagto Tipo</font></strong></td>

                  <td align="center"><strong><font class="texto">sonda status</font></strong></td>
                  <td align="center"><strong><font class="texto"><nobr>Data confirmed</nobr></font></strong></td>

                  <td align="center"><strong><font class="texto"><nobr>Histórico atualizações registro Cód</nobr></font></strong></td>

                </tr>
              <?php
                  $cor_hover = "#CCFFCC";
                  $cor1 = "#FFFFFF";
                  $cor2 = "#FFFFFF";
                  $cor3 = "#CCCCCC";
                  while($rs_pedidos_row = pg_fetch_array($rs_pedidos)){
                      $cor1 = ($cor1 == $cor2)?$cor3:$cor2;
                      $total_pedidos_pagina += $rs_pedidos_row['ip_amount'];
              ?>
                <tr bgcolor="<?php echo $cor1 ?>" valign="top" onmouseover="bgColor='#CFDAD7'" onmouseout="bgColor='<?php echo $cor1 ?>'"> 
                  <td class="texto" width="50" align="center"><?php echo $rs_pedidos_row['ip_id'] ?></td>
                  <td class="texto" width="100" align="center"><nobr><?php echo substr($rs_pedidos_row['ip_data_inclusao'],0,19) ?></nobr></td>

                  <td class="texto" width="100" align="center"><?php echo "<nobr>".getPartner_name_By_ID($rs_pedidos_row['ip_store_id']) . " (ID: ".$rs_pedidos_row['ip_store_id'].")</nobr>" ?></td>
                  <td class="texto" width="100" align="center"><?php echo $rs_pedidos_row['ip_client_id']?></td>
                  <td class="texto" width="100" align="center"><?php echo $rs_pedidos_row['ip_client_email']?></td>

                  <td class="texto" width="100" align="center"><?php if($rs_pedidos_row['ip_product_id']) { ?><a href="/gamer/produtos/com_produto_detalhe.php?produto_id=<?php echo $rs_pedidos_row['ip_product_id']?>"><?php echo $rs_pedidos_row['ip_product_id']?></a><?php } else { echo $rs_pedidos_row['ip_product_id'];} ?></td>
                  <td class="texto" width="100" align="center"><font style='color:blue'><?php echo "R$".number_format(($rs_pedidos_row['ip_amount']/100), 2, '.', '.')?></font></td>

                  <td class="texto" width="100" align="center"><?php echo (($rs_pedidos_row['ip_vg_id']>0)?"<a href='/gamer/vendas/com_venda_detalhe.php?venda_id=" . $rs_pedidos_row['ip_vg_id'] . "&fila_ncamp=vg_data_inclusao&fila_ordem=1&BtnSearch=1&tf_v_codigo=" . $rs_pedidos_row['ip_vg_id'] . "' target='_blank'>":"") . $rs_pedidos_row['ip_vg_id'] . (($rs_pedidos_row['ip_vg_id']>0)?"</a>":"") ?></td>
                  <td class="texto" width="100" align="center"><?php echo $rs_pedidos_row['ip_order_id'] ?></td>

                  <td class="texto" width="100" align="center" title="<?php echo (in_array($rs_pedidos_row['vg_ultimo_status'], $STATUS_VENDA) ? $STATUS_VENDA_DESCRICAO[$rs_pedidos_row['vg_ultimo_status']] : (($rs_pedidos_row['vg_ultimo_status']=="") ? "Empty" : "Desconhecido&nbsp;(".$rs_pedidos_row['vg_ultimo_status'].")")) ?>" style="<?php echo (($rs_pedidos_row['vg_ultimo_status']=="5") ? "color:blue" : (($rs_pedidos_row['vg_ultimo_status']=="6") ? "color:lightgray" : "" ) ) ?>"><?php echo (($rs_pedidos_row['vg_ultimo_status']=="") ? "-" : $rs_pedidos_row['vg_ultimo_status'] ) ?></td>
                  <td class="texto" width="100" align="center" title='<?php echo $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$rs_pedidos_row['vg_pagto_tipo']] ?>'><?php echo $rs_pedidos_row['vg_pagto_tipo'] ?></td>

                  <td class="texto" width="100" align="center" title="<?php echo (($rs_pedidos_row['ip_status_sonda_data'])?"status_sonda_data: ".$rs_pedidos_row['ip_status_sonda_data']:"-") ?>"><?php echo $rs_pedidos_row['ip_status_sonda'] ?></td>
                  <td class="texto" width="100" align="center"><nobr><?php echo substr($rs_pedidos_row['ip_data_confirmed'],0,19) ?></nobr></td>

                    <td class="texto" width="100" align="center"><?php 

//								$time_start_iph = getmicrotime();

                          $sql  = "select * ";
                          $sql .= "from tb_integracao_pedido_historico iph ";
                          $sql .= " where 1=1 ";
                          $sql .= " and iph.iph_ip_id = '" . SQLaddFields($rs_pedidos_row['ip_id'], "") . "' ";
                          $sql .= " and iph.iph_ip_store_id = '" . SQLaddFields($rs_pedidos_row['ip_store_id'], "") . "' ";
                          $sql .= " and iph.iph_ip_order_id = '" . SQLaddFields($rs_pedidos_row['ip_order_id'], "") . "' ";
                          $sql .= " and iph.iph_ip_vg_id = '" . SQLaddFields($rs_pedidos_row['ip_vg_id'], "") . "' ";
                          // $sql .= " and iph.iph_ip_vg_id >0 ";
                          $sql .= " order by iph_data_inclusao desc";
//if($rs_pedidos_row['ip_id']==90992) {
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//}
//die("Stop");
//if(b_IsUsuarioReinaldo()) { 
//}

                          $rs = SQLexecuteQuery($sql);

                          if(!$rs || pg_num_rows($rs) == 0) {
                              echo "<p class='texto'>Sem registros</p>\n";
                          } else {
                              echo "<table width='100%' cellpadding='1' cellspacing='1' border='1' bordercolor='#0066CC' style='border-collapse:collapse;' align='center'>\n <tr class='texto' align='center'> <td><b>vg_id</b></td> <td><b>data_inclusao</b></td> <td><b>confirmed</b></td></tr>\n";
                              while($rs_row = pg_fetch_array($rs)){
                                  echo "<tr class='texto' align='center'>\n";
                                  echo "<td title='ipnh_id: ".$rs_row['iph_id'].", ipnh_ip_id: ".$rs_row['iph_ip_id']."'>" . (($rs_row['iph_ip_vg_id']>0)?"<a href='/gamer/vendas/com_venda_detalhe.php?venda_id=" . $rs_row['iph_ip_vg_id'] . "&fila_ncamp=vg_data_inclusao&fila_ordem=1&BtnSearch=1&tf_v_codigo=" . $rs_row['iph_ip_vg_id'] . "' target='_blank'>":"") . $rs_row['iph_ip_vg_id'] . (($rs_row['iph_ip_vg_id']>0)?"</a>":"") . "</td>\n";
                                  echo "<td><nobr>" . substr($rs_row['iph_data_inclusao'],0,19) . "</nobr></td>\n";
                                  echo "<td style='". (($rs_row['iph_ip_status_confirmed']=='1') ? "color:blue" : "" ) ."'>" . $rs_row['iph_ip_status_confirmed'] . "</td>\n";
                                  echo "</tr>\n";
                              }
                              echo "</table>\n";
                          }

                          //echo "<br>".$search_msg . number_format(getmicrotime() - $time_start_iph, 2, '.', '.') . $search_unit
                      ?></td>
                  <td class="texto" width="100" align="center" valign="middle"><?php 
                          if($rs_pedidos_row['confirmed']=='1') {
                              echo "<font style='background-color:yellow; color:blue'>OK</font><br>";
                              echo "<input type='button' value='ReNotify' title='Notify manual de novo para\n order_id: ".$rs_pedidos_row['ip_order_id']."\n do parceiro \"".$rs_pedidos_row['ip_store_id']."\"' class='texto' style='color:red' onClick='open_notify_window(".$rs_pedidos_row['ip_id'].", \"".$rs_pedidos_row['ip_store_id']."\", \"".$rs_pedidos_row['ip_order_id'] ."\");'>";
                          } elseif($rs_pedidos_row['vg_ultimo_status']=="5") {
                              echo "<input type='button' value='Notify' title='Notify manual para\n order_id: ".$rs_pedidos_row['ip_order_id']."\n do parceiro \"".$rs_pedidos_row['ip_store_id']."\"' class='texto' style='color:red' onClick='open_notify_window(".$rs_pedidos_row['ip_id'].", \"".$rs_pedidos_row['ip_store_id']."\", \"".$rs_pedidos_row['ip_order_id'] ."\");'>";
                          } else {
                              echo "&nbsp;";
                          }
                      ?></td>


              <?php 	
                  }
                  $total_pedidos_pagina /= 100;
                  if($n_rows>$max) {
              ?>
                <tr bgcolor="#ECE9D8"> 
                  <td colspan="5" class="texto">&nbsp;</font></td>
                  <td class="texto"><b>Subtotal:</b></font></td>
                  <td class="texto"><font style='color:blue'>R$<?php echo number_format($total_pedidos_pagina, 2, '.', '.') ?></font></td>
                  <td colspan="7" class="texto">&nbsp;</font></td>
                </tr>
              <?php 	
                  }
              ?>
                <tr bgcolor="#ECE9D8"> 
                  <td colspan="5" class="texto">&nbsp;</font></td>
                  <td class="texto"><b>Total:</b></font></td>
                  <td class="texto">
                      <font style='color:blue'>R$<?php echo number_format($total_pedidos, 2, '.', '.') ?></font>
                      <script language="JavaScript">
                        document.getElementById('txt_totais').innerHTML = '( R$ <?php echo number_format($total_pedidos_pagina, 2, ',', '.') ?> / R$ <?php echo number_format($total_pedidos, 2, ',', '.') ?>)';
                        </script>
                  </td>
                  <td colspan="7" class="texto">&nbsp;</font></td>
                </tr>
                <tr bgcolor="#ECE9D8"> 
                  <td colspan="5" class="texto">&nbsp;</font></td>
                  <td class="texto"><b>&nbsp;</b></font></td>
                  <td class="texto"><font style='color:blue'>R$<?php echo number_format($total_pedidos/$n_rows, 2, '.', '.') ?>/pedido</font></td>
                  <td colspan="7" class="texto">&nbsp;</font></td>
                </tr>
                <tr> 
                  <td colspan="14" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
                </tr>
              <?php paginacao_query($inicial, $n_rows, $max, 100, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
              </table>
          <?php  }  ?>
<div>
    <div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
<?php
	function obter($filtro, $orderBy, &$rs){

//echo "<pre>".print_r($filtro, true)."</pre><br>";
		$ret = "";
		$filtro = array_map("strtoupper", $filtro);

		$sql  = "select ip.* ";
		$sql .= ", vg.vg_ultimo_status, vg.vg_pagto_tipo, \n";
		$sql .= " coalesce((select iph_ip_status_confirmed
									from tb_integracao_pedido_historico iph 
									where 1=1 
										and iph.iph_ip_id = ip.ip_id
										and iph.iph_ip_store_id = ip.ip_store_id
										and iph.iph_ip_order_id = ip.ip_order_id
										and iph.iph_ip_vg_id = ip.ip_vg_id
										and iph.iph_ip_vg_id >0
										and iph.iph_ip_status_confirmed = 1
									order by ip.ip_data_inclusao desc
									limit 1
										), 0) as confirmed, \n ";
		$sql .= "coalesce(vg_id, 0) as vg_id \n";
//		$sql .= ", pc.status ";
		$sql .= "from tb_integracao_pedido ip \n";
//		$sql .= "	left outer join tb_pag_compras pc on ip.ip_vg_id = pc.idvenda ";
		$sql .= "	left outer join tb_venda_games vg on ip.ip_vg_id = vg.vg_id \n";
		if(!is_null($filtro) && $filtro != ""){
		
			if(!is_null($filtro['opr'])) {
			}

			if(!is_null($filtro['dataMin']) && !is_null($filtro['dataMax'])){
//echo "tf_data_ini: '".$filtro['dataMin']."' - tf_data_fim: '".$filtro['dataMax']."' <br>";
				$filtro['dataMin'] = formata_data_ts_integracao($filtro['dataMin']);
				$filtro['dataMax'] = formata_data_ts_integracao($filtro['dataMax']);
//echo "tf_data_ini: '".$filtro['dataMin']."' - tf_data_fim: '".$filtro['dataMax']."' <br>";
			}			
			if(!is_null($filtro['dataConfMin']) && !is_null($filtro['dataConfMax'])){
//echo "tf_data_conf_ini: '".$filtro['dataConfMin']."' - tf_data_fim: '".$filtro['dataConfMax']."' <br>";
				$filtro['dataConfMin'] = formata_data_ts_integracao($filtro['dataConfMin']);
				$filtro['dataConfMax'] = formata_data_ts_integracao($filtro['dataConfMax']);
//echo "tf_data_conf_ini: '".$filtro['dataConfMin']."' - tf_data_fim: '".$filtro['dataConfMax']."' <br>";
			}			
			if(!is_null($filtro['dataConciliaMin']) && !is_null($filtro['dataConciliaMax'])){
//echo "tf_data_conf_ini: '".$filtro['dataConfMin']."' - tf_data_fim: '".$filtro['dataConfMax']."' <br>";
				$filtro['dataConciliaMin'] = formata_data_ts_integracao($filtro['dataConciliaMin']);
				$filtro['dataConciliaMax'] = formata_data_ts_integracao($filtro['dataConciliaMax']);
//echo "tf_data_conf_ini: '".$filtro['dataConfMin']."' - tf_data_fim: '".$filtro['dataConfMax']."' <br>";
			}			

			$sql .= " where 1=1 ";
//			$sql .= " and (not idvenda = 0) ";
			
//			$sql .= " and (" . (is_null($filtro['dataMin']) || is_null($filtro['dataMax'])?1:0);
//			$sql .= "=1 or ca.data between " . SQLaddFields($filtro['dataMin'], "") . " and " . SQLaddFields($filtro['dataMax'], "") . ")";

			if($filtro['dataMin'] && $filtro['dataMax']) {
				$sql .= " and (" . (is_null($filtro['dataMin']) || is_null($filtro['dataMax'])?1:0);
				$sql .= "=1 or ip.ip_data_inclusao between '".$filtro['dataMin']." 00:00:00' and '".$filtro['dataMax']." 23:59:59') \n";
			}
			if($filtro['dataConfMin'] && $filtro['dataConfMax']) {
				$sql .= " and (" . (is_null($filtro['dataConfMin']) || is_null($filtro['dataConfMax'])?1:0);
				$sql .= "=1 or ip.ip_data_confirmed between '".$filtro['dataConfMin']." 00:00:00' and '".$filtro['dataConfMax']." 23:59:59') \n";
			}
			if($filtro['dataConciliaMin'] && $filtro['dataConciliaMax']) {
				$sql .= " and (" . (is_null($filtro['dataConciliaMin']) || is_null($filtro['dataConciliaMax'])?1:0);
				$sql .= "=1 or vg.vg_data_concilia between '".$filtro['dataConciliaMin']." 00:00:00' and '".$filtro['dataConciliaMax']." 23:59:59') \n";
			}

			$sql .= " and (" . (is_null($filtro['store_id'])?1:0);
			$sql .= "=1 or ip.ip_store_id = '" . SQLaddFields($filtro['store_id'], "") . "') \n";


//$sql_where .= "and ip.ip_client_email = '".$filtro['cliente_email']."' ";

			$sql .= " and (" . (is_null($filtro['cliente_email'])?1:0);
			$sql .= "=1 or upper(ip.ip_client_email) = '" . SQLaddFields(($filtro['cliente_email']), "") . "') \n";

			$sql .= " and (" . (is_null($filtro['amount'])?1:0);
			$sql .= "=1 or ip.ip_amount = '" . SQLaddFields(($filtro['amount']*100), "") . "') \n";

			if($filtro['vg_forma_pagto']) {
				if($filtro['vg_forma_pagto']=="X") {
//echo getListaCodigoNumericoParaPagtoOnline()."<br>";	// -> 5,6,7,9,10,13,11,12,999
//echo getListaCharacterParaPagtoOnline()."<br>";	//	-> '5','6','7','9','A','E','B','P','Z'

					$sql .= " and (" . (is_null($filtro['vg_forma_pagto'])?1:0);
					$sql .= "=1 or vg_pagto_tipo in (" . getListaCodigoNumericoParaPagtoOnline() . ") ) \n";
				} elseif($filtro['vg_forma_pagto']=="Y") {

					$sql .= " and (" . (is_null($filtro['vg_forma_pagto'])?1:0);
					$sql .= "=1 or vg_pagto_tipo in (".$GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF'].", ".$GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'].") ) \n";
				} else {
					$sql .= " and (" . (is_null($filtro['vg_forma_pagto'])?1:0);
					$sql .= "=1 or vg_pagto_tipo = " . getCodigoNumericoParaPagto($filtro['vg_forma_pagto']) . ") \n";
				}
			}

			$sql .= " and (" . (is_null($filtro['vg_status'])?1:0);
			$sql .= "=1 or vg.vg_ultimo_status = " . SQLaddFields($filtro['vg_status'], "") . ") \n";

			if($filtro['vg_id']) {
				$sql .= " and (" . (is_null($filtro['vg_id'])?1:0);
				$sql .= "=1 or (ip.ip_vg_id ".(($filtro['tf_v_codigo_include']=="-1")?"not":"")." in (" . str_replace("'", "", $filtro['vg_id']) . ")) ) \n";
			}
			if($filtro['order_id']) {

				$sql .= " and (" . (is_null($filtro['order_id'])?1:0);
				$sql .= "=1 or (upper(ip.ip_order_id) ".(($filtro['tf_v_order_include']=="-1")?"not":"")." in ('" . str_replace(",", "','", str_replace(" ", "", $filtro['order_id'])) . "')) ) \n";
			}
			if($filtro['client_email_txt']) {
				$sql .= " and (" . (is_null($filtro['client_email_txt'])?1:0);
				$sql .= "=1 or (upper(ip.ip_client_email) like '%" . strtoupper($filtro['client_email_txt']) . "%') ) \n";
			}
			if(!is_null($filtro['confirmed'])) {
					$sql_subquery = "	exists(
									select iph_ip_status_confirmed
									from tb_integracao_pedido_historico iph 
									where 1=1 
									and iph.iph_ip_id = ip.ip_id
									and iph.iph_ip_store_id = ip.ip_store_id
									and iph.iph_ip_order_id = ip.ip_order_id
									and iph.iph_ip_vg_id = ip.ip_vg_id
									and iph.iph_ip_status_confirmed = 1
								)";

					if($filtro['confirmed']==-1) {
						// -1 -> "0 - Não (sem venda)"
						$sql .= "and coalesce(vg_id, 0)=0 ";
						$sql .= "and not ($sql_subquery) \n";
					} elseif($filtro['confirmed']==1) {
						// 1 -> "0 - Não (com venda)"
						$sql .= "and coalesce(vg_id, 0)>0 ";
						$sql .= "and not ($sql_subquery) \n";
					} elseif($filtro['confirmed']==2) {
						// 2 -> "1 - Sim (Completo)"
						$sql .= "and coalesce(vg_id, 0)>0 ";
						$sql .= "and ($sql_subquery) \n";
					}
			}

			$sql .= " and (" . (is_null($filtro['client_id'])?1:0);
			$sql .= "=1 or ip.ip_client_id = " . SQLaddFields($filtro['client_id'], "") . ")  \n";
		}

		if(!is_null($orderBy)) $sql .= " order by " . $orderBy;
//if(b_IsUsuarioWagner()) { 
//echo str_replace("\n", "<br>\n", $sql)."<hr>";
//die("Stop");
//}
		$rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter pedidos de integração(s).\n";

		return $ret;

	}
?>