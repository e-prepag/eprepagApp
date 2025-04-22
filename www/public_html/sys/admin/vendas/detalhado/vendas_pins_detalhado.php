<?php
/*
 * QUERY  Considerando totaliza��o por utiliza��o ou venda dinamicamente para o canal PDV
 select 
    t0.pin_codinterno, 
    pin_codigo as case_codigo, 
    t1.opr_nome, 
    t0.pin_valor, 
    t0.opr_codigo,
    t0.pin_datavenda,
    t0.pin_horavenda,
    'L' as vg_canal
from pins t0
    INNER JOIN operadoras t1 ON (t0.opr_codigo=t1.opr_codigo)
    INNER JOIN pins_status t3 ON (t0.pin_status=t3.stat_codigo) 
   LEFT OUTER JOIN pins_integracao_historico pih ON pih_pin_id = pin_codinterno
where CASE WHEN opr_contabiliza_utilizacao != 0 THEN t0.pin_status = '8' ELSE t0.pin_status = '6' END
    and CASE WHEN opr_contabiliza_utilizacao != 0 THEN pih_codretepp='2' ELSE t0.pin_status = '6' END
    and CASE WHEN opr_contabiliza_utilizacao != 0 THEN pih_data >= '2017-11-22 00:00:00' ELSE pin_datavenda >= '2017-11-22 00:00:00' END
    and CASE WHEN opr_contabiliza_utilizacao != 0 THEN pih_data <= '2017-11-22 23:59:59' ELSE pin_datavenda <= '2017-11-22 23:59:59' END
    and (t0.opr_codigo=13)   
 */
ob_start();

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";

