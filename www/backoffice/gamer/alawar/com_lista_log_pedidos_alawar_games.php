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

if(isset($_GET['filtroJogo'])) { 
	$filtro['pal_pag_id'] = $_GET['filtroJogo'];
	$varsel .= "&filtroJogo=".$_GET['filtroJogo'];
}

if(isset($_GET['filtroEmailUser'])) { 
	$filtro['ug_email'] = $_GET['filtroEmailUser'];
	$varsel .= "&filtroEmailUser=".$_GET['filtroEmailUser'];
}

if(isset($_GET['filtroCertificado'])) { 
	$filtro['pal_pa_certificate_id'] = $_GET['filtroCertificado']; 
	$varsel .= "&filtroCertificado=".$_GET['filtroCertificado'];
}

if(isset($_GET['filtroMensagem'])) { 
	$filtro['pal_mensagem_log'] = $_GET['filtroMensagem']; 
	$varsel .= "&filtroMensagem=".$_GET['filtroMensagem'];
}

$errorDate = "";

if(isset($_GET['filtroDataLogIni']) && $_GET['filtroDataLogIni'] != '') { 
	$filtro['pal_data_log_ini'] = $_GET['filtroDataLogIni']; 
	$varsel .= "&filtroDataLogIni=".$_GET['filtroDataLogIni'];
	
	$dtLogIni = explode('/', $_GET['filtroDataLogIni']);
	if(!checkdate($dtLogIni[1], $dtLogIni[0], $dtLogIni[2])) { $errorDate .= '<li><span style="color: #FC021F;">Data Log Inicial e Inválida! Entre com Outra Data.</span></li>'; }	
}

if(isset($_GET['filtroDataLogFim']) && $_GET['filtroDataLogFim'] != '') { 
	$filtro['pal_data_log_fim'] = $_GET['filtroDataLogFim']; 
	$varsel .= "&filtroDataLogFim=".$_GET['filtroDataLogFim'];
	
	$dtLogFim = explode('/', $_GET['filtroDataLogFim']);
	if(!checkdate($dtLogFim[1], $dtLogFim[0], $dtLogFim[2])) { $errorDate .= '<li><span style="color: #FC021F;">Data Log Final e Inválida! Entre com Outra Data.</span></li>'; }
}


/* ORDER BY */

/** Coluna E-Mail USUARIO **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='USUARIO') {
	$orderBy .= " ug_email ";

	if(isset($_GET['filtroOrderByTipoUsuario']) && $_GET['filtroOrderByTipoUsuario']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_USUARIO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoUsuario'] = "DESC";
		$varsel .= "&filtroOrderBy=USUARIO&filtroOrderByTipoUsuario=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_USUARIO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoUsuario'] = "ASC";
		$varsel .= "&filtroOrderBy=USUARIO&filtroOrderByTipoUsuario=DESC";
	}
}

/** Coluna ID USUARIO **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='ID_USUARIO') {
	$orderBy .= " pal_ug_id ";

	if(isset($_GET['filtroOrderByTipoIdUsuario']) && $_GET['filtroOrderByTipoIdUsuario']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_ID_USUARIO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoIdUsuario'] = "DESC";
		$varsel .= "&filtroOrderBy=ID_USUARIO&filtroOrderByTipoIdUsuario=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_ID_USUARIO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoIdUsuario'] = "ASC";
		$varsel .= "&filtroOrderBy=ID_USUARIO&filtroOrderByTipoIdUsuario=DESC";
	}
}

/** Coluna Certificado **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='CERTIFICADO') {
	$orderBy .= " pal_pa_certificate_id ";

	if(isset($_GET['filtroOrderByTipoCertificado']) && $_GET['filtroOrderByTipoCertificado']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_CERTIFICADO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoCertificado'] = "DESC";
		$varsel .= "&filtroOrderBy=CERTIFICADO&filtroOrderByTipoCertificado=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_CERTIFICADO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoCertificado'] = "ASC";
		$varsel .= "&filtroOrderBy=CERTIFICADO&filtroOrderByTipoCertificado=DESC";
	}
}


/** Coluna Mensagem Erro **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='MENSAGEM_ERRO') {
	$orderBy .= " pal_mensagem_log ";

	if(isset($_GET['filtroOrderByTipoMensagemErro']) && $_GET['filtroOrderByTipoMensagemErro']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_MENSAGEM_ERRO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoMensagemErro'] = "DESC";
		$varsel .= "&filtroOrderBy=MENSAGEM_ERRO&filtroOrderByTipoMensagemErro=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_MENSAGEM_ERRO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoMensagemErro'] = "ASC";
		$varsel .= "&filtroOrderBy=MENSAGEM_ERRO&filtroOrderByTipoMensagemErro=DESC";
	}
}


/** Coluna Data Historico **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='DATA_HISTORICO') {
	$orderBy .= " pal_data_log ";

	if(isset($_GET['filtroOrderByTipoDataHistorico']) && $_GET['filtroOrderByTipoDataHistorico']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_DATA_HISTORICO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoDataHistorico'] = "DESC";
		$varsel .= "&filtroOrderBy=DATA_HISTORICO&filtroOrderByTipoDataHistorico=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_DATA_HISTORICO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoDataHistorico'] = "ASC";
		$varsel .= "&filtroOrderBy=DATA_HISTORICO&filtroOrderByTipoDataHistorico=DESC";
	}
}

/* Se ORDER BY vazio, usa o default */
if(!isset($_GET['filtroOrderBy'])) {
	$orderBy = "pal_data_log DESC";
}

