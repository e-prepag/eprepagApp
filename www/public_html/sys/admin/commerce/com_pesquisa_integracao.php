<?php 
set_time_limit ( 3000 ) ;
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 


/*
	
	********************* ##### *********************
	
	NOTA:
	Registros com 
	
	iph_ip_status_confirmed = '9999'
	ip_status_confirmed = '9999'
	
	estão desativados devido a algum erro no sistema
	- Victor
	
	********************* ##### *********************

*/






//include "../../../../backoffice/web/bkov2_prepag/commerce/includes/classPrincipal.php"; 
//include "includes/classPrincipal.php"; 
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

require_once $raiz_do_projeto . "class/gamer/classIntegracao.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "includes/gamer/functions_pagto.php";
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//echo "<pre>".print_r($_POST, true)."</pre>";

//echo getListaCodigoNumericoParaPagtoOnline()."<br>";	// -> 5,6,7,9,10,13,11,12,999
//echo getListaCharacterParaPagtoOnline()."<br>";	//	-> '5','6','7','9','A','E','B','P','Z'
if($_SESSION["tipo_acesso_pub"]=='PU') {
        $tf_store_id = getPartner_Store_id_By_opr_codigo_ALL_CODES($_SESSION["opr_codigo_pub"]);
//	$tf_store_id = getPartner_Store_id_By_opr_codigo($_SESSION["opr_codigo_pub"]);
}
if  (($_SESSION['userlogin_bko']=="WAGNER")) {
echo "<div class='container'>(R) $tf_store_id<br></div>";
}


	$time_start = getmicrotime();
	
	if(!$ncamp)    $ncamp       = 'ip_data_inclusao';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 1;

//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $n_rows = 0;
	if($BtnSearch==LANG_INTEGRATION_SEARCH) {
		$inicial     = 0;
		$range       = 1;
		$n_rows = 0;
	}
	$total_pedidos = 0;
	$total_pedidos_pagina = 0;


	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "/sys/imagens/proxima.gif";
	$img_anterior = "/sys/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&BtnSearch=1";
	$varsel .= "&tf_data=$tf_data";
	$varsel .= "&tf_data_ini=$tf_data_ini&tf_data_fim=$tf_data_fim&tf_data_conf_ini=$tf_data_conf_ini&tf_data_conf_fim=$tf_data_conf_fim&tf_store_id=$tf_store_id";
	$varsel .= "&tf_cliente_email=$tf_cliente_email&tf_amount=$tf_amount";
	$varsel .= "&tf_d_forma_pagto=$tf_d_forma_pagto&tf_v_codigo=$tf_v_codigo&tf_v_order=$tf_v_order&tf_v_email=$tf_v_email";
	$varsel .= "&tf_data_concilia_ini=$tf_data_concilia_ini&tf_data_concilia_fim=$tf_data_concilia_fim";
//echo "tf_cliente_email: '".$tf_cliente_email."'<br>";

	$sql_where = "";
//echo "tf_d_forma_pagto: '".$tf_d_forma_pagto."'<br>";
//echo "tf_v_codigo: '".$tf_v_codigo."'<br>";
//echo "tf_confirmed: '".$tf_confirmed."'<br>";

	if(!($tf_data_concilia_ini && $tf_data_concilia_fim)) {
//		$tf_data_concilia_ini = date("d/m/Y");
//		$tf_data_concilia_fim = date("d/m/Y");
	}

	if($_SESSION["tipo_acesso_pub"]=='AT') {
		if(!($tf_data_ini && $tf_data_fim)) {
//			$tf_data_ini = date("d/m/Y");
//			$tf_data_fim = date("d/m/Y");
			$sql_where .= "and ip.ip_data_inclusao between '".formata_data_ts_integracao($tf_data_ini)." 00:00:00' and '".formata_data_ts_integracao($tf_data_fim)." 23:59:59' ";
		}
	}

//echo "tf_data_ini: '".$tf_data_ini."'<br>";
//echo "tf_data_fim: '".$tf_data_fim."'<br>";


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
				$sql_where .= "and ip.ip_store_id IN ('".$filtro['store_id']."') ";
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
			//Fixando filtro de confirmado para SIM
