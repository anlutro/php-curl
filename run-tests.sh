#!/bin/sh

php -S localhost:8080 -t tests/server > /dev/null 2>&1 &

phpunit

pkill php
