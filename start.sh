echo "This is a template, you need to press \"Use Template\" and follow the instructions in README.md"
exit 1

pkill httpd
pkill php-fpm

mkdir /run/mysqld
touch /run/mysqld/mysqld.sock
mysqld --datadir /home/runner/$REPL_SLUG/data -u runner &
sleep 2

envsubst -i conf/php-fpm.conf -o /tmp/.php-fpm.conf

php-fpm --fpm-config /tmp/.php-fpm.conf

echo "httpd location: $(which httpd)"

httpd -d .

watch . > /dev/null