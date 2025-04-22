$(document).ready(
	function(){
		$('#popup_banner').dialog({
						autoOpen:true,
						width: $('#imagem_banner').width()+40,
						resizable: false,
						modal:true,
						draggable:false,
						closeText: 'hide',
						closeOnEscape: true,
						close: function(event, ui) { 
						top.location.href = "index.php"; 
						}
 	});
	$('.ui-widget-overlay').click(
		function() { 
			$("#popup_banner").dialog("close"); 
		});
});

