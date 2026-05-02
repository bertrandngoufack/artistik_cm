ARG PHP_BASE_IMAGE=php:8.4-apache-bookworm
FROM ${PHP_BASE_IMAGE}

ARG INSTALL_WPCLI=1

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    libicu-dev \
    curl \
    git \
    unzip \
    pkg-config \
    gettext-base \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install -j"$(nproc)" \
    gd \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
    opcache \
    exif \
    mbstring \
    bcmath \
    intl \
    soap \
    sockets

RUN pecl install redis \
    && pecl install apcu \
    && docker-php-ext-enable redis apcu

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN if [ "$INSTALL_WPCLI" = "1" ]; then \
    curl -fsSL -o /tmp/wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /tmp/wp-cli.phar \
    && mv /tmp/wp-cli.phar /usr/local/bin/wp; \
    else \
    echo '#!/bin/sh' > /usr/local/bin/wp && echo 'echo "WP-CLI désactivé au build (INSTALL_WPCLI=0)" >&2; exit 127' >> /usr/local/bin/wp && chmod +x /usr/local/bin/wp; \
    fi

RUN a2enmod rewrite \
    && a2enmod headers \
    && a2enmod expires \
    && a2enmod remoteip

COPY php.ini /usr/local/etc/php/conf.d/00-custom.ini

RUN mkdir -p /usr/local/share/php-stack/apache /usr/local/share/php-stack/bin
COPY apache/vhost.conf.template /usr/local/share/php-stack/apache/vhost.conf.template

COPY scripts/ensure-mysql-database.php /usr/local/share/php-stack/bin/ensure-mysql-database.php
COPY scripts/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
