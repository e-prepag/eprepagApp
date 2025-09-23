#!/bin/bash

cd /www/background/

SCRIPT_PATH="envia_email_risco_merchant.php"
LOG_PATH="/www/log/log_email_risco_merchant.log"

# Executa o script e salva no log
php $SCRIPT_PATH > "$LOG_PATH" 2>&1
