$(function(){
   
    $('input#btn_submit').click(function(){
        /*
        if(!validate_name( $('form#cpfForm input[name=name]').val() ) ){
            showMessage("Nome Inválido. \nPor favor, tente novamente seu nome completo sem abreviações,\npontos, números ou caracteres especiais.");
            return;
        }
        */
        
        
        if($(this).hasClass("grad1") && $(this).val() == "Confirmar"){
            $(this).val("Aguarde...");
            $(this).removeClass("grad1");
            $(this).attr("disabled", "disabled");
        }
        
        var strError = "";
        
        if(typeof $("#data_nascimento") != "undefined"){
            var currentDate = new Date();
            if($("#data_nascimento").val().length == "10"){
                var dtNasc = $("#data_nascimento").val().split("/");
                var objDtNasc = new Date(parseInt(dtNasc[2]),parseInt(dtNasc[1])-1,parseInt(dtNasc[0]));
                if(objDtNasc.getTime() > currentDate.getTime()){
                    strError = "Data inválida";
                }
            }else if($("#data_nascimento").val() == ""){
                strError += (strError == "") ? "Por favor, preencha data de nascimento." : "<br> Por favor, preencha data de nascimento.";
            }
        }
        
        if(!validate_cpf( $('form#cpfForm input[name=cpf]').val() ) ){
            strError += (strError == "") ? "CPF inválido, por favor revise o número digitado." : "<br> CPF inválido, por favor revise o número digitado.";
        }

        if(strError != ""){
            showMessage(strError);
            if(!$(this).hasClass("grad1") && $(this).val() == "Aguarde..."){
                $(this).val("Confirmar");
                $(this).addClass("grad1");
                $(this).removeAttr("disabled");
            }
            
            return;
        }
        
        $('form#cpfForm').submit();
    });
    
    
    $('input#skipform').click(function(e){
        e.preventDefault();
        
        $('form#cpfForm').prepend('<input type="hidden" name="skip" value="1" />');
        
        $('form#cpfForm').submit();
    });
    
    
});

function validate_cpf(cpf){
    cpf = cpf.replace(/[^\d]+/g,'');
    if(cpf == '') return false;

    // Elimina CPFs invalidos conhecidos
    if (cpf.length != 11 ||
            cpf == '00000000000' ||
            cpf == '11111111111' ||
            cpf == '22222222222' ||
            cpf == '33333333333' ||
            cpf == '44444444444' ||
            cpf == '55555555555' ||
            cpf == '66666666666' ||
            cpf == '77777777777' ||
            cpf == '88888888888' ||
            cpf == '99999999999')
            return false;

    // Valida 1o digito
    add = 0;
    for (i=0; i < 9; i ++)
            add += parseInt(cpf.charAt(i)) * (10 - i);
    rev = 11 - (add % 11);
    if (rev == 10 || rev == 11)
            rev = 0;
    if (rev != parseInt(cpf.charAt(9)))
            return false;

    // Valida 2o digito
    add = 0;
    for (i = 0; i < 10; i ++)
            add += parseInt(cpf.charAt(i)) * (11 - i);
    rev = 11 - (add % 11);
    if (rev == 10 || rev == 11)
            rev = 0;
    if (rev != parseInt(cpf.charAt(10)))
            return false;

    return true;
}

function validate_name(str) {
    var regExp = new RegExp("^\\s*[a-zA-ZÀ-ú']{1,}(\\s+[a-zA-ZÀ-ú']{1,}\\s*)+$");
    return str.match(regExp) && str.indexOf("  ") === -1;
}
