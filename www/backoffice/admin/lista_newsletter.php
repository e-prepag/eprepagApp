<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";


$time_start = getmicrotime();

if(!isset($ncamp) || !$ncamp)						$ncamp						= 'un_data_cadastro';
if(!isset($total_table)) $total_table = null;
if(!isset($inicial) || !$inicial)					$inicial					= 0;
if(!isset($range) || !$range)						$range						= 1;
if(!isset($ordem) || !$ordem)						$ordem						= 0;
if(!isset($tf_v_data_inclusao_ini) || !$tf_v_data_inclusao_ini)    $tf_v_data_inclusao_ini     = date("d/m/Y");
if(!isset($tf_v_data_inclusao_fim) || !$tf_v_data_inclusao_fim)    $tf_v_data_inclusao_fim     = date("d/m/Y");
if(isset($btPesquisar) && $btPesquisar=="Pesquisar") {
        $inicial     = 0;
        $range       = 1;
        $total_table = 0;
}
if(!isset($un_email)) $un_email = null;

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 3000; 
$ordem		  = ($ordem == 1)?2:1;
$range_qtde   = $qtde_range_tela;

$varsel = "&btPesquisar=1";
$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim&un_email=$un_email";
?>
<script src="/js/jquery.mask.min.js"></script>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $server_url_ep; ?>/js/global.js"></script>
<script>
    jQuery(function(e){

        var optDate = new Object();
            optDate.interval = 6;
            optDate.minDate = "01/01/2010";

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
<form name="form1" method="post" action="lista_newsletter.php">
<table class="table txt-preto fontsize-pp">
	<tr bgcolor="F0F0F0">
	  <td class="texto" align="center" colspan="2"><b>Lista E-Mails NewsLetter</b></td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="2"><nobr>Data In&iacute;cio: 
		  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="11" maxlength="10">
		  a Data Fim: 
		  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="11" maxlength="10">
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="2"><nobr>E-Mail: 
		  <input name="un_email" type="text" class="form" id="un_email" value="<?php echo $un_email ?>" size="100" maxlength="100"></nobr>
	  </td>
	</tr>
	<tr bgcolor="F5F5FB">
	  <td class="texto" align="center" colspan="2"><input type="submit" name="btPesquisar" id="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
	  </td>
	</tr>
<?php
//echo "<pre>".print_r($_REQUEST,true)."</pre>";
if(!empty($btPesquisar))
{
    $sql = "SELECT 
					un_email,
					to_char(un_data_cadastro,'DD/MM/YYYY HH24:MI:SS') as un_data_cadastro_aux
			FROM usuarios_newsletter";
	if(strlen($un_email))
				$sql_filters[] = "un_email like '%".addslashes($un_email)."%'";
	if(strlen($tf_v_data_inclusao_ini))
				$sql_filters[] = "un_data_cadastro >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($tf_v_data_inclusao_fim))
				$sql_filters[] = "un_data_cadastro <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	if (count($sql_filters) > 0) {
		$sql_aux = implode(" and ", $sql_filters);
		$sql  .= " WHERE ".$sql_aux;
	}

	$rs_newsletter_total = SQLexecuteQuery($sql);
	$total_table = pg_num_rows($rs_newsletter_total);
	if($total_table == 0) {
		echo "Nenhum e-mail encontrado.<br>".PHP_EOL;
	} else {		
		if($max + $inicial > $total_table)
			$reg_ate = $total_table;
		else
			$reg_ate = $max + $inicial;
	}

	//Ordem
	$sql .= " order by ".$ncamp;
	if($ordem == 1){
		$sql .= " desc ";
		$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
	} else {
		$sql .= " asc ";
		$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
	}

	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;
	
	//echo($sql);

	$rs_newsletter = SQLexecuteQuery($sql);
	if(!isset($rs_newsletter) || !($rs_newsletter) || (pg_num_rows($rs_newsletter)==0)) {
		echo "Erro ao consultar informa&ccedil;&otilde;es de emails de newsletter.<br>";
	}
	else {
		?>
		<tr> 
			<td colspan="2" class="texto"> 
			  Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
			</td>
		</tr>
		<tr bgcolor="F0F0F0">
 			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=un_email&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">E-Mail</font></a><?php if($ncamp == 'un_email') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong></td>
			  <td class="texto" align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=un_data_cadastro&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Data de Cadastro</font></a><?php if($ncamp == 'un_data_cadastro') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?> </strong></td>
		</tr>
		<tr bgcolor='#000000'><td colspan='2' height='1'></td></tr>
		<?php
        if(!isset($i))
            $i = null;
        
        if(!isset($irows))
            $irows = null;
            
		while($rs_newsletter_row = pg_fetch_array($rs_newsletter)){ 
			$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
			$irows++;
		?>
		<tr<?php echo $bgcolor?> valign="top">
		  <td class="texto" align="center"><?php echo $rs_newsletter_row['un_email']?></td>
		  <td class="texto" align="center"><?php echo $rs_newsletter_row['un_data_cadastro_aux']?></td>
		</tr>
		<?php	
		}
	}
}
paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); 
?>
</table>
</form>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>