//			$tf_confirmed = 2;
			if($tf_confirmed) {
				$filtro['confirmed'] = $tf_confirmed;
			}

			$rs_pedidos = null;
			$ret = obter($filtro, null, $rs_pedidos);
			if($ret != "") $msg = $ret;
			else {
				$n_rows = pg_num_rows($rs_pedidos);
//echo "n_rows: $n_rows<br>";

				if($n_rows == 0) {
					$msg = "Nenhum registro de integração encontrado.\n";
				} else {
$b_lista = false;
					$lista_vg_id = "";
					$lista_ug_id = "";
					$lista_ug_email = "";
					$n_lista_ug_id = 0;
					$n_lista_ug_email = 0;
					$n_lista_id_ped = 0;
					while($rs_pedidos_row = pg_fetch_array($rs_pedidos)){
						$total_pedidos += $rs_pedidos_row['ip_amount'];

						// lista de vg_id
						$lista_vg_id .= $rs_pedidos_row['ip_vg_id'].", ";
						if(strpos($lista_ug_id, $rs_pedidos_row['vg_ug_id'])===false) {
							if($lista_ug_id!="") $lista_ug_id .= ", ";
							$lista_ug_id .= $rs_pedidos_row['vg_ug_id'];
							$n_lista_ug_id ++;
						}
						if(strpos($lista_ug_email, $rs_pedidos_row['ip_client_email'])===false) {
							if($lista_ug_email!="") $lista_ug_email .= ", ";
							$lista_ug_email .= $rs_pedidos_row['ip_client_email'];
							$n_lista_ug_email ++;
						}
					}
					$total_pedidos /= 100;
					$lista_id_ped = $lista_vg_id;
					$n_lista_vg_id = $n_rows;

if($b_lista) {
//if  (($_SESSION['userlogin_bko']=="REINALDO")) {
//echo "(R) $lista_vg_id<br>";
//}
}
					//Ordem
					$orderBy = $ncamp;
					if($ordem == 1){
						$orderBy .= " desc ";
						$img_seta = "/sys/imagens/seta_down.gif";
					} else {
						$orderBy .= " asc ";
						$img_seta = "/sys/imagens/seta_up.gif";
					}
			
					if(empty($flistall)) {
						$orderBy .= " limit ".$max; 
						$orderBy .= " offset ".$inicial;
					}
					else {
						$max = $n_rows;
					}
                     
					$ret = obter($filtro, $orderBy, $rs_pedidos);
					//var_dump($rs_pedidos);
                    //var_dump($orderBy);		
					if($ret != "") $msg = $ret;
					else {
						//echo "oi!!$max - $inicial - $n_rows < ".($max + $inicial)."<br>";
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

	//Clientes
//	$sql  = "select distinct ip_client_email as cliente, count(*) as n from tb_integracao_pedido ip where 1=1 ".$sql_where." group by ip_client_email order by ip_client_email;";
//echo "sql: $sql<br>";
//	$rs_clientes = SQLexecuteQuery($sql);
//	$n_clientes = pg_num_rows($rs_clientes);
?>
<link rel="stylesheet" href="/css/css.css" type="text/css">
<script language="javascript">

function open_notify_window(ip_id, store_id, order_id) { 
	window.open('https://' + '<?php echo $server_url_complete ?>' + '/gamer/integracao/com_integracao_notificacao_manual.php?ip_id='+ip_id+'&store_id='+store_id+'&order_id='+order_id,'mywindow', 'width=1000,height=500');
}

function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function getCSVData(){
	var csv_value = $("<div>").append( $("#ReportTable").eq(0).clone() ).html();
	//alert(csv_value);
	 $("#csv_text").val(csv_value);	
}
</script>
<!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    
    var optDate = new Object();
        optDate.interval = 1;
<?php
    if($_SESSION["tipo_acesso_pub"]=='AT') {
        echo "setDateInterval('tf_data_ini','tf_data_fim',optDate);";
        echo "setDateInterval('tf_data_conf_ini','tf_data_conf_fim',optDate);";
    }
?>
    setDateInterval('tf_data_concilia_ini','tf_data_concilia_fim',optDate);
});
</script>
</script>
<style>body{color:#737373;}</style>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_INTEGRATION_TITLE_PAGE; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
<?php 
                    if($msg != "")
                    {
?>
                    <div class="row txt-cinza">
                        <div class="col-md-12 txt-vermelho">
                            <span class="pull-left"><?php echo $msg?></span>
                        </div>
                    </div>
<?php 
                    }
?>
                <form name="form1" method="post" action="">
<?php
                    if($_SESSION["tipo_acesso_pub"]=='AT') 
                    {
?>                    
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_DATE_INCLUDE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_data_ini" type="text" class="form-control pull-left w95" id="tf_data_ini" value="<?php echo $tf_data_ini ?>" size="9" maxlength="10">
                            <span class="pull-left espacamento-laterais10"> até </span>
                            <input name="tf_data_fim" type="text" class="form-control pull-left w95" id="tf_data_fim" value="<?php echo $tf_data_fim ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_DATE_CONFIRM;?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_data_conf_ini" type="text" class="form-control pull-left w95" id="tf_data_conf_ini" value="<?php echo $tf_data_conf_ini ?>" size="9" maxlength="10">
                            <span class="pull-left espacamento-laterais10"> a </span>
                            <input name="tf_data_conf_fim" type="text" class="form-control pull-left w95" id="tf_data_conf_fim" value="<?php echo $tf_data_conf_fim ?>" size="9" maxlength="10">
                        </div>
                    </div>
<?php
                    }
?>
                    <div class="row txt-cinza  top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_DATE_CONCILIATION;?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_data_concilia_ini" type="text" class="form-control pull-left w95" id="tf_data_concilia_ini" value="<?php echo $tf_data_ini ?>" size="9" maxlength="10">
                            <span class="pull-left espacamento-laterais10"> até </span>
                            <input name="tf_data_concilia_fim" type="text" class="form-control pull-left w95" id="tf_data_concilia_fim" value="<?php echo $tf_data_fim ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_REQUEST_NUMBER;?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_v_codigo" type="text" class="form-control" value="<?php echo str_replace("'", "", $tf_v_codigo) ?>" size="20">
                        </div>
                    </div>
                    <div class="row txt-cinza  top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_ORDER_NUMBER;?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_v_order" type="text" class="form-control" value="<?php echo $tf_v_order?>" size="20">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_CONFIRMED;?></span>
                        </div>
                        <div class="col-md-3">
<?php
                        if($_SESSION["tipo_acesso_pub"]=='AT') 
                        {
?>
                            <select name="tf_confirmed" class="form-control">
                                <option value="" <?php if($tf_confirmed == "") echo "selected" ?>>Selecione</option>
                                <option value="2" <?php if ($tf_confirmed == "2") echo "selected";?>>1 - Sim (Completo)</option>
                                <option value="1" <?php if ($tf_confirmed == "1") echo "selected";?>>0 - Não (com venda)</option>
                                <option value="-1" <?php if ($tf_confirmed == "-1") echo "selected";?>>0 - Não (sem venda)</option>
                            </select>
<?php
                        } else 
                        {
                            echo LANG_INTEGRATION_YES;?><input type="hidden" name="tf_confirmed" class="form2" value="<?php echo $tf_confirmed;?>">
<?php
                        }
?>
                        </div>
                    </div>
<?php
                    if($_SESSION["tipo_acesso_pub"]=='AT') 
                    {
?>
                    <div class="row txt-cinza  top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_PAYMENT;?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="tf_d_forma_pagto" class="form-control">
                                <option value="" <?php if($tf_d_forma_pagto == "") echo "selected" ?>><?php echo LANG_INTEGRATION_SELECT;?></option>
                                <option value="L" <?php if($tf_d_forma_pagto == "L") echo "selected" ?>>L - <?php echo LANG_INTEGRATION_ALL_FORMS_PAYMENT;?></option>
                                <option value="D" <?php if($tf_d_forma_pagto == "D") echo "selected" ?>>D - <?php echo LANG_INTEGRATION_DEPOSIT_AND_BILLET;?></option>
                                <?php foreach ($FORMAS_PAGAMENTO_DESCRICAO as $formaId => $formaNome){ ?>
                                <option value="<?php echo $formaId; ?>" <?php if ($tf_d_forma_pagto == $formaId) echo "selected";?>><?php echo $formaId . " - " . $formaNome; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
<?php
                    }
?>
                    <div class="row txt-azul-claro bg-cinza-claro  top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_PARTNER;?></span>
                        </div>
                    </div>
                    <div class="row txt-cinza  top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_PARTNER;?></span>
                        </div>
                        <div class="col-md-3">
<?php 
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
?>
                            <?php echo $_SESSION["opr_nome"]?>
                            <input type="hidden" name="tf_store_id" id="tf_store_id" value="<?php echo $tf_store_id?>">
<?php 
                        } else 
                        {
?>
                            <select name="tf_store_id" class="form-control">
                                    <option value="" <?php if($tf_ip_store_id == "") echo "selected" ?>><?php echo LANG_INTEGRATION_SELECT;?></option>
                                    <?php if($rs_parceiros) while($rs_parceiros_row = pg_fetch_array($rs_parceiros)){ ?>					
                                    <option value="<?php echo $rs_parceiros_row['parceiro']; ?>" <?php if ($tf_store_id == $rs_parceiros_row['parceiro']) echo "selected";?>><?php echo getPartner_name_By_ID($rs_parceiros_row["parceiro"])." (ID: ".$rs_parceiros_row["parceiro"].") ".$rs_parceiros_row["n"]." ".LANG_INTEGRATION_RECORD.(($rs_parceiros_row["n"]>1)?"s":"")." "; ; ?></option>
                                    <?php } ?>
                            </select>
<?php 
                        } 
?>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_EMAIL_CLIENT;?></span>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="tf_cliente_email" value="<?php echo $tf_cliente_email ?>" size="30">
                        </div>
                    </div>
                    <div class="row txt-azul-claro bg-cinza-claro  top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_SALE;?></span>
                        </div>
                    </div>
                    <div class="row txt-cinza  top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_INTEGRATION_VALUES;?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_amount" type="text" class="form-control w66 pull-left" id="tf_amount" value="<?php echo $tf_amount ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-offset-5 col-md-2">
                            <input type="submit" name="BtnSearch" value="<?php echo LANG_INTEGRATION_SEARCH;?>" class="btn pull-right btn-success">
                        </div>
                    </div>
                    <div class="row txt-cinza  top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_LIST_ALL_REGISTERS;?></span>
                        </div>
                        <div class="col-md-3">
                            <input class="pull-left" type="checkbox" name="flistall" id="flistall" value="1"<?php if(!empty($flistall)) echo " checked"; ?>>
                        </div>
                    </div>
                </form>
                <div class="row txt-cinza  top10">
                    <div class="col-md-offset-11 col-md-1">
                        <form name="form3" id="form3" action="/includes/arquivoExcel.php" method="post">
                                    <input  type="image"  src="/imagens/exportToexcel.gif" onclick="getCSVData();" title="Download table in MS Excel format">
                                    <input type="hidden" name="csv_text" id="csv_text">
                        </form>
                    </div>
                </div>
            </div>
        
            <table border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td valign="top"> 
		<?php if($n_rows > 0) { ?>
        <table border="0" cellpadding="0" cellspacing="2">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
				  	<table width="100%" border='0' cellpadding="2" cellspacing="1" id="ReportTable" name="ReportTable">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          <?php echo LANG_INTEGRATION_SHOW_RESULTS;?> <strong><?php echo $inicial + 1 ?></strong> 
                          <?php echo LANG_INTEGRATION_UNTIL;?> <strong><?php echo $reg_ate ?></strong> <?php echo LANG_INTEGRATION_BY;?> <strong><?php echo $n_rows ?></strong> <span id="txt_totais" style="color:blue"></span></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr  bgcolor="#ECE9D8"> 
                         <?php
						  if($_SESSION["tipo_acesso_pub"]=='AT') {
						  ?>
						  <td align="center"><strong><font class="texto">Id.</font>
                          </strong></td>
						  <?php
							}
						  ?>
                        <td align="center"><strong><font class="texto"><?php echo LANG_INTEGRATION_DATE_INCLUDE;?></font>
                          </strong></td>
                        <td align="center"><strong><font class="texto"><?php echo LANG_INTEGRATION_PARTNER;?></font></strong></td>
                        <td align="center"><strong><font class="texto"><?php echo LANG_INTEGRATION_USER;?></font>
                          </strong></td>
                        <td align="center"><strong><font class="texto"><?php echo LANG_INTEGRATION_VALUES;?></font></strong></td>

                        <td align="center"><strong><font class="texto">vg_id</font></strong></td>
                        <td align="center"><strong><font class="texto">order_id</font></strong></td>
                        <?php
						if($_SESSION["tipo_acesso_pub"]=='AT') {
						?>
						<td align="center"><strong><font class="texto"><?php echo LANG_INTEGRATION_LAST_STATUS;?></font></strong></td>
                        <td align="center"><strong><font class="texto"><?php echo LANG_INTEGRATION_TYPE_PAYMENT;?></font></strong></td>
                        <?php
						}	
						?>
						<td align="center"><strong><font class="texto"><nobr><?php echo LANG_INTEGRATION_DATE_CONFIRM;?></nobr></font></strong></td>
					  
                        <!--td align="center"><strong><font class="texto"><nobr><?php echo LANG_INTEGRATION_HISTORICAL_RECORD_UPDATES;?> Id.</nobr></font></strong></td-->

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
                        <?php
						if($_SESSION["tipo_acesso_pub"]=='AT') {
						?>
						<td class="texto" width="50" align="center"><?php echo $rs_pedidos_row['ip_id'] ?></td>
                        <?php
						}
						?>
						<td class="texto" width="100" align="center"><nobr><?php echo substr($rs_pedidos_row['ip_data_inclusao'],0,19) ?></nobr></td>

                        <td class="texto" width="100" align="center"><?php echo "<nobr>".getPartner_name_By_ID($rs_pedidos_row['ip_store_id']) . " (ID: ".$rs_pedidos_row['ip_store_id'].")</nobr>" ?></td>
                        <td class="texto" width="100" align="center"><?php echo $rs_pedidos_row['ip_client_email']?></td>

                        <td class="texto" width="100" align="center"><font style='color:blue'><?php echo "R$".number_format(($rs_pedidos_row['ip_amount']/100), 2, ',', '.')?></font></td>

                        <td class="texto" width="100" align="center"><?php echo $rs_pedidos_row['ip_vg_id'] ?></td>
                        <td class="texto" width="100" align="center"><?php echo $rs_pedidos_row['ip_order_id'] ?></td>

						<?php
						if($_SESSION["tipo_acesso_pub"]=='AT') {
						?>
						<td class="texto" width="100" align="center" title="<?php echo (in_array($rs_pedidos_row['vg_ultimo_status'], $STATUS_VENDA) ? $STATUS_VENDA_DESCRICAO[$rs_pedidos_row['vg_ultimo_status']] : (($rs_pedidos_row['vg_ultimo_status']=="") ? "Empty" : "Desconhecido&nbsp;(".$rs_pedidos_row['vg_ultimo_status'].")")) ?>" style="<?php echo (($rs_pedidos_row['vg_ultimo_status']=="5") ? "color:blue" : (($rs_pedidos_row['vg_ultimo_status']=="6") ? "color:lightgray" : "" ) ) ?>"><?php echo (($rs_pedidos_row['vg_ultimo_status']=="") ? "-" : $rs_pedidos_row['vg_ultimo_status'] ) ?></td>
                        <td class="texto" width="100" align="center" title='<?php echo $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$rs_pedidos_row['vg_pagto_tipo']] ?>'><?php echo $rs_pedidos_row['vg_pagto_tipo'] ?></td>
						<?php
						}
						?>

						<td class="texto" width="100" align="center"><nobr><?php echo substr($rs_pedidos_row['ip_data_confirmed'],0,19) ?></nobr></td>
					<?php 	
						}
						$total_pedidos_pagina /= 100;
						if($n_rows>$max) {
					?>
                      <tr bgcolor="#ECE9D8"> 
                        <td colspan="3" class="texto">&nbsp;</font></td>
                        <td class="texto"><b>Subtotal:</b></font></td>
                        <td class="texto"><font style='color:blue'>R$<?php echo number_format($total_pedidos_pagina, 2, ',', '.') ?></font></td>
                        <td colspan="7" class="texto">&nbsp;</font></td>
                      </tr>
					<?php 	
						}
					?>
                      <tr bgcolor="#ECE9D8"> 
                        <td colspan="3" class="texto">&nbsp;</font></td>
                        <td class="texto"><b>Total:</b></font></td>
                        <td class="texto"><font style='color:blue'>R$<?php echo number_format($total_pedidos, 2, ',', '.') ?></font></td>
                        <td colspan="7" class="texto">&nbsp;</font></td>
                      </tr>
                      <tr bgcolor="#ECE9D8"> 
                        <td colspan="3" class="texto">&nbsp;</font></td>
                        <td class="texto"><b>&nbsp;</b></font></td>
                        <td class="texto"><font style='color:blue'>R$<?php echo number_format($total_pedidos/$n_rows, 2, ',', '.') ?>/pedido</font></td>
                        <td colspan="7" class="texto">&nbsp;</font></td>
                      </tr>
                      <tr> 
                        <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, ',', '.') . $search_unit ?></font></td>
                      </tr>
<script language="JavaScript">
  document.getElementById('txt_totais').innerHTML = '( <?php echo number_format($total_pedidos_pagina, 2, ',', '.') ?> / <?php echo number_format($total_pedidos, 2, ',', '.') ?>)';
</script>

					<?php paginacao_query($inicial, $n_rows, $max, 100, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
                    </table>
				  </td>
                </tr>
              </table>

<?php
	if($_SESSION["tipo_acesso_pub"]=='AT') {
?>
		     <table border="0" cellpadding="0" cellspacing="2">
				<tr bgcolor="D5D5DB"> 
					<td class="texto" align="right" colspan="4"><b>Lista de usuários que realizaram as compras:</b></td>
					<td class="texto" align="center" colspan="4">
						<input type="button" id="but_ids_show" value="Mostra Lista de IDs" onclick="$('#div_ids').show();$('#but_ids_show').hide();">
					</td>
					<td class="texto" align="center" colspan="3">
						<input type="button" id="but_emails_show" value="Mostra Lista de Emails" onclick="$('#div_emails').show();$('#but_emails_show').hide();">
					</td>
					<td class="texto" align="right" colspan="2"></td>
				</tr>
				<tr bgcolor="D5D5DB"> 
					<td class="texto" align="right" colspan="4"><b>Lista de Códs. de pedidos:</b></td>
					<td class="texto" align="center" colspan="4">
						<input type="button" id="but_ids_ped_show" value="Mostra Lista de Códs de Pedidos" onclick="$('#div_ids_ped').show();$('#but_ids_ped_show').hide();">
					</td>
					<td class="texto" align="center" colspan="3">&nbsp;
					</td>
					<td class="texto" align="right" colspan="2"></td>
				</tr>
				<tr bgcolor="D5D5DB"> 
					<td class="texto" align="left" colspan="13">
						<div id="div_ids" style="display:none;">
							Encontrados <?php echo $n_lista_ug_id ?> usuários. - <input type="button" id="but_ids_hide" value="Oculta Lista de IDs" onclick="$('#div_ids').hide(); $('#but_ids_show').show();"><br>
							<?php echo $lista_ug_id ?>
						</div>
						<div id="div_emails" style="display:none;">
							Encontrados <?php echo $n_lista_ug_email ?> usuários. - <input type="button" id="but_emails_hide" value="Oculta Lista de Emails" onclick="$('#div_emails').hide(); $('#but_emails_show').show();"><br>
							<?php echo $lista_ug_email ?>
						</div>
					</td>
				</tr>
				<tr bgcolor="D5D5DB"> 
					<td class="texto" align="left" colspan="13">
						<div id="div_ids_ped" style="display:none;">
							Encontrados <?php echo $n_lista_vg_id ?> pedidos. - <input type="button" id="but_ids_ped_hide" value="Oculta Lista de Códs de Pedidos" onclick="$('#div_ids_ped').hide(); $('#but_ids_ped_show').show();"><br>
							<?php echo $lista_vg_id ?>
						</div>
						<div id="div_emails" style="display:none;">&nbsp;</div>
					</td>
				</tr>
			  </table>
<?php
	}
?>


          <?php  }  ?>
    </td>
  </tr>
</table>
            </div>
    </div>
</div>
    
<?php
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>
</html>
<?php
	function obter($filtro, $orderBy, &$rs){

//echo "<pre>".print_r($filtro, true)."</pre><br>";
		$ret = "";
		$filtro = array_map("strtoupper", $filtro);

		$sql  = "select ip.*";
		$sql .= ", vg.vg_ultimo_status, vg.vg_pagto_tipo ";
		$sql .= ", coalesce((select iph_ip_status_confirmed
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
										), 0) as confirmed ";
		$sql .= ", coalesce(vg_id, 0) as vg_id ";
		$sql .= ", coalesce(vg_ug_id, 0) as vg_ug_id ";
