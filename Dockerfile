FROM composer:2 as builder

COPY . /app/

RUN composer install --no-dev -o

FROM php:8.1-alpine

COPY --from=builder /app/ /app

ARG TOKEN

WORKDIR /app

ENTRYPOINT [ "./bin/console" ]