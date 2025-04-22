<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php"; 

$texto = file_get_contents('http://127.0.0.1:81/server-status');

// Verifica tipo de monitor
if($tipo=="M") {
    $textoAux = substr($texto, (strpos($texto,"requests currently being processed")-10) , 40);
    echo "<div class='text140 txt-vermelho text-center'>".substr($textoAux, (strpos(strtoupper($textoAux),"<DT>")), 6)."</div><div class='text17 text-center'>Acessos</div>";
} else if($tipo=="L") {
    $textoAux = substr($texto, (strpos($texto,"Restart Time:")+strlen("Restart Time:")) , 100);
    echo "<div class='top50 text17'>Último Reinício do Apache:</div><div class='text17 txt-vermelho'>".substr($textoAux, 0, strpos(strtoupper($textoAux),"</DT>"))."</div>";
    $textoAux = substr($texto, (strpos($texto,"Server uptime:")+strlen("Server uptime:")) , 100);
    echo "<div class='top20 text17'>Tempo em execução:</div><div class='text17 txt-vermelho'>".substr($textoAux, 0, strpos(strtoupper($textoAux),"</DT>"))."</div>";
    $textoAux = substr($texto, strpos($texto,"Total accesses:") , 120);
    echo "<div class='top20 txt-vermelho text17'>".substr($textoAux, 0, strpos(strtoupper($textoAux),"</DT>"))."</div><div class='top30 txt-branco'>.</div>";
}

echo "<div class='top50 text13 text-center'>".date("Y-m-d H:i:s")."</div>";
		
//Fechando Conexão
pg_close($connid);

?>
