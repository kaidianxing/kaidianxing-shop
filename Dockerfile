FROM registry.cn-hangzhou.aliyuncs.com/kaidianxing/composer:2.0.8
  
WORKDIR /app

COPY . /app

RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ &&  composer install -vvv

FROM registry.cn-hangzhou.aliyuncs.com/kaidianxing/nginx-php:7.4

WORKDIR /var/www/html

COPY --from=0 --chown=82:82 /app .