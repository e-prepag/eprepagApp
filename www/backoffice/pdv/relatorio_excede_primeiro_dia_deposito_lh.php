<?php 
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto.'includes/gamer/functions.php';
require_once $raiz_do_projeto.'includes/pdv/constantes.php';
require_once $raiz_do_projeto.'includes/pdv/functions_vendaGames.php';
require_once $raiz_do_projeto."class/business/VendasLanHouseBO.class.php";

if(!isset($limite_diario)) 
    $limite_diario = $GLOBALS['RISCO_LANS_PRE_TOTAL_DIARIO'];

if(b_IsBKOUsuarioAdminGestaodeRisco()) {
    
	set_time_limit ( 6000 ) ;
	$time_start = getmicrotime();
	
	if( ! ((isset($tf_v_data_inclusao_ini) && isset($tf_v_data_inclusao_fim)) || (isset($tf_v_data_concilia_ini) && isset($tf_v_data_concilia_fim)) || (isset($tf_v_data_cancelamento_ini) && isset($tf_v_data_cancelamento_fim)) ) ) {
		$tf_v_data_inclusao_ini = date("d/m/Y");
		$tf_v_data_inclusao_fim = date("d/m/Y");
	}

	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//Data Inclusão
		if($msg == "")
			if($tf_v_data_inclusao_ini || $tf_v_data_inclusao_fim){
				if(verifica_data($tf_v_data_inclusao_ini) == 0)	$msg = "A data inicial da venda é inválida.\n";
				if(verifica_data($tf_v_data_inclusao_fim) == 0)	$msg = "A data final da venda é inválida.\n";
			}
		//Busca vendas
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){
                    $vendasBO = new VendasLanHouseBO;
                    $vendasExcedentes = $vendasBO->getPrimeiraVenda(formata_data($tf_v_data_inclusao_ini, 1), formata_data($tf_v_data_inclusao_fim, 1));
                   
                } //end if($msg == "")
                else{
                    echo $msg;
                }
    } //end if(isset($BtnSearch))
        
        
ob_end_flush();
     
?>
    <link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="/js/global.js"></script>
    <script language="javascript">
        $(function(){
           var optDate = new Object();
                optDate.interval = 10000;

            setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        });
    </script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<form name="form1" method="post" action="">
<table class="table fontsize-p txt-preto">
    <tr bgcolor="#F5F5FB"> 
        <td class="texto">Data Inicial</td>
        <td class="texto">
            <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
            até 
            <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
        </td>
        <td class="texto">Limite Diário Considerado</td>
        <td class="texto">
            <input name="limite_diario" type="text" class="form" id="limite_diario" value="<?php echo $limite_diario ?>" size="9" maxlength="10"> *OBS: O Limite Diário Atual Utilizado pelo Sistema é: R$ <?php echo number_format($GLOBALS['RISCO_LANS_PRE_TOTAL_DIARIO'], 2, ",", "."); ?>
        </td>
    </tr>
    <tr bgcolor="#F5F5FB"> 
        <td align="right" colspan="4"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
    </tr>
    <?php if(isset($msg) && $msg != ""){?><tr class="texto"><td colspan="4" align="center"><font color="#FF0000"><?php echo $msg?></font></td></tr><?php } ?>
</table>
</form>
<?php 
    if(!empty($vendasExcedentes)) {
?>
<table class="table fontsize-p txt-preto">
    <tr bgcolor="#00008C"> 
        <td height="11" colspan="4" bgcolor="#FFFFFF" class="texto"> 
                <strong>Total de LANs Consideradas no período: <span id="txt_totais"></span></strong>
        </td>
    </tr>
    <tr bgcolor="#ECE9D8"> 
        <td align="center">
                <strong><font class="texto">ID</strong>
        </td>
        <td align="center">
                <strong><font class="texto">Nome</strong>
        </td>
        <td align="center">
                <strong><font class="texto">Valor Total</strong>
        </td>
        <td align="center">
            <strong><font class="texto">Classificação</strong>
        </td>
        <td align="center">
                <strong><font class="texto">Data</strong>
        </td>
        <td align="center">
                <strong><font class="texto">Qtde. Tipos Pagamentos</strong>
        </td>
    </tr>
<?php
            
    $cont = 0;    
    foreach($vendasExcedentes as $ind => $venda){

        $tipouser = $venda['lan_house']->getVip();
        $valor = $venda['venda']->getValor();
        
        if(isset($tipouser) && $tipouser == 1)
            $status = "VIP"; //$limiteDiario = $GLOBALS['RISCO_LANS_PRE_VIP_TOTAL_DIARIO'];
        else if(isset($tipouser) && $tipouser == 2)
            $status = "MASTER"; //$limiteDiario = $GLOBALS['RISCO_LANS_PRE_MASTER_TOTAL_DIARIO'];
        else
            $status = "NORMAL";//$limiteDiario = $GLOBALS['RISCO_LANS_PRE_TOTAL_DIARIO'];
          
        if($valor <= $limite_diario)
            continue;
        
        $cont++;
?>
    <tr bgcolor="#CCFFFF"> 
        <td nowrap valign="top" class="texto" align="center">
            <?php echo $venda['venda']->getCodUsuario() ?>
        </td>
        <td nowrap valign="top" class="texto" align="center">
            <?php echo $venda['lan_house']->getNome(); ?>
        </td>
        <td nowrap valign="top" class="texto" align="right">
            <?php echo number_format($valor, 2, ',', '.'); ?>
        </td>
        <td nowrap valign="top" class="texto" align="right">
            <?php echo $status;//number_format($limiteDiario, 2, ',', '.'); ?>
        </td>
        <td nowrap valign="top" class="texto" align="right">
            <?php echo $venda['venda']->getDataInclusao();?>
        </td>
        <td nowrap valign="top" class="texto" align="right">
            <?php echo $venda['venda']->getTipoPagamento();?>
        </td>
    <tr> 
<?php
            }//end foreach
?>                        
    <tr> 
        <td colspan="8" bgcolor="#FFFFFF" class="texto">
                &nbsp;
        </td>
    </tr>
    <tr> 
        <td colspan="8" bgcolor="#FFFFFF" class="texto">
                <?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit; ?>
        </td>
    </tr>
</table>
<script language="JavaScript">
  document.getElementById('txt_totais').innerHTML = '<?php echo $cont; ?>';
</script>
          <?php  
          
        }//end if($total_table > 0)
}//end if(b_IsBKOUsuarioAdminGestaodeRisco())

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>