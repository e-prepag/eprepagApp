<?php
ob_start();

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(3600);
ini_set('max_execution_time', 3600); 
ini_set('memory_limit','256M');

require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";

//Declarando valor IOF 6.38 ou 0.38
$iof = array(6.38,0.38, IOF);

$time_start = getmicrotime();

$b_debug = false;

if(!$dd_opr_codigo) $dd_opr_codigo = '';
if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');

if(b_is_Publisher()) {
        $dd_opr_codigo = $_SESSION["opr_codigo_pub"];
}

// Levanta lista de operadoras
$sql  = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
$resopr = pg_exec($connid,$sql);

if($BtnSearch) {
    
        // Levantando as comissões por canal
        $sql = "
                select co_opr_codigo, co_canal, co_data_inclusao, co_volume_tipo, co_volume_min, co_comissao, opr_internacional_alicota
                from operadoras o 
                    left outer join tb_comissoes c on o.opr_codigo=c.co_opr_codigo 
                where to_char(co_data_inclusao,'YYYYDDMMHH24MISS') = (select to_char(max(co_data_inclusao),'YYYYDDMMHH24MISS') from tb_comissoes where co_opr_codigo=opr_codigo) ";
        if($dd_opr_codigo) {
            $sql .= "
                and opr_codigo = ".$dd_opr_codigo."
                     ";
        }
        $sql .= " 
                group by co_opr_codigo, co_canal, co_data_inclusao, co_volume_tipo, co_volume_min, co_comissao, opr_internacional_alicota 
                order by co_opr_codigo, co_canal, co_data_inclusao desc, co_volume_tipo, co_volume_min  
               ";
			   
        $rescommiss = pg_exec($connid,$sql);
        $vetorComissao = "";
        $vetorComissaoIOF = "";
        $vetorAlicotaIOF = "";
        while ($rescommiss_row = pg_fetch_array($rescommiss)) {
            $rescommiss_row['co_canal'] = trim($rescommiss_row['co_canal']);
            $vetorAlicotaIOF[$rescommiss_row['co_opr_codigo']] = $rescommiss_row['opr_internacional_alicota'];
            $vetorComissaoIOF[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_canal']] = false;
            if(empty($rescommiss_row['co_canal'])) {
                $rescommiss_row['co_volume_tipo'] = trim($rescommiss_row['co_volume_tipo']);
                if(empty($rescommiss_row['co_volume_tipo'])) {
                    $vetorComissao[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_volume_min']] = $rescommiss_row['co_comissao'];
                } //end if(empty($rescommiss_row['co_volume_tipo']))
                else {
                    $vetorComissao[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_volume_tipo']][$rescommiss_row['co_volume_min']] = $rescommiss_row['co_comissao'];
                }//end else do if(empty($rescommiss_row['co_volume_tipo']))
            }//end if(empty($rescommiss_row['co_canal']))
            else {
                if(in_array($rescommiss_row['opr_internacional_alicota'], $iof) && !empty($rescommiss_row['co_comissao'])) {
                    $vetorComissao[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_canal']] = $rescommiss_row['co_comissao'];
                    $vetorComissaoIOF[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_canal']] = true;
                }//end if(in_array($rescommiss_row['opr_internacional_alicota'], $iof) && !empty($rescommiss_row['co_comissao']))
                else {
                    $vetorComissao[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_canal']] = $rescommiss_row['co_comissao'];
                }//end else
            }//end else do if(empty($rescommiss_row['co_canal']))
        }//end while
        //echo "<pre>".print_r($vetorComissao,true)."</pre>";
        
        //Buscando PINs na banco de dados
        $sql = "
                select 
                    pin_codinterno, 
                    case_codigo, 
                    opr_nome, 
                    pin_valor, 
                    opr_codigo,
                    pin_datavenda,
                    pin_horavenda,
                    riot_order_id,
                    vg_canal
                FROM (
                ";
        if(strtoupper($fcanal) == 'L' || empty($fcanal)) {
            $sql .= "
                   (select 
                        t0.pin_codinterno, 
                        pin_codigo as case_codigo, 
                        t1.opr_nome, 
                        t0.pin_valor, 
                        t0.opr_codigo,
                        t0.pin_datavenda,
                        t0.pin_horavenda,
                        t4.riot_order_id,
                        case 
                            when pin_status = '3' then 'G'
                            when pin_status = '6' then 'L'
                            -- TODO => Alterar a linha abaixo para verificar se a venda foi para gamer ou LAN
                            when pin_status = '8' then 'L'
                        end as vg_canal
                    from pins t0
                        INNER JOIN operadoras t1 ON (t0.opr_codigo=t1.opr_codigo)
                        INNER JOIN pins_status t3 ON (t0.pin_status=t3.stat_codigo) 
                        LEFT OUTER JOIN pins_riot_id t4 ON (t0.pin_codinterno=t4.pin_codinterno)
                    where 
                        (t4.pin_channel='L' OR t4.pin_channel IS NULL) ";
            if($tf_data_inicial && $tf_data_final) {
                        $data_inic = formata_data(trim($tf_data_inicial), 1);
                        $data_fim = formata_data(trim($tf_data_final), 1); 
                        $sql .= " 
                        and pin_datavenda >= '".trim($data_inic)." 00:00:00' and  pin_datavenda <= '".trim($data_fim)." 23:59:59'  "; 
            }
            if($dd_opr_codigo) {
                $sql .= " 
                        and (t0.opr_codigo=".$dd_opr_codigo.")  ";
            }
            $sql .= " 
                     ) ".PHP_EOL;
            
        } //end if($fcanal == 'L') 

	if(strtoupper($fcanal) == 'C' || empty($fcanal)) {
            //Ativando a descriptografia do PIN
            require_once $raiz_do_projeto."includes/gamer/chave.php";
            require_once $raiz_do_projeto."includes/gamer/AES.class.php";
            //Instanciando Objetos para Descriptografia
            $chave256bits = new Chave();
            $pc = new AES($chave256bits->retornaChave());

            if(empty($fcanal)) {
                $sql .= "
                    UNION ALL
                        ";
            }//end if(empty($fcanal))
            $sql .= "
                   ( SELECT 
                        pih_pin_id as pin_codinterno, 
                        pin_codigo as case_codigo, 
                        o.opr_nome, 
                        pin_valor, 
                        pih_id as opr_codigo,
                        to_char(pih_data,'YYYY-MM-DD')::timestamp as pin_datavenda,
                        to_char(pih_data,'HH24:MI:SS') as pin_horavenda,
                        r.riot_order_id,
                        'C' as vg_canal
                    FROM pins_integracao_card_historico pich
                        inner join pins_card pc ON pin_codinterno=pih_pin_id
                        inner join operadoras o ON pc.opr_codigo=o.opr_codigo
            		LEFT OUTER JOIN pins_riot_id r ON (pc.pin_codinterno=r.pin_codinterno)
                    WHERE pih_codretepp='2'
                        and pich.pin_status =4
                        and pin_lote_codigo > 6 -- Codigo de lotes menor e igual a 6 foram utilizados para testes 
                        and (r.pin_channel='C' OR r.pin_channel IS NULL)
                        "; 
            if($dd_opr_codigo) {
                    $sql .= " 
                        and pih_id = ".$dd_opr_codigo;
            } //end if($dd_opr_codigo) 
            if($tf_data_inicial && $tf_data_final) {
                        $data_inic = formata_data(trim($tf_data_inicial), 1);
                        $data_fim = formata_data(trim($tf_data_final), 1); 
                        $sql .= " 
                        and pih_data >= '".trim($data_inic)." 00:00:00' and  pih_data <= '".trim($data_fim)." 23:59:59'  "; 
            }
            $sql .= " 
                    )
                    ";
        } //end if(strtoupper($fcanal) == 'C' || empty($fcanal)) 
        
        $sql .= " 
                ) as selection
                order by pin_datavenda desc, pin_horavenda desc";
        if(!b_is_Publisher()) {
           // echo "<pre>".print_r($sql,true)."</pre>";
        }
	$resid = pg_exec($connid, $sql);
	$total_table = pg_num_rows($resid);

} //end if($BtnSearch)
?>
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/table2CSV.js"></script>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function validade()
{
	if (document.form1.tf_data_inicial.value.trim() == "") { 
            window.alert("Por favor selecione a Datal Inicial.");
            document.form1.tf_data_inicial.focus();
            return false;
	}
	else if (document.form1.tf_data_final.value.trim() == "") { 
            window.alert("Por favor selecione a Datal Final.");
            document.form1.tf_data_final.focus();
            return false;
	}
        else if(DiferencaDatas(document.form1.tf_data_inicial.value,document.form1.tf_data_final.value) > 30) {
            window.alert("Por favor selecione uma diferença entre Datas de no máximo 30/31 dias.");
            document.form1.tf_data_inicial.focus();
            return false;
        }
        else return true;
}

function DiferencaDatas(data1, data2) {
    //Diferença entre datas com resultado em dias
    
    //Splitando
    var vetorData1 = data1.split("/");
    var vetorData2 = data2.split("/");
    // new Date(year, month, day, hours, minutes, seconds, milliseconds)
    var a = new Date(vetorData1[2],vetorData1[1],vetorData1[0], 0, 0, 0, 0); // data1
    var b = new Date(vetorData2[2],vetorData2[1],vetorData2[0], 0, 0, 0, 0); // data1
    var d = (b-a); // Diferença em millisegundos

    var days = Math.round((b-a)/1000/60/60/24);
    
    return days;
    
} //end function DiferencaDatas
//-->
</script>
<!-- INICIO CODIGO NOVO -->
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_PINS_PAGE_TITLE_REPORT; ?></strong>
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
                <div class="row txt-cinza espacamento">
                    <div class="col-md-2">
                        <?php echo LANG_PINS_START_DATE; ?>
                    </div>
                    <div class="col-md-2">
                        <?php echo LANG_PINS_END_DATE; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_OPERATOR; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_CHANNEL; ?>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <form name="form1" method="post" action="" onSubmit="return validade()">
                        <div class="col-md-2">
                            <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <input alt="Calendário" name="tf_data_final" type="text" class="form-control data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-3">
    <?php 
                            if(b_is_Publisher())
                            {
                                echo $_SESSION["opr_nome"];
    ?>
                                <input type="hidden" name="dd_opr_codigo" id="dd_opr_codigo" value="<?php echo $dd_opr_codigo?>">
    <?php 
                            } else 
                            {
    ?>
                            <select name="dd_opr_codigo" id="dd_opr_codigo" class="form-control">
                                <option value=""><?php echo LANG_PINS_SELECT_OPERATOR; ?></option>
    <?php
                                while ($pgopr = pg_fetch_array($resopr))
                                {
    ?>
                                    <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_opr_codigo) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?></option>
    <?php  
                                }
    ?>
                            </select>
    <?php 
                            } 
    ?>
                        </div>
                        <div class="col-md-3">
                            <select name="fcanal" id="fcanal" class="form-control">
                                <option value=""<?php  if(trim($fcanal) == '') echo "selected"?>><?php echo LANG_PINS_ALL_CHANNELS; ?></option>
                                <option value="L"<?php  if(trim($fcanal) == 'L') echo "selected"?>>PIN Virtual</option>
                                <option value="C"<?php  if(trim($fcanal) == 'C') echo "selected"?>>PIN Card</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success">Pesquisar</button>
                        </div>
                    </form>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
<?php  
                    if($total_table > 0) 
                    {
                        $cabecalho = "'PSPCode','RIOT Order ID','PIN','TransactionType','Region','".LANG_PINS_ID."','Transaction','Purchase','E-Prepag + Tax','Net Amount','Country Code'";
?>
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                        <thead>
                          <tr class="bg-cinza-claro">
                            <th class="text-center">PSPCode</th>
                            <th class="text-center">RIOT Order ID</th>
                            <th class="text-center">PIN</th>
                            <th class="text-center">TransactionType</th>
                            <th class="text-center">Region</th>
                            <th class="text-center"><?php echo LANG_PINS_ID; ?></th>
                            <th class="text-center">Transaction</th>
                            <th class="text-center">Purchase</th>
                            <th class="text-center">E-Prepag + Tax</th>
                            <th class="text-center">Net Amount</th>
                            <th class="text-center">Country Code</th>
                          </tr>
                          <tr>
                            <th colspan="8">
                                <?php echo ' '.LANG_SHOW_DATA.' '; ?> <strong><?php  echo $inicial + 1 ?></strong><?php echo ' '.LANG_TO.' '; ?><strong><?php  echo $reg_ate ?></strong><?php echo ' '.LANG_FROM.' '; ?><strong><?php  echo $total_table ?> <span id="txt_totais" class="txt-azul-claro"></strong>
                            </th>
                          </tr>
                        </thead>
                        <tbody>
<?php
                      while ($pgrow = pg_fetch_array($resid)) {
                            $valor = 1;

                            //Calculando a Comissão
                            if($vetorComissaoIOF[$pgrow['opr_codigo']][$pgrow['vg_canal']]) {
                                $aux_comiss = ($pgrow['pin_valor']*$vetorComissao[$pgrow['opr_codigo']][$pgrow['vg_canal']]/100);
                                // Calculando IOF da nova maneira
                                $total_sem_iof = $pgrow['pin_valor']/(1+$vetorAlicotaIOF[$pgrow['opr_codigo']]/100);
                                $aux_comiss += $total_sem_iof*$vetorAlicotaIOF[$pgrow['opr_codigo']]/100; 
                            }
                            else {
								if($pgrow['opr_codigo'] == 90 && $pgrow[8] == "G"){
									$pgrow['vg_canal'] = "M";
								}	
                                $aux_comiss = ($pgrow['pin_valor']*$vetorComissao[$pgrow['opr_codigo']][$pgrow['vg_canal']]/100);
                            }
                            //Fim Calculando a Comissão
                            
                            $valor_geral += $pgrow['pin_valor'];
                            $valor_comiss += $aux_comiss;
                            $valor_liquido += ($pgrow['pin_valor']-$aux_comiss);

                            if(strtoupper($pgrow['vg_canal']) == 'C') {
                                $pin_serial	= $pc->decrypt(base64_decode($pgrow['case_codigo']));
                            }//end if(strtoupper($pgrow['vg_canal']) == 'C')
                            else {
                                $pin_serial	= $pgrow['case_codigo'];
                            }//end else do if(strtoupper($pgrow['vg_canal']) == 'C') 

                            $opr_codigo	= $pgrow['opr_codigo'];
							
							echo "<script>console.log(".json_encode($pgrow).");</script>";
							
?>
                            <tr class="trListagem">
                                <td align="center">E-Prepag</td>
                                <td><?php echo $pgrow['riot_order_id']; ?></td>
                                <td><?php echo $pin_serial; ?></td>
                                <td align="center"><?php  echo ($pgrow['vg_canal']=='C'?"PIN Card":"PIN Virtual"); ?></td>
                                <td align="center">BR</td>
                                <td align="right"><?php  echo $pgrow['pin_codinterno'] ?></td>
                                <td align="center"><nobr><?php if($pgrow['pin_datavenda']) { ?><?php  echo monta_data($pgrow['pin_datavenda']); ?> - <?php  echo $pgrow['pin_horavenda']; } else echo "--"; ?></nobr></td>
                                <td align="right" style="white-space:nowrap;"><?php  echo "R$ ".number_format($pgrow['pin_valor'], 2, ',', '.'); ?></td>
                                <td align="right" style="white-space:nowrap;"><?php  echo "R$ ".number_format($aux_comiss, 2, ',', '.'); ?></td>
                                <td align="right" style="white-space:nowrap;"><?php  echo "R$ ".number_format(($pgrow['pin_valor']-$aux_comiss), 2, ',', '.'); ?></td>
                                <td align="center">BR</td>
                            </tr>
<?php
                            flush();
                      }
                      
                    if(!$valor) {
?>
                        <tr bgcolor="#f5f5fb"> 
                            <td colspan="8"><?php echo LANG_NO_DATA; ?>.</td>
                        </tr>
<?php  
                    } else { 
                        $time_end = getmicrotime();
                        $time = $time_end - $time_start;
?>
                        <tr> 
                            <td colspan="6">&nbsp;</td>
                            <td><strong>TOTAL (R$)</strong></td>
                            <td class="text-right" style="white-space:nowrap;"><strong><?php  echo number_format($valor_geral, 2, ',', '.') ?></strong></td>
                            <td class="text-right" style="white-space:nowrap;"><strong><?php  echo number_format($valor_comiss, 2, ',', '.') ?></strong></td>
                            <td class="text-right" style="white-space:nowrap;"><strong><?php  echo number_format($valor_liquido, 2, ',', '.') ?></strong></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr class="bg-cinza-claro">
                            <td colspan="11" class="fontsize-pp"><?php echo LANG_PINS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                        </tr>
                        <tr class="bg-cinza-claro"> 
                            <td colspan="11" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); ?></td>
                        </tr>
<?php  
                    } 
?>
                        </tbody>
                    </table>
                    <div class="row text-center" style="margin-bottom: 15px;">
                        <a href="#" class="btn downloadCsv btn-info ">Download CSV</a>
                    </div>
                <script language="JavaScript">
                  document.getElementById('txt_totais').innerHTML = '( <?php echo number_format($valor_geral, 2, ',', '.') ?>)';
                </script>
<?php
                }
                elseif($BtnSearch) 
                {  
                     echo LANG_NO_DATA.".";
                }
?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:[2]});
    
    var optDate = new Object();
        optDate.interval = 1;

    setDateInterval('tf_data_inicial','tf_data_final',optDate);
});
</script>
<!-- FIM CODIGO NOVO -->
<?php  
pg_close($connid); 
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>
