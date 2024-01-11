#!/usr/bin/env bash

cd /var/www/html || exit 1

rm -rf var
php bin/console cache:warmup
chown -R 33:33 var

[ ! -d "$DATABASE_DIR" ] && mkdir -p "$DATABASE_DIR"
chown -R 33:33 "$DATABASE_DIR"
php bin/console doctrine:migrations:migrate -n
chmod -R 0777 "$DATABASE_DIR"

/etc/init.d/supervisor start
supervisorctl reread
supervisorctl update
supervisorctl start messenger-consume:*

exec apache2ctl -D FOREGROUND
