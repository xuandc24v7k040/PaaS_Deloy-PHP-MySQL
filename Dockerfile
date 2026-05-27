FROM php:8.2-apache

# Cài đặt extension MySQL và thư viện curl để tải file
RUN apt-get update && apt-get install -y curl \
    && docker-php-ext-install pdo pdo_mysql

# Tải chứng chỉ Root CA chuẩn của Let's Encrypt (Bắt buộc cho TiDB)
RUN curl -o /etc/ssl/certs/isrgrootx1.pem https://letsencrypt.org/certs/isrgrootx1.pem

# Copy mã nguồn
COPY . /var/www/html/

# Phân quyền thư mục
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80