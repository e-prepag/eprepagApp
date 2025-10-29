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
    private $certificado_privado_epp;
    private $chave_privada_epp;

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
        $this->caminhoCertificadoPublico = '/www/ssl/pre-efinanceira-receita-fazenda-gov-br-2025.cer';
        $this->certificado_privado_epp = '/www/ssl/private-epp-cert.pem';
        $this->chave_privada_epp = '/www/ssl/key-epp-cert.pem';
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

        return $result['ultimo_id'] ?: 9;
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
        $dom->formatOutput = false; // Deixa o XML formatado (quebras de linha e identação)
        $dom->preserveWhiteSpace = true;

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
        $namespace = 'http://www.eFinanceira.gov.br/schemas/evtCadDeclarante/v1_2_0';

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = true;

        // Elemento raiz <eFinanceira> com namespace correto
        $eFinanceira = $dom->createElementNS($namespace, 'eFinanceira');
        $dom->appendChild($eFinanceira);

        // Gera ID único do evento
        $idNovo = $this->obterUltimoIdEnvio() + 1;
        $id_formatado = $this->gerarIdFormatado($idNovo);

        // Cria <evtCadDeclarante> dentro do <eFinanceira>
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

        $paisResid = $dom->createElementNS($namespace, 'paisResid');
        $paisResid->appendChild($dom->createElementNS($namespace, 'Pais', 'BR'));
        $infoCadastro->appendChild($paisResid);

        $evtCadDeclarante->appendChild($infoCadastro);

        return ['xml' => $dom, 'id' => $id_formatado];
    }


    public function gerarAbertura($data_inicio, $data_fim)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = true;

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
        $doc->formatOutput = false;
        $doc->preserveWhiteSpace = true;

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

    /**
     * Gera o XML do lote de eventos como uma STRING.
     * Espera que os eventos já sejam strings XML assinadas.
     *
     * @param array $eventos Array de eventos, ex: [['id' => 'ID0', 'xml' => '<eFinanceira...']]
     * @return string O XML completo do lote (sem criptografia).
     * @throws Exception
     */
    public function gerarLoteAssincrono(array $eventos)
    {
        $nsLote = 'http://www.eFinanceira.gov.br/schemas/envioLoteEventosAssincrono/v1_0_0';

        // 1. Inicia a string do XML com a estrutura do lote (como no exemplo)
        // Adiciona os atributos xsi e xsd que faltavam
        $xmlString = '<?xml version="1.0" encoding="utf-8"?>' .
            '<eFinanceira xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="' . $nsLote . '">' .
            '<loteEventosAssincrono>' .
            '<cnpjDeclarante>' . $this->cnpjEPP . '</cnpjDeclarante>' .
            '<eventos>';

        // 2. Loop para injetar as strings dos eventos
        foreach ($eventos as $ev) {
            if (!isset($ev['id'], $ev['xml']) || !is_string($ev['xml'])) {
                throw new Exception('O evento ' . ($ev['id'] ?? '') . ' não foi passado como uma string XML.');
            }

            // 3. Anexa o evento
            // Isso injeta a string <eFinanceira... do evento dentro da tag <evento>
            $xmlString .= '<evento id="' . $ev['id'] . '">';
            $xmlString .= $ev['xml']; // Injeta a string do evento assinado aqui
            $xmlString .= '</evento>';
        }

        // 4. Fecha as tags do lote
        $xmlString .= '</eventos>' .
            '</loteEventosAssincrono>' .
            '</eFinanceira>';

        // 5. Retorna a string XML completa
        return $xmlString;
    }



    private function obterTagEventoAssinar(DOMElement $eventoElement)
    {
        // Lista de tags de eventos da e-Financeira
        $tiposEventos = [
            'evtCadDeclarante',
            'evtAberturaeFinanceira',
            'evtCadIntermediario',
            'evtCadPatrocinado',
            'evtExclusaoeFinanceira',
            'evtExclusao',
            'evtFechamentoeFinanceira',
            'evtMovOpFin',
            'evtMovPP'
        ];

        $xml = $eventoElement->ownerDocument->saveXML($eventoElement);

        foreach ($tiposEventos as $tipo) {
            if (strpos($xml, $tipo) !== false) {
                return $tipo;
            }
        }

        return null;
    }


    private function buscarElementoEventoPorTag(DOMElement $eventoElement, $tagEvento)
    {
        // Busca o elemento por nome local (ignora namespace)
        $xpath = new DOMXPath($eventoElement->ownerDocument);

        // Procura em qualquer namespace: //*[local-name()='evtCadDeclarante' and @id]
        $query = sprintf(".//*[local-name()='%s' and @id]", $tagEvento);
        $resultado = $xpath->query($query, $eventoElement);

        if ($resultado->length > 0) {
            return $resultado->item(0);
        }

        return null;
    }


    private function buscarElementoEFinanceira(DOMElement $eventoElement)
    {
        // Busca o elemento <eFinanceira> dentro do <evento>
        // Pode ter qualquer namespace ou nenhum
        $xpath = new DOMXPath($eventoElement->ownerDocument);
        $query = ".//*[local-name()='eFinanceira']";
        $resultado = $xpath->query($query, $eventoElement);

        if ($resultado->length > 0) {
            return $resultado->item(0);
        }

        return null;
    }


    public function assinarLoteEventos($xml)
    {
        $senha = $this->senhaCertificado;

        $ch = curl_init('http://assinador:5000/assinar');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'xml'  => $xml,
            'senha' => $senha
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resposta = curl_exec($ch);
        curl_close($ch);

        if ($resposta === false) {
            throw new Exception("Falha ao chamar serviço de assinatura");
        }

        return $resposta;
    }

    public function criptografarLoteEF($xmlConteudo, $idLote)
    {
        // CORREÇÃO CRÍTICA: Converter para string SEM modificações
        if ($xmlConteudo instanceof DOMDocument) {
            // Garantir configurações antes de salvar
            $xmlConteudo->preserveWhiteSpace = true;
            $xmlConteudo->formatOutput = false;

            // Usar saveXML() sem parâmetros para pegar o documento inteiro
            // EXATAMENTE como foi assinado
            $xmlString = $xmlConteudo->saveXML();

            // ALTERNATIVA MAIS SEGURA: usar C14N (mesmo método da assinatura)
            // $xmlString = $xmlConteudo->C14N(false, false);
        } else {
            $xmlString = $xmlConteudo;
        }

        // 1. Gerar chave AES-128 e IV (vetor inicialização)
        $chaveAES = random_bytes(16); // 128 bits
        $iv = random_bytes(16);       // CBC precisa de IV 16 bytes

        // 2. Encriptar XML com AES-128-CBC
        $xmlCriptografado = openssl_encrypt(
            $xmlString,
            'AES-128-CBC',
            $chaveAES,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($xmlCriptografado === false) {
            throw new Exception("Erro ao criptografar XML com AES-128-CBC.");
        }

        // 3. Ler certificado público
        if (!file_exists($this->caminhoCertificadoPublico)) {
            throw new Exception("Arquivo de certificado não encontrado: " . $this->caminhoCertificadoPublico);
        }

        $certPubContent = file_get_contents($this->caminhoCertificadoPublico);
        if (!$certPubContent) {
            throw new Exception("Não foi possível ler o certificado público.");
        }

        // Verificar se não é HTML
        if (strpos($certPubContent, '<html') !== false) {
            throw new Exception("O arquivo contém HTML. Baixe novamente o certificado.");
        }

        // Thumbprint SHA1 do certificado (idCertificado)
        $idCertificado = strtoupper(hash('sha1', $certPubContent));

        // Converter DER para PEM
        $certPEM = "-----BEGIN CERTIFICATE-----\n";
        $certPEM .= chunk_split(base64_encode($certPubContent), 64, "\n");
        $certPEM .= "-----END CERTIFICATE-----\n";

        // Carregar certificado
        $certX509 = openssl_x509_read($certPEM);
        if (!$certX509) {
            throw new Exception("Não foi possível ler o certificado X.509.");
        }

        // Extrair chave pública
        $certPub = openssl_pkey_get_public($certX509);
        if (!$certPub) {
            throw new Exception("Não foi possível extrair a chave pública.");
        }

        // 4. Concatenar CHAVE + VETOR
        $chaveConcatenada = $chaveAES . $iv; // 16 + 16 = 32 bytes

        // 5. Encriptar com RSA (PKCS#1 v1.5)
        $chaveCriptografada = '';
        if (!openssl_public_encrypt($chaveConcatenada, $chaveCriptografada, $certPub, OPENSSL_PKCS1_PADDING)) {
            throw new Exception("Erro ao criptografar chave com RSA.");
        }

        // 6. Converter para Base64
        $xmlCriptografadoBase64 = base64_encode($xmlCriptografado);
        $chaveCriptografadaBase64 = base64_encode($chaveCriptografada);

        // 7. Montar XML final (ESTE pode ser formatado, não tem assinatura)
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = true;

        $ns = 'http://www.eFinanceira.gov.br/schemas/envioLoteCriptografado/v1_2_0';
        $eFinanceira = $dom->createElementNS($ns, 'eFinanceira');
        $eFinanceira->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );
        $dom->appendChild($eFinanceira);

        $loteElem = $dom->createElement('loteCriptografado');
        $eFinanceira->appendChild($loteElem);

        $loteElem->appendChild($dom->createElementNS($ns, 'id', $idLote));
        $loteElem->appendChild($dom->createElementNS($ns, 'idCertificado', $idCertificado));
        $loteElem->appendChild($dom->createElementNS($ns, 'chave', $chaveCriptografadaBase64));
        $loteElem->appendChild($dom->createElementNS($ns, 'lote', $xmlCriptografadoBase64));

        return $dom;
    }

    public function enviarLoteEFinanceira($xmlLoteCriptografado, $usarGzip = false, $producao = false)
    {
        // Definir endpoint
        if ($producao) {
            $urlBase = 'https://efinanceira.receita.fazenda.gov.br/recepcao/lotes/';
        } else {
            $urlBase = 'https://pre-efinanceira.receita.fazenda.gov.br/recepcao/lotes/';
        }

        $endpoint = $urlBase . ($usarGzip ? 'criptoGzip' : 'cripto');

        // CORREÇÃO: Obter XML como string SEM modificações
        if ($xmlLoteCriptografado instanceof DOMDocument) {
            $xmlLoteCriptografado->preserveWhiteSpace = true;
            $xmlLoteCriptografado->formatOutput = false;
            $xmlString = $xmlLoteCriptografado->saveXML();
        } else {
            $xmlString = $xmlLoteCriptografado;
        }

        // Configurar cURL com autenticação mútua TLS
        $ch = curl_init($endpoint);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xmlString,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/xml',
                'Content-Length: ' . strlen($xmlString)
            ],

            // Autenticação mútua TLS
            CURLOPT_SSLCERT => $this->certificado_privado_epp,
            CURLOPT_SSLKEY => $this->chave_privada_epp,
            CURLOPT_SSLCERTPASSWD => $this->senhaCertificado,

            // Segurança TLS
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,

            // Timeout
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 30,

            // Debug (remova em produção)
            CURLOPT_VERBOSE => true
        ]);

        // Executar requisição
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        // Verificar erros de conexão
        if ($response === false) {
            throw new Exception("Erro na conexão com e-Financeira: " . $curlError);
        }

        // Processar resposta
        return $response;
    }

    public function consultarLoteEFinanceira($numeroLote, $producao = false)
    {
        // Limpar o número (apenas dígitos)

        if (empty($numeroLote)) {
            throw new Exception("Número de protocolo inválido: vazio");
        }

        //echo "Consultando protocolo de lote: $numeroLote\n";

        // Definir endpoint CORRETO
        if ($producao) {
            $endpoint = "https://efinanceira.receita.fazenda.gov.br/consulta/lotes/{$numeroLote}";
        } else {
            $endpoint = "https://pre-efinanceira.receita.fazenda.gov.br/consulta/lotes/{$numeroLote}";
        }

        //echo "Endpoint: $endpoint\n";

        // IMPORTANTE: Aguardar pelo menos 30 segundos após envio
        // conforme recomendação da documentação

        // Verificar certificado
        if (!file_exists($this->certificado_privado_epp)) {
            throw new Exception("Certificado A1 não encontrado: " . $this->certificado_privado_epp);
        }

        $ch = curl_init($endpoint);

        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/xml',
            ],

            // Autenticação mútua TLS
            CURLOPT_SSLCERT => $this->certificado_privado_epp,
            CURLOPT_SSLCERTTYPE => 'PEM',

            // Segurança TLS
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,

            // Timeout
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 30,
        ];

        // Se usar chave privada separada
        if ($this->chave_privada_epp !== null) {
            $curlOptions[CURLOPT_SSLKEY] = $this->chave_privada_epp;
            $curlOptions[CURLOPT_SSLKEYTYPE] = 'PEM';
        }

        // Se tiver senha
        if (!empty($this->senhaCertificado)) {
            $curlOptions[CURLOPT_SSLCERTPASSWD] = $this->senhaCertificado;
            $curlOptions[CURLOPT_SSLKEYPASSWD] = $this->senhaCertificado;
        }

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new Exception("Erro na conexão: " . $curlError);
        }

        //return $this->processarRespostaConsulta($response, $httpCode);
        return $response;
    }
    /**
     * Valida TODAS as assinaturas dentro de um XML de lote assinado.
     * Isso verifica se a assinatura é válida do ponto de vista do PHP (xmlseclibs).
     *
     * @param DOMDocument|string $xmlAssinado O DOM ou string XML do lote JÁ ASSINADO.
     * @return array Um array com os resultados da validação de cada evento.
     * @throws Exception
     */
    public function validarLoteAssinado($xmlAssinado)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        if ($xmlAssinado instanceof DOMDocument) {
            // Se já é DOM, apenas usamos
            $dom = $xmlAssinado;
        } elseif (is_string($xmlAssinado)) {
            // Se for string, carregamos
            if (file_exists($xmlAssinado)) {
                $xmlAssinado = file_get_contents($xmlAssinado);
            }
            if (!$dom->loadXML($xmlAssinado)) {
                throw new Exception("Falha ao carregar XML para validação.");
            }
        } else {
            throw new Exception("Entrada inválida. Esperado DOMDocument ou string XML.");
        }

        $xpath = new DOMXPath($dom);
        // Namespace do Lote
        $xpath->registerNamespace('efLote', 'http://www.eFinanceira.gov.br/schemas/envioLoteEventosAssincrono/v1_0_0');
        // Namespace da Assinatura (que agora não tem prefixo, mas o XPath precisa)
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        // Encontra todos os eventos
        $eventos = $xpath->query('//efLote:evento');

        if ($eventos->length === 0) {
            return ["status" => "erro", "mensagem" => "Nenhum evento <evento> encontrado no lote."];
        }

        $resultados = [];
        foreach ($eventos as $eventoNode) {
            $idEvento = $eventoNode->getAttribute('id');

            // Encontra a assinatura DENTRO deste evento
            $assinaturaNode = $xpath->query('.//ds:Signature', $eventoNode)->item(0);

            if (!$assinaturaNode) {
                $resultados[$idEvento] = "FALHA: Evento não contém nó <Signature>.";
            } else {
                // Chama o helper de validação
                $resultados[$idEvento] = $this->validarAssinaturaNode($assinaturaNode);
            }
        }

        return ["status" => "concluido", "resultados" => $resultados];
    }


    /**
     * Helper que valida um único nó <Signature> usando xmlseclibs.
     *
     * @param DOMElement $signatureNode O nó <Signature> a ser validado.
     * @return string "VÁLIDA" ou uma mensagem de erro.
     */
    private function validarAssinaturaNode(DOMElement $signatureNode)
    {
        try {
            // 1. Cria o objeto de segurança e localiza a assinatura
            $objXMLSecDSig = new XMLSecurityDSig('');
            $objDSig = $objXMLSecDSig->locateSignature($signatureNode);

            if (!$objDSig) {
                return "FALHA: Nó <Signature> não pôde ser processado por xmlseclibs.";
            }

            // 2. Validar o Hash (DigestValue) - a causa do MS0017
            // Isso recalcula o hash do evento e compara com o DigestValue
            $objXMLSecDSig->canonicalizeSignedInfo();
            if (!$objXMLSecDSig->validateReference()) {
                return "FALHA: Referência (DigestValue) inválida! O hash C14N não bate.";
            }

            // 3. Localizar a Chave (Certificado)
            $objKey = $objXMLSecDSig->locateKey();
            if (!$objKey) {
                return "FALHA: Chave X509Certificate não encontrada na assinatura.";
            }

            // 4. Carregar a Chave Pública do Certificado
            $x509Cert = $objXMLSecDSig->keyInfo[XMLSecurityKey::X509_CERTIFICATE_NODE]->textContent;
            $objKey->loadKey($x509Cert, false, true); // (content, isFile=false, isCert=true)

            // 5. Verificar a Assinatura (SignatureValue)
            // Isso usa a chave pública para descriptografar a assinatura
            if ($objXMLSecDSig->verify($objKey) !== 1) {
                return "FALHA: Verificação da assinatura (SignatureValue) falhou.";
            }

            // Se chegou aqui, está válida
            return "VÁLIDA";
        } catch (Exception $e) {
            return "EXCEÇÃO: " . $e->getMessage();
        }
    }
}
