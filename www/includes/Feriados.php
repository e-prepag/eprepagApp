<?php

class Feriados
{

    const FERIADO_NACIONAL = 1;
    const FERIADO_ESTADUAL = 2;
    const FERIADO_MUNICIPAL = 3;

    private ?int $dia = null;
    private ?int $mes = null;
    private ?int $ano = null;
    /** @var array<int, array{desc: string, tipo: int}> */
    private array $feriados = array();

    private string $mask = 'd/m/Y';

    /**
     * Se não for setado o ano, será calculado pelo ano atual
     *
     * @param int|null $ano
     * @param mixed $tipoFeriado
     */
    public function __construct(?int $ano = null, $tipoFeriado = null)
    {
        $this->setAno($ano);
        $this->setFeriados($tipoFeriado);
    }

    /**
     * @param string $data Data - formato: DD/MM/YYYY
     * @return bool
     */
    public function isFeriado(string $data): bool
    {
        $vData = $this->validateData($data);
        if (is_array($vData)) {
            $timestamp = mktime(0, 0, 0, (int)$vData[2], (int)$vData[1], (int)$vData[3]);

            return array_key_exists((int)$timestamp, $this->feriados);
        }
        return false;
    }

    /**
     * Verifica se a data informada é um dia útil
     *
     * @param string $data
     * @return bool
     */
    public function isDiaUtil(string $data): bool
    {
        if ($this->isFeriado($data)) {
            return false;
        }
        $timestamp = mktime(0, 0, 0, (int)$this->mes, (int)$this->dia, (int)$this->ano);
        $d = date('N', $timestamp ?: time());
        return ((int)$d < 6);
    }

