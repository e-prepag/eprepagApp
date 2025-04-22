<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once DIR_INCS . "gamer/constantesPinEpp.php";

set_time_limit ( 3600 ) ;

$cor1 = "#F5F5FB";
$cor2 = "#F5F5FB";
$cor3 = "#FFFFFF";				

$time_start = getmicrotime();

if(!isset($nscamp) || !$ncamp) $ncamp = 'opr_nome';
if(!isset($nscamp) || !$nscamp) $nscamp = 'ec_uf, pin_valor';

$resopr = pg_exec($connid, "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_pin_online = 0 order by opr_nome");

if(isset($del_estoque)){
    if(!empty($del_opr_codigo) && !empty($del_pin_valor)){
        $sql = "DELETE FROM pins WHERE pin_status='1' AND opr_codigo = " . $del_opr_codigo . " AND pin_valor = " . $del_pin_valor;
        $resdel = pg_exec($connid, $sql);
        $rows_afetadas = pg_affected_rows($resdel);
        $del_msg = "Foram removidos " . $rows_afetadas . " pins do estoque.";
    }
}
if(isset($Submit) && $Submit){

	if($fopr){			
		$resopr_val = pg_exec($connid, "select opr_codigo from operadoras where opr_codigo='$fopr'");
		$pgopr_val = pg_fetch_array($resopr_val);
		$sql = "select pin_valor from pins where opr_codigo='".$pgopr_val['opr_codigo']."' ";
		if($fcanal) $sql .= "and pin_canal='$fcanal' ";
		$sql .= "group by pin_valor order by pin_valor";
		$resval = pg_exec($connid, $sql);
	} else {
		$fvalor = '';
	}

	//Busca Operadoras exceto Brasil Telecom
	$sql = "select t1.opr_nome, t0.pin_valor, count(t0.pin_valor) as quantidade, sum(t0.pin_valor) as total_face, t0.opr_codigo, t0.pin_status ";
	$sql .= "from pins t0, operadoras t1 ";
	$sql .= "where t0.opr_codigo <> 32 and t1.opr_codigo <> 32 and t1.opr_pin_online = 0 ";
	$sql .= "and pin_status='1' ";
//	if($fcanal)	$sql .= "and pin_canal='$fcanal' ";
	if($fopr){ $sql .= "and (t0.opr_codigo='".$fopr."') and (t0.opr_codigo=t1.opr_codigo) "; }
	if($fvalor){ $sql .= "and (t0.pin_valor='".$fvalor."') "; }
	if($fcanal){ $sql .= "and (t0.pin_canal='".$fcanal."') "; }
	if(!isset($ChkTreinamento) || !$ChkTreinamento && $fopr <> 78) $sql .="and (t0.opr_codigo <> 78) ";
	if(!$fopr && !$fvalor){ $sql .= "and (t0.opr_codigo=t1.opr_codigo) "; }
	$sql .= "group by t1.opr_nome, t0.pin_valor, t0.opr_codigo, t0.pin_status ";

	//Busca Brasil Telecom para fazer union com Operadoras
	$sqlBT  = "select 'BRASIL TELECOM ' || ec_uf, pin_valor, count(pin_valor) as qtde, sum(pin_valor) as total, 32, pin_status ";
	$sqlBT .= "from estab_comissao, pins ";
	$sqlBT .= "where (ec_codigo = pin_local) and opr_codigo = 32 and ec_opr_codigo = 32 ";
	$sqlBT .= "and pin_status='1' ";
	if($fvalor)	$sqlBT .= "and pin_valor='$fvalor' ";
	if(isset($fuf) && $fuf) $sqlBT .= "and ec_codigo='$fuf' ";
	$sqlBT .= "group by ec_uf, pin_valor, pin_status ";

	//Se nao houver filtro, faz union da Brasil Telecom
	if(!$fopr || $fopr == '32') $sql .= "union " . $sqlBT;

	$sql .= "order by ".$ncamp.", pin_valor, pin_status"; 
	$resestat = pg_exec($connid, $sql);

	$sqlMedia = str_replace("and pin_status='1' ", "and pin_status='3' and (pin_datavenda >='" . date("Y-m-d",strtotime("now -7 days")) . "' and pin_datavenda <='".date("Y-m-d",strtotime("now -1 days"))."') ", $sql);
    
	$rs_Media = pg_exec($connid, $sqlMedia);

	//Busca Brasil Telecom
/*	$sql  = "select ec_uf, count(pin_valor) as qtde, pin_valor, sum(pin_valor) as total ";
	$sql .= "from estab_comissao, pins ";
	$sql .= "where (ec_codigo = pin_local) and opr_codigo = 32 and ec_opr_codigo = 32 and pin_status='1'";
	if($fvalor)	$sql .= "and pin_valor='$fvalor' ";
	if($fuf) $sql .= "and ec_codigo='$fuf' ";
	$sql .= "group by ec_uf, pin_valor ";
	$sql .= "order by $nscamp desc";
echo $sql;

    $fp=fopen("../../debug.log","ab");
	fwrite($fp,"\r\nIn Pins_Qtde Like as:\r\n".$sql."\r\n");
	fclose($fp);
	$resoprbrt = pg_exec($connid, $sql);
*/	
	$varsel = "&fopr=$fopr&fvalor=$fvalor";

	if($fopr) {
		$sql="select ec_uf,ec_codigo from estab_comissao where ec_opr_codigo = $fopr order by ec_uf asc";
		$resec=pg_exec($connid,$sql);
	}
/*	
	$sql = "select t0.opr_codigo, pin_valor, count(pin_qtde) as total 
			from estat_venda t0,operadoras t1 
			where  
			t0.opr_codigo=t1.opr_codigo
			and opr_pin_online=0
			and opr_status='1'
			and t0.opr_codigo <> 78                                         
			and (trn_data >='".date("Y-m-d",strtotime("now -7 days"))."'  
			and trn_data <='".date("Y-m-d",strtotime("now -1 days"))."')
			group by t0.opr_codigo,pin_valor 
			order by t0.opr_codigo,pin_valor "; 
	$mediaopr=pg_exec($connid,$sql);
*/

	//Esgotados
	$sql  = "select distinct operadoras.opr_nome, pins.opr_codigo, pins.pin_valor from pins inner join operadoras ";
	$sql .= "on pins.opr_codigo = operadoras.opr_codigo ";
	$sql .= "where operadoras.opr_status='1' and operadoras.opr_pin_online = 0 ";
	$sql .= "and (not (operadoras.opr_codigo=17 and pins.pin_valor=26)) ";	// Não conta Mu Online - 26,00
	if($fcanal) $sql .= "and pins.pin_canal='$fcanal' ";
	$sql .= "except ";
	$sql .= "select distinct operadoras.opr_nome, pins.opr_codigo, pins.pin_valor from pins inner join operadoras ";
	$sql .= "on pins.opr_codigo = operadoras.opr_codigo ";
	$sql .= "where operadoras.opr_status='1' and operadoras.opr_pin_online = 0 and pins.pin_status = '1' ";
	if($fcanal) $sql .= "and pins.pin_canal='$fcanal' ";
        $sql .= " order by opr_nome, pin_valor; ";
        $rs_esgotados = pg_exec($connid, $sql);
}
?>
<script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="index.php">Estoque</a></li>
        <li class="active">Consulta de Estoque de Pins</li>
    </ol>
