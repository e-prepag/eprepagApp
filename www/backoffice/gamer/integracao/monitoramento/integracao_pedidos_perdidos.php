<div class="lista bg-cinza-claro ">
        <strong>
            <a href="/gamer/integracao/com_pesquisa_integracao.php?tf_confirmed=&BtnSearch=1" target="_blank">PI1</a>, 
            <a href="/gamer/integracao/com_pesquisa_integracao.php?tf_confirmed=-1&BtnSearch=1" target="_blank">PI2</a>, 
            <a href="/gamer/integracao/com_pesquisa_integracao.php?tf_confirmed=1&BtnSearch=1" target="_blank">PI3</a>, 
            <a href="/gamer/integracao/com_pesquisa_integracao.php?tf_confirmed=2&BtnSearch=1" target="_blank">PI4</a>, 
<?php
                $tf_data_ini = date("d/m/Y", mktime(0,0,0,date("n"),1,date("Y")));
                $tf_data_fim = date("d/m/Y");
?>
            <a href="/gamer/integracao/com_pesquisa_integracao_day.php?tf_confirmed=&BtnSearch=1&tf_data_ini=<?php echo $tf_data_ini ?>&tf_data_fim=<?php echo $tf_data_fim ?>" target="_blank">PD1</a>, 
            <a href="/gamer/integracao/com_pesquisa_integracao_day.php?tf_confirmed=2&BtnSearch=1&tf_data_ini=<?php echo $tf_data_ini ?>&tf_data_fim=<?php echo $tf_data_fim ?>" target="_blank">PD2</a>, 
            <a href="/gamer/integracao/com_pesquisa_integracao_notificacao.php?BtnSearch=1" target="_blank">PN</a> ,
            <a href="/gamer/integracao/com_pesquisa_integracao_pedidos_perdidos.php" target="_blank">Perdidos</a>
            <a href="/gamer/integracao/com_pesquisa_integracao_pedidos_perdidos.php" target="_blank"><span style="color:red; background-color:#ffff66">Busca pedidos Perdidos</span></a>
        </strong>
    </div>