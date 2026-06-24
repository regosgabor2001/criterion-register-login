FROM php:8.4-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT /var/www/html/src/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

RUN echo "<Directory /var/www/html/src/public>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" >> /etc/apache2/apache2.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html