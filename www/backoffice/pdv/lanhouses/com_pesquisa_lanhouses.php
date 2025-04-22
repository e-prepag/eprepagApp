<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/pdv/inc_utf.php";
require_once $raiz_do_projeto."includes/pdv/inc_ufs.php";

	$time_start = getmicrotime();
/*
echo "ordem_A: $ordem<br>";
echo "inicial_A: $inicial<br>";
echo "BtnSearch: $BtnSearch<br>";
*/
	if(!$ncamp)    $ncamp       = 'data_inclusao';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 1;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

/*
	// Obtem comentario com chars especiais
	$sql = "select comentario from tb_lanhouses where lanhouses_id = 1034";
//echo "sql: ".$sql."<br>";
echo "<hr>";
	$rs_tmp = SQLexecuteQuery($sql);
	if($rs_tmp && pg_num_rows($rs_tmp) > 0){
		$rs_tmp_row = pg_fetch_array($rs_tmp);
		$comentario = $rs_tmp_row['comentario'];
//echo "$comentario<br>";
		$s_especial_chars = "·ÈÌÛ˙‡ËÏÚ˘„ı‚ÍÓÙ‰ÎÔˆ¸Á¡…Õ”⁄¿»Ã“Ÿ√’¬ Œ‘€ƒÀœ÷‹«";
		$sout = "";
		$sout_rev = "";
		$s_remake = "";
		for($i=0;$i<strlen($comentario);$i++) {
			if($comentario[$i]!="√") {
//				echo $i." - ".$comentario[$i]." - ".ord($comentario[$i])."<br>";
				$sout .= "'".$s_especial_chars[($i-1)/2]."' =&gt; ".ord($comentario[$i]).", ";
				$sout_rev .= "".ord($comentario[$i])." =&gt; '".$s_especial_chars[($i-1)/2]."', ";
				$s_remake .= "√".chr(ord($comentario[$i]));
			}
		}
//		echo "extended ascii to utf: ".$sout."<br>";
//		echo "utf to extended ascii: ".$sout_rev."<br>";
//		echo "comentario: ".$comentario."<br>";
//		echo "comentario: ".$s_remake."<br>";
//		if($comentario==$s_remake) echo "Success<br>"; else echo "Wrong<br>";
		$s_remake1 = translate_extended_ascii_to_utf($s_especial_chars);
//		echo "translate_extended_ascii_to_utf: ".$s_remake1."<br>";
//		if($comentario==$s_remake1) echo "Success1<br>"; else echo "Wrong1<br>";

	}
echo "<hr>";
*/

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100;	//$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&tf_lanhouses_id=$tf_lanhouses_id";
	$varsel .= "&tf_lh_nome=$tf_lh_nome&tf_lh_uf=$tf_lh_uf&tf_lh_cidade=$tf_lh_cidade";
/*
echo "ncamp: $ncamp<br>";
echo "inicial_B: $inicial<br>";
echo "ordem_B: $ordem<br>";
echo "varsel: $varsel<br>";
*/
	session_start();
//echo "<pre>".print_r($_SESSION,true)."</pre>";
//echo "varsel: '$varsel'<br>";
	$_SESSION["busca_lh_varsel_list"] = $varsel;
//echo "varsel: '".$_SESSION["busca_lh_varsel_list"]."'<br>";
//echo "<pre>".print_r($GLOBALS,true)."</pre>";
//echo "<pre>".print_r($_SESSION,true)."</pre>";

//echo "BtnSearch: $BtnSearch<br>";

