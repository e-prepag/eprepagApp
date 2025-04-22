<?php
// include do arquivo contendo IPs DEV
require_once DIR_INCS . 'configIP.php';

class Helper{
    
    public static function detect_browser(){

        $ua = $GLOBALS['_SERVER']['HTTP_USER_AGENT'];

        if( preg_match("/Firefox/", $ua) )
                return "firefox";

        if( preg_match("/Chrome/", $ua) || preg_match("/Chromium/", $ua) )
                return "chrome";
        
        if( preg_match("/MSIE/", $ua) || preg_match("/internetexplorer/", $ua) )
                return "internetexplorer";

        return "unknown";
    }
    
    public static function get_cupom_small( $data ){
        $server_url = "www.e-prepag.com.br";
        if(checkIP()) {
            $server_url = $GLOBALS['_SERVER']['SERVER_NAME'];
        }
        $data['logo_epp'] = "/prepag2/images/logo_epp_corelpb.gif";
        
        if(self::detect_browser()=="internetexplorer")
            $template = file_get_contents("http".(($GLOBALS['_SERVER']['HTTPS']=="on")?"s":"") ."://".$server_url."/prepag2/templates/template_cupom_small_ie.html");
        else
            $template = file_get_contents("http".(($GLOBALS['_SERVER']['HTTPS']=="on")?"s":"") ."://".$server_url."/prepag2/templates/template_cupom_small.html");
        
        $html = self::transform($data, $template);
        
        return $html;
    }

    public static function transform($data, $str){
        foreach( $data as $k=>$v ){
            $str = str_replace("{{".$k."}}", $v, $str);
        }
        return $str;
    }
} //end class Helper
?>