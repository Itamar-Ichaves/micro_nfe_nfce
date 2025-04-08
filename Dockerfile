# Base image
FROM php:8.2-fpm

# Set your user name and UID
ARG user=ichaves
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install soap
RUN docker-php-ext-install gd
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install intl
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user


# Install PostgreSQL extensions
RUN apt-get update && \
    apt-get install -y libpq5 libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql && \
    apt-get autoremove --purge -y libpq-dev && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install Redis
RUN pecl install -o -f redis && \
    rm -rf /tmp/pear && \
    docker-php-ext-enable redis

# Set working directory
WORKDIR /var/www

# Copy custom configurations PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Copy application files
COPY . /var/www

# Set permissions for the application directory
RUN chown -R www-data:www-data /var/www
RUN find /var/www -type d -exec chmod 0775 '{}' \;
RUN find /var/www -type f -exec chmod 0664 '{}' \;

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Switch to the created user
USER $user

# Start PHP-FPM
CMD ["php-fpm"]