//		$sql .= ", pc.status ";
		$sql .= "from tb_integracao_pedido ip ";
//		$sql .= "	left outer join tb_pag_compras pc on ip.ip_vg_id = pc.idvenda ";
		$sql .= "	left outer join tb_venda_games vg on ip.ip_vg_id = vg.vg_id ";
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
				$filtro['dataConciliaMin'] = formata_data_ts_integracao($filtro['dataConciliaMin']);
				$filtro['dataConciliaMax'] = formata_data_ts_integracao($filtro['dataConciliaMax']);
			}			

			$sql .= " where 1=1 ";
//			$sql .= " and (not idvenda = 0) ";
			
//			$sql .= " and (" . (is_null($filtro['dataMin']) || is_null($filtro['dataMax'])?1:0);
//			$sql .= "=1 or ca.data between " . SQLaddFields($filtro['dataMin'], "") . " and " . SQLaddFields($filtro['dataMax'], "") . ")";

			if($filtro['dataMin'] && $filtro['dataMax']) {
				$sql .= " and (" . (is_null($filtro['dataMin']) || is_null($filtro['dataMax'])?1:0);
				$sql .= "=1 or ip.ip_data_inclusao between '".$filtro['dataMin']." 00:00:00' and '".$filtro['dataMax']." 23:59:59') ";
			}
			if($filtro['dataConfMin'] && $filtro['dataConfMax']) {
				$sql .= " and (" . (is_null($filtro['dataConfMin']) || is_null($filtro['dataConfMax'])?1:0);
				$sql .= "=1 or ip.ip_data_confirmed between '".$filtro['dataConfMin']." 00:00:00' and '".$filtro['dataConfMax']." 23:59:59') ";
			}
			if($filtro['dataConciliaMin'] && $filtro['dataConciliaMax']) {
				$sql .= " and (" . (is_null($filtro['dataConciliaMin']) || is_null($filtro['dataConciliaMax'])?1:0);
				$sql .= "=1 or date(vg.vg_data_concilia) >= '".$filtro['dataConciliaMin']."' and date(vg.vg_data_concilia) <= '".$filtro['dataConciliaMax']."') ";
				//$sql .= "=1 or vg.vg_data_concilia between '".$filtro['dataConciliaMin']." 00:00:00' and '".$filtro['dataConciliaMax']." 23:59:59') ";
			}

			$sql .= " and (" . (is_null($filtro['store_id'])?1:0);
			$sql .= "=1 or ip.ip_store_id IN ('" . SQLaddFields($filtro['store_id'], "") . "') ) ";


