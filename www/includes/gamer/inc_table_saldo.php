<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
function get_venda_pagto_com_pinsepp_composicao($venda_id) {

	$sql  = "select * from pins_store inner join pins_store_pag_epp_pin ON (ps_pin_codinterno=pin_codinterno) where pin_codinterno in (select ps_pin_codinterno from pins_store_pag_epp_pin where tpc_idpagto = ( select idpagto from tb_pag_compras where tipo_cliente = 'M' and idvenda=". $venda_id .") )";
if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql)."<br>";
}
	$rs_pins_eppcash = SQLexecuteQuery($sql);
	$s_composicao = "<table border='0' cellpadding='0' cellspacing='1' width='100%' align='center'>
		<tr bgcolor='F0F0F0' class='texto'>
		  <td colspan='4'><b>Pins EPP Cash Utilizados</b></td>
		</tr>
		<tr bgcolor='F0F0F0' class='texto'>
		  <td align='center'><b>Canal</b></td>
		  <td align='center'><b>cod_interno</b></td>
		  <td align='center'><b>Distribuidor</b></td>
		  <td align='center'><b>pin_valor</b></td>
		</tr>
		";

	while ($rs_pins_eppcash_row = pg_fetch_array($rs_pins_eppcash)){
		$pin_codinterno = $rs_pins_eppcash_row['pin_codinterno'];
		$distributor_codigo = $rs_pins_eppcash_row['distributor_codigo'];
		$pin_valor = $rs_pins_eppcash_row['pin_valor'];
		$canal_dist = $rs_pins_eppcash_row['pspep_canal'];

		$s_composicao .= "<tr class='texto' bgcolor='#F5F5FB'>
			  <td align='center'><nobr>".$GLOBALS['DISTRIBUIDORAS_CANAIS'][$canal_dist] ."</nobr></td>
			  <td align='center'>".$pin_codinterno ."</td>
			  <td align='center'><nobr>".get_nome_distribuidora_by_codigo($distributor_codigo)." (Cód_b: $distributor_codigo)" ."</nobr></td>
			  <td align='center'>".number_format($pin_valor, 2, ',', '.') ."</td>
			</tr>
			";

	}

	$s_composicao .= "</table>";
	return $s_composicao;

}

function get_venda_pagto_com_saldo_composicao($venda_id) {

		$s_composicao = "<table border='0' cellpadding='0' cellspacing='1' width='100%' align='center'>
				<tr class='texto'  bgcolor='F0F0F0'>
				  <td colspan='4'><b>Composi&ccedil;&atilde;o do Saldo Utilizado</b></td>
				  <td align='center'>&nbsp;</td>
				  <td align='center'></td>
				  <td align='center'></td>
				  <td align='center'><b>";
	  //$s_composicao .= number_format($rs_pins_eppcash_row['valorpagtosaldo'], 2, ',', '.') 
		$s_composicao .= "		  </b></td>
				</tr>
			";
		$sql = "select scf.vg_id as vg_id_deposito, 
				(select distributor_codigo 
				from pins_store ps
					inner join pins_store_pag_epp_pin pspep ON (ps_pin_codinterno=pin_codinterno) 
				where pin_codinterno in 
					(
						select ps_pin_codinterno 
						from pins_store_pag_epp_pin pspep1
						where tpc_idpagto = ( 
								select idpagto from tb_pag_compras tpc where tpc.tipo_cliente = 'M' and tpc.idvenda=scf.vg_id
							) 
					)  limit 1
				) as distributor_codigo,  
				* 
				from saldo_composicao_fifo_utilizado scfu
					INNER JOIN saldo_composicao_fifo scf ON (scfu.scf_id=scf.scf_id)
				where scfu.vg_id=".$venda_id;
if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//die("Stop");
}
		$rs_saldo_utilizado = SQLexecuteQuery($sql);
		while($rs_saldo_utilizado_row = pg_fetch_array($rs_saldo_utilizado)) {

//echo $rs_saldo_utilizado_row['vg_id_deposito']."<br>";

			// procura a origem do depósito desta parte do saldo
			if($rs_saldo_utilizado_row['vg_id_deposito']>0) {

				$sql_origem = "select idvenda_origem from tb_pag_compras where tipo_cliente = 'M' and idvenda = ".$rs_saldo_utilizado_row['vg_id_deposito']."";
				$idvenda_origem = get_db_single_value($sql_origem);

				$idvenda_origem_efetivo = (($idvenda_origem>0)?$idvenda_origem:$rs_saldo_utilizado_row['vg_id_deposito']);
				$distributor_codigo = 0;
				//$sql_pin_deposito = "select distributor_codigo from pins_store inner join pins_store_pag_epp_pin ON (ps_pin_codinterno=pin_codinterno) where pin_codinterno in (select ps_pin_codinterno from pins_store_pag_epp_pin where tpc_idpagto = ( select idpagto from tb_pag_compras where tipo_cliente = 'M' and idvenda=".$idvenda_origem_efetivo." ) )";
//										$distributor_codigo = get_db_single_value($sql_pin_deposito);
				$distributor_codigo = $rs_saldo_utilizado_row['distributor_codigo'];
				if(!$distributor_codigo) {
						if($rs_saldo_utilizado_row['scf_canal']=="C") {
							$distributor_codigo = "C";
						} else {
							$distributor_codigo = "?";
						}
				}

if(b_IsUsuarioReinaldo()) { 
//echo str_replace("\n", "<br>\n", $sql_pin_deposito)."<br>";
//echo "idvenda_origem: $idvenda_origem<br>";
//echo "idvenda_origem_efetivo: $idvenda_origem_efetivo<br>";
//echo "distributor_codigo: $distributor_codigo<br>";
}
			}
		
			$s_composicao .= "	<tr class='texto'  bgcolor='F0F0F0'>\n";
			$s_composicao .= "  <td align='center'>&nbsp;Canal:".$rs_saldo_utilizado_row['scf_id_pagamento'] ."</td>
					  <td align='center'>Utilizado o dep&oacute;sito ";
			if($rs_saldo_utilizado_row['scf_valor']>$rs_saldo_utilizado_row['scfu_valor']) {
				$s_composicao .= "Parcialmente";
			} else { 
				$s_composicao .= "Totalmente";
			}
			$s_composicao .= "	</td>
					  <td align='center'><nobr>
				";
			if(!empty($rs_saldo_utilizado_row['vg_id_deposito'])) {
							
				$s_composicao .= "<a style='text-decoration:none' href='com_venda_detalhe.php?venda_id=".$rs_saldo_utilizado_row['vg_id_deposito']."'>".$rs_saldo_utilizado_row['vg_id_deposito']."</a> 
								<span style='color:darkgreen; background-color:#ffffcc'>";
				if($idvenda_origem>0) {
					$s_composicao .= " (Cód_c: ".$rs_saldo_utilizado_row['scf_canal'].")";
				} else {
					$s_composicao .= get_nome_distribuidora_by_codigo($distributor_codigo)." (Cód_a: $distributor_codigo)" ;
				}
				$s_composicao .= "</span>\n";

			}
			$s_composicao .= "</nobr></td>
					  <td align='center'><b>".number_format($rs_saldo_utilizado_row['scfu_valor'], 2, ',', '.') ."</b></td>
					</tr>
				";

		}//end while
		$s_composicao .= "</table>\n";
		return $s_composicao;
}


function get_db_single_value($sql) {
	$val = 0;
	if(!$sql) {
		return $val;
	}
	$res = SQLexecuteQuery($sql);
	if($pg = pg_fetch_array ($res)) { 
		$val = $pg[0];
	} 
	return $val;
}

?>