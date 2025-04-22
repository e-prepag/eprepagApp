function pagina(inicio, limite, sort, dir)
{
    document.paginar.inicio.value = inicio;
    document.paginar.limite.value = limite;
    document.paginar.sort.value   = sort;
    document.paginar.dir.value    = dir;
    
    document.paginar.submit();
}

var corOriginal = new Array();

function linhaSelecionada(linha, novaCor)
{
    var celulas = null;
    
    if (typeof(document.getElementsByTagName) != 'undefined') {
        celulas = linha.getElementsByTagName('td');
    }
    else if (typeof(linha.cells) != 'undefined') {
        celulas = linha.cells;
    }
    
    numeroCelulas = celulas.length;
    
    for(var i = 0; i < numeroCelulas; i++)
    {
        corOriginal[i] = celulas[i].style.backgroundColor;
        
        celulas[i].style.backgroundColor = novaCor;
    }
}

function linhaNormal(linha)
{
    var celulas = null;
    
    if (typeof(document.getElementsByTagName) != 'undefined') {
        celulas = linha.getElementsByTagName('td');
    }
    else if (typeof(linha.cells) != 'undefined') {
        celulas = linha.cells;
    }
    
    numeroCelulas = celulas.length;
    
    for(var i = 0; i < numeroCelulas; i++)
    {
        celulas[i].style.backgroundColor = corOriginal[i];
    }
}