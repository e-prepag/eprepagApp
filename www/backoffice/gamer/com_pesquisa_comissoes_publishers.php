<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."public_html/sys/admin/stats/inc_Comissoes.php";
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12">
<p class='texto'>São utilizadas <a href="https://<?php echo $_SERVER["SERVER_NAME"] ?>/sys/admin/stats/TOTAL_MES_comiss.php" target="_blank">aqui</a></p>
<?php

	$COMISSOES_BRUTAS_matrix = array();
	$COMISSOES_BRUTAS_canal = array();
	$COMISSOES_BRUTAS_matrix_operadoras = array();
	$COMISSOES_BRUTAS_matrix_operadoras_ids = array();


	// levanta Canais e Jogos
	foreach ($COMISSOES_BRUTAS as $canal => $arr){ 
		if(!isset($COMISSOES_BRUTAS_matrix_canal[$canal])) {
			$COMISSOES_BRUTAS_matrix_canal[$canal] = 1;
		}
		foreach ($arr as $key => $val){ 
			if(!isset($COMISSOES_BRUTAS_matrix_operadoras[$key])) {
				$COMISSOES_BRUTAS_matrix_operadoras[$key] = 1;
			}
		}
	}
	// Sort jogos
	ksort($COMISSOES_BRUTAS_matrix_operadoras);

//echo "<hr><pre>".print_r($COMISSOES_BRUTAS_matrix_canal, true)."</pre><hr>";
//echo "<hr><pre>".print_r($COMISSOES_BRUTAS_matrix_operadoras, true)."</pre><hr>";

	// Monta array de comissão para todos os canais e Jogos
	foreach ($COMISSOES_BRUTAS_matrix_canal as $key_canal => $val_canal) {
		$COMISSOES_BRUTAS_matrix[$key_canal] = array();
		foreach ($COMISSOES_BRUTAS_matrix_operadoras as $key_operadora => $val_operadora) {
			$COMISSOES_BRUTAS_matrix[$key_canal][$key_operadora] = ((isset($COMISSOES_BRUTAS[$key_canal][$key_operadora]))?$COMISSOES_BRUTAS[$key_canal][$key_operadora]:-1);
		}
	}

	// Procura indices de operadoras
	$sql = "select * from operadoras order by opr_codigo";
	$rs_operadoras = SQLexecuteQuery($sql);
	if(!$rs_operadoras) {
		echo "Erro ao listar Operadoras. ($sql)<br>";
	} else {
		if($rs_operadoras && pg_num_rows($rs_operadoras)>0) {
			while($rs_operadoras_row = pg_fetch_array($rs_operadoras)) {
				$COMISSOES_BRUTAS_matrix_operadoras_ids[$rs_operadoras_row['opr_nome']] = $rs_operadoras_row['opr_codigo'];
			}
		} else {
			echo "Erro ao listar Operadoras: sem registros<br>";
		}
	}

//echo "<hr><pre>".print_r($COMISSOES_BRUTAS_matrix_operadoras_ids, true)."</pre><hr>";

	// Lista as comissões
	echo "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
	echo "<tr class='texto' style='background-color:#ccffcc;font-weight:bold'><td colspan='2'>&nbsp;</td><td align='center' colspan='".(count($COMISSOES_BRUTAS_matrix_canal)+1)."'>Canais</td></tr>\n";
	echo "<tr class='texto' style='background-color:#ccffcc;font-weight:bold'><td align='center' width='80px'>Operadoras</td><td align='center' width='30px'>ID</td>\n";
	foreach ($COMISSOES_BRUTAS_matrix_canal as $key_canal => $val_canal) {
		echo "<td align='center' width='30px'>$key_canal</td>\n";
	}
	echo "<td align='center'>&nbsp;</td></tr>\n";
		foreach ($COMISSOES_BRUTAS_matrix_operadoras as $key_operadora => $val_operadora) {
			echo "<tr class='texto'>\n";
			echo "<td align='left' style='font-weight:bold'>$key_operadora</td>\n";
			echo "<td align='center' style='font-weight:bold'>".$COMISSOES_BRUTAS_matrix_operadoras_ids[$key_operadora]."</td>\n";
			$b_commissao_zero = true;
			foreach ($COMISSOES_BRUTAS_matrix_canal as $key_canal => $val_canal) {
				$comissao = $COMISSOES_BRUTAS_matrix[$key_canal][$key_operadora];
				$b_commissao_zero = (($comissao>0)?false:true);
				echo "<td align='center' style='color:".(($comissao==0 || $comissao==100)?"ccc":"#000099")."'>".$comissao."</td>\n";
			}
			echo "<td align='center'>".(($b_commissao_zero)?"<font color='red'>sem comissões</font>":"&nbsp;")."</td>\n";
			echo "</tr>\n";
		}	
	echo "</table>\n";
?>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</div>
</body>
</html>

