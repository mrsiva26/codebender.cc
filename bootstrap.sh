#!/bin/bash
CACHE_DIR=Symfony/app/cache
LOG_DIR=Symfony/app/logs

# INSTALL PACKAGES
apt-get install apache2 php5-sqlite php-apc php5-intl php-pear php5-dev
pecl install mongo-1.2.12

# UPDATE CONFS
echo date.timezone = America/Los_Angeles >> /etc/php5/apache2/php.ini
echo date.timezone = America/Los_Angeles >> /etc/php5/cli/php.ini
echo extension=mongo.so >> /etc/php5/cli/php.ini
echo extension=mongo.so >> /etc/php5/apache/php.ini

# INITIALIZING APP STRUCTURE
if [ ! -d $CACHE_DIR ]; then
	mkdir -p $CACHE_DIR
	chown $SUDO_USER:$SUDO_USER $CACHE_DIR
fi
chmod 777 -R $CACHE_DIR

if [ ! -d $LOG_DIR ]; then
	mkdir -p $LOG_DIR
	chown $SUDO_USER:$SUDO_USER $LOG_DIR
fi
chmod 777 -R $LOG_DIR

# INSTALL SYMFONY BUNDLES
cd Symfony && php bin/vendors install --reinstall && \
	chown -R $SUDO_USER:$SUDO_USER vendor && cd -
