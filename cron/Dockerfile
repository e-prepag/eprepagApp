FROM php:7.3-apache
RUN a2enmod rewrite
RUN a2enmod ssl
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zlib1g-dev \
    libpq-dev \
    ca-certificates \
    curl \
    libcurl4-openssl-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libxml2-dev \ 
    rsyslog\
    cron \
 && rm -rf /var/lib/apt/lists/* \
 && docker-php-ext-configure curl --with-curl=shared \
 && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
 && docker-php-ext-install zip mysqli pgsql pdo_pgsql curl pdo pdo_mysql gd \
 && update-ca-certificates

WORKDIR /www


# Cria usuário e grupo.
RUN groupadd -g 1001 eprepagadm \
    && useradd -u 1001 -g eprepagadm -ms /bin/bash eprepagadm

#Copia e instala os crons
#COPY ./test_cron /etc/cron.d/eprepagadm-cron
COPY ./crontab /etc/cron.d/eprepagadm-cron
RUN chmod 0644 /etc/cron.d/eprepagadm-cron
RUN crontab -u eprepagadm /etc/cron.d/eprepagadm-cron

RUN sed -i 's/www-data/eprepagadm/g' /etc/apache2/envvars

RUN rm /etc/apache2/sites-enabled/000-default.conf

RUN echo 'SetEnv HOME /www/public_html/' >> /etc/apache2/conf-enabled/environment.conf
RUN echo 'SetEnv USER eprepagadm' >> /etc/apache2/conf-enabled/environment.conf
RUN echo 'SetEnv SSL_TLS_SNI localhost' >> /etc/apache2/conf-enabled/environment.conf

COPY ./php.ini /usr/local/etc/php/
RUN sed -i '/imklog/s/^/#/' /etc/rsyslog.conf
RUN service rsyslog start
RUN service cron start

#Permissão para gerar logs no docker
RUN touch /proc/1/fd/1
RUN chmod 666 /proc/1/fd/1

CMD ["cron", "-f"]
