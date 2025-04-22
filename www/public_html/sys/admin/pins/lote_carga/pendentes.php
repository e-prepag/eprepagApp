<?php 
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
    if(isset($_GET["downloadCsv"]) && $_GET["downloadCsv"] == 1){
        set_time_limit(3600);
        ob_start();
    }
        
    
        require_once "../../../../../includes/constantes.php";
        require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

	if(!$dd_situacao) $dd_situacao = "";
	if(!$tf_data_inic) $tf_data_inic = date('d/m/Y');
	if(!$tf_data_final) $tf_data_final = date('d/m/Y');
	if(!$ncamp) $ncamp = 'pin_codinterno';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 0;

	if(isset($_POST['BtnSearch'])){
            $inicial     = 0;
            $range       = 1;
            $total_table = 0;
        }
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "/sys/imagens/proxima.gif";
	$img_anterior = "/sys/imagens/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_opr_codigo = $_SESSION["opr_codigo_pub"];
	}

	if($BtnSearch){
		$msg = "";
		$msgAcao = "";

		//Processa Acoes
		if($msg == ""){
	
			//Acao d - delete
			if($acao && $acao == "d"){
			
				if(!$pin_codinterno || trim($pin_codinterno) == '' || !is_numeric($pin_codinterno)){ 
					$msgAcao = "Código interno do pin não especificado ou inválido.\n";
				}
				
				if($msgAcao == ""){
					
					//Excluir pin
					if($tipo == "pin"){
						$sql = "delete from pins where pin_codinterno = $pin_codinterno";
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msgAcao = "Erro ao excluir pin.\n";
						else $msgAcao .= "Pin excluído.\n";
						
					//Excluir lote
					}else if($tipo == "lote"){
						$sql  = "select * from pins where pin_codinterno = $pin_codinterno";
						$rs_pin = SQLexecuteQuery($sql);
						if(!$rs_pin || pg_num_rows($rs_pin) == 0) $msg = "Lote não encontrado.\n";
						else {
							$rs_pin_row = pg_fetch_array($rs_pin);
							$sql = "delete from pins where 
										pin_lote_codigo	= " . $rs_pin_row['pin_lote_codigo'] . "
										and opr_codigo 		= " . $rs_pin_row['opr_codigo'] . "
										and pin_valor 		= " . $rs_pin_row['pin_valor'] . "
										and pin_dataentrada = '" . $rs_pin_row['pin_dataentrada'] . "'";
							$ret = SQLexecuteQuery($sql);
							if(!$ret) $msgAcao = "Erro ao excluir lote.\n";
							else $msgAcao .= "Lote excluído.\n";
							
						}
					}
				}
			}
		}





		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//Venda
		//------------------------------------------------------------------
		//Data
		if($msg == "")
			if($tf_data_inic || $tf_data_final){
				if(verifica_data($tf_data_inic) == 0)	$msg = "A data inicial de importação é inválida.\n";
				if(verifica_data($tf_data_final) == 0)	$msg = "A data final de importação é inválida.\n";
			}
		//valor
		if($msg == "")
			if($tf_valor_total){
				if(!is_moeda($tf_valor_total)) $msg = "Valor inválido.\n";
			}


		//Busca pins
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){

			$sql = "select p.pin_valor, p.pin_status, p.pin_codinterno, p.pin_dataentrada, p.pin_horaentrada, " .
					"p.pin_lote_codigo, p.pin_codigo, p.pin_serial, p.pin_caracter, opr.opr_codigo, opr.opr_nome, ps.stat_descricao, p.pin_canal ";
			$sql .= "from pins p ";
			$sql .= "left join operadoras opr on opr.opr_codigo = p.opr_codigo ";
			$sql .= "left join pins_status ps on ps.stat_codigo = p.pin_status ";
			if($tf_data_inic && $tf_data_final) $sql .= "where (p.pin_dataentrada >= '".formata_data($tf_data_inic, 1)."' and p.pin_dataentrada <= '".formata_data($tf_data_final, 1)."') ";
			if($dd_opr_codigo) $sql .= "and p.opr_codigo = ".($dd_opr_codigo)." ";
			if(isset($dd_status) && $dd_status != '') $sql .= "and p.pin_status = '".($dd_status)."' ";
			switch($tf_valor_oper) {
				case "gt": $valor_oper=">"; break;
				case "lt": $valor_oper="<"; break;
				case "eq": $valor_oper="="; break;
				default: $valor_oper=""; break;
			}
			if($tf_valor_total && $tf_valor_oper) $sql .= " and p.pin_valor " . $valor_oper . " " . str_replace(',', '.', str_replace('.', '', trim($tf_valor_total))) . " ";
			if($tf_loteopr) $sql .= "and p.pin_lote_codigo = ".$tf_loteopr." ";
			if($tf_nro_pin) $sql .= "and (upper(p.pin_codigo) LIKE '%".strtoupper($tf_nro_pin)."%' or  upper(p.pin_caracter) LIKE '%".strtoupper($tf_nro_pin)."%')";
			if($tf_nro_serie) $sql .= "and upper(p.pin_serial) LIKE '%".strtoupper($tf_nro_serie)."%' ";
		
//echo $sql."<br>";
			$res_count = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($res_count);
		
			if($total_table == 0) $msg = LANG_PINS_NO_PINS_FOUND.".\n";		
		
			if($msg == ""){
				$bol_valor_total_i=0;
				while($u=pg_fetch_array($res_count)) $bol_valor_total_i+=$u['pin_valor'];
				
				$sql .= "order by ".$ncamp." ";
				if($ordem == 1) {
					$sql .= " asc ";
					$img_seta = "glyphicon glyphicon-menu-up top0";
				} else {
					$sql .= " desc ";
					$img_seta = "glyphicon glyphicon-menu-down top0";
				}
			
                                if(!isset($_GET["downloadCsv"])){
                                    $sql .= " limit ".$max." ";
                                    $sql .= " offset ".$inicial;
                                }
				
			
//trace_sql($sql, "Arial", 2, "#666666", 'b');			
				$resest = SQLexecuteQuery($sql);
				
				if($max + $inicial > $total_table) $reg_ate = $total_table;
				else $reg_ate = $max + $inicial;
			}
		}
	}		
	$msg = $msgAcao . $msg;
		
	$varsel  = "&BtnSearch=1&tf_data_inic=$tf_data_inic&tf_data_final=$tf_data_final&dd_opr_codigo=$dd_opr_codigo&tf_loteopr=$tf_loteopr&dd_status=$dd_status&tf_valor_total=$tf_valor_total&tf_valor_oper=$tf_valor_oper";
	$varsel .= "&tf_nro_pin=$tf_nro_pin&tf_nro_serie=$tf_nro_serie";

	//Operadoras
	$sql  = "select * from operadoras ope order by opr_nome";
	$resbco = SQLexecuteQuery($sql);

	//Pins Status
	$sql  = "select * from pins_status ps order by stat_codigo";
	$rs_pins_status = SQLexecuteQuery($sql);

