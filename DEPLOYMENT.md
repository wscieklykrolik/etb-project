# ETB — wdrożenie na Hostingerze

## Status audytu i stagingu (23.09.2026)

**STAGING READY** pod `https://test.eattheball.pl`. Laravel działa na Hostingerze z PHP 8.3.33, MySQL, produkcyjnym buildem Vite i trwałym storage. Wykonano także drugie wdrożenie kontrolne. **NOT READY FOR DEPLOYMENT** dotyczy nadal późniejszego przełączenia publicznej domeny `eattheball.pl`. WordPress pod domeną główną pozostał nietknięty. Nie wykonano commita ani pushu.

Po zmianie sposobu sprzedaży domyślnie działa generator zamówień przez Instagram/e-mail. Poniższe problemy P24 i automatycznych etykiet blokują wyłącznie ponowne włączenie starej kasy; nie są zależnościami ręcznej sprzedaży. Płatności online i automatyczne etykiety nadal wymagają naprawy przed przywróceniem starej kasy lub uruchomieniem ich na domenie produkcyjnej.

1. **Płatności Przelewy24:** `Przelewy24Gateway::sign()` używa tego samego zestawu pól dla rejestracji, powiadomienia i weryfikacji, ignoruje argument `orderId`. API wymaga różnych podpisów. Kontroler nie porównuje pełnego `payment_session_id`, kwoty i waluty z zamówieniem i nie blokuje rekordu przy równoległych webhookach. Webhook jest dodatkowo objęty CSRF (zewnętrzne powiadomienia dostaną 419), a domyślny URL powrotu nie zawiera wymaganego `{order}`. Nie wyłączono CSRF przy niepoprawnym weryfikatorze. Przed ponownym włączeniem płatności online potrzebna jest naprawa integracji i testy sandbox: poprawna płatność, błędny podpis/kwota, powtórzenie i równoczesne powiadomienia, odrzucenie/anulowanie oraz powrót klienta. Samo wpisanie kluczy nie wystarczy.
2. **InPost/DPD:** `InPostShippingProvider` i `DpdShippingProvider` zawierają stuby: generują fikcyjne numery przesyłek i etykiety `#LABEL-STUB...`. W trybie wiadomości etykiety są zablokowane na serwerze, wysyłkę nadaje administrator ręcznie przez Szybkie Nadania. Cenę Paczkomatu B odczytuje osobny serwis z publicznego cennika; nie używa stubów ani tokenu ShipX.
3. **Hostinger:** potwierdzono SSH, PHP CLI 8.3.33, Composer 2.9.8, MySQL, `flock`, `rsync`, cache Laravel, symlinki i obsługę plików statycznych. PHP `exec()` jest wyłączone, dlatego skrypt tworzy link storage bezpośrednio przez `ln -s`. Automatyczny workflow GitHub pozostaje domyślnie wyłączony do zapisania sekretów i zmiennej środowiska repozytorium.
4. **Pozostały odbiór ręczny:** SMTP celowo zastępuje `MAIL_MAILER=log`. Nadal trzeba utworzyć pierwszego administratora i ręcznie przejść CRUD treści, uploady wszystkich typów oraz pobranie prywatnej faktury. Nie wolno przełączać domeny głównej przed tym odbiorem.

Nie podłączaj do stagingu danych klientów ani rzeczywistych płatności. Staging jest publicznie dostępny, ale wysyła `X-Robots-Tag: noindex, nofollow, noarchive` i ma `robots.txt` z `Disallow: /`. Jeśli ma zawierać poufne dane, dodaj ochronę HTTP w hPanel. Zmiany istniejące w katalogu roboczym przed audytem zachowano; muszą znaleźć się w ocenionym commicie, jeśli mają być wdrożone.

## Wyniki przeglądu repozytorium