</div>
<?php
    if(isset($del_msg) && !empty($del_msg)){
?>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success">
                    <?php echo $del_msg; ?>
                </div>
            </div>
        </div>
<?php
    }
?>
<table class="table">
  <tr>
    <td valign="top" bgcolor="#FFFFFF">
      <table class="table">
        <tr valign="top">
          <td height="100%">   
            <form name="form1" method="post" action="<?php echo $PHP_SELF ?>">
              <table class="table">
                <tr bgcolor="#ECE9D8"> 
                  <td height="20" colspan="8"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>Pesquisa</strong></font></td>
                </tr>
                <tr> 
                  <td>Operadora:</td>
                  <td> 
                    <select name="fopr" id="fopr" class="combo_normal">
                      <option value="">Todos as Operadoras</option>
                      <?php while ($pgopr = pg_fetch_array ($resopr)) { ?>
                      <option value="<?php if(isset($pgopr['opr_codigo'])) echo $pgopr['opr_codigo'] ?>" <?php if(isset($pgopr['opr_codigo']) && isset($fopr) && $pgopr['opr_codigo'] == $fopr) echo "selected" ?>><?php if(isset($pgopr['opr_nome'])) echo $pgopr['opr_nome'] ?></option>
                      <?php } ?>
                    </select>
                    </td>
                  <td>Valor:</td>
                  <td> 
                    <select name="fvalor" id="fvalor" class="combo_normal">
                      <option value="">Todos os Valores</option>
                        <?php 
                            if(!empty($resval)){
                                while ($pgval = pg_fetch_array ($resval)) { 
                        ?>
                                    <option value="<?php echo $pgval['pin_valor'] ?>" <?php if($pgval['pin_valor'] == $fvalor) echo "selected" ?>><?php echo $pgval['pin_valor'].",00" ?></option>
                        <?php 

                                } 
                            }
                        ?>
					  
                    </select>
                   </td>

				  <td>Canal:</td>
                  <td> 
                    <select name="fcanal" id="fcanal" class="combo_normal">
                      <option value="">Todos os canais</option>
                      <option value="s" <?php if(isset($fcanal) && trim($fcanal) == 's') echo "selected"?>>Site</option>
                      <option value="p" <?php if(isset($fcanal) && trim($fcanal) == 'p') echo "selected"?>>POS</option>
                      <option value="r" <?php if(isset($fcanal) && trim($fcanal) == 'r') echo "selected"?>>Rede</option>
                    </select>
                  </td>
				  <?php if(isset($fopr) && $fopr == 32) {?>
                  <td>UF:<br>
                    </font></td>
                  <td> 
                    <select name="fuf" id="fuf" class="combo_normal">
                      <option value="">Todos os Estados</option>
                      <?php while($pgec=pg_fetch_array($resec)) { ?>
                      <option value="<?php echo $pgec['ec_codigo'] ?>" <?php if(trim($fuf) == trim($pgec['ec_codigo'])) echo "selected"?>><?php echo $pgec['ec_uf'] ?></option>
                      <?php } ?>
                    </select>
                  </td>
                  <?php  }  ?>
                  <td><input type="submit" name="Submit" value="Buscar" class="btn btn-sm btn-info"></td>
                </tr>
              </table>
              <br>
              <table class="table table-bordered">
