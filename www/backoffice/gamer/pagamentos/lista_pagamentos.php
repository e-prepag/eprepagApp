<?php  
set_time_limit ( 3000 ) ;
$pagina_titulo = "Lista de pagamentos";

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."banco/bradesco/inc_urls_bradesco.php";
require_once $raiz_do_projeto."banco/bancodobrasil/inc_urls_bancodobrasil.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once $raiz_do_projeto."includes/inc_Pagamentos.php";
require_once $raiz_do_projeto."includes/functionsPagamento.php";
require_once "/www/includes/bourls.php";
$time_start_stats = getmicrotime();
$bDebug = false;

//var_dump($_SESSION);

if(!isset($tf_v_data_inclusao_ini) && !isset($tf_v_data_inclusao_fim)) {
        $tf_v_data_inclusao_ini = $tf_v_data_inclusao_fim = date("d/m/Y");
}

//Validacoes
$msg = "";	
$a_ids = array();
$a_idvendas = array();
$a_emails = array();

$a_formas_aceitas = array("5", "6", "9", "A", "B", "E", "EG", "P", "C", "Q", "Z","R");

//paginacao
$p = $_REQUEST['p'];
if(!$p) $p = 1;
$registros = 50;
$registros_total = 0;

if($tf_ug_id){
        if(!is_csv_numeric_global($tf_ug_id, 1)) {
                $msg = "Código do usuário deve ser numérico ou lista de números separada por vírgulas.\n";
        }
}

if($tf_v_codigo){
        if(!is_csv_numeric($tf_v_codigo)) {
                $msg = "Código da venda deve ser numérico ou lista de números separada por vírgulas.\n";
        }
}
if($tf_d_valor_pago) {
//		if(!is_moeda($tf_d_valor_pago)) $msg = "Valor Pago dos dados do pagamento é inválido.\n";
}

//Operadoras
$sql = "select * from operadoras ope where opr_status = '1' order by opr_nome ";
$rs_operadoras = SQLexecuteQuery($sql);

