<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once $raiz_do_projeto."includes/rs_ws/inc_utils.php";
require_once $raiz_do_projeto."class/classDescriptionReport.php";
require_once "/www/includes/bourls.php";
//$descricao = new DescriptionReport('historico_usuario');
//echo $descricao->MontaAreaDescricao();

	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 0;
	if($Pesquisar) $total_table = 0;
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 50; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;
	$registros	  = $max;
	
	if (!empty($vg_id)) {
		$varse1 .= "&vg_id=$vg_id";
	}
	if (!empty($email)) {
		$varse1 .= "&email=$email";
	}
	if (!empty($ug_id)||$ug_id=='0') {
		$varse1 .= "&ug_id=$ug_id";
	}
	if(!empty($tf_v_data_inclusao_ini)) {
		$varse1 .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini";
	}
	if(!empty($tf_v_data_inclusao_fim)) {
		$varse1 .= "&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	}

?>
<link rel="stylesheet" type="text/css" href="/css/gamer/cssClassLista.css" />
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        
    });
        
function VerificaMotivo() {
    return true;
    var teste = true;
    if((document.form1.tf_v_data_inclusao_ini.value=="") && (document.form1.tf_v_data_inclusao_fim.value=="") && (document.form1.ug_id.value=="") && (document.form1.vg_id.value=="")) { 
        teste = false; 
    } 
    if(teste) return true;
    else {
        alert('Você deve informar ao menos um parametro de filtro!');
        return false;
    }
}//end function VerificaMotivo()
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="lstDado"></div>
<div class="col-md-12">
    <form id="form1" name="form1" method="post" onsubmit="return VerificaMotivo();">
        <table class="table">
            <tr>
                <td valign="top">
                    <table  class="table txt-preto fontsize-p">
                        <tr>
                            <td valign="top">
                                <table class="table">
                                    <tr>
                                        <td colspan="4" bgcolor="#DDDDDD">Dados do pagamento</td>
                                    </tr>
                                    <tr>
                                        <td align="right">Data da Ação: </font></td>
                                        <td align="left">
                                            <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="11" maxlength="10">&nbsp;&agrave;&nbsp; 
                                            <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="11" maxlength="10">
                                        </td>
                                        <td align="right">ID da LAN: </td>
                                        <td>
                                            <input name="ug_id" type="text" id="ug_id" size="20" value="<?php echo $ug_id;?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                       <td align="right">E-Mail: </td>
                                        <td>
                                            <input name="email" type="text" id="email" size="30" value="<?php echo $email;?>"/>
                                        </td>
                                        <td align="right">ID da Venda: </td>
                                        <td>
                                            <input name="vg_id" type="text" id="vg_id" size="20" value="<?php echo $vg_id;?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" align="center"><input name="btn_pesquisar" type="submit" class="btn btn-info btn-sm" id="btn_pesquisar" value="Pesquisar" /></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr align="center">
                            <td>
<?php
        
        if ($btn_pesquisar=="Pesquisar") {

        //echo "dd_exclui_testes (".((isset($dd_exclui_testes))?"ON":"off")."): <pre>".print_r($dd_exclui_testes, true)."</pre><br>";

            $sql  = "SELECT * ";
            $sql .= "FROM tb_dist_venda_games_produto_email vgpe 
                            INNER JOIN tb_dist_venda_games_modelo_pins vgmp  ON (vgpe.vgpe_pin_codinterno = vgmp.vgmp_pin_codinterno)
                            INNER JOIN tb_dist_venda_games_modelo vgm  ON (vgmp.vgmp_vgm_id = vgm.vgm_id) \n";
            $sql .= "WHERE 1=1 \n";
            if (!empty($vg_id)) {
                 $sql .= " AND vgm.vgm_vg_id in (". $vg_id . ") \n";
            }
            if (!empty($ug_id)||$ug_id=='0') {
                 $sql .= " AND vgpe_ug_id in (". $ug_id . ") \n";
            }
            if (!empty($email)) {
                 $sql .= " AND vgpe_email like '%". $email . "%' \n";
            }
            if(strlen($tf_v_data_inclusao_ini) && strlen($tf_v_data_inclusao_fim)) {
                $sql .= " AND (vgpe_data between '".formata_data_rc($tf_v_data_inclusao_ini,1)." 00:00:00' AND '".formata_data_rc($tf_v_data_inclusao_fim,1)." 23:59:59') \n";
            }

            $res_tmp = SQLexecuteQuery($sql);
            if ($res_tmp) {
                $total_table = pg_num_rows($res_tmp);
            }

            $max_reg = (($inicial + $max)>$total_table)?$total_table:$max;

            $sql .= " ORDER BY vgpe_data DESC\n ";
            $sql .= " limit $max offset $inicial ";

            //if(b_IsUsuarioWagner()) { 
            //echo "<br><br>(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";
            //}

            $rsResposta = SQLexecuteQuery($sql);
        ?>
        <table class="table table-bordered fontsize-pp">
        <?php
            if((pg_num_rows($rsResposta) != 0) && ($rsResposta)) {
        ?>
            <tr>
                <td align="center">&nbsp;</td>
                <td align="left" colspan="2"><b>Pesquisa <b><?php 
                    echo " (".$total_table." registro"; 
                    if($total_table>1) echo "s"; 
                    echo ")"?></b>
                </td>
                <td align="center">&nbsp;</td>
            </tr>
            <tr>
                <td bgcolor="#DDDDDD" align="center">Data</td>
                <td bgcolor="#DDDDDD" align="center">Venda</td>
                <td bgcolor="#DDDDDD" align="center">ID LAN House</td>
                <td bgcolor="#DDDDDD" align="center">Email</td>
            </tr>
        <?php

            $backcolor1 = "#ddffff";
            $backcolor2 = "#ffffff";
            $bck = $backcolor1;
                while ($pgResposta = pg_fetch_array ($rsResposta)) {

            ?>
                <tr<?php echo " bgcolor='".$bck."'" ?>>
                     <td align="center"><nobr><?php echo $pgResposta['vgpe_data'];?></nobr></td>
                     <td align="center"><nobr><a href="/pdv/vendas/com_venda_detalhe.php?venda_id=<?php echo $pgResposta['vgm_vg_id'];?>" target="_blank"><?php echo $pgResposta['vgm_vg_id'];?></a></nobr></td>
                     <td align="center"><nobr><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $pgResposta['vgpe_ug_id'];?>" target="_blank"><?php echo $pgResposta['vgpe_ug_id'];?></a></nobr></td>
                     <td align="center"><nobr><?php echo $pgResposta['vgpe_email'];?></nobr></td>
                </tr>
            <?php
                    if ($bck == $backcolor1)
                        $bck = $backcolor2;
                    else $bck = $backcolor1;
                } //end while ($pgResposta = pg_fetch_array ($rsResposta)) 

                ?>
                <td align="center"><nobr><?php

                $varse1 .= "&btn_pesquisar=Pesquisar";

                paginacao_query($inicial, $total_table, $max, 50, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varse1);

                ?></nobr></td>
                <?php
            } else {
                ?>
                    <tr<?php echo " bgcolor='".$bck."'" ?>>
                        <td align="center" colspan="7">&nbsp;<font color="red">Sem registros encontrados</font></td>
                    </tr>
                <?php
            }
?>
        </table>
<?php
        }//end if ($btn_pesquisar=="Pesquisar")
?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>