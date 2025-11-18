<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php

/* Classe com os métodos para a entidade json
 */
require_once DIR_CLASS . "util/Util.class.php";
require_once DIR_CLASS . "util/Log.class.php";
require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

class Json
{
    private $_arrJsonFiles;
    private $_html;

    /**
     * @var PDO Conexão com o banco de dados
     */
    private $_pdo;

    function __construct()
    {
        $this->_pdo = ConnectionPDO::getConnection()->getLink();
    }

    public function setArrJsonFiles($arrJsonFiles)
    {
        $this->_arrJsonFiles = $arrJsonFiles;
        return $this;
    }

    /**
     * Atualiza (ou insere) o JSON no banco de dados com sistema de backup.
     * Mantém 3 versões: atual (v1), backup (v2) e backup antigo (v3)
     */
    public function refresh($arrJson = array())
    {
        try {
            $jsonContent = json_encode($arrJson);

            if (!empty($jsonContent)) { //TESTANDO ERRO - JSON INVÁLIDO

                // Pega o nome do arquivo, ex: 'meu_json.json'
                $nomeArquivo = basename($this->_arrJsonFiles[0], '.json');

                // Verifica se já existe um JSON válido no banco antes de fazer backup
                if ($this->isJsonInDB($nomeArquivo)) {
                    $this->moveRecursive($nomeArquivo);
                }

                // SQL para Inserir ou Atualizar se o 'nome' já existir (versão 1)
                $sql = "INSERT INTO jsons_epp (nome, conteudo, versao) 
                            VALUES (:nome, :conteudo, 1)
                            ON CONFLICT (nome, versao)
                            DO UPDATE
                                SET 
                                    conteudo = EXCLUDED.conteudo, 
                                    versao = EXCLUDED.versao;";

                $stmt = $this->_pdo->prepare($sql);

                $success = $stmt->execute([
                    ':nome' => $nomeArquivo,
                    ':conteudo' => $jsonContent
                ]);

                if ($success) {
                    return true;
                } else {
                    throw new Exception("ERRO AO SALVAR NO BANCO - Nome: [" . $nomeArquivo . "]");
                }
            } else {
                echo "Estava VAZIO o JSON_ENCODE (" . json_encode($arrJson) . ")" . PHP_EOL . "Pode ser problema de UFT8_DECODE." . PHP_EOL . "Arquivo: " . $this->_arrJsonFiles[0] . PHP_EOL;
                return true;
            }
        } catch (Exception $ex) {
            $geraLog = new Log("JSON_DB", array(
                "ERROR: " . $ex->getMessage(),
                "FILE: " . $ex->getFile(),
                "LINE " . $ex->getLine()
            ));
            return false;
        }
    }

    /**
     * Faz o rodízio dos backups no banco de dados
     * Move versão 2 para versão 3, e versão 1 para versão 2
     */
    public function moveRecursive($nomeArquivo)
    {
        // Copia v2 para v3
        $this->copyJson($nomeArquivo, 2, 3);
        // Copia v1 para v2
        $this->copyJson($nomeArquivo, 1, 2);
    }

