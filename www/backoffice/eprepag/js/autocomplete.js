(function( $ ) {

var proto = $.ui.autocomplete.prototype,
	initSource = proto._initSource;

function filter( array, term ) {
	var matcher = new RegExp( $.ui.autocomplete.escapeRegex(term), "i" );
	return $.grep( array, function(value) {
		return matcher.test( $( "<div>" ).html( value.label || value.value || value ).text() );
	});
}

$.extend( proto, {
	_initSource: function() {
		if ( this.options.html && $.isArray(this.options.source) ) {
			this.source = function( request, response ) {
				response( filter( this.options.source, request.term ) );
			};
		} else {
			initSource.call( this );
		}
	},
	_renderItem: function( ul, item) {
		return $( "<li></li>" )
			.data( "item.autocomplete", item )
			.append( $( "<a></a>" )[ this.options.html ? "html" : "text" ]( item.label ) )
			.appendTo( ul );
	}
});

})( jQuery );

function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

$(function(){
    $("#busca").autocomplete({
        source: "/creditos/ajax/autocomplete.php",
        minLength: 2,
        select: function(event, ui) {
            var url = ui.item.id;

            if(ui.item.object.id){
                $("#prod").val(ui.item.object.id);
                $("#detalhe").submit();
            }
        },
 
        html: true,
        open: function(event, ui) {
            $(".ui-autocomplete").css("z-index", 1000);
        }
    });

    $("#busca")
        .change(function(){
            $("#busca").val(decodeHtml($("#busca").val())); 
        }).blur(function(){
            $("#busca").val(decodeHtml($("#busca").val())); 
        }).keyup(function(){
            $("#busca").val(decodeHtml($("#busca").val())); 
        }).keydown(function(){
            $("#busca").val(decodeHtml($("#busca").val())); 
        });

    $("#formBusca").submit(function(){
        if($("#busca").val().length < 2)
        {
            $("#busca").addClass("alert-danger");
            return false;
        }
        
    });

    $(".glyphicon-search").click(function(){
        $("#formBusca").submit();
    });
});