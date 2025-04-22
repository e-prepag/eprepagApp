<?php

/**
* Formato do vetor de botoes, com suas definicoes
* 
* A condicao sera avaliada com eval(), logo, deve ser uma expressao
* PHP valida, ou disparara um erro.
* 
* array(
*     array(
*         'acao'     => '{Texto com acao (ex.: pagina.php?acao=editar&codigo={Campo do SQL})}',
*         'coluna'   => '{Coluna onde sera o botao (indice primeira coluna: 0)}',
*         'imagem'   => '{Caminho da imagem}',
*         'width'    => '{largura da imagem}',
*         'height'   => '{altura da imagem}',
*         'alt'      => '{Texto do alt}',
*         'condicao' => '{Condicao para uso do botao (se == true, aparece; else, nao aparece)}'
*     ),
*     array(
*         'acao'     => '{Outra acao}',
*         'coluna'   => '{Outra Coluna (ou a mesma)}',
*         'imagem'   => '{Caminho da imagem}',
*         'width'    => '{largura da imagem}',
*         'height'   => '{altura da imagem}',
*         'alt'      => '{Texto do alt}',
*         'condicao' => '{Condicao para uso do botao (se == true, aparece; else, nao aparece)}'
*     )
* );
* 
* 
* Formato do vetor de permissoes
* 
* array(
*     '{Nome da permissao}' => 'Valor',
*     '{Outra permissao}'   => 'Valor'
* );
*/

class acoesLista
{
    private $colunas;     // numero de colunas necessarias
    private $botoes;      // botoes das acoes
    private $permissoes;  // permissoes (para uso nos testes)
    private $retorno;     // o html que sera retornado
    
    /**
    * Construtor
    * 
    * @param int   $colunas    Numero de colunas necessarias
    * @param array $botoes     Botoes com definicoes necessarias
    * @param array $permissoes Permissoes (para uso nos testes)
    */
    function __construct($colunas, $botoes, $permissoes)
    {
        $this->colunas = $colunas;
        $this->botoes = $botoes;
        $this->permissoes = $permissoes;
    }
    
    /**
    * Retorna botoes, pode ser nenhum, um ou mais
    * Os botoes ja vem nas suas respectivas celulas (formato: <td class="classe">botao</td>)
    * 
    * @param array $camposQuery Campos da query
    */
    public function retornaBotoes($camposQuery)
    {
        $this->retorno = '';
        
        // verifica para cada coluna
        for($col = 0; $col < $this->colunas; $col++)
        {
            $this->retorno .= '<td>';
            
            $tempRetorno = '';
            
            // verifica cada botao
            for($i = 0; $i < count($this->botoes); $i++)
            {
                if($this->botoes[$i]['coluna'] == $col)
                {
                    // captura string condicao
                    $tempCondicao = $this->botoes[$i]['condicao'];

                    // substitui os valores das permissoes
                    foreach($this->permissoes as $tipo => $valor)
                    {
                        $tempCondicao = str_replace('{'.$tipo.'}', $valor, $tempCondicao);
                    }

                    // substitui os valores dos campos
                    foreach($camposQuery as $campo => $resultado)
                    {
                        $tempCondicao = str_replace('{'.$campo.'}', $resultado, $tempCondicao);
                    }

					$tempCondicao = eval($tempCondicao);
                    
                    if($tempCondicao)
                    {
                        $tempAcao  = array_key_exists('acao', $this->botoes[$i]) ? $this->botoes[$i]['acao'] : '';
                        $tempTexto = array_key_exists('texto', $this->botoes[$i]) ? $this->botoes[$i]['texto'] : '';
                        foreach($camposQuery as $campo => $resultado)
                        {
                            $tempAcao  = str_replace('{'.$campo.'}', rawurlencode($resultado), $tempAcao);
                            $tempTexto = str_replace('{'.$campo.'}', $resultado, $tempTexto);
                        }

                        $tempRetorno .= '<span style="vertical-align: middle;"><a href="'.$tempAcao.'" class="pagiLink"><img src="'.$this->botoes[$i]['imagem'].'" width="'.$this->botoes[$i]['width'].'" height="'.$this->botoes[$i]['height'].'" hspace="5" vspace="5" border="0" alt="'.$this->botoes[$i]['alt'].'" />'.$tempTexto.'</a></span>';
                    }
                }
            }

            // se a coluna esta vazia, adiciona um espaco (hack para listagem ficar homogenea)
            if($tempRetorno == '')
            {
                $tempRetorno .= '&nbsp;';
            }

            $this->retorno .= $tempRetorno;

            $this->retorno .= '</td>';
        }

        return $this->retorno;
    }
    
    /**
    * Retorna o numero de colunas necessarias
    */
    public function getColunas()
    {
        return $this->colunas;
    }
}

?>
