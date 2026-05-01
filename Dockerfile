FROM php:8.2-cli

# 1. Extensions PHP nécessaires
RUN apt-get update && apt-get install -y libonig-dev && \
    docker-php-ext-install pdo pdo_mysql mbstring && \
    rm -rf /var/lib/apt/lists/*

# 2. Copier le projet
COPY . /var/www/html/
WORKDIR /var/www/html/

# 3. Créer les dossiers de stockage persistants et les rendre writables
# Ces dossiers seront écrasés par le Volume Railway au runtime — le chmod garantit
# que PHP peut écrire même si le volume est monté vide au premier démarrage.
RUN mkdir -p /var/www/html/storage/profiles \
             /var/www/html/storage/posts \
    && chmod -R 777 /var/www/html/storage

# 4. Railway injecte $PORT
EXPOSE ${PORT}

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t /var/www/html"]