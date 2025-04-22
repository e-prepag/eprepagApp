<?php 
    $dif_max = 180;
    $contents = "";
    try {
        $smonfilename = $raiz_do_projeto . 'log/monitorprocessapagtoonline5.txt';
        if ($handle = fopen($smonfilename, 'r')) { 
            //echo "\nMonitor File opened\n";
            // Exemplo:
            // Tempo médio de processamento (2011-11-17 12:16:03): 0.22 s/processamento<br>11 pagamentos em aberto<br><br><br>
            $contents = fread($handle, filesize($smonfilename));
            $data_created = substr($contents, 30, 19);
            $dif = strtotime(date("Y-m-d H:i:s"))-strtotime($data_created); 
            //echo "[$data_created] ";
            //echo number_format($dif, 2, '.', '.')."<br>"; 
            fclose($handle);										
        } else {
            echo "\n<span class='txt-vermelho'>Error: Couldn't open Monitor File for reading</span>\n";
        }
    } catch (Exception $e) {
        echo "\n<span class='txt-vermelho'>Error(7) opening monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage()."</span>\n";
    }
    //echo "<div class='top10 lista bg-amarelo txt-azul'>Último processamento de Pagtos Online".str_replace("Incompleto", "<span class='txt-vermelho'>Incompleto</span>", str_replace("\n","<br>\n",$contents))."</span></div>";
    echo "<div class='top10 lista bg-amarelo txt-azul'>Último processamento de Pagtos Online".str_replace("Incompleto", "<span class='txt-vermelho'>Incompleto</span>", str_replace("\n","<br>\n",$contents))."<span class='".(($dif>$dif_max)?"txt-vermelho":"txt-preto")."'>Desde o último processamento: ".number_format($dif, 2, '.', '.')."s<br>"."</span></div>";