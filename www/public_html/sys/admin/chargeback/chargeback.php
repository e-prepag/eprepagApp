<?php
session_start();

//if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
		
	//$_SESSION["opr_vinculo"] = 124;
	
	require_once "/www/includes/constantes.php";
	require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
	require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";
	require_once $raiz_do_projeto . "includes/gamer/constantes.php";
	 
    // CONTROLLER
	require_once $raiz_do_projeto."class/business/EstornoChargeBackBO.class.php";
	require_once $raiz_do_projeto."class/business/CategoriaEstornoChargebackBO.class.php";
	
	if(isset($_POST["btnPesquisa"])){
		
		if(!empty($_POST["dataDevolucaoInicio"]) && !empty($_POST["dataDevolucaoFinal"])){

			// configurações/legendas relacionadas aos dados recebidos do banco de dados
			$vetorTipo = array('1' => 'ChargeBack', '2' => 'Estorno');
			$vetorTipoUsuario = array('G' => 'Gamer', 'L' => 'Lan House');
			$vetorFormaDevolucao = array('1' => 'Devolução em Saldo', '2' => 'Devolução através de Depósito');
			$vetorPINsBloqueados = array('0' => 'NÃO foi Bloqueado', '1' => 'Foi Bloqueado');

			// recuperando a lista de operadoras ativas
			$sql = "select opr_codigo, opr_nome from operadoras where opr_status = '1' order by opr_nome";
			$rs_operadoras_operantes = SQLexecuteQuery($sql);
			while($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
				$vetorPublisher[$rs_operadoras_operantes_row['opr_codigo']] = $rs_operadoras_operantes_row['opr_nome'];
			}

			$objEstornoChargeBack = new EstornoChargeBackBO();
			if(!empty($_POST["dataDevolucaoInicio"]))     $filtros["ec_data_devolucao"] = "ec_data_devolucao >= '".Util::getData($_POST["dataDevolucaoInicio"], true)." 00:00:00'";
			if(!empty($_POST["dataDevolucaoFinal"])) $filtros["ec_data_devolucao_fim"] = "ec_data_devolucao <= '".Util::getData($_POST["dataDevolucaoFinal"], true)." 23:59:59'";
			if(!empty($_POST["formachargeback"]))    $filtros["ec_forma_devolucao"] = "ec_forma_devolucao = ".$_POST["formachargeback"]; 
			if(!empty($_POST["numPedido"]))                 $filtros["vg_id"] = "vg_id = ".$_POST["numPedido"]; 
			if(!empty($_SESSION["opr_vinculo"]) && $_SESSION["opr_vinculo"] != 0) $filtros["opr_codigo"] = "opr_codigo = ".$_SESSION["opr_vinculo"]; 
			$EstornoChargeBack = $objEstornoChargeBack->pegaEstornoChargeBack($filtros);
			$_SESSION["excelBack"] = $EstornoChargeBack;
			$_SESSION["oprs"] = $vetorPublisher;

		}else{

			$erro = LANG_FORM_RETURN_INFO_ERRO_DATE;

		}
		
	}
	
//}else{
	//header("location: https://www.e-prepag.com.br/sys/admin/commerce/index.php");
//}

