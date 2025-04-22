<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
//Include com conexão não persistente
//require $_SERVER['DOCUMENT_ROOT']."/connections/connect.php";
include $raiz_do_projeto . "includes/gamer/constantes.php";
require_once "/www/includes/bourls.php";

$bAplicar = true;

set_time_limit ( 18000 ) ; //	500mins
$time_start = getmicrotime();

if(!isset($dd_situacao) || !$dd_situacao) $dd_situacao = "";
if(!isset($tf_data_inic) || !$tf_data_inic) $tf_data_inic = date('d/m/Y');
if(!isset($tf_data_final) || !$tf_data_final) $tf_data_final = date('d/m/Y');
if(!isset($ncamp) || !$ncamp) $ncamp = 'vg_data_inclusao';
if(!isset($inicial) || !$inicial)  $inicial     = 0;
if(!isset($range) || !$range)    $range       = 1;
if(!isset($ordem) || !$ordem)    $ordem       = 0;

if(isset($BtnSearch) && $BtnSearch=="Buscar") {
    $inicial     = 0;
    $range       = 1;
    $total_table = 0;
    $tf_vg_id_lista = "";
}

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 100; //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;


	// Obtem operadora se tiver tf_vg_id_lista
	if(isset($tf_vg_id_lista) && is_numeric($tf_vg_id_lista)) {
		$sql_opr = "select vgm_opr_codigo, vgm_ogpm_id from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg_id = $tf_vg_id_lista;";
//echo $sql_opr."<br>";
		$rs_opr = SQLexecuteQuery($sql_opr);
		$pg_opr = pg_fetch_array($rs_opr);
		$tf_opr_codigo = $pg_opr['vgm_opr_codigo'];
		$vgm_ogpm_id = $pg_opr['vgm_ogpm_id'];
//echo "&nbsp;&nbsp;<b>tf_opr_codigo: ".$tf_opr_codigo."</b><br>";
	}

	if(isset($tf_opr_codigo) && is_numeric($tf_opr_codigo)) {

        if(!isset($vgm_ogpm_id))
            $vgm_ogpm_id = null;
            
		$sql_produto = "select ogp_nome, ogpm_id, ogpm_nome, ogp_id from tb_operadora_games_produto ogp 
							inner join tb_operadora_games_produto_modelo ogpm on ogp.ogp_id = ogpm.ogpm_ogp_id ";
		$sql_produto .= "where 1=1 ";
		if($tf_opr_codigo && is_numeric($tf_opr_codigo)) 
			$sql_produto .= "and ogp_opr_codigo=$tf_opr_codigo ";
		$sql_produto .= "and ogpm_id = $vgm_ogpm_id ";
		$sql_produto .= "order by ogp_opr_codigo ";

/*
		$sql_produto = "select vgm_id, vgm_nome_produto, vgm_nome_modelo, vgm_valor, vgm_qtde from tb_venda_games vg 
							inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id ";
		if($tf_opr_codigo && is_numeric($tf_opr_codigo)) 
			$sql_produto .= "where vgm_opr_codigo=$tf_opr_codigo ";
		$sql_produto .= "order by vgm_opr_codigo ";
*/
//echo "sql_produto: ".$sql_produto."<br>";

		$rs_produto = SQLexecuteQuery($sql_produto);
        
        if($rs_produto){
            $pg_produto = pg_fetch_array($rs_produto);
            $vgm_nome_produto = $pg_produto['ogp_nome'];
            $vgm_ogpm_id = $pg_produto['ogpm_id'];
            $vgm_nome_modelo = $pg_produto['ogpm_nome'];
            $vgm_ogp_id = $pg_produto['ogp_id'];
        }
		
	}
		$b_init_pin_valor = (!isset($tf_pin_valor) || !is_array($tf_pin_valor))?1:0;
	// Tenta montar o pedido com PINs disponíveis
	// Só quando tiver escolhido operadora e venda
	$msg = "";
	if(isset($tf_opr_codigo) && is_numeric($tf_opr_codigo) && $tf_vg_id_lista && is_numeric($tf_vg_id_lista)) {
echo "<hr>";
if(!$bAplicar) {
	echo "<font color='red'>BLOQUEADA a atualização em BD, aceita apenas simulação</font><br><br>";
}
		// Procura PINs disponíveis
		$sql_pin_valor = "select pin_valor, count(*) as n from pins where pin_status='1' and opr_codigo=$tf_opr_codigo group by pin_valor order by pin_valor desc";
		$rs_pin_valor = SQLexecuteQuery($sql_pin_valor);
//echo "sql_pin_valor: $sql_pin_valor<br>";

		$a_pin_valor = array();
		while($f=pg_fetch_array($rs_pin_valor)) {
			$a_pin_valor[] = $f['pin_valor'];
			// Se for a primeira vez set os valores do checkbox a partir da lista de PINs disponíveis
			if($b_init_pin_valor ) $tf_pin_valor[] = $f['pin_valor'];
//echo "pin_valor: R\$".$f['pin_valor'].",00<br>";
		}

		// Obtem o valor total
		$sql_venda_total = "select sum(vgm.vgm_valor * vgm.vgm_qtde) as valor_venda from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg_id = $tf_vg_id_lista group by vg_id;";
//echo $sql_venda_total."<br>";
		$rs_venda_total = SQLexecuteQuery($sql_venda_total);
		$pg_venda_total = pg_fetch_array($rs_venda_total);
		$venda_total = $pg_venda_total['valor_venda'];
echo "&nbsp;&nbsp;<b>Valor total do pedido: R\$".$venda_total.",00</b><br>";

		// registra modelos que devem ser deletados
		$sql_lista_modelos = "select vgm.vgm_id from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg_id = $tf_vg_id_lista;";
//echo $sql_lista_modelos."<br>";
		$rs_lista_modelos = SQLexecuteQuery($sql_lista_modelos);
		$lista_modelos = "0";
		while($f=pg_fetch_array($rs_lista_modelos)) {
			$lista_modelos .= ", ".$f['vgm_id'];
		}
//echo "&nbsp;&nbsp;<b>lista_modelos: <b>".$lista_modelos."</b><br>";


		// Monta a nova composição com o mesmo valor total, valores maiores primeiro
		$a_qtdes = array();
		$resto = $venda_total;
		for($i=0;$i<count($a_pin_valor);$i++) {
			if(in_array($a_pin_valor[$i], $tf_pin_valor)) {
				$a_qtdes[$a_pin_valor[$i]] = floor($resto/$a_pin_valor[$i]);
				$resto = $resto - $a_qtdes[$a_pin_valor[$i]]*$a_pin_valor[$i];			
//echo "Item($i, ".$a_qtdes[$a_pin_valor[$i]]." PINS de R\$".$a_pin_valor[$i].",00) Resto R\$".$resto.",00<br>";
			} else {
				$a_qtdes[$a_pin_valor[$i]] = 0;
				echo "PIN R\$".$a_pin_valor[$i].",00 não foi usado por não estar selecionado<br>\n";
			}
			if($resto<=0) break;
		}

		if($resto==0) {
			echo "Nova composição para obter R\$$venda_total,00<br>";
			for($i=0;$i<count($a_qtdes);$i++) {
				echo "&nbsp;&nbsp; + <b>".$a_qtdes[$a_pin_valor[$i]]."</b> PIN".(($a_qtdes[$a_pin_valor[$i]]>1)?"s":"&nbsp;")." de <b>R\$".$a_pin_valor[$i].",00</b><br>";
			}
			echo "<span style='color:#0000FF; background-color:#CCFFCC'>A nova composição pode ser aplicada (use o botão \"Slice Aplicar\")</span><br>\n";
		} else {
			$msg = "<span style='color:#FF0000; background-color:#FFFF00'>Erro: não foi possível obter o mesmo valor total (R\$$venda_total,00) com os PINs disponíveis selecionados</span><br>\n";
			echo $msg;
			// reset qtdes
			$a_qtdes = array();
		}
echo "<hr>";
	}

