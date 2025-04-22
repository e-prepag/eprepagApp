<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once '../../includes/constantes.php';
// Incluindo a Classe geradora do Arquivo
require_once $raiz_do_projeto."class/util/classFilePosition.php";
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once $raiz_do_projeto."includes/complice/functions.php";
require_once $raiz_do_projeto."class/util/Util.class.php";

//Variavel para teste contendo o ultimo mês permitido
$currentmonthVerify = Util::getTimeByMonth(-1);
$lastMonth = Util::getTimeByMonth(0);

//Variável de controle das mensagens de erro e sucesso nas execuções das querys
$msg = "";

//=========  Mês/Ano considerado na Elaboração dos Arquivos
if (!isset($_POST['periodo']) || empty($_POST['periodo']) || $_POST['periodo'] == -1)  { 

    
    
    if(isset($_POST['periodo']) && $_POST['periodo'] == 0){
        $currentmonth = $lastMonth;
        $mesAno = date('m/Y',$currentmonth);
        $currentmonthVerify = $currentmonth;
        $variado = true;
        $periodo = 0;
    }else{
        $currentmonth = Util::getTimeByMonth(-1);
        $mesAno = date('m/Y',$currentmonth);
        $variado = false;
    }
    
    // Split ano/mes
    list($mes, $ano) = explode("/", $mesAno);

    //Publishers Já em Operação constantes em arquivos BACEN anteriores INTERNacionais
    $vetorPublisher = levantamentoPublisherOperantes($ano,$mes,$variado);

    //Publishers novos nunca antes contou nos arquivos BACEN INTERNacionais
    $vetorPublisherNovos = levantamentoPublisherNovosOperantes($ano,$mes,$variado);

    //Publishers na E-Prepag Pagamentos através da Facilitadora
    $vetorPublisherFacilitadora = levantamentoPublisherEppPagamentosFacilitadora($ano, $mes,$variado);

    //Concatenando os tres vetores
    $vetor_tmp = array_merge($vetorPublisher,$vetorPublisherNovos,$vetorPublisherFacilitadora); 

}
else {
    $currentmonth = Util::getTimeByMonth($_POST['periodo']);
    $mesAno = date('m/Y',$currentmonth);
    $periodo = $_POST['periodo'];
    
    // Split ano/mes
    list($mes, $ano) = explode("/", $mesAno);
    
    $sql = "select cd.opr_codigo from cotacao_dolar cd INNER JOIN operadoras o ON (cd.opr_codigo = o.opr_codigo) where cd_data = '".$ano."-".$mes."-01 00:00:00' ORDER BY opr_nome;" ;
    $rs = SQLexecuteQuery($sql); 
    if($rs && pg_num_rows($rs) > 0 ) {
        while($rs_row = pg_fetch_array($rs)) {
            $vetor_tmp[]=$rs_row['opr_codigo'];
        } //end while   
    }//end if($rs)
    else $vetor_tmp = NULL;
    
} //else do if (empty($_POST['periodo']))

//========= Variável contendo o Ano Mês inicio das operações para efeitos 
$dataInicioOperacao = 201407;

//Data para testes
$testeData = $ano.$mes;

//Limpando saida
ob_clean();

require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>
<style>
    
    .dnone{
        display: none;
    }
    
    .fontColor{
        color: #707070  ;
    }
    
    .corGrupo{
        background-color: #ebebeb ;
    }
    .panel{      
        background-color: #ebebeb ;
        margin: 10px;
        padding: 10px;
    }
    
    .margin10{
        padding-top: 5px;
        padding-bottom: 5px;
        margin: 10px;
    }
    
    .marginLado{
        margin-left: 5px;
        margin-right: 5px;
    }
    
    .btnTamanho {
        width: 30%;
        white-space: normal;
        margin-left: 5px;
        margin-right: 5px;
    }
    
    .btnSalvar{
        width: 10%;
        white-space: normal; 
        margin-top: 10px;
    }
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="clearfix"></div>
<?php

//Capturando o botão submit
$btSubmit = isset($_POST['btSubmit']) ? $_POST['btSubmit'] : false;

