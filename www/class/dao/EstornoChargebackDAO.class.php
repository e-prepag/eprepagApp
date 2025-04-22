<?php
/**
 * Classe Data Access Object de Estorno e Chargeback
 *
 * @author Wagner de Miranda
 * @email wagner.mbis@gmail.com
 * @date 23-10-2015
 * 
 ===> Tabela estorno_chargeback
  ec_id bigserial NOT NULL, -- Campo contendo o ID desta tabela.
  ec_data_devolucao timestamp with time zone NOT NULL, -- Campo contendo a data do Estorno ou ChargeBack.
  ec_pin_bloqueado smallint NOT NULL, -- Campo contendo informação se o PIN relacionado ao pedido foi Bloqueado ou não. Onde: 0 => NÃO foi Bloqueado e 1 => Foi Bloqueado.
  cec_id integer NOT NULL, -- Campo contendo o ID do motivo do Estorno ou ChargeBack.
  ec_tipo_usuario character varying(1) NOT NULL, -- Campo contendo o tipo de usuário do estorno. Onde: G => Gamer e L => Lan House.
  ec_valor numeric(15,2) NOT NULL DEFAULT 0, -- Campo contendo o valor do ChargeBack ou Estorno.
  ug_id bigint NOT NULL, -- Campo contendo o ID do usuário (LAN ou GAMER).
  ec_tipo smallint NOT NULL, -- Campo contendo o tipo. Onde: 1 => ChargeBack e 2 => Estorno.
  ec_nome character varying(256) NOT NULL, -- Campo contendo o nome do solicitante.
  vg_id bigint NOT NULL, -- Campo contendo o ID do pedido (LAN ou GAMER).
  opr_codigo integer NOT NULL, -- Campo contendo o ID do Publisher.
  ec_data_nascimento timestamp with time zone, -- Campo contendo a data de nascimento do solicitante
  ec_cpf character varying(14), -- Campo contendo o CPF do solicitante
  ec_telefone character varying(15), -- Campo contendo o telefone do solicitante
  ec_email character varying(256), -- Campo contendo o e-mail do solicitante
  ec_data_pedido timestamp with time zone, -- Campo contendo a data do pedido
  ec_pin character varying(60), -- Campo contendo o PIN
  ec_ip_pedido character varying(15), -- Campo contendo o IP do pedido
  ec_cod_autorizacao character varying(60), -- Campo contendo o código de autorização
  ec_tid character varying(60), -- Campo contendo o TID
  ec_cod_boleto character varying(20), -- Campo contendo o código do boleto
  ec_cod_deposito character varying(20), -- Campo contendo o código do depósito
  ec_forma_devolucao smallint, -- Campo contendo a forma de devolução no caso de Estorno e usuário ser LAN. Onde: 1 => Devolução em Saldo e 2 => Devolução através de Depósito.
  
 ===> Tabela estorno_dados_bancarios
  edb_id bigserial NOT NULL, -- Campo contendo o ID do registro desta tabela.
  edb_titular character varying(512) NOT NULL, -- Campo contendo o Titular que receberá o Estorno
  edb_cpf_cnpj character varying(18), -- Campo contendo o CPF ou CNPJ do Titular.
  edb_banco character varying(256) NOT NULL, -- Campo contendo o Banco do Titular.
  edb_agencia character varying(15) NOT NULL, -- Campo contendo a agência do Titular.
  edb_conta character varying(15) NOT NULL, -- Campo contendo a conta do Titular.
  edb_tipo_conta smallint NOT NULL, -- Campo contendo o tipo da conta do Titular. Onde:  1 => Conta Corrente e 2 => Conta Poupança.
  ec_id bigint NOT NULL, -- Campo contendo o ID do Estorno da tabelaestorno_chargeback.
  
 ===> Tabela categoria_estorno_chargeback
  cec_id serial NOT NULL, -- ID de identificação da categoria nesta tabela.
  cec_descricao character varying(256) NOT NULL, -- Campo contendo a descrição da categoria de Estorno e ChargeBack de pedidos.
  cec_data_cadastro timestamp with time zone NOT NULL, -- Campo contendo a data de cadastro da categoria de Estorno e ChargeBack de pedidos
  cec_status smallint NOT NULL DEFAULT 0, -- Campo contendo a ativação da categoria de Estorno e ChargeBack de pedidos. Onde 0 = Desativado e 1 = Ativado.
 */

