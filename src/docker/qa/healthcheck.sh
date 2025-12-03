#!/bin/bash
set -e

# Health check script for Magento container

# Check if Nginx is running
if ! pgrep nginx > /dev/null; then
    echo "Nginx is not running"
    exit 1
fi

# Check if PHP-FPM is running
if ! pgrep php-fpm > /dev/null; then
    echo "PHP-FPM is not running"
    exit 1
fi

# Check if we can make HTTP request to localhost
if ! curl -f -s http://localhost/health_check.php > /dev/null; then
    echo "HTTP health check failed"
    exit 1
fi

echo "Health check passed"
exit 0