?>
<html>
	<head>
		<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
		<title>E-Prepag</title>
		<style>
		    body{
				font-family: "Helvetica Neue", Arial, sans-serif, Verdana;
				line-height: 1.42857143;
			}
			.titulo{
				font-size: 16px;
				font-weight: bold;
				margin-bottom: 20px;
			}
			.alert{
				padding: 10px;
				background-color: #dddddd;
				width: 280px;
			}
			.table-response{
				width: 100%;
				overflow: auto;
			}

            @media screen and (min-width: 1000px){

				.height-m{
					min-height: 30vh;
				}
				
			}

		</style>
	</head>
	<body>
	    <h1 class="txt-azul-claro titulo"><?php echo LANG_TEXT_TITLE_CHARGEBACK;?></h1>
        <?php if(isset($erro) && !empty($erro)){ ?>
			<div class="alert">
				<span><?php echo $erro; ?></span>
			</div>
		<?php } ?>
	    <div class="row container" style="display: flex;justify-content: center;margin: 0;"> 
		    <form action="" method="POST">
				<div class="col-md-5">
				    <label><?php echo LANG_DATE_RETURN;?></label>
					<div style="display: flex;">
					<input type="date" 
					min="<?php echo date("Y-m-d", strtotime("-1 year")); ?>" 
					value="<?php echo isset($_POST["dataDevolucaoInicio"]) ? htmlspecialchars($_POST["dataDevolucaoInicio"], ENT_QUOTES, 'UTF-8') : ''; ?>" 
					max="<?php echo date("Y-m-d"); ?>" 
					name="dataDevolucaoInicio" class="form-control" id=""> 
				<span style="margin: 8px;">a</span>
				<input type="date" 
					min="<?php echo date("Y-m-d", strtotime("-1 year")); ?>" 
					value="<?php echo isset($_POST["dataDevolucaoFinal"]) ? htmlspecialchars($_POST["dataDevolucaoFinal"], ENT_QUOTES, 'UTF-8') : ''; ?>" 
					max="<?php echo date("Y-m-d"); ?>" 
					name="dataDevolucaoFinal" class="form-control" id="">
				</div>
				</div>
			    <div class="col-md-3">
					<label><?php echo LANG_NUM_REQUEST;?></label>
					<input type="number" name="numPedido" class="form-control" id="" value="<?php echo isset($_POST["numPedido"]) ? htmlspecialchars($_POST["numPedido"], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="00000">