class EstornoChargeBackDAO {
    
    public $EstornoChargeBacks = array();
    protected $erros = array();
    
    public function __construct(){
    }
    
    public function get($filtro = null, $limit = null){
        
        //Verificando se foi passado filtros de Dados Bancários
        $innerJoin = false;
        if(is_array($filtro)) {
                $objTeste = new EstornoDadosBancariosVO();
                foreach ($filtro as $key => $value) {
                        if($objTeste->isCampoTabela($key)) {
                                $innerJoin = true;
                        }//end if(EstornoDadosBancariosVO::isCampoTabela($key))
                }//end foreach
        }//end if(is_array($filtro))
       
        //Montando a Query
        $sql = "SELECT 
                    ec.ec_id as id,*
                FROM estorno_chargeback as ec
                    INNER JOIN categoria_estorno_chargeback as cec ON ec.cec_id = cec.cec_id
                    ";
        if($innerJoin) $sql .= "INNER JOIN estorno_dados_bancarios as edb ON ec.ec_id = edb.ec_id ".PHP_EOL."                      ";
        else $sql .= "LEFT OUTER JOIN estorno_dados_bancarios as edb ON ec.ec_id = edb.ec_id ".PHP_EOL."                      ";
        if (is_array($filtro))  $sql .= ' WHERE ' . implode(' AND ', $filtro);
        $sql .= " ORDER BY ec_data_devolucao DESC";
        if($limit) $sql .= " LIMIT ".$limit;
        
        try{
            
            if($EstornoChargeBacks = SQLexecuteQuery($sql)){
                
                if(pg_num_rows($EstornoChargeBacks) > 0){
                    
                    while($lineRow = pg_fetch_array($EstornoChargeBacks)){
                        
                        $arrayTemp = array();
						$codesGarena = [];
						
						if($lineRow["ec_tipo_usuario"] == "L"){
							$infoSale = "select vg_data_inclusao,vg_pagto_tipo,ug_responsavel,ug_cpf from tb_dist_venda_games inner join dist_usuarios_games on vg_ug_id = ug_id where vg_id =". $lineRow["vg_id"];
							
							$infoCodeGarena = "select pin_guid_parceiro from tb_dist_venda_games_modelo left join tb_dist_venda_games_modelo_pins on vgmp_vgm_id = vgm_id left join pins on pin_codinterno = vgmp_pin_codinterno where vgm_vg_id =". $lineRow["vg_id"];
							$dataCodeExec = SQLexecuteQuery($infoCodeGarena);
							while($row = pg_fetch_array($dataCodeExec)){
								if($row["pin_guid_parceiro"] != "" && $row["pin_guid_parceiro"] != null){
									$codesGarena[] = $row["pin_guid_parceiro"];
								}
							}
							
							
							//select vg_data_inclusao,vg_pagto_tipo,ug_responsavel,ug_cpf from tb_dist_venda_games inner join dist_usuarios_games on vg_ug_id = ug_id where vg_id =
						}else{
							$infoSale = "select vg_data_inclusao,vg_pagto_tipo,ug_nome,ug_cpf from tb_venda_games inner join usuarios_games on vg_ug_id = ug_id where vg_id =". $lineRow["vg_id"]; 
							
							$infoCodeGarena = "select pin_guid_parceiro from tb_venda_games_modelo left join tb_venda_games_modelo_pins on vgmp_vgm_id = vgm_id left join pins on pin_codinterno = vgmp_pin_codinterno where vgm_vg_id =". $lineRow["vg_id"];
							$dataCodeExec = SQLexecuteQuery($infoCodeGarena);
							while($row = pg_fetch_array($dataCodeExec)){
								if($row["pin_guid_parceiro"] != "" && $row["pin_guid_parceiro"] != null){
									$codesGarena[] = $row["pin_guid_parceiro"];
								}
							}
					     	
						}
						$dataSaleExec = SQLexecuteQuery($infoSale);
						$dataSale = pg_fetch_array($dataSaleExec);
						
						// fazer inner join na tabela de venda de acordo com o tipo de cliente 
                    
                        $EstornoChargeBack = new EstornoChargeBackVO($lineRow);
                        $arrayTemp = Util::object_to_array($EstornoChargeBack->dados);
                        unset($EstornoChargeBack);
						$arrayTemp["vg_data_inclusao"] = $dataSale["vg_data_inclusao"];
						$arrayTemp["vg_pagto_tipo"] = $dataSale["vg_pagto_tipo"];
						$arrayTemp["ug_cpf"] = $dataSale["ug_cpf"];
						$arrayTemp["cod_garena"] = $codesGarena;
						$arrayTemp["usuarioNome"] = ($lineRow["ec_tipo_usuario"] == "L")? $dataSale["ug_responsavel"]: $dataSale["ug_nome"];
                        $this->EstornoChargeBacks[] = $arrayTemp;
                        
                    }//end while

                    return $this->EstornoChargeBacks;
                    
                } //end if(pg_num_rows($EstornoChargeBacks) > 0) 
                
            }// end if executou a query
            
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }//end catch
	
    } //end function get
    
