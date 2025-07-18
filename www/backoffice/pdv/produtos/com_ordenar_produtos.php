<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

	$time_start = getmicrotime();

    $idErrors = array();
    
    if (isset($_POST['produtos']) && !empty($_POST['produtos'])) {

        foreach($_POST['produtos'] as $id => $odem){
            $sql = "update tb_dist_operadora_games_produto set ogp_ordem = " . $odem . " where ogp_id = " . $id;
                           
            if(!$ret1 = SQLexecuteQuery($sql)){
                $idErrors[] = $id;
            }
        }
        
        /*
            Bloco para atualizar listagem de produtos
         */
        $filtro['opr'] = 1;
        $filtro['opr_status'] = '1';
        $filtro['ogp_codigo_negado'] = 39;
        $filtro['ogp_mostra_integracao_gamer_com_loja'] = '1'; // Wagner
        $filtro['ogp_ativo'] = 1;
        
        require_once  $raiz_do_projeto."class/util/Busca.class.php";
        $arrJsonFiles = unserialize(ARR_PRODUTOS_CREDITOS);
        $busca = new Busca;
        $busca->setFullPath(DIR_JSON);
        $busca->setArrJsonFiles($arrJsonFiles);
        $instProduto = new Produto();
        $ret = $instProduto->obterMelhorado($filtro, null, $rs);

        if($rs && pg_num_rows($rs) > 0)
        {
            for($i=0; $rs_row = pg_fetch_array($rs); $i++)
            {
                if(!empty($rs_row['ogp_nome']))
                {
                    $produto                                    = new stdClass();
                    $produto->tipo                              = "games";
                    $produto->id                                = $rs_row['ogp_id'];
                    $produto->nome                              = htmlentities($rs_row['ogp_nome']);
                    $produto->busca                             = htmlentities(strip_tags(Util::cleanStr2($rs_row['ogp_nome']." | ".$rs_row['opr_nome_loja']))); //corrigir traducao dew caracter q nao ta funfando
                    $produto->imagem                            = $rs_row['ogp_nome_imagem'];
                    $produto->operadora                         = $rs_row['opr_nome_loja'];
                    $produto->filtro['ogp_inibi_lojas_online']  = $rs_row['ogp_inibi_lojas_online'];

                    $arrTemp['games'][] = $produto;

                    unset($produto);
                }
            }
        }
        $busca->setProduto($arrTemp);
        unset($arrTemp);

        ////para voltar com os produtos b2c, descomente o bloco acima
        $busca->geraJson();
        
        /*
            Fim do bloco para atualizar listagem de produtos
         */
    }
?>
    <link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script language="javascript">
        $(document).ready(function(){

            $("tbody").sortable({
                appendTo: "parent",
                helper: "clone"
            }).disableSelection();

        });

        function reordena(){
            $(".produto").each(function(i){
               $(this).children().val(i);
            });

            $("#editaProduto").submit();
        }
			
        function GP_popupAlertMsg(msg) 
        { //v1.0
            document.MM_returnValue = alert(msg);
        }

        function GP_popupConfirmMsg(msg) 
        { //v1.0
            document.MM_returnValue = confirm(msg);
        }
</script>
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
            <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
            <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
        </ol>
    </div>
<?php
    if(!empty($idErrors)){
?>
        <div class="col-md-12">
            <div class="alert alert-danger" role="alert">
                <span class="glyphicon glyphicon-exclamation-sign t0" aria-hidden="true"></span>
                <span class="sr-only">Error:</span>
                Erro ao atualizas os seguinte produtos: <?php print implode(", ",$idErrors); ?>
                <div class="clearfix"></div>
            </div>
        </div>
<?php
    }
        
    $rs = pg_query("select tb.*,opr.opr_nome from tb_dist_operadora_games_produto tb inner join operadoras opr on tb.ogp_opr_codigo = opr.opr_codigo where (ogp_ativo = 1 or ogp_mostra_integracao_gamer = 1) order by tb.ogp_ordem");
						
    if(pg_num_rows($rs) > 0) 
    {
?>
<div class="col-md-12">
    <button type="button" class="btn btn-sm btn-success pull-right" onclick="reordena();">Reordenar</button>
</div>
<div class="col-md-12">
    <form id="editaProduto" method="post">
        <table class="table top10 table-bordered fontsize-p txt-preto">
            <thead class="">
                <tr>
                    <th class="text-center">
                        Ordem
                    </th>
                    <th class="text-center">
                        C�d.
                    </th>
                    <th class="text-center">
                        Data de Inclus�o
                    </th>
                    <th class="text-center">
                        Status
                    </th>
                    <th class="text-center">
                        Nome
                    </th>
                    <th class="text-center">
                        Operadora
                    </th>
                </tr>
            </thead> 
            <tbody>
<?php
            while($rs_produtos_row = pg_fetch_array($rs))
            {
                $status = ($rs_produtos_row['ogp_ativo'] == 1)?"Ativo":"Inativo";
?>
                <tr class="trListagem">
                    <td>
                        <strong><?php echo $rs_produtos_row['ogp_ordem'] + 1 ?></strong>
                    </td>
                    <td class="produto" width="50" align="center">
                        <a style="text-decoration:none" href="com_produto_detalhe.php?produto_id=<?php echo $rs_produtos_row['ogp_id'] ?>&voltar=ord"><?php echo $rs_produtos_row['ogp_id'] ?></a>
                        <input type="hidden" name="produtos[<?php echo $rs_produtos_row['ogp_id'] ?>]" value="">
                    </td>
                    <td>
                        <?php echo formata_data($rs_produtos_row['ogp_data_inclusao'],0) ?>
                    </td>
                    <td>
                        <?php echo $status ?>
                    </td>
                    <td class="texto">
                        <a style="text-decoration:none" href="com_produto_detalhe.php?produto_id=<?php echo $rs_produtos_row['ogp_id'] ?>&voltar=ord"><?php echo $rs_produtos_row['ogp_nome'] ?></a>
                    </td>
                    <td class="texto">
                        <?php echo $rs_produtos_row['opr_nome'] ?>
                    </td>
                </tr>
<?php 
            }
?>
                <tr> 
                    <td colspan="6"> 
                       Quantidade de registros ativos: <strong><?php echo pg_num_rows($rs) ?></strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<?php
    }

 require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</html>