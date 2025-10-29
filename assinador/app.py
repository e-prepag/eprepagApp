# app.py (atualizado com melhorias)
from flask import Flask, request, Response
from assinar import assinar_lote_eventos
import traceback

app = Flask(__name__)

@app.route("/assinar", methods=["POST"])
def assinar():
    """Endpoint para assinar XML e-Financeira"""
    
    # Aceitar XML via form-data ou raw body
    if request.content_type and 'multipart/form-data' in request.content_type:
        xml = request.form.get("xml")
        senha = request.form.get("senha")
    elif request.content_type and 'application/json' in request.content_type:
        data = request.get_json()
        xml = data.get("xml")
        senha = data.get("senha")
    else:
        # Raw XML no body
        xml = request.data.decode('utf-8')
        senha = request.headers.get("X-Certificate-Password")
    
    if not xml or not senha:
        return Response(
            '{"erro": "Parâmetros inválidos. Envie xml e senha."}',
            status=400,
            mimetype="application/json"
        )

    pfx_path = "/certs/cert-eprepag.pfx"
    
    try:
        xml_assinado = assinar_lote_eventos(xml, pfx_path, senha)
        
        return Response(
            xml_assinado,
            mimetype="application/xml; charset=utf-8",
            headers={
                "Content-Disposition": "attachment; filename=lote_assinado.xml"
            }
        )
        
    except FileNotFoundError as e:
        return Response(
            f'{{"erro": "Certificado não encontrado: {str(e)}"}}',
            status=500,
            mimetype="application/json"
        )
    except Exception as e:
        error_detail = traceback.format_exc()
        print(f"Erro ao assinar:\n{error_detail}")
        
        return Response(
            f'{{"erro": "Erro ao assinar", "detalhes": "{str(e)}"}}',
            status=500,
            mimetype="application/json"
        )

@app.route("/health", methods=["GET"])
def health():
    """Health check endpoint"""
    return Response('{"status": "ok"}', mimetype="application/json")

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=False)