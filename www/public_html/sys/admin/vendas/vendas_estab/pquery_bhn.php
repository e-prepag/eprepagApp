<?php  
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";
require_once $raiz_do_projeto . "class/gamer/classIntegracao.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";

set_time_limit (3000) ;
$time_start = getmicrotime();

block_user_publisher();

//if(!$tf_data_final)    $tf_data_final   = (date('d')+1).date('/m/Y');
if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');


$data_inicial_limite = data_menos_n(date('d/m/Y'), 120);
$FrmEnviar = 1;

	
if(!verifica_data($tf_data_inicial)){
        $data_inic_invalida = true;
        $FrmEnviar = 0;
}

if(!verifica_data($tf_data_final)){
        $data_fim_invalida = true;
        $FrmEnviar = 0;
}
/*	
if(qtde_dias($data_inicial_limite, $tf_data_inicial) < 0){
        $data_inicial_menor = true;
        $FrmEnviar = 0;
}
*/
if($BtnSearch && $BtnSearch!=1 && $FrmEnviar==1 ) {

        // Formatando a data e Hora de acordo com o TimeZone
        $data_inic = formata_data(trim($tf_data_inicial), 1);
        $data_fim = formata_data(trim($tf_data_final), 1); 
        if (!empty($dd_california) ) {
                date_default_timezone_set('America/Los_Angeles');
                //date_default_timezone_set('UTC');
                $hora_california = date("G");
                date_default_timezone_set('America/Fortaleza');
                $hora_sp = date("G");
                
                $dataIncial  = mktime((($hora_sp - $hora_california)*1), 0, 0, (substr($data_inic, 5, 2)*1), (substr($data_inic, 8, 2)*1), (substr($data_inic, 0, 4)*1));
                //$dataFinal  = mktime((($hora_sp - $hora_california)*1), 0, 0, (substr($data_fim, 5, 2)*1), (substr($data_fim, 8, 2)*1), (substr($data_fim, 0, 4)*1));
                $dataFinal  = mktime((23+($hora_sp - $hora_california)*1), 59, 59, (substr($data_fim, 5, 2)*1), (substr($data_fim, 8, 2)*1), (substr($data_fim, 0, 4)*1));
                
                $data_inic = date('Y-m-d H:i:s',$dataIncial);
                $data_fim = date('Y-m-d H:i:s',$dataFinal);
        }
        else {
                $data_inic .=  " 00:00:00";
                //$data_fim .=  " 00:00:00";
                $data_fim .=  " 23:59:59";
        }

        $estat = "
            SELECT b.*,pro.ogp_nome 
            FROM pedidos_bhn b
			inner join tb_dist_operadora_games_produto_modelo mo on mo.ogpm_pin_resquest_id = b.bhn_product_id
            inner join tb_dist_operadora_games_produto pro on mo.ogpm_ogp_id = pro.ogp_id
            WHERE bhn_data >= '".$data_inic."'
                AND bhn_data < '".$data_fim."'
                and (select vgm_vg_id from tb_dist_venda_games_modelo where vgm_valor < 0 and vgm_vg_id = vg_id limit 1) is null ";
        if(empty($dd_todos)) {
                $estat .=  "AND bhn_status = '00' 
                AND bhn_pin != 'Reversal'
                ";
        }
        elseif($dd_todos == "REVERSAL") {
                $estat .=  "AND bhn_status = '00'
                AND bhn_pin = 'Reversal'
                ";
        }
        elseif($dd_todos == "ERROS"){
                $estat .=  "AND bhn_status != '00'
                ";
        }
        if($dd_valor) {
                $estat .=  "AND bhn_valor = ".$dd_valor."";
        }
        if($dd_product) {
                $estat .=  "AND bhn_product_id = '".$dd_product."'";
        }
        $estat .= "
            ORDER BY bhn_data DESC, bhn_id DESC
            ";


        //echo $estat;
        $rs = pg_query($estat);
        $total_table = pg_num_rows($rs);

}//end if($BtnSearch && $BtnSearch!=1 && $FrmEnviar==1 )

$sql_valor  = "select opr_valor1, opr_valor2, opr_valor3, opr_valor4, opr_valor5, opr_valor6, opr_valor7, opr_valor8, opr_valor9, opr_valor10, opr_valor11, opr_valor12, opr_valor13, opr_valor14, opr_valor15 from operadoras where opr_codigo IN (101,113,114,126,127,128,129,130,131,132,133,134,135)";

