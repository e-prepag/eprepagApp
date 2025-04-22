<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/funcoes_cpf.php";

/* 
    CONTROLLER
 */
//Inicializando a variável contendo o ano
if(!isset($dd_nome))   $dd_nome      = "";

//Verificando se executou o click no botão atualizar
if(isset($BtnSearch) && $BtnSearch) {
        //Variavel de OUTPUT
        $msg = "";
        
        //Montando SQL para a Busca das Taxas Envolvidas na Geração do RPS
        $sql = "
                SELECT to_char(data,'DD/MM/YYYY') as data_formatada, nome, cpf, descricao_funcao 
                FROM pep 
                WHERE 
                    nome like '%".strtoupper(trim($dd_nome))."%'
                group by data_formatada, nome, cpf, descricao_funcao
                ORDER BY nome; 
                ";
        //echo $sql."<br>"; die();
        $rs = SQLexecuteQuery($sql);
        if(!$rs) {
                $msg .= "ERRO: Problema na seleção de dados na tabela PEP.<br>";
        }//end if(!$rs) 

        
} // end if($BtnSearch)
/*
    FIM CONTROLLER
 */
?>
<script>
    function fcnOnSubmit(){
        if(buscaNome.dd_nome.value=='' || buscaNome.dd_nome.value==' '){
            alert('Informe dado para a consulta.');
            return false;
        }
    }
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<div class="clearfix"></div>    
<div class="col-md-12 txt-preto">
    <form id="buscaNome" name="buscaNome" method="post" onsubmit="return fcnOnSubmit();">
        <div class="col-md-6">
            <div class="form-group">
                <label for="dd_nome">Nome ou Parte de Nome para Consulta PEP:</label>
                <input type="text" name="dd_nome" id="dd_nome" class="form-control" value="<?php echo $dd_nome; ?>">
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
            <th>CPF</th>
            <th>Nome</th>
            <th>Função</th>
            <th class="text-center">Data do Registro</th>
          </tr>
        </thead>
        <tbody>
        <?php
        while ($rsRow = pg_fetch_array($rs)) {
        ?>
                <tr class="trListagem"> 
                    <td><?php echo mask(str_pad($rsRow['cpf'],11,'0',STR_PAD_LEFT),'###.###.###-##'); ?></td>
                    <td><?php echo $rsRow['nome']; ?></td>
                    <td><?php echo $rsRow['descricao_funcao']; ?></td>
                    <td class="text-center"><?php echo $rsRow['data_formatada']; ?></td>
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