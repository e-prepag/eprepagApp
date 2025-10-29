FROM mcr.microsoft.com/dotnet/sdk:8.0 AS build
WORKDIR /src

# Copiar apenas csproj primeiro (cache de restore)
COPY *.csproj ./
RUN dotnet restore

# Copiar resto do código
COPY . ./
RUN dotnet build -c Release -o /app/build
RUN dotnet publish -c Release -o /app/publish

# Runtime image
FROM mcr.microsoft.com/dotnet/aspnet:8.0
WORKDIR /app

# Instalar curl para healthcheck
RUN apt-get update && apt-get install -y curl ca-certificates && rm -rf /var/lib/apt/lists/*

COPY --from=build /app/publish .

RUN mkdir -p /certs

EXPOSE 5000

ENTRYPOINT ["dotnet", "EFinanceiraSignerAPI.dll"]