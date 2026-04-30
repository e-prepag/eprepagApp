# --- Estágio de Builder (mesmo do Dockerfile_php8 para garantir paridade) ---
FROM php:8.4-fpm-alpine AS builder

RUN apk add --no-cache \
    libzip-dev \
    libpq-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    curl \
    git

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    mysqli \
    pgsql \
    pdo_pgsql \
    pdo_mysql \
    gd \
    zip \
    intl \
    opcache \
    bcmath

# --- Estágio Final ---
FROM php:8.4-fpm-alpine

# Instalar libs runtime e o CRON (busybox-extras no alpine tem o crond)
RUN apk add --no-cache \
    libzip \
    libpq \
    libpng \
    libjpeg-turbo \
    freetype \
    libxml2 \
    icu-libs \
    oniguruma \
    bash \
    tzdata

# Copiar extensões do builder
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/include/php/ext /usr/local/include/php/ext
RUN docker-php-ext-enable mysqli pgsql pdo_pgsql pdo_mysql gd zip intl opcache bcmath

# Configurações de Hardening (Paridade com Web)
RUN { \
    echo 'expose_php=Off'; \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT'; \
    echo 'disable_functions=system,exec,shell_exec,passthru,proc_open,popen,curl_multi_exec,parse_ini_file,show_source,symlink,link,pcntl_exec,pcntl_fork,pcntl_waitpid,pcntl_alarm,pcntl_signal,proc_get_status,apache_setenv,enable_dl,chown,chgrp,chmod,highlight_file,php_strip_whitespace,filegroup,fileowner,ini_restore,ini_set'; \
    } > /usr/local/etc/php/conf.d/security-hardened.ini

# Criar usuário não-root (UID 1001 para bater com o web e volumes)
RUN addgroup -g 1001 appgroup && adduser -u 1001 -G appgroup -s /bin/sh -D appuser

WORKDIR /www

# Configurar o Crontab
COPY crontab /etc/crontabs/appuser
RUN chmod 0644 /etc/crontabs/appuser

# Ajustar permissões para logs
RUN touch /var/log/cron.log && chown appuser:appgroup /var/log/cron.log

# O Alpine usa o crond do busybox. O flag -f mantém no foreground, -L /dev/stdout joga logs no stdout do Docker
USER appuser
CMD ["crond", "-f", "-L", "/dev/stdout"]
