#!/bin/sh

if [ "$TRAVIS_PHP_VERSION" = "" ]; then
	php_version=$(php --version | grep -oh -P 'PHP (\d\.\d)' | sed 's/PHP //')
else
	php_version=$TRAVIS_PHP_VERSION
fi
if [ "$php_version" != "5.3" ] && [ "$php_version" != "hhvm" ]; then
	server_support=true
fi


if [ $server_support ]; then
	php -S localhost:8080 -t tests/server > /dev/null 2>&1 &
else
	phpunit_args="--exclude-group server"
fi

phpunit $phpunit_args
ret=$?

if [ $server_support ]; then
	pkill php
fi

exit $ret
