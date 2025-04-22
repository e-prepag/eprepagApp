<?php
ob_start();

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";

// Moeda considerada no relatório
// https://pt.wikipedia.org/wiki/ISO_4217
define("REMESSA_MOEDA", "BRL");

define("FORMA_PAGAMENTO", "CARTAO");

//Disponibilizando acesso restrito
set_time_limit (60000) ;

$time_start = getmicrotime();

$b_debug = false;
if ($_SESSION["tipo_acesso_pub"]=='AT') {
    if(!$flist_vg_id) {
	$flist_vg_id = false;
    }//end if(!$flist_vg_id)
}

if(!$dd_opr_codigo) $dd_opr_codigo = '';
if(!$tf_data_final)    $tf_data_final   = date('d/m/Y');
if(!$tf_data_inicial)  $tf_data_inicial = date('d/m/Y');

if(b_is_Publisher()) {
        $dd_opr_codigo = $_SESSION["opr_codigo_pub"];
}

// Levanta lista de operadoras
$sql  = "select opr_codigo, opr_nome, opr_pais, opr_razao from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
$resopr = pg_exec($connid,$sql);

if($BtnSearch) {
    
        $data_inic = formata_data(trim($tf_data_inicial), 1);
        $data_fim = formata_data(trim($tf_data_final), 1); 
  
        if($dd_opr_codigo) {
            // Buscando informações 
            $sql = "SELECT 
                            opr_codigo, 
                            opr_data_inicio_contabilizacao_utilizacao
                    FROM operadoras
                    WHERE 
                            opr_contabiliza_utilizacao != 0
                            AND opr_codigo = ".$dd_opr_codigo.";";

            //echo $sql.PHP_EOL; die();
            $rs_publisher = SQLexecuteQuery($sql);
            //echo pg_num_rows($rs_publisher)."<br>";
            if(!$rs_publisher) {
                echo "Erro na Query de Levantamento de Publishers contendo Fechamento por Utilização de PINs(".$sql.").<br>".PHP_EOL;
                $possui_totalizacao_utilizacao = FALSE;
            }elseif(pg_num_rows($rs_publisher) > 0) {
                $possui_totalizacao_utilizacao = TRUE;
            }//end if(pg_num_rows($rs_publisher) == 0)
            else {
                $possui_totalizacao_utilizacao = FALSE;
            }//end else
        }//end if($dd_opr_codigo) 
        else $possui_totalizacao_utilizacao = FALSE;
        
        //Buscando PINs na banco de dados
        $sql = "
                select 
                    ug_cpf, 
                    ug_nome,
                    data,
                    valor_total
                from ( 
                        ( select 
                                ug_cpf, 
                                ug_nome_cpf as ug_nome,
                                vg_data_concilia as data,
                                sum(vgm.vgm_valor * vgm.vgm_qtde) as valor_total 
                        from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                        where vg.vg_ultimo_status='5' 
                                and vg_ug_id != 7909
                                and vg.vg_data_concilia >= '".trim($data_inic)." 00:00:00'
                                and vg.vg_data_concilia <= '".trim($data_fim)." 23:59:59' ";               
        if($dd_opr_codigo) {
            $sql .= " 
                                and (vgm_opr_codigo=".$dd_opr_codigo.")  ".PHP_EOL;
        }
        $sql .= "
                        group by ug_cpf, ug_nome_cpf, vg_data_concilia )

                    union all

                        (select 
                                vgm_cpf as ug_cpf, 
                                vgm_nome_cpf as ug_nome, 
                                vg_data_inclusao as data, ".PHP_EOL;               
        if($dd_opr_codigo && $possui_totalizacao_utilizacao) {
            $sql .= "                                sum(vgm.vgm_valor) as valor_total ".PHP_EOL;
        }
        else {
            $sql .= "
                                sum(vgm.vgm_valor * vgm.vgm_qtde) as valor_total ".PHP_EOL;
        }
        $sql .= "
                        from tb_dist_venda_games vg 
                                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id  ".PHP_EOL;               
        if($dd_opr_codigo && $possui_totalizacao_utilizacao) {
            $sql .= "                              inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 
                             inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno ".PHP_EOL;
        }
        $sql .= "
                        where vg.vg_ultimo_status='5'  ".PHP_EOL;               
        if($dd_opr_codigo && $possui_totalizacao_utilizacao) {
            $sql .= "                              and pin_status = '8'
                                and pih_codretepp='2'
                                and pih_data >= '".trim($data_inic)." 00:00:00'
                                and pih_data <= '".trim($data_fim)." 23:59:59'".PHP_EOL;
        }
        else {
            $sql .= "
                                and vg.vg_data_inclusao >= '".trim($data_inic)." 00:00:00'
                                and vg.vg_data_inclusao <= '".trim($data_fim)." 23:59:59'";               
        }
        if($dd_opr_codigo) {
            $sql .= " 
                                and (vgm_opr_codigo=".$dd_opr_codigo.")  ".PHP_EOL;
        }
        $sql .= "
                        group by vgm_cpf, vgm_nome_cpf, vg_data_inclusao )  


                    union all

                        (select 
                                picc_cpf as ug_cpf, 
                                picc_nome as ug_nome,
                                pih_data as data,
                                sum(pih_pin_valor/100) as valor_total
                        from pins_integracao_card_historico
                            left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                        where pin_status = '4' 
                                and pih_codretepp = '2'
                                and pih_data >= '".trim($data_inic)." 00:00:00'
                                and pih_data <= '".trim($data_fim)." 23:59:59'";               
        if($dd_opr_codigo) {
            $sql .= " 
                                and (pih_id=".$dd_opr_codigo.")  ".PHP_EOL;
        }
        $sql .= "
                        group by picc_cpf, picc_nome, pih_data )

                    union all

                        (select 
                                vgcbe_cpf as ug_cpf, 
                                vgcbe_nome_cpf as ug_nome, 
                                vgcbe_data_inclusao as data,
                                sum(vgm_valor * vgm_qtde) as valor_total
                        from tb_venda_games_cpf_boleto_express
                            inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                            inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                        where vg_ultimo_status='5' 
                                and vg_ug_id = 7909
                                and vg_data_concilia >= '".trim($data_inic)." 00:00:00'
                                and vg_data_concilia <= '".trim($data_fim)." 23:59:59'";               
        if($dd_opr_codigo) {
            $sql .= " 
                                and (vgm_opr_codigo=".$dd_opr_codigo.")  ".PHP_EOL;
        }
        $sql .= "
                        group by vgcbe_cpf, vgcbe_nome_cpf, vgcbe_data_inclusao )


                ) tabelaUnion 
                order by data;  
                ";
        //echo $sql;
	$resid = pg_exec($connid, $sql);
	$total_table = pg_num_rows($resid);

} //end if($BtnSearch)
?>
<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
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
                        <strong><?php echo LANG_PUBLISHER_CPF_REPORT; ?></strong>
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
                    <div class="col-md-3">
                        <?php echo LANG_PINS_START_DATE; ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo LANG_PINS_END_DATE; ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo LANG_PINS_OPERATOR; ?>
                    </div>
                </div>
                <div class="row txt-cinza">
                    <form name="form1" method="post" action="" onSubmit="return validade()">
                        <div class="col-md-3">
                            <input  alt="Calendário" name="tf_data_inicial" type="text" class="form-control data" id="tf_data_inicial" value="<?php  echo $tf_data_inicial ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-3">
                            <input alt="Calendário" name="tf_data_final" type="text" class="form-control data" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10">
                        </div>
                        <div class="col-md-4">
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
                                    $vetorPublishersAux[$pgopr['opr_codigo']]['Nome'] = $pgopr['opr_nome'];
                                    $vetorPublishersAux[$pgopr['opr_codigo']]['Pais'] = $pgopr['opr_pais'];
                                    $vetorPublishersAux[$pgopr['opr_codigo']]['Razao'] = $pgopr['opr_razao'];
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
                        <div class="col-md-2">
                            <button type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-right btn-success"><?php echo LANG_INTEGRATION_SEARCH;?></button>
                        </div>
                    </form>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-12 bg-cinza-claro">
