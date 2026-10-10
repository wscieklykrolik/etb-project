#!/usr/bin/env bash
set -Eeuo pipefail
base="$HOME/apps/etb-production"
[[ -d "$base" && ! -L "$base" && "$(realpath "$base")" == "$base" ]] || exit 1
[[ -L "$base/current" && "$(readlink -f "$base/current")" == "$base/releases/"* ]] || exit 1
php_bin=$(cat "$base/shared/php-bin")
[[ -x "$php_bin" ]] || exit 1
exec 9>"$base/shared/operation.lock"
flock -n 9 || exit 0
cd "$base/current"
"$php_bin" scripts/check-production.php
"$php_bin" artisan schedule:run --no-interaction
"$php_bin" artisan queue:work database --stop-when-empty --max-time=50 --max-jobs=25 --tries=3 --backoff=30 --timeout=60
