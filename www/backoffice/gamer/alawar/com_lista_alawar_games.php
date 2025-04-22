<?php 

session_start();

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/alawar/config.inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."class/gamer/classAlawarGames.php";
require_once $raiz_do_projeto."class/gamer/classAlawar.php";
require_once $raiz_do_projeto."class/gamer/classSrvUtils.php";
require_once "/www/includes/bourls.php";

$varsel = "";
$filtro = array();			
$orderBy = "";

if($filtroID) { 
	$filtro['pag_id'] = $filtroID; 
	$varsel .= "&filtroID=".$filtroID; 
}

if($filtroNome) { 
	$filtro['pag_name'] = $filtroNome;
	$varsel .= "&filtroNome=".$filtroNome;
}

if($filtroJogoOnline) {
	$filtro['pag_online_game'] = $filtroJogoOnline;
	$varsel .= "&filtroJogoOnline=".$filtroJogoOnline;
}

if($filtroSymbolCode) { 
	$filtro['pag_symbol_code'] = $filtroSymbolCode;
	$varsel .= "&filtroSymbolCode=".$filtroSymbolCode;
}

if($filtroStatus) { 
	$filtro['pag_status'] = $filtroStatus; 
	$varsel .= "&filtroStatus=".$filtroStatus;
}

$errorDate = "";

if($filtroDataInclusaoIni) { 
	$filtro['pag_data_inclusao_ini'] = $filtroDataInclusaoIni; 
	$varsel .= "&filtroDataInclusaoIni=".$filtroDataInclusaoIni;
	
	$dtIncIni = explode('/', $filtroDataInclusaoIni);
	if(!checkdate($dtIncIni[1], $dtIncIni[0], $dtIncIni[2])) { $errorDate .= '<li><span style="color: #FC021F;">Data de Inclusão Inicial e Inválida! Entre com Outra Data.</span></li>'; }
}

if($filtroDataInclusaoFim) { 
	$filtro['pag_data_inclusao_fim'] = $filtroDataInclusaoFim; 
	$varsel .= "&filtroDataInclusaoFim=".$filtroDataInclusaoFim;
	
	$dtIncFim = explode('/', $filtroDataInclusaoFim);
	if(!checkdate($dtIncFim[1], $dtIncFim[0], $dtIncFim[2])) { $errorDate .= '<li><span style="color: #FC021F;">Data de Inclusão Final e Inválida! Entre com Outra Data.</span></li>'; }
}

if($filtroDataAlteracaoIni) { 
	$filtro['pag_data_alteracao_ini'] = $filtroDataAlteracaoIni; 
	$varsel .= "&filtroDataAlteracaoIni=".$filtroDataAlteracaoIni;
	
	$dtAltIni = explode('/', $filtroDataAlteracaoIni);
	if(!checkdate($dtAltIni[1], $dtAltIni[0], $dtAltIni[2])) { $errorDate .= '<li><span style="color: #FC021F;">Data de Alteração Inicial e Inválida! Entre com Outra Data.</span></li>'; }	
}

if($filtroDataAlteracaoFim) { 
	$filtro['pag_data_alteracao_fim'] = $filtroDataAlteracaoFim; 
	$varsel .= "&filtroDataAlteracaoFim=".$filtroDataAlteracaoFim;
	
	$dtAltFim = explode('/', $filtroDataAlteracaoFim);
	if(!checkdate($dtAltFim[1], $dtAltFim[0], $dtAltFim[2])) { $errorDate .= '<li><span style="color: #FC021F;">Data de Alteração Final e Inválida! Entre com Outra Data.</span></li>'; }	
}


/* ORDER BY */

