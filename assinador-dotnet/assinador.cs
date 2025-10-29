// Program.cs
using System;
using System.IO;
using System.Security.Cryptography;
using System.Security.Cryptography.X509Certificates;
using System.Security.Cryptography.Xml;
using System.Xml;

namespace EFinanceiraSignerCLI
{
    class Program
    {
        private const string SIGNATURE_METHOD = @"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256";
        private const string DIGEST_METHOD = @"http://www.w3.org/2001/04/xmlenc#sha256";
        private const string ATRIBUTO_ID = "id";

        static int Main(string[] args)
        {
            Console.WriteLine("=== Assinador e-Financeira CLI ===\n");

            if (args.Length < 3)
            {
                Console.WriteLine("Uso: dotnet run <arquivo.xml> <certificado.pfx> <senha>");
                Console.WriteLine("Exemplo: dotnet run lote.xml cert.pfx minhaSenha");
                return 1;
            }

            string caminhoXml = args[0];
            string caminhoPfx = args[1];
            string senhaPfx = args[2];

            // Validar arquivos
            if (!File.Exists(caminhoXml))
            {
                Console.WriteLine($"ERRO: Arquivo XML não encontrado: {caminhoXml}");
                return 1;
            }

            if (!File.Exists(caminhoPfx))
            {
                Console.WriteLine($"ERRO: Certificado PFX não encontrado: {caminhoPfx}");
                return 1;
            }

            try
            {
                // Registrar algoritmo de assinatura
                CryptoConfig.AddAlgorithm(typeof(RSAPKCS1SHA256SignatureDescription), SIGNATURE_METHOD);

                // Carregar certificado
                Console.WriteLine($"Carregando certificado: {caminhoPfx}");
                X509Certificate2 certificado = new X509Certificate2(caminhoPfx, senhaPfx);

                if (!certificado.HasPrivateKey)
                {
                    Console.WriteLine("ERRO: Certificado não possui chave privada.");
                    return 1;
                }

                Console.WriteLine($"Certificado carregado: {certificado.Subject}");
                Console.WriteLine($"Válido de {certificado.NotBefore} até {certificado.NotAfter}\n");

                // Assinar arquivo
                Console.WriteLine($"Assinando arquivo: {caminhoXml}");
                XmlDocument xmlAssinado = AssinarEventosDoArquivo(caminhoXml, certificado);

                if (xmlAssinado == null)
                {
                    Console.WriteLine("ERRO: Falha ao assinar o arquivo.");
                    return 1;
                }

                // Salvar arquivo assinado
                string caminhoAssinado = Path.Combine(
                    Path.GetDirectoryName(caminhoXml) ?? ".",
                    Path.GetFileNameWithoutExtension(caminhoXml) + "-ASSINADO.xml"
                );

                xmlAssinado.Save(caminhoAssinado);
                Console.WriteLine($"\n? Arquivo assinado com sucesso: {caminhoAssinado}");

                return 0;
            }
            catch (Exception ex)
            {
                Console.WriteLine($"\nERRO: {ex.Message}");
                Console.WriteLine($"Stack Trace:\n{ex.StackTrace}");
                return 1;
            }
        }

        private static string ObtemTagEventoAssinar(XmlDocument arquivo)
        {
            string tipoEvento = null;
            if (arquivo.OuterXml.Contains("evtCadDeclarante")) tipoEvento = "evtCadDeclarante";
            else if (arquivo.OuterXml.Contains("evtAberturaeFinanceira")) tipoEvento = "evtAberturaeFinanceira";
            else if (arquivo.OuterXml.Contains("evtCadIntermediario")) tipoEvento = "evtCadIntermediario";
            else if (arquivo.OuterXml.Contains("evtCadPatrocinado")) tipoEvento = "evtCadPatrocinado";
            else if (arquivo.OuterXml.Contains("evtExclusaoeFinanceira")) tipoEvento = "evtExclusaoeFinanceira";
            else if (arquivo.OuterXml.Contains("evtExclusao")) tipoEvento = "evtExclusao";
            else if (arquivo.OuterXml.Contains("evtFechamentoeFinanceira")) tipoEvento = "evtFechamentoeFinanceira";
            else if (arquivo.OuterXml.Contains("evtMovOpFin")) tipoEvento = "evtMovOpFin";
            else if (arquivo.OuterXml.Contains("evtMovPP")) tipoEvento = "evtMovPP";
            return tipoEvento;
        }

