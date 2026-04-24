FROM php:8.2-apache

# Extensions PDO MySQL + mbstring
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y libonig-dev && docker-php-ext-install mbstring && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier le projet à la racine web (/ sur Railway, pas /fitness/)
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Autoriser .htaccess dans le dossier web
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Railway injecte $PORT → Apache doit écouter dessus
RUN echo 'Listen ${PORT}' >> /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-enabled/000-default.conf

EXPOSE ${PORT}

CMD ["apache2-foreground"]
