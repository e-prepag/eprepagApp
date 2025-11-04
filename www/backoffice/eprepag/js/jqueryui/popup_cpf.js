$(document).ready(
	function(){
		$('#popup_cpf').dialog({
						autoOpen:true,
						height: 320,
						width: 460,
						modal:true,
						closeText: 'hide',
						closeOnEscape: false,
                                                      dialogClass: "no-close",
                                                      draggable: false,
						close: function(event, ui) {
                                                    document.submit();
                                                }
 	});
	$('.ui-widget-overlay').click(
		function() {
                    //$("#popup_cpf").dialog("close");
		});
});

