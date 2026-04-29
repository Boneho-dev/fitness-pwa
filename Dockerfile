FROM php:8.2-apache

# 1. SUPPRESSION RADICALE DU CONFLIT MPM
# On supprime physiquement le chargement de mpm_event avant de forcer mpm_prefork
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
    && a2enmod mpm_prefork

# 2. Extensions PHP nécessaires
RUN apt-get update && apt-get install -y libonig-dev && \
    docker-php-ext-install pdo pdo_mysql mbstring && \
    rm -rf /var/lib/apt/lists/*

# 3. Activer mod_rewrite
RUN a2enmod rewrite

# 4. Copier le projet
COPY . /var/www/html/

# 5. Permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# 6. Configuration Apache pour Railway ($PORT)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN echo 'Listen ${PORT}' >> /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-enabled/000-default.conf

EXPOSE ${PORT}

CMD ["apache2-foreground"]