    /**
     * Copia o conteúdo de uma versão para outra no banco
     */
    public function copyJson($nomeArquivo, $versaoOrigem, $versaoDestino)
    {
        try {
            // Deleta a versão de destino se existir
            $sqlDelete = "DELETE FROM jsons_epp WHERE nome = :nome AND versao = :versao";
            $stmtDelete = $this->_pdo->prepare($sqlDelete);
            $stmtDelete->execute([
                ':nome' => $nomeArquivo,
                ':versao' => $versaoDestino
            ]);

            // Verifica se a versão de origem tem JSON válido
            if ($this->isJsonInDB($nomeArquivo, $versaoOrigem)) {
                // Busca o conteúdo da versão de origem
                $sqlSelect = "SELECT conteudo FROM jsons_epp WHERE nome = :nome AND versao = :versao";
                $stmtSelect = $this->_pdo->prepare($sqlSelect);
                $stmtSelect->execute([
                    ':nome' => $nomeArquivo,
                    ':versao' => $versaoOrigem
                ]);
                $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    // Insere na versão de destino
                    $sqlInsert = "INSERT INTO jsons_epp (nome, conteudo, versao) VALUES (:nome, :conteudo, :versao)";
                    $stmtInsert = $this->_pdo->prepare($sqlInsert);
                    $success = $stmtInsert->execute([
                        ':nome' => $nomeArquivo,
                        ':conteudo' => $row['conteudo'],
                        ':versao' => $versaoDestino
                    ]);

                    if (!$success) {
                        throw new Exception("ERRO AO COPIAR JSON NO BANCO (v{$versaoOrigem} -> v{$versaoDestino})");
                    }

                    return true;
                }
            }
        } catch (Exception $ex) {
            throw new Exception("ERRO AO COPIAR JSON NO BANCO: " . $ex->getMessage());
        }
    }

    /**
     * Verifica se existe um JSON válido no banco de dados
     */
    public function isJsonInDB($nomeArquivo, $versao = 1)
    {
        try {
            $sql = "SELECT conteudo FROM jsons_epp WHERE nome = :nome AND versao = :versao";
            $stmt = $this->_pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nomeArquivo,
                ':versao' => $versao
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['conteudo'])) {
                // Verifica se o conteúdo tem mais de 5 caracteres (equivalente ao filesize < 5)
                if (strlen($row['conteudo']) < 5) {
                    return false;
                }

                $json = json_decode($row['conteudo']);
                if (is_object($json) || is_array($json)) {
                    return true;
                }
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Busca o JSON do banco de dados com fallback para versões de backup.
     * Tenta versão 1, depois 2, depois 3.
     */
    public function getJsonRecursive($currJsonFile = 0)
    {
        try {
            $nomeArquivo = basename($this->_arrJsonFiles[0], '.json');
            $versao = $currJsonFile + 1; // currJsonFile 0 = versão 1, etc.

            // Verifica tamanho mínimo antes de processar
            $sqlSize = "SELECT LENGTH(conteudo) as tamanho FROM jsons_epp WHERE nome = :nome AND versao = :versao";
            $stmtSize = $this->_pdo->prepare($sqlSize);
            $stmtSize->execute([
                ':nome' => $nomeArquivo,
                ':versao' => $versao
            ]);
            $rowSize = $stmtSize->fetch(PDO::FETCH_ASSOC);

            if (!$rowSize || $rowSize['tamanho'] < 5) {
                throw new Exception("JSON muito pequeno ou inexistente (versão {$versao})");
            }

            $sql = "SELECT conteudo FROM jsons_epp WHERE nome = :nome AND versao = :versao";
            $stmt = $this->_pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nomeArquivo,
                ':versao' => $versao
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['conteudo'])) {
                $json = json_decode($row['conteudo']);

                if (is_object($json) || is_array($json)) {
                    return $json;
                } else {
                    throw new Exception("JSON mal formatado no banco (versão {$versao})");
                }
            } else {
                throw new Exception("Nenhum JSON encontrado no banco (versão {$versao})");
            }
        } catch (Exception $ex) {
            $geraLog = new Log("JSON_DB", array(
                "ERROR: problema ao obter do banco (versão " . ($currJsonFile + 1) . "): " . $nomeArquivo,
                "MESSAGE: " . $ex->getMessage(),
                "FILE: " . $ex->getFile(),
                "LINE " . $ex->getLine()
            ));

            // Tenta a próxima versão (fallback)
            $currJsonFile++;

            if ($currJsonFile <= 2) { // Tenta até a versão 3
                return $this->getJsonRecursive($currJsonFile);
            } else {
                // Esgotou todas as versões, envia email de erro
                if (function_exists("enviaEmail4")) {
                    $server_url = "" . EPREPAG_URL . "";
                    $to = "nathany.andrade@e-prepag.com.br, estagiario1@e-prepag.com, wagner@e-prepag.com.br";

                    if (checkIP()) {
                        $server_url = $_SERVER['SERVER_NAME'];
                        $to = "estagiario1@e-prepag.com";
                    }

                    $cc = null;
                    $bcc = false;
                    $subject = "ERRO NA OBTENÇÃO DE JSON DO BANCO. (" . $nomeArquivo . " / url: $server_url)";
                    $body_html = "<p>ERRO GRAVE ao obter todos os JSONs (versões 1, 2 e 3) do banco: " . $nomeArquivo . " - " . date("d/m/Y H:i") . "</p>";
                    $body_html .= "<p>Server url: $server_url</p>";
                    $body_plain = null;

                    enviaEmail4($to, $cc, $bcc, $subject, $body_html, $body_plain);
                }

                return false;
            }
        }
    }

    /*
     * Método que embeleza o Json de forma que vc consiga vê-lo na tela e navegar dentro dele tendo uma melhor visibilidade
     * @var $content - é o conteúdo a ser visualizado (string JSON ou nome de arquivo no banco)
     * @libs - false por padrão, indica que as bibliotecas necessárias para funcionamento do programa NÃO estão inclusas, então, as incluirá.
     * se passado TRUE, ele não as incluirá;
     */
    public function jsonBeautifier($content, $libs = false)
    {
        global $url;

        if (!$url)
            $url = "" . EPREPAG_URL_HTTPS . "";

        $json = null;

        // Tenta buscar do banco se for um nome de arquivo
        if (is_string($content) && !json_decode($content)) {
            $nomeArquivo = basename($content, '.json');
            if ($this->isJsonInDB($nomeArquivo)) {
                $sql = "SELECT conteudo FROM jsons_epp WHERE nome = :nome AND versao = 1";
                $stmt = $this->_pdo->prepare($sql);
                $stmt->execute([':nome' => $nomeArquivo]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $json = json_decode($row['conteudo']);
                }
            }
        } else if (is_string($content)) {
            // É uma string JSON direta
            $json = json_decode($content);
        }

        if (!empty($json)) {
            if (!$libs) {
                $this->_html = '<script type="text/javascript" src="' . $url . '/js/jquery/jquery.js"></script>
                                <script type="text/javascript" src="' . $url . '/bootstrap/js/bootstrap.min.js"></script>
                                <link href="' . $url . '/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
                                <link href="' . $url . '/css/creditos.css" rel="stylesheet" type="text/css" />';
            }

            $this->_html .= '<script>
                                $(function(){
                                    $(".collapse").collapse("show");
                                });
                            </script>
                            <link rel="stylesheet" type="text/css" href="' . $url . '/css/jsonbeautifier.css">
                            <div id="json">';

            $this->jsonBeautifierMain($json);
            $this->_html .= "</div>";
        } else {
            $this->_html = "";
        }

        return $this->_html;
    }

    private function jsonBeautifierMain($json)
    {
        foreach ($json as $ind => $arr) {
            if (empty($arr))
                continue;

            if (is_string($arr)) {
                $this->jsonBeautifierStr($ind, $arr);
            }

            if (is_array($arr)) {
                $this->jsonBeautifierArr($ind, $arr);
            }

            if (is_object($arr)) {
                $this->jsonBeautifierObj($ind, $arr);
            }
        }
    }

    private function jsonBeautifierArr($title, $arr)
    {
        $id = rand();

        $this->_html .= '<li>
                <div class="beautifier-hoverable">
                <a href="#arr' . $id . '" class="glyphicon glyphicon-minus t0" data-toggle="collapse"></a>';

        if (is_string($title))
            $this->_html .=    '<span class="beautifier-property">' . $title . '</span>:';

        $this->_html .=   '<div class="beautifier-collapser"></div>
                [<span class="beautifier-ellipsis"></span>
                <ul id="arr' . $id . '" class="collapse array beautifier-collapsible">';

        $this->jsonBeautifierMain($arr, $id);

        $this->_html .= '      </ul>
                ],';
    }

    private function jsonBeautifierObj($title, $arr)
    {
        $id = rand();

        $this->_html .= '<li>
                <div class="beautifier-hoverable">
                <a href="#ob' . $id . '" class="glyphicon glyphicon-minus t0" data-toggle="collapse"></a>';

        if (is_string($title))
            $this->_html .=    '<span class="beautifier-property">' . $title . '</span>:';

        $this->_html .=   '<div class="beautifier-collapser"></div>
                {<span class="beautifier-ellipsis"></span>
                    <ul id="ob' . $id . '" class="obj collapse beautifier-collapsible t0">';

        $this->jsonBeautifierMain($arr, $id);

        $this->_html .= '      </ul>
                },';
    }

    private function jsonBeautifierStr($title, $arr)
    {
        $this->_html .= "<li class=\"beautifier-li\">
                <div class=\"beautifier-hoverable\">
                    <span class=\"beautifier-property\">$title</span>: 
                    <span class=\"beautifier-type-string\">\"$arr\"</span>,
                </div>
             </li>";
    }
}
