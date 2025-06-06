<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

function verificaFaltaCPFNome($vetorPublisher, $diaLimite, &$rs_dados_incompletos, $vetorPublisherNovos = NULL) {

        /*********************************************
         ***  Dia Limite para geração dos arquivos 
         *********************************************
        $diaLimite
        */
    
        // Instanciando a variavel para verificação
        $verificadorPublishersNovos =implode(",", $vetorPublisherNovos);

        //=========  Mês/Ano
        if((int)date('j') <= (int)$diaLimite) {
            $currentmonth = mktime(0, 0, 0, date('n')-1, 1, date('Y'));
        } //end if(date('j') <= 10)
        else {
            $currentmonth = mktime(0, 0, 0, date('n'), 1, date('Y'));
        } //end else do if(date('j') <= 10)
        $mesAno = date('m/Y',$currentmonth);

        // Split ano/mes
        list($mes, $ano) = explode("/", $mesAno);

        // Buscando informações 
        $sql = "select 
                        ug_cpf, 
                        ug_nome,
                        ug_id,
                        ug_email,
                        min(data) as data_transacao,
                        tipo
                from ( 
                    (select 
                            ug_cpf, 
                            ug_nome_cpf as ug_nome,
                            ug_id::character varying,
                            ug_email,
                            vg.vg_data_concilia as data,
                            'GAMER' as  tipo
                    from tb_venda_games vg 
                            inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                            inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                    where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                            and vg.vg_data_concilia >= '".$ano."-".$mes."-01 00:00:00'
                            and vg.vg_data_concilia <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                            and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' 
                            and (
				ug_cpf is null
				OR
				ug_nome_cpf is null
				OR
				length(ug_cpf) < 14 
				OR
				ug_nome_cpf = ''
                            )
                            and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                    group by ug_cpf, ug_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo, vg_data_concilia)

                    union all

                    (select 
                            vgm_cpf as ug_cpf, 
                            vgm_nome_cpf as ug_nome, 
                            ug_id::character varying,
                            ug_email,
                            vg.vg_data_inclusao as data,
                            'LAN HOUSE' as  tipo
                    from tb_dist_venda_games vg 
                            inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                            inner join dist_usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                    where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                            and vg.vg_data_inclusao >= '".$ano."-".$mes."-01 00:00:00'
                            and vg.vg_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                            and (
                                    vgm_cpf is null
                                    OR
                                    vgm_nome_cpf is null
                                    OR
                                    length(vgm_cpf) < 14 
                                    OR
                                    vgm_nome_cpf = ''
                            )
                            and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                    group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo, vg_data_inclusao)

                    union all

                    (select 
                        picc_cpf as ug_cpf, 
                        picc_nome as ug_nome, 
                        'ID PIN:'||pih_pin_id as ug_id,
                        '' as ug_email,
                        pih_data as data,
                        'CARTAO' as  tipo
                    from pins_integracao_card_historico
                            left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                    where pin_status = '4' 
                        and pih_codretepp = '2'
                        and pih_data >= '".$ano."-".$mes."-01 00:00:00'
                        and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                        and (
                            picc_cpf is null
                            OR
                            picc_nome is null
                            OR
                            length(picc_cpf) < 14 
                            OR
                            picc_nome = ''
                        )
                        and pih_id IN (".implode(",", $vetorPublisher).")  
                    group by picc_cpf, picc_nome, pih_pin_id, ug_email, pih_data, tipo)
                    
                    union all

                    (select 
                        vgcbe_cpf as ug_cpf, 
                        vgcbe_nome_cpf as ug_nome, 
                        'ID Venda:'||vgcbe_vg_id as ug_id,
                        vgcbe_ex_email as ug_email,
                        vgcbe_data_inclusao as data,
                        'BOLETO EXPRESS' as  tipo
                    from tb_venda_games_cpf_boleto_express
			inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
			inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vgcbe_data_inclusao >= '".$ano."-".$mes."-01 00:00:00'
                        and vgcbe_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                        and (
                            vgcbe_cpf is null
                            OR
                            vgcbe_nome_cpf is null
                            OR
                            length(vgcbe_cpf) < 14 
                            OR
                            vgcbe_nome_cpf = ''
                        )
                        and vgm_opr_codigo IN (".implode(",", $vetorPublisher).")  
                    group by vgcbe_cpf, vgcbe_nome_cpf, vgcbe_vg_id, vgcbe_ex_email, vgcbe_data_inclusao, tipo)
                    ";
        if(!empty($verificadorPublishersNovos)) {
            foreach ($vetorPublisherNovos as $key => $value) {
                //echo "Key: $key -- value: $value <br>";
                $sql .= "

                  union all

                     (select 
                                ug_cpf, 
                                ug_nome_cpf as ug_nome,
                                ug_id::character varying,
                                ug_email,
                                vg.vg_data_concilia as data,
                                'GAMER' as  tipo
                        from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                        where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                and vg.vg_data_concilia >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                                and vg.vg_data_concilia <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                                and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' 
                                and (
                                    ug_cpf is null
                                    OR
                                    ug_nome_cpf is null
                                    OR
                                    length(ug_cpf) < 14 
                                    OR
                                    ug_nome_cpf = ''
                                )
                                and vgm_opr_codigo = ".$value."
                        group by ug_cpf, ug_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo, vg_data_concilia)

                        union all

                        (select 
                                vgm_cpf as ug_cpf, 
                                vgm_nome_cpf as ug_nome, 
                                ug_id::character varying,
                                ug_email,
                                vg.vg_data_inclusao as data,
                                'LAN HOUSE' as  tipo
                        from tb_dist_venda_games vg 
                                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                inner join dist_usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                        where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                                and vg.vg_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                                and vg.vg_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                                and (
                                    vgm_cpf is null
                                    OR
                                    vgm_nome_cpf is null
                                    OR
                                    length(vgm_cpf) < 14 
                                    OR
                                    vgm_nome_cpf = ''
                                )
                                and vgm_opr_codigo = ".$value."
                        group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo, vg_data_inclusao)

                        union all

                        (select 
                            picc_cpf as ug_cpf, 
                            picc_nome as ug_nome, 
                            'ID PIN:'||pih_pin_id as ug_id,
                            '' as ug_email,
                            pih_data as data,
                            'CARTAO' as  tipo
                        from pins_integracao_card_historico
                                left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                        where pin_status = '4' 
                            and pih_codretepp = '2'
                            and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                            and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                            and (
                                picc_cpf is null
                                OR
                                picc_nome is null
                                OR
                                length(picc_cpf) < 14 
                                OR
                                picc_nome = ''
                            )
                            and pih_id = ".$value." 
                        group by picc_cpf, picc_nome, pih_pin_id, ug_email, pih_data, tipo)

                        union all

                        (select 
                            vgcbe_cpf as ug_cpf, 
                            vgcbe_nome_cpf as ug_nome, 
                            'ID Venda:'||vgcbe_vg_id as ug_id,
                            vgcbe_ex_email as ug_email,
                            vgcbe_data_inclusao as data,
                            'BOLETO EXPRESS' as  tipo
                        from tb_venda_games_cpf_boleto_express
                            inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                            inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                        where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                            and vgcbe_data_inclusao >=  (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                            and vgcbe_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                            and (
                                vgcbe_cpf is null
                                OR
                                vgcbe_nome_cpf is null
                                OR
                                length(vgcbe_cpf) < 14 
                                OR
                                vgcbe_nome_cpf = ''
                            )
                            and vgm_opr_codigo = ".$value." 
                        group by vgcbe_cpf, vgcbe_nome_cpf, vgcbe_vg_id, vgcbe_ex_email, vgcbe_data_inclusao, tipo)
                         ";
            }//end foreach
        } //end if(!empty($verificadorPublishersNovos))
        $sql .= "
        ) tabelaUnion 
                    group by ug_cpf, ug_nome, ug_id, ug_email, tipo 
                    order by tipo, ug_id;  
            ";

        //echo $sql.PHP_EOL; die();
        $rs_dados_incompletos = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_dados_incompletos)."<br>";
        if(!$rs_dados_incompletos) {
            echo "Erro na Query de Levantamento de CPFs e/ou Nome em Branco para os Publishers (".implode(",", $vetorPublisher).") e Publishers Novos (".implode(",", $vetorPublisherNovos).").<br>".PHP_EOL;
            return false;
        }
        if(pg_num_rows($rs_dados_incompletos) == 0) {
            //echo "Vai retorna Falso. Ou seja, NÃO possui dados faltantes.<br>";
            return false;
        }//end if(!$rs_dados_incompletos || pg_num_rows($rs_dados_incompletos) == 0)
        else {
            //echo "Vai retorna verdadeiro. Ou seja, possui dados faltantes.<br>";
            return true;
        }//end else
    
}//end function verificaFaltaCPFNome

