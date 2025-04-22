<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once "/www/includes/bourls.php";
if (b_IsBKOUsuarioComposicaoFifo()) {

//Explicativo
require_once $raiz_do_projeto."class/classDescriptionReport.php";
$descricao = new DescriptionReport('historico_saldo');
$descricao = $descricao->MontaAreaDescricao();
echo str_replace("<script language='JavaScript' src='/js/jquery.js'></script>","",$descricao);
// Nomes para Display
$deposito = "Depósito";
$venda = "Venda";
$gamer = "GAMER";
$lan = "LANHOUSE";

?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        
    });
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table class="table fontsize-pp txt-preto">
    <tr>
        <td valign="top">
            <table class="table txt-preto fontsize-pp">
                <tr>
                    <td valign="top">
                        <table class="table txt-preto fontsize-pp">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Filtros</td>
                                </tr>
                                <tr>
                                        <td align="right">Data Período: </font></td>
                                        <td align="left" colspan="3">
                                                <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php if(isset($tf_v_data_inclusao_ini)) echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
                                                &nbsp;&agrave;&nbsp; 
                                                <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php if(isset($tf_v_data_inclusao_fim)) echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
                                        </td>
                                </tr>
                                <tr>
                                        <td align="right">Tipo de Usu&aacute;rio: </td>
                                        <td>
                                                <select name="tf_tipo_usuario" class="form2">
                                                        <option value="" <?php if(isset($tf_tipo_usuario) && $tf_tipo_usuario == "") echo "selected" ?>>Todos</option>
                                                        <option value="G" <?php if (isset($tf_tipo_usuario) && $tf_tipo_usuario == "G") echo "selected";?>>Gamer</option>
                                                        <option value="L" <?php if (isset($tf_tipo_usuario) && $tf_tipo_usuario == "L") echo "selected";?>>LAN House</option>
                                                </select>
                                        </td>
                                        <td align="right">ID do Usu&aacute;rio: </td>
                                        <td>
                                                <input name="ug_id" type="text" id="ug_id" size="20" maxlength="10" value="<?php if(isset($ug_id)) echo $ug_id;?>"/>
                                        </td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center"><input name="btn_pesquisar" type="submit" id="btn_pesquisar" value="Pesquisar" /></td>
                                </tr>
                            </table>
                    </td>
                </tr>
                <tr align="center">
                <td>