/** Coluna ID **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='ID_JOGO') {
	$orderBy .= " pag_id ";

	if(isset($_GET['filtroOrderByTipoIdJogo']) && $_GET['filtroOrderByTipoIdJogo']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_ID_JOGO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoIdJogo'] = "DESC";
		$varsel .= "&filtroOrderBy=ID_JOGO&filtroOrderByTipoIdJogo=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_ID_JOGO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoIdJogo'] = "ASC";
		$varsel .= "&filtroOrderBy=ID_JOGO&filtroOrderByTipoIdJogo=DESC";
	}
}


/** Coluna Nome Jogo **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='NOME_JOGO') {
	$orderBy .= " pag_name ";

	if(isset($_GET['filtroOrderByTipoNomeJogo']) && $_GET['filtroOrderByTipoNomeJogo']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_NOME_JOGO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoNomeJogo'] = "DESC";
		$varsel .= "&filtroOrderBy=NOME_JOGO&filtroOrderByTipoNomeJogo=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_NOME_JOGO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoNomeJogo'] = "ASC";
		$varsel .= "&filtroOrderBy=NOME_JOGO&filtroOrderByTipoNomeJogo=DESC";
	}
}


/** Coluna Online **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='JOGO_ONLINE') {
	$orderBy .= " pag_online_game ";

	if(isset($_GET['filtroOrderByTipoJogoOnline']) && $_GET['filtroOrderByTipoJogoOnline']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_JOGO_ONLINE = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoJogoOnline'] = "DESC";
		$varsel .= "&filtroOrderBy=JOGO_ONLINE&filtroOrderByTipoJogoOnline=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_JOGO_ONLINE = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoJogoOnline'] = "ASC";
		$varsel .= "&filtroOrderBy=JOGO_ONLINE&filtroOrderByTipoJogoOnline=DESC";
	}
}


/** Coluna Symbol Code **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='SYMBOL_CODE') {
	$orderBy .= " pag_symbol_code ";

	if(isset($_GET['filtroOrderByTipoSymbolCode']) && $_GET['filtroOrderByTipoSymbolCode']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_SYMBOL_CODE = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoSymbolCode'] = "DESC";
		$varsel .= "&filtroOrderBy=SYMBOL_CODE&filtroOrderByTipoSymbolCode=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_SYMBOL_CODE = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoSymbolCode'] = "ASC";
		$varsel .= "&filtroOrderBy=SYMBOL_CODE&filtroOrderByTipoSymbolCode=DESC";
	}
}

/** Coluna Ativo **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='ATIVO') {
	$orderBy .= " pag_status ";

	if(isset($_GET['filtroOrderByTipoAtivo']) && $_GET['filtroOrderByTipoAtivo']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_ATIVO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoAtivo'] = "DESC";
		$varsel .= "&filtroOrderBy=ATIVO&filtroOrderByTipoAtivo=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_ATIVO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoAtivo'] = "ASC";
		$varsel .= "&filtroOrderBy=ATIVO&filtroOrderByTipoAtivo=DESC";
	}
}

/** Coluna Data Inclusao **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='DATA_INCLUSAO') {
	$orderBy .= " pag_data_inclusao ";

	if(isset($_GET['filtroOrderByTipoDataInclusao']) && $_GET['filtroOrderByTipoDataInclusao']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_DATA_INCLUSAO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoDataInclusao'] = "DESC";
		$varsel .= "&filtroOrderBy=DATA_INCLUSAO&filtroOrderByTipoDataInclusao=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_DATA_INCLUSAO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoDataInclusao'] = "ASC";
		$varsel .= "&filtroOrderBy=DATA_INCLUSAO&filtroOrderByTipoDataInclusao=DESC";
	}
}

/** Coluna Data Alteracao **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='DATA_ALTERACAO') {
	$orderBy .= " pag_data_alteracao ";

	if(isset($_GET['filtroOrderByTipoDataAlteracao']) && $_GET['filtroOrderByTipoDataAlteracao']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_DATA_ALTERACAO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoDataAlteracao'] = "DESC";
		$varsel .= "&filtroOrderBy=DATA_ALTERACAO&filtroOrderByTipoDataAlteracao=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_DATA_ALTERACAO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoDataAlteracao'] = "ASC";
		$varsel .= "&filtroOrderBy=DATA_ALTERACAO&filtroOrderByTipoDataAlteracao=DESC";
	}
}

/* Se ORDER BY vazio, usa o default */
if(!isset($_GET['filtroOrderBy'])) {
	$orderBy = "pag_id ASC";
	$varsel  .= "&filtroOrderBy=".$orderBy;
}

$alawarGames = new AlawarGames();

$listAllAlawarGames = array();