$resval = pg_exec($connid, $sql_valor);

$sql_produtos = "select ogpm_pin_resquest_id,ogpm_nome,ogpm_pin_valor from tb_dist_operadora_games_produto_modelo where ogpm_pin_resquest_id is not null and ogpm_ativo=1;";

$resprods = pg_exec($connid, $sql_produtos);

?>
<html>
<head>
<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<title>E-Prepag</title>
<script language='javascript' src='/js/<?php echo LANG_NAME_CALENDAR_FILE; ?>'></script>
<script language='javascript' src='../../stats/js/jquery-1.4.4.js'></script>
</head>
<!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <form name="form1" method="post" action="">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong>Relatório de Vendas BHN</strong>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="../../commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <div class="row txt-cinza ">
                    <div class="col-md-2">
                        <span class="pull-right"><?php echo LANG_PINS_START_DATE; ?></span>
                    </div>
                    <div class="col-md-3">
                        <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                    </div>
                    <div class="col-md-2">
                        <span class="pull-right"><?php echo LANG_PINS_END_DATE; ?></span>
                    </div>
                    <div class="col-md-3">
                        <input alt="Calendário" name="tf_data_final" type="text" class="form-control w-ipt-medium pull-left data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                    </div>

                </div>
                <div class="row txt-cinza top10">
                    <div class="col-md-2">
                        <span class="pull-right"><?php echo LANG_PINS_VALUE; ?></span>
                    </div>
                    <div class="col-md-3">
                        <select name="dd_valor" id="dd_valor" class="form-control">
                            <option value=""><?php echo LANG_PINS_ALL_VALUES; ?></option>
<?php 
                    if($resval) {
                        $arrayValores = array();
                        while($resval_row = pg_fetch_array($resval)) { 
                            for($i=1;$i<=15;$i++) {
                                if($resval_row["opr_valor$i"]>0) {
                                    $arrayValores[$resval_row["opr_valor$i"]] = $resval_row["opr_valor$i"];
                                }
                                if($i>15) break;
                            }
                        }//end while
                        if (count($arrayValores) > 1) {
                            sort($arrayValores);
                            foreach ($arrayValores as $key => $value) {
?>
                                <option value="<?php echo $value; ?>" <?php if ($dd_valor == $value) echo "selected";?>><?php echo $value; ?></option>
<?php 
                            }//end foreach
                        }//end if (count($arrayValores) > 1) 
                    } 
?>
                      </select>
                    </div>
                    <div class="col-md-2">
                        <span class="pull-right">Registro BHN</span>
                    </div>
                    <div class="col-md-3">
                        <select name="dd_todos" id="dd_todos" class="form-control">
                            <option value=""<?php echo ((!empty($dd_todos))?" selected":"")?>>Total de PIN</option>
                            <option value="ALL"<?php echo ($dd_todos == "ALL"?" selected":"")?>>Todas as transações (Erros, Reversal, etc)</option>
                            <option value="REVERSAL"<?php echo ($dd_todos == "REVERSAL"?" selected":"")?>>Somente as transações Reversal</option>
                            <option value="ERROS"<?php echo ($dd_todos == "ERROS"?" selected":"")?>>Somente as transações Continuam com Erros</option>
                      </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH;?></button>
                    </div>
                </div>
                <div class="row txt-cinza top10">
                    <div class="col-md-2">
                        <span class="pull-right">Product</span>
                    </div>
                    <div class="col-md-3">
                        <select name="dd_product" id="dd_product" class="form-control">
                            <option value=""<?php echo ((!empty($dd_product))?" selected":"")?>>Todos</option>
<?php 
                    if($resprods) {
                        $arrayProdutos = array();
                        while($resprods_row = pg_fetch_array($resprods)) { 
                            $arrayProdutos[$resprods_row["ogpm_pin_resquest_id"]] = $resprods_row["ogpm_nome"]; //." - ".$resprods_row["ogpm_pin_valor"];
                        }//end while
                        if (count($arrayProdutos) > 1) {
                            asort($arrayProdutos);
                            foreach ($arrayProdutos as $key => $value) {
?>
                                <option value="<?php echo $key; ?>" <?php if ($dd_product == $key) echo "selected";?>><?php echo $value; ?></option>
<?php 
                            }//end foreach
                        }//end if (count($arrayProdutos) > 1) 
                    } 