| Obszar | Ustalenia i działanie |
| --- | --- |
| Composer | Laravel 12, PHP `^8.2`, Dompdf, Flysystem/S3. `composer.json` i lock są zgodne; lokalne wymagania platformy spełnione. Audyt produkcyjnych zależności Composer nie zgłosił podatności. Nie wykonywano `composer update`. |
| Frontend | Blade, Tailwind 3, Alpine, Vite 7. Wejścia `resources/css/app.css` i `resources/js/app.js`; Vite generuje `public/build/manifest.json`. Build jest ignorowany przez Git i musi być dostarczony w paczce CI. Node 22 (aktualna wersja 22.x; co najmniej 22.12) tylko w CI. |
| npm | Pełny audyt, również devDependencies wchodzących do bundla, wykrył 11 podatności. Poprawiono wersje w `package-lock.json` przez `npm audit fix --ignore-scripts`, bez `--force`. Ponowny audyt: 0 podatności. Zakresy `package.json` pozostają bez zmian. |
| Sekrety | `.env`, `.env.*`, `auth.json`, prywatne klucze storage, logi i uploady są ignorowane. Jedynym śledzonym `.env*` jest `.env.example`; usunięto dawny wyjątek dla Railway. Nie czytano ani nie kopiowano lokalnego `APP_KEY` do wdrożenia. `.env.example` opisuje staging, SMTP, integracje, sesje i cache bez rzeczywistych sekretów. To kontrola bieżącego drzewa, nie audyt całej historii Git. |
| Logowanie | Rate limiting w `LoginRequest`, regeneracja sesji przy logowaniu, unieważnienie sesji i tokenu CSRF przy wylogowaniu. Silne hasła i Argon2id; na serwerze trzeba potwierdzić obsługę Argon2. |
| Aktywacja i reset | Rejestracja tworzy konto dopiero po kodzie e-mail; kod jest hashowany, ma TTL i limit prób. Reset korzysta z brokera Laravel i neutralnej odpowiedzi chroniącej przed ujawnieniem istnienia konta. Pocztę aktywacji/resetu wysyła synchronicznie — potrzebuje działającego SMTP, nie workera. |
| Ponowna weryfikacja e-mail | `User` nie implementuje `MustVerifyEmail`; mimo istniejących tras podpisanych middleware `verified` nie wymusza weryfikacji. Rejestracja kodem działa, ale po zmianie adresu w profilu nie ma skutecznego wymuszenia ponownej weryfikacji. Pozostawiono obecną politykę kont; przed uznaniem nowego adresu za potwierdzony trzeba uzupełnić ten przepływ. |
| Role | Panel używa `auth` i `role:admin,employee`; zmiana ról, logotypy i eksport e-maili wymagają administratora. Mutacje zawodników/aktualności/meczów mają policies i autoryzację FormRequest. `role` jest źródłem uprawnień; samo `is_admin` nie nadaje uprawnień. |
| CSRF i błędy | Pozostawiono ochronę CSRF. Wyłączenie debugowania w szablonie produkcyjnym. Globalny composer widoków pomija widoki błędów, aby awaria DB nie wywoływała kolejnych odczytów logotypów podczas renderowania błędu. Zaufane proxy są ładowane z cache konfiguracji, po jego inicjalizacji. |
| Sesje/cache | Pozostawiono sterowniki database i istniejące migracje tabel. Staging ma osobne cookie, prefix cache, bazę i APP_KEY; `SESSION_DOMAIN=null`, HTTPS, HttpOnly, SameSite=Lax, szyfrowanie sesji. |
| Queue | Trzy joby wysyłają maile zamówień. Zachowano `database`; krótki worker z crona kończy pracę po opróżnieniu kolejki lub limicie. Ma retry/backoff, timeout krótszy niż `retry_after` i wspólny lock z deploymentem. |
| Scheduler | W `routes/console.php` nie ma zaplanowanych zadań. Publikacja aktualności wynika z daty w zapytaniach, nie z crona. `lzkosz:sync-table` jest poleceniem ręcznym. Nie ma potrzeby uruchamiania `schedule:run` tylko dla obecnego kodu. |
| Migracje i SQL | Przejrzano wszystkie migracje oraz miejsca surowych zapytań (`LOWER`, agregacja `count`, kopiowanie kolumn). Nie znaleziono zapytań zależnych od `strftime`/PRAGMA SQLite. Naprawiono za długie nazwy FK 3x3 i indeksu pivotu produktów. Poszerzono URL-e i teksty akceptowane przez formularze, bo SQLite nie egzekwował limitów VARCHAR/TEXT MySQL. MySQL pozostaje w trybie strict, utf8mb4. Testy obejmują długie polskie treści. |
| Stare migracje danych | Historyczna migracja usuwa sekcję `investors`, inna scala mecze i usuwa stare tabele `games`/`matches`. Bezpieczna instalacja startuje na NOWEJ, PUSTEJ bazie. Nie uruchamiaj całej historii na częściowo przeniesionej bazie. Transfer istniejących danych wymaga osobnego planu i próby na kopii. |
| Seedery | `DatabaseSeeder` zawiera konta demonstracyjne ze znanymi hasłami, a `ContentSeeder` aktualizuje treści i usuwa warianty produktów. Oba otrzymały blokadę poza `local/testing`. Żaden seeder nie jest elementem deploymentu. |
| Treści i zasoby | Nie zmieniano designu. Stałe grafiki pozostają w `public/images`; admin nie zapisuje tam uploadów. Blade escapuje treści; istniejące użycia `nl2br(e(...))` zachowano. Dompdf zapisuje faktury poza publicznym storage. |

### Inwentaryzacja plików

Wszystkie znalezione uploady obrazów używają `App\Support\MediaStorage` → `MEDIA_DISK=public` → `storage/app/public`. Nazwy plików generuje Laravel; nie używa się nazwy klienta jako ścieżki. Formularze sprawdzają typ obrazu i rozmiar.

- aktualności: `news/main`, `news/gallery` (do 100 zdjęć, każde do 5 MiB);
- zawodnicy: `players`; sztab i członkowie 3x3: katalogi przekazywane do `MediaCardService`;
- turnieje: `3x3-tournaments`, logotypy drużyn: `3x3-team-logos`;
- sponsorzy: `sponsors`; logotypy strony: `logos`;
- przeciwnicy/mecze: `opponents`, `team-logos`, `matches`, `logos`;
- sekcje klubu: `club/{slug}`; bilety: `tickets`; ważne strony: `important-pages`;
- produkty: `products`;
- faktury: dysk `local`, czyli **prywatne** `storage/app/private/invoices`; pobieranie przez autoryzowany kontroler panelu.