//	if(isset($BtnSearch)){
	if(true){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//Lanhouse
		//------------------------------------------------------------------
		//codigo
		if($msg == "")
			if($tf_lanhouses_id){
			
				if(!is_numeric($tf_lanhouses_id))
					$msg = "CÛdigo do PDV deve ser numÈrico.\n";
			}
		//ogp_ativo
		if($msg == "")
			if($ogp_ativo){
			
				if(!is_numeric($ogp_ativo))
					$msg = "O cÛdigo de ativo deve ser numÈrico.\n";
			}

		//Busca lanhouses
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){

			$filtro = array();

			$sql = "SELECT * FROM tb_lanhouses ";
			$sql .= "WHERE 1=1 ";
			if($tf_lanhouses_id) 	$sql .= "and lanhouses_id = $tf_lanhouses_id ";
			if($tf_lh_nome) {
				$tf_lh_nome_sql	= strtoupper(translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($tf_lh_nome)),0,50))); 
				$sql .= "and upper(lanhouse) like '%".$tf_lh_nome_sql."%' ";
			}
			if($tf_lh_uf) 			$sql .= "and upper(estado) = '$tf_lh_uf' ";
			if($tf_lh_cidade) {
//echo "tf_lh_cidade: '$tf_lh_cidade'<br>";
//$GLOBALS['_SESSION']['sdebug'] = "";
//echo "remove_special_chars(tf_lh_cidade): '".remove_special_chars(translate_extended_ascii_to_utf($tf_lh_cidade))."<br>";
//echo "translate_extended_ascii_to_utf ... (tf_lh_cidade): '".translate_extended_ascii_to_utf(substr(str_replace("'", "''", strtoupper( remove_special_chars(trim($tf_lh_cidade)) ) ),0,50)) ."'<br>";
//echo "<br>GLOBALS['_SESSION']['sdebug']: '".$GLOBALS['_SESSION']['sdebug']."'<br>";
//$GLOBAL['_SESSION']['sdebug'] = "";

				// usa strtoupper() sÛ depois de fazer traduÁ„o com translate_extended_ascii_to_utf() 
				$tf_lh_cidade_sql	= "%".strtoupper( translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($tf_lh_cidade)  ),0,50)) )."%"; 
				$sql .= "and upper(cidade) like '".$tf_lh_cidade_sql."' ";
			}
			if($tf_lh_bairro) {
//echo "tf_lh_bairro: '$tf_lh_bairro'<br>";
				$tf_lh_bairro_sql	= strtoupper( translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($tf_lh_bairro) ),0,50)) ); 
				$sql .= "and upper(bairro) like '%".$tf_lh_bairro_sql."%' ";
			}


$sql  = "SELECT 
	tipo, ug_nome, ug_cidade, ug_estado, ug_coord_lat, ug_coord_lng, ug_tipo_cadastro, ug_ativo, ug_id, ug_numero, ug_endereco, ug_tipo_end, ug_bairro, ug_cep, data_inclusao
FROM (

	(SELECT 
		'L' as tipo,
		(CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome END) AS ug_nome, 
		ug.ug_cidade AS ug_cidade, ug.ug_estado AS ug_estado, ug.ug_coord_lat, ug.ug_coord_lng, ug.ug_tipo_cadastro, ug.ug_ativo, 
		ug.ug_id AS ug_id, ug.ug_numero, ug.ug_endereco, ug.ug_tipo_end, ug.ug_bairro, ug.ug_cep, ug_data_inclusao as data_inclusao
	FROM dist_usuarios_games ug 
	WHERE 1=1
		AND ug.ug_ativo = 1 
		AND ug.ug_coord_lat != 0
		AND ug.ug_coord_lng != 0
		AND ug.ug_status = 1 ";

if($tf_lh_bairro) {
	$tf_lh_bairro_sql	= strtoupper( translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($tf_lh_bairro) ),0,50)) ); 
	$sql .= "AND upper(ug_bairro) like '%".$tf_lh_bairro_sql."%' ";
}
if($tf_lh_cidade) {
	$tf_lh_cidade_sql = "%".strtoupper( translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($tf_lh_cidade)  ),0,50)) )."%"; 
	$sql .= "AND upper(ug_cidade) like '".$tf_lh_cidade_sql."' ";
}
if($tf_lh_uf) {
	$sql .= "AND upper(ug_estado) = '$tf_lh_uf' ";
}
if($tf_lanhouses_id) {
	$sql .= "AND ug_id = $tf_lanhouses_id ";
}
if($tf_lh_nome) {
	$tf_lh_nome_sql	= strtoupper(translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($tf_lh_nome)),0,50))); 
	$sql .= "and upper(ug_nome) like '%".$tf_lh_nome_sql."%' ";
}

$sql .= ")

UNION ALL

	(SELECT us_tipo_store as tipo, us_nome_loja AS ug_nome, us_cidade AS ug_cidade, us_estado AS ug_estado, us_coord_lat AS ug_coord_lat, us_coord_lng AS ug_coord_lng, 
		us_tipo_store AS ug_tipo_cadastro, '1' AS ug_ativo, us_id AS ug_id, us_numero AS ug_numero, us_endereco AS ug_endereco, '' AS ug_tipo_end, us_bairro AS ug_bairro, us_cep as ug_cep, us_data_inclusao as data_inclusao
	FROM dist_usuarios_stores_cartoes
	WHERE 1=1
		AND us_coord_lat != 0
		AND us_coord_lng != 0 
		";

if($tf_lh_bairro) {
	$tf_lh_bairro_sql	= strtoupper( translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($tf_lh_bairro) ),0,50)) ); 
	$sql .= "AND upper(us_bairro) like '%".$tf_lh_bairro_sql."%' \n";
}
if($tf_lh_cidade) {
	$tf_lh_cidade_sql = "%".strtoupper( translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($tf_lh_cidade)  ),0,50)) )."%"; 
	$sql .= "AND upper(us_cidade) like '".$tf_lh_cidade_sql."' \n";
}
if($tf_lh_uf) {
	$sql .= "AND upper(us_estado) = '$tf_lh_uf' \n";
}
if($tf_lanhouses_id) {
	$sql .= "AND us_id = $tf_lanhouses_id \n";
}
if($tf_lh_nome) {
	$tf_lh_nome_sql	= strtoupper(translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($tf_lh_nome)),0,50))); 
	$sql .= "and upper(us_nome_loja) like '%".$tf_lh_nome_sql."%' ";
}

