<?php
require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto."public_html/sys/includes/topo_sys_inc.php";

date_default_timezone_set('America/Fortaleza');

	$sql = "SELECT pin_valor FROM pins WHERE opr_codigo = " . $_REQUEST['id'] . " GROUP BY pin_valor ORDER BY pin_valor;";
	$rs_oprPins = SQLexecuteQuery($sql);


if($rs_oprPins){
	while($rs_oprPins_row = pg_fetch_array($rs_oprPins)){ 
?>
      <nobr><input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="<?php echo $rs_oprPins_row['pin_valor']; ?>" 
		<?php
		if (isset($tf_pins) && $tf_pins && is_array($tf_pins)){
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
}
?>