# Publikacja ETB na eattheball.pl zamiast WordPressa

Jeśli domena została już zamieniona w Hostingerze, a działająca aplikacja pochodzi ze stagingu, rozpocznij od [naprawy po zmianie domeny](naprawa-po-zmianie-domeny.md). Ten wariant wymaga zachowania istniejącej bazy, uploadów i klucza aplikacji zamiast instalacji pustej produkcji.

Ta instrukcja dotyczy publicznej domeny `https://eattheball.pl`. Najpierw odbierz aplikację na stagingu, następnie przygotuj hosting i kopie zapasowe, a dopiero na końcu przełącz domenę w zaplanowanym oknie serwisowym.

Dokument opisuje pliki przygotowane w repozytorium oraz czynności do wykonania na koncie Hostinger i GitHub. Nie potwierdza bieżącego stanu serwera ani wykonania publikacji. Historyczne wyniki opisane w [DEPLOYMENT.md](../DEPLOYMENT.md) mają własne daty; nie zastępują nowego odbioru.

## 1. Sprawdź, co jest przygotowane

| Plik | Działanie |
| --- | --- |
| `.env.production.example` | Szablon osobnej konfiguracji produkcji, bez rzeczywistych sekretów. |
| `config/deployment.php` | Rozróżnia staging i produkcję przez `DEPLOYMENT_ENVIRONMENT`. |
| `scripts/check-production.php` | Sprawdza konfigurację przed migracją i po zapisaniu cache; nie sprawdza połączenia z bazą ani dostarczenia poczty. |
| `scripts/deploy-production.sh` | Instaluje wydanie w prywatnym katalogu, wykonuje migracje, publikuje zasoby i przełącza `current`. Odmawia działania w katalogu nadal zawierającym WordPressa. |
| `scripts/queue-production.sh` | Uruchamia harmonogram i krótki worker kolejki pod wspólną blokadą z wdrożeniem. |
| `.github/workflows/hostinger-production.yml` | Udostępnia ręczne wdrożenie z `main`, przez GitHub Environment `production`. |
| `.github/workflows/hostinger-verify.yml` | Uruchamia audyty, build i testy SQLite/MySQL oraz przygotowuje paczkę śledzonych plików. |

Skrypty nie przenoszą danych z WordPressa, nie tworzą administratora, nie konfigurują SMTP, DNS ani sekretów GitHub. Nie usuwają WordPressa. Te czynności wykonuje operator według kolejnych kroków.

Frontend korzysta z Tailwind 4.3.3 przez Vite. Zachowanie wcześniejszego wyglądu zapewniają motyw zgodności i preflight z Tailwind 3. Zaktualizowano również `concurrently` do 9.2.5 i `source-map-js` do 1.2.2. Odbiór wizualny jest nadal częścią publikacji.

