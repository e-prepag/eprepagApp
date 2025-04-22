<?php 

session_start();

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/ofertas/config.inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."class/gamer/classOfertas.php";
require_once $raiz_do_projeto."class/gamer/classSrvUtils.php";

$varsel = "";
$filtro = array();			
$orderBy = "";

if(isset($_GET['filtroID'])) { 
	$filtro['ugo_id'] = $_GET['filtroID']; 
	$varsel .= "&filtroID=".$_GET['filtroID']; 
}

if(isset($_GET['filtroCanalOferta'])) { 
	$filtro['ugoc_descricao'] = $_GET['filtroCanalOferta'];
	$varsel .= "&filtroCanalOferta=".$_GET['filtroCanalOferta'];
}

if(isset($_GET['filtroCodigoOferta'])) {
	$filtro['ugo_oferta_id'] = $_GET['filtroCodigoOferta'];
	$varsel .= "&filtroCodigoOferta=".$_GET['filtroCodigoOferta'];
}

if(isset($_GET['filtroTransacaoID'])) { 
	$filtro['ugo_transaction_id'] = $_GET['filtroTransacaoID'];
	$varsel .= "&filtroTransacaoID=".$_GET['filtroTransacaoID'];
}

if(isset($_GET['filtroValorOferta'])) { 
	$filtro['ugo_valor_credito'] = $_GET['filtroValorOferta']; 
	$varsel .= "&filtroValorOferta=".$_GET['filtroValorOferta'];
}

if(isset($_GET['filtroUsuario'])) {
	$filtro['ugo_ug_email'] = $_GET['filtroUsuario'];
	$varsel .= "&filtroUsuario=".$_GET['filtroUsuario'];
}

if(isset($_GET['filtroStatus'])) {
	$filtro['ugo_status'] = $_GET['filtroStatus'];
	$varsel .= "&filtroStatus=".$_GET['filtroStatus'];
}

$errorDate = "";

if(isset($_GET['filtroDataAdesaoIni']) && $_GET['filtroDataAdesaoIni'] != '') { 
	$filtro['ugo_data_adesao_oferta_ini'] = $_GET['filtroDataAdesaoIni']; 
	$varsel .= "&filtroDataAdesaoIni=".$_GET['filtroDataAdesaoIni'];
	
	$dtIncIni = explode('/', $_GET['filtroDataAdesaoIni']);
	if(!checkdate($dtIncIni[1], $dtIncIni[0], $dtIncIni[2])) { $errorDate .= '<li><span style="color: #FC021F;">Data de Adesão Inicial e Inválida! Entre com Outra Data.</span></li>'; }
}

if(isset($_GET['filtroDataAdesaoFim']) && $_GET['filtroDataAdesaoFim'] != '') { 
	$filtro['ugo_data_adesao_oferta_fim'] = $_GET['filtroDataAdesaoFim']; 
	$varsel .= "&filtroDataAdesaoFim=".$_GET['filtroDataAdesaoFim'];
	
	$dtIncFim = explode('/', $_GET['filtroDataAdesaoFim']);
	if(!checkdate($dtIncFim[1], $dtIncFim[0], $dtIncFim[2])) { $errorDate .= '<li><span style="color: #FC021F;">Data de Adesão Final e Inválida! Entre com Outra Data.</span></li>'; }
}

/* ORDER BY */