    public function insert (EstornoChargeBackVO $EstornoChargeBack, EstornoDadosBancariosVO $EstornoDadosBancarios = NULL){
        
        try {
            
            $arrayTemp = $EstornoChargeBack->dados;
            $arrayCampos = array();
            $arrayFormatoValores = array();
            foreach ($arrayTemp as $key => $value) {
                    if($value != "" && $EstornoChargeBack->isCampoTabela($key)) {
                            $arrayCampos[$key] = $value;
                            if(substr_count($value, '/') ==  2) {
                                    $arrayFormatoValores[] = "to_date('%s','DD/MM/YYYY')";
                            }//end if(substr_count($value, '/') ==  2)
                            else {
                                    $arrayFormatoValores[] = "'%s'";
                            }//end else if(substr_count($value, '/') ==  2)
                    }//end if(!is_null($value))
            }//end foreach
            if(count($arrayCampos) > 0) {
                    $query = "INSERT INTO estorno_chargeback ";
                    $query .= '(' . implode(', ', array_keys($arrayCampos)).')';
                    $query .= " VALUES (" . implode(", ", $arrayFormatoValores).") RETURNING Currval('estorno_chargeback_ec_id_seq');";
                    $sql = vsprintf($query, $arrayCampos);
                    $retorno = SQLexecuteQuery($sql);
                    if($retorno) {
                            $arrayTemp = $EstornoDadosBancarios->dados;
                            $arrayCampos = array();
                            $arrayFormatoValores = array();
                            foreach ($arrayTemp as $key => $value) {
                                    if($value != "" && $EstornoDadosBancarios->isCampoTabela($key)) {
                                            $arrayCampos[$key] = $value;
                                            if(substr_count($value, '/') ==  2) {
                                                    $arrayFormatoValores[] = "to_date('%s','DD/MM/YYYY')";
                                            }//end if(substr_count($value, '/') ==  2)
                                            else {
                                                    $arrayFormatoValores[] = "'%s'";
                                            }//end else if(substr_count($value, '/') ==  2)
                                    }//end if(!is_null($value))
                            }//end foreach
                            if(count($arrayCampos) > 0) {
                                    //Capturando a sequence do ultimo inserido
                                    $fetch = pg_fetch_row($retorno);
                                    $arrayCampos['ec_id'] = $fetch[0]; 
                                    $arrayFormatoValores[] = "'%s'";
                                    $query = "INSERT INTO estorno_dados_bancarios ";
                                    $query .= '(' . implode(', ', array_keys($arrayCampos)).')';
                                    $query .= ' VALUES (' . implode(', ', $arrayFormatoValores).');';
                                    $sql = vsprintf($query, $arrayCampos);
                                    $retornoDadosBancarios = SQLexecuteQuery($sql);
                                    if($retornoDadosBancarios) {
                                            return true;
                                    }else{
                                            throw new Exception("FALHA AO INSERIR DADOS BANCÁRIOS DO NOVO ESTORNO / CHARGEBACK. Query: $sql\n");
                                    }                            
                            }//end if(count($arrayCampos) > 0)
                            else return true;
                    }else{
                            throw new Exception("FALHA AO INSERIR NOVO ESTORNO / CHARGEBACK. Query: $sql\n");
                    }
                
            }//end if(count($arrayCampos) > 0) 
            
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
        
    } //end function insert
    
    public function update (EstornoChargeBackVO $EstornoChargeBack, $ec_id, EstornoDadosBancariosVO $EstornoDadosBancarios = NULL){
        try {
            if(!is_null($ec_id)) {
                    
                    $arrayTemp = $EstornoChargeBack->dados;
                    $arrayCampos = array();
                    $arrayFormatoValores = array();
                    foreach ($arrayTemp as $key => $value) {
                            if($EstornoChargeBack->isCampoTabela($key))  {
                                    if($key  != 'ec_id') {
                                            $arrayCampos[$key] = $value;
                                            if(substr_count($value, '/') ==  2) {
                                                    $arrayFormatoValores[$key] = "to_date('%s','DD/MM/YYYY')";
                                            }//end if(substr_count($value, '/') ==  2)
                                            elseif($value == ""){
                                                    $arrayFormatoValores[$key] = "%s";
                                                    $arrayCampos[$key] = 'NULL';
                                            }//end elseif($value == "")
                                            else {
                                                    $arrayFormatoValores[$key] = "'%s'";
                                            }//end else if(substr_count($value, '/') ==  2)
                                    }//end if($key  != 'ec_id')
                            }//end if(!is_null($value))
                    }//end foreach
                    if(count($arrayCampos) > 0) {

                            $query = "UPDATE estorno_chargeback SET ";
                            $sets = false;
                            foreach ($arrayCampos as $key => $value) {
                                if($key != 'ec_id') {
                                    $query .= ($sets?', ':'').$key.' = ' .$arrayFormatoValores[$key] .' ';
                                    $sets = true;
                                }//end if(key($arrayCampos) != 'ec_id')
                            }// end for
                            $query .= " WHERE ec_id = ".$ec_id.";";
                            $sql = vsprintf($query, $arrayCampos);
                            $retorno = SQLexecuteQuery($sql);
                            if($retorno) {
                                //Tratando e atualizando dados bancários
                                $arrayTemp = $EstornoDadosBancarios->dados;
                                $arrayCampos = array();
                                $arrayFormatoValores = array();
                                $arrayTesteDelete = array();
                                foreach ($arrayTemp as $key => $value) {
                                        if($EstornoDadosBancarios->isCampoTabela($key)) {
                                                if($key  != 'edb_id') {
                                                        $arrayCampos[$key] = $value;
                                                        if(substr_count($value, '/') ==  2) {
                                                                $arrayFormatoValores[$key] = "to_date('%s','DD/MM/YYYY')";
                                                        }//end if(substr_count($value, '/') ==  2)
                                                        elseif($value == ""){
                                                                $arrayFormatoValores[$key] = "%s";
                                                                $arrayCampos[$key] = 'NULL';
                                                                $arrayTesteDelete[$key] = $value;
                                                        }//end elseif($value == "")
                                                        else {
                                                                $arrayFormatoValores[$key] = "'%s'";
                                                        }//end else if(substr_count($value, '/') ==  2)
                                                }//end if($key  != 'edb_id')
                                        }//end if($EstornoDadosBancarios->isCampoTabela($key))
                                }//end foreach
                                
                                $sql = "SELECT edb_id FROM estorno_dados_bancarios WHERE ec_id = ".$ec_id.";";
                                $retornoExiste = SQLexecuteQuery($sql);
                                if($retornoExiste && pg_num_rows($retornoExiste) >= 1) {
                                   // echo "<pre>arrayTesteDelete".print_r($arrayTesteDelete,true)."arrayCampos".print_r($arrayCampos,true)."</pre>";
                                        if(count($arrayTesteDelete) == count($arrayCampos)) { 
                                                //echo "DELETAR";
                                                $sql = "DELETE FROM estorno_dados_bancarios WHERE ec_id = ".$ec_id.";";
                                                //echo $sql;
                                                $retornoDadosBancarios = SQLexecuteQuery($sql);
                                                if(pg_affected_rows($retornoDadosBancarios) > 0) {
                                                        return true;
                                                }else{
                                                        throw new Exception("FALHA AO ATUALIZAR USANDO DELETE DADOS BANCÁRIOS DO NOVO ESTORNO / CHARGEBACK. Query: $sql\n");
                                                }  

                                        }//end if(count($arrayTesteDelete) == count($arrayCampos))
                                        else { 
                                                //echo "UPDATE";
                                                $query = "UPDATE estorno_dados_bancarios SET ";
                                                $sets = false;
                                                foreach ($arrayCampos as $key => $value) {
                                                    if($key != 'edb_id') { 
                                                        $query .= ($sets?', ':'').$key.' = ' .$arrayFormatoValores[$key] .' ';
                                                        $sets = true;
                                                    }//end if(key($arrayCampos) != 'ec_id')
                                                }// end for
                                                $query .= " WHERE ec_id = ".$ec_id.";";
                                                $sql = vsprintf($query, $arrayCampos);
                                                //echo $sql;
                                                $retornoDadosBancarios = SQLexecuteQuery($sql);
                                                if($retornoDadosBancarios) {
                                                        return true;
                                                }else{
                                                        throw new Exception("FALHA AO ATUALIZAR DADOS BANCÁRIOS DO NOVO ESTORNO / CHARGEBACK. Query: $sql\n");
                                                }                             
                                        }//end else do if(count($arrayTesteDelete) == count($arrayCampos))
                                }//end if($retornoExiste && pg_num_rows($retornoExiste) >= 1)
                                else {
                                        //echo "INSERT";
                                        $arrayCampos['ec_id'] = $ec_id; 
                                        $arrayFormatoValores[] = "'%s'";
                                        $query = "INSERT INTO estorno_dados_bancarios ";
                                        $query .= '(' . implode(', ', array_keys($arrayCampos)).')';
                                        $query .= ' VALUES (' . implode(', ', $arrayFormatoValores).');';
                                        $sql = vsprintf($query, $arrayCampos);
                                        //echo $sql;
                                        $retornoDadosBancarios = SQLexecuteQuery($sql);
                                        if($retornoDadosBancarios) {
                                                return true;
                                        }else{
                                                throw new Exception("FALHA AO ATUALIZAR USANDO INSERT DADOS BANCÁRIOS DO NOVO ESTORNO / CHARGEBACK. Query: $sql\n");
                                        }            
                                }//end else do if($retornoExiste && pg_num_rows($retornoExiste) >= 1)

                            }else{
                                $this->erros[] = "ERRO AO ATUALIZAR ESTORNO / CHARGEBACK. Query: $sql \n ";
                            }
                            
                    }//end if(count($arrayCampos) > 0) 
                    
            }//end if(!is_null($ec_id)) 
            
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
        
    } //end function update
    
    
    
} //end class EstornoChargeBackDAO
