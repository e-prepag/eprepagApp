<?php
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
//Recupera qtde de usuarios com cadastro pendentes
$qtde_pendente = 0;
$spendente = "";
//	$sql  = "select count(*) as qtde from dist_usuarios_games where ug_ativo = 2 and ug_qtde_acessos = 0";
$sql  = "select ug_substatus, count(*) as qtde from dist_usuarios_games where ug_ativo = 2 group by ug_substatus";
$rs_pendente = SQLexecuteQuery($sql);
if($rs_pendente && pg_num_rows($rs_pendente) > 0){
        $spendente = "<table  class='table table-bordered top10'  width='50%'  title='Número de Lans em cada status'>\n <tr align='center'><th colspan='2'>";
        $spendente .= "Classif. ug_substatus</th></tr>\n";
        $scol1 = "#f0f0f0";
        $scol2 = "#eeffff";
        $scol = $scol1;

        while($rs_pendente_row = pg_fetch_array($rs_pendente)) {
//			$qtde_pendente = $rs_pendente_row['qtde'];
                $spendente .= "<tr><td align='right'><a href='/pdv/usuarios/com_pesquisa_usuarios.php?BtnSearch=1&tf_u_status=2&tf_u_substatus=".(($rs_pendente_row['ug_substatus']!='')?$rs_pendente_row['ug_substatus']:'v')."' class='menu'>".$SUBSTATUS_LH[$rs_pendente_row['ug_substatus']]."</a></td><td align='center'>".$rs_pendente_row['qtde']."</td></tr>\n";
                $scol = ($scol==$scol1)?$scol2:$scol1;
        }
        $spendente .= "</table>\n";
} else {
        $spendente = "<b>Erro ao procurar lans pendentes</b>";
}

if($tf_u_status_busca)
//Recupera qtde de usuarios por status da busca
$qtde_busca = 0;
$sbusca = "";
$sql  = "select ug_status, count(*) as qtde from dist_usuarios_games where ug_ativo = 1 group by ug_status";
$rs_pendente = SQLexecuteQuery($sql);
if($rs_pendente && pg_num_rows($rs_pendente) > 0){
        $sbusca = "<table class='table table-bordered top10' width='50%' title='Número de Lans em cada status'>\n <tr align='center'><th colspan='2'>";
        $sbusca .= "Classif. ug_status</th></tr>\n";
        $scol = $scol1;

        while($rs_pendente_row = pg_fetch_array($rs_pendente)) {
//			$qtde_pendente = $rs_pendente_row['qtde'];
                $stitle = (($rs_pendente_row['ug_status']=="1")?"BUSCA ATIVA":(($rs_pendente_row['ug_status']=="2")?"Busca inativa":"Status não definido"));
                $sbusca .= "<tr><td align='right'><a href='/pdv/usuarios/com_pesquisa_usuarios.php?BtnSearch=1&tf_u_status=1&tf_u_status_busca=".(($rs_pendente_row['ug_status']!='')?$rs_pendente_row['ug_status']:'v')."' class='menu' target='_blank'>$stitle (".$rs_pendente_row['ug_status'].")</a></td><td align='center'>".$rs_pendente_row['qtde']."</td></tr>\n";
                $scol = ($scol==$scol1)?$scol2:$scol1;
        }
        $sbusca .= "</table>\n";
        $sbusca .= "<a href='/pdv/usuarios/com_pesquisa_usuarios.php?BtnSearch=1&tf_u_codigo=".str_replace(" ", "", $CONST_ATIVA_BUSCA_LANS_BLACK_LIST)."' target='_blank' class='txt-preto'>black_list</a><br>\n";
        $sbusca .= "</b><span class='txt-preto fontsize-p p-right0'>".$CONST_ATIVA_BUSCA_LANS_BLACK_LIST."</span><br>\n";
} else {
        $sbusca = "<font color='#FF0000'><b>Erro ao procurar status da busca</b></font>";
}
?>

<div class="top10 row p0">
    <div class="col-md-6 fontsize-pp">
        <?php echo $spendente; ?>
    </div>
    <div class="col-md-6 fontsize-pp">
        <?php echo $sbusca; ?>
    </div>
</div>