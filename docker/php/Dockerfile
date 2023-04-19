FROM php:8.1-fpm-alpine

# Arguments defined in docker-compose.yml
ARG user
ARG uid
ARG working_dir

# Install system dependencies
RUN apk update && apk add --no-cache --update curl \
    libxml2-dev \
    libzip-dev \
    zip \
    supervisor \
    libgomp \
    busybox-suid \
    libpng-dev \
    icu-dev \
    oniguruma-dev && \
    # Add php extension
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl && \
    # Remove cache after installing packages
    rm /var/cache/apk/*

# Copy configuration files into container
COPY supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY start.sh /start.sh

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chmod +x /start.sh && \
    # Create system user to run Composer and Artisan Commands
    adduser -D -G www-data -u $uid -h /home/$user $user && \
    # Add group for user
    addgroup -S $user && addgroup $user $user && \
    # Set permissions for the user
    mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user && \
    chown -R $user:$user /var/log && \
    chown -R $user:$user /var/run && \
    chown -R $user:$user /run

# Set working directory
WORKDIR $working_dir

USER $user

ENTRYPOINT /start.sh
