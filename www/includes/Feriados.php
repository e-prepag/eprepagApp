<?php

class Feriados {

    const FERIADO_NACIONAL = 1;
    const FERIADO_ESTADUAL = 2;
    const FERIADO_MUNICIPAL = 3;

    private $dia;
    private $mes;
    private $ano;
    private $feriados = array();

    private $mask = 'd/m/Y';

    /**
     * Se não for setado o ano, será calculado pelo ano atual
     *
     * @param null|string $ano
     */
    public function __construct($ano = null,$tipoFeriado = null)
    {
        $this->setAno($ano);
        $this->setFeriados($tipoFeriado);
    }

    /**
     * @param $data String Data - formtato: DD/MM/YYYY
     * @return bool
     */
    public function isFeriado($data)
    {
        $vData = $this->validateData($data);
        if ( $vData ) {
            $timestamp = mktime(0, 0, 0, $vData[2], $vData[1], $vData[3]);

            return array_key_exists($timestamp, $this->feriados) ? $this->feriados[$timestamp] : false;
        }
        return false;
    }

    /**
     * Verifica se a data informada é um dia útil
     *
     * @param string $data
     * @return bool
     */
    public function isDiaUtil($data)
    {
        if ( $this->isFeriado($data) ) {
            return false;
        }
        $d = date('N', mktime(0, 0, 0, $this->mes, $this->dia, $this->ano));
        return ((int)$d < 6);
    }

