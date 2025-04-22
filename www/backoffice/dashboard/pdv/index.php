<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<?php
session_start();

ini_set('memory_limit', '8192M');
set_time_limit(0);

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";
require_once "/www/includes/bourls.php";
$con = ConnectionPDO::getConnection()->getLink();

//ini_set("display_errors", 1);
//ini_set("display_startup_errors", 1);
//error_reporting(E_ALL);

if (!isset($GLOBALS["_SESSION"]["iduser_bko"])) {
    header("Location: login.php");
    die();
}

if (isset($_POST["startDate"])) {
    $startDate = $_POST["startDate"];
}

if (isset($_POST["endDate"])) {
    $endDate = $_POST["endDate"];
}

if (isset($_POST["billing"])) {
    $billing = $_POST["billing"];
}

if (!empty($_POST)) {
    if ($_POST["formcode"] == "1") {
        if (isset($_POST["id"]) && !empty($_POST["id"])) {
            $id = $_POST["id"];
            $sql = "select sum(vgm_valor*vgm_qtde) as valor_bruto, sum(vgm_valor*vgm_qtde-((vgm_valor*vgm_qtde)*vgm_perc_desconto/100)) as valor_liquido, extract(month from vg_data_inclusao) as mes, extract(year from vg_data_inclusao) as ano from tb_dist_venda_games inner join tb_dist_venda_games_modelo on vg_id = vgm_vg_id 
	where vg_ug_id = $id 
	and vg_ultimo_status = 5 
	and date(vg_data_inclusao) > '$startDate'
    and date(vg_data_inclusao) < '$endDate'
    group by extract(year from vg_data_inclusao), extract(month from vg_data_inclusao)
    order by ano, mes asc";
            $grafico = "bar";
        } else {
            $sql = "select sum(vgm_valor*vgm_qtde) as valor_bruto, sum(vgm_valor*vgm_qtde-((vgm_valor*vgm_qtde)*vgm_perc_desconto/100)) as valor_liquido, 
	extract(month from vg_data_inclusao) as mes, extract(year from vg_data_inclusao) as ano, ug_id from tb_dist_venda_games inner join tb_dist_venda_games_modelo on vg_id = vgm_vg_id 
	inner join dist_usuarios_games on vg_ug_id = ug_id
	where vg_ultimo_status = 5 
	and date(vg_data_inclusao) > '$startDate'
    and date(vg_data_inclusao) < '$endDate'
	and ug_ativo = '1'
    group by extract(year from vg_data_inclusao), extract(month from vg_data_inclusao), ug_id
    order by ano, mes asc
	limit 1;";
            $grafico = "line";
        }

        $query = $con->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $mesTemp = [
            1 => "Janeiro",
            2 => "Feveiro",
            3 => "Março",
            4 => "Abril",
            5 => "Maio",
            6 => "Junho",
            7 => "Julho",
            8 => "Agosto",
            9 => "Setembro",
            10 => "Outubro",
            11 => "Novembro",
            12 => "Dezembro",
        ];

        $data = [];
        $labels = [];
        $userId = [];
        foreach ($result as $value) {
            $mes = $mesTemp[$value["mes"]];
            $labels[] = $mes . "/" . $value["ano"];
            $valorLiquido = $value["valor_liquido"];
            $valorBruto = $value["valor_bruto"];
            $dataBruto[] = $valorBruto;
            $dataLiq[] = $valorLiquido;

            $dataId[] = $userId;
        }
    }
}

require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

echo '<div class="bg-branco"> 
	
		
<div class="dataContainer">
	<div style="display: flex; align-items: center; justify-content: center">
		<h3 class="tittle">Venda de Produtos </h3>
		
		<div class="container-title-icone" style="z-index: 2;">
			<span class="material-symbols-outlined icone-question">help</span>
			<div class="calculo-merchant hidden">
				 <b>Legendas:</b><br> * <u>MMA</u>: Média Móvel Aritmética<br>* <u>FD</u>: Fechamento Total do dia<br>* <u>DA</u>: Total de Dias Anteriores<br><br>
				 <b>Formula:</b><br> * MMA = (FD1 + FD2 + FD3 + FD4 + FD5) ÷ DA
			</div>
		</div>
	</div>
	
	
	<form method="post" class="form">
		<input type="hidden" value="1" name="formcode"/>
		
		<div class="container-input">
			<h4>Data Inicial <span>&#128198;</span></h4> 
			<input class="form-input" type="date" value="'.date("Y-m-d", strtotime("-7 days")).'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="startDate" id="dt_inicial_produtos" />
		</div>
		
		<div class="container-input">
			<h4>Data Final <span>&#128198;</span> </h4>
			<input class="form-input" type="date" value="'.date("Y-m-d").'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="endDate" id="dt_final_produtos" />
		</div>
		
		<div class="container-input">
			<h4>Produto <span>&#128722</span></h4>
		
			<select id="produtos" class="js-example-basic-single form-input" name="state">
				<option value="">Selecione um Produto</option>
			</select>
		</div>	
		
		<div class="container-input">
			<h4>Dias Anteriores <span>&#128204;</span></h4>
			<input class="form-input" type="number" min="1" value="7" name="periodo" id="periodo-produto">    
		</div>		
		
		<div>
			<button id="chart-produto" type="button" class="btn btn-success button-send" style="margin-top: 4rem">Buscar</button> 
		</div>
		
	</form>
	
	<div class="containerMerchant"> 
		<canvas id="chartMerchant"></canvas>
	</div>
