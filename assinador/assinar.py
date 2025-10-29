# assinar.py
import os
from lxml import etree
from signxml import XMLSigner, methods
from cryptography.hazmat.primitives.serialization import pkcs12, Encoding, PrivateFormat, NoEncryption

# Tipos de eventos e-Financeira
TIPOS_EVENTOS = [
    "evtCadDeclarante",
    "evtAberturaeFinanceira",
    "evtCadIntermediario",
    "evtCadPatrocinado",
    "evtExclusaoeFinanceira",
    "evtExclusao",
    "evtFechamentoeFinanceira",
    "evtMovOpFin",
    "evtMovPP"
]

def obter_tag_evento_assinar(evento_node: etree._Element) -> str:
    """
    Identifica qual tipo de evento está presente no nó.
    Retorna o nome da tag do evento específico (ex: evtMovOpFin)
    """
    evento_xml = etree.tostring(evento_node, encoding='unicode')
    
    for tipo_evento in TIPOS_EVENTOS:
        if tipo_evento in evento_xml:
            return tipo_evento
    
    return None

def assinar_lote_eventos(xml_input: str, pfx_path: str, pfx_password: str) -> bytes:
    """
    Assina cada evento em um lote e-Financeira seguindo o padrão da Receita Federal.
    
    Args:
        xml_input: String XML ou caminho para arquivo XML
        pfx_path: Caminho para o certificado .pfx/.p12
        pfx_password: Senha do certificado
        
    Returns:
        XML assinado em bytes (UTF-8)
    """
    
    # 1) Carregar XML (string ou caminho de arquivo)
    if os.path.isfile(xml_input):
        with open(xml_input, 'rb') as f:
            xml_str = f.read()
    else:
        xml_str = xml_input.encode('utf-8') if isinstance(xml_input, str) else xml_input

    # Parser que preserva espaços em branco (CRÍTICO para assinatura)
    parser = etree.XMLParser(
        remove_blank_text=False,
        strip_cdata=False,
        resolve_entities=False
    )
    xml_doc = etree.fromstring(xml_str, parser)

    # 2) Extrair chave privada + certificado do PFX usando cryptography
    with open(pfx_path, 'rb') as f:
        pfx_data = f.read()
    
    # Carregar PKCS12
    private_key, certificate, additional_certs = pkcs12.load_key_and_certificates(
        pfx_data,
        pfx_password.encode('utf-8')
    )
    
    # Converter para PEM
    private_key_pem = private_key.private_bytes(
        encoding=Encoding.PEM,
        format=PrivateFormat.TraditionalOpenSSL,
        encryption_algorithm=NoEncryption()
    )
    
    cert_pem = certificate.public_bytes(Encoding.PEM)

    # 3) Buscar todos os eventos no lote
    NS = {"ef": "http://www.eFinanceira.gov.br/schemas/envioLoteEventosAssincrono/v1_0_0"}
    eventos = xml_doc.xpath("//ef:loteEventos/ef:evento", namespaces=NS)
    
    if not eventos:
        raise Exception("Nenhum evento encontrado no lote.")

    # 4) Assinar cada evento do lote
    for evento_node in eventos:
        # Criar um XmlDocument para o evento (igual ao C#)
        evento_xml_doc = etree.fromstring(
            etree.tostring(evento_node),
            parser
        )
        
        # Identificar o tipo de evento
        tag_evento_assinar = obter_tag_evento_assinar(evento_xml_doc)
        
        if not tag_evento_assinar:
            raise Exception(f"Tipo de evento inválido para e-Financeira")
        
        # Assinar o XML do evento
        evento_assinado = assinar_xml_evento(
            evento_xml_doc,
            private_key_pem,
            cert_pem,
            tag_evento_assinar
        )
        
        if evento_assinado is None:
            raise Exception(f"Erro ao assinar evento tipo '{tag_evento_assinar}'")
        
        # Substituir o InnerXml do evento (igual ao C#: node.InnerXml = xmlDocEventoAssinado.InnerXml)
        # Remove todos os filhos do evento original
        for child in evento_node:
            evento_node.remove(child)
        
        # Adiciona os filhos do evento assinado
        for child in evento_assinado:
            evento_node.append(child)

    # 5) Retornar XML assinado (sem formatação, preservando estrutura original)
    return etree.tostring(
        xml_doc,
        encoding='utf-8',
        xml_declaration=True,
        pretty_print=False
    )


def assinar_xml_evento(evento_xml_doc: etree._Element, private_key_pem: bytes, 
                       cert_pem: bytes, tag_evento_assinar: str) -> etree._Element:
    """
    Assina um evento específico da e-Financeira.
    
    Equivalente ao método AssinarXmlEvento do C#.
    
    Args:
        evento_xml_doc: Elemento XML do evento
        private_key_pem: Chave privada em formato PEM
        cert_pem: Certificado em formato PEM
        tag_evento_assinar: Nome da tag do evento (ex: evtMovOpFin)
        
    Returns:
        Elemento XML assinado
    """
    try:
        # Encontrar o elemento do evento específico (igual ao GetElementsByTagName)
        elementos_evento = evento_xml_doc.xpath(f".//*[local-name()='{tag_evento_assinar}']")
        
        if not elementos_evento:
            raise Exception(f"Elemento '{tag_evento_assinar}' não encontrado no evento")
        
        elemento_para_assinar = elementos_evento[0]
        
        # Obter o ID do elemento
        evento_id = elemento_para_assinar.get("id")
        if not evento_id:
            raise Exception(f"Elemento '{tag_evento_assinar}' não possui atributo 'id'")
        
        # Remover assinaturas existentes
        for sig in evento_xml_doc.xpath(".//ds:Signature", 
                                       namespaces={"ds": "http://www.w3.org/2000/09/xmldsig#"}):
            sig.getparent().remove(sig)
        
        # Criar signer com as mesmas configurações do C#
        signer = XMLSigner(
            method=methods.enveloped,
            signature_algorithm="rsa-sha256",  # SIGNATURE_METHOD
            digest_algorithm="sha256",          # DIGEST_METHOD
            c14n_algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"
        )

        # Assinar o elemento específico
        elemento_assinado = signer.sign(
            elemento_para_assinar,
            key=private_key_pem,
            cert=cert_pem,
            reference_uri=f"#{evento_id}"
        )
        
        # Extrair o nó Signature gerado
        signature_node = elemento_assinado.find(".//{http://www.w3.org/2000/09/xmldsig#}Signature")
        
        if signature_node is None:
            raise Exception("Assinatura não foi gerada corretamente")
        
        # Adicionar a assinatura como filho do elemento pai (eFinanceira)
        # Igual ao C#: xmlDocEvento.GetElementsByTagName(tagEventoParaAssinar)[0].ParentNode.AppendChild(xmlElementAssinado)
        parent_node = elemento_para_assinar.getparent()
        if parent_node is None:
            raise Exception("Elemento pai não encontrado")
        
        parent_node.append(signature_node)
        
        # Retornar o documento do evento (preservando whitespace)
        return evento_xml_doc
        
    except Exception as e:
        print(f"Falha ao assinar xml evento '{tag_evento_assinar}': {str(e)}")
        return None
