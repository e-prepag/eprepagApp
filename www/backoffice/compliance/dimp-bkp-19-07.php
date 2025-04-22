<?php

require_once '../../includes/constantes.php';

require_once $raiz_do_projeto."backoffice/includes/topo.php";

require_once $raiz_do_projeto."includes/gamer/constantes.php";



set_time_limit(7200);



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

                                        'gamer'          => '10',

                                        'pdv'            => '20',

                                        'cards'          => '30',

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

  padding: 0!important;

}



.modal:before {

  content: '';

  display: inline-block;

  height: 100%;

  vertical-align: middle;

  margin-right: -4px; / Adjusts for spacing /

}



.modal-dialog {

  display: inline-block;

  text-align: left;

  vertical-align: middle;

}

</style>

<?php

if(isset($BtnSearch) && $BtnSearch) {

?>

<div id="modal-load" class="modal show" data-backdrop="static" data-keyboard="false" role="dialog">

    <div class="modal-dialog modal-lg">

        <!-- Modal content-->

        <div class="modal-content">

            <div class="modal-header">

              <h4 class="modal-title txt-vermelho" id="modal-title"><b>ATENÇÃO</b></h4>

            </div>

            <div class="modal-body">

                <div class="alert alert-danger" id="tipo-modal" role="alert"> 

                    <h5><span id="error-text"><div class="row"><div class="col-md-10"><b>Aguarde enquanto o sistema está processando as informações para a geração do arquivo.<br>Isto pode levar algum tempo.</b></div><div class="col-md-2"><center><img src="https://www.e-prepag.com.br/imagens/engrenagem.gif" height="50px" /><center></div></div></span></h5>

              </div>

            </div>

        </div>

    </div>

    <div style="width:100%; height:100vh; background-color: rgba(0,0,0,0.5); position:absolute; top:0px; z-index: -11 !important;"></div> 

</div>

<?php  

}//end if(isset($BtnSearch) && $BtnSearch)

else {

?>

<div id="modal-load" class="modal fade" role="dialog">

    <div class="modal-dialog modal-lg">

        <!-- Modal content-->

        <div class="modal-content">

            <div class="modal-header">

              <h4 class="modal-title txt-vermelho" id="modal-title"></h4>

            </div>

            <div class="modal-body">

                <div class="alert alert-danger" id="tipo-modal" role="alert"> 

                  <h5><span id="error-text"></span></h5>

              </div>

            </div>

        </div>

    </div>

</div>

<?php 

}

?>

<div class="col-md-12" id="teste">

    <ol class="breadcrumb top10">

        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>

        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>

        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>

    </ol>

</div>

<div class="col-md-12 txt-preto">

    <h4 class="txt-azul-claro bottom50">Gerador de Arquivo Declaração de Informações de Meios de Pagamentos - DIMP</h4>

    <form id="congelamento" name="congelamento" method="post" action="">

        <div class="col-md-12 col-sm-12 col-xs-12 text-right">

            <div class="col-md-2 col-sm-2 col-xs-2 text-right">Período Apurado:</div>

            <div class="col-md-2 col-sm-2 col-xs-2">

                <input type="text" id="data_inicial" name="data_inicial" char="7" maxlength="7" class="form form-control data w150" readonly="readonly" placeholder="MM/YYYY" value="<?php echo (!empty($_POST['data_inicial']))? $_POST['data_inicial']:""; ?>">

            </div>

            <div class="col-md-5 col-sm-5 col-xs-5 text-right">Unidade Federação do Fisco da informação prestada:</div>

            <div class="col-md-3 col-sm-3 col-xs-3">

                <select id="estado" name="estado" class="form form-control">

                    <option value="AC" <?php if(isset($estado) && $estado == "AC") echo 'selected="selected" '; ?>>Acre</option>

                    <option value="AL" <?php if(isset($estado) && $estado == "AL") echo 'selected="selected" '; ?>>Alagoas</option>

                    <option value="AP" <?php if(isset($estado) && $estado == "AP") echo 'selected="selected" '; ?>>Amapá</option>

                    <option value="AM" <?php if(isset($estado) && $estado == "AM") echo 'selected="selected" '; ?>>Amazonas</option>

                    <option value="BA" <?php if(isset($estado) && $estado == "BA") echo 'selected="selected" '; ?>>Bahia</option>

                    <option value="CE" <?php if(isset($estado) && $estado == "CE") echo 'selected="selected" '; ?>>Ceará</option>

                    <option value="DF" <?php if(isset($estado) && $estado == "DF") echo 'selected="selected" '; ?>>Distrito Federal</option>

                    <option value="ES" <?php if(isset($estado) && $estado == "ES") echo 'selected="selected" '; ?>>Espírito Santo</option>

                    <option value="GO" <?php if(isset($estado) && $estado == "GO") echo 'selected="selected" '; ?>>Goiás</option>

                    <option value="MA" <?php if(isset($estado) && $estado == "MA") echo 'selected="selected" '; ?>>Maranhão</option>

                    <option value="MT" <?php if(isset($estado) && $estado == "MT") echo 'selected="selected" '; ?>>Mato Grosso</option>

                    <option value="MS" <?php if(isset($estado) && $estado == "MS") echo 'selected="selected" '; ?>>Mato Grosso do Sul</option>

                    <option value="MG" <?php if(isset($estado) && $estado == "MG") echo 'selected="selected" '; ?>>Minas Gerais</option>

                    <option value="PA" <?php if(isset($estado) && $estado == "PA") echo 'selected="selected" '; ?>>Pará</option>

                    <option value="PB" <?php if(isset($estado) && $estado == "PB") echo 'selected="selected" '; ?>>Paraíba</option>

                    <option value="PR" <?php if(isset($estado) && $estado == "PR") echo 'selected="selected" '; ?>>Paraná</option>

                    <option value="PE" <?php if(isset($estado) && $estado == "PE") echo 'selected="selected" '; ?>>Pernambuco</option>

                    <option value="PI" <?php if(isset($estado) && $estado == "PI") echo 'selected="selected" '; ?>>Piauí</option>

                    <option value="RJ" <?php if(isset($estado) && $estado == "RJ") echo 'selected="selected" '; ?>>Rio de Janeiro</option>

                    <option value="RN" <?php if(isset($estado) && $estado == "RN") echo 'selected="selected" '; ?>>Rio Grande do Norte</option>

                    <option value="RS" <?php if(isset($estado) && $estado == "RS") echo 'selected="selected" '; ?>>Rio Grande do Sul</option>

                    <option value="RO" <?php if(isset($estado) && $estado == "RO") echo 'selected="selected" '; ?>>Rondônia</option>

                    <option value="RR" <?php if(isset($estado) && $estado == "RR") echo 'selected="selected" '; ?>>Roraima</option>

                    <option value="SC" <?php if(isset($estado) && $estado == "SC") echo 'selected="selected" '; ?>>Santa Catarina</option>

                    <option value="SP" <?php if(empty($estado) || (isset($estado) && $estado == "SP")) echo 'selected="selected" '; ?>>São Paulo</option>

                    <option value="SE" <?php if(isset($estado) && $estado == "SE") echo 'selected="selected" '; ?>>Sergipe</option>

                    <option value="TO" <?php if(isset($estado) && $estado == "TO") echo 'selected="selected" '; ?>>Tocantins</option>

                </select>

            </div>



        </div>

        <div class="col-md-12 col-sm-12 col-xs-12 text-right"><br></div>

        <div class="col-md-12 col-sm-12 col-xs-12 text-right">

            <div class="col-md-4 col-sm-4 col-xs-4 text-right">Código da Finalidade do Arquivo:</div>

            <div class="col-md-6 col-sm-6 col-xs-6">

                <select name="cod_fin" id="cod_fin" class="form form-control" onchange="changeSelect();">

                    <option value="1" <?php if(empty($cod_fin) || (isset($cod_fin) && $cod_fin == "1")) echo 'selected="selected" '; ?>>Remessa de arquivo normal</option>

                    <option value="2" <?php if(isset($cod_fin) && $cod_fin == "2") echo 'selected="selected" '; ?>>Remessa de arquivo retificador</option>

                    <option value="3" <?php if(isset($cod_fin) && $cod_fin == "3") echo 'selected="selected" '; ?>>Remessa de arquivo para atender notificação</option>

                    <!-- option value="4" <?php if(isset($cod_fin) && $cod_fin == "4") echo 'selected="selected" '; ?>>Remessa de arquivo zerado</option -->

                    <option value="5" <?php if(isset($cod_fin) && $cod_fin == "5") echo 'selected="selected" '; ?>>Remessa de arquivo de encerramento de atividades</option>

              </select>

            </div>

            <div class="col-md-2 col-sm-12 col-xs-12 pull-right">

                <button type="submit" name="BtnSearch" id="BtnSearch" value="Gerar Arquivo" class="btn pull-right btn-success">Gerar Arquivo</button>

            </div>

        </div>

        <div class="col-md-12 col-sm-12 col-xs-12 text-right cod_fin-3" <?php  if((isset($cod_fin) && $cod_fin != "3") || empty($cod_fin)) echo "hidden"; ?> ><br></div>

        <div class="col-md-12 col-sm-12 col-xs-12 text-right cod_fin-3" <?php  if((isset($cod_fin) && $cod_fin != "3") || empty($cod_fin)) echo "hidden"; ?> >

            <div class="col-md-6 col-sm-6 col-xs-6 text-right">CPF/CNPJ:</div>

            <div class="col-md-6 col-sm-6 col-xs-6">

                <input type="text" id="cpfcnpj" name="cpfcnpj" char="18" maxlength="18" class="form form-control data w320" placeholder="AINDA NÃO IMPLEMENTADO - CPF ou CNPJ" value="<?php echo (!empty($_POST['cpfcnpj']))? $_POST['cpfcnpj']:""; ?>" readonly="readonly">

            </div>

        </div>

    </form>