?>

<html>
<head>
<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<script language='javascript' src='/js/popcalendar.js'></script>
<script language="JavaScript">
function GP_popupConfirmMsg(msg) { 
  document.MM_returnValue = confirm(msg);
}

function GP_popupAlertMsg(msg) { 
  document.MM_returnValue = alert(msg);
}
</script>

</head>
<body>
    <!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_PINS_PAGE_TITLE_2; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="../index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <form name="form1" method="post" action="">
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_IMPORT;?>:</span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_data_inic" type="text" class="form-control data pull-left w100" id="tf_data_inic" value="<?php echo $tf_data_inic ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2 ">
                            <span class="pull-right"> até </span>   
                        </div>
                        <div class="col-md-3">
                            <input name="tf_data_final" type="text" class="form-control data pull-left w100" id="tf_data_final" value="<?php echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2 pull-right">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH;?></button>
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_STATUS; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_status" id="dd_status" class="form-control">
                                <option value=""><?php echo LANG_PINS_ALL; ?></option>
    <?php 
                                while ($rs_pins_status_row = pg_fetch_array($rs_pins_status)) 
                                {
                                    echo '<option value="'.$rs_pins_status_row['stat_codigo'].'"';
                                    if ($rs_pins_status_row['stat_codigo'] == $dd_status)
                                        echo "SELECTED";

                                    switch ($rs_pins_status_row['stat_descricao'])
                                    {
                                        case "Aguardando Liberação":  echo '>'.LANG_PINS_STATUS_MSG_0.'</option>\n'; break;
                                        case "Disponivel":  echo '>'.LANG_PINS_STATUS_MSG_1.'</option>\n'; break;
                                        case "Em processo":  echo '>'.LANG_PINS_STATUS_MSG_2.'</option>\n'; break;
                                        case "Vendido":  echo '>'.LANG_PINS_STATUS_MSG_3.'</option>\n'; break;
                                        case "Vendido – Lan House":  echo '>'.LANG_PINS_STATUS_MSG_6.'</option>\n'; break;
                                        case "Vendido - POS":  echo '>'.LANG_PINS_STATUS_MSG_7.'</option>\n'; break;
                                        case "E-Prepag":  echo '>'.LANG_PINS_STATUS_MSG_8.'</option>\n'; break;
                                        case "Desativado":  echo '>'.LANG_PINS_STATUS_MSG_9.'</option>\n'; break;
                                    }
                                }	
    ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_OPERATOR; ?></span>
                        </div>
                        <div class="col-md-3">
