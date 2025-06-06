<?php

  foreach(scandir("/www/log") as $key => $value){
	  if($value != "." && $value != ".."){
		  if(is_numeric($value) && filesize($value) == 0 && filetype("/www/log/".$value) == "file"){
			   //echo "/www/log/".$value."<br>";
               unlink("/www/log/".$value);
		  }		  
	  }
  }  
	
?>