<?php 

$run_silently = 1;
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

?>

<br>
		<table width="607" border="0" align="center" bgcolor="#BBBBBB" class="texto">
<?php 


header("Content-Type: text/html; charset=ISO-8859-1",true); 
$id_lan = $_GET['id_lan'];
$bal_id = $_GET['bal_id'];
$query = "select * from dist_balancos where db_id = '$bal_id' and db_ug_id = '$id_lan' ";
$res = SQLexecuteQuery($query);
	if ( $info_balanco = pg_fetch_array($res)) {
	$id_lan = $info_balanco['db_ug_id'];
	$qtde_boletos = $info_balanco['db_qtde_boletos'];
	$qtde_cortes = $info_balanco['db_qtde_cortes'];
	$qtde_pagonline = $info_balanco['db_qtde_pagonline'];
	$qtde_vendas = $info_balanco['db_qtde_vendas'];
	$saldo_balanco = number_format($info_balanco['db_valor_balanco'],2,',','.');
	$val_boletos = number_format($info_balanco['db_val_boletos'],2,',','.');
	$val_cortes = number_format($info_balanco['db_val_cortes'],2,',','.');
	$val_pagonline = number_format($info_balanco['db_val_pagonline'],2,',','.');
	$val_vendas = number_format($info_balanco['db_val_vendas'],2,',','.');
	$data_balanco = $info_balanco['db_data_balanco'];
	$resultado = $info_balanco['db_resultado'];

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////// Resgatando o ponto anterior a este para posteriormente exibir a lista de vendas e pagamento entre os balanços /////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$query = "select * from dist_balancos where db_ug_id = '$id_lan' and db_data_balanco < '$data_balanco' order by db_data_balanco desc limit 1";
	$res = SQLexecuteQuery($query);
	if ( $info_ponto_inicial =  pg_fetch_array($res)) {
		$data_ponto_inicial = $info_ponto_inicial['db_data_balanco'];
		$saldo_anterior = number_format($info_ponto_inicial['db_valor_balanco'],2,',','.');
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////// Carregando a Lista De itens para exibição da somatória ////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$query = "select num_doc, tipo_pagto, data, valor, sum(valor - repasse) as comissao, repasse, tipo, status from (

		(select (vg.vg_id::text) as num_doc,
		 vg.vg_data_inclusao as data,
		 vg.vg_pagto_tipo as tipo_pagto ,
		 sum(vgm.vgm_valor * vgm.vgm_qtde) as valor ,
		 sum(vgm.vgm_qtde) as qtde_itens,
		 count(*) as qtde_produtos,
		 sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse ,
		 'Venda' as tipo,
		 '' as status

		from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_ug_id= ".$id_lan." 
		 and vg.vg_data_inclusao between '".$data_ponto_inicial."' and '".$data_balanco."' group by num_doc , data , tipo_pagto , vg.vg_ultimo_status, vg.vg_usuario_obs order by vg.vg_data_inclusao )

		union all


		(select (bol_documento::text) as num_doc,
		bol_importacao as data ,
		vg_pagto_tipo as tipo_pagto ,
		sum (bol_valor - bbg_valor_taxa) as valor ,
		NULL as qtde_itens,
		NULL as qtde_produtos ,
		NULL as repasse ,
		'Boleto' as tipo,
		'Pre' as status 

		from boletos_pendentes, bancos_financeiros, tb_dist_venda_games, dist_boleto_bancario_games where (bol_banco = bco_codigo) and (bol_venda_games_id=vg_id) and (bco_rpp = 1) and vg_ug_id = ".$id_lan." and bol_documento LIKE '4%' and bbg_ug_id = ".$id_lan." and bbg_vg_id = vg_id and bol_valor > 0 and bol_aprovado_data between '".$data_ponto_inicial."' and '".$data_balanco."'

		group by bol_documento,vg_data_inclusao,vg_pagto_tipo,bol_importacao order by bol_importacao  )

		union all 

		(select 
		(bbc_documento::text) as  num_doc,
		bbc_data_inclusao as data ,
		cor_tipo_pagto as  tipo_pagto , 
		cor_venda_liquida as  valor,
		NULL as qtde_itens,
		NULL as qtde_produtos,
		NULL as repasse,
		'Corte' as tipo,
		'Pos' as status
		from cortes c inner join boleto_bancario_cortes as bbc on cor_bbc_boleto_codigo = bbc_boleto_codigo where c.cor_ug_id = ".$id_lan." and c.cor_venda_liquida > 0	and c.cor_status=3 and bbc_data_inclusao between '".$data_ponto_inicial."' and  '".$data_balanco."' order by cor_data_concilia 
		)

		union all 

		(select numcompra::text as num_doc,
		datainicio as data,  
		(case when iforma='A' then 10 else iforma::int end ) as tipo_pagto,  
		sum (total/100 - taxas) as valor, 
		NULL as qtde_itens, 
		NULL as qtde_produtos , 
		NULL as repasse , 
		'BoletoPagtoOnline' as tipo, 
		(case when tipo_cliente='LR' then 'Pre' when tipo_cliente='LO' then 'Pos' else '???' end) as status 

		from tb_pag_compras
		where substr(tipo_cliente,1,1)='L' and idcliente=".$id_lan." and status=3  and datainicio between '".$data_ponto_inicial."' and  '".$data_balanco."' group by numcompra::text, datainicio, tipo_pagto, tipo_cliente order by data)

		union all 

		( 
		select 
		(db_id::text) as num_doc,
		db_data_balanco as data ,
		NULL as tipo_pagto,
		db_valor_balanco as valor,
		NULL as qtde_itens,
		NULL as qtde_produtos,
		NULL as repasse,
		'Balanco' as tipo,
		(db_tipo_lan::text) as status 
		from dist_balancos where db_ug_id = ".$id_lan."  and db_data_balanco between '".$data_ponto_inicial."' and '".$data_balanco."' group by db_id,status ,data,valor order by db_data_balanco 

		)

		) as venda

		group by venda.num_doc,venda.tipo_pagto,venda.data,venda.valor,tipo,repasse,status order by data ";

		$res = SQLexecuteQuery($query);

		//////////print $query;

		// 5 È um ponto Inicial
		// 4 resultado falha na contagem do limite
		// 3 resultado ok o limite esta correto
		// 2 resultado falha na contagem do saldo
		// 1 saldo correto

		if ($resultado == 1) {
			$msg = "Não houve diferenças no saldo registrado e o valor calculado";
			//$cor = "#00AA00";
		}

		if ($resultado == 2) {
			$msg = "Houve diferenças entre a contagem do Saldo e o valor que está registrando, alguma movimentação foi realizada sem registro no sistema";
			$cor = "#AA0000";
		}

		if ($resultado == 3) {
			$msg = "Não houve diferenças no Limite/Saldo registrado e o valor calculado";
			//$cor = "#00AA00";
		}

		if ($resultado == 4) {
			$msg = "Houve diferença entre a contagem de Limite/Saldo e o valor que está registrando, alguma movimentação foi realizada sem registro no sistema";
			$cor = "#AA0000";
		}

		if ($resultado == '') {
			$msg = "Não há dados suficientes para especificar a falha";
			$cor = "#AA0000";
		}
		?>
		
		<tr>
			<td width="143" bgcolor="#EEEEEE" ><strong>Informa&ccedil;&atilde;o:</strong></td>
			<td colspan="3" bgcolor="#F5F5FB" ><font color="<?php echo $cor ?>">&nbsp;<?php echo $msg; ?></font></td>
		</tr>
		<tr>
			<td bgcolor="#EEEEEE"><div align="left"><strong>Saldo do Balanco</strong></div></td>
			<td colspan="3" bgcolor="#F5F5FB">&nbsp;<?php echo $saldo_balanco?></td>
		</tr>
		 <tr>
			 <td bgcolor="#EEEEEE"><div align="left"><strong>Saldo Anterior</strong></div></td>
			 <td colspan="3" bgcolor="#F5F5FB">&nbsp;<?php echo $saldo_anterior?></td>
		</tr>
		<tr>
			 <td bgcolor="#EEEEEE"><div align="left"><strong>Total Boletos</strong></div></td>
			 <td bgcolor="#F5F5FB"><div align="left">&nbsp;<? echo $val_boletos ?></div></td>
			 <td bgcolor="#EEEEEE"><div align="left">Qtde</div></td>
			 <td width="179" bgcolor="#F5F5FB"><div align="left">&nbsp;<? echo $qtde_boletos ?></div></td>
		</tr>
		<tr>
			 <td bgcolor="#EEEEEE"><div align="left"><strong>Total Cortes</strong></div></td>
			 <td bgcolor="#F5F5FB"><div align="left">&nbsp;<? echo $val_cortes ?></div></td>
			 <td bgcolor="#EEEEEE"><div align="left">Qtde</div></td>
			 <td bgcolor="#F5F5FB"><div align="left">&nbsp;<? echo $qtde_cortes ?></div></td>
		</tr>
	   <tr>
			 <td bgcolor="#EEEEEE"><div align="left"><strong>Total Vendas</strong></div></td>
			 <td width="157" bgcolor="#F5F5FB"><div align="left">&nbsp;<? echo $val_vendas ?></div></td>
			 <td width="110" bgcolor="#EEEEEE"><div align="left">Qtde</div></td>
			 <td bgcolor="#F5F5FB"><div align="left">&nbsp;<? echo $qtde_vendas ?></div></td>
		</tr>
	   <tr>
			 <td bgcolor="#EEEEEE"><div align="left"><strong>Total Pagto Online</strong></div></td>
			 <td bgcolor="#F5F5FB"><div align="left">&nbsp;<? echo $val_pagonline ?></div></td>
			 <td bgcolor="#EEEEEE"><div align="left">Qtde</div></td>
			 <td bgcolor="#F5F5FB"><div align="left">&nbsp;<? echo $qtde_pagonline ?></div></td>
		</tr>
		<tr>
			 <td bgcolor="#EEEEEE">&nbsp;</td>
			 <td colspan="3" bgcolor="#F5F5FB">&nbsp;</td>
		</tr>
		<tr>
			 <td colspan="4" bgcolor="#EEEEEE">Rela&ccedil;&atilde;o de Itens Registrados:</td>
		</tr>
	   <tr>
		 <td bgcolor="#EEEEEE">Tipo</td>
		 <td bgcolor="#EEEEEE">Data</td>
		 <td bgcolor="#EEEEEE">Valor</td>
		 <td bgcolor="#EEEEEE">A&ccedil;&atilde;o</td>
	   </tr>
	   <?php
		 //// INICIALMEMTE CARREGA O VALOR DO BALANÇO MAIS MODERNO ANTES DO QUE ESTA SENDO TESTADO
		 $valor_conta = $info_ponto_inicial['db_valor_balanco'];
		 //// VALOR NOVO = VARIAVEL QUE IRÀ GUARDA O RESULTADO ENTRE O VALOR ANTIGO COM UMA OPERAÇÃO DO VALOR DO ITEM DA LISTA
		 $valor_novo = 0;

		// print $res;
		 
		 while ( $info_lista = pg_fetch_array($res)) {

			 $tipo_item_lista = $info_lista['tipo'];
			 $data_item_lista = formata_data($info_lista['data'],0);
				 if ($tipo_item_lista != 'Balanco') {	 	 
					 if ($tipo_item_lista == 'Venda' ) {
						 $valor_item = $info_lista['repasse'];
						 $valor_item_lista_view = number_format($info_lista['repasse'],2,',','.');
						 $valor_novo =  $valor_conta - $valor_item;
						 $acao = "-";
						 $bgcolor = '#BCBCBC';
					 } else {
						 $valor_item = $info_lista['valor'];
						 $valor_item_lista_view = number_format($info_lista['valor'],2,',','.');
						 $valor_novo =  $valor_conta + $valor_item;
						 $acao = "+";
						 $bgcolor = '#FFFFFF';
					 }
						 
					 $valor_novo_view = number_format($valor_novo,2,',','.');
					 $valor_conta_view = number_format($valor_conta,2,',','.');
				
					 ?>
		   <tr>
			 <td bgcolor="<?php echo $bgcolor?>">&nbsp;<?php echo $tipo_item_lista ?></td>
			 <td bgcolor="<?php echo $bgcolor?>">&nbsp;<?php echo $data_item_lista ?></td>
			 <td bgcolor="<?php echo $bgcolor?>">&nbsp;<?php echo $valor_item_lista_view?></td>
			 <td bgcolor="<?php echo $bgcolor?>">&nbsp;<?php echo $valor_conta_view ." ".$acao." ".$valor_item_lista_view." = ".$valor_novo_view; ?></td>
		   </tr>
		 <?php
				 } else {
						
					$valor_item = $info_lista['valor'];
					$valor_item_lista_view = number_format($info_lista['valor'],2,',','.');
					$valor_novo =  $valor_conta ;
					$valor_novo_view = number_format($valor_novo,2,',','.');
					$valor_conta_view = number_format($valor_conta,2,',','.');
					$bgcolor = '#FFFF99';
					$acao = "-";
		?>
			 <tr>
			 <td bgcolor="<?php echo $bgcolor?>">&nbsp;<?php echo $tipo_item_lista ?></td>
			 <td bgcolor="<?php echo $bgcolor?>">&nbsp;<?php echo $data_item_lista ?></td>
			 <td bgcolor="<?php echo $bgcolor?>">&nbsp;<?php echo $valor_item_lista_view?></td>
			 <td bgcolor="<?php echo $bgcolor?>">&nbsp;<?php echo $valor_conta_view; ?></td>

		   <?php
				 } 
			$valor_conta = $valor_novo;
			}
				 
			$valor_diferente = number_format($valor_item - $valor_conta,2,',','.');

			if ($valor_diferente == 0 ) {
				$color = "#009900";
			} else {
				$color = "#BB0000";
			}
			?>
	</tr>
		 <tr bgcolor="#EEEEEE">
		   <td colspan="2" ><strong>Diferen&ccedil;a n&atilde;o registrada:</strong></td>
		   <td colspan="2" ><font color='<?php echo $color ?>'>&nbsp;<?php echo $valor_diferente ?></font></td>
		 </tr>
		 <?php } else { ?>
		<tr bgcolor="#EEEEEE">
	    <td colspan="4" ><strong><font color="#DD0000">Não Foi possível encontrar o ponto Anterior deste Balanço</font></strong></td>
		</tr>
		<?php
			
		//	die($query);
			
			} ?>
 <?php } else {?>
 <tr bgcolor="#EEEEEE">
	   <td colspan="4" ><strong><font color="#DD0000">Registro de Balanco Invalido</font></strong></td>
 </tr>

 <?php } // FIM DO IF SELECT BALANCO ID  ?> 
</table>
			<table border="0" cellspacing="0" align="center" width="100%">
            <tr valign="middle" bgcolor="#FFFFFF">
              	<td align="left" class="texto"><table border="0" cellspacing="1" width="100%">
		            <tr><td colspan="2">&nbsp;</td></tr>
	                <tr>
	                    <td colspan="2" align="center">
	                    	<input type="button" name="btVoltar" value="Voltar" OnClick="fecha(<?php echo $bal_id?>,'<?php echo $corAntiga?>');" class="botao_simples">
	                    </td>
	                </tr>
		            <tr><td colspan="2">&nbsp;</td></tr>
                	</table>
           	  </td>
            </tr>
        	</table>
<?php //include "../../incs/rodape_bko.php"; ?>