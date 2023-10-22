#!/bin/bash

sleep 30

set -e
clickhouse client -n <<-EOSQL
CREATE DATABASE ${CLICKHOUSE_DATABASE} ENGINE = MySQL('mariadb:3306', '${MARIADB_DATABASE}', '${MARIADB_USER}', '${MARIADB_PASSWORD}');

CREATE TABLE ${CLICKHOUSE_DATABASE}.url_info ( id Int64, url String, time DateTime, length Int16) ENGINE = MySQL('mariadb:3306', '${MARIADB_DATABASE}', 'url_info', '${MARIADB_USER}', '${MARIADB_PASSWORD}');
EOSQL
