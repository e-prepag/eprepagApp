<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."class/gamer/classIntegracao.php";

set_time_limit(300);
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

if(empty($s_local_ip_address)) {
    $s_local_ip_address = " ";
}//end if empty

$a_opr = array();	
$sql = "select opr_nome, opr_codigo from operadoras where opr_status='1' OR opr_status='2' order by opr_nome"; 
$resopr = SQLexecuteQuery($sql);

if($resopr && pg_num_rows($resopr) > 0) {
        while ($pgopr = pg_fetch_array ($resopr)) { 
                $a_opr[$pgopr['opr_codigo']] = $pgopr['opr_nome'];
        } 
}
?>
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
            <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
            <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
        </ol>
    </div>
    <form name="form1" method="post" action="com_lista_integracao.php">
        <table class="table txt-preto fontsize-p">
            <tr bgcolor="#FFFFFF"> 
                  <td bgcolor="#ECE9D8" class="texto">Parceiros</font></td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td>
                    <select name="tf_store_id" class="form2">
                            <option value="" <?php if($tf_store_id=="") echo "selected" ?>>Todos os parceiros</option>
                    <?php
                            foreach($partner_list as $key => $val) {
                    ?>
                            <option value="<?php echo $val['partner_id'] ?>" <?php if ($tf_store_id == $val['partner_id']) echo "selected";?>><?php echo $key." (ID: ".$val['partner_id'].") "; ?></option>
                    <?php
                            }	
                    ?>
                    </select>
                </td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td align="right" colspan="4"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td> 
            </tr>
        </table>
    </form>
    <br>
    </div></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <table  class="fontsize-pp txt-preto bg-branco" style="margin-left: auto; margin-right: auto;">
                    <tr> 
                        <td width="896" height="100%" valign="top" align="center"> 
                            <table class="table table-bordered">
                                <tr class="texto" style="font-weight:bold" align="center"> 
                                        <td colspan="7" align="left">&nbsp;Total de cadastros: <?php echo count($partner_list)?><br>
                                            <div id="div_total_ativos">&nbsp;</div>
                                            <div id="div_lista_ids_ativos">&nbsp;Ativos: <?php echo get_list_Partner_IDs(true, false); ?></div>
                                            <div id="div_lista_ids_ativos">&nbsp;Inativos: <?php echo get_list_Partner_IDs(false, false); ?></div>
                                            <div id="div_link_monitor">&nbsp;<a href="#monitor_atividade">Monitor de atividade da integração</a></div>
                                        </td>
                                </tr>
                                <tr class="texto" style="font-weight:bold" align="center"> 
                                        <td>Nome</td><td>store_id</td><td>opr</td><td>ativo</td> <td>URLs/IP</td> <td>produto</td> <td>currency</td>
                                </tr>
                                    <?php
                                    $n_ativos = 0;
                                    foreach($partner_list as $key => $val) {
                                            if(($tf_store_id!="") && ($tf_store_id!=$val['partner_id'])) {
                                                    continue;
                                            }
                                            // Encontra a operadora do produto cadastrado
                                            $sql_produtos = "select ogp_opr_codigo from tb_operadora_games_produto ogp where ogp.ogp_id in (".$val['partner_produto_id'].")" ;
                                            $resprods = SQLexecuteQuery($sql_produtos);
                                            if($resprods && pg_num_rows($resprods) > 0) {
                                                    $pgoprods = pg_fetch_array ($resprods);
                                                    $ogp_opr_codigo = $pgoprods['ogp_opr_codigo'];
                                            }

                                            $b_ativo = b_Partner_is_Ativo($val['partner_id']);
                                            $s_bgcolor = (($b_ativo)?"#CCFF99":"white");
                                            if($b_ativo) {
                                                    $n_ativos ++;
                                            }
                                            echo "<tr class='texto' bgcolor='$s_bgcolor'>";
                                            echo "<td valign='top'>".$key."&nbsp;(".(($b_ativo)?"<font color='blue'>ATIVO</font>":"<font color='red'>inativo</font>").")";
                                                    echo "<br>".(($val['partner_img_logo']!='')?"<img src='".$val['partner_img_logo']."' title='".$val['partner_img_logo']."'>":"");
                                                    echo "</td>\n";
            //					echo "<td valign='top'>".$val['partner_name']."</td>\n";
                                            echo "<td align='center' valign='top'>".$val['partner_id']."</td>\n";
                                            echo "<td align='center' valign='top'>".$a_opr[$val['partner_opr_codigo']]."<br>(ID:".$val['partner_opr_codigo'].")". (($val['partner_opr_codigo']!=$ogp_opr_codigo && ($val['amount_free']==0)) ? "<br><nobr><font style='color:red; background-color:#FFFFCC'>PROD-OPR<br>don't match</font></nobr>":"") . "</td>\n";
                                            echo "<td align='center'".(($val['partner_active']!='1')?" bgcolor='#FFCC33'":"")." valign='top'>".(($val['partner_active']==1)?"SIM":"não")."</td>\n";
                                            $server_ip = gethostbyname($val['partner_ip']);
                                            $s_style_server_ip = (($server_ip!=$val['partner_ip'])?"background-color:#FFFF66; color:red":"color:black");
                                            $s_title_server_ip = (($server_ip!=$val['partner_ip'])?"IP do Servidor não corresponde a URL do servidor":"IP do Servidor Verificado");
                                            echo "<td valign='top'>";
                                            echo "<table cellpadding='0' cellspacing='1' border='0' bordercolor='#cccccc' style='border-collapse:collapse;' width='100%'>\n";
                                            // partner_url
                                            $style = (strpos(strtoupper($val['partner_url']), strtoupper("https://www.e-prepag.com.br/prepag2/commerce/"))!==false )?" style='color:grey'":" style='color:blue'";
                                            echo "<tr class='texto' align='left'><td style='color:black'>partner_url</td><td>&nbsp;</td><td".$style." width='100%'><a href='".$val['partner_url']."' target='_blank'>".$val['partner_url']."</a></td></tr>\n";
                                           // partner IP
                                            $style = ((!(strpos(strtoupper($val['partner_ip']), strtoupper($s_local_ip_address))))?" style='color:grey'":" style='color:blue'");
                                            echo "<tr class='texto' align='left' style='$s_style_server_ip' title='$s_title_server_ip'><td>partner_ip</td><td>&nbsp;</td><td".$style.">".$val['partner_ip'];
                                            // IPs em bloco
                                            if(strlen($val['partner_ip_block_start'])>0 & strlen($val['partner_ip_block_end'])>0) {
                                                    echo "<br><font style='background-color:#CCFFCC'>&nbsp;&nbsp;<nobr>(bloco ".$val['partner_ip_block']." - [".$val['partner_ip_block_start']." - ".$val['partner_ip_block_end']."])</nobr></font>"; 
                                            } 
                                            // IPs em lista
                                            elseif (strlen($val['partner_ip_defined_list'])>0) {
                                                    echo "<br><font style='background-color:#CCCC00'>&nbsp;&nbsp;(lista ".str_replace(",", ", ", $val['partner_ip_defined_list']).")</font>";
                                            }
                                            // IPs em lista de blocos
                                            if(isset($val['partner_ip_intervals']) && is_array($val['partner_ip_intervals'])  ) {
                                                    foreach($val['partner_ip_intervals'] as $key1 => $val1) {
                                                            echo "<br><font style='background-color:#CCFFCC'>&nbsp;&nbsp;<nobr>(bloco ".$val1['partner_ip_block']." - [".$val1['partner_ip_block_start']." - ".$val1['partner_ip_block_end']."])</nobr></font>"; 
                                                    }
                                            }
                                            echo "</td></tr>\n";
                                            // notify_url
                                            $style = (strpos(strtoupper($val['notify_url']), strtoupper("https://www.e-prepag.com.br/prepag2/commerce/"))!==false )?" style='color:grey'":" style='color:blue'";
                                            echo "<tr class='texto' align='left'><td style='color:black'>notify_url</td><td>&nbsp;</td><td".$style.">" . $val['notify_url'] . "</td></tr>\n";
                                            // return_url
                                            $style = (strpos(strtoupper($val['return_url']), strtoupper("http://www.e-prepag.com.br/prepag2/commerce/"))!==false )?" style='color:grey'":" style='color:blue'";
                                            echo "<tr class='texto' align='left'><td style='color:black'>return_url</td><td>&nbsp;</td><td".$style.">".$val['return_url']."</td></tr>\n";
                                            // sonda_url
                                            $style = ( strlen($val['sonda_url'])==0)?" style='color:grey'":" style='color:blue'";
                                            echo "<tr class='texto' align='left'><td style='color:black'>sonda_url</td><td>&nbsp;</td><td".$style.">".((strlen($val['sonda_url'])>0) ? $val['sonda_url']:"--")."</td></tr>\n";
                                            // partner_img_logo
                                            $style = ($val['partner_img_logo']=='')?" style='color:grey'":" style='color:blue'";
                                            $partner_img_logo = substr( $val['partner_img_logo'], strrpos($val['partner_img_logo'], "/")+1);
                                            echo "<tr class='texto' align='left'><td style='color:black'>partner_img_logo</td><td>&nbsp;</td><td ".$style.">". (($val['partner_img_logo']!='')?"<a href='".$val['partner_img_logo']."' target='_blank'>":"") . $partner_img_logo .(($val['partner_img_logo']!='')?'</a>':'')."</td></tr>\n";
                                            echo "<tr class='texto' align='left'><td style='color:black'>partner_img_prods_logo</td><td>&nbsp;</td><td ".$style.">"; if($val['partner_img_prods_logo']) {
                                                    foreach($val['partner_img_prods_logo'] as $key => $val_logo) {
                                                            $prod_img_logo = substr( $val_logo, strrpos($val_logo, "/")+1);
                                                            echo "<a href='".$val_logo."' target='_blank'>".$prod_img_logo."</a>, ";
                                                    }
                                            } else {
                                                    echo "--";
                                            }
                                            echo "</td></tr>\n";
                                            echo "<tr class='texto' align='left'><td style='color:black'>partner_email</td><td>&nbsp;</td><td>".(($val['partner_email'])?$val['partner_email']:"<font color='red'>sem email cadastrado  === </font>")."</td></tr>\n";
                                            echo "<tr class='texto' align='left'><td style='color:black'>partner_testing_email</td><td>&nbsp;</td><td>".(($val['partner_testing_email'])?"<font color='blue'>envia email DEBUG</font>":"<font color='red'>sem envio de email DEBUG</font>")."</td></tr>\n";
                                            echo "<tr class='texto' align='left'><td style='color:black'>partner_do_notify</td><td>&nbsp;</td><td>".(($val['partner_do_notify']==1)?"<font color='darkgreen'>COM NOTIFICAÇÃO</font>":"sem notificação")."</td>";
                                            echo "<tr class='texto' align='left'><td style='color:black'>partner_do_renotify_automatico</td><td>&nbsp;</td><td>".(($val['partner_do_renotify_automatico']==1)?"<font color='darkgreen'>COM RE-notificação Automatica</font>":"sem RE-notificação Automatica")."</td>";
                                            echo "<tr class='texto' align='left'><td style='color:black'>bypass_ip_check</td><td>&nbsp;</td><td>".(($val['partner_bypass_ip_check']==1)?"<font style='color:red; background-color:#FFFF66'>SEM CHECK IP</font>":"<font color='darkgreen'>Check IP</font>")."</td>";
                                            echo "<tr class='texto' align='left'><td style='color:black'>amount_free</td><td>&nbsp;</td><td>".(($val['amount_free']==1)?"<font style='color:red; background-color:#FFFF66'>VALORES DE MODELOS LIVRES</font>":"<font color='darkgreen'>valores de modelos fixos</font>")."</td>";
                                            echo "<tr class='texto' align='left'><td style='color:black'>lista_formas_pagto_bloqueadas</td><td>&nbsp;</td><td>".(($val['lista_formas_pagto_bloqueadas'])?"<font color='red'>".$val['lista_formas_pagto_bloqueadas']."</font>":"sem bloqueios de formas de pagamentos")."</td>";
                                            echo "<tr class='texto' align='left'><td style='color:black'>forma_pagto_direta</td><td>&nbsp;</td><td>".(($val['forma_pagto_direta'])?"<font color='red'>".$val['forma_pagto_direta']." - '".$GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$val['forma_pagto_direta']]."'</font>":"sem formas de pagamentos direta")."</td>";
                                            if($val['forma_pagto_direta']) {
                                                    if($val['forma_pagto_direta']==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIN_EPREPAG']) {
                                                            echo "<tr class='texto' align='left'><td style='color:black'>forma_pagto_direta_gocash</td><td>&nbsp;</td><td>".(($val['forma_pagto_direta_gocash'])?"<font color='red'>Usa Apenas PINS GoCash</font>":"--")."</td>";
                                                    }
                                                    echo "<tr class='texto' align='left'><td style='color:black'>Show in Frame?</td><td>&nbsp;</td><td>". (($val['forma_pagto_direta_in_frame']) ? "<font color='red'>SIM (sem a loja EPP, apenas forma de pagamento)</font>":"não (mostra Loja EPP normal)")."</td>";
                                                    echo "<tr class='texto' align='left'><td style='color:black'><b>&nbsp;</b></td><td>&nbsp;</td><td>
                                                    FPD: ".((b_use_forma_pagto_direta($val['partner_id']))?"SIM":"não")."
                                                    PINsEPP: ".((b_is_forma_pagto_direta_epp_cash($val['partner_id']))?"SIM":"não")."
                                                    GoCash: ".((b_is_forma_pagto_direta_gocash($val['partner_id']))?"SIM":"não")."
                                                    </td>";
                                            }
                                            echo "<tr class='texto' align='left'><td style='color:black'>partner_need_cpf</td><td>&nbsp;</td><td>".(($val['partner_need_cpf']==1)?"<font color='red'>OBRIGATÓRIO</font>":(($val['partner_need_cpf']==2)?"OPCIONAL":"Não Exige CPF"))."</td>";
                                            echo "</tr>\n";
                                            echo "</table>\n";
                                            echo "</td>\n";
                                            $integracao_mod = get_Integracao_modelo($val['partner_id'], null, $integracao_iativo, $integracao_prod_nome);
                                            echo "<td align='center' valign='top'>";
                                            if($val['amount_free']==0) {
                                                    $sql_produtos = "select * from tb_operadora_games_produto_modelo ogpm  
                                                                                            where 1=1 
                                                                                                    and (0=1 or ogpm.ogpm_ogp_id in (".$val['partner_produto_id'].")) 
                                                                                            order by ogpm.ogpm_ogp_id, ogpm.ogpm_valor";
                                                    $resprods = SQLexecuteQuery($sql_produtos);
                                                    echo "produto: '<font color='blue'>$integracao_prod_nome</font>' (ID:".$val['partner_produto_id'].")<br>";
                                                    echo "<table cellpadding='0' cellspacing='1' border='1' bordercolor='#cccccc' style='border-collapse:collapse;' width='100%'>\n";
                                                    if($resprods && pg_num_rows($resprods) > 0) {
                                                            $ogpm_ogp_id = 0;
                                                            while ($pgoprods = pg_fetch_array ($resprods)) { 
                                                                    if($ogpm_ogp_id != $pgoprods['ogpm_ogp_id']) {
                                                                            echo "<tr class='texto' align='center' style='color:#CC0000'><td>ogp_id</td><td>ogpm_id</td><td>nome</td><td>valor</td><td>ativo</td><td>integracao</td></tr>";
                                                                            $ogpm_ogp_id = $pgoprods['ogpm_ogp_id'];
                                                                    }
                                                                    echo "<tr class='texto' align='left'><td style='color:black' align='center'>".$pgoprods['ogpm_ogp_id']."</td><td style='color:black' align='center'>".$pgoprods['ogpm_id']."</td><td align='center'><nobr>".$pgoprods['ogpm_nome']."</nobr></td><td align='right'>".number_format($pgoprods['ogpm_valor'], 2, '.', ',')."</td><td align='center'><nobr>".(($pgoprods['ogpm_ativo'])?"<font style='color:blue'>SIM</font>":"não")."</nobr></td><td align='center'><nobr>".(($pgoprods['ogpm_integracao'])?"<font style='color:blue'>SIM</font>":"não")."</nobr></td></tr>";
                                                            } 
                                                    }
                                                    echo "</table>\n";
                                            } else {
                                                    echo "produto: '<font color='blue'>$integracao_prod_nome</font>' (ID:".$val['partner_produto_id'].")<br>";
                                                    echo "Operadora com valores free (sem modelos cadastrados)";
                                            }
                                            echo "</td>";
                                            echo "<td align='center' valign='top'>".$val['partner_currency_code']."</td></tr>\n";
                                    }
                                    ?>
                            </table>
                            <script language="JavaScript" type="text/JavaScript">
                                    document.getElementById('div_total_ativos').innerHTML = "<?php echo '&nbsp;Total de cadastros ativos: '.$n_ativos?>";
                            </script>
                            <?php
                            $sql_monitor = "select ip_store_id, max(ip_data_inclusao) as last_data, 
                                    EXTRACT ('epoch' FROM (CURRENT_TIMESTAMP - max(ip_data_inclusao))) as delay_request,
                                    count(*) as n_pedidos,
                                    (
                                            select max(ipnh_data_inclusao) as last_data_notify
                                                    from tb_integracao_pedido_notificacao_historico ipnh
                                            where ipnh_store_id = ip_store_id
                                            group by ipnh_store_id

                                    ) as last_data_notify,
                                    EXTRACT ('epoch' FROM (CURRENT_TIMESTAMP - (
                                            select max(ipnh_data_inclusao) as last_data_notify
                                                    from tb_integracao_pedido_notificacao_historico ipnh
                                            where ipnh_store_id = ip_store_id
                                            group by ipnh_store_id

                                    ))) as delay_notify,
                                    coalesce((
                                            select count(*) as n
                                                    from tb_integracao_pedido_notificacao_historico ipnh
                                            where ipnh_store_id = ip_store_id
                                            group by ipnh_store_id

                                    ), 0) as n_notify
                            from tb_integracao_pedido ip
                            group by ip_store_id
                            order by ip_store_id";
                            echo "<a name='monitor_atividade'></a>\n";
                            $rs = SQLexecuteQuery($sql_monitor);

                            $n_rows = pg_num_rows($rs);
                            if($n_rows == 0) {
                                    echo "<font color='red'>Nenhum registro de integração encontrado para monitor.</font>\n";
                            } else {
                                    echo "<p class='texto'>Encontrados $n_rows registros no monitor</p>";
                                    echo "<table cellpadding='2' cellspacing='0' border='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
                                    echo "<tr class='texto'>";
                                            echo "<td>ip_store_id</td>";
                                            echo "<td>nome</td>";
                                            echo "<td>ativo</td>";
                                            echo "<td>delay_pedidos</td>";
                                            echo "<td>n_pedidos</td>";
                                            echo "<td>delay_notify</td>";
                                            echo "<td>n_notify</td>";
                                            echo "<td>% notify</td>";
                                    echo "</tr>\n";

                                    $n_pedidos_total = 0;
                                    $n_notify_total = 0;
                                    while($rs_row = pg_fetch_array($rs)){
                                            $b_is_ativo = b_Partner_is_Ativo($rs_row['ip_store_id']);
                                            echo "<tr class='texto'".(($b_is_ativo)?" style='background-color:#ccff99'":"").">";
                                                    echo "<td>".$rs_row['ip_store_id']."</td>";
                                                    echo "<td align='right'>" . get_Integracao_nome_parceiro($rs_row['ip_store_id']) . "</td>";
                                                    echo "<td align='center'>" . (($b_is_ativo)?"<font color='blue'>SIM</font>":"<font color='gray'>não</font>") ."</td>";
                                                    echo "<td align='right' title='".substr($rs_row['last_data'], 0, 19)."'>".convert_secs_to_string($rs_row['delay_request'])."</td>";
                                                    echo "<td align='right'>".$rs_row['n_pedidos']."</td>";
                                                    echo "<td align='right' title='".substr($rs_row['last_data_notify'], 0, 19)."'>".convert_secs_to_string($rs_row['delay_notify'])."</td>";
                                                    echo "<td align='right'>".$rs_row['n_notify']."</td>";
                                                    echo "<td align='right'><font color=" . (($b_is_ativo)?"'blue'":"'gray'") .">".number_format((100*$rs_row['n_notify']/$rs_row['n_pedidos']), 2, '.', '.')."% </font></td>";
                                            echo "</tr>\n";
                                            $n_pedidos_total += $rs_row['n_pedidos'];
                                            $n_notify_total += $rs_row['n_notify'];

                                    }
                                    echo "<tr class='texto' style='background-color:#ffff99'>";
                                            echo "<td colspan='4' align='right'>Total&nbsp;</td>";
                                            echo "<td align='right'>".$n_pedidos_total."</td>";
                                            echo "<td align='right'>&nbsp;</td>";
                                            echo "<td align='right'>".$n_notify_total."</td>";
                                            echo "<td align='right'>".number_format((100*$n_notify_total/$n_pedidos_total), 2, '.', '.')."% </td>";
                                    echo "</tr>\n";
                                    echo "</table>\n";
                            }
                            ?>
                                <br>

                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div><div>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>