function verificaCPFValido($vetorPublisher, $diaLimite, &$rs_dados, $vetorPublisherNovos = NULL) {

        /*********************************************
         ***  Dia Limite para geração dos arquivos 
         *********************************************
        $diaLimite
        */
        
        // Instanciando a variavel para verificação
        $verificadorPublishersNovos =implode(",", $vetorPublisherNovos);

        //=========  Mês/Ano
        if((int)date('j') <= (int)$diaLimite) {
            $currentmonth = mktime(0, 0, 0, date('n')-1, 1, date('Y'));
        } //end if(date('j') <= 10)
        else {
            $currentmonth = mktime(0, 0, 0, date('n'), 1, date('Y'));
        } //end else do if(date('j') <= 10)
        $mesAno = date('m/Y',$currentmonth);

        // Split ano/mes
        list($mes, $ano) = explode("/", $mesAno);

        // Buscando informações 
        $sql = "select 
                        ug_cpf, 
                        ug_nome,
                        ug_id,
                        ug_email,
                        tipo
                from ( 
                    (select 
                            ug_cpf, 
                            ug_nome_cpf as ug_nome,
                            ug_id::character varying,
                            ug_email,
                            'GAMER' as  tipo
                    from tb_venda_games vg 
                            inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                            inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                    where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                            and vg.vg_data_concilia >= '".$ano."-".$mes."-01 00:00:00'
                            and vg.vg_data_concilia <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                            and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' 
                            and (
				ug_cpf is not null
				OR
				ug_nome_cpf is not null
				OR
				length(ug_cpf) = 14 
				OR
				ug_nome_cpf != ''
                            )
                            and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                    group by ug_cpf, ug_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo)

                    union all

                    (select 
                            vgm_cpf as ug_cpf, 
                            vgm_nome_cpf as ug_nome, 
                            ug_id::character varying,
                            ug_email,
                            'LAN HOUSE' as  tipo
                    from tb_dist_venda_games vg 
                            inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                            inner join dist_usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                    where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                            and vg.vg_data_inclusao >= '".$ano."-".$mes."-01 00:00:00'
                            and vg.vg_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                            and (
				vgm_cpf is not null
                                OR
				vgm_nome_cpf is not null
                                OR
				length(vgm_cpf) = 14 
                                OR
				vgm_nome_cpf != ''
                            )
                            and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
                    group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo)
                    
                    union all

                    (select 
                        picc_cpf as ug_cpf, 
                        picc_nome as ug_nome, 
                        'ID PIN:'||pih_pin_id as ug_id,
                        '' as ug_email,
                        'CARTAO' as  tipo
                    from pins_integracao_card_historico
                            left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                    where pin_status = '4' 
                        and pih_codretepp = '2'
                        and pih_data >= '".$ano."-".$mes."-01 00:00:00'
                        and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                        and (
                            picc_cpf is not null
                            OR
                            picc_nome is not null
                            OR
                            length(picc_cpf) = 14 
                            OR
                            picc_nome != ''
                        )
                        and pih_id IN (".implode(",", $vetorPublisher).") 
                    group by picc_cpf, picc_nome, pih_pin_id, ug_email, tipo)
                    
                    union all

                    (select 
                        vgcbe_cpf as ug_cpf, 
                        vgcbe_nome_cpf as ug_nome, 
                        'ID Venda:'||vgcbe_vg_id as ug_id,
                        vgcbe_ex_email as ug_email,
                        'BOLETO EXPRESS' as  tipo
                    from tb_venda_games_cpf_boleto_express
			inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
			inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vgcbe_data_inclusao >= '".$ano."-".$mes."-01 00:00:00'
                        and vgcbe_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                        and (
                            vgcbe_cpf is not null
                            OR
                            vgcbe_nome_cpf is not null
                            OR
                            length(vgcbe_cpf) = 14 
                            OR
                            vgcbe_nome_cpf != ''
                        )
                        and vgm_opr_codigo IN (".implode(",", $vetorPublisher).")  
                    group by vgcbe_cpf, vgcbe_nome_cpf, vgcbe_vg_id, vgcbe_ex_email, vgcbe_data_inclusao, tipo)


                    ";
        if(!empty($verificadorPublishersNovos)) {
            foreach ($vetorPublisherNovos as $key => $value) {
                //echo "Key: $key -- value: $value <br>";
                $sql .= "

                  union all

                    (select 
                            ug_cpf, 
                            ug_nome_cpf as ug_nome,
                            ug_id::character varying,
                            ug_email,
                            'GAMER' as  tipo
                    from tb_venda_games vg 
                            inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                            inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                    where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                            and vg.vg_data_concilia >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                            and vg.vg_data_concilia <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                            and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."' 
                            and (
				ug_cpf is not null
				OR
				ug_nome_cpf is not null
				OR
				length(ug_cpf) = 14 
				OR
				ug_nome_cpf != ''
                            )
                            and vgm_opr_codigo = ".$value." 
                    group by ug_cpf, ug_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo)

                    union all

                    (select 
                            vgm_cpf as ug_cpf, 
                            vgm_nome_cpf as ug_nome, 
                            ug_id::character varying,
                            ug_email,
                            'LAN HOUSE' as  tipo
                    from tb_dist_venda_games vg 
                            inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                            inner join dist_usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                    where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                            and vg.vg_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                            and vg.vg_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                            and (
				vgm_cpf is not null
                                OR
				vgm_nome_cpf is not null
                                OR
				length(vgm_cpf) = 14 
                                OR
				vgm_nome_cpf != ''
                            )
                            and vgm_opr_codigo = ".$value." 
                    group by vgm_cpf, vgm_nome_cpf, vgm_opr_codigo, ug_id, ug_email, tipo)

                    union all

                    (select 
                        picc_cpf as ug_cpf, 
                        picc_nome as ug_nome, 
                        'ID PIN:'||pih_pin_id as ug_id,
                        '' as ug_email,
                        'CARTAO' as  tipo
                    from pins_integracao_card_historico
                            left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                    where pin_status = '4' 
                        and pih_codretepp = '2'
                        and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                        and pih_data <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                        and (
                            picc_cpf is not null
                            OR
                            picc_nome is not null
                            OR
                            length(picc_cpf) = 14 
                            OR
                            picc_nome != ''
                        )
                        and pih_id = ".$value." 
                    group by picc_cpf, picc_nome, pih_pin_id, ug_email, tipo)

                    union all

                    (select 
                        vgcbe_cpf as ug_cpf, 
                        vgcbe_nome_cpf as ug_nome, 
                        'ID Venda:'||vgcbe_vg_id as ug_id,
                        vgcbe_ex_email as ug_email,
                        'BOLETO EXPRESS' as  tipo
                    from tb_venda_games_cpf_boleto_express
			inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
			inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                    where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vgcbe_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                        and vgcbe_data_inclusao <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 23:59:59'
                        and (
                            vgcbe_cpf is not null
                            OR
                            vgcbe_nome_cpf is not null
                            OR
                            length(vgcbe_cpf) = 14 
                            OR
                            vgcbe_nome_cpf != ''
                        )
                        and vgm_opr_codigo = ".$value."  
                    group by vgcbe_cpf, vgcbe_nome_cpf, vgcbe_vg_id, vgcbe_ex_email, vgcbe_data_inclusao, tipo)

                  ";
            }//end foreach
        } //end if(!empty($verificadorPublishersNovos))
        $sql .= "

        ) tabelaUnion 
                    group by ug_cpf, ug_nome, ug_id, ug_email, tipo 
                    order by tipo, ug_id;  
            ";

        //echo $sql.PHP_EOL; die();
        $rs_dados = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_dados)."<br>";
        if(!$rs_dados) {
            echo "Erro na Query de Levantamento de CPFs e/ou Nome Preenchidos para os Publishers (".implode(",", $vetorPublisher).") e Publishers Novos (".implode(",", $vetorPublisherNovos).").<br>".PHP_EOL;
            return false;
        }
        if(pg_num_rows($rs_dados) == 0) {
            //echo "Vai retorna Falso. Ou seja, NÃO possui dados faltantes.<br>";
            return false;
        }//end if(!$rs_dados || pg_num_rows($rs_dados) == 0)
        else {
            //echo "Vai retorna verdadeiro. Ou seja, possui dados faltantes.<br>";
            return true;
        }//end else
    
}//end function verificaCPFValido

