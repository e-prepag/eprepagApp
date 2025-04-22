<?php

class ClassCaf
{

    /* configuração dodos cliente */
    private $token;
    private $url;

    public function __construct()
    {
        $this->url = "https://api.combateafraude.com/v1/services";
        $this->token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsIm9yZyI6ImNhZiJ9.eyJpc3MiOiJja2lkX3dwNHNleHloNmRpeWQwM3BueTVvZTBpeiIsImlhdCI6MTc0MzQyNDc3MzIzNSwiYXVkIjoiNTViYzEzMzMtZmRjMy00NWQwLWIzODEtYjA3NjA0Njg2NjJkIn0.q0oDGO4w805JDrs_BFdJjbSeTzHwcDb5FaUb3bad4w4";
    }

    public function consultaCPF($cpf, $data_nascimento)
    {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data_nascimento)) {
            // Converte para YYYY-MM-DD
            $partes = explode('/', $data_nascimento);
            $data_nascimento = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
        }        

        $data = [
            "service" => "pf_basic_data",
            "attributes" => [
                "cpf" => $cpf,
                "birthDate" => $data_nascimento
            ]
        ];

        $data_post = json_encode($data, JSON_PRETTY_PRINT); // Converte para JSON formatado        

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->url, //desenv
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ],
            CURLOPT_POSTFIELDS => $data_post
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $resultado = [
                "msg" => 'Erro ao fazer a requisição cURL: ' . curl_error($ch),
            ];
            curl_close($ch);
            return $resultado;
        } else {
            $data = json_decode($response, true);

            if ($data !== null) {
                // Verifique se a resposta é uma matriz e se contém pelo menos um elemento
                if (is_array($data) && count($data) > 0 && isset($data["data"]["taxIdNumber"])) {

                    $arquivo = '/www/log/logCaf.txt';

                    $abre_arquivo = fopen($arquivo, 'a+');

                    fwrite($abre_arquivo, $response . "\n");

                    fclose($abre_arquivo);

                    // Extrai CPF e data de nascimento
                    $dataNascimento = substr($data["data"]["birthDate"], 0, 10);

                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataNascimento)) {
                        // Converte para DD/MM/YYYY
                        $partes = explode('-', $dataNascimento);
                        $dataNascimento = $partes[2] . '/' . $partes[1] . '/' . $partes[0];
                    }

                    $resultado = [
                        "pesquisas" => [
                            "camposResposta" => [
                                "status" => $data["data"]["taxIdStatus"],
                                "situacao" => $data["data"]["taxIdStatus"],
                                "nome" => $data["data"]["name"],
                                "data_nascimento" => $dataNascimento
                            ]
                        ]
                    ];
                    curl_close($ch);
                    return $resultado;
                } else {

                    $error = 'Erro ao fazer a requisicaoo cURL: ' . curl_error($ch);

                    $arquivo = '/www/log/logCaferror.txt';

                    $abre_arquivo = fopen($arquivo, 'a+');

                    if ($abre_arquivo) {
                        fwrite($abre_arquivo, $error . "\n"); // Escreve a mensagem de erro
                        fwrite($abre_arquivo, $response . "\n"); // Escreve a resposta da requisi��o, se houver
                        fclose($abre_arquivo);
                    }

                    $resultado = [
                        "msg" => 'A resposta JSON não contém nenhum elemento ou não é uma matriz.',
                    ];
                    curl_close($ch);
                    return $resultado;
                }
            } else {

                $error = 'Erro ao fazer a requisicaoo cURL: ' . curl_error($ch);

                $arquivo = '/www/log/logCaferror.txt';

                $abre_arquivo = fopen($arquivo, 'a+');

                if ($abre_arquivo) {
                    fwrite($abre_arquivo, $error . "\n"); // Escreve a mensagem de erro
                    fwrite($abre_arquivo, $response . "\n"); // Escreve a resposta da requisi��o, se houver
                    fclose($abre_arquivo);
                }

                $resultado = [
                    "msg" => 'Erro ao analisar a resposta JSON.',
                ];
                curl_close($ch);
                return $resultado;
            }
        }
    }
}

?>