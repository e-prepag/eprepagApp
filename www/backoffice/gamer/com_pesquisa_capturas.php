<?php 
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

	set_time_limit ( 3000 ) ;

	$time_start = getmicrotime();
	
	if(!$ncamp)    $ncamp       = 'data';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 1;

	if($btBrowser)	$BtnSearch  = 1;		// simula uma busca após atualizar dados do browser
	if($btIP2Country)	$BtnSearch  = 1;		

//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}


	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&BtnSearch=1&tf_acessoid=$tf_acessoid";
	$varsel .= "&tf_data=$tf_data&tf_localid=$tf_localid&tf_canalid=$tf_canalid&tf_origem=$tf_origem&tf_origemid=$tf_origemid";
	$varsel .= "&tf_platform=$tf_platform&tf_browser=$tf_browser&tf_version=$tf_version&tf_country=$tf_country";

/*
	//Capturas ASP sem browser: tem 'http_user_agent' mas não tem 'browser_platform', 'browser_browser', 'browser_version' 
	$msg_browser = "";
	if(isset($btBrowser) && false){
		include "C:/Sites/E-Prepag/www/web/prepag2/incs/inc_Browser.php";

		$msg_browser = "Processa browser<br>";

		$sql  = "select * 
					from tb_CanalAcesso 
					where browser_platform is null and browser_browser is null and browser_version is null and (not http_user_agent is null )
					order by data desc";
	//echo "sql: $sql<br>";
		$rs_browser_pending = SQLexecuteQuery($sql);
		if($rs_browser_pending && pg_num_rows($rs_browser_pending)>0) {
			while($rs_browser_pending_row = pg_fetch_array($rs_browser_pending)) {
				$browser = new Browser($rs_browser_pending_row['http_user_agent']);				
				$sql = "update tb_CanalAcesso set browser_platform='".$browser->getPlatform()."', browser_browser='".$browser->getBrowser()."', browser_version='".$browser->getVersion()."' where acessoid=".$rs_browser_pending_row['acessoid'].";";
//echo $sql."<br>";
				$rs_browser_pending2 = SQLexecuteQuery($sql);
			}				
			$msg_browser .= "Registros processados com sucesso<br>";
		} else {
			$msg_browser .= "Não foram encontrados registros sem cadastro de browser<br>";

		}
	}
*/
	// Passou para Tarefa Automática de conciliação (cada minuto)
	// Aqui apenas atualiza os registros mais recentes
	if(1 || isset($btIP2Country)){
		$sql = "update tb_CanalAcesso a set 
				country_code = 
				(SELECT country_code FROM ip2c 
				 WHERE (
						SELECT (((elements[1]::bigint * 256) + elements[2]::bigint) * 256 + elements[3]::bigint) * 256 + elements[4]::bigint as ip_long	
						FROM (
						  SELECT  string_to_array(ip, '.') as elements 
						  FROM tb_CanalAcesso b
						  where a.acessoid = b.acessoid 
						) t 
					) BETWEEN begin_ip_num AND end_ip_num
				) 
			where country_code='';";
		$rs_ip2country = SQLexecuteQuery($sql);
	}
    
    $computer = checkIP();
    
    if($computer) {
        if($computer["COMPUTERNAME"] == 'VM_DEV' || $computer["COMPUTERNAME"] == 'WWW2-DEV'){
            $bd = "martin";
        } else{
            $bd = "epp_test";
        }
    }
    else {
		require_once "/www/includes/load_dotenv.php";
        $bd = getenv('DB_BANCO');
    }
    
	// Calcula tamanho do BD no disco, da tabela e nrows
	$sql  = "SELECT pg_size_pretty(pg_database_size('".$bd."')) as db_size, pg_size_pretty(pg_total_relation_size('tb_CanalAcesso')) as table_size, pg_size_pretty(pg_relation_size('tb_CanalAcesso')) as table_size_noindex, (select count(*) from tb_CanalAcesso) as nrows;";