    /**
     * Valida se uma data é válida
     * Retorna false se inválido ou um array contendo dia[1], mes[2] e ano[3] se válida.
     *
     * @param string $data Data no formato DD/MM/YYYY
     * @return bool|mixed
     */
    private function isDateValid($data)
    {
        $s = preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $data, $matches);
        if ( !!$s ) {
            if ( $matches[2] > 12 || $matches[1] > 31 ) {
                return false;
            }
        }
        return $matches;
    }

    /**
     * Valida a data, retornando o ano, mes, dia se válida ou false se invalida
     *
     * @param string $data Data
     * @return bool|mixed
     */
    public function validateData($data)
    {
        $matches = $this->isDateValid($data);
        if ( !!$matches ) {
            $this->dia = $matches[1];
            $this->mes = $matches[2];
            $this->ano = $matches[3];
        }
        return $matches;
    }

    /**
     * @param string $data Data no formato DD/MM/YYYY
     * @return bool|int
     */
    public function getTimestamp($data)
    {
        $vDate = $this->isDateValid($data);
        if ( !!$vDate ) {
            return mktime(0,0,0, $vDate[2], $vDate[1], $vDate[3]);
        }
        return false;
    }

    /**
     * Coloca todos os feriados na propriedade
     */
    private function setFeriados($tipoFeriado = null)
    {
        if($tipoFeriado != null){
            $this->setFeriadosEstaduais();
            $this->setFeriadosMunicipais();
        }
        
        $this->setFeriadosNacionais();
        ksort($this->feriados);
    }

    /**
     * Calcula os feriados do estado de sao paulo
     */
    private function setFeriadosEstaduais()
    {
        $this->addFeriado(mktime(0, 0, 0, 7,  9,    $this->ano), 'Revolução Constitucionalista de 1932', self::FERIADO_ESTADUAL);// São Paulo - Lei nº 9.497, de 5 de maio de 1997
    }
    
    /**
     * Calcula os feriados do município de sao paulo
     */
    private function setFeriadosMunicipais()
    {
        $this->addFeriado(mktime(0, 0, 0, 1,  25,   $this->ano), 'Aniversário da cidade de São Paulo', self::FERIADO_MUNICIPAL);// São Paulo
        $this->addFeriado(mktime(0, 0, 0, 11,  20,  $this->ano), 'Dia da Consciência Negra', self::FERIADO_MUNICIPAL);// São Paulo - Lei nº 9.497, de 5 de maio de 1997
    }

    /**
     * Calcula todos os feriados nacionais
     */
    private function setFeriadosNacionais()
    {
        $pascoa     = easter_date($this->ano); // Limite de 1970 ou após 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php
        $dia_pascoa = date('j', $pascoa);
        $mes_pascoa = date('n', $pascoa);
        $ano_pascoa = date('Y', $pascoa);

        // Datas fixas dos feriados Nacionail Basileiras
        $this->addFeriado(mktime(0, 0, 0, 1,  1,    $this->ano), 'Confraternização Universal',  self::FERIADO_NACIONAL);// Lei nº 662, de 06/04/49
        $this->addFeriado(mktime(0, 0, 0, 4,  21,   $this->ano), 'Tiradentes',                  self::FERIADO_NACIONAL);// Lei nº 662, de 06/04/49
        $this->addFeriado(mktime(0, 0, 0, 5,  1,    $this->ano), 'Dia do Trabalhador',          self::FERIADO_NACIONAL);// Lei nº 662, de 06/04/49
        $this->addFeriado(mktime(0, 0, 0, 9,  7,    $this->ano), 'Proclamação da Independência',self::FERIADO_NACIONAL);// Lei nº 662, de 06/04/49
        $this->addFeriado(mktime(0, 0, 0, 10,  12,  $this->ano), 'Nossa Senhora Aparecida',     self::FERIADO_NACIONAL);// Lei nº 6802, de 30/06/80
        $this->addFeriado(mktime(0, 0, 0, 11,  2,   $this->ano), 'Finados',                     self::FERIADO_NACIONAL);// Lei nº 662, de 06/04/49
        $this->addFeriado(mktime(0, 0, 0, 11, 15,   $this->ano), 'Proclamação da República',    self::FERIADO_NACIONAL);// Lei nº 662, de 06/04/49
        $this->addFeriado(mktime(0, 0, 0, 12, 25,   $this->ano), 'Natal',                       self::FERIADO_NACIONAL);// Lei nº 662, de 06/04/49
        // Dias que dependem da páscoa
        $this->addFeriado(mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48, $ano_pascoa), 'Segunda-feira de Carnaval', self::FERIADO_NACIONAL);
        $this->addFeriado(mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47, $ano_pascoa), 'Terça-feira de Carnaval',   self::FERIADO_NACIONAL);
        $this->addFeriado(mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2,  $ano_pascoa), 'Sexta-feira Santa',         self::FERIADO_NACIONAL);
        $this->addFeriado(mktime(0, 0, 0, $mes_pascoa, $dia_pascoa,      $ano_pascoa), 'Páscoa',                    self::FERIADO_NACIONAL);
        $this->addFeriado(mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60, $ano_pascoa), 'Corpus Christ',             self::FERIADO_NACIONAL);
    }

    /**
     * Adiciona um feriado a lista
     *
     * @param int $timestamp
     * @param string $desc
     * @param int $tipo
     */
    public function addFeriado($timestamp, $desc, $tipo)
    {
        $this->feriados[$timestamp] = array('desc' => $desc, 'tipo' => $tipo);
    }

    /**
     * Retorna os feriados do ano informado
     *
     * @param int|string $ano
     * @return array
     */
    public function getFeriados($ano) {
        if ( $ano != $this->ano ) {
            $this->setAno($ano)->setFeriados();
        }
        return $this->feriados;
    }

    /**
     * Ao informar um ano ele recalcula os feriados nacionais
     * (existem feriados com dias variáveis)
     *
     * @param mixed $ano
     * @return Feriados
     */
    public function setAno($ano)
    {
        if ( is_null($ano) ) {
            $ano = (int) date('Y');
        }
        $this->ano = $ano;
        $this->setFeriados();
        return $this;
    }

    /**
     * Retorna o próximo dia útil da data informada
     *
     * @param string $data Data no formato DD/MM/YYYY
     * @return bool|int
     */
    public function nextDiaUtil($data)
    {
        $vData = $this->isDateValid($data);
        if ( !$vData ) {
            return false;
        }
        $timestamp = mktime(0, 0, 0, $vData[2], $vData[1], $vData[3])+(3600*24);
        $d = $this->isDiaUtil(date($this->mask, $timestamp));

        if ( !$d ) {
            while ( !$d ) {
                $timestamp = $timestamp + (3600 * 24);
                $d = $this->isDiaUtil(date($this->mask, $timestamp));
                if ( $d ) {
                    return $timestamp;
                }
            }
        }
        return $timestamp;
    }

    /**
     * Retorna o último dia útil anterior da data informada
     *
     * @param string $data Data no formato DD/MM/YYYY
     * @return bool|int
     */
    public function lastDiaUtil($data)
    {
        $vData = $this->isDateValid($data);
        if ( !$vData ) {
            return false;
        }

        $timestamp = mktime(0, 0, 0, $vData[2], $vData[1], $vData[3])-(3600*24);
        $d = $this->isDiaUtil(date($this->mask, $timestamp));

        if ( !$d ) {
            while ( !$d ) {
                $timestamp = $timestamp - (3600*24);
                $d = $this->isDiaUtil(date($this->mask, $timestamp));
                if ( $d ) {
                    return $timestamp;
                }
            }
        }

        return $timestamp;
    }

    /**
     * Adiciona uma quantidade de dias úteis a data
     *
     * @param string $data Data no formato DD/MM/YYYY
     * @param int $dias Dias a serem adicionados (contando apenas dias uteis)
     * @return bool|int
     */
    public function addDiaUtil($data, $dias) {
        if ( $dias < 1) {
            return $this->getTimestamp($data);
        }
        $vData = $this->validateData($data);
        if ( !$vData ) {
            return false;
        }

        $novoTimestamp = $this->nextDiaUtil($data); // Dia +1 util
        $dias--;
        while ( $dias ) {
            $novoTimestamp = $this->nextDiaUtil(date($this->mask, $novoTimestamp)); // Dia +dias-1 util
            $dias -= 1;
        }

        return $novoTimestamp;
    }

    /**
     * Subtrai uma quantidade de dias úteis a data
     *
     * @param string $data
     * @param int $dias
     * @return bool|int
     */
    public function subDiaUtil($data, $dias)
    {
        if ( $dias < 1) {
            return $this->getTimestamp($data);
        }

        $vData = $this->validateData($data);
        if ( !$vData ) {
            return false;
        }

        $timestamp = $this->lastDiaUtil($data);// Dia -1 util
        $dias--;
        while ( $dias ) {
            $timestamp = $this->lastDiaUtil(date($this->mask, $timestamp)); // Dia -dias-1 util
            $dias -=1;
        }

        return $timestamp;
    }
}