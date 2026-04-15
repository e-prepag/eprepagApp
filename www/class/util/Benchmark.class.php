<?php
require_once "Validate.class.php";
require_once "Log.class.php";

class Benchmark{
    
    private $funcao = "";
    private $sistema = "";
    private $tempo = "";
    private $data = "";
    private $pdo = "";
    private $erro_sistema = "";
    private $errors = array();
    
    public function __construct($funcao = "", $sistema = "", $tempo = "", $erro_sistema = "", $data = "") {
        $this->setFuncao($funcao);
        $this->setSistema($sistema);
        $this->setTempo($tempo);
        $this->setErroSistema($erro_sistema);
        //Inicializando conexao PDO
        $con = ConnectionPDO::getConnection();
        $this->pdo = $con->getLink();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function setFuncao($funcao) {
        $this->funcao = $funcao;
    }

    private function setSistema($sistema) {
        $this->sistema = $sistema;
    }

    private function setTempo($tempo) {
        $this->tempo = $tempo;
    }
    
    public function setErroSistema($erro) {
        $this->erro_sistema = $erro;
    }

    public function getErrors() {
        return $this->errors;
    }
    
    public function getErroSistema() {
        return $this->erro_sistema;
    }

    public function setErro($error) {
        array_push($this->errors,$error);
    }
    
    public function validate(){
        
        $valida = new Validate();
        if($valida->qtdCaracteres($this->funcao, 1, 256))
            $this->setErro("Função é um campo obrigatório.");
        
        if($valida->qtdCaracteres($this->sistema, 1, 256))
            $this->setErro("Sistema é um campo obrigatório.");
        
        if($valida->qtdCaracteres($this->tempo, 1, 256))
            $this->setErro("Tempo é um campo obrigatório.");
        
        return $this->getErrors() ? false : true;
    }

    public function save(){
        $query = "insert into benchmark (funcao, sistema, tempo, erro_sistema) values (:funcao, :sistema, :tempo, :erro)";
        
        if($this->validate()){
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':funcao', $this->funcao, PDO::PARAM_STR);
            $stmt->bindParam(':sistema', $this->sistema, PDO::PARAM_STR);
            $stmt->bindParam(':tempo', $this->tempo, PDO::PARAM_STR);
            $stmt->bindParam(':erro', $this->erro_sistema, PDO::PARAM_STR);
            $stmt->execute();
            
            $ret = ($stmt->rowCount() == 1) ? RETURN_SUCCESS : RETURN_WRONG;
        }else{
            $geraLog = new Log("BENCHMARK",$this->getErrors());
        }
        
        return $ret;

    }
    
}