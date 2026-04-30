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
    
    private function parseFiltroLegacy($cond, &$params)
    {
        $cond = trim($cond);
        $allowed = array('ec.ec_id','ec_id','ec.cec_id','cec_id','ec_data_devolucao','ec_forma_devolucao','vg_id','opr_codigo','ec_tipo','ec_tipo_usuario','ec_pin_bloqueado','ug_id','edb_cpf_cnpj','edb_titular','ec_cod_autorizacao','ec_valor');
        if (preg_match("/^UPPER\((edb_titular)\)\s+like\s+'%(.*)%'$/i", $cond, $m)) {
            $params[] = '%' . strtoupper($m[2]) . '%';
            return 'UPPER(' . $m[1] . ') like $' . count($params);
        }
        if (preg_match("/^([a-zA-Z_][a-zA-Z0-9_.]*)\s*(=|>=|<=|>|<)\s*'([^']*)'$/", $cond, $m) && in_array($m[1], $allowed)) {
            $params[] = $m[3];
            return $m[1] . ' ' . $m[2] . ' $' . count($params);
        }
        if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_.]*)\s*(=|>=|<=|>|<)\s*(-?\d+(?:\.\d+)?)$/', $cond, $m) && in_array($m[1], $allowed)) {
            $params[] = $m[3];
            return $m[1] . ' ' . $m[2] . ' $' . count($params);
        }
        return null;
    }

    private function buildFiltroWhere($filtro, &$params)
    {
        $where = array();
        if (!is_array($filtro)) return '';
        $allowed = array('ec.ec_id','ec_id','ec.cec_id','cec_id','ec_data_devolucao','ec_forma_devolucao','vg_id','opr_codigo','ec_tipo','ec_tipo_usuario','ec_pin_bloqueado','ug_id','edb_cpf_cnpj','edb_titular','ec_cod_autorizacao','ec_valor');
        $operators = array('=', '<>', '>', '<', '>=', '<=', 'LIKE', 'ILIKE');
        foreach ($filtro as $cond) {
            if (is_array($cond) && count($cond) === 3 && in_array($cond[0], $allowed) && in_array(strtoupper($cond[1]), $operators)) {
                $params[] = $cond[2];
                $where[] = $cond[0] . ' ' . strtoupper($cond[1]) . ' $' . count($params);
            } elseif (is_string($cond)) {
                $parsed = $this->parseFiltroLegacy($cond, $params);
                if ($parsed !== null) $where[] = $parsed;
            }
        }
        return count($where) ? ' WHERE ' . implode(' AND ', $where) : '';
    }

    private function insertRow($table, $fields, $returning = '')
    {
        $columns = array();
        $values = array();
        $params = array();
        foreach ($fields as $key => $value) {
            $columns[] = $key;
            $params[] = $value;
            $ph = '$' . count($params);
            $values[] = (is_string($value) && substr_count($value, '/') == 2) ? "to_date($ph,'DD/MM/YYYY')" : $ph;
        }
        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ')' . $returning . ';';
        return SQLexecuteQueryParams($sql, $params);
    }

    private function updateRow($table, $fields, $idColumn, $idValue)
    {
        $sets = array();
        $params = array();
        foreach ($fields as $key => $value) {
            if ($key == $idColumn) continue;
            if ($value === '') {
                $sets[] = $key . ' = NULL';
                continue;
            }
            $params[] = $value;
            $ph = '$' . count($params);
            $sets[] = (is_string($value) && substr_count($value, '/') == 2) ? $key . " = to_date($ph,'DD/MM/YYYY')" : $key . ' = ' . $ph;
        }
        if (!count($sets)) return false;
        $params[] = $idValue;
        $sql = "UPDATE $table SET " . implode(', ', $sets) . " WHERE $idColumn = $" . count($params) . ';';
        return SQLexecuteQueryParams($sql, $params);
    }
    
    public function get($filtro = null, $limit = null){
        $innerJoin = false;
        if(is_array($filtro)) {
                $objTeste = new EstornoDadosBancariosVO();
                foreach ($filtro as $key => $value) {
                        if($objTeste->isCampoTabela($key) || (is_array($value) && isset($value[0]) && $objTeste->isCampoTabela($value[0]))) {
                                $innerJoin = true;
                        }
                }
        }
        $sql = "SELECT 
                    ec.ec_id as id,*
                FROM estorno_chargeback as ec
                    INNER JOIN categoria_estorno_chargeback as cec ON ec.cec_id = cec.cec_id
                    ";
        if($innerJoin) $sql .= "INNER JOIN estorno_dados_bancarios as edb ON ec.ec_id = edb.ec_id ".PHP_EOL."                      ";
        else $sql .= "LEFT OUTER JOIN estorno_dados_bancarios as edb ON ec.ec_id = edb.ec_id ".PHP_EOL."                      ";
        $params = array();
        $sql .= $this->buildFiltroWhere($filtro, $params);
        $sql .= " ORDER BY ec_data_devolucao DESC";
        if($limit) {
            $params[] = (int) $limit;
            $sql .= " LIMIT $" . count($params);
        }
        try{
            $EstornoChargeBacks = count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
            if($EstornoChargeBacks && ((($EstornoChargeBacks) ? pg_num_rows($EstornoChargeBacks) : 0) > 0)){
                while($lineRow = pg_fetch_array($EstornoChargeBacks)){
                    $arrayTemp = array();
                    $codesGarena = [];
                    if($lineRow["ec_tipo_usuario"] == "L"){
                        $infoSale = "select vg_data_inclusao,vg_pagto_tipo,ug_responsavel,ug_cpf from tb_dist_venda_games inner join dist_usuarios_games on vg_ug_id = ug_id where vg_id = $1";
                        $infoCodeGarena = "select pin_guid_parceiro from tb_dist_venda_games_modelo left join tb_dist_venda_games_modelo_pins on vgmp_vgm_id = vgm_id left join pins on pin_codinterno = vgmp_pin_codinterno where vgm_vg_id = $1";
                    }else{
                        $infoSale = "select vg_data_inclusao,vg_pagto_tipo,ug_nome,ug_cpf from tb_venda_games inner join usuarios_games on vg_ug_id = ug_id where vg_id = $1";
                        $infoCodeGarena = "select pin_guid_parceiro from tb_venda_games_modelo left join tb_venda_games_modelo_pins on vgmp_vgm_id = vgm_id left join pins on pin_codinterno = vgmp_pin_codinterno where vgm_vg_id = $1";
                    }
                    $dataCodeExec = SQLexecuteQueryParams($infoCodeGarena, [$lineRow["vg_id"]]);
                    while($row = pg_fetch_array($dataCodeExec)){
                        if($row["pin_guid_parceiro"] != "" && $row["pin_guid_parceiro"] != null){
                            $codesGarena[] = $row["pin_guid_parceiro"];
                        }
                    }
                    $dataSaleExec = SQLexecuteQueryParams($infoSale, [$lineRow["vg_id"]]);
                    $dataSale = pg_fetch_array($dataSaleExec);
                    $EstornoChargeBack = new EstornoChargeBackVO($lineRow);
                    $arrayTemp = Util::object_to_array($EstornoChargeBack->dados);
                    unset($EstornoChargeBack);
                    $arrayTemp["vg_data_inclusao"] = $dataSale["vg_data_inclusao"];
                    $arrayTemp["vg_pagto_tipo"] = $dataSale["vg_pagto_tipo"];
                    $arrayTemp["ug_cpf"] = $dataSale["ug_cpf"];
                    $arrayTemp["cod_garena"] = $codesGarena;
                    $arrayTemp["usuarioNome"] = ($lineRow["ec_tipo_usuario"] == "L")? $dataSale["ug_responsavel"]: $dataSale["ug_nome"];
                    $this->EstornoChargeBacks[] = $arrayTemp;
                }
                return $this->EstornoChargeBacks;
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    } //end function get
    public function insert (EstornoChargeBackVO $EstornoChargeBack, ?EstornoDadosBancariosVO $EstornoDadosBancarios = null){
        try {
            $arrayCampos = array();
            foreach ($EstornoChargeBack->dados as $key => $value) {
                if($value != "" && $EstornoChargeBack->isCampoTabela($key)) $arrayCampos[$key] = $value;
            }
            if(count($arrayCampos) > 0) {
                $retorno = $this->insertRow('estorno_chargeback', $arrayCampos, " RETURNING Currval('estorno_chargeback_ec_id_seq')");
                if($retorno) {
                    if(!($EstornoDadosBancarios instanceof EstornoDadosBancariosVO)) throw new Exception("FALHA AO INSERIR NOVO ESTORNO / CHARGEBACK.");
                    $arrayCampos = array();
                    foreach ($EstornoDadosBancarios->dados as $key => $value) {
                        if($value != "" && $EstornoDadosBancarios->isCampoTabela($key)) $arrayCampos[$key] = $value;
                    }
                    if(count($arrayCampos) > 0) {
                        $fetch = pg_fetch_row($retorno);
                        $arrayCampos['ec_id'] = $fetch[0]; 
                        $retornoDadosBancarios = $this->insertRow('estorno_dados_bancarios', $arrayCampos);
                        if($retornoDadosBancarios) return true;
                        throw new Exception("FALHA AO INSERIR DADOS BANCARIOS DO NOVO ESTORNO / CHARGEBACK.
");
                    }
                    return true;
                }
                throw new Exception("FALHA AO INSERIR NOVO ESTORNO / CHARGEBACK.
");
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    } //end function insert
    public function update (EstornoChargeBackVO $EstornoChargeBack, $ec_id, ?EstornoDadosBancariosVO $EstornoDadosBancarios = null){
        try {
            if(!is_null($ec_id)) {
                $arrayCampos = array();
                foreach ($EstornoChargeBack->dados as $key => $value) {
                    if($EstornoChargeBack->isCampoTabela($key) && $key != 'ec_id') $arrayCampos[$key] = $value;
                }
                if(count($arrayCampos) > 0) {
                    $retorno = $this->updateRow('estorno_chargeback', $arrayCampos, 'ec_id', $ec_id);
                    if($retorno) {
                        if(!($EstornoDadosBancarios instanceof EstornoDadosBancariosVO)) return true;
                        $arrayCampos = array();
                        $arrayTesteDelete = array();
                        foreach ($EstornoDadosBancarios->dados as $key => $value) {
                            if($EstornoDadosBancarios->isCampoTabela($key) && $key != 'edb_id') {
                                $arrayCampos[$key] = $value;
                                if($value == "") $arrayTesteDelete[$key] = $value;
                            }
                        }
                        $retornoExiste = SQLexecuteQueryParams("SELECT edb_id FROM estorno_dados_bancarios WHERE ec_id = $1;", [$ec_id]);
                        if($retornoExiste && pg_num_rows($retornoExiste) >= 1) {
                            if(count($arrayTesteDelete) == count($arrayCampos)) { 
                                $retornoDadosBancarios = SQLexecuteQueryParams("DELETE FROM estorno_dados_bancarios WHERE ec_id = $1;", [$ec_id]);
                                if(pg_affected_rows($retornoDadosBancarios) > 0) return true;
                                throw new Exception("FALHA AO ATUALIZAR USANDO DELETE DADOS BANCARIOS DO NOVO ESTORNO / CHARGEBACK.
");
                            }
                            $retornoDadosBancarios = $this->updateRow('estorno_dados_bancarios', $arrayCampos, 'ec_id', $ec_id);
                            if($retornoDadosBancarios) return true;
                            throw new Exception("FALHA AO ATUALIZAR DADOS BANCARIOS DO NOVO ESTORNO / CHARGEBACK.
");
                        }
                        $arrayCampos['ec_id'] = $ec_id; 
                        $retornoDadosBancarios = $this->insertRow('estorno_dados_bancarios', $arrayCampos);
                        if($retornoDadosBancarios) return true;
                        throw new Exception("FALHA AO ATUALIZAR USANDO INSERT DADOS BANCARIOS DO NOVO ESTORNO / CHARGEBACK.
");
                    }
                    $this->erros[] = "ERRO AO ATUALIZAR ESTORNO / CHARGEBACK.
 ";
                }
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    } //end function update
    
    
    
} //end class EstornoChargeBackDAO
