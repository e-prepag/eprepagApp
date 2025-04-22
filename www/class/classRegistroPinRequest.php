<?php

class classRegistroPinRequest{
    
    static function verificaExisteRegistroBHN($dadosPedido) {
            $sql = "SELECT bhn_id FROM pedidos_egift_bhn WHERE vgm_id = ".$dadosPedido['vgm_id']." AND vg_id = ".$dadosPedido['vg_id']." AND opr_codigo = ".$dadosPedido['opr_codigo'].";";
            $rs_teste = SQLexecuteQuery($sql);
            if(!$rs_teste || pg_num_rows($rs_teste) == 0) {
                    return FALSE;
            } else {
                    RETURN TRUE;
            }          
    }//end function verificaExisteRegistroBHN
 
    private function nextLote($pinParams) {
        
            $sql = "select max(pin_lote_codigo) as max_pin_lote_codigo from pins where opr_codigo = ".$pinParams['opr_codigo'].";";
            $rs_lote = SQLexecuteQuery($sql);
            if(!$rs_lote || pg_num_rows($rs_lote) == 0) {
                    $ilote = 1;
            } else {
                    $rs_lote_row = pg_fetch_array($rs_lote); 
                    $ilote = $rs_lote_row['max_pin_lote_codigo'] + 1;
            }
            return $ilote;
    }//end function nextLote
    
    static function insereEstoque($pinParams, classRegistroPinRequest $objectAux) {
            //Verificando se io PIN já foi inserido no estoque anteriormente
            if(!$objectAux->getPinCodInterno($pinParams)) {
                    $sql = "insert into pins ( 
                                               pin_serial, 
                                               pin_codigo, 
                                               opr_codigo, 
                                               pin_valor, 
                                               pin_lote_codigo, 
                                               pin_dataentrada, 
                                               pin_canal, 
                                               pin_horaentrada, 
                                               pin_status,
                                               pin_datavenda,
                                               pin_datapedido,
                                               pin_horavenda,
                                               pin_horapedido,
                                               pin_est_codigo,
                                               pin_validade) 
                            values (
                                            '".$pinParams['pin_serial']."',
                                            '".$pinParams['pin_codigo']."', 
                                            ".$pinParams['opr_codigo'].",
                                            ".$pinParams['bhn_valor'].", 
                                            ".$objectAux->nextLote($pinParams).", 
                                            CURRENT_TIMESTAMP, 
                                            's', 
                                            NOW(),
                                            '3',
                                            NOW(),
                                            NOW(),
                                            '".date ("H:i:s")."',
                                            '".date ("H:i:s")."',
                                            '1',
                                            (NOW() + interval '6 month')
                                    );";

                    $rs_pins_save = SQLexecuteQuery($sql);
                    if(!$rs_pins_save) {
                            echo "Erro ao salvar o novo PIN ($sql)".PHP_EOL;
                            return false;
                    }
                    elseif($objectAux->relacionaPinVendaModelo($pinParams)) {
                        return true;
                    }
                    else return false;
            }//end if($this->getPinCodInterno($pinParams))
            else {
                echo "PIN já existe no estoque (tabela PINs)".PHP_EOL;
                return true;
            }//end else do  if($this->getPinCodInterno($pinParams))
    }//end function insereEstoque
	
    private function getPinCodInterno($pinParams) {
            $sql = "select pin_codinterno from pins where pin_codigo = '".$pinParams['pin_codigo']."' and opr_codigo = ".$pinParams['opr_codigo']." ORDER BY pin_dataentrada DESC, pin_horaentrada DESC LIMIT 1;";
            $rs_pins_estoque = SQLexecuteQuery($sql);
            if(!$rs_pins_estoque) {
                    echo "Erro ao selecionar o novo PIN no estoque ($sql)".PHP_EOL;
                    return false;
            }
            else{
                    $rs_pins_estoque_row = pg_fetch_array($rs_pins_estoque);
                    return $rs_pins_estoque_row['pin_codinterno'];
            }//end else if(!$rs_pins_estoque)
    }//end function getPinCodInterno
    
    private function relacionaPinVendaModelo($pinParams){
            $codigoInterno = $this->getPinCodInterno($pinParams);
            if($codigoInterno) {
                $sql = "insert into tb_venda_games_modelo_pins (vgmp_vgm_id, vgmp_pin_codinterno) values (" . $pinParams['vgm_id'] . "," . $codigoInterno . ");";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) {
                    echo "Erro ao associar pin no modelo vendido.".PHP_EOL;
                    return false;
                }
                elseif($this->atualizaModelo($pinParams, $codigoInterno)) {
                    return true;
                }
                else return false;
            }//end if($codigoInterno) 
            else return false;
    }//end  function relacionaPinVendaModelo    
    
    private function atualizaModelo($pinParams,$codigoInterno){
            $sql = "update tb_venda_games_modelo set vgm_pin_codinterno = coalesce(vgm_pin_codinterno,'') || '" . $codigoInterno . ",' WHERE vgm_id = '" . $pinParams['vgm_id'] . "';";
            $ret = SQLexecuteQuery($sql);
            if(!$ret) {
                echo "Erro ao atualizar registro de modelo de pedido com o ID do PIN.".PHP_EOL;
                return false;
            }
            else return true;
    }//end  function atualizaModelo    
    
    
}

?>

