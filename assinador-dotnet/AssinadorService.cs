using System;
using System.IO;
using System.Linq;
using System.Security.Cryptography;
using System.Security.Cryptography.X509Certificates;
using System.Security.Cryptography.Xml;
using System.Text;
using System.Threading.Tasks;
using System.Xml;

public class AssinadorService
{
    private const string SIGNATURE_METHOD = "http://www.w3.org/2001/04/xmldsig-more#rsa-sha256";
    private const string DIGEST_METHOD = "http://www.w3.org/2001/04/xmlenc#sha256";
    private const string ATRIBUTO_ID = "id";

    public async Task<string> AssinarLoteEventos(string xmlContent, string pfxPath, string pfxPassword)
    {
        return await Task.Run(() =>
        {
            Console.WriteLine($"[Assinador] Carregando certificado: {pfxPath}");

            if (!File.Exists(pfxPath))
            {
                throw new FileNotFoundException($"Certificado não encontrado: {pfxPath}");
            }

            X509Certificate2 certificado;
            try
            {
                certificado = new X509Certificate2(pfxPath, pfxPassword);
            }
            catch (Exception ex)
            {
                throw new Exception($"Erro ao carregar certificado: {ex.Message}", ex);
            }

            if (!certificado.HasPrivateKey)
            {
                throw new Exception("Certificado não possui chave privada");
            }

            Console.WriteLine($"[Assinador] Certificado: {certificado.Subject}");

            XmlDocument xmlDoc = new XmlDocument();
            try
            {
                xmlDoc.LoadXml(xmlContent);
            }
            catch (Exception ex)
            {
                throw new Exception($"Erro ao carregar XML: {ex.Message}", ex);
            }

            XmlNamespaceManager nsmgr = new XmlNamespaceManager(xmlDoc.NameTable);
            nsmgr.AddNamespace("ef", xmlDoc.DocumentElement?.NamespaceURI ?? "");
            XmlNodeList? eventos = xmlDoc.SelectNodes("//ef:loteEventosAssincrono/ef:eventos/ef:evento", nsmgr);

            if (eventos == null || eventos.Count == 0)
            {
                throw new Exception("Nenhum evento encontrado no XML");
            }

            Console.WriteLine($"[Assinador] Encontrados {eventos.Count} evento(s)");

            int contador = 1;
            foreach (XmlNode eventoNode in eventos)
            {
                Console.WriteLine($"[Assinador] Assinando evento {contador}/{eventos.Count}");

                XmlDocument xmlDocEvento = new XmlDocument();
                xmlDocEvento.LoadXml(eventoNode.InnerXml);

                string? tagEvento = ObterTagEvento(xmlDocEvento);
                if (string.IsNullOrWhiteSpace(tagEvento))
                {
                    throw new Exception($"Tipo de evento não identificado no evento {contador}");
                }

                Console.WriteLine($"[Assinador]   Tipo: {tagEvento}");

                XmlDocument? xmlEventoAssinado = AssinarXmlEvento(xmlDocEvento, certificado, tagEvento);

                if (xmlEventoAssinado == null)
                {
                    throw new Exception($"Erro ao assinar evento {contador}");
                }

                eventoNode.InnerXml = xmlEventoAssinado.InnerXml;

                Console.WriteLine($"[Assinador]   ✓ Evento {contador} assinado");
                contador++;
            }

            // --- CORREÇÃO: Bloco para forçar a serialização para UTF-8 ---
            using (var ms = new MemoryStream())
            {
                // Configurações do XmlWriter: UTF-8 SEM BOM, sem indentação, com declaração XML
                var settings = new XmlWriterSettings
                {
                    Encoding = new UTF8Encoding(false), // UTF8 SEM Byte Order Mark (BOM)
                    Indent = false,                     // Sem indentação (importante para RFB)
                    OmitXmlDeclaration = false          // Inclui a declaração <?xml ...?>
                };

                using (var writer = XmlWriter.Create(ms, settings))
                {
                    xmlDoc.Save(writer);
                }

                // Retorna o XML serializado como uma string UTF-8
                return Encoding.UTF8.GetString(ms.ToArray());
            }
            // --- FIM DA CORREÇÃO ---
        });
    }

