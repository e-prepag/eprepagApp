<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
set_time_limit(3600);
$time_start = getmicrotime();

if(!$ncamp)    $ncamp       = 'bbc_data_inclusao';
if(!$inicial)  $inicial     = 0;
if(!$range)    $range       = 1;
if(!$ordem)    $ordem       = 0;
if($BtnSearch=="Buscar") {
        $inicial     = 0;
        $range       = 1;
        $total_table = 0;
}

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 100;    //qtde_reg_tela;
$range_qtde   = $qtde_range_tela;

$varsel = "&BtnSearch=1";
$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_cnpj=$tf_u_cnpj&tf_u_responsavel=$tf_u_responsavel&tf_u_email=$tf_u_email";
$varsel .= "&tf_c_valor=$tf_c_valor&tf_c_repasse=$tf_c_repasse&tf_cor_periodo_ini=$tf_cor_periodo_ini&tf_cor_periodo_fim=$tf_cor_periodo_fim&tf_data_inclusao_ini=$tf_data_inclusao_ini&tf_data_inclusao_fim=$tf_data_inclusao_fim";


if(isset($BtnSearch)){

        //Validacao
        $msg = "";

        if($msg == ""){
            if($tf_u_codigo){
                if(!is_csv_numeric_global($tf_u_codigo, 1)) {
                    $msg = "Código do usuário deve ser numérico.\n";
                }
            }
        }
        if($msg == ""){
            if($tf_cor_periodo_ini || $tf_cor_periodo_fim){
                if(verifica_data($tf_cor_periodo_ini) == 0)    {
                    $msg = "A data de inicio do período de corte é inválida.\n";
                } 
                if(verifica_data($tf_cor_periodo_fim) == 0)    {
                    $msg = "A data de fim do período de corte é inválida.\n";
                } 
                
                $p_ini = date('Y-m-d', strtotime(str_replace('/', '-', $tf_cor_periodo_ini)));
                $p_fim = date('Y-m-d', strtotime(str_replace('/', '-', $tf_cor_periodo_fim)));
                if(strtotime($p_ini) > strtotime($p_fim)){
                    $msg = "A data inicial do período de corte deve ser anterior a data final do período de corte.\n";
                }
            }
        }
        if($msg == ""){    
            if($tf_data_inclusao_ini || $tf_data_inclusao_fim){
                if(verifica_data($tf_data_inclusao_ini) == 0)    {
                    $msg = "A data de inclusão inicial da venda é inválida.\n";
                } 
                if(verifica_data($tf_data_inclusao_fim) == 0)    {
                    $msg = "A data de inclusão final da venda é inválida.\n";
                } 
                
                $d_ini = date('Y-m-d', strtotime(str_replace('/', '-', $tf_data_inclusao_ini)));
                $d_fim = date('Y-m-d', strtotime(str_replace('/', '-', $tf_data_inclusao_fim)));
                if(strtotime($d_ini) > strtotime($d_fim)){
                    $msg = "A data de inclusão inicial deve ser anterior a data de inclusão final.\n";
                }
            }
        }
        
        if($msg == ""){
            if($tf_c_repasse){
                if(!is_numeric(str_replace (',', '.', $tf_c_repasse)))    $msg = "Valor de Repasse deve ser numérico.\n";
            }  
        }
            
        //Valor
        if($msg == ""){
            if($tf_c_valor){
                if(!is_numeric(str_replace (',', '.', $tf_c_valor)))    $msg = "Valor deve ser numérico.\n";
            }  
        }


        if($msg == ""){
            if($tf_u_cnpj){
                if(!is_numeric($tf_u_cnpj)) $msg = "O CNPJ/CPF deve ter somente números.\n";
            }
        }

        if($msg == ""){

            include $raiz_do_projeto . "includes/pdv/inc_pesquisa_boletos_PDV_POS_sql.php";

            $total_table = pg_num_rows($rs_boleto_pos);

            //Ordem
            $sql .= " order by ".$ncamp;
            if($ordem == 1){
                $sql .= " asc ";
                $img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
            } else {
                $sql .= " desc ";
                $img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
            }

            $sql .= " limit ".$max;
            $sql .= " offset ".$inicial;
            if($total_table == 0) {
                $msg = "Nenhum boleto encontrado.\n";
            } else {
                $rs_boleto_pos = SQLexecuteQuery($sql);
                if(!$rs_boleto_pos){
                    $msg = "Problema ao recuperar os dados.\n";
                }

                if($max + $inicial > $total_table)
                    $reg_ate = $total_table;
                else
                    $reg_ate = $max + $inicial;
            }
        }    
    }

