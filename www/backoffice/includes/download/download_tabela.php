<?php

//error_reporting(E_ALL); 
//ini_set("display_errors", 1);

$filename = isset($_REQUEST['filename']) ? $_REQUEST['filename'] : 'arquivo.txt';

header("Content-Description: File Transfer");
header('Content-Type: application/octet-stream;');
header('Content-Disposition: attachment; filename="'. $filename);

?>
<?php echo base64_decode($_REQUEST['content']); ?>