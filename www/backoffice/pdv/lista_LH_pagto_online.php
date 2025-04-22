<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

	set_time_limit ( 3000 ) ;

	$time_start_stats = getmicrotime();

	$data_inicio_pagto_online = "2010-04-01";
	if(!$btnSubmit) $tf_u_edita_status = null; 

	if($tf_op=="sav") {
		if($tf_u_id && is_numeric($tf_u_id)) {
			$substatus_sav = ($tf_u_substatus_selected>0)?$tf_u_substatus_selected:-1;
			$substatus_pag_online_sav = ($tf_u_substatus_pag_online_selected>0)?$tf_u_substatus_pag_online_selected:-1;
			echo "<p class='texto' style='color:blue'>Atualiza registro $tf_u_id para sub-status <b>$substatus_sav</b> e <b>$substatus_pag_online_sav</b></p>";
			$sql = "update dist_usuarios_games set   ug_substatus = ".$substatus_sav.",  ug_substatus_pag_online = ".$substatus_pag_online_sav." where ug_id = ".$tf_u_id.";";
//echo "$sql<br>";
			$ret = SQLexecuteQuery($sql);
			if($ret) {
				echo "<p class='texto' style='color:blue'>Cadastro do usuário atualizado com sucesso(registro $tf_u_id)</p>";
			} else {
				echo "<p class='texto' style='color:red'>Erro ao atualizar cadastro do usuário (registro $tf_u_id)</p>";
			}

		}
	}
	
	if(!$tf_order_field) $tf_order_field = "p";
	if(!is_numeric($tf_u_substatus)) $tf_u_substatus = "";
	if(!is_numeric($tf_u_substatus_pag_online)) $tf_u_substatus_pag_online = "";

	$sql  = "select pag_n, coalesce(pag_total, 0) as pag_total, vg_n, coalesce(vg_total, 0) as vg_total, ug_id, ug_login, 
				ug_ativo, ug_risco_classif, ug_tipo_cadastro, ug_contatada_ultimo_mes, 
				ug_data_inclusao, ug_data_ultimo_acesso, ug_qtde_acessos, ug_nome_fantasia, ug_razao_social, ug_cnpj, ug_responsavel, ug_email, ug_endereco, ug_numero, ug_complemento, ug_bairro, ug_cidade, ug_estado, ug_cep, ug_tel_ddi, ug_tel_ddd, ug_tel, ug_cel_ddi, ug_cel_ddd, ug_cel, ug_fax_ddi, ug_fax_ddd, ug_fax, ug_ra_codigo, ug_ra_outros, ug_contato01_nome, ug_contato01_cargo, ug_contato01_tel_ddi, ug_contato01_tel_ddd, ug_contato01_tel, ug_observacoes, ug_tipo_cadastro, ug_nome, ug_rg, ug_cpf, ug_data_nascimento, ug_sexo, ug_substatus, ug_substatus_pag_online, ug_coord_lat, ug_coord_lng, ug_google_maps_status
			from dist_usuarios_games ug
				left outer join (
					select vg_ug_id, count(*) as pag_n, sum(vg_pagto_valor_pago) as pag_total 
					from tb_dist_venda_games vg 
					where vg_ultimo_status=5 and vg_pagto_tipo>3 and vg_pagto_valor_pago>0 and vg.vg_data_inclusao>='".$data_inicio_pagto_online."'  
					group by vg_ug_id
					) p on ug.ug_id = p.vg_ug_id
				left outer join (
					select vg_ug_id, count(*) as vg_n, sum(vgm.vgm_valor*vgm.vgm_qtde) as vg_total
					from tb_dist_venda_games vg 
						inner join tb_dist_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id 
					where vg.vg_ultimo_status = 5 and vg.vg_data_inclusao>='".$data_inicio_pagto_online."'
					group by vg.vg_ug_id
					) v on ug.ug_id = v.vg_ug_id 
			where ug_ativo=1 and ug_risco_classif=2 
			";
	if($tf_u_substatus) {
		$sql  .= " and ug_substatus = $tf_u_substatus ";
	}
	if($tf_u_substatus_pag_online) {
		$sql  .= " and ug_substatus_pag_online = $tf_u_substatus_pag_online ";
	}

	if($tf_order_field=="p") {
		$sql  .= "order by pag_total desc, pag_n desc, ug_login";
	} else {
		$sql  .= "order by vg_total desc, vg_n desc, ug_login";
	}