$sql .= ")
) as locais
";
// "ORDER BY ug_nome, ug_estado"

//echo "'$tf_lh_cidade': -&gt; '$tf_lh_cidade_sql'<br>";
//echo "'$tf_lh_bairro': -&gt; '$tf_lh_bairro_sql'<br>";
if(b_IsUsuarioReinaldo()) { 
//echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>";
//die("Stop");
}
			$rs_lhs = SQLexecuteQuery($sql);

	
			if($ret != "") $msg = $ret;
			else {
				$total_table = pg_num_rows($rs_lhs);
//echo date("H:i:s")." - total_table: $total_table<br>";

				if($total_table == 0) {
					$msg = "Nenhum PDV encontrado.\n";
				} else {
					//Ordem
					$orderBy = $ncamp;
					if($ordem == 1){
						$orderBy .= " desc ";
						$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
					} else {
						$orderBy .= " asc ";
						$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
					}
			
					$orderBy .= " limit ".$max; 
					$orderBy .= " offset ".$inicial;
				
					$sql .= " order by ".$orderBy;

if(b_IsUsuarioReinaldo()) { 
//echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>";
//die("Stop");
}

					$rs_lhs = SQLexecuteQuery($sql);

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
	
	
ob_end_flush();
?>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12 pull-right">
    <a href="com_lanhouses_detalhe.php?bNew=1" class="btn btn-info">Novo</a>
    <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info">
</div>
<div class="col-md-12">
    <form name="form1" method="post" action="com_pesquisa_lanhouses.php" class="txt-preto fontsize-p">
        <table class="table top10 ">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="6" bgcolor="#ECE9D8" class="texto">Lanhouse</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">CÛdigo</font></td>
            <td>
              	<input name="tf_lanhouses_id" type="text" class="form2" value="<?php echo $tf_lanhouses_id ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Nome</font></td>
			<td>
					<input name="tf_lh_nome" type="text" class="form2" value="<?php echo $tf_lh_nome ?>" size="25" maxlength="100">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">&nbsp;</font></td>
            <td>
              	&nbsp;
			</td>
            <td class="texto">&nbsp;</font></td>
            <td>&nbsp;</td>
		  </tr>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">EndereÁo</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">UF</td>
            <td>
				<select name="tf_lh_uf" class="form2">
					<option value=""<?php if($tf_lh_uf=="") echo " selected" ?>>Escolha UF</option>
				<?php 
					foreach($ufs as $key => $uf) {
				?><option value="<?php echo $uf ?>"<?php if($tf_lh_uf==$uf) echo " selected" ?>><?php echo $uf ?></option>
				<?php 
					}
				?>
				</select>
			</td>
            <td class="texto">Cidade</font></td>
			<td>
			<?php if($tf_lh_uf) { ?>
					<?php 
					$sqlcidades = "select cidade , estado from tb_lanhouses ";
					$sqlcidades .= "where estado='".substr($tf_lh_uf,0,2)."' ";
					$sqlcidades .= "group by cidade , estado order by cidade , estado";
					$rs_cidades = SQLexecuteQuery($sqlcidades);
					?>
				<select name="tf_lh_cidade" class="form2">
					<option value=""<?php if($tf_lh_cidade=="") echo " selected" ?>>Selecione a cidade</option>
					<?php
					while($rs_cidades_row = pg_fetch_array($rs_cidades)){
						$stmp = translate_utf_to_extended_ascii($rs_cidades_row["cidade"]);
						echo "<option value=\"".translate_utf_to_extended_ascii($rs_cidades_row["cidade"])."\"" . ((translate_utf_to_extended_ascii($rs_cidades_row["cidade"])==$tf_lh_cidade)?" selected":"") . ">".translate_utf_to_extended_ascii($rs_cidades_row["cidade"]) . " (".$tf_lh_uf.")</option>\n";

					}

					?>
				</select>
			<?php 

				  } else { ?>
			<input type="hidden" name="tf_lh_cidade" value="">
			<?php } ?>
			</td>
		  </tr>
		</table>

        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>

		<?php if($total_table > 0) { ?>
        <table class="table txt-preto fontsize-p">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
				  	<table width="100%" border='0' cellpadding="2" cellspacing="1">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php 
							$varsel_pag = "&ordem=".$ordem."";
//echo "varsel_pag: $varsel_pag<br>";
//echo "varsel: $varsel<br>";
//echo "varsel: ".$varsel_pag.$varsel."<br>";

							$ordem = ($ordem == 1)?2:1; 
						?>
                      <tr  bgcolor="#ECE9D8"> 
                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=lanhouses_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">id</a> 
                          <?php if($ncamp == 'lanhouses_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><font class="texto">Tipo</font></a></strong></td>

                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data de Inclus„o</font></a>
                          <?php if($ncamp == 'data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=lanhouse&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Nome</font></a>
                          <?php if($ncamp == 'lanhouse') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=logradouro&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Logradouro</font></a>
                          <?php if($ncamp == 'logradouro') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=numero&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">N˙mero</font></a>
                          <?php if($ncamp == 'numero') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=complemento&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Complemento</font></a>
                          <?php if($ncamp == 'complemento') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=bairro&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Bairro</font></a>
                          <?php if($ncamp == 'bairro') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=cidade&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Cidade</font></a>
                          <?php if($ncamp == 'cidade') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=cep&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">CEP</font></a>
                          <?php if($ncamp == 'cep') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=estado&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">UF</font></a>
                          <?php if($ncamp == 'estado') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>

                        <td align="center"><strong><a href="<?php echo $default_add."?BtnSearch=Buscar&ordem=".$ordem."&ncamp=comentario&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Coment·rio</font></a>
                          <?php if($ncamp == 'comentario') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>

					  </tr>
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						while($rs_lhs_row = pg_fetch_array($rs_lhs)){
//echo "Lista <pre>".print_r($rs_lhs_row, true)."</pre>\n";
//die("Stop");
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
							$status = ($rs_lhs_row['ogp_ativo'] == 1)?"Ativo":"Inativo";
							$opr_status = ($rs_lhs_row['opr_status'] == '1')?"Ativo":"Inativo";
							$tipo = (($rs_lhs_row['tipo']=="L")?"Lan&nbsp;house":(($rs_lhs_row['tipo']=="V")?"Loja&nbsp;Saraiva":(($rs_lhs_row['tipo']=="S")?"Store":"")));

//tipo, ug_nome, ug_cidade, ug_estado, ug_coord_lat, ug_coord_lng, ug_tipo_cadastro, ug_ativo, ug_id, ug_numero, ug_endereco, ug_tipo_end, ug_bairro

					?>
                      <tr bgcolor="<?php echo $cor1 ?>"> 
                        <td class="texto" width="50" align="center"><a style="text-decoration:none" href="com_lanhouses_detalhe.php?ug_id=<?php echo $rs_lhs_row['ug_id'] ?>"><?php echo $rs_lhs_row['ug_id'] ?></a></td>
                        <td class="texto" width="100" align="center" title="<?php echo $tipo ?>"><?php echo $rs_lhs_row['tipo'] ?></td>
                        <td class="texto" width="100" align="center"><?php echo formata_data($rs_lhs_row['data_inclusao'],0) ?></td>
                        <td class="texto"><nobr><a style="text-decoration:none" href="com_lanhouses_detalhe.php?lanhouses_id=<?php echo $rs_lhs_row['ug_id'] ?>"><?php echo translate_utf_to_extended_ascii($rs_lhs_row['ug_nome']) ?></a></nobr></td>
                        <td class="texto" width="100" align="center"><nobr><?php echo translate_utf_to_extended_ascii($rs_lhs_row['ug_endereco']) ?></nobr></td>
                        <td class="texto" width="100" align="center"><nobr><?php echo translate_utf_to_extended_ascii($rs_lhs_row['ug_numero']) ?></nobr></td>
                        <td class="texto" width="100" align="center"><nobr><?php echo translate_utf_to_extended_ascii($rs_lhs_row['complemento']) ?></nobr></td>
                        <td class="texto" width="100" align="center"><nobr><?php echo translate_utf_to_extended_ascii($rs_lhs_row['ug_bairro']) ?></nobr></td>
                        <td class="texto" width="100" align="center"><nobr><?php echo translate_utf_to_extended_ascii($rs_lhs_row['ug_cidade']) ?></nobr></td>
                        <td class="texto" width="100" align="center"><nobr><?php echo $rs_lhs_row['ug_cep'] ?></nobr></td>
                        <td class="texto" width="100" align="center"><?php echo $rs_lhs_row['ug_estado'] ?></td>

                        <td class="texto" width="100" align="center"><?php echo translate_utf_to_extended_ascii($rs_lhs_row['comentario']) ?></td>

					  </tr>
					<?php 	}	?>
                      <tr> 
                        <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
                      </tr>
					<?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel.$varsel_pag); ?>
                    </table>
				  </td>
                </tr>
              </table>
          <?php  }  ?>
    </div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>