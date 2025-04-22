<?php

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

define("CPF_VALIDADE","0");

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."consulta_cpf/config.inc.cpf.php";
require_once "/www/includes/bourls.php";
?>



<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/jquery.mask.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>

<script>
    $(document).ready(function(){
        
        $("#nascimento").datepicker({
            dateFormat: "dd/mm/yy",
            maxDate: "dateToday"
        });

        $("#cpf").mask("999.999.999-99");
        $("#nascimento").mask("99/99/9999");
        
    });
    
    var searching = false;
    var content = "";
        
    function pegaNomeRF(){
        
        if(!validaCpf()){
            alert("O CPF informado não corresponde a um cpf válido!");
            return false;
        }

        if($("#cpf").val().trim().length === 14 && $("#nascimento").val().trim().length === 10 && !searching){
            $.ajax({
                type: "POST",
                url: "/ajax/ajaxCpf.php",
                dataType : "json",
                data: { cpf : $("#cpf").val(), dataNascimento : $("#nascimento").val(), validade: "0"},
                beforeSend: function(){
                    searching = true;
                    $(".loading").html("<img src='http://<?php echo $server_url_complete; ?>/images/ajax-loader.gif' width='30' height='30' title='Consultando...'>");
                },
                success: function(txt){
                    searching = false;
                    console.log(txt);
                    if(txt.erros.length > 0){
                        alert(txt.erros);
                        content += "<h5 class='txt-vermelho'>Erro na pesquisa do cpf " + $("#cpf").val() + "</h5>";
                        content += "<ul class='txt-vermelho'><li>" + txt.erros + "</li></ul>";
                        content += "<hr>";

                    } else{
                        content += "<h5>Sucesso na pesquisa do cpf " + $("#cpf").val() + "</h5>";
                        content += "<ul><li><b>Nome: </b> " + txt.nome + "</li>";
                        content += "<li><b>Data de Nascimento: </b> " + txt.data_nascimento + "</li></ul>";
                        content += "<hr>";
                        $('#nome_resultado').html(txt.nome);
                        $('#nascimento_resultado').html(txt.data_nascimento);
                        $('#modalResultado').modal();
                        $(".loading").html("");
                    }
                    $("#cpf").val("");
                    $("#nascimento").val("");
                    $(".loading").html("");
                    $(".panel-body").html(content);

                },
                error: function(x,y){
                    $(".loading").addClass("hidden");
                    return false;
                }
            });
        } else{
            alert("Antes de clicar em 'Consultar CPF', preencha corretamente os campos CPF e Data de Nascimento");
            return false;
        }
    }
    
    function validaCpf(){
        var cpf = $("#cpf").val();
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
    
</script>

<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<div class="row">
    
    <div class="col-md-12">
        
        <form class="form-inline">
            <div class="form-group">
                <label for="exampleInputName2">CPF</label>
                <input type="text" class="form-control" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" title="xxx.xxx.xxx-xx" id="cpf" name="cpf">
            </div>
            <div class="form-group left20">
                <label for="exampleInputEmail2">Data de Nascimento</label>
                <input type="text" class="form-control" id="nascimento" name="nascimento" size="9" maxlength="10">
            </div>
            <button type="button" class="btn btn-default left20" onclick="pegaNomeRF()">Pesquisar</button>
            <span class="loading left5"></span>
        </form>
        
    </div>
    
</div>

<div class="panel panel-default top20">
    <div class="panel-heading">
        <h3 accesskey=""class="panel-title">Resultados</h3>
    </div>
    <div class="panel-body">
        Nenhuma pesquisa realizada
    </div>
</div>

<div class="modal fade" id="modalResultado" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Sucesso!</h4>
            </div>
            <div class="modal-body">
                <h4>Dados retornados: </h4>
                <p><b>Nome: </b><span id="nome_resultado"></span></p>
                <p><b>Data de Nascimento: </b><span id="nascimento_resultado"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</div>    
</body>
</html>