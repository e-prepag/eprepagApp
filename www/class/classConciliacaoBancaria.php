<?php
require $raiz_do_projeto . 'includes/Feriados.php';
        
class ConciliacaoBancaria {

    private $data;
    private $tipo_pagamento;

    public function __construct(){}

    public function canAgrupar($tipo_pagamento, $ids){
        if ( is_null($tipo_pagamento) ) {
            throw new Exception('Tipo Pagamento não informado');
        }
        if ( is_null($ids) ) {
            throw new Exception('Ids não informados');
        }

        list($primeiro, $segundo) = explode(',', $ids);
        $data_primeiro = $this->getDateById($primeiro);
        $data_segundo = $this->getDateById($segundo);

        if ( !$data_primeiro || !$data_segundo ) {
            throw new Exception('Datas inválidas.');
        }

        /**
         * tipo_pagamento | dd_banco | agencia
         * 5 | 1 | 237 - Banco Bradesco S.A - Af: 2062-1 C/C: 1689-6
         * 5/6 | 2 | 237 - Banco Bradesco S.A. - Ag: 2062-1 C/C: 4707-4
         * 5/6 | 3 | 237 - Banco Bradesco S.A. - Ag: 2062-1 C/C: 20.459-5
         * 9 | 4 | 001 - Banco do Brasil - Ag: 1270-X C/C: 14.498-3
         * 9 | 4 | 001 - Banco do Brasil - Ag: 4055-X C/C: 5.811-4
         * NAO_EXISTE | 5 | 104 - Caixa Econômica Federal - Ag: 263 C/C: 003.21.922-9
         * A | 6 | 341 - Banco Itaú - Ag: 0444 C/C: 77567-0
         * A | 7 | 341 - Banco Itaú - Ag: 0444 C/C: 35570-5
         */
        global $BOLETO_MONEY_BRADESCO_COD_BANCO,
               $BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO,
               $BOLETO_MONEY_BANCO_ITAU_COD_BANCO;
        $bancos = array(
            '5' => $BOLETO_MONEY_BRADESCO_COD_BANCO, // Bradesco
            '6' => $BOLETO_MONEY_BRADESCO_COD_BANCO, // Bradesco
            '9' => $BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO, // Banco do Brasil
            //'A' => $BOLETO_MONEY_CAIXA_COD_BANCO', // Caixa Economica Federal // Nao existe
            'A' => $BOLETO_MONEY_BANCO_ITAU_COD_BANCO, // Banco Itaú
        );

        if ( $this->isWeekend($data_primeiro) || $this->isWeekend($data_segundo) ) {
            return true;
        }
        
//      $toBrazillian1 = date_format(DateTime::createFromFormat('Y-m-d H:i:s', $data_primeiro),'d/m/Y'); 
        $toBrazillian = explode(" ",$data_primeiro);
        $toBrazillian1 = explode("-",$toBrazillian[0]);
        $toBr = $toBrazillian1[2]."/".$toBrazillian1[1]."/".$toBrazillian1[0];
//      $toBrazillian2 = date_format(DateTime::createFromFormat('Y-m-d H:i:s', $data_segundo),'d/m/Y'); 
        $toBrazillian = explode(" ",$data_segundo);
        $toBrazillian2 = explode("-",$toBrazillian[0]);
        $toBr2 = $toBrazillian2[2]."/".$toBrazillian2[1]."/".$toBrazillian2[0];
        
        $feriados = new Feriados($toBrazillian1[0]);
        
        if ( $feriados->isFeriado($toBr) || $feriados->isFeriado($toBr2)){
            return true;
        }

        $importado_primeiro = $this->alreadyImportado($bancos[$tipo_pagamento], $data_primeiro);
        $importado_segundo = $this->alreadyImportado($bancos[$tipo_pagamento], $data_segundo);

        if ( !$importado_primeiro || !$importado_segundo ) {
            throw new Exception('Extrato não importado para essas datas.');
        }

        return true;
    }

    public function alreadyImportado($tipo_pagamento, $data){
        // Verificar se ja foi importado
        $sql = "SELECT dep_codigo FROM depositos_pendentes
                    WHERE dep_banco='{$tipo_pagamento}'
                    AND dep_data='{$data}';";

        //echo "$sql\n\n";

        $rs = SQLexecuteQuery($sql);
        return (pg_num_rows($rs) > 0);
    }

    public function getDateById($id) {
        $sql = "SELECT rfcb_data_registro FROM relfin_conciliacao_bancaria WHERE rfcb_id = {$id}";
        $rs = SQLexecuteQuery($sql);
        $fetched = pg_fetch_assoc($rs);
        if ( array_key_exists('rfcb_data_registro', $fetched) ) {
            return $fetched['rfcb_data_registro'];
        }
        return false;
    }

    public function isAgrupada($data, $tipo_pagamento){
        return !is_null($this->discoverFirstDateOfGroup($data, $tipo_pagamento));
    }

    /**
     * @param $data ?string Data que deve ser verificada se esta agrupada ou nao
     * @param $tipo_pagamento ?string Qual banco do relatorio
     * @return ?int
     */
    public function discoverFirstDateOfGroup($data = null, $tipo_pagamento = null) {

        if ( !is_null($data) ) {
            $this->data = $data;
        } elseif ( is_null($this->data) ) {
            return null;
        }
        if ( !is_null($tipo_pagamento) ){
            $this->tipo_pagamento = $tipo_pagamento;
        } elseif ( is_null($this->tipo_pagamento) ) {
            return null;
        }
        $id = null;

        $data_inicio = date('Y-m-d', strtotime("-6 days", strtotime($data))) . ' 00:00:00';

        $sql = "SELECT * FROM relfin_conciliacao_bancaria
                WHERE rfcb_data_registro
                BETWEEN '{$data_inicio}'::TIMESTAMP
                AND '{$data} 23:59:59'::TIMESTAMP
                AND rfcb_tipo_pagamento='{$tipo_pagamento}';";
        $rs = SQLexecuteQuery($sql);
        if ( pg_num_rows($rs) > 0 ) {
            while ( $r = pg_fetch_assoc($rs) ) {
                // Ainda esta no mesmo mes e esta agrupado?
                if ( date('m', strtotime($r['rfcb_data_registro'])) == date('m', strtotime($data)) && $r['rfcb_numero_de_dias'] > 1 ) {
                    $id = $r['rfcb_id'];
                    break;
                }
            }
        }
        return $id;
    }

    public function isWeekend($date) {
        return (date('N', strtotime($date)) >= 6);
    }
}