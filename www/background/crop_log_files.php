<?php
$logs_path = "/www/log/";
$logs_bkp_path = "/var/log/aplicacao/";
$atipo_ext = array("LOG", "TXT");
$max_size = 5*1024*1024; // 5Mb 
$snome_novo = "_".date("Y_m_d_H");
$a_exclusion_list = array("CRIPTOGAMERS.LOG");

echo str_repeat("=", 80)."\n".date("Y-m-d H:i:s").PHP_EOL;
echo "Processa dir: $logs_path".PHP_EOL;
echo "Salva em dir: $logs_bkp_path".PHP_EOL;
echo "Arquivos nao processados: ".implode(",", $a_exclusion_list).PHP_EOL;
echo "Extensoes processadas: ".implode(",", $atipo_ext).PHP_EOL;
echo "Tamanho maximo: $max_size (".number_format($max_size/1024/1024, 2, '.', '.')."Mb)".PHP_EOL;
echo "Renomeia para : $snome_novo".PHP_EOL;

if (is_dir($logs_path)) {
	$i = 0;
	$current_dir = @opendir($logs_path);	
	echo PHP_EOL;
        if(is_dir($logs_path)) {
            while($filename = @readdir($current_dir)) {
                    echo "";
                    if ($filename != "." && $filename != "..") {
                            $fsize = filesize($logs_path.$filename);
                            $a_parts = explode(".", $filename);

                            // Se não é diretorio
                            $dir = $logs_path.$filename;
                            if (!is_dir($dir)) {

                                    $fname  = $a_parts[0];
                                    $ext	= strtoupper($a_parts[1]);

                                    echo "$i ".$logs_path." - ".$filename." ".$fsize."   ".number_format($fsize/1024/1024, 2, '.', '.')."Mb ";
                                    echo " $ext ";
                                    echo "".(($fsize>$max_size)?"CROP_size":"")." ".((in_array($ext, $atipo_ext))?"CROP_ext":"")." ";
                                    echo "".((in_array(strtoupper($fname.".".$ext), $a_exclusion_list))?"DON'T_CROP_exception":"")." ";

                                    $filename_new = "";
                                    echo " ";
                                    if ($fsize>$max_size && in_array($ext, $atipo_ext) && (!in_array(strtoupper($fname.".".$ext), $a_exclusion_list)) ) {
                                            echo " -> CROP THIS FILE";
                                            $filename_new = $fname.$snome_novo.".".$ext;
                                    }
                                    echo " ";

                                    if($filename_new) {
                                            echo " $filename_new ('$logs_path$filename' -> '$logs_bkp_path$filename_new') ";
                                            rename($logs_path.$filename, $logs_bkp_path.$filename_new);
                                    } else {
                                            echo "  ";
                                    }
                                    $i++;
                            } else {
                                    echo "$i ".$logs_path." - ".$filename." IS DIR - DON'T PROCESS".PHP_EOL;
                            }
                    }
                    echo PHP_EOL;
            }	
        }
	echo PHP_EOL.PHP_EOL;
	@closedir($current_dir);

} else {
	echo "NOT DIR - don't process ($dir)".PHP_EOL;
}

?>