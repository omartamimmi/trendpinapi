FROM php:8.2-fpm

# Set PHP configuration for file uploads
RUN echo "post_max_size=20M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "upload_max_filesize=20M" >> /usr/local/etc/php/conf.d/custom.ini

RUN echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/custom.ini

# Install necessary packages and dependencies
RUN apt-get update && apt-get install -y unzip libzip-dev \
    curl \
    gnupg2 \
    unixodbc-dev \
    libssl-dev \
    librdkafka-dev \
    gcc \
    make \
    autoconf \
    bzip2 \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/* 

# Add Microsoft repository for SQL Server
RUN curl https://packages.microsoft.com/keys/microsoft.asc | tee /etc/apt/trusted.gpg.d/microsoft.asc \
    && curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update

# Install the Microsoft ODBC Driver for SQL Server
RUN ACCEPT_EULA=Y apt-get install -y msodbcsql17 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP SQL Server extensions
RUN pecl install pdo_sqlsrv sqlsrv \
    && docker-php-ext-enable pdo_sqlsrv sqlsrv \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Install the rdkafka PHP extension
RUN pecl install rdkafka \
    && docker-php-ext-enable rdkafka \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y python3 python3-pip
RUN apt-get install -y supervisor

RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

RUN apt-get update && apt-get install -y \
    libmagickwand-dev --no-install-recommends \
    && pecl install imagick \
    && docker-php-ext-enable imagick


# Install PHP zip extension
RUN docker-php-ext-install zip

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"

# Install Node.js and other utilities
#RUN apt-get install -y nodejs zip unzip vim

# Add the cron job definition
#COPY ./docker/crontab /etc/cron.d/laravel-cron

# Give the cron file proper permissions
#RUN chmod 0644 /etc/cron.d/laravel-cron

# Apply cron job and run cron in the background
#RUN crontab /etc/cron.d/laravel-cron

# Install pcntl and other necessary extensions
RUN docker-php-ext-install pcntl

# Install necessary tools and Redis extension
RUN apt-get update && apt-get install -y \
    libz-dev \
    libcurl4-openssl-dev \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

RUN npm install -g vite

# RUN chown -R www-data:www-data /var/www/html/storage

# RUN chown -R www-data:www-data /var/www/html/bootstrap
# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Expose port 9000
# EXPOSE 9001

ENTRYPOINT ["docker-php-entrypoint"]

# Set the command to run PHP-FPM
RUN docker-php-ext-install pdo pdo_mysql
CMD ["php-fpm", "-F"]   # FPM will listen on 9000 inside the container