</div>			
	
<div class="vendas-por-merchant">
	<div style="display: flex; align-items: center; justify-content: center">
		<h3 class="tittle">Vendas por Merchant</h3>
		
		<div class="container-title-icone" style="z-index: 2;">
			<span class="material-symbols-outlined icone-question">help</span>
			<div class="calculo-merchant hidden">
				 <b>Legendas:</b><br> * <u>MMA</u>: Média Móvel Aritmética<br>* <u>FD</u>: Fechamento Total do dia<br>* <u>DA</u>: Total de Dias Anteriores<br><br>
				 <b>Formula:</b><br> * MMA = (FD1 + FD2 + FD3 + FD4 + FD5) ÷ DA
			</div>
		</div>
	</div>
	<form method="post" class="form">
		<input type="hidden" value="1" name="formcode"/>
		
		<div class="container-input">
			<h4>Data Inicial <span>&#128198;</span></h4> 
			<input class="form-input" type="date" value="'.date("Y-m-d", strtotime("-7 days")).'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="dt_inicial_merchant" id="dt_inicial_merchant">
		</div>
		
		<div class="container-input">
			<h4>Data Final <span>&#128198;</span> </h4>
			<input class="form-input" type="date" value="'.date("Y-m-d").'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="dt_final_merchant" id="dt_final_merchant">
		</div>
		
		<div class="container-input">
			<h4>Merchant <span>&#128202;</span></h4>
		
			<select style="color: black;" class="form-input js-example-basic-single" name="codigo_sale_mechant" id="codigo_sale_mechant">
				<option value="">Selecione um Merchant</option>
			</select>
		</div>				
		
		<div class="container-input">
			<h4>Dias Anteriores <span>&#128204;</span></h4>
			<input class="form-input" type="number" min="1" value="7" name="periodo" id="periodo">    
		</div>
		<div class="button container-layout-button">
			<button id="chart-merchant" type="button" class="btn btn-success button-send">Buscar</button>
		</div>
	</form>
	
	<div class="containerMerchant2"> 
		<canvas id="chartMerchant2"></canvas>
	</div>
</div>		
		
<div class="mais-vendidos" style="text-align: center">
	<h1>Produtos mais vendidos</h1>
	<form method="post" class="form">
		<input type="hidden" value="1" name="formcode"/>
		<div class="container-input">
			<h4>Data Inicial <span>&#128198;</span></h4> 	
			<input type="date" class="form-input" value="'.date("Y-m-d", strtotime("-7 days")).'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="startDate" id="dt_inicial_maisvendidos" />
		</div>
		
		<div class="container-input">
			<h4>Data Final <span>&#128198;</span></h4> 
			<input type="date" class="form-input" value="'.date("Y-m-d").'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="endDate" id="dt_final_maisvendidos" />
		</div>	
		
		<div>
			<button id="chart-produtos-maisvendidos" type="button" class="btn btn-success button-send" style="margin-top: 3.7rem">Buscar</button>
		</div>
	</form>
	
	<div class="containerMerchant3"> 
		<canvas id="chartMerchant3"></canvas>
	</div>
</div>
		
<div class="pagamentos-por-produto" style="text-align: center;">
	<h1>Principais Formas de Pagamento Por Produto</h1>
	
	<div  style="display: flex; flex-direction: row; align-items: center; justify-content: space-evenly">
		<div class="container-input">
			<h4>Data Inicial <span>&#128198;</span></h4> 
			<input type="date" class="form-input" value="'.date("Y-m-d", strtotime("-7 days")).'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="startDate" id="dt_inicial_pagamentos" />
		</div>
		
		<div class="container-input">
			<h4>Data Final <span>&#128198;</span></h4> 
			<input type="date" class="form-input" value="'.date("Y-m-d").'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="endDate" id="dt_final_pagamentos" />
		</div>
		
		<div class="container-input">
			<h4>Produto <span>&#128722</span></h4>
			<select id="pagamentos-produtos" class="js-example-basic-single form-input" name="state">
				<option value="">Selecione um Produto</option>
			</select>
		</div>	
		
		<div>
			<button id="claim-pagamentos" type="button" class="btn btn-success button-send" style="margin-top: 3.7rem; margin-left: 1rem;">Buscar</button>
		</div>
	</div>
	<div class="containerMerchant4"> 
		<canvas id="chartMerchant4"></canvas>
	</div>
</div>
	