?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<?php 
    if($msg != ""){
?>
    <div class="col-md-12">
        <div class="alert alert-info" role="alert"><?php echo str_replace("\n", "<br>", $msg) ?></div>
    </div>
<?php 
    } elseif($msgAcao != "")
    {
?>
    <div class="col-md-12">
        <div class="alert alert-info" role="alert"><?php echo str_replace("\n", "<br>", $msgAcao) ?></div>
    </div>
<?php 
    } 
?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_data_inclusao_ini','tf_data_inclusao_fim',optDate);
        setDateInterval('tf_cor_periodo_ini', 'tf_cor_periodo_fim',optDate);
        
        
    });
</script>

<div class="col-md-12 fontsize-pp">
    <form name="form1" method="post" action="com_pesquisa_boletos_PDV_POS.php">
    <div class="col-md-12">
        <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-success btn-sm pull-right">
    </div>
    <div class="clearfix"></div>
    <div class="top10 panel panel-default">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Dados do Usuário/Empresa</h3>
          </div>
            <div class="panel-body">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tf_u_codigo">Cód. Usuário</label>
                        <input type="text" class="input-sm form-control" id="tf_u_codigo" name="tf_u_codigo" value="<?php echo $tf_u_codigo ?>">
                    </div>
                    <div class="form-group">
                        <label for="tf_u_responsavel" class="w100">Nome Responsável</label>
                        <input name="tf_u_responsavel" id="tf_u_responsavel" type="text" class="input-sm form-control" value="<?php echo $tf_u_responsavel ?>" size="25" maxlength="100">
                    </div>  
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tf_u_email" class="w100">Email</label>
                        <input name="tf_u_email" id="tf_u_email" type="text" class="input-sm form-control" value="<?php echo $tf_u_email ?>" size="25" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="tf_u_cnpj" class="w100">CNPJ/CPF</label>
                        <input name="tf_u_cnpj" id="" type="text" class="input-sm form-control" value="<?php echo $tf_u_cnpj ?>" size="25" maxlength="14">
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <div class="top10 panel panel-default">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Dados do Boleto</h3>
          </div>
            <div class="panel-body">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tf_c_valor" class="w100">Valor</label>
                        <input name="tf_c_valor" id="tf_u_razao_social" type="text" class="input-sm form-control" value="<?php echo $tf_c_valor ?>" size="25" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="tf_cor_periodo_ini" class="w100">Período Corte</label>
                        <input name="tf_cor_periodo_ini" type="text" class="input-sm form-control w150  dislineblock" id="tf_cor_periodo_ini" value="<?php echo $tf_cor_periodo_ini ?>" size="9" maxlength="10">
                        <span class="text-info p-3  dislineblock">a</span>
                        <input name="tf_cor_periodo_fim" type="text" class="input-sm form-control w150  dislineblock left5" id="tf_cor_periodo_fim" value="<?php echo $tf_cor_periodo_fim ?>" size="9" maxlength="10">
                        <span class="text-info p-3  dislineblock">(99/99/9999)</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tf_c_repasse" class="w100">Valor Repasse</label>
                        <input name="tf_c_repasse" id="tf_c_repasse" type="text" class="input-sm form-control" value="<?php echo $tf_c_repasse ?>" size="25" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="tf_cor_periodo_ini" class="w100">Data Inclusão</label>
                        <input name="tf_data_inclusao_ini" type="text" class="input-sm form-control w150  dislineblock" id="tf_data_inclusao_ini" value="<?php echo $tf_data_inclusao_ini ?>" size="9" maxlength="10">
                        <span class="text-info p-3  dislineblock">a</span>
                        <input name="tf_data_inclusao_fim" type="text" class="input-sm form-control w150  dislineblock left5" id="tf_data_inclusao_fim" value="<?php echo $tf_data_inclusao_fim ?>" size="9" maxlength="10">
                        <span class="text-info p-3  dislineblock">(99/99/9999)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-success btn-sm pull-right">
    </div>
<?php 
    if($msg != "")
    {
?>
    <div class="col-md-12 top10 alert alert-danger" role="alert">
        <strong><?php echo $msg;?></strong>
    </div>
<?php
    }