        private static XmlDocument AssinarEventosDoArquivo(string caminhoArquivo, X509Certificate2 certificadoAssinatura)
        {
            // Carrega XML
            XmlDocument arquivoXml = new XmlDocument();
            try
            {
                arquivoXml.Load(caminhoArquivo);
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Não foi possível carregar XML: {ex.Message}");
                return null;
            }

            // Verifica se XML possui eventos
            XmlNamespaceManager nsmgr = new XmlNamespaceManager(arquivoXml.NameTable);
            nsmgr.AddNamespace("eFinanceira", arquivoXml.DocumentElement.NamespaceURI);
            XmlNodeList eventos = arquivoXml.SelectNodes("//eFinanceira:loteEventosAssincrono/eFinanceira:eventos/eFinanceira:evento", nsmgr);

            if (eventos.Count <= 0)
            {
                Console.WriteLine("Não encontrou eventos no arquivo selecionado.");
                return null;
            }

            Console.WriteLine($"Encontrados {eventos.Count} evento(s) para assinar.\n");

            // Assina cada evento do arquivo
            int contador = 1;
            foreach (XmlNode node in eventos)
            {
                Console.WriteLine($"Assinando evento {contador}/{eventos.Count}...");

                XmlDocument xmlDocEvento = new XmlDocument();
                xmlDocEvento.LoadXml(node.InnerXml);

                string tagEventoParaAssinar = ObtemTagEventoAssinar(xmlDocEvento);

                if (string.IsNullOrWhiteSpace(tagEventoParaAssinar))
                {
                    Console.WriteLine($"Tipo Evento inválido para a e-Financeira: '{tagEventoParaAssinar}'");
                    return null;
                }

                Console.WriteLine($"  Tipo: {tagEventoParaAssinar}");

                XmlDocument xmlDocEventoAssinado = AssinarXmlEvento(xmlDocEvento, certificadoAssinatura, tagEventoParaAssinar);

                if (xmlDocEventoAssinado == null)
                {
                    return null;
                }

                node.InnerXml = xmlDocEventoAssinado.InnerXml;

                Console.WriteLine($"  ? Evento {contador} assinado");
                contador++;
            }

            return arquivoXml;
        }

        private static XmlDocument AssinarXmlEvento(XmlDocument xmlDocEvento, X509Certificate2 certificado, string tagEventoParaAssinar)
        {
            try
            {
                XmlNodeList nodeParaAssinatura = xmlDocEvento.GetElementsByTagName(tagEventoParaAssinar);
                SignedXml signedXml = new SignedXml((XmlElement)nodeParaAssinatura[0]);
                signedXml.SignedInfo.SignatureMethod = SIGNATURE_METHOD;

                // Adicionando a chave privada para assinar o documento
                using (RSA chavePrivada = certificado.GetRSAPrivateKey())
                {
                    signedXml.SigningKey = chavePrivada;

                    Reference reference = new Reference("#" + nodeParaAssinatura[0].Attributes[ATRIBUTO_ID].Value);
                    reference.AddTransform(new XmlDsigEnvelopedSignatureTransform(false));
                    reference.AddTransform(new XmlDsigC14NTransform(false));
                    reference.DigestMethod = DIGEST_METHOD;
                    signedXml.AddReference(reference);

                    KeyInfo keyInfo = new KeyInfo();
                    keyInfo.AddClause(new KeyInfoX509Data(certificado));
                    signedXml.KeyInfo = keyInfo;

                    signedXml.ComputeSignature();

                    // Adiciona xml assinatura ao evento
                    XmlElement xmlElementAssinado = signedXml.GetXml();
                    xmlDocEvento.GetElementsByTagName(tagEventoParaAssinar)[0].ParentNode.AppendChild(xmlElementAssinado);

                    XmlDocument xmlAssinado = new XmlDocument();
                    xmlAssinado.PreserveWhitespace = true;
                    xmlAssinado.LoadXml(xmlDocEvento.OuterXml);

                    return xmlAssinado;
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Falha ao assinar xml evento: {ex.Message}");
                return null;
            }
        }
    }
}