//Teste para edição e alteração da cotação
if($testeData == date('Ym',$currentmonthVerify) && $btSubmit) {   
    $dias_mes = cal_days_in_month(CAL_GREGORIAN, date("n",$currentmonthVerify), date("Y",$currentmonthVerify)); // 31
    $maior_data = null;
    $menor_data = null;
    foreach ($vetor_tmp as $value) {
        
        if(isset($_POST["maisdeuma"][$value])){
            if(isset($_POST['multiCotacaoValor' . $value])){
                $multiCotacaoInicio = $_POST['multiCotacaoInicio'.$value];
                $multiCotacaoFim = $_POST['multiCotacaoFim'.$value];
                $totalDias = 0;
                for($i = 0; $i < count($multiCotacaoInicio); $i++){
                    $dataInicio = $multiCotacaoInicio[$i];
                    $dataFim = $multiCotacaoFim[$i];
                    if($menor_data == null || $dataInicio < $menor_data) $menor_data = $dataInicio;
                    if($maior_data == null || $dataFim > $maior_data) $maior_data = $dataFim;
                    $totalDias += ((strtotime($multiCotacaoFim[$i]) - strtotime($multiCotacaoInicio[$i])) / (60 * 60 * 24)) + 1;
                    if(date("n", strtotime($multiCotacaoInicio[$i])) != date("n",$currentmonthVerify) || date("n", strtotime($multiCotacaoFim[$i])) != date("n",$currentmonthVerify)){
                        $msg .= "ERRO: O MÊS INFORMADO NOS INTERVALOS NÃO CORRESPONDE AO MÊS DA COTAÇÃO";
                        break;
                    }
                }
                              
                if(((strtotime($maior_data) - strtotime($menor_data)) / (60 * 60 * 24)) + 1 < $totalDias){
                    $msg .= "<br>ERRO: OS INTERVALOS CADASTRADOS DE COTAÇÃO DE DÓLAR SE SOBREPÕEM<br>";
                }

                if($msg == "" && $totalDias != $dias_mes && $currentmonthVerify != $lastMonth){
                    $msg .= "<br>ERRO: OS INTERVALOS CADASTRADOS DE COTAÇÃO DE DÓLAR NÃO ENGLOBAM OU ULTRAPASSAM OS DIAS DO MÊS<br>";
                }
            }else{
                $msg .= "<br>ERRO: A OPERADORA $value ESTÁ MARCADA PARA MULTICOTAÇÃO, PORÉM NENHUMA FOI INFORMADA.<br>";
            }
        }
        
        if(!empty($value) && $msg == "") {
            
            //Verificando se exite o registro
            $sql = "select cd_cotacao from cotacao_dolar where opr_codigo = ".$value." and cd_data = '".date('Y-m-d',$currentmonthVerify)." 00:00:00';";
            //echo $sql."<br>";
            $rsTeste = SQLexecuteQuery($sql);
            if($rsTeste && pg_num_rows($rsTeste)) {
                
                if(isset($_POST["multiCotacao".$value]) && $_POST["multiCotacao".$value] == "1"){
                    
                    //Limpando cotações e adicionando de novo
                    $data = mktime(0, 0, 0, date("n",$currentmonthVerify), 1, date("Y",$currentmonthVerify));
                    $mesAno = date('m/Y',$data);
                    
                    $dataProx = mktime(0, 0, 0, date("n",$currentmonthVerify) + 1, 1, date("Y",$currentmonthVerify));
                    $mesAnoProx = date('m/Y',$dataProx);
                    
                    list($mes, $ano) = explode("/", $mesAno);
                    list($mesProx, $anoProx) = explode("/", $mesAnoProx);
                    
                    $sql = "DELETE FROM cotacao_dolar where cd_data >= '".$ano."-".$mes."-01 00:00:00' and cd_data < '".$anoProx."-".$mesProx."-01 00:00:00' AND opr_codigo = ".$value." AND cd_freeze = 0;";
                    //die($sql);
                    $rs_delete_cotacao = SQLexecuteQuery($sql); 
                    if(!$rs_delete_cotacao){
                        $msg .= "Erro ao deletar as cotações de dólar para o operador $value! <br>";
                    }elseif(isset($_POST["maisdeuma"][$value])){
                        for($i = 0; $i < count($_POST["multiCotacaoInicio".$value]); $i++){
                            if($_POST["multiCotacaoCongelada".$value][$i] == '0'){
                                list($ano, $mes, $dia) = explode("-", $_POST["multiCotacaoInicio".$value][$i]);
                                $numDias = ((strtotime($_POST["multiCotacaoFim".$value][$i]) - strtotime($_POST["multiCotacaoInicio".$value][$i])) / (60 * 60 * 24)) + 1;
                                for($j = 0; $j < $numDias; $j++){
                                    //Inserindo
                                    $diaAtual = $dia + $j;
                                    $data = $ano . "-" . $mes . "-" . $diaAtual;
                                    $sql = "insert into cotacao_dolar values ('".$data." 00:00:00',".$value.",".str_replace(",",".",$_POST["multiCotacaoValor".$value][$i]).");";

                                    $rsInsert = SQLexecuteQuery($sql);
                                    if(!$rsInsert) $msg .= "Erro ao inserir a cotação de R$".$_POST["multiCotacaoValor".$value][$i]." para do código de operador $value na data $data!<br>";
                                }
                            }
                        }
                    }else{
                        if(!empty($cotacao[$value])){
                            
                            $numDias = date("t", $currentmonthVerify);
                            $data = mktime(0, 0, 0, date("n",$currentmonthVerify), 1, date("Y",$currentmonthVerify));
                            $mesAno = date('m/Y',$data);
                            list($mes, $ano) = explode("/", $mesAno);
                            for($i = 1; $i <= $numDias; $i++){
                                $data = $ano . "-" . $mes . "-" . $i;
                                $sql = "insert into cotacao_dolar values ('".$data." 00:00:00',".$value.",".str_replace(",",".",$cotacao[$value]).");";
                                $rsInsert = SQLexecuteQuery($sql);
                                if(!$rsInsert) $msg .= "Erro ao inserir a cotação de R$".$cotacao[$value]." para do código de operador $value na data $data!<br>";
                            }
                        }
                    }
                }else{
                    //Atualizando
                    if(!empty($cotacao[$value])){
                        $sql = "update cotacao_dolar set cd_cotacao = ".str_replace(",",".",$cotacao[$value])." where opr_codigo = ".$value." and cd_data = '".date('Y-m-d',$currentmonthVerify)." 00:00:00';";
                        $rsUpdate = SQLexecuteQuery($sql);
                        if(!$rsUpdate) $msg .= "Erro ao atualizar a cotação de R$ ".$cotacao[$value]." para do código de operador $value na data ".date('Y-m-d',$currentmonthVerify)."!<br>";
                    }
                }
                    
            }//end if($rsTeste && pg_num_rows($rsTeste))
            else {
                if($_POST["multiCotacao".$value] == "1"){
                    if(isset($_POST["maisdeuma"][$value])){
                        
                        for($i = 0; $i < count($_POST["multiCotacaoInicio".$value]); $i++){
                            list($ano, $mes, $dia) = explode("-", $_POST["multiCotacaoInicio".$value][$i]);
                            $numDias = ((strtotime($_POST["multiCotacaoFim".$value][$i]) - strtotime($_POST["multiCotacaoInicio".$value][$i])) / (60 * 60 * 24)) + 1;
                            for($j = 0; $j < $numDias; $j++){
                                //Inserindo
                                $diaAtual = $dia + $j;
                                $data = $ano . "-" . $mes . "-" . $diaAtual;
                                $sql = "insert into cotacao_dolar values ('".$data." 00:00:00',".$value.",".str_replace(",",".",$_POST["multiCotacaoValor".$value][$i]).");";

                                $rsInsert = SQLexecuteQuery($sql);
                                if(!$rsInsert) $msg .= "Erro ao inserir a cotação de R$ ".$_POST["multiCotacaoValor".$value][$i]." para do código de operador $value na data $data!<br>";
                            }

                        }
                    }else{
                        if(!empty($cotacao[$value])){
                            
                            $numDias = date("t", $currentmonthVerify);
                            $data = mktime(0, 0, 0, date("n",$currentmonthVerify), 1, date("Y",$currentmonthVerify));
                            $mesAno = date('m/Y',$data);
                            list($mes, $ano) = explode("/", $mesAno);
                            for($i = 1; $i <= $numDias; $i++){
                                $data = $ano . "-" . $mes . "-" . $i;
                                $sql = "insert into cotacao_dolar values ('".$data." 00:00:00',".$value.",".str_replace(",",".",$cotacao[$value]).");";
                                $rsInsert = SQLexecuteQuery($sql);
                                if(!$rsInsert) $msg .= "Erro ao inserir a cotação de R$".$cotacao[$value]." para do código de operador $value na data $data!<br>";
                            }
                        }
                    }
                }else{
                    if(!empty($cotacao[$value])){
                        //Inserindo
                        $sql = "insert into cotacao_dolar values ('".date('Y-m-d',$currentmonthVerify)." 00:00:00',".$value.",".str_replace(",",".",$cotacao[$value]).");";
                        $rsInsert = SQLexecuteQuery($sql);
                        if(!$rsInsert) $msg .= "Erro ao inserir a cotação de R$ ".$cotacao[$value]." para do código de operador $value na data ".date('Y-m-d',$currentmonthVerify)."!<br>";
                    }
                }
            }//end else do if($rsTeste && pg_num_rows($rsTeste))
            
        }//end if(!empty($value)) 
        
    }//end foreach
    
    if($msg != ""){
        echo '<div id="modal-load" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title txt-vermelho" id="modal-title"><b>ATENÇÃO</b></h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" id="tipo-modal" role="alert"> 
                            <h5><span id="error-text"><div class="row"><div class="col-md-12"><b>'.$msg.'</b></div></span></h5>
                      </div>
                    </div>
                </div>
            </div>
        </div>';
        echo "<script> $('#modal-load').modal('show');</script>";
        echo "<script> $('.modal-backdrop').fadeOut(500);</script>";
    }else{
        echo '<div id="modal-load" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title txt-verde" id="modal-title"><b>SUCESSO</b></h4>
                    </div>
                    <div class="modal-body">
                        <h5><span><div class="row"><div class="col-md-12 txt-verde"><b>As cotações foram atualizadas com sucesso !</b></div></span></h5>
                    </div>
                </div>
            </div>
        </div>';
        echo "<script> $('#modal-load').modal('show');</script>";
        echo "<script> $('.modal-backdrop').fadeOut(500);</script>";
    }
    
}// end if($testeData == date('Ym',$currentmonthVerify) && $btSubmit)

