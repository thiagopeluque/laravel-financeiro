FROM serversideup/php:8.3-fpm-nginx

WORKDIR /var/www/html

COPY . .

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build do frontend
RUN npm install && npm run build

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80