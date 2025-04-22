#!/bin/bash
date -R >> /www/log/apaga1Ano.log

find /var/log/aplicacao/ -maxdepth 1 -mtime +365 -type f -exec rm -v {} + >> /www/log/apaga1Ano.log
find /var/log/apache2/ -maxdepth 1 -mtime +365 -type f -exec rm -v {} + >> /www/log/apaga1Ano.log
find /var/log/php/ -maxdepth 1 -mtime +365 -type f -exec rm -v {} + >> /www/log/apaga1Ano.log

echo ========================================================================================================== >> /www/log/apaga1Ano.log
