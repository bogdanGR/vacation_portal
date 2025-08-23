FROM php:8.2-apache

# Enable required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set Apache DocumentRoot to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf || true

# Enable useful Apache modules
RUN a2enmod rewrite

# Enable .htaccess in /public
RUN printf "<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/app.conf \
    && a2enconf app

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
