FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer inside the container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

CMD ["sleep", "infinity"]