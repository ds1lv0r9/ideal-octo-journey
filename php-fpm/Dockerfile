FROM php:fpm-alpine

COPY wait-for-it.sh /usr/bin/wait-for-it
RUN chmod +x /usr/bin/wait-for-it
RUN apk --update --no-cache add git
RUN docker-php-ext-install pdo_mysql

RUN docker-php-ext-install opcache
	
COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www

CMD wait-for-it database:3306;  php-fpm 
EXPOSE 9000