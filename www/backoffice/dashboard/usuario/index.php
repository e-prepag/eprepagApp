<?php
require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>

<div class="container-sales-product">
    <div class="container-title">
		<h1 class="title-product">Vendas por Merchant</h1>
		<div class="container-title-icone">
				<span class="material-symbols-outlined icone-question">help</span>
		        <div class="calculo-merchant hidden">
                     <b>Legendas:</b><br> * <u>MMA</u>: Média Móvel Aritmética<br>* <u>FD</u>: Fechamento Total do dia<br>* <u>DA</u>: Total de Dias Anteriores<br><br>
				     <b>Formula:</b><br> * MMA = (FD1 + FD2 + FD3 + FD4 + FD5) ÷ DA
				</div>
		</div>
    </div>
	<div class="form-container">
        <div class="container-input">
             <label>Data inicial <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d", strtotime("-7 days")); ?>" min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>" name="dt_inicial_merchant" id="dt_inicial_merchant">
        </div>
        <div class="container-input">
             <label>Data Final <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d"); ?>"min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>"  name="dt_final_merchant" id="dt_final_merchant">
        </div>
        <div class="container-input">
            <label>Merchant <span>&#128202;</span></label>
			<select style="color: black;" class="form-input js-example-basic-single" name="codigo_sale_mechant" id="codigo_sale_mechant">
				<option value="">Selecione um Merchant</option>
			</select>
        </div>
        <div class="container-input">
             <label>Dias Anteriores <span>&#128204;</span></label>
             <input class="form-input" type="number" min="1" value="7" name="periodo" id="periodo">    
        </div>
		<div class="container-input button">
			 <button id="chart-merchant" type="button" class="btn btn-success button-send">Buscar</button>
		</div>
	</div>
	<div class="containerMerchant"> 
		<canvas id="chartMerchant"></canvas>
	</div>
</div>

<div class="container-sales-product">
	<div class="container-title">
		<h1 class="title-product">Vendas por Produto</h1>
		<div class="container-title-icone">
			<span class="material-symbols-outlined icone-question">help</span>
			<div class="calculo-merchant hidden">
				 <b>Legendas:</b><br> * <u>MMA</u>: Média Móvel Aritmética<br>* <u>FD</u>: Fechamento Total do dia<br>* <u>DA</u>: Total de Dias Anteriores<br><br>
				 <b>Formula:</b><br> * MMA = (FD1 + FD2 + FD3 + FD4 + FD5) ÷ DA
			</div>
		</div>
    </div>
	<div class="form-container">
        <div class="container-input">
             <label>Data inicial <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d", strtotime("-7 days")); ?>" min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>" name="dt_inicial_merchant" id="dt_inicial_produto">
        </div>
        <div class="container-input">
             <label>Data Final <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d"); ?>"min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>"  name="dt_final_merchant" id="dt_final_produto">
        </div>
        <div class="container-input">
            <label>Produto <span>&#128722;</span></label>
			<select style="color: black;" class="form-input js-example-basic-single" name="codigo_sale_mechant" id="codigo_sale_produto">
				<option value="">Selecione um Produto</option>
			</select>
        </div>
        <div class="container-input">
             <label>Dias Anteriores <span>&#128204;</span></label>
             <input class="form-input" type="number" min="1" value="7" name="periodo" id="periodo_produto">    
        </div>
		<div class="container-input button">
			<button id="chart-produto" type="button" class="btn btn-success button-send">Buscar</button>
		</div>
	</div>
	<div class="containerMerchant2"> 
		<canvas id="chartMerchant2"></canvas>
	</div>
</div>

<div class="container-sales-product">
	<div class="container-title">
		<h1 class="title-product">Produtos mais Vendidos</h1>
    </div>
	<div class="form-container">
        <div class="container-input">
             <label>Data inicial <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d", strtotime("-7 days")); ?>" min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>" name="dt_inicial_produtos_vendidos" id="dt_inicial_produtos_vendidos">
        </div>
        <div class="container-input">
             <label>Data Final <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d"); ?>"min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>"  name="dt_final_produtos_vendidos" id="dt_final_produtos_vendidos">
        </div>
        
		<div class="container-input button">
			<button id="chat-produtos-vendidos" type="button" class="btn btn-success button-send">Buscar</button>
		</div>
	</div>
	<div class="containerMerchant3"> 
		<canvas id="chartMerchant3"></canvas>
	</div>
</div>

<div class="container-sales-product">
	<div class="container-title">
		<h1 class="title-product">Principais Formas de Pagamento por Produto</h1>
    </div>
	
	<div class="form-container">
		<div class="container-input">
             <label>Data inicial <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d", strtotime("-7 days")); ?>" min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>" name="dt_inicial_pagamento_periodo" id="dt_inicial_pagamento_periodo">
        </div>
        <div class="container-input">
             <label>Data Final <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d"); ?>"min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>"  name="dt_final_produtos_vendidos" id="dt_final_pagamento_periodo">
        </div>
		
		<div class="container-input">
            <label>Produto <span>&#128202;</span></label>
			<select style="color: black;" class="form-input js-example-basic-single" name="codigo_sale_mechant" id="codigo_pagamento_periodo">
				<option value="">Selecione um Produto</option>
			</select>
        </div>
        
		<div class="container-input button" style="margin-left: 10px;">
			<button id="chat-pagamentos" type="button" class="btn btn-success button-send">Buscar</button>
		</div>
	</div>
	<div class="containerMerchant4"> 
		<canvas id="chartMerchant4"></canvas>
	</div>
</div>

