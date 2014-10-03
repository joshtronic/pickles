#!/bin/sh

php -i

VERSION=`phpenv version-name`

if [ "${VERSION}" = 'hhvm' ]
then
    PHPINI=/etc/hhvm/php.ini
else
    PHPINI=~/.phpenv/versions/$VERSION/etc/php.ini

    echo "extension = memcache.so"  >> $PHPINI
    echo "extension = memcached.so" >> $PHPINI
    echo "extension = redis.so"     >> $PHPINI
fi

