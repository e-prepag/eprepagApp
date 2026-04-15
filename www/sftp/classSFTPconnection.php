<?php
class SFTPConnection
{
    private $connection;
    private $sftp;

    public function __construct($host, $port = 22)
    {
        // [SFTP DESATIVADO] Construtor neutralizado.
        $this->connection = null;
        $this->sftp = null;
    }

    public function login($username, $password)
    {
        // [SFTP DESATIVADO] Login desativado.
        // if (! @ssh2_auth_password($this->connection, $username, $password)) { ... }
        // $this->sftp = @ssh2_sftp($this->connection);
        return false;
    }

    public function uploadFile($local_file, $remote_file)
    {
        // [SFTP DESATIVADO] Upload desativado.
        // $stream = @fopen("ssh2.sftp://...", 'w');
        // $data_to_send = @file_get_contents($local_file);
        // @fwrite($stream, $data_to_send);
        // @fclose($stream);
        return false;
    }

    public function scanFilesystem($remote_file)
    {
        // [SFTP DESATIVADO] Varredura remota desativada.
        return array();
    }
}
?>
