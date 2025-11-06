FROM php:8.2-cli

# Instalar dependências do sistema necessárias para librdkafka
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    wget \
    build-essential \
    libssl-dev \
    libsasl2-dev \
    libsasl2-modules \
    libzstd-dev \
    liblz4-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar librdkafka (biblioteca C do Kafka)
RUN cd /tmp && \
    wget https://github.com/confluentinc/librdkafka/archive/refs/tags/v2.3.0.tar.gz && \
    tar -xzf v2.3.0.tar.gz && \
    cd librdkafka-2.3.0 && \
    ./configure --prefix=/usr/local && \
    make && \
    make install && \
    ldconfig && \
    rm -rf /tmp/librdkafka-2.3.0 /tmp/v2.3.0.tar.gz

# Instalar a extensão rdkafka para PHP
RUN pecl install rdkafka && \
    docker-php-ext-enable rdkafka

# Verificar se a extensão foi instalada corretamente
RUN php -m | grep rdkafka

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www/html

# Comando padrão para manter o container rodando
CMD ["tail", "-f", "/dev/null"]