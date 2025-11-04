/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function checkEmail(email){
    var filter=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i
    return filter.test(email);
}

function valida(){
    var erro = false;
        
    $(".form-control").each(function(){
        if(typeof $(this).attr("char") !== undefined && $(this).attr("char") != false){
            if($(this).val().trim().length < $(this).attr("char")){
                $(this).css("border-color","#a94442");
                erro = true;
            }else if($(this).attr("id") == "email"){
                if(!checkEmail($(this).val())){
                    $(this).css("border-color","#a94442");
                    erro = true;
                }else{
                    $(this).css("border-color","");
                }
            }else if($(this).attr("id") == "cpf"){
                if(!validaCpf($(this).val())){
                    $(this).css("border-color","#a94442");
                    erro = true;
                }else{
                    $(this).css("border-color","");
                }
            }else{
                $(this).css("border-color","");
            }
        }
    });

    if(erro){
        if($("#tipo-modal").hasClass("alert-success")){
           $("#tipo-modal").switchClass("alert-success","alert-danger");
           $(".modal-title").switchClass("txt-azul","txt-vermelho").html("Erro de preenchimento");
        }
        $("#error-text").html("Os campos em destaque estão inválidos.");
        $("#modal-load").modal();
        return false;
    }else{
        return true;
    }
}

//@var: danger = 1
//      success = 2
function manipulaModal(tipo, msg, titulo){
    if(tipo == 2){
        if($("#tipo-modal").hasClass("alert-danger")){
           $("#tipo-modal").switchClass("alert-danger","alert-success");
           $("#modal-title").switchClass("txt-vermelho","txt-azul");
        }else{
            $("#tipo-modal").addClass("alert-success");
            $("#modal-title").addClass("txt-verde");
        }
    }else if(tipo == 1){
        if($("#tipo-modal").hasClass("alert-success")){
           $("#tipo-modal").switchClass("alert-success","alert-danger");
           $("#modal-title").switchClass("txt-azul","txt-vermelho");
        }else{
            $("#tipo-modal").addClass("alert-danger");
            $("#modal-title").addClass("txt-vermelho");
        }
    }
    
    $("#error-text").html(msg);
    $("#modal-load").css("z-index", "1051");

    if(titulo)
        $("#modal-title").html(titulo);
    
    $("#modal-load").modal();
}

function validaCpf(cpf){
    
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