Usunięcie zdjęcia przez administratora nadal usuwa plik zgodnie z funkcją aplikacji. Deployment nie usuwa mediów. Operacje DB i filesystem nie są wszędzie atomowe — błąd zapisu może pozostawić osierocony plik; nie uruchamiaj automatycznego czyszczenia bez porównania z bazą i backupu. `media:sync` to osobne narzędzie, nie krok wdrożenia; na Hostingerze nie jest potrzebne S3.

Limity PHP muszą odpowiadać formularzom: `upload_max_filesize` co najmniej 5M, `max_file_uploads` co najmniej 101 dla pełnej galerii z obrazem głównym, `post_max_size` odpowiednio większe od sumy przesyłanych plików (najgorszy przypadek przekracza 500 MiB). Jeżeli plan nie pozwala na taki request, dodawaj galerię partiami; nie obiecuj 100 zdjęć naraz przed sprawdzeniem limitów hPanel i serwera WWW.

## Architektura wdrożenia

Wybrano **GitHub Actions → paczka kodu + Vite → SSH → Hostinger**. Zwykły deploy Git w hPanel nie gwarantuje uruchomienia builda i bezpiecznego położenia kodu. Nie konfiguruj równolegle drugiego mechanizmu wdrażającego to samo drzewo.

```text
/home/UZYTKOWNIK/apps/etb-staging/       (poza wszystkimi katalogami WWW)
  shared/
    .env                               (sekrety tylko na serwerze)
    storage/                           (media, faktury, sesje plikowe, logi)
    public-root, php-bin, composer-bin  (lokalne ścieżki)
    staging-access.htaccess            (reguły ochrony stagingu)
    staging.htpasswd                   (hash hasła HTTP)
    operation.lock
  incoming/IDENTYFIKATOR/               (paczka i suma kontrolna)
  releases/IDENTYFIKATOR/               (kod konkretnego commita)
    .env -> ../../shared/.env
    storage -> ../../shared/storage
    public/storage -> shared/storage/app/public (storage:link)
  current -> releases/IDENTYFIKATOR

/home/UZYTKOWNIK/domains/test.eattheball.pl/public_html/
  index.php                            (mały plik wejściowy poniżej)
  .htaccess, robots.txt, favicon.ico
  build/, images/                      (wyłącznie zasoby public/)
  storage -> /home/UZYTKOWNIK/apps/etb-staging/shared/storage/app/public
```

Alternatywny obsługiwany document root subdomeny to `/home/UZYTKOWNIK/domains/eattheball.pl/public_html/test`. Katalog musi być nowy i przeznaczony wyłącznie dla subdomeny. Nie zmieniaj `.htaccess`, `index.php`, DNS ani plików w głównym katalogu WordPressa. Jeśli katalog `test` już ma jakąkolwiek zawartość, zatrzymaj konfigurację i ustal inną izolację z hostingiem.

Publiczny katalog zawiera tylko odpowiedniki Laravel `public`. Kod, vendor, baza, `.env`, backupy i całe storage nie mogą się tam znaleźć. Publiczny symlink prowadzi wyłącznie do `storage/app/public`. Nie używaj reguły przekierowującej z publicznego katalogu zawierającego całe repo do `public/` jako jedynej ochrony.

## Pierwsze wdrożenie — przygotowanie serwera

1. W hPanel utwórz **wyłącznie** subdomenę `test.eattheball.pl`, sprawdź jej document root i włącz HTTPS. Nie zmieniaj domeny głównej/WordPressa. Potwierdź obsługę PHP 8.2+, SSH, Composer 2, crona, symlinków, `rsync`, `flock`, `tar`, `sha256sum` oraz dostęp PHP do prywatnego katalogu przez `open_basedir`. PHP WWW i CLI muszą mieć zgodne rozszerzenia; wymagane są m.in. PDO MySQL, mbstring, DOM/XML, fileinfo, OpenSSL, cURL i GD dla obrazów. PCNTL jest potrzebne do egzekwowania timeoutu workera.
2. Utwórz osobną, pustą bazę MySQL i użytkownika w hPanel. Nie używaj bazy WordPressa. Wybierz utf8mb4/InnoDB; sprawdź `SELECT VERSION()` na docelowej bazie. Jeśli wersja różni się od CI, powtórz na niej test migracji przed właściwą instalacją.
3. Po SSH, jako użytkownik hostingu, utwórz katalogi:

```bash
umask 027
mkdir -p "$HOME/apps/etb-staging/shared/storage" "$HOME/apps/etb-staging/releases" "$HOME/apps/etb-staging/incoming"
command -v php
php -v
php -m
command -v composer2
composer2 --version
command -v rsync
command -v flock
```

Zapisz do `shared/php-bin` pełną potwierdzoną ścieżkę PHP i do `shared/composer-bin` pełną ścieżkę Composer 2, po jednej linii. Composer musi używać kompatybilnego PHP; nie wystarczy zmienić wersji wyłącznie w hPanel. Przykład po potwierdzeniu poleceń:

```bash
command -v php > "$HOME/apps/etb-staging/shared/php-bin"
command -v composer2 > "$HOME/apps/etb-staging/shared/composer-bin"
printf '%s\n' "$HOME/domains/test.eattheball.pl/public_html" > "$HOME/apps/etb-staging/shared/public-root"
```

4. Utwórz prywatny `shared/.env` na podstawie `.env.example`. Wpisz dane MySQL/SMTP **na serwerze**, nie w repo ani w komendach zapisywanych do historii. Pozostaw `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://test.eattheball.pl`, `MEDIA_DISK=public`, `FILESYSTEM_DISK=local`, `QUEUE_CONNECTION=database`, bez domeny współdzielonej dla cookies. Uzupełnij adres nadawcy. `APP_KEY=` pozostaw pusty przy pierwszym wdrożeniu: skrypt wykona `php artisan key:generate --force` tylko przy braku `current`. Przy kolejnych wdrożeniach klucz zostaje zachowany. Przyszła produkcja dostaje osobny klucz; nigdy lokalny ani klucz stagingu.

```bash
chmod 600 "$HOME/apps/etb-staging/shared/.env"
```

5. Skonfiguruj dodatkową ochronę HTTP stagingu, np. interaktywnie przez `htpasswd` (jeśli dostępne) albo z pomocą hostingu. Plik haseł umieść w `shared/staging.htpasswd`, nigdy w publicznym katalogu/repo. Utwórz `shared/staging-access.htaccess` z regułami poniżej, zastępując `UZYTKOWNIK` faktyczną nazwą. Plik jest dopisywany do `.htaccess` przy każdej aktualizacji, więc ochrona nie znika po deployu:

```apache
AuthType Basic
AuthName "Staging ETB"
AuthUserFile /home/UZYTKOWNIK/apps/etb-staging/shared/staging.htpasswd
Require valid-user
<IfModule mod_headers.c>
    Header always set X-Robots-Tag "noindex, nofollow, noarchive"
</IfModule>
```

6. Sprawdź, że document root stagingu jest nowy/pusty i **nie jest katalogiem WordPressa**. Umieść w nim `.etb-staging` jako pusty znacznik oraz `.htaccess` z powyższą ochroną. Utwórz tam `index.php` (z właściwą ścieżką użytkownika):

```php
<?php
require '/home/UZYTKOWNIK/apps/etb-staging/current/public/index.php';
```

Nie kopiuj całego projektu do tego katalogu. Laravel nadal zna swój prywatny katalog `public` i manifest danego wydania; publiczny katalog WWW zawiera te same pliki Vite. Kod wejściowy nie zawiera sekretów. Serwer musi wykonywać PHP, a nie zwracać jego kod jako tekst.

7. Utwórz kopie zapasowe konfiguracji i sprawdź odtwarzanie. Przed każdą aktualizacją z migracją wymagany jest backup bazy oraz wspólnego storage. Kopie przechowuj poza WWW, z ograniczonym dostępem i najlepiej również poza kontem hostingowym.

Uprawnienia: `.env` 600, prywatne pliki zwykle 640 i katalogi 750; `storage` i `bootstrap/cache` muszą być zapisywalne przez tego samego użytkownika PHP/SSH. Publiczne zasoby 644, katalogi 755. Nie stosuj 777. Potwierdź prawa przejścia przez katalogi nadrzędne i odczyt przez serwer WWW; nie rozszerzaj uprawnień do całego prywatnego projektu.

## GitHub Actions i pierwsze wydanie

Workflow: `.github/workflows/hostinger-staging.yml`. Działa dla `main` i `develop`, testuje SQLite i MySQL 8, buduje Vite i zapisuje paczkę jako artefakt. Deployment jest osobnym krokiem, domyślnie wyłączonym.

1. Sprawdź `git status` i `git diff`; repo zawiera również wcześniejsze zmiany użytkownika. Upewnij się, że `.env` nie jest śledzony (`git check-ignore .env`, `git ls-files '.env*'`). Nie wymuszaj dodawania ignorowanych plików.
2. W GitHub utwórz Environment `staging`. Sekrety środowiska: `HOSTINGER_SSH_HOST`, `HOSTINGER_SSH_PORT`, `HOSTINGER_SSH_USER`, `HOSTINGER_SSH_KEY` (dedykowany prywatny klucz SSH), `HOSTINGER_SSH_KNOWN_HOSTS` (zweryfikowany klucz hosta, z formatem `[host]:port` dla niestandardowego portu). Klucz publiczny autoryzuj na hostingu. Fingerprint hosta potwierdź niezależnie w hPanel/u dostawcy; nie wyłączaj `StrictHostKeyChecking`.
3. Zmienna **repozytorium** `HOSTINGER_STAGING_BRANCH=main` wybiera początkowy branch stagingu. `HOSTINGER_STAGING_ENABLED` pozostaw wyłączone do przygotowania serwera. Dopiero po przeglądzie skryptów i spełnieniu warunków ustaw ją na `true`. To włącza przyszłe deploye po pushu; niniejszy audyt jej nie ustawia.
4. Po własnej akceptacji wykonaj commit i push. Automatyczny workflow wykona testy i build, prześle paczkę do prywatnego `incoming` i uruchomi `scripts/deploy-staging.sh`. Jeśli testy/audyt npm/Composer nie przejdą, deployment nie wystartuje.

