<?php
require_once "/www/backoffice/includes/encoding.php";
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')) {
       echo "Chamada não permitida<br>";
       die("Stop");
}
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";

date_default_timezone_set('America/Fortaleza');
$opr = $opr ?? 0;
$str = $str ?? null;
$otni_id = $otni_id ?? "";
$data = $data ?? "";
$sqlOperadoras = "select * from operadoras where opr_codigo = $1";
$rs_operadoras = SQLexecuteQueryParams($sqlOperadoras, array($opr));

//opr_codigo integer NOT NULL, -- Código do Publisher - Corresponde ao campo opr_codigo na tabela Operadoras
//otni_data timestamp with time zone NOT NULL, -- Campo contendo a data de alteração de nacionalidade.
//otni_origem smallint NOT NULL, -- Campo contendo a origem da direação, onde 0 = Nacional e 1 = Internacional.
//otni_destino smallint NOT NULL -- Campo contendo o destino da direção, onde 0 = Nacional e 1 = Internacional.
$erro = false;

if((($rs_operadoras) ? pg_num_rows($rs_operadoras) : 0) != 1)
    $erro = true;
 
if($str == 0){
    $origem = "0";
    $destino = "1";
    
}elseif($str == 1){
    $origem = "1";
    $destino = "0";
}else{
    $erro = true;
}

if(isset($origem) && !$erro){
    $sql_operadoras = "update operadoras set opr_vinculo_empresa = 1, opr_troca_nacional_internacional = 1 where opr_codigo = $1";
    $rs_operadoras = SQLexecuteQueryParams($sql_operadoras, array($opr));
    
    if($otni_id != ""){
        $sql = "update operadoras_troca_nacional_internacional set otni_data = to_date($1,'DD/MM/YYYY'), otni_origem = $2, otni_destino = $3 where otni_id = $4 and opr_codigo = $5";
        $params = array($data, $origem, $destino, $otni_id, $opr);
    }else{
        $sql = "insert into operadoras_troca_nacional_internacional (opr_codigo, otni_data, otni_origem, otni_destino) values ($1, to_date($2,'DD/MM/YYYY'), $3, $4)";
        $params = array($opr, $data, $origem, $destino);
    }
    
    $rs = SQLexecuteQueryParams($sql, $params);
    
    if($rs && $rs_operadoras){
        $sqlTrocaNacionalInternacional = "select * from operadoras_troca_nacional_internacional where opr_codigo = $1 order by otni_id desc";
        $rs_TrocaNacionalInternacional = SQLexecuteQueryParams($sqlTrocaNacionalInternacional, array($opr));
        
        if($rs_TrocaNacionalInternacional){
            $ret = '<div class="borda bloco top10">
                    <p>Clique para editar</p>
                    <table class="row text-center bordaTabela" >
                        <thead class="">
                            <tr>
                                <th>Data de Alteração</th>
                                <th>Origem</th>
                                <th>Destino</th>
                            </tr>
                        </thead>
                        <tbody title="Clique para editar" id="tbodyNacionalInternacional">';
            
            while($rs_row = pg_fetch_array($rs_TrocaNacionalInternacional)) {
                $data = explode(" ",$rs_row['otni_data']);
                $data = explode("-",$data[0]);
                $data = $data[2]."/".$data[1]."/".$data[0];
                
                if($rs_row['otni_origem'] == 1){
                    $origem = "Internacional";
                }else{
                    $origem = "Nacional";
                }
                
                if($rs_row['otni_destino'] == 1){
                    $destino = "Internacional";
                }else{
                    $destino = "Nacional";
                }
                
                $ret .= "<tr class='bannersOpt' onclick='callEditNacInc(this.id)' id='".$rs_row['otni_id']."'>
                            <td class='style1'>".$data."</td> 
                            <td class='style1'>".$origem."</td> 
                            <td class='style1'>".$destino."</td> 
                        </tr>";
            }
            
            $ret .= '</tbody>
                    </table></div>';
            
            print backoffice_iso_to_utf8($ret);
        }else{
            $erro = true;
        }
        
    }else{
        $erro = true;
    }
}

if($erro)
    print false;