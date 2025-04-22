<?php

	set_time_limit ( 300 ) ;

// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

	require_once "../../../../../includes/constantes.php";
        require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
        require_once $raiz_do_projeto . "includes/gamer/constantes.php";
	//include "../../../incs/configuracao.inc";
	$pos_pagina = $seg_auxilar;
	
	//include "../../../connections/connect.php";
	//include "../../../incs/header.php";
	//include "../../../incs/functions.php";
	$time_start = getmicrotime();
	
//echo "dd_operadora: ".$dd_operadora."<br>";
//echo "Submit: $Submit<br>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		if(!$dd_mode) $dd_mode = "S";
		$Submit = "Buscar";
	} else {
		if(!$dd_mode) $dd_mode = "V";
	}

	if(!$dd_mode || ($dd_mode!='V')) {
		$dd_mode = "S";
	}
	
	
	$where_mode_data = "vg.vg_data_inclusao";	// default
	if($dd_mode=='S') $where_mode_data = "vg.vg_data_concilia";
	
	
	if(!$tf_data_inic) $tf_data_inic = date('d/m/Y');
	if(!$tf_data_final) $tf_data_final = date('d/m/Y');

	$default_add  = nome_arquivo($PHP_SELF);

	$qtde_dias_vendas = qtde_dias($tf_data_inic, $tf_data_final) + 1;

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_nome";
	} else {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status != '0') order by opr_nome";
	}
	$resopr = pg_exec($connid, $sqlopr);
//echo "$sqlopr<br>";


?>
<link href="/css/css.css" rel="stylesheet" type="text/css"/>
<title>E-Prepag</title>
<script language="JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
</head>
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
                        <strong><?php echo LANG_REPORTS_PAGE_TITLE_7;?></strong>
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
                <form name="form1" method="post" action="">
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_CHANNEL; ?></span>
                        </div>
                        <div class="col-md-3">
                            <select name="dd_canal" id="dd_canal" class="form-control pull-left">
                                <option value="" <?php  if($dd_canal!="Site" && $dd_canal!="POS" && $dd_canal!="ATIMO") echo "selected" ?>><?php echo LANG_PINS_ALL; ?></option>
                                <option value="Site" <?php if("Site" == $dd_canal) echo "selected" ?>>Site</option>
                                <option value="POS" <?php if("POS" == $dd_canal) echo "selected" ?>>POS</option>
								<option value="ATIMO" <?php  if($dd_canal=="ATIMO") echo "selected"; ?>>ATIMO</option>
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
                            <input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora?>">
<?php 
                        } else 
                        {
?>
                            <select name="dd_operadora" id="dd_operadora" class="form-control pull-left">
                                <option value=""><?php echo LANG_PINS_ALL_OPERATORS; ?></option>
                                <?php while ($pgopr = pg_fetch_array ($resopr)) { ?>
				  <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php echo $pgopr['opr_nome'] ?></option>
                                <?php } ?>
                            </select>
<?php 
                        } 
?>
                        </div> 
                    </div>
                    <div class="row txt-cinza top10">
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_PINS_REPORT_TYPE; ?></span>
                        </div>
                        <div class="col-md-3">
<?php  
                    if($_SESSION["tipo_acesso_pub"]=='PU') 
                    {
?>
                            <strong><?php echo LANG_PINS_OUT; ?></strong>
                            <input type="hidden" name="dd_mode" id="dd_mode" value="<?php echo $dd_mode?>">
<?php  
                    } else 
                    { 
?>	
                            <select name="dd_mode" id="dd_mode" class="form-control pull-left">
                                <option value="S" <?php  if($dd_mode=="S") echo "selected" ?>><?php echo LANG_PINS_OUT; ?></option>
                                <option value="V" <?php  if($dd_mode=="V") echo "selected" ?>><?php echo LANG_PINS_SALE; ?></option>
                            </select>
<?php 
                    } 
