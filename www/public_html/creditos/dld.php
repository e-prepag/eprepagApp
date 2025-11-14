<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);  // Exibe todos os tipos de erros
require_once "../../includes/constantes.php";

if (!function_exists('finfo_open')) {

  /**
   * Define a constante que a função original usa, se não estiver definida.
   */
  if (!defined('FILEINFO_MIME_TYPE')) {
    define('FILEINFO_MIME_TYPE', 16); // Este é o valor padrão da constante
  }

  /**
   * Cria uma função 'finfo_open' de substituição.
   */
  function finfo_open($options = 0, $magic_file = null)
  {
    $finfo = new stdClass(); // Cria um objeto genérico
    $finfo->options = $options; // Armazena as opções (vamos precisar no finfo_file)
    return $finfo;
  }

  /**
   * Cria uma função 'finfo_file' de substituição.
   */
  function finfo_file($finfo, $filename)
  {
    // Pega a extensão do arquivo em letras minúsculas
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Lista de tipos MIME básicos (MUITO incompleta!)
    $mime_types = [
      'jpg'  => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'png'  => 'image/png',
      'gif'  => 'image/gif',
      'pdf'  => 'application/pdf',
      'doc'  => 'application/msword',
      'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'xls'  => 'application/vnd.ms-excel',
      'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'txt'  => 'text/plain',
      'csv'  => 'text/csv',
    ];

    // Se a extensão estiver na lista, retorna o tipo MIME.
    // Caso contrário, retorna um tipo genérico.
    $mime_type = $mime_types[$extension] ?: 'application/octet-stream';

    // Verifica se o usuário pediu especificamente o MIME_TYPE
    if ($finfo->options === FILEINFO_MIME_TYPE) {
      return $mime_type;
    }

    // A função original podia retornar outras coisas, mas simplificamos
    return $mime_type;
  }

  /**
   * Cria uma função 'finfo_close' de substituição.
   * Não faz nada, apenas retorna true.
   */
  function finfo_close($finfo)
  {
    unset($finfo); // Libera o objeto da memória
    return true;
  }
}

// Configurações
define('BASE_DIR', realpath(RAIZ_DO_PROJETO . 'arquivos_gerados/txts/txt'));
define('LOG_DOWNLOADS', false);
define('LOG_FILE', BASE_DIR . '/downloads.log');

// Whitelist de extensões permitidas
$allowed_ext = array(
  'csv' => 'text/csv'
);

// Validar BASE_DIR
if (BASE_DIR === false || !is_dir(BASE_DIR)) {
  error_log("BASE_DIR inválido ou não existe");
  die("Erro de configuração do servidor.");
}

// 1. VALIDAR parâmetro 'f'
if (!isset($_GET['f']) || !is_string($_GET['f']) || empty($_GET['f'])) {
  die("Nome de arquivo não especificado.");
}

// 2. SANITIZAR nome do arquivo
$fname = basename($_GET['f']);
$fname = preg_replace('/[^a-zA-Z0-9._-]/', '', $fname);

if (empty($fname)) {
  die("Nome de arquivo inválido.");
}

// 3. VALIDAR extensão ANTES de qualquer operação
$fext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));

if (!array_key_exists($fext, $allowed_ext)) {
  die("Tipo de arquivo não permitido.");
}

// 4. CONSTRUIR caminho completo (SEM busca recursiva)
$file_path = BASE_DIR . DIRECTORY_SEPARATOR . $fname;

// 5. RESOLVER caminho real
$real_path = realpath($file_path);

// 6. VALIDAR que o arquivo está dentro do BASE_DIR
if ($real_path === false || strpos($real_path, BASE_DIR) !== 0) {
  die("Arquivo não encontrado.");
}

// 7. VERIFICAR se é arquivo (não diretório)
if (!is_file($real_path)) {
  die("Arquivo não encontrado.");
}

// 8. VALIDAR MIME type real do arquivo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$real_mime = finfo_file($finfo, $real_path);
finfo_close($finfo);

// Validar contra CSV real
if ($real_mime !== 'text/plain' && $real_mime !== 'text/csv' && $real_mime !== 'application/csv') {
  die("Tipo de arquivo inválido.");
}

// 9. Obter tamanho do arquivo
$fsize = filesize($real_path);

// 10. SANITIZAR nome de download customizado
if (isset($_GET['fc']) && is_string($_GET['fc']) && !empty($_GET['fc'])) {
  $asfname = basename($_GET['fc']);
  $asfname = preg_replace('/[^a-zA-Z0-9._-]/', '', $asfname);

  // Garantir extensão correta
  if (pathinfo($asfname, PATHINFO_EXTENSION) !== $fext) {
    $asfname = pathinfo($asfname, PATHINFO_FILENAME) . '.' . $fext;
  }
} else {
  $asfname = $fname;
}

if (empty($asfname)) {
  $asfname = 'download.' . $fext;
}

// 11. Configurar timeout
set_time_limit(300); // 5 minutos (não 0!)

// 12. Limpar buffer de saída
if (ob_get_level()) {
  ob_end_clean();
}

// 13. Enviar headers
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Type: " . $allowed_ext[$fext]);
header("Content-Disposition: attachment; filename=\"" . addslashes($asfname) . "\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . $fsize);

// 14. Enviar arquivo
$file = fopen($real_path, "rb");
if ($file === false) {
  die("Erro ao abrir arquivo.");
}

while (!feof($file)) {
  echo fread($file, 8192);
  flush();

  if (connection_status() != 0) {
    fclose($file);
    exit;
  }
}

fclose($file);

// 15. Log (se habilitado)
if (LOG_DOWNLOADS) {
  $log_entry = sprintf(
    "[%s] IP: %s | File: %s\n",
    date("Y-m-d H:i:s"),
    $_SERVER['REMOTE_ADDR'] ?: 'unknown',
    $fname
  );

  error_log($log_entry, 3, LOG_FILE);
}

exit;
