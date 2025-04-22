//*********************************************************************
// Funções utilizadas para formatar nomes - Primeira letra MAIUSCULA //
//*********************************************************************

function fix_name_js(name){
    var name_low_case = name.toLowerCase() ;
    var full_name = name_low_case.split(' ');

    for(i=0; i < full_name.length; i++){
        if(full_name[i].length <= 2)
            continue;
        full_name[i] = ucfirst_js(full_name[i]);
    }
    return full_name.join(' ');
}

function ucfirst_js(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}