<?php
require_once '/www/db/connect.php';
require_once '/www/db/ConnectionPDO.php';
require_once '../libs/xmlseclibs.php';
require_once '/www/includes/load_dotenv.php';
require_once '../libs/xmlseclibs.php';

use RobRichards\XMLSecLibs\XMLSecurityKey;
use RobRichards\XMLSecLibs\XMLSecurityDSig;


class GerarEFinanceira
{

    private $cnpjEPP;                            // CNPJ da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $razaoEPP;  // Razão Social da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $enderecoEPP;    // Endereço da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $bairroEPP;
    private $numeroEPP;
    private $complementoEPP;
    private $cepEPP;                                   // CEP da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $ufEPP;                                          // UF da empresa E-PREPAG ADMINISTRADORA DE CARTOES LTDA
    private $municipioEPP;
    private $nomeRespRMF;                  // Nome do responsável pela RMF
    private $cpfRespRMF;                    // CPF do responsável pela RMF
    private $foneRespRMF;                  // Telefone do responsável pela RMF
    private $dddRespRMF;                  // DDD do responsável pela RMF
    private $emailRespeFIN;                // Email do responsável pela e-financeira
    private $cpfRespeFIN;                // CPF do responsável pela e-financeira
    private $nomeRespeFIN;                // Nome do responsável pela e-financeira
    private $foneRespeFIN;                // Telefone do responsável pela e-financeira
    private $dddRespeFIN;                // DDD do responsável pela e-financeira
    private $setorRespeFIN;                // Setor do responsável pela e-financeira
    private $enderecoRespeFIN;                // Endereço do responsável pela e-financeira
    private $municipioRespeFIN;                // Município do responsável pela e-financeira
    private $ufRespeFIN;                // UF do responsável pela e-financeira
    private $cepRespeFIN;                // CEP do responsável pela e-financeira
    private $bairroRespeFIN;                // Bairro do responsável pela e-financeira
    private $complementoRespeFIN;                // Complemento do responsável pela e-financeira
    private $numeroRespeFIN;                // Número do responsável pela e-financeira
    private $setorReprLegal;
    private $telefoneReprLegal;
    private $dddTelefoneReprLegal;
    private $cpfReprLegal;
    private $codMunicipioEPP;
    private $certificado;
    private $senhaCertificado;
    private $caminhoCertificadoPublico;

