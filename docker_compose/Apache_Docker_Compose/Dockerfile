FROM php:8.4-apache-bookworm

# Installation des dépendances système pour PHP 8.4
RUN apt-get update && apt-get install -y \
    # Bibliothèques de base
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    # ICU pour intl (nécessaire PHP 8.4)
    libicu-dev \
    # Outils système
    curl \
    git \
    unzip \
    pkg-config \
    # Pour envsubst (optionnel)
    gettext \
    && rm -rf /var/lib/apt/lists/*

# Configuration GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Installation des extensions PHP 8.4
RUN docker-php-ext-install -j$(nproc) \
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

# Installation des extensions PECL
RUN pecl install redis \
    && pecl install apcu \
    && docker-php-ext-enable redis apcu

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installation de WP-CLI (optionnel)
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# Configuration Apache pour PHP 8.4
RUN a2enmod rewrite \
    && a2enmod headers \
    && a2enmod expires \
    && a2enmod remoteip

# Configuration PHP
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Script d'entrée
COPY scripts/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]