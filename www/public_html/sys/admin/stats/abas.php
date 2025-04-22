<?php
session_start();

//session_register("script");
$_SESSION['script']	=	$_GET['script'];
$script				=	$_SESSION['script'];
//echo $script.": Script";

//session_register('dd_operadora');
$_SESSION['dd_operadora']	=	$_GET['dd_operadora'];
$dd_operadora				=	$_SESSION['dd_operadora'];

//session_register('dd_mode');
$_SESSION['dd_mode']	=	$_GET['dd_mode'];
$dd_mode				=	$_SESSION['dd_mode'];

if(!empty($_GET['button'])) {
	//session_register('button');
	$_SESSION['button']	=	$_GET['button'];
}

if($_GET['script'] == "POS_stats_abas.php"){
    $_SERVER['SCRIPT_NAME'] = "/sys/admin/stats/abas.php?script=POS_stats_abas.php";
    
}else if($_GET['script'] == "Money_stats_abas.php"){
    $_SERVER['SCRIPT_NAME'] = "/sys/admin/stats/abas.php?script=Money_stats_abas.php";
    
}else if($_GET['script'] == "MoneyEx_stats_abas.php"){
    $_SERVER['SCRIPT_NAME'] = "/sys/admin/stats/abas.php?script=MoneyEx_stats_abas.php";
    
}else if($_GET['script'] == "LHMoney_stats_abas.php"){
    $_SERVER['SCRIPT_NAME'] = "/sys/admin/stats/abas.php?script=LHMoney_stats_abas.php";
    
}else if($_GET['script'] == "Cartoes_stats_abas.php"){
    $_SERVER['SCRIPT_NAME'] = "/sys/admin/stats/abas.php?script=Cartoes_stats_abas.php";
    
}

require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";

//require_once("../../incs/configuracao.php");
//require_once("../../connections/connect.php");	
//require_once("../../incs/header.php");
//require_once("../../incs/security.php");	
//require_once("../../incs/functions.php");
//require_once("../../incs/languages.php");

//echo '<pre>';
//print_r($_SESSION);
//print_r($_GET);
//echo '</pre><hr>';
//echo "ABAS dd_operadora: ".$dd_operadora."<br>";
//echo "ABAS dd_mode: ".$dd_mode."<br>";


if($script == 'POS_stats_abas.php') {
	//echo "Entrou n IF<br>";
	$projetonome = LANG_HOME_ITEM_SALES_STATS_POS;
	
	$abanome[] = LANG_STATISTICS_FOR_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_WEEK_DAY;
	$abanome[] = LANG_STATISTICS_FOR_DAY;
	$abanome[] = LANG_STATISTICS_TOTALS;
	$abanome[] = 'Publisher';
	$abanome[] = LANG_STATISTICS_FOR_PUBLISHER_THIS_MONTH_1;
	$abanome[] = LANG_STATISTICS_FOR_GAME;
	$abanome[] = LANG_STATISTICS_FOR_STATE;
	$abanome[] = LANG_STATISTICS_FOR_CITY;
	$abanome[] = LANG_STATISTICS_FOR_TYPE;
	$abanome[] = LANG_STATISTICS_FOR_ESTABLISHMENT;
	$abanome[] = LANG_STATISTICS_FOR_LAST_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_LAST_WEEK;
	$abanome[] = LANG_TOTAL_ESTABLISHMENT;
	$abanome[] = LANG_TOTAL_ESTABLISHMENT_MONTH;
}

if($script == 'Money_stats_abas.php') {
	$projetonome = LANG_HOME_ITEM_SALES_STATS_MONEY;

 	$abanome[] = LANG_STATISTICS_FOR_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_WEEK_DAY;
	$abanome[] = LANG_STATISTICS_FOR_DAY;
	$abanome[] = LANG_STATISTICS_FOR_GAME;
	$abanome[] = LANG_STATISTICS_FOR_GAME_THIS_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_STATE;
	$abanome[] = LANG_STATISTICS_FOR_CITY;
	$abanome[] = LANG_STATISTICS_FOR_USER;
//	$abanome[] = LANG_STATISTICS_FOR_LAST_MONTH;
//	$abanome[] = LANG_STATISTICS_FOR_LAST_WEEK;
}

