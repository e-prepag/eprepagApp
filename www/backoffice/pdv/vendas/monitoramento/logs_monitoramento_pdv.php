<?php
$contents = "";
    
try {
    $smonfilename = $raiz_do_projeto . 'log/monitoragendamentos12.txt';
    if (file_exists($smonfilename) && $handle = fopen($smonfilename, 'r')) { 
            //echo "\nMonitor File opened\n";
            $contents = fread($handle, filesize($smonfilename));

            fclose($handle);

    } else {
            echo "\n<font color='#FF0000'>Error: Couldn't open Monitor File for reading</font><br>\n";
    }
    $smonfilename = $raiz_do_projeto . 'log/monitoragendamentos34.txt';
    if (file_exists($smonfilename) && $handle = fopen($smonfilename, 'r')) { 
            //echo "\nMonitor File opened\n";
            $contents .= fread($handle, filesize($smonfilename));

            fclose($handle);

    } else {
            echo "\n<font color='#FF0000'>Error: Couldn't open Monitor File for reading</font><br>\n";
    }
    $smonfilename = $raiz_do_projeto . 'log/monitoragendamentos56.txt';
    if (file_exists($smonfilename) && $handle = fopen($smonfilename, 'r')) { 
            //echo "\nMonitor File opened\n";
            $contents .= fread($handle, filesize($smonfilename));

            fclose($handle);

    } else {
            echo "\n<font color='#FF0000'>Error: Couldn't open Monitor File for reading</font><br>\n";
    }
    $smonfilename = $raiz_do_projeto . 'log/monitoragendamentos78.txt';
    if (file_exists($smonfilename) && $handle = fopen($smonfilename, 'r')) { 
            //echo "\nMonitor File opened\n";
            $contents .= fread($handle, filesize($smonfilename));

            fclose($handle);

    } else {
            echo "\n<font color='#FF0000'>Error: Couldn't open Monitor File for reading</font><br>\n";
    }
    $smonfilename = $raiz_do_projeto . 'log/monitoragendamentos90.txt';
    if (file_exists($smonfilename) && $handle = fopen($smonfilename, 'r')) { 
            //echo "\nMonitor File opened\n";
            $contents .= fread($handle, filesize($smonfilename));

            fclose($handle);

    } else {
            echo "\n<font color='#FF0000'>Error: Couldn't open Monitor File for reading</font><br>\n";
    }
} catch (Exception $e) {
        echo "\n<font color='#FF0000'>Error(7) opening monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage()."</font><br>\n";
}

echo "<div class='espacamento-laterais p-top10' style='background-color:#FFFF99'>Último processamento de agendamentos<br>".str_replace("Incompleto", "<font color='#FF0000'>Incompleto", str_replace("\n","<br>\n",$contents))."</div>";