    private string? ObterTagEvento(XmlDocument doc)
    {
        if (doc.OuterXml.Contains("evtCadDeclarante")) return "evtCadDeclarante";
        if (doc.OuterXml.Contains("evtAberturaeFinanceira")) return "evtAberturaeFinanceira";
        if (doc.OuterXml.Contains("evtCadIntermediario")) return "evtCadIntermediario";
        if (doc.OuterXml.Contains("evtCadPatrocinado")) return "evtCadPatrocinado";
        if (doc.OuterXml.Contains("evtExclusaoeFinanceira")) return "evtExclusaoeFinanceira";
        if (doc.OuterXml.Contains("evtExclusao")) return "evtExclusao";
        if (doc.OuterXml.Contains("evtFechamentoeFinanceira")) return "evtFechamentoeFinanceira";
        if (doc.OuterXml.Contains("evtMovOpFin")) return "evtMovOpFin";
        if (doc.OuterXml.Contains("evtMovPP")) return "evtMovPP";
        return null;
    }

    private XmlDocument? AssinarXmlEvento(XmlDocument xmlDocEvento, X509Certificate2 certificado, string tagEvento)
    {
        try
        {
            XmlNodeList? nodeParaAssinatura = xmlDocEvento.GetElementsByTagName(tagEvento);
            if (nodeParaAssinatura == null || nodeParaAssinatura.Count == 0)
            {
                throw new Exception($"Elemento '{tagEvento}' não encontrado");
            }

            var elemento = nodeParaAssinatura[0] as XmlElement;
            if (elemento == null)
            {
                throw new Exception($"Elemento '{tagEvento}' não é XmlElement");
            }

            // CRÍTICO: Inicializa com o elemento a ser referenciado (o que tem o ID)
            SignedXml signedXml = new SignedXml(elemento); 
            signedXml.SignedInfo.SignatureMethod = SIGNATURE_METHOD;

            using (RSA? chavePrivada = certificado.GetRSAPrivateKey())
            {
                if (chavePrivada == null)
                {
                    throw new Exception("Não foi possível obter chave privada");
                }

                signedXml.SigningKey = chavePrivada;

                var idAttr = elemento.Attributes?[ATRIBUTO_ID];
                if (idAttr == null)
                {
                    throw new Exception($"Atributo 'id' não encontrado em '{tagEvento}'");
                }

                Reference reference = new Reference("#" + idAttr.Value);
                reference.AddTransform(new XmlDsigEnvelopedSignatureTransform(false));
                reference.AddTransform(new XmlDsigC14NTransform(false));
                reference.DigestMethod = DIGEST_METHOD;
                signedXml.AddReference(reference);

                KeyInfo keyInfo = new KeyInfo();
                keyInfo.AddClause(new KeyInfoX509Data(certificado));
                signedXml.KeyInfo = keyInfo;

                signedXml.ComputeSignature();

                XmlElement xmlElementAssinado = signedXml.GetXml();
                
                // CRÍTICO: Anexa a assinatura no elemento PAI (<eFinanceira>), não no elemento assinado.
                var nodesPorTag = xmlDocEvento.GetElementsByTagName(tagEvento);
                if (nodesPorTag.Count > 0 && nodesPorTag[0]?.ParentNode != null)
                {
                    nodesPorTag[0].ParentNode.AppendChild(xmlElementAssinado);
                }

                XmlDocument xmlAssinado = new XmlDocument();
                xmlAssinado.PreserveWhitespace = true;
                xmlAssinado.LoadXml(xmlDocEvento.OuterXml);

                return xmlAssinado;
            }
        }
        catch (Exception ex)
        {
            Console.WriteLine($"[Assinador] ERRO: {ex.Message}");
            throw;
        }
    }
}