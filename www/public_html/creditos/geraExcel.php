<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
   <table style="width: 100%;" border="1">
	  <thead>
		   <tr>
			   <th style="text-align: center;background-color: #246b89;color: white;">Data</th>
			   <th style="text-align: center;background-color: #246b89;color: white;">Id do pedido</th>
			   <th style="text-align: center;background-color: #246b89;color: white;">Pin</th>
			   <th style="text-align: center;background-color: #246b89;color: white;">Valor</th>
		   </tr>
	  </thead>
	  <tbody>
	   <?php foreach($_SESSION["pins"] as $key => $value){ ?>
		   <tr>
			   <td style="text-align: center;"><?php echo substr($value["vg_data_inclusao"], 8, 2)."/".substr($value["vg_data_inclusao"], 5, 2)."/".substr($value["vg_data_inclusao"], 0, 4);?></td>
			   <td style="text-align: center;"><?php echo $value["vg_id"];?></td>
			   <td style="text-align: center;">'<?php echo $value["pin_codigo"];?>'</td>
			   <td style="text-align: center;"><?php echo $value["vgm_valor"];?></td>
		   </tr>
	   <?php } ?>
	  </tbody>
    </table>
<?php

   if(empty($_SESSION["pins"]) || !isset($_SESSION["pins"])){
	   header("loction: " . EPREPAG_URL_HTTPS . "/creditos/pesquisa.php");
	   exit;
   }
   $arquivo = 'Pins-disponiveis.xls';
   header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
   header ("Cache-Control: no-cache, must-revalidate"); 
   header ("Pragma: no-cache");
   header ("Content-type: application/x-msexcel");
   header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" ); 
   //unset($_SESSION["pins"]);
?>
</body>
</html>