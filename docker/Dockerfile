FROM php:8.3-apache

ENV APP_ENV prod
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
ENV DATABASE_DIR /opt/database

EXPOSE 80

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update && \
    apt-get -y install libxml2-dev libonig-dev libpq-dev supervisor tini unzip && \
    rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install intl opcache pcntl pdo pdo_pgsql && \
    pecl install redis && \
    docker-php-ext-enable redis
RUN a2enmod rewrite

COPY --chown=33:33 . /var/www/html
COPY docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/supervisor.conf /etc/supervisor/conf.d/messenger-worker.conf
RUN mkdir /opt/database && \
    chown 33:33 /opt/* && \
    chmod +x /var/www/html/docker/entrypoint.bash

WORKDIR /var/www/html
RUN curl https://getcomposer.org/installer -o /tmp/composer-installer && \
    php /tmp/composer-installer && \
    chmod +x composer.phar && \
    mv composer.phar /usr/local/bin/composer && \
    rm -rf var vendor && \
    composer install --no-dev --no-scripts -o -a && \
    rm -rf /usr/local/bin/composer && \
    rm -rf /tmp/composer-installer

ENTRYPOINT ["/usr/bin/tini", "--", "/var/www/html/docker/entrypoint.bash"]
