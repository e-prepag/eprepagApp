<?php

		//Validacoes
		$msg = "";	
                declare_valida_formatacao();
		//Campos fixos
		//-------------------------------------------------------------------------------------------------------------------------------------------
		//Valida pagto banco
		if($msg == ""){
			if(!$pagto_banco || is_null($pagto_banco) || $pagto_banco == "" || !is_numeric($pagto_banco))	$msg .= "O Banco deve ser selecionado.\n";
			elseif(!array_key_exists($pagto_banco, $PAGTO_BANCOS))											$msg .= "O Banco é inválido.\n";
		}
		
		//Valida pagto local
		if($msg == ""){
			if(!$pagto_local || is_null($pagto_local) || $pagto_local == "" || !is_numeric($pagto_local))	$msg .= "O Local deve ser selecionado.\n";
			elseif(!array_key_exists($pagto_local, $PAGTO_LOCAIS[$pagto_banco]))							$msg .= "O Local é inválido.\n";
		}

		//Valida pagto Hora:Minutos
		if($msg == ""){
			if((is_null($pagto_data_horas) || $pagto_data_horas == "") || (is_null($pagto_data_minutos) || $pagto_data_minutos == ""))	$msg .= "A Hora:Minutos de Pagamento deve ser preenchida.\n";
		}

		//Valida pagto data
		if($msg == ""){
//			$pagto_data = $pagto_data_Dia . "/" . $pagto_data_Mes . "/" . $pagto_data_Ano;
			$pagto_data = $pagto_data_data. " ".$pagto_data_horas.":".$pagto_data_minutos;
//echo "pagto_data: '".$pagto_data."'<br>";
//echo "date(): '".date("d/m/Y")."'<br>";
//echo "date()-pagto_data: '".(date("d/m/Y")-$pagto_data)."'<br>";
			if(is_null($pagto_data) || $pagto_data == "") 	$msg .= "A Data de Pagamento deve ser preenchida.\n";
			elseif(!verifica_data($pagto_data)) 			$msg .= "A Data de Pagamento é inválida.\n";
//			elseif((date("d/m/Y")-$pagto_data)<0 ) 	$msg .= "A Data de Pagamento é futura, o pagamento deve ter sido feito anteriormente à data de hoje.\n";
//			elseif(bIsFuture($pagto_data)>0) 	$msg .= "A Data de Pagamento é futura, o pagamento deve ter sido feito anteriormente à data de hoje.\n";
		}
		
		//Valida pagto valor pago
		if($msg == ""){
			if(!$pagto_valor_pago || is_null($pagto_valor_pago) || $pagto_valor_pago == "") $msg .= "O Valor Pago deve ser preenchido.\n";
			elseif(!is_moeda($pagto_valor_pago)) 											$msg .= "O Valor Pago é inválido.\n";
		}
		
		//Campos variaveis
		//-------------------------------------------------------------------------------------------------------------------------------------------
		//Valida numero do documento
		if($msg == ""){

			$pagto_nome_docto_Ar = explode(";", $PAGTO_NOME_DOCTO[$pagto_banco][$pagto_local]);

			for($i=0; $i<count($pagto_nome_docto_Ar); $i++){
		
				if(!$pagto_num_docto[$i] || is_null($pagto_num_docto[$i]) || $pagto_num_docto[$i] == ""){ 
					$msg .= $pagto_nome_docto_Ar[$i] . " deve ser preenchido.\n";
	
				} else if(strpos($pagto_num_docto[$i], "|") !== false){ 
					$msg .= $pagto_nome_docto_Ar[$i] . " não pode conter o caracter \"|\".\n";

				} else {
					
					//conversao
					$pagto_num_docto[$i] = trim($pagto_num_docto[$i]);
					$pagto_num_docto[$i] = strtoupper($pagto_num_docto[$i]);
					
					//Banco do Brasil
					//-------------------------------------------------------------------------------------------------------
					if($pagto_banco == "001"){
						
						if($pagto_local == "01"){
							if($i == 0){							
								if(!valida_formatacao("N", 10, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter 10 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 10, "0", STR_PAD_LEFT);  
							}
						
						}else if($pagto_local == "02"){
							if($i == 0){							
								if(!valida_formatacao("NleX", 14, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 14 digitos ou letras. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 14, "0", STR_PAD_LEFT);  
							}

						}else if($pagto_local == "03"){
							if($i == 0){							
								if(!valida_formatacao("NleX", 4, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 4 digitos ou letras. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 4, "0", STR_PAD_LEFT);  
							}else if($i == 1){
								if(!valida_formatacao("NleX", 10, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 10 digitos ou letras. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 10, "0", STR_PAD_LEFT);
							}
							
						}else if($pagto_local == "04"){
							if($i == 0){							
								if(!valida_formatacao("Nle", 15, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 15 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 15, "0", STR_PAD_LEFT);  
							}
							
						}else if($pagto_local == "05"){
							if($i == 0){							
								if(!valida_formatacao("NleX", 4, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 4 digitos ou letras. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 4, "0", STR_PAD_LEFT);  
							}else if($i == 1){
								if(!valida_formatacao("NleX", 10, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 10 digitos ou letras. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 10, "0", STR_PAD_LEFT);
							}
						}
						
					//Bradesco
					//-------------------------------------------------------------------------------------------------------
					}else if($pagto_banco == "237"){

						if($pagto_local == "01"){
							if($i == 0){							
								if(strlen($pagto_num_docto[$i])==4) $pagto_num_docto[$i] = "0".$pagto_num_docto[$i];
								if(!valida_formatacao("N", 5, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter 5 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 5, "0", STR_PAD_LEFT);  
							}else if($i == 1){
								if(!valida_formatacao("N", 4, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter 4 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 4, "0", STR_PAD_LEFT);  
							}else if($i == 2){
								if(!valida_formatacao("Nle", 4, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 4 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 4, "0", STR_PAD_LEFT);  
							}

						}else if($pagto_local == "02"){
							if($i == 0){							
								if(strlen($pagto_num_docto[$i])==3) $pagto_num_docto[$i] = "0".$pagto_num_docto[$i];
								if(!valida_formatacao("Nle", 4, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 4 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 4, "0", STR_PAD_LEFT);  
							}else if($i == 1){
								if(!valida_formatacao("N", 3, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter 3 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 3, "0", STR_PAD_LEFT);  
							}

						}else if($pagto_local == "03"){
							if($i == 0){							
								if(!valida_formatacao("Nle", 7, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 7 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 7, "0", STR_PAD_LEFT);  
							}else if($i == 1){
								if(!valida_formatacao("Nle", 4, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 4 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 4, "0", STR_PAD_LEFT);  
							}

						}else if($pagto_local == "04"){
							if($i == 0){							
								if(!valida_formatacao("Nle", 10, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 10 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 10, "0", STR_PAD_LEFT);  
							}

						}else if($pagto_local == "05"){
							if($i == 0){							
								if(strlen($pagto_num_docto[$i])==4) $pagto_num_docto[$i] = "0".$pagto_num_docto[$i];
								if(!valida_formatacao("N", 5, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter 5 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 5, "0", STR_PAD_LEFT);  
							}else if($i == 1){
								if(!valida_formatacao("N", 4, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter 4 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 4, "0", STR_PAD_LEFT);  
							}
						}
						
					//Caixa Economica Federal
					//-------------------------------------------------------------------------------------------------------
					}else if($pagto_banco == "104"){

						if($pagto_local == "01"){
							if($i == 0){							
								if(!valida_formatacao("N", 10, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter 10 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 10, "0", STR_PAD_LEFT);  
							}  

						}else if($pagto_local == "02"){
							if($i == 0){							
								if(!valida_formatacao("N", 6, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter 6 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 6, "0", STR_PAD_LEFT);  
							}  

						}else if($pagto_local == "03"){
							if($i == 0){							
								if(!valida_formatacao("N", 10, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter 10 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 10, "0", STR_PAD_LEFT);  
							}  

						}else if($pagto_local == "04"){
							if($i == 0){							
								if(!is_hora($pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ser HH:MM\n";
							}  

						}else if($pagto_local == "05"){
							if($i == 0){							
								if(!valida_formatacao("Nle", 10, $pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ter até 10 digitos. Não digite traços ou pontos.\n";
								else $pagto_num_docto[$i] = str_pad($pagto_num_docto[$i], 10, "0", STR_PAD_LEFT);  
							}else if($i == 1){							
								if(!is_hora($pagto_num_docto[$i])) $msg .= $pagto_nome_docto_Ar[$i] . " é inválido. Deve ser HH:MM\n";
							}
						}
					}
				}
			}
		}

		//Verifica se ja existe dados de pagamento com estes valores
		//-------------------------------------------------------------------------------------------------------------------------------------------
		if($msg == ""){
			$sql  = "select count(*) as qtde from tb_venda_games vg 
					 where vg.vg_ug_id = $usuarioId
					 	and vg_pagto_banco = '$pagto_banco'
					 	and vg_pagto_local = '$pagto_local'
					 	and vg_pagto_num_docto = '" . implode("|", $pagto_num_docto) . "'
					 	and vg_pagto_valor_pago = " . moeda2numeric($pagto_valor_pago) . "
					 	and vg_pagto_data = '" . monta_data_gravacao($pagto_data_data)." ".$pagto_data_horas.":".$pagto_data_minutos."'"; 
//					 	and vg_pagto_data = '" . $pagto_data_Ano ."-". $pagto_data_Mes ."-". $pagto_data_Dia . " 00:00:00'";
//echo "sqlaaa: $sql<br>";
                        
			$rs_venda_pagto = SQLexecuteQuery($sql);
//echo "n: ".pg_num_rows($rs_venda_pagto)."<br>";
			if(!$rs_venda_pagto || pg_num_rows($rs_venda_pagto) == 0) $msg = "Erro ao validar dados.\n";
			else {
				$rs_venda_pagto_row = pg_fetch_array($rs_venda_pagto);
				if($rs_venda_pagto_row['qtde'] != 0) $msg = "Dados bancários inválidos ou pedido duplicado.\n";
			}
		}
	
?>
