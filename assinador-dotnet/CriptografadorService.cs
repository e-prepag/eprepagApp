using System;
using System.IO;
using System.Linq;
using System.Security.Cryptography;
using System.Security.Cryptography.X509Certificates;
using System.Text;
using System.Xml;
using System.Xml.Linq;

public class CriptografadorService
{
    /// <summary>
    /// Criptografa um lote XML assinado conforme o padrão da e-Financeira (AES + RSA).
    /// </summary>
    /// <param name="xmlAssinado">Conteúdo do XML assinado (string).</param>
    /// <param name="certServidorPath">Caminho para o certificado público da RFB (.cer ou .pem).</param>
    /// <returns>XML final criptografado como string UTF-8.</returns>
    public string CriptografarLoteEF(string xmlAssinado, string certServidorPath)
    {
        // === 1. Carregar e validar certificado público do servidor ===
        if (!File.Exists(certServidorPath))
            throw new FileNotFoundException($"Certificado do servidor não encontrado: {certServidorPath}");

        var certBytes = File.ReadAllBytes(certServidorPath);
        X509Certificate2 certServidor;

        try
        {
            certServidor = new X509Certificate2(certBytes); // tenta DER
        }
        catch
        {
            string pem = Encoding.UTF8.GetString(certBytes);
            if (pem.Contains("-----BEGIN CERTIFICATE-----"))
                certServidor = new X509Certificate2(Convert.FromBase64String(
                    pem.Replace("-----BEGIN CERTIFICATE-----", "")
                       .Replace("-----END CERTIFICATE-----", "")
                       .Replace("\r", "")
                       .Replace("\n", "")
                ));
            else
                throw new Exception("Formato de certificado inválido. Esperado .cer (DER) ou .pem (PEM).");
        }

        // === 2. Gerar chave e vetor AES 128 bits ===
        byte[] chaveAES = GerarBytesAleatorios(16);
        byte[] vetorAES = GerarBytesAleatorios(16);

        // === 3. Criptografar XML com AES CBC + PKCS7 ===
        byte[] xmlBytes = Encoding.UTF8.GetBytes(xmlAssinado);
        byte[] xmlCriptografado;

        using (var aes = Aes.Create())
        {
            aes.Key = chaveAES;
            aes.IV = vetorAES;
            aes.Mode = CipherMode.CBC;
            aes.Padding = PaddingMode.PKCS7;

            using var encryptor = aes.CreateEncryptor();
            xmlCriptografado = encryptor.TransformFinalBlock(xmlBytes, 0, xmlBytes.Length);
        }

        string xmlLoteCriptografadoBase64 = Convert.ToBase64String(xmlCriptografado);

        // === 4. Calcular SHA1 do certificado (thumbprint) ===
        using var sha1 = SHA1.Create();
        string idCertificado = BitConverter.ToString(sha1.ComputeHash(certServidor.RawData)).Replace("-", "");

        // === 5. Concatenar chave + vetor e criptografar com RSA ===
        byte[] chaveConcatenada = chaveAES.Concat(vetorAES).ToArray();
        byte[] chaveCriptografada;

        using (RSA rsa = certServidor.GetRSAPublicKey())
        {
            chaveCriptografada = rsa.Encrypt(chaveConcatenada, RSAEncryptionPadding.Pkcs1);
        }

        string chaveLoteCriptografadoBase64 = Convert.ToBase64String(chaveCriptografada);

        // === 6. Montar XML final ===
        XNamespace ns = "http://www.eFinanceira.gov.br/schemas/envioLoteCriptografado/v1_2_0";

        var xmlFinal = new XDocument(
            new XDeclaration("1.0", "utf-8", null),
            new XElement(ns + "eFinanceira",
                new XAttribute(XNamespace.Xmlns + "xsi", "http://www.w3.org/2001/XMLSchema-instance"),
                new XAttribute(XNamespace.Xmlns + "xsd", "http://www.w3.org/2001/XMLSchema"),
                new XElement(ns + "loteCriptografado",
                    new XElement(ns + "id", Guid.NewGuid().ToString()),
                    new XElement(ns + "idCertificado", idCertificado),
                    new XElement(ns + "chave", chaveLoteCriptografadoBase64),
                    new XElement(ns + "lote", xmlLoteCriptografadoBase64)
                )
            )
        );

        return xmlFinal.Declaration + xmlFinal.ToString(SaveOptions.DisableFormatting);
    }

    private static byte[] GerarBytesAleatorios(int tamanho)
    {
        byte[] bytes = new byte[tamanho];
        using var rng = RandomNumberGenerator.Create();
        rng.GetBytes(bytes);
        return bytes;
    }
}
