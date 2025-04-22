<?php 
set_time_limit(3600);

require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
include_once $raiz_do_projeto."includes/gamer/constantes.php";
include_once $raiz_do_projeto."includes/complice/functions.php";

?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="clearfix"></div>
    <script>
        function fcnOnSubmit(){

            if(form1.opr_vinculo_empresa.value==''){
                alert('Empresa não especificada');
                return false;
            }

        }
    </script>
    <style>
        body {
            font-family: Verdana !important;
            font-size: 12px;
        }
        fieldset {
            width: 100%;
            margin-top: 35px;
            height: 240px;
            text-align: justify;
            border-radius: 10px;
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            -o-border-radius: 10px;
        }
        fieldset input[type="submit"] {
            margin-left: 80px;
            font-size: 15px;
            color: #FFFFFF;
            background-color: #A6A6A6;
            border: none;
            text-transform: none;
            font-weight: bold;
            padding: 5px 15px 5px 15px;
            border-radius: 10px;
            -webkit-border-radius: 10px;
        }
        fieldset form {
            margin-left: 13%;
            margin-top: 35px;
        }
        fieldset select {
            font-family: Verdana !important;
            font-size: 15px !important;
        }
        .msg {
            font-weight: 500;
            font-size: 17px;
            text-align: center;
        }
        .error {
            color: #ff423e;
        }
        .success {
            color: #29981b;
        }
        .wrapper_tbl {
            margin: 0 auto;
            width: 450px;
            border-collapse: collapse;
            border: 1px solid #b1b1b1;
        }
        .wrapper_tbl thead th {
            border-bottom: 1px solid #b1b1b1;
        }
        .odd{background-color: #ebfff6;}
        .odd:hover{background-color: #c9ded5;}

        .even{background-color: #f4ffe3;}
        .even:hover{background-color: #d3dec3;}
    </style>
<fieldset>
    <legend>Relatório de Usuários Ativos para o BACEN</legend>
    <br>
    Selecione a empresa para a geração de do relatório contendo os usuários ativos conforme descrição abaixo*. 
    <form name="form1" action="" enctype="multipart/form-data" method="post" onsubmit="return fcnOnSubmit();">
        <select id='opr_vinculo_empresa' name='opr_vinculo_empresa'>
            <option value='' <?php echo ((!isset($opr_vinculo_empresa) || $opr_vinculo_empresa=="")?" selected":"") ?>>Selecione</option>
            <option value='<?php echo $IDENTIFICACAO_EMPRESA_PAGAMENTOS; ?>' <?php echo ((isset($opr_vinculo_empresa) && $opr_vinculo_empresa==(string)$IDENTIFICACAO_EMPRESA_PAGAMENTOS)?" selected":"") ?>>E-Prepag Pagamentos</option>
            <option value='<?php echo $IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO; ?>' <?php echo ((isset($opr_vinculo_empresa) && $opr_vinculo_empresa==$IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO)?" selected":"") ?>>E-Prepag Administradora</option>
        </select>
        <input type="submit" name="BtnSubmit" value="Gerar Relatório" />
    </form>
    <br>
    * Quantidade de usuários finais ativos: nesse campo deve ser informado o número total de usuários finais ativos no último dia útil do ano-base definido, ou seja, que tenham utilizado, nos 90 dias anteriores, o serviço de pagamento disciplinado pelo
arranjo. Esse campo não será habilitado para arranjos classificados como relacionamento eventual segundo o critério de relacionamento do usuário final com a instituição participante.
</fieldset>
</body>
</html>
<?php
if(isset($BtnSubmit) && $BtnSubmit) {

    //Variável contendo a mensagem de erro.
    $msg = "";
    
    echo "<br><br>";

    //=========  Mês/Ano considerado no Elaboração dos Arquivos
    $currentmonth = mktime(0, 0, 0, date('n')-1, 1, date('Y'));
    $mesAno = date('m/Y',$currentmonth);

    if($opr_vinculo_empresa == $IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO) {
            //========= Variável contendo o Ano Mês inicio das operações 
            $dataInicioOperacao = 201407;
        
            // Split ano/mes
            list($mes, $ano) = explode("/", $mesAno);

            //Publishers Já em Operação constantes em arquivos BACEN anteriores
            $vetorPublisher = levantamentoPublisherOperantes($ano,$mes);
            
            //Publishers Já em Operação constantes em arquivos BACEN e Municipal anteriores
            //$vetorPublisher = array_merge(levantamentoPublisherOperantes($ano,$mes), levantamentoPublisherOperantesMunicipais($ano,$mes));

    } //end  if($opr_vinculo_empresa == $IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO) 
    else {
        
            //========= Variável contendo o Ano Mês inicio das operações 
            $dataInicioOperacao = (int)(date('Y')-1)."01";
        
            // Split ano/mes
            list($mes, $ano) = explode("/", $mesAno);

            //Publishers vinculados a E_prepag Pagamentos
            $vetorPublisher = array();

    } //end else do  if($opr_vinculo_empresa == $IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO) 

    // Teste de Abortagem
    $testeData = $ano.$mes;
    if($testeData < $dataInicioOperacao) {
        $msg = "O mês ano deve ser obrigatóriamente superior a ".$dataInicioOperacao." (AAAAMM).\n";
        die($msg);
    }// end if($testeData < 201403)

    if(empty($msg) && count($vetorPublisher) > 0 ) {
        
        echo '<table class="wrapper_tbl">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Quantidade de Usuários Ativos</th>
                        </tr>
                    </thead>
                    <tbody>
                ';
        //Contador para dar cores diferente nas linhas
        $i = 0;
        
        while($testeData >= $dataInicioOperacao) {
            
                $i++;
        
                // Capturando a quantidade total de cartões ativos
                $sql = "
                select   
                    count(distinct(ug_cpf_tmp)) as cartoes_ativos
                from 
                (
                ";
                $insere_union_all = 1;
                foreach ($vetorPublisher as $key => $value) {
                    if($insere_union_all > 1) {
                        $sql .= "

                        union all

                        ";
                    } //end if($insere_union_all > 1)

                    $sql .= "
                            (
                                select 
                                        ug_cpf as ug_cpf_tmp
                                from tb_venda_games vg 
                                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                        inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                                where vg.vg_ultimo_status = '".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                        and vg.vg_data_concilia >=  CASE
                                                                        WHEN 
                                                   (select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) 
                                                                        THEN 
                                                   (select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                                        ELSE CASE
                                                                                WHEN 
                                                   ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                                THEN 
                                                   ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) 
                                                                                ELSE 
                                                   ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                                END
                                                                        END
                                        and vg.vg_data_concilia <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                                        and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' 
                                        and vgm_opr_codigo  = ".$value."
                            )    

                        union all

                            (
                                select 
                                        vgm_cpf as ug_cpf_tmp
                                 from tb_dist_venda_games vg 
                                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                 where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                        and vg.vg_data_inclusao >=  CASE 
                                                                        WHEN 
                                                   (select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) 
                                                                        THEN 
                                                   (select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                                        ELSE  CASE 
                                                                                WHEN 
                                                    ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                                THEN 
                                                    ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) 
                                                                                ELSE 
                                                    ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                                END
                                                                        END
                                        and vg.vg_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                                        and vgm_opr_codigo  = ".$value." 
                            )

                        union all

                            (
                                select 
                                        picc_cpf as ug_cpf_tmp
                                from pins_integracao_card_historico
                                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                                where pin_status = '4' 
                                        and pih_codretepp = '2'
                                        and pih_data >=  CASE 
                                                                        WHEN 
                                                   (select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) 
                                                                        THEN 
                                                   (select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                                        ELSE  CASE 
                                                                                WHEN 
                                                    ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                                THEN 
                                                    ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) 
                                                                                ELSE 
                                                    ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                                END
                                                                        END
                                        and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                                        and pih_id  = ".$value." 
                            )
                        
                     union all

                        (
                            select 
                                    vgcbe_cpf as ug_cpf_tmp
                            from tb_venda_games_cpf_boleto_express
                                inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                                inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
			    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                    and vgcbe_data_inclusao >=  CASE 
                                                                        WHEN 
                                                   (select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) > ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) 
                                                                        THEN 
                                                   (select to_char(opr_data_inicio_operacoes, 'YYYY-MM-DD 00:00:00')::timestamp  from operadoras where opr_codigo = ".$value." ) 
                                                                        ELSE  CASE 
                                                                                WHEN 
                                                    ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) > ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                                THEN 
                                                    ('".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'::timestamp - '3 months'::interval) 
                                                                                ELSE 
                                                    ('".substr($dataInicioOperacao,0,4)."-".substr($dataInicioOperacao,4,2)."-01 00:00:00'::timestamp) 
                                                                                END
                                                                        END
                                    and vgcbe_data_inclusao <= '".getEndDateTrimestral(($mes),$ano)." 23:59:59'
                                    and vgm_opr_codigo  = ".$value." 
                        )                        
                ";
                    $insere_union_all++;

                }//end foreach ($vetorPublisher as $key => $value)

                $sql .= " 
                )  tabelaUnion     
                ";

                //echo $sql."\n"; die();

                $rsInfoAtivos = SQLexecuteQuery($sql);
                if(!$rsInfoAtivos) echo "Erro ao selecionar o Cartões Ativos.<br>\n";
                else { 
                    //Capturando Dados Cartões Emitidos
                    $rsInfoAtivos_row = pg_fetch_array($rsInfoAtivos);
                    echo "<tr class='".(($i&1) ? "odd" : "even")."'>
                                <td align='center'>".$ano."/".$mes."</td>
                                <td align='right'>".$rsInfoAtivos_row['cartoes_ativos']."</td>
                          </tr>\n";
                }//end else do if(!$rsDesvio)

                $currentmonth = mktime(0, 0, 0, $mes-1, 1, $ano);
                $mesAno = date('m/Y',$currentmonth);

                // Split ano/mes
                list($mes, $ano) = explode("/", $mesAno);

                // Teste de Abortagem
                $testeData = $ano.$mes;

        }//end while($testeData >= $dataInicioOperacao)
        echo ' </tbody>
            </table>';

    } //end if(empty($msg) && count($vetorPublisher) > 0 ) 

}//end if($BtnSubmit)

?>
<br>  
<br>  
<br>  
<br>  
