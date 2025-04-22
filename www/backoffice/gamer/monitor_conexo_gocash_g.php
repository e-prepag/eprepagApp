    <div class="top10 lista bg-azul-claro txt-branco">
<?php

    require_once $raiz_do_projeto . "banco/gocash/config.inc.php";
    require_once $raiz_do_projeto . "includes/gamer/functions.php";

    gocash_monitor_load($params_in);
    //echo "<pre>".print_r($params_in, true)."</pre>";
    echo get_gocash_monitor_info($params_in)."</div>";
?>
    </div>