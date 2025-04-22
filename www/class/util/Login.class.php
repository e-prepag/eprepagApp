<?php
require_once $raiz_do_projeto."class/util/Log.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";

class Login {
    
    private $_senha = "";
    private $_minCarac = 6; //minimo de caracteres permitidos em uma senha
    private $_maxCarac = 12; //maximo de caracteres permitidos em uma senha
    private $_maxTentativas = 5; //maximo de tentativas permitido para bloquear o acesso
    private $_urlRedirect = ""; //url para redirecionar
    private $_tempoDesbloqueio = 60; //tempo para desbloquear
    private $_msgErro = ""; //mensagem de erro
    
    
    //put your code here
    public function __construct($senha = ""){
        $this->_senha = $senha;
    }
    
    public function getMsgErro() {
        return $this->_msgErro;
    }

    public function setMsgErro($msgErro) {
        $this->_msgErro = $msgErro;
        return $this;
    }
    
    public function getTempoDesbloqueio() {
        return $this->_tempoDesbloqueio;
    }

    /*
        Metodo que seta o tempo limite de desbloqueio quando o usuario excede o máximo de tentativas de login
        @var $sec: tempo passado em segundos a ser calculado
    */
    public function setTempoDesbloqueio($sec) {
        $this->_tempoDesbloqueio = $sec;
        return $this;
    }

        
    public function getUrlRedirect() {
        return $this->_urlRedirect;
    }

    public function setUrlRedirect($urlRedirect) {
        $this->_urlRedirect = $urlRedirect;
        return $this;
    }
    
    public function setLimiteCaracteres($min,$max){
        $this->_minCarac = $min;
        $this->_maxCarac = $max;
    }
    
    public function setMaxTentativas($tentativas){
        $this->_maxTentativas = $tentativas;
    }
    
    public function getMaxTentativas(){
        return $this->_maxTentativas;
    }
        
    public function valida(){
        
        $erros = 0;
        
        $validate = new Validate;
        $erros += $validate->qtdCaracteres($this->_senha,$this->_minCarac,$this->_maxCarac); //string,qtd minima, qtd maxima
        $erros += $validate->caracteresEspeciais($this->_senha);
        $erros += $validate->letras($this->_senha);
        $erros += $validate->numeros($this->_senha);

        return $erros;
            
        
    }
    
    public function encripta(){
        
    }
    
    public function decripta(){
        
    }
    
    public function autentica(){
        if(isset($_SESSION['locked'])){
            $time = date("Y-m-d h:i:s");
            $diff = Util::timeSub($_SESSION['locked'],$time);

            if($diff < 0){
                $this->desbloqueia();
            }else{
                $erro = $this->getMsgErro();
                $redirect = ($erro != "") ?
                                        $this->getUrlRedirect(). sprintf($this->getMsgErro(),Util::secToTime($diff)) : $this->getUrlRedirect();
                
                Util::redirect($redirect);
            }
        }
    }
    
    public function falhaAutenticacao(){
        if(isset($_SESSION['erro']))
            $_SESSION['erro']++;
        else
            $_SESSION['erro'] = 1;
        
        if($_SESSION['erro'] >= $this->getMaxTentativas())
            $this->bloqueia();
    }
    
    public function desbloqueia(){
        unset($_SESSION['locked']);
        unset($_SESSION['erro']);
    }
    
    public function bloqueia(){
        $time = Util::sumSeconds(date("Y-m-d h:i:s"),$this->getTempoDesbloqueio());
        $_SESSION['locked'] = $time;
        
        $erro = $this->getMsgErro();
        
        $redirect = ($erro != "") ?
                                $this->getUrlRedirect(). sprintf($this->getMsgErro(),Util::secToTime($this->getTempoDesbloqueio())) : $this->getUrlRedirect();
        Util::redirect($redirect);
    }
}
?>