// Exibindo o Período de Apuração 
echo "<fieldset><legend>Mês/Ano do período de apuração da cotação do dolar: <span class='glyphicon glyphicon-backward t0 c-pointer' aria-hidden='true' title='Volta um período para consulta' id='voltar'></span> <span style='color: red'> ".$mesAno."</span> ".(($testeData >= date('Ym',$lastMonth))?"":"<span class='glyphicon glyphicon-forward t0 c-pointer' aria-hidden='true' title='Avança um período para consulta' id='avancar'></span>");
echo (date("m", $currentmonth) + 1 == date("m", $lastMonth)) ? "<span style='color:red; font-size: 12px; margin-bottom: 20px !important;'>(Mês vigente somente cotação variável)</span>" : "";
echo "</legend>".PHP_EOL;

// Teste de Abortagem
if($testeData < $dataInicioOperacao) {
    die("O mês ano deve ser obrigatóriamente superior a ".$dataInicioOperacao." (AAAAMM).<br>\n");
}// end if($testeData < 201403)


//Montando o array com Operadoras
$sqlopr = "select opr_nome, opr_codigo, opr_cotacao_dolar from operadoras order by opr_nome"; //where (opr_status != '0') 
$resopr = SQLexecuteQuery($sqlopr);
while($resopr_row = pg_fetch_array($resopr)) {
    $vetorOperadoras[$resopr_row['opr_codigo']] = $resopr_row['opr_nome'];
    $vetorCotacaoMultipla[$resopr_row['opr_codigo']] = $resopr_row['opr_cotacao_dolar'];
}//end while

