<?php
/*
echo $_POST['csv_text']."<br>";
echo "size: ".strlen($_POST['csv_text'])."<br>";
die("Stop");
*/

/*
$file = "C:/Sites/E-Prepag/backoffice/offweb/tarefas/log/export_table.html";

//Grava mensagem no arquivo
if ($handle = fopen($file, 'w')) {
	fwrite($handle, $_POST['csv_text']);
	fclose($handle);
}	
*/
//die("Teste");
header("Content-type: application/vnd.ms-excel; name='excel'");
header("Content-Disposition: filename=export.xls");
$data = $_POST['csv_text'];
//$data = stripcslashes($_POST['csv_text']);
//$data = iconv('utf-8','iso-8859-1',$data);
echo $data; 
?>