//echo str_replace("\n", "<br>\n", $sql);	
	$rs_logins = SQLexecuteQuery($sql);
	$n_lans = pg_num_rows($rs_logins);
?>
<script type="text/javascript" src="/js/jquery.tablesorter/jquery-latest.js"></script> 
<script type="text/javascript" src="/js/jquery.tablesorter/jquery.tablesorter.js"></script> 
<link rel="stylesheet" href="/js/jquery.tablesorter/themes/blue/style.css" type="text/css" media="print, projection, screen" />
<script>
$(document).ready(function() {

		$.tablesorter.addParser({
			id: "fancyCurrency",
			is: function(s) {
				// return false so this parser is not auto detected 
	            return false; 
			},
			format: function(s) {
//			  s = s.replace(/[$,]/g,'');
			  s = s.replace('.','');
			  return $.tablesorter.formatFloat( s );
			},
			type: "numeric"
		});

		$.tablesorter.addParser({
			id: "fancyPercent",
			is: function(s) {
				// return false so this parser is not auto detected 
	            return false; 
			},
			format: function(s) {
			  s = s.replace('.','').substr(0,s.length-1);
			  return $.tablesorter.formatFloat( s );
			},
			type: "numeric"
		});

        $("#LHsTable").tablesorter({
			widthFixed: true, 
			widgets: ['zebra'],
			headers: {
//				2 : { sorter: false },
				7 : { sorter: "fancyCurrency" },
//				9 : { sorter: false },
				9: { sorter: "fancyCurrency" },
				10: { sorter: "fancyPercent" },
				13 : { sorter: false },
				14 : { sorter: false },
				15 : { sorter: false }
			},
			// sort on the first column and third column, order asc 
			sortList: [[7,1]] 
			// enable debug mode 
	        //debug: true 
		});
//		.tablesorterPager({container: $("#pager")});

	; 

		// Oculta todos os divs de edição
		$('div.edit').hide();
    }); 
    
	var id_prev = 0;

	function salva_user_substatus(id) {
		document.form1.tf_op.value = "sav";
		document.form1.tf_u_id.value = id;
		document.form1.tf_u_id.value = id;

//alert("Get  'tf_u_substatus"+id+"'");
		var sel_1 = document.getElementById('tf_u_substatus'+id);
//alert("Element tf_u_substatus: "+sel_1+"");
		document.form1.tf_u_substatus_selected.value = sel_1.options[sel_1.selectedIndex].value;

		var sel_2 = document.getElementById('tf_u_substatus_pag_online'+id);
		document.form1.tf_u_substatus_pag_online_selected.value = sel_2.options[sel_2.selectedIndex].value;

//alert("id: "+id+ " ->  document.form1.tf_u_id.value: "+document.form1.tf_u_id.value);
//alert("tf_u_substatus_selected: "+document.form1.tf_u_substatus_pag_online_selected.value+ "\ntf_u_substatus_pag_online_selected: "+document.form1.tf_u_substatus_pag_online_selected.value);

		document.form1.submit();
	}

	function mostra_user_substatus(id) {
//		visibility:visible; display:block
//		visibility:hidden; display:none
//alert("Hide: div_"+id_prev+"\nShow: div_"+id);
		if(id_prev>0) {
//			document.getElementById('div_'+id_prev).style.visibility = 'hidden';
//			document.getElementById('div_'+id_prev).style.display = 'none';
			$('#div_'+id_prev).hide();
		}

//		document.getElementById('div_'+id).style.visibility = 'visible';
//		document.getElementById('div_'+id).style.display = 'block';
		$('#div_'+id).show();

		id_prev = id;
		$('#label_id_prev').html(id_prev);
	}

	function validaGeo(ug_tipo_end, ug_endereco, ug_bairro, ug_cidade, ug_id, ug_pais, ug_cep, ug_estado, ug_numero) {
		var ug_tipo_end = ug_tipo_end;
		var ug_endereco = ug_endereco;
		var ug_bairro   = ug_bairro;
		var ug_cidade   = ug_cidade;
		var ug_estado	= ug_estado;
		var ug_cep		= ug_cep;
		var ug_numero	= ug_numero;
		ug_cep			= ug_cep.replace("-", "");
		
		var ug_id		= eval(ug_id);
		//var endereco	= ug_endereco+', '+ug_cidade+', '+ug_bairro;
		
		if(ug_numero != '') {
			if(ug_tipo_end == '') {
				var endereco	= ug_endereco+', '+ug_numero+', '+ug_cidade+', '+ug_estado;
			} else {
				var endereco	= ug_tipo_end+' '+ug_endereco+', '+ug_numero+', '+ug_cidade+', '+ug_estado;
			}
		} else {
			if(ug_tipo_end == '') {
				var endereco	= ug_endereco+', '+ug_cidade+', '+ug_estado;
			} else {
				var endereco	= ug_tipo_end+' '+ug_endereco+', '+ug_cidade+', '+ug_estado;
			}		
		}
		
		window.open ("/pdv/geobusca/geobusca.php?endereco="+endereco+'&ug_id='+ug_id+'&ug_cep='+ug_cep,"geobusca");
	}

