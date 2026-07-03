#!/bin/bash
# Reads DB credentials from wp-config.php and dumps the database. Runs
# entirely server-side so the password never has to transit through the
# calling script's argument handling.
set -e
WP_CONFIG="$1"
OUT_FILE="$2"

DB_NAME=$(grep DB_NAME "$WP_CONFIG" | cut -d "'" -f 4)
DB_USER=$(grep DB_USER "$WP_CONFIG" | cut -d "'" -f 4)
DB_PASSWORD=$(grep DB_PASSWORD "$WP_CONFIG" | cut -d "'" -f 4)

mysqldump --no-tablespaces -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$OUT_FILE"
echo "DUMP_OK:$DB_NAME"