echo "";

//echo "Vai para Aplicar - a_pin_valor: ".print_r($a_pin_valor,true).", a_qtdes: ".print_r($a_qtdes,true)."<br>";
//$msg="Nope";
//echo "msg: '$msg'<br>";	
//echo "BtnSliceHidden: '$BtnSliceHidden'<br>";	

	if(true) {	// Start bloqueo de Slice Aplicar
	if($msg=="" && isset($BtnSliceHidden) && $BtnSliceHidden=="1") {
//echo "Vai para Aplicar - a_pin_valor: ".print_r($a_pin_valor,true).", a_qtdes: ".print_r($a_qtdes,true)."<br>";
		if(count($a_pin_valor)>0 && count($a_qtdes)>0) {
			echo "Slice com PINs disponíveis para operadora $tf_opr_codigo<br>";

			//Inicia transacao
			if($msg == ""){
				$sql = "BEGIN TRANSACTION ";
				if($bAplicar) {
					$ret = SQLexecuteQuery($sql);
					if(!$ret) $msg = "Erro ao iniciar transação.\n";
				} else {
					echo "<font face='Arial, Helvetica, sans-serif' size='2' color='green'>Desativada a gravação no BD ($sql)</font><br>";
				}
			}

			// Para cada valor disponível 
			for($i=0;$i<count($a_qtdes);$i++) {
				if($msg == ""){
					$vgm_pin_valor = $a_pin_valor[$i];
					if($a_qtdes[$vgm_pin_valor]>0) {
						$sql = "insert into tb_venda_games_modelo( 
									vgm_vg_id, vgm_ogp_id, vgm_nome_produto, vgm_ogpm_id, vgm_nome_modelo, 
									vgm_opr_codigo, vgm_qtde, vgm_valor, vgm_pin_valor 
									) values(
									".$tf_vg_id_lista.", ".$vgm_ogp_id.", '".$vgm_nome_produto."', ".$vgm_ogpm_id.", '".$vgm_nome_modelo."', 
									".$tf_opr_codigo.", ".$a_qtdes[$vgm_pin_valor].", ".$vgm_pin_valor.", ".$vgm_pin_valor." 
									);";
						echo "<font face='Arial, Helvetica, sans-serif' size='2' color='#0000CC'>$i: '<b>$sql</b>'</font><br>";
						if($bAplicar) {
							$ret = SQLexecuteQuery($sql);
							if(!$ret) $msg = "Erro ao inserir modelo.\n";
						} else {
							echo "<font face='Arial, Helvetica, sans-serif' size='2' color='green'>Desativada a gravação no BD ($sql)</font><br>";
						}
					} else {
						echo "<font face='Arial, Helvetica, sans-serif' size='2' color='red'>$i: Não insere PINs de R\$".$vgm_pin_valor.",00</font><br>";
					}
				} else {
					break;
				}
			}
			// delete o modelo anterior
			if($msg == ""){
				$sql = "delete from tb_venda_games_modelo where vgm_vg_id = ".$tf_vg_id_lista." and vgm_opr_codigo = ".$tf_opr_codigo." and vgm_id in ($lista_modelos);";

				echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#0000CC'>$sql</font><br>";
				if($bAplicar) {
					$ret = SQLexecuteQuery($sql);
					if(!$ret) $msg = "Erro ao deletar modelo.\n";
				} else {
					echo "<font face='Arial, Helvetica, sans-serif' size='2' color='green'>Desativada a gravação no BD ($sql)</font><br>";
				}
			}

			//Finaliza transacao
			if($msg == ""){
				$sql = "COMMIT TRANSACTION ";
				if($bAplicar) {
					$ret = SQLexecuteQuery($sql);
					if(!$ret) $msg = "Erro ao comitar transação.\n";
				} else {
					echo "<font face='Arial, Helvetica, sans-serif' size='2' color='green'>Desativada a gravação no BD ($sql)</font><br>";
				}
			} else {
				$sql = "ROLLBACK TRANSACTION ";
				if($bAplicar) {
					$ret = SQLexecuteQuery($sql);
					if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
				} else {
					echo "<font face='Arial, Helvetica, sans-serif' size='2' color='green'>Desativada a gravação no BD ($sql)</font><br>";
				}
			}

//      Old
/*
			$sql = "select * 
					from tb_venda_games vg 
					inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					where vg_id = $tf_vg_id_lista;";
			$res_slice = pg_exec($sql);

			while($f=pg_fetch_array($res_slice)) {
				echo "<div style='background-color:#CCFFCC'>vgm_id: ".$f['vgm_id'].", vgm_valor: ".$f['vgm_valor'].", vgm_qtde: ".$f['vgm_qtde'];
				$nslices = $f['vgm_qtde']*$f['vgm_valor']/$dd_pin_valor_para_slice;
				if($nslices>1) {
					echo " -> Slice in ".($nslices)." PINs de R\$".$dd_pin_valor_para_slice.",00";
				}
				echo "</div>";
				if($nslices>1) {
					//Inicia transacao
					if($msg == ""){
						$sql = "BEGIN TRANSACTION ";
	//					$ret = SQLexecuteQuery($sql);
	//					if(!$ret) $msg = "Erro ao iniciar transação.\n";
					}

					for($i=1;$i<=$nslices;$i++) {
						if($msg == ""){
							$vgm_pin_valor = $dd_pin_valor_para_slice;
							$sql = "insert into tb_venda_games_modelo( 
										vgm_vg_id, vgm_ogp_id, vgm_nome_produto, vgm_ogpm_id, vgm_nome_modelo, 
										vgm_valor, vgm_qtde, vgm_opr_codigo, vgm_pin_valor 
										) values(
										".$f['vgm_vg_id'].", ".$f['vgm_ogp_id'].", '".$vgm_nome_produto."', ".$vgm_ogpm_id.", '".$vgm_nome_modelo."', 
										".$dd_pin_valor_para_slice.", 1, ".$f['vgm_opr_codigo'].", ".$vgm_pin_valor." 
										);";
							echo "<font face='Arial, Helvetica, sans-serif' size='2' color='#0000CC'>$sql<br>";
	//						$ret = SQLexecuteQuery($sql);
	//						if(!$ret) $msg = "Erro ao inserir modelo.\n";
						} else {
							break;
						}
					}
					// delete o modelo anterior
					if($msg == ""){
						$sql = "delete from into tb_venda_games_modelo where vg_id = ".$f['vg_id']." and vgm_id = ".$f['vgm_id']." and vgm_opr_codigo = ".$f['vgm_opr_codigo']." ;";
						echo "<br><font face='Arial, Helvetica, sans-serif' size='2' color='#0000CC'>$sql<br>";
	//					$ret = SQLexecuteQuery($sql);
	//					if(!$ret) $msg = "Erro ao deletar modelo.\n";
					}

					//Finaliza transacao
					if($msg == ""){
						$sql = "COMMIT TRANSACTION ";
	//					$ret = SQLexecuteQuery($sql);
	//					if(!$ret) $msg = "Erro ao comitar transação.\n";
					} else {
						$sql = "ROLLBACK TRANSACTION ";
	//					$ret = SQLexecuteQuery($sql);
	//					if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
					}

				} else {
					echo "Não mudar nada neste modelo<br>";
				}
			}
*/
		} else {
			echo "Slice não realizado: não tem valores de PINs disponíveis (ou não foi encontrada a quantidade necessária)<br>";
		}


	}	
	} // End bloqueo de Slice Aplicar


	$sql = "select *, (select opr_nome from operadoras o where o.opr_codigo=vgm.vgm_opr_codigo) as opr_nome 
			from tb_venda_games vg 
			inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id ";
