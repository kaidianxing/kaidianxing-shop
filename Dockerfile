FROM registry.cn-hangzhou.aliyuncs.com/kaidianxing/composer:2.0.8 as builder
  
WORKDIR /app

COPY . /app

RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ &&  composer install -vvv

FROM registry.cn-hangzhou.aliyuncs.com/kaidianxing/nginx-php:7.4

WORKDIR /var/www/html

VOLUME /var/www/html/public/data

COPY --from=builder --chown=82:82 /app .