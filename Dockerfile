FROM php:8.2-apache

# Szükséges kiterjesztések telepítése
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apache rewrite modul bekapcsolása (a routerhez)
RUN a2enmod rewrite

# Composer hozzáadása
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# AZ ÚJ RÉSZ: Átállítjuk az Apache DocumentRoot-ot a public mappára
ENV APACHE_DOCUMENT_ROOT /var/www/html/src/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Engedélyezzük a .htaccess használatát és letiltjuk a mappalistázást a public mappában
RUN echo "<Directory /var/www/html/src/public>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" >> /etc/apache2/apache2.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html