<?php 
set_time_limit ( 6000 ) ;
ob_start();
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."includes/inc_Pagamentos.php";
require_once $raiz_do_projeto."includes/gamer/func_conta_dez_dias.php";

$time_start = getmicrotime();

if(!$inicial)  $inicial     = 0;
if(!$range)    $range       = 1;
if(!$ordem)    $ordem       = 0;
if($Pesquisar) $total_table = 0;
$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 30; //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;
$registros	  = $max;

if(!$tf_v_data_inclusao_ini) {
	$hoje		= date("d/m/Y");
	$tf_v_data_inclusao_ini = data_menos_n($hoje,730);
	
}	
if(!$tf_v_data_inclusao_fim) $tf_v_data_inclusao_fim = $hoje;
$tf_v_codigo	= $_POST['tf_v_codigo'];
$ug_nome	= strtoupper($_POST['ug_nome']);
$codigo_user	= $_POST['codigo_user'];

if (isset($tf_v_data_inclusao_ini) && isset($tf_v_data_inclusao_fim)) {
	$varse1 .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
}
	
if (isset($ug_nome)){
	$varse1 .= "&ug_nome=$ug_nome";
}
		
if (isset($codigo_user)){
	$varse1 .= "&codigo_user=$codigo_user";
}

$ug_perfil_saldo		= "";
$ug_n					= "";
$ug_n1					= "";
$ug_valor				= "";
$ug_cor_codigo			= "";
$ug_cor_venda_bruta		= "";
$ug_cor_venda_liquida	= "";
//echo $codigo_user."Teste<br>"; 

if ($codigo_user>0){}
	
// desenhando painel -- abaixo 

/////////////////////////////////////////////////////////////////////////////////////////////////////////
// AQUI COMEÇA A QUERY DE RECUPERAR DADOS: //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "select substr(ugc_data_cancelamento::text, 1, 19) as data_cancelamento, ug.ug_id, ug_email, ug_nome
from usuarios_games ug 
	inner join usuarios_games_cancelado ugc on ug.ug_id = ugc.ug_id ";
if (!empty($ug_nome))
	$sbds_aux[] = "upper(ug_nome) LIKE '%" . strtoupper($ug_nome) . "%'";
if (!empty($codigo_user))
	$sbds_aux[] = "ug.ug_id = ". $codigo_user ;
if (!empty($tf_v_data_inclusao_ini))
	$sbds_aux[] = "ugc_data_cancelamento >= to_date('". $tf_v_data_inclusao_ini ." 00:00:00','DD/MM/YYYY HH24:MI:SS')";
if (!empty($tf_v_data_inclusao_fim))
	$sbds_aux[] = "ugc_data_cancelamento <= to_date('". $tf_v_data_inclusao_fim ." 23:59:59','DD/MM/YYYY HH24:MI:SS')";
if (is_array($sbds_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sbds_aux);
}

$res_tmp = SQLexecuteQuery($sql);
if ($res_tmp) {
	$total_table = pg_num_rows($res_tmp);
}

$max_reg = (($inicial + $max)>$total_table)?$total_table:$max;

//echo "total_table: ".$total_table."<br>";

$sql .= " order by data_cancelamento desc ";
$sql .= " limit $max offset $inicial ";

//if(b_IsUsuarioWagner()) { 
//echo "<br><br>(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";
//}

$res = SQLexecuteQuery($sql);
$pagina_titulo = "Usuários Cancelados"; 
require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        
    });
