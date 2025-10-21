<?php
require_once '/www/db/connect.php';
require_once '/www/db/ConnectionPDO.php';

class GerarEFinanceira
{

    public function __construct() {}

    private function obterDadosMovFin()
    {
        $sql = "SELECT DISTINCT ON (ug.ug_id) 
                ug.ug_id,
                ug.*,
                sl.* -- Pega todas as colunas do log da transação
            FROM 
                dist_usuarios_games ug
            JOIN 
                dist_usuarios_games_saldo_log sl ON ug.ug_id = sl.dugsl_ug_id
            WHERE 
                ug.ug_status = 1 
                AND sl.dugsl_data_inclusao::date <= '2025-09-30'
            ORDER BY 
                ug.ug_id,                -- 1. Tem que bater com o DISTINCT ON
                sl.dugsl_data_inclusao DESC; -- 2. Define qual linha pegar (a mais recente)
                
            -- CTE (tabela temporária) que calcula a movimentação de TODOS os usuários
            WITH MovimentacaoMensal AS (
                SELECT 
                    ug.ug_id,
                    
                    SUM(CASE 
                    WHEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) > 0 
                    THEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) 
                    ELSE 0 
                END) AS entradas,
                
                -- Soma a diferença APENAS SE for negativa (menor que 0)
                -- e usa ABS() para tornar o resultado positivo
                ABS(SUM(CASE 
                    WHEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) < 0 
                    THEN (sl.dugsl_ug_perfil_saldo - sl.dugsl_ug_perfil_saldo_antes) 
                    ELSE 0 
                END)) AS saidas
                    
                FROM 
                    dist_usuarios_games ug
                JOIN 
                    dist_usuarios_games_saldo_log sl ON ug.ug_id = sl.dugsl_ug_id
                WHERE 
                    ug.ug_ativo = 1 
                    AND sl.dugsl_data_inclusao::date BETWEEN '2025-09-01' AND '2025-09-30'
                GROUP BY 
                    ug.ug_id
            ),
            DadosUsuario as (
            	SELECT DISTINCT ON (ug.ug_id) 
            	    ug.ug_id,
                	ug.ug_nome_fantasia,
            	 	ug.ug_razao_social,
            	 	ug.ug_cnpj,
            	 	ug.ug_endereco,
            	 	ug.ug_numero,
            	 	ug.ug_complemento,
            	 	ug.ug_bairro,
            	 	ug.ug_cidade,
            	 	ug.ug_estado,
            	 	ug.ug_cep,
            	 	ug.ug_repr_legal_nome,
            	 	ug.ug_repr_legal_cpf,
            	 	ug.ug_repr_legal_data_nascimento,
                	sl.dugsl_ug_perfil_saldo
            	FROM 
                	dist_usuarios_games ug
            	JOIN 
                	dist_usuarios_games_saldo_log sl ON ug.ug_id = sl.dugsl_ug_id
            	WHERE 
                	ug.ug_id IN (SELECT m.ug_id FROM MovimentacaoMensal m)
                	AND sl.dugsl_data_inclusao::date <= '2025-09-30'
            	ORDER BY 
                	ug.ug_id,
                	sl.dugsl_data_inclusao DESC
            ),
            DadosUsuario as (
            	SELECT DISTINCT ON (ug.ug_id) 
            	    ug.ug_id,
                	ug.ug_nome_fantasia,
            	 	ug.ug_razao_social,
            	 	ug.ug_cnpj,
            	 	ug.ug_endereco,
            	 	ug.ug_numero,
            	 	ug.ug_complemento,
            	 	ug.ug_bairro,
            	 	ug.ug_cidade,
            	 	ug.ug_estado,
            	 	ug.ug_cep,
            	 	ug.ug_repr_legal_nome,
            	 	ug.ug_repr_legal_cpf,
            	 	ug.ug_repr_legal_data_nascimento,
                	sl.dugsl_ug_perfil_saldo
            	FROM 
                	dist_usuarios_games ug
            	JOIN 
                	dist_usuarios_games_saldo_log sl ON ug.ug_id = sl.dugsl_ug_id
            	WHERE 
                	ug.ug_id IN (SELECT m.ug_id FROM MovimentacaoMensal m)
                	AND sl.dugsl_data_inclusao::date <= '2025-09-30'
            	ORDER BY 
                	ug.ug_id,
                	sl.dugsl_data_inclusao DESC
            )
            SELECT 
                m.*,
                (m.entradas + m.saidas) AS total_movimentado,
                d.*
            FROM 
                MovimentacaoMensal m
            join 
            	DadosUsuario d ON d.ug_id = m.ug_id
            WHERE 
                (entradas + saidas) > 2000;";
    }

    private function obterUltimoIdEnvio()
    {
        $pdo = ConnectionPDO::getConnection()->getLink();

        $stmt = $pdo->prepare("SELECT MAX(id) AS ultimo_id FROM envios_e_financeira;");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['ultimo_id'] ?: 0;
    }

    private function gerarIdFormatado($numero)
    {
        // Define o prefixo
        $prefixo = 'ID';

        // Define o comprimento total da parte numérica
        $comprimentoNumerico = 18;

        // Usa str_pad para preencher o número com '0' à esquerda (STR_PAD_LEFT)
        // até que ele atinja o comprimento de 18 caracteres.
        $parteNumerica = str_pad($numero, $comprimentoNumerico, '0', STR_PAD_LEFT);

        // Concatena o prefixo com a parte numérica
        return $prefixo . $parteNumerica;
    }

    private function apenasNumeros($documento)
    {
        // A expressão regular /\D/ significa "qualquer caractere que NÃO seja um dígito".
        // A função preg_replace substitui todos eles por uma string vazia ('').
        return preg_replace('/\D/', '', $documento);
    }

    public function gerarMovimentacaoFinanceira($tipoNI, $cpfCnpj, $nomeDeclarado, $dataNascimento = '', $enderecoCliente, $ano, $mes, $ug_id, $entradas, $saidas)
    {
        // Criar o objeto DOMDocument
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true; // Deixa o XML formatado (quebras de linha e identação)

        // Criar o elemento raiz com namespace
        $eFinanceira = $dom->createElementNS(
            'http://www.eFinanceira.gov.br/schemas/evtMovOpFin/v1_2_0', // namespace
            'eFinanceira' // nome do elemento
        );
        $dom->appendChild($eFinanceira);

        // Criar o elemento evtMovOpFin com atributo id
        $idNovo = $this->obterUltimoIdEnvio() + 1;

        $id_formatado = $this->gerarIdFormatado($idNovo);

        $evtMovOpFin = $dom->createElement('evtMovOpFin');
        $evtMovOpFin->setAttribute('id', $id_formatado);
        $eFinanceira->appendChild($evtMovOpFin);

        // ideEvento - grupo
        $ideEvento = $dom->createElement('ideEvento');
        $evtMovOpFin->appendChild($ideEvento);

        // indRetificacao - 1 para original 2 para retificação 3 para retificação a pedido
        $indRetificacao = $dom->createElement('indRetificacao', '1');
        $ideEvento->appendChild($indRetificacao);

        // tpAmb
        $tpAmb = $dom->createElement('tpAmb', '1');
        $ideEvento->appendChild($tpAmb);

        // aplicEmi
        $aplicEmi = $dom->createElement('aplicEmi', '1');
        $ideEvento->appendChild($aplicEmi);

        // verAplic
        $verAplic = $dom->createElement('verAplic', '00000000000000000001');
        $ideEvento->appendChild($verAplic);

        // ideDeclarante - grupo
        $ideDeclarante = $dom->createElement('ideDeclarante');
        $evtMovOpFin->appendChild($ideDeclarante);

        $cnpjDeclarante = $dom->createElement('CNPJ', '12345678000190');
        $ideDeclarante->appendChild($cnpjDeclarante);

        // ideDeclarado -  grupo
        $ideDeclarado = $dom->createElement('ideDeclarado');
        $evtMovOpFin->appendChild($ideDeclarado);

        // tipo NI 1-cpf 2-cnpj
        $tpNI = $dom->createElement('tpNI', $tipoNI);
        $ideDeclarado->appendChild($tpNI);

        // NIDeclarado cpf ou cnpj
        $cpfCnpjNum = $this->apenasNumeros($cpfCnpj);
        $NIDeclarado = $dom->createElement('NIDeclarado', $cpfCnpjNum);
        $ideDeclarado->appendChild($NIDeclarado);

        // NomeDeclarado
        $NomeDeclarado = $dom->createElement('NomeDeclarado', substr($nomeDeclarado, 0, 100));
        $ideDeclarado->appendChild($NomeDeclarado);

        //tpNomeDeclarado
        $tpNomeDeclarado = $dom->createElement('tpNomeDeclarado', 'OECD207');
        $ideDeclarado->appendChild($tpNomeDeclarado);

        if($tipoNI == 1){
            // DataNasc
            $DataNasc = $dom->createElement('DataNasc', $dataNascimento);
            $ideDeclarado->appendChild($DataNasc);
        }

        // EnderecoLivre
        $EnderecoLivre = $dom->createElement('EnderecoLivre', substr($enderecoCliente, 0, 200));
        $ideDeclarado->appendChild($EnderecoLivre);

        //PaisEndereco - grupo
        $PaisEndereco = $dom->createElement('PaisEndereco');
        $ideDeclarado->appendChild($PaisEndereco);

        //tpEndereco
        if($tipoNI == 1){
            $tpEndereco = $dom->createElement('tpEndereco', 'OECD302');
        } else {
            $tpEndereco = $dom->createElement('tpEndereco', 'OECD303');
        }
        $ideDeclarado->appendChild($tpEndereco);

        //Pais
        $Pais = $dom->createElement('Pais', 'BR');
        $PaisEndereco->appendChild($Pais);

        //mesCaixa - grupo
        $mesCaixa = $dom->createElement('mesCaixa');
        $evtMovOpFin->appendChild($mesCaixa);

        //anoMesCaixa
        $anoMesCaixa = $dom->createElement('anoMesCaixa', "{$ano}{$mes}");
        $mesCaixa->appendChild($anoMesCaixa);

        //movOpFin - grupo
        $movOpFin = $dom->createElement('movOpFin');
        $mesCaixa->appendChild($movOpFin);

        //Conta - grupo
        $Conta = $dom->createElement('Conta');
        $movOpFin->appendChild($Conta);

        //infoConta - grupo
        $infoConta = $dom->createElement('infoConta');
        $Conta->appendChild($infoConta);

        //Reportavel - grupo
        $Reportavel = $dom->createElement('Reportavel');
        $infoConta->appendChild($Reportavel);

        //Pais
        $PaisReportavel = $dom->createElement('Pais', 'BR');
        $Reportavel->appendChild($PaisReportavel);

        //tpConta 1 deposito
        $tpConta = $dom->createElement('tpConta', '1');
        $infoConta->appendChild($tpConta);

        //subTpConta 105 conta pré paga 
        $subTpConta = $dom->createElement('subTpConta', '105');
        $infoConta->appendChild($subTpConta);

        //tpNumConta
        $tpNumConta = $dom->createElement('tpNumConta', 'OECD602');
        $infoConta->appendChild($tpNumConta);

        //numConta
        $numConta = $dom->createElement('numConta', '$ug_id');
        $infoConta->appendChild($numConta);

        //tpRelacaoDeclarado
        $tpRelacaoDeclarado = $dom->createElement('tpRelacaoDeclarado', '1');
        $infoConta->appendChild($tpRelacaoDeclarado);

        //BRL moeda
        $moeda = $dom->createElement('moeda', 'BRL');
        $infoConta->appendChild($moeda);

        //NoTitulares
        $NoTitulares = $dom->createElement('NoTitulares', '1');
        $infoConta->appendChild($NoTitulares);

        //dtEncerramentoConta RESOLVER DEPOIS

        //IndInatividade RESOLVER DEPOIS 6 ANOS INATIV

        //BalancoConta grupo
        $BalancoConta = $dom->createElement('BalancoConta');
        $infoConta->appendChild($BalancoConta);

        //totCreditos

        $entradas_formatadas = number_format($entradas, 2, ',', '');
        $totCreditos = $dom->createElement('totCreditos', $entradas_formatadas);
        $BalancoConta->appendChild($totCreditos);

        //totDebitos
        $saidas_formatadas = number_format($saidas, 2, ',', '');
        $totDebitos = $dom->createElement('totDebitos', $saidas_formatadas);
        $BalancoConta->appendChild($totDebitos);

        //totCreditosMesmaTitularidade
        $totCreditosMesmaTitularidade = $dom->createElement('totCreditosMesmaTitularidade', '0,00');
        $BalancoConta->appendChild($totCreditosMesmaTitularidade);

        //totDebitosMesmaTitularidade
        $totDebitosMesmaTitularidade = $dom->createElement('totDebitosMesmaTitularidade', '0,00');
        $BalancoConta->appendChild($totDebitosMesmaTitularidade);

        //vlrUltDia RESOLVER DEPOIS SO MES DEZEMBRO

        //PgtosAcum - grupo
        $PgtosAcum = $dom->createElement('PgtosAcum');
        $infoConta->appendChild($PgtosAcum);

        //tpPgto
        $tpPgto = $dom->createElement('tpPgto', '999');
        $PgtosAcum->appendChild($tpPgto);

        //totPgtosAcum
        $totPgtosAcum = $dom->createElement('totPgtosAcum', '0,00');
        $PgtosAcum->appendChild($totPgtosAcum);

        // Gerar XML final
        $xmlString = $dom->saveXML();
        echo $xmlString;
    }
}
