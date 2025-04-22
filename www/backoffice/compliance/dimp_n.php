<?php
header("Content-Type: text/html; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
set_time_limit(0);
// Define o caminho do arquivo de log personalizado
ini_set('log_errors', 1);
ini_set('error_log', '/caminho/para/seu_arquivo_de_log.log');

// Habilita o relatório de erros
error_reporting(E_ALL);

// Opcional: Mostrar erros na tela (apenas para desenvolvimento)
ini_set('display_errors', 1); // Defina como 0 em produção


require_once "../../class/util/classFilePipe.php";
require_once '../../includes/constantes.php';

require_once $raiz_do_projeto . "backoffice/includes/topo.php";

require_once $raiz_do_projeto . "includes/gamer/constantes.php";



set_time_limit(7200);

function removerAcentos($string)
{
    $map = array(
        'á' => 'a',
        'à' => 'a',
        'ã' => 'a',
        'â' => 'a',
        'ä' => 'a',
        'Á' => 'A',
        'À' => 'A',
        'Ã' => 'A',
        'Â' => 'A',
        'Ä' => 'A',
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'É' => 'E',
        'È' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'í' => 'i',
        'ì' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'Í' => 'I',
        'Ì' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'ó' => 'o',
        'ò' => 'o',
        'õ' => 'o',
        'ô' => 'o',
        'ö' => 'o',
        'Ó' => 'O',
        'Ò' => 'O',
        'Õ' => 'O',
        'Ô' => 'O',
        'Ö' => 'O',
        'ú' => 'u',
        'ù' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'Ú' => 'U',
        'Ù' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'ç' => 'c',
        'Ç' => 'C',
        'ñ' => 'n',
        'Ñ' => 'N'
    );
    return strtr($string, $map);
}



//Captura início da execução

$time_start = getmicrotime();



//Dados Necessários

$cnpjEPP = '19037276000172';                            // CNPJ da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA

$razaoEPP = 'E-prepag Administradora de Cartoes Ltda';  // Razão Social da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA

$enderecoEPP = 'Rua Deputado Lacerda Franco, 300 - conjuntos 26-A, Pinheiros';    // Endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA

$cepEPP = '05418000';                                   // CEP da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA

$ufEPP = 'SP';                                          // UF da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA

$nomeRespEPP = 'Daniela Oliveira';                      // Nome do responsável da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA

$foneEPP = '01130309106';                               // Telefone para contato na empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA 

$emailEPP = 'financeiro@e-prepag.com.br';               // Email para contato na empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA 



$opr_codigoEPPPAGTO = '1';                               // opr_codigo utilizado para identificação da empresa E-PREPAG PAGAMENTOS ELETRONICOS LTDA

$cnpjEPPPAGTO = '08221305000135';                        // CNPJ da empresa E-PREPAG PAGAMENTOS ELETRONICOS LTDA

$razaoEPPPAGTO = 'E-PREPAG PAGAMENTOS ELETRONICOS LTDA';      // Razão Social da empresa E-PREPAG PAGAMENTOS ELETRONICOS LTDA

$enderecoEPPPAGTO = 'Rua Deputado Lacerda Franco, 300 - conjuntos 26, 27 e 28, Pinheiros';    // Endereço da empresa E-PREPAG PAGAMENTOS ELETRONICOS LTDA

$cepEPPPAGTO = '05418000';                                   // CEP da empresa E-PREPAG PAGAMENTOS ELETRONICOS LTDA

$ufEPPPAGTO = 'SP';                                          // UF da empresa E-PREPAG PAGAMENTOS ELETRONICOS LTDA

$nomeRespEPPPAGTO = 'Glaucia da Costa Gregio';               // Nome do responsável da empresa E-PREPAG PAGAMENTOS ELETRONICOS LTDA

$foneEPPPAGTO = '01130309101';                               // Telefone para contato na empresa E-PREPAG PAGAMENTOS ELETRONICOS LTDA 

$emailEPPPAGTO = 'glaucia@e-prepag.com.br';                  // Email para contato na empresa E-PREPAG PAGAMENTOS ELETRONICOS LTDA 

$dataCredenciamentoEPPPAGTO = '20171101';                    // Data do credenciamento da empresa E-PREPAG PAGAMENTOS ELETRONICOS LTDA junto a E-PREPAG ADMINISTRADORA DE CARTOES LTDA

$IND_COMEX_EPPPAGTO = "0";                                   // Informar se a transação se refere a pagamento ao exterior, em transações cross border. Valores Válidos: [0,1]  Indicador de transação de pagamento ao exterior 0 - Não 1 - Sim 



$COD_MCAPT = "4";                                            // Informar o código de identificação do Meio de Captura, ele é de livre atribuição da Instituição de Pagamento e único por arquivo. 

$NUM_LOG = "E-commerce";                                     // Informar o número lógico do Meio de Captura que identifica o terminal e corresponde ao informado nos comprovantes de pagamento. Validação: Para o Tipo de Tecnologia ?4 - E- commerce? do Campo 04, caso não seja possível identificar o meio de captura, deve ser informado ?E-commerce?. 

$TIPO_TECN = "4";                                            // O tipo 6 refere-se a URA - Unidade de Resposta Audível e MOTO (mail order / telephone order). Valores Válidos: [1,2,3,4,5,6] 

$TERM_PROP = "0";                                            // Informar sobre a propriedade do terminal, se é próprio ou de terceiros. Valores Válidos: [0,1]. Validação: Para o Tipo de Tecnologia ?4 - E-commerce? do Campo 04 deve ser informado obrigatoriamente ?0 terminal próprio?. 

$MARCA = "E-PREPAG";                                         // Informar a marca que identifica a Instituição de Pagamento no comprovante da transação. Validação: Para TERM_PROP do Campo 05 igual a ?1 - terminal de terceiro? este campo deve ser informado obrigatoriamente. 

$IND_EXTEMP = "0";                                           // Informar se a transação se refere a operação extemporânea. Para arquivos de retificação, finalidade 02 do campo 03 no registro 0000, não serão aceitos registros extemporâneos. Valores Válidos: [0,1]. Validação: Para ?Retificação do arquivo?, o IND_EXTEMP deve ser igual a zero. 

$IND_SPLIT = "0";                                            // Informar se a operação faz parte de uma operação ?splitada?. Valores Válidos: [0,1]

$BANDEIRA = "99";                                            // A especificação da bandeira deve estar contida na Relação de Bandeiras constante no Manual de Orientação ao Contribuinte do Projeto NF-e. Para boleto, informar 99 ? outros. 

$NAT_OPER = "2";                                             // Informar a natureza da operação realizada no comprovante de pagamento referenciado. Valores Válidos: [1,2,3,4,9] NAT_OPER 1 ? Crédito 2 ? Débito 3 ? Boleto 4 ? Pagamentos instantâneos /Transferência de Recursos 9 - Outros. 



//Esse ID é concatenado no inicio de cada id da operação('id_venda' => 'id_op') para diferenciar o tipo de venda que foi feito

$ARRAY_CONCATENA_ID_VENDA = array

(

    'gamer' => '10',

    'pdv' => '20',

    'cards' => '30',

    'boleto_express' => '40'

);



//Buscando Publisher que possuem totalização por utilização

$vetorPublisherPorUtilizacao = levantamentoPublisherComFechamentoUtilizacao();



?>

<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">

<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>

<script src="/js/global.js"></script>

<script type="text/javascript" src="/js/jquery.mask.min.js"></script>

<script src="https://www.e-prepag.com.br/js/valida.js"></script>

<link rel="stylesheet" type="text/css" href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" />

<style>
    .modal {

        text-align: center;

        padding: 0 !important;

    }



    .modal:before {

        content: '';

        display: inline-block;

        height: 100%;

        vertical-align: middle;

        margin-right: -4px;

    }



    .modal-dialog {

        display: inline-block;

        text-align: left;

        vertical-align: middle;

    }
</style>

<div class="col-md-12" id="teste">

    <ol class="breadcrumb top10">

        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice -
                <?php echo $currentAba->getDescricao(); ?></a></li>

        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>

        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>

    </ol>

</div>

<div class="col-md-12 txt-preto">

    <h4 class="txt-azul-claro bottom50">Gerador de Arquivo Declaração de Informações de Meios de Pagamentos - DIMP</h4>

    <form id="congelamento" name="congelamento" method="post" action="">

        <div class="col-md-12 col-sm-12 col-xs-12 text-right">

            <div class="col-md-2 col-sm-2 col-xs-2 text-right">Período Apurado:</div>

            <div class="col-md-2 col-sm-2 col-xs-2">

                <input type="text" id="data_inicial" name="data_inicial" char="7" maxlength="7"
                    class="form form-control data w150" readonly="readonly" placeholder="MM/YYYY"
                    value="<?php echo (!empty($_POST['data_inicial'])) ? $_POST['data_inicial'] : ""; ?>">

            </div>

            <div class="col-md-5 col-sm-5 col-xs-5 text-right">Unidade Federação do Fisco da informação prestada:</div>

            <div class="col-md-3 col-sm-3 col-xs-3">

                <select id="estado" name="estado" class="form form-control">

                    <option value="AC" <?php if (isset($estado) && $estado == "AC")
                        echo 'selected="selected" '; ?>>Acre
                    </option>

                    <option value="AL" <?php if (isset($estado) && $estado == "AL")
                        echo 'selected="selected" '; ?>>
                        Alagoas
                    </option>

                    <option value="AP" <?php if (isset($estado) && $estado == "AP")
                        echo 'selected="selected" '; ?>>Amapá
                    </option>

                    <option value="AM" <?php if (isset($estado) && $estado == "AM")
                        echo 'selected="selected" '; ?>>
                        Amazonas</option>

                    <option value="BA" <?php if (isset($estado) && $estado == "BA")
                        echo 'selected="selected" '; ?>>Bahia
                    </option>

                    <option value="CE" <?php if (isset($estado) && $estado == "CE")
                        echo 'selected="selected" '; ?>>Ceará
                    </option>

                    <option value="DF" <?php if (isset($estado) && $estado == "DF")
                        echo 'selected="selected" '; ?>>
                        Distrito Federal</option>

                    <option value="ES" <?php if (isset($estado) && $estado == "ES")
                        echo 'selected="selected" '; ?>>
                        Espírito Santo</option>

                    <option value="GO" <?php if (isset($estado) && $estado == "GO")
                        echo 'selected="selected" '; ?>>Goiás
                    </option>

                    <option value="MA" <?php if (isset($estado) && $estado == "MA")
                        echo 'selected="selected" '; ?>>
                        Maranhão</option>

                    <option value="MT" <?php if (isset($estado) && $estado == "MT")
                        echo 'selected="selected" '; ?>>Mato
                        Grosso</option>

                    <option value="MS" <?php if (isset($estado) && $estado == "MS")
                        echo 'selected="selected" '; ?>>Mato
                        Grosso do Sul</option>

                    <option value="MG" <?php if (isset($estado) && $estado == "MG")
                        echo 'selected="selected" '; ?>>Minas
                        Gerais</option>

                    <option value="PA" <?php if (isset($estado) && $estado == "PA")
                        echo 'selected="selected" '; ?>>Pará
                    </option>

                    <option value="PB" <?php if (isset($estado) && $estado == "PB")
                        echo 'selected="selected" '; ?>>
                        Paraíba
                    </option>

                    <option value="PR" <?php if (isset($estado) && $estado == "PR")
                        echo 'selected="selected" '; ?>>Paraná
                    </option>

                    <option value="PE" <?php if (isset($estado) && $estado == "PE")
                        echo 'selected="selected" '; ?>>
                        Pernambuco</option>

                    <option value="PI" <?php if (isset($estado) && $estado == "PI")
                        echo 'selected="selected" '; ?>>Piauí
                    </option>

                    <option value="RJ" <?php if (isset($estado) && $estado == "RJ")
                        echo 'selected="selected" '; ?>>Rio de
                        Janeiro</option>

                    <option value="RN" <?php if (isset($estado) && $estado == "RN")
                        echo 'selected="selected" '; ?>>Rio
                        Grande do Norte</option>

                    <option value="RS" <?php if (isset($estado) && $estado == "RS")
                        echo 'selected="selected" '; ?>>Rio
                        Grande do Sul</option>

                    <option value="RO" <?php if (isset($estado) && $estado == "RO")
                        echo 'selected="selected" '; ?>>
                        Rondônia</option>

                    <option value="RR" <?php if (isset($estado) && $estado == "RR")
                        echo 'selected="selected" '; ?>>
                        Roraima
                    </option>

                    <option value="SC" <?php if (isset($estado) && $estado == "SC")
                        echo 'selected="selected" '; ?>>Santa
                        Catarina</option>

                    <option value="SP" <?php if (empty($estado) || (isset($estado) && $estado == "SP"))
                        echo 'selected="selected" '; ?>>São Paulo</option>

                    <option value="SE" <?php if (isset($estado) && $estado == "SE")
                        echo 'selected="selected" '; ?>>
                        Sergipe
                    </option>

                    <option value="TO" <?php if (isset($estado) && $estado == "TO")
                        echo 'selected="selected" '; ?>>
                        Tocantins</option>

                </select>

            </div>



        </div>

        <div class="col-md-12 col-sm-12 col-xs-12 text-right"><br></div>

        <div class="col-md-12 col-sm-12 col-xs-12 text-right">

            <div class="col-md-4 col-sm-4 col-xs-4 text-right">Código da Finalidade do Arquivo:</div>

            <div class="col-md-6 col-sm-6 col-xs-6">

                <select name="cod_fin" id="cod_fin" class="form form-control" onchange="changeSelect();">

                    <option value="1" <?php if (empty($cod_fin) || (isset($cod_fin) && $cod_fin == "1"))
                        echo 'selected="selected" '; ?>>Remessa de arquivo normal</option>

                    <option value="2" <?php if (isset($cod_fin) && $cod_fin == "2")
                        echo 'selected="selected" '; ?>>
                        Remessa
                        de arquivo retificador</option>

                    <option value="3" <?php if (isset($cod_fin) && $cod_fin == "3")
                        echo 'selected="selected" '; ?>>
                        Remessa
                        de arquivo para atender notificação</option>

                    <!-- option value="4" <?php if (isset($cod_fin) && $cod_fin == "4")
                        echo 'selected="selected" '; ?>>Remessa de arquivo zerado</option -->

                    <option value="5" <?php if (isset($cod_fin) && $cod_fin == "5")
                        echo 'selected="selected" '; ?>>
                        Remessa
                        de arquivo de encerramento de atividades</option>

                </select>

            </div>

            <div class="col-md-2 col-sm-12 col-xs-12 pull-right">

                <button type="submit" name="BtnSearch" id="BtnSearch" value="Gerar Arquivo"
                    class="btn pull-right btn-success">Gerar Arquivo</button>

            </div>

        </div>

        <div class="col-md-12 col-sm-12 col-xs-12 text-right cod_fin-3" <?php if ((isset($cod_fin) && $cod_fin != "3") || empty($cod_fin))
            echo "hidden"; ?>><br></div>

        <div class="col-md-12 col-sm-12 col-xs-12 text-right cod_fin-3" <?php if ((isset($cod_fin) && $cod_fin != "3") || empty($cod_fin))
            echo "hidden"; ?>>

            <div class="col-md-6 col-sm-6 col-xs-6 text-right">CPF/CNPJ:</div>

            <div class="col-md-6 col-sm-6 col-xs-6">

                <input type="text" id="cpfcnpj" name="cpfcnpj" char="18" maxlength="18"
                    class="form form-control data w320" placeholder="AINDA NÃO IMPLEMENTADO - CPF ou CNPJ"
                    value="<?php echo (!empty($_POST['cpfcnpj'])) ? $_POST['cpfcnpj'] : ""; ?>" readonly="readonly">

            </div>

        </div>

    </form>

</div>

</div> <!--fecha a div de class="txt-azul-claro col-md-12 bg-branco p-bottom40"> -->



</div> <!--fecha a div de class="container"> -->

<div id="resultado"></div> <!-- Elemento onde o HTML retornado será inserido -->

<?php

// if (isset($BtnSearch) && $BtnSearch) {


//     echo '<div class="row"><div class="col-md-12 text-center top50"><a href="/dimp/' . date('Ymd') . '/' . strtoupper($nomeArquivo) . '" class="btn btn-info" download="' . strtoupper($nomeArquivo) . '">Download Arquivo DIMP</a><div></div>';


// }

?>

<style>
    .ui-datepicker-calendar {

        display: none;

    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

    jQuery(function (e) {



        $("#cpfcnpj").keypress(function () {

            if ($('#cpfcnpj').val().length > 13) {

                $("#cpfcnpj").unmask();

                $("#cpfcnpj").mask("99.999.999/9999-99");

            }

            else {

                $("#cpfcnpj").mask("999.999.999-99");

            }

        });



        $.datepicker.regional['pt-BR'] = {

            closeText: 'Selecionar',

            prevText: '&#x3c;Anterior',

            nextText: 'Pr&oacute;ximo&#x3e;',

            currentText: 'Hoje',

            monthNames: ['Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],

            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],

            dayNames: ['Domingo', 'Segunda-feira', 'Ter&ccedil;a-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sabado'],

            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],

            dayNamesMin: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],

            firstDay: 0,

            isRTL: false,

            showMonthAfterYear: false,

            yearSuffix: ''
        };

        $.datepicker.setDefaults($.datepicker.regional['pt-BR']);



        $('#data_inicial').datepicker({

            changeMonth: true,

            changeYear: true,

            maxDate: "y -1m w d",

            minDate: "y -10m w d",

            setDate: 'today',

            showButtonPanel: true,

            dateFormat: 'mm/yy',

            monthNamesShort: ['Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],

            onClose: function (dateText, inst) {

                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));

            }

        });



    });



    $(function () {

        $("#modal-load").removeClass('show');

        $("#modal-load").addClass('fade');



        $("#modal-load").modal('hide');

        $(document).ready(function () {
            $('#congelamento').on('submit', function (e) {
                e.preventDefault(); // Evita o comportamento padrão do formulário

                // Exibe o modal de carregamento
                Swal.fire({
                    title: 'Carregando...',
                    text: 'Por favor, aguarde',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading(); // Mostra o spinner
                    }
                });

                // Serializa os dados do formulário
                var formData = $(this).serialize();

                // Chamada AJAX
                $.ajax({
                    url: './ajax_dimp.php', // Arquivo PHP que processará os dados
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        // Fecha o modal de carregamento
                        Swal.close();

                        $('#resultado').html(response);
                    },
                    error: function () {
                        Swal.close();
                        formData += '&verificacao=true';
                        const intervalId = setInterval(function () {
                            $.ajax({
                                url: './ajax_dimp.php',
                                method: 'POST',
                                success: function (statusResponse) {
                                    if (statusResponse !== 'processando') {
                                        // Quando o processamento é concluído, exibe o resultado final
                                        $('#resultado').html(statusResponse);

                                        // Para o intervalo
                                        clearInterval(intervalId);
                                    } else {
                                        // Atualiza a mensagem ou mantém o feedback
                                        $('#resultado').html('<div class="row"><div class="col-md-12 text-center top50">Ainda processando, por favor aguarde...<div></div>');
                                    }
                                },
                                error: function () {
                                    // Tratar erros no AJAX de verificação
                                    $('#resultado').html('Erro ao verificar geração do DIMP.');
                                }
                            });
                        }, 10000); // Executa a cada 10 segundos
                    }
                });
            });
        });


    });



    function changeSelect() {

        if ($('#cod_fin').val() == "3") {

            $('.cod_fin-3').removeAttr("hidden");

        }

        else {

            $('.cod_fin-3').attr("hidden", "hidden");



        }

    }

</script>



<?php

echo "<div class='row'><div class='col-md-12 text-center txt-cinza top50'>Elapsed time: " . number_format(getmicrotime() - $time_start, 2, '.', '.') . "s</div></div>";

require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";

?>
</body>

</html>