//	if($dd_usuario) {
//		$sql .= "inner join tb_dist_venda_games vg on bp.vgm_venda_games_id = vg.vg_id ";
//		$sql .= "inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id ";
//	}
	$sql .= "where 1=1 ";
	$sql .= "and (vg_ultimo_status=1 or vg_ultimo_status=2)";

	if(!isset($tf_vg_id_lista) || !$tf_vg_id_lista) {
		// Lista no servidor de Prod
//		$tf_vg_id_lista = "8647952, 622404, 5619411, 4741454, 9378295, 6177602, 3529753, 155418, 6557869, 5898434, 235686, 6870630, 7058602, 1014678, 2314769, 3959222, 3347331, 4369651, 1298390, 7202678, 1436505, 2213643, 5036661, 2998561, 629323, 4199756, 3343981, 8617653, 7450303, 3873242, 5293439, 9986380, 4708224";
		// Lista no servidor Dev
//		$tf_vg_id_lista = "252083, 645766, 6995545, 9990853, 8949896, 267808, 267808, 267808, 7197878, 428318, 5125128, 5125128, 3862931, 2894591, 1465767, 5518813, 299104, 5783693, 294217, 7993783, 7993783, 7993783, 632863, 4924631, 908554, 9129944, 727748, 252324, 1560072, 1560072, 6625682, 6625682, 2527780, 116918, 4287427, 238143, 2604985, 5355231, 516070, 591373, 6027540, 8584301, 314643, 4061598, 1539926, 958700, 7822883, 4378672, 4633487, 7477426, 5403455, 8032837, 8032837, 8032837";
	}
