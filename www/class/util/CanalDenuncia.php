<?php
require_once "Validate.class.php";
require_once "Log.class.php";

class CanalDenuncia{
    
    private $protocolo = "";
    private $nome = "";
    private $cpf = "";
    private $email = "";
    private $celular = "";
    private $motivo_denuncia = "";
    private $mensagem_denuncia = "";
    private $denuncia_anonima = "";
    private $ug_id = "";
    private $pdo = "";
    private $errors = array();
    
    public static $ARRAY_MOTIVOS = array(
                                    '1' => 'Relacionamento Interpessoal',
                                    '2' => 'Normas e Políticas',
                                    '3' => 'Má intenção/Ilícitos',
                                    '4' => 'Ética',
                                    '5' => 'Sustentabilidade',
                                    '6' => 'Outros'
    
    );

    public function __construct($array_dados) {
        $this->setProtocolo($array_dados['protocolo']);
        $this->setId($array_dados['ug_id']);
        $this->setNome($array_dados['nome']);
        $this->setCPF($array_dados['cpf']);
        $this->setEmail($array_dados['email']);
        $this->setCelular($array_dados['celular']);
        $this->setMotivoDenuncia($array_dados['motivo_denuncia']);
        $this->setMensagemDenuncia($array_dados['mensagem_denuncia']);
        $this->setDenunciaAnonima($array_dados['denuncia_anonima']);
        
        //Inicializando conexao PDO
        $con = ConnectionPDO::getConnection();
        $this->pdo = $con->getLink();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function getProtocolo() {
        return $this->protocolo;
    }
    
    private function  setProtocolo($dado){
        $this->protocolo = $dado;
    }
    
    private function  setId($dado){
        $this->ug_id = $dado;
    }

    private function setNome($dado) {
        $this->nome = $dado;
    }

    private function setCPF($dado) {
        $this->cpf = $dado;
    }
    
    private function setEmail($dado) {
        $this->email = $dado;
    }

    private function setCelular($dado) {
        $this->celular = $dado;
    }
    
    private function getMotivoDenuncia() {
        return $this->motivo_denuncia;
    }

    private function setMotivoDenuncia($dado) {
        $this->motivo_denuncia = $dado;
    }
    
    private function getMensagemDenuncia() {
        return $this->mensagem_denuncia;
    }

    private function setMensagemDenuncia($dado) {
        $this->mensagem_denuncia = $dado;
    }
    
    private function getDenunciaAnonima() {
        return $this->denuncia_anonima;
    }

    private function setDenunciaAnonima($dado) {
        $this->denuncia_anonima = $dado;
    }
    
    
    public function getErrors() {
        return $this->errors;
    }

    public function setErro($error) {
        array_push($this->errors,$error);
    }
    
    public function validate(){
        
        $valida = new Validate();
        if($valida->qtdCaracteres($this->getProtocolo(), 1, 15))
            $this->setErro("Problema ao gerar protocolo.".PHP_EOL);
        
        if($valida->qtdCaracteres($this->getMotivoDenuncia(), 1, 1))
            $this->setErro("Problema ao recuperar campo Motivo da Denúncia. É um campo obrigatório".PHP_EOL);
        
        if($valida->qtdCaracteres($this->getMensagemDenuncia(), 1, 5000))
            $this->setErro("Problema ao recuperar campo Sua Denúncia. É um campo obrigatório".PHP_EOL);
        
        if($valida->qtdCaracteres($this->getDenunciaAnonima(), 1, 5000))
            $this->setErro("Problema ao recuperar campo Denúncia Anônima. É um campo obrigatório".PHP_EOL);
        
        return $this->getErrors() ? false : true;
    }
    
    public function retorna_motivo_denuncia($id){

        return self::$ARRAY_MOTIVOS[$id];
    }

    public function save(){
        $query = "INSERT INTO canal_de_denuncia (protocolo, nome, cpf, email, celular, motivo_denuncia, mensagem_denuncia, denuncia_anonima, ug_id) VALUES (:protocolo, :nome, :cpf, :email, :celular, :motivo_denuncia, :mensagem_denuncia, :denuncia_anonima, :id);";
        
        if($this->validate()){
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':protocolo', $this->protocolo, PDO::PARAM_STR);
            $stmt->bindParam(':nome', $this->nome, PDO::PARAM_STR);
            $stmt->bindParam(':cpf', $this->cpf, PDO::PARAM_STR);
            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindParam(':celular', $this->celular, PDO::PARAM_STR);
            $stmt->bindParam(':motivo_denuncia', $this->motivo_denuncia, PDO::PARAM_INT);
            $stmt->bindParam(':mensagem_denuncia', $this->mensagem_denuncia, PDO::PARAM_STR);
            $stmt->bindParam(':denuncia_anonima', $this->denuncia_anonima, PDO::PARAM_INT);
            $stmt->bindParam(':id', $this->ug_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $ret = ($stmt->rowCount() == 1) ? TRUE : FALSE;
        }else{
            $geraLog = new Log("CANAL_DENUNCIA",$this->getErrors());
        }
        
        return $ret;

    }
    
}