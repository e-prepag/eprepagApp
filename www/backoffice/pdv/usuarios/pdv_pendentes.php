<?php
   session_start();
   require_once '../../../includes/constantes.php';
   require_once "/www/includes/main.php";
   require_once $raiz_do_projeto."backoffice/includes/topo.php";
   
   if(isset($_POST["btn-envia"])){
	    
	   $listArquivo = [];
	   $arquivosDir = scandir("/www/public_html/creditos/layout/pdvLog");
	   for($num = 0; $num < count($arquivosDir); $num++){
		   if($arquivosDir[$num] != "." && $arquivosDir[$num] != ".."){
			   array_push($listArquivo, $arquivosDir[$num]);
		   }
	   }
	  
	   function estruturaExcel($info){
		   $html = "<table border='1'>";
			$html .= "<tr><td style='text-align:center;color:#fff;background-color:#268fbd;font-size:24px;' colspan='8'><b>PDVs que não finalizaram o cadastro</b></td></tr>";
			$html .= "<thead>";
				$html .= "<tr>";
					 $html .= "<th colspan='4'><b>Username</b></th>";
					 $html .= "<th colspan='4'><b>E-mail</b></th>";
				$html .= "</tr>";
			$html .= "</thead>";
			$html .= "<tbody>";
			for($num = 0; $num < count($info); $num++){
				$html .= "<tr>
					 <td style='text-align:center;' colspan='4'>".$info[$num][1]."</td>
					 <td style='text-align:center;' colspan='4'>".$info[$num][3]."</td>
				 </tr>";
			}
			$html .= "</tbody>";
			$html .= "</table>";
            $_SESSION["excelPdv"] = $html;			
	   }
	   
	   if(count($listArquivo) > 0){
           $dados = [];
		   for($num = 0; $num < count($listArquivo); $num++){
		       $dataArq = substr($listArquivo[$num], 4, 8);
			   if($dataArq >= str_replace("-", "", $_POST["dtMin"]) && $dataArq <= str_replace("-", "", $_POST["dtMax"])){
				  $fileName = "/www/public_html/creditos/layout/pdvLog/".$listArquivo[$num];
				  $file = fopen($fileName, "r");
                  while(!feof($file)){
					  $linha = explode(",", fgets($file));
					  if(count($linha) != 1){
							$emails = array_column($dados, 3);
							if(!in_array($linha[3], $emails)){
								
								$sql = SQLexecuteQuery("select ug_id from dist_usuarios_games where ug_email in('".trim(strtoupper($linha[3]))."','".trim(ucfirst($linha[3]))."','".trim($linha[3])."');");
								$resultadoDB = pg_fetch_assoc($sql);
								
								if($resultadoDB === false){
								   $dados[] = $linha;
								}
							} 
					  }			  
				  }				  
			   }
	       }
		   
		   if(isset($dados)){
               $emails = array_column($dados, 3);
                //echo '<script>console.log('.json_encode($emails).')</script>';
			   estruturaExcel($dados);   
		   }
	   }
   }
   
?>
<style>
   #titulo{
	    font-size: 1.6em;   
		color: #000;
		margin-left: 8px;
   }
   .btn-enviar{
	   padding: 10px 15px;
	   color: #fff;
	   border: none;
	   border-radius: 5px;
	   background-color: #198754;
   }
   input[type="date"]{
	   width: 200px;
	   font-size: 17px;
	   margin: 8px;
   }
   #info{
	   margin-top: 20px;
	   margin-left: 8px;
	   clear: both;
	   width: calc(100% - 15px);
   }
   #info th{
	   padding: 10px;
	   text-align: center;
	   background-color: #dddddd;
	   color: #000;
   }
   #info td{
	   color: #000;
	   text-align: center;
	   padding: 10px;
   }
   .btn-excel{
	   margin-right: 7px;
	   margin-bottom: 10px;
       display: block;
	   float: right;
       width: fit-content;
   }
   .btn-enviar:hover{
	   color: #dddddd;
   }
</style>
<form method="POST">
     <h2 id="titulo">Pesquisa de PDVs com cadastro pendente</h2>
     <input value="<?php echo isset($_POST["dtMin"])?$_POST["dtMin"]:"";?>" max="<?php echo date("Y-m-d");?>" name="dtMin" type="date" id="dtMin" required>
	 <input value="<?php echo isset($_POST["dtMax"])?$_POST["dtMax"]:"";?>" max="<?php echo date("Y-m-d");?>" name="dtMax" type="date" id="dtMax" required>
	 <input type="submit" name="btn-envia" value="Buscar" class="btn-enviar" id="btn-enviar">
</form>
<?php 
    if(isset($_POST["btn-envia"])){ 
	   if(isset($dados)){
?>
          <a href="excelPdv.php" class="btn-enviar btn-excel">Excel</a>
<?php  }  ?>
<table id="info" border="1">
    <thead>
	     <tr>
		     <th>Username</th>
			 <th>E-mail</th>
		 </tr>
	</thead>
	<tbody>
	     <?php
		    if(isset($dados) && count($dados) > 0){
			   for($num = 0; $num < count($dados); $num++){
		?>
			 <tr>
				 <td><?php echo $dados[$num][1];?></td>
				 <td><?php echo $dados[$num][3];?></td>
			 </tr>
        <?php		
			   }		
			}else{
		?>
			 <tr>
				 <td colspan="2">Nenhum registro encontrado</td>
			 </tr>
		<?php
			}
		 ?>
	</tbody>
</table>
<?php
   }
   require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>