function verificaCPF_BACEN($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

	$RecebeCPF=$cpf;

		if (strlen($RecebeCPF)!=11)
		{ return 0; }
		else
		if ($RecebeCPF=="00000000000" || $RecebeCPF=="11111111111")
		{ return 0; }
		else
		{
			$Numero[1]=intval(substr($RecebeCPF,1-1,1));
			$Numero[2]=intval(substr($RecebeCPF,2-1,1));
			$Numero[3]=intval(substr($RecebeCPF,3-1,1));
			$Numero[4]=intval(substr($RecebeCPF,4-1,1));
			$Numero[5]=intval(substr($RecebeCPF,5-1,1));
			$Numero[6]=intval(substr($RecebeCPF,6-1,1));
			$Numero[7]=intval(substr($RecebeCPF,7-1,1));
			$Numero[8]=intval(substr($RecebeCPF,8-1,1));
			$Numero[9]=intval(substr($RecebeCPF,9-1,1));
			$Numero[10]=intval(substr($RecebeCPF,10-1,1));
			$Numero[11]=intval(substr($RecebeCPF,11-1,1));

			$soma=10*$Numero[1]+9*$Numero[2]+8*$Numero[3]+7*$Numero[4]+6*$Numero[5]+5*
			$Numero[6]+4*$Numero[7]+3*$Numero[8]+2*$Numero[9];
			$soma=$soma-(11*(intval($soma/11)));

			if ($soma==0 || $soma==1)
			{ $resultado1=0; }
			else
			{ $resultado1=11-$soma; }

			if ($resultado1==$Numero[10])
			{
				$soma=$Numero[1]*11+$Numero[2]*10+$Numero[3]*9+$Numero[4]*8+$Numero[5]*7+$Numero[6]*6+$Numero[7]*5+
				$Numero[8]*4+$Numero[9]*3+$Numero[10]*2;
				$soma=$soma-(11*(intval($soma/11)));

				if ($soma==0 || $soma==1)
				{ $resultado2=0; }
				else
				{ $resultado2=11-$soma; }
				if ($resultado2==$Numero[11])
				{ return TRUE;}
				else
				{ return 0; }
			}
			else
			{ return 0; }
	 }
}//end function verificaCPF_BACEN


