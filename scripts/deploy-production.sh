#!/usr/bin/env bash
# Uruchamiany wyłącznie po ręcznym przygotowaniu katalogu publicznego zamiast WordPressa.
set -Eeuo pipefail
umask 027
release_id=${1:?Podaj identyfikator wydania}
[[ "$release_id" =~ ^[a-f0-9]{40}-[0-9]+-[0-9]+$ ]] || exit 1
base="$HOME/apps/etb-production"
[[ -d "$base" && ! -L "$base" ]] || { echo 'Brak prywatnego katalogu produkcji.'; exit 1; }
[[ "$(realpath "$base")" == "$base" ]] || exit 1
for directory in shared releases incoming; do
    [[ -d "$base/$directory" && ! -L "$base/$directory" && "$(realpath "$base/$directory")" == "$base/$directory" ]] || exit 1
done
[[ -f "$base/shared/.env" && ! -L "$base/shared/.env" && -f "$base/shared/public-root" ]] || exit 1
[[ -d "$base/shared/storage" && ! -L "$base/shared/storage" ]] || exit 1
public_root=$(cat "$base/shared/public-root")
[[ "$public_root" == "$HOME/domains/eattheball.pl/public_html" ]] || { echo 'Niedozwolony katalog publiczny produkcji.'; exit 1; }
[[ -d "$public_root" && ! -L "$public_root" && "$(realpath "$public_root")" == "$public_root" ]] || exit 1
[[ -f "$public_root/.etb-production" && ! -L "$public_root/.etb-production" ]] || exit 1
for wordpress_path in wp-config.php wp-admin wp-includes wp-content wp-login.php; do
    [[ ! -e "$public_root/$wordpress_path" && ! -L "$public_root/$wordpress_path" ]] || {
        echo 'WordPress nadal znajduje się w katalogu publicznym. Najpierw wykonaj ręczne przełączenie po backupie.'; exit 1;
    }
done
[[ -f "$public_root/index.php" && ! -L "$public_root/index.php" ]] || { echo 'Brak przygotowanego pliku wejściowego produkcji.'; exit 1; }
grep -Fq "$base/current/public/index.php" "$public_root/index.php" || { echo 'Plik wejściowy nie wskazuje prywatnego wydania produkcji.'; exit 1; }
php_bin=$(cat "$base/shared/php-bin")
composer_bin=$(cat "$base/shared/composer-bin")
[[ -x "$php_bin" && -x "$composer_bin" ]] || exit 1
command -v flock >/dev/null
command -v rsync >/dev/null
[[ ! -e "$base/current" && ! -L "$base/current" ]] || {
    [[ -L "$base/current" && "$(readlink -f "$base/current")" == "$base/releases/"* && -f "$base/current/artisan" ]] || exit 1;
}
[[ -d "$base/incoming/$release_id" && ! -L "$base/incoming/$release_id" ]] || exit 1
exec 9>"$base/shared/operation.lock"
flock -w 120 9 || { echo 'Trwa wdrożenie lub obsługa kolejki produkcji.'; exit 1; }
cd "$base/incoming/$release_id"
sha256sum -c release.tar.gz.sha256
release="$base/releases/$release_id"
[[ ! -e "$release" && ! -L "$release" ]] || { echo 'To wydanie już istnieje. Użyj nowego uruchomienia workflow.'; exit 1; }
mkdir "$release"
tar -xzf release.tar.gz -C "$release"
[[ ! -e "$release/.env" && ! -e "$release/storage" && ! -e "$release/public/hot" && ! -e "$release/vendor" ]] || exit 1
[[ -s "$release/public/build/manifest.json" && -f "$release/public/.htaccess" ]] || exit 1
# Cache zawierający konfigurację konkretnego środowiska nie może pochodzić z paczki.
[[ ! -e "$release/bootstrap/cache/config.php" && ! -e "$release/bootstrap/cache/routes-v7.php" ]] || exit 1
ln -s "$base/shared/.env" "$release/.env"
ln -s "$base/shared/storage" "$release/storage"
mkdir -p "$base/shared/storage/app/public" "$base/shared/storage/app/private" \
    "$base/shared/storage/framework/cache/data" "$base/shared/storage/framework/sessions" \
    "$base/shared/storage/framework/views" "$base/shared/storage/logs" "$release/bootstrap/cache"
