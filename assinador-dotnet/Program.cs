using Microsoft.AspNetCore.Builder;
using Microsoft.AspNetCore.Http;

var builder = WebApplication.CreateBuilder(args);

// Adicionar serviço de assinatura
builder.Services.AddSingleton<AssinadorService>();
builder.Services.AddSingleton<CriptografadorService>();

// Configurar CORS
builder.Services.AddCors(options =>
{
    options.AddDefaultPolicy(policy =>
    {
        policy.AllowAnyOrigin()
              .AllowAnyMethod()
              .AllowAnyHeader();
    });
});

var app = builder.Build();

app.UseCors();

// Health check
app.MapGet("/health", () => Results.Ok(new { status = "ok", timestamp = DateTime.UtcNow }));

// Endpoint principal
app.MapPost("/assinar", async (HttpRequest request, AssinadorService assinador) =>
{
    try
    {
        Console.WriteLine($"[{DateTime.Now:yyyy-MM-dd HH:mm:ss}] Nova requisição de assinatura");

        var form = await request.ReadFormAsync();
        var xml = form["xml"].ToString();
        var senha = form["senha"].ToString();
        var pfxPath = "/certs/cert-eprepag.pfx";

        if (string.IsNullOrWhiteSpace(xml))
        {
            Console.WriteLine("ERRO: XML não fornecido");
            return Results.BadRequest(new { erro = "XML é obrigatório" });
        }

        if (string.IsNullOrWhiteSpace(senha))
        {
            Console.WriteLine("ERRO: Senha não fornecida");
            return Results.BadRequest(new { erro = "Senha é obrigatória" });
        }

        Console.WriteLine($"Assinando com certificado: {pfxPath}");
        var xmlAssinado = await assinador.AssinarLoteEventos(xml, pfxPath, senha);

        Console.WriteLine("✓ Assinado com sucesso");
        return Results.Content(xmlAssinado, "application/xml; charset=utf-8");
    }
    catch (FileNotFoundException ex)
    {
        Console.WriteLine($"ERRO: {ex.Message}");
        return Results.NotFound(new { erro = "Certificado não encontrado", detalhes = ex.Message });
    }
    catch (Exception ex)
    {
        Console.WriteLine($"ERRO: {ex.Message}");
        Console.WriteLine($"Stack: {ex.StackTrace}");
        return Results.Problem(detail: ex.Message, statusCode: 500);
    }
});

app.MapPost("/criptografar", async (HttpRequest request, CriptografadorService criptografador) =>
{
    try
    {
        Console.WriteLine($"[{DateTime.Now:yyyy-MM-dd HH:mm:ss}] Nova requisição de criptografia");

        var form = await request.ReadFormAsync();
        var xml = form["xml"].ToString();
        var certPath;
        if(form["prod"].ToString() == "true"){
            certPath = "/certs/efinanceira-receita-fazenda-gov-br-2025.cer";
        }else{
            certPath = "/certs/pre-efinanceira-receita-fazenda-gov-br-2025.cer";
        }

        if (string.IsNullOrWhiteSpace(xml))
            return Results.BadRequest(new { erro = "XML é obrigatório" });

        Console.WriteLine($"Criptografando com certificado: {certPath}");
        var xmlCriptografado = criptografador.CriptografarLoteEF(xml, certPath);

        Console.WriteLine("✓ Lote criptografado com sucesso");
        return Results.Content(xmlCriptografado, "application/xml; charset=utf-8");
    }
    catch (Exception ex)
    {
        Console.WriteLine($"ERRO: {ex.Message}");
        return Results.Problem(detail: ex.Message, statusCode: 500);
    }
});


Console.WriteLine("=== Assinador e-Financeira API ===");
Console.WriteLine("Endpoints:");
Console.WriteLine("  GET  /health");
Console.WriteLine("  POST /assinar");
Console.WriteLine("\nServidor rodando em http://0.0.0.0:5000");

app.Run("http://0.0.0.0:5000");