function levantamentoPublisherOperantes($ano,$mes, $variado = false) {

        // Buscando informações 
        $sql = "select 
                        opr_codigo, 
                        opr_nome
                from operadoras
                where 
                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_data_inicio_operacoes <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'
                        and opr_internacional_alicota != 0
                        and opr_status = '1'
                        and opr_ja_contabilizou = ".$GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU'];
        if($variado) $sql .= " and opr_cotacao_dolar = 1 ";
        $sql .= "order by opr_nome";

        //echo $sql.PHP_EOL; die();
        $rs_operadoras_operantes = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_operadoras_operantes)."<br>";
        if(!$rs_operadoras_operantes) {
            echo "Erro na Query de Levantamento de Publishers INTERNacionais já em operação(".$sql.").<br>".PHP_EOL;
            return array(0);
        }
        if(pg_num_rows($rs_operadoras_operantes) == 0) {
            echo "<b>Nenhum Publisher INTERNacional foi considerado na elaboração de arquivos de Complice BACEN em mêses anteriores</b><br><br>".PHP_EOL;
            return array(0);
        }//end if(pg_num_rows($rs_operadoras_operantes) == 0)
        else {
            echo "<b>Publishers INTERNacionais que já foram considerados na elaboração de arquivos de Complice BACEN em mêses anteriores:</b><br><br>".PHP_EOL;
            while($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
                $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
                echo " ID [".$rs_operadoras_operantes_row['opr_codigo']."] => [".$rs_operadoras_operantes_row['opr_nome']."]<br>".PHP_EOL;
            }//end while
            echo "<br><br>".PHP_EOL;
            return $aux_retorno;
        }//end else
    
}//end function levantamentoPublisherOperantes()


function levantamentoPublisherOperantesNacionais($ano,$mes) {

        // Buscando informações 
        $sql = "select 
                        opr_codigo, 
                        opr_nome
                from operadoras
                where 
                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_data_inicio_operacoes <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'
                        and opr_internacional_alicota = 0
                        and opr_status != '0'
                        and opr_ja_contabilizou = ".$GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']."
                order by opr_nome
                ";

        //echo $sql.PHP_EOL; die();
        $rs_operadoras_operantes = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_operadoras_operantes)."<br>";
        if(!$rs_operadoras_operantes) {
            echo "Erro na Query de Levantamento de Publishers já em operação NACIONAIS(".$sql.").<br>".PHP_EOL;
            return array(0);
        }
        if(pg_num_rows($rs_operadoras_operantes) == 0) {
            echo "<b>Nenhum Publishers Nacional foi considerado na elaboração de arquivos de Complice BACEN em mêses anteriores</b><br><br>".PHP_EOL;
            return array(0);
        }//end if(pg_num_rows($rs_operadoras_operantes) == 0)
        else {
            echo "<b>Publishers NACIONAIS que já foram considerados na elaboração de arquivos de Complice BACEN em mêses anteriores:</b><br><br>".PHP_EOL;
            while($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
                $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
                echo " ID [".$rs_operadoras_operantes_row['opr_codigo']."] => [".$rs_operadoras_operantes_row['opr_nome']."]<br>".PHP_EOL;
            }//end while
            echo "<br><br>".PHP_EOL;
            return $aux_retorno;
        }//end else
    
}//end function levantamentoPublisherOperantesNacionais()


function levantamentoPublisherOperantesMunicipais($ano,$mes) {

        // Buscando informações 
        $sql = "select 
                        opr_codigo, 
                        opr_nome
                from operadoras
                where 
                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_data_inicio_operacoes <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'
                        and opr_internacional_alicota = 0
                        and opr_status != '0'
                        and UPPER(opr_estado) = 'SP'
                        and TRIM(opr_cidade) ilike 's%o Paulo'
                        and opr_ja_contabilizou = ".$GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']."
                order by opr_nome
                ";

        //echo $sql.PHP_EOL; die();
        $rs_operadoras_operantes = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_operadoras_operantes)."<br>";
        if(!$rs_operadoras_operantes) {
            echo "Erro na Query de Levantamento de Publishers já em operação(".$sql.").<br>".PHP_EOL;
            return array(0);
        }
        if(pg_num_rows($rs_operadoras_operantes) == 0) {
            echo "<b>Nenhum Publishers foi considerado na elaboração de arquivos de Complice Municipal em mêses anteriores</b><br><br>".PHP_EOL;
            return array(0);
        }//end if(pg_num_rows($rs_operadoras_operantes) == 0)
        else {
            echo "<b>Publishers que já foram considerados na elaboração de arquivos de Complice Municipal em mêses anteriores:</b><br><br>".PHP_EOL;
            while($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
                $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
                echo " ID [".$rs_operadoras_operantes_row['opr_codigo']."] => [".$rs_operadoras_operantes_row['opr_nome']."]<br>".PHP_EOL;
            }//end while
            echo "<br><br>".PHP_EOL;
            return $aux_retorno;
        }//end else
    
}//end function levantamentoPublisherOperantesMunicipais()