//Buscando dados na tabela
$sql = "select cd_cotacao,opr_codigo,cd_freeze,cd_data from cotacao_dolar where to_char(cd_data,'YYYY-MM') = '".date('Y-m',$currentmonth)."';";
$rs = SQLexecuteQuery($sql);
if($rs && pg_num_rows($rs) > 0 ) {
    while($rs_row = pg_fetch_array($rs)) {
        $vetorCotacaoUSS[$rs_row['opr_codigo']] = $rs_row['cd_cotacao'];
        $vetorCotacaoCongelada[$rs_row['opr_codigo']] = $rs_row['cd_freeze'];     
    } //end while   
}//end if($rs)
else {
    foreach ($vetorOperadoras as $key => $value) {
        $vetorCotacaoCongelada[$key] = 0;
    }
}
?>
<script language="javascript">
    $(function(){
        $("#avancar").click(function(){
            $("#periodo").val(parseInt($("#periodo").val())+1);
            document.form1.submit();
        });
    });
    $(function(){
        $("#voltar").click(function(){
            $("#periodo").val(parseInt($("#periodo").val())-1);
            document.form1.submit();
        });
    });

</script>
<script>
    
    
    
    function showMaisCotacoes(value){
        var element = $("input[name='maisdeuma["+value+"]'");
        if(element.is(':checked')){
            $("#listaCotacoes"+value).removeClass("dnone");
            $("#divListagem"+value).addClass("corGrupo");
            $("input[name='cotacao["+value+"]']").attr("readonly", "true");
<?php 
            if($testeData == date('Ym',$currentmonthVerify)) {
?>   
            $("input[name='cotacao["+value+"]']").val("Adicione as cotações abaixo");
<?php
            }
?>            
        }else{
            $("#listaCotacoes"+value).addClass("dnone");
            $("#divListagem"+value).removeClass("corGrupo");
            $("input[name='cotacao["+value+"]']").removeAttr("readonly");
            var first_value = $("input[name='cotacao["+value+"]']").attr("fv");
            $("input[name='cotacao["+value+"]']").val(first_value);
        }
    }
    
    function addCotacao(value){
        html = "";
        html += "<div class='row top5 p-8'>";
        html +=    "<div class='col-md-4 fontColor fontColor text-right'>";
        html +=        "US$ <input type='text' class='marginLado' name='multiCotacaoValor"+value+"[]' value='0,00' maxlength='16' required></input>";
        html +=    "</div>";
        html +=    "<div class='col-md-3 fontColor'>";
        html +=        "Início <input type='date' class='marginLado' name='multiCotacaoInicio"+value+"[]' <?php echo "value='" . date("Y-m", $currentmonthVerify) . "-01'";  ?> <?php echo "max='" . date("Y-m", $currentmonthVerify) . "-" . date("t", $currentmonthVerify) . "'";  ?> required></input>";
        html +=    "</div>";
        html +=    "<div class='col-md-3 fontColor'>";
        html +=        "Fim <input type='date' class='marginLado' name='multiCotacaoFim"+value+"[]' <?php echo "value='" . date("Y-m", $currentmonthVerify) . "-01'";  ?> <?php echo "max='" . date("Y-m", $currentmonthVerify) . "-" . date("t", $currentmonthVerify) . "'";  ?> required></input>";
        html +=    "</div>";
        html +=    "<div class='col-md-2'>";
        html +=        "<a class='btn btn-danger btnTamanho' onclick='removeCotacao(this)'> - </a>";
        html +=    "</div>";
        html +=    '<input type="hidden" name="multiCotacaoCongelada'+value+'[]" value="0"></input>';
        html += "</div>";
        $("#colListaCotacoes"+value).append(html);
        console.log($("input[name='multiCotacaoInicio"+value+"[]']"));
    }
    
    function removeCotacao(element){
        var pai = $(element).parent();
        var avo = pai.parent();
        console.log(avo.siblings().length);
        if(avo.siblings().length > 0){
            avo.remove();
        }
    }
    
    
