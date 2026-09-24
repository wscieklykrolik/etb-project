#!/usr/bin/env bash
# Wyłącznie staging. Uruchamiany z prywatnego katalogu incoming po przesłaniu paczki.
set -Eeuo pipefail
umask 027
release_id=${1:?Podaj identyfikator wydania}
[[ "$release_id" =~ ^[a-f0-9]{40}-[0-9]+-[0-9]+$ ]] || exit 1
base="$HOME/apps/etb-staging"
[[ -d "$base" && ! -L "$base" ]] || { echo 'Brak prywatnego katalogu stagingu.'; exit 1; }
base=$(realpath "$base")
[[ "$base" == "$HOME/apps/etb-staging" ]] || exit 1
[[ -f "$base/shared/.env" && -f "$base/shared/public-root" ]] || exit 1
public_root=$(cat "$base/shared/public-root")
# Nie wolno użyć katalogu głównego WordPressa.
case "$public_root" in
    "$HOME/domains/test.eattheball.pl/public_html"|"$HOME/domains/eattheball.pl/public_html/test") ;;
    *) echo 'Niedozwolony katalog publiczny stagingu.'; exit 1 ;;
esac
[[ -d "$public_root" && ! -L "$public_root" ]] || exit 1
[[ "$(realpath "$public_root")" == "$public_root" ]] || exit 1
[[ -f "$public_root/.etb-staging" && ! -e "$public_root/wp-config.php" ]] || exit 1
[[ -f "$public_root/index.php" ]] || { echo 'Najpierw przygotuj plik wejściowy według DEPLOYMENT.md.'; exit 1; }
php_bin=$(cat "$base/shared/php-bin")
composer_bin=$(cat "$base/shared/composer-bin")
[[ -x "$php_bin" && -x "$composer_bin" ]] || exit 1
command -v flock >/dev/null
command -v rsync >/dev/null
exec 9>"$base/shared/operation.lock"
flock -w 120 9 || { echo 'Trwa wdrożenie lub obsługa kolejki.'; exit 1; }
cd "$base/incoming/$release_id"
sha256sum -c release.tar.gz.sha256
# Paczka powstaje z jawnej listy plików w workflow; nigdy nie zawiera .env ani uploadów.
release="$base/releases/$release_id"
[[ ! -e "$release" ]] || { echo 'To wydanie już istnieje. Użyj nowego uruchomienia workflow.'; exit 1; }
mkdir "$release"
tar -xzf release.tar.gz -C "$release"
[[ ! -e "$release/.env" && ! -e "$release/storage" && ! -e "$release/public/hot" && ! -e "$release/vendor" ]] || exit 1
[[ -s "$release/public/build/manifest.json" ]] || exit 1
ln -s "$base/shared/.env" "$release/.env"
ln -s "$base/shared/storage" "$release/storage"
mkdir -p "$base/shared/storage/app/public" "$base/shared/storage/app/private" \
    "$base/shared/storage/framework/cache/data" "$base/shared/storage/framework/sessions" \
    "$base/shared/storage/framework/views" "$base/shared/storage/logs" "$release/bootstrap/cache"
# LiteSpeed działa w grupie apache; tylko prawo przejścia umożliwia obsługę publicznego symlinku.
chmod 711 "$HOME/apps" "$base" "$base/shared" "$base/shared/storage" "$base/shared/storage/app"
chmod 755 "$base/shared/storage/app/public"
cd "$release"
"$composer_bin" install --no-dev --optimize-autoloader --no-interaction --prefer-dist
"$composer_bin" check-platform-reqs --no-dev
# Kontrola nie wypisuje sekretów i odbywa się przed jakąkolwiek migracją.
"$php_bin" scripts/check-staging.php
if [[ ! -e "$base/current" ]] && grep -qx 'APP_KEY=' "$base/shared/.env"; then
    "$php_bin" artisan key:generate --force
fi
"$php_bin" artisan config:cache
"$php_bin" artisan route:cache
# Hostinger blokuje funkcję PHP exec(), z której korzysta storage:link.
# Tworzymy dokładnie ten sam link poleceniem systemowym i kontrolujemy cel.
if [[ -L "$release/public/storage" ]]; then
    [[ "$(readlink -f "$release/public/storage")" == "$base/shared/storage/app/public" ]] || exit 1
elif [[ -e "$release/public/storage" ]]; then
    echo 'Nieoczekiwany public/storage w paczce wydania.'; exit 1
else
    ln -s "$base/shared/storage/app/public" "$release/public/storage"
fi
# Jeden wspólny storage: tryb konserwacji obejmuje też bieżące wydanie.
"$php_bin" artisan down --retry=60
trap 'echo "Wdrożenie przerwane. Nie wykonano automatycznego rollbacku bazy; sprawdź logi i tryb konserwacji." >&2' ERR
"$php_bin" artisan migrate --force
"$php_bin" artisan view:cache
# Kopiowane są tylko publiczne zasoby. Bez --delete, bez podążania za symlinkami.
rsync -r --exclude=index.php --exclude=.htaccess --exclude=storage --exclude=hot \
    "$release/public/" "$public_root/"
# umask hostingu wpływa również na rsync; statyczne pliki muszą być czytelne dla WWW.
for directory in build images; do
    if [[ -d "$public_root/$directory" ]]; then
        find "$public_root/$directory" -type d -exec chmod 755 {} +
        find "$public_root/$directory" -type f -exec chmod 644 {} +
    fi
done
printf 'User-agent: *\nDisallow: /\n' > "$public_root/robots.txt"
chmod 644 "$public_root/robots.txt"
if [[ -L "$public_root/storage" ]]; then
    [[ "$(readlink -f "$public_root/storage")" == "$base/shared/storage/app/public" ]] || exit 1
elif [[ -e "$public_root/storage" ]]; then
    echo 'Katalog storage już istnieje; nie zostanie nadpisany.'; exit 1
else
    ln -s "$base/shared/storage/app/public" "$public_root/storage"
fi
# Znacznik wersji umożliwia sprawdzenie, czy PHP rzeczywiście obsługuje nowe wydanie.
printf '%s\n' "$release_id" > "$public_root/etb-release.txt"
chmod 644 "$public_root/etb-release.txt"
[[ ! -e "$base/current" || -L "$base/current" ]] || exit 1
[[ ! -e "$base/current.next" && ! -L "$base/current.next" ]] || exit 1
ln -s "$release" "$base/current.next"
mv -Tf "$base/current.next" "$base/current"
"$php_bin" artisan up
trap - ERR
# Brak czyszczenia wydań lub storage. Retencja dopiero po kopii zapasowej i testach.
echo 'Wdrożono staging. Wykonaj testy odbiorowe opisane w DEPLOYMENT.md.'
