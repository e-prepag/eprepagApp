<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."includes/gamer/functions_vendaGames.php";
require_once "/www/includes/bourls.php";

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

	set_time_limit ( 30000 ) ;

	$n_emails_in_bloco = 15;
	$n_delay = 0;	// 20

	// disable output buffer
	ob_end_flush();

	$time_start = getmicrotime();

	if(!$ncamp)    $ncamp       = 'vg_id';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	//echo "range: $range<br>";
	if(!$ordem)    $ordem       = 0;

	// Se for enviar emails tem que listar as vendas 
	if($BtnProcessaEmail){
		$BtnSearch=1;
	}
	//	if($BtnSearch) $inicial     = 0;
	//	if($BtnSearch) $range       = 1;
	if($BtnSearch) $total_table = 0;

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 50; //$qtde_reg_tela;
	$range_qtde   = 50; //$qtde_range_tela;
//	echo "qtde_reg_tela: ".$qtde_reg_tela.", qtde_range_tela: ".$qtde_range_tela."<br>";

	$varsel = "&BtnSearch=1&tf_v_codigo=$tf_v_codigo&tf_v_status=$tf_v_status";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	$varsel .= "&tf_v_data_concilia_ini=$tf_v_data_concilia_ini&tf_v_data_concilia_fim=$tf_v_data_concilia_fim";
	$varsel .= "&tf_v_data_cancelamento_ini=$tf_v_data_cancelamento_ini&tf_v_data_cancelamento_fim=$tf_v_data_cancelamento_fim";
	$varsel .= "&tf_v_concilia=$tf_v_concilia&tf_d_forma_pagto=$tf_d_forma_pagto&tf_d_banco=$tf_d_banco&tf_d_local=$tf_d_local";
	$varsel .= "&tf_d_data_ini=$tf_d_data_ini&tf_d_data_fim=$tf_d_data_fim";
	$varsel .= "&tf_d_data_inclusao_ini=$tf_d_data_inclusao_ini&tf_d_data_inclusao_fim=$tf_d_data_inclusao_fim";
	$varsel .= "&tf_d_valor_pago=$tf_d_valor_pago&tf_d_num_docto=$tf_d_num_docto";
	$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_nome=$tf_u_nome&tf_u_email=$tf_u_email&tf_u_cpf=$tf_u_cpf";
	$varsel .= "&tf_v_valor=$tf_v_valor&tf_v_qtde_produtos=$tf_v_qtde_produtos&tf_v_qtde_itens=$tf_v_qtde_itens";
	$varsel .= "&tf_v_dep_codigo=$tf_v_dep_codigo&tf_v_bol_codigo=$tf_v_bol_codigo&tf_v_origem=$tf_v_origem";
	//Operadoras		
	$varsel .= "&tf_opr_codigo=$tf_opr_codigo&tf_v_origem=$tf_v_origem";
	

	//Produtos
	if ($tf_produto && is_array($tf_produto))
		if (count($tf_produto) == 1)
			$tf_produto = $tf_produto[0];
		else
			$tf_produto = implode("|",$tf_produto);
	$varsel .= "&tf_produto=$tf_produto";
	if ($tf_produto && $tf_produto != "")
		$tf_produto = explode("|",$tf_produto);
	
	//Valores
	if ($tf_pins && is_array($tf_pins))
		if (count($tf_pins) == 1)
			$tf_pins = $tf_pins[0];
		else
			$tf_pins = implode("|",$tf_pins);
	$varsel .= "&tf_pins=$tf_pins";
	if ($tf_pins && $tf_pins != "")
		$tf_pins = explode("|",$tf_pins);	

	if(isset($BtnSearch))
	{	
		//Validacao
		$msg = "";

		//Venda
		//------------------------------------------------------------------
		//codigo
		if($msg == "")
			if($tf_v_codigo)
			{
				if(empty($tf_v_codigo))
					$msg = "Código da venda deve ter conteudo válido.".PHP_EOL;
			}
		//Data
		if($msg == "")
			if($tf_v_data_inclusao_ini || $tf_v_data_inclusao_fim)
			{
				if(verifica_data($tf_v_data_inclusao_ini) == 0)	$msg = "A data inicial da venda é inválida.\n";
				if(verifica_data($tf_v_data_inclusao_fim) == 0)	$msg = "A data final da venda é inválida.\n";
			}
		//Data Conciliacao
		if($msg == "")
			if($tf_v_data_concilia_ini || $tf_v_data_concilia_fim)
			{
				if(!is_DateTime($tf_v_data_concilia_ini))	$msg = "A data inicial da conciliação da venda é inválida.\n";
				if(!is_DateTime($tf_v_data_concilia_fim))	$msg = "A data final da conciliação da venda é inválida.\n";
			}
		//Data Cancelamento
		if($msg == "")
			if($tf_v_data_cancelamento_ini || $tf_v_data_cancelamento_fim)
			{
				if(verifica_data($tf_v_data_cancelamento_ini) == 0)	$msg = "A data inicial do cancelamento é inválida.\n";
				if(verifica_data($tf_v_data_cancelamento_fim) == 0)	$msg = "A data final do cancelamento é inválida.\n";
			}
		//valor
		if($msg == "")
			if($tf_v_valor)
			{
				if(!is_moeda($tf_v_valor))
					$msg = "Valor da venda é inválido.\n";
			}
		//qtde produtos
		if($msg == "")
			if($tf_v_qtde_produtos)
			{
				if(!is_numeric($tf_v_qtde_produtos))
					$msg = "Qtde Produtos da venda deve ser numérico.\n";
			}
		//qtde itens
		if($msg == "")
			if($tf_v_qtde_itens)
			{
				if(!is_numeric($tf_v_qtde_itens))
					$msg = "Qtde Itens da venda deve ser numérico.\n";
			}

		//tf_v_dep_codigo
		if($msg == "")
			if($tf_v_dep_codigo)
			{
				if(!is_numeric($tf_v_dep_codigo))
					$msg = "Código do depósito deve ser numérico.\n";
			}

		//tf_v_bol_codigo
		if($msg == "")
			if($tf_v_bol_codigo)
			{
				if(!is_numeric($tf_v_bol_codigo))
					$msg = "Código do boleto deve ser numérico.\n";
			}

		//Dados do Pagamento
		//------------------------------------------------------------------
		//Data
		if($msg == "")
			if($tf_d_data_ini || $tf_d_data_fim)
			{
				if(verifica_data($tf_d_data_ini) == 0)	$msg = "A data inicial dos dados do pagamento é inválida.\n";
				if(verifica_data($tf_d_data_fim) == 0)	$msg = "A data final dos dados do pagamento é inválida.\n";
			}
		
		//Data inclusao
		if($msg == "")
			if($tf_d_data_inclusao_ini || $tf_d_data_inclusao_fim)
			{
				if(verifica_data($tf_d_data_inclusao_ini) == 0)	$msg = "A data de inclusão inicial dos dados do pagamento é inválida.\n";
				if(verifica_data($tf_d_data_inclusao_fim) == 0)	$msg = "A data de inclusão final dos dados do pagamento é inválida.\n";
			}
		
		//valor pago
		if($msg == "")
			if($tf_d_valor_pago)
			{
				if(!is_moeda($tf_d_valor_pago))
					$msg = "Valor Pago dos dados do pagamento é inválido.\n";
			}

		//Usuario
		//------------------------------------------------------------------
		//tf_u_codigo
		if($msg == "")
			if($tf_u_codigo)
			{
				if(!is_numeric($tf_u_codigo))
					$msg = "Código do usuário deve ser numérico.\n";
			}
	
		//Busca vendas
		if($msg == "")
		{
			$sql  = "select ug.ug_id, ug.ug_email, ug.ug_nome, 
							vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia, vg_pagto_num_docto, vg_pagto_banco, vg_pagto_local, vg.vg_ug_id, vg.vg_ex_email, 
							vg.vg_dep_codigo, vg.vg_bol_codigo, 
							sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos 
					 from tb_venda_games vg 
					 inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
					 inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id  ";
 			$sql .= "where 1=1 ";
			if($tf_v_codigo) 			$sql .= " and vg.vg_id IN (".$tf_v_codigo.") ";
			if($tf_v_status) 			$sql .= " and vg.vg_ultimo_status = '".$tf_v_status."' ";
			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) $sql .= " and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'";
			if($tf_v_data_concilia_ini && $tf_v_data_concilia_fim) $sql .= " and vg.vg_data_concilia between '".formata_data_ts($tf_v_data_concilia_ini, 2, true, false)."' and '".formata_data_ts($tf_v_data_concilia_fim, 2, true, false)."' ";
			if($tf_v_data_cancelamento_ini && $tf_v_data_cancelamento_fim){
				$sql .= " and vg.vg_id in (select vgh_vg_id from tb_venda_games_historico where vgh_status = " . $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'] . " and vgh_data_inclusao between '".formata_data($tf_v_data_cancelamento_ini,1)." 00:00:00' and '".formata_data($tf_v_data_cancelamento_fim,1)." 23:59:59')";
			} 
			if(!is_null($tf_v_concilia) && $tf_v_concilia != "") $sql .= " and vg.vg_concilia = '".$tf_v_concilia."' ";
			if($tf_d_forma_pagto) 		$sql .= " and vg.vg_pagto_tipo = ".(($tf_d_forma_pagto==$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) ? $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC : (($tf_d_forma_pagto==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX'])?$GLOBALS['PAGAMENTO_PIX_NUMERIC']:$tf_d_forma_pagto))." ";
			if($tf_v_dep_codigo) 		$sql .= " and vg.vg_dep_codigo = '".$tf_v_dep_codigo."' ";
			if($tf_v_bol_codigo) 		$sql .= " and vg.vg_bol_codigo = '".$tf_v_bol_codigo."' ";
			if($tf_d_banco) 			$sql .= " and vg.vg_pagto_banco = '".$tf_d_banco."' ";
			if($tf_d_local) 			$sql .= " and vg.vg_pagto_local = '".$tf_d_local."' ";
			if($tf_d_data_ini && $tf_d_data_fim) $sql .= " and vg.vg_pagto_data between '".formata_data($tf_d_data_ini,1)." 00:00:00' and '".formata_data($tf_d_data_fim,1)." 23:59:59'";
			if($tf_d_data_inclusao_ini && $tf_d_data_inclusao_fim) $sql .= " and vg.vg_pagto_data_inclusao between '".formata_data($tf_d_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_d_data_inclusao_fim,1)." 23:59:59'";
			if($tf_d_valor_pago) 		$sql .= " and vg.vg_pagto_valor_pago = ".moeda2numeric($tf_d_valor_pago)." ";
			if($tf_d_num_docto) 		$sql .= " and upper(vg.vg_pagto_num_docto) like '%". strtoupper($tf_d_num_docto)."%' ";
		
			// Para permitir buscar E-money por email cadastrado (todos tem o ID de Patrick)
			if($tf_v_origem == "exmo"){	
				$sql .= " and ug.ug_id = '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' ";
				if($tf_u_email) 		$sql .= " and upper(vg.vg_ex_email) like '%".strtoupper($tf_u_email)."%' ";
			} else {
				if($tf_u_codigo)		$sql .= " and ug.ug_id = '".$tf_u_codigo."' ";
				if($tf_u_nome) 			$sql .= " and upper(ug.ug_nome) like '%".strtoupper($tf_u_nome)."%' ";
				if($tf_u_email) 		$sql .= " and upper(ug.ug_email) like '%".strtoupper($tf_u_email)."%' ";
				if($tf_u_cpf) 			$sql .= " and ug.ug_cpf like '%".$tf_u_cpf."%' ";
			}

			//Produtos
			if ($tf_produto && is_array($tf_produto))
				if (count($tf_produto) == 1)
						$sql .= " and upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto[0])) . "%' ";	
				else
				{
					$sql .= " and (";
					foreach($tf_produto as $tf_produto_id => $tf_produto_row)	
						if ($tf_produto_id == count($tf_produto) - 1)
							$sql .= "upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto_row)) . "%')";
						else
							$sql .= "upper(vgm.vgm_nome_produto) like '%" . str_replace("'", "''",strtoupper($tf_produto_row)) . "%' or ";
				}

			//Valores
			if ($tf_pins && is_array($tf_pins))
				if (count($tf_pins) == 1)
						$sql .= " and vgm.vgm_valor = " . moeda2numeric($tf_pins[0]) . " ";	
				else
				{
					$sql .= " and (";
					foreach($tf_pins as $tf_pins_id => $tf_pins_row)	
						if ($tf_pins_id == count($tf_pins) - 1)
							$sql .= "vgm.vgm_valor = " . moeda2numeric($tf_pins_row) . ")";
						else
							$sql .= "vgm.vgm_valor = " . moeda2numeric($tf_pins_row) . " or ";
				}
			
			//Operadoras
			if($tf_opr_codigo) 			$sql .= " and vgm.vgm_opr_codigo = ".$tf_opr_codigo." ";

			$sql .= " group by ug.ug_id, ug.ug_email, ug.ug_nome, 
        						vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia, vg_pagto_num_docto, vg_pagto_banco, vg_pagto_local, vg.vg_ug_id, vg.vg_ex_email, 
        						vg.vg_dep_codigo, vg.vg_bol_codigo
					 having 1=1 ";
			if($tf_v_valor) $sql .= " and sum(vgm.vgm_valor * vgm.vgm_qtde) = ".moeda2numeric($tf_v_valor)." ";
			if($tf_v_qtde_produtos) $sql .= " and count(*) = ".$tf_v_qtde_produtos." ";
			if($tf_v_qtde_itens) $sql .= " and sum(vgm.vgm_qtde) = ".$tf_v_qtde_itens." ";

//echo $sql."<br>";
//die("Stop");
		
			$rs_venda = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_venda);

			//Total Geral
			$totalGeral_valor = 0;
			$totalGeral_qtde_itens = 0;
			if($total_table > 0) {
				while($rs_venda_row = pg_fetch_array($rs_venda)) {
					$totalGeral_valor += $rs_venda_row['valor'];
					$totalGeral_qtde_itens += $rs_venda_row['qtde_itens'];
				}
			}

			// Envia emails
			if($BtnProcessaEmail){
				//envia email para o cliente
				if($msgConcilia == ""){
					$rs_venda = SQLexecuteQuery($sql);
					$n_emails = 0; 
//					$n_emails_in_bloco = 15;
//					$n_delay = 20;
					while($rs_venda_row = pg_fetch_array($rs_venda)) {
						if($rs_venda_row['vg_ultimo_status']==$STATUS_VENDA['VENDA_REALIZADA']) {
							$parametros['ultimo_status_obs'] = $ultimo_status_obs;
							$venda_id = $rs_venda_row['vg_id'];
							$n_emails ++;
echo "<font color='#0000FF'>Enviando email N ".$n_emails." da venda $venda_id '".$rs_venda_row['vg_data_inclusao']."' (IDUsuario: ".$rs_venda_row['vg_ug_id'].", Nome: ".(($rs_venda_row['vg_ug_id'] == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'])?'':$rs_venda_row['ug_nome']).", Email: ".(($rs_venda_row['vg_ug_id'] == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'])?$rs_venda_row['vg_ex_email']:$rs_venda_row['ug_email']).")</font><br>\n";
							$msgConcilia = processaEmailVendaGames($venda_id, $parametros);
							if($msgConcilia == "") {
								$msgConciliaUsuario = "Envio de email: Enviado com sucesso.\n";

								// Manda blocos de 15 emails e aguarda por $n_delay segundos
								if(($n_emails%$n_emails_in_bloco)==0) {
									echo "<font color='#FF6600'>Espera $n_delay segundos para enviar o próximo bloco de $n_emails_in_bloco emails</font><br>\n";
//									sleep($n_delay);
								}
							}
							else  $msgConciliaUsuario = "Envio de email: " . $msgConcilia;

						} else {
echo "<font color='#FF0000'>Venda $venda_id INCOMPLETA (NÃO ENVIA EMAIL) '".$rs_venda_row['vg_data_inclusao']."' (IDUsuario: ".$rs_venda_row['vg_ug_id'].", Nome: ".(($rs_venda_row['vg_ug_id'] == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'])?'':$rs_venda_row['ug_nome']).", Email: ".(($rs_venda_row['vg_ug_id'] == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'])?$rs_venda_row['vg_ex_email']:$rs_venda_row['ug_email']).")</font><br>\n";
						}
					}
				}
			}


			//Ordem
			$sql .= " order by ".$ncamp;
			if($ordem == 1)
			{
				$sql .= " desc ";
				$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
			} else {
				$sql .= " asc ";
				$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
			}
		
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;

//echo $sql."<br>";
//die("Stop");
			if($total_table == 0) 
			{
				$msg = "Nenhuma venda encontrada.\n";
			} else {		
				$rs_venda = SQLexecuteQuery($sql);
				
				if($max + $inicial > $total_table)
					$reg_ate = $total_table;
				else
					$reg_ate = $max + $inicial;
			}		
		}
	}
	
	//Operadoras / Produtos / Valores
	$sql = "select * from operadoras ope where opr_status = '1' ";
	$rs_operadoras = SQLexecuteQuery($sql);
	if($tf_opr_codigo) {
		$sql = "select ogp_id,ogp_nome from tb_operadora_games_produto where ogp_opr_codigo = " . $tf_opr_codigo . "";
		$rs_oprProdutos = SQLexecuteQuery($sql);
		$sql = "select pin_valor from pins where opr_codigo = " . $tf_opr_codigo . " group by pin_valor order by pin_valor;";
		$rs_oprPins = SQLexecuteQuery($sql);
	}
