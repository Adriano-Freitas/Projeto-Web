FROM php:8.2-alpine

# Instala dependências do sistema para PostgreSQL e GD (manipulação de imagens)
RUN apk add --no-cache \
    libpq-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql gd

WORKDIR /app

# Copia os arquivos do projeto para o container (necessário para deploy na nuvem)
COPY . /app

EXPOSE 8000

# Executa o servidor embutido do PHP na porta dinâmica (${PORT} na nuvem ou 8000 local)
CMD ["sh", "-c", "php -d upload_max_filesize=20M -d post_max_size=25M -S 0.0.0.0:${PORT:-8000} -t /app"]