?>                            
                      </select>
                    </div>
                    <div class="col-md-7 top20 txt-cinza">
                        <input type="checkbox" name="dd_california" id="dd_california" <?php echo ((!empty($dd_california))?" checked":"")?> value="1"> Considerar horário da Califórnia na geração do relatório.</td>
                    </div>
                </div>
<?php
                if($data_inic_invalida == true) echo '<div class="row txt-cinza txt-vermelho">'.LANG_PINS_START_DATE."</div>";
                if($data_fim_invalida == true) echo '<div class="row txt-cinza txt-vermelho">'.LANG_PINS_END_DATE."</div>";
                if($data_inicial_menor == true) echo '<div class="row txt-cinza txt-vermelho">'.LANG_PINS_COMP_DATE_START_WITH_END."</div>";
                
                $colspan = 6;
                
                $cabecalho = "'Nossa ".LANG_PINS_DATE."','BHN ".LANG_PINS_DATE."','Valor','PIN','Número de série do cartão','Nosso NSU','BHN NSU','Nosso Audit','BHN Audit',";
?>
                </form>
            </div>
        </div>
    </div>
</div>
<?php 
                if($BtnSearch && $BtnSearch!=1 && $FrmEnviar==1) {
?>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                            <thead>
                              <tr class="bg-cinza-claro">
							    <th class="text-center">Nome</th>
                                <th class="text-center">Product_ID</th>
                                <th class="text-center">Nossa <?php echo LANG_PINS_DATE; ?></th>
                                <th class="text-center">BHN <?php echo LANG_PINS_DATE; ?></th>
                                <th class="text-center">Valor</th>
                                <th class="text-center">PIN</th>
                                <th class="text-center">Número de série do cartão</th>
                                <th class="text-center">Nosso NSU</th>
                                <th class="text-center">BHN NSU</th>
                                <th class="text-center">Nosso Audit</th>
                                <th class="text-center">BHN Audit</th>
<?php 
                            if(b_is_Financeiro()) 
                            {
                                $cabecalho .= "'vg_id'";
                                $colspan = 11;
?>
                                <th class="text-center"><strong>vg_id</strong></th>
<?php
                            }
?>
                              </tr>
                            </thead>
                            <tr>
                                <th colspan="<?php echo $colspan;?>">
<?php
                                if($total_table > 0) 
                                {

                                    echo LANG_SHOW_DATA; 
?>
                                    <span id="txt_totais" class="txt-azul-claro"></span>
<?php  
                                } 
?>
                                </th>
                            </tr>
                            <tbody>
<?php
                            $total_considerado = 0;
                            $vetorAudit = array();
                            while ($pgrow = pg_fetch_array($rs))
                            {
						
                                //Decodificando o JSON
                                $pgrow['bhn_xml_retorno'] = str_replace('"Aplicar"', "'Aplicar'",str_replace("\n", "", $pgrow['bhn_xml_retorno']));
								$pgrow['bhn_xml_retorno'] = str_replace('"Aplicar"', "'Aplicar'",str_replace("\r", "", $pgrow['bhn_xml_retorno']));
								$pgrow['bhn_xml_retorno'] = str_replace('"Aplicar"', "'Aplicar'",str_replace("\t", "", $pgrow['bhn_xml_retorno']));
                                $instrucoes = json_decode($pgrow['bhn_xml_retorno']);
                                					
                                $data_registro_bhn = "20".mask($instrucoes->transaction->localTransactionDate,"##-##-##")." ".mask($instrucoes->transaction->localTransactionTime,"##:##:##");
                                //echo $data_inic." <= ".$data_registro_bhn." && ".$data_fim." > ".$data_registro_bhn."<br>";
                                //if ($data_inic <= $data_registro_bhn && $data_fim > $data_registro_bhn) {
                                        $valor = true;
                                        $total_considerado++;
                                        $valor_total_tela += $pgrow['bhn_valor'];
                                        $vetorAudit[] = $pgrow['bhn_audit'];
                          
?>                                
                                        <tr class="trListagem">
										    <td class="text-right"><?php  echo $pgrow['ogp_nome']; ?></td>
                                            <td class="text-right"><?php  echo $pgrow['bhn_product_id']; ?></td>
                                            <td class="text-center"><?php echo substr($pgrow['bhn_data'], 0, 19); ?></td>
                                            <td class="text-center"><?php echo $data_registro_bhn; ?></td>
                                            <td class="text-right"><?php  echo number_format($pgrow['bhn_valor'], 2, ',', '.'); ?></td>
                                            <td class="text-right"><?php  echo $pgrow['bhn_pin']; ?></td>
                                            <td class="text-right"><?php  echo $instrucoes->transaction->additionalTxnFields->activationAccountNumber; ?></td>
                                            <td class="text-right"><?php  echo $pgrow['bhn_id']; ?></td>
                                            <td class="text-right"><?php  echo $instrucoes->transaction->retrievalReferenceNumber; //echo "<pre>". print_r($instrucoes,true)."</pre>";?></td>
                                            <td class="text-right"><?php  echo $pgrow['bhn_audit']; ?></td>
                                            <td class="text-right"><?php  echo $instrucoes->transaction->systemTraceAuditNumber;?></td>
<?php
                                        if(b_is_Financeiro()) {
?>
                                            <td class="text-right"><a href="https://<?php echo $_SERVER["SERVER_NAME"] ?>:8080/pdv/vendas/com_venda_detalhe.php?venda_id=<?php  echo "".$pgrow['vg_id']; ?>" target="_blank"><?php  echo $pgrow['vg_id']; ?></a></td>
<?php 
                                        }

?>
                                        </tr>
<?php
                                //}//end if ($data_inic <= $data_registro_bhn && $data_fim > $data_registro_bhn) 
                            }//end while
                            if (!$valor) {
?>
                                <tr> 
                                    <td colspan="<?php echo $colspan;?>">
                                        <strong><?php echo LANG_NO_DATA; ?>.</strong>
                                    </td>
                                </tr>
<?php
                            } 
                            else {
                                //Exibindo salto de Audit Number
                                $total_audit = count($vetorAudit);
                                if($total_audit > 0) {
                                    sort($vetorAudit);
                                    $texto_aux = "<tr><td colspan='".$colspan."' class='txt-vermelho fontsize-13'><strong>Houve números ausentes de BHN Audit Number ( ";
                                    $bhn_audit_anterior = NULL;
                                    $contador_faltantes = 0;
									
                                    foreach ($vetorAudit as $key => $value) {
                                        if(!empty($bhn_audit_anterior) && ($bhn_audit_anterior+1) !=$value) {
                                            if($contador_faltantes != 0) $texto_aux .= ", ";
                                            $texto_aux .= ($bhn_audit_anterior+1);
                                            $contador_faltantes++;
                                        }//end if(!empty($bhn_audit_anterior) && ($bhn_audit_anterior+1) !=$value)
                                        $bhn_audit_anterior = $value;
                                    }//end foreach
                                    if ($contador_faltantes > 0) {
                                        echo $texto_aux." )<br>TOTAL de Faltantes: ".$contador_faltantes."<strong></td></tr>";
                                    }//end if ($contador_faltantes > 0)
                                }//end if($total_audit > 0)

                                $time_end = getmicrotime();
                                $time = $time_end - $time_start;
?>
                                <script language="JavaScript">
                                  document.getElementById('txt_totais').innerHTML = ' registros <?php echo $total_considerado; ?> ( R$ <?php echo number_format($valor_total_tela, 2, ',', '.'); ?>)';
                                </script>
                                <tr class="bg-cinza-claro">
                                    <td colspan="<?php echo $colspan;?> " class="fontsize-pp">
                                        <strong><?php echo LANG_PINS_LAST_MSG; ?>.</strong>
                                    </td>
                                </tr>
                                <tr class="bg-cinza-claro">
                                    <td colspan="<?php echo $colspan;?>" class="fontsize-pp"><?php echo LANG_PINS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                                </tr>
                                <tr class="bg-cinza-claro"> 
                                    <td colspan="<?php echo $colspan;?>" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); ?></td>
                                </tr>
<?php  
                            }  
?>                                
                            </tbody>
                        </table>
                        <a href="#" class="btn downloadCsv btn-info ">Download CSV</a>
                        <center>
                        </center>
                    </div>
                </div>
<?php 
                } //end if($BtnSearch && $BtnSearch!=1 && $FrmEnviar==1 )
?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/facebook.js"></script>
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="/js/table2CSV.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:[4,6,8]});
    
    var optDate = new Object();
        optDate.interval = 1;

    setDateInterval('tf_data_inicial','tf_data_final',optDate);
});
</script>

<!-- FIM CODIGO NOVO -->
<?php  
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>