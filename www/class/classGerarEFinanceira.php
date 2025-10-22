<?php
require_once '/www/db/connect.php';
require_once '/www/db/ConnectionPDO.php';

class GerarEFinanceira
{

    private $cnpjEPP;                            // CNPJ da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $razaoEPP;  // Razão Social da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $enderecoEPP;    // Endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $cepEPP;                                   // CEP da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $ufEPP;                                          // UF da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $nomeRespEPP;                      // Nome do responsável da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $foneEPP;                               // Telefone para contato na empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA 
    private $emailEPP;               // Email para contato na empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA 
    private $municipioEPP;

    public function __construct()
    {
        $this->cnpjEPP = '19037276000172';
        $this->razaoEPP = 'E-prepag Administradora de Cartoes Ltda';
        $this->enderecoEPP = 'Rua Deputado Lacerda Franco, 300 - conjuntos 26-A, Pinheiros';
        $this->cepEPP = '05418000';
        $this->ufEPP = 'SP';
        $this->nomeRespEPP = 'Daniela Oliveira';
        $this->foneEPP = '01130309106';
        $this->emailEPP = getenv('email_financeiro');
        $this->municipioEPP = 'SAO PAULO';
    }

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

        $cnpjDeclarante = $dom->createElement('CNPJ', $this->cnpjEPP);
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

        if ($tipoNI == 1) {
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
        $numConta = $dom->createElement('numConta', $ug_id);
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
        return $dom;
    }

    public function gerarCadastroDeclarante()
    {
        // Cria o objeto DOM
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true; // deixa o XML identado

        // Cria o elemento raiz com namespace
        $eFinanceira = $dom->createElementNS(
            'http://www.eFinanceira.gov.br/schemas/evtCadDeclarante/v1_2_0',
            'eFinanceira'
        );
        $dom->appendChild($eFinanceira);

        // Cria o nó evtCadDeclarante
        $idNovo = $this->obterUltimoIdEnvio() + 1;

        $id_formatado = $this->gerarIdFormatado($idNovo);

        $evtCadDeclarante = $dom->createElement('evtCadDeclarante');
        $evtCadDeclarante->setAttribute('id', $id_formatado);
        $eFinanceira->appendChild($evtCadDeclarante);

        // ideEvento
        $ideEvento = $dom->createElement('ideEvento');
        $ideEvento->appendChild($dom->createElement('indRetificacao', '1'));
        $ideEvento->appendChild($dom->createElement('tpAmb', '1'));
        $ideEvento->appendChild($dom->createElement('aplicEmi', '2'));
        $ideEvento->appendChild($dom->createElement('verAplic', '00000000000000000001'));
        $evtCadDeclarante->appendChild($ideEvento);

        // ideDeclarante
        $ideDeclarante = $dom->createElement('ideDeclarante');
        $ideDeclarante->appendChild($dom->createElement('cnpjDeclarante', $this->cnpjEPP));
        $evtCadDeclarante->appendChild($ideDeclarante);

        // infoCadastro
        $infoCadastro = $dom->createElement('infoCadastro');
        $infoCadastro->appendChild($dom->createElement('nome', $this->razaoEPP));
        $infoCadastro->appendChild($dom->createElement('enderecoLivre', $this->enderecoEPP));
        $infoCadastro->appendChild($dom->createElement('municipio', $this->municipioEPP));
        $infoCadastro->appendChild($dom->createElement('UF', $this->ufEPP));
        $infoCadastro->appendChild($dom->createElement('CEP', $this->cepEPP));
        $infoCadastro->appendChild($dom->createElement('Pais', 'BR'));

        // Subnó paisResid
        $paisResid = $dom->createElement('paisResid');
        $paisResid->appendChild($dom->createElement('Pais', 'BR'));
        $infoCadastro->appendChild($paisResid);

        $evtCadDeclarante->appendChild($infoCadastro);

        return $dom;
    }

    public function gerarAbertura()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // <eFinanceira>
        $eFinanceira = $dom->createElementNS('http://www.eFinanceira.gov.br/schemas/evtAberturaeFinanceira/v1_2_1', 'eFinanceira');
        $dom->appendChild($eFinanceira);

        // <evtAberturaeFinanceira id="...">
        $evt = $dom->createElement('evtAberturaeFinanceira');
        $evt->setAttribute('id', 'ID0000000000001'); // Identificador único do evento
        $eFinanceira->appendChild($evt);