set_time_limit (60000) ;

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
    
        //Buscando PINs na banco de dados
        $sql = "
                select 
				    pin_guid_epp,
					pin_guid_parceiro,
                    pin_codinterno, 
                    case_codigo, 
                    opr_nome, 
                    pin_valor, 
                    opr_codigo,
                    pin_datavenda,
                    pin_horavenda,
                    vg_canal
                FROM (
                ";
        if(strtoupper($fcanal) == 'L' || empty($fcanal)) { //distinct on(pin_codigo) 
            $sql .= "
                   (select 
				   		pin_codigo as case_codigo, 
				        t0.pin_guid_epp,
						t0.pin_guid_parceiro,
                        t0.pin_codinterno,				
                        t1.opr_nome, 
                        t0.pin_valor, 
                        t0.opr_codigo,
                        to_char(pih_data,'YYYY-MM-DD')::timestamp as pin_datavenda,
                        to_char(pih_data,'HH24:MI:SS') as pin_horavenda,
                        'L' as vg_canal
                    from pins t0
                        INNER JOIN operadoras t1 ON (t0.opr_codigo=t1.opr_codigo)
                        INNER JOIN pins_status t3 ON (t0.pin_status=t3.stat_codigo) 
                        INNER JOIN pins_integracao_historico pih ON pih_pin_id = pin_codinterno
                        INNER JOIN tb_dist_venda_games_modelo_pins vgmp ON vgmp_pin_codinterno = pih_pin_id
                        INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_id = vgmp_vgm_id 
                        INNER JOIN tb_dist_venda_games ON vg_id = vgm_vg_id
                    where t0.pin_status = '8'
					AND pih_codretepp='2' 
                          AND vg_ultimo_status='5' 
                          AND (CASE WHEN t1.opr_contabiliza_utilizacao <> 0 THEN vg_data_inclusao >= t1.opr_data_inicio_contabilizacao_utilizacao ELSE TRUE END) ";
            if($tf_data_inicial && $tf_data_final) {
                        $data_inic = formata_data(trim($tf_data_inicial), 1);
                        $data_fim = formata_data(trim($tf_data_final), 1); 
                        $sql .= " 
                        AND pih_data >= '".trim($data_inic)." 00:00:00' AND  pih_data <= '".trim($data_fim)." 23:59:59'  "; //::timestamp + '1 second'::interval
            }
            if($dd_opr_codigo) {
                $sql .= " 
                        AND (t0.opr_codigo=".$dd_opr_codigo.")  ";
            }
            $sql .= " 
                     ) ".PHP_EOL;
			/*$sql .= "
			        UNION ALL
					
                   (select 
					  pin_codigo as case_codigo, 
					  '',
					  '',
					  t0.pin_codinterno, 
					  t1.opr_nome, 
					  t0.pin_valor, 
					  t0.opr_codigo, 
					  to_char(vg_data_inclusao, 'YYYY-MM-DD'):: timestamp as pin_datavenda, 
					  to_char(vg_data_inclusao, 'HH24:MI:SS') as pin_horavenda, 
					  'L' as vg_canal 
                    from pins t0
                        INNER JOIN operadoras t1 ON (t0.opr_codigo=t1.opr_codigo)
                        INNER JOIN pins_status t3 ON (t0.pin_status=t3.stat_codigo) 
                        INNER JOIN tb_dist_venda_games_modelo_pins vgmp ON vgmp_pin_codinterno = t0.pin_codinterno
                        INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_id = vgmp_vgm_id 
                        INNER JOIN tb_dist_venda_games ON vg_id = vgm_vg_id
                    where t0.pin_status = '6' 
                          AND vg_ultimo_status='5' 
                          AND (CASE WHEN t1.opr_contabiliza_utilizacao <> 0 THEN vg_data_inclusao >= t1.opr_data_inicio_contabilizacao_utilizacao ELSE TRUE END) ";
            if($tf_data_inicial && $tf_data_final) {
                        $data_inic = formata_data(trim($tf_data_inicial), 1);
                        $data_fim = formata_data(trim($tf_data_final), 1); 
                        $sql .= " 
                        AND vg_data_inclusao >= '".trim($data_inic)." 00:00:00' AND  vg_data_inclusao <= '".trim($data_fim)." 23:59:59'  "; //::timestamp + '1 second'::interval
            }
            if($dd_opr_codigo) {
                $sql .= " 
                        AND (t0.opr_codigo=".$dd_opr_codigo.")  ";
            }
            $sql .= " 
                     ) ".PHP_EOL; */
            $sql .= "
                    UNION ALL

                    (select 
					    distinct on(pin_codigo) pin_codigo as case_codigo, 
					    t0.pin_guid_epp,
						t0.pin_guid_parceiro,
                        t0.pin_codinterno,			           
                        t1.opr_nome, 
                        t0.pin_valor, 
                        t0.opr_codigo,
                        to_char(vg_data_concilia,'YYYY-MM-DD')::timestamp as pin_datavenda,
                        to_char(vg_data_concilia,'HH24:MI:SS') as pin_horavenda,
                        'G' as vg_canal
                    from pins t0
                        INNER JOIN operadoras t1 ON (t0.opr_codigo=t1.opr_codigo)
                        INNER JOIN pins_status t3 ON (t0.pin_status=t3.stat_codigo) 
                        INNER JOIN tb_venda_games_modelo_pins vgmp ON vgmp_pin_codinterno = pin_codinterno
                        INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_id = vgmp_vgm_id 
                        INNER JOIN tb_venda_games ON vg_id = vgm_vg_id
                    where vg_ultimo_status='5' 
                          AND (CASE WHEN t1.opr_contabiliza_utilizacao <> 0 THEN vg_data_inclusao >= t1.opr_data_inicio_contabilizacao_utilizacao ELSE TRUE END) ";
            if($tf_data_inicial && $tf_data_final) {
                        $data_inic = formata_data(trim($tf_data_inicial), 1);
                        $data_fim = formata_data(trim($tf_data_final), 1); 
		
                        $sql .= " 
                        AND vg_data_concilia >= '".trim($data_inic)." 00:00:00' AND  vg_data_concilia <= '".trim($data_fim)." 23:59:59'  "; //::timestamp + '1 second'::interval
            }
            if($dd_opr_codigo) {
                $sql .= " 
                        AND (t0.opr_codigo=".$dd_opr_codigo.")  ";
            }
            $sql .= " 
                     ) ".PHP_EOL;
                                
                     
            /* Modelo abaixo PINs por utiliza��o para Gamers         
            $sql .= "
                    UNION ALL

                    (select 
                        t0.pin_codinterno, 
                        pin_codigo as case_codigo, 
                        t1.opr_nome, 
                        t0.pin_valor, 
                        t0.opr_codigo,
                        to_char(pih_data,'YYYY-MM-DD')::timestamp as pin_datavenda,
                        to_char(pih_data,'HH24:MI:SS') as pin_horavenda,
                        'G' as vg_canal
                    from pins t0
                        INNER JOIN operadoras t1 ON (t0.opr_codigo=t1.opr_codigo)
                        INNER JOIN pins_status t3 ON (t0.pin_status=t3.stat_codigo) 
                        INNER JOIN pins_integracao_historico pih ON pih_pin_id = pin_codinterno
                        INNER JOIN tb_venda_games_modelo_pins vgmp ON vgmp_pin_codinterno = pih_pin_id
                        INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_id = vgmp_vgm_id 
                        INNER JOIN tb_venda_games ON vg_id = vgm_vg_id
                    where t0.pin_status = '8' 
                          AND pih_codretepp='2' 
                          AND vg_ultimo_status='5' 
                          AND (CASE WHEN t1.opr_contabiliza_utilizacao <> 0 THEN vg_data_inclusao >= t1.opr_data_inicio_contabilizacao_utilizacao ELSE TRUE END) ";
            if($tf_data_inicial && $tf_data_final) {
                        $data_inic = formata_data(trim($tf_data_inicial), 1);
                        $data_fim = formata_data(trim($tf_data_final), 1); 
                        $sql .= " 
                        AND pih_data >= '".trim($data_inic)." 00:00:00' AND  pih_data <= '".trim($data_fim)." 23:59:59'  "; //::timestamp + '1 second'::interval
            }
            if($dd_opr_codigo) {
                $sql .= " 
                        AND (t0.opr_codigo=".$dd_opr_codigo.")  ";
            }
            $sql .= " 
                     ) ".PHP_EOL;
            */
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
				        pin_codigo as case_codigo, 
				        '',
						'',
                        pih_pin_id as pin_codinterno, 
                        o.opr_nome, 
                        pin_valor, 
                        pih_id as opr_codigo,
                        to_char(pih_data,'YYYY-MM-DD')::timestamp as pin_datavenda,
                        to_char(pih_data,'HH24:MI:SS') as pin_horavenda,
                        'C' as vg_canal
                    FROM pins_integracao_card_historico pich
                        INNER JOIN pins_card pc ON pin_codinterno=pih_pin_id
                        INNER JOIN operadoras o ON pc.opr_codigo=o.opr_codigo
                    WHERE pih_codretepp='2'
                        AND pich.pin_status =4
                        AND CASE WHEN pih_id = 90 THEN pin_lote_codigo > 6 ELSE pin_lote_codigo > 0 END 
                        "; //-- Codigo de lotes menor e igual a 6 foram utilizados para testes da RIOT
            if($dd_opr_codigo) {
                    $sql .= " 
                        AND pih_id = ".$dd_opr_codigo;
            } //end if($dd_opr_codigo) 
            if($tf_data_inicial && $tf_data_final) {
                        $data_inic = formata_data(trim($tf_data_inicial), 1);
                        $data_fim = formata_data(trim($tf_data_final), 1); 
                        $sql .= " 
                        AND pih_data >= '".trim($data_inic)." 00:00:00' AND  pih_data <= '".trim($data_fim)." 23:59:59'  "; 
            }
            $sql .= " 
                    )
                    ";
        } //end if(strtoupper($fcanal) == 'C' || empty($fcanal)) 
        
	if(strtoupper($fcanal) == 'CG' || empty($fcanal)) {
            
            if(empty($fcanal)) {
                $sql .= "
                    UNION ALL
                        ";
            }//end if(empty($fcanal))
            
            $sql .= "
                    ( SELECT 
					    pgc_pin_number as case_codigo, 
					    '',
						'',
                        pgc_id as pin_codinterno,
                        opr_nome,
                        CASE WHEN opr_product_type = 5 THEN pgc_real_amount 
                                 WHEN (opr_product_type = 7 OR opr_product_type = 4)  THEN pgc_face_amount 
                                 ELSE pgc_face_amount END as pin_valor, 
                        opr_codigo,
                        to_char(pgc_pin_response_date,'YYYY-MM-DD')::timestamp as pin_datavenda,
                        to_char(pgc_pin_response_date,'HH24:MI:SS') as pin_horavenda,
                        'CG' as vg_canal
                    FROM pins_gocash
                        INNER JOIN operadoras ON opr_codigo = pgc_opr_codigo
                    WHERE pgc_opr_codigo != 0 
                          "; 
            if($dd_opr_codigo) {
                    $sql .= " 
                        AND pgc_opr_codigo = ".$dd_opr_codigo;
            } //end if($dd_opr_codigo) 
            if($tf_data_inicial && $tf_data_final) {
                        $data_inic = formata_data(trim($tf_data_inicial), 1);
                        $data_fim = formata_data(trim($tf_data_final), 1); 
                        $sql .= " 
                        AND pgc_pin_response_date >= '".trim($data_inic)." 00:00:00' AND  pgc_pin_response_date <= '".trim($data_fim)." 23:59:59'  "; 
            }
            $sql .= "
                    ) 
            ";
        }//end if(strtoupper($fcanal) == 'CG' || empty($fcanal))
        
        $sql .= " 
                ) as selection
                order by pin_datavenda desc, pin_horavenda desc";
        
		//echo "<pre>".print_r($sql,true)."</pre>";
		
	//echo $sql;
		
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
            window.alert("Por favor selecione uma diferen�a entre Datas de no m�ximo 30/31 dias.");
            document.form1.tf_data_inicial.focus();
            return false;
        }
        else return true;
}