Na serwerze wykonywane są:

```bash
composer2 install --no-dev --optimize-autoloader --no-interaction --prefer-dist
composer2 check-platform-reqs --no-dev
php scripts/check-staging.php
# Wyłącznie pierwszy start z pustym APP_KEY:
php artisan key:generate --force
php artisan config:cache
php artisan route:cache
php artisan storage:link
php artisan down --retry=60
php artisan migrate --force
php artisan view:cache
# Publikacja publicznych zasobów i zmiana symlinku current:
php artisan up
```

Skrypt używa zapisanych pełnych ścieżek PHP/Composer. `config:cache`, `route:cache`, `view:cache` zostały sprawdzone. `optimize:clear` celowo nie jest standardowym krokiem: wydanie ma świeży cache konfiguracji/tras, a przy `CACHE_STORE=database` globalne czyszczenie przed pierwszą migracją próbuje korzystać z nieistniejącej tabeli; przy aktualizacji niepotrzebnie usuwa także cache aplikacji. Diagnostycznie można użyć pojedynczych `config:clear`/`route:clear` i następnie odtworzyć cache. `view:cache` uruchamia się w trybie konserwacji, bo katalog skompilowanych widoków jest współdzielony.

Nie używaj `composer update`, `composer run setup`, `npm run dev`, `migrate:fresh`, `migrate:refresh`, `db:wipe`, `db:seed` ani `git clean -fdx` jako deploymentu. Nie kopiuj lokalnego SQLite do produkcji. Nie dodawaj `--delete` do synchronizacji plików. Node/npm nie jest potrzebny na serwerze.

## Zamówienia przez Instagram i e-mail

Domyślny tryb to wiadomości (shop_order_mode=message, także gdy wpisu jeszcze nie ma). Nie wymaga nowej migracji ani zmiany lokalnego pliku .env. Ustawienia są w istniejącej tabeli app_settings, więc pozostają po deploymentach. Nie uruchamiaj seedera.