?>
    </form
    
    <?php
    if($total_table > 0) 
    {
        $ordem = ($ordem == 1)?2:1; 
?>
    <div id="focusAfterSubmited"></div>
    <script>
        $('html,body').animate({
        scrollTop: $("#focusAfterSubmited").offset().top},
        'slow');
    </script>
    <div class="col-md-12">
        <blockquote>
            <p>Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></p>
        </blockquote>
    </div>

</div></div>
    <div class="bg-branco">
    <table class="table bg-branco table-bordered txt-preto text-center fontsize-pp">
        <thead>
            <tr class="bg-cinza-claro text-center">
                <td align="center" title="Ordenar por Cód.Usuário">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>">Cód. Usuário</a>
                    <?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                </td>
                
                <td align="center" title="Ordenar por CNPJ/CPF">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cnpj&inicial=".$inicial.$varsel ?>">CNPJ/CPF</a>
                    <?php if($ncamp == 'ug_cnpj') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>

                <td align="center" title="Ordenar por Email">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_email&inicial=".$inicial.$varsel ?>">Email</a>
                    <?php if($ncamp == 'ug_email') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                
                <td align="center" title="Ordenar por Responsável">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_responsavel&inicial=".$inicial.$varsel ?>">Responsável</a>
                    <?php if($ncamp == 'ug_responsavel') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                
                <td align="center" title="Ordenar por Cód. Boleto">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bbc_boleto_codigo&inicial=".$inicial.$varsel ?>">Cód. Boleto</a>
                    <?php if($ncamp == 'bbc_boleto_codigo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                
                <td align="center" title="Ordenar por Data de Inclusão">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bbc_data_inclusao&inicial=".$inicial.$varsel ?>">Data Inclusão</a>
                    <?php if($ncamp == 'bbc_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                
                <td align="center" title="Ordenar por Valor">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_venda_bruta&inicial=".$inicial.$varsel ?>">Valor</a>
                    <?php if($ncamp == 'cor_venda_bruta') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                
                <td align="center" title="Ordenar por Valor Repasse">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_venda_liquida&inicial=".$inicial.$varsel ?>">Valor Repasse</a>
                    <?php if($ncamp == 'cor_venda_liquida') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                
                <td align="center">
                    <strong>
                        Período Corte
                    </strong>
                </td>
                
                <td align="center">
                    <strong>
                        Gerar Boleto Sem Resgistrar
                    </strong>
                </td>

        </thead>
        <tbody>
<?php 
        while($rs_usuario_row = pg_fetch_array($rs_boleto_pos))
        {
?>            
            <tr class="trListagem">
                <td align="center"><a style="text-decoration:none" title="Ver Usuário" href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_usuario_row['ug_id'] ?>"><?php echo $rs_usuario_row['ug_id'] ?></a></td>
                
                <td align="center"><?php if($rs_usuario_row['ug_cnpj']) echo $rs_usuario_row['ug_cnpj']; else  echo $rs_usuario_row['ug_cpf'];?></td>
                
                <td align="center"><?php  echo $rs_usuario_row['ug_email'] ?></td>
                
                <td><?php if($rs_usuario_row['ug_responsavel']) echo $rs_usuario_row['ug_responsavel']; else echo $rs_usuario_row['ug_nome_fantasia']  ?></td>
                
                <td align="center"><?php  echo $rs_usuario_row['bbc_boleto_codigo'] ?></td>
            
                <td><?php echo formata_data_ts($rs_usuario_row['bbc_data_inclusao'],0, true,true); ?></td>

                <td ><?php echo "R$". number_format($rs_usuario_row['cor_venda_bruta'], 2, ',','.'); ?></td>
                
                <td ><?php echo "R$". number_format($rs_usuario_row['cor_venda_liquida'], 2, ',','.'); ?></td>
                
                <td><?php echo formata_data($rs_usuario_row['cor_periodo_ini'],0) ." a ". formata_data($rs_usuario_row['cor_periodo_fim'],0); ?></td>
                                
                <td align="center">
                <?php
                    $ug_id = $rs_usuario_row['ug_id'] ;
                    $bbc_boleto_cod = $rs_usuario_row['bbc_boleto_codigo'];
                ?>
                    <a href="javascript:void(0);" title="Gerar sem Registrar" onClick="dadosBoleto('<?php echo $ug_id ?>','<?php echo $bbc_boleto_cod ?>');">Gerar Boleto</a>
                </td>
            </tr>
<?php     
        }
?>
            <tr>
               <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></td>
            </tr>
<?php
        paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
?>
        </tbody>
        </table>
    </div>
<div><div>
<?php
    }

    require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>

<script>
    function dadosBoleto(ug_id, bbc_boleto_codigo) {

        var bbc_boleto_codigo    = eval(bbc_boleto_codigo);
        var ug_id        = eval(ug_id);

        window.open ("corte_boleto_bradesco_sem_registro.php?ug_id="+ug_id+'&bbc_boleto_codigo='+bbc_boleto_codigo);
    }
</script>