<?php
                        if($_SESSION["tipo_acesso_pub"]=='PU') 
                        {
                            echo $_SESSION["opr_nome"];
?>
                            <input type="hidden" name="dd_opr_codigo" id="dd_opr_codigo" value="<?php echo $dd_opr_codigo?>">
<?php
                        }
                        else 
                        {
?>
                            <select name="dd_opr_codigo" id="dd_opr_codigo" class="form-control">
                                <option value=""><?php echo LANG_PINS_ALL; ?></option>
                                <?php while($pgbco = pg_fetch_array($resbco)) { ?>
                                <option value="<?php echo $pgbco['opr_codigo'] ?>" <?php if($pgbco['opr_codigo'] == $dd_opr_codigo) echo "selected" ?>><?php echo $pgbco['opr_nome'] ?></option>
                                <?php } ?>
                            </select>
<?php
                        } 
?>
                        </div>
                    </div>
                    <div class="row txt-cinza top10">                     
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_VALUE; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="tf_valor_oper" class="pull-left form-control w66">
                                <option value="gt" <?php if($tf_valor_oper == "gt") echo "selected" ?>>></option>
                                <option value="eq" <?php if((!$tf_valor_oper) || $tf_valor_oper == "eq") echo "selected" ?>>=</option>
                                <option value="lt" <?php if($tf_valor_oper == "lt") echo "selected" ?>><</option>
                            </select>
                            <input name="tf_valor_total" type="text" class="form-control mleft5 w66 pull-left" id="tf_valor" value="<?php echo $tf_valor_total ?>" size="9" maxlength="10">
                            <span class="pull-right"><?php echo LANG_PINS_FORMAT; ?> xx,xx </span>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_LOT; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_loteopr" type="text" class="form-control" id="tf_loteopr" value="<?php echo $tf_loteopr ?>" size="10" maxlength="10">
                        </div>
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_SERIAL_NUMBER; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_nro_serie" type="text" class="form-control" id="tf_nro_serie" value="<?php echo $tf_nro_serie ?>" size="20" maxlength="20">
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_PIN_NUMBER; ?></span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_nro_pin" type="text" class="form-control" id="tf_nro_pin" value="<?php echo $tf_nro_pin ?>" size="16" maxlength="16">
                        </div>
                    </div>               
                </div>
                </form>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