//Recupera as vendas
if($msg == ""){

    // Teste se clicou na busca
    if($btPesquisar) {
        $sql_where_data = "";
        $sql_where_canal = "";
        $sql_where_forma = "";
        $sql_where_status = "";
        $sql_where_operadora = "";

        // Avoid empty value
        if(!$tf_v_tipo_transacao) $tf_v_tipo_transacao = "Todos";		
        // Validate
        if(($tf_v_tipo_transacao!="Completos") && ($tf_v_tipo_transacao!="Incompletos")  && ($tf_v_tipo_transacao!="Todos") ) $tf_v_tipo_transacao = "Todos";		

        if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) 
                if(verifica_data($tf_v_data_inclusao_ini) != 0 && verifica_data($tf_v_data_inclusao_fim) != 0)
                        $sql_where_data = " and pgt.datainicio between timestamp '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and timestamp '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'";
        if($tf_v_canal) {
                if(($tf_v_canal=="M") || ($tf_v_canal=="E") || ($tf_v_canal=="LR") || ($tf_v_canal=="LO") ) {
                        $sql_where_canal = " and pgt.tipo_cliente='".$tf_v_canal."'";
                }
                // Depósito em saldo é apenas para gamers
                if(($tf_v_canal=="LR") || ($tf_v_canal=="LO")) {
                        if($tf_v_deposito_em_saldo) {
                                $tf_v_deposito_em_saldo = "";
                        }
                }
        }
        if(!empty($tf_numcompra)) {
                $sql_where_data .= " and pgt.numcompra='".$tf_numcompra."'";
        }
        if(!in_array($tf_v_forma_pagamento, $a_formas_aceitas)) {
                $tf_v_forma_pagamento = ""; 
        }
        if($tf_v_forma_pagamento) {
                if($tf_v_forma_pagamento=="C") {
                        $sql_where_forma = " and ".getSQLWhereParaPagtoOnline(true)." ";
                } elseif($tf_v_forma_pagamento=="EG") {
                        $sql_where_forma = " and pgt.iforma='E' and valorpagtogocash>0 ";
                } else {
                        $sql_where_forma = " and pgt.iforma='".$tf_v_forma_pagamento."' ";
                }
        }
        if($tf_v_forma_pagamento == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE'] && !empty($id_transacao_itau)) {
            $sql_id_itau = " and pgt.id_transacao_itau IN (".$id_transacao_itau.") ";
        }//end if($tf_v_forma_pagamento == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE'] && !empty($id_transacao_itau))
        if($tf_v_tipo_transacao=="Completos") {
                $sql_where_status = " and pgt.status=3 ";	// "and vg.vg_ultimo_status='5' "
        } else if($tf_v_tipo_transacao=="Incompletos") {
                $sql_where_status = " and (not pgt.status=3) ";
        }
        if($tf_opr_codigo) {
                $sql_where_operadora = " and ((select count(*) as n from tb_venda_games vg inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id where vg_id = idvenda and vgm_opr_codigo = $tf_opr_codigo))>0  ";
        }

        $sql  = "select ";
        if($tf_com_total_venda) {
        $sql .= "	
                coalesce(case when pgt.tipo_cliente='M' then (select sum(vgm.vgm_valor * vgm.vgm_qtde) as vg_valor from tb_venda_games vg inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id where vg_ug_id = pgt.idcliente and vg_pagto_tipo>2 and vg_ultimo_status = 5 group by vg_ug_id) 
                when pgt.tipo_cliente='LR' then (select sum(vgm.vgm_valor * vgm.vgm_qtde) as vg_valor from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id where vg_ug_id = pgt.idcliente and vg_pagto_tipo>2 and vg_ultimo_status = 5 group by vg_ug_id) end, 0 ) as vg_valor, 

                coalesce(case when pgt.tipo_cliente='M' then (select count(*) as vg_qtde_produtos from tb_venda_games vg inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id 
                where vg_ug_id = pgt.idcliente and vg_pagto_tipo>2 and vg_ultimo_status = 5 group by vg_ug_id) 
                when pgt.tipo_cliente='LR' then (select count(*) as vg_qtde_produtos from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id 		
                where vg_ug_id = pgt.idcliente and vg_pagto_tipo>2 and vg_ultimo_status = 5 group by vg_ug_id) end, 0 ) as vg_qtde_produtos, 

                coalesce(case when pgt.tipo_cliente='M' then (select sum(vgm.vgm_qtde) as vg_qtde_itens from tb_venda_games vg inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id where vg_ug_id = pgt.idcliente and vg_pagto_tipo>2 and vg_ultimo_status = 5 group by vg_ug_id) 
                when pgt.tipo_cliente='LR' then (select sum(vgm.vgm_qtde) as vg_qtde_itens from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id where vg_ug_id = pgt.idcliente and vg_pagto_tipo>2 and vg_ultimo_status = 5 group by vg_ug_id) end, 0 ) as vg_qtde_itens, ";

        }

        $sql .= "
                (case when pgt.tipo_cliente='M' then vg.vg_ultimo_status 
                    when pgt.tipo_cliente='LR' then vgd.vg_ultimo_status 
                    end ) as vg_ultimo_status, 
                    (case when pgt.tipo_cliente='M' then ug.ug_email
                    when pgt.tipo_cliente='LR' then ugd.ug_email
                    end ) as ug_email, 
                    (case when pgt.tipo_cliente='M' then ug.ug_cidade
                    when pgt.tipo_cliente='LR' then ugd.ug_cidade
                    end ) as ug_cidade, 
                    (case when pgt.tipo_cliente='M' then ug.ug_estado
                    when pgt.tipo_cliente='LR' then ugd.ug_estado
                    end ) as ug_estado, 
                    (case when pgt.tipo_cliente='M' then vg.vg_integracao_parceiro_origem_id
                    when pgt.tipo_cliente='LR' then '' 
                    end ) as vg_integracao_parceiro_origem_id, 
                    (case when pgt.tipo_cliente='M' then vg.vg_drupal_order_id
                    when pgt.tipo_cliente='LR' then 0 
                    end ) as vg_drupal_order_id, 
                    pgt.* 
                from tb_pag_compras pgt 
                left outer join tb_venda_games vg on (vg.vg_id=pgt.idvenda)
                left outer join tb_dist_venda_games vgd ON (vgd.vg_id=pgt.idvenda)
                left outer join usuarios_games ug ON (ug.ug_id=pgt.idcliente)
                left outer join dist_usuarios_games ugd ON (ugd.ug_id=pgt.idcliente) 
                where 1=1 ";
        $sql .= "$sql_where_data $sql_where_canal $sql_where_forma $sql_where_status $sql_where_operadora $sql_id_itau"; 
        if($tf_v_codigo) 				$sql .= " and idvenda in (".$tf_v_codigo.") ";
        if($tf_ug_id) 					$sql .= " and idcliente in (".$tf_ug_id.") ";
        if($tf_d_valor_pago) 			$sql .= " and (total/100-taxas) = ".$tf_d_valor_pago." ";
        if($tf_v_deposito_em_saldo && ($tf_v_canal=="M")) {
                if($tf_v_deposito_em_saldo=="P") {
                        $sql .= " and tipo_deposito = 0 ";
                } elseif($tf_v_deposito_em_saldo=="D") {
                        $s_ids = "";
                        foreach($GLOBALS['TIPO_DEPOSITO'] as $key => $val) {$s_ids .= (($s_ids=="")?"":",").$val;}
                        $sql .= " and tipo_deposito in ($s_ids) ";
                }elseif(in_array($tf_v_deposito_em_saldo, $GLOBALS['TIPO_DEPOSITO'])) {
                        $sql .= " and tipo_deposito = $tf_v_deposito_em_saldo ";
                }
        }

//if(b_IsUsuarioWagner()) echo str_replace("\n", "<br>\n", $sql)."<br>";

//if(b_IsUsuarioReinaldo()) echo "Elapsed time (A1): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";



/*
if(b_IsUsuarioWagner()) { 

//echo "sql_where_operadora: '$sql_where_operadora'<br>";
echo "tf_v_deposito_em_saldo: '$tf_v_deposito_em_saldo'<br>";
echo "'$tf_v_deposito_em_saldo' exists in array : ".((in_array($tf_v_deposito_em_saldo, $GLOBALS['TIPO_DEPOSITO']))?"YES":"NO")."<br>";
echo "GLOBALS['TIPO_DEPOSITO']: <pre>".print_r($GLOBALS['TIPO_DEPOSITO'], true)."</pre><br>";
echo "GLOBALS['TIPO_DEPOSITO'][0]: '".$GLOBALS['TIPO_DEPOSITO'][0]."'<br>";
echo "GLOBALS['TIPO_DEPOSITO'][last]: '".$GLOBALS['TIPO_DEPOSITO'][sizeof($GLOBALS['TIPO_DEPOSITO'])-1]."'<br>";
echo "GLOBALS['TIPO_DEPOSITO'][last]: '".$GLOBALS['TIPO_DEPOSITO'][end(array_keys($GLOBALS['TIPO_DEPOSITO']))]."'<br>";
$s_ids = "";
foreach($GLOBALS['TIPO_DEPOSITO'] as $key => $val) {$s_ids .= (($s_ids=="")?"":",").$val;}
echo "$s_ids<hr>";
}
*/

    //if($_SESSION["iduser_bko"] == "0401121156120"){
	    //$_SESSION["iduser_bko"] == "0401121156120"
	   //var_dump($sql);
		
	//}


//        $rs_total = SQLexecuteQuery($sql);
//        if($rs_total) $registros_total = pg_num_rows($rs_total);
    
        $rs_transacoes = SQLexecuteQuery($sql);
		
		//if(isset($_POST["btnExcel"])){
		    // excel
		    $html = "<table border='1'>";
			$html .= "<tr><td style='text-align:center;color:#fff;background-color:#268fbd;font-size:24px;' colspan='13'><b>Extrato de Pagamento</b></td></tr>";
			$html .= "<thead>";
				$html .= "<tr>";
					 $html .= "<th><b>ID</b></th>";
					 $html .= "<th><b>Venda</b></th>";
					 $html .= "<th><b>Data inicio</b></th>";
					 $html .= "<th><b>Data pagto</b></th>";
					 $html .= "<th><b>".utf8_encode("Usuário")."</b></th>";
					 $html .= "<th><b>Canal</b></th>";
					 $html .= "<th><b>Cesta</b></th>";
					 $html .= "<th><b>TD</b></th>";
					 $html .= "<th><b>Valor</b></th>";
					 $html .= "<th><b>Taxas</b></th>";
					 $html .= "<th><b>Pagto</b></th>";
					 $html .= "<th><b>Venda</b></th>";
					 $html .= "<th><b>Sonda</b></th>";
				$html .= "</tr>";
			$html .= "</thead>";
			$html .= "<tbody>";
			
			$valor = 0;
			$taxaTotal = 0;
			while($row = pg_fetch_array($rs_transacoes)){
			
			    $valor += ($row['total']/100-$row['taxas']);
				$taxaTotal += $row['taxas'];
				
				switch($row['tipo_cliente']){
				
					case "M":
					    $tipo = "Money";
					break;
					case "E":
					    $tipo = "Money_Express";
					break;
					case "LR":
					    $tipo = "LH_Pré";
					break;
					case "LO":
                        $tipo = "LH_Pós";
                    break;
                    default:
                        $tipo = "???";
                    break;
				
				}
				
				$html .= "<tr>";
				    $html .= "<td style='text-align:center;'>'".$row["numcompra"]."'</td>";
					$html .= "<td style='text-align:center;'>".$row["idvenda"]."</td>";
					$html .= "<td style='text-align:center;'>".$row["datainicio"]."</td>";
					$html .= "<td style='text-align:center;'>".$row["datacompra"]."</td>";
					$html .= "<td style='text-align:center;'>".utf8_decode($row["cliente_nome"])." (id:".$row["idcliente"].") ".$row["ug_email"]."</td>";
					$html .= "<td style='text-align:center;'>".utf8_encode($tipo)."</td>";
					$html .= "<td style='text-align:center;'>".utf8_encode(substr($row['cesta'],0,strlen($row['cesta'])-1))."</td>";
					$html .= "<td style='text-align:center;'>".$row['tipo_deposito']."</td>";
                    $html .= "<td style='text-align:center;'>".number_format(($row['total']/100-$row['taxas']), 2, '.', '')."</td>";
					$html .= "<td style='text-align:center;'>".number_format(($row['taxas']), 2, '.', '')."</td>";
					$html .= "<td style='text-align:center;'>".$row['status']."</td>";
					$html .= "<td style='text-align:center;'>".$row['vg_ultimo_status']."</td>";
					$html .= "<td style='text-align:center;'>(Sonda)</td>";
				$html .= "</tr>";
				
			}
			
			                        
            $html .= "<tr>
			                <td style='text-align:center;color:#fff;background-color:#268fbd;'>Total: ".number_format($valor, 2, '.', '.')."</td>
							<td style='text-align:center;color:#fff;background-color:#268fbd;'>Taxa Total: ".number_format($taxaTotal, 2, '.', '')."</td>
			         </tr>"; 
			$html .= "</tbody>"; 
			
			$_SESSION["excel"] = $html;
			
				
		//}
		
        if(!$rs_transacoes || pg_num_rows($rs_transacoes) == 0) {
                $msg = "Nenhum pagamento encontrado.\n";
        } else {
                $registros_total = pg_num_rows($rs_transacoes);

                $total_pagamentos=0;
                $total_taxas=0;
                $a_vg_id = array();
                $s_normais = array();
                $s_normais_todos = array();
                $s_integracao = array();
                $s_integracao_todos = array();
				pg_result_seek($rs_transacoes, 0);
                while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){
                        $total_pagamentos += ($rs_transacoes_row['total']/100-$rs_transacoes_row['taxas']);
                        $total_taxas += $rs_transacoes_row['taxas'];
//echo "<pre>".print_r($rs_transacoes_row,true)."</pre>";

                        $a_ids[$rs_transacoes_row['idcliente']] = 1;
                        $a_idvendas[$rs_transacoes_row['idvenda']] = 1;
                        $a_emails[$rs_transacoes_row['idcliente']] = array('idcliente' => $rs_transacoes_row['idcliente'], 'ug_email' => $rs_transacoes_row['ug_email'], 'cliente_nome' => $rs_transacoes_row['cliente_nome'], 'ug_cidade' => $rs_transacoes_row['ug_cidade'], 'ug_estado' => $rs_transacoes_row['ug_estado']);

//if($bDebug1) echo str_repeat("=", 80). "<br>'idcliente' => '".$rs_transacoes_row['idcliente']."', 'ug_email' => '".$rs_transacoes_row['ug_email']."', 'cliente_nome' => '".$rs_transacoes_row['cliente_nome']."', 'ug_cidade' => '".$rs_transacoes_row['ug_cidade']."', 'ug_estado' => '".$rs_transacoes_row['ug_estado']."'<br>";

                        $a_vg_id[] = $rs_transacoes_row['idvenda'];

                        if($rs_transacoes_row['vg_integracao_parceiro_origem_id']=="") {
//						echo $rs_transacoes_row['ug_email']." - ".$rs_transacoes_row['vg_ultimo_status']."<br>";
                                // acrescenta o email à lista de incompletos
                                if(!in_array($rs_transacoes_row['ug_email'], $s_normais_todos)) {
//if($bDebug1) echo "TODOS INCOMPLETO + Acrescenta '".$rs_transacoes_row['ug_email']."'<br>";
                                        $s_normais_todos[] = $rs_transacoes_row['ug_email'];
                                } else {
//if($bDebug1) echo "TODOS INCOMPLETO = Já existe '".$rs_transacoes_row['ug_email']."'<br>";
                                }
                                if($rs_transacoes_row['vg_ultimo_status']==$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
                                        // tem pelo menos um pagamento completo -> retira o email da lista de incompletos
                                        if(in_array($rs_transacoes_row['ug_email'], $s_normais)) {
//if($bDebug1) echo "COMPLETO - Retira '".$rs_transacoes_row['ug_email']."'<br>";
                                                remove_element($s_normais, $rs_transacoes_row['ug_email']);
                                        }
                                } else {
                                        // acrescenta o email à lista de incompletos
                                        if(!in_array($rs_transacoes_row['ug_email'], $s_normais)) {
//if($bDebug1) echo "INCOMPLETO + Acrescenta '".$rs_transacoes_row['ug_email']."'<br>";
                                                $s_normais[] = $rs_transacoes_row['ug_email'];
                                        } else {
//if($bDebug1) echo "INCOMPLETO = Já existe '".$rs_transacoes_row['ug_email']."'<br>";
                                        }
                                }
//if($bDebug1) echo "s_normais: <pre>".print_r($s_normais, true)."</pre>";
//if($bDebug1) echo "s_normais_todos: <pre>".print_r($s_normais_todos, true)."</pre>";
                        } 
                        if($rs_transacoes_row['vg_integracao_parceiro_origem_id']!="") {
//						echo $rs_transacoes_row['ug_email']." - ".$rs_transacoes_row['vg_ultimo_status']."<br>";
                                // acrescenta o email à lista de incompletos
                                if(!in_array($rs_transacoes_row['ug_email'], $s_integracao_todos)) {
//if($bDebug1) echo "TODOS INCOMPLETO + Acrescenta '".$rs_transacoes_row['ug_email']."'<br>";
                                        $s_integracao_todos[] = $rs_transacoes_row['ug_email'];
                                } else {
//if($bDebug1) echo "TODOS INCOMPLETO = Já existe '".$rs_transacoes_row['ug_email']."'<br>";
                                }
                                if($rs_transacoes_row['vg_ultimo_status']==$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
                                        // tem pelo menos um pagamento completo -> retira o email da lista de incompletos
                                        if(in_array($rs_transacoes_row['ug_email'], $s_integracao)) {
//if($bDebug1) echo "COMPLETO - Retira '".$rs_transacoes_row['ug_email']."'<br>";
                                                remove_element($s_integracao, $rs_transacoes_row['ug_email']);
                                        }
                                } else {
                                        // acrescenta o email à lista de incompletos
                                        if(!in_array($rs_transacoes_row['ug_email'], $s_integracao)) {
//if($bDebug1) echo "INCOMPLETO + Acrescenta '".$rs_transacoes_row['ug_email']."'<br>";
                                                $s_integracao[] = $rs_transacoes_row['ug_email'];
                                        } else {
//if($bDebug1) echo "INCOMPLETO = Já existe '".$rs_transacoes_row['ug_email']."'<br>";
                                        }
                                }
//if($bDebug1) echo "<pre>".print_r($s_integracao, true)."</pre>";
                        }
                }

                $sql .= " order by pgt.datainicio desc ";
                $sql .= " offset " . ($p - 1) * $registros . " limit " . $registros;
//if(b_IsUsuarioWagner()) echo "SQL (W):<br>".str_replace("\n", "<br>\n", $sql);
                // Ler novamente para esta página
                $rs_transacoes = SQLexecuteQuery($sql);

//if($bDebug) echo "Elapsed time (A2): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

        ksort($a_ids);
        $s_ids = "";
        foreach($a_ids as $key => $val) {
                if(strlen($s_ids)>0) $s_ids .= ", ";
                $s_ids .= $key;
        }
//echo "<hr>IDs: ".$s_ids."<hr>";

// Bloco Print Wagner Begin 
        ksort($a_idvendas);
        $s_idvendas = "";
        foreach($a_idvendas as $key => $val) {
                if(strlen($s_idvendas)>0) $s_idvendas .= ", ";
                $s_idvendas .= $key;
        }
if(b_IsUsuarioWagner()) { 
	echo "<hr>IDVendas Wagner: ".$s_idvendas."<hr>";
}		
// Bloco Print Wagner End

//if(b_IsUsuarioReinaldo()) echo "Elapsed time (A5): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

		$sql_where_subquery = "and pgt1.status=3 ".str_replace("pgt.", "pgt1.", $sql_where_canal." ".$sql_where_status);	// "".$sql_where_forma." "
		$sql_res = "select idcliente, count(*) as n_total, sum(pgt.total/100-pgt.taxas) as v_total, (
						select (pgt1.total/100-pgt1.taxas) as v_last_total 
						from tb_pag_compras pgt1
						where pgt1.idcliente = pgt.idcliente $sql_where_subquery 
						order by pgt1.datainicio desc limit 1
					) as v_last_total, (
						select (pgt1.total/100-pgt1.taxas) as v_last_total 
						from tb_pag_compras pgt1
						where pgt1.idcliente = pgt.idcliente $sql_where_subquery
						order by pgt1.datainicio asc limit 1
					) as v_first_total, ( 
						select pgt1.datainicio as v_last_total 
						from tb_pag_compras pgt1 
						where pgt1.idcliente = pgt.idcliente $sql_where_subquery 
						order by pgt1.datainicio desc limit 1 
					) as v_last_data, ( 
						select pgt1.datainicio as v_first_total 
						from tb_pag_compras pgt1 
						where pgt1.idcliente = pgt.idcliente $sql_where_subquery 
						order by pgt1.datainicio asc limit 1 
					) as v_first_data
				from tb_pag_compras pgt 
				where 1=1 and pgt.status=3 $sql_where_canal $sql_where_status and idcliente in ($s_ids)	
				group by pgt.idcliente
				";
						// $sql_where_data 
						// $sql_where_forma 
