#!/bin/bash
date -R >> /www/arquivos_gerados/logs/apaga1Ano.log

find /var/arquivos_gerados/logs/aplicacao/ -maxdepth 1 -mtime +365 -type f -exec rm -v {} + >> /www/arquivos_gerados/logs/apaga1Ano.log
find /var/arquivos_gerados/logs/apache2/ -maxdepth 1 -mtime +365 -type f -exec rm -v {} + >> /www/arquivos_gerados/logs/apaga1Ano.log
find /var/arquivos_gerados/logs/php/ -maxdepth 1 -mtime +365 -type f -exec rm -v {} + >> /www/arquivos_gerados/logs/apaga1Ano.log

echo ========================================================================================================== >> /www/arquivos_gerados/logs/apaga1Ano.log
