FROM python:3.12-slim

WORKDIR /app

# Dependências do XML e assinatura digital
RUN apt update && apt install -y \
    libxml2-dev libxmlsec1-dev libxmlsec1-openssl pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Copia requisitos (permite cache de dependências)
COPY requirements.txt /app/requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

# Copia o código do assinador
COPY . /app

EXPOSE 5000

CMD ["flask", "run", "--reload"]
