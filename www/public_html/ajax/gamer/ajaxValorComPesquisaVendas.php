<?php
require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto."public_html/sys/includes/topo_sys_inc.php";

if ($_REQUEST['id']){
	$sql = "SELECT pin_valor FROM pins WHERE 1=1 ";
	// id=-1 para levantar valores de todas as operadoras
	if($_REQUEST['id']!=-1) {
		$sql .= " AND opr_codigo = ".$_REQUEST['id']."";
	}
	if(isset($_REQUEST['st']) && $_REQUEST['st']) {
		if(($_REQUEST['st']=="Vendido - TODOS") || ($_REQUEST['st']=="stVendido-TODOS")){
			$sql .= " AND (pin_status='3' or pin_status='6' or pin_status='7')";
		} else {
			$sql .= " AND pin_status='".$_REQUEST['st']."'";
		}
	}
	if(isset($_REQUEST['cn']) && $_REQUEST['cn']) {
		$sql .= " AND pin_canal='".$_REQUEST['cn']."'";
	}
	$sql .= " GROUP BY pin_valor ORDER BY pin_valor;";
    //echo $sql."<br>";
	$rs_oprPins = SQLexecuteQuery($sql);
}


if($rs_oprPins){
	while($rs_oprPins_row = pg_fetch_array($rs_oprPins)){ 
?>
      <nobr><input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="<?php echo $rs_oprPins_row['pin_valor']; ?>" 
		<?php
		if (isset($tf_pins) && is_array($tf_pins)){
			if (in_array($rs_oprPins_row['pin_valor'], $tf_pins)){
				echo " checked";
			}else{
				if ($rs_oprPins_row['pin_valor'] == $tf_pins){
					echo " checked";
				}
			}
		}
		?>
		>
		<?php 
		echo $rs_oprPins_row['pin_valor'] . ",00"; 
		?></nobr>
<?php 
	} 
} else {
	echo "Valores não encontrados (0)";
}
?>