//if(b_IsUsuarioWagner()) echo "SQL (W2):<br>".str_replace("\t", "&nbsp;&nbsp;&nbsp;", str_replace("\n", "<br>\n", $sql_res))."<br>sql_where_subquery = $sql_where_subquery";
		$rs_res = SQLexecuteQuery($sql_res);


		}
            } //end do teste se clicou no botão de busca   
	} else {
		echo "<p class='texto' style='color:red'>$msg</p>"; 
	}
//if($bDebug) echo "Elapsed time (A3): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

	$varsel = "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	$varsel .= "&tf_v_tipo_transacao=$tf_v_tipo_transacao&tf_v_canal=$tf_v_canal&tf_v_forma_pagamento=$tf_v_forma_pagamento";
	$varsel .= "&tf_v_deposito_em_saldo=$tf_v_deposito_em_saldo&tf_opr_codigo=$tf_opr_codigo";
	$varsel .= "&tf_v_codigo=".str_replace(" ", "", $tf_v_codigo)."&tf_d_valor_pago=$tf_d_valor_pago&id_transacao_itau=$id_transacao_itau";
	$varsel .= "&btPesquisar=".str_replace(" ", "", $btPesquisar)."";


?>

<script>

    $(document).ready(function(){
	    $("#btnExcel").on("click", function(){
		   <?php if(isset($_SESSION["excel"]) && !empty($_SESSION["excel"])){ ?>
		        window.location.href = "excel.php";
		   <?php }else{ ?>
		        alert("Tabela vazia,utilize os filtros de pesquisa a baixo"); 
		   <?php }?>
		
		});
    });
	//Função para buscar o endereço.
	function refresh_status_sonda(iforma, numcompra, status, id_transacao_itau){
		var id_field = "id_sonda_"+iforma+"_"+numcompra;
		//função para verificar se o objeto DOM do javascript está pronto.
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/pagamento/pagto_sonda_ajax.php",
				data: "iforma="+iforma+"&numcompra="+numcompra+"&status="+status+"&id_transacao_itau="+id_transacao_itau,
				beforeSend: function(){
//					$("#"+id_field).html("Aguarde... Consultando ("+tipo+")");
					$("#"+id_field).html("<img src='/images/AjaxLoadingIcon.gif' width='30' height='30' title='"+iforma+", "+numcompra+"'>");
				},
				success: function(txt){
					var txt0="????";
					if(txt.length>0) {
						$("#"+id_field).html(txt);	// " ("+txt.length+")<br>"+  // +'<br>\n'+trans1
					} else {
						$("#"+id_field).html("ERROR, iforma="+iforma+",numcompra="+numcompra);
					}
				},
				error: function(){
					$("#"+id_field).html("???");
				}
			});
		});
	}

	function fade_out_mark(mark_name) {
		$(document).ready(function(){
			$("#"+mark_name).fadeOut("slow");
		});
	}

	function show_graph(serial_values) {
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/pagamento/graphs_pags.php",
				data: "vals="+serial_values,
				beforeSend: function(){
					$("#graph").html("Aguarde... Consultando ()");
				},
				success: function(txt){
					$("#graph").html(txt);
				},
				error: function(){
					$("#graph").html("???");
				}
			});
		});
	}
	
</script>
<link href="https://<?php echo $server_url_complete; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="https://<?php echo $server_url_complete; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="https://<?php echo $server_url_complete; ?>/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
        optDate.interval = 1000;

    setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
});
<!--
	function reload() {
		document.form1.action = "lista_pagamentos.php";
		document.form1.submit();
	}

function edita_reg(id) { 
  document.form1.action = "edita_pagamentos.php";
  document.form1.id.value = id;
  document.form1.submit();
}