function levantamentoPublisherNovosOperantes($ano,$mes, $variado = false) {

        // Buscando informações 
        $sql = "select 
                        opr_codigo,
                        opr_nome, 
                        to_char(opr_data_inicio_operacoes,'DD/MM/YYYY') as data_inicio
                from operadoras
                where 
                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_data_inicio_operacoes <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'
                        and opr_internacional_alicota != 0
                        and opr_status = '1'
                        and opr_ja_contabilizou != ".$GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU'];
        if($variado) $sql .= " and opr_cotacao_dolar = 1 ";
        $sql .= "order by opr_nome";

        //echo $sql.PHP_EOL; die();
        $rs_operadoras_operantes = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_operadoras_operantes)."<br>";
        if(!$rs_operadoras_operantes) {
            echo "Erro na Query de Levantamento de Publishers INTERNacionais NOVO(".$sql.").<br>".PHP_EOL;
            return array(0);
        }
        if(pg_num_rows($rs_operadoras_operantes) == 0) {
            echo "<b>Nenhum Publisher INTERNacional NOVO iniciou operações no Mês Anterior</b><br><br>".PHP_EOL;
            return array(0);
        }//end if(pg_num_rows($rs_operadoras_operantes) == 0)
        else {
            echo "<b>Publishers INTERNacionais NOVOs que serão considerados na elaboração de arquivos:</b><br><br>".PHP_EOL;
            while($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
                $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
                echo " ID [".$rs_operadoras_operantes_row['opr_codigo']."] => [".$rs_operadoras_operantes_row['opr_nome']."] => Data Início das Operações [<b style='color: red'>".$rs_operadoras_operantes_row['data_inicio']."</b>]<br>".PHP_EOL;
            }//end while
            echo "<br><br>".PHP_EOL;
            return $aux_retorno;
        }//end else
    
}//end function levantamentoPublisherNovosOperantes()


function levantamentoPublisherNovosOperantesNacionais($ano,$mes) {

        // Buscando informações 
        $sql = "select 
                        opr_codigo,
                        opr_nome, 
                        to_char(opr_data_inicio_operacoes,'DD/MM/YYYY') as data_inicio
                from operadoras
                where 
                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_data_inicio_operacoes <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'
                        and opr_internacional_alicota = 0
                        and opr_ja_contabilizou != ".$GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']." 
                order by opr_nome
                ";

        //echo $sql.PHP_EOL; die();
        $rs_operadoras_operantes = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_operadoras_operantes)."<br>";
        if(!$rs_operadoras_operantes) {
            echo "Erro na Query de Levantamento de Publishers NACIONAIS NOVO(".$sql.").<br>".PHP_EOL;
            return array(0);
        }
        if(pg_num_rows($rs_operadoras_operantes) == 0) {
            echo "<b>Nenhum Publisher NACIONAL NOVO iniciou operações no Mês Anterior</b><br><br>".PHP_EOL;
            return array(0);
        }//end if(pg_num_rows($rs_operadoras_operantes) == 0)
        else {
            echo "<b>Publishers NACIONAIS NOVOs que serão considerados na elaboração de arquivos:</b><br><br>".PHP_EOL;
            while($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
                $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
                echo " ID [".$rs_operadoras_operantes_row['opr_codigo']."] => [".$rs_operadoras_operantes_row['opr_nome']."] => Data Início das Operações [<b style='color: red'>".$rs_operadoras_operantes_row['data_inicio']."</b>]<br>".PHP_EOL;
            }//end while
            echo "<br><br>".PHP_EOL;
            return $aux_retorno;
        }//end else
    
}//end function levantamentoPublisherNovosOperantesNacionais()


function levantamentoPublisherNovosOperantesMunicipais($ano,$mes) {

        // Buscando informações 
        $sql = "select 
                        opr_codigo,
                        opr_nome, 
                        to_char(opr_data_inicio_operacoes,'DD/MM/YYYY') as data_inicio
                from operadoras
                where 
                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_data_inicio_operacoes <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'
                        and opr_internacional_alicota = 0
                        and UPPER(opr_estado) = 'SP'
                        and TRIM(opr_cidade) ilike 's%o Paulo'
                        and opr_ja_contabilizou != ".$GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']." 
                order by opr_nome
                ";

        //echo $sql.PHP_EOL; die();
        $rs_operadoras_operantes = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_operadoras_operantes)."<br>";
        if(!$rs_operadoras_operantes) {
            echo "Erro na Query de Levantamento de Publishers já em operação(".$sql.").<br>".PHP_EOL;
            return array(0);
        }
        if(pg_num_rows($rs_operadoras_operantes) == 0) {
            echo "<b>Nenhum Publishers NOVO iniciou operações no Mês Anterior</b><br><br>".PHP_EOL;
            return array(0);
        }//end if(pg_num_rows($rs_operadoras_operantes) == 0)
        else {
            echo "<b>Publishers NOVOs que serão considerados na elaboração de arquivos:</b><br><br>".PHP_EOL;
            while($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
                $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
                echo " ID [".$rs_operadoras_operantes_row['opr_codigo']."] => [".$rs_operadoras_operantes_row['opr_nome']."] => Data Início das Operações [<b style='color: red'>".$rs_operadoras_operantes_row['data_inicio']."</b>]<br>".PHP_EOL;
            }//end while
            echo "<br><br>".PHP_EOL;
            return $aux_retorno;
        }//end else
    
}//end function levantamentoPublisherNovosOperantesMunicipais()


function alteracaoPublisherNovosJaArquivoBACEN($vetorPublisherNovos) {

        // Buscando informações 
        $sql = "update operadoras
                set opr_ja_contabilizou =  ".$GLOBALS['STATUS_ARQUIVO_BACEN']['AGUARDANDO_RETORNO_BACEN']."
                where 
                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_ja_contabilizou != ".$GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']." 
                        and opr_internacional_alicota != 0
                        and opr_codigo IN (".implode(",", $vetorPublisherNovos).")
                ";

        //echo $sql.PHP_EOL; die();
        $rs_update = SQLexecuteQuery($sql);
        if(!$rs_update) {
            echo "Erro na Query de Alteração de Publishers para já em arquivo do BACEN (".$sql.").<br>".PHP_EOL;
            return false;
        }
        if(pg_affected_rows($rs_update) === 0) {
            echo "<b>Nenhum Publishers NOVO foi alterado para já em arquivo do BACEN</b><br><br>".PHP_EOL;
            return false;
        }//end if(pg_num_rows($rs_update) == 0)
        else {
            echo "<b>Publishers NOVOs foram alterados para já em arquivo do BACEN [".implode(",", $vetorPublisherNovos)."]</b><br><br>".PHP_EOL;
            return true;
        }//end else
    
}//end function alteracaoPublisherNovosJaArquivoBACEN()

