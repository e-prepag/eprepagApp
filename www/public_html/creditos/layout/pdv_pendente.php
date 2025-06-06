<?php
   function criaArquivo(){ 
	   $fileName = "PDV_".date("Ymd").".txt";
	   $file = fopen("pdvLog/".$fileName, "a+");
	   fclose($file);
   }
   
   function gravaDados($dados){
	   $fileName = "PDV_".date("Ymd").".txt";
	   $file = fopen("pdvLog/".$fileName, "a");
	   fwrite($file, "username,".$_POST["username"].",email,".$_POST["email"]."\n");
	   fclose($file);
   }
     
   criaArquivo();
   gravaDados($_POST);
?>