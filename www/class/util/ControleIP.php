<?php

class ControleIP
{
    /**
     * Validar IP
     *
     * @param $ip string IP a ser validado
     * @return bool
     */
    public function isIpValid($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * @param $opr_range string Lista de IPs separados por ponto-e-virgula (;) e as faixas separadas por hífen (-)
     * @param $ip
     * @return bool
     */
    public function isInOprRange($opr_range, $ip)
    {
        $opr_range = preg_replace('/\s+/', '', $opr_range);
        if (empty($opr_range)) {
            return true;
        }
        if (strpos($opr_range, ';') !== false) {
            $ips = explode(';', $opr_range);
            foreach ($ips as $ip_range) {
                if (strpos($ip_range, '-') !== false) {
                    list($inicial, $final) = explode('-', $ip_range);
                    if ( $this->simpleInRange($ip, $inicial, $final) ) {
                        return true;
                    }
                } else {
                    if ( $ip == $ip_range) {
                        return true;
                    }
                }
            }
        } elseif (strpos($opr_range, '-') !== false) {
            list($inicial, $final) = explode('-', $opr_range);
            return $this->simpleInRange($ip, $inicial, $final);
        }
        return $opr_range == $ip;
    }

    /**
     * Verifica se um dado IP esta dentro da faixa de dois IPs informados
     *
     * @param $ip string IP a ser verificado
     * @param $initial string IP inicial da faixa
     * @param $final string IP final da faixa
     * @return bool
     */
    public function simpleInRange($ip, $initial, $final)
    {
        $initial = ip2long($initial);
        $final = ip2long($final);
        $ip = ip2long($ip);

        return ($ip >= $initial && $ip <= $final);
    }
}