function DiferencaDatas(data1, data2) {
    //Diferen�a entre datas com resultado em dias
    
    //Splitando
    var vetorData1 = data1.split("/");
    var vetorData2 = data2.split("/");
    // new Date(year, month, day, hours, minutes, seconds, milliseconds)
    var a = new Date(vetorData1[2],vetorData1[1],vetorData1[0], 0, 0, 0, 0); // data1
    var b = new Date(vetorData2[2],vetorData2[1],vetorData2[0], 0, 0, 0, 0); // data1
    var d = (b-a); // Diferen�a em millisegundos

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
						<?php //echo $sql;?>
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
                        <?php echo LANG_PINS_TYPE; ?>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <form name="form1" method="post" action="" onSubmit="return validade()">
                        <div class="col-md-2">
                            <input  alt="Calend�rio" name="tf_data_inicial" type="text" class="form-control data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <input alt="Calend�rio" name="tf_data_final" type="text" class="form-control data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
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
    <?php
                                while ($pgopr = pg_fetch_array($resopr))
                                {
    ?>
                                    <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if($pgopr['opr_codigo'] == $dd_opr_codigo || (!$dd_opr_codigo && $pgopr['opr_codigo'] == 13)) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?></option>
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
                                <option value=""<?php  if(trim($fcanal) == '') echo "selected"?>><?php echo LANG_PINS_ALL_TYPES; ?></option>
                                <option value="L"<?php  if(trim($fcanal) == 'L') echo "selected"?>>PIN Virtual</option>
                                <option value="C"<?php  if(trim($fcanal) == 'C') echo "selected"?>>PIN Card</option>
                                <option value="CG"<?php  if(trim($fcanal) == 'CG') echo "selected"?>>PIN Card E-Prepag CASH</option>
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
                        $cabecalho = (!b_is_Publisher()?"'Publisher',":"")."'GUID E-Prepag','GUID Partner','PIN','". LANG_PINS_TRANSACTION_TYPE."','".LANG_PINS_ID."','".LANG_PINS_DATE."','".LANG_PINS_QUANTITY."'"; //,'Comiss�o','Net Amount'
?>
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                        <thead>
                          <tr class="bg-cinza-claro">
<?php
                            if(!b_is_Publisher()) {
?>
                            <th class="text-center">Publisher</th>
<?php
                            }
?>
                            <th class="text-center">GUID E-Prepag</th>
							<th class="text-center">GUID Partner</th>
                            <th class="text-center">PIN</th>
                            <th class="text-center"><?php echo LANG_PINS_TRANSACTION_TYPE; ?></th>
                            <th class="text-center"><?php echo LANG_PINS_ID; ?></th>
                            <th class="text-center"><?php echo LANG_PINS_DATE; ?></th>
                            <th class="text-center"><?php echo LANG_PINS_QUANTITY; ?></th>
							<!--<th class="text-center"><?php //echo "Date"; ?></th>-->
<?php
/*
?>                            
                            <th class="text-center">Comiss�o</th>
                            <th class="text-center">Net Amount</th>
 <?php
 */
?>
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
							
							
							if($pgrow['opr_codigo'] == 124){
								 switch($pgrow['pin_valor']){
									case 4:
									    $pin_valor = 4.49;
										break;
									case 5:
										$pin_valor = 4.49;
										break;
									case 14:
										$pin_valor = 13.99;
										break;
									case 21:
										$pin_valor = 20.99;
										break;
									case 45:
										$pin_valor = 44.99;
										break;
									case 88:
										$pin_valor = 87.99;
										break;
									case 210:
										$pin_valor = 209.99;
										break;
									default:
									    $pin_valor = $pgrow['pin_valor'];
									    break;
								}
							}else{
								$pin_valor = $pgrow['pin_valor'];
							}
							
                            $valor_geral += $pin_valor;
                            
                            if(strtoupper($pgrow['vg_canal']) == 'C') {
                                $pin_serial	= $pc->decrypt(base64_decode($pgrow['case_codigo']));
                            }//end if(strtoupper($pgrow['vg_canal']) == 'C')
                            else {
                                $pin_serial	= $pgrow['case_codigo'];
                            }//end else do if(strtoupper($pgrow['vg_canal']) == 'C') 

                            $opr_codigo	= $pgrow['opr_codigo'];
?>
                            <tr class="trListagem">
<?php
                                if(!b_is_Publisher()) {
?>
                                <td align="right"><?php  echo $pgrow['opr_nome'] ?></td>
<?php
                                }
																
								$guid_epp = !empty($pgrow['pin_guid_epp'])?$pgrow['pin_guid_epp']: LANG_NOT;
								$guid_partner = !empty($pgrow['pin_guid_parceiro'])?$pgrow['pin_guid_parceiro']: LANG_NOT; 
?>
                                <td><?php echo $guid_epp; ?></td>
								<td><?php echo $guid_partner; ?></td>
                                <td><?php echo $pin_serial; ?></td>
                                <td align="center"><?php  echo (($pgrow['vg_canal']=='L'||$pgrow['vg_canal']=='G')?"PIN Virtual":($pgrow['vg_canal']=='C'?"PIN Card":($pgrow['vg_canal']=='CG'?"PIN Card E-Prepag CASH":"--"))); ?></td>
                                <td align="right"><?php  echo $pgrow['pin_codinterno'] ?></td>
                                <td align="center"><nobr><?php if($pgrow['pin_datavenda']) { ?><?php  echo monta_data($pgrow['pin_datavenda']); ?> - <?php  echo $pgrow['pin_horavenda']; } else echo "--"; ?></nobr></td>
                                <td align="right" style="white-space:nowrap;"><?php  echo "R$ ".number_format($pin_valor, 2, ',', '.'); ?></td> 
								<!--<td align="right" style="white-space:nowrap;"><?php  //echo monta_data($pgrow['vg_data_inclusao']); ?></td>-->
<?php
/*
?>                                
                                <td align="right" style="white-space:nowrap;"><?php  echo "R$ ".number_format($aux_comiss, 2, ',', '.'); ?></td>
                                <td align="right" style="white-space:nowrap;"><?php  echo "R$ ".number_format(($pgrow['pin_valor']-$aux_comiss), 2, ',', '.'); ?></td>
 <?php
 */
?>
                            </tr>
<?php
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
                            <td colspan="<?php echo (!b_is_Publisher())?"4":"3"; ?>">&nbsp;</td>
                            <td><strong>TOTAL (R$)</strong></td>
                            <td class="text-right" style="white-space:nowrap;"><strong><?php  echo number_format($valor_geral, 2, ',', '.') ?></strong></td>
<?php
/*
?>                            
                            <td class="text-right" style="white-space:nowrap;"><strong><?php  echo number_format($valor_comiss, 2, ',', '.') ?></strong></td>
                            <td class="text-right" style="white-space:nowrap;"><strong><?php  echo number_format($valor_liquido, 2, ',', '.') ?></strong></td>
                            <td>&nbsp;</td>
 <?php
 */
?>
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
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:[1]});
    
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