//echo "tf_vg_id_lista: $tf_vg_id_lista<br>";
//echo "tf_opr_codigo: $tf_opr_codigo<br>";
	if(isset($tf_vg_id_lista) && $tf_vg_id_lista) {
		$sql .= "and vg_id in ($tf_vg_id_lista) ";
		if(is_numeric($tf_vg_id_lista)) {
			//
			$sql_venda_id = "select vgm_opr_codigo from tb_venda_games vg inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg_id = $tf_vg_id_lista;";
			$rs_vendaid = SQLexecuteQuery($sql_venda_id);
			$pg_vendaid = pg_fetch_array($rs_vendaid);
			$tf_opr_codigo = $pg_vendaid['vgm_opr_codigo'];
		}
	}
	if(isset($tf_opr_codigo) && is_numeric($tf_opr_codigo)) {
		$sql_pin_valor = "select pin_valor, count(*) as n from pins where pin_status='1' and opr_codigo=$tf_opr_codigo group by pin_valor order by pin_valor";
		$rs_pin_valor = SQLexecuteQuery($sql_pin_valor);
	}

//echo "tf_vg_id_lista: $tf_vg_id_lista<br>";
//echo "tf_opr_codigo: $tf_opr_codigo<br>";

	if($tf_data_inic && $tf_data_final) {
		$sql .= "and (vg_data_inclusao between '".formata_data($tf_data_inic, 1)." 00:00:00' and '".formata_data($tf_data_final, 1)." 23:59:59') ";
	}	

	if(isset($dd_usuario) && $dd_usuario)		$sql .= "and ug.ug_id = ".$dd_usuario." ";		
	if(isset($tf_valor) && $tf_valor)		$sql .= " and vgm_valor " . $tf_valor_oper . " " . str_replace(',', '.', str_replace('.', '', trim($tf_valor))) . " ";

	if(isset($tf_opr_codigo) && $tf_opr_codigo) 	$sql .= " and vgm.vgm_opr_codigo = ".$tf_opr_codigo." ";

