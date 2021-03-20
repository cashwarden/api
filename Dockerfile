FROM php:7.4.3-apache

ARG TIMEZONE

RUN sed -i 's/deb.debian.org/mirrors.ustc.edu.cn/g' /etc/apt/sources.list \
    && sed -i 's|security.debian.org/debian-security|mirrors.ustc.edu.cn/debian-security|g' /etc/apt/sources.list \
    && requirements="vim cron mariadb-client libwebp-dev libxpm-dev libmcrypt-dev libmcrypt4 libcurl3-dev libxml2-dev \
libmemcached-dev zlib1g-dev libc6 libstdc++6 libkrb5-3 openssl debconf libfreetype6-dev libjpeg-dev libtiff-dev \
libpng-dev git libmagickwand-dev ghostscript gsfonts libbz2-dev libonig-dev libzip-dev zip unzip" \
    && apt-get update && apt-get install -y --no-install-recommends $requirements && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install mysqli \
                              pdo_mysql \
                              gd  \
                              exif \
                              bcmath \
                              opcache \
    && docker-php-ext-enable opcache

RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini \
    && "date"

RUN a2enmod headers && \
    a2enmod rewrite

# Install composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Add configuration files
COPY image-files/ /

RUN chmod 700 \
        /usr/local/bin/docker-entrypoint.sh

WORKDIR /srv
COPY . /srv/

RUN composer install --prefer-dist \
    && chmod 777 -R /srv/runtime \
    && chmod +x /srv/yii \
    && chmod 777 -R /srv/web/assets \
    && chmod 777 -R /srv/web/uploads \
    && chown -R www-data:www-data /srv/

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["apache2-foreground"]
