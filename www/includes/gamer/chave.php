<?php
require_once __DIR__ . "/../load_dotenv.php";

class Chave {
        // chave de cyptografia PINs EPP
        private static string $chave256bits		= "papibaquigrafo_EPREPAG_2011_PLUS"; // 256-bit key
        
        // chave de cyptografia PINs Publishers
        private static string $chave256bitsPub		= "publishers_key_EPREPAG_2011_PLUS"; // 256-bit key
        
        // chave de cyptografia Codigo Promocional
        private static string $chave256bitsPromo	= "promocoes_usuarios_key_2011_PLUS"; // 256-bit key
        
        /** constructs. */
        public function __construct() {
        }

        /** Retorna Chave PIN EPP.**/
        public function retornaChave(): string {
                return self::$chave256bits;
        }

        /** Retorna Chave PIN Publishers.**/
        public function retornaChavePub(): string {
                return self::$chave256bitsPub;
        }

		/** Retorna Chave Codigo Promocional.**/
        public function retornaChavePromo(): string {
                return self::$chave256bitsPromo;
        }

}
?>