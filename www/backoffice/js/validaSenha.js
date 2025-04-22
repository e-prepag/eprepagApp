$(function(){
   $(".novaSenha").keyup(function(){
        $.post( "/includes/nivelSenha.php", {str: $(this).val()}, function( data ) {
            if(data <= 2){
                $(".progress-bar-danger").removeClass("hidden");
            }else{
                $(".progress-bar-danger").addClass("hidden");
            }

            if(data <= 1){
                $(".progress-bar-warning").removeClass("hidden");
            }else{
                $(".progress-bar-warning").addClass("hidden");
            }

            if(data <= 0){
                $(".progress-bar-success").removeClass("hidden");
            }else{
                $(".progress-bar-success").addClass("hidden");
            }
        }).done(function() {
            $(".novaSenha").focus();
        }).fail(function() {
            alert( "ERRO!" );
        });
   });
});

function validaFormSenha(){
    var erro = [];
    if($(".progress-bar-success").hasClass("hidden")){
        erro.push("Senha ainda � insegura. Sua senha deve ter: de 6 a 12 caracteres, letras, n�meros, caracteres especiais (|,!,?,*,$,%, etc).");
    }

    if($(":input").hasClass("senhaAtual") && $(".senhaAtual").val().length < 6){
        erro.push("Senha atual deve ser preenchida.");
    }

    if($(".novaSenha").val() !== $(".confirmacaoSenha").val()){
        erro.push("Confirma��o de senha n�o confere.");
    }else if($(".senhaAtual").val() == $(".novaSenha").val()){
        erro.push("A nova senha � id�ntica � senha atual. Insira uma nova senha.");
    }
    
//    console.log(erro);
    
    return erro;
}