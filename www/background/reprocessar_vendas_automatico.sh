#!/bin/bash

cd /www/background/

SCRIPT_PATH="reprocessar_vendas_automatico.php"
LOG_PATH="/www/log/log_reprocessar_pins.log"

# Executa o script e salva no log
php $SCRIPT_PATH > "$LOG_PATH" 2>&1
