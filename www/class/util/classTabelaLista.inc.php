<?php

/**
* Formato do vetor de Campos de retorno do sql => Headers da tabela gerada
* Caso nao seja suficiente a explicacao rapida abaixo
* 
* array(
*     '{Campo no Banco}' => '{Texto do header da tabela}',
*     '{Outro Campo}' => '{Outro Header}'
* );
*/

class tabelaLista
{
    private $tipo;       // tipo de listagem (externo/colaborador)
    private $inicio;     // inicio para delimitar retornos
    private $limite;     // numero de retornos esperado
    private $sql;        // sql que será usada na seleção
    private $msg;        // utilizado para notificacoes (erros e etc)
    private $sqlFinal;   // query que sera realmente utilizado no DB
    private $lista;      // guarda a lista pronta (tabela)
    private $barra;      // barra de navegacao de paginacao
    private $action;     // envio do form
    private $sort;       // ordernacao
    private $dir;        // direcao da ordenacao (asc ou desc)
    private $botoes;     // objeto dos botoes de acoes
    private $estilos;    // estilos para mudar cor de linhas
    private $formFields; // campos de formulario externo
	private $paginacao;  // dá o caminho relativo daonde tu está :D
    
    
    /**
    * Campos de retorno do sql => Headers da tabela gerada
    * @public string array
    */
    public $camposTabela;
    
    /**
    * Campos utilizados no objeto e passados para comparacao nos botoes
    * @public string array
    */
    public $camposTabelaCondicao;
    
    /**
    * Construtor
    * 
    * @param db     $sql    Sql para o DB
    * @param string $action Action do formulario
    * @param int    $inicio Inicio dos retornos
    * @param int    $limite Numero de retornos esperado
    * @param obj    $botoes Objeto dos botoes de acoes
    */
    function __construct($sql, $action, $paginacao = null, $inicio = 0, $limite = 25, $sort = '', $dir = 'ASC', $botoes = '', $estilos = '', $formFields = null)
    {
        $this->sql        = $sql;
        $this->action     = $action;
        $this->inicio     = $inicio;
        $this->limite     = $limite;
        $this->sort       = $sort;
        $this->dir        = $dir;
        $this->botoes     = $botoes;
        $this->estilos    = $estilos;
        $this->formFields = $formFields;
        if ( is_array( $paginacao ) ) {
            $this->paginacao  = $paginacao;
        } else {
            $this->paginacao = array( 
									'primeiro' 	=> '../images/resultset_first.png',
									'anterior' 	=> '../images/resultset_previous.png',
									'proximo' 	=> '../images/resultset_next.png',
									'ultimo'	=> '../images/resultset_last.png'
									);
        }
    }
    