$listAllLogs = array();

if (!$errorDate)
	$listAllLogs = AlawarAPI::listLogErrors($filtro,"",0,0);

$totalLogs = count($listAllLogs);
$paginaLogs = $_GET['inicial'];
$limitPaginaLogs = 20;

$listPaginateLogs = array();

if (!$errorDate)
	$listPaginateLogs = AlawarAPI::listLogErrors($filtro, $orderBy, $limitPaginaLogs, $paginaLogs);
				
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

    setDateInterval('filtroDataLogIni','filtroDataLogFim',optDate);

});
</script>
	<script type="text/javascript" language="javascript" src="/js/appFunctions.js"></script>
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
			font: 10px "Lucida Grande", Verdana, Arial, Helvetica, sans-serif;
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
                <table class="table txt-preto">
					<tr> 
					    <td>						
							<!-- Filtro DataTables -->
							<div id="filterDataGridAlawar" style="float: left; display: block; border:1px solid #ccc; width: 100%;background: #eeeeee;">
								<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get" name="formFiltro" id="formFiltroLog">
									<div class="filterLine" style="margin: 0 auto; display: block; padding: 5px; width: 860px; background: #eeeeee; overflow: hidden">
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 360px;">
											<label style="float: left; display: block; width: 40px; font-weight: bold; margin-top: 6px;">Jogo</label>
											<select style="float: left; display: block; width: 300px;" name="filtroJogo" id="filtroJogo" >
												<option value=""> -- Todos os Jogos --</option>
												<?php 
												
												$alawarGames = new AlawarGames();
												$filtro = array();
												$listOfAlawarGames = $alawarGames->getGamesBy($filtro,"pag_name ASC, pag_id ASC",0,0);
												
												foreach ($listOfAlawarGames as $alGameID => $alGame) {							
													echo '<option value="'.$alGameID.'" '.($alGameID==$_GET['filtroJogo'] ? ' selected' : '').'>'.$alGameID.' - '.$alGame['pag_name'].''.($alGame['pag_online_game']==1? ' (online) ' : '').'</option>';
												}
												
												?>						
											</select>					
										</div>				
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 260px; margin-left: 10px;">
											<label style="float: left; display: block; width: 90px; font-weight: bold; margin-top: 6px;">E-Mail Usuário</label>
											<input type="text" name="filtroEmailUser" id="filtroEmailUser" value="<?php echo $_GET['filtroEmailUser']; ?>" style="float: left; display: block; width: 150px;" />
										</div>				
										<div class="filterField" style="float: right; display: block; padding: 2px; width: 200px;">
											<label style="float: left; display: block; width: 70px; font-weight: bold; margin-top: 6px;">Certificado</label>
											<input type="text" name="filtroCertificado" id="filtroCertificado" value="<?php echo $_GET['filtroCertificado']; ?>" style="float: left; display: block; width: 120px;" />
										</div>				
						
										<div class="filterSepartor" style="float: left; display: block; padding: 2px; width: 855px; border-top: 1px solid #ccc; margin-top: 7px; margin-bottom: 4px;"></div>
						
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 290px;">
											<label style="float: left; display: block; width: 100px; font-weight: bold; margin-top: 6px;">Mensagem Log</label>
											<input type="text" name="filtroMensagem" id="filtroMensagem" value="<?php echo $_GET['filtroMensagem']; ?>" style="float: left; display: block; width: 150px;" />
										</div>				
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 310px;margin-left: 35px;">
											<label style="float: left; display: block; width: 60px; font-weight: bold; margin-top: 6px;">Data Log</label>
											<input type="text" name="filtroDataLogIni" id="filtroDataLogIni" class="text ui-widget-content ui-corner-all" value="<?php echo $_GET['filtroDataLogIni']; ?>" style="float: left; display: block; width: 80px; color:#6F6F6F; border-color: #B2AEAB;" />
											
											<label style="float: left; display: block; width: 25px; font-weight: bold; margin-top: 6px; text-align: center;"> a </label>
											<input type="text" name="filtroDataLogFim" id="filtroDataLogFim" class="text ui-widget-content ui-corner-all" value="<?php echo $_GET['filtroDataLogFim']; ?>" style="float: left; display: block; width: 80px; color:#6F6F6F; border-color: #B2AEAB;" />
										</div>
																
										<div class="filterSepartor" style="float: left; display: block; padding: 2px; width: 855px; border-top:1px solid #ccc; margin-top: 7px; margin-bottom: 4px;"></div>
												
										<div class="filterField" style="float: right; display: block; width: auto; text-align: center;">
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
								<?php echo "Total de Jogos Cadastrados: <strong>".$totalLogs."</strong>"; ?>		
							</div>							 
					  	 </td>
					  </tr>	 
					  <tr>
						 <td>
							<!-- DataTables -->
							<div>							
                                <table id="dataGridGames" class="table table-bordered">	
									<thead>
										<tr>
											<th style="width:180px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=USUARIO&filtroOrderByTipoUsuario=".($_GET['filtroOrderByTipoUsuario']=='DESC' ? 'DESC' : 'ASC'); ?>">Usuário <?php echo $imgOrderTipo_USUARIO; ?></a></th>
											<th style="width:50px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=ID_USUARIO&filtroOrderByTipoIdUsuario=".($_GET['filtroOrderByTipoIdUsuario']=='DESC' ? 'DESC' : 'ASC'); ?>">ID Usuário <?php echo $imgOrderTipo_ID_USUARIO; ?></a></th>
											<th style="width:200px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=NOME_JOGO&filtroOrderByTipoNomeJogo=".($_GET['filtroOrderByTipoNomeJogo']=='DESC' ? 'DESC' : 'ASC'); ?>">Jogo <?php echo $imgOrderTipo_NOME_JOGO; ?></a></th>
											<th style="width:90px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=CERTIFICADO&filtroOrderByTipoCertificado=".($_GET['filtroOrderByTipoCertificado']=='DESC' ? 'DESC' : 'ASC'); ?>">Certificado <?php echo $imgOrderTipo_CERTIFICADO; ?></a></th>
											<th style="width:90px; text-align: center;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=MENSAGEM_ERRO&filtroOrderByTipoMensagemErro=".($_GET['filtroOrderByTipoMensagemErro']=='DESC' ? 'DESC' : 'ASC'); ?>">Mensagem Erro <?php echo $imgOrderTipo_MENSAGEM_ERRO; ?></a></th>
											<th style="width:140px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=DATA_HISTORICO&filtroOrderByTipoDataHistorico=".($_GET['filtroOrderByTipoDataHistorico']=='DESC' ? 'DESC' : 'ASC'); ?>">Data Histórico <?php echo $imgOrderTipo_DATA_HISTORICO; ?></a></th>		
										</tr>			
									</thead>
									<tbody>
									<?php 
									
									foreach ($listPaginateLogs as $alLogs) {
										
									?>
										<tr>
											<td><?php echo $alLogs['ug_email']; ?></td>
											<td><?php echo '<a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id='.$alLogs['pal_ug_id'].'" target="_blank">'.$alLogs['pal_ug_id']."</a>"; ?></td>
											<td align="center"><?php echo $alLogs['nome_jogo']; ?></td>
											<td align="center"><?php echo $alLogs['pal_pa_certificate_id']; ?></td>		
											<td align="center"><?php echo $alLogs['pal_mensagem_log']; ?></td>
											<td align="center"><?php echo $alLogs['pal_data_log_format']; ?></td>
										</tr>
									<?php 
									
									}
									
									if (!$listPaginateLogs) {
											
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
					  paginacao_query($paginaLogs, $totalLogs, $limitPaginaLogs, 6, $img_anterior, $img_proxima, nome_arquivo($PHP_SELF), 1, 10, "", $varsel);
							
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
