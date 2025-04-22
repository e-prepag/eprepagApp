<?php
/*
 * Classe estática para definir qual servidor de 
 * envio de Emails automático do Sistema.
 * 
 * @author Wagner de Miranda
 */

require_once $raiz_do_projeto.'includes/configEmail.php';

class EmailEnvironment {
    
    private static $serverList = array(
                                        '1' => 'E-mail Server .COM',
                                        '2' => 'E-mail Server .COM.BR',
                                        );
    
    public static function serverId() {
        
        if(defined('EMAIL_SERVER')) {
            return EMAIL_SERVER;
        }//end if
        
        else {
            return 2;
        }//end else
        
    }//end function serverId
    
    public static function serverList() {
        
            return self::$serverList;
        
    }//end function serverList
    
} // end class
?>