    /**
    * Gera lista
    */
    function geraLista()
    {
        if($this->sql == '')
        {
            $this->msg = 'Não há query!';
            return false;
        }
        
        if(count($this->camposTabela) == 0)
        {
            $this->msg = 'Não há campos para retorno ou headers para lista!';
            return false;
        }
        
        $this->geraSql();
        
//echo $this->sqlFinal."<br>";
		// executa o query
		$rs_query = SQLexecuteQuery($this->sqlFinal);
		
        // formulario para controle paginacao/ordenacao
        $this->lista = '<form name="paginar" id="paginar" method="post" action="'.$this->action.'">'.PHP_EOL;
        $this->lista .= '<input type="hidden" name="inicio" value="" />'.PHP_EOL;
        $this->lista .= '<input type="hidden" name="limite" value="" />'.PHP_EOL;
        $this->lista .= '<input type="hidden" name="sort" value="'.$this->sort.'" />'.PHP_EOL;
        $this->lista .= '<input type="hidden" name="dir" value="'.$this->dir.'" />'.PHP_EOL;
        
        if(count($this->formFields) > 0)
        {
            foreach($this->formFields as $name => $value)
            {
                $this->lista .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />'.PHP_EOL;
            }
        }
        
        // inicia a tabela de retorno
        $this->lista .= '<table width="570" border="0" cellspacing="1" cellpadding="1" class="lista-table">'.PHP_EOL;
        
        // header
        $this->lista .= '<tr class="titulo-table">'.PHP_EOL;
        foreach($this->camposTabela as $campo => $header)
        {
            if($this->sort == $campo)
            {
                $this->lista .= '<td><a href="#" onclick="pagina(0,'.$this->limite.',\''.$campo.'\',\''.$this->sortDir().'\')" class="pagiLink">'.$header.'</a></td>'.PHP_EOL;
            }
            else
            {
                $this->lista .= '<td><a href="#" onclick="pagina(0,'.$this->limite.',\''.$campo.'\',\''.$this->sortDir().'\')" class="pagiLink">'.$header.'</a></td>'.PHP_EOL;
            }
        }
        
        if($this->botoes != '')
        {
            $tempColunas = $this->botoes->getColunas();
            for($i = 0; $i < $tempColunas; $i++)
            {
                $this->lista .= '<td>&nbsp;</td>'.PHP_EOL;
            }
        }
        
        $this->lista .= '</tr>'.PHP_EOL;

        // corpo
        if($rs_query)
        {
			while($rs_query_row = pg_fetch_array($rs_query)) 
			{
                $this->lista .= '<tr onmouseover="linhaSelecionada(this,\'#F8F8F8\')" onmouseout="linhaNormal(this)">';
                
                foreach($this->camposTabela  as $campo => $header)
                {
                    $tempCampo = nl2br($rs_query_row[$campo]);
                    if($tempCampo == '')
                    {
                        $tempCampo = '&nbsp;';
                    }
                    
                    if($this->sort == $campo)
                    {
                        $this->lista .= '<td>'.$tempCampo.'</td>';
                    }
                    else
                    {
                        if($this->estilos != '')
                        {
                            $tempEstilo = $this->estilos->retornaEstilo($rs_query);
                            $this->lista .= '<td class="'.$tempEstilo.'">'.$tempCampo.'</td>';
                        }
                        else
                        {
                            $this->lista .= '<td class="lstDado">'.$tempCampo.'</td>';
                        }
                    }
                }
                
                if($this->botoes != '')
                {
                    $this->lista .= $this->botoes->retornaBotoes($rs_query_row);
                }
                
                $this->lista .= '</tr>'.PHP_EOL;
            }
        }
        
        $this->lista .= '</table>'.PHP_EOL;
        
        $this->fazBarra();
        
        $this->lista .= $this->barra;
        
        $this->lista .= '</form>'.PHP_EOL;
        
        return true;
    }
    
    /**
    * Troca direcao de ordenacao
    */
    private function sortDir()
    {
        if($this->dir == 'ASC')
        {
            return 'DESC';
        }
        else
        {
            return 'ASC';
        }
    }
    
