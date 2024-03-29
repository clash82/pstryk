FROM php:8.0.28-apache

RUN apt update && apt upgrade -y && apt install -y \
      git\
      unzip\
      libicu-dev \
      sudo \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      libpng-dev \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
      pdo_mysql \
      intl \
      opcache \
    && rm -rf /tmp/* \
    && rm -rf /var/list/apt/* \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# install composer globally
COPY .docker/composer_installer.sh /tmp/composer_installer.sh
RUN sh /tmp/composer_installer.sh

# install node
RUN curl -fsSL https://deb.nodesource.com/setup_10.x | sudo -E bash -
RUN apt update && apt install -y nodejs npm

# install yarn
RUN npm install -g yarn

# modify default apache site
RUN sed -i 's#/var/www/html#/var/www/html/public_html#' /etc/apache2/sites-enabled/000-default.conf

# enable required apache modules
RUN a2enmod headers
RUN a2enmod rewrite

# restart apache
RUN service apache2 restart
