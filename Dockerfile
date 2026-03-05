FROM serversideup/php:8.3-fpm-nginx

WORKDIR /var/www/html

COPY . .

# Instala dependências do sistema que o Laravel costuma precisar
RUN install-php-extensions \
    pdo_mysql \
    bcmath \
    intl \
    gd \
    zip

# Instala dependências PHP sem rodar scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Cria .env vazio temporário para evitar erro
RUN cp .env.example .env || true

# Roda os scripts manualmente
RUN php artisan package:discover || true

# Build do frontend
RUN npm install && npm run build

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80