<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'HeaderController.class.php';
require_once DIR_CLASS."util/Validate.class.php";

class PagamentoOfflineController extends HeaderController{
    public function __construct(){
        parent::__construct();
        if($this->logado){
            Util::redirect("/game/pedido/passo-2.php");
        }
    }
}