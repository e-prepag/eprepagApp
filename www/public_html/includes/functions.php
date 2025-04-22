<?php

if (!function_exists('modal_includes')) {
    function modal_includes($fancybox=true){
        $url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") ? "s" : "") . '://' . $_SERVER['SERVER_NAME'];

        $html = '';

        if($fancybox){
            $html .= '<link rel="stylesheet" href="/js/fancybox/jquery.fancybox.css" type="text/css" />' . PHP_EOL;
            $html .= '<script src="'.$url.'/js/fancybox/jquery.fancybox.js"></script>' . PHP_EOL;    
        }


        $html .= '<link rel="stylesheet" href="'.$url.'/css/modal.css" type="text/css" />' . PHP_EOL;
        $html .= '<script src="'.$url.'/js/modal.js"></script>' . PHP_EOL;

        echo $html;
    }
}

if (!function_exists('fix_name')) {
    function fix_name($str){
        $name = explode(' ', strtolower($str));
        foreach( $name as $k=>$n ){
            if(strlen($n)<=2)
                continue;

           $name[$k] = ucfirst($n);
        }
        return implode(' ', $name);
    }
}
?>