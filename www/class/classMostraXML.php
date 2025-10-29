<?php

/**
 * Formata um XML de forma bonita e legível
 * 
 * @param string|DOMDocument $xml O XML a ser formatado (string ou DOMDocument)
 * @param bool $retornarString Se true, retorna string. Se false, retorna DOMDocument
 * @return string|DOMDocument|false XML formatado ou false em caso de erro
 */
function formatarXML($xml, $retornarString = true)
{
    try {
        $dom = new DOMDocument('1.0', 'UTF-8');

        // Se receber uma string, carrega como XML
        if (is_string($xml)) {
            // Remove espaços em branco desnecessários antes de carregar
            $dom->preserveWhiteSpace = false;

            // Tenta carregar o XML
            if (!$dom->loadXML($xml)) {
                throw new Exception('XML inválido');
            }
        }
        // Se receber um DOMDocument, clona ele
        elseif ($xml instanceof DOMDocument) {
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($xml->saveXML());
        } else {
            throw new Exception('Tipo de entrada inválido. Use string ou DOMDocument.');
        }

        // Ativa a formatação bonita
        $dom->formatOutput = true;

        // Retorna conforme solicitado
        if ($retornarString) {
            return $dom->saveXML();
        } else {
            return $dom;
        }
    } catch (Exception $e) {
        error_log("Erro ao formatar XML: " . $e->getMessage());
        return false;
    }
}


/**
 * Exibe um XML formatado no navegador com syntax highlighting
 * 
 * @param string|DOMDocument $xml O XML a ser exibido
 * @param bool $comHeader Se deve incluir header HTML completo
 */
function exibirXML($xml, $comHeader = true)
{
    $xmlFormatado = formatarXML($xml, true);

    if ($xmlFormatado === false) {
        echo "Erro ao formatar XML";
        return;
    }

    if ($comHeader) {
        echo '
    <div class="xml-container">
        <div class="header">
            <button class="btn-copy" onclick="copiarXML()">📋 Copiar XML</button>
            <h2>📄 Visualizador XML</h2>
        </div>
        <pre id="xml-content">';
    }

    // Aplica syntax highlighting
    $xmlComCores = htmlspecialchars($xmlFormatado);

    // Colore as tags
    $xmlComCores = preg_replace(
        '/(&lt;\/?[a-zA-Z0-9_:-]+)/',
        '<span class="xml-tag">$1</span>',
        $xmlComCores
    );

    // Colore os atributos
    $xmlComCores = preg_replace(
        '/([a-zA-Z0-9_:-]+)=/',
        '<span class="xml-attr">$1</span>=',
        $xmlComCores
    );

    // Colore os valores dos atributos
    $xmlComCores = preg_replace(
        '/=&quot;([^&]*)&quot;/',
        '=<span class="xml-value">&quot;$1&quot;</span>',
        $xmlComCores
    );

    echo $xmlComCores;

    if ($comHeader) {
        echo '</pre>
    </div>
    
    <script>
        function copiarXML() {
            const xmlContent = document.getElementById("xml-content").textContent;
            navigator.clipboard.writeText(xmlContent).then(function() {
                const btn = document.querySelector(".btn-copy");
                const textoOriginal = btn.textContent;
                btn.textContent = "✓ Copiado!";
                btn.classList.add("success");
                setTimeout(function() {
                    btn.textContent = textoOriginal;
                    btn.classList.remove("success");
                }, 2000);
            });
        }
    </script>
';
    }
}