<p align="center">
<?php
if (isset($btn_pesquisar) && $btn_pesquisar=="Pesquisar") {
        
        if(empty($tf_v_data_inclusao_ini) || empty($tf_v_data_inclusao_fim)) {
            die("<br>É obrigatório informar uma data inícial e uma data final!");
        }//end if
            
        $arrayResposta = array();
        
        //-- Esta Query é para levantar o saldo incial e saldo final
        $sql = "
        SELECT 
                tipo,
                id,
                saldo_inicial,
                saldo_final
        FROM (
                ";
        unset($sql_aux);
        if (empty($tf_tipo_usuario) || $tf_tipo_usuario == 'G') {
                $sql .= " (
                    SELECT 
                        '".$gamer."' as tipo,
                        ugsl_ug_id as id, 
                        (select ugsl_ug_perfil_saldo_antes from usuarios_games_saldo_log s where  s.ugsl_ug_id = ugsl.ugsl_ug_id group by s.ugsl_ug_perfil_saldo_antes,s.ugsl_data_inclusao having min(ugsl.ugsl_data_inclusao) = s.ugsl_data_inclusao ORDER BY ugsl_ug_perfil_saldo_antes DESC LIMIT 1) as saldo_inicial,
                        (select ugsl_ug_perfil_saldo from usuarios_games_saldo_log s where s.ugsl_ug_id = ugsl.ugsl_ug_id group by s.ugsl_ug_perfil_saldo,s.ugsl_data_inclusao having max(ugsl.ugsl_data_inclusao) = s.ugsl_data_inclusao ORDER BY s.ugsl_ug_perfil_saldo LIMIT 1) as saldo_final 
                    FROM 
                            usuarios_games_saldo_log ugsl 
                            ";
                if (!empty($ug_id))
                        $sql_aux[] = "ugsl_ug_id = ". $ug_id . " ";
                if(strlen($tf_v_data_inclusao_ini))
                        $sql_aux[] = "ugsl_data_inclusao >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS') \n";
                if(strlen($tf_v_data_inclusao_fim))
                        $sql_aux[] = "ugsl_data_inclusao <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS') \n";
                if (isset($sql_aux) && is_array($sql_aux)) {
                        $sql .= " WHERE " . implode(' AND ', $sql_aux) . " ";
                }
                $sql .= " GROUP BY ugsl_ug_id )";
        } //end if (empty($tf_tipo_usuario) || $tf_tipo_usuario == 'G')
        unset($sql_aux);
        if (empty($tf_tipo_usuario) || $tf_tipo_usuario == 'L') {
                if (empty($tf_tipo_usuario)) {
                        $sql .= "
                    UNION ALL
                    ";
                }//end if (empty($tf_tipo_usuario))
                $sql .= " (
                    SELECT 
                        '".$lan."' as tipo,
                        dugsl_ug_id as id, 
                        (select dugsl_ug_perfil_saldo_antes from dist_usuarios_games_saldo_log s where  s.dugsl_ug_id = dugsl.dugsl_ug_id group by s.dugsl_ug_perfil_saldo_antes,s.dugsl_data_inclusao having min(dugsl.dugsl_data_inclusao) = s.dugsl_data_inclusao) as saldo_inicial,
                        (select dugsl_ug_perfil_saldo from dist_usuarios_games_saldo_log s where  s.dugsl_ug_id = dugsl.dugsl_ug_id group by s.dugsl_ug_perfil_saldo,s.dugsl_data_inclusao having max(dugsl.dugsl_data_inclusao) = s.dugsl_data_inclusao) as saldo_final
                    FROM 
                            dist_usuarios_games_saldo_log dugsl 
                            ";
                if (!empty($ug_id))
                        $sql_aux[] = "dugsl_ug_id = ". $ug_id . " ";
                if(strlen($tf_v_data_inclusao_ini))
                        $sql_aux[] = "dugsl_data_inclusao >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS') \n";
                if(strlen($tf_v_data_inclusao_fim))
                        $sql_aux[] = "dugsl_data_inclusao <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS') \n";
                if (is_array($sql_aux)) {
                        $sql .= " WHERE " . implode(' AND ', $sql_aux) . " ";
                }
                $sql .= " GROUP BY dugsl_ug_id )
                        ";
        } //end if (empty($tf_tipo_usuario) || $tf_tipo_usuario == 'L')
        $sql .= " ) as unificado ;";

        //die(str_replace("\n", "<br>\n", $sql)."<br>");
        $rsResposta = SQLexecuteQuery($sql);
        while ($rsRespostaRow = pg_fetch_array ($rsResposta)) {
               $arrayResposta[$rsRespostaRow['id'].substr($rsRespostaRow['tipo'],0,1)] = array (
                                                                                    'id'            => $rsRespostaRow['id'],
                                                                                    'tipo'          => $rsRespostaRow['tipo'],
                                                                                    'saldo_inicial' => $rsRespostaRow['saldo_inicial'],
                                                                                    'saldo_final'   => $rsRespostaRow['saldo_final'],
                                                                                    );
        } //end while

        //-- Esta Query é para levantar o total de entrada e total de saida
        $sql = "
        SELECT 
                tipo,
                id,
                operacao,
                total
        FROM (
                ";
        unset($sql_aux);
        if (empty($tf_tipo_usuario) || $tf_tipo_usuario == 'G') {
                $sql .= " (
                    SELECT 
                        '".$gamer."' as tipo,
                        ugsl_ug_id as id, 
                        case 
                                when (ugsl_ug_perfil_saldo_antes - ugsl_ug_perfil_saldo) < 0 
                                        then '".$deposito."' 
                                else '".$venda."' end as operacao,
                        sum(ugsl_ug_perfil_saldo_antes - ugsl_ug_perfil_saldo) as total
                    FROM 
                            usuarios_games_saldo_log ugsl 
                            ";
                if (!empty($ug_id))
                        $sql_aux[] = "ugsl_ug_id = ". $ug_id . " ";
                if(strlen($tf_v_data_inclusao_ini))
                        $sql_aux[] = "ugsl_data_inclusao >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS') \n";
                if(strlen($tf_v_data_inclusao_fim))
                        $sql_aux[] = "ugsl_data_inclusao <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS') \n";
                if (is_array($sql_aux)) {
                        $sql .= " WHERE " . implode(' AND ', $sql_aux) . " ";
                }
                $sql .= " GROUP BY operacao,ugsl_ug_id )";
        } //end if (empty($tf_tipo_usuario) || $tf_tipo_usuario == 'G')
        unset($sql_aux);
        if (empty($tf_tipo_usuario) || $tf_tipo_usuario == 'L') {
                if (empty($tf_tipo_usuario)) {
                        $sql .= "
                    UNION ALL
                    ";
                }//end if (empty($tf_tipo_usuario))
                $sql .= " (
                    SELECT 
                        '".$lan."' as tipo,
                        dugsl_ug_id as id, 
                        case 
                                when (dugsl_ug_perfil_saldo_antes - dugsl_ug_perfil_saldo) < 0 
                                        then '".$deposito."' 
                                else '".$venda."' end as operacao,
                        sum(dugsl_ug_perfil_saldo_antes - dugsl_ug_perfil_saldo) as total
                    FROM 
                            dist_usuarios_games_saldo_log dugsl 
                            ";
                if (!empty($ug_id))
                        $sql_aux[] = "dugsl_ug_id = ". $ug_id . " ";
                if(strlen($tf_v_data_inclusao_ini))
                        $sql_aux[] = "dugsl_data_inclusao >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS') \n";
                if(strlen($tf_v_data_inclusao_fim))
                        $sql_aux[] = "dugsl_data_inclusao <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS') \n";
                if (is_array($sql_aux)) {
                        $sql .= " WHERE " . implode(' AND ', $sql_aux) . " ";
                }
                $sql .= " GROUP BY operacao,dugsl_ug_id )
                        ";
        } //end if (empty($tf_tipo_usuario) || $tf_tipo_usuario == 'L')
        $sql .= " ) as unificado ;";

        //die(str_replace("\n", "<br>\n", $sql)."<br>");
        $rsRespostaComplementar = SQLexecuteQuery($sql);
        while ($rsRespostaRow = pg_fetch_array ($rsRespostaComplementar)) {
                if($rsRespostaRow['operacao'] == $deposito) {
                    $arrayResposta[$rsRespostaRow['id'].substr($rsRespostaRow['tipo'],0,1)]['deposito'] = $rsRespostaRow['total']*-1;
                } //end if($rsRespostaRow['operacao'] == $deposito) 
                elseif($rsRespostaRow['operacao'] == $venda) {
                    $arrayResposta[$rsRespostaRow['id'].substr($rsRespostaRow['tipo'],0,1)]['venda'] = $rsRespostaRow['total'];
                } //end elseif($rsRespostaRow['operacao'] == $venda)  

        } //end while
        
        //Ordenando o Vetor
        ksort($arrayResposta);
        
        //echo "<pre>".print_r($arrayResposta,true)."</pre>";
        
}//end if ($btn_pesquisar=="Pesquisar")
?>
<table width="100%" border="0" align="center" class="texto">
<?php
if(isset($rsResposta) && $rsResposta && pg_num_rows($rsResposta) != 0) {
?>
    <tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4"><?php echo "Encontrado".((pg_num_rows($rsResposta)>0)?"s":"")." ".pg_num_rows($rsResposta)." registro".((pg_num_rows($rsResposta)>0)?"s":"")."";?></td>
        <td align="center">&nbsp;</td>
    </tr>
    <tr>
        <td bgcolor="#DDDDDD" align="center">&nbsp;</td>
        <td bgcolor="#DDDDDD" align="center">ID do Usu&aacute;rio</td>
        <td bgcolor="#DDDDDD" align="center">Tipo</td>
        <td bgcolor="#DDDDDD" align="center">Saldo Inicial R$</td>
        <td bgcolor="#DDDDDD" align="center">Entrada R$</td>
        <td bgcolor="#DDDDDD" align="center">Saída R$</td>
        <td bgcolor="#DDDDDD" align="center">Saldo Final R$</td>
    </tr>
<?php
} //end if((pg_num_rows($rsResposta) != 0) && ($rsResposta))
$backcolor1 = "#ccffff";
$backcolor2 = "#ffffff";
$bck = $backcolor1;

//Dados de totais
$total_saldo_inicial = 0;
$total_deposito = 0;
$total_venda = 0;
$total_saldo_final = 0;

if(!empty($arrayResposta)){
    foreach ($arrayResposta as $key => $value) {
?>
        <tr<?php echo " bgcolor='".$bck."'" ?>>
            <td align="center">&nbsp;</td>
            <td align="left"><nobr><a href="/<?php echo ($value['tipo']==$gamer?"gamer":"pdv");?>/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $value['id'];?>"><?php echo $value['id'];?></a></nobr></td>
        <td align="center"><nobr><?php echo $value['tipo'];?></nobr></td>
            <td align="right"><nobr><?php if(isset($value['saldo_inicial'])) echo number_format($value['saldo_inicial'], 2, ',', '.');?></nobr></td>
            <td align="right"><nobr><?php if(isset($value['deposito'])) echo number_format($value['deposito'], 2, ',', '.');?></nobr></td>
            <td align="right"><nobr><?php if(isset($value['venda'])) echo number_format($value['venda'], 2, ',', '.');?></nobr></td>
            <td align="right"><nobr><?php if(isset($value['saldo_final'])) echo number_format($value['saldo_final'], 2, ',', '.');?></nobr></td>
        </tr>
<?php
        if ($bck == $backcolor1)
            $bck = $backcolor2;
        else $bck = $backcolor1;

            //Totalizando
        if(isset($value['saldo_inicial']))
            $total_saldo_inicial += $value['saldo_inicial'];
        if(isset($value['deposito']))
            $total_deposito += $value['deposito'];
        if(isset($value['venda']))
            $total_venda += $value['venda'];
        if(isset($value['saldo_final']))
            $total_saldo_final += $value['saldo_final'];

    } //end while ($pgResposta = pg_fetch_array ($rsResposta))
}
if(isset($rsResposta) && pg_num_rows($rsResposta) != 0) {
?>    <tr>
        <td bgcolor="#DDDDDD" align="center">&nbsp;</td>
        <td bgcolor="#DDDDDD" align="right" colspan="2"><b>Total</b></td>
        <td bgcolor="#DDDDDD" align="right"><b><?php echo number_format($total_saldo_inicial, 2, ',', '.');?></b></td>
        <td bgcolor="#DDDDDD" align="right"><b><?php echo number_format($total_deposito, 2, ',', '.');?></b></td>
        <td bgcolor="#DDDDDD" align="right"><b><?php echo number_format($total_venda, 2, ',', '.');?></b></td>
        <td bgcolor="#DDDDDD" align="right"><b><?php echo number_format($total_saldo_final, 2, ',', '.');?></b></td>
    </tr>
<?php
} //end if((pg_num_rows($rsResposta) != 0) && ($rsResposta))
?>    
</table>
</p>
               </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?php
} // end if (b_IsBKOUsuarioComposicaoFifo())
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>