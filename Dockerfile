FROM php:8.2-cli

RUN docker-php-ext-install mysqli

WORKDIR /app
COPY . /app

ENV PORT=8080
EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]