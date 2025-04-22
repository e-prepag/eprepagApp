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
        
		<div class="container-input button">
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
	</div>
	<div class="form-container">
	
		<div class="container-input">
			 <label>Data inicial <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d", strtotime("-7 days")); ?>" min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>" name="dt_inicial_ltv" id="dt_inicial_ltv">
        </div>
        <div class="container-input">
             <label>Data Final <span>&#128198;</span></label>
		     <input class="form-input" type="date" value="<?php echo date("Y-m-d"); ?>"min="<?php echo date("Y-m-d", strtotime("-1 years")); ?>"  max="<?php echo date("Y-m-d"); ?>"  name="dt_final_carrinho" id="dt_final_ltv">
        </div>
		
		<div class="container-input">
            <label>Usuário <span>&#128202;</span></label>
			<select style="color: black;" class="form-input select2" name="codigo_sale_mechant" id="codigo_usuario_ltv">
				<option value="">Selecione um Usuário</option>			
			</select>
        </div>
		
		<div class="container-input button">	 
			<button id="chart-usuarios-ltv" type="button" class="btn btn-success button-send" style="margin-left: 5px">Buscar</button>
		</div>
	</div>
	<label class="user-label" style="margin-top: 10px; margin-right: 10px;">Usuário Selecionado: </label>
	
	<div class="containerMerchant8"> 
		<canvas id="chartMerchant8"></canvas>
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
				 url: 'https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxUsuarios/ajaxListaUsuariosFinais.php', // URL do arquivo PHP que retorna os nomes
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
			"2": "Boleto Bancario",
			"5": "Transferência entre contas Bradesco",
			"6": "Pagamento Fácil Bradesco - Débito",
			"7": "Pagamento Fácil Bradesco - Crébito",
			"9": "Pagamento BB - Débito sua Conta",
			"A": "Pagamento Itaú - à vista (Transferência)",
			"B": "Pagamento Online HiPay",
			"P": "Pagamento Online PayPal",
			"E": "Pagamento Através de E-PREPAG CASH",
			"F": "Pagamento Visa Net",
			"G": "Pagamento Visa Crédito",
			"H": "Pagamento Visa Maestro",
			"I": "Pagamento Mastercard Crédito",
			"J": "Pagamento Elo Débito",
			"K": "Pagamento Elo Crédito",
			"L": "Pagamento Diners Crédito",
			"M": "Pagamento Discover Crédito",
			"O": "Ofertas",
			"Q": "Pagamentos MCOIN - celular",
			"R": "PIX",
			"S": "E-PREPAG CASH personalizado",
			"Z": "Z"
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
	  
	  function chart_merchant(){
		  let data_inicial = $("#dt_inicial_merchant").val();
		  let data_final = $("#dt_final_merchant").val();
		  let operadora = $("#codigo_sale_mechant").val();
		  let periodo = parseInt($("#periodo").val()); 
		  
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
		
		console.log(data_inicial, data_final, produto, periodo);
		$.ajax({
			  method: "post",
			  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxUsuarios/ajaxMediaMovel.php",
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
			
			console.log(datainicial, datafinal);
			$.ajax({
			  method: "post",
			  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxUsuarios/ajaxProdutosVendidos.php",
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
			
			console.log(datainicial, datafinal, produto);
			$.ajax({
				method: "post",
				url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxUsuarios/ajaxPeriodoPagamento.php",
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
			
			$.ajax({
				method: "post",
				url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxUsuarios/ajaxCarrinhoAbandonado.php",
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
				url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxUsuarios/ajaxPeriodoProduto.php",
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
			  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxUsuariosAtivos.php",
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
							label: "Quantidade Vendida",
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
		
		console.log(datainicial, datafinal, ltv_id);

		$.ajax({
		  method: "post",
		  url: "https://<?php echo $server_url_complete; ?>/dashboard/pdv/ajaxUsuarios/ajaxLtv.php",
		  data: { id: ltv_id, datainicial, datafinal },
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
			  
			console.log(parseFloat(dataValues[0]));
			
			const data = {
			  labels: ["Gráfico do LTV"],
			  datasets: [{
				label: "LTV",
				data: [parseFloat(dataValues[0])],
			  }]
			};
			
			const options = {};
			
			$("#chartMerchant8").remove();
			$(".containerMerchant8").append('<canvas id="chartMerchant8" class="active bar"></canvas>');
			const ctx = document.getElementById('chartMerchant8');
		
			
			let myChart = new Chart(ctx, {
			  type: "bar",
			  data: data,
			  options: options
			});

			
			setTimeout(() => {				
				window.scrollTo({
					top: $("#chartMerchant8").offset().top + 300,
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
</style>
<?php require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php"; ?>