<?php 
                    if($msg != "")
                    {
?>
                        <div class="row text-center txt-vermelho">
                            <?php echo str_replace("\n", "<br>", $msg) ?>
                        </div>
<?php 
                    }

                    if($total_table > 0) 
                    {
                        require_once $raiz_do_projeto."class/util/CSV.class.php";

                        $cabecalho = LANG_PINS_ID.";".LANG_PINS_IMPORT.";".LANG_PINS_OPERATOR.";".LANG_PINS_VALUE.";".LANG_PINS_LOT.";".LANG_PINS_SERIAL_PIN.";".LANG_PINS_CODIGO_PIN.";".LANG_PINS_CARACTER_PIN.";".LANG_PINS_CHANNEL.";".LANG_PINS_STATUS;

                        $objCsv = new CSV($cabecalho, md5(uniqid()), $raiz_do_projeto."public_html/cache/");
                        $objCsv->setCabecalho();
?> 
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                            <thead>
                              <tr class="bg-cinza-claro">
                                <th class="text-center">
                                    <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=pin_codinterno" . $varsel ?>"><?php echo LANG_PINS_ID; ?></a></strong>
                                    <?php if($ncamp == 'pin_codinterno') echo "<span class='".$img_seta."'></span>"; ?>
                                </th>
                                <th class="text-center">
                                    <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=pin_dataentrada" . $varsel ?>"><?php echo LANG_PINS_IMPORT; ?></a></strong>
                                    <?php if($ncamp == 'pin_dataentrada') echo "<span class='".$img_seta."'></span>"; ?>
                                </th>
                                <th class="text-center">
                                    <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=opr_nome" . $varsel ?>"><?php echo LANG_PINS_OPERATOR; ?></a></strong> 
                                    <?php if($ncamp == 'opr_nome') echo "<span class='".$img_seta."'></span>"; ?>
                                </th>
                                <th class="text-center">
                                    <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=pin_valor" . $varsel ?>"><?php echo LANG_PINS_VALUE; ?></a></strong>
                                    <?php if($ncamp == 'pin_valor') echo "<span class='".$img_seta."'></span>"; ?>
                                </th>    
                                <th class="text-center">
                                    <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=pin_lote_codigo" . $varsel ?>"><?php echo LANG_PINS_LOT; ?></a></strong>
                                </th>
                                <th class="text-center">
                                    <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=pin_serial" . $varsel ?>"><?php echo LANG_PINS_SERIAL_PIN; ?></a></strong> 
                                    <?php if($ncamp == 'pin_serial') echo "<span class='".$img_seta."'></span>"; ?>
                                </th>
                                <th class="text-center">
                                    <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=pin_codigo" . $varsel ?>"><?php echo LANG_PINS_CODIGO_PIN; ?></a></strong> 
                                    <?php if($ncamp == 'pin_codigo') echo "<span class='".$img_seta."'></span>"; ?>
                                </th>
                                <th class="text-center">
                                    <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=pin_caracter" . $varsel ?>"><?php echo LANG_PINS_CARACTER_PIN; ?></a></strong> 
                                    <?php if($ncamp == 'pin_caracter') echo "<span class='".$img_seta."'></span>"; ?>
                                </th>
                                <th class="text-center">
                                    <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=pin_canal" . $varsel ?>"><?php echo LANG_PINS_CHANNEL; ?></a></strong> 
                                    <?php if($ncamp == 'pin_canal') echo "<span class='".$img_seta."'></span>"; ?>
                                </th>
                                <th class="text-center">
                                    <strong><a href="<?php echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=pin_status" . $varsel ?>"><?php echo LANG_PINS_STATUS; ?></a></strong>
                                    <?php if($ncamp == 'stat_descricao') echo "<span class='".$img_seta."'></span>"; ?>
                                </th>
                              </tr>
                              <tr class="texto">
                                <th>
                                    <?php if($total_table > 0) { ?>
                                            <?php echo LANG_SHOW_DATA.' '; ?> <strong><?php echo $inicial + 1 ?></strong><?php echo ' '.LANG_TO.' '; ?><strong><?php echo $reg_ate ?></strong><?php echo ' '.LANG_FROM.' '; ?><strong><?php echo $total_table ?></strong>
                                    <?php } ?>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
<?php
                        if($resest)
                        {
                            while ($pgest = pg_fetch_array($resest))
                            {
                                $bol_valor_total += $pgest['pin_valor'];

                                switch ($pgest['stat_descricao'])
                                {
                                    case "Aguardando Liberação":  
                                            $stat_descricao = LANG_PINS_STATUS_MSG_0; 
                                            break;
                                    case "Disponivel":  
                                            $stat_descricao = LANG_PINS_STATUS_MSG_1; 
                                            break;
                                    case "Em processo":  
                                            $stat_descricao = LANG_PINS_STATUS_MSG_2; 
                                            break;
                                    case "Vendido":  
                                            $stat_descricao = LANG_PINS_STATUS_MSG_3; 
                                            break;
                                    case "Vendido – Lan House":  
                                            $stat_descricao = LANG_PINS_STATUS_MSG_6; 
                                            break;
                                    case "Vendido - POS":  
                                            $stat_descricao = LANG_PINS_STATUS_MSG_7; 
                                            break;
                                    case "E-Prepag":  
                                            $stat_descricao = LANG_PINS_STATUS_MSG_8; 
                                            break;
                                    case "Desativado":  
                                            $stat_descricao = LANG_PINS_STATUS_MSG_9; 
                                            break;
                                }

                                $lineCsv = array();
                                $lineCsv[] = $pgest['pin_codinterno'];
                                $lineCsv[] = formata_data($pgest['pin_dataentrada'], 0)." ".$pgest['pin_horaentrada'];
                                $lineCsv[] = $pgest['opr_nome'];
                                $lineCsv[] = number_format($pgest['pin_valor'], 2, ",", ".");
                                $lineCsv[] = $pgest['pin_lote_codigo'];
                                $lineCsv[] = "";
                                $lineCsv[] = "";
                                $lineCsv[] = "";
                                $lineCsv[] = (($pgest['pin_canal']=="s")?"Site":(($pgest['pin_canal']=="p")?"POS":($pgest['pin_canal']=="a")?"AtimoPay":"???"));
                                $lineCsv[] = $stat_descricao;

                                if(is_array($lineCsv)) 
                                    $objCsv->setLine(implode(";",$lineCsv));
?>
                            <tr class="trListagem"> 
                                <td class="text-center"><?php echo $pgest['pin_codinterno'] ?></td>
                                <td class="text-center"><?php echo formata_data($pgest['pin_dataentrada'], 0) ?> <?php echo $pgest['pin_horaentrada'] ?></td>
                                <td class="text-center"><?php echo $pgest['opr_nome'] ?></td>
                                <td class="text-center"><?php echo number_format($pgest['pin_valor'], 2, ",", ".") ?></td>
                                <td class="text-center"><?php echo $pgest['pin_lote_codigo'] ?></td>
                                <td class="text-center"><?php echo "-"; ?></td>
                                <td class="text-center"><?php echo "-"; ?></td>
                                <td class="text-center"><?php echo "-"; ?></td>
                                <td class="text-center"><?php echo (($pgest['pin_canal']=="s")?"Site":(($pgest['pin_canal']=="p")?"POS":($pgest['pin_canal']=="a")?"AtimoPay":"???")) ?></td>
                                <td class="text-center"><?php echo $stat_descricao; ?></td>
                            </tr>
<?php	
                            } 
                        
                            if($reg_ate >= $total_table && !isset($_REQUEST["inicial"]))
                                $csv = $objCsv->export();
?>
                                </tbody>
                                <tr class="texto" bgcolor="#F4F4F4"> 
                                    <td colspan="3"><strong><?php echo LANG_PINS_SUBTOTAL; ?></strong></td>
                                    <td><div align="center"><strong><?php echo number_format($bol_valor_total, 2, ',', '.') ?></strong></div></td>
                                    <td colspan="6">&nbsp;</td>
                                </tr>
                                <tr class="texto" bgcolor="#E4E4E4"> 
                                    <td colspan="3"><strong><?php echo LANG_PINS_TOTAL; ?></strong></td>
                                    <td><div align="center"><strong><?php echo number_format($bol_valor_total_i, 2, ',', '.') ?></strong></div></td>
                                    <td colspan="6">&nbsp;</td>
                                </tr>
<?php 
                            if(isset($_GET["downloadCsv"]))
                            {
                                require_once $raiz_do_projeto."public_html/includes/downloadCsv.php";
                            }elseif(isset($csv))
                            {
                                $csv = "/includes/downloadCsv.php?csv=$csv&dir=cache";
                            }elseif($total_table > 0)
                            {
                                $csv = "/sys/admin/pins/lote_carga/pendentes.php?downloadCsv=1&".$varsel;
                            }

                            if(isset($csv))
                            {
?>
                                <tr>
                                    <td class="text-center" colspan="10"><a href="<?php echo $csv;?>" target="_blank"><span class="btn btn-info">Download CSV</span></a></td>
                                </tr>
<?php 
                            } 

                            paginacao_query($inicial, $total_table, $max, '11', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
<?php  
                        }
                            
                    }
?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
        optDate.interval = 1;

    setDateInterval('tf_data_inic','tf_data_final',optDate);
});
</script>

<!-- FIM CODIGO NOVO -->
</body>
</html>