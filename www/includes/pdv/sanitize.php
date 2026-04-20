<?php

declare(strict_types=1);

/**
 * sanitize input data in array $params[]
 * returns the sanitized version of $params[]
 *
 * @param array<string, array<int, string>> $params
 * @param string $err_cod
 * @return array<string, mixed>
 */
function sanitize_input_data_array(array $params, string &$err_cod): array
{
    /** @var array<string, mixed> $params_out */
    $params_out = [];
    foreach ($params as $key => $val) {
        $val_mod = null;
        if (isset($val[2]) && $val[2] === '1') {
            $val[0] = sanitize_general_array($val[0]);
        }
        if (isset($val[1])) {
            switch ($val[1]) {
                case 'S':
                    $val_mod = sanitize_str_array($val[0]);
                    break;
                case 'I':
                    $val_mod = sanitize_int_array($val[0]);
                    break;
                case 'D':
                    $val_mod = sanitize_date_array($val[0]);
                    break;
            }
        }
        $params_out[$key] = $val_mod;
    }
    return $params_out;
}

/**
 * @param mixed $intval
 * @return int
 */
function sanitize_int_array(mixed $intval): int
{
    if (is_numeric($intval)) {
        return (int)$intval;
    }
    return 0;
}

/**
 * @param string $strval
 * @return string
 */
function sanitize_str_array(string $strval): string
{
    $strval = addslashes($strval);
    // FILTER_SANITIZE_STRING is deprecated in PHP 8.1
    return htmlspecialchars($strval, ENT_QUOTES, 'UTF-8');
}

/**
 * @param string $dateval
 * @return string
 */
function sanitize_date_array(string $dateval): string
{
    $dateval = addslashes($dateval);
    if (strlen($dateval) === 19) {
        if ((substr($dateval, 4, 1) === "-") && (substr($dateval, 7, 1) === "-") && (substr($dateval, 10, 1) === " ") && (substr($dateval, 13, 1) === ":") && (substr($dateval, 16, 1) === ":")) {
            if ((is_numeric(substr($dateval, 0, 4))) && (is_numeric(substr($dateval, 5, 2))) &&
                (is_numeric(substr($dateval, 8, 2))) && (is_numeric(substr($dateval, 11, 2))) &&
                (is_numeric(substr($dateval, 14, 2))) && (is_numeric(substr($dateval, 17, 2)))
            ) {
                return $dateval;
            }
            return "DATEERRORValuesInt";
        }
        return "DATEERRORPunctuation";
    }
    return "DATEERROR";
}

/**
 * @param string $strval
 * @return string
 */
function sanitize_general_array(string $strval): string
{
    $outval = $strval;
    $replacements = [
        "DROP" => "d_r_o_p",
        "CREATE" => "c_r_e_a_t_e",
        "INSERT" => "i_n_s_e_r_t",
        "DELETE" => "d_e_l_e_t_e",
        "SELECT" => "s_e_l_e_c_t",
        "UPDATE" => "u_p_d_a_t_e",
        "ALTER" => "a_l_t_e_r"
    ];

    $upperOutval = strtoupper($outval);
    foreach ($replacements as $search => $replace) {
        $upperOutval = str_replace($search, $replace, $upperOutval);
    }

    $outval = $upperOutval;
    $outval = str_replace(["--", "\\", "'", ";"], "", $outval);
    return $outval;
}