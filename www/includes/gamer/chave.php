<?php
require_once "/www/includes/load_dotenv.php";

class Chave {
        // chave de cyptografia PINs EPP
        private static $chave256bits		= getenv('CHAVE_256'); // 256-bit key
        
        // chave de cyptografia PINs Publishers
        private static $chave256bitsPub		= getenv('CHAVE_256_PUB'); // 256-bit key
        
        // chave de cyptografia Codigo Promocional
        private static $chave256bitsPromo	= getenv('CHAVE_256_PROMO'); // 256-bit key
        
        /** constructs. */
        public function __construct() {
        }

        /** Retorna Chave PIN EPP.**/
        public function retornaChave() {
                return self::$chave256bits;
        }

        /** Retorna Chave PIN Publishers.**/
        public function retornaChavePub() {
                return self::$chave256bitsPub;
        }

		/** Retorna Chave Codigo Promocional.**/
        public function retornaChavePromo() {
                return self::$chave256bitsPromo;
        }

}
?>	