<?php 
function convert_secs_to_string($n) {
        $sout = "";
        $ndays = 0;
        $nhours = 0;
        $nmins = 0;
        $nsecs = 0;

        $ndays = intval($n/(60*60*24));
        $nhours = str_pad(intval(($n-$ndays*60*60*24)/(60*60)), 2, "0", STR_PAD_LEFT);
        $nmins = str_pad(intval(($n-$ndays*60*60*24-$nhours*60*60)/(60)), 2, "0", STR_PAD_LEFT);
        $nsecs = str_pad(intval(($n-$ndays*60*60*24-$nhours*60*60-$nmins*60)), 2, "0", STR_PAD_LEFT);


        $sout .= "<font size='1'>";
        $sout .= (($ndays>0)?$ndays."<font color='#FF0000'>d</font>":"");
        $sout .= (($ndays>0 || $nhours>0)?$nhours."<font color='#FF0000'>h</font>":"");
        $sout .= (($ndays>0 || $nhours>0 || $nmins>0)?$nmins."<font color='#FF0000'>m</font>":"");
        $sout .= (($ndays>0 || $nhours>0 || $nmins>0 || $nsecs>0)?$nsecs."<font color='#FF0000'>s</font>":"");
        $sout .= "</font>";

        return $sout;
}//end function
?>
