<?php header("Content-Type: text/html; charset=ISO-8859-1",true) ?>
<?php 

require_once "../../../includes/constantes.php";
require_once $raiz_do_projeto."public_html/sys/includes/topo_sys_inc.php";

if($tf_platform=="undefined") $tf_platform = "";
if($tf_browser=="undefined") $tf_browser = "";
if($tf_version=="undefined") $tf_version = "";
 

?>
<script language="javascript">

	$(document).ready(function(){

		$("#tf_platform").change(function() { 
			get_filtros_navegador($("#tf_platform").val(), $("#tf_browser").val(), $("#tf_version").val());
		}); 		
		$("#tf_browser").change(function() {
			get_filtros_navegador($("#tf_platform").val(), $("#tf_browser").val(), $("#tf_version").val());
		}); 		
		$("#tf_version").change(function() { 
			get_filtros_navegador($("#tf_platform").val(), $("#tf_browser").val(), $("#tf_version").val());
		}); 		
	});
</script>
<?php 
// Exemplo para testes
// com_pesquisa_newsletters_ajax.php?tf_platform=Linux&tf_browser=Firefox&tf_version=3.0.1
	
	//Browser - Plataformas
	$sql  = "select browser_platform, count(*) as n from tb_CanalAcesso where not browser_platform is null group by browser_platform order by browser_platform"; // "--, min(data) as data_min, max(data) as data_max "
	$rs_platforms = SQLexecuteQuery($sql);

	//Browser - Browsers
	$sql  = "select browser_browser, count(*) as n from tb_CanalAcesso where not browser_browser is null ";
	if($tf_platform) {
		$sql .= " and strpos(upper(browser_platform),'".str_replace("'","''",strtoupper($tf_platform))."')>0 ";	
	}
	$sql .= "group by browser_browser order by browser_browser";	// "--, min(data) as data_min, max(data) as data_max "
	$rs_browsers = SQLexecuteQuery($sql);

	//Browser - Versoes
	$sql  = "select browser_browser, browser_version, count(*) as n from tb_CanalAcesso where 1=1 ";
	if($tf_platform) { $sql  .= " and strpos(upper(browser_platform),'".str_replace("'","''",strtoupper($tf_platform))."')>0 ";	 }
	if($tf_browser) { $sql  .= " and strpos(upper(browser_browser),'".str_replace("'","''",strtoupper($tf_browser))."')>0 ";	 }
//	if($tf_version) { $sql  .= " and strpos(upper(browser_version),'".str_replace("'","''",strtoupper($tf_version))."')>0 ";	 }
	$sql  .= " group by browser_browser, browser_version order by browser_browser, browser_version";	
	$rs_versions = SQLexecuteQuery($sql);


?>
       <table width="894" border="0" cellpadding="0" cellspacing="0">
		  <tr bgcolor="#F5F5FB"> 
            <td width="100%" colspan="4">
				<table width="894" border="0" cellpadding="0" cellspacing="2">
		          <tr bgcolor="#F5F5FB"> 
				    <td width="17%" class="texto" align="right">&nbsp;Plataforma&nbsp;</td>
				    <td width="17%" class="texto">&nbsp;
						<select id="tf_platform" name="tf_platform" class="form2">
							<option value="" <?php if($tf_platform == "") echo "selected" ?>>Selecione</option>
							<?php if($rs_platforms) while($rs_platforms_row = pg_fetch_array($rs_platforms)){ ?>					
							<option value="<? echo $rs_platforms_row['browser_platform']; ?>" <?php if ($tf_platform == $rs_platforms_row['browser_platform']) echo "selected";?>><? echo (($rs_platforms_row["browser_platform"])?$rs_platforms_row["browser_platform"]:"Vazio")." (".$rs_platforms_row["n"]." reg".(($rs_platforms_row["n"]>1)?"s":"").".)"; ?></option>
							<? } ?>
						</select>&nbsp;
					</td>
				    <td width="17%" class="texto" align="right">&nbsp;Navegador&nbsp;</td>
				    <td width="17%" class="texto">&nbsp;
						<select id="tf_browser" name="tf_browser" class="form2"> 	<?php //  onChange="change_select()" ?>
							<option value="" <?php if($tf_browser == "") echo "selected" ?>>Selecione</option>
							<?php if($rs_browsers) while($rs_browsers_row = pg_fetch_array($rs_browsers)){ ?>					
							<option value="<? echo $rs_browsers_row['browser_browser']; ?>" <?php if ($tf_browser == $rs_browsers_row['browser_browser']) echo "selected";?>><? echo (($rs_browsers_row["browser_browser"])?$rs_browsers_row["browser_browser"]:"Vazio")." (".$rs_browsers_row["n"]." reg".(($rs_browsers_row["n"]>1)?"s":"").".)"; ?></option>
							<? } ?>
						</select>&nbsp;
					</td>
				    <td width="17%" class="texto" align="right">&nbsp;Versão&nbsp;</td>
				    <td width="17%" class="texto">&nbsp;
						<?php if($rs_versions) { ?>
							<select id="tf_version" name="tf_version" class="form2"> 	<?php //  onChange="change_select()" ?>
								<option value="" <?php if($tf_version == "") echo "selected" ?>>Selecione</option>
								<?php if($rs_versions) while($rs_versions_row = pg_fetch_array($rs_versions)){ ?>					
								<option value="<? echo $rs_versions_row['browser_version']; ?>" <?php if ($tf_version == $rs_versions_row['browser_version']) echo "selected";?>><? echo (($rs_versions_row["browser_version"])?$rs_versions_row["browser_version"]:"Vazio")." (".$rs_versions_row["n"]." reg".(($rs_versions_row["n"]>1)?"s":"").".)"; ?></option>
								<? } ?>
							</select>
						<?php } ?>
						&nbsp;
					</td>
				  </tr>
				</table>
			</td>
		  </tr>
	 </table>