?>
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
<!--<script type="text/javascript" src="js/jquery.ui.nestedSortable.js"></script>-->
<script language="javascript">
    function GP_popupAlertMsg(msg) 
    { //v1.0
        document.MM_returnValue = alert(msg);
    }

    function GP_popupConfirmMsg(msg) 
    { //v1.0
        document.MM_returnValue = confirm(msg);
    }

$(document).ready(function () {
    
    var optDate = new Object();
            optDate.interval = 6;
            optDate.minDate = "01/01/2010";

    setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
    setDateInterval('tf_v_data_cancelamento_ini','tf_v_data_cancelamento_fim',optDate);
    setDateInterval('tf_d_data_ini','tf_d_data_fim',optDate);
    setDateInterval('tf_d_data_inclusao_ini','tf_d_data_inclusao_fim',optDate);
    
    
    $('#tf_v_origem').change(function(){
        if ($('#tf_v_origem').val() == "exmo") {
            $('#DivCodigoNome').hide();
            $('#DivCpf').hide();
            $('#DivCpfInput').hide();
        }else{
            $('#DivCodigoNome').show();
            $('#DivCpf').show();
            $('#DivCpfInput').show();
        }
    });
        
    <?php
    if ($tf_v_origem == "exmo"){
    ?>
    $('#DivCodigoNome').hide();
    $('#DivCpf').hide();
    $('#DivCpfInput').hide();
    <?php
    }
    ?>
            
    $('#tf_opr_codigo').change(function(){
        var id = $(this).val();
        //alert(id);

        $.ajax({
            type: "POST",
            url: "/ajax/gamer/ajaxProdutoComPesquisaVendas.php",
            data: "id="+id,
            beforeSend: function(){
                $('#mostraProdutos').html("Aguarde...");
            },
            success: function(html){
                //alert('produto');
                $('#mostraProdutos').html(html);
            },
            error: function(){
                alert('erro produto');
            }
        });

        $.ajax({
            type: "POST",
            url: "/ajax/gamer/ajaxValorComPesquisaVendas.php",
            data: "id="+id,
            beforeSend: function(){
                $('#mostraValores').html("Aguarde...");
            },
            success: function(html){
                //alert('valor');
                $('#mostraValores').html(html);
            },
            error: function(){
                alert('erro valor');
            }
        });
    });
});
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
    <tr> 
        <td width="891" valign="top">
        <form name="form1" method="post" action="com_pesquisa_vendas_envia_email.php<?php if($tf_v_origem == "exmo") echo "?tf_v_origem=exmo";?>">
        <table class="table txt-preto fontsize-pp">
            <tr bgcolor="#F5F5FB"> 
                <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
            </tr>
        </table>
        <table class="table txt-preto fontsize-pp">
            <tr bgcolor="#FFFFFF"> 
                <td colspan="6" bgcolor="#ECE9D8" class="texto">Venda</font></td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td width="100" class="texto">Origem</font></td>
                <td>
                    <select name="tf_v_origem" id='tf_v_origem' class="form2">
                        <option value="">Selecione</option>
                        <option value="mo" <?php if($tf_v_origem == "mo") echo "selected" ?>>Money</option>
                        <option value="exmo" <?php if($tf_v_origem == "exmo") echo "selected" ?>>Express Money</option>
                    </select>
                </td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td width="100" class="texto">Número do Pedido</font></td>
                <td>
                    <input name="tf_v_codigo" type="text" class="form2" value="<?php echo $tf_v_codigo ?>" size="50">
                </td>
                <td class="texto">Status</font></td>
                <td>
                    <select name="tf_v_status" class="form2">
                        <option value="" <?php if($tf_v_status == "") echo "selected" ?>>Selecione</option>
                        <?php foreach ($STATUS_VENDA_DESCRICAO as $statusId => $statusNome){ ?>
                            <option value="<?php echo $statusId; ?>" <?php if ($tf_v_status == $statusId) echo "selected";?>><?php echo $statusId . " - " . substr($statusNome, 0, strpos($statusNome, '.')); ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td class="texto">Qtde Produtos</font></td>
                <td>
                    <input name="tf_v_qtde_produtos" type="text" class="form2" value="<?php echo $tf_v_qtde_produtos ?>" size="7" maxlength="7">
                </td>
                <td class="texto">Qtde Itens Total</font></td>
                <td>
                    <input name="tf_v_qtde_itens" type="text" class="form2" value="<?php echo $tf_v_qtde_itens ?>" size="7" maxlength="7">
                </td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td class="texto">Valor</font></td>
                <td>
                    <input name="tf_v_valor" type="text" class="form2" value="<?php echo $tf_v_valor ?>" size="10" maxlength="10">
                </td>
                <td class="texto">Data Inclusão</font></td>
                <td class="texto">
                    <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
                    a 
                    <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
                </td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td class="texto">Conciliação</font></td>
                <td>
                    <select name="tf_v_concilia" class="form2">
                        <option value="" <?php if($tf_v_concilia == "") echo "selected" ?>>Selecione</option>
                        <option value="1" <?php if ($tf_v_concilia == "1") echo "selected";?>>Conciliado</option>
                        <option value="0" <?php if ($tf_v_concilia == "0") echo "selected";?>>Não conciliado</option>
                    </select>
                </td>
                <td class="texto">Data Conciliação</font></td>
                <td class="texto">
                    <input name="tf_v_data_concilia_ini" type="text" class="form" id="tf_v_data_concilia_ini" value="<?php echo $tf_v_data_concilia_ini ?>" size="15" maxlength="16">
                    a 
                    <input name="tf_v_data_concilia_fim" type="text" class="form" id="tf_v_data_concilia_fim" value="<?php echo $tf_v_data_concilia_fim ?>" size="15" maxlength="16">
                    Formato: DD/MM/AAAA hh:mm
                </td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td class="texto"></font></td>
                <td></td>
                <td class="texto">Data Cancelamento</font></td>
                <td class="texto">
                    <input name="tf_v_data_cancelamento_ini" type="text" class="form" id="tf_v_data_cancelamento_ini" value="<?php echo $tf_v_data_cancelamento_ini ?>" size="9" maxlength="10">
                    a 
                    <input name="tf_v_data_cancelamento_fim" type="text" class="form" id="tf_v_data_cancelamento_fim" value="<?php echo $tf_v_data_cancelamento_fim ?>" size="9" maxlength="10">
                </td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td class="texto">Cód Depósito</font></td>
                <td>
                    <input name="tf_v_dep_codigo" type="text" class="form2" value="<?php echo $tf_v_dep_codigo ?>" size="7" maxlength="7">
                </td>
                <td class="texto">Cód Boleto</font></td>
                <td>
                    <input name="tf_v_bol_codigo" type="text" class="form2" value="<?php echo $tf_v_bol_codigo ?>" size="7" maxlength="7">
                </td>
            </tr>
            <tr bgcolor="#FFFFFF"> 
                <td colspan="4" bgcolor="#ECE9D8" class="texto">Dados do Pagamento</font></td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td width="100" class="texto">Forma de Pagamento</td>
                <td colspan="3">
                    <select name="tf_d_forma_pagto" class="form2">
                        <option value="" <?php if($tf_d_forma_pagto == "") echo "selected" ?>>Selecione</option>
                        <?php foreach ($FORMAS_PAGAMENTO_DESCRICAO as $formaId => $formaNome){ ?>
                            <option value="<?php echo $formaId; ?>" <?php if ($tf_d_forma_pagto == $formaId) echo "selected";?>><?php echo $formaId . " - " . $formaNome; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr bgcolor="#F5F5FB">
                <td width="100" class="texto">Banco</td>
                <td colspan="3">
                    <select name="tf_d_banco" class="form2" onChange="populate_local(document.form1.tf_d_banco, document.form1.tf_d_local, '');">
                        <option value="" <?php if($tf_d_banco == "") echo "selected" ?>>Selecione</option>
                        <?php foreach ($PAGTO_BANCOS as $bancoId => $bancoNome){ ?>
                            <option value="<?php echo $bancoId; ?>" <?php if ($tf_d_banco == $bancoId) echo "selected";?>><?php echo $bancoNome; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr bgcolor="#F5F5FB">
                <td width="100" class="texto">Local</td>
                <td colspan="3">
                    <select name="tf_d_local" class="form2"></select>
                </td>
            </tr>
            <script>
                function populate_local(combo1, combo2, combo2_default)
                {
                    opcoesAr = new Array(new Array("","Selecione"));

                    <?php foreach ($PAGTO_BANCOS as $bancoId => $bancoNome){ ?>
                        if(combo1[combo1.selectedIndex].value == "<?php echo $bancoId?>")
                        {   <?php if(isset($PAGTO_LOCAIS[$bancoId])){ ?>
                                <?php foreach ($PAGTO_LOCAIS[$bancoId] as $localId => $localNome){ ?>
                                    opcoesAr[opcoesAr.length] = new Array("<?php echo $localId?>","<?php echo $localNome?>");
                                <?php } ?>
                            <?php } ?>
                        }
                    <?php } ?>

                    //limpa combo
                    for(var i=combo2.length-1; i>=0; i--) combo2.options[i] = null;
                    //popula combo
                    for(var i=0; i<opcoesAr.length; i++) combo2.options[i] = new Option(opcoesAr[i][1], opcoesAr[i][0]);
                    //seleciona opcao
                    for(var i=combo2.length-1; i>=0; i--) if(combo2[i].value == combo2_default) combo2.selectedIndex = i;
                } 
            </script>
            <script>populate_local(document.form1.tf_d_banco, document.form1.tf_d_local, "<?php echo $tf_d_local?>");</script>
            <tr bgcolor="#F5F5FB">
                <td class="texto">N. Docto</font></td>
                <td>
                    <input name="tf_d_num_docto" type="text" class="form2" value="<?php echo $tf_d_num_docto ?>" size="25" maxlength="15">
                </td>
                <td class="texto">Data Informada</font></td>
                <td class="texto">
                    <input name="tf_d_data_ini" type="text" class="form" id="tf_d_data_ini" value="<?php echo $tf_d_data_ini ?>" size="9" maxlength="10">
                    a 
                    <input name="tf_d_data_fim" type="text" class="form" id="tf_d_data_fim" value="<?php echo $tf_d_data_fim ?>" size="9" maxlength="10">
                </td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td class="texto">Valor Pago</font></td>
                <td>
                    <input name="tf_d_valor_pago" type="text" class="form2" value="<?php echo $tf_d_valor_pago ?>" size="7" maxlength="7">
                </td>
                <td class="texto">Data Inclusão</font></td>
                <td class="texto">
                    <input name="tf_d_data_inclusao_ini" type="text" class="form" id="tf_d_data_inclusao_ini" value="<?php echo $tf_d_data_inclusao_ini ?>" size="9" maxlength="10">
                    a 
                    <input name="tf_d_data_inclusao_fim" type="text" class="form" id="tf_d_data_inclusao_fim" value="<?php echo $tf_d_data_inclusao_fim ?>" size="9" maxlength="10">
                </td>
            <tr bgcolor="#FFFFFF" id="divUsuario"> 
                <td colspan="4" bgcolor="#ECE9D8" class="texto">

                    <table class="table txt-preto fontsize-pp">
                        <tr bgcolor="#FFFFFF"> 
                            <td colspan="4" bgcolor="#ECE9D8" class="texto">Usuário</font></td>
                        </tr>
                    </table>

                        <div id='DivCodigoNome'>
                        <table class="table txt-preto fontsize-pp">
                            <tr bgcolor="#F5F5FB" id="divUsuario1"> 
                            <td class="texto" width='98px'>C&oacute;digo</font></td>
                            <td width='208px'>
                                <input name="tf_u_codigo" type="text" class="form2" value="<?php echo $tf_u_codigo ?>" size="7" maxlength="7">
                                </td>
                            <td class="texto" width='110px'>Nome</font></td>
                            <td>
                                <input name="tf_u_nome" type="text" class="form2" value="<?php echo $tf_u_nome ?>" size="25" maxlength="100">
                                </td>
                            </tr>
                    </table>
                        </div>

                        <table class="table txt-preto fontsize-pp">
                            <tr bgcolor="#F5F5FB"> 
                            <td class="texto" width='98px'>Email</font></td>
                            <td width='208px'>
                                <input name="tf_u_email" type="text" class="form2" value="<?php echo $tf_u_email ?>" size="25" maxlength="100">
                                </td>
                            <td class="texto" id="divUsuario2" width='110px'>
                                    <div id='divCpf'>
                                    CPF
                                    </div>
                                </td>
                            <td id="divUsuario3">
                                    <div id='divCpfInput'>
                                <input name="tf_u_cpf" type="text" class="form2" value="<?php echo $tf_u_cpf ?>" size="25" maxlength="14">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr bgcolor="#FFFFFF"> 
                <td colspan="4" bgcolor="#ECE9D8" class="texto">Produto</font></td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td width="100" class="texto">Operadora</font></td>
                <td>
                    <select name="tf_opr_codigo" id="tf_opr_codigo" class="form2">
                        <option value="" <?php if($tf_opr_codigo == "") echo "selected" ?>>Selecione</option>
                        <?php 
                            if($rs_operadoras) 
                                while($rs_operadoras_row = pg_fetch_array($rs_operadoras))
                                {
                        ?>
                                    <option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>"
                                    <?php 
                                        if ($tf_opr_codigo == $rs_operadoras_row['opr_codigo'] || $rs_operadoras_row['opr_codigo'] == $buscaOper) 
                                            echo " selected";
                                    ?>><?php echo $rs_operadoras_row['opr_nome']; ?></option>
                                <?php } ?>
                    </select>
                </td>
                <td colspan="2"></td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td class="texto">Produtos</font></td>
                <td colspan="3" class="texto">
                        <div id='mostraProdutos'>
                        <?php 
                if(isset($rs_oprProdutos))
                   while($rs_oprProdutos_row = pg_fetch_array($rs_oprProdutos))
                   { 
                ?>
                                <input type="checkbox" id="tf_produto[]" name="tf_produto[]" value="<?php echo $rs_oprProdutos_row['ogp_nome']; ?>" 
                                <?php
                                    if ($tf_produto && is_array($tf_produto))
                                        if (in_array($rs_oprProdutos_row['ogp_nome'], $tf_produto)) 
                                            echo " checked";
                                    else
                                        if ($rs_oprProdutos_row['ogp_nome'] == $tf_produto)
                                            echo " checked";
                                ?>><?php echo $rs_oprProdutos_row['ogp_nome']; ?>
                <?php 
                            } 
                        ?>
                        </div>
                    </td>
            </tr>
          <tr bgcolor="#F5F5FB"> 
                <td class="texto">Valores</font></td>
                <td colspan="3" class="texto">
                        <div id='mostraValores'>
                        <?php 
                        if(isset($rs_oprPins))
                            while($rs_oprPins_row = pg_fetch_array($rs_oprPins))
                            { 
                    ?>
                                <input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="<?php echo $rs_oprPins_row['pin_valor']; ?>" 
                                <?php
                                    if ($tf_pins && is_array($tf_pins))
                                        if (in_array($rs_oprPins_row['pin_valor'], $tf_pins)) 
                                            echo " checked";
                                    else
                                        if ($rs_oprPins_row['pin_valor'] == $tf_pins)
                                            echo " checked";
                                ?>><?php echo $rs_oprPins_row['pin_valor'] . ",00"; ?>
                            <?php } ?>
                        </div>
                    </td>
            </tr>  
        </table>
        <table class="table txt-preto fontsize-pp">
            <tr bgcolor="#F5F5FB"> 
                <td colspan="2" align="center"><input type="submit" name="BtnProcessaEmail" value="Processar Email" class="btn btn-sm btn-info"> <?php if($n_delay>0) { ?>(<font color='#FF6600' class="texto">Envia blocos de <?php echo $n_emails_in_bloco ?> emails e aguarda pelo menos <?php echo $n_delay ?> segundos entre um bloco e outro</font>)<?php } ?></td>
            </tr>
            <tr bgcolor="#F5F5FB"> 
                <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
            </tr>
            <?php if($msg != ""){?>
                <tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr>
            <?php }?>
        </table>
        </form>
        <?php if($total_table > 0) { ?>
            <table class="table txt-preto fontsize-pp">
                <tr bgcolor="#00008C"> 
                    <td height="11" colspan="3" bgcolor="#FFFFFF"> 
                        <table class="table txt-preto fontsize-pp">
                            <tr> 
                                <td colspan="20" class="texto"> 
                                    Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                                </td>
                            </tr>
                            <?php $ordem = ($ordem == 1)?2:1; ?>
                            <tr  bgcolor="#ECE9D8"> 
                                <td align="center">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.</a><?php if($ncamp == 'vg_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="center">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data Inclusão</font></a><?php if($ncamp == 'vg_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="center">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_pagto_tipo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Forma de<br>Pagamento</font></a><?php if($ncamp == 'vg_pagto_tipo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="center">
                                    <strong><font class="texto">Dados de pagamento</font></strong>
                                </td>
                                <td align="center">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=valor&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Valor</font></a><?php if($ncamp == 'valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="center">
                                    <strong><font class="texto">Depósito/<br>Boleto</font></strong>
                                </td>
                                <td align="center">
                                    <strong><font class="texto">Produtos</font></strong>
                                </td>
                                <td align="center">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=qtde_itens&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Qtde<br>Total</font></a><?php if($ncamp == 'qtde_itens') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="center">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">C&oacute;d.<br>Usuário</font></a><?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="center">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_nome&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Nome Usuário</font></a><?php if($ncamp == 'ug_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                                <td align="center">
                                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_concilia&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Conciliação</font></a><?php if($ncamp == 'vg_concilia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
                                </td>
                            </tr>
                            <?php
                                $cor1 = $query_cor1;
                                $cor2 = $query_cor1;
                                $cor3 = $query_cor2;
                                //total
                                $total_valor = 0;
                                $total_qtde_itens = 0;
                                while($rs_venda_row = pg_fetch_array($rs_venda))
                                {
                                    $cor1 = ($cor1 == $cor2)?$cor3:$cor2;
                                    $status = $rs_venda_row['vg_ultimo_status'];
                                    $pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
                                    if($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']) 
                                        $pagto_tipo_aux = "Transf, DOC, Dep";
                                    else if($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'])
                                        $pagto_tipo_aux = "Boleto";
                                    else 
                                        $pagto_tipo_aux = (isset($GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$pagto_tipo])) ? $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$pagto_tipo] : "";

                                    //total
                                    $total_valor += $rs_venda_row['valor'];
                                    $total_qtde_itens += $rs_venda_row['qtde_itens'];
                            ?>
                            <tr bgcolor="<?php echo $cor1 ?>"> 
                                <td class="texto" align="center"><a style="text-decoration:none" href="com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['vg_id'] ?>&fila_ncamp=<?php echo $ncamp?>&fila_ordem=<?php echo ($ordem == 1?2:1) ?><?php echo $varsel?>"><?php echo $rs_venda_row['vg_id'] ?></a>
                                </td>
                                <td class="texto" align="center">
                                    <?php echo formata_data_ts($rs_venda_row['vg_data_inclusao'],0, true,true) ?>
                                </td>
                                <td class="texto">
                                    <?php echo $pagto_tipo_aux ?>
                                </td>
                                <?php if($status == $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA']){?>
                                <?php } else {?>
                                <?php } ?>
                                <td class="texto" align="left">
                                    <?php
                                        $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
                                        $vg_pagto_local = $rs_venda_row['vg_pagto_local'];
                                        $pagto_num_docto = preg_split("/\|/", $rs_venda_row['vg_pagto_num_docto']);
                                        $pagto_nome_docto_Ar = (isset($PAGTO_NOME_DOCTO[$vg_pagto_banco][$vg_pagto_local])) ? preg_split("/;/", $PAGTO_NOME_DOCTO[$vg_pagto_banco][$vg_pagto_local]) : "";
                                    ?>
                                    <b>Banco:</b> <?php echo (isset($PAGTO_BANCOS[$vg_pagto_banco])) ? $PAGTO_BANCOS[$vg_pagto_banco] : ""?><br>
                                    <b>Local:</b> <?php echo (isset($PAGTO_LOCAIS[$vg_pagto_banco][$vg_pagto_local])) ? $PAGTO_LOCAIS[$vg_pagto_banco][$vg_pagto_local] : ""?><br>
                                    <?php for($i=0; $i<count($pagto_nome_docto_Ar); $i++){?>
                                        <b><?php echo (!is_array($pagto_nome_docto_Ar)?"Nro Documento":$pagto_nome_docto_Ar[$i]); ?></b>: <?php echo $pagto_num_docto[$i]?><br>
                                    <?php } ?>
                                </td>
                                <td class="texto" align="right">
                                    <?php echo number_format($rs_venda_row['valor'], 2, ',','.') ?>
                                </td>
                                <?php	if($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']){ ?>
                                    <td class="texto" align="center">
                                        <a style="text-decoration:none" target="_blank" href="/financeiro/pedidos/depositos/altera.php?DepCod=<?php echo $rs_venda_row['vg_dep_codigo'] ?>"><?php echo $rs_venda_row['vg_dep_codigo'] ?></a>
                                    </td>
                                <?php	} else if($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){ ?>
                                    <td class="texto" align="center">
                                        <a style="text-decoration:none" target="_blank" href="/financeiro/pedidos/boletos/altera.php?BolCod=<?php echo $rs_venda_row['vg_bol_codigo'] ?>"><?php echo $rs_venda_row['vg_bol_codigo'] ?></a>
                                    </td>
                                <?php	} else { ?>
                                    <td class="texto" align="center">&nbsp;</td>
                                <?php	} ?>
                                <?php
                                    $venda_id = $rs_venda_row['vg_id'];

                                    //Recupera modelos
                                    if($msg == "")
                                    {
                                        $sql  = "select * from tb_venda_games vg "  . "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " . "where vg.vg_id = " . $venda_id;
                                        //echo "sql: $sql<br>";
                                        $rs_venda_modelos = SQLexecuteQuery($sql);
                                        if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) 
                                            $produtos = "Sem produto.\n";
                                        else 
                                        {
                                            $produtos = "";
                                            while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos))
                                            {
                                                $produtos .= "<b>" . $rs_venda_modelos_row['vgm_nome_produto'] . "</b>"; 
                                                if($rs_venda_modelos_row['vgm_nome_modelo']!="") 
                                                    $produtos .= " - " . $rs_venda_modelos_row['vgm_nome_modelo'];
                                                    $produtos .= "<br>"; 
                                            }
                                        }
                                    }
                                ?>                        
                                <td class="texto" align="left"><?php echo $produtos ?></td>
                                <td class="texto" align="right"><?php echo $rs_venda_row['qtde_itens'] ?></td>
                                <td class="texto" align="center"><?php echo $rs_venda_row['ug_id'] ?></td>
                                <td class="texto">
                                    <?php 
                                        echo $rs_venda_row['ug_nome']; 
                                        // Recupera email para Money Express
                                        if($tf_v_origem == "exmo")
                                        {	
                                            $sql1 = "select vg_ex_email from tb_venda_games vg where vg.vg_id = " . $rs_venda_row['vg_id'];
                                            //echo "sql1: $sql1<br>";
                                            $rs_venda1 = SQLexecuteQuery($sql1);
                                            if($rs_venda1 && pg_num_rows($rs_venda1) > 0) 
                                            {
                                                $rs_venda_row1 = pg_fetch_array($rs_venda1);
                                                echo "<br>(".strtoupper($rs_venda_row1['vg_ex_email']).")";
                                            }
                                        }
                                    ?>
                                </td>
                                <?php if(	$status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] ||
                                $status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] ||
                                $status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] ||
                                $status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']){?>
                                    <td class="texto" align="center">
                                        <a style="text-decoration:none" href="com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['vg_id'] ?>&fila_ncamp=<?php echo $ncamp?>&fila_ordem=<?php echo ($ordem == 1?2:1) ?><?php echo $varsel?>">
                                        <?php if($rs_venda_row['vg_concilia'] == 0){?>
                                            <font color="#ff0000">Conciliar</font>
                                        <?php } else { ?>
                                            <font color="#009933">Conciliado</font>				
                                        <?php } ?>
                                        </a>
                                    </td>
                                <?php } else { ?>
                                    <td>&nbsp;</td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        <tr bgcolor="E5E5EB"> 
                            <td class="texto" align="right" colspan="4"><b>Total:</b></td>
                            <td class="texto" align="right"><?php echo number_format($total_valor, 2, ',','.') ?></td>
                            <td class="texto" align="right" colspan="2">&nbsp;</td>
                            <td class="texto" align="right">
                                <?php echo number_format($total_qtde_itens, 0, '','.') ?>
                            </td>
                            <td class="texto" align="right" colspan="3"></td>
                        </tr>
                        <tr bgcolor="D5D5DB"> 
                            <td class="texto" align="right" colspan="4"><b>Total Geral:</b></td>
                            <td class="texto" align="right"><?php echo number_format($totalGeral_valor, 2, ',','.') ?></td>
                            <td class="texto" align="right" colspan="2">&nbsp;</td>
                            <td class="texto" align="right"><?php echo number_format($totalGeral_qtde_itens, 0, '','.') ?></td>
                            <td class="texto" align="right" colspan="3"></td>
                        </tr>
                        <tr> 
                            <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
                        </tr>
                        <?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
                    </table>
                </td>
            </tr>
        </table>
    <?php  }  ?>
    </td>
</tr>
</table>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</html>