function validaForm() {
    if (document.form1.id_transacao_itau.value != '' && document.form1.tf_v_forma_pagamento.value != '<?php echo $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']; ?>') {
        alert('Para utilizar o filtro de ID de transação Itau é necessário selecionar a forma de Pagamento Itau!');
        return false;
    }
    else return true;
}
//-->
</script>
<style type="text/css">
.t {border-top: 1px #ccc solid; font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #666666; text-decoration: none;}
.l {border-left: 1px #ccc solid; font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #666666; text-decoration: none;}
.r {border-right: 1px #ccc solid; font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #666666; text-decoration: none;}
.b {border-bottom: 1px #ccc solid; font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #666666; text-decoration: none;}
.lr {border-left: 1px #ccc solid; border-right: 1px #ccc solid; font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #666666; text-decoration: none;}
</style>

<style type="text/css">
<!--
.linkout {background-color: #FFFFFF;} 
.linkout2 {background-color: #F5F5FB;} 
.linkover {background-color:#CCFFCC;}
.linkover3 {background-color:#0099CC;}
-->
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
	<center style="margin: -90px;">

    <table class="txt-preto table fontsize-pp">
    <tr valign="top" align="center">
      <td>
			<form name="form1" id="form1" method="post" action="lista_pagamentos.php" onsubmit="return validaForm();">
			<input type="hidden" id="id" name="id" value="">
			<input type="hidden" name="varsel" value="<?php echo $varsel; ?>">
            <table class="table">
				<?php if(isset($msg_retorno)) { ?>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3">Msg: <b><font color="#339900"><?php echo $msg_retorno; ?></font></b></td>
    	        </tr>
				<?php } ?>
	            <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3">
						<button type="button" id="btnExcel">Gerar Excel</button>
				  </td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Forma de Pagamento</b></td>
    	          <td class="texto" align="center"><b>Período da Compra</b></td>
    	          <td>&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;
					<select id='tf_v_canal' name='tf_v_canal' class="form2"> 
						<option value=''<?php echo (($tf_v_canal=="")?" selected":""); ?>>Todos os canais de venda</option> 
						<option value='M'<?php echo (($tf_v_canal=="M")?" selected":"") ?>>Money</option>
						<option value='E'<?php echo (($tf_v_canal=="E")?" selected":"") ?>>Money Express</option>
						<option value='LR'<?php echo (($tf_v_canal=="LR")?" selected":"") ?>>Lanhouse Pré</option>
						<option value='LO'<?php echo (($tf_v_canal=="LO")?" selected":"") ?>>Lanhouse Pós</option>
					  </select>
				  </td>
    	          <td class="texto" align="center">
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
					  a 
					  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">
					<select id='tf_v_tipo_transacao' name='tf_v_tipo_transacao' class="form2"> 
						<option value='Todos'<?php echo (($tf_v_tipo_transacao=="Todos")?" selected":"") ?>>Todos os pagamentos</option>
						<option value='Incompletos'<?php echo (($tf_v_tipo_transacao=="Incompletos")?" selected":"") ?>>Apenas pagamentos incompletos</option>
						<option value='Completos'<?php echo (($tf_v_tipo_transacao=="Completos")?" selected":"") ?>>Apenas pagamentos COMPLETOS</option>
					  </select>
				  </td>
    	          <td class="texto" align="center">
					<select id='tf_v_forma_pagamento' name='tf_v_forma_pagamento' class="form2"> 
						<option value='Todas'<?php echo (!in_array($tf_v_forma_pagamento, $a_formas_aceitas)?" selected":"") ?>>Todas as formas</option>
						<option value='5'<?php echo (($tf_v_forma_pagamento=="5")?" selected":"") ?>>Transferência entre contas Bradesco (BRD5)</option>
						<option value='6'<?php echo (($tf_v_forma_pagamento=="6")?" selected":"") ?>>Pagamento Fácil Bradesco (BRD6)</option>
						<option value='9'<?php echo (($tf_v_forma_pagamento=="9")?" selected":"") ?>>Pagamento BB - Débito sua Conta (BBR9)</option>
						<option value='A'<?php echo (($tf_v_forma_pagamento=="A")?" selected":"") ?>>Pagamento Banco Itaú (BITA)</option>
						<option value='B'<?php echo (($tf_v_forma_pagamento=="B")?" selected":"") ?>>Pagamento HiPay (HIPB)</option>
						<option value='E'<?php echo (($tf_v_forma_pagamento=="E")?" selected":"") ?>>PINs EPP (EPPE)</option>
						<option value='EG'<?php echo (($tf_v_forma_pagamento=="EG")?" selected":"") ?>>PINs EPP (EPPE) - GoCash</option>
						<option value='P'<?php echo (($tf_v_forma_pagamento=="P")?" selected":"") ?>>Pagamento PayPal (PYPP)</option>
						<option value='C'<?php echo (($tf_v_forma_pagamento=="C")?" selected":"") ?>>Pagamentos Cielo (F-M)</option>
						<option value='Q'<?php echo (($tf_v_forma_pagamento=="Q")?" selected":"") ?>>Pagamento MCOIN (MCOQ)</option>
                                                <option value='R'<?php echo (($tf_v_forma_pagamento=="R")?" selected":"") ?>>Pagamento PIX (<?php echo $GLOBALS['PAGAMENTO_PIX_NOME_BANCO']; ?>)</option>
						<option value='Z'<?php echo (($tf_v_forma_pagamento=="Z")?" selected":"") ?>>Pagamento Banco E-Prepag (BEPZ)</option>
					  </select>
				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">
						<select name="tf_opr_codigo" id="tf_opr_codigo" class="form2">
							<option value="" <?php if($tf_opr_codigo == "") echo "selected" ?>>Todas as operadoras</option>
							<?php 
								if($rs_operadoras) 
									while($rs_operadoras_row = pg_fetch_array($rs_operadoras))
									{
							?>
									<option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>"
									<?php 
										if ($tf_opr_codigo == $rs_operadoras_row['opr_codigo'] || $rs_operadoras_row['opr_codigo'] == $buscaOper) 
											echo " selected";
									?>><?php echo $rs_operadoras_row['opr_nome']." (ID: ".$rs_operadoras_row['opr_codigo'].")"; ?></option>
									<?php } ?>
						</select>

					</td>
    	          <td class="texto" align="center"><nobr>Código do usuário: <input name="tf_ug_id" type="text" class="form2" value="<?php echo $tf_ug_id?>" size="30"></nobr></td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>Códigos do Pedido: <input name="tf_v_codigo" type="text" class="form2" value="<?php echo $tf_v_codigo ?>" size="20"></nobr></td>
    	          <td class="texto" align="center"><nobr>Valor Pago: <input name="tf_d_valor_pago" type="text" class="form2" value="<?php echo $tf_d_valor_pago ?>" size="7" maxlength="7"></nobr> (sem vírgula)</td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>Tipo de pagamento: 
					<select id='tf_v_deposito_em_saldo' name='tf_v_deposito_em_saldo' class="form2"> 
						<option value=''<?php echo (($tf_v_deposito_em_saldo=="")?" selected":"") ?>>Todos os pagamentos</option>
						<option value='P'<?php echo (($tf_v_deposito_em_saldo=="P")?" selected":"") ?>>Apenas pagamentos para compra de PINs</option>
						<option value='D'<?php echo (($tf_v_deposito_em_saldo=="D")?" selected":"") ?>>Apenas Depósitos - Todos</option>
						<?php
//						$TIPO_DEPOSITO_LEGENDA
							foreach($GLOBALS['TIPO_DEPOSITO'] as $key => $val) {
						?>
						<option value='<?php echo $val ?>'<?php echo (($tf_v_deposito_em_saldo==$val)?" selected":"") ?>>Apenas Depósito de '<?php echo $GLOBALS['TIPO_DEPOSITO_LEGENDA'][$val]?>'</option>
						<?php
							}
						?>
					  </select>
				  </nobr></td>
    	          <td class="texto" align="center">
				  <?php 
					if(b_IsBKOUsuarioAdminBKO()) {
				  ?>
				  <nobr>&nbsp;
				  Mostra total da venda <input type="checkbox" name="tf_com_total_venda"<?php if($tf_com_total_venda) echo " CHECKED"; ?>><?php if($tf_com_total_venda) echo "<font color='red'> (Com total de venda)</font>"; ?>
				  </nobr>
				  <?php 
					} else {
				  ?>
				  <nobr>&nbsp;</nobr>
				  <?php 
				  }
				  ?>
				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>ID do pagamento: <input name="tf_numcompra" type="text" class="form2" value="<?php echo $tf_numcompra?>" size="30"></nobr></td>
    	          <td class="texto" align="center"><nobr>ID do Itau: <input name="id_transacao_itau" id="id_transacao_itau" type="text" class="form2" value="<?php echo $id_transacao_itau?>" size="30"></nobr></td>
				  <td class="texto" align="center"><input type="submit" name="btPesquisar" id="btPesquisar" value="Pesquisar" class="botao_simples"></td>
    	        </tr>
			</table>
			
            <table class=" table">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="10">&nbsp;</td>
    	          <td class="texto" align="center" colspan="2"><b>Status</b>&nbsp;</td>

				  <td class="texto" align="center">&nbsp;</td>
			<?php if($tf_com_total_venda) { ?>
				  <td class="texto l" align="center" colspan="3"><b>Vendas</b></td>
			<?php } ?>

				</tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>ID</b>&nbsp;</td>
    	          <td class="texto" align="center"><b>IDVenda</b>&nbsp;</td>
    	          <td class="texto" align="center"><b>Data inicio</b></td>
    	          <td class="texto" align="center"><b>Data pagto.</b></td>

				  <td class="texto" align="center"><b>Usuário</b></td>

				  <td class="texto" align="center"><b>&nbsp;</b></td>
				  <td class="texto" align="center"><b>Canal</b></td>

  <?php //  	          <td class="texto" align="center"><b>Forma pagto.</b></td> ?>
    	          <td class="texto" align="center"><b>Cesta</b></td>
    	          <td class="texto" align="center"><b>TD</b></td>
    	          <td class="texto" align="center"><b>Valor</b></td>
    	          <td class="texto" align="center"><b>Taxas</b></td>

				  <td class="texto" align="center"><b>Pagto</b></td>
    	          <td class="texto" align="center"><b>Venda</b></td>
    	          <td class="texto" align="center"><b>Sonda</b></td>

			<?php if($tf_com_total_venda) { ?>
    	          <td class="texto l" align="center"><b>Total</b></td>
    	          <td class="texto" align="center"><b>NProds</b></td>
    	          <td class="texto" align="center"><b>NItens</b></td>
			<?php } ?>
				</tr>
		<?php	

			$i=0;
			$irows=0;
			if($rs_transacoes) {

// $s_new = array(
//				array('nvendas' => 0, 'nusuarios' => 0, 'emails' => ''),
//			);
				$s_new = array();

				asort($a_emails);
//foreach($a_emails as $key => $val) {
//	echo "'$key'  -&gt; '".$val['idcliente']."', '".$val['ug_email']."', '".$val['cliente_nome']."', '".$val['ug_cidade']."', '".$val['ug_estado']."'<br>";
//}

//============
// ** $user = $_SESSION['userlogin_bko'];
// ** if(strtoupper($user)=="REINALDO") echo "<hr>";

/* **
if(strtoupper($user)=="REINALDO") {
		$rs_transacoes = SQLexecuteQuery($sql);
		if(!$rs_transacoes || pg_num_rows($rs_transacoes) == 0) {
		} else {
			$i = 0;
			while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){ 
					
//				echo "<pre>".print_r($rs_transacoes_row, true)."</pre>";
				echo ($i++)." - \"".$rs_transacoes_row['cliente_nome']."\n - ".$rs_transacoes_row['ug_email']."<br>\n";
			}
		}
}
*/
//============
$i = 0;
$a_res = array();
$b_found = false;
$s_key_found = "";

//if($bDebug) echo "Elapsed time (B1): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

// Prepara lista de IDs para contar depois quais estão com 0 venda 
$a_ids0 = $a_ids;
//foreach($a_ids0 as $key_idcliente => $val) {
//echo ", ".$key_idcliente;
//}
if($rs_res)
while($rs_res_row = pg_fetch_array($rs_res)){
// ** 	echo "<tr><td>".($i++)."</td> <td>".$rs_res_row['idcliente']."</td> <td>".$rs_res_row['n_total']."</td> <td>".number_format($rs_res_row['v_total'], 2, ',', '.')."</td> <td>".$rs_res_row['v_last_total']."</td> <td>".$rs_res_row['v_first_total']."</td> </tr>\n";
$a_res[$rs_res_row['idcliente']] = array(
	'idcliente' => $rs_res_row['idcliente'], 
	'n_total' => $rs_res_row['n_total'], 
	'v_total' => $rs_res_row['v_total'], 
	'v_last_total' => $rs_res_row['v_last_total'], 
	'v_first_total' => $rs_res_row['v_first_total'],
	'v_last_data' => $rs_res_row['v_last_data'], 
	'v_first_data' => $rs_res_row['v_first_data'],
	);

	// Contabiliza total de usuários por número de vendas completas
	$b_found = false;
	$ug_email = $a_emails[$rs_res_row['idcliente']]['ug_email'];
// ** if(strtoupper($user)=="REINALDO") echo "<hr>Novo email: ".$ug_email." (".$rs_res_row['n_total'].")<br>\n";
	foreach($s_new as $key => $val) { 
// ** if(strtoupper($user)=="REINALDO") echo "&nbsp;&nbsp;&nbsp;s_new[$key] = \"".print_r($val,true)."\"<br>\n";
		if(((1*strpos($val['emails'], $ug_email))>0)) {
			$b_found = true;
			$s_key_found = $key;
// ** if(strtoupper($user)=="REINALDO") echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color='blue'>YES FOUND (key: $s_key_found)</font> ".$ug_email." (".$rs_res_row['n_total'].")<br>\n";
			break;
		} else {
// ** if(strtoupper($user)=="REINALDO") echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color='red'>NOT FOUND</font> ".$ug_email." (".$rs_res_row['n_total'].")<br>\n";
		}
	}
/* **
if(strtoupper($user)=="REINALDO") {
echo "<font color='green'>&nbsp;&nbsp;&nbsp;Lista s_new[] ".count($s_new)." items<br>\n";
foreach($s_new as $key => $val) { 
echo "&nbsp;&nbsp;&nbsp;s_new[$key] = \"".print_r($val,true)."\"<br>\n";
}
echo "</font><br>\n";
}
*/
// ** if(strtoupper($user)=="REINALDO") echo "&nbsp;&nbsp;&nbsp;====== ====== ===<br>\n";

	if(!$b_found) {
		$key = $rs_res_row['n_total'];
		$idcliente = $rs_res_row['idcliente'];
// ** if(strtoupper($user)=="REINALDO") echo "&nbsp;&nbsp;&nbsp;++ ++ <font color='blue'>SAVE (key: '$key')</font>: ".$ug_email." (".$rs_res_row['n_total'].")<br>\n";
		if(!isset($s_new[$key])) $s_new[$key] = array('nvendas' => $key, 'nusuarios' => 0, 'emails' => '');

		if(strlen($s_new[$key]['emails'])>0) $s_new[$key]['emails'] .= "; ";
		$s_new[$key]['emails'] .= "\"".$a_emails[$idcliente]['cliente_nome']."\"&nbsp;&lt;".$ug_email."&gt;";
		$s_new[$key]['nusuarios']++;

		// Retira de lista de emails com n_total = 0
		unset($a_ids0[$idcliente]);
// ** if(strtoupper($user)=="REINALDO") echo "&nbsp;&nbsp;&nbsp;Retira de lista com n_total=0: idcliente=$idcliente<br>\n";

	} else {
// ** if(strtoupper($user)=="REINALDO") echo "&nbsp;&nbsp;&nbsp;** ** <font color='red'>SKIP</font>: ".$ug_email."<br>\n";
	}
// ** if(strtoupper($user)=="REINALDO") echo "Termina email: ".$ug_email."<hr>\n";
/* ** 
if(strtoupper($user)=="REINALDO") {
echo "<font color='darkyellow'>&nbsp;&nbsp;&nbsp;Lista s_new[] ".count($s_new)." items<br>\n";
foreach($s_new as $key => $val) { 
echo "&nbsp;&nbsp;&nbsp;s_new[$key] = \"".print_r($val,true)."\"<br>\n";
}
echo "</font><br>\n";
}
*/
} // while {} end

//if(b_IsUsuarioReinaldo()) echo "Elapsed time (A7): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

//if($bDebug) echo "Elapsed time (B2): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

// Insere emails com n_total = 0  - aqueles que restarem em $a_ids0
$key0 = 0;
if(!isset($s_new[$key0])) $s_new[$key0] = array('nvendas' => $key0, 'nusuarios' => 0, 'emails' => '');
foreach($a_ids0 as $key_idcliente => $val) {
//echo ", ".$key_idcliente;
	$ug_email = $a_emails[$key_idcliente]['ug_email'];
	if(strlen($s_new[$key0]['emails'])>0) $s_new[$key0]['emails'] .= "; ";
	$s_new[$key0]['emails'] .= "\"".$a_emails[$key_idcliente]['cliente_nome']."\"&nbsp;&lt;".$ug_email."&gt;";
	$s_new[$key0]['nusuarios']++;
}

//if($bDebug) echo "Elapsed time (B3): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

/*
echo "<hr>";
$i = 0;
echo "<tr><td>i</td> <td>numcompra</td> <td>idcliente</td> <td>ug_email</td> <td>n_total</td> <td>v_total</td> <td>v_last_total</td> <td>v_last_data</td> <td>v_first_total</td>  <td>v_first_data</td>  </tr>\n";

while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){
//	echo str_replace("\n", "<br>\n", print_r($rs_transacoes_row, true))."<br>";
	$idcliente = $rs_transacoes_row['idcliente'];
	echo "<tr><td>".($i++)."</td> <td>".$rs_transacoes_row['numcompra']."</td> <td>".$rs_transacoes_row['idcliente']."</td> <td>".$rs_transacoes_row['ug_email']."</td> <td>".$a_res[$idcliente]['n_total']."</td> <td>".$a_res[$idcliente]['v_total']."</td> <td>".$a_res[$idcliente]['v_last_total']."</td> <td><nobr>".$a_res[$idcliente]['v_last_data']."</nobr></td> <td>".$a_res[$idcliente]['v_first_total']."</td> <td><nobr>".$a_res[$idcliente]['v_first_data']."</nobr></td> </tr>\n";
}
*/
//die("Stop");

				$irows=0;
				$total_pagamentos_page=0;
				$total_taxas_page=0;
				while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){
//if($bDebug) echo "Elapsed time (B4_$irows): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

					$bgcolor = ((++$i) % 2)?"F5F5FB":"FFFFFF";
					$sstyle = (($i) % 2)?"linkout2":"linkout";
					$bgcolor2 = ((($rs_transacoes_row['total']/100-$rs_transacoes_row['taxas'])>0  || ($rs_transacoes_row['iforma']=='Z') )?"":";background-color:FFCC33");
					$irows++;
					$total_pagamentos_page += ($rs_transacoes_row['total']/100-$rs_transacoes_row['taxas']);
					$total_taxas_page += $rs_transacoes_row['taxas'];
					$idcliente = $rs_transacoes_row['idcliente'];
					
					$venda_id = $rs_transacoes_row['idvenda'];
					$vgm_qtde = 0;
					$vgm_pin_codinterno = "";
					$npins = 999999;	//get_qtde_pins($venda_id, $vgm_qtde, $vgm_pin_codinterno);

					$subiforma = (($rs_transacoes_row['iforma']=="E" && $rs_transacoes_row['valorpagtogocash']>0)?"G":"");
/*	
if(b_IsUsuarioReinaldo()) { 
echo "iforma: '".$rs_transacoes_row['iforma']."', subiforma: '$subiforma', valorpagtogocash: ".$rs_transacoes_row['valorpagtogocash']."<br>";
}
*/
			?>
    	        <tr bgcolor='<?php echo $bgcolor?>' valign="top">
				<?php 
					// <span onClick="edita_reg(<_?_php echo $rs_transacoes_row['idpagto']?_>)">
					//tables
				?>
				 
    	          <td class="texto" align="center">&nbsp;<a href="edita_pagamentos.php?id=<?php echo $rs_transacoes_row['idpagto']?>" target="_blank"><?php echo $rs_transacoes_row['numcompra']?></a><?php if($rs_transacoes_row['iforma']=='A') echo "<br><font color='#0000CC'>IDItau: ".str_pad($rs_transacoes_row['id_transacao_itau'], 8, "0", STR_PAD_LEFT) ?>
				  <?php if($rs_transacoes_row['vg_drupal_order_id']>0) echo "<br><font color='#339933'>dr_id: ".$rs_transacoes_row['vg_drupal_order_id']."</font>" ?>
				  </font></td>
    	          <td class="texto" align="center" title="<?php echo "venda_id: ".$venda_id."\n vgm_qtde: ".$vgm_qtde."\n vgm_pin_codinterno: ".$vgm_pin_codinterno." " ?>">&nbsp;<?php if($rs_transacoes_row['idvenda']>0) { ?><a href="/<?php if($rs_transacoes_row['tipo_cliente']=="LR" || $rs_transacoes_row['tipo_cliente']=="LO") echo "pdv"; else echo "gamer";?>/vendas/com_venda_detalhe.php?venda_id=<?php echo str_pad($rs_transacoes_row['idvenda'], 8, "0", STR_PAD_LEFT)?>" target="_blank"><?php } ?><?php echo str_pad($rs_transacoes_row['idvenda'], 8, "0", STR_PAD_LEFT)?><?php if($rs_transacoes_row['idvenda']>0) { ?></a><?php } ?></td>
    	          <td class="texto" align="center"><nobr><?php echo (($rs_transacoes_row['datainicio'])?formata_data_ts_pos($rs_transacoes_row['datainicio'], 0, true, true):"-") ?></nobr></td>
    	          <td class="texto" align="center">&nbsp;<nobr><font color='<?php echo (($rs_transacoes_row['datacompra'])?"#FF0000":"") ?>'><?php echo (($rs_transacoes_row['datacompra'])?formata_data_ts_pos($rs_transacoes_row['datacompra'], 0, true, true):"-") ?></font></nobr></td>
    	          
				  <td class="texto" align="center" title="<?php echo "Total de ".(1*$a_res[$idcliente]['n_total'])." vendas com R$".number_format($a_res[$idcliente]['v_total'], 2, ',', '.')." pagos" ?>"><nobr>&nbsp;<?php echo "<span".(($rs_transacoes_row['vg_integracao_parceiro_origem_id']!="")?" style='background-color:#FFFF00'":"")."".(($rs_transacoes_row['vg_integracao_parceiro_origem_id']!="")?" title='Integração ".getPartner_name_By_ID($rs_transacoes_row['vg_integracao_parceiro_origem_id'])." (store_id: ".$rs_transacoes_row['vg_integracao_parceiro_origem_id'].")'":"").">".$rs_transacoes_row['cliente_nome']."</span>&nbsp;(ID:".$rs_transacoes_row['idcliente'].")<br><span style='color:#0000CC'>".$rs_transacoes_row['ug_email']."</span> ".(($rs_transacoes_row['ug_cidade'])?"<br><b style='color:#990000'>".$rs_transacoes_row['ug_cidade']."</b> (<b  style='color:#990000'>".$rs_transacoes_row['ug_estado']."</b>)":""); ?>&nbsp;</nobr></td>

    	          <td class="texto" align="center"><nobr><?php 
					  echo ( ((1*$a_res[$idcliente]['n_total'])==0) 
								? "<b><font style='background-color:#CCFFCC; color:#0000FF' title='Usuário ainda sem compras'>NEW_0</font></b>" 
								: ( ($a_res[$idcliente]['n_total']<10) 
										? "<font style='' title='Usuário com ".$a_res[$idcliente]['n_total']." compra"
												. ( ($a_res[$idcliente]['n_total']>1)?"s":"" ) 
												. " - R$".number_format($a_res[$idcliente]['v_total'], 2, ',', '.').""
												.(($a_res[$idcliente]['n_total']>1)
													?"\nMédia - R$" . number_format($a_res[$idcliente]['v_total']/$a_res[$idcliente]['n_total'], 2, ',', '.')."/compra" 
														. "\nPrimeira em " . substr($a_res[$idcliente]['v_first_data'], 0, 19) . " - R$" 
														. number_format($a_res[$idcliente]['v_first_total'], 2, ',', '.') 
													:"")
												. "\nÚltima em " . substr($a_res[$idcliente]['v_last_data'], 0, 19) . " - R$" 
												. number_format($a_res[$idcliente]['v_last_total'], 2, ',', '.') 
												. "'>NEW_".$a_res[$idcliente]['n_total']."</font>" 
										: "<b><font style='background-color:#FFFF00; color:#FF0000' title='Usuário VIP: "  
											. $a_res[$idcliente]['n_total'] . " compras - R$" . number_format($a_res[$idcliente]['v_total'], 2, ',', '.') 
											. "\nMédia - R$" . number_format($a_res[$idcliente]['v_total']/$a_res[$idcliente]['n_total'], 2, ',', '.')."/compra" 
												.(($a_res[$idcliente]['n_total']>1)
												?"\nPrimeira em " . substr($a_res[$idcliente]['v_first_data'], 0, 19) . " - R$" 
													. number_format($a_res[$idcliente]['v_first_total'], 2, ',', '.') 
												:"")
											. "\nÚltima em " . substr($a_res[$idcliente]['v_last_data'], 0, 19) . " - R$" 
											. number_format($a_res[$idcliente]['v_last_total'], 2, ',', '.') 
											. "'>VIP_".$a_res[$idcliente]['n_total']."</font></b>"
								) 
							)
					?></nobr></td>
				<?php 
					// $_SERVER['SERVER_HOST']
					if(($rs_transacoes_row['tipo_cliente']=="LR") || ($rs_transacoes_row['tipo_cliente']=="LO")) {
						$surl_vendas = "/pdv/vendas/com_venda_detalhe.php?ordem=1&ncamp=vg_data_inclusao&inicial=0&BtnSearch=1&tf_v_data_inclusao_ini=".date("d/m/Y")."&tf_v_data_inclusao_fim=".date("d/m/Y")."&tf_u_codigo=".$rs_transacoes_row['idcliente'];
					} elseif(($rs_transacoes_row['tipo_cliente']=="M") || ($rs_transacoes_row['tipo_cliente']=="E")) {
						$surl_vendas = "/gamer/vendas/com_venda_detalhe.php?ordem=1&ncamp=vg_data_inclusao&inicial=0&BtnSearch=1&tf_v_data_inclusao_ini=".date("d/m/Y")."&tf_v_data_inclusao_fim=".date("d/m/Y")."&tf_u_codigo=".$rs_transacoes_row['idcliente'];
					}
				?>
				  <td class="texto" align="center" valign="top">
				  <a href="<?php echo $surl_vendas ?>" target="_blank"><?php echo utf8_decode(get_tipo_cliente_descricao($rs_transacoes_row['tipo_cliente'])) ?></a>
				  <br>
					<?php echo getLogoBancoSmall($rs_transacoes_row['iforma'], $subiforma); //(($rs_transacoes_row['iforma']==5)?"<img src='../../images/logo-bradesco-small.gif' width='15' height='15' border='0' title='Bradesco'>":(($rs_transacoes_row['iforma']==6)?"<img src='../../images/logo-bradesco-small.gif' width='15' height='15' border='0' title='Bradesco'>":(($rs_transacoes_row['iforma']==9)?"<img src='../../images/B_Brasil-small.gif' width='15' height='15' border='0' title='Banco do Brasil'>":(($rs_transacoes_row['iforma']=='A')?"<img src='../../images/itau-small.gif' width='15' height='15' border='0' title='Itaú'>":"??*??")))); 
					?>
				  </td>
<?php //    	          <td class="texto" align="center">&nbsp;<_?php echo $FORMAS_PAGAMENTO_DESCRICAO[$rs_transacoes_row['iforma']]." (".$rs_transacoes_row['iforma'].")".(($rs_transacoes_row['cctype'])?"<br>(".$rs_transacoes_row['cctype'].")":"") ?_>&nbsp;</td>
?>
    	          <td class="texto" align="center">&nbsp;<?php echo str_replace("item:", "<font color='#3300CC'>|</font>", substr($rs_transacoes_row['cesta'],0,strlen($rs_transacoes_row['cesta'])-1))."<font color='#3300CC'>|</font>"?>&nbsp;</td>
				  <td class="texto" align="center"<?php if($rs_transacoes_row['tipo_deposito']>0) echo " title='tipo_deposito: " . $rs_transacoes_row['tipo_deposito'] . " (".$GLOBALS['TIPO_DEPOSITO_LEGENDA'][$rs_transacoes_row['tipo_deposito']]. ")'" ?>>&nbsp;<?php echo $rs_transacoes_row['tipo_deposito'] ?></td>  

				  
				  <td class="texto" align="center" style="color:<?php echo ( ( ($rs_transacoes_row['total']/100-$rs_transacoes_row['taxas'])>0 || ($rs_transacoes_row['iforma']=='Z') ) ? "#3300CC" : "#FF0000" )?><?php echo $bgcolor2?>">&nbsp;<?php echo number_format(($rs_transacoes_row['total']/100-$rs_transacoes_row['taxas']), 2, ',', '.') ?></td>  
    	          
				  <td class="texto" align="center" style="color:<?php echo (($rs_transacoes_row['taxas']>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format(($rs_transacoes_row['taxas']), 2, ',', '.')?></td>  
    	          
				  <td class="texto" align="center" title="<?php echo  (($rs_transacoes_row['status']=='1')?"Incompleto":(($rs_transacoes_row['status']=='3')?"COMPLETO":(($rs_transacoes_row['status']=='-1')?"CANCELADO":"DESCONHECIDO")) ) ?>" onMouseOver="this.className='linkover'" onMouseOut="this.className='<?php echo $sstyle; ?>'">&nbsp;<nobr><?php echo (($rs_transacoes_row['status']=='3')?"<font color='#009933'>":"<font color='#666666'>").$rs_transacoes_row['status']."</font>"?></nobr>&nbsp;</td>
<?php
	/*
		Para LHs 
							'DADOS_PAGTO_RECEBIDO' 		=> '7',
							'PAGTO_CONFIRMADO' 			=> '8'
	*/
	if($rs_transacoes_row['vg_ultimo_status']==$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
		$scolor_style1 = "";

		$scolor_style2 = "#009933";

		$stitle = $STATUS_VENDA_PAG_DESCRICAO[$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']];
	} else {
		$scolor_style1 = (($rs_transacoes_row['vg_ultimo_status']==$GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] || $rs_transacoes_row['vg_ultimo_status']==$GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] || ($rs_transacoes_row['vg_ultimo_status']=='7') || ($rs_transacoes_row['vg_ultimo_status']=='8')) ? " style='color:#FF0000;background-color:#FFFF00'" : "");

		$scolor_style2 = (($rs_transacoes_row['vg_ultimo_status']==$GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] || $rs_transacoes_row['vg_ultimo_status']==$GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] || ($rs_transacoes_row['vg_ultimo_status']=='7') || ($rs_transacoes_row['vg_ultimo_status']=='8')) ? "#009933":"");

		$stitle = (($rs_transacoes_row['vg_ultimo_status']==$GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] || $rs_transacoes_row['vg_ultimo_status']==$GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] || ($rs_transacoes_row['vg_ultimo_status']=='7') || ($rs_transacoes_row['vg_ultimo_status']=='8')) ? $STATUS_VENDA_PAG_DESCRICAO[$GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']] : $STATUS_VENDA_PAG_DESCRICAO[$rs_transacoes_row['vg_ultimo_status']]);
	}
?>
    	          <td class="texto" align="center" title="<?php echo $stitle; ?>" onMouseOver="this.className='linkover'" onMouseOut="this.className='<?php echo $sstyle; ?>'"<?php echo $scolor_style1; ?>>&nbsp;<?php echo "<font color='".$scolor_style2."'>".$rs_transacoes_row['vg_ultimo_status']."</font>" ?>&nbsp;</td>

    	          <td class="text" align="center" onMouseOver="this.className='linkover3'" onMouseOut="this.className='<?php echo $sstyle; ?>'"><nobr><?php 
					  /*
					if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
						$sonda = getTransacaoPagamentoOK("Transf", $rs_transacoes_row['numcompra'], $aline);
						$dataconfirma = "'".substr($aline[3],6,4)."-".substr($aline[3],3,2)."-".substr($aline[3],0,2)." ".$aline[4]."'";

						echo "[".(($sonda)?"<font color='#009900'>OK</font>":"<font color='#FF0000'>none</font>")."]";
						echo ( (($rs_transacoes_row['status']=='1' && $sonda) || ($rs_transacoes_row['status']=='3' && !$sonda)) ?" <font color='#FF0000'>NO SYNC<font>":"");	//." <nobr>[".$dataconfirma."]</nobr>";
					} else if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
						$sonda = getTransacaoPagamentoOK("PagtoFacil", $rs_transacoes_row['numcompra'], $aline);
						$dataconfirma = "'".substr($aline[3],6,4)."-".substr($aline[3],3,2)."-".substr($aline[3],0,2)." ".$aline[4]."'";

						echo "[".(($sonda)?"<font color='#009900'>OK</font>":"<font color='#FF0000'>none</font>")."]";
						echo (( (($rs_transacoes_row['status']=='1' && $sonda) || ($rs_transacoes_row['status']=='3' && !$sonda)))?" <font color='#FF0000'>NO SYNC<font>":"");	//." <nobr>[".$dataconfirma."]</nobr>";
					} else if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
						$sonda = getTransacaoPagamentoOK("BancodoBrasil", $rs_transacoes_row['numcompra'], $aline);
						$dataconfirma = "'".substr($aline[3],6,4)."-".substr($aline[3],3,2)."-".substr($aline[3],0,2)."'";

						echo "[".(($sonda)?"<font color='#009900'>OK</font>":"<font color='#FF0000'>none</font>")."]";
						echo (( (($rs_transacoes_row['status']=='1' && $sonda) || ($rs_transacoes_row['status']=='3' && !$sonda)))?" <font color='#FF0000'>NO SYNC<font>":"");	//." <nobr>[".$dataconfirma."]</nobr>";
//echo "<pre>";
//print_r($aline);
//echo "</pre>";
					} else if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) {
						$sonda = getTransacaoPagamentoOK("BancoItau", $rs_transacoes_row['numcompra'], $aline);
						$dataconfirma = "'".substr($aline[3],6,4)."-".substr($aline[3],3,2)."-".substr($aline[3],0,2)."'";

						echo "[".(($sonda)?"<font color='#009900'>OK</font>":"<font color='#FF0000'>none</font>")."]";
						echo (( (($rs_transacoes_row['status']=='1' && $sonda) || ($rs_transacoes_row['status']=='3' && !$sonda)))?" <font color='#FF0000'>NO SYNC<font>":"");	//." <nobr>[".$dataconfirma."]</nobr>";
					}
//$aline[0] = ""; 
//echo "<pre>";
//print_r($aline);
//echo "</pre>";
					*/

					$iforma_numeric = (($rs_transacoes_row['iforma'] == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) ? $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC : ((($rs_transacoes_row['iforma'])==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX'])?$GLOBALS['PAGAMENTO_PIX_NUMERIC']:($rs_transacoes_row['iforma']))); 
                                        if($rs_transacoes_row['iforma'] == $FORMAS_PAGAMENTO['PAGAMENTO_PIX']){
                                            $iforma_numeric = $PAGAMENTO_PIX_NUMERIC;
                                        }
					
				  ?><div style="cursor: pointer;" id='id_sonda_<?php echo $rs_transacoes_row['iforma']."_".$rs_transacoes_row['numcompra']; ?>' onclick="refresh_status_sonda('<?php echo $rs_transacoes_row['iforma'] ?>', '<?php echo $rs_transacoes_row['numcompra']; ?>', '<?php echo $rs_transacoes_row['status']; ?>', '<?php echo $rs_transacoes_row['id_transacao_itau']; ?>')" title="('<?php echo $rs_transacoes_row['iforma'] ?>', '<?php echo $rs_transacoes_row['numcompra']; ?>', '<?php echo $rs_transacoes_row['status']; ?>', '<?php echo $rs_transacoes_row['id_transacao_itau']; ?>')">(Sonda)</div><?php //echo "(".$iforma_numeric."_".$rs_transacoes_row['numcompra'].")"; ?></nobr><?php //if(!$sonda && ($aline[6]!='000000') && (count($aline)>=6) && ($rs_transacoes_row['status']!='1') && (($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']))) echo "<br>Erro: <b>".$aline[6]."<br>" ?>
				  
				  </td>

			<?php if($tf_com_total_venda) { ?>
				  <td class="texto l" align="center">&nbsp;<?php echo number_format(($rs_transacoes_row['vg_valor']), 2, ',', '.')?></td>  
				  <td class="texto" align="center">&nbsp;<?php echo $rs_transacoes_row['vg_qtde_produtos'] ?></td>  
				  <td class="texto" align="center">&nbsp;<?php echo $rs_transacoes_row['vg_qtde_itens'] ?></td>  
			<?php } ?>

    	        </tr>
		<?php
				}

//if(b_IsUsuarioReinaldo()) echo "Elapsed time (A8): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

//if($bDebug) echo "Elapsed time (B4): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."<br>";

				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="12">&nbsp;<font color='#FF0000'>Não foram encontrados registros para os valores escolhidos (2)</font></td>
					</tr>
			<?php
				} else {
			?>
					<tr>
					  <td class="texto" align="right" colspan="7">Subtotal&nbsp;<font color='#FF0000'></font></td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagamentos_page>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format($total_pagamentos_page, 2, ',', '.')?>&nbsp;<font color='#FF0000'></font></td>
					  <td class="texto" align="center" style="color:<?php echo (($total_taxas_page>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format($total_taxas_page, 2, ',', '.')?>&nbsp;<font color='#FF0000'></font></td>
					  <td class="texto" align="center" colspan="3">&nbsp;<font color='#FF0000'></font></td>
					</tr>
					<tr>
					  <td class="texto" align="right" colspan="7">Total&nbsp;<font color='#FF0000'></font></td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagamentos>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format($total_pagamentos, 2, ',', '.')?>&nbsp;<font color='#FF0000'></font></td>
					  <td class="texto" align="center" style="color:<?php echo (($total_taxas>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format($total_taxas, 2, ',', '.')?>&nbsp;<font color='#FF0000'></font></td>
					  <td class="texto" align="center" colspan="3">&nbsp;<font color='#FF0000'></font></td>
					</tr>
			<?php

				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="7">&nbsp;<font color='#FF0000'>Não foram encontrados registros para os valores escolhidos</font></td>
    	        </tr>
		<?php
			}
		?>
			</table>
      </td>
    </tr>
	</table>
	<hr>
	<?php
		if($tf_v_canal=="M") {
			$s_titulo = "Money";
		} elseif($tf_v_canal=="E") {
			$s_titulo = "ExpressMoney";
		} elseif($tf_v_canal=="LR") {
			$s_titulo = "Lanhouse Pré";
		} elseif($tf_v_canal=="LO") {
			$s_titulo = "Lanhouse Pós";
		} else {
			$s_titulo = "Tipo de usuário desconhecido";
		}

//			$s_new = array(
//					array('nvendas' => 0, 'emails' => '', 'nusers' => 0),
//					);
if(b_IsUsuarioReinaldo()) {
//	echo print_r($s_new,true);
//	echo serialize($s_new)."<br>";
//	foreach($s_new as $key => $val) { 
//		echo $val['nvendas']." -> ".$val['nusuarios']."<br>";
//	}
//	echo "<hr>";
}


		function cmp_by_nvendas($a_arr, $b_arr) {
			$a = $a_arr['nvendas']; //$au = $a_arr['nusuarios'];
			$b = $b_arr['nvendas']; //$bu = $b_arr['nusuarios'];
			if ($a == $b) return 0;
			return ($a < $b) ? -1 : 1;
		}
                if(is_array($s_new)) {
                    usort($s_new, "cmp_by_nvendas");
                }

/*
if(b_IsUsuarioReinaldo()) {
//	echo print_r($s_new,true);
//	foreach($s_new as $key => $val) { 
//		echo $val['nvendas']." -> ".$val['nusuarios']."<br>";
//	}
//	echo "<hr>";
}
*/
//echo "<pre>".print_r($s_new,true)."</pre>\n";

		$n_vendas_0 = $s_new[0]['nusuarios'];
		$n_vendas_total = 0;
                if(is_array($s_new)) {
                    foreach($s_new as $key => $val) { 
    //			echo $key." - <pre>".print_r($val,true)."</pre>\n";
                            $n_vendas_total += $val['nusuarios'];
                    }
                }
		if($n_vendas_total==0) $n_vendas_total = 1;
//echo "0/Total = $n_vendas_0 / $n_vendas_total<br>";
	?>

    <input type="button" class="btn btn-sm btn-info" id="but_usuarios_show" value="Mostra Lista de usuários" onclick="$('#div_usuarios').show();$('#but_usuarios_show').hide();">

	<div id="div_usuarios" style="display:none;">
	<input type="button" id="div_usuarios_hide" value="Oculta Lista de usuários" onclick="$('#div_usuarios').hide(); $('#but_usuarios_show').show();">
	<p>Lista de emails de usuários (<b class="bold_blue"><?php echo $s_titulo; ?></b>) agrupados por número de vendas completas no intervalo <br>(<?php echo "'".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' - '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'"?>) <br>Usuários sem venda completa: <?php echo $n_vendas_0 ?> de <?php echo $n_vendas_total ?> (<b class="bold_blue"><?php echo number_format((100*$n_vendas_0 / $n_vendas_total), 2, ',', '.') ?>%</b>)</p>

<?php
/*
if(b_IsUsuarioReinaldo()) { 
	echo "<table border='1'>\n";
	foreach($s_new as $key => $val) {
//echo "<pre>".print_r($_POST, true)."</pre>";
		echo "<tr><td>".$val['nvendas']."</td> <td>".$val['nusuarios'] ."</td></tr>\n";
	}
	echo "</table>\n";
}
*/
?>
    <table class=" table">
			<tr>
				<td align="center" class="texto"><b>NVendas</b></td>
				<td align="center" class="texto"><b>NUsuários</b></td>
				<td align="center" class="texto"><b>Lista de usuários</b></td>
			</tr>
			<?php 
				$bgcolor = "F5F5FB";
//				$shisto = "";
				$n_usuarios_total = 0;
				$n_usuarios_p = 0;
                                if(is_array($s_new)) {
                                    foreach($s_new as $key => $val) {
    //					$shisto .= $val['nvendas']."\t".$val['nusuarios']."<br>\n";
                                            if(strlen($val['emails'])>0) {
                                                    $bgcolor = ($bgcolor=="F5F5FB")?"FFFFFF":"F5F5FB";
                            ?>
                            <tr bgcolor='<?php echo $bgcolor?>' valign="top">
                                    <td align="center" class="texto"><?php echo $val['nvendas'] ?>&nbsp;venda<?php echo ($val['nvendas']==1)?"":"s" ?>&nbsp;completa<?php echo ($val['nvendas']==1)?"":"s" ?></td>
                                    <td align="center" class="texto"><?php echo $val['nusuarios'] ?>&nbsp;usuário<?php echo ($val['nusuarios']==1)?"":"s" ?></td>
                                    <td align="center" class="texto"><?php echo "<font".((1*($val['nvendas'])<1)?" color='blue'":"").">".$val['emails']."</font>" ?></td>
                            </tr>
                            <?php	
                                                    $n_usuarios_total += $val['nusuarios'];
                                                    if($key>1) {
                                                            $n_usuarios_p += $val['nusuarios'];	
                                                    }
                                            }
                                    }
                                }
				$n_usuarios_0 = $s_new[0]['nusuarios'];
				$n_usuarios_1 = $s_new[1]['nusuarios'];
			?>
	</table>
	</div>

<?php	
//if(b_IsUsuarioReinaldo()) 
{
?>
	&nbsp;<br>
	<input type="button" class="btn btn-sm btn-info" id="but_histogram_show" value="Mostra Histograma" onclick="$('#div_histogram').show();$('#but_histogram_show').hide();">

	<div id="div_histogram" style="display:none;">
	<input type="button" class="btn btn-sm btn-info" id="div_histogram_hide" value="Oculta Histograma" onclick="$('#div_histogram').hide(); $('#but_histogram_show').show();">

<?php
		$a_values = array();
                if(is_array($s_new)) {
                    foreach($s_new as $key => $val) {
                            $valtmp = array();
                            $valtmp['nvendas'] = $val['nvendas'];
                            $valtmp['nusuarios'] = $val['nusuarios'];
                            $a_values[] = $valtmp;
                    }
                }

//	echo "<hr color='red'>";
//	echo "<pre>".print_r($s_new, true)."</pre>";
//	echo $shisto;
//	echo "<pre>".print_r($a_values, true)."</pre>";
//	echo "<hr color='red' width='3'>";
$s_values_graph = serialize($a_values);
//echo "<font style='color:darkgreen'>$s_values_graph</font><br>";

?>
<script language="JavaScript" type="text/JavaScript">
    show_graph('<?php echo $s_values_graph ?>');
	
	
</SCRIPT>

	<p style="background-color:#ccffcc"><div id="graph">*</div></p>
<?php
}	
?>
	</div>

<?php
			?>
	&nbsp;<br>
	<?php 
		if($tf_v_canal=="M") {
	?>
	<input type="button" class="btn btn-sm btn-info" id="but_emails_show" value="Mostra Lista de emails" onclick="$('#div_emails').show();$('#but_emails_show').hide();">

	<div id="div_emails" style="display:none;">
	<input type="button" class="btn btn-sm btn-info" id="div_emails_hide" value="Oculta Lista de emails" onclick="$('#div_emails').hide(); $('#but_emails_show').show();">

	<?php 
		sort($s_normais);
		$n_normais = (count($s_normais))?count($s_normais):1;
		$n_normais_total = (count($s_normais_todos))?count($s_normais_todos):1;
	?>
    <table class=" table">
    <tr>
      	<td align="center" class="texto">
			<p><b>Lista de emails de usuários (sem Integração) que não completaram o pagamento (<?php echo $n_normais?> usuários de <?php echo $n_normais_total?>, <b class="bold_blue"><?php echo number_format((100*$n_normais / $n_normais_total), 2, ',', '.') ?>%</b> )</b></p>
			<p><font color='blue'>
			<?php
				foreach($s_normais as $key => $val) {
					echo $val.", ";
				}	
			?></font>
			</p>
      	</td>
    </tr>
	</table>

	<?php 
		sort($s_integracao);
		$n_integracao = (count($s_integracao))?count($s_integracao):1;
		$n_integracao_total = (count($s_integracao_todos))?count($s_integracao_todos):1;
	?>
    <table class=" table">
    <tr>
      	<td align="center" class="texto">
			<p><b>Lista de emails de usuários de INTEGRAÇÃO que não completaram o pagamento (<?php echo $n_integracao?> usuários de <?php echo $n_integracao_total?>, <b class="bold_blue"><?php echo number_format((100*$n_integracao / $n_integracao_total), 2, ',', '.') ?>%</b> )</b></p>
			<p><font color='blue'>
			<?php
				foreach($s_integracao as $key => $val) {
					echo $val.", ";
				}	
			?></font>
			</p>
      	</td>
    </tr>
	</table>
	
	</div>
	<?php 
		} 
	?>
	<center>
	<input type="button" class="btn btn-sm btn-info" id="but_parametros_show" value="Mostra Vendas incompletas/completas" onclick="$('#div_parametros').show();$('#but_parametros_show').hide();">

	<div id="div_parametros" style="display:none;">
	<input type="button" class="btn btn-sm btn-info" id="div_parametros_hide" value="Oculta Vendas incompletas/completas" onclick="$('#div_parametros').hide(); $('#but_parametros_show').show();">

	<p><b>Parámetros vendas que não completaram o pagamento (total de <?php echo $n_usuarios_total?> usuários)</b></p>
    <table class=" table">
	    <tr><td align="center" class="texto"><b>Grupo</b></td> <td align="center" class="texto"><b>n_usuários</b></td> <td align="center" class="texto"><b>%</b></td></tr>
	    <tr><td align="center" class="texto">0 vendas</td> <td align="center" class="texto"><?php echo $n_usuarios_0?></td> <td align="center" class="texto"><?php echo (!empty($n_usuarios_total)?number_format((100*($n_usuarios_0/$n_usuarios_total)), 2, ',', '.'):""); ?></td></tr>
	    <tr><td align="center" class="texto">1 venda</td> <td align="center" class="texto"><?php echo $n_usuarios_1?></td> <td align="center" class="texto"><?php echo (!empty($n_usuarios_total)?number_format((100*($n_usuarios_1/$n_usuarios_total)), 2, ',', '.'):""); ?></td></tr>
	    <tr><td align="center" class="texto">>1 venda</td> <td align="center" class="texto"><?php echo $n_usuarios_p?></td> <td align="center" class="texto"><?php echo (!empty($n_usuarios_total)?number_format((100*($n_usuarios_p/$n_usuarios_total)), 2, ',', '.'):""); ?></td></tr>
	    <tr><td align="center" class="texto">Total</td> <td align="center" class="texto"><?php echo $n_usuarios_total?></td> <td align="center" class="texto">&nbsp;</td></tr>
	</table>
	</div>

	</center>

	<br>&nbsp;
    <table class=" table">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<?php if($p > 1){ ?>
         	<input type="button" name="btPrimeiro" value=" << " OnClick="window.location='?p=<?php echo 1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
      		<?php 
//if(b_IsUsuarioReinaldo()) 
{ 
				$tot_p = (int)(($registros_total+$registros-1)/$registros);

				if($tot_p>3){ 
					echo "<select id='bt_goto_pag' name='bt_goto_pag'>\n";
					for($i=1;$i<=$tot_p;$i++) {
						echo "<option value='$i'".(($p==$i)?" selected":"").">Pág. $i de $tot_p</option>\n";
					}
					echo "</select>\n";
					echo "&nbsp;\n";
					echo "<input type='button' name='btPag' value=' Vai para página ' OnClick='window.location=\"?p=\"+document.form1.bt_goto_pag.value+\"".$varsel."\";'  class=\"btn btn-sm btn-info\">\n";
				} 
}
			?>
         	
      		<?php if($p < $tot_p){ ?>
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';"  class="btn btn-sm btn-info">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btUltimo" value=" >> " OnClick="window.location='?p=<?php echo $tot_p?><?php echo $varsel?>';"  class="btn btn-sm btn-info">
			<?php } ?>
			</nobr>
      	</td>
    </tr>
	</table>
	<br>&nbsp;

    <table class=" table">	
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto">&nbsp;</td>
	  </tr>
	</table>

	</div>
	</center>
	</form>

<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
<?php
	function is_csv_numeric($list) {
		$list1 = str_replace(" ", "", $list);
		$alist = explode(",", $list1);
		$bret = true;
		foreach($alist as $key => $val) {
			$bret = is_numeric($val);
			if(!$bret) {
				break;
			}
		}
		return $bret;
	}
?>