</script>
<style type="text/css">
<!--
.style1 {
	color: #FF0000;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
}
-->
   </style>
   <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
            <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
            <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
        </ol>
    </div>
    <table class="table">
		<tr valign="top" align="center">
		  <td>
		    <form name="form1" method="POST" action="com_pesquisa_usuarios_cancelados.php">
                <table class="table fontsize-pp txt-preto">
					<tr bgcolor="F0F0F0">
					  <td class="texto" align="center" colspan="6"><b>Pesquisa <b><?php 
						echo " (".$total_table." registro"; 
						if($total_table>1) echo "s"; 
						echo ")"?></b></td>
					</tr>
					<tr bgcolor="F5F5FB">
					  <td class="texto" align="center"><div align="right"><b>C&oacute;digo do Usu&aacute;rio: </b></div></td>
					  <td class="texto" align="center"><div align="left">
						<input name="codigo_user" type="text" class="form" id="codigo_user" value="<?php echo $codigo_user?>" size="24" maxlength="7">
					</div></td>
					  <td colspan="3" align="center" class="texto">
						  <b>Per&iacute;odo do Cancelamento</b>
						  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
						  a 
						  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
                      </td>
					  <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-info btn-sm"></td>
					</tr>
					<tr bgcolor="F5F5FB">
					  <td align="center" class="texto"><div align="right"><strong>Nome: </strong></div></td>
					  <td align="center" class="texto"><div align="left"><strong>
					 <input name="ug_nome" type="text" class="form" id="ug_nome" value="<?php echo $ug_nome?>" size="24" maxlength="40">
					  </strong></div></td>
					  <td width="19%" align="left" class="texto"></td>
					  <td width="17%" align="left" class="texto"></td>
					  <td width="17%" align="left" class="texto"></td>
					  <td class="texto" align="center">&nbsp;</td>
				  </tr>
					<tr bgcolor="F5F5FB">
					  <td height="21" align="center" class="texto">&nbsp;</td>
					  <td class="texto" align="center">&nbsp;</td>
					  <td colspan="3" align="center" class="texto">&nbsp;</td>
                      <td class="texto" align="center"><a href="index.php" class="btn btn-sm btn-info pull-right">Voltar</a></td>
				  </tr>
				</table>
			  </form>
                      </tr>
                      
	</tr>
	<tr align="center">
        <td>
			  
            <table class="table fontsize-pp txt-preto">
                <tr <?php echo $fcolor?> class="texto" >
                  <td height="21" colspan="5" align="left" bgcolor="#EEEEEE" class='texto'>Listando de 
                  <?php echo ($inicial +1)?> a <?php echo ($max_reg)?> de <?php echo $total_table?></td>
                </tr>
                <tr class='texto'>
                    <td align="center" bgcolor="#CCCCCC"><strong>Data Cancelamento</strong></td>
                    <td align="center" bgcolor="#CCCCCC" id="res_total2"><strong>Codigo Cliente</strong></td>
                    <td align="center" bgcolor="#CCCCCC" id="res_total2"><strong>Nome Cliente</strong></td>
                    <td align="center" bgcolor="#CCCCCC"><strong>Email</strong></td>
                </tr>
				 <?php	
				/// fechando a query 
	$i_row = 0;
	$total_entrada = 0;
	$total_saida = 0;
	
	while( $info = pg_fetch_array($res) ){
		
		$i_row++;

		///////////////////////////////////////////////////////////////////
		/////////////// ---- SETUP CORES DAS CELULAS -------///////////////
		//////////												///////////
		/////														///////
		///															    ///
		$bgcolor = (($i_row) % 2)?" bgcolor='#E0E0E0'":" bgcolor='#FAFAFE'";
		//																 //
		//////														///////
		/////////////											///////////
		//////////////// ----------- FIM SETUP ------------////////////////
		///////////////////////////////////////////////////////////////////
						
		$nome_gamer_view = $info['ug_nome'];
		$email_gamer_view = $info['ug_email'];
		$data_view  = formata_data_ts($info['data_cancelamento'], 0, true, false);
		$id_view = $info['ug_id'];
		?>
				 <tr <?php echo $bgcolor?>>
				   <td class="texto" align="center"><nobr><?php echo $data_view?></nobr></td>
				   <td align="center" class="texto"><a href="com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				   <?php echo $id_view?>
				   </a></td>
				   <td align="center" class="texto"><a href="com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				   <?php echo $nome_gamer_view?>
				   </a></td>
				   <td align="center" class="texto"><a href="com_usuario_detalhe.php?usuario_id=<?php echo $id_view?>" target="_blank">
				   <?php echo $email_gamer_view?>
				   </a></td>
				 </tr>
	   <?php

	} // fim do while principal

	?>
            </table>
        </td>
	  </tr>
	<tr align="center"><td><table border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
	  <tr>
		<td colspan="20" bgcolor="#FFFFFF" class="texto"></font></td>
		</tr>
	  <tr>
		<td align="center" class="texto">
		</tr>
	</table>
		<br>
	<?php 
		
	$varse1 .= "";
		
	paginacao_query($inicial, $total_table, $max, 50, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varse1);

	?>
		</td>
	</tr>
	</table>

	<div align="center">
	<br>
<?php
if($total_table==0) {
?>
   <span class="style1">Nenhum registro foi encontrado   </span><br>
<?php
}//end if($total_table==0)
///echo "<br>DADOS1:".$sql."<br>" ;
?>
</div>
	
<div class='texto'>
<?php
echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit;
?>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>