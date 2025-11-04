$(document).ready(
	function(){
		$('#popup_cpf').dialog({
						autoOpen:true,
						height: 360,
						width: 460,
						modal:true,
						closeText: 'hide',
						closeOnEscape: true,
                                                      draggable: false,
						close: function(event, ui) {
                                                    document.submit();
                                                }
 	});
	$('.ui-widget-overlay').click(
		function() {
                    $("#popup_cpf").dialog("close");
		});
});