chmod 711 "$HOME/apps" "$base" "$base/shared" "$base/shared/storage" "$base/shared/storage/app"
chmod 755 "$base/shared/storage/app/public"
cd "$release"
"$composer_bin" install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts
# Hosting blokuje proc_open używane przez Composer do uruchamiania @php.
# Nowe wydanie nie zawiera cache; odkrywanie pakietów wykonujemy bezpośrednio.
"$php_bin" artisan package:discover --ansi
"$composer_bin" check-platform-reqs --no-dev
"$php_bin" scripts/check-production.php
if [[ ! -e "$base/current" ]] && grep -Eq "^[[:space:]]*APP_KEY[[:space:]]*=[[:space:]]*(\"\"|''|null)?[[:space:]]*$" "$base/shared/.env"; then
    "$php_bin" artisan key:generate --force
fi
"$php_bin" artisan config:cache
"$php_bin" scripts/check-production.php --require-key
"$php_bin" artisan route:cache
[[ ! -e "$release/public/storage" && ! -L "$release/public/storage" ]] || exit 1
ln -s "$base/shared/storage/app/public" "$release/public/storage"
# Jeden wspólny storage zapewnia tryb konserwacji również dla poprzedniego wydania.
"$php_bin" artisan down --retry=60
trap 'echo "Wdrożenie przerwane. Produkcja pozostaje w konserwacji; sprawdź logi. Nie wykonano rollbacku bazy." >&2' ERR
"$php_bin" artisan migrate --force
"$php_bin" artisan view:cache
# Skrypt nigdy nie publikuje index.php ani nie usuwa istniejących zasobów.
rsync -r --exclude=index.php --exclude=.htaccess --exclude=storage --exclude=hot \
    "$release/public/" "$public_root/"
for directory in build images; do
    if [[ -d "$public_root/$directory" ]]; then
        find "$public_root/$directory" -type d -exec chmod 755 {} +
        find "$public_root/$directory" -type f -exec chmod 644 {} +
    fi
done
htaccess_next="$public_root/.htaccess.next-$release_id"
[[ ! -e "$htaccess_next" && ! -L "$htaccess_next" ]] || exit 1
{
    cat <<'APACHE'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} !=on [OR]
    RewriteCond %{HTTP_HOST} !^eattheball\.pl$ [NC]
    RewriteRule ^ https://eattheball.pl%{REQUEST_URI} [R=301,L,NE]
    RewriteRule ^(?:app|wp-admin|wp-content|wp-includes)(?:/|$) - [F,L,NC]
    RewriteRule ^wp-.*\.php$ - [F,L,NC]
</IfModule>
APACHE
    cat "$release/public/.htaccess"
} > "$htaccess_next"
chmod 644 "$htaccess_next"
mv -Tf "$htaccess_next" "$public_root/.htaccess"
printf 'User-agent: *\nAllow: /\n' > "$public_root/robots.txt"
chmod 644 "$public_root/robots.txt"
if [[ -L "$public_root/storage" ]]; then
    [[ "$(readlink -f "$public_root/storage")" == "$base/shared/storage/app/public" ]] || exit 1
elif [[ -e "$public_root/storage" ]]; then
    echo 'Katalog storage już istnieje; nie zostanie nadpisany.'; exit 1
else
    ln -s "$base/shared/storage/app/public" "$public_root/storage"
fi
printf '%s\n' "$release_id" > "$public_root/etb-release.txt"
chmod 644 "$public_root/etb-release.txt"
[[ ! -e "$base/current" || -L "$base/current" ]] || exit 1
[[ ! -e "$base/current.next" && ! -L "$base/current.next" ]] || exit 1
ln -s "$release" "$base/current.next"
mv -Tf "$base/current.next" "$base/current"
"$php_bin" artisan up
trap - ERR
echo 'Wdrożono produkcję. Wykonaj odbiór domeny, poczty i zasobów według instrukcji publikacji.'