<?php //			if($fopr <> '32') { ?>
                <tr> 
                  <td><strong><a href="?ncamp=opr_nome<?php echo ($varsel) ? $varsel : "" ?>" class="link_br">Operadora</a></strong></td>
                  <td><div align="right"><strong><a href="?ncamp=quantidade<?php echo ($varsel) ? $varsel : "" ?>" class="link_br">Qtde</a></strong></div></td>
                  <td><strong><a href="?ncamp=quantidade<?php echo ($varsel) ? $varsel : "" ?>" class="link_br">Média diária</a> (última semana)</strong></td>
                  <td><strong><a href="?ncamp=quantidade<?php echo ($varsel) ? $varsel : "" ?>" class="link_br">Dura&ccedil;&atilde;o</a></strong></td>
                  <td><div align="right"><strong><a href="?ncamp=pin_valor<?php echo ($varsel) ? $varsel : "" ?>" class="link_br">Valor da face</a></strong></div></td>
                  <td><div align="right"><strong><a href="?ncamp=total_face<?php echo ($varsel) ? $varsel : "" ?>" class="link_br">Valor Total</a></strong></div></td>
                  <td><div align="right"></div></td>
				</tr>
<?php
			if(!isset($resestat) || !$resestat || pg_num_rows($resestat) == 0){ ?>
				  <tr bgcolor="#f5f5fb"> 
					<td colspan="6" bgcolor="<?php echo $cor1 ?>" align="center">
						<font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
						N&atilde;o h&aacute; registros<br><br>
						</strong></font></td>
				  </tr>
			<?php } else {?>
<?php				
                $pin_total_valor = $total_reg = $pin_total_qtde = 0;
                
                while ($pgestat = pg_fetch_array($resestat)){
					$opr_nome_aux = (isset($opr_nome)) ? $opr_nome : "";
					$opr_nome = $pgestat['opr_nome'];
                                        $opr_codigo = $pgestat['opr_codigo'];
                                        $pin_valor = $pgestat['pin_valor'];
					$pin_total_valor += $pgestat['total_face'];
					$pin_total_qtde += $pgestat['quantidade'];
					$total_reg ++;
					$valor = 1;
                                        
                                        if($opr_codigo != $dd_operadora_EPP_Cash && $opr_codigo != $dd_operadora_EPP_Cash_LH){
                                            $sql = "select count(*) as total_lan
                                                    from tb_dist_operadora_games_produto dogp 
                                                    inner join tb_dist_operadora_games_produto_modelo dogpm on dogp.ogp_id =dogpm.ogpm_ogp_id
                                                    where dogp.ogp_opr_codigo = ".$opr_codigo."
                                                            and dogpm.ogpm_valor = ".$pin_valor." 
                                                            and dogpm.ogpm_ativo = 1
                                                            and dogp.ogp_pin_request = 0;";

                                            $rs_count_lan = pg_exec($connid, $sql);
                                            $rs_count_lan_row = pg_fetch_array($rs_count_lan);
                                            $sql = "select count(*) as total_gamer
                                                    from tb_operadora_games_produto ogp
                                                    inner join tb_operadora_games_produto_modelo ogpm on ogp.ogp_id =ogpm.ogpm_ogp_id
                                                    where ogp.ogp_opr_codigo = ".$opr_codigo."
                                                            and ogpm.ogpm_valor = ".$pin_valor."
                                                            and ogpm.ogpm_ativo = 1
                                                            and ogp.ogp_pin_request=0;";
                                            $rs_count_gamer = pg_exec($connid, $sql);
                                            $rs_count_gamer_row = pg_fetch_array($rs_count_gamer);

                                            if($rs_count_gamer_row['total_gamer'] == 0 && $rs_count_lan_row['total_lan'] == 0) {
                                                $exclusao = true;
                                            }else{
                                                $exclusao = false;
                                            }
                                        }else{
                                            $exclusao = false;
                                        }

					if($pgestat['opr_codigo'] == 32 && $pgestat['quantidade'] <= 10){
						$corTexto = "FF0000";
						$corLinha = $cor1;	//$corLinha = 'FFC8C8';
					} else {
						$corTexto = "666666";
						$corLinha = $cor1;
					}
?>
				<?php if($total_reg > 1 && $opr_nome_aux != $opr_nome){ ?><tr bgcolor="F0F0F0"><td colspan="6" height="10"></td></tr><?php } ?>
                <tr bgcolor="<?php echo $corLinha ?>"> 
                  <td><font color="#<?php echo $corTexto ?>" size="2" face="Arial, Helvetica, sans-serif"><?php echo $opr_nome ?></font></td>
                  <td><div align="right"><font color="#<?php echo $corTexto ?>" size="2" face="Arial, Helvetica, sans-serif"><?php echo $pgestat['quantidade'] ?></font></div></td>
                  	<?php $executa=false;

					   $nrows = 0;

						if($rs_Media && pg_num_rows($rs_Media) > 0) pg_fetch_array($rs_Media,0);
				  		while($pgmediaopr=pg_fetch_array($rs_Media)) {
//echo "pgmediaopr O V Q (S): ".$pgmediaopr['opr_nome']."= ".$pgmediaopr['quantidade']."  ".$pgmediaopr['pin_valor']." (".$pgmediaopr['pin_status'].")<br>\n";
							if($pgmediaopr['opr_nome']==$pgestat['opr_nome'] && $pgmediaopr['pin_valor']==$pgestat['pin_valor']) {
				  				$executa=true;
								$media = $pgmediaopr['quantidade']/7;
								$nrows++;
?>
                  <td><div align="right"><font color="#<?php echo $corTexto ?>" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($media, 2, ',', '.') ?></font></div></td>
                  <td><div align="right"><font color="#<?php echo $corTexto ?>" size="2" face="Arial, Helvetica, sans-serif"><?php echo floor($pgestat['quantidade'] / $media)?> dia(s)</font></div></td>
<?php 
							}
						}
?>
                 <?php 	if(!$executa) { ?>
				  <td>&nbsp;</td>
                  <td>&nbsp;</td>
				 <?php 	} ?>
                  <td><div align="right"><font color="#<?php echo $corTexto ?>" size="2" face="Arial, Helvetica, sans-serif"><?php echo number_format($pgestat['pin_valor'], 2, ',', '.') ?></font></div></td>
                  <td><div align="right"><font color="#<?php if(isset($corTexto)) echo $corTexto ?>" size="2" face="Arial, Helvetica, sans-serif"><?php if(isset($pgestat['total_face'])) echo number_format($pgestat['total_face'], 2, ',', '.') ?></font></div></td>
<?php
                    if($exclusao){
?>
                        <td><div align="center">
                                <form action="" method="POST">
                                    <input type="hidden" name="del_opr_codigo" value="<?php echo $opr_codigo?>">
                                    <input type="hidden" name="del_pin_valor" value="<?php echo $pin_valor?>">
                                    <button type="submit" name="del_estoque" value="Remover Estoque" class="btn btn-danger">Remover Estoque</button>
                                </form>
                            </div>
                        </td>
<?php
                    }
?>
                </tr>
<?php
				 		if ($cor1==$cor2) {$cor1=$cor3;} else {$cor1=$cor2;} 			  
				}
?>
<?php				if (!$valor) { ?>
                <tr> 
                  <td colspan="16" bgcolor="<?php echo $cor1 ?>"><div align="center"><font size="2" face="Arial, Helvetica, sans-serif" color="#666666"><strong><br>
                      N&atilde;o h&aacute; registros.<br><br>
                      </strong></font></div></td>
                </tr>
                <?php } else { ?>
                <tr> 
                  <td bgcolor="#E4E4E4"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong>TOTAIS</strong></font></td>
                  <td bgcolor="#E4E4E4"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_qtde, 0, ',', '.') ?></strong></font></div></td>
                  <td colspan="11" bgcolor="#E4E4E4"><div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></font></div>
                    <div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"></font></div>
                    <div align="right"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo number_format($pin_total_valor, 2, ',', '.') ?></strong></font></div></td>
                </tr>
<?php 				

                }
