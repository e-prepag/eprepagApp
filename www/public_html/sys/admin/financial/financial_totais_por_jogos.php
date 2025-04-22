<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
session_start();
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

require_once $raiz_do_projeto . "includes/sys/inc_stats.php";
$time_start_stats = getmicrotime();

//canais para montagem de grade inicial da tabela com os totais
$canais_grade = array('P','L','E','M','C','A');

//legenda para os canais
$canais_grade_legenda = array(
								'P' => 'POS',
								'L' => 'LAN House',
								'E' => 'Express',
								'M' => 'Money',
								'C' => 'Cartões',
								'A' => 'ATIMO'
							);

//Produtos
if ($tf_produto && is_array($tf_produto)) {
	if (count($tf_produto) == 1) $tf_produto_aux = $tf_produto[0];
	else $tf_produto_aux = implode("','",$tf_produto);
}

//Valores
if ($tf_pins && is_array($tf_pins)) {
	if (count($tf_pins) == 1) $tf_pins_aux = $tf_pins[0];
	else $tf_pins_aux = implode(",",$tf_pins);
}

//Inicializando as datas
if(empty($tf_data_inicial)) {
	$tf_data_inicial	= date('d/m/Y',mktime(0, 0, 0, (date('m')-1),  date('d'), date('Y')));
}//end if(empty($tf_data_inicial))
if(empty($tf_data_final)) {
	$tf_data_final		=  date('d/m/Y');
}//end if(empty($tf_data_final))

