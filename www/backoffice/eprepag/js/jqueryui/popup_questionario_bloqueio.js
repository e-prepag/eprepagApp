$(document).ready(
	function(){
		$('#popup_questionario').dialog({
						autoOpen:true,
						height: 400,
						width: 460,
						modal:true,
						closeText: 'hide',
						closeOnEscape: false,
						open: function(event, ui) { 
							//hide close button.
							$(this).parent().children().children('.ui-dialog-titlebar-close').hide();
						}
	});
});

