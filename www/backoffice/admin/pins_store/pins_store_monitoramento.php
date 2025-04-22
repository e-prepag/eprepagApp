<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

set_time_limit(3000);
$operacao_array = VetorDistribuidoras();
$distributor_codigo 	= isset($_POST['pin_operacao'])      ? $_POST['pin_operacao']	: null;
$lote           = isset($_POST['pin_lote'])          ? $_POST['pin_lote']       : null;
$valor          = isset($_POST['pin_valor'])         ? $_POST['pin_valor']      : null;
$time_start_stats = getmicrotime();

$msg = "";
$msg_pin = "";
?>
<script language="JavaScript">
<!--
	function reload() {
		document.form1.action = "pins_store_monitoramento.php";
		document.form1.submit();
	}
	function verifica()
	{
		if ((event.keyCode<47)||(event.keyCode>58)){
			  alert("Somente numeros sao permitidos");
			  event.returnValue = false;
		}
	}
	function timedRefresh(timeoutPeriod) {
		//setTimeout("location.reload(true);",timeoutPeriod);
	}
-->
        
$(function(){
    timedRefresh(100000);
})
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li class="active">Monitoramento de PINs E-PREPAG</li>
    </ol>
</div>  
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_store_menu.php";
?>
<form name="form1" method="post" action="pins_store_monitoramento.php" onsubmit="javascript:return reload();">
	<table class="table txt-preto fontsize-pp bg-branco">
	<?php
	if($msg_pin) echo "<tr><td>".$msg_pin."</td></tr>";
	?>
    <tr valign="top" align="center">
      <td>
			<table border="0" cellspacing="01" align="center" width="100%">
                <tr class="bg-azul-claro">
    	          <td class="texto" align="center"><b>PINs Utilizados e N&Atilde;O Contidos em Arquivos:</b>&nbsp;</td>
    	        </tr>
		<?php
                        $i=0;
                        $sql_total = "SELECT count(psdh_pin_status) as total, psdh_pin_codinterno 
                                     from pins_store_db_historico 
                                     where psdh_pin_codinterno IN (SELECT psah_pin_id 
                                                                     from pins_store_apl_historico 
                                                                     where pin_status = '".intval($PINS_STORE_STATUS_VALUES['U'])."' 
                                                                           AND psah_acao = '".intval($PINS_STORE_MSG_LOG_STATUS['SUCESSO_UTILIZACAO'])."' 
                                                                     order by psah_data DESC) 
                                           AND psdh_pin_status = '".intval($PINS_STORE_STATUS_VALUES['A'])."' 
                                     group by psdh_pin_codinterno 
                                     having count(psdh_pin_status) < 2";
                        //echo $sql_total;
                        $rs_total = SQLexecuteQuery($sql_total);
                        $aux_count = pg_num_rows($rs_total);
                        while($rs_total_row = pg_fetch_array($rs_total)) {
                            $bgcolor = ((++$i) % 2)?" bgcolor='#F0F0F0'":"";
                             ?>
                             <tr<?php echo $bgcolor?> valign="top">
                               <td class="texto" align="center"><nobr>&nbsp;<?php
                                                     echo "ID do PIN: ".$rs_total_row['psdh_pin_codinterno'];
                               ?>&nbsp;</nobr></td>
                               </tr>
                             <?php
                        } //end while($rs_total_row = pg_fetch_array($rs_total))
                        if ($aux_count == 0) {
                                ?>
                                <tr<?php echo $bgcolor?> valign="top">
                                  <td class="texto" align="center"><nobr>&nbsp;<?php
                                                        echo "Nenhum PIN encontrado (B).";
                                  ?>&nbsp;</nobr></td>
                                  </tr>
                                <?php
                        }
			
                        $sql_apl  = "SELECT count(psah_pin_id) as total from pins_store_apl_historico WHERE psah_pin_id = '0' or pin_status = '0'";
                        $rs_hist_apl = SQLexecuteQuery($sql_apl);
                        if($rs_hist_apl_row = pg_fetch_array($rs_hist_apl)) {
                                if($rs_hist_apl_row['total']>0) {
                                        ?>
                                        <tr bgcolor="F0F0F0">
                                          <td class="texto" align="center"><nobr><b><blink><font color="FF0000" size="2" style="font-weight:bold;">ALERTA:</font></blink> Tentativa de uso de PINs INEXISTENTES:</b>&nbsp;</nobr></td>
                                        </tr>
                                        <tr valign="top">
                                                <td class="texto" align="center"><nobr>&nbsp;Total de tentativas: <blink><font color="FF0000" size="2" style="font-weight:bold;"><?php echo $rs_hist_apl_row['total'];?></font></blink>&nbsp;</nobr></td>
                                        </tr>
                                        <tr valign="top">
                                                <td class="texto" align="center"><nobr>&nbsp;<a href="pins_store_alerta.php">Clique aqui</a> para ver detalhamento.&nbsp;</nobr></td>
                                        </tr>
                        <?php
                                }
                        }
                        ?>
                        <tr bgcolor="F0F0F0">
                                  <td class="texto" align="center"><nobr><b>PINs que Foram Utilizados Mais de Uma Vez:</b>&nbsp;</nobr></td>
                        </tr>
                        <?php
                        $sql_total = "SELECT count(psah_pin_id) as total,to_char(max(psah_data),'YYYY/MM/DD HH24:MI:SS') as data, psah_pin_id
                                        from pins_store_apl_historico
                                        where   pin_status = '".intval($PINS_STORE_STATUS_VALUES['U'])."' AND 
                                                psah_acao = '".intval($PINS_STORE_MSG_LOG_STATUS['SUCESSO_UTILIZACAO'])."'
                                        group by psah_pin_id
                                        having count(psah_pin_id) >1
                                        order by data desc";
                        //echo $sql_total."<br>";
                        $rs_total = SQLexecuteQuery($sql_total);
                        $aux_count = pg_num_rows($rs_total);
                        echo "<table>";
                        while($rs_total_row = pg_fetch_array($rs_total)) { 
                            $bgcolor = ((++$i) % 2)?" bgcolor='#F0F0F0'":"";
                            ?>
                            <tr<?php echo $bgcolor?> valign="top">
                              <td align="center"  class="texto"><nobr>&nbsp;<?php
                                                    echo "ID do PIN: ".$rs_total_row['psah_pin_id']."</nobr></td><td align='center' class='texto'><nobr>&nbsp;Utilizado [".$rs_total_row['total']."] Vezes </nobr></td><td align='center' class='texto'><nobr>&nbsp; Ultima data em [".$rs_total_row['data']."]</nobr>";
                              ?>&nbsp;</nobr></td>
                              </tr>
                            <?php
                        }//end while($rs_total_row = pg_fetch_array($rs_total))
                        echo "</table>";
                        if ($aux_count == 0) {
                                ?>
                                <tr<?php echo $bgcolor?> valign="top">
                                  <td class="texto" align="center"><nobr>&nbsp;<?php
                                                        echo "Nenhum PIN encontrado (A).<br><br>";
                                  ?>&nbsp;</nobr></td>
                                  </tr>
                                <?php
                        }
                        else {
                                ?>
                                <tr<?php echo $bgcolor?> valign="top">
                                  <td class="texto" align="center"><nobr>&nbsp;<?php
                                                        echo "Total de PINs com Problema na Utilização <b>[".$aux_count."]</b><br><br>";
                                  ?>&nbsp;</nobr></td>
                                  </tr>
                                <?php
                        }//end else
                        $sql_apl  = "SELECT count(psah_pin_id) as total from pins_store_apl_historico WHERE psah_acao = '".intval($PINS_STORE_MSG_LOG_STATUS['ERRO_VALIDACAO'])."' and psah_pin_id != '0'";
                        $rs_hist_apl = SQLexecuteQuery($sql_apl);
                        if($rs_hist_apl_row = pg_fetch_array($rs_hist_apl)) {
                        ?>
                                <tr bgcolor="F0F0F0">
                                  <td class="texto" align="center"><nobr><b> Quantidade de Erro na Valida&ccedil;&atilde;o de PINs EXISTENTES:</b>&nbsp;</nobr></td>
                                </tr>
                                <tr valign="top">
                                        <td class="texto" align="center"><nobr>&nbsp;Total de ERROS: <blink><font color="FF0000" size="2" style="font-weight:bold;"><?php echo $rs_hist_apl_row['total'];?></font></blink>&nbsp;</nobr></td>
                                </tr>
                        <?php
                        }
                        $sql_apl  = "SELECT count(psah_pin_id) as total from pins_store_apl_historico WHERE psah_acao = '".intval($PINS_STORE_MSG_LOG_STATUS['ERRO_VALOR'])."'";
                        $rs_hist_apl = SQLexecuteQuery($sql_apl);
                        if($rs_hist_apl_row = pg_fetch_array($rs_hist_apl)) {
                        ?>
                                <tr bgcolor="F0F0F0">
                                  <td class="texto" align="center"><nobr><b> Quantidade de Erro na Valida&ccedil;&atilde;o de Valor do PIN / Valor Total da Compra em PINs EXISTENTES:</b>&nbsp;</nobr></td>
                                </tr>
                                <tr valign="top">
                                        <td class="texto" align="center"><nobr>&nbsp;Total de ERROS: <blink><font color="FF0000" size="2" style="font-weight:bold;"><?php echo $rs_hist_apl_row['total'];?></font></blink>&nbsp;</nobr></td>
                                </tr>
                        <?php
                        }
                        $sql_apl  = "SELECT count(psah_pin_id) as total from pins_store_apl_historico WHERE psah_acao = '".intval($PINS_STORE_MSG_LOG_STATUS['ERRO_UTILIZACAO'])."'";
                        $rs_hist_apl = SQLexecuteQuery($sql_apl);
                        if($rs_hist_apl_row = pg_fetch_array($rs_hist_apl)) {
                        ?>
                                <tr bgcolor="F0F0F0">
                                  <td class="texto" align="center"><nobr><b> Quantidade de Erro na Utiliza&ccedil;&atilde;o de PINs EXISTENTES:</b>&nbsp;</nobr></td>
                                </tr>
                                <tr valign="top">
                                        <td class="texto" align="center"><nobr>&nbsp;Total de ERROS: <blink><font color="FF0000" size="2" style="font-weight:bold;"><?php echo $rs_hist_apl_row['total'];?></font></blink>&nbsp;</nobr></td>
                                </tr>
                        <?php
                        }
		?>
			</table>

      </td>
    </tr>
	</table>

	<br>&nbsp;
	<table class="table txt-preto fontsize-pp bg-branco">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<input type="button" name="btOK" value="Voltar" OnClick="window.location='index.php';" class="botao_simples">
      		</nobr>
      	</td>
    </tr>
	</table>
	<br>&nbsp;

	<table class="table txt-preto fontsize-pp bg-branco">
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>

	
</form>
	</center>
</div>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