function alteracaoPublisherNovosJaArquivoMunicipais($vetorPublisherNovos) {

        // Buscando informações 
        $sql = "update operadoras
                set opr_ja_contabilizou =  ".$GLOBALS['STATUS_ARQUIVO_BACEN']['AGUARDANDO_RETORNO_BACEN']."
                where 
                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_ja_contabilizou != ".$GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']." 
                        and opr_internacional_alicota = 0
                        and UPPER(opr_estado) = 'SP'
                        and TRIM(opr_cidade) ilike 's%o Paulo'
                        and opr_codigo IN (".implode(",", $vetorPublisherNovos).")
                ";

        //echo $sql.PHP_EOL; die();
        $rs_update = SQLexecuteQuery($sql);
        if(!$rs_update) {
            echo "Erro na Query de Alteração de Publishers para já em arquivo para Prefeitura (".$sql.").<br>".PHP_EOL;
            return false;
        }
        if(pg_affected_rows($rs_update) === 0) {
            echo "<b>Nenhum Publishers NOVO foi alterado para já em arquivo para Prefeitura</b><br><br>".PHP_EOL;
            return false;
        }//end if(pg_num_rows($rs_update) == 0)
        else {
            echo "<b>Publishers NOVOs foram alterados para já em arquivo para Prefeitura [".implode(",", $vetorPublisherNovos)."]</b><br><br>".PHP_EOL;
            return true;
        }//end else
    
}//end function alteracaoPublisherNovosJaArquivoMunicipais()


function levantamentoPublisherEppPagamentosFacilitadora($ano,$mes, $variado = false) {

        // Buscando informações 
        $sql = "select 
                        opr_codigo, 
                        opr_nome
                from operadoras
                where 
                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_PAGAMENTOS']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_data_inicio_operacoes <= '".$ano."-".$mes."-".date("t",mktime(0, 0, 0, ($mes*1), 1, $ano))." 00:00:00'
                        and (opr_internacional_alicota = 0.38 OR opr_internacional_alicota = ".IOF.")
                        and opr_status = '1'";
        if($variado) $sql .= " and opr_cotacao_dolar = 1 ";
        $sql .= "order by opr_nome";

        //echo $sql.PHP_EOL; die();
        $rs_operadoras_operantes = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_operadoras_operantes)."<br>";
        if(!$rs_operadoras_operantes) {
            echo "Erro na Query de Levantamento de Publishers Epp Pagamentos Facilitadora já em operação(".$sql.").<br>".PHP_EOL;
            return array(0);
        }
        if(pg_num_rows($rs_operadoras_operantes) == 0) {
            echo "<b>Nenhum Publisher Epp Pagamentos Facilitadora foi considerado em mêses anteriores</b><br><br>".PHP_EOL;
            return array(0);
        }//end if(pg_num_rows($rs_operadoras_operantes) == 0)
        else {
            echo "<b>Publishers Epp Pagamentos Facilitadora:</b><br><br>".PHP_EOL;
            while($rs_operadoras_operantes_row = pg_fetch_array($rs_operadoras_operantes)) {
                $aux_retorno[] = $rs_operadoras_operantes_row['opr_codigo'];
                echo " ID [".$rs_operadoras_operantes_row['opr_codigo']."] => [".$rs_operadoras_operantes_row['opr_nome']."]<br>".PHP_EOL;
            }//end while
            echo "<br><br>".PHP_EOL;
            return $aux_retorno;
        }//end else
    
}//end function levantamentoPublisherEppPagamentosFacilitadora()



function trimestre($mes=null)
{
    $mes = is_null($mes) ? date('m') : $mes;
    $trim=floor(($mes-1) / 3)+1;
    return $trim;
}//end function trimestre($mes=null)

function semestre($mes=null)
{
    $mes = is_null($mes) ? date('m') : $mes;
    $trim=floor(($mes-1) / 6)+1;
    return $trim;
}//end function semestre($mes=null)

function isTrimestral($mes)
{
    $mesesFechamentoTrimenstral = array(3,6,9,12);

    if(in_array(($mes*1), $mesesFechamentoTrimenstral)) {
            return true;
    }
    return false;
}//end function isTrimestral($mes)

function isSemestral($mes)
{
    $mesesFechamentoSemenstral = array(6,12);

    if(in_array(($mes*1), $mesesFechamentoSemenstral)) {
            return true;
    }
    return false;
}//end function isSemestral($mes)


function getStartDateTrimestral($mes,$ano)
{
    global $dataInicioOperacao,$testeData;
    $date= "";
    $trimestreAux = trimestre($mes);
    switch ($trimestreAux) {
        case 1:
            if($testeData == $dataInicioOperacao) { 
                $date = $ano."-".$mes."-01";
            } //end if($testeData == $dataInicioOperacao)
            else {
                $date = $ano."-01-01";
            }//end else do if($testeData == $dataInicioOperacao)
            break;
        case 2:
            $date = $ano."-04-01";
            break;
        case 3:
            $date = $ano."-07-01";
            break;
        case 4:
            $date = $ano."-10-01";
            break;
    }//end switch
    return $date;
}//end function getStartDateTrimestral($mes,$ano)

function getEndDateTrimestral($mes,$ano)
{
    $date= "";
    $trimestreAux = trimestre($mes);
    switch ($trimestreAux) {
        case 1:
            $date = $ano."-03-31";
            break;
        case 2:
            $date = $ano."-06-30";
            break;
        case 3:
            $date = $ano."-09-30";
            break;
        case 4:
            $date = $ano."-12-31";
            break;
    }//end switch
    return $date;
}//end function getEndDateTrimestral($mes,$ano)

function getStartDateSemestral($mes,$ano)
{
    global $dataInicioOperacao,$testeData;
    $date= "";
    $trimestreAux = semestre($mes);
    switch ($trimestreAux) {
        case 1:
            if($testeData == $dataInicioOperacao) { 
                $date = $ano."-".$mes."-01";
            } //end if($testeData == $dataInicioOperacao)
            else {
                $date = $ano."-01-01";
            }//end else do if($testeData == $dataInicioOperacao)
            break;
        case 2:
            $date = $ano."-07-01";
            break;
    }//end switch
    return $date;
}//end function getStartDateSemestral($mes,$ano)

function getEndDateSemestral($mes,$ano)
{
    $date= "";
    $trimestreAux = semestre($mes);
    switch ($trimestreAux) {
        case 1:
            $date = $ano."-06-30";
            break;
        case 2:
            $date = $ano."-12-31";
            break;
    }//end switch
    return $date;
}//end function getEndDateSemestral($mes,$ano)

