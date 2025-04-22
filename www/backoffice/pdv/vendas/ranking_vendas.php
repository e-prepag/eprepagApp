<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

if(b_IsBKOUsuarioRankingLAN()) {
    
	set_time_limit ( 6000 ) ;
	$time_start = getmicrotime();
	
	if( ! (($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) || ($tf_v_data_concilia_ini && $tf_v_data_concilia_fim) || ($tf_v_data_cancelamento_ini && $tf_v_data_cancelamento_fim) ) ) {
		$tf_v_data_inclusao_ini = date("d/m/Y");
		$tf_v_data_inclusao_fim = date("d/m/Y");
	}

	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//Venda
		//------------------------------------------------------------------
		//codigo
                if($tf_v_codigo){
                        if(!is_csv_numeric($tf_v_codigo)) {
                                $msg = "Código da venda deve ser numérico ou lista de números separada por vírgulas.\n";
                        }
                }

		//Data Inclusão
		if($msg == "")
			if($tf_v_data_inclusao_ini || $tf_v_data_inclusao_fim){
				if(verifica_data($tf_v_data_inclusao_ini) == 0)	$msg = "A data inicial da venda é inválida.\n";
				if(verifica_data($tf_v_data_inclusao_fim) == 0)	$msg = "A data final da venda é inválida.\n";
			}
		//Busca vendas
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){
                    
                        //Inicializando String SQL
                        $sql = "";
                        
                        if($tf_u_so_depositos=="1") {
                                $sql .= "
                        select 
                                ve_nome, 
                                ug_id,
                                ve_cidade, 
                                ve_estado, 
                                min(primeira_venda) as primeira_venda , 
                                max(ultima_venda) as ultima_venda, 
                                sum(vendas) as vendas, 
                                sum(qtde_itens) as qtde_itens 	 
                        from (
                        ";
                        } //end if($tf_u_so_depositos=="1")
			$sql  .= "select 
                                        (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia||' ('||ug.ug_tipo_cadastro||')'  WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome||' ('||ug.ug_tipo_cadastro||')'  END) as ve_nome,
                                        ug.ug_id,
                                        ug.ug_cidade as ve_cidade, 
                                        ug.ug_estado as ve_estado, 
                                        min(vg.vg_data_inclusao) as primeira_venda , 
                                        max(vg.vg_data_inclusao) as ultima_venda, ";

			if($tf_u_so_depositos=="1") {
				$sql .= "
                                        case when (substr(vg_pagto_num_docto, 1, 1)='4') then bol_valor else pag_valor end as vendas, 
                                        1 as qtde_itens ";

			} else {
				$sql .= "		
                                        sum(vgm.vgm_valor * vgm.vgm_qtde) as vendas, 
                                        sum(vgm.vgm_qtde) as qtde_itens ";
			}
			$sql .= "	 
                                from tb_dist_venda_games vg ";
			if($tf_u_so_depositos=="1") {
				$sql .= "
                                    left outer join ( 
                                                    select idvenda as pag_vg_id, (total/100-taxas) as pag_valor, iforma as pag_tipo 
                                                    from tb_pag_compras pg 
                                                    where pg.status=3 
                                                          and (pg.tipo_cliente = 'LR') 
						    ) pag on pag.pag_vg_id = vg.vg_id
                                    left outer join ( 
                                                    select bol_venda_games_id as bol_vg_id, bol_valor as bol_valor, 'B' || substr(bol_documento, 1, 1) as bol_tipo 
                                                    from boletos_pendentes bol 
                                                    where bol_aprovado = 1 
                                                        and (substr(bol_documento, 1, 1)='4') 	
                                                    ) bol on bol.bol_vg_id = vg.vg_id 	
						";
			} 
                        else {
                                $sql .= " 
                                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id ";
                        }
                        $sql .= "
                                    inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id ";
			$sql .= " 
                            where vg.vg_ultimo_status='5' ";

			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim)  $sql .= " and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59' ";
			if($tf_u_estado)                                        $sql .= " and  trim(both ' ' from ug.ug_estado) = '".strtoupper($tf_u_estado)."' ";
                        if($tf_u_so_depositos=="1")     			$sql .= " and ( (not (bol_tipo is null)) or (not (pag_tipo is null)  ) ) ";
			
			$sql .= " 
                            group by ug.ug_nome_fantasia, ug.ug_nome, ug.ug_cidade, ug.ug_estado, ug.ug_tipo_cadastro,ug_id";
                        if($tf_u_so_depositos=="1") {
                                $sql .= ",vg.vg_pagto_num_docto,bol_vg_id,pag_vg_id,bol_valor,pag_valor 
                        ) total_deposito
                        group by ve_nome, ve_cidade, ve_estado,ug_id
                        ";
                        } //end if($tf_u_so_depositos=="1")
			$sql .= " 
                            order by  vendas DESC, ve_nome;";

                        //die($sql);
			$rs_venda = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_venda);


                        
                        
		} //end if($msg == "")

    } //end if(isset($BtnSearch))
        
        
