# Use official PHP runtime as base image
FROM php:8.2-cli

# Install PostgreSQL extension for PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Create uploads directory
RUN mkdir -p uploads && chmod 755 uploads

# Expose port
EXPOSE $PORT

# Start PHP built-in server
CMD php -S 0.0.0.0:$PORT -t . 