function verificaLimiteDetalhamento($limite, &$rs) {
    // A variável limite deve ser informada em DOLAR (USS). Ex.: $limite = 1000 significa $USS 1,000
    
    /* Calculando individualmente por publisher(PONDERAADA)
    // Calculado a Cotação Média
    $mediaCotacao = 0;
    foreach ($GLOBALS['vetorCotacaoUSS'] as $key => $value) {
        //echo $value."*<br>";
        $mediaCotacao += $value;
    }//end foreach
    $mediaCotacao = $mediaCotacao/count($GLOBALS['vetorCotacaoUSS']);
    //echo "[$mediaCotacao]<br>";
    */
    
    // Selecionando os usuarios que ultrapassaram o Limite
    $sql = "
        select 
            ug_cpf,
            sum(n) as qtde,
            sum(total) as total_geral
     from ( 
                ";
    $insere_union_all = 1;
    foreach ($GLOBALS['vetorPublisher'] as $key => $value) {
        if($insere_union_all > 1) {
            $sql .= "

              union all

            ";
        } //end if($insere_union_all > 1)
        $sql .= "
            (
                select 
                        ug_cpf as ug_cpf,
                        sum(vgm.vgm_qtde) as n, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde)/".$GLOBALS['vetorCotacaoUSS'][$value]." as total 
                from tb_venda_games vg 
                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_concilia >= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-01 00:00:00'
                        and vg.vg_data_concilia <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                        and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                        and vgm_opr_codigo = ".$value."
                group by ug_cpf
            )
            
            union all
            
            (
                select 
                        vgm_cpf as ug_cpf, 
                        sum(vgm.vgm_qtde) as n, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde)/".$GLOBALS['vetorCotacaoUSS'][$value]." as total 
                from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_inclusao >= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-01 00:00:00'
                        and vg.vg_data_inclusao <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                        and vgm_opr_codigo = ".$value." 
                group by vgm_cpf
            )
            
            union all
            
            (
                select 
                        picc_cpf as ug_cpf, 
                        count(*) as n, 
                        sum(pih_pin_valor/100)/".$GLOBALS['vetorCotacaoUSS'][$value]." as total 
                from pins_integracao_card_historico
                                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
	        where pin_status = '4' 
		        and pih_codretepp = '2'
                        and pih_data >= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-01 00:00:00'
                        and pih_data <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                        and pih_id = ".$value." 
                group by picc_cpf
            )

            union all

            (
                select 
                    vgcbe_cpf as ug_cpf, 
                    count(*) as n,
                    sum(vgm_valor * vgm_qtde)/".$GLOBALS['vetorCotacaoUSS'][$value]." as total 
                from tb_venda_games_cpf_boleto_express
                    inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                    inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vgcbe_data_inclusao >= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-01 00:00:00'
                    and vgcbe_data_inclusao <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                    and vgm_opr_codigo = ".$value."  
                group by vgcbe_cpf
            )

         ";
        $insere_union_all++;

    }//end foreach ($vetorPublisher as $key => $value)

    
    if(!empty($GLOBALS['verificadorPublishersNovos'])) {
        foreach ($GLOBALS['vetorPublisherNovos'] as $key => $value) {
            //echo "Key: $key -- value: $value <br>";
            $sql .= "

            union all

                (
                   select 
                           ug_cpf as ug_cpf,
                           sum(vgm.vgm_qtde) as n, 
                           sum(vgm.vgm_valor * vgm.vgm_qtde)/".$GLOBALS['vetorCotacaoUSS'][$value]." as total 
                   from tb_venda_games vg 
                           inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                           inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                   where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                           and vg.vg_data_concilia >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                           and vg.vg_data_concilia <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                           and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                           and vgm_opr_codigo = ".$value." 
                   group by ug_cpf
                )

            union all

                (
                   select 
                           vgm_cpf as ug_cpf, 
                           sum(vgm.vgm_qtde) as n, 
                           sum(vgm.vgm_valor * vgm.vgm_qtde)/".$GLOBALS['vetorCotacaoUSS'][$value]." as total 
                   from tb_dist_venda_games vg 
                           inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                   where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                           and vg.vg_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                           and vg.vg_data_inclusao <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                           and vgm_opr_codigo = ".$value."
                   group by vgm_cpf
                )

            
            union all
            
            (
                select 
                        picc_cpf as ug_cpf, 
                        count(*) as n, 
                        sum(pih_pin_valor/100)/".$GLOBALS['vetorCotacaoUSS'][$value]." as total 
                from pins_integracao_card_historico
                                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
	        where pin_status = '4' 
		        and pih_codretepp = '2'
                        and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                        and pih_data <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                        and pih_id = ".$value." 
                group by picc_cpf
            )
            
            union all

            (
                select 
                    vgcbe_cpf as ug_cpf, 
                    count(*) as n,
                    sum(vgm_valor * vgm_qtde)/".$GLOBALS['vetorCotacaoUSS'][$value]." as total 
                from tb_venda_games_cpf_boleto_express
                    inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                    inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vgcbe_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                    and vgcbe_data_inclusao <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                    and vgm_opr_codigo = ".$value."  
                group by vgcbe_cpf
            )


                    ";
        }//end foreach
    } //end if(!empty($verificadorPublishersNovos))
    
    $sql .= " 
        ) tabelaUnion  
        group by ug_cpf 
        having sum(total) > ".($limite)."
        order by total_geral desc;";
            
    //echo $sql.PHP_EOL; die();

    $rs = SQLexecuteQuery($sql);
    if(!$rs || pg_num_rows($rs) == 0) {
        return false;
    }//end if(!$rs || pg_num_rows($rs) == 0)
    else {
        return true;
    }//end else
    
}//end function verificaLimiteDetalhamento