if($script == 'MoneyEx_stats_abas.php') {
	$projetonome = LANG_HOME_ITEM_SALES_STATS_MONEY_EXPRESS;
	
 	$abanome[] = LANG_STATISTICS_FOR_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_WEEK_DAY;
	$abanome[] = LANG_STATISTICS_FOR_DAY;
	$abanome[] = LANG_STATISTICS_FOR_GAME;
	$abanome[] = LANG_STATISTICS_FOR_GAME_THIS_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_STATE;
	$abanome[] = LANG_STATISTICS_FOR_CITY;
	$abanome[] = LANG_STATISTICS_FOR_USER;
//	$abanome[] = LANG_STATISTICS_FOR_LAST_MONTH;
//	$abanome[] = LANG_STATISTICS_FOR_LAST_WEEK;
}

if($script == 'Site_stats_abas.php') {
	$projetonome = LANG_HOME_ITEM_SALES_STATS_SITE;
	
 	$abanome[] = LANG_STATISTICS_FOR_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_WEEK_DAY;
	$abanome[] = LANG_STATISTICS_FOR_DAY;
	$abanome[] = LANG_STATISTICS_FOR_GAME;
	$abanome[] = LANG_STATISTICS_FOR_GAME_THIS_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_STATE;
	$abanome[] = LANG_STATISTICS_FOR_CITY;
	$abanome[] = LANG_STATISTICS_FOR_USER;	
	$abanome[] = LANG_STATISTICS_FOR_LAST_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_LAST_WEEK;	
}

if($script == 'LHMoney_stats_abas.php') {
	$projetonome = LANG_HOME_ITEM_SALES_STATS_LH_MONEY;
	
 	$abanome[] = LANG_STATISTICS_FOR_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_WEEK_DAY;
	$abanome[] = LANG_STATISTICS_FOR_DAY;
	$abanome[] = LANG_STATISTICS_FOR_GAME;
	$abanome[] = LANG_STATISTICS_FOR_GAME_THIS_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_STATE;
	$abanome[] = LANG_STATISTICS_FOR_CITY;
	$abanome[] = 'Lan-Houses';		
	$abanome[] = LANG_STATISTICS_FOR_USER;	
	//$abanome[] = LANG_STATISTICS_FOR_LAST_MONTH;
	//$abanome[] = LANG_STATISTICS_FOR_LAST_WEEK;		
}

if($script == 'Cartoes_stats_abas.php') {
	$projetonome = LANG_HOME_ITEM_SALES_STATS_CARDS;
	
 	//$abanome[] = LANG_STATISTICS_FOR_MONTH;
	$abanome[] = LANG_STATISTICS_FOR_WEEK_DAY;
	$abanome[] = LANG_STATISTICS_FOR_DAY;
	$abanome[] = 'Publisher';
	//$abanome[] = LANG_STATISTICS_FOR_PUBLISHER_THIS_MONTH_1;
	$abanome[] = LANG_STATISTICS_FOR_GAME;
	$abanome[] = LANG_STATISTICS_FOR_STATE;
	$abanome[] = LANG_STATISTICS_FOR_CITY;
	$abanome[] = LANG_STATISTICS_FOR_ESTABLISHMENT;	
	//$abanome[] = 'MU-ONLINE-Estabelecimento';
	//$abanome[] = 'Totais-Vendas-Mes';
	//$abanome[] = LANG_STATISTICS_FOR_LAST_MONTH;
	//$abanome[] = 'Totais-Vendas-Semana';
	//$abanome[] = LANG_STATISTICS_FOR_LAST_WEEK;
			
}
sort($abanome);

if (!empty($_GET['abanomeAux'])){
	$abanomeTMP = $_GET['abanomeAux'];
	//echo $abanomeTMP."<br>";
	for($i=0; $i<count($abanome);$i++){
		//echo $abanomeTMP."<br>";
		if($abanomeTMP===$abanome[$i]){
			$abanomeTMP = $i;
		}
	}
	//echo $abanomeTMP."<br>";
}
else {
	$abanomeTMP = 0;
}
//echo $abanomeTMP."<br>";
//print_r($abanome);
//echo '<hr>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="/css/jquery.ui.all.css">
<!--link rel="stylesheet" href="css/demos.css"-->
<link rel="stylesheet" href="/includes/tablesorter/docs/css/jq.css" type="text/css" media="print, projection, screen" />
<link rel="stylesheet" href="/includes/tablesorter/themes/blue/style.css" type="text/css" media="print, projection, screen" />
<script src="/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="/js/jquery.ui.core.js"></script> 
<script src="/js/jquery.ui.widget.js"></script> 
<script src="/js/jquery.ui.tabs.js"></script> 
<script type="text/javascript" src="/includes/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="/includes/tablesorter/docs/js/chili/chili-1.8b.js"></script>
<script type="text/javascript" src="/includes/tablesorter/docs/js/docs.js"></script>

