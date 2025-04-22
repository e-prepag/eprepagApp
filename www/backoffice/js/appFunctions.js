$(function() {

	/*
	$( "#filtroDataInclusaoIni, #filtroDataInclusaoFim, #filtroDataAlteracaoIni, #filtroDataAlteracaoFim, #filtroDataTransacaoIni, #filtroDataTransacaoFim, #filtroDataLogIni, #filtroDataLogFim" ).datepicker({
		showWeek: true,
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		yearRange: '2011:2025',
		dateFormat: 'dd/mm/yy'
	});
	*/
	
	$( "button" ).button();
	
	
	/** Validacao do Formulário de Busca dos JOGOS Alawar **/
    $(document).on("submit", "#formFiltro", function() {
		
		var dataInclusaoIni = $("#filtroDataInclusaoIni").val();
		var dataInclusaoFim = $("#filtroDataInclusaoFim").val();
		var dataAlteracaoIni = $("#filtroDataAlteracaoIni").val();
		var dataAlteracaoFim = $("#filtroDataAlteracaoFim").val();			
		
		var msgErro = '';
		
		if(dataInclusaoFim && !dataInclusaoIni) {
			msgErro += '* Digite a Data de Inclusão Inicial \n';
		}
				
		if(dataInclusaoFim && dataInclusaoIni) {
			if(checkDate(dataInclusaoIni, dataInclusaoFim)) {
				msgErro += '* Data de Inclusão Inicial é menor que a Data Final \n';
			}
		}
		
		if(dataAlteracaoFim && !dataAlteracaoIni) {
			msgErro += '* Digite a Data de Alteração Inicial \n';
		}

		if(dataAlteracaoFim && dataAlteracaoIni) {
			if(checkDate(dataAlteracaoIni, dataAlteracaoFim)) {
				msgErro += '* Data de Alteração Inicial é menor que a Data Final \n';
			}
		}
		
		if(msgErro){
			alert(msgErro);
			return false;
		}
	});
	

	/** Validacao do Formulário de Busca dos PEDIDOS Alawar **/
    $(document).on("submit", "#formFiltroPedidos", function() {
		
		var dataTransacaoIni = $("#filtroDataTransacaoIni").val();
		var dataTransacaoFim = $("#filtroDataTransacaoFim").val();
		
		var msgErro = '';
		
		if(dataTransacaoFim && !dataTransacaoIni) {
			msgErro += '* Digite a Data de Transação Inicial \n';
		}
				
		if(dataTransacaoFim && dataTransacaoIni) {
			if(checkDate(dataTransacaoIni, dataTransacaoFim)) {
				msgErro += '* Data de Transação Inicial é menor que a Data Final \n';
			}
		}
		
		if(msgErro){
			alert(msgErro);
			return false;
		}
	});
	

	/** Validacao do Formulário de Busca dos LOGS Alawar **/
    $(document).on("submit", "#formFiltroLog", function() {
		
		var dataLogIni = $("#filtroDataLogIni").val();
		var dataLogFim = $("#filtroDataLogFim").val();
		
		var msgErro = '';
		
		if(dataLogFim && !dataLogIni) {
			msgErro += '* Digite a Data de Log Inicial \n';
		}
				
		if(dataLogFim && dataLogIni) {
			if(checkDate(dataLogIni, dataLogFim)) {
				msgErro += '* Data de Log Inicial é menor que a Data Final \n';
			}
		}
		
		if(msgErro){
			alert(msgErro);
			return false;
		}
	});
	
	
	$("#btnLimpar").click(function() {
		$("input:text").val('');
		$("select").val('');
	});	
	
});	


function checkDate(dateIni, dateEnd) {
	
	var dateIniStr = dateIni.split('/');
	var dateEndStr = dateEnd.split('/');
	
	var dateInitial = new Date(dateIniStr[2], dateIniStr[1], dateIniStr[0]);
	var dateFinal = new Date(dateEndStr[2], dateEndStr[1], dateEndStr[0]);
		
	if(dateInitial.getTime() > dateFinal.getTime()) {
		return true;
	}
	else { 
		return false;
	}
}