/** Coluna ID **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='ID_OFERTA') {
	$orderBy .= " ug_ofertas.ugo_id ";

	if(isset($_GET['filtroOrderByTipoIdOferta']) && $_GET['filtroOrderByTipoIdOferta']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_ID_OFERTA = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoIdOferta'] = "DESC";
		$varsel .= "&filtroOrderBy=ID_OFERTA&filtroOrderByTipoIdOferta=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_ID_OFERTA = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoIdOferta'] = "ASC";
		$varsel .= "&filtroOrderBy=ID_OFERTA&filtroOrderByTipoIdOferta=DESC";
	}
}


/** Coluna Canal de Ofertas **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='CANAL_OFERTA') {
	$orderBy .= " ug_ofertas_canal.ugoc_descricao ";

	if(isset($_GET['filtroOrderByTipoCanalOferta']) && $_GET['filtroOrderByTipoCanalOferta']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_CANAL_OFERTA = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoCanalOferta'] = "DESC";
		$varsel .= "&filtroOrderBy=CANAL_OFERTA&filtroOrderByTipoCanalOferta=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_CANAL_OFERTA = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoCanalOferta'] = "ASC";
		$varsel .= "&filtroOrderBy=CANAL_OFERTA&filtroOrderByTipoCanalOferta=DESC";
	}
}


/** Coluna ID da Codigo da Oferta **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='CODIGO_OFERTA') {
	$orderBy .= " ug_ofertas.ugo_oferta_id ";

	if(isset($_GET['filtroOrderByTipoCodigoOferta']) && $_GET['filtroOrderByTipoCodigoOferta']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_CODIGO_OFERTA = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoCodigoOferta'] = "DESC";
		$varsel .= "&filtroOrderBy=CODIGO_OFERTA&filtroOrderByTipoCodigoOferta=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_CODIGO_OFERTA = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoCodigoOferta'] = "ASC";
		$varsel .= "&filtroOrderBy=CODIGO_OFERTA&filtroOrderByTipoCodigoOferta=DESC";
	}
}


/** Coluna ID da Transacao **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='TRANSACAO_ID') {
	$orderBy .= " ug_ofertas.ugo_transaction_id ";

	if(isset($_GET['filtroOrderByTipoTransacaoId']) && $_GET['filtroOrderByTipoTransacaoId']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_TRANSACAO_ID = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoTransacaoId'] = "DESC";
		$varsel .= "&filtroOrderBy=TRANSACAO_ID&filtroOrderByTipoTransacaoId=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_TRANSACAO_ID = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoTransacaoId'] = "ASC";
		$varsel .= "&filtroOrderBy=TRANSACAO_ID&filtroOrderByTipoTransacaoId=DESC";
	}
}

/** Coluna Valor **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='VALOR') {
	$orderBy .= " ug_ofertas.ugo_valor_credito ";

	if(isset($_GET['filtroOrderByTipoValor']) && $_GET['filtroOrderByTipoValor']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_VALOR = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoValor'] = "DESC";
		$varsel .= "&filtroOrderBy=VALOR&filtroOrderByTipoValor=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_VALOR = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoValor'] = "ASC";
		$varsel .= "&filtroOrderBy=VALOR&filtroOrderByTipoValor=DESC";
	}
}

/** Coluna Usuario Gamer **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='USUARIO_GAMER') {
	$orderBy .= " ug_ofertas.ugo_ug_email ";

	if(isset($_GET['filtroOrderByTipoUsuarioGamer']) && $_GET['filtroOrderByTipoUsuarioGamer']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_USUARIO_GAMER = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoUsuarioGamer'] = "DESC";
		$varsel .= "&filtroOrderBy=USUARIO_GAMER&filtroOrderByTipoUsuarioGamer=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_USUARIO_GAMER = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoUsuarioGamer'] = "ASC";
		$varsel .= "&filtroOrderBy=USUARIO_GAMER&filtroOrderByTipoUsuarioGamer=DESC";
	}
}

/** Coluna Status **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='STATUS') {
	$orderBy .= " ug_ofertas.ugo_status ";

	if(isset($_GET['filtroOrderByTipoStatus']) && $_GET['filtroOrderByTipoStatus']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_STATUS = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoStatus'] = "DESC";
		$varsel .= "&filtroOrderBy=STATUS&filtroOrderByTipoStatus=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_STATUS = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoStatus'] = "ASC";
		$varsel .= "&filtroOrderBy=STATUS&filtroOrderByTipoStatus=DESC";
	}
}

/** Coluna Data Adesao **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='DATA_ADESAO') {
	$orderBy .= " ug_ofertas.ugo_data_adesao_oferta ";

	if(isset($_GET['filtroOrderByTipoDataAdesao']) && $_GET['filtroOrderByTipoDataAdesao']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_DATA_ADESAO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoDataAdesao'] = "DESC";
		$varsel .= "&filtroOrderBy=DATA_ADESAO&filtroOrderByTipoDataAdesao=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_DATA_ADESAO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoDataAdesao'] = "ASC";
		$varsel .= "&filtroOrderBy=DATA_ADESAO&filtroOrderByTipoDataAdesao=DESC";
	}
}

/* Se ORDER BY vazio, usa o default */
if(!isset($_GET['filtroOrderBy']) || $_GET['filtroOrderBy'] =='') {
	$orderBy = "ug_ofertas.ugo_id DESC";
	$varsel  .= "&filtroOrderBy=ID_OFERTA&filtroOrderByTipoIdOferta=DESC";
}

