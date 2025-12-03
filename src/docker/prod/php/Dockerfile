FROM php:8.2.23-fpm

ARG XDEBUG_VERSION=3.4.2
# Install system dependencies
ARG UID=1000
ARG GID=1000

USER root

RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    curl \
    libcurl4-openssl-dev \
    vim \
    libbz2-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    zlib1g-dev \
    libxslt1-dev

RUN rm -rf /var/lib/apt/lists/*

RUN apt update && docker-php-ext-configure zip && \
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg && \
    docker-php-ext-configure xsl

RUN docker-php-ext-install -j$(nproc) \
    gd \
    zip \
    pdo_mysql \
    bz2 \
    curl \
    mbstring \
    intl \
    soap \
    xml \
    bcmath \
    sockets \
    ftp \
    xsl
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Xdebug
RUN yes | pecl install xdebug

# Install Composer
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html/magento-app

COPY composer*.json ./
# Copy auth.json for private repository access
# Install Magento dependencies
COPY ./docker/php/auth.json /root/.composer/auth.json
# RUN composer install --ignore-platform-reqs --optimize-autoloader --no-dev --no-interaction
RUN composer install --ignore-platform-reqs --optimize-autoloader --no-interaction
# If you want to install Magento in production mode, uncomment the following line

COPY . .

# Setup Magento
RUN mkdir -p /var/www/html/magento-app/generated \
    && mkdir -p /var/www/html/magento-app/var \
    && mkdir -p /var/www/html/magento-app/var/cache \
    && mkdir -p /var/www/html/magento-app/var/tmp \
    && mkdir -p /var/www/html/magento-app/var/log \
    && mkdir -p /var/www/html/magento-app/var/page_cache \
    && mkdir -p /var/www/html/magento-app/pub \
    && mkdir -p /var/www/html/magento-app/pub/static \
    && mkdir -p /var/www/html/magento-app/pub/media
COPY docker/php/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
# Change permissions
RUN chown -R www-data:www-data \
    /var/www/html/magento-app/generated \
    /var/www/html/magento-app/var \
    /var/www/html/magento-app/pub/static \
    /var/www/html/magento-app/pub/media
RUN chmod -R 755 \
    /var/www/html/magento-app/generated \
    /var/www/html/magento-app/var \
    /var/www/html/magento-app/pub/static \
    /var/www/html/magento-app/pub/media

RUN find /var/www/html/magento-app/generated /var/www/html/magento-app/var /var/www/html/magento-app/pub/static /var/www/html/magento-app/pub/media -type d -exec chmod 775 {} \;
RUN find /var/www/html/magento-app/generated /var/www/html/magento-app/var /var/www/html/magento-app/pub/static /var/www/html/magento-app/pub/media -type f -exec chmod 664 {} \;

RUN  usermod -u ${UID} www-data && groupmod -g ${GID} www-data

COPY ./docker/php/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

USER www-data:www-data
CMD ["php-fpm"]
EXPOSE 9000