//$sql_where .= "and ip.ip_client_email = '".$filtro['cliente_email']."' ";

			$sql .= " and (" . (is_null($filtro['cliente_email'])?1:0);
			$sql .= "=1 or upper(ip.ip_client_email) = '" . SQLaddFields(($filtro['cliente_email']), "") . "') ";

			$sql .= " and (" . (is_null($filtro['amount'])?1:0);
			$sql .= "=1 or ip.ip_amount = '" . SQLaddFields(($filtro['amount']*100), "") . "') ";

			if($filtro['vg_forma_pagto']) {
				if($filtro['vg_forma_pagto']=="X") {
//echo getListaCodigoNumericoParaPagtoOnline()."<br>";	// -> 5,6,7,9,10,13,11,12,999
//echo getListaCharacterParaPagtoOnline()."<br>";	//	-> '5','6','7','9','A','E','B','P','Z'

					$sql .= " and (" . (is_null($filtro['vg_forma_pagto'])?1:0);
					$sql .= "=1 or vg_pagto_tipo in (" . getListaCodigoNumericoParaPagtoOnline() . ") ) ";
				} elseif($filtro['vg_forma_pagto']=="Y") {

					$sql .= " and (" . (is_null($filtro['vg_forma_pagto'])?1:0);
					$sql .= "=1 or vg_pagto_tipo in (".$GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF'].", ".$GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'].") ) ";
				} else {
					$sql .= " and (" . (is_null($filtro['vg_forma_pagto'])?1:0);
					$sql .= "=1 or vg_pagto_tipo = " . getCodigoNumericoParaPagto($filtro['vg_forma_pagto']) . ") ";
				}
			}

			$sql .= " and (" . (is_null($filtro['vg_status'])?1:0);
			$sql .= "=1 or vg.vg_ultimo_status = " . SQLaddFields($filtro['vg_status'], "") . ") ";

			if($filtro['vg_id']) {
				$sql .= " and (" . (is_null($filtro['vg_id'])?1:0);
				$sql .= "=1 or (ip.ip_vg_id ".(($filtro['tf_v_codigo_include']=="-1")?"not":"")." in (" . str_replace("'", "", $filtro['vg_id']) . ")) ) ";
			}
			if($filtro['order_id']) {

				$sql .= " and (" . (is_null($filtro['order_id'])?1:0);
				$sql .= "=1 or (upper(ip.ip_order_id) ".(($filtro['tf_v_order_include']=="-1")?"not":"")." in ('" . str_replace(",", "','", str_replace(" ", "", $filtro['order_id'])) . "')) ) ";
			}
			if($filtro['client_email_txt']) {
				$sql .= " and (" . (is_null($filtro['client_email_txt'])?1:0);
				$sql .= "=1 or (upper(ip.ip_client_email) like '%" . strtoupper($filtro['client_email_txt']) . "%') ) ";
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
						$sql .= "and not ($sql_subquery) ";
					} elseif($filtro['confirmed']==1) {
						// 1 -> "0 - Não (com venda)"
						$sql .= "and coalesce(vg_id, 0)>0 ";
						$sql .= "and not ($sql_subquery) ";
					} elseif($filtro['confirmed']==2) {
						// 2 -> "1 - Sim (Completo)"
						$sql .= "and coalesce(vg_id, 0)>0 ";
						$sql .= "and ($sql_subquery) ";
					}
			}

			$sql .= " and (" . (is_null($filtro['client_id'])?1:0);
			$sql .= "=1 or ip.ip_client_id = " . SQLaddFields($filtro['client_id'], "") . ")  ";
		}

		if(!is_null($orderBy)) $sql .= " order by " . $orderBy;

		//echo $sql;
		//exit;

if(b_IsUsuarioWagner()) { 
//echo "<!-- ".str_replace("\n", "<br>\n", $sql)." --><br>";
//echo "".str_replace("\n", "<br>\n", $sql)."<br>";
//die("Stop");
}               
		$rs = SQLexecuteQuery($sql);
		echo '<script>';
    echo 'console.log(' . json_encode($sql) . ');';
    echo '</script>';
		//echo("felipe:" + $sql);
		if(!$rs) $ret = "Erro ao obter pedidos de integração(s).\n";

		return $ret;

	}
	
?>