    public function __construct()
    {
        $this->cnpjEPP = '19037276000172';
        $this->razaoEPP = 'E-prepag Administradora de Cartoes Ltda';
        $this->enderecoEPP = 'R. Dep. Lacerda Franco';
        $this->bairroEPP = 'Pinheiros';
        $this->numeroEPP = '300';
        $this->complementoEPP = 'conjuntos 26-A';
        $this->cepEPP = '05418000';
        $this->ufEPP = 'SP';
        $this->nomeRespRMF = 'Glaucia da Costa Gregio';
        $this->cpfRespRMF = '16806289843';
        $this->foneRespRMF = '51783224';
        $this->dddRespRMF = '11';
        $this->emailRespeFIN = 'rc@e-prepag.com.br';
        $this->municipioEPP = 'SAO PAULO';
        $this->enderecoRespeFIN = 'R. Dep. Lacerda Franco';
        $this->municipioRespeFIN = 'SAO PAULO';
        $this->ufRespeFIN = 'SP';
        $this->cepRespeFIN = '05418000';
        $this->bairroRespeFIN = 'Pinheiros';
        $this->complementoRespeFIN = 'conjuntos 26-A';
        $this->numeroRespeFIN = '300';
        $this->cpfRespeFIN = '38574409880';
        $this->nomeRespeFIN = 'THANIA LOPES FERREIRA';
        $this->dddRespeFIN = '11';
        $this->foneRespeFIN = '51783224';
        $this->setorRespeFIN = 'Risco e Compliance';
        $this->setorReprLegal = 'Diretoria';
        $this->telefoneReprLegal = '975687428';
        $this->dddTelefoneReprLegal = '11';
        $this->cpfReprLegal = '16806289843';
        $this->codMunicipioEPP = '350010';
        $this->certificado = '../ssl/cert-eprepag.pfx';
        $this->senhaCertificado = getenv('senha_certificado_digital');
        $this->caminhoCertificadoPublico = '../ssl/pre-efinanceira-receita-fazenda-gov-br-2025.cer';
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

        $namespace = 'http://www.eFinanceira.gov.br/schemas/evtMovOpFin/v1_2_1';
        // Criar o elemento raiz com namespace
        $eFinanceira = $dom->createElementNS(
            $namespace, // namespace
            'eFinanceira' // nome do elemento
        );
        $dom->appendChild($eFinanceira);

        // Criar o elemento evtMovOpFin com atributo id
        $idNovo = $this->obterUltimoIdEnvio() + 1;

        $id_formatado = $this->gerarIdFormatado($idNovo);

        $evtMovOpFin = $dom->createElementNS($namespace, 'evtMovOpFin');
        $evtMovOpFin->setAttribute('id', $id_formatado);
        $eFinanceira->appendChild($evtMovOpFin);

        // ideEvento - grupo
        $ideEvento = $dom->createElementNS($namespace, 'ideEvento');
        $evtMovOpFin->appendChild($ideEvento);

        // indRetificacao - 1 para original 2 para retificação 3 para retificação a pedido
        $indRetificacao = $dom->createElementNS($namespace, 'indRetificacao', '1');
        $ideEvento->appendChild($indRetificacao);

        // tpAmb
        $tpAmb = $dom->createElementNS($namespace, 'tpAmb', '1');
        $ideEvento->appendChild($tpAmb);

        // aplicEmi
        $aplicEmi = $dom->createElementNS($namespace, 'aplicEmi', '1');
        $ideEvento->appendChild($aplicEmi);

        // verAplic
        $verAplic = $dom->createElementNS($namespace, 'verAplic', '00000000000000000001');
        $ideEvento->appendChild($verAplic);

        // ideDeclarante - grupo
        $ideDeclarante = $dom->createElementNS($namespace, 'ideDeclarante');
        $evtMovOpFin->appendChild($ideDeclarante);

        $cnpjDeclarante = $dom->createElementNS($namespace, 'cnpjDeclarante', $this->cnpjEPP);
        $ideDeclarante->appendChild($cnpjDeclarante);

        // ideDeclarado -  grupo
        $ideDeclarado = $dom->createElementNS($namespace, 'ideDeclarado');
        $evtMovOpFin->appendChild($ideDeclarado);

        // tipo NI 1-cpf 2-cnpj
        $tpNI = $dom->createElementNS($namespace, 'tpNI', $tipoNI);
        $ideDeclarado->appendChild($tpNI);

        // NIDeclarado cpf ou cnpj
        $cpfCnpjNum = $this->apenasNumeros($cpfCnpj);
        $NIDeclarado = $dom->createElementNS($namespace, 'NIDeclarado', $cpfCnpjNum);
        $ideDeclarado->appendChild($NIDeclarado);

        // NomeDeclarado
        $NomeDeclarado = $dom->createElementNS($namespace, 'NomeDeclarado', substr($nomeDeclarado, 0, 100));
        $ideDeclarado->appendChild($NomeDeclarado);

        if ($tipoNI == 1) {
            // DataNasc
            $DataNasc = $dom->createElementNS($namespace, 'DataNasc', $dataNascimento);
            $ideDeclarado->appendChild($DataNasc);
        }

        // EnderecoLivre
        $EnderecoLivre = $dom->createElementNS($namespace, 'EnderecoLivre', substr($enderecoCliente, 0, 200));
        $ideDeclarado->appendChild($EnderecoLivre);

        //PaisEndereco - grupo
        $PaisEndereco = $dom->createElementNS($namespace, 'PaisEndereco');
        $ideDeclarado->appendChild($PaisEndereco);

        //Pais
        $Pais = $dom->createElementNS($namespace, 'Pais', 'BR');
        $PaisEndereco->appendChild($Pais);

        //mesCaixa - grupo
        $mesCaixa = $dom->createElementNS($namespace, 'mesCaixa');
        $evtMovOpFin->appendChild($mesCaixa);

        //anoMesCaixa
        $anoMesCaixa = $dom->createElementNS($namespace, 'anoMesCaixa', "{$ano}{$mes}");
        $mesCaixa->appendChild($anoMesCaixa);

        //movOpFin - grupo
        $movOpFin = $dom->createElementNS($namespace, 'movOpFin');
        $mesCaixa->appendChild($movOpFin);

        //Conta - grupo
        $Conta = $dom->createElementNS($namespace, 'Conta');
        $movOpFin->appendChild($Conta);

        //infoConta - grupo
        $infoConta = $dom->createElementNS($namespace, 'infoConta');
        $Conta->appendChild($infoConta);

        //Reportavel - grupo
        $Reportavel = $dom->createElementNS($namespace, 'Reportavel');
        $infoConta->appendChild($Reportavel);

        //Pais
        $PaisReportavel = $dom->createElementNS($namespace, 'Pais', 'BR');
        $Reportavel->appendChild($PaisReportavel);

        //tpConta 1 deposito
        $tpConta = $dom->createElementNS($namespace, 'tpConta', '1');
        $infoConta->appendChild($tpConta);

        //subTpConta 105 conta pré paga 
        $subTpConta = $dom->createElementNS($namespace, 'subTpConta', '105');
        $infoConta->appendChild($subTpConta);

        //tpNumConta
        $tpNumConta = $dom->createElementNS($namespace, 'tpNumConta', 'OECD602');
        $infoConta->appendChild($tpNumConta);

        //numConta
        $numConta = $dom->createElementNS($namespace, 'numConta', $ug_id);
        $infoConta->appendChild($numConta);

        //tpRelacaoDeclarado
        $tpRelacaoDeclarado = $dom->createElementNS($namespace, 'tpRelacaoDeclarado', '1');
        $infoConta->appendChild($tpRelacaoDeclarado);

        //BRL moeda
        $moeda = $dom->createElementNS($namespace, 'moeda', 'BRL');
        $infoConta->appendChild($moeda);

        //NoTitulares
        $NoTitulares = $dom->createElementNS($namespace, 'NoTitulares', '1');
        $infoConta->appendChild($NoTitulares);

        //dtEncerramentoConta RESOLVER DEPOIS

        //IndInatividade RESOLVER DEPOIS 6 ANOS INATIV

        //BalancoConta grupo
        $BalancoConta = $dom->createElementNS($namespace, 'BalancoConta');
        $infoConta->appendChild($BalancoConta);

        //totCreditos

        $entradas_formatadas = number_format($entradas, 2, ',', '');
        $totCreditos = $dom->createElementNS($namespace, 'totCreditos', $entradas_formatadas);
        $BalancoConta->appendChild($totCreditos);

        //totDebitos
        $saidas_formatadas = number_format($saidas, 2, ',', '');
        $totDebitos = $dom->createElementNS($namespace, 'totDebitos', $saidas_formatadas);
        $BalancoConta->appendChild($totDebitos);

        //totCreditosMesmaTitularidade
        $totCreditosMesmaTitularidade = $dom->createElementNS($namespace, 'totCreditosMesmaTitularidade', '0,00');
        $BalancoConta->appendChild($totCreditosMesmaTitularidade);

        //totDebitosMesmaTitularidade
        $totDebitosMesmaTitularidade = $dom->createElementNS($namespace, 'totDebitosMesmaTitularidade', '0,00');
        $BalancoConta->appendChild($totDebitosMesmaTitularidade);

        //vlrUltDia RESOLVER DEPOIS SO MES DEZEMBRO

        //PgtosAcum - grupo
        $PgtosAcum = $dom->createElementNS($namespace, 'PgtosAcum');
        $infoConta->appendChild($PgtosAcum);

        //tpPgto
        $tpPgto = $dom->createElementNS($namespace, 'tpPgto', '999');
        $PgtosAcum->appendChild($tpPgto);

        //totPgtosAcum
        $totPgtosAcum = $dom->createElementNS($namespace, 'totPgtosAcum', '0,00');
        $PgtosAcum->appendChild($totPgtosAcum);

        // Gerar XML final
        return ['xml' => $dom, 'id' => $id_formatado];
    }