<div class="ltv" style="text-align: center">
	<div style="display: flex; align-items: center; justify-content: center">
		<h1>LTV</h1>
		<div class="container-title-icone">
			<span class="material-symbols-outlined icone-question">help</span>
			<div class="calculo-merchant hidden" style="z-index: 2">
				 <b>Legendas:</b><br> * <u>LTV</u>: Life Time Value (Valor do Tempo de Vida)<br><br>
				 <b>Formula:</b><br>(Ticket Médio da Empresa * Média do número de mensalidades a cada ano) * média de tempo de relacionamento
			</div>
		</div>
	</div>
	<div style="display: flex; flex-direction: row; align-items: center; justify-content: space-evenly">
		<div style="width: 150px" class="container-input">
			<h4>Data Inicial <span>&#128198;</span></h4>
			<input type="date" class="form-input" value="'.date("Y-m-d", strtotime("-1 year")).'" max="'.date("Y-m-d").'" name="startDate" id="dt_inicial_ltv" />
		</div>
		
		<div style="width: 150px" class="container-input">
			<h4>Data Inicial <span>&#128198;</span></h4>
			<input type="date" class="form-input" value="'.date("Y-m-d").'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="endDate" id="dt_final_ltv" />
		</div>
			
		
		<button id="claim-ltv" type="button" class="btn btn-success button-send" style="margin-top: 3.7rem;">Buscar</button>
	</div>
	<div id="containerMerchant5" class="containerMerchant5"> 
	</div>
</div>

<div class="carrinho-abandonado">
	<h1>Carrinho abandonado</h1>
	
	<div style="display: flex; flex-direction: row; align-items: center; justify-content: space-evenly">
		<div style="width: 150px" class="container-input">
			<h4>Data Inicial <span>&#128198;</span></h4>
			<input type="date" class="form-input" value="'.date("Y-m-d", strtotime("-7 days")).'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="startDate" id="dt_inicial_carrinho" />
		</div>
		
		<div style="width: 150px" class="container-input">
			<h4>Data Inicial <span>&#128198;</span></h4>
			<input type="date" class="form-input" value="'.date("Y-m-d").'" min="'.date("Y-m-d", strtotime("-1 years")).'" max="'.date("Y-m-d").'" name="endDate" id="dt_final_carrinho" />
		</div>
		
		<button id="claim-carrinho" type="button" class="btn btn-success button-send" style="margin-top: 3.7rem;">Buscar</button>
	</div>
	
	<div class="containerMerchant6"> 
		<canvas id="chartMerchant6"></canvas>
	</div>
</div>		





<div class="mais-vendidos-periodo" style="text-align: center;">
	<h1>Produtos mais vendidos por período</h1>
	
	<form method="post" class="form">
		<div style="width: 150px" class="container-input">
			<h4>Data Inicial <span>&#128198;</span></h4>
			<select class="form-input" id="mais-vendidos-periodo">
				<option value="">Selecione um Período</option>
				<option value="week">Semanal</option>
				<option value="day">Quinzenal</option>
				<option value="month">Mensal</option>
			</select>
		</div>
		<div>
			<button id="chat-produtos-maisvendidos-periodo" type="button" class="btn btn-success button-send" style="margin-top: 3.7rem; margin-left: 1rem">Buscar</button> 
		</div>
	</form>
	<div class="containerMerchant7"> 
		<canvas id="chartMerchant7"></canvas>
	</div>
</div>
		
		
<div class="pdv-mais-ativos" style="text-align: center;">
	<h1>PDVs cadastrados x PDVs ativos</h1>
	<form method="post" class="form">	
		
		<button id="pdvativos" type="button" class="btn btn-success button-send">Buscar</button>
	</form>
	
	<div class="containerMerchant8"> 
		<canvas id="chartMerchant8"></canvas>
	</div>
</div>
	
	
 </div>';



	