1. Jako administrator otwórz **Panel → Sklep → Ustawienia zamówień** (/admin/shop-settings). Wpisz e-mail do zamówień oraz instrukcje płatności, wysyłki i odbioru. Pusty e-mail ukrywa przycisk poczty; Instagram nadal działa. Domyślna instrukcja każe poczekać na potwierdzenie i dane do płatności, nie zawiera wymyślonego numeru rachunku.
2. Klient w koszyku klika **Zamów**, wybiera Paczkomat InPost B lub bezpłatny odbiór na meczu i generuje tekst. Wiadomość zawiera ID/nazwę produktu, rozmiar, ilość, aktualne ceny z bazy, koszt dostawy i łączną kwotę. Kwoty dostarczone przez przeglądarkę są ignorowane. Ceny zachowują sposób prezentacji obecnego sklepu.
3. Klient kopiuje tekst i sam wysyła go przez przycisk Instagram DM lub pocztę. Link do profilu jest alternatywą, jeśli przekierowanie DM nie działa na urządzeniu. Nie wysyłamy automatycznych maili ani wiadomości. Generator nie rezerwuje stanów, nie czyści koszyka i nie zapisuje rekordu zamówienia; listę takich zamówień obsługuj w korespondencji. Dotychczasowe zamówienia w bazie są zachowane.
4. Koszt **jednej paczki B** jest pobierany po HTTPS z [publicznego cennika InPost](https://inpost.pl/cenniki), sekcja „InPost Szybkie Nadania Paczkomat”. Gabaryt B: do 19 × 38 × 64 cm, 25 kg. Na 22.09.2026 rzeczywisty odczyt zwrócił **18,49 zł brutto**; cena nie jest wpisana na stałe do kodu. Udany odczyt jest przechowywany 6 godzin i odświeżany przy kolejnym żądaniu. Nie wymaga crona ani workera.
5. Pobieranie zależy od struktury oficjalnej strony HTML, nie od gwarantowanego API. Wymagane są PHP DOM/libxml (już zależności aplikacji) oraz wychodzące HTTPS do inpost.pl z poprawną weryfikacją TLS. Błąd sieci lub niejednoznaczny cennik blokuje InPost, nigdy nie podstawia darmowej ani starej ceny. Ponowienie najwcześniej po minucie; odbiór na meczu działa dalej. Po zmianach strony InPost trzeba poprawić parser i jego testy. Na stagingu sprawdź rzeczywisty odczyt.
6. Gabaryt B jest świadomym, stałym założeniem dla całego koszyka; system nie zna wymiarów produktów i nie pakuje ich automatycznie. Jeśli zamówienie nie mieści się w jednej paczce, uzgodnij koszt z klientem przed płatnością. Domyślna instrukcja klienta o tym informuje.
7. Powrót do starego sklepu jest globalny, tylko dla administratora. Wymaga okna ostrzegawczego, zaznaczenia zgody i wpisania **ZMIENIAM TRYB SKLEPU**; backend sprawdza wszystkie warunki. Samo przełączenie nie naprawia P24 ani stubów wysyłki. Nie włączaj starego trybu przed naprawami i testami opisanymi w audycie. W trybie wiadomości bezpośrednie POST-y starej kasy, webhook i generowanie etykiet są zablokowane; właściciel nadal może zobaczyć historyczne potwierdzenie.

Odbiór: sprawdź gościa i zalogowanego klienta, warianty i ilości, obie dostawy, kopiowanie na telefonie/komputerze, DM i pocztę, brak ceny InPost przy awarii, edycję instrukcji i odmowę dostępu pracownikowi. Sprawdź, że po zmianie dostawy stary tekst znika do ponownego wygenerowania. Nie przełączaj na rzeczywiste płatności w ramach tych testów.

## Poczta i kolejki

Sam generator wiadomości nie używa kolejki ani SMTP. SMTP pozostaje wymagane dla rejestracji i resetu hasła; istniejące joby starych zamówień nadal mogą wymagać crona.

Na obecnym stagingu ustawiono `MAIL_MAILER=log`; rejestracja i reset hasła zapisują wiadomości wyłącznie w prywatnym logu. Dla późniejszej produkcji ustaw `MAIL_MAILER=smtp`. Dla STARTTLS: `MAIL_PORT=587`, `MAIL_ENCRYPTION=tls`; transport wymaga TLS. Dla SMTPS: port 465 i `MAIL_ENCRYPTION=ssl`. `MAIL_SCHEME` opcjonalnie nadpisuje schemat (`smtp`/`smtps`). Nie wyłączaj weryfikacji certyfikatów. `MAIL_TIMEOUT=20` ogranicza zawieszanie requestów/workerów. Sprawdź uprawnienia nadawcy i SPF/DKIM/DMARC u dostawcy poczty.

W hPanel dodaj zadanie typu **Custom** co minutę (lub z minimalną częstotliwością planu):

```bash
/bin/bash /home/UZYTKOWNIK/apps/etb-staging/current/scripts/queue-staging.sh
```

Skrypt korzysta z tej samej blokady co deployment; nie uruchamia dwóch workerów jednocześnie. `--stop-when-empty --max-time=50 --max-jobs=25 --tries=3 --backoff=30 --timeout=60`, `DB_QUEUE_RETRY_AFTER=90`. Limit czasu jest sprawdzany między zadaniami; pojedynczy job może wydłużyć wykonanie. SMTP ma osobny timeout. Nie dodawaj `--force`: worker ma respektować tryb konserwacji. Sprawdź limity procesów/PCNTL i faktyczne uruchomienia crona; przy ograniczeniach nie zmieniaj po cichu sterownika na `sync`.

Monitoruj `php artisan queue:failed` oraz prywatne logi. Ponawiaj konkretny job dopiero po sprawdzeniu, czy mail nie został już wysłany — dostarczenie SMTP i potwierdzenie joba nie są jedną transakcją. Nie publikuj logów/failed_jobs, bo mogą zawierać dane osobowe. Planuj retencję logów, nie automatyczne usuwanie danych użytkowników.

## Pierwszy administrator i dane

Zarejestruj prawdziwe konto na stagingu przez kod SMTP. Po potwierdzeniu tożsamości operator może jednorazowo w prywatnej konsoli `php artisan tinker` odszukać dokładne konto po e-mailu i nadać `role=admin`. Nie twórz konta z hasłem w seederze ani w pliku śledzonym przez Git. Nie wykonuj tego dla niezweryfikowanego konta. Dane demonstracyjne nie są potrzebne do startu; bazowe sekcje/kategorie sponsorów aplikacja inicjalizuje własnymi metodami.

Przeniesienie lokalnych treści, użytkowników, WordPressa lub zdjęć **nie jest wykonywane automatycznie**. Jeśli potrzebne, przygotuj osobną migrację danych: backup SQLite, kopię mediów, mapowanie tabel, kontrolę FK/ID, duplikatów w kolacji MySQL (wielkość liter/akcenty), dat, kwot i ścieżek plików. Najpierw próba na nowej bazie. Nie importuj dumpa SQLite wprost do MySQL i nie zastępuj istniejącej bazy. Zachowaj oryginały.

## Testy odbiorowe stagingu

- Staging działa przez HTTPS i zwraca nagłówek `X-Robots-Tag: noindex, nofollow, noarchive`; `robots.txt` blokuje indeksowanie. WordPress pod domeną główną nadal działa bez zmian. Ochrona HTTP nie jest obecnie włączona.
- Sprawdź stronę główną, wszystkie menu, logowanie/wylogowanie, aktywację, reset hasła, zmianę adresu e-mail i uprawnienia fan/pracownik/admin. Sprawdź cookies Secure/HttpOnly/SameSite i brak współdzielenia z domeną główną.
- Odbierz rzeczywiste maile, sprawdź domenę linków i ich ważność. Przetestuj kolejkę oraz błędny SMTP. Sprawdź brak szczegółów wyjątków w odpowiedziach HTTP.
- `/.env`, `/composer.json`, `/vendor/autoload.php`, `/app/`, `/config/`, `/database/`, `/storage/logs/laravel.log`, `/storage/invoices/...`, `/storage/app/private/...` mają zwracać 403/404, nigdy treść. `/storage/` wskazuje tylko katalog publicznych mediów. Osobno sprawdź, że serwer nie wykonuje `.php` z uploadów.
- Dodaj pliki w każdej kategorii z inwentaryzacji. Otwórz je przez `/storage/...`; odrzuć nieobraz i zbyt duży plik. Wykonaj drugie wdrożenie i porównaj adresy oraz sumy kontrolne wcześniej przesłanych zdjęć i prywatnej faktury.
- Sprawdź `public/build/manifest.json` w wydaniu, odpowiedzi 200 dla wskazanych CSS/JS, brak odwołań do portu 5173 i `public/hot`. Sprawdź wersję w `etb-release.txt`. Cache PHP/OPcache nie może stale serwować starego wydania; jeżeli aktualizacja nie jest widoczna, zresetuj OPcache mechanizmem hostingu i potwierdź ustawienia z dostawcą.
- Przetestuj duże polskie treści, długie URL-e, turnieje, filtry i galerie na docelowym MySQL. Sprawdź odtworzenie backupu na osobnej bazie/katalogu.
- Płatności, faktury i przesyłki wymagają osobnego odbioru po usunięciu opisanych blokerów. Nie traktuj obecnych mockowanych testów jako potwierdzenia działania API operatorów.

## Kolejne aktualizacje i awarie

Po jednorazowym uruchomieniu integracji typowa aktualizacja to:

```bash
git status
git diff
git add .
git diff --cached --stat
git commit -m "Opis poprawki"
git push origin main
```

Push inicjuje testy → build → paczkę → staging. `.env` i media nie są częścią paczki. Każde wydanie dostaje osobny katalog; oba rodzaje storage, logi i klucz zostają w `shared`. Pliki publiczne są kopiowane bez kasowania poprzednich; stare hashowane zasoby można później usuwać dopiero po ustaleniu retencji i możliwości rollbacku. Monitoruj wolne miejsce, bo skrypt celowo nie usuwa wydań ani archiwów automatycznie.

Jeśli skrypt zawiedzie po `artisan down`, aplikacja zostaje w konserwacji. Sprawdź log workflow i prywatne logi. Nie wykonuj automatycznego `migrate:rollback`: DDL MySQL nie zapewnia rollbacku całego wdrożenia, a stary kod może nie pasować do nowego schematu. Nowa migracja poszerzająca kolumny odmawia automatycznego zwężania, aby nie obciąć treści.

Przy zgodnym schemacie operator może przywrócić symlink `current` do poprzedniego, zachowanego wydania, odtworzyć `view:cache` dla niego i wykonać `artisan up`. `.env` i storage zostają wspólne. Przy niezgodnym schemacie wymagana jest przemyślana naprawa lub odtworzenie skoordynowanego backupu na osobnej bazie, z zachowaniem danych powstałych po backupie. Nie nadpisuj działającej bazy automatycznie.

## Późniejsze przełączenie domeny

Obecny skrypt i workflow **nie wdrażają na eattheball.pl**. Po pełnym odbiorze stagingu osobno przygotuj środowisko produkcyjne: prywatny katalog, osobne `.env`/APP_KEY/bazę/storage, `APP_URL=https://eattheball.pl`, cookies/prefix produkcji, SMTP, rzeczywiste i przetestowane integracje. Zaplanuj migrację treści i ręczne przełączenie oraz zachowaj backup WordPressa. Dopiero wtedy rozszerz skrypt o jawnie wskazaną produkcję i Environment `production` z wymaganym zatwierdzeniem. Docelowo zmienna `HOSTINGER_STAGING_BRANCH=develop` kieruje `develop` na staging; `main` pozostaje docelową gałęzią produkcyjną. Samo przestawienie zmiennej nie wdraża domeny głównej.

## Weryfikacja lokalna

- Przed zmianami: 177 testów / 831 asercji na SQLite i MariaDB.
- Po przygotowaniu wdrożenia: 180 testów / 841 asercji na SQLite i MariaDB 10.4.32, PHP 8.2.12; nowa izolowana baza testowa, bez uruchamiania migracji na lokalnych danych aplikacji.
- `composer validate --no-check-publish`, `composer check-platform-reqs`, `composer audit --locked --no-dev`: poprawne.
- `npm ci`, produkcyjny build Vite i kontrola manifestu: poprawne. Po poprawkach npm ponownie wykonano build; audyt 0 podatności.
- `config:cache`, `route:cache` (189 tras aplikacji), `view:cache`: poprawne, sprawdzone z konfiguracją produkcyjną i cache poza repozytorium.
- Składnia skryptów Bash i zmienionego PHP: sprawdzona. Workflow GitHub, SSH, reguły Apache/LiteSpeed i połączenia SMTP nie zostały przetestowane na Hostingerze.

- Po dodaniu zamówień przez wiadomości: 195 testów / 943 asercje na SQLite; rzeczywisty odczyt cennika InPost przez PHP zwrócił 1849 groszy. Testy obejmują uprawnienia, przełącznik, błędny cennik, ceny, warianty i brak automatycznego zamówienia.

## Źródła ograniczeń hostingu

Sprawdzone podczas audytu, lecz dostępność funkcji zależy od wykupionego planu:

- [Hostinger: ograniczenia document root](https://www.hostinger.com/support/1583494-what-is-the-path-to-your-website-s-root-home-directory-and-how-to-change-it-in-hostinger/)
- [Hostinger: wdrażanie Git](https://www.hostinger.com/support/1583302-how-to-deploy-a-git-repository-in-hostinger/)
- [Hostinger: Composer 2](https://www.hostinger.com/support/5792078-how-to-use-composer-at-hostinger/)
- [Hostinger: włączanie symlinków](https://www.hostinger.com/support/1583694-how-to-create-a-symlink-at-hostinger/)
- [Hostinger: cron z poleceniami powłoki](https://www.hostinger.com/support/5646919-how-to-set-up-a-cron-job-with-special-characters-at-hostinger/)
- [Przelewy24: oficjalna specyfikacja API](https://developers.przelewy24.pl/yaml/pl_documentation_1.0.yaml)


## Aktualizacja zlecenia: istniejący staging Hostinger

Użytkownik autoryzował wdrożenie wyłącznie test.eattheball.pl. Zgłoszony checkout znajduje się w /home/u112182572/domains/test.eattheball.pl/public_html/app, gałąź main; docelowy PHP CLI 8.3.33. Dostęp SSH: 45.84.206.249, port 65002, konto u112182572. Baza i użytkownik: u112182572_etb_test; host localhost, port 3306. Te informacje wymagają potwierdzenia po zalogowaniu.

Na tym etapie świadomie używamy **MAIL_MAILER=log**. Kontrola scripts/check-staging.php dopuszcza log wyłącznie dla wskazanego stagingu; dla smtp nadal wymaga właściwych parametrów. Rejestracja i reset hasła nie dostarczą wiadomości do skrzynki: kody/linki zostaną w prywatnych logach. Testy tych przepływów wymagają kontrolowanego odczytu na serwerze, bez kopiowania kodów i logów do rozmowy. Nie traktuj tego jako gotowości poczty produkcyjnej.

Workflow i skrypt zostały dopasowane do prywatnych wydań w `~/apps/etb-staging`. `public_html/index.php` ładuje symlink `current`, publiczne zasoby są kopiowane bez `--delete`, a `/app` jest blokowane przez nadrzędny i własny `.htaccess`.

Dedykowany klucz SSH został autoryzowany. Uszkodzony `.env` zachowano w prywatnej kopii; nowy plik ma prawa 600 i osobny wygenerowany `APP_KEY`. Hasła MySQL nie zapisano w repozytorium ani odpowiedziach.

Lokalny `npm ci` i `npm run build` przeszły poprawnie; build przesłano w paczce i zasoby CSS/JS zwracają 200. Wdrożono lokalny stan roboczy, który zawiera niezatwierdzone zmiany, dlatego przed uruchomieniem automatyzacji trzeba je przejrzeć, zatwierdzić i dopiero na osobne polecenie wypchnąć do GitHub.
## Wykonany pierwszy deployment stagingu — 23.09.2026

- Prywatna struktura: `~/apps/etb-staging/{incoming,releases,shared,current}`. `.env`, storage, logi i uploady są współdzielone między wydaniami.
- Publiczny katalog: `~/domains/test.eattheball.pl/public_html`. Wystawia tylko front controller, build, statyczne obrazy i symlink `/storage`; `/app`, `.env`, vendor, config i logi zwracają 403/404.
- MySQL: nowa, pusta baza; wykonano wyłącznie `php artisan migrate --force`. Wszystkie migracje mają status `Ran`.
- Wygenerowano stagingowy `APP_KEY`; `APP_DEBUG=false`, `APP_ENV=production`, `APP_URL=https://test.eattheball.pl`, `MAIL_MAILER=log`.
- `config:cache`, `route:cache` (197 tras) i `view:cache` działają. `storage:link` przez Artisan nie działa z powodu zablokowanego PHP `exec()`; równoważny symlink tworzy skrypt przez `ln -s`.
- Pierwsze i drugie wydanie zakończyły się poprawnie. Drugie zwróciło `Nothing to migrate`, przełączyło `current` i przywróciło aplikację z maintenance mode.
- Po drugim wydaniu: `/`, `/login`, `/register`, `/shop`, `/cart`, CSS i JS zwracają 200; losowa trasa 404; POST logowania bez CSRF 419; panel administratora przekierowuje do logowania; źródła prywatne 403/404.
- Plik `storage/app/public/deployment-storage-check.png` pozostał dostępny przez `/storage/...` po drugim wdrożeniu, co potwierdza trwałość mediów.
- Rzeczywisty odczyt publicznego cennika InPost zwrócił 18,49 zł dla Paczkomatu B.
- W logach są wyłącznie błędy powstałe podczas kontrolowanych prób: dwa odrzucone hasła MySQL i niedostępne PHP `exec()`; przyczyny zostały usunięte lub obsłużone.

Nie uruchomiono SMTP, płatności P24, automatycznych etykiet, crona kolejki ani produkcyjnej domeny. Nie wykonano seederów, `migrate:fresh`, rollbacku, usuwania bazy, commita ani pushu.