?>
                        </div>
                        <div class="col-md-2">
                            <span class="pull-right"><?php echo LANG_REPORTS_INTERVAL_DATES_MSG;?>:</span>
                        </div>
                        <div class="col-md-3">
                            <input name="tf_data_inic" type="text" class="form-control data pull-left w100" id="tf_data_inic" value="<?php echo $tf_data_inic ?>" size="9" maxlength="10">
                            <span class="pull-left espacamento-laterais10"> até </span>
                            <input name="tf_data_final" type="text" class="form-control data pull-left w100" id="tf_data_final" value="<?php echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-2 pull-right">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH;?></button>
                        </div>
                    </div>
                </form>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                        <thead>
                          <tr class="bg-cinza-claro">
                            <th>Range</th>
                            <th class="text-right"><?php echo LANG_PINS_QUANTITY;?></th>
                            <th class="text-right"><?php echo LANG_REPORTS_AVERAGE_QUANTITY;?></th>
                            <th class="text-right"><?php echo LANG_PINS_TOTAL_VALUE;?></th>
                            <th class="text-right"><?php echo LANG_REPORTS_AVERAGE_VALUE;?></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
        $where_opr_1 = "";
	$where_opr_2 = "";
	$where_opr_3 = "";
	$where_canal = "";

	if($dd_canal) {
		$where_canal = " and canal= '".$dd_canal."' ";
	}

	if($dd_operadora) {
		$where_opr_1 = " and (t0.opr_codigo = ".$dd_operadora.") ";

		if($dd_operadora==13)	//($dd_operadora_nome=='ONGAME') 
			$where_opr_2 = " and (ve_jogo = 'OG') ";
		elseif  ($dd_operadora==17)	//($dd_operadora_nome=='MU ONLINE') 
			$where_opr_2 = " and (ve_jogo = 'MU') ";
		elseif  ($dd_operadora==16)	//($dd_operadora_nome=='HABBO HOTEL') 
			$where_opr_2 = " and (ve_jogo = 'HB') ";
		else
			$where_opr_2 = " and (ve_jogo = 'xx') ";

		$where_opr_3 = " and (vgm.vgm_opr_codigo= ".$dd_operadora.") ";

	}