ob_end_flush();
require_once "/www/includes/bourls.php";
?>
    <link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
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
<form name="form1" method="post" class="fontsize-p txt-preto">
    <table class="table">
        <tr bgcolor="#F5F5FB"> 
                <td class="texto">Tipo de pedido</font></td>
                <td class="texto">
                    <select name="tf_u_so_depositos" class="form2">
                            <option value="" <?php if($tf_u_so_depositos != "1") echo "selected" ?>>Venda de PINs</option>
                            <option value="1" <?php if($tf_u_so_depositos == "1") echo "selected" ?>>Depósito em Saldo (PDV Pré)</option>
                    </select>
                </td>
                <td class="texto">Data Inclusão</font></td>
                <td class="texto">
                    <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
                    a 
                    <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
                </td>
                <td class="texto">Estado</td>
                <td class="texto">
                    <select name="tf_u_estado" id="tf_u_estado" class="form2" class="field_dados">
                            <option value="" <?php if($tf_u_estado == "") echo "selected" ?>>Selecione</option>
                    <?php for($i=0; $i < count($SIGLA_ESTADOS); $i++){ ?>
                            <option value="<?php echo $SIGLA_ESTADOS[$i] ?>" <?php if($tf_u_estado == $SIGLA_ESTADOS[$i]) echo "selected"; ?>><?php echo $SIGLA_ESTADOS[$i] ?></option>
                    <?php } ?>
                    </select>
                </td>
        </tr>
</table>
<br>
<table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
</table>
        
</form>        


<?php 
    if($total_table > 0) { 
?>
<table class="table fontsize-pp txt-preto">
    <tr bgcolor="#00008C"> 
        <td height="11" colspan="8" bgcolor="#FFFFFF" class="texto"> 
                <strong>Total de LANs Consideradas no período: <?php echo $total_table; ?> => Volume Total no período R$ <span id="txt_totais"></span></strong>
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
                <strong><font class="texto">Cidade</strong>
        </td>
        <td align="center">
                <strong><font class="texto">UF</strong>
        </td>
        <td align="center">
                <strong><font class="texto">Primeira Operação no Período Selecionado</strong>
        </td>
        <td align="center">
                <strong><font class="texto">Última Operação no Período Selecionado</strong>
        </td>
        <td align="center">
                <strong><font class="texto">Quantidade de Transações</strong>
        </td>
        <td align="center">
                <strong><font class="texto">Valor Total das Transações</strong>
        </td>
    </tr>
<?php
            $totalGeral_valor = 0;
            while($rs_venda_row = pg_fetch_array($rs_venda)){
                $totalGeral_valor += $rs_venda_row['vendas'];
?>
    <tr bgcolor="#CCFFFF"> 
        <td nowrap valign="top" class="texto" align="right">
            &nbsp;<?php echo $rs_venda_row['ug_id']; ?>&nbsp;
        </td>
        <td nowrap valign="top" class="texto" align="left">
            <?php echo $rs_venda_row['ve_nome']; ?>
        </td>
        <td nowrap valign="top" class="texto" align="left">
            <?php echo $rs_venda_row['ve_cidade']; ?>
        </td>
        <td nowrap valign="top" class="texto" align="center">
            <?php echo $rs_venda_row['ve_estado']; ?>
        </td>
        <td nowrap valign="top" class="texto" align="center">
            <?php echo formata_data_ts($rs_venda_row['primeira_venda'],0, true,true); ?>
        </td>
        <td nowrap valign="top" class="texto" align="center">
            <?php echo formata_data_ts($rs_venda_row['ultima_venda'],0, true,true); ?>
        </td>
        <td nowrap valign="top" class="texto" align="right">
            <?php echo number_format($rs_venda_row['qtde_itens'], 0, ',','.'); ?>
        </td>
        <td nowrap valign="top" class="texto" align="right">
            <?php echo number_format($rs_venda_row['vendas'], 2, ',','.'); ?>
        </td>
    <tr> 
<?php
            } //end while
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
  document.getElementById('txt_totais').innerHTML = '<?php echo number_format($totalGeral_valor, 2, ',', '.'); ?>';
</script>
          <?php  
          
        }//end if($total_table > 0)
}//end if(b_IsBKOUsuarioRankingLAN())

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>