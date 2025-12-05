# Stage 1: Build PHP with extensions
FROM php:8.2.23-fpm-alpine AS php-base

ARG XDEBUG_VERSION=3.4.2

USER root

# Update vulnerable packages to fixed versions
RUN --mount=type=cache,target=/var/cache/apk \
    apk upgrade --no-cache && \
    apk add --no-cache \
    'libcrypto3>=3.3.3-r0' \
    'libssl3>=3.3.3-r0' \
    'openssl>=3.3.3-r0' \
    'libxml2>=2.12.10-r0' \
    'sqlite-libs>=3.45.3-r2' \
    'xz>=5.6.2-r1' \
    'xz-libs>=5.6.2-r1'

RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache linux-headers g++ make autoconf libc-dev pkgconf \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    curl-dev \
    vim \
    bzip2-dev \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    zlib-dev \
    libxslt-dev \
    $PHPIZE_DEPS

RUN --mount=type=cache,target=/tmp/pear \
    docker-php-ext-configure zip && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-configure xsl && \
    docker-php-ext-install -j$(nproc) \
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

# Install Xdebug
RUN --mount=type=cache,target=/tmp/pear \
    pecl install xdebug-${XDEBUG_VERSION} && docker-php-ext-enable xdebug

RUN apk del $PHPIZE_DEPS && rm -rf /tmp/* /var/cache/apk/*

# Stage 2: Final application image
FROM php:8.2.23-fpm-alpine

ARG XDEBUG_VERSION=3.4.2
ARG UID=1000
ARG GID=1000
ARG TARGET_STAGE
ARG PECL_MODULES_ENABLED

USER root

# Update vulnerable packages to fixed versions
RUN --mount=type=cache,target=/var/cache/apk \
    apk upgrade --no-cache && \
    apk add --no-cache \
    'libcrypto3>=3.3.3-r0' \
    'libssl3>=3.3.3-r0' \
    'openssl>=3.3.3-r0' \
    'libxml2>=2.12.10-r0' \
    'sqlite-libs>=3.45.3-r2' \
    'xz>=5.6.2-r1' \
    'xz-libs>=5.6.2-r1'

# Install runtime dependencies and composer
RUN apk add --no-cache \
    freetype \
    libjpeg-turbo \
    libpng \
    libzip \
    curl \
    vim \
    bzip2 \
    icu \
    oniguruma \
    zlib \
    libxslt \
    shadow

# Copy PHP extensions from php-base stage
COPY --from=php-base /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-base /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.8.4

# Install Composer configuration
COPY infrastructure/docker/${TARGET_STAGE:-dev}/php/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html/portfolio-app

# Copy composer files for caching
COPY composer.* ./

# Copy auth.json for private repository access (if exists)
# COPY ./docker/${TARGET_STAGE:-dev}/php/auth.json /root/.composer/auth.json

# Install dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_PROCESS_TIMEOUT=2000
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_HTTP_TIMEOUT=600

# Modify www-data user/group IDs before composer install
RUN usermod -u ${UID} www-data && groupmod -g ${GID} www-data

COPY ./src .

# Setup Laravel directories
RUN mkdir -p \
    storage/app \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
COPY infrastructure/docker/${TARGET_STAGE:-dev}/php/docker-php-ext-xdebug.ini /tmp/docker-php-ext-xdebug.ini

RUN [ -z "$PECL_MODULES_ENABLED" ] && \ echo "No PECL modules to install." || \
    docker-php-ext-enable $PECL_MODULES_ENABLED

RUN /bin/sh -c "if [[ \"$PECL_MODULES_ENABLED\" =~ \"xdebug\" ]]; then \
    cp /tmp/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo 'xdebug.mode=debug,develop,coverage' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    fi"


# Copy Xdebug configuration

# Set permissions for Laravel
RUN chown -R www-data:www-data storage bootstrap/cache vendor && \
    chmod -R 755 storage bootstrap/cache && \
    find storage bootstrap/cache -type d -exec chmod 775 {} \; && \
    find storage bootstrap/cache -type f -exec chmod 664 {} \;

USER www-data:www-data
CMD ["php-fpm"]
EXPOSE 9000