    /**
    * Faz barra de paginacao
    */
    private function fazBarra()
    {
		// busca o total de entradas no banco retornadas pelo sql
        $sql = 'SELECT COUNT(*) AS TOTAL FROM ('.$this->sql.') AS tempQuery';
		$rs_query = SQLexecuteQuery($sql);
		$rs_query_row = pg_fetch_array($rs_query);
		$tempTotal = $rs_query_row['total'];
        // calcula o numero de paginas
        // floor retorna o numero inteiro de uma divisao, arrendondado para baixo
        $tempPaginas = floor($tempTotal / $this->limite);
        // se a divisao tem resto, adiciona uma pagina a mais (das entradas que sobraram)
        if(($tempTotal % $this->limite) > 0)
        {
            $tempPaginas++;
        }
        
        //se o numero de paginas eh 0 (nao ha dados), retorna um (para ficar visualmente melhor)
        if($tempPaginas == 0)
        {
            $tempPaginas = 1;
        }
        
        // calcula a pagina atual de acordo com os limites
        $tempPaginaAtual = 1;
        for($i = $this->inicio; $i >= $this->limite; $i -= $this->limite)
        {
            $tempPaginaAtual++;
        }

        // inicia a tabela da barra
        $this->barra = '<table width="570" border="0" cellspacing="0" cellpadding="0">';
        
        $this->barra .= '<tr class="rodape-table">';
        
        // limites da pagina anterior
        if($tempPaginaAtual > 1)
        {
            $anterior = (($tempPaginaAtual - 2) * $this->limite);
        }
        else
        {
            $anterior = (($tempPaginaAtual - 1) * $this->limite);
        }
        
        // limites da proxima pagina
        if($tempPaginaAtual >= $tempPaginas)
        {
            $proxima = (($tempPaginaAtual - 1) * $this->limite);
        }
        else
        {
            $proxima = (($tempPaginaAtual) * $this->limite);
        }
        
        // ultima pagina
        $ultima = (($tempPaginas - 1) * $this->limite);
        
        // links de paginacao
        $this->barra .= '<td class="lstHeader" style="text-align: center;"><a href="#" onclick="pagina(0,'.$this->limite.',\''.$this->sort.'\',\''.$this->dir.'\')" class="pagiLink"><img src="' . $this->paginacao['primeiro']  . '" alt="Primeira" border="0"></a></td>';
        $this->barra .= '<td class="lstHeader" style="text-align: center;"><a href="#" onclick="pagina('.$anterior.','.$this->limite.',\''.$this->sort.'\',\''.$this->dir.'\')" class="pagiLink"><img src="' . $this->paginacao['anterior']  . '" alt="Anterior" border="0"></a</td>';
        $this->barra .= '<td class="lstHeader" style="text-align: center;">P&aacute;gina '.$tempPaginaAtual.' de  '.$tempPaginas.' - Total de itens: '.$tempTotal.'</td>';
        $this->barra .= '<td class="lstHeader" style="text-align: center;"><a href="#" onclick="pagina('.$proxima.','.$this->limite.',\''.$this->sort.'\',\''.$this->dir.'\')" class="pagiLink"><img src="' . $this->paginacao['proximo']  . '" alt="Próxima" border="0"></a</td>';
        $this->barra .= '<td class="lstHeader" style="text-align: center;"><a href="#" onclick="pagina('.$ultima.','.$this->limite.',\''.$this->sort.'\',\''.$this->dir.'\')" class="pagiLink"><img src="' . $this->paginacao['ultimo']  . '" alt="Última" border="0"></a</td>';
        
        $this->barra .= '</tr>';
        
        $this->barra .= '</table>';
    }
    
    /**
    * Imprime lista
    */
    function imprimeLista()
    {
        if($this->lista == '')
        {
            $this->msg = 'Lista não gerada!';
            return false;
        }
        
        echo $this->lista;
    }
    
    /**
     * Retorna lista
     */
    function getLista()
    {
        if($this->lista == '')
        {
            echo $this->msg = 'Lista não gerada!';
            return false;
        }
        
        return $this->lista;
    }
    
    /**
    * Converte para UTF-8 
    */
    function listaUTF8()
    {
        $this->lista = utf8_encode($this->lista);
    }
    
    /**
    * Gera sql para paginacao
    */
    private function geraSql()
    {
        // o sql mais interno eh o original
        // o segundo, o que cria a coluna id com os numeros em sequencia
        // o externo eh o que seleciona a parte necessaria de acordo com a paginacao
        $this->sqlFinal = 'SELECT * FROM ('.$this->sql.' ORDER BY '.$this->sort.' '.$this->dir.') AS tempQuery LIMIT '.$this->limite.' OFFSET '.$this->inicio.'';
	}
    
    /**
    * Imprime mensagem (erros e etc)
    */
    function getMsg()
    {
        echo $this->msg;
    }
}

?>