#!/usr/bin/env bash

mariadb --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS testing;
EOSQL

if [ -n "$MYSQL_USER" ]; then
mariadb --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    GRANT ALL PRIVILEGES ON \`testing%\`.* TO '$MYSQL_USER'@'%';
EOSQL
fi
