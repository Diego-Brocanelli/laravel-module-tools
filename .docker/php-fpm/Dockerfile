FROM phpdockerio/php74-fpm:latest
WORKDIR "/application"
# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive
# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install  php-memcached php7.4-mysql php7.4-bcmath php7.4-bz2 php7.4-enchant php7.4-gd php7.4-intl php7.4-soap php7.4-xmlrpc php7.4-xsl \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*