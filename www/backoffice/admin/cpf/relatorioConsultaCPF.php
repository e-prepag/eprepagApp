<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."consulta_cpf/config.inc.cpf.php";
require_once "/www/includes/bourls.php";
/* 
    CONTROLLER
 */
//Inicializando a variável contendo o ano
if(!isset($dd_ano))   $dd_ano      = date('Y');

//Verificando se executou o click no botão atualizar
if(isset($BtnSearch) && $BtnSearch) {
        //Variavel de OUTPUT
        $msg = "";
        
        $where = "date_trunc('year', cp_date) = '".$dd_ano."-01-01 00:00:00'";
        
        //Montando SQL para a Busca das Taxas Envolvidas na Geração do RPS
        $sql = "
                SELECT to_char(cp_date,'MM') as mes,* 
                FROM cpf_partners 
                WHERE 
                    $where
                ORDER BY cp_date DESC; 
                ";
        //echo $sql."<br>"; die();
        $rs = SQLexecuteQuery($sql);
        if($rs) {
            
                //Setando Vetor Consulta
                $vetorConsulta = array();
                
                $mesAnterior = "";
                while ($rsRow = pg_fetch_array($rs)) {
                        if($mesAnterior != $rsRow['mes']) {
                            foreach($vetorReverso as $key => $value){
                                $vetorConsulta[$rsRow['mes']*1][$key] = 0;
                            }//end foreach
                        }//end if($mesAnterior != $rsRow['mes'])
                        $vetorConsulta[$rsRow['mes']*1][$rsRow['cp_id']] = $rsRow['cp_count'];
                        $mesAnterior = $rsRow['mes']*1;
                }//end while
                
                //echo "<pre>".print_r($vetorConsulta,true)."</pre>";
        }//end if($rs) 
        else {
                $msg .= "ERRO: Problema na Geração do Relatório de Consultas Anuais.<br>";
        }//end else do if($rs) 
} // end if($BtnSearch)
/*
    FIM CONTROLLER
 */
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script>
    $(function(){
        var optDate = new Object();
            optDate.interval = 6;
            optDate.minDate = "01/01/2014";

        setDateInterval('dataI','dataF',optDate);
    });
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12 txt-preto">
    <form id="buscaBanner" name="buscaBanner" method="post">
        <div class="col-md-3">
            <div class="form-group">
                <label for="dd_ano">Ano Base de Consulta:</label>
                <select name="dd_ano" id="dd_ano" class="form-control">
                        <?php 
                        for($i =  date('Y'); $i >= 2014 ; $i--) 
                        { 
                        ?>
                        <option value="<?php echo $i ?>" <?php if($dd_ano == $i) echo "selected" ?>><?php echo $i ?></option>
                        <?php 
                        } 
                        ?>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" name="BtnSearch" value="Consultar" class="btn top20 btn-success ">Consultar</button>
        </div>
    </form>
</div>
<?php
if(isset($rs) && isset($BtnSearch)) {

        //Setando variaveis para captura no mês referência
        setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        date_default_timezone_set('America/Fortaleza');
                
?>
<div class="col-md-12 bg-cinza-claro">
    <table id="table" class="table bg-branco txt-preto fontsize-p">
        <thead>
          <tr class="bg-cinza-claro">
            <th>Mês</th>
            <?php
            ksort($vetorReverso);
            foreach($vetorReverso as $key => $value){
            ?>
            <th class="text-right"><?php echo $vetorLegenda[$key]; ?></th>
            <?php
            }//end foreach
            ?>
          </tr>
        </thead>
        <tbody>
        <?php
        foreach($vetorConsulta as $key => $value) {
                $mesFechamento = mktime(0, 0, 0, $key, 1, $dd_ano*1);
        ?>
                <tr class="trListagem"> 
                    <td><?php echo ucfirst(strftime("%B",$mesFechamento)); ?></td>
                    <?php
                    ksort($value);
                    foreach($value as $chave => $valor) {
                    ?>
                    <td class="text-right"><?php echo number_format($valor, 0, ',', '.') ?></td>
                    <?php 
                    } //end foreach
                    ?>
                </tr>
        <?php        
        }//foreach
        ?>
        </tbody>
    </table>
</div>
<?php    
}//end if(isset($rs))
else if(isset($msg)) {
    echo "<div class='txt-preto'>".$msg."</div>";
}
?>
<div class="bloco row">
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</div>    
</body>
</html>