// require_once "mostsales.php";
?>
 
 <script>
	
	var selectProdutos = $("#produtos");
	var selectPagamentos = $("#pagamentos-produtos");
	var selectLtv = $("#ltv-produtos");
	var selectOperadoras = $("#codigo_sale_mechant");
	var operadorasResultado = [];
	var produtosResultado = [];
	
	$(document).ready(function() {
		
		$("#produtos").select2();
		$("#pagamentos-produtos").select2();
		$("#ltv-produtos").select2();
		selectOperadoras.select2();
		
		$.ajax({
		method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxProdutos.php",
		  }).done(function(operadoras){
			  
			 $.each(operadoras, function(index, value){
			    produtosResultado[value.ogp_id] = value.ogp_nome;
				selectProdutos.append('<option value="'+value.ogp_id+'" style="color: #222 !important;">'+value.ogp_nome+'</option>');
				selectPagamentos.append('<option value="'+value.ogp_id+'" style="color: #222 !important;">'+value.ogp_nome+'</option>');
			 });
		});
		
		$.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxOperadoras.php",
		  }).done(function(operadoras){
			 $.each(operadoras, function(index, value){ 
			    operadorasResultado[value.opr_codigo] = value.opr_nome;
				selectOperadoras.append('<option value="'+value.opr_codigo+'">'+value.opr_nome+'</option>');
			 });
		});
		
		$.ajax({
			method: "post",
			url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxListaPdvs.php"
		}).done(function(operadoras) {
			
			$.each(operadoras, function(index, value) {
				$("#ltv-produtos").append('<option value="'+value.ug_id+'">'+value.ug_nome_fantasia+'</option>');
			});
		});
	});
	
	$(".icone-question").on("click", function(){
		  if($(".calculo-merchant").hasClass("hidden")){
			  $(".calculo-merchant").switchClass( "hidden", "show", 1000, "easeInOutQuad");
		  }else{
			  $(".calculo-merchant").switchClass( "show", "hidden", 1000, "easeInOutQuad");
		  }
	  });
	  
	$("#chart-merchant").on("click", function(){
		 chart_produto_merchant();
	  });
	  
	$("#chart-produto").on("click", function(){
		chart_merchant();
	 });
	 
	$("#chart-produtos-maisvendidos").on("click", function() {
		call_produtos_maisvendidos();
	}); 
		
	$("#chat-produtos-maisvendidos-periodo").on("click", function() {
		call_produtos_maisvendidos_periodo();
	})
	
	$("#pdvativos").on("click",  function() {
		call_pdv_ativos();
	});
	
	$("#usuariosativos").on("click", function() {
		call_usuarios_ativos();
	});
	
	$("#claim-pagamentos").on("click", function() {
		call_pagamento_por_produto();
	});
	
	$("#claim-carrinho").on("click", function() {
		claim_carrinho();
	});
	
	$("#claim-ltv").on("click", function() {
		claim_ltv();
	})
	
	function showLoadingSwal() {
	  Swal.fire({
		title: 'Carregando informações!',
	    html: '<p>Coletando as informações necessarias</p><img style="width: 80px;" src="../../images/loading.gif">',
	    showConfirmButton: false,
	    allowOutsideClick: false,
	    allowEscapeKey: false,
		onBeforeOpen: () => {
		  Swal.showLoading();
		}
	  });
	}
	
	function verifica_datas(datainicial, datafinal) {
		let dataInicial = new Date(datainicial);
		let dataFinal = new Date(datafinal);
		
		if(dataFinal < dataInicial) {
			Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: 'A Data final não pode ser menor que a inicial.'
			 });
			 
			 return false;
		}
		
		console.log(dataInicial, dataFinal);
		return true;
	}
	
	function chart_merchant(){
		  let data_inicial = $("#dt_inicial_produtos").val();
		  let data_final = $("#dt_final_produtos").val();
		  let produtos = $("#produtos").val();
		  let diaInicial = parseInt(data_inicial.substr(8)); 
		  let mesInicial = parseInt(data_inicial.substr(5, 2));
		  let anoInicial = parseInt(data_inicial.substr(0, 4));
		  let periodo = parseInt($("#periodo-produto").val()); 
		  
		  let datasValidas = verifica_datas(data_inicial, data_final);
		  if(!datasValidas) return;
		           		  
		  showLoadingSwal();
		  
		  $.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxProdutoMerchant.php",
		  data: { id: produtos, datainicial: data_inicial, datafinal: data_final, grafico: 2,periodo: periodo},
		  success: function(response) {
				// Código a ser executado em caso de sucesso na requisição
			Swal.close(); // Fecha o Swal de loading
		  },
			error: function(xhr, textStatus, errorThrown) {
			  // Código a ser executado em caso de erro na requisição
			  Swal.close(); // Fecha o Swal de loading
			  Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: 'Ocorreu um erro na requisição'
			  });
			}
		  }).done(function(dataValues){
			 
			 if(!dataValues.length) {
				Swal.fire({
					icon: 'warning',
					title: 'Aviso',
					text: 'Não foram encontrados dados na data especifica'
				}); 
				
				return;
			 }
			 
			 let diferencaSegundo = new Date(data_final) - new Date(data_inicial);
			 let diferencaDias = Math.round(diferencaSegundo / (1000 * 60 * 60 * 24));
			 let resultado = new Array();
			 
			 for(let data of dataValues) {
				
				resultado.push({
					data: `${('0' + data.dia).slice(-2)}/${('0' + data.mes).slice(-2)}/${data.ano}`,
					total: data.total,
					qtde: data.qtde
				});
			 }
			 
			 function mediaMovel(array, periodo) {
			  const resultado = [];
			  
			  for (let i = periodo; i < array.length; i++) {
				const valores = array.slice(i - periodo, i);
				console.log(valores);
				const soma = valores.reduce((acc, valor) => parseFloat(acc) + parseFloat(valor.total), 0);
				const media = soma / periodo;
				const obj = { 
					data: `${('0' + array[i].dia).slice(-2)}/${('0' + array[i].mes).slice(-2)}/${array[i].ano}`, 
					qtde: array[i].qtde, 
					media: media.toFixed(2) 
				};
				
				resultado.push(obj);
			  }
			  
			  return resultado;
			}
			
			let resultadoDasMediasMoveis = mediaMovel(dataValues, 7);
						
				$("#chartMerchant").remove();
					$(".containerMerchant").append('<canvas id="chartMerchant" class="bar active"></canvas>');
					const ctx = document.getElementById('chartMerchant');
					let myChart = new Chart(ctx, {
						type: 'line',
						data: {
						  datasets: [{
								label: produtosResultado[produtos],
								data: resultadoDasMediasMoveis,
								borderWidth: 1
						   }]
						},
						options: {
							parsing: {
							  xAxisKey: 'data',
							  yAxisKey: 'media'
							},
						    animation: {
							  onComplete: () => {
								delayed = true;
							  },
							  delay: (context) => {
								let delay = 0;
								delayed = false;
								if (context.type === 'data' && context.mode === 'default' && !delayed) {
									 delay = context.dataIndex * 200 + context.datasetIndex * 100;
								}
								return delay;
							  },
						   }
						}
					}); 
					
									 
				$(window).on("resize", function(){
                     myChart.resize();
			    });
				
				resultado = [];
				setTimeout(() => {				
					window.scrollTo({
						top: $("#chartMerchant").offset().top,
						left: 0,
						behavior: 'smooth'
					});
				}, 1000)
			})
	}
	
	function chart_produto_merchant(){
		  let data_inicial = $("#dt_inicial_merchant").val();
		  let data_final = $("#dt_final_merchant").val();
		  let operadora = $("#codigo_sale_mechant").val();
		  let periodo = parseInt($("#periodo").val()); 
		  
		  let datasValidas = verifica_datas(data_inicial, data_final);
		  if(!datasValidas) return;

		  $.ajax({
			  method: "post",
			  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxSalesProduct.php",
			  data: { id: operadora, datainicial: data_inicial, datafinal: data_final, grafico: 2, periodo: periodo},
			  beforeSend: function() {
				Swal.fire({
				  title: 'Carregando informações!',
				  html: '<p>Coletando as informações necessarias</p><img style="width: 80px;" src="../../images/loading.gif">',
				  showConfirmButton: false,
				  allowOutsideClick: false,
				  allowEscapeKey: false
				});
			  },
			  success: function(response) {
				  Swal.close(); 
		      }
		  }).done(function(dataValues){
			 
			 if(dataValues.length > 0){
			
				 function mediaMovel(array, periodo) {
					  const resultado = []; 
					  for (let i = periodo; i < array.length; i++) {
						  const valores = array.slice(i - periodo, i);
						  const soma = valores.reduce((acc, valor) => parseFloat(acc) + parseFloat(valor.total), 0);
						  const media = soma / periodo;
						  const obj = { 
								data: `${('0' + array[i].dia).slice(-2)}/${('0' + array[i].mes).slice(-2)}/${array[i].ano}`, 
								qtde: array[i].qtde, 
								media: media.toFixed(2)
						  };
						
						  resultado.push(obj);
					  }
					  
					  return resultado;
				  }
		
				 let resultadoDasMediasMoveis = mediaMovel(dataValues, periodo);		
				 $("#chartMerchant2").remove();
				 $(".containerMerchant2").append('<canvas id="chartMerchant2" class="bar active"></canvas>');
				 let ctx = document.getElementById('chartMerchant2');  
					   var myChart = new Chart(ctx, {
							type: 'line',
							data: {
							  datasets: [{
									label: operadorasResultado[operadora],
									data: resultadoDasMediasMoveis,
									borderWidth: 1
							   }]
							},
							options: {
								responsive: true,
								parsing: {
								  xAxisKey: 'data',
								  yAxisKey: 'media'
								},

								animation: {
								  onComplete: () => {
									delayed = true;
								  },
								  delay: (context) => {
									let delay = 0;
                                    delayed = false;
									if (context.type === 'data' && context.mode === 'default' && !delayed) {
									     delay = context.dataIndex * 200 + context.datasetIndex * 100;
									}
									return delay;
								  },
								}
							}
				 });  
				 
				$(window).on("resize", function(){
                     myChart.resize();
			    });
                
                setTimeout(function(){
					window.scrollTo({
						top: $("#chartMerchant2").offset().top,
						left: 0,
						behavior: 'smooth'
					});
					
                }, 1000);
                
			 }else{
				Swal.fire({
					icon: 'warning',
					title: 'Aviso',
					text: 'Não foram encontrados dados na data especifica'
				});
			 }
 
	      }).fail(function(){
			   Swal.fire({
					icon: 'error',
					title: 'Erro',
					text: 'Não foi possivel carregar o grafico',
					showConfirmButton: false,
					allowOutsideClick: true,
					allowEscapeKey: false
				});
		  });  
	  }
	
	function call_produtos_maisvendidos() {
		let data_inicial = $('#dt_inicial_maisvendidos').val();
		let data_final = $('#dt_final_maisvendidos').val();
		
		console.log(data_inicial);
		console.log(data_final);
		let datasValidas = verifica_datas(data_inicial, data_final);
		if(!datasValidas) return;
		showLoadingSwal();
		
		$.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxProdutosMaisVendidos.php",
		  data: { datainicial: data_inicial, datafinal: data_final, grafico: 2},
		  success: function(response) {
				// Código a ser executado em caso de sucesso na requisição
			Swal.close(); // Fecha o Swal de loading
		  },
			error: function(xhr, textStatus, errorThrown) {
			  // Código a ser executado em caso de erro na requisição
			  Swal.close(); // Fecha o Swal de loading
			  Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: 'Ocorreu um erro na requisição'
			  });
			}
		  }).done(function(dataValues){
			  
			if(!dataValues.length) {
				Swal.fire({
					icon: 'warning',
					title: 'Aviso',
					text: 'Não foram encontrados dados na data especifica'
				}); 
				
				return;
			 }
			
			$("#chartMerchant3").remove();
			$(".containerMerchant3").append('<canvas id="chartMerchant3" class="active bar"></canvas>');
			const ctx = document.getElementById('chartMerchant3');
			new Chart(ctx, {
				type: 'bar',
				data: {
				  datasets: [{
						label: "Quantidade Vendida",
						data: dataValues,
						borderWidth: 1
				   }]
				},
				options: {
					parsing: {
					  xAxisKey: 'nome',
					  yAxisKey: 'qtde'
					}
				}
			});
			setTimeout(() => {				
				window.scrollTo({
					top: $("#chartMerchant3").offset().top,
					left: 0,
					behavior: 'smooth'
				});
			}, 1000)
		
			resultado = [];
		})
	}
	
	function gerarCoresAleatorias(tamanho) {
	  const cores = [];
	  for (let i = 0; i < tamanho; i++) {
		const r = Math.floor(Math.random() * 256);
		const g = Math.floor(Math.random() * 256);
		const b = Math.floor(Math.random() * 256);
		cores.push(`rgb(${r}, ${g}, ${b})`);
	  }
	  return cores;
	}
	
	function call_produtos_maisvendidos_periodo() {
		let periodo = $("#mais-vendidos-periodo").val();
		
		showLoadingSwal();
		
		$.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxProdutosPeriodo.php",
		  data: { periodo: periodo },
		  success: function(response) {
				// Código a ser executado em caso de sucesso na requisição
			Swal.close(); // Fecha o Swal de loading
		  },
			error: function(xhr, textStatus, errorThrown) {
			  // Código a ser executado em caso de erro na requisição
			  Swal.close(); // Fecha o Swal de loading
			  Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: 'Ocorreu um erro na requisi?'
			  });
			}
		  }).done(function(dataValues){
			  
			console.log(dataValues);
			let backgroundsColor = gerarCoresAleatorias(dataValues.length);
			let nomeLabels = dataValues.map(x => x.nome);
			
			
			$("#chartMerchant7").remove();
			$(".containerMerchant7").append('<canvas id="chartMerchant7" class="active"></canvas>');
			const ctx = document.getElementById('chartMerchant7');
			ctx.style.width = "200px";
			new Chart(ctx, {
				type: 'pie',
				data: {
				  labels: nomeLabels,
				  datasets: [{
						label: "Quantidade Vendida",
						data: dataValues,
						borderWidth: 1,
						//backgroundColor: backgroundsColor
						hoverOffset: 4
				   }]
				},
				options: {
					parsing: {
					  key: 'quantidade'
					}
				}
			});
			
			setTimeout(() => {				
				window.scrollTo({
					top: $("#chartMerchant7").offset().top,
					left: 0,
					behavior: 'smooth'
				});
			}, 1000)
		  });
		
	}
	
	function call_pdv_ativos() {
		
		showLoadingSwal();
		
		$.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxPdvAtivos.php",
		  data: {},
		  success: function(response) {
				// Código a ser executado em caso de sucesso na requisição
			Swal.close(); // Fecha o Swal de loading
		  },
			error: function(xhr, textStatus, errorThrown) {
			  // Código a ser executado em caso de erro na requisição
			  Swal.close(); // Fecha o Swal de loading
			  Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: 'Ocorreu um erro na requisição'
			  });
			}
		  }).done(function(dataValues){
			 
			console.log(dataValues);
			$("#chartMerchant8").remove();
			$(".containerMerchant8").append('<canvas id="chartMerchant8" class="active"></canvas>');
			const ctx = document.getElementById('chartMerchant8');
			ctx.style.width = "200px !important";
			ctx.style.height = "200px !important";
			new Chart(ctx, {
				type: 'pie',
				data: {
				  labels: ["Não realizaram primeiro pedido", "Realizaram primeiro pedido"],
				  datasets: [{
						label: "Quantidade de PDVs",
						data: dataValues,
						borderWidth: 1,
						//backgroundColor: backgroundsColor
						hoverOffset: 4
				   }]
				},
				options: {
					parsing: {
					  key: 'qtde'
					}
				}
			});
			
			setTimeout(() => {				
				window.scrollTo({
					top: $("#chartMerchant8").offset().top,
					left: 0,
					behavior: 'smooth'
				});
			}, 1000)
		  });
		 
	}
	
	
	function call_pagamento_por_produto() {
		
		let datainicial = $("#dt_inicial_pagamentos").val();
		let datafinal = $("#dt_final_pagamentos").val();
		let id = $("#pagamentos-produtos").val();
		
		let datasValidas = verifica_datas(datainicial, datafinal);
		if(!datasValidas) return;
		
		showLoadingSwal();
		
		$.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxPagamentosPorProduto.php",
		  data: {id, datainicial, datafinal},
		  success: function(response) {
				// Código a ser executado em caso de sucesso na requisição
			Swal.close(); // Fecha o Swal de loading
		  },
			error: function(xhr, textStatus, errorThrown) {
			  // Código a ser executado em caso de erro na requisição
			  Swal.close(); // Fecha o Swal de loading
			  Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: 'Ocorreu um erro na requisi?'
			  });
			}
		  }).done(function(dataValues){
			  console.log(dataValues);
			if(!dataValues.length) {
				Swal.fire({
					icon: 'warning',
					title: 'Aviso',
					text: 'Não foram encontrados dados na data especificada'
				});
				
				return;
			}
			
			console.log(dataValues);
			let tipos = {
				"1": "Transferência Bancaria",
				"2": "Saldo em conta",  // Boleto
				"3": "Master Card",
				"4": "Diners",
				"5": "Transferência entre contas Bradesco",
				"6": "Pagamento Fácil Bradesco - Débito",
				"7": "Pagamento Fácil Bradesco - Crédito",
				"8": "Pagamento BB - Débito sua Empresa",
				"9": "Pagamento BB - Débito sua Conta",
				"10": "Pagamento Itáu online",	
				"11": "Pagamento Hipay online",
				"12": "Pagamento Paypal online",
				"13": "Pagamento EPP Cash",
				"14": "Pagamento Visa Net",
				"15": "Pagamento Visa Crédito",
				"16": "Pagamento Maestro",
				"17": "Pagamento Mastercard Crédito",
				"18": "Pagamento Elo Débito",
				"19": "Pagamento Elo Crédito",
				"20": "Pagamento Diners Crédito",
				"21": "Pagamento Discover Crédito",
				"22": "Ofertas",
				"23": "Pagamentos MCOIN - celular",
				"24": "Pix"
			};
			
			for(let x of dataValues) {
				console.log(x);
				console.log(`testando combina?: ${tipos[x.forma_pagamento]}`);
				x.forma_pagamento = tipos[x.forma_pagamento];
			}
			
			console.log(dataValues);
			$("#chartMerchant4").remove();
			$(".containerMerchant4").append('<canvas id="chartMerchant4" class="bar active"></canvas>');
			const ctx = document.getElementById('chartMerchant4');
			let myChart = new Chart(ctx, {
				type: 'bar',
				data: {
				  datasets: [{
						label: dataValues[0].ogp_nome,
						data: dataValues,
						borderWidth: 1
				   }]
				},
				options: {
					parsing: {
					  xAxisKey: 'forma_pagamento',
					  yAxisKey: 'qtde'
					}
				}
			});
			
			setTimeout(() => {				
				window.scrollTo({
					top: $("#chartMerchant4").offset().top,
					left: 0,
					behavior: 'smooth'
				});
			}, 1000)
		  });
		  
		 $(window).on("resize", function(){
			myChart.resize();
		});
	}
	
	function claim_carrinho() {
		let datainicial = $("#dt_inicial_carrinho").val();
		let datafinal = $("#dt_final_carrinho").val();
		
		let datasValidas = verifica_datas(datainicial, datafinal);
		if(!datasValidas) return;
		
		showLoadingSwal();
		
		$.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxCarrinho.php",
		  data: {datainicial, datafinal},
		  success: function(response) {
				// Código a ser executado em caso de sucesso na requisição
			Swal.close(); // Fecha o Swal de loading
		  },
			error: function(xhr, textStatus, errorThrown) {
			  // Código a ser executado em caso de erro na requisição
			  Swal.close(); // Fecha o Swal de loading
			  Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: 'Ocorreu um erro na requisi?'
			  });
			}
		  }).done(function(dataValues){
			console.log(dataValues);
			let tipos = {
				"1": "Transferência Bancaria",
				"2": "Saldo em conta", // Boleto
				"3": "Master Card",
				"4": "Diners",
				"5": "Transferência entre contas Bradesco",
				"6": "Pagamento Fácil Bradesco - Débito",
				"7": "Pagamento Fácil Bradesco - Crédito",
				"8": "Pagamento BB - Débito sua Empresa",
				"9": "Pagamento BB - Débito sua Conta",
				"10": "Pagamento Itáu online",	
				"11": "Pagamento Hipay online",
				"12": "Pagamento Paypal online",
				"13": "Pagamento EPP Cash",
				"14": "Pagamento Visa Net",
				"15": "Pagamento Visa Crédito",
				"16": "Pagamento Maestro",
				"17": "Pagamento Mastercard Crédito",
				"18": "Pagamento Elo Débito",
				"19": "Pagamento Elo Crédito",
				"20": "Pagamento Diners Crédito",
				"21": "Pagamento Discover Crédito",
				"22": "Ofertas",
				"23": "Pagamentos MCOIN - celular",
				"24": "Pix"
			};
			
			for(let x of dataValues) {
				console.log(x);
				x.forma_pagamento = tipos[x.forma_pagamento];
			}
			$("#chartMerchant6").remove();
			$(".containerMerchant6").append('<canvas id="chartMerchant6" class="bar active"></canvas>');
			const ctx = document.getElementById('chartMerchant6');
			
			const data = {
			  labels: dataValues.map(obj => obj.ogp_nome),
			  datasets: [{
				label: 'Quantidade',
				data: dataValues.map(obj => obj.qtde)
			  }],
			};
			
			let myChart = new Chart(ctx, {
				type: 'line',
				  data: data,
				  options: {
					plugins: {
						tooltip: {
						callbacks: {
						  footer: (tooltipItems) => {
							  console.log(dataValues[tooltipItems[0].dataIndex]);
							  
							  return `Forma: ${dataValues[tooltipItems[0].dataIndex].forma_pagamento}`;
							},
						}
					  }
					}
					
				}
			});
			
			setTimeout(() => {				
				window.scrollTo({
					top: $("#chartMerchant6").offset().top,
					left: 0,
					behavior: 'smooth'
				});
			}, 1000)
		  });
		  
		$(window).on("resize", function(){
			myChart.resize();
		});
		
	}
	
	function claim_ltv() {
		//let id_produto = $("#ltv-produtos").val();
		let datainicial = $("#dt_inicial_ltv").val();
		let datafinal = $("#dt_final_ltv").val();
		
		let datasValidas = verifica_datas(datainicial, datafinal);
		if(!datasValidas) return;
		
		showLoadingSwal();
		
		$.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxLtv.php",
		  data: { datainicial, datafinal }, //id: id_produto,
		  success: function(response) {
				// Código a ser executado em caso de sucesso na requisição
			Swal.close(); // Fecha o Swal de loading
		  },
			error: function(xhr, textStatus, errorThrown) {
			  // Código a ser executado em caso de erro na requisição
			  Swal.close(); // Fecha o Swal de loading
			  Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: 'Ocorreu um erro na requisi?'
			  });
			}
		  }).done(function(dataValues){
			
			if(parseFloat(dataValues[0]) == 0) {

				Swal.fire({
					icon: 'warning',
					title: 'Aviso',
					text: 'Não foram encontrados dados na data especificada ou o LTV é 0'
				});
				
				return;
			}
			console.log(dataValues[0].ltv);
			
			const options = { style: 'currency', currency: 'BRL', minimumFractionDigits: 2, maximumFractionDigits: 3 };
            const formatNumber = new Intl.NumberFormat('pt-BR', options);
			
			const data = {
			  labels: dataValues.map(obj => obj.pdv),
			  datasets: [{
				label: "LTV",
				data: dataValues.map(obj => formatNumber.format(obj.ltv)), //parseFloat(dataValues[0])  parseFloat(obj.ltv)
			  }]
			};
			
			if($("#myTable").length) {
				$("#myTable").remove();
				$("#myTable_wrapper").remove();
			}
			
			
			var newTable = document.createElement("table");
			newTable.id = "myTable"
			var thead = document.createElement("thead");
			var tr = document.createElement("tr"); 
			var th1 = document.createElement("th");
			var th2 = document.createElement("th");
			th1.textContent = "Nome do PDV";
			th2.textContent = "LTV";
			tr.appendChild(th1);
			tr.appendChild(th2);
			thead.append(tr);
			newTable.appendChild(thead);
			
			$('#containerMerchant5').append(newTable);
			$("#myTable_filter").css("margin-right", "20px");
			
			let table = new DataTable('#myTable', {
			    ordering: false,
				language: {
					lengthMenu: "Mostrar _MENU_ resultados por página",
					zeroRecords: "Não foram encontrados CNAES",
					info: "Mostrando a página _PAGE_ de _PAGES_",
					infoEmpty: "Dados inexistentes",
					infoFiltered: "(filtro aplicado em _MAX_ registros)",
					sSearch: "Pesquisar:",
					paginate: {
						previous: "Anterior",
						next: "Próximo",
					}
				},
				data: dataValues,
				columns: [
					{ 	
						title: 'Nome do PDV',
						data: 'pdv' 
					},
					{ 
						title: 'LTV',
						data: 'ltv'
					}
				],
			});
			
			$("#myTable").addClass("cell-border stripe hover");
			//table.cell(0, 0).node().setAttribute('colspan', '3');
			setTimeout(() => {				
				window.scrollTo({
					top: $("#containerMerchant5").offset().top,
					left: 0,
					behavior: 'smooth'
				});
			}, 1000)
			
			$(window).on("resize", function(){
				myChart.resize();
			});

	    });
			 
	}
	