<?php  
                    if($total_table > 0) 
                    {
                        $cabecalho = "'Nome','CPF','Moeda','Valor','Forma de Pagamento','Data','Nome do Vendedor','Pais'";
?>
                        <table id="table" class="table bg-branco txt-preto fontsize-p">
                        <thead>
                          <tr class="bg-cinza-claro">
                            <th class="text-center">Nome</th>
                            <th class="text-center">CPF</th>
                            <th class="text-center">Moeda</th>
                            <th class="text-center">Valor</th>
                            <th class="text-center">Forma Pagto</th>
                            <th class="text-center">Data</th>
                            <th class="text-center">Nome Vendedor</th>
                            <th class="text-center">Pais</th>
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

                            $valor_geral += $pgrow['valor_total'];
?>
                            <tr class="trListagem">
                                <td><?php  echo $pgrow['ug_nome']; ?></td>
                                <td><?php  echo $pgrow['ug_cpf']; ?></td>
                                <td align="center"><?php echo REMESSA_MOEDA; ?></td>
                                <td align="right"><?php  echo "R$ ".number_format($pgrow['valor_total'], 2, ',', '.'); ?></td>
                                <td align="center"><?php echo FORMA_PAGAMENTO; ?></td>
                                <td align="center"><?php echo substr($pgrow['data'],0,19); ?></td>
                                <td><?php  echo $vetorPublishersAux[$dd_opr_codigo]['Razao']; ?></td>
                                <td><?php  echo $vetorPublishersAux[$dd_opr_codigo]['Pais']; ?></td>
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
                            <td>&nbsp;</td>
                            <td>TOTAL</td>
                            <td>&nbsp;</td>
                            <td class="text-right"><strong>R$ <?php  echo number_format($valor_geral, 2, ',', '.') ?></strong></td>
                            <td colspan="4">&nbsp;</td>
                        </tr>
                        <tr class="bg-cinza-claro">
                            <td colspan="8" class="fontsize-pp"><?php echo LANG_PINS_SEARCH_MSG.' '.number_format($time, 2, '.', '.').' '.LANG_PINS_SEARCH_MSG_UNIT; ?></td>
                        </tr>
                        <tr class="bg-cinza-claro"> 
                            <td colspan="8" class="fontsize-pp"><?php echo date('Y-m-d H:i:s'); ?></td>
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
    $('#table').table2CSV({header:[<?php echo $cabecalho; ?>],toStr:""});
    
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