<div class="container-sales-product">
	<div class="container-title">
		<h1 class="title-product">Carrinho Abandonado</h1>
    </div>
	
	<div class="form-container">
		<div class="container-input">
             <label>Data inicial <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d", strtotime("-7 days")); ?>" min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>" name="dt_inicial_carrinho" id="dt_inicial_carrinho">
        </div>
        <div class="container-input">
             <label>Data Final <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d"); ?>"min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>"  name="dt_final_carrinho" id="dt_final_carrinho">
        </div>
		
        
		<div class="container-input button">
			<button id="chart-carrinho" type="button" class="btn btn-success button-send">Buscar</button>
		</div>
	</div>
	<div class="containerMerchant5"> 
		<canvas id="chartMerchant5"></canvas>
	</div>
</div>

<div class="container-sales-product">
	<div class="container-title">
		<h1 class="title-product">Produtos mais Vendidos por Período</h1>
	</div>
	
	<div class="form-container">
		<div class="container-input">
            <label>Período <span>&#128202;</span></label>
			<select style="color: black;" class="form-input js-example-basic-single" name="codigo_sale_mechant" id="codigo_produto_periodo">
				<option value="">Selecione um Período</option>
				<option value="week">Semanal</option>
				<option value="day">Quinzenal</option>
				<option value="month">Mensal</option>
			</select>
        </div>
		
		<div class="container-input button">
			<button id="chart-produto-periodo" type="button" class="btn btn-success button-send">Buscar</button>
		</div>
	</div>
	<div class="containerMerchant6"> 
		<canvas id="chartMerchant6"></canvas>
	</div>
</div>

<div class="container-sales-product">
	<div class="container-title">
		<h1 class="title-product">Usuários Cadastrados x Usuários Ativos</h1>
	</div>
	<div class="form-container">
	
		
		<div class="container-input button">
			<button id="chart-usuarios-ativos" type="button" class="btn btn-success button-send">Buscar</button>
		</div>
	</div>
	<div class="containerMerchant7"> 
		<canvas id="chartMerchant7"></canvas>
	</div>
</div>

<div class="container-sales-product">
	<div class="container-title">
		<h1 class="title-product">LTV</h1>
		<div class="container-title-icone">
			<span class="material-symbols-outlined icone-question">help</span>
			<div class="calculo-merchant hidden" style="z-index:999">
				 <b>Legendas:</b><br> * <u>LTV</u>: Life Time Value (Valor do Tempo de Vida)<br><br>
				 <b>Formula:</b><br>(Ticket Médio da Empresa * Média do número de mensalidades a cada ano) * média de tempo de relacionamento
			</div>
		</div>
	</div>
	<div class="form-container">
	
		<div class="container-input">
			 <label>Data inicial <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d", strtotime("-1 year")); ?>" max="<?php echo date("Y-m-d"); ?>" name="dt_inicial_ltv" id="dt_inicial_ltv">
        </div>
        <div class="container-input">
             <label>Data Final <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d"); ?>"min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>"  name="dt_final_carrinho" id="dt_final_ltv">
        </div>
		
		<!-- <div class="container-input">
            <label>Usuário <span>&#128202;</span></label>
			<select style="color: black;" class="form-input select2" name="codigo_sale_mechant" id="codigo_usuario_ltv">
				<option value="">Selecione um Usuário</option>			
			</select>
        </div> -->
		
		<div class="container-input button">	 
			<button id="chart-usuarios-ltv" type="button" class="btn btn-success button-send" style="margin-left: 5px">Buscar</button>
		</div>
	</div>
	<label class="user-label" style="margin-top: 10px; margin-right: 10px;">Usuário Selecionado: </label>
	
	<div id="containerMerchant8" class="containerMerchant8">
	</div>
</div>

