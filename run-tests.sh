#!/bin/sh
set -eu
php -S localhost:8080 -t tests/server > /dev/null 2>&1 &
php_s_pid=$!
cleanup() { kill $php_s_pid; }
trap cleanup EXIT
CURL_TEST_SERVER_RUNNING=1 vendor/bin/phpunit $@