// echo "sql: $sql<br>";
	$rs_db_size = SQLexecuteQuery($sql);
	if($rs_db_size && pg_num_rows($rs_db_size)>0) {
		$rs_db_size_row = pg_fetch_array($rs_db_size);
		$db_size = $rs_db_size_row['db_size'];
		$table_size = $rs_db_size_row['table_size'];
		$table_size_noindex = $rs_db_size_row['table_size_noindex'];
		$nrows = $rs_db_size_row['nrows'];
	} else {
		$db_size = 0;
		$table_size = 0;
		$table_size_noindex = 0;
		$nrows = 0;
	}

	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//capturas
		//------------------------------------------------------------------
		//Data
		if($msg == "")
			if($tf_data_ini || $tf_data_fim){
				if(verifica_data($tf_data_ini) == 0)	$msg = "A data de inclusão inicial do click é inválida." . PHP_EOL;
				if(verifica_data($tf_data_fim) == 0)	$msg = "A data de inclusão final do click é inválida." . PHP_EOL;
			}
		//opr_codigo
		if($msg == "")
			if($ca_canal_codigo){
			
				if(!is_numeric($ca_opr_codigo))
					$msg = "O código do canal deve ser numérico." . PHP_EOL;
			}

		//opr_codigo
		if($msg == "")
			if($ca_localid){
			
			}

		//Busca capturas
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){

			$filtro = array();
			if($tf_data_ini && $tf_data_fim) {
				$filtro['dataMin'] = $tf_data_ini;
				$filtro['dataMax'] = $tf_data_fim;
			}
			if($tf_localid) 		$filtro['localid'] = $tf_localid;
			if($tf_canalid) 		$filtro['canalid'] = $tf_canalid;
			if($tf_origem)	 		$filtro['origem']  = $tf_origem;
			if($tf_origemid) 		$filtro['origemid']  = $tf_origemid;

//echo "".$tf_platform.", ".$tf_browser.", ".$tf_version."<br>";


			if($tf_platform) 		$filtro['platform']  = $tf_platform;
			if($tf_browser)	 		$filtro['browser']  = $tf_browser;
			if($tf_version)	 		$filtro['version']  = $tf_version;
			if($tf_country)	 		$filtro['country']  = $tf_country;

			$rs_capturas = null;
			$ret = obter($filtro, null, $rs_capturas);
			if($ret != "") $msg = $ret;
			else {
//				$total_table = pg_num_rows($rs_capturas);
				$rs_capturas_row = pg_fetch_array($rs_capturas);
				$total_table = $rs_capturas_row['n'];

				if($total_table == 0) {
					$msg = "Nenhum click encontrado." . PHP_EOL;
				} else {
					//Ordem
					$orderBy = $ncamp;
					if($ordem == 1){
						$orderBy .= " desc ";
						$img_seta = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
					} else {
						$orderBy .= " asc ";
						$img_seta = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
					}
			
					$orderBy .= " limit ".$max; 
					$orderBy .= " offset ".$inicial;
				
					$ret = obter($filtro, $orderBy, $rs_capturas);
					if($ret != "") $msg = $ret;
					else {
				
						if($max + $inicial > $total_table)
							$reg_ate = $total_table;
						else
							$reg_ate = $max + $inicial;
					}
				}
			}
				
		}
	}
	
	//Campanhas
	$sql  = "select distinct canalId as name, count(*) as n from tb_CanalAcesso group by canalId order by canalId ";
//echo "sql: $sql<br>";
	$rs_canais = SQLexecuteQuery($sql);

	//Locais
	$sql  = "select distinct LocalId as name, count(*) as n from  tb_CanalAcesso group by LocalId order by name";
//echo "sql: $sql<br>";
	$rs_locais = SQLexecuteQuery($sql);

	//Origens
	// "trim(both ' ' from origemid)"
	$sql  = "select origemid, count(*) as n from tb_CanalAcesso group by origemid order by origemid";