<script>

  $(document).ready(function(){

      //$('#codigo_sale_mechant').select2();
	  var ltv_id = null
	  var selectOperadoras = $("#codigo_sale_mechant");
	  var operadorasResultado = [];
	  let produtosResultado = [];
      var selectPdvs = $("#codigo_pdv_ltv");
	  let selectProdutos = $("#codigo_sale_produto");
	  let selectProdutosPeriodo = $("#codigo_pagamento_periodo");
	  let selectUsuariosLtv = $("#codigo_usuario_ltv");
	  
	  $(".user-label").hide();
	  
	  function display_nome(ug_id, ug_nome) {
		let userLabel = $(".user-label");
		userLabel.text(`Usuário selecionado: ${ug_nome}`);
		userLabel.show();
		ltv_id = ug_id;
	  }
	  
	  selectOperadoras.select2();
	  selectPdvs.select2();
	  selectProdutos.select2();
	  selectProdutosPeriodo.select2();
	  $.ajax({
		  url: "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/pt-BR.js",
		  dataType: 'script',
		  cache: true
		}).done(function() {
			selectUsuariosLtv.select2({
			  // mínimo de 3 caracteres para ativar a pesquisa
			  minimumInputLength: 2,
			  closeOnSelect: true,
			  language: "pt-BR",
			  ajax: {
				 url: 'https://<?php echo $server_url_complete; ?>/dashboard/usuario/ajaxListaUsuariosFinais.php', // URL do arquivo PHP que retorna os nomes
				 dataType: 'json',
				 
				 delay: 250, // tempo de atraso para aguardar a entrada do usuário antes de enviar a solicitação de pesquisa
				 data: function (params) {
					return {
					   q: params.term, // termo de pesquisa
					   page: params.page || 1 // página atual
					};
				 },
				 processResults: function (data, params) {
					params.page = params.page || 1;
					return {
					   results: data.nomes,
					   pagination: {
						  more: data.more // define se há mais resultados para carregar
					   }
					};
				 }
			  },
			  templateResult: function (data) { 
				var $result = $('<span></span>');
				$result.text(data.ug_nome);
				
				$result.on('click', function() {
				  selectUsuariosLtv.val(data.ug_id);
				  display_nome(data.ug_id, data.ug_nome);
				  selectUsuariosLtv.select2("close");
				});
				
				return $result;
			  }, // exibe apenas o texto dos resultados
			  templateSelection: function(data) {
				  return "Selecione seu Usuário";
			  }
		});
	  });
	  
      var pdvsResultado = [];
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
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxListaPdvs.php",
		  }).done(function(operadoras){
			 $.each(operadoras, function(index, value){ 
			     pdvsResultado[value.ug_id] = value.ug_nome_fantasia;
				 selectPdvs.append('<option value="'+value.ug_id+'">'+value.ug_nome_fantasia+'</option>');
			 });
	  });
	  
	  $.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxProdutos.php",
		  data: {type: 2}
		  }).done(function(operadoras){ 
			 $.each(operadoras, function(index, value){ 
			     produtosResultado[value.ogp_id] = value.ogp_nome;
				selectProdutos.append('<option value="'+value.ogp_id+'" style="color: #222 !important;">'+value.ogp_nome+'</option>');
				selectProdutosPeriodo.append('<option value="'+value.ogp_id+'" style="color: #222 !important;">'+value.ogp_nome+'</option>');
			 });
	  });
	  
	    let tipos = {
			"1": "Transferência Bancaria",
			"2": "Boleto Bancário",
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
	 
	  $(".icone-question").on("click", function(){
		  if($(".calculo-merchant").hasClass("hidden")){
			  $(".calculo-merchant").switchClass( "hidden", "show", 1000, "easeInOutQuad");
		  }else{
			  $(".calculo-merchant").switchClass( "show", "hidden", 1000, "easeInOutQuad");
		  }
	  });
	  
	  $("#chart-merchant").on("click", function(){
		 chart_merchant();
	  });
	  
	  $("#chart-produto").on("click", function() {
		chart_produtos();
	  });
	  
	  $("#chat-produtos-vendidos").on("click", function() {
		chart_produtos_vendidos();
	  })
	  
	  $("#chat-pagamentos").on("click", function() {
		chart_pagamentos();  
	  })
	  
	  $("#chart-carrinho").on("click", function() {
		chart_carrinho();
	  });
	  
	  $("#chart-produto-periodo").on("click", function() {
		chart_produto_periodo();
	  });
	  
	  $("#chart-usuarios-ativos").on("click", function() {
		chart_usuarios_ativos();
	  });
	  
	  $("#chart-usuarios-ltv").on("click", function() {
		chart_usuarios_ltv();
	  });
	  
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
		  let data_inicial = $("#dt_inicial_merchant").val();
		  let data_final = $("#dt_final_merchant").val();
		  let operadora = $("#codigo_sale_mechant").val();
		  let periodo = parseInt($("#periodo").val()); 
		  
		  let datasValidas = verifica_datas(data_inicial, data_final);
		  
		  if(!datasValidas) return;
			  
		  $.ajax({
			  method: "post",
			  url: "https://<?php echo $server_url_complete; ?>/dashboard/usuario/ajaxSalesProduct.php",
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
				 $("#chartMerchant").remove();
				 $(".containerMerchant").append('<canvas id="chartMerchant"></canvas>');
				 let ctx = document.getElementById('chartMerchant');  
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
						top: $("#chartMerchant").offset().top,
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
	    
	  
	  
	  function chart_produtos() {
		let data_inicial = $("#dt_inicial_produto").val();
		let data_final = $("#dt_final_produto").val();
		let produto = $("#codigo_sale_produto").val();
		let periodo = parseInt($("#periodo_produto").val()); 
		
		let datasValidas = verifica_datas(data_inicial, data_final);  
		if(!datasValidas) return;
		
		$.ajax({
			  method: "post",
			  url: "https://<?php echo $server_url_complete; ?>/dashboard/usuario/ajaxMediaMovel.php",
			  data: { id: produto, datainicial: data_inicial, datafinal: data_final, periodo: periodo},
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
			  console.log(dataValues);
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
						  console.log(obj);
						  resultado.push(obj);
					  }
					  
					  return resultado;
				  }
		
				 let resultadoDasMediasMoveis = mediaMovel(dataValues, periodo);
				 console.log(resultadoDasMediasMoveis);
				 $("#chartMerchant2").remove();
				 $(".containerMerchant2").append('<canvas id="chartMerchant2"></canvas>');
				 let ctx = document.getElementById('chartMerchant2');  
					   var myChart = new Chart(ctx, {
							type: 'line',
							data: {
							  datasets: [{
									label: produtosResultado[produto],
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
		
		function chart_produtos_vendidos() {
			let datainicial = $("#dt_inicial_produtos_vendidos").val();
			let datafinal = $("#dt_final_produtos_vendidos").val();
			
			let datasValidas = verifica_datas(datainicial, datafinal);
			if(!datasValidas) return;
		  
			console.log(datainicial, datafinal);
			$.ajax({
			  method: "post",
			  url: "https://<?php echo $server_url_complete; ?>/dashboard/usuario/ajaxProdutosVendidos.php",
			  data: {datainicial: datainicial, datafinal: datafinal},
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
			console.log(dataValues); 
				if(dataValues.length > 0) {
					$("#chartMerchant3").remove();
					$(".containerMerchant3").append('<canvas id="chartMerchant3"></canvas>');
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
				}
				else{
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
		
		
		function chart_pagamentos() {
			let datainicial = $("#dt_inicial_pagamento_periodo").val();
			let datafinal = $("#dt_final_pagamento_periodo").val();
			let produto = $("#codigo_pagamento_periodo").val();
			
			let datasValidas = verifica_datas(datainicial, datafinal);
			if(!datasValidas) return;
			console.log(datainicial, datafinal, produto);
			$.ajax({
				method: "post",
				url: "https://<?php echo $server_url_complete; ?>/dashboard/usuario/ajaxPeriodoPagamento.php",
				data: {id: produto, datainicial: datainicial, datafinal: datafinal},
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
				
				console.log(dataValues);
				if(dataValues.length > 0) {
						
					for(let x of dataValues) {
						console.log(x);
						x.forma_pagamento = tipos[x.forma_pagamento];
					}
					$("#chartMerchant4").remove();
					$(".containerMerchant4").append('<canvas id="chartMerchant4"></canvas>');
					const ctx = document.getElementById('chartMerchant4');
					
					const data = {
					  labels: dataValues.map(obj => obj.ogp_nome),
					  datasets: [{
						label: 'Quantidade',
						data: dataValues.map(obj => obj.qtde)
					  }],
					};
					
					let myChart = new Chart(ctx, {
						type: 'bar',
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
							top: $("#chartMerchant4").offset().top + 300,
							left: 0,
							behavior: 'smooth'
						});
					}, 1000)
				}
				else{
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
		
		function chart_carrinho() {
			let datainicial = $("#dt_inicial_carrinho").val();
			let datafinal = $("#dt_final_carrinho").val();
			
			let datasValidas = verifica_datas(datainicial, datafinal);
			if(!datasValidas) return;
			
			$.ajax({
				method: "post",
				url: "https://<?php echo $server_url_complete; ?>/dashboard/usuario/ajaxCarrinhoAbandonado.php",
				data: {datainicial, datafinal},
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
			}).done(function(dataValues) {
				
				console.log(dataValues);
				for(let x of dataValues) {
					console.log(x);
					x.forma_pagamento = tipos[x.forma_pagamento];
				}
				$("#chartMerchant5").remove();
				$(".containerMerchant5").append('<canvas id="chartMerchant5"></canvas>');
				const ctx = document.getElementById('chartMerchant5');
				
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
						top: $("#chartMerchant5").offset().top + 300,
						left: 0,
						behavior: 'smooth'
					});
				}, 1000)
			});
		}
		
		function chart_produto_periodo() {
			
			let periodo = $("#codigo_produto_periodo").val();
			
			$.ajax({
				method: "post",
				url: "https://<?php echo $server_url_complete; ?>/dashboard/usuario/ajaxPeriodoProduto.php",
				data: {periodo},
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
			}).done(function(dataValues) {
				console.log(dataValues);
				
				if(dataValues.length > 0) {
					let nomeLabels = dataValues.map(x => x.nome);
			
			
					$("#chartMerchant6").remove();
					$(".containerMerchant6").append('<canvas id="chartMerchant6" class="pie"></canvas>');
					const ctx = document.getElementById('chartMerchant6');
					ctx.style.width = "200px";
					let myChart = new Chart(ctx, {
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
							top: $("#chartMerchant6").offset().top + 300,
							left: 0,
							behavior: 'smooth'
						});
					}, 1000);
				}
			});
		}
		
		function chart_usuarios_ativos() {
			$.ajax({
			  method: "post",
			  url: "https://<?php echo $server_url_complete; ?>/dashboard/usuario/ajaxUsuariosAtivos.php",
			  data: {},
			  beforeSend: function() {
				Swal.fire({
				  title: 'Carregando informações!',
				  html: '<p>Coletando as informações necessarias</p><img style="width: 80px;" src="../../images/loading.gif">',
				  showConfirmButton: false,
				  allowOutsideClick: false,
				  allowEscapeKey: false
			  })},	
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
				$("#chartMerchant7").remove();
				$(".containerMerchant7").append('<canvas id="chartMerchant7" class="pie"></canvas>');
				const ctx = document.getElementById('chartMerchant7');
				let myChart = new Chart(ctx, {
					type: 'pie',
					data: {
					  labels: ["Não realizaram primeiro pedido", "Realizaram primeiro pedido"],
					  datasets: [{
							label: "Quantidade de Usuários",
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
				
				ctx.style.width = "200px !important";
				ctx.style.height = "200px !important";
				
				setTimeout(() => {				
					window.scrollTo({
						top: $("#chartMerchant7").offset().top + 300,
						left: 0,
						behavior: 'smooth'
					});
				}, 1000)
				
			  });
			  
			$(window).on("resize", function(){
				myChart.resize();
			});
		}
	
	function chart_usuarios_ltv() {
		let datainicial = $("#dt_inicial_ltv").val();
		let datafinal = $("#dt_final_ltv").val();
		
		let datasValidas = verifica_datas(datainicial, datafinal);
		if(!datasValidas) return;
		
		$.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/usuario/ajaxLtv.php",
		  data: { datainicial, datafinal },
		  beforeSend: function() {
				Swal.fire({
				  title: 'Carregando informações!',
				  html: '<p>Coletando as informações necessarias</p><img style="width: 80px;" src="../../images/loading.gif">',
				  showConfirmButton: false,
				  allowOutsideClick: false,
				  allowEscapeKey: false
			  })},	
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
			  
			//console.log(parseFloat(dataValues[0]));
			const options = { style: 'currency', currency: 'BRL', minimumFractionDigits: 2, maximumFractionDigits: 3 };
            const formatNumber = new Intl.NumberFormat('pt-BR', options);
			
			const data = {
			  labels: dataValues.map(obj => obj.usuario),
			  datasets: [{
				label: "LTV",
				data: dataValues.map(obj => formatNumber.format(obj.ltv)), //parseFloat(dataValues[0])
			  }]
			};
			
			//const options = {};
		
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
			
			$('#containerMerchant8').append(newTable);
			$("#myTable_filter").css("margin-right", "20px");
			$("#myTable").addClass("cell-border stripe hover");
			
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
						data: 'usuario'						
					},
					{ 
						title: 'LTV',
						data: 'ltv'
					}
				],
			});
			
			setTimeout(() => {				
				window.scrollTo({
					top: $("#containerMerchant8").offset().top + 300,
					left: 0,
					behavior: 'smooth'
				});
			}, 1000)
			
			$(window).on("resize", function(){
				myChart.resize();
			});
		 })
	}
	
	$('#codigo_usuario_ltv').on('select2:scroll', function (e) {
	   var data = $('#codigo_usuario_ltv').select2('data');
	   var page = data.length / 100; // divide o número de resultados atuais por 100 para determinar a página atual
	   $('#codigo_usuario_ltv').select2('trigger', 'query', { page: page + 1 }); // carrega a próxima página de resultados
	});
	
	
  });
  
</script>

<style>
body {
	color: #222;
}

.select2-container {
	width: 200px !important;
}

.select2-container--default .select2-selection--single {
	padding: 20px 0px !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
	margin-top: -15px;
}

.select2-container--default .select2-results>.select2-results__options {
	overflow-x: hidden !important;
}

.pie {
	width: 800px !important;
	height: 800px !important;
	text-align: center;
	margin: 0 auto;
}

.user-label {
	margin: 0px auto;
	text-align: center;
	width: 100%;
}

#myTable {
	color: #222;
}


@charset "UTF-8";
:root {
  --dt-row-selected: 13, 110, 253;
  --dt-row-selected-text: 255, 255, 255;
  --dt-row-selected-link: 9, 10, 11;
}
 
table.dataTable td.dt-control {
  text-align: center;
  cursor: pointer;
}
table.dataTable td.dt-control:before {
  height: 1em;
  width: 1em;
  margin-top: -9px;
  display: inline-block;
  color: white;
  border: 0.15em solid white;
  border-radius: 1em;
  box-shadow: 0 0 0.2em #444;
  box-sizing: content-box;
  text-align: center;
  text-indent: 0 !important;
  font-family: "Courier New", Courier, monospace;
  line-height: 1em;
  content: "+";
  background-color: #31b131;
}
table.dataTable tr.dt-hasChild td.dt-control:before {
  content: "-";
  background-color: #d33333;
}
 
table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting_asc_disabled, table.dataTable thead > tr > th.sorting_desc_disabled,
table.dataTable thead > tr > td.sorting,
table.dataTable thead > tr > td.sorting_asc,
table.dataTable thead > tr > td.sorting_desc,
table.dataTable thead > tr > td.sorting_asc_disabled,
table.dataTable thead > tr > td.sorting_desc_disabled {
  cursor: pointer;
  position: relative;
  padding-right: 26px;
}
table.dataTable thead > tr > th.sorting:before, table.dataTable thead > tr > th.sorting:after, table.dataTable thead > tr > th.sorting_asc:before, table.dataTable thead > tr > th.sorting_asc:after, table.dataTable thead > tr > th.sorting_desc:before, table.dataTable thead > tr > th.sorting_desc:after, table.dataTable thead > tr > th.sorting_asc_disabled:before, table.dataTable thead > tr > th.sorting_asc_disabled:after, table.dataTable thead > tr > th.sorting_desc_disabled:before, table.dataTable thead > tr > th.sorting_desc_disabled:after,
table.dataTable thead > tr > td.sorting:before,
table.dataTable thead > tr > td.sorting:after,
table.dataTable thead > tr > td.sorting_asc:before,
table.dataTable thead > tr > td.sorting_asc:after,
table.dataTable thead > tr > td.sorting_desc:before,
table.dataTable thead > tr > td.sorting_desc:after,
table.dataTable thead > tr > td.sorting_asc_disabled:before,
table.dataTable thead > tr > td.sorting_asc_disabled:after,
table.dataTable thead > tr > td.sorting_desc_disabled:before,
table.dataTable thead > tr > td.sorting_desc_disabled:after {
  position: absolute;
  display: block;
  opacity: 0.125;
  right: 10px;
  line-height: 9px;
  font-size: 0.8em;
}
table.dataTable thead > tr > th.sorting:before, table.dataTable thead > tr > th.sorting_asc:before, table.dataTable thead > tr > th.sorting_desc:before, table.dataTable thead > tr > th.sorting_asc_disabled:before, table.dataTable thead > tr > th.sorting_desc_disabled:before,
table.dataTable thead > tr > td.sorting:before,
table.dataTable thead > tr > td.sorting_asc:before,
table.dataTable thead > tr > td.sorting_desc:before,
table.dataTable thead > tr > td.sorting_asc_disabled:before,
table.dataTable thead > tr > td.sorting_desc_disabled:before {
  bottom: 50%;
  content: "?";
  content: "?"/"";
}
table.dataTable thead > tr > th.sorting:after, table.dataTable thead > tr > th.sorting_asc:after, table.dataTable thead > tr > th.sorting_desc:after, table.dataTable thead > tr > th.sorting_asc_disabled:after, table.dataTable thead > tr > th.sorting_desc_disabled:after,
table.dataTable thead > tr > td.sorting:after,
table.dataTable thead > tr > td.sorting_asc:after,
table.dataTable thead > tr > td.sorting_desc:after,
table.dataTable thead > tr > td.sorting_asc_disabled:after,
table.dataTable thead > tr > td.sorting_desc_disabled:after {
  top: 50%;
  content: "?";
  content: "?"/"";
}
table.dataTable thead > tr > th.sorting_asc:before, table.dataTable thead > tr > th.sorting_desc:after,
table.dataTable thead > tr > td.sorting_asc:before,
table.dataTable thead > tr > td.sorting_desc:after {
  opacity: 0.6;
}
table.dataTable thead > tr > th.sorting_desc_disabled:after, table.dataTable thead > tr > th.sorting_asc_disabled:before,
table.dataTable thead > tr > td.sorting_desc_disabled:after,
table.dataTable thead > tr > td.sorting_asc_disabled:before {
  display: none;
}
table.dataTable thead > tr > th:active,
table.dataTable thead > tr > td:active {
  outline: none;
}
 
div.dataTables_scrollBody > table.dataTable > thead > tr > th:before, div.dataTables_scrollBody > table.dataTable > thead > tr > th:after,
div.dataTables_scrollBody > table.dataTable > thead > tr > td:before,
div.dataTables_scrollBody > table.dataTable > thead > tr > td:after {
  display: none;
}
 
div.dataTables_processing {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 200px;
  margin-left: -100px;
  margin-top: -26px;
  text-align: center;
  padding: 2px;
}
div.dataTables_processing > div:last-child {
  position: relative;
  width: 80px;
  height: 15px;
  margin: 1em auto;
}
div.dataTables_processing > div:last-child > div {
  position: absolute;
  top: 0;
  width: 13px;
  height: 13px;
  border-radius: 50%;
  background: #0d6efd;
  background: rgb(var(--dt-row-selected));
  animation-timing-function: cubic-bezier(0, 1, 1, 0);
}
div.dataTables_processing > div:last-child > div:nth-child(1) {
  left: 8px;
  animation: datatables-loader-1 0.6s infinite;
}
div.dataTables_processing > div:last-child > div:nth-child(2) {
  left: 8px;
  animation: datatables-loader-2 0.6s infinite;
}
div.dataTables_processing > div:last-child > div:nth-child(3) {
  left: 32px;
  animation: datatables-loader-2 0.6s infinite;
}
div.dataTables_processing > div:last-child > div:nth-child(4) {
  left: 56px;
  animation: datatables-loader-3 0.6s infinite;
}
 
@keyframes datatables-loader-1 {
  0% {
    transform: scale(0);
  }
  100% {
    transform: scale(1);
  }
}
@keyframes datatables-loader-3 {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(0);
  }
}
@keyframes datatables-loader-2 {
  0% {
    transform: translate(0, 0);
  }
  100% {
    transform: translate(24px, 0);
  }
}
table.dataTable.nowrap th, table.dataTable.nowrap td {
  white-space: nowrap;
}
table.dataTable th.dt-left,
table.dataTable td.dt-left {
  text-align: left;
}
table.dataTable th.dt-center,
table.dataTable td.dt-center,
table.dataTable td.dataTables_empty {
  text-align: center;
}
table.dataTable th.dt-right,
table.dataTable td.dt-right {
  text-align: right;
}
table.dataTable th.dt-justify,
table.dataTable td.dt-justify {
  text-align: justify;
}
table.dataTable th.dt-nowrap,
table.dataTable td.dt-nowrap {
  white-space: nowrap;
}
table.dataTable thead th,
table.dataTable thead td,
table.dataTable tfoot th,
table.dataTable tfoot td {
  text-align: left;
}
table.dataTable thead th.dt-head-left,
table.dataTable thead td.dt-head-left,
table.dataTable tfoot th.dt-head-left,
table.dataTable tfoot td.dt-head-left {
  text-align: left;
}
table.dataTable thead th.dt-head-center,
table.dataTable thead td.dt-head-center,
table.dataTable tfoot th.dt-head-center,
table.dataTable tfoot td.dt-head-center {
  text-align: center;
}
table.dataTable thead th.dt-head-right,
table.dataTable thead td.dt-head-right,
table.dataTable tfoot th.dt-head-right,
table.dataTable tfoot td.dt-head-right {
  text-align: right;
}
table.dataTable thead th.dt-head-justify,
table.dataTable thead td.dt-head-justify,
table.dataTable tfoot th.dt-head-justify,
table.dataTable tfoot td.dt-head-justify {
  text-align: justify;
}
table.dataTable thead th.dt-head-nowrap,
table.dataTable thead td.dt-head-nowrap,
table.dataTable tfoot th.dt-head-nowrap,
table.dataTable tfoot td.dt-head-nowrap {
  white-space: nowrap;
}
table.dataTable tbody th.dt-body-left,
table.dataTable tbody td.dt-body-left {
  text-align: left;
}
table.dataTable tbody th.dt-body-center,
table.dataTable tbody td.dt-body-center {
  text-align: center;
}
table.dataTable tbody th.dt-body-right,
table.dataTable tbody td.dt-body-right {
  text-align: right;
}
table.dataTable tbody th.dt-body-justify,
table.dataTable tbody td.dt-body-justify {
  text-align: justify;
}
table.dataTable tbody th.dt-body-nowrap,
table.dataTable tbody td.dt-body-nowrap {
  white-space: nowrap;
}
 
/*
 * Table styles
 */
table.dataTable {
  width: 100%;
  margin: 0 auto;
  clear: both;
  border-collapse: separate;
  border-spacing: 0;
  /*
   * Header and footer styles
   */
  /*
   * Body styles
   */
}
table.dataTable thead th,
table.dataTable tfoot th {
  font-weight: bold;
}
table.dataTable thead th,
table.dataTable thead td {
  padding: 10px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.3);
}
table.dataTable thead th:active,
table.dataTable thead td:active {
  outline: none;
}
table.dataTable tfoot th,
table.dataTable tfoot td {
  padding: 10px 10px 6px 10px;
  border-top: 1px solid rgba(0, 0, 0, 0.3);
}
table.dataTable tbody tr {
  background-color: transparent;
}
table.dataTable tbody tr.selected > * {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.9);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected), 0.9);
  color: white;
  color: rgb(var(--dt-row-selected-text));
}
table.dataTable tbody tr.selected a {
  color: #090a0b;
  color: rgb(var(--dt-row-selected-link));
}
table.dataTable tbody th,
table.dataTable tbody td {
  padding: 8px 10px;
}
table.dataTable.row-border tbody th, table.dataTable.row-border tbody td, table.dataTable.display tbody th, table.dataTable.display tbody td {
  border-top: 1px solid rgba(0, 0, 0, 0.15);
}
table.dataTable.row-border tbody tr:first-child th,
table.dataTable.row-border tbody tr:first-child td, table.dataTable.display tbody tr:first-child th,
table.dataTable.display tbody tr:first-child td {
  border-top: none;
}
table.dataTable.cell-border tbody th, table.dataTable.cell-border tbody td {
  border-top: 1px solid rgba(0, 0, 0, 0.15);
  border-right: 1px solid rgba(0, 0, 0, 0.15);
}
table.dataTable.cell-border tbody tr th:first-child,
table.dataTable.cell-border tbody tr td:first-child {
  border-left: 1px solid rgba(0, 0, 0, 0.15);
}
table.dataTable.cell-border tbody tr:first-child th,
table.dataTable.cell-border tbody tr:first-child td {
  border-top: none;
}
table.dataTable.stripe > tbody > tr.odd > *, table.dataTable.display > tbody > tr.odd > * {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.023);
}
table.dataTable.stripe > tbody > tr.odd.selected > *, table.dataTable.display > tbody > tr.odd.selected > * {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.923);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.923));
}
table.dataTable.hover > tbody > tr:hover > *, table.dataTable.display > tbody > tr:hover > * {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.035);
}
table.dataTable.hover > tbody > tr.selected:hover > *, table.dataTable.display > tbody > tr.selected:hover > * {
  box-shadow: inset 0 0 0 9999px #0d6efd !important;
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 1)) !important;
}
table.dataTable.order-column > tbody tr > .sorting_1,
table.dataTable.order-column > tbody tr > .sorting_2,
table.dataTable.order-column > tbody tr > .sorting_3, table.dataTable.display > tbody tr > .sorting_1,
table.dataTable.display > tbody tr > .sorting_2,
table.dataTable.display > tbody tr > .sorting_3 {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.019);
}
table.dataTable.order-column > tbody tr.selected > .sorting_1,
table.dataTable.order-column > tbody tr.selected > .sorting_2,
table.dataTable.order-column > tbody tr.selected > .sorting_3, table.dataTable.display > tbody tr.selected > .sorting_1,
table.dataTable.display > tbody tr.selected > .sorting_2,
table.dataTable.display > tbody tr.selected > .sorting_3 {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.919);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.919));
}
table.dataTable.display > tbody > tr.odd > .sorting_1, table.dataTable.order-column.stripe > tbody > tr.odd > .sorting_1 {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.054);
}
table.dataTable.display > tbody > tr.odd > .sorting_2, table.dataTable.order-column.stripe > tbody > tr.odd > .sorting_2 {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.047);
}
table.dataTable.display > tbody > tr.odd > .sorting_3, table.dataTable.order-column.stripe > tbody > tr.odd > .sorting_3 {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.039);
}
table.dataTable.display > tbody > tr.odd.selected > .sorting_1, table.dataTable.order-column.stripe > tbody > tr.odd.selected > .sorting_1 {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.954);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.954));
}
table.dataTable.display > tbody > tr.odd.selected > .sorting_2, table.dataTable.order-column.stripe > tbody > tr.odd.selected > .sorting_2 {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.947);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.947));
}
table.dataTable.display > tbody > tr.odd.selected > .sorting_3, table.dataTable.order-column.stripe > tbody > tr.odd.selected > .sorting_3 {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.939);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.939));
}
table.dataTable.display > tbody > tr.even > .sorting_1, table.dataTable.order-column.stripe > tbody > tr.even > .sorting_1 {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.019);
}
table.dataTable.display > tbody > tr.even > .sorting_2, table.dataTable.order-column.stripe > tbody > tr.even > .sorting_2 {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.011);
}
table.dataTable.display > tbody > tr.even > .sorting_3, table.dataTable.order-column.stripe > tbody > tr.even > .sorting_3 {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.003);
}
table.dataTable.display > tbody > tr.even.selected > .sorting_1, table.dataTable.order-column.stripe > tbody > tr.even.selected > .sorting_1 {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.919);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.919));
}
table.dataTable.display > tbody > tr.even.selected > .sorting_2, table.dataTable.order-column.stripe > tbody > tr.even.selected > .sorting_2 {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.911);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.911));
}
table.dataTable.display > tbody > tr.even.selected > .sorting_3, table.dataTable.order-column.stripe > tbody > tr.even.selected > .sorting_3 {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.903);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.903));
}
table.dataTable.display tbody tr:hover > .sorting_1, table.dataTable.order-column.hover tbody tr:hover > .sorting_1 {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.082);
}
table.dataTable.display tbody tr:hover > .sorting_2, table.dataTable.order-column.hover tbody tr:hover > .sorting_2 {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.074);
}
table.dataTable.display tbody tr:hover > .sorting_3, table.dataTable.order-column.hover tbody tr:hover > .sorting_3 {
  box-shadow: inset 0 0 0 9999px rgba(0, 0, 0, 0.062);
}
table.dataTable.display tbody tr:hover.selected > .sorting_1, table.dataTable.order-column.hover tbody tr:hover.selected > .sorting_1 {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.982);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.982));
}
table.dataTable.display tbody tr:hover.selected > .sorting_2, table.dataTable.order-column.hover tbody tr:hover.selected > .sorting_2 {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.974);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.974));
}
table.dataTable.display tbody tr:hover.selected > .sorting_3, table.dataTable.order-column.hover tbody tr:hover.selected > .sorting_3 {
  box-shadow: inset 0 0 0 9999px rgba(13, 110, 253, 0.962);
  box-shadow: inset 0 0 0 9999px rgba(var(--dt-row-selected, 0.962));
}
table.dataTable.no-footer {
  border-bottom: 1px solid rgba(0, 0, 0, 0.3);
}
table.dataTable.compact thead th,
table.dataTable.compact thead td,
table.dataTable.compact tfoot th,
table.dataTable.compact tfoot td,
table.dataTable.compact tbody th,
table.dataTable.compact tbody td {
  padding: 4px;
}
 
