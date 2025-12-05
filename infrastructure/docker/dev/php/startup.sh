#!/bin/bash

# Generate 8-character hash for PHP_INSTANCE based on container hostname
CONTAINER_NAME=$(hostname)
PHP_INSTANCE_HASH=$(echo -n "$CONTAINER_NAME" | sha256sum | cut -c1-8)
export PHP_INSTANCE="$PHP_INSTANCE_HASH"
echo "Starting PHP-FPM with instance hash: $PHP_INSTANCE (from container: $CONTAINER_NAME)"

if [ ! -f app/etc/env.php ]; then
   php bin/magento setup:install \
      --base-url=http://localhost:8080 \
      --db-host=mysql \
      --db-name=magento \
      --db-user=magento \
      --db-password=magentopassword \
      --admin-firstname=Admin \
      --admin-lastname=User \
      --admin-email=admin@example.com \
      --admin-user=admin \
      --admin-password=admin123 \
      --language=en_US \
      --currency=USD \
      --timezone=Asia/Kolkata \
      --use-rewrites=1 \
      --search-engine=elasticsearch8 \
      --elasticsearch-host=elasticsearch \
      --elasticsearch-port=9200 \
      --elasticsearch-timeout=15 \
      --elasticsearch-index-prefix=magento2
fi

# # Install sample data if not already installed
# if [ ! -f .sample_data_installed ]; then
#     echo "Installing sample data..."
#     COMPOSER_AUTH=${COMPOSER_AUTH} php bin/magento sampledata:deploy
#     php bin/magento setup:upgrade
#     touch .sample_data_installed
# fi

# Disable Two-Factor Authentication for development
echo "Disabling Two-Factor Authentication..."
php bin/magento module:disable Magento_TwoFactorAuth
php bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth

# Deploy static content for developer mode
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
php bin/magento setup:di:compile
php bin/magento indexer:reindex
php bin/magento deploy:mode:set developer

# Start PHP-FPM with the generated instance hash
echo "Starting PHP-FPM with instance hash: $PHP_INSTANCE"
exec php-fpm
