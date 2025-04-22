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
        erro.push("Senha ainda é insegura. Sua senha deve ter: de 6 a 12 caracteres, letras, números, caracteres especiais (|,!,?,*,$,%, etc).");
    }

    if($(":input").hasClass("senhaAtual") && $(".senhaAtual").val().length < 6){
        erro.push("Senha atual deve ser preenchida.");
    }

    if($(".novaSenha").val() !== $(".confirmacaoSenha").val()){
        erro.push("Confirmação de senha não confere.");
    }else if($(".senhaAtual").val() == $(".novaSenha").val()){
        erro.push("A nova senha é idêntica à senha atual. Insira uma nova senha.");
    }
    
//    console.log(erro);
    
    return erro;
}