if (!$errorDate)
	$listAllAlawarGames = $alawarGames->getGamesBy($filtro, "", 0, 0);

$totalGames = count($listAllAlawarGames);
$paginaGames = $_GET['inicial'];
$limitPaginaGames = 20;

$listPaginateAlawarGames = array();

if (!$errorDate) {
	$listPaginateAlawarGames = $alawarGames->getGamesBy($filtro, $orderBy, $limitPaginaGames, $paginaGames);
}
				
$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";		

?>
	<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
    <script language="javascript">
    $(function(){
        var optDate = new Object();
        optDate.interval = 10000;

        setDateInterval('filtroDataAlteracaoIni','filtroDataAlteracaoFim',optDate);
        setDateInterval('filtroDataInclusaoIni','filtroDataInclusaoFim',optDate);

    });
    </script>
	<script type="text/javascript" language="javascript" src="js/appFunctions.js"></script>
	<style type="text/css">
	
		.dataGridAlawar {
			font: 11px Arial, Helvetica, sans-serif;
			color: #333;
			background-color: #fff;
			width: auto;
			border: 1px solid #ccc;
			margin-top: 5px;	
			clear:  both;
			overflow: hidden;
		}
		
		#filterDataGridAlawar {
			font: 11px Arial, Helvetica, sans-serif;
			margin: 0 auto; 
			display: block; 
			border: 1px solid #ccc; 
			width: 100%; 
			height: auto;
			overflow: hidden;
			background: #eeeeee;			
			clear:  both;
		}
				
		.dataGridPaginationInfo {
			float: left;
			display: block;
			width: 350px;
			color: #5E6972;
			font: 11px Arial, Helvetica, sans-serif;
			margin: 10px 5px 5px 5px;	
		}		
		
		#dataGridGames { 
			border-collapse: collapse;
			width: 100%;	
			overflow: hidden;
			margin: 0 auto;
			display: block;
			font: 11px Arial, Helvetica, sans-serif;
		}
		
		#dataGridGames td, #dataGridGames th { 
			font: 11px "Lucida Grande", Verdana, Arial, Helvetica, sans-serif;
			border: 1px solid #cccccc;
			text-align: center; 
		}
		
		#dataGridGames th { background: #91AEBC; color: #FFFFFF; font-weight: bold;}
		#dataGridGames th a { background: #91AEBC; color: #FFFFFF; font-weight: bold;} 	 	
		#dataGridGames tbody > tr:hover { background: #C9DCE5; }
	
	</style>
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
        <table>
          <tr> 
            <td>
				<table width="894">
					<tr> 
					    <td>						
							<!-- Filtro DataTables -->
							<div id="filterDataGridAlawar">
								<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get" name="formFiltro" id="formFiltro">
									<div class="filterLine" style="margin: 0 auto; display: block; padding: 5px; width: 860px; background: #eeeeee; overflow: hidden; clear: both;">
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 200px;">
											<label style="float: left; display: block; width: 50px; font-weight: bold; margin-top: 6px;">ID Jogo</label>
											<input type="text" name="filtroID" id="filtroID" value="<?php echo $_GET['filtroID']; ?>" style="float: left; display: block; width: 70px;" />
										</div>
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 300px; margin-left: 25px;">
											<label style="float: left; display: block; width: 50px; font-weight: bold; margin-top: 6px;">Nome</label>
											<input type="text" name="filtroNome" id="filtroNome" value="<?php echo $_GET['filtroNome']; ?>" style="float: left; display: block; width: 200px;" />
										</div>				
										<div class="filterField" style="float: right; display: block; padding: 2px; width: 290px;">
											<label style="float: left; display: block; width: 80px; font-weight: bold; margin-top: 6px;">Symbol Code</label>
											<input type="text" name="filtroSymbolCode" id="filtroSymbolCode" value="<?php echo $_GET['filtroSymbolCode']; ?>" style="float: left; display: block; width: 200px;" />
										</div>				
						
										<div class="filterSepartor" style="float: left; display: block; padding: 2px; width: 855px; border-top: 1px solid #ccc; margin-top: 7px; margin-bottom: 4px;"></div>
						
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 155px;">
											<label style="float: left; display: block; width: 55px; font-weight: bold; margin-top: 6px;">Status</label>
											<select style="float: left; display: block; width: 95x;" name="filtroStatus" id="filtroStatus" >
												<option value=""> -- Todos --</option>
												<option value="1" <?php if ($_GET['filtroStatus']=='1') { echo 'selected'; } ?>>Ativo</option>
												<option value="0" <?php if ($_GET['filtroStatus']=='0') { echo 'selected'; } ?>>Inativo</option>
											</select>
										</div>

										<div class="filterField" style="float: left; display: block; padding: 2px; width: 325px; margin-left: 20px;">
											<label style="float: left; display: block; width: 90px; font-weight: bold; margin-top: 6px;">Data Inclusão</label>
											<input type="text" name="filtroDataInclusaoIni" id="filtroDataInclusaoIni" class="text ui-widget-content ui-corner-all" value="<?php echo $_GET['filtroDataInclusaoIni']; ?>" style="float: left; display: block; width: 80px; color:#6F6F6F; border-color: #B2AEAB;" />
											<label style="float: left; display: block; width: 25px; font-weight: bold; margin-top: 6px; text-align: center;"> a </label>
											<input type="text" name="filtroDataInclusaoFim" id="filtroDataInclusaoFim" class="text ui-widget-content ui-corner-all" value="<?php echo $_GET['filtroDataInclusaoFim']; ?>" style="float: left; display: block; width: 80px; color:#6F6F6F; border-color: #B2AEAB;" />
										</div>																																																				
										<div class="filterField" style="float: right; display: block; padding: 2px; width: 320px;">
											<label style="float: left; display: block; width: 90px; font-weight: bold; margin-top: 6px;">Data Alteração</label>
											<input type="text" name="filtroDataAlteracaoIni" id="filtroDataAlteracaoIni" class="text ui-widget-content ui-corner-all" value="<?php echo $_GET['filtroDataAlteracaoIni']; ?>" style="float: left; display: block; width: 80px; color:#6F6F6F; border-color: #B2AEAB;" />
											<label style="float: left; display: block; width: 25px; font-weight: bold; margin-top: 6px; text-align: center;"> a </label>
											<input type="text" name="filtroDataAlteracaoFim" id="filtroDataAlteracaoFim" class="text ui-widget-content ui-corner-all" value="<?php echo $_GET['filtroDataAlteracaoFim']; ?>" style="float: left; display: block; width: 80px; color:#6F6F6F; border-color: #B2AEAB;" />
										</div>

										<div class="filterSepartor" style="float: left; display: block; padding: 2px; width: 855px; border-top:1px solid #ccc; margin-top: 7px; margin-bottom: 4px;"></div>

										<div class="filterField" style="float: left; display: block; padding: 2px; width: 320px;">
											<label style="float: left; display: block; width: 80px; font-weight: bold; margin-top: 6px;">Jogos Online</label>											
											<select style="float: left; display: block; width: 95x;" name="filtroJogoOnline" id="filtroJogoOnline" >
												<option value=""> -- Todos --</option>
												<option value="1" <?php if ($_GET['filtroJogoOnline']=='1') { echo 'selected'; } ?>>Online</option>
												<option value="0" <?php if ($_GET['filtroJogoOnline']=='0') { echo 'selected'; } ?>>Offline</option>
											</select>
											
										</div>

										<div class="filterSepartor" style="float: left; display: block; padding: 2px; width: 855px; border-top:1px solid #ccc; margin-top: 7px; margin-bottom: 4px;"></div>
												
										<div class="filterField" style="float: right; display: block; width: auto; text-align: center; ">
											<button type="submit" name="btnBuscar" id="btnBuscar" value="Buscar" class="btn btn-info btn-sm">Buscar</button>
											<button type="submit" name="btnLimpar" id="btnLimpar" value="Limpar Campos" class="btn btn-info btn-sm">Limpar</button>
										</div>																																
									</div>		
								</form>
							</div>
							<!-- /Filtro DataTables -->
						</td>
					  </tr>
					  <tr>
						 <td>
							<div class="dataGridPaginationInfo">		
								<?php echo "Total de Jogos Cadastrados: <strong>".$totalGames."</strong>"; ?>		
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
											<th style="width:50px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=ID_JOGO&filtroOrderByTipoIdJogo=".($_GET['filtroOrderByTipoIdJogo']=='DESC' ? 'DESC' : 'ASC'); ?>">ID <?php echo $imgOrderTipo_ID_JOGO; ?></a></th>
											<th style="width:310px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=NOME_JOGO&filtroOrderByTipoNomeJogo=".($_GET['filtroOrderByTipoNomeJogo']=='DESC' ? 'DESC' : 'ASC'); ?>">Nome <?php echo $imgOrderTipo_NOME_JOGO; ?></a></th>
											<th style="width:50px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=JOGO_ONLINE&filtroOrderByTipoJogoOnline=".($_GET['filtroOrderByTipoJogoOnline']=='DESC' ? 'DESC' : 'ASC'); ?>">Online <?php echo $imgOrderTipo_JOGO_ONLINE; ?></a></th>
											<th style="width:310px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=SYMBOL_CODE&filtroOrderByTipoSymbolCode=".($_GET['filtroOrderByTipoSymbolCode']=='DESC' ? 'DESC' : 'ASC'); ?>">Symbol Code <?php echo $imgOrderTipo_SYMBOL_CODE; ?></a></th>
											<th style="width:50px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=ATIVO&filtroOrderByTipoAtivo=".($_GET['filtroOrderByTipoAtivo']=='DESC' ? 'DESC' : 'ASC'); ?>">Ativo <?php echo $imgOrderTipo_ATIVO; ?></a></th>
											<th style="width:80px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=DATA_INCLUSAO&filtroOrderByTipoDataInclusao=".($_GET['filtroOrderByTipoDataInclusao']=='DESC' ? 'DESC' : 'ASC'); ?>">Data Inclusão <?php echo $imgOrderTipo_DATA_INCLUSAO; ?></a></th>		
											<th style="width:80px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=DATA_ALTERACAO&filtroOrderByTipoDataAlteracao=".($_GET['filtroOrderByTipoDataAlteracao']=='DESC' ? 'DESC' : 'ASC'); ?>">Data Alteração <?php echo $imgOrderTipo_DATA_ALTERACAO; ?></a></th>
										</tr>			
									</thead>
									<tbody>
									<?php 
									
									foreach ($listPaginateAlawarGames as $alGameID => $alGames) {
										
									?>
										<tr>
											<td align="center"><?php echo $alGameID; ?></td>
											<td><?php echo $alGames['pag_name']; ?></td>
											<td align="center"><?php if ($alGames['pag_online_game']==1) { echo '<img src="/images/gamer/online.png" />'; } else { echo "&nbsp;"; }; ?></td>
											<td align="center"><?php echo $alGames['pag_symbol_code']; ?></td>
											<td align="center"><img src="/images/gamer/<?php echo ($alGames['pag_status']==1 ? 'ativo.png' : 'inativo.png'); ?>" /></td>		
											<td align="center"><?php echo $alGames['pag_data_inclusao']; ?></td>
											<td align="center"><?php echo $alGames['pag_data_alteracao']; ?></td>
										</tr>
									<?php 
									
									}
									
									if (!$listPaginateAlawarGames) {
											
									?>						
										<tr>
											<td colspan="6">
												<?php 
													if($errorDate) {
														echo "<ul style=\"margin-top: 5px;\">".$errorDate."</ul>";
													}	
													else { 
														echo "Nenhuma Oferta Encontrada.";	
													} 
												?>												
											</td>	
										</tr>												
									<?php 							
						
									}				
									?>							
									</tbody>
								</table>
							</div>
							<!-- /DataTables -->
						 </td>				  
					  </tr>
					  <?php 															
					  
					  

					  // functions.php no PATH -> /E-Prepag/www/web/incs/functions.php					
					  paginacao_query($paginaGames, $totalGames, $limitPaginaGames, 6, $img_anterior, $img_proxima, nome_arquivo($PHP_SELF), 1, 10, "", $varsel);
							
					  ?>							  
				</table>
			</td>
          </tr>
		</table>
    </td>
  </tr>
</table>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>		
</body>
</html>
