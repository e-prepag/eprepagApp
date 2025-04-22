<?php
/**
 * Class Authentication
 * 
 * Classe que vai tratar da autenticaчуo das credenciais junto a casa do credito 
 * 
 * @author Wagner de Miranda
 *
*/

//Classe contendo os Authentication 
class Authentication {

        public $client_id;	// string
	public $client_secret;	// string
        public $grant_type;	// string
        public $scope;		// string

        function __construct() {
		$this->client_id	= CLIENT_ID;			
		$this->client_secret	= CLIENT_SECRET;
		$this->grant_type	= GRANT_TYPE;			
		$this->scope		= SCOPE;
                return array($this);
	} //end Construct
        
} //end class Authentication 

?>