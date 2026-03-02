#!/bin/bash

LOG_DIR="/www/arquivos_gerados/logs"

LOG_FILE="$LOG_DIR/worker_log_$(date +%Y_%d_%m).log"

mkdir -p "$LOG_DIR"

cd /www/background/

php -d max_execution_time=0 -q ./processa_workers.php >> "$LOG_FILE" 2>&1