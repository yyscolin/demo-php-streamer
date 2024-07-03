FROM php:8.3.6-fpm
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable mysqli
WORKDIR /home/site/wwwroot
RUN chown www-data:www-data .
COPY --chown=www-data . .
RUN find . -type f -exec chmod 400 {} + -o -type d -exec chmod 100 {} +
