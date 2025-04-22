<?php
// sanitize input data in array $params[]
// returns the sanitized version of $params[]
// public 
$exemplo = array(
			'nome_var_1' => array('0' => 'valor_var_1',
								  '1' => 'tipo_var_1',
								  '2' => 'utiliza_metodo_general'),
			'nome_var_2' => array('0' => 'valor_var_2',
								  '1' => 'tipo_var_2',
								  '2' => 'utiliza_metodo_general'),
		);
/*==============================================================
						Legendas
================================================================
tipo de variavel:
		S = String;
		I = Inteiro;
		D = Data.
utiliza metodo general:
		0 = Nao utiliza;
		1 = Utiliza.
=================================================================*/

function sanitize_input_data_array($params, &$err_cod){
			   $params_out = array();
			   foreach($params as $key => $val) {
					   //echo "<font color='bleu'>Campo (key: '$key')</font><br>";
					   if ($val['2'] == '1')
							$val['0'] = sanitize_general_array($val['0']);
					   switch($val['1']) {
							   //String
							   case 'S': 
											 $val_mod = sanitize_str_array($val['0']);
											 break;
							   case 'I': 
											 $val_mod = sanitize_int_array($val['0']);
											 break;
							   case 'D': 
											 $val_mod = sanitize_date_array($val['0']);
											 break;
					   }
					   //echo "<font color='darkgreen'>&nbsp;&nbsp;Campo processado (key: '$key', val: <pre>".print_r($val)."</pre>)</font><br>";
					   $params_out[$key] = $val_mod;
			   }
			   return $params_out;
}
//protected 
function sanitize_int_array($intval){
			   $intval = intval($intval);
			   $outval = (filter_var($intval, FILTER_VALIDATE_INT) === false)?"000":$intval;
			   return $outval;
}
//protected 
function sanitize_str_array($strval){
			   $strval = addslashes($strval);
			   $outval = (filter_var($strval, FILTER_SANITIZE_STRING) === false)?"ERROR":$strval;
			   return $outval;
}
//protected 
function sanitize_date_array($dateval){
			   $dateval = addslashes($dateval);
			   if(strlen($dateval)==19) {
							   // '    4  7  0  3  6  '
							   // '0123456789012345678'
							   // '2010-11-12 18:01:51' 
							   if( (substr($dateval,4,1)=="-") && (substr($dateval,7,1)=="-") && (substr($dateval,10,1)==" ") && (substr($dateval,13,1)==":") && (substr($dateval,16,1)==":") ) {
											   if( (is_numeric(substr($dateval,0,4))) && (is_numeric(substr($dateval,5,2))) && 
															  (is_numeric(substr($dateval,8,2))) &&  (is_numeric(substr($dateval,11,2))) && 
															  (is_numeric(substr($dateval,14,2))) && (is_numeric(substr($dateval,17,2))) ) {
															  $outval = $dateval;
											   } else {
															  $outval = "DATEERRORValuesInt";
											   }
							   } else {
											   $outval = "DATEERRORPunctuation";
							   }
			   } else {
							   $outval = "DATEERROR";
			   }
			   return $outval;
}
// protected 
function sanitize_general_array($strval){
			   $outval = $strval;
			   if (strtoupper($outval) != str_replace("DROP", "d_r_o_p", strtoupper($outval)))
					$outval = str_replace("DROP", "d_r_o_p", strtoupper($outval));
			   if (strtoupper($outval) != str_replace("CREATE", "c_r_e_a_t_e", strtoupper($outval)))
					$outval = str_replace("CREATE", "c_r_e_a_t_e", strtoupper($outval));
			   if (strtoupper($outval) != str_replace("INSERT", "i_n_s_e_r_t", strtoupper($outval)))
					$outval = str_replace("INSERT", "i_n_s_e_r_t", strtoupper($outval));
			   if (strtoupper($outval) != str_replace("DELETE", "d_e_l_e_t_e", strtoupper($outval)))
					$outval = str_replace("DELETE", "d_e_l_e_t_e", strtoupper($outval));
			   if (strtoupper($outval) != str_replace("SELECT", "s_e_l_e_c_t", strtoupper($outval)))
					$outval = str_replace("SELECT", "s_e_l_e_c_t", strtoupper($outval));
			   if (strtoupper($outval) != str_replace("UPDATE", "u_p_d_a_t_e", strtoupper($outval)))
					$outval = str_replace("UPDATE", "u_p_d_a_t_e", strtoupper($outval));
			   if (strtoupper($outval) != str_replace("ALTER", "a_l_t_e_r", strtoupper($outval)))
					$outval = str_replace("ALTER", "a_l_t_e_r", strtoupper($outval));

			   $outval = str_replace("--", "", $outval);
			   $outval = str_replace("\\", "", $outval);
			   $outval = str_replace("'", "", $outval);
			   $outval = str_replace(";", "", $outval);
//			   $outval = str_replace(" ", "", $outval);
			   return $outval;
}
?>