$ofertas = new Ofertas();

if (!$errorDate) 
	$listPaginateOfOffers = $ofertas->getOffersBy($filtro, "", 0, 0);

$totaisGrid = $ofertas->getTotalsBy($filtro);
$totalCredito = $totaisGrid["total_valor_credito"];
$totalOffers = $totaisGrid["total_registros"];
$paginaOffers = $_GET['inicial'];
$limitPaginaOffers = 10;

$listPaginateOfOffers = array();

if (!$errorDate) 
	$listPaginateOfOffers = $ofertas->getOffersBy($filtro, $orderBy, $limitPaginaOffers, $paginaOffers);
				
$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";		
require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
$(function(){
    var optDate = new Object();
    optDate.interval = 10000;

    setDateInterval('filtroDataAdesaoIni','filtroDataAdesaoFim',optDate);

});
</script>
<script type="text/javascript" language="javascript" src="/js/appFunctions.js"></script>
<link rel="stylesheet" href="/css/gamer/estilo_datagrid.css" type="text/css">
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table">
  <tr> 
    <td valign="top">
        <table class="table fontsize-pp txt-preto">
					<tr> 
					    <td>						
							<!-- Filtro DataTables -->
							<div id="filterDataGridAlawar">
								<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get" name="formFiltro" id="formFiltro">
									<input type="hidden" name="locationHref" id="locationHref" value="<?php echo $_SERVER["SCRIPT_NAME"]; ?>">
									<div class="filterLine" style="margin: 0 auto; display: block; padding: 5px; width: 900px; background: #eeeeee; overflow: hidden; clear: both;">
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 200px;margin-left: 5px; ">
											<label style="float: left; display: block; width: 65px; font-weight: bold; margin-top: 3px;">ID Oferta</label>
											<input type="text" name="filtroID" id="filtroID" value="<?php echo $_GET['filtroID']; ?>" style="float: left; display: block; width: 70px; border: 1px solid #ccc;" />
										</div>
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 300px; margin-left: 35px;">
											<label style="float: left; display: block; width: 80px; font-weight: bold; margin-top: 3px;">Canal Oferta</label>
											<input type="text" name="filtroCanalOferta" id="filtroCanalOferta" value="<?php echo $_GET['filtroCanalOferta']; ?>" style="float: left; display: block; width: 200px; border: 1px solid #ccc;" />
										</div>				
										<div class="filterField" style="float: right; display: block; padding: 2px; width: 310px;">
											<label style="float: left; display: block; width: 90px; font-weight: bold; margin-top: 3px;">Código Oferta</label>
											<input type="text" name="filtroCodigoOferta" id="filtroCodigoOferta" value="<?php echo $_GET['filtroCodigoOferta']; ?>" style="float: left; display: block; width: 200px; border: 1px solid #ccc;" />
										</div>				
						
										<div class="filterSepartor" style="float: left; display: block; padding: 2px; width: 890px; border-top: 1px solid #ccc; margin-top: 7px; margin-bottom: 4px;"></div>
						
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 310px; margin-left: 5px;">
											<label style="float: left; display: block; width: 80px; font-weight: bold; margin-top: 3px;">ID Transação</label>
											<input type="text" name="filtroTransacaoID" id="filtroTransacaoID" value="<?php echo $_GET['filtroTransacaoID']; ?>" style="float: left; display: block; width: 200px; border: 1px solid #ccc;" />
										</div>				

										<div class="filterField" style="float: left; display: block; padding: 2px; width: 150px; margin-left: 25px;">
											<label style="float: left; display: block; width: 55px; font-weight: bold; margin-top: 3px;">Valor</label>
											<input type="text" name="filtroValorOferta" id="filtroValorOferta" value="<?php echo $_GET['filtroValorOferta']; ?>" style="float: left; display: block; width: 80px; border: 1px solid #ccc;" />
										</div>				

										<div class="filterField" style="float: right; display: block; padding: 2px; width: 325px; overflow: hidden;">										
											<label style="float: left; display: block; width: 80px; font-weight: bold; margin-top: 3px;">Data Adesão</label>
											<input type="text" name="filtroDataAdesaoIni" id="filtroDataAdesaoIni" class="text ui-widget-content ui-corner-all" value="<?php echo $_GET['filtroDataAdesaoIni']; ?>" style="float: left; display: block; width: 80px; color:#6F6F6F; border-color: #B2AEAB; border: 1px solid #ccc; overflow: hidden;" />
																						
											<label style="float: left; display: block; width: 25px; font-weight: bold; margin-top: 3px; text-align: center;"> a </label>
											<input type="text" name="filtroDataAdesaoFim" id="filtroDataAdesaoFim" class="text ui-widget-content ui-corner-all" value="<?php echo $_GET['filtroDataAdesaoFim']; ?>" style="float: left; display: block; width: 80px; color:#6F6F6F; border-color: #B2AEAB; border: 1px solid #ccc; overflow: hidden;" />										
										</div>																																																				
										
										<div class="filterSepartor" style="float: left; display: block; padding: 2px; width: 890px; border-top:1px solid #ccc; margin-top: 7px; margin-bottom: 4px;"></div>

										<div class="filterField" style="float: left; display: block; padding: 2px; width: 350px; margin-left: 5px;">
											<label style="float: left; display: block; width: 130px; font-weight: bold; margin-top: 5px;">E-mail Usuário Gamer</label>
											<input type="text" name="filtroUsuario" id="filtroUsuario" value="<?php echo $_GET['filtroUsuario']; ?>" style="float: left; display: block; width: 200px; border: 1px solid #ccc;" />
										</div>				

										<div class="filterField" style="float: left; display: block; padding: 2px; width: 310px; margin-left: 25px;">
											<label style="float: left; display: block; width: 50px; font-weight: bold; margin-top: 5px;">Status</label>
											<select name="filtroStatus" id="filtroStatus" style="float: left; display: block; width: 200px; border: 1px solid #ccc; height: auto; padding:2px;" class="text ui-widget-content ui-corner-all">
												<option value="">-- Todos os Status --</option>
												<?php foreach ($ofertas->getOfferStatus() as $status): ?>
													<option value="<?php echo $status['ugo_status_id']; ?>" <?php echo ($status['ugo_status_id'] == $_GET['filtroStatus'] ? ' selected' : '') ?>><?php echo $status['descricao']; ?></option>
												<?php endforeach; ?>
											</select>
										</div>				
										
										<div class="filterSepartor" style="float: left; display: block; padding: 2px; width: 890px; border-top:1px solid #ccc; margin-top: 7px; margin-bottom: 4px;"></div>
												
										<div class="filterField" style="float: right; display: block; width: auto; text-align: center; ">
											<button type="submit" name="btnBuscar" id="btnBuscar" value="Buscar" class="ui-widget ui-button" style="background: #6b91b8; color: #FFFFFF; padding: 0;">Buscar</button>
											<button type="submit" name="btnLimpar" id="btnLimpar" value="Limpar Campos" class="ui-widget ui-button" style="background: #6b91b8; color: #FFFFFF; padding: 0;">Limpar</button>
										</div>																																
									</div>		
								</form>
							</div>
							<!-- /Filtro DataTables -->
						</td>
					  </tr>
					  <tr>
						 <td>
							<div class="dataGridPaginationTotais">	
							<?php
								if($totalOffers) echo '<label id="total_registros">Total de Jogos Cadastrados: <strong>'.$totalOffers.'</strong></label>';										
								if($totalCredito) echo '<label>Total Geral Créditos (EPP$): <strong>'.$totalCredito.'</strong></label>';
							?>
							</div>							 
					  	 </td>
					  </tr>	 
					  <tr>
						 <td>
							<!-- DataTables -->
							<div class="dataGridAlawar">							
								<table id="dataGridGames">	
									<thead>
										<tr>
											<th style="width:40px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=ID_OFERTA&filtroOrderByTipoIdOferta=".($_GET['filtroOrderByTipoIdOferta']=='DESC' ? 'DESC' : 'ASC'); ?>">ID <?php echo $imgOrderTipo_ID_OFERTA; ?></a></th>
											<th style="width:150px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=CANAL_OFERTA&filtroOrderByTipoCanalOferta=".($_GET['filtroOrderByTipoCanalOferta']=='DESC' ? 'DESC' : 'ASC'); ?>">Canal Oferta <?php echo $imgOrderTipo_CANAL_OFERTA; ?></a></th>
											<th style="width:50px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=CODIGO_OFERTA&filtroOrderByTipoCodigoOferta=".($_GET['filtroOrderByTipoCodigoOferta']=='DESC' ? 'DESC' : 'ASC'); ?>">Código Oferta <?php echo $imgOrderTipo_CODIGO_OFERTA; ?></a></th>
											<th style="width:350px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=TRANSACAO_ID&filtroOrderByTipoTransacaoId=".($_GET['filtroOrderByTipoTransacaoId']=='DESC' ? 'DESC' : 'ASC'); ?>">ID Transação <?php echo $imgOrderTipo_TRANSACAO_ID; ?></a></th>
											<th style="width:50px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=VALOR&filtroOrderByTipoValor=".($_GET['filtroOrderByTipoValor']=='DESC' ? 'DESC' : 'ASC'); ?>">Valor <?php echo $imgOrderTipo_VALOR; ?></a></th>
											<th style="width:230px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=USUARIO_GAMER&filtroOrderByTipoUsuarioGamer=".($_GET['filtroOrderByTipoUsuarioGamer']=='DESC' ? 'DESC' : 'ASC'); ?>">Usuário <?php echo $imgOrderTipo_USUARIO_GAMER; ?></a></th>
											<th style="width:180px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=STATUS&filtroOrderByTipoStatus=".($_GET['filtroOrderByTipoStatus']=='DESC' ? 'DESC' : 'ASC'); ?>">Status <?php echo $imgOrderTipo_STATUS; ?></a></th>
											<th style="width:80px;text-align: center;"><a style="*float: left; *display: block; *margin-left:28px;" href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=DATA_ADESAO&filtroOrderByTipoDataAdesao=".($_GET['filtroOrderByTipoDataAdesao']=='DESC' ? 'DESC' : 'ASC'); ?>"><nobr>Data Adesão</nobr> <?php echo $imgOrderTipo_DATA_ADESAO; ?></a></th>		
										</tr>			
									</thead>
									<tbody>
									<?php foreach ($listPaginateOfOffers as $userOffers): ?>
										<tr>
											<td align="center"><?php echo $userOffers['ugo_id']; ?></td>
											<td><nobr><?php echo $userOffers['ugoc_descricao']; ?></nobr></td>
											<td align="center"><?php echo $userOffers['ugo_oferta_id']; ?></td>
											<td align="center"><nobr><?php echo $userOffers['ugo_transaction_id']; ?></nobr></td>
											<td align="center"><?php echo $userOffers['ugo_valor_credito']; ?></td>
											<td align="center"><?php echo $userOffers['ugo_ug_email']; ?></td>			
											<td align="center"><?php echo $userOffers['descricao']; ?></td>
											<td align="center"><nobr><?php echo $userOffers['ugo_data_adesao_oferta']; ?></nobr></td>
										</tr>
									<?php 	

											$subTotalCredito += $userOffers['ugo_valor_credito']; 
										
										  endforeach;

									if (!$listPaginateOfOffers) {
											
									?>						
										<tr>
											<td colspan="8">
												<?php 
													if($errorDate) 
														echo "<ul style=\"margin-top: 5px;\">".$errorDate."</ul>";
													else 
														echo "Nenhuma Oferta Encontrada.";	
												?>												
											</td>	
										</tr>												
									<?php 							
						
									}
									else {
										/* Adiciona o label com o Sub-Total de Moedas */
										if($subTotalCredito) {																					
											echo "<script>$(function() { $('#total_registros').after('<label id=\"subtotal_pagina\">Sub-Total Créditos (EPP$): <strong>".$subTotalCredito."</strong></label>'); });</script>";
										}
									?>
										<tr style="background-color: #eeeeee;">
											<td colspan="4"><label style="float:right; display: block;">Sub-Total (EPP$)</label></td>
											<td><strong><?php echo $subTotalCredito; ?></strong></td>
											<td colspan="3"></td>											
										</tr>
										<tr style="background-color: #eeeeee;">
											<td colspan="4"><label style="float: right; display: block;">Total Geral (EPP$): </label></td>
											<td><strong><?php echo $totalCredito; ?> </strong></td>
											<td colspan="3"></td>
										</tr>																														
									<?php } ?>
									</tbody>
								</table>
							</div>
							<!-- /DataTables -->
						 </td>				  
					  </tr>
					  <?php 															
					  					  
					  // functions.php no PATH -> /E-Prepag/www/web/incs/functions.php					
					  paginacao_query($paginaOffers, $totalOffers, $limitPaginaOffers, 6, $img_anterior, $img_proxima, nome_arquivo($PHP_SELF), 1, 10, "", $varsel);							
					  ?>							  
				</table>
    </td>
  </tr>
</table>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>		
</body>
</html>