</script> 


<style>
body {
	color: #222;
}
.bg-branco {
	width: 100%;
	display: flex;
	flex-wrap: wrap;
	justify-content: space-evenly;
	background-color: #eee !important;
}

canvas.active {
	width: 600px !important;
	height: 600px !important;
}

canvas.bar {
	width: 1200px !important;
}

.select2-container {
	width: 200px !important;
	margin: 5px 10px;
    border-radius: 0;
    border: 1px solid #dddddd !important;
    outline: 0;
}

.select2-container--default .select2-selection--single {
	padding: 20px 0px !important;
	border: none !important;
}	

.select2-container--default .select2-selection--single .select2-selection__rendered {
	margin-top: -15px;
}

.select2-container--default .select2-results>.select2-results__options {
	overflow-x: hidden;
}

.container-layout-button{
	display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: end;
    padding-bottom: 10px;
}

.ltvContainer{
	margin: 15px 40px 
}

.containerMerchant {
	padding-top: 30px;
	width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.containerMerchant5 {
	padding-top: 30px;
	width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dataContainer,
.mais-vendidos,  
.mais-vendidos-periodo,
.pdv-mais-ativos,
.usuarios-mais-ativos,
.carrinho-abandonado,
.ltv,
.pagamentos-por-produto,
.vendas-por-merchant {
	/*margin-bottom: 100px;*/
	display: flex;
	width: 100%;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	border: 1px solid #ccc;
    margin: 10px 0;
    padding: 0 10px 100px 10px;
	
}

.dataContainer{
	display: flex;
	flex-direction: column;
	
}

.form{
	display: flex;
	justify-content: center;
	aling-itens: center;
	gap: 1rem;
}

.tittle{
	display: flex;
	justify-content: center;
	aling-itens: center;
}

.container-input {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
}

.container-input h4 {
	color: #222;
	font-weight: normal;
	font-size: 16px;
}

.dataTables_wrapper, #myTable{
	 width: 100% !important;
}
h1 {
	font-size: 22px;
}
</style>

<?php require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>