<script type="text/javascript">
	<!--
	function MM_showHideLayers() { //v9.0
	  var i,p,v,obj,args=MM_showHideLayers.arguments;
	  for (i=0; i<(args.length-2); i+=3) 
	  with (document) if (getElementById && ((obj=getElementById(args[i]))!=null)) { v=args[i+2];
		if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
		obj.visibility=v; }
	}
	//-->
</script>

<script>
	$(function() {
		$("#tabs").tabs({
			ajaxOptions: {
				beforeSend: function(){
					MM_showHideLayers('tabs','','hide')
					MM_showHideLayers('loader','','show')
				},	
				complete: function() {
					MM_showHideLayers('loader','','hide')
					MM_showHideLayers('tabs','','show')
				},
				success: function() {
					MM_showHideLayers('loader','','hide')
					MM_showHideLayers('tabs','','show')
				},
				error: function( xhr, status, index, anchor ) {
					$( anchor.hash ).html(
						"Não foi possível abrir essa aba. "
						);
				}
									
			}
		});
		$( "#tabs" ).tabs( "option", "cache", true );
		//$( "#tabs" ).tabs( "option", "cache", false );
		$( "#tabs" ).tabs({ selected: <?php echo $abanomeTMP;?> });
		//$( "#tabs" ).tabs({ selected: 0 });
		//$( "#tabs" ).tabs({ selected: 'ui-tabs-<?php if (!empty($_GET['abanomeAux'])) {	echo $_GET['abanomeAux']; }	else { echo $abanome[0];}?>' });
		//$( "#tabs"  ).tabs({ idPrefix: 'ui-tabs-Cidade' });
	});	
</script>



<title>.:: E-Prepag - <?php echo $projetonome;?> ::.</title>
</head>
<body>


<div id="geral" >
	<div id="projetonome" style="border:medium;border:#000; border-style:solid;border-width:1px;text-align:center;background:#0000CC;">
    	<font color="white"><?php echo $projetonome;?></font>
    </div>
    
	<div id="spacer" style="height:5px;">
    </div>

	<div class="demo">
    	<div id='loader' style="visibility:hidden;">
    	  <div align="center">
          	<img src="/sys/imagens/lendo.gif" alt="CARREGANDO DADOS. AGUARDE..." title="CARREGANDO DADOS. AGUARDE..." width="48" height="47" border="0" />
          </div>
    	</div>
<div id="tabs">
			<ul>
	            <!--li><a href="#tabs-1"><?php echo ($abanome[0]);?></a></li-->
				<?php
					//echo "count(abanome): ".count($abanome)."<br>";
					//for ($i = 1; $i <= count($abanome)-1; $i++) {
					for ($i = 0; $i <= count($abanome)-1; $i++) {
						//echo "i: $i<br>";
						//if($i == 0) {
							?>
							
							<?php
						//} else {
							?>
							<li><a href="<?php echo $script;?>?abanome=<?php echo ($abanome[$i]);?>&dd_operadora='<?php echo $dd_operadora;?>'&dd_mode=<?php echo $dd_mode;?>&inicial=<?php echo $_GET['inicial'];?>&range=<?php echo $_GET['range'];?>&ncamp=<?php echo $_GET['ncamp'];?>&ordem=<?php echo $_GET['ordem'];?>&crescente=<?php echo $_GET['crescente'];?>&abanomeOld=<?php echo $_GET['abanomeAux'];?>"><?php echo ($abanome[$i]);?></a></li>
							<?php
						//}
					}
				?>
			</ul>
			<?php
				if (!empty($_GET['abanomeAux'])) {	
					$abanome	= 	$_GET['abanomeAux'];
				}
				else {
					$abanome = $abanome[0];
				}
				//$dd_operadora = $dd_operadora;
				//$dd_mode = $dd_mode;
				//echo "ABAS2 dd_operadora: ".$dd_operadora."<br>";
				//echo "ABAS2 dd_mode: ".$dd_mode."<br>";
				//echo "ABANOME: ".$abanome."<br>";
				//die("AKI");
				//require_once($script);
			?>
			<!--div id="tabs-1">
				<?php
				if (!empty($_GET['abanomeAux'])) {	
					$abanome	= 	$_GET['abanomeAux'];
				}
				else {
					$abanome = $abanome[0];
				}
				$dd_operadora = $dd_operadora;
				$dd_mode = $dd_mode;
				//echo "ABANOME: ".$abanome."<br>";
				//die("AKI");
				//require_once($script);
				?>
			</div-->
		</div>
	</div>
</div>
<center>
<?php
    require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>
</center>   
</body>
</html>