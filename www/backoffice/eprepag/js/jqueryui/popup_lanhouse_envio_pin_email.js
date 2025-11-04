function showValuesConfirmado() {
  var str = $('form').serialize();
  return str;
}
$(document).ready(
	function(){
    		$('#popup_envio_email').dialog({
						autoOpen:true,
						height: 290,
						width: 450,
						modal:true,
						closeText: 'hide',
						closeOnEscape: true,
						close: function(event, ui) { 
                                                        $.ajax({
                                                                type: "POST",
                                                                data: showValuesConfirmado(),
                                                                url: location.protocol+"//"+location.hostname+"/prepag2/dist_commerce/ajaxPedido.php",
                                                                beforeSend: function(xhr) {
                                                                            xhr.setRequestHeader('X-CSRF-Token', $('meta[name="csrf-token"]').attr('content'))
                                                                },
                                                                success: function(html){
                                                                        $('#box-lan-hope').html(html);
                                                                },
                                                                error: function(){
                                                                        alert('erro info_pedido');
                                                                }
                                                        });
						}
 	});
	$('.ui-widget-overlay').click(
		function() { 
			$("#popup_envio_email").dialog("close"); 
		});
});

