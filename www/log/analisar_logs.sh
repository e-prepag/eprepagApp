#!/bin/bash

# --- Script para analisar logs de modificação de arquivos (.php) ---

# Verifica se um arquivo de log foi fornecido como argumento
log_file="$1"
if [ -z "$log_file" ] || [ ! -f "$log_file" ]; then
    echo "Erro: Forneça um caminho de arquivo de log válido."
    echo "Uso: $0 /caminho/para/seu/log.txt"
    exit 1
fi

# 1. Extrai todos os caminhos de arquivo do log
#    - Adicionamos 'grep "\.php ("' para filtrar apenas linhas de arquivos .php
all_paths=$(grep -E 'Arquivo (modificado|criado): ' "$log_file" | grep "\.php (" | sed 's/.*: \(.*\) (Usu.*/\1/') # LINHA MODIFICADA

if [ -z "$all_paths" ]; then
    echo "Nenhum arquivo .php modificado ou criado foi encontrado no log."
    exit 0
fi

# --- Objetivo 1: Pastas únicas ---
echo "--- Pastas únicas com modificações (.php) ---"
#    - xargs -n1 dirname: Pega cada caminho e executa o comando 'dirname' nele
#    - sort -u: Ordena a lista de pastas e remove as duplicatas
echo "$all_paths" | xargs -n1 dirname | sort -u
echo "" # Adiciona uma linha em branco para melhor formatação

# --- Objetivo 2: Arquivos únicos por pasta ---
echo "--- Arquivos .php únicos por pasta (ignorando duplicados) ---"
#    - sort -u: Pega a lista de caminhos COMPLETOS e remove duplicatas
#    - while read ...: Lê cada caminho único, linha por linha
#    - dirname/basename: Separa o caminho da pasta e o nome do arquivo
#    - printf: Formata a saída
echo "$all_paths" | sort -u | while read -r full_path; do
    folder=$(dirname "$full_path")
    file=$(basename "$full_path")
    printf "Pasta: %-30s | Arquivo: %s\n" "$folder" "$file"
done
