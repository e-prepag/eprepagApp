<?php
/****************************************************************
 **     __/      ____/   ____/    __/    ____/   _____/    ___/**
 **    /        /   /   /   /    /      /   /   /    /    /    **
 **   _/  _/   ____/   ____/    _/     ____/   ___  /    /     **
 **  /__      /       /  \    /__     /       /    /    /_/ /  **
 **____/    _/      _/   _\  ____/  _/      _/   _/   _____/   **
 ****************************************************************
 ******       Autor: Wagner de Miranda   2016-03-03
 ****************************************************************
 */

class Session {
    
        public static function unserialize($session_data) {
                $method = ini_get("session.serialize_handler");
                switch ($method) {
                    case "php":
                        return self::unserialize_php($session_data);
                        break;
                    case "php_binary":
                        return self::unserialize_phpbinary($session_data);
                        break;
                    default:
                        throw new Exception("Unsupported session.serialize_handler: " . $method . ". Supported: php, php_binary");
                }
        }//end function unserialize

        private static function unserialize_php($session_data) {
                $return_data = array();
                $offset = 0;
                while ($offset < strlen($session_data)) {
                    if (!strstr(substr($session_data, $offset), "|")) {
                        throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
                    }
                    $pos = strpos($session_data, "|", $offset);
                    $num = $pos - $offset;
                    $varname = substr($session_data, $offset, $num);
                    $offset += $num + 1;
                    $data = unserialize(substr($session_data, $offset));
                    $return_data[$varname] = $data;
                    $offset += strlen(serialize($data));
                }
                return $return_data;
        } //end function unserialize_php

        private static function unserialize_phpbinary($session_data) {
                $return_data = array();
                $offset = 0;
                while ($offset < strlen($session_data)) {
                    $num = ord($session_data[$offset]);
                    $offset += 1;
                    $varname = substr($session_data, $offset, $num);
                    $offset += $num;
                    $data = unserialize(substr($session_data, $offset));
                    $return_data[$varname] = $data;
                    $offset += strlen(serialize($data));
                }
                return $return_data;
        } //end 
        
} //end class function unserialize_phpbinary
?>
