<?php
class SFTPConnection
{
    private $connection;
    private $sftp;

    public function __construct($host, $port=22)
    {
        $this->connection = @ssh2_connect($host, $port);
        if (! $this->connection)
            throw new Exception("Não conseguiu conectar no servidor $host na porta $port.".PHP_EOL);
    }

    public function login($username, $password)
    {
        if (! @ssh2_auth_password($this->connection, $username, $password))
            throw new Exception("Não conseguiu autenticar com username $username e senha $password.".PHP_EOL);

        $this->sftp = @ssh2_sftp($this->connection);
        if (! $this->sftp)
            throw new Exception("Não conseguiu inicializar SFTP.".PHP_EOL);
    }

    public function uploadFile($local_file, $remote_file)
    {
        $stream = @fopen("ssh2.sftp://".intval($this->sftp)."/".$remote_file, 'w');
                
        if (! $stream)
            throw new Exception("Não conseguiu abrir o arquivo DESTINO (remoto) para escrita: $remote_file");

        $data_to_send = @file_get_contents($local_file);
        if ($data_to_send === false)
            throw new Exception("Não pode abrir o arquivo de ORIGEM (local): $local_file.");

        if (@fwrite($stream, $data_to_send) === false)
            throw new Exception("Não pode enviar os dados para o arquivo DESTINO (remoto): $remote_file.");
        else echo "Arquivo trasnferido com sucesso. Tamanho (".filesize("ssh2.sftp://".intval($this->sftp)."/".$remote_file).") bytes.".PHP_EOL.PHP_EOL;

        @fclose($stream);
    }
    
    public function scanFilesystem($remote_file) {
        $dir = "ssh2.sftp://".intval($this->sftp)."/".$remote_file;
        $tempArray = array();
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    $filetype = filetype($dir . $file);
                    if($filetype == "dir") {
                        $tmp = $this->scanFilesystem($remote_file . $file . "/");
                        foreach($tmp as $t) {
                            $tempArray[] = $file . "/" . $t;
                        }
                    } else {
                        $tempArray[] = $file;
                    }
                }
                closedir($dh);
            }
        }
        else echo "Não é um diretório".PHP_EOL;

        return $tempArray;
    }
}
?>