function dataLegenda($this_date){
	$data_inicial_aux  = explode('-',$this_date);
	$meses = array("", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");
	return $meses[($data_inicial_aux[1]*1)]."/".$data_inicial_aux[0];
}



//Operadoras / Produtos / Valores
if($_SESSION["tipo_acesso_pub"]=='PU') {
        $tf_opr_codigo = $_SESSION["opr_codigo_pub"];
}

$sql = "select * from operadoras ope where opr_status = '1' order by opr_nome"; //".($tf_opr_codigo?" and opr_codigo = ".$tf_opr_codigo:"")."
$rs_operadoras = SQLexecuteQuery($sql);

if($tf_opr_codigo) {

	$sql = "
	select ogp_nome as ogp_nome_aux
	from (
		(select ogp_nome from tb_dist_operadora_games_produto inner join tb_dist_operadora_games_produto_modelo on (ogpm_ogp_id=ogp_id) where ogp_opr_codigo = " . $tf_opr_codigo . " group by ogp_nome
	)
	union all (
		select ogp_nome from tb_operadora_games_produto inner join tb_operadora_games_produto_modelo on (ogpm_ogp_id=ogp_id) where ogp_opr_codigo = " . $tf_opr_codigo . " group by ogp_nome
	)
	union all (
		select ogp_nome from tb_pos_operadora_games_produto where ogp_opr_codigo = " . $tf_opr_codigo . " group by ogp_nome
	)
	) as jogos
	group by ogp_nome_aux
	order by ogp_nome_aux";
	$rs_oprProdutos = SQLexecuteQuery($sql);
	$sql = "select pin_valor from pins where opr_codigo = " . $tf_opr_codigo . " group by pin_valor order by pin_valor;";
	$rs_oprPins = SQLexecuteQuery($sql);
}

$descricao = new DescriptionReport('totais_jogos');
echo $descricao->MontaAreaDescricao();

?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> <?php echo LANG_STATISTICS_TOTAL_SALES; ?> </title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<link href="/sys/css/css.css" rel="stylesheet" type="text/css">
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
    <script language="javascript">
    $(document).ready(function () {
        var optDate = new Object();
        optDate.interval = 3;

        setDateInterval('tf_data_inicial','tf_data_final',optDate);
        
        //Ao selecionar a operadora
        $('#tf_opr_codigo').change(function(){
            var id = $(this).val();

            $.ajax({
                type: "POST",
                url: "/ajax/gamer/ajaxProdutoComPesquisaVendas.php",
                data: "id="+id,
                beforeSend: function(){
                        $('#mostraProdutos').html("Aguarde...");
                },
                success: function(html){
                        //alert('produto');
                        $('#mostraProdutos').html(html);
                },
                error: function(){
                        alert('erro produto');
                }
            });

            $.ajax({
                type: "POST",
                url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
                data: "id="+id,
                beforeSend: function(){
                        $('#mostraValores').html("Aguarde...");
                },
                success: function(html){
                        //alert('valor');
                        $('#mostraValores').html(html);
                },
                error: function(){
                        alert('erro valor');
                }
            });
        });
    });
    </script>
</head>
<body>

<?php
    $bg_col_01 = "#FFFFFF";
    $bg_col_02 = "#EEEEEE";
    $bg_col = $bg_col_01;

    $sqlopr = "select opr_nome as ogp_nome_aux, opr_codigo from operadoras where (opr_status = '1') ".($tf_opr_codigo?" and opr_codigo = ".$tf_opr_codigo:"")." order by opr_ordem";
    $resopr = SQLexecuteQuery($sqlopr);
?>
<br>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong>Totais de Vendas Por Jogos (<?php echo get_current_date();?>)</strong>
                    </div>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-offset-6 col-md-6">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <form name="form1" method="post" action="">
                <div class="row txt-cinza espacamento">
                    <div class="col-md-2">
                        <span class="pull-right">Data Início</span>
                    </div>
                    <div class="col-md-3">
                        <input name="tf_data_inicial" type="text" class="form-control" id="tf_data_inicial" value="<?php echo $tf_data_inicial ?>" size="9" maxlength="10">
                    </div>
                    <div class="col-md-2">
                        <span class="pull-right">Data Fim</span>
                    </div>
                    <div class="col-md-3">
                       <input name="tf_data_final" type="text" class="form-control" id="tf_data_final" value="<?php echo $tf_data_final ?>" size="9" maxlength="10">
                    </div>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-2">
                        <span class="pull-right">Operadora</span>
                    </div>
                    <div class="col-md-3">
<?php
                    if($_SESSION["tipo_acesso_pub"]!='PU') {                           
?>
                        <select name="tf_opr_codigo" id="tf_opr_codigo" class="form-control">
                            <option value="" <?php if($tf_opr_codigo == "") echo "selected" ?>>Todos</option>
<?php 
                            if($rs_operadoras) {
                                while($rs_operadoras_row = pg_fetch_array($rs_operadoras))
                                {
                                    $opr_codigo_matriz[]=$rs_operadoras_row['opr_codigo'];
                                    $opr_codigo_matriz_legenda[$rs_operadoras_row['opr_codigo']]=$rs_operadoras_row['opr_nome'];
?>
                                    <option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>"
<?php 
                                    if ($tf_opr_codigo == $rs_operadoras_row['opr_codigo'] || $rs_operadoras_row['opr_codigo'] == $buscaOper) 
                                        echo " selected";
                                    ?>><?php echo $rs_operadoras_row['opr_nome']; ?></option>
<?php 
                                } 
                            }
?>
                        </select>
<?php
                    }//end  if($_SESSION["tipo_acesso_pub"]!='PU')  
                    else {
                        $rs_operadoras_row = pg_fetch_array($rs_operadoras);
                        echo $rs_operadoras_row['opr_nome'];
                    }
?>
                    </div>
                    <div class="col-md-2">
                        <span class="pull-right">Valores</span>
                    </div>
                    <div class="col-md-3 borda-fina">
                        <span class="text-left fontsize-p" id='mostraValores'>
<?php 
                    if($rs_oprPins)
                        while($rs_oprPins_row = pg_fetch_array($rs_oprPins))
                        { 
?>
                            <input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="<?php echo $rs_oprPins_row['pin_valor']; ?>" 
<?php
                            if ($tf_pins && is_array($tf_pins))
                                if (in_array($rs_oprPins_row['pin_valor'], $tf_pins)) 
                                    echo " checked";
                                else
                                    if ($rs_oprPins_row['pin_valor'] == $tf_pins)
                                    echo " checked";
				?>><?php echo $rs_oprPins_row['pin_valor'] . ",00"; ?>&nbsp;
<?php
                        }//end while 
?>
                        </span>
                    </div>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-2">
                        <span class="pull-right">Produtos</span>
                    </div>
                    <div class="col-md-8 borda-fina">
                        <span class="text-left fontsize-p"  id='mostraProdutos'>
                        
<?php 
                    if($rs_oprProdutos)
                        while($rs_oprProdutos_row = pg_fetch_array($rs_oprProdutos))
                        { 
?>
                            <input type="checkbox" id="tf_produto[]" name="tf_produto[]" value="<?php echo $rs_oprProdutos_row['ogp_nome_aux']; ?>" 
<?php
                            if ($tf_produto && is_array($tf_produto))
                                if (in_array($rs_oprProdutos_row['ogp_nome_aux'], $tf_produto)) 
                                    echo " checked";
                                else
                                    if ($rs_oprProdutos_row['ogp_nome_aux'] == $tf_produto)
					echo " checked";
					?>><?php echo $rs_oprProdutos_row['ogp_nome_aux']; ?>&nbsp;
<?php
                        }//end while 
?>
                        </span>
                    </div>
                    <div class="col-md-2">
                        <input type="submit" name="btconsultar" id="btconsultar" value="Consultar" class="btn pull-left btn-success">
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<br>
<table width="900px" border="0" cellpadding="0" cellspacing="1" bordercolor="#cccccc" style="border-collapse:collapse;"> 
<?php
//testando se clicou no Consultar
if($btconsultar)
{
	
	//Inicializando o vetor
	$firstmonth		= mktime(0, 0, 0, 1, 1, 2008);
	$currentmonth	= mktime(0, 0, 0, date("m"), 1, date("Y"));
	$Months = array();
	if($rs_operadoras) {
		while($currentmonth >=$firstmonth) {
			if(empty($tf_opr_codigo)) {
				foreach ($opr_codigo_matriz as $codigo => $valor) {
					$sql = "
					select ogp_nome as ogp_nome_aux
					from (
						(select ogp_nome from tb_dist_operadora_games_produto inner join tb_dist_operadora_games_produto_modelo on (ogpm_ogp_id=ogp_id) where ogp_opr_codigo = " . $valor . " group by ogp_nome
					)
					union all (
						select ogp_nome from tb_operadora_games_produto inner join tb_operadora_games_produto_modelo on (ogpm_ogp_id=ogp_id) where ogp_opr_codigo = " . $valor . " group by ogp_nome
					)
					union all (
						select ogp_nome from tb_pos_operadora_games_produto where ogp_opr_codigo = " . $valor . " group by ogp_nome
					)
					) as jogos
					group by ogp_nome_aux
					order by ogp_nome_aux";
					$rs_Produtos = SQLexecuteQuery($sql);
					while($rs_Produtos_row = pg_fetch_array($rs_Produtos)) {
						foreach ($canais_grade as $canal => $valor2) {
							$Months[date("Y-m-d",$currentmonth)][$valor][$rs_Produtos_row['ogp_nome_aux']][$valor2]['Qtde']	= 0;
							$Months[date("Y-m-d",$currentmonth)][$valor][$rs_Produtos_row['ogp_nome_aux']][$valor2]['Total']= 0;
						}//end foreach ($canais_grade as $canal => $valor)
					}//end while($rs_Produtos_row = pg_fetch_array($rs_Produtos))
				}//end foreach ($opr_codigo_matriz as $codigo => $valor)
			}//end if(empty($tf_opr_codigo))
			else {
				if(empty($tf_produto_aux)) {
					$sql = "
					select ogp_nome as ogp_nome_aux
					from (
						(select ogp_nome from tb_dist_operadora_games_produto inner join tb_dist_operadora_games_produto_modelo on (ogpm_ogp_id=ogp_id) where ogp_opr_codigo = " . $tf_opr_codigo . " group by ogp_nome
					)
					union all (
						select ogp_nome from tb_operadora_games_produto inner join tb_operadora_games_produto_modelo on (ogpm_ogp_id=ogp_id) where ogp_opr_codigo = " . $tf_opr_codigo . " group by ogp_nome
					)
					union all (
						select ogp_nome from tb_pos_operadora_games_produto where ogp_opr_codigo = " . $tf_opr_codigo . " group by ogp_nome
					)
					) as jogos
					group by ogp_nome_aux
					order by ogp_nome_aux";
					$rs_Produtos = SQLexecuteQuery($sql);
					while($rs_Produtos_row = pg_fetch_array($rs_Produtos)) {
						foreach ($canais_grade as $canal => $valor2) {
							$Months[date("Y-m-d",$currentmonth)][$tf_opr_codigo][$rs_Produtos_row['ogp_nome_aux']][$valor2]['Qtde']	= 0;
							$Months[date("Y-m-d",$currentmonth)][$tf_opr_codigo][$rs_Produtos_row['ogp_nome_aux']][$valor2]['Total']= 0;
						}//end foreach ($canais_grade as $canal => $valor)
					}//end while($rs_Produtos_row = pg_fetch_array($rs_Produtos))
				}//end if(empty($tf_produto))
				else {
					$sql = "
					select ogp_nome as ogp_nome_aux
					from (
						(select ogp_nome from tb_dist_operadora_games_produto inner join tb_dist_operadora_games_produto_modelo on (ogpm_ogp_id=ogp_id) where ogp_nome IN ('" . $tf_produto_aux . "') group by ogp_nome
					)
					union all (
						select ogp_nome from tb_operadora_games_produto inner join tb_operadora_games_produto_modelo on (ogpm_ogp_id=ogp_id) where ogp_nome IN ('" . $tf_produto_aux . "') group by ogp_nome
					)
					union all (
						select ogp_nome from tb_pos_operadora_games_produto where ogp_nome IN ('" . $tf_produto_aux . "') group by ogp_nome
					)
					) as jogos
					group by ogp_nome_aux
					order by ogp_nome_aux";
					$rs_Produtos = SQLexecuteQuery($sql);
					while($rs_Produtos_row = pg_fetch_array($rs_Produtos)) {
						foreach ($canais_grade as $canal => $valor2) {
							$Months[date("Y-m-d",$currentmonth)][$tf_opr_codigo][$rs_Produtos_row['ogp_nome_aux']][$valor2]['Qtde']	= 0;
							$Months[date("Y-m-d",$currentmonth)][$tf_opr_codigo][$rs_Produtos_row['ogp_nome_aux']][$valor2]['Total']= 0;
						}//end foreach ($canais_grade as $canal => $valor)
					}//end while($rs_Produtos_row = pg_fetch_array($rs_Produtos))
				}//end else if(empty($tf_produto))
			}//end else if(empty($tf_opr_codigo))
			$currentmonth = mktime(0, 0, 0, date("m", $currentmonth)-1, 1, date("Y",$currentmonth));
		}//end while($currentmonth >=$firstmonth)
		//final do inicializa vetor
	
		//Setando as variaveis para NULL quanto estiverem vazias
		if(empty($tf_opr_codigo))	$tf_opr_codigo	= null;
		if(empty($tf_produto_aux))	$tf_produto_aux	= null;
		if(empty($tf_pins_aux))		$tf_pins_aux	= null;
				
		// capturando o SQL
		$sql = getSQLTotaisporJogos($tf_opr_codigo,$tf_produto_aux,$tf_data_inicial,$tf_data_final,$tf_pins_aux);

        //echo $sql."<br>";
//die("Stop");

		$rs_totais = SQLexecuteQuery($sql);
		
		//DataInicial para exibição
		$DataInicialExibicao	= '3000-12-31';
		//DataFinal para exibição
		$DataFinalExibicao		= '2008-01-01';

		//Vetor com os totais
		$MonthsTotal = array();
		//carregando as informações no vetor
		while ($rs_totais_row = pg_fetch_array ($rs_totais)) { 

			if($DataInicialExibicao > substr($rs_totais_row['mes'],0,10)) {
				$DataInicialExibicao = substr($rs_totais_row['mes'],0,10);
			}//end if($DataInicialExibicao > substr($rs_totais_row['mes'],0,10))
			
			if($DataFinalExibicao < substr($rs_totais_row['mes'],0,10)) {
				$DataFinalExibicao = substr($rs_totais_row['mes'],0,10);
			}//end if($DataFinalExibicao < substr($rs_totais_row['mes'],0,10))
			
			if(!is_array($Months[$DataInicialExibicao][$rs_totais_row['publisher']][$rs_totais_row['jogo_nome']])) {
				foreach($Months as $mes => $valor) {
					//echo "<pre>".print_r($valor[key($valor)],true)."</pre>";
					foreach($valor[key($valor)] as $jogo_aux => $canal_aux) {
						//echo "<pre>".print_r($canal_aux,true)."</pre>";
						foreach($canal_aux as $char_canal => $total_canal) {
							//echo $char_canal."<br>";
							$Months[$mes][$rs_totais_row['publisher']][$rs_totais_row['jogo_nome']][$char_canal]['Qtde'] = 0;
							$Months[$mes][$rs_totais_row['publisher']][$rs_totais_row['jogo_nome']][$char_canal]['Total']	= 0;
						}//end foreach($canal_aux as $char_canal => $total_canal)
					}//end foreach($valor[key($valor)] as $jogo_aux => $canal_aux)
				}//end foreach($valor[key($valor)] as $jogo_aux => $canal_aux)
			}//end if(!is_array($Months[$DataInicialExibicao][$rs_totais_row['publisher']][$rs_totais_row['jogo_nome']]))

			$Months[substr($rs_totais_row['mes'],0,10)][$rs_totais_row['publisher']][$rs_totais_row['jogo_nome']][$rs_totais_row['canal']]['Qtde']	+= $rs_totais_row['n_total'];
			$Months[substr($rs_totais_row['mes'],0,10)][$rs_totais_row['publisher']][$rs_totais_row['jogo_nome']][$rs_totais_row['canal']]['Total']	+= $rs_totais_row['venda_total'];
			$MonthsTotal[substr($rs_totais_row['mes'],0,10)][$rs_totais_row['publisher']][$rs_totais_row['jogo_nome']]['Qtde']	+= $rs_totais_row['n_total'];
			$MonthsTotal[substr($rs_totais_row['mes'],0,10)][$rs_totais_row['publisher']][$rs_totais_row['jogo_nome']]['Total']	+= $rs_totais_row['venda_total'];
			$MonthsTotal[substr($rs_totais_row['mes'],0,10)][$rs_totais_row['publisher']]['Qtde']	+= $rs_totais_row['n_total'];
			$MonthsTotal[substr($rs_totais_row['mes'],0,10)][$rs_totais_row['publisher']]['Total']	+= $rs_totais_row['venda_total'];
			$MonthsTotal[substr($rs_totais_row['mes'],0,10)]['QtdeGeral']	+= $rs_totais_row['n_total'];
			$MonthsTotal[substr($rs_totais_row['mes'],0,10)]['TotalGeral']	+= $rs_totais_row['venda_total'];
		} //end while
    }//end if($rs_operadoras)
	else echo "Problema ao selecionar operadoras!(350)<br>";

?>
</table>
<table border="1" cellpadding="0" cellspacing="1" bordercolor="#FFFFFF" style="border-collapse:collapse;" class='texto'> 
<?php
	//ordenando decrescendo as datas
	uksort($Months);
	//echo 'Vetor Mes:<pre>'.print_r($Months,true).'</pre><br><br>';
	//echo 'Vetor:<pre>'.print_r($MonthsTotal,true).'</pre><br><br>';

	//inicializador de primeira linha
	$primeira_linha = 1;

	//Montando o cabeçalho
	foreach ($Months as $mes => $valor) {
		if($primeira_linha == 1) {
			echo "\t<tr bgcolor='#C4DAE7' style='font-size:14px'>\n\t\t<td rowspan='2' align='center'><b>&nbsp;&nbsp;M&ecirc;s&nbsp;&nbsp;</b></td>\n";
		}//end if($primeira_linha == 1)
		else break;
		ksort($valor);
		foreach ($valor as $publisher => $valor2) {
			ksort($valor2);
			foreach ($valor2 as $jogo_nome => $valor3) {
				$jogo_nome_jquery = strtoupper(str_replace(":","",str_replace(".","",str_replace("-","",str_replace(" ","",str_replace("'","",$jogo_nome))))));
				echo "\t\t<td colspan='2' align='center' onclick='javascript: div".$publisher.$jogo_nome_jquery."();' style='cursor:pointer;cursor:hand;' title='Clique aqui para exibir os totais por canais'><script>function div".$publisher.$jogo_nome_jquery."() { $('.".$publisher.$jogo_nome_jquery."').show(); }function div".$publisher.$jogo_nome_jquery."Oculta() { $('.".$publisher.$jogo_nome_jquery."').hide(); }</script><nobr><b>&nbsp;&nbsp;".$jogo_nome."&nbsp;&nbsp;</b><nobr></td>\n";
				$numeroQtdeTotal[] = 1;
				ksort($valor3);
				foreach ($valor3 as $canal => $valor4) {
					echo "\t\t<td colspan='2' align='center' bgcolor='#E4F1F9' style='display:none' class='".$publisher.$jogo_nome_jquery."' onclick='javascript: div".$publisher.$jogo_nome_jquery."Oculta();'  style='cursor:pointer;cursor:hand;' title='Clique aqui para OCULTAR os totais por canais'><nobr>&nbsp;&nbsp;".$canais_grade_legenda[$canal]."&nbsp;&nbsp;<nobr></td>\n";
					$numeroQtdeTotal[] = $publisher.$jogo_nome_jquery;
				}//end foreach ($valor3 as $canal => $valor4) 
			}//end foreach ($valor2 as $jogo_nome => $valor3)
			echo "\t\t<td colspan='2' align='center'><nobr><b>&nbsp;&nbsp;".$opr_codigo_matriz_legenda[$publisher]."&nbsp;&nbsp;</b><nobr></td>\n";
			$numeroQtdeTotal[] = 1;
		}//end foreach ($valor as $publisher => $valor2)
		//mudando para segunda linha
		$primeira_linha = 2;
	}//end foreach ($Months as $mes => $valor)
	echo "\t\t<td colspan='2' align='center'><nobr><b>&nbsp;&nbsp;Total Geral&nbsp;&nbsp;</b><nobr></td>\n";
	$numeroQtdeTotal[] = 1;
	echo "\t</tr>\n";
	foreach ($numeroQtdeTotal as $auxtotais => $exibe) {
		if ($exibe == 1) {
			echo "\t\t<td align='center' bgcolor='#DBEFFB'><nobr>&nbsp;&nbsp;Qtde.&nbsp;&nbsp;<nobr></td>\n";
			echo "\t\t<td align='center' bgcolor='#DBEFFB'><nobr>&nbsp;&nbsp;Total&nbsp;&nbsp;<nobr></td>\n";
		} //end if ($exibe == 1)
		else {
			echo "\t\t<td align='center' bgcolor='#F1F9FC' style='display:none' class='".$exibe."' onclick='javascript: div".$exibe."Oculta();' style='cursor:pointer;cursor:hand;' title='Clique aqui para OCULTAR os totais por canais'><nobr>&nbsp;&nbsp;Qtde.&nbsp;&nbsp;<nobr></td>\n";
			echo "\t\t<td align='center' bgcolor='#F1F9FC' style='display:none' class='".$exibe."' onclick='javascript: div".$exibe."Oculta();' style='cursor:pointer;cursor:hand;' title='Clique aqui para OCULTAR os totais por canais'><nobr>&nbsp;&nbsp;Total&nbsp;&nbsp;<nobr></td>\n";
		} //end else if ($exibe == 1)
	}//end	foreach ($numeroQtdeTotal)
	echo "\t</tr>\n";
	//fim Montando o cabeçalho

	//resetando o vetor
	reset($Months);
	
	//montando o esquema de cor
	$cor_linha1			= "#FCF2DA";
	$cor_linha2			= "#FAE8C1";
	$cor_linha_atual	= $cor_linha1;
	
	//exibindo os dados
	foreach ($Months as $mes => $valor) {
		if (($mes >= $DataInicialExibicao) && ($mes <= $DataFinalExibicao)){
			if($cor_linha_atual == $cor_linha2)
				$cor_linha_atual = $cor_linha1;
			else $cor_linha_atual = $cor_linha2;
			echo "\t<tr bgcolor='$cor_linha_atual'>\n\t\t<td align='right'><nobr><b>&nbsp;&nbsp;".dataLegenda($mes)."&nbsp;&nbsp;</b></nobr></td>\n";
			ksort($valor);
			foreach ($valor as $publisher => $valor2) {
				ksort($valor2);
				foreach ($valor2 as $jogo_nome => $valor3) {
					$jogo_nome_jquery = strtoupper(str_replace(":","",str_replace(".","",str_replace("-","",str_replace(" ","",str_replace("'","",$jogo_nome))))));
					echo "\t\t<td align='right'onclick='javascript: div".$publisher.$jogo_nome_jquery."();' style='cursor:pointer;cursor:hand;' title='Clique aqui para exibir os totais por canais'><nobr>&nbsp;&nbsp;".$MonthsTotal[$mes][$publisher][$jogo_nome]['Qtde']."&nbsp;&nbsp;</nobr></td>\n";
					echo "\t\t<td align='right'onclick='javascript: div".$publisher.$jogo_nome_jquery."();' style='cursor:pointer;cursor:hand;' title='Clique aqui para exibir os totais por canais'><nobr>&nbsp;&nbsp;".number_format($MonthsTotal[$mes][$publisher][$jogo_nome]['Total'], 2, ',', '.')."&nbsp;&nbsp;</nobr></td>\n";
					ksort($valor3);
					foreach ($valor3 as $canal => $valor4) {
						echo "\t\t<td align='right' bgcolor='#F6F6F6' style='display:none' class='".$publisher.$jogo_nome_jquery."' onclick='javascript: div".$publisher.$jogo_nome_jquery."Oculta();' style='cursor:pointer;cursor:hand;' title='Clique aqui para OCULTAR os totais por canais'><nobr>&nbsp;&nbsp;".$valor4['Qtde']. "&nbsp;&nbsp;</nobr></td>\n";
						echo "\t\t<td align='right' bgcolor='#F6F6F6' style='display:none' class='".$publisher.$jogo_nome_jquery."' onclick='javascript: div".$publisher.$jogo_nome_jquery."Oculta();' style='cursor:pointer;cursor:hand;' title='Clique aqui para OCULTAR os totais por canais'><nobr>&nbsp;&nbsp;".number_format($valor4['Total'], 2, ',', '.')."&nbsp;&nbsp;</nobr></td>\n";
					}//end foreach ($valor3 as $canal => $valor4) 
				}//end foreach ($valor2 as $jogo_nome => $valor3)
				echo "\t\t<td align='right'><nobr>&nbsp;&nbsp;<b>".$MonthsTotal[$mes][$publisher]['Qtde']."</b>&nbsp;&nbsp;</nobr></td>\n";
				echo "\t\t<td align='right'><nobr>&nbsp;&nbsp;<b>".number_format($MonthsTotal[$mes][$publisher]['Total'], 2, ',', '.')."</b>&nbsp;&nbsp;</nobr></td>\n";
			}//end foreach ($valor as $publisher => $valor2)
			echo "\t\t<td align='right'><nobr>&nbsp;&nbsp;<b>".$MonthsTotal[$mes]['QtdeGeral']."</b>&nbsp;&nbsp;</nobr></td>\n";
			echo "\t\t<td align='right'><nobr>&nbsp;&nbsp;<b>".number_format($MonthsTotal[$mes]['TotalGeral'], 2, ',', '.')."</b>&nbsp;&nbsp;</nobr></td>\n";
			echo "\t</tr>\n";
		}//end if (($mes >= $DataInicialExibicao) && ($mes <= $DataFinalExibicao))
	}//end foreach ($Months as $mes => $valor)
	//fim exibindo os dados

?>
</table>
<table width="900px" border="0" cellpadding="0" cellspacing="1" bordercolor="#cccccc" style="border-collapse:collapse;"> 
	<tr> 
		<td class="texto" colspan="3" align="center">&nbsp;<?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT ?>
		</td>
	</tr>
<?php
}//end if($btconsultar)
?>
	<tr> 
		<td class="texto" colspan="3" align="center">
		<?php
		require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
		?>
		</td>
	</tr>
</table>
