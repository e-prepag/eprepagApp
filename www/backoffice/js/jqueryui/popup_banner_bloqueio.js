$(document).ready(
	function(){
		$('#popup_banner').dialog({
						autoOpen:true,
						width: $('#imagem_banner').width()+40,
						resizable: false,
						modal:true,
						draggable:false,
						closeText: 'hide',
						closeOnEscape: false,
						open: function(event, ui) { 
							//hide close button.
							$(this).parent().children().children('.ui-dialog-titlebar-close').hide();
						}
	});
});