    public function gerarCadastroDeclarante()
    {
        // Cria o objeto DOM
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true; // deixa o XML identado

        $namespace = 'http://www.eFinanceira.gov.br/schemas/evtCadDeclarante/v1_2_0';

        // Cria o elemento raiz com namespace
        $eFinanceira = $dom->createElementNS(
            $namespace,
            'eFinanceira'
        );
        $dom->appendChild($eFinanceira);

        // Cria o nó evtCadDeclarante
        $idNovo = $this->obterUltimoIdEnvio() + 1;

        $id_formatado = $this->gerarIdFormatado($idNovo);

        $evtCadDeclarante = $dom->createElementNS($namespace, 'evtCadDeclarante');
        $evtCadDeclarante->setAttribute('id', $id_formatado);
        $eFinanceira->appendChild($evtCadDeclarante);

        // ideEvento
        $ideEvento = $dom->createElementNS($namespace, 'ideEvento');
        $ideEvento->appendChild($dom->createElementNS($namespace, 'indRetificacao', '1'));
        $ideEvento->appendChild($dom->createElementNS($namespace, 'tpAmb', '1'));
        $ideEvento->appendChild($dom->createElementNS($namespace, 'aplicEmi', '1'));
        $ideEvento->appendChild($dom->createElementNS($namespace, 'verAplic', '00000000000000000001'));
        $evtCadDeclarante->appendChild($ideEvento);

        // ideDeclarante
        $ideDeclarante = $dom->createElementNS($namespace, 'ideDeclarante');
        $ideDeclarante->appendChild($dom->createElementNS($namespace, 'cnpjDeclarante', $this->cnpjEPP));
        $evtCadDeclarante->appendChild($ideDeclarante);

        // infoCadastro
        $infoCadastro = $dom->createElementNS($namespace, 'infoCadastro');
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'nome', $this->razaoEPP));
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'enderecoLivre', $this->enderecoEPP));
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'municipio', $this->codMunicipioEPP));
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'UF', $this->ufEPP));
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'CEP', $this->cepEPP));
        $infoCadastro->appendChild($dom->createElementNS($namespace, 'Pais', 'BR'));

        // Subnó paisResid
        $paisResid = $dom->createElementNS($namespace, 'paisResid');
        $paisResid->appendChild($dom->createElementNS($namespace, 'Pais', 'BR'));
        $infoCadastro->appendChild($paisResid);

        $evtCadDeclarante->appendChild($infoCadastro);

        return ['xml' => $dom, 'id' => $id_formatado];
    }

    public function gerarAbertura($data_inicio, $data_fim)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = 'http://www.eFinanceira.gov.br/schemas/evtAberturaeFinanceira/v1_2_1';

        // <eFinanceira>
        $eFinanceira = $dom->createElementNS($namespace, 'eFinanceira');
        $dom->appendChild($eFinanceira);

        // <evtAberturaeFinanceira id="...">
        $idNovo = $this->obterUltimoIdEnvio() + 1;

        $id_formatado = $this->gerarIdFormatado($idNovo);
        $evt = $dom->createElementNS($namespace, 'evtAberturaeFinanceira');
        $evt->setAttribute('id', $id_formatado); // Identificador único do evento
        $eFinanceira->appendChild($evt);

        // <ideEvento>
        $ideEvento = $dom->createElementNS($namespace, 'ideEvento');
        // Indicativo de Retificação: 1 - Original, 2 - Retificador, 3 - Retificador a Pedido
        $indRetificacao = $dom->createElementNS($namespace, 'indRetificacao', '1');
        $ideEvento->appendChild($indRetificacao);
        // Identificação do ambiente: 1 - Produção, 2 - Homologação
        $tpAmb = $dom->createElementNS($namespace, 'tpAmb', '1');
        $ideEvento->appendChild($tpAmb);
        // Processo de emissão do evento: 1 - Emissão com aplicativo da empresa
        $aplicEmi = $dom->createElementNS($namespace, 'aplicEmi', '1');
        $ideEvento->appendChild($aplicEmi);
        // Versão do aplicativo de emissão do evento
        $verAplic = $dom->createElementNS($namespace, 'verAplic', '0000000000000001');
        $ideEvento->appendChild($verAplic);

        $evt->appendChild($ideEvento);

        // <ideDeclarante>
        $ideDeclarante = $dom->createElementNS($namespace, 'ideDeclarante');
        // CNPJ da Entidade Declarante
        $cnpjDeclarante = $dom->createElementNS($namespace, 'cnpjDeclarante', $this->cnpjEPP);
        $ideDeclarante->appendChild($cnpjDeclarante);

        $evt->appendChild($ideDeclarante);

        // <infoAbertura>
        $infoAbertura = $dom->createElementNS($namespace, 'infoAbertura');
        // Data Inicial
        $dtInicio = $dom->createElementNS($namespace, 'dtInicio', $data_inicio);
        $infoAbertura->appendChild($dtInicio);
        // Data Final
        $dtFim = $dom->createElementNS($namespace, 'dtFim', $data_fim);
        $infoAbertura->appendChild($dtFim);

        $evt->appendChild($infoAbertura);

        // <AberturaMovOpFin> informações obrigatórias
        $aberturaMov = $dom->createElementNS($namespace, 'AberturaMovOpFin');

        // <ResponsavelRMF>
        $responsavel = $dom->createElementNS($namespace, 'ResponsavelRMF');
        // CNPJ da entidade responsável pela RMF
        $cnpj = $dom->createElementNS($namespace, 'CNPJ', $this->cnpjEPP);
        $responsavel->appendChild($cnpj);
        // CPF
        $cpf = $dom->createElementNS($namespace, 'CPF', $this->cpfRespRMF);
        $responsavel->appendChild($cpf);
        // Nome
        $nome = $dom->createElementNS($namespace, 'Nome', $this->nomeRespRMF);
        $responsavel->appendChild($nome);
        // Setor
        $setor = $dom->createElementNS($namespace, 'Setor', 'Financeiro');
        $responsavel->appendChild($setor);

        // Telefone
        $telefone = $dom->createElementNS($namespace, 'Telefone');
        // DDD
        $ddd = $dom->createElementNS($namespace, 'DDD', $this->dddRespRMF);
        $telefone->appendChild($ddd);
        // Número
        $numero = $dom->createElementNS($namespace, 'Numero', $this->foneRespRMF);
        $telefone->appendChild($numero);
        $responsavel->appendChild($telefone);

        // Endereço
        $endereco = $dom->createElementNS($namespace, 'Endereco');
        // Logradouro
        $logradouro = $dom->createElementNS($namespace, 'Logradouro', $this->enderecoEPP);
        $endereco->appendChild($logradouro);
        // Número
        $num = $dom->createElementNS($namespace, 'Numero', $this->numeroEPP);
        $endereco->appendChild($num);
        //Complemento
        $complemento = $dom->createElementNS($namespace, 'Complemento', $this->complementoEPP);
        $endereco->appendChild($complemento);
        // Bairro
        $bairro = $dom->createElementNS($namespace, 'Bairro', $this->bairroEPP);
        $endereco->appendChild($bairro);
        // CEP
        $cep = $dom->createElementNS($namespace, 'CEP', $this->cepEPP);
        $endereco->appendChild($cep);
        // Município
        $municipio = $dom->createElementNS($namespace, 'Municipio', $this->municipioEPP);
        $endereco->appendChild($municipio);
        // UF
        $uf = $dom->createElementNS($namespace, 'UF', $this->ufEPP);
        $endereco->appendChild($uf);

        $responsavel->appendChild($endereco);

        $aberturaMov->appendChild($responsavel);

        // <RespeFin> responsável pela e-Financeira
        $respeFin = $dom->createElementNS($namespace, 'RespeFin');
        // CPF
        $cpfRF = $dom->createElementNS($namespace, 'CPF', $this->cpfRespeFIN);
        $respeFin->appendChild($cpfRF);
        // Nome
        $nomeRF = $dom->createElementNS($namespace, 'Nome', $this->nomeRespeFIN);
        $respeFin->appendChild($nomeRF);
        // Setor
        $setorRF = $dom->createElementNS($namespace, 'Setor', $this->setorRespeFIN);
        $respeFin->appendChild($setorRF);
        // Telefone
        $telRF = $dom->createElementNS($namespace, 'Telefone');
        // DDD
        $dddRF = $dom->createElementNS($namespace, 'DDD', $this->dddRespeFIN);
        $telRF->appendChild($dddRF);
        // Número
        $numRF = $dom->createElementNS($namespace, 'Numero', $this->foneRespeFIN);
        $telRF->appendChild($numRF);
        $respeFin->appendChild($telRF);

        // Cria o nó <Endereco>
        $enderecoRespEfin = $dom->createElementNS($namespace, 'Endereco');

        // Adiciona os filhos de <Endereco>
        $logradouroEfin = $dom->createElementNS($namespace, 'Logradouro', $this->enderecoRespeFIN);
        $numeroEfin = $dom->createElementNS($namespace, 'Numero', $this->numeroRespeFIN);
        $bairroEfin = $dom->createElementNS($namespace, 'Bairro', $this->bairroRespeFIN);
        $cepEfin = $dom->createElementNS($namespace, 'CEP', $this->cepRespeFIN);
        $municipioEfin = $dom->createElementNS($namespace, 'Municipio', $this->municipioRespeFIN);
        $ufEfin = $dom->createElementNS($namespace, 'UF', $this->ufRespeFIN);
        $ComplementoEfin = $dom->createElementNS($namespace, 'Complemento', $this->complementoRespeFIN);

        // Monta o elemento <Endereco>
        $enderecoRespEfin->appendChild($logradouroEfin);
        $enderecoRespEfin->appendChild($numeroEfin);
        $enderecoRespEfin->appendChild($ComplementoEfin);
        $enderecoRespEfin->appendChild($bairroEfin);
        $enderecoRespEfin->appendChild($cepEfin);
        $enderecoRespEfin->appendChild($municipioEfin);
        $enderecoRespEfin->appendChild($ufEfin);

        // Cria o elemento <Email>
        $emaiRespEfin = $dom->createElementNS($namespace, 'Email', $this->emailRespeFIN);

        // Adiciona <Endereco> e <Email> dentro de <RespeFin>
        $respeFin->appendChild($enderecoRespEfin);
        $respeFin->appendChild($emaiRespEfin);

        $aberturaMov->appendChild($respeFin);

        //RepresLegal
        $represLegal = $dom->createElementNS($namespace, 'RepresLegal');

        // CPF
        $cpfRL = $dom->createElementNS($namespace, 'CPF', $this->cpfReprLegal);
        $represLegal->appendChild($cpfRL);
        // Setor
        $setorRL = $dom->createElementNS($namespace, 'Setor', $this->setorReprLegal);
        $represLegal->appendChild($setorRL);
        // Telefone
        $telRL = $dom->createElementNS($namespace, 'Telefone');
        // DDD
        $dddRL = $dom->createElementNS($namespace, 'DDD', $this->dddTelefoneReprLegal);
        $telRL->appendChild($dddRL);
        // Número
        $numRL = $dom->createElementNS($namespace, 'Numero', $this->telefoneReprLegal);
        $telRL->appendChild($numRL);
        $represLegal->appendChild($telRL);

        $aberturaMov->appendChild($represLegal);

        $evt->appendChild($aberturaMov);

        // Gerar XML
        return ['xml' => $dom, 'id' => $id_formatado];
    }

    public function gerarFechamento($dataInicioSemestre, $dataFimSemestre)
    {

        // 1. Definição dos Namespaces
        $ns = 'http://www.eFinanceira.gov.br/schemas/evtFechamentoeFinanceira/v1_2_2';
        $nsDS = 'http://www.w3.org/2000/09/xmldsig#';

        // 2. Dados de Exemplo para o fechamento (1º Semestre de 2025)
        $idNovo = $this->obterUltimoIdEnvio() + 1;
        $id_formatado = $this->gerarIdFormatado($idNovo);
        $versaoApp = '1.0.0';
        $ambiente = '2'; // 1 = Produção, 2 = Homologação

        // Exemplo de quantos arquivos evtMovOpFin você enviou por mês
        $arquivosPorMes = [
            '202501' => '1500',
            '202502' => '1450',
            '202503' => '1600',
            '202504' => '1520',
            '202505' => '1580',
            '202506' => '1700'
        ];

        // 3. Criação do Documento DOM
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = false;

        // 4. Elemento Raiz (eFinanceira)
        $eFinanceira = $doc->createElementNS($ns, 'eFinanceira');
        $doc->appendChild($eFinanceira);

        // 5. Elemento do Evento (evtFechamentoeFinanceira)
        $evtFechamento = $doc->createElementNS($ns, 'evtFechamentoeFinanceira');
        $evtFechamento->setAttribute('id', $id_formatado);
        $eFinanceira->appendChild($evtFechamento);

        // 6. Grupo: ideEvento (Obrigatório)
        $ideEvento = $doc->createElementNS($ns, 'ideEvento');
        $evtFechamento->appendChild($ideEvento);

        $ideEvento->appendChild($doc->createElementNS($ns, 'indRetificacao', '1')); // 1 = Original
        $ideEvento->appendChild($doc->createElementNS($ns, 'tpAmb', $ambiente));
        $ideEvento->appendChild($doc->createElementNS($ns, 'aplicEmi', '1')); // 1 = Aplicativo da empresa
        $ideEvento->appendChild($doc->createElementNS($ns, 'verAplic', $versaoApp));

        // 7. Grupo: ideDeclarante (Obrigatório)
        $ideDeclarante = $doc->createElementNS($ns, 'ideDeclarante');
        $evtFechamento->appendChild($ideDeclarante);

        $ideDeclarante->appendChild($doc->createElementNS($ns, 'cnpjDeclarante', $this->cnpjEPP));

        // 8. Grupo: infoFechamento (Obrigatório)
        $infoFechamento = $doc->createElementNS($ns, 'infoFechamento');
        $evtFechamento->appendChild($infoFechamento);

        $infoFechamento->appendChild($doc->createElementNS($ns, 'dtInicio', $dataInicioSemestre));
        $infoFechamento->appendChild($doc->createElementNS($ns, 'dtFim', $dataFimSemestre));
        $infoFechamento->appendChild($doc->createElementNS($ns, 'sitEspecial', '0')); // 0 = Não se aplica

        // 9. Grupo: FechamentoMovOpFin (Funcionalmente Obrigatório para você)
        // Este grupo é minOccurs="0" no XSD, mas obrigatório pela regra de negócio do seu módulo.
        $fechamentoMovOpFin = $doc->createElementNS($ns, 'FechamentoMovOpFin');
        $evtFechamento->appendChild($fechamentoMovOpFin);

        // Você DEVE adicionar um 'FechamentoMes' para cada mês do semestre.
        foreach ($arquivosPorMes as $anoMes => $quantidade) {
            // Grupo: FechamentoMes (Obrigatório dentro de FechamentoMovOpFin)
            $fechamentoMes = $doc->createElementNS($ns, 'FechamentoMes');
            $fechamentoMovOpFin->appendChild($fechamentoMes);

            // anoMesCaixa (Obrigatório)
            $fechamentoMes->appendChild($doc->createElementNS($ns, 'anoMesCaixa', $anoMes));
            // quantArqTrans (Obrigatório)
            $fechamentoMes->appendChild($doc->createElementNS($ns, 'quantArqTrans', $quantidade));
        }

        // 10. Grupo: ds:Signature (Obrigatório)
        // Este é o placeholder para a Assinatura Digital XMLDSig.
        // A assinatura deve ser gerada por uma biblioteca específica (ex: xmlseclibs)
        // e inserida aqui.
        $signature = $doc->createElementNS($nsDS, 'ds:Signature');
        $eFinanceira->appendChild($signature);
        $signature->appendChild($doc->createComment(' Bloco obrigatório para assinatura digital (XMLDSig) '));

        // 11. Exibir o XML
        return ['xml' => $doc, 'id' => $id_formatado];
    }

    function gerarLoteAssincrono(array $eventos)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $ns = 'http://www.eFinanceira.gov.br/schemas/envioLoteEventosAssincrono/v1_0_0';

        // Elemento raiz <eFinanceira>
        $eFinanceira = $dom->createElementNS($ns, 'eFinanceira');
        $dom->appendChild($eFinanceira);

        // <loteEventosAssincrono>
        $lote = $dom->createElement('loteEventosAssincrono');
        $eFinanceira->appendChild($lote);

        // <cnpjDeclarante>
        $cnpjElem = $dom->createElement('cnpjDeclarante', $this->cnpjEPP);
        $lote->appendChild($cnpjElem);

        // <eventos>
        $eventosElem = $dom->createElement('eventos');
        $lote->appendChild($eventosElem);

        foreach ($eventos as $ev) {
            if (!isset($ev['id'], $ev['xml'])) {
                continue; // pula itens inválidos
            }

            $eventoElem = $dom->createElement('evento');
            $eventoElem->setAttribute('id', $ev['id']);

            // Importa o XML do evento para dentro de <evento>
            if ($ev['xml'] instanceof DOMDocument) {
                $imported = $dom->importNode($ev['xml']->documentElement, true);
                $eventoElem->appendChild($imported);
            } elseif ($ev['xml'] instanceof DOMElement) {
                $imported = $dom->importNode($ev['xml'], true);
                $eventoElem->appendChild($imported);
            } else {
                throw new Exception('O evento deve ser DOMDocument ou DOMElement');
            }

            $eventosElem->appendChild($eventoElem);
        }

        return $dom;
    }

    public function assinarXML(DOMDocument $dom)
    {
        // Busca qualquer elemento que tenha atributo 'id'
        $xpath = new DOMXPath($dom);
        $elementoEvento = $xpath->query('//*[@id]')->item(0);

        if (!$elementoEvento) {
            throw new Exception('Elemento com atributo "id" não encontrado no XML.');
        }

        // Obtém o valor do id
        $idEvento = $elementoEvento->getAttribute('id');

        if (empty($idEvento)) {
            throw new Exception('Atributo "id" está vazio.');
        }

        // Remove assinatura existente se houver
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $assinaturaExistente = $xpath->query('.//ds:Signature', $elementoEvento);
        if ($assinaturaExistente instanceof DOMNodeList && $assinaturaExistente->length > 0) {
            $elementoEvento->removeChild($assinaturaExistente->item(0));
        }

        // Lê certificado .pfx
        if (!file_exists($this->certificado)) {
            throw new Exception('Certificado PFX não encontrado: ' . $this->certificado);
        }

        $pfxContent = file_get_contents($this->certificado);
        if (!openssl_pkcs12_read($pfxContent, $certs, $this->senhaCertificado)) {
            throw new Exception('Erro ao abrir o certificado PFX. Verifique caminho e senha.');
        }

        // Cria objeto de assinatura
        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N);

        // Adiciona referência SEM gerar Id automático
        // Passa o id_name vazio para evitar geração de Id aleatório
        $objDSig->addReference(
            $elementoEvento,
            XMLSecurityDSig::SHA256,
            array(
                'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                'http://www.w3.org/TR/2001/REC-xml-c14n-20010315'
            ),
            array('overwrite' => false)
        );

        // Carrega chave privada
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
        $objKey->loadKey($certs['pkey'], false);

        // Assina o documento
        $objDSig->sign($objKey);

        // Adiciona certificado X509
        $objDSig->add509Cert($certs['cert'], true, false);

        // Anexa assinatura no elemento
        $signatureNode = $objDSig->appendSignature($elementoEvento);

        // Após assinar, ajusta o atributo URI manualmente no XML gerado
        if ($signatureNode) {
            $xpathSig = new DOMXPath($dom);
            $xpathSig->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
            $referenceNode = $xpathSig->query('.//ds:Reference', $signatureNode)->item(0);

            if ($referenceNode) {
                // Define o URI com o id original
                $referenceNode->setAttribute('URI', '#' . $idEvento);
            }
        }

        // Remove o atributo Id (maiúsculo) gerado automaticamente pela biblioteca
        // Mantém apenas o id (minúsculo) original
        if ($elementoEvento->hasAttribute('Id')) {
            $idMaiusculo = $elementoEvento->getAttribute('Id');
            // Remove apenas se for diferente do id original (é o gerado automaticamente)
            if ($idMaiusculo !== $idEvento) {
                $elementoEvento->removeAttribute('Id');
            }
        }

        return $dom;
    }


    public function criptografarLoteEF($xmlConteudo)
    {
        // 1. Gerar chave AES-128 e IV (vetor inicialização)
        $chaveAES = openssl_random_pseudo_bytes(16); // 128 bits
        $iv = openssl_random_pseudo_bytes(16);       // CBC precisa de IV 16 bytes

        // 2. Encriptar XML com AES-128-CBC
        $xmlCriptografado = openssl_encrypt(
            $xmlConteudo,
            'AES-128-CBC',
            $chaveAES,
            OPENSSL_RAW_DATA,
            $iv
        );

        // 3. Ler certificado público e encriptar chave AES + IV com RSA 2048
        $certPubContent = file_get_contents($this->caminhoCertificadoPublico);
        $certPub = openssl_pkey_get_public($certPubContent);
        if (!$certPub) {
            throw new Exception("Não foi possível carregar o certificado público.");
        }

        // Concatenar IV + chave AES
        $chaveConcatenada = $iv . $chaveAES;
        // Encriptar com RSA (PKCS#1 v1.5)
        $chaveCriptografada = '';
        if (!openssl_public_encrypt($chaveConcatenada, $chaveCriptografada, $certPub, OPENSSL_PKCS1_PADDING)) {
            throw new Exception("Erro ao criptografar chave AES com RSA.");
        }

        // 4. Converter tudo para Base64
        $xmlCriptografadoBase64 = base64_encode($xmlCriptografado);
        $chaveCriptografadaBase64 = base64_encode($chaveCriptografada);

        // 5. Montar XML final
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $ns = 'http://www.eFinanceira.gov.br/schemas/envioLoteCriptografado/v1_2_0';
        $eFinanceira = $dom->createElementNS($ns, 'eFinanceira');
        $dom->appendChild($eFinanceira);

        $loteElem = $dom->createElement('loteCriptografado');
        $eFinanceira->appendChild($loteElem);

        $loteElem->appendChild($dom->createElement('id', $idLote));
        $loteElem->appendChild($dom->createElement('idCertificado', $idCertificado));
        $loteElem->appendChild($dom->createElement('chave', $chaveCriptografadaBase64));
        $loteElem->appendChild($dom->createElement('lote', $xmlCriptografadoBase64));

        return $dom;
    }
}