function verificaLimiteCOAF($limite, &$rs) {
    // A variável limite deve ser informada em REAIS (R$). Ex.: $limite = 1000 significa R$ 1.000,00
    
    // Selecionando os usuarios que ultrapassaram o Limite
    $sql = "
        select 
            ug_cpf,
            sum(n) as qtde,
            sum(total) as total_geral
     from ( 
                ";
    $insere_union_all = 1;
    foreach ($GLOBALS['vetorPublisher'] as $key => $value) {
        if($insere_union_all > 1) {
            $sql .= "

              union all

            ";
        } //end if($insere_union_all > 1)
        $sql .= "
            (
                select 
                        ug_cpf as ug_cpf,
                        sum(vgm.vgm_qtde) as n, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
                from tb_venda_games vg 
                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_concilia >= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-01 00:00:00'
                        and vg.vg_data_concilia <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                        and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                        and vgm_opr_codigo = ".$value."
                group by ug_cpf
            )
            
            union all
            
            (
                select 
                        vgm_cpf as ug_cpf, 
                        sum(vgm.vgm_qtde) as n, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
                from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                        and vg.vg_data_inclusao >= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-01 00:00:00'
                        and vg.vg_data_inclusao <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                        and vgm_opr_codigo = ".$value." 
                group by vgm_cpf
            )
            
            union all
            
            (
                select 
                        picc_cpf as ug_cpf, 
                        count(*) as n, 
                        sum(pih_pin_valor/100) as total 
                from pins_integracao_card_historico
                                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
	        where pin_status = '4' 
		        and pih_codretepp = '2'
                        and pih_data >= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-01 00:00:00'
                        and pih_data <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                        and pih_id = ".$value." 
                group by picc_cpf
            )

            union all

            (
                select 
                    vgcbe_cpf as ug_cpf, 
                    count(*) as n,
                    sum(vgm_valor * vgm_qtde) as total 
                from tb_venda_games_cpf_boleto_express
                    inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                    inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vgcbe_data_inclusao >= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-01 00:00:00'
                    and vgcbe_data_inclusao <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                    and vgm_opr_codigo = ".$value."  
                group by vgcbe_cpf
            )

         ";
        $insere_union_all++;

    }//end foreach ($vetorPublisher as $key => $value)

    
    if(!empty($GLOBALS['verificadorPublishersNovos'])) {
        foreach ($GLOBALS['vetorPublisherNovos'] as $key => $value) {
            //echo "Key: $key -- value: $value <br>";
            $sql .= "

            union all

                (
                   select 
                           ug_cpf as ug_cpf,
                           sum(vgm.vgm_qtde) as n, 
                           sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
                   from tb_venda_games vg 
                           inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                           inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                   where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                           and vg.vg_data_concilia >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                           and vg.vg_data_concilia <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                           and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                           and vgm_opr_codigo = ".$value." 
                   group by ug_cpf
                )

            union all

                (
                   select 
                           vgm_cpf as ug_cpf, 
                           sum(vgm.vgm_qtde) as n, 
                           sum(vgm.vgm_valor * vgm.vgm_qtde) as total 
                   from tb_dist_venda_games vg 
                           inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                   where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                           and vg.vg_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                           and vg.vg_data_inclusao <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                           and vgm_opr_codigo = ".$value."
                   group by vgm_cpf
                )

            
            union all
            
            (
                select 
                        picc_cpf as ug_cpf, 
                        count(*) as n, 
                        sum(pih_pin_valor/100) as total 
                from pins_integracao_card_historico
                                    left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
	        where pin_status = '4' 
		        and pih_codretepp = '2'
                        and pih_data >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                        and pih_data <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                        and pih_id = ".$value." 
                group by picc_cpf
            )
            
            union all

            (
                select 
                    vgcbe_cpf as ug_cpf, 
                    count(*) as n,
                    sum(vgm_valor * vgm_qtde) as total 
                from tb_venda_games_cpf_boleto_express
                    inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                    inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vgcbe_data_inclusao >= (select opr_data_inicio_operacoes from operadoras where opr_codigo = ".$value." )
                    and vgcbe_data_inclusao <= '".$GLOBALS['ano']."-".$GLOBALS['mes']."-".date("t",mktime(0, 0, 0, ($GLOBALS['mes']*1), 1, $GLOBALS['ano']))." 23:59:59'
                    and vgm_opr_codigo = ".$value."  
                group by vgcbe_cpf
            )


                    ";
        }//end foreach
    } //end if(!empty($verificadorPublishersNovos))
    
    $sql .= " 
        ) tabelaUnion  
        group by ug_cpf 
        having sum(total) > ".($limite)."
        order by total_geral desc;";
            
    //echo $sql.PHP_EOL; die();

    $rs = SQLexecuteQuery($sql);
    if(!$rs || pg_num_rows($rs) == 0) {
        return false;
    }//end if(!$rs || pg_num_rows($rs) == 0)
    else {
        return true;
    }//end else
    
}//end function verificaLimiteCOAF
function levantamentoPublisherObrigatorioCPF(&$vetorPublisherLegenda) {
        // Buscando informações 
        $sql = "select 
                        opr_codigo, 
                        opr_nome
                from operadoras
                where 
                        opr_data_inicio_operacoes is not null
                        and opr_need_cpf_lh = 1
						and opr_status = '1' 
                order by opr_nome
                ";
        //echo $sql.PHP_EOL; die();
        $rs_operadoras_obrigatorio_cpf = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_operadoras_obrigatorio_cpf)."<br>";
        if(!$rs_operadoras_obrigatorio_cpf) {
            echo "Erro na Query de Levantamento de Publishers Exigem CPF na operação(".$sql.").<br>".PHP_EOL;
            return array(0);
        }
        if(pg_num_rows($rs_operadoras_obrigatorio_cpf) == 0) {
            echo "<b>Nenhum Publisher que Exige CPF foi considerado no seleção(".$sql.").</b><br><br>".PHP_EOL;
            return array(0);
        }//end if(pg_num_rows($rs_operadoras_obrigatorio_cpf) == 0)
        else {
            //echo "<b>Publishers que Exigem CPF como Obrigatório:</b><br><br>".PHP_EOL;
            while($rs_operadoras_obrigatorio_cpf_row = pg_fetch_array($rs_operadoras_obrigatorio_cpf)) {
                $aux_retorno[] = $rs_operadoras_obrigatorio_cpf_row['opr_codigo'];
                $vetorPublisherLegenda[$rs_operadoras_obrigatorio_cpf_row['opr_codigo']] = $rs_operadoras_obrigatorio_cpf_row['opr_nome'];
                //echo " ID [".$rs_operadoras_obrigatorio_cpf_row['opr_codigo']."] => [".$rs_operadoras_obrigatorio_cpf_row['opr_nome']."]<br>".PHP_EOL;
            }//end while
            //echo "<br><br>".PHP_EOL;
            return $aux_retorno;
        }//end else
}//end function levantamentoPublisherObrigatorioCPF()

?>  
