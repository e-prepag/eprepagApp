<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";

$dir_imgs = DIR_WEB."imagens/uploads/";

$imagem_del = isset($_GET['imagem_del']) ? $_GET['imagem_del'] : '';

if(!empty($imagem_del)) {
	unlink($dir_imgs.$imagem_del);
}

function buscanovidade($imagem_search) {
	$sql = "SELECT * FROM tbnovidade WHERE imagem LIKE '%".$imagem_search."%'";
	//echo $sql.';<br>';
	$rss = SQLexecuteQuery($sql);
	$tot = pg_num_rows($rss);
	
	if($tot == 0) {
		return $tot;
	} else {
		$row = pg_fetch_assoc($rss);
		extract($row);
		
		return $titulo;
	}
}
?>
<script>
	function removeImagem(dado) {
		var dado 	= dado;
		var answer  = confirm ("Deseja remover permanentemente essa Imagem?")

		if (answer) {
			window.open('com_imagem_manut.php?imagem_del='+dado,'_self');
		} else {
			return false;
		}
	}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>  
<table class="table txt-preto fontsize-pp">
  <tr>
    <td align="center" valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top">
          <td height="100%">

            
        <table class="table txt-preto table-bordered" id="tbresultado" name="tbresultado">
            <thead>
                  <tr bgcolor="" class="texto">
                    <th width="48%"><div align="center"><strong>Imagem</strong> </div></th>
                    <th width="29%" align="center"><strong>Novidade Relacionada</strong></th>
                    <th width="29%" align="center"><strong>Data Criação</strong></th>
                    <th width="22%" align="center"><strong>A&ccedil;&atilde;o</strong></th>
                  </tr>
              </thead>
				  <?php
				  $p = opendir($dir_imgs);
                                  if(is_dir($dir_imgs)) {
                                    while(($dado = readdir($p))!==false) {
                                          if($dado !='.' && $dado !='..' && $dado!='Thumbs.db') {
                        
                        if(!isset($object))
                            $object = null; 
                        
						$filename = $dir_imgs . $dado;
						$file_object = array(
							'name' => $object,
							'size' => filesize($filename),
							'type' => filetype($filename),
							'time' => date("d/m/Y H:i:s", filemtime($filename))
						);
						$novidade = buscanovidade($dado);
						//echo $novidade.'<br>';
					  	?>
                          <tr class="trListagem">
                            <td ><nobr><div align="left"><a href="<?php echo $server_url_ep ?>/imagens/uploads/<?php echo $dado ?>" target="_blank"><?php echo $dado; ?></a></div></nobr></td>
                            <td align="center"><nobr>
							<?php
                            if($novidade != '0') {
								echo utf8_decode($novidade); 
							}
                            ?>
		                        </nobr></td>
                            <td align="center" ><nobr>
								<?php 
									echo $file_object['time'];
								?>
	                            </nobr></td>
                            <td align="center" ><nobr>
							<?php 
							if($novidade == '0') {
								?>
                            	<a href="javascript:void(0);" onClick="removeImagem('<?php echo $dado; ?>');">remover</a>
                                <?php
							}
							?>
	                            </nobr></td>
                          </tr>   
						<?php
					}
				  }
                                }
                            ?>                  
                         
            </table>
			<?php
			/*
              <div id="pager" class="pager" style="top: 637px; position: absolute; ">
              <hr>
                <form>
                    <img src="css/first.png" class="first">
                    <img src="css/prev.png" class="prev">
                    <input type="text" class="pagedisplay">
                    <img src="css/next.png" class="next">
                    <img src="css/last.png" class="last">
                    <select class="pagesize">
                        <option selected="selected" value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="40">40</option>
                    </select>
                </form>
            </div>                
			*/
			?>
          </td>
        </tr>
      </table>
      </td>
  </tr>
  <tr>
    <td colspan="3">
	<?php
        require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
	?>
    <div align="center"></div></td>
  </tr>
</table>
</body>
</html>