table.dataTable th,
table.dataTable td {
  box-sizing: content-box;
}
 
/*
 * Control feature layout
 */
.dataTables_wrapper {
  position: relative;
  clear: both;
}
.dataTables_wrapper .dataTables_length {
  float: left;
}
.dataTables_wrapper .dataTables_length select {
  border: 1px solid #aaa;
  border-radius: 3px;
  padding: 5px;
  background-color: transparent;
  padding: 4px;
}
.dataTables_wrapper .dataTables_filter {
  float: right;
  text-align: right;
}
.dataTables_wrapper .dataTables_filter input {
  border: 1px solid #aaa;
  border-radius: 3px;
  padding: 5px;
  background-color: transparent;
  margin-left: 3px;
}
.dataTables_wrapper .dataTables_info {
  clear: both;
  float: left;
  padding-top: 0.755em;
}
.dataTables_wrapper .dataTables_paginate {
  float: right;
  text-align: right;
  padding-top: 0.25em;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
  box-sizing: border-box;
  display: inline-block;
  min-width: 1.5em;
  padding: 0.5em 1em;
  margin-left: 2px;
  text-align: center;
  text-decoration: none !important;
  cursor: pointer;
  color: inherit !important;
  border: 1px solid transparent;
  border-radius: 2px;
  background: transparent;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
  color: inherit !important;
  border: 1px solid rgba(0, 0, 0, 0.3);
  background-color: rgba(230, 230, 230, 0.1);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, rgba(230, 230, 230, 0.1)), color-stop(100%, rgba(0, 0, 0, 0.1)));
  /* Chrome,Safari4+ */
  background: -webkit-linear-gradient(top, rgba(230, 230, 230, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
  /* Chrome10+,Safari5.1+ */
  background: -moz-linear-gradient(top, rgba(230, 230, 230, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
  /* FF3.6+ */
  background: -ms-linear-gradient(top, rgba(230, 230, 230, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
  /* IE10+ */
  background: -o-linear-gradient(top, rgba(230, 230, 230, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
  /* Opera 11.10+ */
  background: linear-gradient(to bottom, rgba(230, 230, 230, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
  /* W3C */
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
  cursor: default;
  color: #666 !important;
  border: 1px solid transparent;
  background: transparent;
  box-shadow: none;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  color: white !important;
  border: 1px solid #111111;
  background-color: #585858;
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #585858), color-stop(100%, #111111));
  /* Chrome,Safari4+ */
  background: -webkit-linear-gradient(top, #585858 0%, #111111 100%);
  /* Chrome10+,Safari5.1+ */
  background: -moz-linear-gradient(top, #585858 0%, #111111 100%);
  /* FF3.6+ */
  background: -ms-linear-gradient(top, #585858 0%, #111111 100%);
  /* IE10+ */
  background: -o-linear-gradient(top, #585858 0%, #111111 100%);
  /* Opera 11.10+ */
  background: linear-gradient(to bottom, #585858 0%, #111111 100%);
  /* W3C */
}
.dataTables_wrapper .dataTables_paginate .paginate_button:active {
  outline: none;
  background-color: #2b2b2b;
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #2b2b2b), color-stop(100%, #0c0c0c));
  /* Chrome,Safari4+ */
  background: -webkit-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);
  /* Chrome10+,Safari5.1+ */
  background: -moz-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);
  /* FF3.6+ */
  background: -ms-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);
  /* IE10+ */
  background: -o-linear-gradient(top, #2b2b2b 0%, #0c0c0c 100%);
  /* Opera 11.10+ */
  background: linear-gradient(to bottom, #2b2b2b 0%, #0c0c0c 100%);
  /* W3C */
  box-shadow: inset 0 0 3px #111;
}
.dataTables_wrapper .dataTables_paginate .ellipsis {
  padding: 0 1em;
}
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_processing,
.dataTables_wrapper .dataTables_paginate {
  color: inherit;
}
.dataTables_wrapper .dataTables_scroll {
  clear: both;
}
.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody {
  -webkit-overflow-scrolling: touch;
}
.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody > table > thead > tr > th, .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody > table > thead > tr > td, .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody > table > tbody > tr > th, .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody > table > tbody > tr > td {
  vertical-align: middle;
}
.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody > table > thead > tr > th > div.dataTables_sizing,
.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody > table > thead > tr > td > div.dataTables_sizing, .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody > table > tbody > tr > th > div.dataTables_sizing,
.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody > table > tbody > tr > td > div.dataTables_sizing {
  height: 0;
  overflow: hidden;
  margin: 0 !important;
  padding: 0 !important;
}
.dataTables_wrapper.no-footer .dataTables_scrollBody {
  border-bottom: 1px solid rgba(0, 0, 0, 0.3);
}
.dataTables_wrapper.no-footer div.dataTables_scrollHead table.dataTable,
.dataTables_wrapper.no-footer div.dataTables_scrollBody > table {
  border-bottom: none;
}
.dataTables_wrapper:after {
  visibility: hidden;
  display: block;
  content: "";
  clear: both;
  height: 0;
}
 
@media screen and (max-width: 767px) {
  .dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
    float: none;
    text-align: center;
  }
  .dataTables_wrapper .dataTables_paginate {
    margin-top: 0.5em;
  }
}
@media screen and (max-width: 640px) {
  .dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    float: none;
    text-align: center;
  }
  .dataTables_wrapper .dataTables_filter {
    margin-top: 0.5em;
  }
}

.dataTables_wrapper, #myTable{
	 width: 100% !important;
}
</style>
<?php require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php"; ?>
