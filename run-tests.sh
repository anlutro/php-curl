#!/bin/sh

if [ "$TRAVIS_PHP_VERSION" = "" ]; then
	php_version=$(php --version | grep -oh -P 'PHP (\d\.\d)' | sed 's/PHP //')
else
	php_version=$TRAVIS_PHP_VERSION
fi
if [ "$php_version" != "5.3" ] && [ "$php_version" != "hhvm" ]; then
	php -S localhost:8080 -t tests/server > /dev/null 2>&1 &
	php_pid=$!
	export CURL_TEST_SERVER_RUNNING=1
fi

phpunit $@
ret=$?

if [ $CURL_TEST_SERVER_RUNNING ]; then
	kill $php_pid
fi

exit $ret