        // <ideEvento>
        $ideEvento = $dom->createElement('ideEvento');
        // Indicativo de Retificação: 1 - Original, 2 - Retificador, 3 - Retificador a Pedido
        $indRetificacao = $dom->createElement('indRetificacao', '1');
        $ideEvento->appendChild($indRetificacao);
        // Identificação do ambiente: 1 - Produção, 2 - Homologação
        $tpAmb = $dom->createElement('tpAmb', '1');
        $ideEvento->appendChild($tpAmb);
        // Processo de emissão do evento: 1 - Emissão com aplicativo da empresa
        $aplicEmi = $dom->createElement('aplicEmi', '1');
        $ideEvento->appendChild($aplicEmi);
        // Versão do aplicativo de emissão do evento
        $verAplic = $dom->createElement('verAplic', '0000000000000001');
        $ideEvento->appendChild($verAplic);

        $evt->appendChild($ideEvento);

        // <ideDeclarante>
        $ideDeclarante = $dom->createElement('ideDeclarante');
        // CNPJ da Entidade Declarante
        $cnpjDeclarante = $dom->createElement('cnpjDeclarante', '11111111111111');
        $ideDeclarante->appendChild($cnpjDeclarante);

        $evt->appendChild($ideDeclarante);

        // <infoAbertura>
        $infoAbertura = $dom->createElement('infoAbertura');
        // Data Inicial
        $dtInicio = $dom->createElement('dtInicio', '2025-01-01');
        $infoAbertura->appendChild($dtInicio);
        // Data Final
        $dtFim = $dom->createElement('dtFim', '2025-12-31');
        $infoAbertura->appendChild($dtFim);

        $evt->appendChild($infoAbertura);

        // <AberturaMovOpFin> informações obrigatórias
        $aberturaMov = $dom->createElement('AberturaMovOpFin');

        // <ResponsavelRMF>
        $responsavel = $dom->createElement('ResponsavelRMF');
        // CNPJ da entidade responsável pela RMF
        $cnpj = $dom->createElement('CNPJ', '11111111111111');
        $responsavel->appendChild($cnpj);
        // CPF
        $cpf = $dom->createElement('CPF', '12345678901');
        $responsavel->appendChild($cpf);
        // Nome
        $nome = $dom->createElement('Nome', 'João da Silva');
        $responsavel->appendChild($nome);
        // Setor
        $setor = $dom->createElement('Setor', 'Financeiro');
        $responsavel->appendChild($setor);

        // Telefone
        $telefone = $dom->createElement('Telefone');
        // DDD
        $ddd = $dom->createElement('DDD', '21');
        $telefone->appendChild($ddd);
        // Número
        $numero = $dom->createElement('Numero', '999999999');
        $telefone->appendChild($numero);
        $responsavel->appendChild($telefone);

        // Endereço
        $endereco = $dom->createElement('Endereco');
        // Logradouro
        $logradouro = $dom->createElement('Logradouro', 'Rua Exemplo');
        $endereco->appendChild($logradouro);
        // Número
        $num = $dom->createElement('Numero', '123');
        $endereco->appendChild($num);
        // Bairro
        $bairro = $dom->createElement('Bairro', 'Centro');
        $endereco->appendChild($bairro);
        // CEP
        $cep = $dom->createElement('CEP', '12345678');
        $endereco->appendChild($cep);
        // Município
        $municipio = $dom->createElement('Municipio', '3304557');
        $endereco->appendChild($municipio);
        // UF
        $uf = $dom->createElement('UF', 'RJ');
        $endereco->appendChild($uf);

        $responsavel->appendChild($endereco);

        $aberturaMov->appendChild($responsavel);

        // <RespeFin> responsável pela e-Financeira
        $respeFin = $dom->createElement('RespeFin');
        // CPF
        $cpfRF = $dom->createElement('CPF', '98765432100');
        $respeFin->appendChild($cpfRF);
        // Nome
        $nomeRF = $dom->createElement('Nome', 'Maria Oliveira');
        $respeFin->appendChild($nomeRF);
        // Setor
        $setorRF = $dom->createElement('Setor', 'TI');
        $respeFin->appendChild($setorRF);
        // Telefone
        $telRF = $dom->createElement('Telefone');
        // DDD
        $dddRF = $dom->createElement('DDD', '21');
        $telRF->appendChild($dddRF);
        // Número
        $numRF = $dom->createElement('Numero', '988888888');
        $telRF->appendChild($numRF);
        $respeFin->appendChild($telRF);

        $aberturaMov->appendChild($respeFin);

        $evt->appendChild($aberturaMov);

        // Gerar XML
        echo $dom->saveXML();
    }
}
