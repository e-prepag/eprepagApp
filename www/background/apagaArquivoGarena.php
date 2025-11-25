<?php

  foreach(scandir("/www/arquivos_gerados/logs") as $key => $value){
	  if($value != "." && $value != ".."){
		  if(is_numeric($value) && filesize($value) == 0 && filetype("/www/arquivos_gerados/logs/".$value) == "file"){
			   //echo "/www/arquivos_gerados/logs/".$value."<br>";
               unlink("/www/arquivos_gerados/logs/".$value);
		  }		  
	  }
  }  
	
?>