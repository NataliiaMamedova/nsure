FROM bref/php-80-fpm-dev

USER root

RUN yum install -y wget git bash unzip openssl

# Composer
RUN php -r "copy('https://getcomposer.org/installer', '/usr/bin/composer-setup.php');"
RUN php -r "if (hash_file('sha384', '/usr/bin/composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php /usr/bin/composer-setup.php --install-dir=/usr/bin
RUN php -r "unlink('/usr/bin/composer-setup.php');"
RUN mv /usr/bin/composer.phar /usr/bin/composer
RUN chmod +x /usr/bin/composer

CMD [ "public/index.php" ]