</div>

</div>   <!--fecha a div de class="txt-azul-claro col-md-12 bg-branco p-bottom40"> -->



</div>   <!--fecha a div de class="container"> --> 



<?php

if(isset($BtnSearch) && $BtnSearch) {

    flush();

    ob_flush();



    // Split ano/mes

    list($mes, $ano) = explode("/", $_POST['data_inicial']);

    

    // Capturando o ultimo dia do mês

    $currentmonth = mktime(0, 0, 0, $mes, 1, $ano);

    $ultimoDiaMes = date('t',$currentmonth);

 

    //Checando se existe algum período não congelado para a geração do mesmo

    $sql_check_freeze = "

                        select 

                               count(*) as total

                        from financial_processing 

                                inner join operadoras on fp_publisher = opr_codigo

                        where  fp_date >= '".$ano."-".$mes."-01 00:00:00' 

                               and fp_date <= '".$ano."-".$mes."-".$ultimoDiaMes." 23:59:59'

                               and fp_freeze=0

                               and opr_status = '1';";  

    $freeze_total = SQLexecuteQuery($sql_check_freeze);

    $msg = "";

    $class = "alert ";

    if($freeze_total && $freeze_total_row = pg_fetch_array($freeze_total)){

        

        //verificando se existem registros sem congelamento

        if($freeze_total_row['total'] > 0) {

            $msg = "<strong><br>ATENÇÃO 0002</strong>: Existem registros sem data congelada dentro do período apurado<br><br>";

            $class = "alert-danger txt-vermelho";

            

            //Detalhando quais registros não estão congelados

            $sql_no_freeze = "  select 

                                       count(*) as total,

                                       fp_publisher,

                                       fp_date,

                                       opr_nome

                                from financial_processing 

                                    inner join operadoras on fp_publisher = opr_codigo

                                where  fp_date >= '".$ano."-".$mes."-01 00:00:00' 

                                       and fp_date <= '".$ano."-".$mes."-".$ultimoDiaMes." 23:59:59'

                                       and fp_freeze=0

                                       and opr_status = '1'

                                group by fp_publisher,

                                       fp_date,

                                       opr_nome

                                order by opr_nome, 

                                       fp_date;";     

            $freeze_detail = SQLexecuteQuery($sql_no_freeze);

            if ($freeze_detail) {

                $msg = "<div class='col-md-12'>

                            <strong>Relação de Publihers que precisão ter seus períodos congelados:</strong>

                         </div>

                         <div class='col-md-12 bg-cinza'></div>

                         <div class='col-md-12'>

                            <div class='col-md-4'>Cod. Publisher</div>

                            <div class='col-md-4'>Publisher</div>

                            <div class='col-md-4'>Datas</div>

                         </div>";

                $lastPublisher = NULL;

                while ($freeze_detail_row = pg_fetch_array($freeze_detail)) {

                    if($freeze_detail_row['fp_publisher'] != $lastPublisher) $msg .=  "<div class='col-md-12 bg-cinza'></div>";

                    $msg .= "<div class='col-md-12'>

                                <div class='col-md-4'>".(($freeze_detail_row['fp_publisher'] != $lastPublisher)?$freeze_detail_row['fp_publisher']:"")."</div>

                                <div class='col-md-4'>".(($freeze_detail_row['fp_publisher'] != $lastPublisher)?$freeze_detail_row['opr_nome']:"")."</div>

                                <div class='col-md-4'>".substr($freeze_detail_row['fp_date'],0,10)."</div>

                             </div>";

                    $lastPublisher = $freeze_detail_row['fp_publisher'];

                }//end while

            }//end if ($freeze_detail)

            else {

                $msg = "<strong><br>ERRO 0003</strong>: Erro ao executar o select dos DETALHES dos periodos não congelados<br><br>";

            }//end else do if ($freeze_detail)

       } //end if($freeze_total_row['total'] > 0)

        else {



            //

            // INÍCIO DO BLOCO DE GERAÇÃO DO ARQUIVO

            //

            

            require_once $raiz_do_projeto . "class/util/classFilePipe.php";



            $nomeArquivo = $_POST['estado'].'_TEF-DIMP_'.$mes.$ano.date('-YmdHis').'.txt';

            $nomeArquivoDIMP[] = $nomeArquivo;

            // Contador de registros do Bloco Zero

            $countBlocoZero = 0;

            

            unset($file);

            $file = new FilePipe($nomeArquivo);



            //=========================================================================================================================

            // REGISTRO TIPO 0000: ABERTURA DO ARQUIVO DIGITAL E IDENTIFICAÇÃO DA INSTITUIÇÃO DE PAGAMENTO 

            //=========================================================================================================================

            unset($vetorLines);

            $vetorLines = array (

                                 0 => array('name' => '0000',

                                            'size' => 4

                                            ),

                                 1 => array('name' => '09',//'07', '03'

                                            'size' => 2

                                            ),

                                 2 => array('name' => $_POST['cod_fin'], 

                                            'size' => 1

                                            ),

                                 3 => array('name' => $_POST['estado'],

                                            'size' => 2

                                            ),

                                 4 => array('name' => $cnpjEPP,

                                            'size' => 14

                                            ),

                                 5 => array('name' => $razaoEPP,

                                            'size' => 40

                                            ),

                                 6 => array('name' => $ano.$mes.'01',

                                            'size' => 8

                                            ),

                                 7 => array('name' => $ano.$mes.$ultimoDiaMes,

                                            'size' => 8

                                            ),

                                 8 => array('name' => (!checkIP()?'1':'2'), 

                                            'size' => 1

                                            ), 

                                 9 => array('name' => date("Ym"),

                                            'size' => 6

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $countBlocoZero++;

            $REG_BLC['0000'] = 1;



            //=========================================================================================================================

            // REGISTRO TIPO 0001: ABERTURA DO BLOCO 0 

            //=========================================================================================================================

            unset($vetorLines);

            $vetorLines = array (

                                 0 => array('name' => '0001',

                                            'size' => 4

                                            ),

                                 1 => array('name' => '1',

                                            'size' => 1

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $countBlocoZero++;

            $REG_BLC['0001'] = 1;



            //=========================================================================================================================

            // REGISTRO TIPO 0005: DADOS COMPLEMENTARES DA INSTITUIÇÃO DE PAGAMENTO 

            //=========================================================================================================================

            unset($vetorLines);

            $vetorLines = array (

                                 0 => array('name' => '0005',

                                            'size' => 4

                                            ),

                                 1 => array('name' => $razaoEPP,

                                            'size' => 40

                                            ),

                                 2 => array('name' => $enderecoEPP,

                                            'size' => 70

                                            ),

                                 3 => array('name' => $cepEPP,

                                            'size' => 8

                                            ),

                                 4 => array('name' => '3550308',

                                            'size' => 7

                                            ),

                                 5 => array('name' => $ufEPP,

                                            'size' => 2

                                            ),

                                 6 => array('name' => $nomeRespEPP,

                                            'size' => 30

                                            ),

                                 7 => array('name' => $foneEPP,

                                            'size' => 13

                                            ),

                                 8 => array('name' => $emailEPP,

                                            'size' => 30,

                                            'upper'=> true

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $countBlocoZero++;

            $REG_BLC['0005'] = 1;

            

            //=========================================================================================================================

            // REGISTRO TIPO 0100: TABELA DE CADASTRO DO CLIENTE

            //=========================================================================================================================

            $REG_BLC['0100'] = 0;

            

            //Selecionando Publishers ligados a EPP Pagto

            

            $publishers_epp_pagto = NULL;

            $sql_publishers_epp_pagto = "select opr_codigo 

                                         from operadoras 

                                            inner join financial_processing on fp_publisher = opr_codigo 

                                         where opr_vinculo_empresa=0 

                                            and fp_date >= '".$ano."-".$mes."-01 00:00:00' 

                                            and fp_date <= '".$ano."-".$mes."-".$ultimoDiaMes." 23:59:59' 

                                            and opr_status = '1' 

                                            and fp_freeze=1 

                                         group by opr_codigo

                                         order by opr_codigo;";

            //echo $sql_publishers_epp_pagto."<br>";

            $response_epp_pagto = SQLexecuteQuery($sql_publishers_epp_pagto);

            if ($response_epp_pagto) {

                while ($response_epp_pagto_row = pg_fetch_array($response_epp_pagto)) {

                    if(!empty($publishers_epp_pagto)) {

                        $publishers_epp_pagto .= ", ".$response_epp_pagto_row['opr_codigo'];

                    }//edn if(!empty($publishers_epp_pagto))

                    else {

                        $publishers_epp_pagto = $response_epp_pagto_row['opr_codigo'];

                    }//end else do if(!empty($publishers_epp_pagto))

                }//end while

            }//end if ($response_epp_pagto) 

            else {

                $msg .= "<strong><br>ERRO 0004</strong>: Erro ao executar o select dos Publishes vinculados à E-Prepag Pagamentos<br><br>";

                $class = "alert-danger txt-vermelho";

            }//end esle do if ($response_epp_pagto) 

            

            if(!empty($publishers_epp_pagto)) {

                //Dados da EPP PAGTO

                unset($vetorLines);

                $vetorLines = array (

                                     0 => array('name' => '0100',

                                                'size' => 4

                                                ),

                                     1 => array('name' => str_pad($opr_codigoEPPPAGTO,7,'0',STR_PAD_LEFT),

                                                'size' => 7

                                                ),

                                     2 => array('name' => $cnpjEPPPAGTO,

                                               'size' => 14

                                               ),

                                     3 => array('name' => '',

                                               'size' => 11

                                               ),

                                     4 => array('name' => $razaoEPPPAGTO,

                                                'size' => 40

                                                ),

                                     5 => array('name' => $enderecoEPPPAGTO,

                                                'size' => 70

                                                ),

                                     6 => array('name' => $cepEPPPAGTO,

                                                'size' => 8

                                                ),

                                     7 => array('name' => '3550308',

                                                'size' => 7

                                                ),

                                     8 => array('name' => $ufEPPPAGTO,

                                                'size' => 2

                                                ),

                                     9 => array('name' => $nomeRespEPPPAGTO,

                                                'size' => 30

                                                ),

                                     10=> array('name' => $foneEPPPAGTO,

                                                'size' => 13

                                                ),

                                     11=> array('name' => $emailEPPPAGTO,

                                                'size' => 30,

                                                'upper'=> true

                                                ),

                                     12=> array('name' => $dataCredenciamentoEPPPAGTO,

                                                'size' => 8

                                                ),
                                     13=> array('name' => '1',

                                                'size' => 1

                                     ),

                                );

                $file->setVetorLines($vetorLines);

                $countBlocoZero++;

                $REG_BLC['0100']++;

            }//end if(!empty($publishers_epp_pagto))

            //echo "<div> EPP PAGTO : [".$publishers_epp_pagto."]</div>";



            //Selecionando Publishers ligados a EPP Adm

            

            $publishers_epp_adm = NULL;

            $sql_publishers_epp_adm = "select opr_codigo,

                                              opr_nome_loja,

                                              opr_cnpj,

                                              opr_endereco, opr_numero, opr_complemento,opr_bairro,opr_cidade,

                                              opr_cep,

                                              opr_estado,

                                              opr_contato,

                                              opr_cont_fone,

                                              opr_email_dimp, 

                                              to_char(opr_data_inicio_operacoes,'YYYYMMDD') as data 

                                       from operadoras 

                                            inner join financial_processing on fp_publisher = opr_codigo 

                                       where opr_vinculo_empresa=1 

                                            and opr_status = '1' 

                                            and fp_freeze=1 

                                            and fp_date >= '".$ano."-".$mes."-01 00:00:00' 

                                            and fp_date <= '".$ano."-".$mes."-".$ultimoDiaMes." 23:59:59'
											
                                       group by opr_codigo,

                                              opr_nome_loja,

                                              opr_cnpj,

                                              opr_endereco, opr_numero, opr_complemento,opr_bairro,opr_cidade,

                                              opr_cep,

                                              opr_estado,

                                              opr_contato,

                                              opr_cont_fone,

                                              opr_email_dimp,

                                              data

                                       order by opr_codigo;";  // and opr_estado in ('".$_POST['estado']."', '')

            //echo $sql_publishers_epp_adm."<br>";

            $response_epp_adm = SQLexecuteQuery($sql_publishers_epp_adm);

            if ($response_epp_adm) {

                while ($response_epp_adm_row = pg_fetch_array($response_epp_adm)) {

                    //Dados dos Publishers vinculados a EPP ADM

                    unset($vetorLines);

                    $vetorLines = array (

                                        0 => array('name' => '0100',

                                                   'size' => 4

                                                   ),

                                        1 => array('name' => str_pad($response_epp_adm_row['opr_codigo'],7,'0',STR_PAD_LEFT),

                                                   'size' => 7

                                                   ),

                                        2 => array('name' => ((!empty($response_epp_adm_row['opr_cnpj']))?$response_epp_adm_row['opr_cnpj']:$cnpjEPP),

                                                  'size' => 14

                                                  ),

                                        3 => array('name' => '',

                                                  'size' => 11

                                                  ),

                                        4 => array('name' => $response_epp_adm_row['opr_nome_loja'],

                                                   'size' => 40

                                                   ),

                                        5 => array('name' => ($response_epp_adm_row['opr_estado'] == "RS")? 'AVENIDA DAS NACOES UNIDAS, 12901 - SL 25 102 - BROOKLIN PAULISTA - SAO PAULO': $response_epp_adm_row['opr_endereco'].', '.$response_epp_adm_row['opr_numero'].((!empty($response_epp_adm_row['opr_complemento']))?' - '.$response_epp_adm_row['opr_complemento']:"").((!empty($response_epp_adm_row['opr_bairro']))?' - '.$response_epp_adm_row['opr_bairro']:"").' - '.$response_epp_adm_row['opr_cidade'],

                                                   'size' => 200

                                                   ),

                                        6 => array('name' => ($response_epp_adm_row['opr_estado'] == "RS")? '04578910': str_pad(str_replace(" ","",str_replace("-","",str_replace(".", "", $response_epp_adm_row['opr_cep']))),8,"0",STR_PAD_LEFT),

                                                   'size' => 8

                                                   ),

                                        7 => array('name' => '3550308',

                                                   'size' => 7

                                                   ),

                                        8 => array('name' => ((!empty($response_epp_adm_row['opr_estado']))?(($response_epp_adm_row['opr_estado'] == "RS")? $ufEPP: $response_epp_adm_row['opr_estado']):$ufEPP),

                                                   'size' => 2

                                                   ),

                                        9 => array('name' => $response_epp_adm_row['opr_contato'], 

                                                   'size' => 100

                                                   ),

                                        10=> array('name' => str_replace(" ","",str_replace("-","",str_replace(".", "", $response_epp_adm_row['opr_cont_fone']))),

                                                   'size' => 13

                                                   ),

                                        11=> array('name' => substr($response_epp_adm_row['opr_email_dimp'],0,strpos($response_epp_adm_row['opr_email_dimp'],',')),

                                                   'size' => 30,

                                                   'upper'=> true

                                                   ),

                                        12=> array('name' => $response_epp_adm_row['data'],

                                                   'size' => 8

                                                   ),
                                        13=> array('name' => '0',

                                                'size' => 1

                                        ),
                       

                                    );

                    $file->setVetorLines($vetorLines);

                    $countBlocoZero++;

                    $REG_BLC['0100']++;                    

                }//end while

            }//end if ($response_epp_adm) 

            else {

                $msg .= "<strong><br>ERRO 0005</strong>: Erro ao executar o select dos Publishes vinculados à E-Prepag Administradora<br><br>";

                $class = "alert-danger txt-vermelho";

            }//end esle do if ($response_epp_adm) 

            

            

            //=========================================================================================================================

            //        REGISTRO TIPO 0105: TABELA DE VAN DO CLIENTE ==> Não é nosso caso

            //Este registro tem por objetivo identificar as instituições cujas operações realizadas pelo

            //beneficiário do pagamento não são liquidadas pela informante do arquivo. Obrigatório

            //apenas para as instituições que tenham contrato ativo ou realizaram operações de VAN

            //no período para o beneficiário de pagamento informado no registro Pai 0100. É facultado

            //às Instituições de Pagamentos escolher como irão reportar essa informação, por

            //contratos ativos ou se realizaram transações no período.     

            //=========================================================================================================================



            

            

            //=========================================================================================================================

            // REGISTRO TIPO 0200: TABELA DE CADASTRO DO MEIO DE CAPTURA

            //=========================================================================================================================

            unset($vetorLines);

            $vetorLines = array (

                                 0 => array('name' => '0200',

                                            'size' => 4

                                            ),

                                 1 => array('name' => $COD_MCAPT,

                                            'size' => 20

                                            ),

                                 2 => array('name' => $NUM_LOG,

                                            'size' => 20

                                            ),

                                 3 => array('name' => $TIPO_TECN,

                                            'size' => 1

                                            ),

                                 4 => array('name' => $TERM_PROP,

                                            'size' => 1

                                            ),

                                 5 => array('name' => $MARCA,

                                            'size' => 20

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $countBlocoZero++;

            $REG_BLC['0200'] = 1;

            

            //=========================================================================================================================

            // REGISTRO TIPO 0300: DADOS DA INSTITUIÇÃO DE PAGAMENTO PARCEIRA ==> Não é nosso caso

            //Este registro tem por objetivo identificar as instituições de pagamento parceiras cujas

            //transações estejam sendo reportadas no arquivo. Quando uma instituição é responsável

            //pelo fornecimento das informações de outra IP é considerada ?Instituição Parceira?.

            //Este registro também identifica as subadquirentes ou marketplace que exercem atividade

            //de meio de pagamento, mas não compartilham as informações de seus clientes com a

            //IP remetente do arquivo. Nesses casos, o COD_IP_PAR deve ser igual ao

            //COD_CLIENTE do registro 0100.

            //=========================================================================================================================



            

            //=========================================================================================================================

            // REGISTRO 0990: ENCERRAMENTO DO BLOCO 0 

            //=========================================================================================================================

            unset($vetorLines);

            $countBlocoZero++;

            $vetorLines = array (

                                 0 => array('name' => '0990',

                                            'size' => 4

                                            ),

                                 1 => array('name' => $countBlocoZero,

                                            'size' => 20

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $REG_BLC['0990'] = 1;

            

            

             // Contador de registros do Bloco Um

            $countBlocoUm = 0;

            

            //=========================================================================================================================

            // REGISTRO TIPO 1001: ABERTURA DO BLOCO 1

            //=========================================================================================================================

            unset($vetorLines);

            $vetorLines = array (

                                 0 => array('name' => '1001',

                                            'size' => 4

                                            ),

                                 1 => array('name' => '1',

                                            'size' => 1

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $countBlocoUm++;

            $REG_BLC['1001'] = 1;

        

            

            //=========================================================================================================================

            // REGISTRO TIPO 1100: RESUMO MENSAL DAS OPERAÇÕES DE PAGAMENTO

            //=========================================================================================================================

            $REG_BLC['1100'] = 0;

            $REG_BLC['1110'] = 0;

            $REG_BLC['1115'] = 0;



            //Verificando se existe publishers vinculados a EPP Pagto

            if(!empty($publishers_epp_pagto)) {

                $sql_total_epp_pagto = "

                            select 

                                    sum(fp_total_order) as fp_total_order, sum(fp_total) as total

                            from financial_processing 

                                inner join operadoras on opr_codigo =  fp_publisher

                            where  fp_publisher IN (".$publishers_epp_pagto.")

                                    and fp_date >= '".$ano."-".$mes."-01 00:00:00' 

                                    and fp_date <= '".$ano."-".$mes."-".$ultimoDiaMes." 23:59:59'

                                    and fp_date > opr_data_inicio_operacoes

                                    and opr_status = '1'								

                                    and fp_freeze=1; ";

                //echo $sql_total_epp_pagto."<br>"; die();

                $total_epp_pagto = SQLexecuteQuery($sql_total_epp_pagto);

                if ($total_epp_pagto) {

                    if ($total_epp_pagto_row = pg_fetch_array($total_epp_pagto)) {

                        unset($vetorLines);

                        $vetorLines = array (

                                             0 => array('name' => '1100',

                                                        'size' => 4

                                                        ),

                                             1 => array('name' => '',

                                                        'size' => 20

                                                        ),

                                             2 => array('name' => str_pad($opr_codigoEPPPAGTO,7,'0',STR_PAD_LEFT),

                                                        'size' => 7

                                                        ),

                                             3 => array('name' => $IND_COMEX_EPPPAGTO,

                                                        'size' => 1

                                                        ),

                                             4 => array('name' => $IND_EXTEMP,

                                                        'size' => 1

                                                        ),

                                             5 => array('name' => $ano.$mes.'01',

                                                        'size' => 8

                                                        ),

                                             6 => array('name' => $ano.$mes.$ultimoDiaMes,

                                                        'size' => 8

                                                        ),

                                             7 => array('name' => number_format($total_epp_pagto_row['total'],2,',',''),

                                                        'size' => 21

                                                        ),

                                             8 => array('name' => $total_epp_pagto_row['fp_total_order'],

                                                        'size' => 10

                                                        ),

                                        );

                        $file->setVetorLines($vetorLines);

                        $countBlocoUm++;

                        $REG_BLC['1100']++;

                        

                        //=========================================================================================================================

                        // REGISTRO TIPO 1110: OPERAÇÕES DIÁRIAS DE PAGAMENTO POR MEIO DE CAPTURA - Para Publisher vinculados a EPP PAGTO

                        //=========================================================================================================================

                        

                        //Levantando totais por OPERAÇÕES DIÁRIAS DE PAGAMENTO POR MEIO DE CAPTURA

                        $sql_total_operacoes_diarias = "

                                            select 

                                                to_char(fp_date,'YYYYMMDD') as fp_date,sum(fp_total_order) as fp_total_order, sum(fp_total) as total

                                            from financial_processing 

                                                inner join operadoras on opr_codigo =  fp_publisher

                                            where fp_date >= '".$ano."-".$mes."-01 00:00:00' 

                                                and fp_date <= '".$ano."-".$mes."-".$ultimoDiaMes." 23:59:59'

                                                and fp_publisher IN (".$publishers_epp_pagto.")

                                                and opr_status = '1' 

                                                and fp_freeze=1 

                                                and fp_date > opr_data_inicio_operacoes

                                            group by fp_date

                                            order by fp_date; ";



                        //echo "<br>1110 => ".$sql_total_operacoes_diarias."<br>"; //die();

                        $total_operacoes_diarias = SQLexecuteQuery($sql_total_operacoes_diarias);

                        if ($total_operacoes_diarias) {

                            while ($total_operacoes_diarias_row = pg_fetch_array($total_operacoes_diarias)) {



                                unset($vetorLines);

                                $vetorLines = array (

                                                     0 => array('name' => '1110',

                                                                'size' => 4

                                                                ),

                                                     1 => array('name' => $COD_MCAPT,

                                                                'size' => 20

                                                                ),

                                                     2 => array('name' => $total_operacoes_diarias_row['fp_date'],

                                                                'size' => 8

                                                                ),

                                                     3 => array('name' => number_format($total_operacoes_diarias_row['total'],2,',',''),

                                                                'size' => 13

                                                                ),

                                                     4 => array('name' => $total_operacoes_diarias_row['fp_total_order'],

                                                                'size' => 10

                                                                ),

                                                     5 => array('name' => $cnpjEPPPAGTO,

                                                                'size' => 14

                                                                ),

                                                );

                                $file->setVetorLines($vetorLines);

                                $countBlocoUm++;

                                $REG_BLC['1110']++;



                                //=========================================================================================================================

                                // REGISTRO TIPO 1115: OPERAÇÕES POR COMPROVANTE DE PAGAMENTO - Para Publisher vinculados a EPP PAGTO

                                //=========================================================================================================================



                                if(count($vetorPublisherPorUtilizacao)>0) { 

                                    $where_opr_venda_lan = " AND ( CASE ";

                                    $where_opr_venda_lan_negativa = " AND ( CASE ";

                                    $where_opr_utilizacao_lan = " AND ( CASE ";

                                    foreach ($vetorPublisherPorUtilizacao as $opr_codigo => $opr_data_inicio_contabilizacao_utilizacao){ 

                                        //echo "ID: ".$opr_codigo." => DATA: [".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."]<br>";

                                        $where_opr_venda_lan .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao < '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";

                                        $where_opr_venda_lan_negativa .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";

                                        $where_opr_utilizacao_lan .= "  WHEN pih_id = $opr_codigo THEN pih_data >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";

                                    }//end foreach

                                    $where_opr_venda_lan .= " ELSE vg.vg_data_inclusao > '2008-01-01 00:00:00' END )";

                                    $where_opr_venda_lan_negativa .= " ELSE FALSE END )";

                                    $where_opr_utilizacao_lan .= "  ELSE FALSE END ) ";

                                } //end if(count($vetorPublisherPorUtilizacao)>0)

                                else {

                                    $where_opr_venda_lan = "";

                                    $where_opr_venda_lan_negativa = "";

                                    $where_opr_utilizacao_lan = "";

                                }//end else do if(count($vetorPublisherPorUtilizacao)>0)          



                                //Query de levantamento de transações

                                $sql = "

                                select 

                                        venda,

                                        to_char(dia,'HH24MISS') as dia,

                                        sum(n) as n, 

                                        round(sum(total)::numeric,2) as total 

                                from ( 

                                        (select 

                                                '".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(ve_data_inclusao,'YYMMDD') || lpad(CASE WHEN ve_jogo='HB' THEN 16 WHEN ve_jogo='OG' THEN 13 WHEN ve_jogo='MU' THEN 34 END::text , 3, '0') || lpad(ve_id::text , 8, '0') as venda,

                                                ve_data_inclusao as dia,

                                                CASE WHEN ve_jogo='HB' THEN 16 WHEN ve_jogo='OG' THEN 13 WHEN ve_jogo='MU' THEN 34 END as publisher,

                                                count(*) as n, 

                                                sum(ve_valor) as total 

                                        from dist_vendas_pos 

                                        where date_trunc('day', ve_data_inclusao)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."'

                                                and CASE WHEN ve_jogo='HB' THEN 16 WHEN ve_jogo='OG' THEN 13 WHEN ve_jogo='MU' THEN 34 END IN (".$publishers_epp_pagto.")

                                        group by venda,

                                                dia,

                                                publisher) 

                                union all 

                                        (select 

                                                '".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_concilia,'YYMMDD') || lpad(vgm_opr_codigo::text , 3, '0') || lpad(vg.vg_id::text , 8, '0') as venda,

                                                vg.vg_data_concilia as dia,

                                                vgm_opr_codigo as publisher,

                                                sum(vgm.vgm_qtde) as n, 

                                                sum(vgm.vgm_valor * vgm.vgm_qtde) as total 

                                        from tb_venda_games vg 

                                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 

                                                inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 

                                        where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 

                                                and date_trunc('day', vg.vg_data_concilia)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                                and SUBSTR(tvgpo.tvgpo_canal, 1, 1) ='P' 

                                                and vgm_opr_codigo IN (".$publishers_epp_pagto.")

                                                and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 

                                        group by venda,

                                                dia, 

                                                publisher) 

                                union all

                                        (select 

                                                '".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(data_transacao,'YYMMDD') || lpad(opr_codigo::text , 3, '0') || lpad(id_transacao::text , 8, '0') as venda,

                                                data_transacao as dia,

                                                opr_codigo as publisher,

                                                count(*) as n, 

                                                sum(valor) as total 

                                        from pos_transacoes_ponto_certo 

                                        where opr_codigo is not NULL 

                                                and opr_codigo IN (".$publishers_epp_pagto.")

                                                and date_trunc('day', data_transacao)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                        group by venda,

                                                dia,

                                                publisher) 

                                union all 

                                        (select 

                                                case when vg.vg_ug_id = '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' then '".$ARRAY_CONCATENA_ID_VENDA['boleto_express']."' when vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' then '".$ARRAY_CONCATENA_ID_VENDA['gamer']."' end || to_char(vg.vg_data_concilia,'YYMMDD') || lpad(vgm_opr_codigo::text , 3, '0') || lpad(vg.vg_id::text , 8, '0') as venda,

                                                vg.vg_data_concilia as dia,

                                                vgm_opr_codigo as publisher,

                                                sum(vgm.vgm_qtde) as n, 

                                                sum(vgm.vgm_valor * vgm.vgm_qtde) as total 

                                        from tb_venda_games vg 

                                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 

                                        where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 

                                                and date_trunc('day', vg.vg_data_concilia)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                                and vgm_opr_codigo IN (".$publishers_epp_pagto.")

                                                and vg.vg_pagto_tipo != ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 

                                        group by venda,

                                                dia, 

                                                publisher) 

                                union all 

                                        (select 

                                                case when vg.vg_ug_id = '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' then '".$ARRAY_CONCATENA_ID_VENDA['boleto_express']."' when vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' then '".$ARRAY_CONCATENA_ID_VENDA['gamer']."' end || to_char(vg.vg_data_concilia,'YYMMDD') || lpad(vgm_opr_codigo::text , 3, '0') || lpad(vg.vg_id::text , 8, '0') as venda,

                                                vg.vg_data_concilia as dia,

                                                vgm_opr_codigo as publisher,

                                                sum(vgm.vgm_qtde) as n, 

                                                sum(vgm.vgm_valor * vgm.vgm_qtde) as total 

                                        from tb_venda_games vg 

                                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 

                                                inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 

                                        where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 

                                                and date_trunc('day', vg.vg_data_concilia)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                                and vgm_opr_codigo IN (".$publishers_epp_pagto.")

                                                and tvgpo.tvgpo_canal='G' 

                                                and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 

                                        group by venda,

                                                dia, 

                                                publisher) 

                                                

                                -- Contabilizando PINs PDVs por VENDA na Integração                

                                union all 

                                        (select 

                                                '".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vgm_opr_codigo::text , 3, '0') || lpad(vg.vg_id::text , 8, '0') as venda,

                                                vg.vg_data_inclusao as dia,

                                                vgm_opr_codigo as publisher,

                                                sum(vgm.vgm_qtde) as n, 

                                                sum(vgm.vgm_valor * vgm.vgm_qtde) as total 

                                        from tb_dist_venda_games vg 

                                                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 

                                        where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' ".$where_opr_venda_lan."

                                                and vgm_opr_codigo IN (".$publishers_epp_pagto.")

                                                and date_trunc('day', vg.vg_data_inclusao)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                        group by venda,

                                                dia,

                                                publisher) 

                                                ";

                                //Contabilizando vendas por utilização de PINs Publisher

                                if(count($vetorPublisherPorUtilizacao)> 0) {

                                    $sql .= "

                                        

                                -- Contabilizando PINs PDVs por UTILIZAÇÃO na Integração                

                                union all



                                        (select 

                                                '".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vgm_opr_codigo::text , 3, '0') || lpad(vg.vg_id::text , 8, '0') as venda,

                                                vg.vg_data_inclusao as dia,

                                                vgm_opr_codigo as publisher,

                                                count(*) as n, 

                                                sum(vgm.vgm_valor) as total

                                        from tb_dist_venda_games vg 

                                             inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 

                                             inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 

                                             inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno

                                        where vg.vg_data_inclusao>='2008-01-01 00:00:00' 

                                             and vg.vg_ultimo_status='5'

                                             and vgm_opr_codigo IN (".$publishers_epp_pagto.")

											 
                                             and pin_status = '8'

                                             and pih_codretepp='2'

                                             ".$where_opr_venda_lan_negativa."

                                             and date_trunc('day', pih_data)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                             ".$where_opr_utilizacao_lan."

                                        group by venda,

                                                dia,

                                                publisher) 

                                            ";

                                }//end if(count($vetorPublisherPorUtilizacao)>0)

                                $sql .= " 

                                union all 

                                        (select 

                                                '".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vgm_opr_codigo::text , 3, '0') || lpad(vg.vg_id::text , 8, '0') as venda,

                                                vg.vg_data_concilia as dia,

                                                vgm_opr_codigo as publisher,

                                                sum(vgm.vgm_qtde) as n, 

                                                sum(vgm.vgm_valor * vgm.vgm_qtde) as total 

                                        from tb_venda_games vg 

                                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 

                                                inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 

                                        where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 

                                                and date_trunc('day', vg.vg_data_concilia)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                                and vgm_opr_codigo IN (".$publishers_epp_pagto.")

                                                and tvgpo.tvgpo_canal='L' 

                                                and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 

                                        group by venda,

                                                dia, 

                                                publisher) 

                                -- naun vai calcular os cartoes fisicos da webzen e ongame por conta da incoerencia de informações

                                -- Contabilizando PINs GoCASH utilizado na loja como EPP CASH                

                                union all 

                                        (select 

                                                '".$ARRAY_CONCATENA_ID_VENDA['cards']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vgm_opr_codigo::text , 3, '0') || lpad(vg.vg_id::text , 8, '0') as venda,

                                                vg.vg_data_concilia as dia,

                                                vgm_opr_codigo as publisher,

                                                sum(vgm.vgm_qtde) as n, 

                                                sum(vgm.vgm_valor * vgm.vgm_qtde) as total 

                                        from tb_venda_games vg 

                                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 

                                                inner join tb_venda_games_pinepp_origem tvgpo on tvgpo.vg_id = vg.vg_id 

                                        where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 

                                                and date_trunc('day', vg.vg_data_concilia)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                                and vgm_opr_codigo IN (".$publishers_epp_pagto.")

                                                and tvgpo.tvgpo_canal='C' 

                                                and vg.vg_pagto_tipo = ".$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']." 

                                        group by venda,

                                                dia, 

                                                publisher) 

                            

                                -- Contabilizando PINs GiftCards utilizados por Integração                

                                union all 

                                        (select 

                                                '".$ARRAY_CONCATENA_ID_VENDA['cards']."'  || to_char(pih_data,'YYMMDD') || lpad(pih_id::text , 3, '0') || lpad(pih_pin_id::text , 8, '0') as venda,

                                                pih_data as dia,

                                                pih_id as publisher,

                                                count(*) as n, 

                                                sum(pih_pin_valor/100) as total 

                                        from pins_integracao_card_historico

                                        where pin_status = '".intval($PINS_STORE_STATUS_VALUES['U'])."' 

                                                and pih_id IN (".$publishers_epp_pagto.")

                                                and pih_codretepp = '2'

                                                and date_trunc('day', pih_data)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                        group by venda,

                                                dia,

                                                publisher)

                                -- Contabilizando PINs GoCASH utilizado por Integração de Utilização                

                                union all 

                                        (select 

                                                '".$ARRAY_CONCATENA_ID_VENDA['cards']."'  || to_char(pgc_pin_response_date,'YYMMDD') || lpad(pgc_opr_codigo::text , 3, '0') || lpad(pgc_id::text , 8, '0') as venda,

                                                pgc_pin_response_date as dia,

                                                pgc_opr_codigo as publisher,

                                                count(*) as n, 

                                                CASE WHEN (select opr_product_type from operadoras where opr_codigo = pgc_opr_codigo) = 5 THEN sum(pgc_real_amount) 

                                                     WHEN ((select opr_product_type from operadoras where opr_codigo = pgc_opr_codigo) = 7 OR (select opr_product_type from operadoras where opr_codigo = pgc_opr_codigo) = 4 )  THEN sum (pgc_face_amount) 

                                                     ELSE sum (pgc_face_amount) END as total 

                                        from pins_gocash

                                        where pgc_opr_codigo != 0 

                                                 and pgc_opr_codigo IN (".$publishers_epp_pagto.")

                                                 and date_trunc('day', pgc_pin_response_date)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                        group by venda,

                                                dia,

                                                publisher) 

                                ) t 

                                inner join operadoras ON opr_codigo = publisher

                                where publisher IN (".$publishers_epp_pagto.")

                                    and opr_status = '1'

                                    and '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' > opr_data_inicio_operacoes

                                group by venda, dia

                                order by dia;";

                                //echo "<br>1115 => ".$sql.PHP_EOL.PHP_EOL; die();

                                $transacoes = SQLexecuteQuery($sql);

                                if ($transacoes) {

                                    while ($transacoes_row = pg_fetch_array($transacoes)) {

                                        unset($vetorLines);

                                        $vetorLines = array (

                                                             0 => array('name' => '1115',

                                                                        'size' => 4

                                                                        ),

                                                             1 => array('name' => $transacoes_row['venda'].str_pad($REG_BLC['1115'],6,'0',STR_PAD_LEFT),

                                                                        'size' => 25

                                                                        ),

                                                             2 => array('name' => '',

                                                                        'size' => 8

                                                                        ),

                                                             3 => array('name' => '',

                                                                        'size' => 8

                                                                        ),

                                                             4 => array('name' => $IND_SPLIT,

                                                                        'size' => 1

                                                                        ),

                                                             5 => array('name' => $BANDEIRA,

                                                                        'size' => 2

                                                                        ),

                                                             6 => array('name' => $transacoes_row['dia'],

                                                                        'size' => 6

                                                                        ),

                                                             7 => array('name' => number_format($transacoes_row['total'],2,',',''),

                                                                        'size' => 13

                                                                        ),

                                                             8 => array('name' => $NAT_OPER,

                                                                        'size' => 1

                                                                        ),

                                                             9 => array('name' => '',

                                                                        'size' => 14

                                                                        ),
															10 => array('name' => '',

																		'size' => 14

																		),
															11 => array('name' => '',

																		'size' => 14

																		)																	

                                                        );

                                        $file->setVetorLines($vetorLines);

                                        $countBlocoUm++;

                                        $REG_BLC['1115']++;

                                    }//endwhile

                                }//end if ($transacoes)

                                else {

                                    $msg .= "<strong><br>ERRO 0010</strong>: Erro ao executar a Query das Transações dos Publishes vinculados à E-Prepag Pagamentos<br><br>";

                                    $class = "alert-danger txt-vermelho";                        

                                }//end else do if ($transacoes)

                                //=========================================================================================================================

                                // FIM DO BLOCO - REGISTRO TIPO 1115: OPERAÇÕES POR COMPROVANTE DE PAGAMENTO - Para Publisher vinculados a EPP PAGTO

                                //=========================================================================================================================

                                

                                

                            }//endwhile

                        }//end iif ($total_operacoes_diarias)

                        else {

                            $msg .= "<strong><br>ERRO 0009</strong>: Erro ao executar o select dos Totais de Operações Diárias<br><br>";

                            $class = "alert-danger txt-vermelho";

                        }//end esle do if ($total_operacoes_diarias)



                        //=========================================================================================================================

                        // FIM DO BLOCO - REGISTRO TIPO 1110: OPERAÇÕES DIÁRIAS DE PAGAMENTO POR MEIO DE CAPTURA - Para Publisher vinculados a EPP PAGTO

                        //=========================================================================================================================

                        

                    }//end if ($total_epp_pagto_row = pg_fetch_array($total_epp_pagto))

                    else {

                        $msg .= "<strong><br>ERRO 0007</strong>: Erro ao executar o fectch_array dos Totais dos Publishes vinculados à E-Prepag Pagamentos<br><br>";

                        $class = "alert-danger txt-vermelho";                        

                    }//end else do if ($total_epp_pagto_row = pg_fetch_array($total_epp_pagto))

                }//end if ($total_epp_pagto) 

                else {

                    $msg .= "<strong><br>ERRO 0006</strong>: Erro ao executar o select dos Totais dos Publishes vinculados à E-Prepag Pagamentos<br><br>";

                    $class = "alert-danger txt-vermelho";

                }//end esle do if ($total_epp_pagto) 



            }//end if(!empty($publishers_epp_pagto))

            //Levantando totais por Publishers vinculados a EPP ADM

            $sql_total_epp_adm = "

                                select 

                                        opr_internacional_alicota,fp_publisher, sum(fp_total_order) as fp_total_order, sum(fp_total) as total

                                from financial_processing 

                                    inner join operadoras on opr_codigo =  fp_publisher

                                where  fp_publisher NOT IN (".$publishers_epp_pagto.")

                                    and fp_date >= '".$ano."-".$mes."-01 00:00:00' 

                                    and fp_date <= '".$ano."-".$mes."-".$ultimoDiaMes." 23:59:59'

                                    and opr_status = '1' 

                                    and fp_freeze=1 
									
                                group by fp_publisher,opr_internacional_alicota; ";

        

            //echo $sql_total_epp_adm."<br>"; die();

            $total_epp_adm = SQLexecuteQuery($sql_total_epp_adm);

            if ($total_epp_adm) {

                while ($total_epp_adm_row = pg_fetch_array($total_epp_adm)) {

                    unset($vetorLines);

                    $vetorLines = array (

                                         0 => array('name' => '1100',

                                                    'size' => 4

                                                    ),

                                         1 => array('name' => '',

                                                    'size' => 20

                                                    ),

                                         2 => array('name' => str_pad($total_epp_adm_row['fp_publisher'],7,'0',STR_PAD_LEFT),

                                                    'size' => 7

                                                    ),

                                         3 => array('name' => (($total_epp_adm_row['opr_internacional_alicota'] == 0)?'0':'1'),

                                                    'size' => 1

                                                    ),

                                         4 => array('name' => $IND_EXTEMP,

                                                    'size' => 1

                                                    ),

                                         5 => array('name' => $ano.$mes.'01',

                                                    'size' => 8

                                                    ),

                                         6 => array('name' => $ano.$mes.$ultimoDiaMes,

                                                    'size' => 8

                                                    ),

                                         7 => array('name' => number_format($total_epp_adm_row['total'],2,',',''),

                                                    'size' => 21

                                                    ),

                                         8 => array('name' => $total_epp_adm_row['fp_total_order'],

                                                    'size' => 10

                                                    ),

                                    );

                    $file->setVetorLines($vetorLines);

                    $countBlocoUm++;

                    $REG_BLC['1100']++;

                    

                    //=========================================================================================================================

                    // REGISTRO TIPO 1110: OPERAÇÕES DIÁRIAS DE PAGAMENTO POR MEIO DE CAPTURA - Para Publisher vinculados a EPP ADM

                    //=========================================================================================================================

                    //Levantando totais por OPERAÇÕES DIÁRIAS DE PAGAMENTO POR MEIO DE CAPTURA

                    $sql_total_operacoes_diarias = "

                                        select 

                                            to_char(fp_date,'YYYYMMDD') as fp_date,sum(fp_total_order) as fp_total_order, sum(fp_total) as total

                                        from financial_processing 

                                            inner join operadoras on opr_codigo =  fp_publisher

                                        where fp_date >= '".$ano."-".$mes."-01 00:00:00' 

                                            and fp_date <= '".$ano."-".$mes."-".$ultimoDiaMes." 23:59:59'

                                            and fp_publisher = ".$total_epp_adm_row['fp_publisher']."

                                            and opr_status = '1' 

                                            and fp_freeze=1 

                                        group by fp_date

                                        order by fp_date; ";



                    //echo $sql_total_operacoes_diarias."<br>";

                    $total_operacoes_diarias = SQLexecuteQuery($sql_total_operacoes_diarias);

                    if ($total_operacoes_diarias) {

                        while ($total_operacoes_diarias_row = pg_fetch_array($total_operacoes_diarias)) {



                            unset($vetorLines);

                            $vetorLines = array (

                                                 0 => array('name' => '1110',

                                                            'size' => 4

                                                            ),

                                                 1 => array('name' => $COD_MCAPT,

                                                            'size' => 20

                                                            ),

                                                 2 => array('name' => $total_operacoes_diarias_row['fp_date'],

                                                            'size' => 8

                                                            ),

                                                 3 => array('name' => number_format($total_operacoes_diarias_row['total'],2,',',''),

                                                            'size' => 13

                                                            ),

                                                 4 => array('name' => $total_operacoes_diarias_row['fp_total_order'],

                                                            'size' => 10

                                                            ),

                                                 5 => array('name' => $cnpjEPPPAGTO,  

                                                            'size' => 14

                                                            ),

                                            );

                            $file->setVetorLines($vetorLines);

                            $countBlocoUm++;

                            $REG_BLC['1110']++;

                                    

                            

                            //=========================================================================================================================

                            // REGISTRO TIPO 1115: OPERAÇÕES POR COMPROVANTE DE PAGAMENTO - Para Publisher vinculados a EPP ADM

                            //=========================================================================================================================



                            if(count($vetorPublisherPorUtilizacao)>0) {

                                $where_opr_venda_lan = " AND ( CASE ";

                                $where_opr_venda_lan_negativa = " AND ( CASE ";

                                $where_opr_utilizacao_lan = " AND ( CASE ";

                                foreach ($vetorPublisherPorUtilizacao as $opr_codigo => $opr_data_inicio_contabilizacao_utilizacao){ 

                                    //echo "ID: ".$opr_codigo." => DATA: [".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."]<br>";

                                    $where_opr_venda_lan .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao < '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";

                                    $where_opr_venda_lan_negativa .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";

                                    $where_opr_utilizacao_lan .= "  WHEN pih_id = $opr_codigo THEN pih_data >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";

                                }//end foreach

                                $where_opr_venda_lan .= " ELSE vg.vg_data_inclusao > '2008-01-01 00:00:00' END )";

                                $where_opr_venda_lan_negativa .= " ELSE FALSE END )";

                                $where_opr_utilizacao_lan .= "  ELSE FALSE END ) ";

                            } //end if(count($vetorPublisherPorUtilizacao)>0)

                            else {

                                $where_opr_venda_lan = "";

                                $where_opr_venda_lan_negativa = "";

                                $where_opr_utilizacao_lan = "";

                            }//end else do if(count($vetorPublisherPorUtilizacao)>0)          



                            //Query de levantamento de transações

                            $sql = "

                            select 

                                    venda,

                                    to_char(dia,'HH24MISS') as dia,

                                    sum(n) as n, 

                                    round(sum(total)::numeric,2) as total 

                            from ( 

                                    (select 

                                            '".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(ve_data_inclusao,'YYMMDD') || lpad(CASE WHEN ve_jogo='HB' THEN 16 WHEN ve_jogo='OG' THEN 13 WHEN ve_jogo='MU' THEN 34 END::text , 3, '0') || lpad(ve_id::text , 8, '0') as venda,

                                            ve_data_inclusao as dia,

                                            CASE WHEN ve_jogo='HB' THEN 16 WHEN ve_jogo='OG' THEN 13 WHEN ve_jogo='MU' THEN 34 END as publisher,

                                            count(*) as n, 

                                            sum(ve_valor) as total 

                                    from dist_vendas_pos 

                                    where date_trunc('day', ve_data_inclusao)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                            and CASE WHEN ve_jogo='HB' THEN 16 WHEN ve_jogo='OG' THEN 13 WHEN ve_jogo='MU' THEN 34 END = ".$total_epp_adm_row['fp_publisher']."

                                    group by venda,

                                            dia,

                                            publisher) 

                            union all

                                    (select 

                                            '".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(data_transacao,'YYMMDD') || lpad(opr_codigo::text , 3, '0') || lpad(id_transacao::text , 8, '0') as venda,

                                            data_transacao as dia,

                                            opr_codigo as publisher,

                                            count(*) as n, 

                                            sum(valor) as total 

                                    from pos_transacoes_ponto_certo 

                                    where opr_codigo is not NULL 

                                            and opr_codigo = ".$total_epp_adm_row['fp_publisher']."

                                            and date_trunc('day', data_transacao)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                    group by venda,

                                            dia,

                                            publisher) 

                            union all 

                                    (select 

                                            '".$ARRAY_CONCATENA_ID_VENDA['gamer']."'  || to_char(vg.vg_data_concilia,'YYMMDD') || lpad(vgm_opr_codigo::text , 3, '0') || lpad(vg.vg_id::text , 8, '0') as venda,

                                            vg.vg_data_concilia as dia,

                                            vgm_opr_codigo as publisher,

                                            sum(vgm.vgm_qtde) as n,

                                            sum(vgm.vgm_valor * vgm.vgm_qtde) as total 

                                    from tb_venda_games vg 

                                            inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 

                                    where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 

                                            and vgm_opr_codigo = ".$total_epp_adm_row['fp_publisher']."

                                            and date_trunc('day', vg.vg_data_concilia)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                    group by venda,

                                            dia, 

                                            publisher) ";

                            //verificando se o Publisher contabiliza vendas por utilização
                            
                            if(array_key_exists ($total_epp_adm_row['fp_publisher'],$vetorPublisherPorUtilizacao)) {

                                $sql .= "



                            -- Contabilizando PINs PDVs por UTILIZAÇÃO na Integração                

                            union all



                                    (select 

                                            '".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vgm_opr_codigo::text , 3, '0') || lpad(vg.vg_id::text , 8, '0') as venda,

                                            vg.vg_data_inclusao as dia,

                                            vgm_opr_codigo as publisher,

                                            count(*) as n, 

                                            sum(vgm.vgm_valor) as total

                                    from tb_dist_venda_games vg 

                                         inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 

                                         inner join tb_dist_venda_games_modelo_pins vgmp on vgmp_vgm_id = vgm.vgm_id 

                                         inner join pins_integracao_historico pih on pih_pin_id = vgmp_pin_codinterno

                                    where vg.vg_data_inclusao>='2008-01-01 00:00:00' 

                                         and vg.vg_ultimo_status='5'

                                         and vgm_opr_codigo = ".$total_epp_adm_row['fp_publisher']."

                                         and pin_status = '8'

                                         and pih_codretepp='2'

                                         ".$where_opr_venda_lan_negativa."

                                         and date_trunc('day', pih_data)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                         ".$where_opr_utilizacao_lan."

                                    group by venda,

                                            dia,

                                            publisher) 

                                        ";

                            }//end if(array_key_exists ($total_epp_adm_row['fp_publisher'],$vetorPublisherPorUtilizacao)) 

                            else {

                                            $sql .= " 

                                                

                                -- Contabilizando PINs PDVs por VENDA na Integração                

                                union all 

                                    (select 

                                            '".$ARRAY_CONCATENA_ID_VENDA['pdv']."'  || to_char(vg.vg_data_inclusao,'YYMMDD') || lpad(vgm_opr_codigo::text , 3, '0') || lpad(vg.vg_id::text , 8, '0') as venda,

                                            vg.vg_data_inclusao as dia,

                                            vgm_opr_codigo as publisher,

                                            sum(vgm.vgm_qtde) as n, 

                                            sum(vgm.vgm_valor * vgm.vgm_qtde) as total 

                                    from tb_dist_venda_games vg 

                                            inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 

                                    where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' ".$where_opr_venda_lan."

                                            and vgm_opr_codigo = ".$total_epp_adm_row['fp_publisher']."

                                            and date_trunc('day', vg.vg_data_inclusao)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                    group by venda,

                                            dia,

                                            publisher) ";



                            }//end else do if(array_key_exists ($total_epp_adm_row['fp_publisher'],$vetorPublisherPorUtilizacao)) 

                            $sql .=" 

                            -- Contabilizando PINs GiftCards utilizados por Integração                

                            union all 

                                    (select 

                                            '".$ARRAY_CONCATENA_ID_VENDA['cards']."'  || to_char(pih_data,'YYMMDD') || lpad(pih_id::text , 3, '0') || lpad(pih_pin_id::text , 8, '0') as venda,

                                            pih_data as dia,

                                            pih_id as publisher,

                                            count(*) as n, 

                                            sum(pih_pin_valor/100) as total 

                                    from pins_integracao_card_historico

                                    where pin_status = '".intval($PINS_STORE_STATUS_VALUES['U'])."' 

                                            and pih_id = ".$total_epp_adm_row['fp_publisher']."

                                            and pih_codretepp = '2'

                                            and date_trunc('day', pih_data)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                    group by venda,

                                            dia,

                                            publisher)

                            -- Contabilizando PINs GoCASH utilizado por Integração de Utilização                

                            union all 

                                    (select 

                                            '".$ARRAY_CONCATENA_ID_VENDA['cards']."'  || to_char(pgc_pin_response_date,'YYMMDD') || lpad(pgc_opr_codigo::text , 3, '0') || lpad(pgc_id::text , 8, '0') as venda,

                                            pgc_pin_response_date as dia,

                                            pgc_opr_codigo as publisher,

                                            count(*) as n, 

                                            CASE WHEN (select opr_product_type from operadoras where opr_codigo = pgc_opr_codigo) = 5 THEN sum(pgc_real_amount) 

                                                 WHEN ((select opr_product_type from operadoras where opr_codigo = pgc_opr_codigo) = 7 OR (select opr_product_type from operadoras where opr_codigo = pgc_opr_codigo) = 4 )  THEN sum (pgc_face_amount) 

                                                 ELSE sum (pgc_face_amount) END as total 

                                    from pins_gocash

                                    where pgc_opr_codigo != 0 

                                             and pgc_opr_codigo = ".$total_epp_adm_row['fp_publisher']."

                                             and date_trunc('day', pgc_pin_response_date)  = '".  substr($total_operacoes_diarias_row['fp_date'], 0, 4)."-".  substr($total_operacoes_diarias_row['fp_date'], 4, 2)."-".  substr($total_operacoes_diarias_row['fp_date'], 6, 2)."' 

                                    group by venda,

                                            dia,

                                            publisher) 

                            ) t 

                            inner join operadoras ON opr_codigo = publisher

                            where publisher = ".$total_epp_adm_row['fp_publisher']."

                                and opr_status = '1' 

                            group by venda, dia

                            order by dia;";

                            //echo $sql.PHP_EOL.PHP_EOL;     die();   

                            $transacoes = SQLexecuteQuery($sql);

                            if ($transacoes) {

                                while ($transacoes_row = pg_fetch_array($transacoes)) {

                                    unset($vetorLines);

                                    $vetorLines = array (

                                                         0 => array('name' => '1115',

                                                                    'size' => 4

                                                                    ),

                                                         1 => array('name' => $transacoes_row['venda'].str_pad($REG_BLC['1115'],6,'0',STR_PAD_LEFT),

                                                                    'size' => 25

                                                                    ),

                                                         2 => array('name' => '',

                                                                    'size' => 8

                                                                    ),

                                                         3 => array('name' => '',

                                                                    'size' => 8

                                                                    ),

                                                         4 => array('name' => $IND_SPLIT,

                                                                    'size' => 1

                                                                    ),

                                                         5 => array('name' => $BANDEIRA,

                                                                    'size' => 2

                                                                    ),

                                                         6 => array('name' => $transacoes_row['dia'],

                                                                    'size' => 6

                                                                    ),

                                                         7 => array('name' => number_format($transacoes_row['total'],2,',',''),

                                                                    'size' => 13

                                                                    ),

                                                         8 => array('name' => $NAT_OPER,

                                                                    'size' => 1

                                                                    ),

                                                         9 => array('name' => '',

                                                                    'size' => 14

                                                                    ),
														10 => array('name' => '',

                                                                    'size' => 14

                                                                    ),
														11 => array('name' => '',

                                                                    'size' => 14

                                                                    )

                                                    );

                                    $file->setVetorLines($vetorLines);

                                    $countBlocoUm++;

                                    $REG_BLC['1115']++;

                                }//endwhile

                            }//end if ($transacoes)

                            else {

                                $msg .= "<strong><br>ERRO 0011</strong>: Erro ao executar a Query das Transações dos Publishes vinculados à E-Prepag Administradora<br><br>";

                                $class = "alert-danger txt-vermelho";                        

                            }//end else do if ($transacoes)

                    

                            //=========================================================================================================================

                            // FIM DO BLOCO - REGISTRO TIPO 1115: OPERAÇÕES POR COMPROVANTE DE PAGAMENTO - Para Publisher vinculados a EPP ADM

                            //=========================================================================================================================

                            

                            

                        }//endwhile

                    }//end if ($total_operacoes_diarias) 

                    else {

                        $msg .= "<strong><br>ERRO 0009</strong>: Erro ao executar o select dos Totais de Operações Diárias<br><br>";

                        $class = "alert-danger txt-vermelho";

                    }//end esle do if ($total_operacoes_diarias) 





                    //=========================================================================================================================

                    // FIM DO BLOCO - REGISTRO TIPO 1110: OPERAÇÕES DIÁRIAS DE PAGAMENTO POR MEIO DE CAPTURA - Para Publisher vinculados a EPP ADM

                    //=========================================================================================================================



                }//endwhile

            }//end if ($total_epp_adm) 

            else {

                $msg .= "<strong><br>ERRO 0008</strong>: Erro ao executar o select dos Totais dos Publishes vinculados à E-Prepag Administradora<br><br>";

                $class = "alert-danger txt-vermelho";

            }//end esle do if ($total_epp_adm) 

            //=========================================================================================================================

            // FIM DO BLOCO - REGISTRO TIPO 1100: RESUMO MENSAL DAS OPERAÇÕES DE PAGAMENTO

            //=========================================================================================================================

            

            

            

            

            //=========================================================================================================================

            // REGISTRO TIPO 1200: CANCELAMENTO EXTEMPORÂNEO ==> Não é nosso caso

            //Este registro deve ser gerado para informar todas as operações canceladas em períodos

            //anteriores a esta declaração. Os cancelamentos identificados no período desta

            //declaração não devem ser informados. Não serão admitidos reportes de cancelamentos

            //parciais, deve-se efetivar o cancelamento integral e, se for o caso, registrar um novo

            //lançamento extemporâneo da efetiva operação no registro 1100 e demais registros

            //hierarquicamente relacionados. Registros de cancelamento extemporâneo são aceitos

            //apenas em arquivos com finalidade 01 ? Normal, campo 03 do registro 0000. 

            //=========================================================================================================================



            

            

            

            //=========================================================================================================================

            // REGISTRO 1990: ENCERRAMENTO DO BLOCO 1 

            //=========================================================================================================================

            unset($vetorLines);

            $countBlocoUm++;

            $vetorLines = array (

                                 0 => array('name' => '1990',

                                            'size' => 4

                                            ),

                                 1 => array('name' => $countBlocoUm,

                                            'size' => 20

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $REG_BLC['1990'] = 1;

            

            

            



            

            

            

            // Contador de registros do Bloco Nove

            $countBlocoNove = 0;            

            

            //=========================================================================================================================

            // REGISTRO 9001: ABERTURA DO BLOCO 9  

            //=========================================================================================================================

            unset($vetorLines);

            $vetorLines = array (

                                 0 => array('name' => '9001',

                                            'size' => 4

                                            ),

                                 1 => array('name' => '1',

                                            'size' => 1

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $countBlocoNove++;

            $REG_BLC['9001'] = 1;

            

 

            

            //=========================================================================================================================

            // REGISTRO TIPO 9900: REGISTROS DO ARQUIVO

            //=========================================================================================================================

            unset($vetorLines);

            foreach ($REG_BLC as $key => $value) {

                $vetorLines = array (

                                     0 => array('name' => '9900',

                                                'size' => 4

                                                ),

                                     1 => array('name' => str_pad($key,4,'0',STR_PAD_LEFT),

                                                'size' => 4

                                                ),

                                     2 => array('name' => $value,

                                                'size' => 20

                                                ),

                                );

                $file->setVetorLines($vetorLines);

                $countBlocoNove++;

            } //end foreach

            $vetorLines = array (

                                 0 => array('name' => '9900',

                                            'size' => 4

                                            ),

                                 1 => array('name' => '9900',

                                            'size' => 4

                                            ),

                                 2 => array('name' => count($REG_BLC)+3,

                                            'size' => 20

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $countBlocoNove++;

            $vetorLines = array (

                                 0 => array('name' => '9900',

                                            'size' => 4

                                            ),

                                 1 => array('name' => '9990',

                                            'size' => 4

                                            ),

                                 2 => array('name' => '1',

                                            'size' => 20

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $countBlocoNove++;

            $vetorLines = array (

                                 0 => array('name' => '9900',

                                            'size' => 4

                                            ),

                                 1 => array('name' => '9999',

                                            'size' => 4

                                            ),

                                 2 => array('name' => '1',

                                            'size' => 20

                                            ),

                            );

            $file->setVetorLines($vetorLines);

            $countBlocoNove++;

            

            

 

            

 

           

            //=========================================================================================================================

            // REGISTRO TIPO 9990: ENCERRAMENTO DO BLOCO 9  

            //=========================================================================================================================

            unset($vetorLines);

            $countBlocoNove++;

            $vetorLines = array (

                                 0 => array('name' => '9990',

                                            'size' => 4

                                            ),

                                 1 => array('name' => $countBlocoNove + 1,

                                            'size' => 20

                                            ),

                            );

            $file->setVetorLines($vetorLines);



            

            



            

            //=========================================================================================================================

            // REGISTRO TIPO 9999: ENCERRAMENTO DO ARQUIVO DIGITAL

            //=========================================================================================================================

            unset($vetorLines);

            $countBlocoNove++;

            $vetorLines = array (

                                 0 => array('name' => '9999',

                                            'size' => 4

                                            ),

                                 1 => array('name' => $countBlocoZero+$countBlocoUm+$countBlocoNove,

                                            'size' => 20

                                            ),

                            );

            $file->setVetorLines($vetorLines);







            $file->saveFile(true,true);

            //echo "<pre>".print_r($_POST,true).print_r($file,true)."</pre>";

            //

            // FINAL DO BLOCO DE GERAÇÃO DO ARQUIVO

            //

            

            echo '<div class="row"><div class="col-md-12 text-center top50"><a href="/dimp/'.date('Ymd').'/'.strtoupper($nomeArquivo).'" class="btn btn-info" download="'.strtoupper($nomeArquivo).'">Download Arquivo DIMP</a><div></div>';



        } //end else do if($freeze_total_row['total'] > 0)

        

    } //end if($freeze_total && $freeze_total_row = pg_fetch_array($freeze_total))

    else {

        $msg = "<strong><br>ERRO 0001</strong>: Erro ao executar o select dos periodos não congelados.<br><br>";

        $class = "alert-danger txt-vermelho";

    }

    

    if(!empty($msg)) {

        ?>

        <div class="container espacamento">

            <div class="col-md-12 <?php echo $class;?>" style="border: 1px solid gray"><?php echo $msg;?></div>

        </div>

        <?php 

    }//end if(!empty($msg))

    

} // end if($BtnSearch)

?>

<style>

.ui-datepicker-calendar {

    display: none;

    }

</style>

<script>

    jQuery(function(e){



        $("#cpfcnpj").keypress(function(){

            if($('#cpfcnpj').val().length > 13){

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

        monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],

        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],

        dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sabado'],

        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],

        dayNamesMin: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],

        firstDay: 0,

        isRTL: false,

        showMonthAfterYear: false,

        yearSuffix: ''};

        $.datepicker.setDefaults($.datepicker.regional['pt-BR']);



        $('#data_inicial').datepicker( {

            changeMonth: true,

            changeYear: true,

            maxDate: "y -1m w d",

            minDate: "y -10m w d",

            setDate: 'today',

            showButtonPanel: true,

            dateFormat: 'mm/yy',

            monthNamesShort: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],

            onClose: function(dateText, inst) { 

                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));

            }

        });



    });

    

    $(function() {

        $("#modal-load").removeClass('show');

        $("#modal-load").addClass('fade');

        

        $("#modal-load").modal('hide');

        $('#BtnSearch').click(function() {

            var msg = "";

            if($("#cod_fin").val().trim() == ""){

                msg += "- É preciso definir código da finalidade do arquivo para o período apurado!\n";

            }

            else if($("#cod_fin").val().trim() == "3" && $("#cpfcnpj").val().trim() == ""){

                msg += "- Para remessa de arquivo para atender notificação é preciso definir um CPF ou CNPJ!\n";

            }

            

            if($("#data_inicial").val().trim() == ""){

                msg += "- É preciso definir uma data para o período apurado!\n";

            }

            

            if($("#estado").val().trim() == ""){

                msg += "- É preciso definir Sigla da Unidade da Federação do Fisco para o qual está sendo prestada a informação para o período apurado!\n";

            }

            

            if(msg !== ""){

                alert("Erros encontrados:\n"+msg);

                return false;

            }

            else {

                manipulaModal(1,'<div class="row"><div class="col-md-10"><b>Aguarde enquanto o sistema está processando as informações para a geração do arquivo.<br>Isto pode levar algum tempo.</b></div><div class="col-md-2"><center><img src="https://www.e-prepag.com.br/imagens/engrenagem.gif" height="50px" /><center></div></div>','<b>ATENÇÃO</b>');

            }

        });

    });

    

    function changeSelect() {

        if($('#cod_fin').val() == "3") {

            $('.cod_fin-3').removeAttr("hidden");

        }

        else {

            $('.cod_fin-3').attr("hidden","hidden");

       

        }

    }

</script>



<?php

echo "<div class='row'><div class='col-md-12 text-center txt-cinza top50'>Elapsed time: ".number_format(getmicrotime() - $time_start, 2, '.', '.')."s</div></div>";

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";

?>

</body>

</html>