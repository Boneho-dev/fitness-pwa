FROM php:8.2-cli

# 1. Extensions PHP nécessaires
RUN apt-get update && apt-get install -y libonig-dev && \
    docker-php-ext-install pdo pdo_mysql mbstring && \
    rm -rf /var/lib/apt/lists/*

# 2. Copier le projet
COPY . /var/www/html/
WORKDIR /var/www/html/

# 3. Railway injecte $PORT
# On utilise le serveur intégré de PHP qui est très léger
EXPOSE ${PORT}

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t ."]