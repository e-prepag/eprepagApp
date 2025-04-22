    <div class="top10 lista bg-amarelo txt-azul">
<?php
    require_once $raiz_do_projeto . "includes/gamer/constantes.php";
    //require_once $raiz_do_projeto . "includes/gamer/main.php";
    require_once $raiz_do_projeto . "class/gamer/class_bank_sonda.php";
    $bank_sonda = new bank_sonda();
    $bank_sonda->load_banks_sonda_array();
    echo "Data do monitor: <b>".$bank_sonda->get_date_last_db_restore()."</b>";
    echo $bank_sonda->list_registers(true);
?>
    </div>