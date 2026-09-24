#!/usr/bin/env bash
set -Eeuo pipefail
base="$HOME/apps/etb-staging"
php_bin=$(cat "$base/shared/php-bin")
exec 9>"$base/shared/operation.lock"
flock -n 9 || exit 0
cd "$base/current"
"$php_bin" artisan queue:work database --stop-when-empty --max-time=50 --max-jobs=25 --tries=3 --backoff=30 --timeout=60
