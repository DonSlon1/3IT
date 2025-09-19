FROM php:8.4-apache

USER root

RUN apt-get update

RUN apt-get install -y \
   wget \
   nano \
   gnupg \
   locales \
   unzip \
   dialog \
   apt-utils  \
   git \
   libbz2-dev \
   libwebp-dev \
   libgmp-dev \
   libfreetype6-dev \
   libpng-dev \
   libxpm-dev \
   libjpeg-dev \
   libjpeg62-turbo-dev \
   libicu-dev \
   libpq-dev \
   libpspell-dev \
   libkrb5-dev \
   libmagickwand-dev \
   curl \
   libcurl4-openssl-dev \
   libonig-dev \
   default-libmysqlclient-dev \
   default-libmysqlclient-dev

RUN apt-get update && apt-get install -y tzdata
ENV TIMEZONE=Europe/Prague
RUN ln -snf /usr/share/zoneinfo/$TIMEZONE /etc/localtime && echo $TIMEZONE > /etc/timezone

RUN docker-php-ext-install \
   opcache \
   intl \
   bcmath \
   mbstring \
   curl \
   gd \
   mysqli \
   pdo \
   pdo_mysql

RUN docker-php-ext-enable pdo_mysql

RUN docker-php-ext-install calendar && docker-php-ext-configure calendar

RUN a2enmod \
   actions \
   allowmethods \
   asis \
   authn_file \
   authz_core \
   authz_groupfile \
   authz_host authz_user \
   autoindex \
   cgi \
   dir \
   env \
   headers \
   include \
   info \
   cache_disk \
   mime \
   negotiation \
   proxy \
   proxy_ajp \
   rewrite \
   setenvif \
   socache_shmcb \
   ssl \
   status

RUN echo "umask 0002" >> /etc/apache2/envvars

RUN echo "IncludeOptional /var/www/html/config/docker/vhost.conf" > /etc/apache2/sites-available/vhosts.conf
RUN a2ensite vhosts.conf

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

RUN userdel -f www-data || true \
   && if getent group www-data > /dev/null; then groupdel www-data; fi \
   && groupadd -g 1000 www-data \
   && useradd -l -u 1000 -g www-data www-data \
   && install -d -m 0755 -o www-data -g www-data /home/www-data \
   && chown --changes --no-dereference --recursive --from=33:33 1000:1000 /home/www-data

USER www-data
