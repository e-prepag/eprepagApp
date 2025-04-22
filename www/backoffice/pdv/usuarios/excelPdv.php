<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
<?php
   session_start();

   $arquivo = 'PDVs-pendentes.xls';
	
   header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
   header ("Cache-Control: no-cache, must-revalidate");
   header ("Pragma: no-cache");
   header ("Content-type: application/x-msexcel");
   header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" ); 
	
   echo $_SESSION["excelPdv"];
   unset($_SESSION["excelPdv"]);
   exit;

?>
</body>
</html>