</script>
<?php
    if($currentmonthVerify == $lastMonth){
?>
<div class="row">
    <div class="col-md-12 text-center">
        <span style="color: red;">O mês atual é o mês vigente e portanto apenas as operadoras que utilizam múltiplas cotações de dólar serão apresentadas</span>
    </div>
</div>

<?php
    }
?>
<form id="form1" name="form1" method="post" action="">
    <input type="hidden" name="periodo" id="periodo" value="<?php echo (!isset($periodo))?"-1": $periodo; ?>" />
<?php                        
$totalPublisher = 0;
$testeCongelamento =  false;
if(!isset($vetor_tmp) || empty($vetor_tmp) || count($vetor_tmp) == 0){
?>
    <div class="row">
        <div class="col-md-12 text-center">
            <span style="color: red;">Não existem cotações salvas para esse mês</span>
        </div>
    </div>
<?php
}else{
    foreach ($vetor_tmp as $value) {

        if(!empty($value)) {
            $checked = "";
            $html = "";
            $totalPublisher++;
            echo "<div class='divListagem".$value."' id='divListagem".$value."'>
                <div class='row trListagem margin10'>
                    <div class='col-md-3 text-right'><b>".$vetorOperadoras[$value]."</b></div>".PHP_EOL;
            if($testeData == date('Ym',$currentmonthVerify) && isset($vetorCotacaoCongelada[$value]) && $vetorCotacaoCongelada[$value] == '0' || !isset($vetorCotacaoCongelada[$value]) || $vetorCotacaoMultipla[$value] == 1) {
                $multipla_cotacao = $vetorCotacaoMultipla[$value];
                $testeCongelamento = true;
                $readonly = "";
                if($multipla_cotacao ==1){
                    $sql = "SELECT COUNT(*), cd_cotacao FROM cotacao_dolar WHERE opr_codigo = $value AND to_char(cd_data,'YYYY-MM')  = '" . date("Y-m", $currentmonth) . "' GROUP BY cd_cotacao";
                    $rs_num_cotacoes = SQLexecuteQuery($sql);

                    $sql = "SELECT * FROM cotacao_dolar WHERE opr_codigo = $value AND to_char(cd_data,'YYYY-MM')  = '" . date("Y-m", $currentmonth) . "' ORDER BY cd_data";
                    $rs_cotacoes = SQLexecuteQuery($sql);
                    $rs_row = pg_fetch_array($rs_cotacoes);
                    $cotacao_congelada = $rs_row["cd_freeze"];
                    if($rs_cotacoes && $rs_num_cotacoes && pg_num_rows($rs_cotacoes) > 0 && (pg_num_rows($rs_num_cotacoes) > 1 || $currentmonthVerify ==  $lastMonth)) {
                        $checked = "checked";
                        $dataAtual = explode(" ",$rs_row["cd_data"])[0];
                        $valorAtual =  $rs_row["cd_cotacao"];
                        if($cotacao_congelada == 1){
                            $html .= "<div class='row top5 p-8'>";
                            $html .=    "<input type='hidden' name='multiCotacaoValor".$value."[]' value='".$valorAtual."'></input>";
                            $html .=    "<input type='hidden' name='multiCotacaoInicio".$value."[]' value='".$dataAtual."'></input>";
                            $html .=    "<input type='hidden' name='multiCotacaoCongelada".$value."[]' value='1'></input>";
                            $html .=    "<div class='col-md-4 fontColor text-center'>";
                            $html .=        "US$$valorAtual";
                            $html .=    "</div>";
                            $html .=    "<div class='col-md-3 fontColor text-center'>";
                            $html .=        "Início: " . date("d/m/Y", strtotime($dataAtual));
                            $html .=    "</div>";
                        }else{
                            $html .= "<div class='row top5 p-8'>";
                            $html .=    "<div class='col-md-4 fontColor fontColor text-right'>";
                            $html .=        "US$ <input type='text' class='marginLado' name='multiCotacaoValor".$value."[]' value='".$valorAtual."' maxlength='16' required></input>";
                            $html .=    "</div>";
                            $html .=    "<div class='col-md-3 fontColor'>";
                            $html .=        "Início <input type='date' class='marginLado' name='multiCotacaoInicio".$value."[]' value='".$dataAtual."' max='" . date("Y-m", $currentmonth) . "-" . date("t", $currentmonth) . "' required></input>";
                            $html .=        "<input type='hidden' name='multiCotacaoCongelada".$value."[]' value='0'></input>";
                            $html .=    "</div>";
                        }
                        while($rs_row = pg_fetch_array($rs_cotacoes)) {
                            if($valorAtual != $rs_row["cd_cotacao"]){
                                if($cotacao_congelada == 1){
                                    $html .=    "<div class='col-md-3 fontColor text-center'>";
                                    $html .=        "Fim: " . date("d/m/Y", strtotime($dataAtual));
                                    $html .=    "</div>";
                                    $html .=    "<input type='hidden' name='multiCotacaoFim".$value."[]' value='".$dataAtual."'></input>";
                                    $html .= "</div>";
                                }else{
                                    $html .=    "<div class='col-md-3 fontColor'>";
                                    $html .=        "Fim <input type='date' class='marginLado' name='multiCotacaoFim".$value."[]' value='".$dataAtual."' max='" . date("Y-m", $currentmonth) . "-" . date("t", $currentmonth) . "' required></input>";
                                    $html .=    "</div>";
                                    $html .=    "<div class='col-md-2'>";
                                    $html .=        "<a class='btn btn-danger btnTamanho' onclick='removeCotacao(this)'> - </a>";
                                    $html .=    "</div>";
                                    $html .= "</div>";
                                }
                                $valorAtual = $rs_row["cd_cotacao"];
                                $dataAtual = explode(" ",$rs_row["cd_data"])[0];
                                $cotacao_congelada = $rs_row["cd_freeze"];
                                if($cotacao_congelada == 1){
                                    $html .= "<div class='row top5 p-8'>";
                                    $html .=    "<input type='hidden' name='multiCotacaoValor".$value."[]' value='".$valorAtual."'></input>";
                                    $html .=    "<input type='hidden' name='multiCotacaoInicio".$value."[]' value='".$dataAtual."'></input>";
                                    $html .=    "<input type='hidden' name='multiCotacaoCongelada".$value."[]' value='1'></input>";
                                    $html .=    "<div class='col-md-4 fontColor text-center'>";
                                    $html .=        "US$$valorAtual";
                                    $html .=    "</div>";
                                    $html .=    "<div class='col-md-3 fontColor text-center'>";
                                    $html .=        "Início: " . date("d/m/Y", strtotime($dataAtual));
                                    $html .=    "</div>";
                                }else{
                                    $html .= "<div class='row top5 p-8'>";
                                    $html .=    "<div class='col-md-4 fontColor text-right'>";
                                    $html .=        "US$ <input type='text' class='marginLado' name='multiCotacaoValor".$value."[]' value='".$valorAtual."' maxlength='16' required></input>";
                                    $html .=    "</div>";
                                    $html .=    "<div class='col-md-3 fontColor'>";
                                    $html .=        "Início <input type='date' class='marginLado' name='multiCotacaoInicio".$value."[]' value='".$dataAtual."' max='" . date("Y-m", $currentmonth) . "-" . date("t", $currentmonth) . "' required></input>";
                                    $html .=        "<input type='hidden' name='multiCotacaoCongelada".$value."[]' value='0'></input>";
                                    $html .=    "</div>";
                                }
                            }else{
                                $dataAtual = explode(" ",$rs_row["cd_data"])[0];
                            }

                        } //end while
                        if($cotacao_congelada == 1){
                            $html .=    "<div class='col-md-3 fontColor text-center'>";
                            $html .=        "Fim: " . date("d/m/Y", strtotime($dataAtual));
                            $html .=    "</div>";
                            $html .=    "<input type='hidden' name='multiCotacaoFim".$value."[]' value='".$dataAtual."'></input>";
                            $html .= "</div>";
                        }else{
                            $html .=    "<div class='col-md-3 fontColor'>";
                            $html .=        "Fim <input type='date' class='marginLado' name='multiCotacaoFim".$value."[]' value='".$dataAtual."' max='" . date("Y-m", $currentmonth) . "-" . date("t", $currentmonth) . "' required></input>";
                            $html .=    "</div>";
                            $html .=    "<div class='col-md-2'>";
                            $html .=        "<a class='btn btn-danger btnTamanho' onclick='removeCotacao(this)'> - </a>";
                            $html .=    "</div>";
                            $html .= "</div>";
                        }
                    }

                }
                if($checked != "" && $testeData == date('Ym',$currentmonthVerify)){
                    $valor = "Adicione as cotações abaixo";
                    $readonly = "readonly";
                }elseif(isset($cotacao[$value])){
                    $valor = str_replace(".",",",$cotacao[$value]);
                }
                else{
                    $valor = (empty($vetorCotacaoUSS[$value])?"0,00":str_replace(".",",",$vetorCotacaoUSS[$value]));
                }
                if (isset($vetorCotacaoCongelada[$value]) && $vetorCotacaoCongelada[$value] == 0 || !isset($vetorCotacaoCongelada[$value])) {
                    echo "<div class='col-md-9 fontColor text-left'>US$  <input class='marginLado fontColor' type='text' name='cotacao[".$value."]' fv='".(empty($vetorCotacaoUSS[$value])?"0,00":str_replace(".",",",$vetorCotacaoUSS[$value]))."' id='cotacao[".$value."]' value='".$valor."' maxlength='16' ".$readonly."></input>"; 
                }
                else {
                    echo "<div class='col-md-9 fontColor text-left'>".(($multipla_cotacao == 1 && $rs_num_cotacoes && pg_num_rows($rs_num_cotacoes) > 1)?"Cotações Abaixo:":"US$ ".str_replace(".",",",((isset($value) && isset($vetorCotacaoUSS[$value]))?$vetorCotacaoUSS[$value]:"0,00")).""); 
                }
                echo "<input type='hidden' name='multiCotacao$value' value='$multipla_cotacao'/>";
                if($multipla_cotacao == 1){
                    echo "<input class='form-check-input marginLado' type='checkbox' id='maisdeuma[".$value."]' onclick='showMaisCotacoes(".$value.")' name='maisdeuma[".$value."]' " . $checked .(($cotacao_congelada == 1)?" disabled":"")."><label class='form-check-label marginLado fontColor' for='maisdeuma[".$value."]' >Possui mais de uma cotação dentro do mês</label></div>".PHP_EOL;
                }
            }
            else {
                $html = "";
                $sql = "SELECT opr_cotacao_dolar FROM operadoras WHERE opr_codigo = $value";
                $rs_multipla_cotacao = SQLexecuteQuery($sql);
                $multipla_cotacao = pg_fetch_array($rs_multipla_cotacao)[0];
                if($multipla_cotacao ==1){
                    $sql = "SELECT COUNT(*), cd_cotacao FROM cotacao_dolar WHERE opr_codigo = $value AND to_char(cd_data,'YYYY-MM')  = '" . date("Y-m", $currentmonth) . "' GROUP BY cd_cotacao";
                    $rs_num_cotacoes = SQLexecuteQuery($sql);

                    $sql = "SELECT * FROM cotacao_dolar WHERE opr_codigo = $value AND to_char(cd_data,'YYYY-MM')  = '" . date("Y-m", $currentmonth) . "' ORDER BY cd_data";
                    $rs_cotacoes = SQLexecuteQuery($sql);
                    if($rs_cotacoes && $rs_num_cotacoes && pg_num_rows($rs_cotacoes) > 0 && pg_num_rows($rs_num_cotacoes) > 1) {
                        $rs_row = pg_fetch_array($rs_cotacoes);
                        $dataAtual = explode(" ",$rs_row["cd_data"])[0];
                        $dataAtual = explode(" ",$rs_row["cd_data"])[0];
                        $valorAtual =  $rs_row["cd_cotacao"];
                        $html .= "<div class='col-md-9 fontColor'> US$ ".$valorAtual." no intervalo entre ".date("d/m/Y",strtotime($dataAtual))." e ";
                        while($rs_row = pg_fetch_array($rs_cotacoes)) {
                            if($valorAtual != $rs_row["cd_cotacao"]){
                                $html .= date("d/m/Y",strtotime($dataAtual)) . "</div>".PHP_EOL;
                                $valorAtual = $rs_row["cd_cotacao"];
                                $dataAtual = explode(" ",$rs_row["cd_data"])[0];
                                $html .= "<div class='col-md-offset-3 col-md-9 fontColor'> US$ ".$valorAtual." no intervalo entre ".date("d/m/Y",strtotime($dataAtual))." e ";
                            }else{
                                $dataAtual = explode(" ",$rs_row["cd_data"])[0];
                            }
                        }
                        $html .= date("d/m/Y",strtotime($dataAtual))." </div>".PHP_EOL;
                        echo $html;
                    }else{
                        echo "<div class='col-md-2 fontColor'> US$ ".(empty($vetorCotacaoUSS[$value])?"0,00":str_replace(".",",",$vetorCotacaoUSS[$value]))."</div>".PHP_EOL;
                    }
                }else{
                    echo "<div class='col-md-2 fontColor'> US$ ".(empty($vetorCotacaoUSS[$value])?"0,00":str_replace(".",",",$vetorCotacaoUSS[$value]))."</div>".PHP_EOL;
                }
            }
            echo "</div>".PHP_EOL;

            echo    "<div class='row panel dnone' id='listaCotacoes".$value."'>
                        <div class='col-md-12' id='colListaCotacoes".$value."'>
                            <div class='row'>
                                <div class='col-md-1'></div>
                                <div class='col-md-9'>
                                    <h5><b>Cadastro de multiplas cotações para $vetorOperadoras[$value]</b></h5>
                                </div>
                                <div class='col-md-2'>
                                    ".($testeData == date('Ym',$currentmonthVerify)?"<a class='btn btn-success btnTamanho' onclick='addCotacao($value)'> + </a>":"")."
                                </div>
                            </div>
                            $html
                        </div>
                    </div>";
            echo "</div>";
            echo "<script> showMaisCotacoes(".$value."); </script>";

        } //end if(!empty($value)) 

    } //end foreach
} //end if(!isset($vetor_tmp) || empty($vetor_tmp) || count($vetor_tmp) == 0)

if($testeData == date('Ym',$currentmonthVerify) && $testeCongelamento) {
    echo "
        <div class='row top10' align='center'>             
                <input type='submit' value='Salvar' id='btSubmit' name='btSubmit' class='btn btn-success btnSalvar'>           
        </div>";
    
}
echo "<br><br>Total de <b>".$totalPublisher." Publishers</b> envolvido neste Período.";
?>
</form>
</fieldset>

