FROM php:8.3-apache

LABEL maintainer="Andreas Kasper <andreas.kasper@goo1.de>"
LABEL description="Static caching proxy with domain rewriting for WordPress"
LABEL version="2.0.0"

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    zlib1g-dev \
    && docker-php-ext-install curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod headers rewrite ssl proxy proxy_http deflate

# Configure PHP for optimal performance
RUN { \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 30'; \
    echo 'upload_max_filesize = 32M'; \
    echo 'post_max_size = 32M'; \
    echo 'date.timezone = UTC'; \
    echo 'expose_php = Off'; \
} > /usr/local/etc/php/conf.d/custom.ini

# Copy application files
COPY src/html/ /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod 755 /var/www/html \
    && chmod 644 /var/www/html/index.php \
    && chmod 644 /var/www/html/.htaccess

# Create log directory
RUN mkdir -p /var/log/apache2 \
    && chown -R www-data:www-data /var/log/apache2

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

EXPOSE 80

CMD ["apache2-foreground"]