//echo "sql: $sql<br>";
	$rs_origens = SQLexecuteQuery($sql);


	//Capturas ASP sem browser: tem 'http_user_agent' mas não tem 'browser_platform', 'browser_browser', 'browser_version' 
/*	$sql  = "select * 
				from tb_CanalAcesso 
				where browser_platform is null and browser_browser is null and browser_version is null and (not http_user_agent is null )
				order by data desc";
//echo "sql: $sql<br>";
	$rs_browser_pending = SQLexecuteQuery($sql);
*/

	
ob_end_flush();
require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

	$(document).ready(function(){
        
        var optDate = new Object();
        optDate.interval = 10000;

        setDateInterval('tf_data_ini','tf_data_fim',optDate);

	});


	function get_filtros_navegador(tf_platform_val, tf_browser_val, tf_version_val) {

		// send request
		$.ajax({
			type: "POST",
			url: "/ajax/gamer/com_pesquisa_capturas_ajax.php",
			data: 'tf_platform=' + tf_platform_val + '&tf_browser=' + tf_browser_val + '&tf_version=' + tf_version_val +'',
			beforeSend: function(){	
				$("#filtro_navegador").html("<img src='/images/AjaxLoadingQuickQuote.gif' width='44' height='44' border='0' title='Loading'>");

			},
			success: function(txt){
				$("#filtro_navegador").html("");
				if (txt != "ERRO") {
					$("#filtro_navegador").html(txt);

//					$("#tf_platform").val("<?php echo $tf_platform; ?>");
//					$("#tf_browser").val("<?php echo $tf_browser; ?>");
//					$("#tf_version").val("<?php echo $tf_version; ?>");

				}
				else {
					alert("Erro ao procurar filtros de navegador.");
				}			
			},
			error: function(){
				$("#filtro_navegador").html("???");
				alert("Erro no servidor, por favor tente novamente.");
			}
		});
	}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table fontsize-pp txt-preto">
  <tr> 
    <td>
		<form name="form1" method="post" action="com_pesquisa_capturas.php">
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="left" width="90%" class="texto"><?php echo "db_size: <b>".$db_size."</b>, table_size: <b>".$table_size."</b>, table_size_noindex: <b>".$table_size_noindex."</b>, nrows: <b>".number_format($nrows, 0, '.', '.')."</b>"; ?></td>
            <td align="right" width="10%" class="texto"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          </tr>
		</table>
            <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="6" bgcolor="#ECE9D8" class="texto">Capturas</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data de Inclusão</font></td>
            <td class="texto" colspan="3">
              <input name="tf_data_ini" type="text" class="form" id="tf_data_ini" value="<?php echo $tf_data_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_data_fim" type="text" class="form" id="tf_data_fim" value="<?php echo $tf_data_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Campanhas</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Local</td>
            <td>
				<select name="tf_localid" class="form2">
					<option value="" <?php if($tf_localid == "") echo "selected" ?>>Selecione</option>
					<?php if($rs_locais) while($rs_locais_row = pg_fetch_array($rs_locais)){ ?>					
					<option value="<?php echo $rs_locais_row['name']; ?>" <?php if ($tf_localid == $rs_locais_row['name']) echo "selected";?>><?php echo $rs_locais_row["name"]; ?></option>
					<?php } ?>
				</select>
			</td>
            <td width="100" class="texto">Campanha</td>
			<td>
				<select name="tf_canalid" class="form2">
					<option value="" <?php if($tf_canalid == "") echo "selected" ?>>Selecione</option>
					<?php if($rs_canais) while($rs_canais_row = pg_fetch_array($rs_canais)){ ?>					
					<option value="<?php echo $rs_canais_row['name']; ?>" <?php if ($tf_canalid == $rs_canais_row['name']) echo "selected";?>><?php echo "ID: ".$rs_canais_row["name"]." (".$rs_canais_row["n"]." registro".(($rs_canais_row["n"]>1)?"s":"").")"; ?></option>
					<?php } ?>
				</select>
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Origens</td>
            <td>
				<select name="tf_origemid" class="form2">
					<option value="-" <?php if($tf_origemid == "-") echo "selected" ?>>Selecione</option>
					<?php if($rs_origens) while($rs_origens_row = pg_fetch_array($rs_origens)){ ?>					
					<option value="<?php echo trim($rs_origens_row['origemid']); ?>" <?php if ($tf_origemid == trim($rs_origens_row['origemid']) ) echo "selected";?>><?php echo $rs_origens_row["origemid"]; ?></option>
					<?php } ?>
				</select>
			</td>
            <td width="100" class="texto">&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Origem</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">URL (parcial)</td>
            <td>
				<input type="text" name="tf_origem" id="tf_origem" maxlength="50" size="50" value="<?php echo $tf_origem;?>">
			</td>
            <td width="100" class="texto">&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto"><?php // Atualiza Origens IP2Country ?></td>
            <td class="texto">&nbsp;<?php // <input type="submit" name="btIP2Country" id="btIP2Country" value="Atualiza IP2Country" class="botao_search">
				?>
			</td>
            <td width="100" class="texto">&nbsp;Seleciona pais</td>
			<td>&nbsp;
				<select name="tf_country" id="tf_country">
					<option value=""<?php if(($tf_country!="BR") && ($tf_country!="NOTBR")) echo " selected"?>>Todos</option>
					<option value="BR"<?php if(($tf_country=="BR")) echo " selected"?>>Só Brasil</option>
					<option value="US"<?php if(($tf_country=="US")) echo " selected"?>>Só USA</option>
					<option value="NOTBR"<?php if(($tf_country=="NOTBR")) echo " selected"?>>Todos menos Brasil</option>
					<option value="NOTBRUS"<?php if(($tf_country=="NOTBRUS")) echo " selected"?>>Todos menos (Brasil + USA)</option>
				</select>
			</td>
		  </tr>

          <tr bgcolor="#F5F5FB"> 
            <td width="100%" class="texto" colspan="4"><span id="filtro_navegador">Filtros de navegador desabilitados</span></td>
		  </tr>
		
		</table>

            <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td> <?php //   onClick="var stmp = $('#tf_platform').val()+', '+$('#tf_browser').val()+', '+$('#tf_version').val(); alert('tf_platform: '+stmp);" 
			?>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
          <?php if($msg_browser != ""){?><tr class="texto"><td align="center"><br><br><font color="#0000FF"><?php echo $msg_browser?></font></td></tr><?php }?>
		</table>
		</form>

		<?php if($total_table > 0) { ?>
        <table width="894" border="0" cellpadding="0" cellspacing="2">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
				  	<table width="100%" border='0' cellpadding="2" cellspacing="1">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Filtros [plataforma: '<b><?php echo $tf_platform?></b>'], [browser: '<b><?php echo $tf_browser?></b>'], [version: '<b><?php echo $tf_version?></b>']
                        </td>
					  </tr>
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr  bgcolor="#ECE9D8"> 
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=acessoid&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.</a> 
                          <?php if($ncamp == 'acessoid') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=data&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data de Inclusão</font></a>
                          <?php if($ncamp == 'data') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=CanalId&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Campanha</font></a>
                          <?php if($ncamp == 'CanalId') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=LocalId&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Local</font></a>
                          <?php if($ncamp == 'LocalId') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><font class="texto">OrigemId</font></strong></td>
                        <td align="center"><strong><font class="texto">End. IP</font></strong></td>
                        <td align="center"><strong><font class="texto">Pais</font></strong></td>
                        <td align="center"><strong><font class="texto">Plataforma</font></strong></td>
                        <td align="center"><strong><font class="texto">Navegador</font></strong></td>
                        <td align="center"><strong><font class="texto">Versão</font></strong></td>
                      </tr>
					<?php
						$cor_hover = "#CCFFCC";
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						while($rs_capturas_row = pg_fetch_array($rs_capturas)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
							$canalid = $rs_capturas_row['canalid'];
							$localid = $rs_capturas_row['localid'];
							$endip = $rs_capturas_row['ip'];
							$country_code = trim($rs_capturas_row['country_code']);

/*
							if($endip) {
								$endip_long = sprintf("%u",ip2long($endip));
//echo "'$endip' - $endip_long<br>";
								$sqlip = "SELECT country_code,country_name FROM ip2c WHERE $endip_long BETWEEN begin_ip_num AND end_ip_num";
//echo "sqlip: $sqlip<br>";
								$rsip = SQLexecuteQuery($sqlip);
//echo "pg_num_rows(rsip): ".pg_num_rows($rsip)."<br>";
								if($rsip) { // && pg_num_rows($rsip) > 0){
									$rsip_row = pg_fetch_array($rsip);
									 $country_code = trim($rsip_row['country_code']);
									 $country_name = $rsip_row['country_name'];
								} else  {
									$country_code = "*";
									$country_name = "Empty";
								}
							} else  {
								$country_code = "-";
								$country_name = "Empty";
							}
//echo $country_name." (".$country_code.")<br>";
*/

							$sqlip = "SELECT country_name FROM ip2c WHERE country_code = '".$country_code."' limit 1;";
//echo "sqlip: $sqlip<br>";
							$rsip = SQLexecuteQuery($sqlip);
//echo "pg_num_rows(rsip): ".pg_num_rows($rsip)."<br>";
							if($rsip) { 
								$rsip_row = pg_fetch_array($rsip);
								 $country_name = trim($rsip_row['country_name']);
							} else {
								$country_name = "????";
							}
//echo $country_name." (".$country_code.")<br>";

					?>
                      <tr bgcolor="<?php echo $cor1 ?>"> 
                        <td class="texto" width="50" align="center"><?php echo $rs_capturas_row['acessoid'] ?></td>
                        <td class="texto" width="100" align="center"><nobr><?php echo substr($rs_capturas_row['data'],0,19)?></nobr></td>
                        <td class="texto" width="50" align="center"><?php echo $canalid ?></td>
                        <td class="texto" width="50" align="center" title="<?php echo $rs_capturas_row['script_name'] ?>" onMouseOver="this.style.backgroundColor='<?php echo $cor_hover ?>'" onMouseOut="this.style.backgroundColor='<?php echo $cor1 ?>'"><span <?php echo (($rs_capturas_row['script_name']=="")?"color='#FF0000'":"") ?>><?php echo $localid ?></span></td>
                        <td class="<?php echo ((strpos($rs_capturas_row['origemid'], "EMPTY") === false)?"texto":"texto_red") ?>" width="50" align="center"><?php echo $rs_capturas_row['origemid'] ?></td>
                        <td class="texto" width="50" align="center" title="<?php echo (($rs_capturas_row['http_referer'])?$rs_capturas_row['http_referer']:"HTTP_REFERER: Vazio") ?>" onMouseOver="this.style.backgroundColor='<?php echo $cor_hover ?>'" onMouseOut="this.style.backgroundColor='<?php echo $cor1 ?>'"><span <?php echo (($rs_capturas_row['http_referer']=="")?"color='#FF0000'":"") ?>><?php echo $endip ?></span></td>
                        <td class="texto" width="50" align="center"><nobr><?php echo $country_name." (".$country_code.")" ?></nobr></td>

                        <td class="texto" width="30" align="center" title="<?php echo $rs_capturas_row['http_user_agent'] ?>" onMouseOver="this.style.backgroundColor='<?php echo $cor_hover ?>'" onMouseOut="this.style.backgroundColor='<?php echo $cor1 ?>'"><span <?php echo (($rs_capturas_row['browser_platform']=="unknown")?"color='#FF0000'":"") ?>><nobr><?php echo $rs_capturas_row['browser_platform'] ?></nobr></span></td>
                        <td class="texto" width="30" align="center"><span <?php echo (($rs_capturas_row['browser_browser']=="unknown")?"color='#FF0000'":"") ?>><nobr><?php echo $rs_capturas_row['browser_browser'] ?></nobr></span></td>
                        <td class="texto" width="30" align="center"><span <?php echo (($rs_capturas_row['browser_version']=="unknown")?"color='#FF0000'":"") ?>><?php echo $rs_capturas_row['browser_version'] ?></span></td>
                      </tr>
					<?php 	}	?>
                      <tr> 
                        <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
                      </tr>
					<?php paginacao_query($inicial, $total_table, $max, 100, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
                    </table>
				  </td>
                </tr>
              </table>
          <?php  }  ?>
    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
<?php
	function obter($filtro, $orderBy, &$rs){

		$ret = "";
		$filtro = array_map("strtoupper", $filtro);

		$sql = "select ";
		if($orderBy) {
			$sql .= "* ";
		} else {
			$sql .= "count(*) as n ";
		}
		$sql .= " from tb_CanalAcesso ca ";

		if(!is_null($filtro) && $filtro != ""){
		
			if(!is_null($filtro['opr']))

			if(!is_null($filtro['dataMin']) && !is_null($filtro['dataMax'])){
				$filtro['dataMin'] = formata_data_ts($filtro['dataMin'] . " 00:00:00", 1, true, true);
				$filtro['dataMax'] = formata_data_ts($filtro['dataMax'] . " 23:59:59", 1, true, true);
			}			

			$sql .= " where 1=1";
			
//			$sql .= " and (" . (is_null($filtro['dataMin']) || is_null($filtro['dataMax'])?1:0);
//			$sql .= "=1 or ca.data between " . SQLaddFields($filtro['dataMin'], "") . " and " . SQLaddFields($filtro['dataMax'], "") . ")";

			if($filtro['dataMin'] && $filtro['dataMax']) {
				$sql .= " and (" . (is_null($filtro['dataMin']) || is_null($filtro['dataMax'])?1:0);
				$sql .= "=1 or ca.data between '".formata_data($filtro['dataMin'], 1)." 00:00:00' and '".formata_data($filtro['dataMax'], 1)." 23:59:59')";
			}

			$sql .= " and (" . (is_null($filtro['localid'])?1:0);
			$sql .= "=1 or upper(ca.localid) = '" . SQLaddFields($filtro['localid'], "") . "')";

			$sql .= " and (" . (is_null($filtro['canalid'])?1:0);
			$sql .= "=1 or ca.canalid = " . SQLaddFields($filtro['canalid'], "") . ")";

			if(!is_null($filtro['origem'])) {
				$sql .= " and (strpos(upper(http_referer), '".strtoupper($filtro['origem'])."')>0) ";
			}

			if(!is_null($filtro['country'])) {
				if($filtro['country']=="BR") {
					$sql .= " and (country_code='BR') ";
				} elseif($filtro['country']=="US")  {
					$sql .= " and (country_code='US') ";
				} elseif($filtro['country']=="NOTBR")  {
					$sql .= " and (not (country_code='BR')) ";
				} elseif($filtro['country']=="NOTBRUS")  {
					$sql .= " and (not (country_code='BR' or country_code='US')) ";
				}
			}
			if(!is_null($filtro['origemid']) && ($filtro['origemid']!="-")) {
				$sql .= " and (upper(origemid)='".trim(strtoupper($filtro['origemid']))."') ";
			}

			if(!is_null($filtro['platform'])) {
				$sql .= " and (strpos(upper(browser_platform), '".strtoupper($filtro['platform'])."')>0) ";
			}
			if(!is_null($filtro['browser'])) {
				$sql .= " and (strpos(upper(browser_browser), '".strtoupper($filtro['browser'])."')>0) ";
				if(!is_null($filtro['version'])) {
					$sql .= " and (strpos(upper(browser_version), '".strtoupper($filtro['version'])."')>0) ";
				}
			}
		}
		
		if(!is_null($orderBy)) $sql .= " order by " . $orderBy;
		
//echo $sql."<br>\n";
		$rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter Capturas ($sql)." . PHP_EOL;

		return $ret;

	}

?>