</div>
				<div class="col-md-3">
				    <label><?php echo LANG_FORM_RETURN;?></label>
					<select class="form-control" id="" name="formachargeback">
					    <option value="" <?php if(isset($_POST["formachargeback"]) && $_POST["formachargeback"] == "") echo "selected"; ?>><?php echo LANG_FORM_RETURN_OPALL;?></option>
						<option value="1" <?php if(isset($_POST["formachargeback"]) && $_POST["formachargeback"] == "1") echo "selected"; ?>><?php echo LANG_FORM_RETURN_OPONE;?></option>
						<option value="2" <?php if(isset($_POST["formachargeback"]) && $_POST["formachargeback"] == "2") echo "selected"; ?>><?php echo LANG_FORM_RETURN_OPTWO;?></option>
					</select>
				</div>
				<div class="col-md-1">
				    <button style="margin-top: 15px;" class="btn btn-success" name="btnPesquisa"><?php echo LANG_TEXT_BUTTON_SEARCH;?></button>
				</div>
			</form>
		</div>
		<div class="row container height-m" style="margin: 20px 0 0 0;">
		<?php if(isset($_POST["btnPesquisa"])){ ?>
			<div style="text-align: left;margin: 0;">
		        <a style="margin: 10px 0;" href="https://www.e-prepag.com.br/sys/admin/chargeback/geraExcel.php" class="btn btn-success">Excel</a>
		    </div>
			<div class="table-response">
				<table class="table table-bordered bg-branco txt-preto fontsize-pp text-center">
					<thead style="background-color: black;color: white;">
						<tr>
							<th class="text-center">Data Devolução</th>
							<th class="text-center">Data Venda</th>
							<th class="text-center">Tipo de pagamento</th>
							<th class="text-center">Tipo Devolução</th>
							<th class="text-center">Forma</th>
							<th class="text-center">ID Usuário</th>
							<th class="text-center">Titular</th>
							<th class="text-center">CPF Titular</th>
							<th class="text-center">Pedido</th>
							<th class="text-center">Publisher</th>
							<?php if($_SESSION["opr_vinculo"] == 124 || $_SESSION["opr_vinculo"] == 0){ ?>
							    <th class="text-center">Display txn_id</th>
							<?php } ?>
							<th class="text-center">PIN Bloqueado Publisher</th>
							<th class="text-center">Valor R$</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
								if(isset($EstornoChargeBack) && !empty($EstornoChargeBack) && is_array($EstornoChargeBack)){
									foreach($EstornoChargeBack as $informacoes){
										$total_geral += $informacoes['ec_valor'];  //$FORMAS_PAGAMENTO_DESCRICAO
							?>
								<tr class="trListagem c-pointer estornoChargebackOpt" id="<?php echo htmlspecialchars($informacoes['id'], ENT_QUOTES, 'UTF-8'); ?>">
									<td><?php echo Util::getData(htmlspecialchars($informacoes['ec_data_devolucao'], ENT_QUOTES, 'UTF-8')); ?></td>
									<td><?php echo Util::getData(htmlspecialchars($informacoes['vg_data_inclusao'], ENT_QUOTES, 'UTF-8')); ?></td>
									<td><?php echo htmlspecialchars($FORMAS_PAGAMENTO_DESCRICAO_NUMERICO[$informacoes['vg_pagto_tipo']], ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars($vetorTipo[$informacoes['ec_tipo']], ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo (isset($informacoes['ec_forma_devolucao']) ? htmlspecialchars($vetorFormaDevolucao[$informacoes['ec_forma_devolucao']], ENT_QUOTES, 'UTF-8') : ""); ?></td>
									<td><?php echo htmlspecialchars($informacoes['ug_id'], ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo ucwords(strtolower(htmlspecialchars((isset($informacoes['edb_titular']) ? $informacoes['edb_titular'] : $informacoes['usuarioNome']), ENT_QUOTES, 'UTF-8'))); ?></td>
									<td class="nobr"><?php echo (isset($informacoes['edb_cpf_cnpj']) ? htmlspecialchars($informacoes['edb_cpf_cnpj'], ENT_QUOTES, 'UTF-8') : htmlspecialchars(substr($informacoes['ug_cpf'], 0, 3) . "." . substr($informacoes['ug_cpf'], 3, 3) . "." . substr($informacoes['ug_cpf'], 6, 3) . "-" . substr($informacoes['ug_cpf'], 9, 2), ENT_QUOTES, 'UTF-8')); ?></td>
									<td><?php echo htmlspecialchars($informacoes['vg_id'], ENT_QUOTES, 'UTF-8'); ?></td>
									<td class="nobr"><?php echo htmlspecialchars($vetorPublisher[$informacoes['opr_codigo']], ENT_QUOTES, 'UTF-8'); ?></td>
									<?php if ($_SESSION["opr_vinculo"] == 124 || $_SESSION["opr_vinculo"] == 0) { ?>
										<td>
											<?php 
												if (count($informacoes['cod_garena']) > 0) {
													foreach ($informacoes['cod_garena'] as $value) {
														if ($value != "" && $value != null) {
															echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "<br>";
														}
													}
												} else {
													echo "Não possui";
												}
											?>
										</td>
									<?php } ?>
									<td><?php echo htmlspecialchars($vetorPINsBloqueados[$informacoes['ec_pin_bloqueado']], ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo Util::getNumero(htmlspecialchars($informacoes['ec_valor'], ENT_QUOTES, 'UTF-8')); ?></td>
								</tr>

							<?php
									}
							?>
								<tr>
									<td colspan="<?php echo ($_SESSION["opr_vinculo"] == 124 || $_SESSION["opr_vinculo"] == 0)? "12":"11"; ?>" align="right"><b>Total R$</b></td>
									<td><b><?php echo Util::getNumero(htmlspecialchars($total_geral, ENT_QUOTES, 'UTF-8')); ?></b></td>
								</tr>
							<?php
								}else{
							?>
								<tr class="trListagem c-pointer estornoChargebackOpt">
									<td colspan="<?php echo ($_SESSION["opr_vinculo"] == 124 || $_SESSION["opr_vinculo"] == 0)? "13":"12"; ?>"><?php echo LANG_FORM_RETURN_INFO;?></td>
								</tr>
							<?php
								}
							?>
						</tr>
					</tbody>
				</table>
			</div>
		<?php } ?>
		</div>
	</body>
</html>
<?php  
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>