// **					for ($i = 0 ; $i <= 23 ; $i++)
// **					{


        $sql  = "select hora_seq, sum(quantidade) as quantidade, sum (total) as total 
                from (
                    select hora_seq, sum(quantidade) as quantidade, sum (total) as total, canal 
                    from (
                        (select (generate_series(0,23)) as hora_seq  ) d
                        left outer join
                                (												
                                select extract (hour from vg.vg_data_inclusao) as hora, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'Site'::text as canal  
                                from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                where vg.vg_data_inclusao>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' ".$where_opr_3." 
                                        and (vg.vg_data_inclusao>='".formata_data($tf_data_inic, 1)." 00:00:00' and vg.vg_data_inclusao<='".formata_data($tf_data_final, 1)." 23:59:59') 
                                group by extract (hour from vg.vg_data_inclusao) 				

                                union all

                                select extract (hour from $where_mode_data) as hora, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'Site'::text as canal  
                                from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                where $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and vg.vg_pagto_tipo != ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." 
                                        and ($where_mode_data>='".formata_data($tf_data_inic, 1)." 00:00:00' and $where_mode_data<='".formata_data($tf_data_final, 1)." 23:59:59') 
                                group by extract (hour from $where_mode_data) 			

                                union all

                                select extract (hour from $where_mode_data) as hora, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'Site'::text as canal  
                                from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
                                where $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and (tvgpo.tvgpo_canal='G' or tvgpo.tvgpo_canal='L') and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." 
                                        and ($where_mode_data>='".formata_data($tf_data_inic, 1)." 00:00:00' and $where_mode_data<='".formata_data($tf_data_final, 1)." 23:59:59') 
                                group by extract (hour from $where_mode_data)

                                union all

                                select extract (hour from $where_mode_data) as hora, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'ATIMO'::text as canal  
                                from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id  
                                where $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and vg.vg_ultimo_status_obs like '%AtimoPay%' and vg.vg_http_referer_origem = 'ATIMO' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." 
                                        and ($where_mode_data>='".formata_data($tf_data_inic, 1)." 00:00:00' and $where_mode_data<='".formata_data($tf_data_final, 1)." 23:59:59') 
                                group by extract (hour from $where_mode_data)								

                                union all

                                select extract (hour from $where_mode_data) as hora, sum(vgm.vgm_qtde) as quantidade, sum(vgm.vgm_valor * vgm.vgm_qtde) as total, 'POS'::text as canal  
                                from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 
                                where $where_mode_data>='2008-01-01 00:00:00' and vg.vg_ultimo_status='5' and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' and vg.vg_pagto_tipo = ".$PAGAMENTO_PIN_EPREPAG_NUMERIC." ".$where_opr_3." 
                                        and ($where_mode_data>='".formata_data($tf_data_inic, 1)." 00:00:00' and $where_mode_data<='".formata_data($tf_data_final, 1)." 23:59:59') 
                                group by extract (hour from $where_mode_data) 			

                                union all

                                select extract (hour from ve_data_inclusao) as hora, count(*) as quantidade, sum(ve_valor) as total, 'POS'::text as canal  
                                from dist_vendas_pos 
                                where 1=1 ".$where_opr_2." 
                                        and (ve_data_inclusao>='".formata_data($tf_data_inic, 1)." 00:00:00' and ve_data_inclusao<='".formata_data($tf_data_final, 1)." 23:59:59') 
                                group by extract (hour from ve_data_inclusao)
                                ) v 
                        on d.hora_seq = v.hora
                        ) vseq
                        group by hora_seq, canal 
                    ) vh 
                ";
                        // "inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id "
				
                $sql .= " where 1=1 ".$where_canal." ";
                $sql .= " group by hora_seq ";
                $sql .= " order by hora_seq ";
				
				if ($_SERVER['REMOTE_ADDR'] == '201.93.162.169') {
					echo "<div style='background-color:#000;color:#fff'>".$sql."</div>";
				}
				
                $cabecalho = "'Range','Qtde','Média Qtde','Valor Total','Média Valor'";

                //echo $sql;
                $resven = SQLexecuteQuery($sql);
                if($resven && pg_num_rows($resven)>0) 
                {
                    while($pgven = pg_fetch_array($resven))
                    {
                            $media_qtde = $pgven['quantidade'] / $qtde_dias_vendas;
                            $media_valor = $pgven['total'] / $qtde_dias_vendas;
								
                            $valor = true;
//echo "media_qtde: ".$media_qtde." , media_valor : ".$media_valor."<br>";

                            $pin_total_valor += $pgven['total'];
                            $pin_total_qtde += $pgven['quantidade'];

                            $pin_total_media_valor += $media_valor;
                            $pin_total_media_qtde += $media_qtde;
							
if(b_IsUsuarioReinaldo()) { 
	if($media_valor>0) {
		$stitle = number_format($media_valor/60, 2, ',', '.') ."/min";
	}
}
?>
                            <tr class="trListagem"> 
                                <td><?php echo "das ".$pgven['hora_seq'].":00:00 as ".$pgven['hora_seq'].":59:59" ?></td>
                                <td class="text-right"><?php echo number_format($pgven['quantidade'], 0, ',', '.') ?></td>
                                <td class="text-right"><?php echo number_format($media_qtde, 2, ',', '.') ."/dia" ?></td>
                                <td class="text-right"><?php echo number_format($pgven['total'], 2, ',', '.') ?></td>
                                <td class="text-right"><?php echo number_format($media_valor, 2, ',', '.') ."/dia" ?></td>
                            </tr>
<?php                            

                        }
                }
                $stitle = "";
              
                        if (!$valor)
                        {
?>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="5">
                                  <?php echo LANG_NO_DATA; ?>.
                                </td>
                            </tr>
<?php  
                        } else 
                        { 
?>
                            <tr class="bg-cinza-claro"> 
                                <td><strong>TOTAL</strong></td>
                                <td class="text-right"><?php echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></td>
                                <td class="text-right"><strong><?php echo number_format($pin_total_media_qtde, 2, ',', '.') ."/dia" ?></strong></td>
                                <td class="text-right"><strong><?php echo number_format($pin_total_valor, 2, ',', '.') ?></strong></td>
                                <td class="text-right"><strong><?php echo number_format($pin_total_media_valor, 2, ',', '.') ."/dia" ?></strong></td>
                            </tr>
<?php 
                            $time_end = getmicrotime();
                            $time = $time_end - $time_start;
?>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="5" class="fontsize-pp"><strong><?php echo LANG_PINS_LAST_MSG; ?>. </strong></td>
                            </tr>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="5" class="fontsize-pp"><?php  echo LANG_PINS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                            </tr>
                            <tr class="bg-cinza-claro"> 
                                <td colspan="5" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); ?></td>
                            </tr>
<?php  
                        } 
?>
                        </tbody>
                      </table>
<?php
                        if($valor) {
?>
                        <div class="row text-center" style="margin-bottom: 15px;">
                            <a href="#" class="btn downloadCsv btn-info ">Download CSV</a>
                        </div>
<?php
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
<script type="text/javascript" src="/js/table2CSV.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:""});
    
    var optDate = new Object();
        optDate.interval = 1;

    setDateInterval('tf_data_inic','tf_data_final',optDate);
});
</script>

<!-- FIM CODIGO NOVO -->

<?php  
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>