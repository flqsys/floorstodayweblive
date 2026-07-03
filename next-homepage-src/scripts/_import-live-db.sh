#!/bin/bash
# Reads DB credentials from wp-config.php and imports a SQL file into that
# database. Runs entirely server-side so the password never has to transit
# through the calling script's argument handling. Mirror of
# _dump-live-db.sh, import instead of dump.
set -e
WP_CONFIG="$1"
IN_FILE="$2"

DB_NAME=$(grep DB_NAME "$WP_CONFIG" | cut -d "'" -f 4)
DB_USER=$(grep DB_USER "$WP_CONFIG" | cut -d "'" -f 4)
DB_PASSWORD=$(grep DB_PASSWORD "$WP_CONFIG" | cut -d "'" -f 4)

mysql -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$IN_FILE"
echo "IMPORT_OK:$DB_NAME"
