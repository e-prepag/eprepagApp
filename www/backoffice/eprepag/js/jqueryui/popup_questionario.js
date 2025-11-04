$(document).ready(
	function(){
		$('#popup_questionario').dialog({
						autoOpen:true,
						height: 400,
						width: 460,
						modal:true,
						closeText: 'hide',
						closeOnEscape: true,
						close: function(event, ui) { 
							top.location.href = "index.php"; 
						}
 	});
	$('.ui-widget-overlay').click(
		function() { 
			$("#popup_questionario").dialog("close"); 
		});
});