//echo "sql: $sql<br>";

	$res_count = pg_exec($sql);
	$total_table = pg_num_rows($res_count);

	$vgm_valor_total_i = 0;
	while($u=pg_fetch_array($res_count)) {
		$vgm_valor_total_i += $u['vgm_valor']*$u['vgm_qtde'];
//echo "vgm_valor_total_i: $vgm_valor_total_i<br>";
	}


	$sql .= "order by ".$ncamp." ";

	if($ordem == 1) {
		$sql .= " asc ";
		$img_seta = "/images/seta_up.gif";
	} else {
		$sql .= " desc ";
		$img_seta = "/images/seta_down.gif";
	}

	$sql .= " limit ".$max." ";
	$sql .= " offset ".$inicial;

//echo $sql."<br>";
//echo "A: ".date("Y-m-d H:i:s")."<br>";
//die("Stop");

//	trace_sql($sql, "Arial", 2, "#666666", 'b');			
	$resest = pg_exec($connid,$sql);
	
	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
		

	//Operadoras / Produtos / Valores
	$sql = "select * from operadoras ope where opr_status = '1'";
	$rs_operadoras = SQLexecuteQuery($sql);

    if(!isset($tf_data_inic))
        $tf_data_inic = null;
    
    if(!isset($tf_data_final))
        $tf_data_final = null;
    
    if(!isset($tf_valor))
        $tf_valor = null;
    
    if(!isset($tf_opr_codigo))
        $tf_opr_codigo = null;
    
	$varsel = "&tf_data_inic=$tf_data_inic&tf_data_final=$tf_data_final&tf_valor=$tf_valor&tf_opr_codigo=$tf_opr_codigo";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
        optDate.interval = 1000;

    setDateInterval('tf_data_inic','tf_data_final',optDate);
});