</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table>
  <tr> 
  <td width="100%" valign="top"> 

	<form method="post" name="form1" action="lista_LH_pagto_online.php">
	<input type="hidden" name="tf_u_id" value="">
	<input type="hidden" name="tf_op" value="">

	<input type="hidden" name="tf_u_substatus_selected" value="">
	<input type="hidden" name="tf_u_substatus_pag_online_selected" value="">
    <table class="table fontsize-p txt-preto">
        <tr>
            <td>
                Sub-status
            </td>
            <td>
                <select name="tf_u_substatus" class="form2">
					<option value="" <?php  if($tf_u_substatus == "") echo "selected" ?>>Selecione</option>
					<?php
						foreach($SUBSTATUS_LH as $indice=>$dado) {
							echo "<option value=\"".$indice."\""; if(strcmp($tf_u_substatus,$indice)==0) echo " selected"; echo " >".$dado." (".$indice.")</option>\n";
						}
					?>
				</select>
            </td>
            <td>
                Sub-status Pag. Online
            </td>
            <td>
                <select name="tf_u_substatus_pag_online" class="form2">
					<option value="" <?php  if($tf_u_substatus_pag_online == "") echo "selected" ?>>Selecione</option>
					<?php
						foreach($SUBSTATUS_LH_PAG_ONLINE as $indice=>$dado) {
							echo "<option value=\"".$indice."\""; if(strcmp($tf_u_substatus_pag_online,$indice)==0) echo " selected"; echo " >".$dado." (".$indice.")</option>\n";
						}
					?>
				</select>
            </td>
        </tr>
        <tr>
            <td>
                Edita Status?
            </td>
            <td>
                <input type="checkbox" name="tf_u_edita_status" class="form2"<?php if($tf_u_edita_status) echo " checked" ?>>
            </td>
            <td colspan="2">
                <input type="submit" name="btnSubmit" class="btn btn-info pull-right btn-sm" value="Busca">
            </td>
        </tr>
    </table>
  </td>
  </tr>
  <tr> 
  <td width="100%" valign="top"> <div align="left"> <font color="#666666" size="2" face="Arial, Helvetica, sans-serif">  
	<?php
	
	if($rs_logins && pg_num_rows($rs_logins) > 0){
		echo "<p>Total de PDVs encontrados: $n_lans (vendas depois de $data_inicio_pagto_online)</p>\n";
		echo "<p>Dicas: 
                    <ul>
                        <li>passe o mouse sobre o a coluna 'Login' para ver os detalhes de contato da LH</li>
                        <li>clique no título da coluna para ordenar a tabela toda segundo os valores ascendentes/descendentes da coluna</li>
                    </ul>
                </p>\n";

		echo "<table class='table'>\n";
		echo "<tr align='center'>
                    <td colspan='13'>
                        <font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>
                            <div id=\"div_final\"><img src='/images/AjaxLoadingQuickQuote.gif' width='44' height='44' border='0' title='Loading'></div>
                        </font>
                    </td>
                </tr>
                <tr align='center'>
                    <td colspan='13'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'></font></td>
                </tr>";
		echo "</table>\n";

		echo "<div id='tableforsort'>\n";
		echo "<table border='0' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;' id='LHsTable' class='tablesorter'>\n";
		$scol1 = "#CCFFCC";
		$scol2 = "#FFFFFF";
		$scol = $scol1;
		$i_row = 1;

		$pag_n_tot = 0;
		$pag_tot = 0;
		$vg_n_tot = 0;
		$vg_tot = 0;
		$n_lans_pagto = 0;

		echo "<thead>\n";
		echo "<tr>";
			echo "<th>i</th>";
			echo "<th>id</th>";

			echo "<th>login</th>";
			echo "<th title='Status da LH'>ativo</th>";
			echo "<th title='Tipo de LH (Pós/Pré)'>tipo1</th>";
			echo "<th title='Tipo de LH (PJ/PF)'>tipo2</th>";
			echo "<th title='Número de pagamentos'>nPags</th>";
			echo "<th title='Valor total de pagamentos online (R$)'>TPags</th>";

			echo "<th title='Número de vendas'>nVendas</th>";
			echo "<th title='Valor total de vendas (R$)'>TVendas</th>";
			echo "<th title='Percentagem dos pagamentos sobre o total de vendas (%)'><nobr>%(p/v)</nobr></th>";
			echo "<th title='SubTipo'>sTipo</th>";
			echo "<th title='SubTipo de Pagamentos Online'>sTipoPag</th>";
			echo "<th></th>";
			echo "<th>GMaps</th>";
			echo "<th>VMaps</th>";
		echo "</tr>\n";
		echo "</thead>\n";
		echo "<tbody>\n";
		while($rs_logins_row = pg_fetch_array($rs_logins)) {

			$sout = "Nome                 = ".$rs_logins_row['ug_nome'] ."
RG                   = ".$rs_logins_row['ug_rg'] ."
CPF                  = ".$rs_logins_row['ug_cpf'] ."
Data Nascimento      = ".$rs_logins_row['ug_data_nascimento'] ."
Sexo                 = ".$rs_logins_row['ug_sexo'] ."
Data Inclusão        = ".substr($rs_logins_row['ug_data_inclusao'],0,19) ."
Data Último Acesso   = ".substr($rs_logins_row['ug_data_ultimo_acesso'],0,19) ."
Qtde Acessos         = ".$rs_logins_row['ug_qtde_acessos'] ."
Nome Fantasia        = ".$rs_logins_row['ug_nome_fantasia'] ."
Razão Social         = ".$rs_logins_row['ug_razao_social'] ."
CNPJ                 = ".$rs_logins_row['ug_cnpj'] ."
Responsavel          = ".$rs_logins_row['ug_responsavel'] ."
Email                = ".$rs_logins_row['ug_email'] ."
Endereço             = ".$rs_logins_row['ug_endereco'] ."
Número               = ".$rs_logins_row['ug_numero'] ."
Complemento          = ".$rs_logins_row['ug_complemento'] ."
Bairro               = ".$rs_logins_row['ug_bairro'] ."
Cidade               = ".$rs_logins_row['ug_cidade'] ."
Estado               = ".$rs_logins_row['ug_estado'] ."
CEP                  = ".$rs_logins_row['ug_cep'] ."
Tipo Cadastro        = ".$rs_logins_row['ug_tipo_cadastro'] ."
";
			if(trim($rs_logins_row['ug_tel'])!="") {
				$sout .= "Tel                  = (".$rs_logins_row['ug_tel_ddi'] .") (".$rs_logins_row['ug_tel_ddd'] .") ".$rs_logins_row['ug_tel'] ."
";
			}
			if(trim($rs_logins_row['ug_cel'])!="") {
				$sout .= "Cel                  = (".$rs_logins_row['ug_cel_ddi'] .") (".$rs_logins_row['ug_cel_ddd'] .") ".$rs_logins_row['ug_cel'] ."
";
			}
			if(trim($rs_logins_row['ug_fax'])!="") {
				$sout .= "Fax                  = (".$rs_logins_row['ug_fax_ddi'] .") (".$rs_logins_row['ug_fax_ddd'] .") ".$rs_logins_row['ug_fax'] ."
";
			}
			if(trim($rs_logins_row['ug_contato01_nome'])!="") {
				$sout .= "Contato01 Nome       = ".$rs_logins_row['ug_contato01_nome'] ."
Contato01 Cargo      = ".$rs_logins_row['ug_contato01_cargo'] ."
";
				if(trim($rs_logins_row['ug_contato01_tel'])!="") {
				$sout .= "Contato01 Tel        = (".$rs_logins_row['ug_contato01_tel_ddi'] .") (".$rs_logins_row['ug_contato01_tel_ddd'] .") ".$rs_logins_row['ug_contato01_tel'] ."
";
				}
			}
			if(trim($rs_logins_row['ug_observacoes'])!="") {
				$sout .= "Observações          = ".$rs_logins_row['ug_observacoes'] ."
";
			}

			$sout = str_replace("'", "", $sout);

			echo "<tr><td align='center'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".($i_row++)."</font></td>";
			echo "<td align='right'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'><a href='/pdv/usuarios/com_usuario_detalhe.php?usuario_id=".$rs_logins_row['ug_id']."' class='menu' target='_blank'>".$rs_logins_row['ug_id']."</a></font></td>";

			echo "<td align='center' title='".$sout."'><font color='".(($rs_logins_row['ug_contatada_ultimo_mes']=="1")?"#6600CC'":"#000000")."' size='1' face='Arial, Helvetica, sans-serif'><nobr>".$rs_logins_row['ug_login']."</nobr></font></td>";
			//  ".(($i_row<=2)?"<pre>".$sout."</pre>":"")."
			
			echo "<td align='center'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".(($rs_logins_row['ug_ativo']==1)?"SIM":"não")."</font></td>";
			echo "<td align='center'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".(($rs_logins_row['ug_risco_classif']==1)?"Pós":"Pré")."</font></td> ";
			echo "<td align='center'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".$rs_logins_row['ug_tipo_cadastro']."</font></td> ";
			echo "<td align='center'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".number_format($rs_logins_row['pag_n'], 0, ',', '.')."</font></td>";
			echo "<td align='right' title='pag. médio: ".number_format($rs_logins_row['pag_total']/(($rs_logins_row['pag_n']>0)?$rs_logins_row['pag_n']:1), 2, ',', '.')."'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".number_format($rs_logins_row['pag_total'], 2, ',', '.')."</font></td> ";

			echo "<td align='center'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".number_format($rs_logins_row['vg_n'], 0, ',', '.')."</font></td> ";
			echo "<td align='right' title='venda média: ".number_format($rs_logins_row['vg_total']/(($rs_logins_row['vg_n']>0)?$rs_logins_row['vg_n']:1), 2, ',', '.')."'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".number_format($rs_logins_row['vg_total'], 2, ',', '.')."</font></td>  ";

			echo "<td align='right'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".number_format(100*$rs_logins_row['pag_total']/(($rs_logins_row['vg_total']>0)?$rs_logins_row['vg_total']:1), 2, ',', '.')."%</font></td>";

			echo "<td align='center' title='".($rs_logins_row['ug_substatus']." - ".$SUBSTATUS_LH[$rs_logins_row['ug_substatus']])."'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".$rs_logins_row['ug_substatus']."</font></td>";
			echo "<td align='center' title='".($rs_logins_row['ug_substatus']." - ".$SUBSTATUS_LH_PAG_ONLINE[$rs_logins_row['ug_substatus_pag_online']])."'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".$rs_logins_row['ug_substatus_pag_online']."</font></td>";

			echo "<td align='center'>";
			if($tf_u_edita_status) {
			?>
				<input type='button' onclick='mostra_user_substatus(<?php echo $rs_logins_row['ug_id'] ?>)' value='Editar ID:<?php echo $rs_logins_row['ug_id'] ?>' class="texto">
				<div id="div_<?php echo $rs_logins_row['ug_id'] ?>" class="edit">
					<table border='1' cellpadding='0' cellspacing='1' width='100%' bordercolor='#cccccc' style='border-collapse:collapse;'>
					  <tr> 
						  <td width="50%" class="texto" valign="top"><nobr>Sub-status</nobr></td>
						  <td width="50%" class="texto" valign="top"><select name="tf_u_substatus<?php echo $rs_logins_row['ug_id'] ?>" id="tf_u_substatus<?php echo $rs_logins_row['ug_id'] ?>" class="texto">
									<option value="" <?php  if($rs_logins_row['ug_substatus_pag'] == "") echo "selected" ?>>Selecione</option>
									<?php
										foreach($SUBSTATUS_LH as $indice=>$dado) {
											echo "<option value=\"".$indice."\""; if(strcmp($rs_logins_row['ug_substatus'],$indice)==0) echo " selected"; echo " >".$dado." (".$indice.")</option>\n";
										}
									?>
									</select></td>
					  </tr>
					  <tr>
						  <td width="50%" class="texto" valign="top"><nobr>Sub-status Pag. Online</nobr></td>
						  <td width="50%" class="texto" valign="top" align="right"><select name="tf_u_substatus_pag_online<?php echo $rs_logins_row['ug_id'] ?>" id="tf_u_substatus_pag_online<?php echo $rs_logins_row['ug_id'] ?>" class="texto">
									<option value="" <?php  if($rs_logins_row['ug_substatus_pag_online'] == "") echo "selected" ?>>Selecione</option>
									<?php
										foreach($SUBSTATUS_LH_PAG_ONLINE as $indice=>$dado) {
											echo "<option value=\"".$indice."\""; if(strcmp($rs_logins_row['ug_substatus_pag_online'],$indice)==0) echo " selected"; echo " >".$dado." (".$indice.")</option>\n";
										}
									?>
								</select></td>
					  </tr>
					  <tr>
						  <td width="50%" class="texto" valign="top"></td>
						  <td width="50%" class="texto" valign="top" align="right"><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'><input type='button' onclick='salva_user_substatus(<?php echo $rs_logins_row['ug_id'] ?>)' value='Salvar ID:<?php echo $rs_logins_row['ug_id'] ?>' style="color:red" class="texto"></font></td>
					  </tr>
					</table>
				</div>
			<?php
			} else {
			echo "";
			}
			echo "</td> ";

			$statusMaps = $rs_logins_row['ug_google_maps_status'];

			$statusMaps_descr = "";
			switch($statusMaps) {
				case 1: 
					$statusMaps_descr = "Não Localizada";
					break;
				case 2: 
					$statusMaps_descr = "Fora do Brasil";
					break;
				default: 
					$statusMaps_descr = "Tipo Desconhecido";
					if(strlen(trim($statusMaps))==0) $statusMaps_descr .= " (Empty)";
					else $statusMaps_descr .= " ('$statusMaps')";
					break;
			}
			if($rs_logins_row['ug_coord_lat']==0 && $rs_logins_row['ug_coord_lng']==0) {
				if($statusMaps_descr!="") $statusMaps_descr.= "\n";
				$statusMaps_descr .= "Sem Geolocalização";
			} else {
				$statusMaps_descr .= "\n[".number_format($rs_logins_row['ug_coord_lat'], 2, '.', '.').", ".number_format($rs_logins_row['ug_coord_lng'], 2, '.', '.')."]";
			}
			if(trim($statusMaps)=="") {
				if($rs_logins_row['ug_coord_lat']==0 && $rs_logins_row['ug_coord_lng']==0) {
					$statusMaps = "<font color='red'>Coords=0</font>";
				} else {
					$statusMaps = "<font color='blue'>Com_Coords</font>";
				}
			}

		?>
		<td title="<?php echo $statusMaps_descr ?>" align="center"><?php echo $statusMaps ?></td>
		<td align="center">
			<?php
				$ug_endereco = $rs_logins_row['ug_tipo_end'] . "','" . str_replace("'", "\'", $rs_logins_row['ug_endereco']) . "','" . str_replace("'", "\'", $rs_logins_row['ug_bairro']) . "','" . str_replace("'", "\'", $rs_logins_row['ug_cidade']) . "','" . $rs_logins_row['ug_id'] . "','Brasil','" . $rs_logins_row['ug_cep'] . "','" . $rs_logins_row['ug_estado'] . "','" . $rs_logins_row['ug_numero'] . "";	
			?>

			<a href="javascript:void(0);" onClick="validaGeo('<?php  echo $ug_endereco ?>');"><img src="/images/pdv/global-search-icon_peq.jpg" width="28" height="21" border="0" title="<?php echo "Lat/Lng: [".$rs_logins_row['ug_coord_lat']." , ".$rs_logins_row['ug_coord_lng']."]\n '".$ug_endereco ."'" ?>"></a>
		</td>
			<?php
			echo "</tr>\n";

			$scol = ($scol==$scol1)?$scol2:$scol1;

			$pag_n_tot += $rs_logins_row['pag_n'];
			$pag_tot += $rs_logins_row['pag_total'];

			$vg_n_tot += $rs_logins_row['vg_n'];
			$vg_tot += $rs_logins_row['vg_total'];

			if($rs_logins_row['pag_total']>0) $n_lans_pagto++;
			if($rs_logins_row['vg_total']>0) $n_lans_vg++;
		}

		echo "</tbody>\n";
		echo "</table>\n";
		echo "</div>\n";

		$s_final = "<table border='1' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
		$s_final .= "<tr bgcolor='#99CCFF'>";
		$s_final .= "<td align='center' colspan='6'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'></font></td>";
		$s_final .= "<td align='center'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'></font></td>";
		$s_final .= "<td align='center'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'><b>pag_n</b></font></td>";
		$s_final .= "<td align='center'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'><b>pag_total</b></font></td>";
		$s_final .= "<td align='center'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'></font></td>";
		$s_final .= "<td align='center'><font color='blue' size='1' face='Arial, Helvetica, sans-serif'><b>vg_n</b></font></td>";
		$s_final .= "<td align='center'><font color='blue' size='1' face='Arial, Helvetica, sans-serif'><b>vg_total</b></font></td>";
		$s_final .= "<td align='center'><font color='blue' size='1' face='Arial, Helvetica, sans-serif'><b>%(pag/vendas)</b></font></td>";
		$s_final .= "</tr>\n";

		$s_final .= "<tr bgcolor='#99CCFF'>";
		$s_final .= "<td align='center' colspan='6'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'></font></td>";
		$s_final .= "<td align='right'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>Total</font></td>";
		$s_final .= "<td align='center'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".number_format($pag_n_tot, 0, ',', '.')."</font></td>";
		$s_final .= "<td align='right' title='pag. médio: ".number_format($pag_tot/(($pag_n_tot>0)?$pag_n_tot:1), 2, ',', '.')."'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>".number_format($pag_tot, 2, ',', '.')."</font></td>";
		$s_final .= "<td align='right'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'></font></td>";
		$s_final .= "<td align='center'><b><font color='blue' size='1' face='Arial, Helvetica, sans-serif'>".number_format($vg_n_tot, 0, ',', '.')."</font></b></td>";
		$s_final .= "<td align='right' title='venda média: ".number_format($vg_tot/(($vg_n_tot>0)?$vg_n_tot:1), 2, ',', '.')."'><b><font color='blue' size='1' face='Arial, Helvetica, sans-serif'>".number_format($vg_tot, 2, ',', '.')."</font></b></td>";
		$s_final .= "<td align='right'><font color='blue' size='1' face='Arial, Helvetica, sans-serif'>".number_format(100*$pag_tot/(($vg_tot>0)?$vg_tot:1), 2, ',', '.')."%</font></td>";
		$s_final .= "</tr>\n";

		$s_final .= "<tr bgcolor='#99CCFF'><td align='center' colspan='3'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'></font></td><td align='right'><font color='red' size='1' face='Arial, Helvetica, sans-serif'>Lans Pré com pagto on-line</font></td> <td align='center'><font color='red' size='1' face='Arial, Helvetica, sans-serif'>".number_format($n_lans_pagto, 0, ',', '.')."</font></td> <td align='right' colspan='3'><font color='red' size='1' face='Arial, Helvetica, sans-serif'><nobr>(".number_format((100*$n_lans_pagto/(($n_lans)?$n_lans:1)), 2, ',', '.')."% del total de PDVs Pré)</nobr></font></td><td align='center' colspan='5'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'></font></td></tr>\n";

		$s_final .= "<tr bgcolor='#99CCFF'><td align='center' colspan='3'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'></font></td><td align='right'><font color='red' size='1' face='Arial, Helvetica, sans-serif'>Lans Pré com vendas</font></td> <td align='center'><font color='red' size='1' face='Arial, Helvetica, sans-serif'>".number_format($n_lans_vg, 0, ',', '.')."</font></td> <td align='right' colspan='3'><font color='red' size='1' face='Arial, Helvetica, sans-serif'><nobr>(".number_format((100*$n_lans_vg/(($n_lans)?$n_lans:1)), 2, ',', '.')."% del total de PDVs Pré)</nobr></font></td><td align='center' colspan='5'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'></font></td></tr>\n";

		$s_final .= "</table>\n";

		echo $s_final;


//		echo "<pre>".htmlentities($s_final)."</pre>";

		echo "<script language=\"JavaScript\" type=\"text/JavaScript\">\ndocument.getElementById(\"div_final\").innerHTML = \"<table width='100%' border='0' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>".str_replace("\n", " ", $s_final)."</table>\";\n</script>";

	} else {
		echo "<p><font color='#FF0000'><b>Erro ao procurar PDVs cadastrados</b></font></p>\n";
	}

	?>
		</font></div></td>
      </tr>
    </table>
<?php 
	echo "<p><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, ',', '.')."s</font></p>";
?>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</body></html>