    /**
     * Valida se uma data é válida
     * Retorna null se inválido ou um array contendo dia[1], mes[2] e ano[3] se válida.
     *
     * @param string $data Data no formato DD/MM/YYYY
     * @return array<int, string>|null
     */
    private function isDateValid(string $data): ?array
    {
        $s = preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $data, $matches);
        if ($s) {
            if ((int)$matches[2] > 12 || (int)$matches[1] > 31) {
                return null;
            }
            return $matches;
        }
        return null;
    }

    /**
     * Valida a data, retornando o ano, mes, dia se válida ou null se invalida
     *
     * @param string $data Data
     * @return array<int, string>|null
     */
    public function validateData(string $data): ?array
    {
        $matches = $this->isDateValid($data);
        if ($matches !== null) {
            $this->dia = (int)$matches[1];
            $this->mes = (int)$matches[2];
            $this->ano = (int)$matches[3];
        }
        return $matches;
    }

    /**
     * @param string $data Data no formato DD/MM/YYYY
     * @return int|null
     */
    public function getTimestamp(string $data): ?int
    {
        $vDate = $this->isDateValid($data);
        if ($vDate !== null) {
            return (int)mktime(0, 0, 0, (int)$vDate[2], (int)$vDate[1], (int)$vDate[3]);
        }
        return null;
    }

    /**
     * Coloca todos os feriados na propriedade
     * 
     * @param mixed $tipoFeriado
     * @return void
     */
    private function setFeriados(mixed $tipoFeriado = null): void
    {
        if ($tipoFeriado != null) {
            $this->setFeriadosEstaduais();
            $this->setFeriadosMunicipais();
        }

        $this->setFeriadosNacionais();
        ksort($this->feriados);
    }

    /**
     * Calcula os feriados do estado de sao paulo
     * @return void
     */
    private function setFeriadosEstaduais(): void
    {
        $this->addFeriado((int)mktime(0, 0, 0, 7,  9,    (int)$this->ano), 'Revolução Constitucionalista de 1932', self::FERIADO_ESTADUAL); // São Paulo - Lei nº 9.497, de 5 de maio de 1997
    }

    /**
     * Calcula os feriados do município de sao paulo
     * @return void
     */
    private function setFeriadosMunicipais(): void
    {
        $this->addFeriado((int)mktime(0, 0, 0, 1,  25,   (int)$this->ano), 'Aniversário da cidade de São Paulo', self::FERIADO_MUNICIPAL); // São Paulo
        $this->addFeriado((int)mktime(0, 0, 0, 11,  20,  (int)$this->ano), 'Dia da Consciência Negra', self::FERIADO_MUNICIPAL); // São Paulo - Lei nº 9.497, de 5 de maio de 1997
    }

    /**
     * Calcula todos os feriados nacionais
     * @return void
     */
    private function setFeriadosNacionais(): void
    {
        $pascoa     = easter_date((int)$this->ano); // Limite de 1970 ou após 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php
        $dia_pascoa = (int)date('j', (int)$pascoa);
        $mes_pascoa = (int)date('n', (int)$pascoa);
        $ano_pascoa = (int)date('Y', (int)$pascoa);

        // Datas fixas dos feriados Nacionail Basileiras
        $this->addFeriado((int)mktime(0, 0, 0, 1,  1,    (int)$this->ano), 'Confraternização Universal',  self::FERIADO_NACIONAL); // Lei nº 662, de 06/04/49
        $this->addFeriado((int)mktime(0, 0, 0, 4,  21,   (int)$this->ano), 'Tiradentes',                  self::FERIADO_NACIONAL); // Lei nº 662, de 06/04/49
        $this->addFeriado((int)mktime(0, 0, 0, 5,  1,    (int)$this->ano), 'Dia do Trabalhador',          self::FERIADO_NACIONAL); // Lei nº 662, de 06/04/49
        $this->addFeriado((int)mktime(0, 0, 0, 9,  7,    (int)$this->ano), 'Proclamação da Independência', self::FERIADO_NACIONAL); // Lei nº 662, de 06/04/49
        $this->addFeriado((int)mktime(0, 0, 0, 10,  12,  (int)$this->ano), 'Nossa Senhora Aparecida',     self::FERIADO_NACIONAL); // Lei nº 6802, de 30/06/80
        $this->addFeriado((int)mktime(0, 0, 0, 11,  2,   (int)$this->ano), 'Finados',                     self::FERIADO_NACIONAL); // Lei nº 662, de 06/04/49
        $this->addFeriado((int)mktime(0, 0, 0, 11, 15,   (int)$this->ano), 'Proclamação da República',    self::FERIADO_NACIONAL); // Lei nº 662, de 06/04/49
        $this->addFeriado((int)mktime(0, 0, 0, 12, 25,   (int)$this->ano), 'Natal',                       self::FERIADO_NACIONAL); // Lei nº 662, de 06/04/49
        // Dias que dependem da páscoa
        $this->addFeriado((int)mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48, $ano_pascoa), 'Segunda-feira de Carnaval', self::FERIADO_NACIONAL);
        $this->addFeriado((int)mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47, $ano_pascoa), 'Terça-feira de Carnaval',   self::FERIADO_NACIONAL);
        $this->addFeriado((int)mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2,  $ano_pascoa), 'Sexta-feira Santa',         self::FERIADO_NACIONAL);
        $this->addFeriado((int)mktime(0, 0, 0, $mes_pascoa, $dia_pascoa,      $ano_pascoa), 'Páscoa',                    self::FERIADO_NACIONAL);
        $this->addFeriado((int)mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60, $ano_pascoa), 'Corpus Christ',             self::FERIADO_NACIONAL);
    }

    /**
     * Adiciona um feriado a lista
     *
     * @param int $timestamp
     * @param string $desc
     * @param int $tipo
     * @return void
     */
    public function addFeriado(int $timestamp, string $desc, int $tipo): void
    {
        $this->feriados[$timestamp] = array('desc' => $desc, 'tipo' => $tipo);
    }

    /**
     * Retorna os feriados do ano informado
     *
     * @param mixed $ano
     * @return array<int, array{desc: string, tipo: int}>
     */
    public function getFeriados(mixed $ano): array
    {
        if ($ano != $this->ano) {
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
    public function setAno(mixed $ano): self
    {
        if (is_null($ano)) {
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
     * @return int|null
     */
    public function nextDiaUtil(string $data): ?int
    {
        $vData = $this->isDateValid($data);
        if ($vData === null) {
            return null;
        }
        $timestamp = (int)mktime(0, 0, 0, (int)$vData[2], (int)$vData[1], (int)$vData[3]) + (3600 * 24);
        $d = $this->isDiaUtil(date($this->mask, $timestamp));

        while (!$d) {
            $timestamp = $timestamp + (3600 * 24);
            $d = $this->isDiaUtil(date($this->mask, $timestamp));
        }
        return $timestamp;
    }

    /**
     * Retorna o último dia útil anterior da data informada
     *
     * @param string $data Data no formato DD/MM/YYYY
     * @return int|null
     */
    public function lastDiaUtil(string $data): ?int
    {
        $vData = $this->isDateValid($data);
        if ($vData === null) {
            return null;
        }

        $timestamp = (int)mktime(0, 0, 0, (int)$vData[2], (int)$vData[1], (int)$vData[3]) - (3600 * 24);
        $d = $this->isDiaUtil(date($this->mask, $timestamp));

        while (!$d) {
            $timestamp = $timestamp - (3600 * 24);
            $d = $this->isDiaUtil(date($this->mask, $timestamp));
        }

        return $timestamp;
    }

    /**
     * Adiciona uma quantidade de dias úteis a data
     *
     * @param string $data Data no formato DD/MM/YYYY
     * @param int $dias Dias a serem adicionados (contando apenas dias uteis)
     * @return int|null
     */
    public function addDiaUtil(string $data, int $dias): ?int
    {
        if ($dias < 1) {
            return $this->getTimestamp($data);
        }
        $vData = $this->validateData($data);
        if ($vData === null) {
            return null;
        }

        $novoTimestamp = $this->nextDiaUtil($data);
        if ($novoTimestamp === null) return null;
        $dias--;
        while ($dias) {
            $novoTimestamp = $this->nextDiaUtil(date($this->mask, $novoTimestamp));
            if ($novoTimestamp === null) return null;
            $dias -= 1;
        }

        return $novoTimestamp;
    }

    /**
     * Subtrai uma quantidade de dias úteis a data
     *
     * @param string $data
     * @param int $dias
     * @return int|null
     */
    public function subDiaUtil(string $data, int $dias): ?int
    {
        if ($dias < 1) {
            return $this->getTimestamp($data);
        }

        $vData = $this->validateData($data);
        if ($vData === null) {
            return null;
        }

        $timestamp = $this->lastDiaUtil($data);
        if ($timestamp === null) return null;
        $dias--;
        while ($dias) {
            $timestamp = $this->lastDiaUtil(date($this->mask, $timestamp));
            if ($timestamp === null) return null;
            $dias -= 1;
        }

        return $timestamp;
    }
}