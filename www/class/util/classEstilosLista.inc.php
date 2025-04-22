<?php

/**
* Formato do vetor de estilos, com suas definicoes
* 
* A condicao sera avaliada com eval(), logo, deve ser uma expressao
* PHP valida, ou disparara um erro.
* 
* array(
*     array(
*         'estilo'   => '{Estilo a ser utilizado}',
*         'condicao' => '{Condicao para uso do estilo (se == true, aparece; else, nao aparece)}'
*     ),
*     array(
*         'estilo'   => '{Estilo a ser utilizado}',
*         'condicao' => '{Condicao para uso do estilo (se == true, aparece; else, nao aparece)}'
*     )
* );
*/

class estilosLista
{
    private $estilos; // estilos
    private $retorno; // o html que sera retornado
    
    /**
    * Construtor
    * 
    * @param array $estilos Estilos com definicoes necessarias
    */
    function __construct($estilos)
    {
        $this->estilos = $estilos;
    }
    
    /**
    * Retorna estilo
    * 
    * @param array $camposQuery Campos da query
    */
    public function retornaEstilo($camposQuery)
    {
        $this->retorno = '';
        
        // verifica cada estilo
        for($i = 0; $i < count($this->estilos); $i++)
        {
            // captura string condicao
            $tempCondicao = $this->estilos[$i]['condicao'];
            
            // substitui os valores dos campos
            foreach($camposQuery as $campo => $resultado)
            {
                $tempCondicao = str_replace('{'.$campo.'}', $resultado, $tempCondicao);
            }
            
            // avalia a condicao
            $tempCondicao = eval($tempCondicao);
            
            if($tempCondicao)
            {
                $this->retorno = $this->estilos[$i]['estilo'];
            }
        }
        
        return $this->retorno;
    }
}

?>