function GP_popupConfirmMsg(msg) { 
  document.MM_returnValue = confirm(msg);
}

function GP_popupAlertMsg(msg) { 
  document.MM_returnValue = alert(msg);
}

function get_confirm() {
	if(confirm("Tem certeza que quer substituir um modelo por outro?")) {
		document.form1.BtnSliceHidden.value = 1;
		document.form1.submit();
	} else {
	}
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table txt-preto">
  <tr> 
    <td valign="top"> 
	<form name="form1" method="post" action="">        
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td>
                Intervalo de Datas
            </td>
            <td colspan="2"> 
              <input name="tf_data_inic" type="text" class="form" id="tf_data_inic" value="<?php  echo $tf_data_inic ?>" size="9" maxlength="10">
              - 
              <input name="tf_data_final" type="text" class="form" id="tf_data_final" value="<?php  echo $tf_data_final ?>" size="9" maxlength="10"></td>
            <td colspan="5">
                Valor <select name="tf_valor_oper">
                    <option value=">" <?php if(!isset($tf_valor_oper)) $tf_valor_oper = null;  if($tf_valor_oper == ">") echo "selected" ?>>></option>
                    <option value="=" <?php  if((!$tf_valor_oper) || $tf_valor_oper == "=") echo "selected" ?>>=</option>
                    <option value="<" <?php  if($tf_valor_oper == "<") echo "selected" ?>><</option>
                </select>
                <input name="tf_valor" type="text" class="form" id="tf_valor" value="<?php  echo $tf_valor ?>" size="9" maxlength="10">
                
            </td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td>
                Operadora
            </td>
            <td>
				<select name="tf_opr_codigo" id="tf_opr_codigo" class="form2">
					<option value="" <?php if($tf_opr_codigo == "") echo "selected" ?>>Selecione</option>
					<?php 
						if($rs_operadoras) 
							while($rs_operadoras_row = pg_fetch_array($rs_operadoras)) { ?>
								<option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>" <?php  if ($tf_opr_codigo == $rs_operadoras_row['opr_codigo']) echo "selected"; ?>><?php echo $rs_operadoras_row['opr_nome']; ?></option>
							<?php } ?>
				</select>
            </td>
            <td>
                Valores de PIN disponíveis
            </td>
            <td colspan="5">
					<?php 
						if(isset($rs_pin_valor) && $rs_pin_valor) {
							while($rs_pin_valor_row = pg_fetch_array($rs_pin_valor)) { 
								echo "<nobr><input type='checkbox' name='tf_pin_valor[]' value='".$rs_pin_valor_row['pin_valor']."'";
								if(isset($tf_pin_valor) && is_array($tf_pin_valor)) {
									if(in_array($rs_pin_valor_row['pin_valor'], $tf_pin_valor)) {
										echo " checked";
									}
								}
								echo ">R$".$rs_pin_valor_row['pin_valor'].",00 (".$rs_pin_valor_row['n']." pins)</nobr><br>\n";
									}
						} else {
							echo "Sem valores de PINs disponíveis";
						}
					?>
				</select>
              </td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td height="0" colspan="4"><div align="right">
				<?php if( isset($tf_vg_id_lista) && is_numeric($tf_vg_id_lista)) { ?>
				<input type="button" name="BtnSlice" value="Slice Aplicar" class="botao_search" style="color:#FF0000" onClick="get_confirm()"> 
				<input type="hidden" name="BtnSliceHidden" value="">
				 <?php // onClick="alert('Função desabilitada'); return false;"?>
				&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;
				<input type="submit" name="BtnSlice" value="Slice Simular" class="botao_search" style="color:#0000CC"> &nbsp;&nbsp;&nbsp;
				<?php } else { ?>
				Selecione uma venda para aplicar o Slice
				<?php }  ?>
            </td>
            <td>
                <div align="center"> 
                    <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info">
                </div>
            </td>
          </tr>
        </table>
      </form>
    <table class="table">
        <tr> 
            <td>
                <?php  if($total_table > 0) { ?>
                    Exibindo resultados <strong><?php  echo $inicial + 1 ?></strong> a <strong><?php  echo $reg_ate ?></strong> de <strong><?php  echo $total_table ?></strong>
                <?php  } else { ?>
                    &nbsp;
                <?php  } ?>
            </td>
        </tr>
    </table>   
        <table class="table fontsize-pp">
            <tr bgcolor="#268fbd" class="txt-branco"> 
                <td>
                    <strong><a class="txt-branco" href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=vg_id" . $varsel ?>">vg_id</a></strong> 
                    <?php  if($ncamp == 'vg_id') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                </td>
                <td>
                    <strong><a class="txt-branco" href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=vgm_id" . $varsel ?>">vgm_id</a></strong> 
                    <?php  if($ncamp == 'vgm_id') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                </td>
                <td>
                    <strong><a class="txt-branco" href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=vgm_opr_codigo" . $varsel ?>">vgm_opr_codigo</a></strong> 
                    <?php  if($ncamp == 'vgm_opr_codigo') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                </td>
                <td>
                    <strong>vg_ultimo_status</strong>
                </td>
                <td>
                    <strong><a class="txt-branco" href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=vg_data_inclusao" . $varsel ?>">Data</a></strong>
                    <?php  if($ncamp == 'vg_data_inclusao') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                </td>
                <td>
                    <div align="right"> 
                        <?php  if($ncamp == 'vgm_qtde') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                        <strong><a class="txt-branco" href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=vgm_qtde" . $varsel ?>"><span class="link_br">Qtde</span></a></strong>
                    </div>
                </td>
                <td> <div align="right"> 
                    <?php  if($ncamp == 'vgm_valor_pin') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                    <strong><a class="txt-branco" href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=vgm_valor_pin" . $varsel ?>"><span class="link_br">Valor PIN</span></a></strong></div>
                </td>
                <td>
                    <div align="right"> 
                        <?php  if($ncamp == 'vgm_valor') echo "<img src=".$img_seta." width='10' height='7'>"; ?>
                        <strong><a class="txt-branco" href="<?php  echo $default_add . "?ordem=" . !$ordem . "&inicial=" . $inicial . "&ncamp=vgm_valor" . $varsel ?>"><span class="link_br">Valor</span></a></strong>
                    </div>
                </td>
                <td>
                    <strong><span class="link_br">vgm_id<span></strong>
                </td>
            </tr>
<?php 
        $cor1 = "#F5F5FB";
        $cor2 = "#F5F5FB";
        $cor3 = "#FFFFFF"; 	
        $vg_id_prev = -1;
        if(!isset($vgm_valor_total))
            $vgm_valor_total = null;
        
        while ($pgest = pg_fetch_array($resest)) {
            $valor = 1;
            $vgm_valor_total += $pgest['vgm_valor']*$pgest['vgm_qtde'];
				
?>
            <tr> 
                <td bgcolor="<?php  echo $cor1 ?>"> <?php 
                if( isset($tf_vg_id_lista) && is_numeric($tf_vg_id_lista)) { 
?>
                    <a class="txt-branco" href="/gamer/vendas/com_venda_detalhe.php?venda_id=<?php  echo $pgest['vg_id'] ?>" target="_blank"><?php  echo $pgest['vg_id'] ?></a>
<?php
                } else {

                    if($vg_id_prev!=$pgest['vg_id']) { 
?>
                        <a class="txt-branco" href="slice.php?tf_vg_id_lista=<?php  echo $pgest['vg_id'] . $varsel  ?>" class="link_azul"> <?php } ?>
<?php  
                        echo $pgest['vg_id'];
                        
                        if($vg_id_prev!=$pgest['vg_id']) { 
?>
                        </a>
<?php 
                        } 
                } 
?>
                </td>
                <td bgcolor="<?php  echo $cor1 ?>">             
                  <?php  echo $pgest['vgm_id'] ?></td>         
                <td bgcolor="<?php  echo $cor1 ?>" align="center">             
                  <nobr><?php  echo $pgest['opr_nome']." (".$pgest['vgm_opr_codigo'].")" ?></nobr></td>         
                <td bgcolor="<?php  echo $cor1 ?>" align="center" title="<?php echo $GLOBALS['STATUS_VENDA_DESCRICAO'][$pgest['vg_ultimo_status']]?>"><?php  echo $pgest['vg_ultimo_status'] ?></td>
                <td bgcolor="<?php  echo $cor1 ?>"><?php  echo formata_timestamp ($pgest['vg_data_inclusao'],2) ?></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><?php  echo $pgest['vgm_qtde'] ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><?php  echo $pgest['vgm_pin_valor'] ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><?php  echo $pgest['vgm_pin_valor']*$pgest['vgm_qtde'] ?></div></td>
                <td bgcolor="<?php  echo $cor1 ?>"><div align="right"><?php  echo $pgest['vgm_id'] ?></div></td>
            </tr>
<?php 

		       if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} 
			   $vg_id_prev = $pgest['vg_id']; 
        }
	   
        if (!isset($valor) || !$valor)
        {  
?>
            <tr bgcolor="#f5f5fb"> 
                <td colspan="9" bgcolor="<?php  echo $cor1 ?>">
                    <div align="center"><strong><br>
                    N&atilde;o h&aacute; registros<br>
                    </strong></div></td>
            </tr>
<?php  
        } else { 
?>
            <tr bgcolor="#E4E4E4"> 
              <td colspan="7"><strong>SUBTOTAL</strong></td>
              <td><div align="right"><strong><?php  echo number_format($vgm_valor_total, 2, ',', '.') ?></strong></div></td>
              <td><div align="right">&nbsp;</div></td>
            </tr>
            <tr bgcolor="#E4E4E4"> 
              <td colspan="7"><strong>TOTAL</strong></td>
              <td><div align="right"><strong><?php  echo number_format($vgm_valor_total_i, 2, ',', '.') ?></strong></div></td>
              <td><div align="right">&nbsp;</div></td>
            </tr>
            <tr bgcolor="#E4E4E4"> 
              <td colspan="9" bgcolor="#FFFFFF"><strong> 
                OBS: Valores expressos em R$.</strong></td>
            </tr>
<?php 
            $time_end = getmicrotime();
            $time = $time_end - $time_start;
?>
            <tr> 
              <td colspan="9" bgcolor="#FFFFFF"><?php  echo $search_msg . number_format($time, 2, '.', '.') . $search_unit?> 
                </td>
            </tr>
<?php 
			paginacao_query($inicial, $total_table, $max, '7', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
        }  
?>
      </table>
      <?php  pg_close ($connid); ?>
    </td>
  </tr>
</table>
</html>