Tailwind 4 wymaga aktualnych przeglądarek: Safari 16.4+, Chrome 111+ i Firefox 128+. Warstwa zgodności zachowuje dotychczasowe wartości stylów, ale nie przywraca obsługi starszych przeglądarek. Uwzględnij to w odbiorze urządzeń użytkowników. [Wymagania przeglądarek i migracja Tailwind](https://tailwindcss.com/docs/upgrade-guide).

## 2. Sprawdź projekt lokalnie w Windows

Użyj PHP co najmniej 8.2 z wymaganymi rozszerzeniami, Composer 2 i Node 22 co najmniej 22.12. Poniższe polecenia wykonuj w lokalnym projekcie, nie w produkcyjnym katalogu serwera:

```powershell
Set-Location -LiteralPath 'D:\Php Storm\etb'
git status --short
git diff --check
composer validate --no-check-publish
composer install --no-interaction --prefer-dist --optimize-autoloader
composer audit --locked --no-dev
npm ci
npm audit --audit-level=high
npm run build
php artisan config:clear
php artisan test --compact
```

Każde polecenie musi zakończyć się poprawnie przed przejściem dalej. Build powinien utworzyć `public/build/manifest.json`. Testy korzystają z konfiguracji testowej w `phpunit.xml`; nie uruchamiaj ich na bazie produkcyjnej. GitHub dodatkowo wykonuje testy na jednorazowej bazie MySQL 8, ponieważ lokalna SQLite nie potwierdza zgodności z MySQL hostingu.

Gdy audyt zgłasza podatności, sprawdź raport, popraw zależności lokalnie i ponów build oraz testy. `npm audit fix --force` może wprowadzić niezgodne zmiany głównych wersji i zmienić lockfile; wykonywanie go podczas CI omija przegląd konkretnego zestawu zależności. CI ma instalować zatwierdzony lockfile przez `npm ci` i sprawdzać audyt. Nie usuwaj audytu, nie dodawaj `|| true` i nie pomijaj zależności narzędzi budujących. [Zasady npm audit](https://docs.npmjs.com/cli/v11/commands/npm-audit/).

## 3. Przejrzyj i zapisz wyłącznie zamierzone zmiany

Sprawdź również istniejące wcześniej zmiany w katalogu roboczym:

```powershell
git diff --stat
git diff
git check-ignore .env .env.production auth.json
git ls-files -- '.env*'
```

Spośród plików `.env*` wolno śledzić wyłącznie szablony `.env.example` i `.env.production.example`. Pliki z prawdziwymi danymi połączeń, klucze SSH, logi, lokalne bazy, uploady, `vendor`, `node_modules` i wygenerowany `public/build` nie należą do commita. Zmienne `VITE_*` trafiają do zasobów publicznych, więc nie mogą zawierać sekretów.

Dodawaj sprawdzone pliki po nazwie, np. sam dokument:

```powershell
git add -- docs/publikacja-produkcji.md
git diff --cached --check
git diff --cached --stat
git diff --cached
```

To przykład, nie komplet plików potrzebnych do wydania. Osobnymi poleceniami `git add --` dodaj wszystkie ocenione poprawki aplikacji, zależności, pliki zgodności CSS, szablon produkcji, konfigurację, skrypty i workflow. Uwzględnij usunięcie starego `postcss.config.js`. Nie używaj `git add .` ani wymuszonego dodawania ignorowanych plików. Paczka wdrożenia zawiera tylko pliki śledzone w Git oraz build wykonany w CI; pominięty nowy plik nie trafi na serwer.

Dla zmian przygotowanych w tym zadaniu komplet ścieżek wygląda następująco. Najpierw przeczytaj diff, następnie wykonaj:

```powershell
$releaseFiles = @(
    '.env.example', '.env.production.example', '.gitignore', 'README.md', 'DEPLOYMENT.md',
    'docs/publikacja-produkcji.md', 'package.json', 'package-lock.json',
    'vite.config.js', 'tailwind.config.js', 'postcss.config.js',
    'resources/css/app.css', 'resources/css/preflight.css',
    'resources/css/legacy-utilities.css', 'resources/css/tailwind-legacy-theme.js',
    'resources/views/home.blade.php', 'resources/views/components/dropdown.blade.php',
    'resources/views/auth/register.blade.php', 'resources/views/auth/verify-email.blade.php',
    'resources/views/emails/verify-email.blade.php', 'app/Notifications/VerifyEmailNotification.php',
    'app/Models/User.php', 'app/Http/Controllers/ProfileController.php',
    'app/Http/Controllers/Auth/EmailVerificationNotificationController.php',
    'app/Http/Controllers/Admin/ShopSettingsController.php', 'app/Support/ShopSettings.php',
    'app/Support/DeploymentConfiguration.php', 'config/shop.php', 'config/deployment.php',
    'routes/web.php', 'routes/admin.php', 'routes/console.php',
    'app/Http/Controllers/Admin/ClubSectionController.php',
    'app/Http/Controllers/Admin/SiteLogoController.php',
    'app/Http/Controllers/PublicClubController.php', 'app/Http/Controllers/PublicScheduleController.php',
    'scripts/check-staging.php', 'scripts/check-production.php', 'scripts/deploy-staging.sh',
    'scripts/deploy-production.sh', 'scripts/queue-production.sh',
    '.github/workflows/hostinger-staging.yml', '.github/workflows/hostinger-production.yml',
    '.github/workflows/hostinger-verify.yml', 'tests/Feature/ProductionConfigurationTest.php',
    'tests/Feature/Auth/EmailVerificationTest.php', 'tests/Feature/Auth/RegistrationTest.php',
    'tests/Feature/ProfileTest.php', 'tests/Feature/MessageOrderTest.php',
    'tests/Feature/SponsorManagementTest.php'
)
git add -- $releaseFiles
git diff --cached --check
git diff --cached
```

Pliki z sześcioma wcześniejszymi błędami stylu poprawiono przez Pint, aby osobny workflow CI również przeszedł. Lista powyżej uwzględnia te zmiany. Przed zapisaniem sprawdź, czy nie dodano późniejszych, nieocenionych zmian w tych samych plikach.

Po przeglądzie zapisz commit, przeprowadź zwykły przegląd zmian i opublikuj zatwierdzoną wersję na `main`. Jeśli pracujesz w tym katalogu na potwierdzonej gałęzi `main`, użyj:

```powershell
git branch --show-current
git commit -m "Przygotowanie publikacji ETB i poprawa audytu zależności"
git push origin main
git rev-parse HEAD
```

Jeżeli pierwsze polecenie pokaże inną gałąź, użyj zwykłego procesu pull request i zatwierdzenia do `main`. Nie przełączaj gałęzi ani nie wymuszaj pushu, aby ominąć przegląd. Zapisz identyfikator commita przeznaczonego do wdrożenia. Nie publikuj nowej wersji tylko po to, aby ominąć nieudane kontrole. Push może uruchomić staging, jeśli `HOSTINGER_STAGING_ENABLED` jest włączone; produkcja nadal wymaga ręcznego uruchomienia.

## 4. Odbierz aplikację na stagingu

Wdróż ten sam commit na staging według [DEPLOYMENT.md](../DEPLOYMENT.md). Produkcja i staging mają osobne bazy, klucze aplikacji, storage, cookies i konfigurację. Nie kopiuj całego stagingowego `.env` do produkcji.

Przed przełączeniem domeny wykonaj rzeczywisty odbiór:

- **Poczta i konta:** dostarczenie kodu rejestracji i resetu hasła do kontrolowanej skrzynki, ponowna weryfikacja po zmianie e-maila, logowanie, wylogowanie i uprawnienia administratora oraz pozostałych ról. `MAIL_MAILER=log` lub `array` nie potwierdza dostarczania poczty.
- **Treść i wygląd:** strona główna, menu, tabele, drużyna, rozgrywki, aktualności, akademia, sponsorzy, kontakt, bilety, sklep i profil. Sprawdź telefon oraz komputer, odstępy, formularze, obwódki fokusu, rozwijane menu i animacje po aktualizacji Tailwinda.
- **Media:** dodawanie i edycja wszystkich używanych typów zdjęć, logotypów oraz galerii; publiczne zdjęcia muszą działać, a prywatne faktury być dostępne tylko przez uprawniony kontroler. Zweryfikuj limity PHP i wgrywaj duże galerie partiami, jeśli hosting ogranicza rozmiar żądania.
- **MySQL i zadania:** migracje na pustej bazie zgodnej z hostingiem, długie polskie teksty, kolejka, harmonogram oraz faktyczne wykonanie crona.
- **Dokumenty i sprzedaż:** kompletne dane podmiotu, kontakt, polityka prywatności, cookies, regulamin i zasady obsługi zamówień. Usuń treści demonstracyjne i ustal docelowe adresy stron.

Pierwszy administrator musi być rzeczywistym, zweryfikowanym kontem. Po rejestracji i potwierdzeniu tożsamości operator może w prywatnej konsoli `php artisan tinker` wyszukać dokładne konto i jednorazowo nadać mu `role=admin`. Nie wpisuj hasła do seedera, repozytorium ani polecenia utrwalanego w historii. Nie uruchamiaj demonstracyjnych seederów.

Istniejące konta z `email_verified_at=null` zostaną skierowane do potwierdzenia adresu przed wejściem do panelu lub danych prywatnych. Upewnij się, że operator ma dostęp do swojej skrzynki i odbiera polski link weryfikacyjny. Ekran pozwala ponowić wysyłkę i poprawić błędnie wpisany adres.

Sklep ma działać w trybie wiadomości. Zachowaj `SHOP_LEGACY_ENABLED=false`; domyślny tryb `message` nie uruchamia starej kasy. W panelu ustaw prawdziwy adres do zamówień oraz instrukcje potwierdzania dostępności, płatności, wysyłki i odbioru. Sprawdź generator wiadomości oraz blokadę bezpośrednich wywołań starej kasy i etykiet.

Przelewy24 wymaga osobnej naprawy i testów integracji, a dostawcy automatycznych etykiet InPost/DPD nadal zawierają stuby. Samo wpisanie kluczy nie uruchamia poprawnej sprzedaży online. Nie włączaj starego trybu przy tej publikacji; szczegóły ograniczeń opisuje [DEPLOYMENT.md](../DEPLOYMENT.md).

## 5. Zrób backup WordPressa i sprawdź odtwarzanie

Przed zmianą katalogu domeny:

1. Pobierz aktualną kopię wszystkich plików WordPressa, w tym mediów, ukrytych plików i `.htaccess`, oraz eksport odpowiadającej mu bazy. Zapisz datę i zakres kopii. hPanel udostępnia osobne kopie plików i baz; sprawdź, że wybierasz właściwą stronę i bazę. [Pobieranie kopii w Hostinger](https://www.hostinger.com/support/5981435-how-to-download-backups-at-hostinger/).
2. Zachowaj konfigurację domeny, przekierowania, DNS, certyfikatów, poczty i zadań WordPressa. Pobierz kopię także poza konto hostingowe. Archiwum i eksport SQL nie mogą być dostępne z katalogu WWW.
3. Sprawdź integralność archiwum oraz odtworzenie plików i bazy w oddzielnym, niepublicznym środowisku z inną bazą. Sprawdź treści, media i możliwość zalogowania. Nie wykonuj próby odtwarzania na działającym WordPressie ani stagingu.
4. Ustal osobę wykonującą przełączenie, okno serwisowe i warunek powrotu do WordPressa. Przygotuj instrukcję przywrócenia konkretnych plików, konfiguracji i bazy. Nie opieraj planu wyłącznie na istnieniu przycisku przywracania w hPanel.

Przywrócenie kopii zastępuje obecny stan danymi z jej daty. Przed późniejszym odtwarzaniem zachowaj również stan po publikacji. Nie używaj przywracania całego konta, jeśli mogłoby nadpisać staging lub inne strony. [Zakres odtwarzania kopii Hostingera](https://support.hostinger.com/en/articles/4283700-how-to-restore-backups-at-hostinger).

## 6. Przygotuj oddzielną produkcję poza WWW

Potwierdź w hPanel lub z pomocą hostingu: HTTPS dla domeny głównej i `www`, PHP WWW i CLI co najmniej 8.2, wymagane rozszerzenia, Composer 2, SSH, `rsync`, `flock`, `tar`, `sha256sum`, symlinki oraz dostęp PHP do prywatnego katalogu przez `open_basedir`. Worker wymaga możliwości egzekwowania timeoutu, w tym PCNTL. Nie zakładaj, że dawny test stagingu potwierdza dzisiejszą konfigurację produkcji.

Utwórz **nową, pustą bazę MySQL i osobnego użytkownika produkcji** z prawami do tej bazy. Nie używaj bazy WordPressa ani stagingu. Sprawdź wersję MySQL, InnoDB i utf8mb4. Import istniejących danych wymaga osobnego, przetestowanego planu; skrypt nie konwertuje bazy WordPressa i nie powinien wykonywać historii migracji na częściowo zaimportowanym schemacie.

Po SSH jako właściciel konta hostingowego:

```bash
umask 027
mkdir -p "$HOME/apps/etb-production/shared/storage" "$HOME/apps/etb-production/releases" "$HOME/apps/etb-production/incoming"
command -v php
php -v
php -m
command -v composer2
composer2 --version
command -v rsync
command -v flock
```

Jeśli hosting udostępnia inne ścieżki PHP lub Composer, potwierdź je i użyj tych rzeczywistych ścieżek. Po sprawdzeniu dostępnych poleceń zapisz:

```bash
command -v php > "$HOME/apps/etb-production/shared/php-bin"
command -v composer2 > "$HOME/apps/etb-production/shared/composer-bin"
printf '%s\n' "$HOME/domains/eattheball.pl/public_html" > "$HOME/apps/etb-production/shared/public-root"
```

`shared/.env` musi być zwykłym plikiem, a `shared/storage` zwykłym katalogiem, bez symlinków do stagingu. Produkcyjne skrypty akceptują wyłącznie katalog WWW `$HOME/domains/eattheball.pl/public_html`. Jeśli konto ma inną strukturę, zatrzymaj publikację i uzgodnij zmianę konfiguracji oraz zabezpieczeń skryptu.

## 7. Uzupełnij prywatną konfigurację produkcji

Na serwerze utwórz `~/apps/etb-production/shared/.env` na podstawie `.env.production.example`. Sekrety wpisuj w prywatnym edytorze, nie w repozytorium, rozmowie ani komendach zapisujących je do historii. Zastąp wszystkie wartości wymagające uzupełnienia rzeczywistymi danymi.

| Obszar | Wymagana konfiguracja |
| --- | --- |
| Środowisko | `APP_ENV=production`, `DEPLOYMENT_ENVIRONMENT=production`, `APP_DEBUG=false`, `APP_URL=https://eattheball.pl`, język `pl`, strefa `Europe/Warsaw`, konserwacja `file`. |
| Baza | `DB_CONNECTION=mysql` i dane nowej bazy produkcji; bez `DB_URL`. |
| Sesja | Sterownik `database`, cookie `etb_production_session`, szyfrowanie, Secure i HttpOnly włączone, SameSite `lax`, ścieżka `/`, `SESSION_DOMAIN=null`. |
| Cache i kolejka | `CACHE_STORE=database`, prefix `etb_production_cache_`, `QUEUE_CONNECTION=database`, `DB_QUEUE_RETRY_AFTER=90`. |
| Pliki | `MEDIA_DISK=public`, `FILESYSTEM_DISK=local`; tylko publiczna część uploadów będzie dostępna przez WWW. |
| Sklep | `SHOP_LEGACY_ENABLED=false`; uzupełnione prawdziwe dane sprzedawcy i ustawienia wiadomości. |
| Poczta | `MAIL_MAILER=smtp`, prawdziwy serwer, użytkownik, hasło i zatwierdzony adres nadawcy; bez `MAIL_URL`. |

SMTP musi korzystać z portu 587 i `MAIL_ENCRYPTION=tls` albo portu 465 i `MAIL_ENCRYPTION=ssl`. Jeżeli ustawiasz `MAIL_SCHEME`, dobierz odpowiednio `smtp` albo `smtps`. Zachowaj weryfikację certyfikatu i timeout 20 sekund. Sprawdź nadawcę oraz SPF/DKIM/DMARC u dostawcy poczty. Kontrola konfiguracji nie zastępuje testu dostarczenia kodu i linku resetu.

Przy pierwszym wdrożeniu pozostaw dokładnie `APP_KEY=`. Skrypt wygeneruje osobny klucz produkcji, jeśli `current` jeszcze nie istnieje. Zachowaj ten klucz w kolejnych wydaniach i backupach; jego zmiana unieważni szyfrowane dane i sesje. Nie używaj klucza lokalnego ani stagingowego.

```bash
chmod 600 "$HOME/apps/etb-production/shared/.env"
```

Nie ustawiaj `TRUSTED_PROXIES=*` bez potwierdzenia konfiguracji proxy. Nie zmieniaj sterowników sesji, cache ani kolejki, aby ominąć błędną konfigurację. Nie kopiuj do produkcji demonstracyjnych kont, prywatnych logów ani niepotrzebnych danych stagingu.

## 8. Przygotuj GitHub, ale jeszcze nie włączaj publikacji

1. Utwórz GitHub Environment o technicznej nazwie `production`, oddzielny od `staging`. Ogranicz wdrożenia do `main`; jeśli plan GitHub pozwala, ustaw osoby zatwierdzające i ochronę środowiska.
2. W `production` skonfiguruj osobno `HOSTINGER_SSH_HOST`, `HOSTINGER_SSH_PORT`, `HOSTINGER_SSH_USER`, `HOSTINGER_SSH_KEY` i `HOSTINGER_SSH_KNOWN_HOSTS`. Użyj dedykowanego klucza wdrożeniowego; autoryzuj klucz publiczny na hostingu. Zweryfikuj fingerprint serwera niezależnie z hostingiem. Nie wyłączaj sprawdzania klucza hosta.
3. Zmienną **repozytorium** `HOSTINGER_PRODUCTION_ENABLED` pozostaw wyłączoną do przygotowania katalogu WWW. Nie wystarczy utworzenie zmiennej tylko w Environment, ponieważ warunek workflow korzysta ze zmiennej repozytorium.
4. Sprawdź obecność nowych plików w zatwierdzonym `main` i pozytywne kontrole tego commita. Workflow produkcji uruchamia się tylko ręcznie przez `workflow_dispatch`; zwykły push nie publikuje produkcji.

Nazwy sekretów są takie jak dla stagingu, lecz wartości są skonfigurowane oddzielnie w Environment `production`. Prawdziwy `.env` pozostaje na serwerze, poza paczką i sekretami workflow.

## 9. Przygotuj treści oraz adresy po WordPressie

Sporządź listę dotychczasowych adresów WordPressa, szczególnie stron z ruchem, aktualności, kontaktu i plików mediów. Dla każdego wskaż docelowy adres albo uzasadnione 404/410. Przygotuj i przetestuj przekierowania 301 bez pętli oraz poprawne adresy kanoniczne. Nie przekierowuj automatycznie wszystkich brakujących stron na stronę główną.

Skrypt generuje HTTPS i kanoniczną domenę bez `www`, ale nie tworzy mapy dawnych adresów ani sitemap. Własne przekierowania wdrażaj jako przejrzaną zmianę aplikacji lub bazowego `public/.htaccess`; ręczne dopiski w docelowym `.htaccess` zostaną zastąpione przy kolejnym wydaniu. Przekierowania dawnych `/wp-content/...` wymagają świadomego wyjątku, ponieważ skrypt blokuje stare ścieżki WordPressa.

Przygotuj właściwe treści, media i dokumenty przed przełączeniem. Uzgodnij sposób wprowadzenia ich do nowej bazy po pierwszych migracjach i przećwicz go na kopii. Nie traktuj wdrożenia pustej bazy jako automatycznego przeniesienia treści. Jeżeli wprowadzenie danych wymaga czasu, utrzymaj okno serwisowe do końca odbioru produkcji.

## 10. Przełącz domenę ręcznie w oknie serwisowym

Wykonuj ten krok dopiero po odbiorze stagingu, sprawdzeniu backupu i planu powrotu.

1. Wstrzymaj edycję i zapisy WordPressa, wykonaj końcową kopię jego plików i bazy. Ustal z hostingiem tymczasową odpowiedź 503 dla odwiedzających. Przy pierwszym wdrożeniu `current` powstanie dopiero na końcu instalacji, więc sam plik wejściowy Laravel nie zapewni wcześniej strony konserwacji. Ochrona okna odbioru musi przetrwać zmianę publicznego `.htaccess` przez deploy.
2. Sprawdź document root `test.eattheball.pl`. Jeśli staging używa `~/domains/eattheball.pl/public_html/test`, najpierw odseparuj go z pomocą hPanel lub hostingu i zaktualizuj jego `shared/public-root`, plik wejściowy oraz pozostałą konfigurację. Zachowanie tego katalogu na miejscu wymaga świadomego planu i testu obu domen. Nie przenoś stagingu razem z WordPressem ani nie kasuj całego `public_html`.
3. Wyprowadź wyłącznie rozpoznane pliki i katalogi WordPressa do zabezpieczonego archiwum poza wszystkimi katalogami WWW. Uwzględnij jego ukryte pliki i `.htaccess`; pozostaw pliki hostingu i obsługi certyfikatu, jeżeli nie należą do WordPressa. Nie usuwaj bazy WordPressa. Nie zostawiaj aktywnej kopii w `public_html/wordpress`, `public_html/backup` ani innym publicznym podkatalogu.
4. Potwierdź, że przygotowany katalog domeny nie zawiera już `wp-config.php`, `wp-admin`, `wp-includes`, `wp-content` ani `wp-login.php`, a każdy pozostawiony element ma znane przeznaczenie. Przy pozostawionym stagingu nie można uznać całego katalogu za pusty; zachowaj jego izolację i sprawdź wpływ reguł nadrzędnych.
5. W przygotowanym katalogu domeny utwórz pusty marker `.etb-production` i zwykły `index.php`. Użyj pełnej ścieżki wynikającej z rzeczywistego `$HOME`, nie dosłownego znaku `~` w PHP. Poniższe polecenia odmawiają nadpisania istniejącego pliku wejściowego:

```bash
production_public="$HOME/domains/eattheball.pl/public_html"
[[ -d "$production_public" && ! -L "$production_public" ]] || exit 1
[[ "$(realpath "$production_public")" == "$production_public" ]] || exit 1
for wordpress_path in wp-config.php wp-admin wp-includes wp-content wp-login.php; do
    [[ ! -e "$production_public/$wordpress_path" && ! -L "$production_public/$wordpress_path" ]] || exit 1
done
[[ ! -e "$production_public/index.php" && ! -L "$production_public/index.php" ]] || exit 1
[[ ! -e "$production_public/.etb-production" && ! -L "$production_public/.etb-production" ]] || exit 1
touch "$production_public/.etb-production"
printf '%s\n' '<?php' "require '$HOME/apps/etb-production/current/public/index.php';" > "$production_public/index.php"
chmod 644 "$production_public/index.php"
```

6. Nie kopiuj repozytorium, `vendor`, `.env`, eksportu SQL ani całego storage do WWW. Docelowy katalog zawiera jedynie plik wejściowy i publiczne zasoby; kod pozostaje w `~/apps/etb-production/releases`.
7. Ustaw repozytoryjne `HOSTINGER_PRODUCTION_ENABLED=true`. W GitHub uruchom ręcznie workflow **Ręczna publikacja eattheball.pl** dla `main`; sprawdź identyfikator commita i zatwierdź Environment, jeśli jest chronione. Obserwuj cały wynik, nie tylko krok builda.

Workflow najpierw wykonuje audyty i testy SQLite/MySQL oraz buduje paczkę. Na serwerze deploy sprawdza sumę paczki i konfigurację, instaluje Composer bez zależności developerskich, generuje klucz tylko przy pierwszym uruchomieniu, tworzy cache, włącza konserwację, wykonuje migracje, kopiuje publiczne zasoby i atomowo przełącza symlink `current`. Nie uruchamia seederów ani nie usuwa starszych wydań i mediów.

Publiczny `.htaccess` powstaje z reguł HTTPS/kanonicznej domeny oraz `public/.htaccess` wydania. Produkcja otrzymuje `robots.txt` z `Allow: /`, bez stagingowego nagłówka `noindex`. Plik `etb-release.txt` pozwala sprawdzić identyfikator opublikowanego wydania. Nie przenoś stagingowych ograniczeń indeksowania do produkcji.

## 11. Uruchom cron i odbierz produkcję przed otwarciem

Po poprawnej instalacji wykonaj przez SSH kontrolę i pierwszy przebieg kolejki:

```bash
cd "$HOME/apps/etb-production/current"
production_php=$(cat "$HOME/apps/etb-production/shared/php-bin")
"$production_php" scripts/check-production.php
bash scripts/queue-production.sh
```

W hPanel skonfiguruj zadanie własne co minutę uruchamiające `/bin/bash` i pełną ścieżkę do `~/apps/etb-production/current/scripts/queue-production.sh`. Ustal tę ścieżkę poleceniem `printf '%s\n' "$HOME/apps/etb-production/current/scripts/queue-production.sh"`; w polu hPanel wpisz otrzymaną ścieżkę, nie znak `~`.

Worker kończy pracę po opróżnieniu kolejki lub ustalonym limicie i używa wspólnego `operation.lock`, dzięki czemu nie pracuje podczas wdrożenia. Harmonogram aplikacji ma własną strefę `Europe/Warsaw`; godziny hPanel są w UTC, natomiast zadanie uruchamiane co minutę nie wymaga przeliczania czasu sezonowego. Sprawdź faktyczne wykonania, prywatne logi i `queue:failed`. Nie dodawaj `--force`, które omijałoby konserwację. [Konfiguracja crona w Hostinger](https://www.hostinger.com/support/1583465-how-to-set-up-a-cron-job-at-hostinger/).

W chronionym oknie odbioru wprowadź uzgodnione treści i media, zarejestruj rzeczywiste konto administratora oraz nadaj rolę jak na stagingu. Nie przenoś hasła ani klucza aplikacji ze stagingu. Zweryfikuj na domenie produkcji:

- prawdziwe dostarczenie rejestracji, resetu i ponownej weryfikacji e-maila przez produkcyjne SMTP;
- stronę główną, treści, dokumenty prawne, formularze, profile, sklep wiadomości, zdjęcia i prywatne dokumenty;
- trwałość danych MySQL, sesji i uploadów, prawidłowe cookie produkcji, cron i kolejkę;
- `http://eattheball.pl` i `https://www.eattheball.pl` przekierowujące na `https://eattheball.pl`, działający certyfikat, brak pętli;
- zgodność `etb-release.txt` z wydaniem, dostępność CSS/JS bez 404, brak publicznego `hot` i poprawne 404 dla nieistniejącej strony;
- brak udostępnienia `/.env`, `/.git`, prywatnego kodu i faktur oraz dawnych paneli WordPressa;
- brak `noindex` w nagłówkach i treści produkcji, poprawne przekierowania starych adresów, metadane oraz uzgodnioną sitemap i jej zgłoszenie w narzędziach wyszukiwarki.

Dopiero po tym odbiorze zdejmij tymczasową osłonę 503 i pozwól na normalne zapisy. Sprawdź stronę również bez zalogowania i z telefonu. Zapisz wynik odbioru oraz identyfikator wydania.

## 12. Przygotuj powrót i dalsze aktualizacje

Jeśli deploy przerwie się po `artisan down`, aplikacja pozostanie w konserwacji. Zachowaj log workflow i sprawdź prywatne logi serwera. Nie otwieraj strony przez samo `artisan up`, dopóki przyczyna nie zostanie usunięta. Kontrola konfiguracji może zakończyć się przed migracją; jej komunikat nie oznacza, że serwer sprawdził SMTP lub backup.

Powrót do starszego wydania Laravel jest możliwy wyłącznie po potwierdzeniu zgodności jego kodu z bieżącym schematem bazy. Operator powinien przejąć `shared/operation.lock`, włączyć konserwację, atomowo wskazać `current` na zachowane wydanie, odtworzyć cache widoków i dopiero po kontroli wyłączyć konserwację. Skrypty pozostawiają dawne zasoby, ale kopia publicznych plików może wymagać odtworzenia z właściwego wydania. Nie wykonuj automatycznego `migrate:rollback`: migracja MySQL nie zapewnia odwrócenia całego wdrożenia.

Jeśli trzeba przywrócić WordPressa, utrzymaj 503, wyłącz produkcyjny workflow i cron, zachowaj nowe dane Laravel oraz jego bazę i storage. Przywróć konkretne pliki wejściowe, reguły i zasoby WordPressa z prywatnej kopii, nie nadpisując zachowanego stagingu. Jeżeli jego baza pozostała nienaruszona, nie przywracaj jej bez potrzeby. Gdy odtworzenie bazy jest konieczne, uwzględnij zapisy od chwili backupu. Sprawdź WordPressa przed zakończeniem konserwacji. Produkcyjny marker Laravel usuwa operator w ramach świadomego powrotu; nie uruchamiaj wtedy jego deployu.

Po publikacji wykonuj kopie produkcyjnej bazy, `shared/storage` i prywatnej konfiguracji, przechowywane poza WWW i również poza kontem. Testuj odtwarzanie. Monitoruj logi, dostarczanie poczty, błędy kolejki, miejsce na dysku i stare adresy zwracające 404. Skrypty nie mają automatycznej retencji wydań; czyszczenie wymaga osobnego planu i zachowania wersji potrzebnych do powrotu.

Nie stosuj w produkcji `composer run setup`, `composer update`, `npm run dev`, `migrate:fresh`, `migrate:refresh`, `db:wipe`, `db:seed`, `git clean -fdx` ani synchronizacji z `--delete`. Kolejne wydanie przechodzi ten sam audyt i odbiór stagingu, otrzymuje backup przed migracją, a następnie jest publikowane ręcznie z zatwierdzonego `main`.

## Źródła oficjalne

- [npm: audyt zależności, progi błędów i działanie opcji force](https://docs.npmjs.com/cli/v11/commands/npm-audit/).
- [Tailwind CSS: migracja do wersji 4 i zmiany zgodności](https://tailwindcss.com/docs/upgrade-guide).
- [Hostinger: pobieranie kopii plików i baz](https://www.hostinger.com/support/5981435-how-to-download-backups-at-hostinger/).
- [Hostinger: odtwarzanie kopii i wybór zakresu](https://support.hostinger.com/en/articles/4283700-how-to-restore-backups-at-hostinger).
- [Hostinger: konfiguracja zadań cron](https://www.hostinger.com/support/1583465-how-to-set-up-a-cron-job-at-hostinger/).
- [Hostinger: cron uruchamiający skrypt powłoki](https://www.hostinger.com/support/5646919-how-to-set-up-a-cron-job-with-special-characters-at-hostinger/).

Dokumentację dostawcy sprawdzono podczas przygotowania tej instrukcji. Dostępne funkcje hPanel i ograniczenia konkretnego planu musi potwierdzić właściciel konta przed publikacją.
