<?php
/**
 * Description of Validacoes
 *
 * @author e-prepag
 */
class Validate {
    //put your code here
    public function qtdCaracteres($str,$min,$max){
        return (strlen($str) >= $min && strlen($str) <= $max) ? 0 : 1;
    }
    
    public function letras($str){
        return (preg_match("/([a-zA-Z])/", $str)) ? 0 : 1;
    }
    
    public function numeros($str){
        return (preg_match("/([0-9])/", $str)) ? 0 : 1;
    }
    
    public function caracteresEspeciais($str){
        return (preg_match('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $str)) ? 0 : 1;
    }
    
    public function email($str){
        $filter= "/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i";
        return preg_match($filter,$str) ? 0 : 1;
    }
    
    public function imagem($file){
        $erros = array();
        $formatos = array('jpg','jpeg','gif','png');
        $ext = explode('/',$file['type']);
        $ext = array_reverse($ext);
                
        if(empty($file["name"]))
            $erros[] = "A imagem precisa ter um nome.";
        
        if(!in_array($ext[0],$formatos))
            $erros[] = "Formato de imagem inválido.";
        
        return
           (!empty($erros)) ? $erros : false;
    }
}
