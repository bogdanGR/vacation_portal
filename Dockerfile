FROM php:8.2-apache

# PHP extensions
RUN apt-get update \
 && apt-get install -y --no-install-recommends unzip libzip-dev \
 && docker-php-ext-install pdo pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf || true

RUN a2enmod rewrite
RUN printf "<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/app.conf \
    && a2enconf app

# Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# Prefer ZIP dists (avoids git)
RUN composer config -g preferred-install dist

WORKDIR /var/www/html