?>
                <tr> 
                  <td colspan="12" bgcolor="#FFFFFF">
				  	<font size="1" face="Arial, Helvetica, sans-serif" color="#666666"><strong>Total de registros na tela: <?php echo $total_reg ?><br>
                    OBS: Valores expressos em R$.</strong></font></td>
                </tr>
	<?php } ?>
              </table>

              <table class="table table-bordered">
                  <td colspan="3" class="alert alert-danger text-center"><strong>Pins Esgotados</font></strong></td>
              </tr>
		    <?php if (!isset($rs_esgotados) || !$rs_esgotados || pg_num_rows($rs_esgotados) == 0){  ?>
              <tr class="alert alert-danger"> 
                <td colspan="3" bgcolor="<?php echo $cor1 ?>" align="center">
                    N&atilde;o h&aacute; registros
                </td>
              </tr>
              <?php } else { ?>
              <tr > 
                <td><strong>Operadora</font></strong></td>
                <td align="right"><strong>Valor</font></strong></td>
              </tr>
			  <?php
				   $cor1 = "#F5F5FB"; $cor2 = "#F5F5FB"; $cor3 = "#FFFFFF";
				   while ($pgest = pg_fetch_array($rs_esgotados)){
                                                $sql = "select count(*) as total_lan
                                                        from tb_dist_operadora_games_produto dogp 
                                                        inner join tb_dist_operadora_games_produto_modelo dogpm on dogp.ogp_id =dogpm.ogpm_ogp_id
                                                        where dogp.ogp_opr_codigo = ".$pgest['opr_codigo']."
                                                                and dogpm.ogpm_valor = ".$pgest['pin_valor']." 
                                                                and dogpm.ogpm_ativo = 1
                                                                and dogp.ogp_pin_request = 0;";
                                                $rs_count_lan = pg_exec($connid, $sql);
                                                $rs_count_lan_row = pg_fetch_array($rs_count_lan);
                                                $sql = "select count(*) as total_gamer
                                                        from tb_operadora_games_produto ogp
                                                        inner join tb_operadora_games_produto_modelo ogpm on ogp.ogp_id =ogpm.ogpm_ogp_id
                                                        where ogp.ogp_opr_codigo = ".$pgest['opr_codigo']."
                                                                and ogpm.ogpm_valor = ".$pgest['pin_valor']."
                                                                and ogpm.ogpm_ativo = 1
                                                                and ogp.ogp_pin_request=0;";
                                                $rs_count_gamer = pg_exec($connid, $sql);
                                                $rs_count_gamer_row = pg_fetch_array($rs_count_gamer);
                                                
                                                //echo "Publisher ".$pgest['opr_nome']."GAMER:".$rs_count_gamer_row['total_gamer']."  LAN:".$rs_count_lan_row['total_lan']."<br>";
                                                if($rs_count_gamer_row['total_gamer'] != 0 || $rs_count_lan_row['total_lan'] != 0) {
                                                    
                                                        if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;}
				?>
					  <tr bgcolor="#f5f5fb"> 
						<td bgcolor="<?php echo $cor1 ?>"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
						  <?php echo $pgest['opr_nome'] ?></font></td>
						<td bgcolor="<?php echo $cor1 ?>"><div align="right"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"> 
							<?php echo number_format($pgest['pin_valor'], 2, ',', '.') ?> </font></div></td>
					  </tr>
              <?php
                                                } //end if($rs_count_gamer_row['total_gamer'] == 0 && $rs_count_lan_row['total_lan'] == 0)
                                                
                                    } //end while ($pgest = pg_fetch_array($rs_esgotados))
                            } //end else  do if (!$rs_esgotados || pg_num_rows($rs_esgotados) == 0)
              ?>
					 
            </table>
            </form>
          </td>
        </tr>
        <tr> 
          <td bgcolor="#FFFFFF"><font size="1" face="Arial, Helvetica, sans-serif" color="#666666">Tempo execução: <?php  echo number_format(getmicrotime() - $time_start, 2, '.', '.') ?>s 
            </font></td>
        </tr>
      </table>
   </td>
  </tr>
</table>
 <?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
 ?>
</body>
</html>
